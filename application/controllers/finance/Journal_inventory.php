<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property CI_Form_validation $form_validation
 * @property Crud $crud
 * @property AutoPostingJournal $autopostingjournal
 */
class Journal_inventory extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');

        $this->load->model('crud');
        $this->load->model('autopostingjournal');

        $this->_check_table_exist();
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['approval'] = $this->crud->read('signatures');
            $data['menus_id'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('finance/journal_inventory');
        } else {
            redirect('error_access');
        }
    }

    // Fungsi Private untuk validasi tabel
    private function _check_table_exist() {
        if (!$this->db->table_exists('journal_inventory')) {
            die("<pre style='font-size:14pt;'> Database Error: Tabel 'journal_inventory' not found! Please contact admin.</pre>");
        }
    }

    public function number($journal_date)
    {
        $datenow    = "GLINV" . date("ym", strtotime(base64_decode($journal_date)));
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM journal_inventory WHERE `number` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $datenow . $autoID;
    }

    // Module Journal Inventory dari Journal Types (is_journal_inventory = 1), 1 module = 1 menu
    public function readModul()
    {
        $search = strtoupper(trim($this->input->post('q') ?? ''));

        $this->db->select('number, module, inventory_process, transaction_type, division');
        $this->db->from('journal_types');
        $this->db->where('deleted', 0);
        $this->db->where('is_journal_inventory', 1);
        $journal_types = $this->db->get()->result();

        $records = [];
        foreach ($journal_types as $jt) {
            // Hanya module yang didukung auto posting (BPM memakai Journal Type TRANSACTION RM + BPM)
            $modules = [];
            foreach (array_map('trim', explode(',', $jt->module)) as $jt_module) {
                $modules = array_merge($modules, $this->autopostingjournal->modules_of_journal_type($jt_module, $jt->transaction_type));
            }

            foreach ($modules as $module) {
                if ($search && strpos($module, $search) === false) continue;

                if (!isset($records[$module])) {
                    $records[$module] = [
                        'id'                  => $module,
                        'name'                => $module,
                        'kind'                => [],
                        'journal_types'       => [],
                        'is_company_required' => $this->autopostingjournal->modules[$module]['company_required'],
                    ];
                }
                if ($jt->inventory_process) $records[$module]['kind'][] = $jt->inventory_process;
                $records[$module]['journal_types'][] = $jt->number . ($jt->transaction_type ? " ({$jt->transaction_type})" : "") . ($jt->division ? " [{$jt->division}]" : "");
            }
        }

        foreach ($records as &$record) {
            $record['kind'] = implode('/', array_unique($record['kind']));
            $record['journal_types'] = implode(', ', $record['journal_types']);
        }
        ksort($records);

        echo json_encode(array_values($records));
    }

    public function readJournalType()
    {
        $search = $this->input->post('q') ?? '';
        $modul  = base64_decode($this->input->post('modul') ?? $this->input->get('modul') ?? '');

        $this->db->select('*');
        $this->db->from('journal_types');
        $this->db->where('deleted', 0);
        $this->db->where('is_journal_inventory', 1);
        if ($search) {
            $this->db->like('name', $search);
        }
        if ($modul) {
            // Module tanpa Journal Type sendiri (BPM) memakai module + transaction type setup-nya
            $setup = $this->autopostingjournal->modules[$modul]['setup'] ?? null;
            if ($setup) {
                list($modul, $transaction_type) = $setup;
                $this->db->where('transaction_type', $transaction_type);
            }
            $this->db->where("FIND_IN_SET(" . $this->db->escape($modul) . ", REPLACE(module, ', ', ',')) > 0", NULL, FALSE);
        }
        $this->db->order_by('module', 'asc');
        $this->db->order_by('name', 'asc');

        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readCompany()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $get = $this->input->get();

        $this->db->select('id as company_id, name as company_name');
        $this->db->from('suppliers');
        $this->db->like('name', $post);
        $this->db->order_by('name', 'asc');
        $records = $this->db->get()->result_array();
        echo json_encode($records); 
    }

    public function readDocumentNo()
    {
        $post = $this->input->post();

        $modul        = $this->autopostingjournal->normalize_module(base64_decode($post['modul'] ?? ''));
        $journal_date = base64_decode($post['journal_date'] ?? '');
        $company_id   = base64_decode($post['company_id'] ?? '');

        // Set Period 1 month
        $transaction_from = !empty($journal_date) ? date("Y-m-01", strtotime((string) $journal_date)) : date("Y-m-01");
        $transaction_to   = !empty($journal_date) ? date("Y-m-t", strtotime((string) $journal_date)) : date("Y-m-t");

        // Dokumen yang sudah dijurnal untuk module ini
        $journaled = "SELECT document_no FROM journal_inventory WHERE modul = " . $this->db->escape($modul);

        $records = [];

        if ($modul == "PURCHASE ORDER RECEIPT")
        {
            // POR closed = semua label sudah di-scan (sama dengan Autopostingjournal::is_por_ready)
            $not_scanned = "SELECT x.receipt_no FROM purchase_order_receipts x
                LEFT JOIN (SELECT receipt_id, SUM(`status`) as total_scan FROM purchase_order_labels GROUP BY receipt_id) l ON x.receipt_id = l.receipt_id
                WHERE x.deleted = 0 AND COALESCE(l.total_scan, 0) < x.qty_label";

            $this->db->select('a.receipt_no as document_no, MAX(a.supplier_id) as supplier_id, MIN(a.receipt_date) as trans_date', FALSE);
            $this->db->from('purchase_order_receipts a');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.supplier_id', $company_id);
            $this->db->where('a.receipt_date >=', $transaction_from);
            $this->db->where('a.receipt_date <=', $transaction_to);
            $this->db->where("a.receipt_no NOT IN ($not_scanned)", NULL, FALSE);
            $this->db->where("a.receipt_no NOT IN ($journaled)", NULL, FALSE);
            $this->db->where("NOT " . $this->autopostingjournal->non_rm_por_sql('a.receipt_no'), NULL, FALSE);
            $this->db->group_by('a.receipt_no');
            $this->db->order_by('a.receipt_no', 'asc');

            $records = $this->db->get()->result_array();
        }
        elseif ($modul == "BPM")
        {
            // BPM (INJ): semua item sudah ter-scan penuh (status = 1)
            $this->db->select('a.request_no as document_no, MIN(a.request_date) as trans_date', FALSE);
            $this->db->from('bpm a');
            $this->db->where('a.deleted', 0);
            $this->db->where("a.request_no NOT IN ($journaled)", NULL, FALSE);
            $this->db->group_by('a.request_no');
            $this->db->having('SUM(a.status <> 1) = 0', NULL, FALSE);
            $this->db->order_by('trans_date', 'DESC');

            $records = $this->db->get()->result_array();
        }
        elseif (in_array($modul, ["SUPPLY SHEET", "NON SUPPLY SHEET", "MATERIAL REQUESTION"]))
        {
            // Semua item dokumen sudah selesai di-issue (Supply Sheet: 1 = selesai, 2 = Closing SH)
            $tables = ["SUPPLY SHEET" => ['supply_sheets', '1, 2'], "NON SUPPLY SHEET" => ['supply_materials', '1'], "MATERIAL REQUESTION" => ['supply_requestions', '1']];
            list($table, $closed_status) = $tables[$modul];

            $this->db->select('a.request_no as document_no, MIN(a.request_date) as trans_date', FALSE);
            $this->db->from("$table a");
            $this->db->where('a.deleted', 0);
            $this->db->where('a.request_date >=', $transaction_from);
            $this->db->where('a.request_date <=', $transaction_to);
            $this->db->where("a.request_no NOT IN ($journaled)", NULL, FALSE);
            $this->db->group_by('a.request_no');
            $this->db->having("SUM(a.status NOT IN ($closed_status)) = 0", NULL, FALSE);
            $this->db->order_by('a.request_no', 'asc');

            $records = $this->db->get()->result_array();
        }
        elseif ($modul == "TRANSACTION RM")
        {
            // Hanya jenis transaksi yang sudah punya Journal Type
            $this->db->select('a.request_no as document_no, MIN(a.request_date) as trans_date, MAX(a.transaction_type) as transaction_type', FALSE);
            $this->db->from('transaction_rm a');
            $this->db->join('journal_types j', "j.transaction_type = a.transaction_type AND j.is_journal_inventory = 1 AND j.deleted = 0 AND FIND_IN_SET('TRANSACTION RM', REPLACE(j.module, ', ', ',')) > 0");
            $this->db->where('a.deleted', 0);
            $this->db->where('a.request_date >=', $transaction_from);
            $this->db->where('a.request_date <=', $transaction_to);
            $this->db->where("a.request_no NOT IN ($journaled)", NULL, FALSE);
            $this->db->group_by('a.request_no');
            $this->db->order_by('a.request_no', 'asc');

            $records = $this->db->get()->result_array();
        }

        header('Content-Type: application/json');
        echo json_encode($records);
    }


    // Check Status Can Be Posting Journal Inventory
    public function validate_posting_eligibility()
    {
        $post = $this->input->post();

        $modul       = $this->autopostingjournal->normalize_module($post['modul'] ?? '');
        $document_no = $post['document_no'] ?? '';

        if (empty($modul) || empty($document_no)) {
            echo json_encode(['status' => false, 'message' => "Module or Document No. parameters are incomplete."]);
            return;
        }

        if ($this->autopostingjournal->check_duplicate_entry($modul, $document_no)) {
            echo json_encode(['status' => false, 'message' => "Document Number $document_no has already been journaled for module $modul"]);
            return;
        }

        // Validasi lengkap (status dokumen, setup akun, harga) sama dengan auto posting
        $preview = $this->autopostingjournal->preview($modul, $document_no);
        echo json_encode(['status' => $preview['status'], 'message' => $preview['message']]);
    }

    // Get temporary data
    public function datatablesTemp()
    {
        $post = $this->input->post();

        $modul       = base64_decode($post['modul'] ?? '');
        $document_no = trim(base64_decode($post['document_no'] ?? ''));

        if (empty($modul) || empty($document_no)) {
            echo json_encode(["total" => 0, "rows" => [], "footer" => [], "message" => "Module or Document No. is empty."]);
            return;
        }

        // Preview memakai perhitungan yang sama dengan auto posting
        $preview = $this->autopostingjournal->preview($modul, $document_no);

        header('Content-Type: application/json');
        echo json_encode([
            "total"   => count($preview['rows']),
            "rows"    => $preview['rows'],
            "footer"  => $preview['footer'],
            "message" => $preview['message'],
        ]);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from            = base64_decode((string) $this->input->get('filter_from')) ?? null;
            $filter_to              = base64_decode((string) $this->input->get('filter_to')) ?? null;
            $filter_journal_type    = base64_decode((string) $this->input->get('filter_journal_type')) ?? null;
            $filter_modul           = base64_decode((string) $this->input->get('filter_modul')) ?? null;
            $filter_voucher         = base64_decode((string) $this->input->get('filter_voucher')) ?? null;
            $filter_item_category   = base64_decode((string) $this->input->get('filter_item_category')) ?? null;

            //Pagination 1-10
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            // Aggregate MAX() agar aman di MySQL ONLY_FULL_GROUP_BY
            $this->db->select('
                por.receipt_no,
                MAX(item.id) AS id,
                MAX(item.name) AS name,
                MAX(item.division) AS division,
                MAX(item.item_category_id) AS item_category_id,
                MAX(jenis.number) AS category_code', FALSE);
            $this->db->from('purchase_order_receipts por');
            $this->db->join('item_rm item', 'por.item_rm_id = item.id');
            $this->db->join("item_categories jenis", "jenis.id = item.item_category_id", "left");
            $this->db->where('por.status', 0);
            $this->db->group_by('por.receipt_no');
            $sub_por = $this->db->get_compiled_select();

            //Select Query
            $this->db->select('a.number,
                MAX(a.journal_date) as journal_date, MAX(a.journal_type_id) as journal_type_id, MAX(b.name) as journal_type_name,
                MAX(a.document_no) as document_no, MAX(a.modul) as modul, MAX(a.remarks) as remarks, MAX(a.currency) as currency,
                MAX(a.rates) as rates, MAX(a.posting) as posting,
                MAX(a.created_by) as created_by, MAX(a.created_date) as created_date, MAX(a.updated_by) as updated_by, MAX(a.updated_date) as updated_date,
                MAX(a.approved) as approved, MAX(a.approved_to) as approved_to, MAX(a.approved_by) as approved_by, MAX(a.approved_date) as approved_date,
                MAX(c.division) as division, MAX(c.item_category_id) as item_category_id, MAX(c.category_code) as category_code,
                SUM(a.original_debit) as original_debit, 
                SUM(a.original_credit) as original_credit, 
                SUM(a.local_debit) as local_debit, 
                SUM(a.local_credit) as local_credit', FALSE);
            $this->db->from('journal_inventory a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id', 'left');
            $this->db->join("($sub_por) c", "a.document_no = c.receipt_no", 'left', FALSE);

            if ($filter_from != "" && $filter_to != "") {
                $this->db->where("a.journal_date BETWEEN '$filter_from' and '$filter_to'");
            }
            if ($filter_journal_type != "") {
                $this->db->like('a.journal_type_id', $filter_journal_type);
            }
            if ($filter_modul != "") {
                $this->db->like('a.modul', $filter_modul);
            }
            if ($filter_voucher != "") {
                $this->db->like('a.number', $filter_voucher);
            }
            if ($filter_item_category != "") {
                $this->db->like('c.item_category_id', $filter_item_category);
            }
            $this->db->group_by('a.number');
            $this->db->order_by('journal_date', 'asc');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            $this->db->limit($rows, $offset);
            $records = $this->db->get()->result_array();

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatablesCheck()
    {
        $post = $this->input->post();

        $modul       = $this->autopostingjournal->normalize_module($post['modul'] ?? '');
        $document_no = $post['document_no'] ?? '';

        if (empty($modul) || empty($document_no)) {
            echo json_encode(['status' => false, 'message' => 'Module or Document No. parameters are incomplete.']);
            return;
        }

        // POR non-RM / label belum lengkap (misal saat Print Receiving Note): belum dijurnal, hanya info
        $por_ready = ($modul == 'PURCHASE ORDER RECEIPT') ? $this->autopostingjournal->is_por_ready($document_no) : ['ready' => true];

        // Cek duplikat sama dengan auto posting (semua periode)
        if ($this->autopostingjournal->check_duplicate_entry($modul, $document_no)) {
            echo json_encode(['status' => false, 'message' => "Document $document_no has already been journaled."]);
        } elseif (!$por_ready['ready']) {
            echo json_encode(['status' => false, 'message' => $por_ready['message']]);
        } else {
            echo json_encode(['status' => true, 'message' => 'Ready to post']);
        }
    }

    public function datatableDetails()
    {
        $number = base64_decode((string) $this->input->get('number'));
        $filters = json_decode($this->input->post('filterRules') ?? '[]');

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select('*');
        $this->db->from('journal_inventory');
        $this->db->where('number', $number);
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('document_no', 'asc');
        $this->db->order_by('journal_date', 'asc');
        $this->db->order_by('account_number', 'asc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $data = array();
        $grand_original_debit = 0;
        $grand_original_credit = 0;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        foreach ($records as $record) {
            $data[] = array(
                "trans_date" => $record['trans_date'],
                "document_no" => $record['document_no'],
                "invoice_no" => $record['invoice_no'],
                "company_name" => $record['company_name'],
                "description" => $record['description'],
                "account_number" => $record['account_number'],
                "account_name" => $record['account_name'],
                "currency" => $record['currency'],
                "original_debit" => $record['original_debit'],
                "original_credit" => $record['original_credit'],
                "rates" => $record['rates'],
                "local_debit" => $record['local_debit'],
                "local_credit" => $record['local_credit'],
            );

            $grand_original_debit += $record['original_debit'];
            $grand_original_credit += $record['original_credit'];
            $grand_local_debit += $record['local_debit'];
            $grand_local_credit += $record['local_credit'];
        }

        $footer[] = array(
            "currency" => "TOTAL",
            "original_debit" => $grand_original_debit,
            "original_credit" => $grand_original_credit,
            "local_debit" => $grand_local_debit,
            "local_credit" => $grand_local_credit,
        );

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records, 'footer' => $footer]);
        echo json_encode($result);
    }

    public function datatableUpdates()
    {
        $number = base64_decode((string) $this->input->get('number'));

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select('*');
        $this->db->from('journal_inventory');
        $this->db->where('number', $number);
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        // $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $data = array();
        $grand_original_debit = 0;
        $grand_original_credit = 0;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        foreach ($records as $record) {
            array_push($data, $record);

            $grand_original_debit += $record['original_debit'];
            $grand_original_credit += $record['original_credit'];
            $grand_local_debit += $record['local_debit'];
            $grand_local_credit += $record['local_credit'];
        }

        $footer[] = array(
            "currency" => "TOTAL",
            "original_debit" => $grand_original_debit,
            "original_credit" => $grand_original_credit,
            "local_debit" => $grand_local_debit,
            "local_credit" => $grand_local_credit,
        );

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records, 'footer' => $footer]);
        echo json_encode($result);    
    }

    // INSERT MANUAL
    public function create()
    {
        $post = $this->input->post();
        $details = json_decode($post['details'] ?? '[]', true);
        $post['modul'] = $this->autopostingjournal->normalize_module($post['modul'] ?? '');

        if (empty($details)) {
            echo json_encode(['title' => 'Error', 'message' => 'No detail data received', 'theme' => 'error']);
            return;
        }

        $this->db->trans_begin();

        try {
            // validate exist = true
            $is_duplicated = $this->autopostingjournal->check_duplicate_entry($post['modul'], $post['document_no']);
            
            if ($is_duplicated) {
                throw new Exception("Document Number " . $post['document_no'] . " has already been journaled for module " . $post['modul']);
            }

            foreach ($details as $row) {
                $autoID = $this->crud->autoid('journal_inventory'); 

                $data_insert = [
                    'id'              => $autoID,
                    'number'          => $post['voucher_no'],
                    'journal_date'    => $post['journal_date'],
                    'journal_type_id' => $row['journal_type_id'],
                    'trans_date'      => $row['trans_date'],
                    'document_no'     => $row['document_no'],
                    'invoice_no'      => $row['invoice_no'] ?? '-',
                    'account_number'  => $row['account_number'],
                    'account_name'    => $row['account_name'],
                    'description'     => $row['description'],
                    'original_debit'  => $row['original_debit'],
                    'original_credit' => $row['original_credit'],
                    'local_debit'     => $row['local_debit'],
                    'local_credit'    => $row['local_credit'],
                    'rates'           => $row['rates'],
                    'currency'        => $row['currency'],
                    'company_name'    => $row['company_name'] ?? '-',
                    'company_id'      => $post['company_id'] ?? null,
                    'modul'           => $post['modul'],
                    'remarks'         => $post['remarks'] ?? null,
                    'created_date'    => date('Y-m-d H:i:s'),
                    'created_by'      => $this->session->username,
                ];
                
                if (!$this->db->insert('journal_inventory', $data_insert)) {
                    $err = $this->db->error();
                    throw new Exception("Insert Failed: " . $err['message']);
                }
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Database transaction failed");
            } else {
                $this->db->trans_commit();
                // Dokumen yang sebelumnya gagal auto posting dianggap selesai
                $this->autopostingjournal->queue_mark_success($post['modul'], $post['document_no'], $post['voucher_no']);
                echo json_encode(['title' => 'Success', 'message' => 'Journal saved successfully', 'theme' => 'success']);
            }

        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['title' => 'Failed', 'message' => $e->getMessage(), 'theme' => 'error']);
        }
    }

    /** -------------------------------------------
     * Failed Posting (journal_inventory_queue)
     ---------------------------------------------- */

    // Daftar dokumen di queue (default FAILED)
    public function datatablesQueue()
    {
        $status  = $this->input->get('status') ?: 'FAILED';
        $filters = json_decode($this->input->post('filterRules') ?? '[]');

        $this->db->from('journal_inventory_queue');
        $this->db->where($this->autopostingjournal->queue_visible_sql(), NULL, FALSE);
        if ($status != 'ALL') $this->db->where('status', $status);
        foreach ((array) $filters as $filter) {
            if (in_array($filter->field, ['modul', 'document_no', 'status', 'last_error', 'last_source', 'journal_number'])) {
                $this->db->like($filter->field, $filter->value);
            }
        }
        $this->db->order_by('last_attempt_date', 'desc');
        $records = $this->db->get()->result_array();

        header('Content-Type: application/json');
        echo json_encode(['total' => count($records), 'rows' => $records]);
    }

    // Jumlah dokumen gagal posting (badge tombol Failed Posting)
    public function countFailed()
    {
        $total = $this->db->where('status', 'FAILED')->where($this->autopostingjournal->queue_visible_sql(), NULL, FALSE)->count_all_results('journal_inventory_queue');
        header('Content-Type: application/json');
        echo json_encode(['total' => $total]);
    }

    // Repost dokumen gagal: ids[] tertentu, atau semua FAILED jika ids kosong
    public function repost()
    {
        if (empty($this->session->username)) {
            echo json_encode(['title' => 'Error', 'theme' => 'error', 'message' => 'Session expired, please login again.']);
            return;
        }

        $ids = $this->input->post('ids');
        $summary = $this->autopostingjournal->repost_failed(!empty($ids) ? (array) $ids : null, 'REPOST');

        $message = "Repost: {$summary['success']} success, {$summary['failed']} failed.";
        foreach ($summary['results'] as $document_no => $result) {
            $message .= "<br>$document_no: $result";
        }
        echo json_encode([
            'title'   => $summary['failed'] > 0 ? 'Warning' : 'Success',
            'theme'   => $summary['failed'] > 0 ? 'warning' : 'success',
            'message' => $message,
            'summary' => $summary,
        ]);
    }

    /**
     * Repost otomatis untuk cron (CLI saja). HTTP_HOST wajib di-set karena base_url memakai $_SERVER['HTTP_HOST']:
     *   HTTP_HOST=erp.domain php index.php finance/journal_inventory/retry_cli            -> repost FAILED (attempts < MAX_AUTO_ATTEMPTS)
     *   HTTP_HOST=erp.domain php index.php finance/journal_inventory/retry_cli 20261001   -> + sapu jaring POR sejak tanggal tsb yang belum dijurnal
     * Tanggal format YYYYMMDD (tanda '-' tidak diizinkan permitted_uri_chars)
     */
    public function retry_cli($sweep_from = null)
    {
        if (!is_cli()) {
            show_404();
            return;
        }

        $summary = $this->autopostingjournal->repost_failed(null, 'CRON', Autopostingjournal::MAX_AUTO_ATTEMPTS);
        echo date('Y-m-d H:i:s') . " Repost FAILED: {$summary['success']} success, {$summary['failed']} failed" . PHP_EOL;

        if ($sweep_from) {
            $date = DateTime::createFromFormat('Ymd', $sweep_from);
            if (!$date || $date->format('Ymd') !== $sweep_from) {
                echo "Invalid sweep date $sweep_from (format YYYYMMDD)" . PHP_EOL;
                return;
            }
            $sweep_from = $date->format('Y-m-d');
            $sweep = $this->autopostingjournal->sweep_por($sweep_from, 'CRON');
            echo date('Y-m-d H:i:s') . " Sweep POR since $sweep_from: {$sweep['success']} success, {$sweep['failed']} failed" . PHP_EOL;
            $summary['results'] += $sweep['results'];
        }

        foreach ($summary['results'] as $document_no => $result) {
            echo "  $document_no: $result" . PHP_EOL;
        }
    }

    // INSERT VIA AUTO-POSTING
    public function execute_auto_journal() 
    {
        $modul = $this->input->post("modul");
        $receipt_no = $this->input->post("document_no");
        
        // Validasi input awal
        if (empty($receipt_no) || empty($modul)) {
            echo json_encode(['status' => 'error', 'message' => 'Document No or Module is missing.']);
            return;
        }

        $result = $this->autopostingjournal->inventory($modul, $receipt_no);
        
        // Check if result error and not return array
        if (!is_array($result)) {
            $result = [
                'status' => false,
                'message' => 'Internal Server Error: Model did not return an array. Result: ' . var_export($result, true)
            ];
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function delete()
    {
        $voucher_numbers = $this->input->post('voucher_numbers');
        if (empty($voucher_numbers)) {
            echo json_encode(['status' => 'error', 'message' => 'No voucher selected']);
            return;
        }

        $this->db->trans_begin();

        try {
            $this->db->where_in('number', $voucher_numbers);
            $this->db->delete('journal_inventory');

            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Failed to delete journal entries from database.");
            }

            $this->db->trans_commit();
            echo json_encode(['title' => 'Success', 'theme' => 'success', 'message' => 'Data deleted successfully']);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['title' => 'Error', 'theme' => 'error', 'message' => $e->getMessage()]);
        }
    }


    // PRINT 
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=posting_journals_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_from = base64_decode((string) $this->input->get('filter_from'));
        $filter_to = base64_decode((string) $this->input->get('filter_to'));
        $filter_journal_type = base64_decode((string) $this->input->get('filter_journal_type'));
        $filter_modul = base64_decode((string) $this->input->get('filter_modul'));
        $filter_voucher = base64_decode((string) $this->input->get('filter_voucher'));

        $this->db->select('a.*, b.name as journal_type_name');
        $this->db->from('journal_inventory a');
        $this->db->join('journal_types b', 'a.journal_type_id = b.id');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where("a.journal_date BETWEEN '$filter_from' and '$filter_to'");
        }
        $this->db->like('a.journal_type_id', $filter_journal_type);
        $this->db->like('a.modul', $filter_modul);
        $this->db->like('a.number', $filter_voucher);
        $this->db->order_by('a.journal_date', 'asc');
        $this->db->order_by('a.number', 'asc');
        $this->db->order_by('a.document_no', 'asc');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>POSTING JOURNAL</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Voucher No</th>
                <th rowspan="2">Journal Date</th>
                <th rowspan="2">Journal Type</th>
                <th rowspan="2">Modul</th>
                <th rowspan="2">Document No</th>
                <th rowspan="2">Invoice No</th>
                <th rowspan="2">Company Name</th>
                <th rowspan="2">Trans Date</th>
                <th rowspan="2">Description</th>
                <th rowspan="2">Account No</th>
                <th rowspan="2">Account Name</th>
                <th colspan="3">Original Debit</th>
                <th colspan="3">Local Debit</th>
            </tr>
            <tr>
                <th>Currency</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Rates</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td style="vertical-align:middle;">' . $no . '</td>
                    <td style="vertical-align:middle;">' . $data['number'] . '</td>
                    <td style="vertical-align:middle;">' . $data['journal_date'] . '</td>
                    <td style="vertical-align:middle;">' . $data['journal_type_name'] . '</td>
                    <td style="vertical-align:middle;">' . $data['modul'] . '</td>
                    <td style="vertical-align:middle;">' . $data['document_no'] . '</td>
                    <td style="vertical-align:middle;">' . $data['invoice_no'] . '</td>
                    <td style="vertical-align:middle;">' . $data['company_name'] . '</td>
                    <td style="vertical-align:middle;">' . $data['trans_date'] . '</td>
                    <td style="vertical-align:middle;">' . $data['description'] . '</td>
                    <td style="vertical-align:middle;">' . $data['account_number'] . '</td>
                    <td style="vertical-align:middle;">' . $data['account_name'] . '</td>
                    <td style="text-align:center; vertical-align:middle;">' . $data['currency'] . '</td>
                    <td style="text-align:right; vertical-align:middle;">' . $this->formatNominal($data['original_debit'], $data['currency'], $option) . '</td>
                    <td style="text-align:right; vertical-align:middle;">' . $this->formatNominal($data['original_credit'], $data['currency'], $option) . '</td>
                    <td style="text-align:center; vertical-align:middle;">' . $this->formatNominal($data['rates'], $data['currency'], $option) . '</td>
                    <td style="text-align:right; vertical-align:middle;">' . $this->formatNominal($data['local_debit'], $data['currency'], $option) . '</td>
                    <td style="text-align:right; vertical-align:middle;">' . $this->formatNominal($data['local_credit'], $data['currency'], $option) . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    private function formatNominal($value, $currency, $option = "") 
    {
        if (!is_numeric($value)) {
            return $value;
        }
        
        $decimal = (empty($currency) || $currency === 'IDR') ? 2 : 4;
        if (!empty($option) && $option == "excel") {
            return number_format((float) $value, $decimal, ".", "");
        } else {
            return number_format((float) $value, $decimal, ",", ".");
        }
    }
}
