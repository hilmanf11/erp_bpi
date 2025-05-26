<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Fixeds extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();

            $row = $this->crud->read("asset_fixeds", [], [], "1", "trans_date", "asc");

            $row = $this->crud->read("asset_fixeds", [], [], "1", "trans_date", "asc");

            if ($row) { 
                // Jika data ditemukan, gunakan trans_date
                $data['filter_from'] = $row->trans_date;
            } else { 
                // Jika data tidak ditemukan, beri nilai default
                $data['filter_from'] = null; // Atau nilai default seperti 'N/A', tanggal tertentu, dsb.
            }

            $this->load->view('template/header', $data);
            $this->load->view('finance/fixeds');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function readPi()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT a.number, b.name
            FROM purchase_invoices a 
            JOIN item_familys b ON a.family_id = b.id
            JOIN account_statements d ON a.account_number = d.account_number
            -- JOIN asset_fixeds c ON a.item_no != c.number AND a.number != c.purchase_invoice_number
            WHERE d.name = 'Fixed assets-cost' AND a.number LIKE '%$post%'
            ORDER BY a.number asc");

        $finals = array();
        $item = array();
        foreach ($send as $row) {
            $number = $row->number;
            $invoices = $this->crud->query("SELECT a.*
                FROM purchase_invoices a 
                JOIN account_statements d ON a.account_number = d.account_number
                WHERE d.name = 'Fixed assets-cost' AND a.number = '$number' 
                ORDER BY a.item_no asc");

            foreach ($invoices as $invoice) {
                $item_no = $invoice->item_no;
                $fixed = $this->crud->query("SELECT distinct `number` FROM asset_fixeds WHERE `number` like '%$item_no%' and purchase_invoice_number = '$number'");

                if(empty($fixed)){
                    $item[] = array("item_no" => $item_no);
                }
            }

            if(!empty($item)){
                array_push($finals, $row);
            }
        }  

        echo json_encode($finals);
    }

    //GET FIXED
    public function readProductPi($number)
    {
        $number = base64_decode($number);
        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $send = $this->crud->query("SELECT a.*, c.name as supplier_name
            FROM purchase_invoices a 
            JOIN item_familys b ON a.family_id = b.id 
            JOIN suppliers c ON a.supplier_id = c.id
            JOIN account_statements d ON a.account_number = d.account_number
            WHERE b.number != '002'
            AND d.name = 'Fixed assets-cost'
            AND a.number = '$number'
            AND a.item_no LIKE '%$post%' GROUP BY a.item_no ORDER BY a.item_no ASC");

        $finals = array();
        foreach ($send as $row) {
            $item_no = $row->item_no;
            $send = $this->crud->query("SELECT distinct `number` FROM asset_fixeds WHERE `number` like '%$item_no%' and purchase_invoice_number = '$number'");
            if(empty($send)){
                array_push($finals, $row);
            }
        }        

        echo json_encode($finals);
    }

    public function readExchangeRates()
    {
        $trans_date = date("Y-m-01", strtotime("-1 month", strtotime($this->input->post('trans_date'))));
        $currency = $this->input->post('currency');

        $records = $this->crud->query("SELECT middle
            FROM exchange_rates
            WHERE start_date = '$trans_date'
            AND currency_from = '$currency'
            AND currency_to = 'IDR'");
        echo json_encode($records);
    }

    public function readExpired($month, $trans_date){
        $trans_date = base64_decode($trans_date);
        echo date("Y-m-d", strtotime("+" . $month . ' months', strtotime($trans_date)));
    }

    //GET FILTER DATA
    public function readNumber($asset_category_number)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $asset_category_number = base64_decode($asset_category_number);
        $send = $this->crud->query("SELECT DISTINCT `number`, `name` FROM asset_fixeds WHERE asset_category_number = '$asset_category_number' and (`number` like '%$post%' or name like '%$post%')");
        echo json_encode($send);
    }

    public function readPurchaseInvoiceNumber()
    {
        $send = $this->crud->query("SELECT DISTINCT purchase_invoice_number FROM asset_fixeds");
        echo json_encode($send);
    }

    public function readSupplier()
    {
        $send = $this->crud->query("SELECT DISTINCT supplier_name FROM asset_fixeds");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');

            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_number = base64_decode($this->input->get('filter_number'));
            $filter_category = base64_decode($this->input->get('filter_category'));
            $filter_estimate = base64_decode($this->input->get('filter_estimate'));
            $filter_purchase_invoice_number = base64_decode($this->input->get('filter_purchase_invoice_number'));
            $filter_supplier = base64_decode($this->input->get('filter_supplier'));

            $filter_period_from = date("Y-m", strtotime($filter_from));
            $filter_period_to = date("Y-m", strtotime($filter_to));

            //Select Query
            $this->db->select("a.*, 
                b.name as asset_category_name, 
                b.type as asset_category_type, 
                PERIOD_DIFF(DATE_FORMAT('$filter_to', '%Y%m'), DATE_FORMAT(trans_date, '%Y%m')) AS qty_month, 
                (COALESCE(c.depreciation_acc, 0) + a.depreciation_accumulate) as depreciation_acc,
                (a.cost - (COALESCE(c.depreciation_acc, 0) + a.depreciation_accumulate)) as book_value,
                (CASE WHEN (a.cost - (COALESCE(c.depreciation_acc, 0) + a.depreciation_accumulate)) > 0 THEN 0 ELSE 1 END) as status_expired");
            $this->db->from('asset_fixeds a');
            $this->db->join('asset_categories b', 'a.asset_category_number = b.number');
            $this->db->join("(SELECT asset_no, SUM(debit) as depreciation_acc FROM asset_journals WHERE periode BETWEEN '$filter_period_from' and '$filter_period_to' GROUP BY asset_no) c", 'a.number = c.asset_no', 'left');
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
            if($filter_category != ""){
                $this->db->where('a.asset_category_number', $filter_category);
            }
            $this->db->like('a.number', $filter_number);
            $this->db->like('a.estimate_year', $filter_estimate);
            $this->db->like('a.purchase_invoice_number', $filter_purchase_invoice_number);
            $this->db->like('a.supplier_name', $filter_supplier);
            $this->db->group_by('a.number');

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

    public function numberId($number)
    {
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM asset_fixeds WHERE `number` like '%$number%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;

        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }

        echo $datenow . "-" . $autoID;
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = "[]";
            for ($i = 0; $i < $post['qty']; $i++) {

                if ($post['qty'] > 1) {
                    $asset_no = $post['number'];
                    $sqlGetID = $this->db->query("SELECT max(`number`) as kode FROM asset_fixeds WHERE `number` like '%$asset_no%'");
                    $rowID = $sqlGetID->row();
                    $kode = $rowID->kode;

                    if ($kode == NULL) {
                        $number = $post['number'] . sprintf("%03d", ($i + 1));
                    } else {
                        $urutan = (int) substr($kode, -3);
                        $urutan++;
                        $number = $post['number'] . sprintf("%03s", $urutan);
                    }
                } else {
                    $number = $post['number'];
                }

                $data = array(
                    "asset_category_number" => $post['asset_category_number'],
                    "purchase_invoice_number" => $post['purchase_invoice_number'],
                    "supplier_name" => $post['supplier_name'],
                    "number" => $number,
                    "name" => $post['name'],
                    "trans_date" => $post['trans_date'],
                    "qty" => 1,
                    "currency" => $post['currency'],
                    "cost" => $post['cost'],
                    "estimate_month" => $post['estimate_month'],
                    "expired_date" => $post['expired_date'],
                    "estimate_year" => $post['estimate_year'],
                    "depreciation" => $post['depreciation'],
                    "remarks" => $post['remarks'],
                    "method" => $post['method'],
                    "departement" => $post['departement'],
                    "location" => $post['location'],
                    "total" => $post['total'],
                );

                $send   = $this->crud->create('asset_fixeds', $data);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('asset_fixeds', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('asset_fixeds', $data);
        echo $send;
    }

    public function upload()
    {
        error_reporting(0);
        require_once 'finance/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);

        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'purchase_invoice_number' => $data->val($i, 2),
                'number' => $data->val($i, 3),
                'name' => $data->val($i, 4),
                'asset_category_number' => $data->val($i, 5),
                'trans_date' => $data->val($i, 6),
                'supplier_name' => $data->val($i, 7),
                'qty' => $data->val($i, 8),
                'currency' => $data->val($i, 9),
                'cost' => $data->val($i, 10),
                'estimate_year' => $data->val($i, 11),
                'depreciation_accumulate' => $data->val($i, 12),
                'remarks' => $data->val($i, 13),
                'method' => $data->val($i, 14),
                'departement' => $data->val($i, 15),
                'location' => $data->val($i, 16)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/asset_fixeds.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/asset_fixeds.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/asset_fixeds.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data       = $this->input->post('data');

            //Cek Process Number
            $asset_categories = $this->crud->read('asset_categories', [], ["number" => $data['asset_category_number']]);
            $asset_fixeds = $this->crud->read('asset_fixeds', [], ["number" => $data['number']]);

            if (empty($asset_categories->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Asset Category No " . $data['asset_category_number'] . " Not Found", "theme" => "error"));
            } elseif (!empty($asset_fixeds->id)) {
                echo json_encode(array("title" => "Duplicate", "message" => "Asset  No " . $data['number'] . " Duplicated", "theme" => "error"));
            } else {
                
                if(($data['estimate_year'] * 12) > 0){
                    $estimate_month = ($data['estimate_year'] * 12);
                }else{
                    $estimate_month = 1;
                }

                $dataFinal = array(
                    "asset_category_number" => $data['asset_category_number'],
                    "purchase_invoice_number" => $data['purchase_invoice_number'],
                    "supplier_name" => $data['supplier_name'],
                    "number" => $data['number'],
                    "name" => $data['name'],
                    "trans_date" => $data['trans_date'],
                    "qty" => $data['qty'],
                    "currency" => $data['currency'],
                    "cost" => $data['cost'],
                    "expired_date" => date("Y-m-d", strtotime("+" . ($data['estimate_year'] * 12) . ' months', strtotime($data['trans_date']))),
                    "estimate_year" => $data['estimate_year'],
                    "estimate_month" => ($data['estimate_year'] * 12),
                    "depreciation" => ($data['cost'] / $estimate_month),
                    "depreciation_accumulate" => $data['depreciation_accumulate'],
                    "remarks" => $data['remarks'],
                    "method" => $data['method'],
                    "departement" => $data['departement'],
                    "location" => $data['location'],
                    "total" => ($data['qty'] * $data['cost']),
                );

                $send   = $this->crud->create('asset_fixeds', $dataFinal);
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=asset_fixeds_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_number = base64_decode($this->input->get('filter_number'));
        $filter_category = base64_decode($this->input->get('filter_category'));
        $filter_estimate = base64_decode($this->input->get('filter_estimate'));
        $filter_purchase_invoice_number = base64_decode($this->input->get('filter_purchase_invoice_number'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));

        $filter_period_from = date("Y-m", strtotime($filter_from));
        $filter_period_to = date("Y-m", strtotime($filter_to));

        //Select Query
        $this->db->select("a.*, 
        b.name as asset_category_name, 
        b.type as asset_category_type, 
        PERIOD_DIFF(DATE_FORMAT('$filter_to', '%Y%m'), DATE_FORMAT(trans_date, '%Y%m')) AS qty_month, 
        (COALESCE(c.depreciation_acc, 0) + a.depreciation_accumulate) as depreciation_acc,
        (a.cost - (c.depreciation_acc + a.depreciation_accumulate)) as book_value,
        (CASE WHEN (a.cost - (c.depreciation_acc + a.depreciation_accumulate)) > 0 THEN 0 ELSE 1 END) as status_expired");
        $this->db->from('asset_fixeds a');
        $this->db->join('asset_categories b', 'a.asset_category_number = b.number');
        $this->db->join("(SELECT asset_no, SUM(debit) as depreciation_acc FROM asset_journals WHERE periode BETWEEN '$filter_period_from' and '$filter_period_to' GROUP BY asset_no) c", 'a.number = c.asset_no', 'left');
        $this->db->where('a.trans_date >=', $filter_from);
        $this->db->where('a.trans_date <=', $filter_to);
        if($filter_category != ""){
            $this->db->where('a.asset_category_number', $filter_category);
        }
        if($filter_number != ""){
            $this->db->where('a.number', $filter_number);
        }
        if($filter_estimate != ""){
            $this->db->where('a.estimate_year', $filter_estimate);
        }
        if($filter_purchase_invoice_number != ""){
            $this->db->where('a.purchase_invoice_number', $filter_purchase_invoice_number);
        }
        if($filter_supplier != ""){
            $this->db->where('a.supplier_name', $filter_supplier);
        }
        $this->db->group_by('a.number');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' .  $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>' . $config->description . '</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br>
                <br>
                <h3 style="margin:0;">ASSET FIXED</h3>
                <small>Period '.$filter_from.' to '.$filter_to.'</small>
            </center>
            <br>
            
            <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Asset No</th>
                <th>Asset Name</th>
                <th>Asset Category</th>
                <th>Asset Type</th>
                <th>Purchase Invoice</th>
                <th>Supplier</th>
                <th>Purchase Date</th>
                <th>Qty</th>
                <th>Cost</th>
                <th>EST. <br>Year</th>
                <th>EST. <br>Month</th>
                <th>Expired Date</th>
                <th>Depreciation</th>
                <th>Depreciation Accumulation</th>
                <th>Book Value</th>
                <th>Depreciation<br>Method</th>
                <th>Departement</th>
                <th>Location</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if($data['status_expired'] == 1){
                $status = "EXPIRED";
            }else{
                $status = "ACTIVE";
            }
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['name'] . '</td>
                        <td>' . $data['asset_category_name'] . '</td>
                        <td>' . $data['asset_category_type'] . '</td>
                        <td>' . $data['purchase_invoice_number'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['trans_date'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['cost'] . '</td>
                        <td>' . $data['estimate_year'] . '</td>
                        <td>' . $data['estimate_month'] . '</td>
                        <td>' . $data['expired_date'] . '</td>
                        <td>' . $data['depreciation'] . '</td>
                        <td>' . $data['depreciation_acc'] . '</td>
                        <td>' . $data['book_value'] . '</td>
                        <td>' . $data['method'] . '</td>
                        <td>' . $data['departement'] . '</td>
                        <td>' . $data['location'] . '</td>
                        <td>' . $status . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
