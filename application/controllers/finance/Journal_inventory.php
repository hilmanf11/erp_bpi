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

    public function readModul()
    {
        $post = $this->input->post();

        $modul        = base64_decode($post['modul'] ?? '');
        $journal_date = base64_decode($post['journal_date'] ?? '');
        $journal_type = base64_decode($post['journal_type'] ?? '');
        $company_id   = base64_decode($post['company_id'] ?? '');

        // Set Period 1 month
        $transaction_from = !empty($journal_date) ? date("Y-m-01", strtotime($journal_date)) : date("Y-m-01");
        $transaction_to   = !empty($journal_date) ? date("Y-m-t", strtotime($journal_date)) : date("Y-m-t");

        $records = [];

        if ($modul == "PURCHASE ORDER RECEIPT") 
        {
            $this->db->select('a.receipt_no as document_no, a.supplier_id');
            $this->db->from('purchase_order_receipts a');
            $this->db->join('journal_inventory b', 'a.receipt_no = b.document_no', 'left');
            
            $this->db->where('a.supplier_id', $company_id);
            $this->db->where('a.receipt_date >=', $transaction_from);
            $this->db->where('a.receipt_date <=', $transaction_to);
            
            // Filter agar hanya dokumen yang belum dijurnal yang muncul
            $this->db->where('b.document_no', NULL);

            $this->db->group_by('a.receipt_no');
            $this->db->order_by('a.receipt_no', 'asc');
            
            $records = $this->db->get()->result_array();
        }

        header('Content-Type: application/json');
        echo json_encode($records);
    }


    public function datatablesTemp()
    {
        $post = $this->input->post();

        $modul        = base64_decode($post['modul'] ?? '');
        $journal_date = base64_decode($post['journal_date'] ?? '');
        $journal_type = base64_decode($post['journal_type'] ?? '');
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
        
        $result = [];

        if ($modul == "PURCHASE ORDER RECEIPT") {
            // Debit: Raw Material Injection
            $acc_debit = $this->db->get_where('account_coa', ['account_number' => '150.110.00'])->row();
            // Credit: Accrual Raw Materials
            $acc_credit = $this->db->get_where('account_coa', ['account_number' => '220.190.00'])->row();

            // Get Query Utama
            $this->db->select("
                a.receipt_no as document_no, a.receipt_date as trans_date, a.po_no as invoice_no,
                c.name as item_name, COUNT(a.item_rm_id) as total_item,
                b.name as supplier_name, b.currency, 
                a.qty_receipt2 as qty, f.price, f.discount
            ");
            // Rumus Total: SUM((Qty * Price) * (1 - Disc/100))
            $this->db->select("SUM((a.qty_receipt2 * f.price) * (1 - (COALESCE(f.discount, 0) / 100))) as total_original", FALSE);

            $this->db->from('purchase_order_receipts a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('purchase_orders f', "a.po_no = f.po_no AND a.item_rm_id = f.item_rm_id", 'left');
            $this->db->where('a.supplier_id', $company_id);
            $this->db->where('a.receipt_date >=', $transaction_from);
            $this->db->where('a.receipt_date <=', $transaction_to);
            $this->db->where_in('a.receipt_no', $document_no_multiple);
            
            // GROUP BY agar 1 POR jadi 1 baris data di PHP
            $this->db->group_by('a.receipt_no');
            $this->db->order_by('a.receipt_no', 'asc'); 

            $records = $this->db->get()->result_array();

            $data = [];
            $grand_total_debit  = 0;
            $grand_total_credit = 0;

            foreach ($records as $row) {
                $current_rate = $get_rate($row['trans_date'], $row['currency']);
                
                $amount_original = $row['total_original'] ?? 0;
                $amount_local = round($amount_original * $current_rate, 2);

                // Description
                if ((int)$row['total_item'] > 1) {
                    $description = $row['document_no'] . " | " . $row['supplier_name'];
                } else {
                    $description = $row['item_name'] . " | " . $row['document_no'] . " | " . $row['supplier_name'];
                }

                // --- DEBIT ---
                $data[] = [
                    "trans_date"      => $row['trans_date'],
                    "document_no"     => $row['document_no'],
                    "invoice_no"      => $row['invoice_no'],
                    "company_name"    => $row['supplier_name'],
                    "account_number"  => $acc_debit->account_number,
                    "account_name"    => $acc_debit->account_name,
                    "description"     => $description,
                    "currency"        => $row['currency'],
                    "rates"           => $current_rate,
                    "original_debit"  => $amount_original,
                    "original_credit" => 0,
                    "local_debit"     => $amount_local,
                    "local_credit"    => 0
                ];

                // --- CREDIT ---
                $data[] = [
                    "trans_date"      => $row['trans_date'],
                    "document_no"     => $row['document_no'],
                    "invoice_no"      => $row['invoice_no'],
                    "company_name"    => $row['supplier_name'],
                    "account_number"  => $acc_credit->account_number,
                    "account_name"    => $acc_credit->account_name,
                    "description"     => $description,
                    "currency"        => $row['currency'],
                    "rates"           => $current_rate,
                    "original_debit"  => 0,
                    "original_credit" => $amount_original,
                    "local_debit"     => 0,
                    "local_credit"    => $amount_local
                ];

                $grand_total_debit += $amount_local;
                $grand_total_credit += $amount_local;
            }

            $footer[] = [
                "local_debit"  => $grand_total_debit,
                "local_credit" => $grand_total_credit
            ];

            $result = (["total" => count($data), "rows" => $data, "footer" => $footer]);
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_journal_type = base64_decode($this->input->get('filter_journal_type'));
            $filter_modul = base64_decode($this->input->get('filter_modul'));
            $filter_voucher = base64_decode($this->input->get('filter_voucher'));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.journal_date, a.number, a.journal_type_id, b.name as journal_type_name, 
                a.document_no, a.modul, a.remarks, a.currency, a.rates, a.posting,
                a.created_by, a.created_date, a.updated_by, a.updated_date, 
                a.approved, a.approved_to, a.approved_by, a.approved_date,
                SUM(a.original_debit) as original_debit, 
                SUM(a.original_credit) as original_credit, 
                SUM(a.local_debit) as local_debit, 
                SUM(a.local_credit) as local_credit');
            $this->db->from('journal_inventory a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id', 'left');
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
            $this->db->group_by('a.number');
            $this->db->order_by('a.journal_date', 'asc');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
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

        $journal_date = $post['journal_date'];
        $transaction_to = date("Y-m-t", strtotime($journal_date));
        $modul = $post['modul'];
        $company_id = $post['company_id'];

        if(!empty($post['document_no'])){
            $document_no = explode(",", $post['document_no']);
        }else{
            $document_no = array();
        }

        $this->db->select('*');
        $this->db->from('journal_inventory');
        $this->db->where('journal_date', $transaction_to);
        $this->db->where('modul', $modul);
        if(count($document_no) > 0){
            $this->db->where_in('document_no', $document_no);
        }
        $totalRows = $this->db->count_all_results('', false);

        if($totalRows > 0){
            echo 1;
        }else{
            echo 0;
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

    public function create()
    {
        if ($this->input->post()) {

            $post = $this->input->post();
            $period = date("Ym", strtotime($post['trans_date']));

            if(empty($post['id'])){
                $send = $this->crud->create('journal_inventory', $post);
            }else{
                $send = $this->crud->update('journal_inventory', ["id" => $post['id']], $post);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('journal_inventory', $data);
        echo $send;
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
