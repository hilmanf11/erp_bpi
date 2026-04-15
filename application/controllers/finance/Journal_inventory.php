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
 * @property M_journal_inventory $m_journal_inventory
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
        $this->load->model('m_journal_inventory');

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

    public function readModul()
    {
        $search = $this->input->post('q');
        $kind   = $this->input->post('kind');

        $this->db->select('*');
        $this->db->from('journal_inventory_modules');

        if ($search) {
            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('id', $search);
            $this->db->group_end();
        }

        if ($kind) {
            $this->db->where('kind', $kind);
        }

        $this->db->order_by('name', 'asc');
        $records = $this->db->get()->result_array();
        
        echo json_encode($records);
    }

    public function saveModul()
    {
        $id = $this->input->post('id');
        $is_edit = !empty($id);

        $data = [
            'name'            => strtoupper(trim($this->input->post('name'))),
            'category'        => $this->input->post('category'),
            'kind'            => $this->input->post('kind'),
            'ref_id'          => $this->input->post('ref_id'),
            'description'     => $this->input->post('description'),
            'updated_by'      => $this->session->username,
            'updated_date'    => null,
            'status'          => 1, // default
            'is_company_required' => $this->input->post('is_company_required') ?? 0, // wajib input company_id
        ];
        
        // Validasi create new
        if (!$is_edit) {
            $data['created_by'] = $this->session->username;
        } else {
            $data['id'] = $id;
        }

        $save = $this->m_journal_inventory->save_modul_master($data);

        if ($save) {
            echo json_encode(['status' => 'success', 'message' => 'Module saved successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error when saving module.']);
        }
    }

    public function readJournalType()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $get = $this->input->get();

        $this->db->select('*');
        $this->db->from('journal_types');
        $this->db->like('name', $post);
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

        $modul        = base64_decode($post['modul'] ?? '');
        $journal_date = base64_decode($post['journal_date'] ?? '');
        $company_id   = base64_decode($post['company_id'] ?? '');

        // Set Period 1 month
        $transaction_from = !empty($journal_date) ? date("Y-m-01", strtotime($journal_date)) : date("Y-m-01");
        $transaction_to   = !empty($journal_date) ? date("Y-m-t", strtotime($journal_date)) : date("Y-m-t");

        $records = [];

        if ($modul == "PURCHASE ORDER RECEIPT") 
        {
            // Get status Scan POR
            $subquery = "(SELECT receipt_id, SUM(`status`) as total_scan 
                FROM purchase_order_labels 
                GROUP BY receipt_id) lbl";
            
            $this->db->select('a.receipt_no as document_no, a.supplier_id');
            $this->db->select('a.receipt_date as trans_date');
            $this->db->from('purchase_order_receipts a');
            $this->db->join('journal_inventory b', 'a.receipt_no = b.document_no', 'left');
            $this->db->join($subquery, "a.receipt_id = lbl.receipt_id", "inner");
            
            $this->db->where('lbl.total_scan >', 0);        // POR sudah di-scan = closed
            $this->db->where('a.print', 1);                 // POR GRN = closed
            $this->db->where('a.supplier_id', $company_id);
            $this->db->where('a.receipt_date >=', $transaction_from);
            $this->db->where('a.receipt_date <=', $transaction_to);
            
            // Filter agar hanya dokumen yang belum dijurnal yang muncul
            $this->db->where('b.document_no', NULL);

            $this->db->group_by('a.receipt_no');
            $this->db->order_by('a.receipt_no', 'asc');
            
            $records = $this->db->get()->result_array();
        }
        elseif ($modul == "BPM") 
        {
            // Get BPM
            $this->db->select('a.request_no as document_no, a.request_date as trans_date');
            $this->db->from('bpm a');
            $this->db->where('a.status', 1); // BPM status=closed
            $this->db->where('a.deleted', 0);
            $this->db->where('a.request_no NOT IN (SELECT document_no FROM journal_inventory)'); // Get yang belum di journal
            $this->db->group_by('a.request_no');
            $this->db->order_by('a.request_date', 'DESC');

            $records = $this->db->get()->result_array();
        }
        elseif ($modul == "ADJ IN STO" || $modul == "ADJ OUT STO") 
        {
            $this->db->select('a.request_no as document_no, a.request_date as trans_date');
            $this->db->from('transaction_rm a');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.request_no NOT IN (SELECT document_no FROM journal_inventory)');

            $records = $this->db->get()->result_array();
        }

        header('Content-Type: application/json');
        echo json_encode($records);
    }


    // Check Status Can Be Posting Journal Inventory
    public function validate_posting_eligibility() 
    {
        try {
            $post = $this->input->post();

            $modul       = $post['modul'] ?? '';
            $document_no = $post['document_no'] ?? '';
            $item_rm_id  = $post['item_rm_id'] ?? '';

            if (empty($modul) || empty($document_no)) {
                throw new Exception("Module or Document No. parameters are incomplete.");
            }

            $result = false;

            if ($modul == "PURCHASE ORDER RECEIPT") 
            {
                // Check POR
                $this->db->select('print');
                $por = $this->db->get_where('purchase_order_receipts', ['receipt_no' => $document_no])->row();
                if ($por && $por->print == 1) $result = true;
            }
            elseif ($modul == "BPM") 
            {
                // Get Total Qty yang Diminta untuk SELURUH item dalam 1 request_no
                $this->db->select_sum('qty', 'total_request');
                $this->db->select_sum('status', 'total_status'); // Untuk cek status closed per baris
                $this->db->where('request_no', $document_no);
                $this->db->where('deleted', 0);
                $bpm_summary = $this->db->get('bpm')->row();

                if (!$bpm_summary || $bpm_summary->total_request == 0) {
                    throw new Exception("BPM Data not found or empty for Request No: $document_no");
                }

                // Get Total Qty yang SUDAH di-scan
                $this->db->select_sum('qty', 'total_scan');
                $this->db->where('request_no', $document_no);
                $scan_summary = $this->db->get('scan_item_bpm')->row();
                $total_scan_qty = $scan_summary->total_scan ?? 0;

                // Get Jumlah Baris Item di BPM
                $total_items = $this->db->where(['request_no' => $document_no, 'deleted' => 0])
                                ->from('bpm')
                                ->count_all_results();

                // Validate Eligibility
                $check = new stdClass();
                
                // Dokumen dianggap closed jika semua baris item statusnya '1'
                $check->is_all_closed = ($bpm_summary->total_status >= $total_items); 
                $check->is_fully_scanned = (round($total_scan_qty, 2) >= round($bpm_summary->total_request, 2));

                if ($check->is_all_closed || $check->is_fully_scanned) {
                    $result = true;
                } else {
                    $diff = round($bpm_summary->total_request - $total_scan_qty, 2);
                    throw new Exception("Document not ready. Still need $diff qty to be scanned.");
                }
            }

            // Response
            if ($result) {
                echo json_encode(['status' => true, 'message' => 'Ready to post']);
            } else {
                echo json_encode(['status' => false, 'message' => "Document $document_no does not meet the criteria (Scan not complete or status open)."]);
            }

        } catch (Exception $e) {
            log_message('error', "checkStatus Error: " . $e->getMessage());
            echo json_encode(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    // Get temporary data
    public function datatablesTemp()
    {
        $post = $this->input->post();

        $modul        = base64_decode($post['modul'] ?? '');
        $journal_date = base64_decode($post['journal_date'] ?? '');
        $company_id   = base64_decode($post['company_id'] ?? '');
        $document_no   = base64_decode($post['document_no'] ?? '');
        
        // Validasi: Jika document_no kosong, hentikan proses agar tidak error SQL
        $document_no_multiple = array_filter(array_map('trim', explode(',', $document_no)));
        if (empty($document_no_multiple)) {
            echo json_encode(["total" => 0, "rows" => [], "footer" => []]);
            return;
        }

        // Set Period 1 month
        $transaction_from = !empty($journal_date) ? date("Y-m-01", strtotime($journal_date)) : date("Y-m-01");
        $transaction_to   = !empty($journal_date) ? date("Y-m-t", strtotime($journal_date)) : date("Y-m-t");

        // Get Exchange Rates
        $all_rates = $this->db->get('standard_exchange_rates')->result();
        $get_rate = function($date, $currency) use ($all_rates) {
            if (empty($date) || $currency == "IDR") return 1.0;
            foreach ($all_rates as $r) {
                if ($currency == $r->currency_from && $date >= $r->start_date && $date <= $r->end_date) {
                    return (float)$r->middle;
                }
            }
            return 1.0; // Default rate
        };

        // Get Journal Types
        $all_journal_types = $this->db->get('journal_types')->result();
        $find_journal_type = function($module, $acc_no) use ($all_journal_types) {
            if (empty($module) || empty($acc_no)) return null;
            
            foreach ($all_journal_types as $jt) {
                if (trim($jt->module) == trim($module) && trim($jt->account_number) == trim($acc_no)) {
                    return $jt->id;
                }
            }
            return null;
        };
        
        $result = [];

        if ($modul == "PURCHASE ORDER RECEIPT") {
            // Debit: Raw Material Injection
            $acc_debit = $this->db->get_where('account_coa', ['account_number' => '150.110.00'])->row();
            // Credit: Accrual Raw Materials
            $acc_credit = $this->db->get_where('account_coa', ['account_number' => '220.190.00'])->row();

            // Journal Type ID
            $debit_jt_id  = $find_journal_type($modul, $acc_debit->account_number);
            $credit_jt_id = $find_journal_type($modul, $acc_credit->account_number);

            // Get status Scan POR
            $subquery = "(SELECT receipt_id, SUM(`status`) as total_scan 
                FROM purchase_order_labels 
                GROUP BY receipt_id) lbl";

            // Get Data POR (Query Detail per Item)
            $this->db->select("
                a.receipt_no as document_no, 
                a.receipt_date as trans_date, 
                a.po_no as invoice_no,
                a.item_rm_id,
                c.name as item_name, 
                b.name as supplier_name, 
                b.currency, 
                a.supplier_id,
                a.qty_receipt2 as qty, 
                f.price, 
                f.discount,
            ");
            // Rumus Total per Item (Debit)
            $this->db->select("((a.qty_receipt2 * f.price) * (1 - (COALESCE(f.discount, 0) / 100))) as item_total_original", FALSE);
            $this->db->select('lbl.total_scan');

            $this->db->from('purchase_order_receipts a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('purchase_orders f', "a.po_no = f.po_no AND a.item_rm_id = f.item_rm_id", 'left');
            $this->db->join($subquery, "a.receipt_id = lbl.receipt_id", "inner");

            // Filter & Order
            $this->db->where_in('a.receipt_no', $document_no_multiple);
            $this->db->where('lbl.total_scan >', 0);        // POR sudah di-scan = closed
            $this->db->where('a.print', 1);                 // POR GRN = closed
            $this->db->order_by('a.receipt_no', 'asc'); 

            $records = $this->db->get()->result_array();

            $data = [];
            $summary_credit = [];
            $grand_total_debit = 0;
            $grand_total_credit = 0;

            foreach ($records as $row) {
                $current_rate = $get_rate($row['trans_date'], $row['currency']);
                $amount_original = $row['item_total_original'] ?? 0;
                $amount_local = round($amount_original * $current_rate, 2);

                // --- DEBIT ---
                $data[] = [
                    "trans_date"      => $row['trans_date'],
                    "document_no"     => $row['document_no'],
                    "invoice_no"      => $row['invoice_no'],
                    "company_name"    => $row['supplier_name'],
                    "journal_type_id" => $debit_jt_id,
                    "account_number"  => $acc_debit->account_number,
                    "account_name"    => $acc_debit->account_name,
                    "description"     => $row['item_name'] . " | " . $row['document_no'] . " | " . $row['supplier_name'],
                    "currency"        => $row['currency'],
                    "rates"           => $current_rate,
                    "original_debit"  => $amount_original,
                    "original_credit" => 0,
                    "local_debit"     => $amount_local,
                    "local_credit"    => 0
                ];

                // --- CREDIT ---
                if (!isset($summary_credit[$row['document_no']])) {
                    $summary_credit[$row['document_no']] = [
                        'total_orig'   => 0,
                        'total_local'  => 0,
                        'row_data'     => $row,
                        'rate'         => $current_rate
                    ];
                }
                $summary_credit[$row['document_no']]['total_orig']  += $amount_original;
                $summary_credit[$row['document_no']]['total_local'] += $amount_local;
                
                $grand_total_debit += $amount_local;
            }

            // CREDIT PER DOCUMENT NO.
            foreach ($summary_credit as $doc_no => $val) {
                $data[] = [
                    "trans_date"      => $val['row_data']['trans_date'],
                    "document_no"     => $doc_no,
                    "invoice_no"      => $val['row_data']['invoice_no'],
                    "company_name"    => $val['row_data']['supplier_name'],
                    "journal_type_id" => $credit_jt_id,
                    "account_number"  => $acc_credit->account_number,
                    "account_name"    => $acc_credit->account_name,
                    "description"     => $doc_no . " | " . $val['row_data']['supplier_name'],
                    "currency"        => $val['row_data']['currency'],
                    "rates"           => $val['rate'],
                    "original_debit"  => 0,
                    "original_credit" => $val['total_orig'],
                    "local_debit"     => 0,
                    "local_credit"    => $val['total_local']
                ];
                $grand_total_credit += $val['total_local'];
            }

            $footer[] = [
                "local_debit"  => $grand_total_debit,
                "local_credit" => $grand_total_credit
            ];

            $result = ["total" => count($data), "rows" => $data, "footer" => $footer];
        }
        elseif ($modul == "BPM") 
        {
            // Debit: Raw Material Injection
            $acc_debit  = $this->db->get_where('account_coa', ['account_number' => '150.110.00'])->row();
            // Credit: Accrual Raw Materials
            $acc_credit = $this->db->get_where('account_coa', ['account_number' => '150.210.00'])->row();

            if (!$acc_debit || !$acc_credit) {
                throw new Exception("Account COA not found (150.110.00 or 150.210.00)");
            }

            // Journal Type ID
            $debit_jt_id  = $this->autopostingjournal->journal_type($modul, $acc_debit->account_number);
            $credit_jt_id = $this->autopostingjournal->journal_type($modul, $acc_credit->account_number);

            if (!$debit_jt_id || !$credit_jt_id) {
                throw new Exception("Journal Type Account NOT FOUND for module $modul! Please add Journal Types");
            }

            // Get BPM
            $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom, COALESCE(SUM(c.qty),0) as qty_actual');
            $this->db->select("'' as supplier_id, '' as supplier_name");
            $this->db->select("'IDR' as currency");

            $this->db->from('bpm a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('scan_item_bpm c', 'a.request_id = c.request_id and a.item_rm_id = c.item_rm_id','left');

            $this->db->where('a.status', 1); // BPM status=closed
            $this->db->where('a.deleted', 0);
            $this->db->where('a.request_no', $document_no);
            $this->db->group_by('a.id');
            $this->db->order_by('a.request_id', 'ASC');

            $records = $this->db->get()->result_array();

            if (empty($records)) {
                throw new Exception("No records found for Request No: $document_no or document not closed (Scan/Print)");
            }

            $currency        = "IDR"; // default
            $data_combine    = [];
            $total_orig_all  = 0;
            $total_local_all = 0;

            foreach ($records as $row) 
            {
                $rate  = $this->autopostingjournal->get_rate($row['request_date'], $currency);
                $price = $this->autopostingjournal->get_price_rm($row['request_date'], $row['item_rm_id']);

                $amount_original = (float)$row['qty'] * $price;
                $amount_local = round($amount_original * $rate, 2);

                $data_combine[] = [
                    'journal_date'    => $row['request_date'],
                    "journal_type_id" => $debit_jt_id,
                    'trans_date'      => date('Y-m-d'),
                    'document_no'     => $row['request_no'],
                    'invoice_no'      => $row['request_id'],
                    'account_number'  => $acc_debit->account_number,
                    'account_name'    => $acc_debit->account_name,
                    'description'     => $row['item_name'] . " | " . $row['request_no'] . " | " . $row['supplier_name'],
                    'original_debit'  => $amount_original,
                    'original_credit' => 0,
                    'local_debit'     => $amount_local,
                    'local_credit'    => 0,
                    'rates'           => $rate,
                    'currency'        => $currency,
                    'company_name'    => $row['supplier_name'],
                    'company_id'      => $row['supplier_id'],
                    'modul'           => $modul,
                    'remarks'         => "Auto Posting Journal",
                    'created_date'    => date('Y-m-d H:i:s'),
                    'created_by'      => $this->session->username ? $this->session->username : 'SYSTEM',
                ];

                $total_orig_all += $amount_original;
                $total_local_all += $amount_local;
            }

            // Data CREDIT
            $data_combine[] = [
                'journal_date'    => $records[0]['request_date'],
                "journal_type_id" => $credit_jt_id,
                'trans_date'      => date('Y-m-d'),
                'document_no'     => $records[0]['request_no'],
                'invoice_no'      => $records[0]['request_id'],
                'account_number'  => $acc_credit->account_number,
                'account_name'    => $acc_credit->account_name,
                'description'     => $records[0]['request_no'] . " | " . $records[0]['supplier_name'],
                'original_debit'  => 0,
                'original_credit' => $total_orig_all,
                'local_debit'     => 0,
                'local_credit'    => $total_local_all,
                'rates'           => $this->autopostingjournal->get_rate($records[0]['request_date'], $currency),
                'currency'        => $currency,
                'company_name'    => $records[0]['supplier_name'],
                'company_id'      => $records[0]['supplier_id'],
                'modul'           => $modul,
                'remarks'         => "Auto Posting Journal",
                'created_date'    => date('Y-m-d H:i:s'),
                'created_by'      => $this->session->username ? $this->session->username : 'SYSTEM',
            ];

            $footer[] = [
                "currency"        => "TOTAL",
                "original_debit"  => $total_orig_all,
                "original_credit" => $total_orig_all,
                "rate"            => null,
                "local_debit"     => $total_local_all,
                "local_credit"    => $total_local_all,
            ];

            $result = ["total" => count($data_combine), "rows" => $data_combine, "footer" => $footer];    
        }
        elseif ($modul == "ADJ IN STO" || $modul == "ADJ OUT STO") 
        {
            // Debit: Raw Material Injection
            $acc_debit  = $this->db->get_where('account_coa', ['account_number' => '150.110.00'])->row();
            // Credit: Accrual Raw Materials
            $acc_credit = $this->db->get_where('account_coa', ['account_number' => '510.220.00'])->row();

            if (!$acc_debit || !$acc_credit) {
                throw new Exception("Account COA not found (150.110.00 or 510.220.00)");
            }

            // Validasi Type / Kind
            $module = "TRANSACTION RM";
            if ($modul == "ADJ IN STO") {
                $kind = "IN";
            } elseif ($modul == "ADJ OUT STO") {
                $kind = "OUT";
            }

            // Journal Type ID
            $debit_jt_id  = $this->autopostingjournal->journal_type_kind($module, $acc_debit->account_number, $kind);
            $credit_jt_id = $this->autopostingjournal->journal_type_kind($module, $acc_credit->account_number, $kind);

            if (!$debit_jt_id || !$credit_jt_id) {
                throw new Exception("Journal Type Account NOT FOUND for module $modul! Please add Journal Types");
            }

            // Get Transaction
            $this->db->select('a.*, c.number as item_number, c.name as item_name, c.uom');
            $this->db->select('c.number as item_no, a.remarks as invoice_no');
            $this->db->select("'' as supplier_name, '' as supplier_id");
            $this->db->from('transaction_rm a');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.request_no', $document_no);
            $this->db->group_by('a.id');
            $this->db->order_by('a.request_no', 'ASC');

            $records = $this->db->get()->result_array();

            if (empty($records)) {
                throw new Exception("No records found for Request No: $document_no or document not closed (Scan/Print)");
            }

            $currency        = "IDR"; // default
            $data_combine    = [];
            $total_orig_all  = 0;
            $total_local_all = 0;

            foreach ($records as $row) 
            {
                $rate  = $this->autopostingjournal->get_rate($row['request_date'], $currency);
                $price = $this->autopostingjournal->get_price_rm($row['request_date'], $row['item_rm_id']);

                $amount_original = (float)$row['qty'] * $price;
                $amount_local = round($amount_original * $rate, 2);

                $supplier_name = !empty($row['supplier_name']) ? $row['supplier_name'] . " | " : "";
                $description   = $supplier_name . $document_no . " | " . $row['invoice_no'] . " | " . $row['item_no'] . " | " . $row['item_name'];

                $data_combine[] = [
                    'journal_date'    => $row['request_date'],
                    "journal_type_id" => $debit_jt_id,
                    'trans_date'      => date('Y-m-d'),
                    'document_no'     => $row['request_no'],
                    'invoice_no'      => $row['request_id'] ?? '',
                    'account_number'  => $acc_debit->account_number,
                    'account_name'    => $acc_debit->account_name,
                    'original_debit'  => $amount_original,
                    'original_credit' => 0,
                    'local_debit'     => $amount_local,
                    'local_credit'    => 0,
                    'rates'           => $rate,
                    'description'     => $description,
                    'currency'        => $currency,
                    'company_name'    => $row['supplier_name'],
                    'company_id'      => $row['supplier_id'],
                    'modul'           => $modul,
                    'remarks'         => "Auto Posting Journal",
                    'created_date'    => date('Y-m-d H:i:s'),
                    'created_by'      => $this->session->username ? $this->session->username : 'SYSTEM',
                ];

                $total_orig_all += $amount_original;
                $total_local_all += $amount_local;
            }


            $supplier_name = !empty($records[0]['supplier_name']) ? $records[0]['supplier_name'] . " | " : "";
            $description   = $supplier_name . $document_no . " | " . $records[0]['invoice_no'] . " | " . $records[0]['item_no'] . " | " . $records[0]['item_name'];

            // INSERT CREDIT
            $data_combine[] = [
                'journal_date'    => $records[0]['request_date'],
                "journal_type_id" => $credit_jt_id,
                'trans_date'      => date('Y-m-d'),
                'document_no'     => $records[0]['request_no'],
                'invoice_no'      => $records[0]['request_id'] ?? '-',
                'account_number'  => $acc_credit->account_number,
                'account_name'    => $acc_credit->account_name,
                'original_debit'  => 0,
                'original_credit' => $total_orig_all,
                'local_debit'     => 0,
                'local_credit'    => $total_local_all,
                'rates'           => $this->autopostingjournal->get_rate($records[0]['request_date'], $currency),
                'description'     => $description,
                'currency'        => $currency,
                'company_name'    => $records[0]['supplier_name'],
                'company_id'      => $records[0]['supplier_id'],
                'modul'           => $modul,
                'remarks'         => "Auto Posting Journal",
                'created_date'    => date('Y-m-d H:i:s'),
                'created_by'      => $this->session->username ? $this->session->username : 'SYSTEM',
            ];

            $footer[] = [
                "currency"        => "TOTAL",
                "original_debit"  => $total_orig_all,
                "original_credit" => $total_orig_all,
                "rate"            => null,
                "local_debit"     => $total_local_all,
                "local_credit"    => $total_local_all,
            ];

            $result = ["total" => count($data_combine), "rows" => $data_combine, "footer" => $footer];  
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from            = base64_decode($this->input->get('filter_from')) ?? null;
            $filter_to              = base64_decode($this->input->get('filter_to')) ?? null;
            $filter_journal_type    = base64_decode($this->input->get('filter_journal_type')) ?? null;
            $filter_modul           = base64_decode($this->input->get('filter_modul')) ?? null;
            $filter_voucher         = base64_decode($this->input->get('filter_voucher')) ?? null;
            $filter_item_category   = base64_decode($this->input->get('filter_item_category')) ?? null;

            //Pagination 1-10
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $this->db->select('
                por.receipt_no, 
                item.id, 
                item.name, 
                item.division, 
                item.item_category_id,
                jenis.number AS category_code');
            $this->db->from('purchase_order_receipts por');
            $this->db->join('item_rm item', 'por.item_rm_id = item.id');
            $this->db->join("item_categories jenis", "jenis.id = item.item_category_id", "left");
            $this->db->where('por.status', 0);
            $this->db->group_by('por.receipt_no');
            $sub_por = $this->db->get_compiled_select();

            //Select Query
            $this->db->select('a.journal_date, a.number, a.journal_type_id, b.name as journal_type_name, 
                a.document_no, a.modul, a.remarks, a.currency, a.rates, a.posting,
                a.created_by, a.created_date, a.updated_by, a.updated_date, 
                a.approved, a.approved_to, a.approved_by, a.approved_date,
                c.division, c.item_category_id, c.category_code,
                SUM(a.original_debit) as original_debit, 
                SUM(a.original_credit) as original_credit, 
                SUM(a.local_debit) as local_debit, 
                SUM(a.local_credit) as local_credit');
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
            $this->db->order_by('a.journal_date', 'asc');

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
        try {
            $post = $this->input->post();

            $modul        = $post['modul'] ?? '';
            $journal_date = $post['journal_date'] ?? '';
            $document_no  = $post['document_no'] ?? '';

            if (empty($modul) || empty($document_no)) {
                throw new Exception("Module or Document No. parameters are incomplete.");
            }

            // Set Period
            $ref_date = !empty($journal_date) ? strtotime($journal_date) : time();
            $transaction_from = date("Y-m-01", $ref_date);
            $transaction_to   = date("Y-m-t", $ref_date);

            // Query Check Exist
            $this->db->select('*');
            $this->db->from('journal_inventory');
            $this->db->where('modul', $modul);
            $this->db->where('document_no', $document_no);
            $this->db->where('journal_date >=', $transaction_from);
            $this->db->where('journal_date <=', $transaction_to);
            
            $exists = $this->db->count_all_results();

            // Jika exists == 0, berarti BOLEH posting (return true)
            if ($exists === 0) {
                echo json_encode(['status' => true, 'message' => 'Ready to post']);
            } else {
                echo json_encode(['status' => false, 'message' => "Document $document_no has already been journaled in this period."]);
            }

        } catch (Exception $e) {
            log_message('error', "datatablesCheck Error: " . $e->getMessage());
            echo json_encode(['status' => false, 'message' => 'Internal Server Error: ' . $e->getMessage()]);
        }
    }

    public function datatableDetails()
    {
        $number = base64_decode($this->input->get('number'));
        $filters = json_decode($this->input->post('filterRules'));

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
        if (@count($filters) > 0) {
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
        $number = base64_decode($this->input->get('number'));

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
                echo json_encode(['title' => 'Success', 'message' => 'Journal saved successfully', 'theme' => 'success']);
            }

        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['title' => 'Failed', 'message' => $e->getMessage(), 'theme' => 'error']);
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

        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_journal_type = base64_decode($this->input->get('filter_journal_type'));
        $filter_modul = base64_decode($this->input->get('filter_modul'));
        $filter_voucher = base64_decode($this->input->get('filter_voucher'));

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
            return number_format($value, $decimal, ".", "");
        } else {
            return number_format($value, $decimal, ",", ".");
        }
    }
}
