<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Balance_suppliers extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //VALIDASI FORM
        $this->form_validation->set_rules('supplier_id', 'Supplier', 'required|min_length[1]|max_length[30]|is_unique[account_balance_suppliers.supplier_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/balance_suppliers');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('account_balance_suppliers', ["name" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.id as supplier_ids, a.number as supplier_number, a.name as supplier_name, a.currency, b.*, c.account_name');
            $this->db->from('suppliers a');
            $this->db->join('account_balance_suppliers b', 'a.id = b.supplier_id', 'left');
            $this->db->join('account_coa c', 'b.account_number = c.account_number', 'left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "supplier_number"){
                        $this->db->like("a.number", $filter->value);
                    }elseif($filter->field == "supplier_name"){
                        $this->db->like("a.name", $filter->value);
                    }elseif($filter->field == "currency"){
                        $this->db->like("a.currency", $filter->value);
                    }else{
                        $this->db->like("b.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('a.name', 'asc');
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

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            
            if($post['debt_limit'] > 0){
                $status = "1";
            }else{
                $status = "0";
            }

            $account_balance_suppliers = $this->crud->reads("account_balance_suppliers", [], ["supplier_id" => $post['supplier_ids']]);
            if($account_balance_suppliers){
                $send = $this->crud->update('account_balance_suppliers', ["supplier_id" => $post['supplier_ids']], [
                    "supplier_id" => $post['supplier_ids'],
                    "account_number" => $post['account_number'],
                    "currency" => $post['currency'],
                    "currency_local" => $post['currency_local'],
                    "balance" => $post['balance'],
                    "balance_local" => $post['balance_local'],
                    "debt_limit" => $post['debt_limit'],
                    "start_date" => $post['start_date'],
                    "status" => $status,
                ]);
            }else{
                $send = $this->crud->create('account_balance_suppliers', [
                    "supplier_id" => $post['supplier_ids'],
                    "account_number" => $post['account_number'],
                    "currency" => $post['currency'],
                    "currency_local" => $post['currency_local'],
                    "balance" => $post['balance'],
                    "balance_local" => $post['balance_local'],
                    "debt_limit" => $post['debt_limit'],
                    "start_date" => $post['start_date'],
                    "status" => $status,
                ]);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        for ($i = 4; $i <= $total_row; $i++) {
            $datas[] = array(
                'supplier_number' => $data->val($i, 2),
                'account_number' => $data->val($i, 3),
                'currency' => $data->val($i, 4),
                'balance' => $data->val($i, 5),
                'currency_local' => $data->val($i, 6),
                'balance_local' => $data->val($i, 7),
                'debt_limit' => $data->val($i, 8),
                'start_date' => $data->val($i, 9)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    //UPLOAD CLEAR CACHE
    public function uploadclearFailed()
    {
        @unlink('excel/failed/account_balance_suppliers.txt');
    }

    //UPLOAD CREATE FAILED
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/account_balance_suppliers.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/account_balance_suppliers.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            //Cek Process Number
            $suppliers = $this->crud->read('suppliers', [], ["number" => $data['supplier_number']]);
            $account_balance_suppliers = $this->crud->reads("account_balance_suppliers", [], ["supplier_id" => @$suppliers->id]);
            $account_coa = $this->crud->reads("account_coa", [], ["account_number" => @$data['account_number']]);

            if (empty($suppliers->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Supplier Code " . $data['supplier_number'] . " Not Found", "theme" => "error"));
            } else {
                if($data['debt_limit'] > 0){
                    $status = "1";
                }else{
                    $status = "0";
                }
                
                if($account_coa){
                    if($account_balance_suppliers){
                        $send = $this->crud->update('account_balance_suppliers', ["supplier_id" => @$suppliers->id], [
                            "supplier_id" => @$suppliers->id,
                            "account_number" => $data['account_number'],
                            "currency" => $data['currency'],
                            "currency_local" => $data['currency_local'],
                            "balance" => $data['balance'],
                            "balance_local" => $data['balance_local'],
                            "start_date" => $data['start_date'],
                            "status" => $status,
                        ]);
                    }else{
                        $send = $this->crud->create('account_balance_suppliers', [
                            "supplier_id" => @$suppliers->id,
                            "account_number" => $data['account_number'],
                            "currency" => $data['currency'],
                            "currency_local" => $data['currency_local'],
                            "balance" => $data['balance'],
                            "balance_local" => $data['balance_local'],
                            "debt_limit" => $data['debt_limit'],
                            "start_date" => $data['start_date'],
                            "status" => $status,
                        ]);
                    }

                    echo $send;
                }else{
                    echo json_encode(array("title" => "Not Found", "message" => "Account Number " . $data['account_number'] . " Not Found", "theme" => "error"));
                }
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=account_balance_suppliers_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.id as supplier_ids, a.number as supplier_number, a.name as supplier_name, a.currency, b.*, c.account_name');
        $this->db->from('suppliers a');
        $this->db->join('account_balance_suppliers b', 'a.id = b.supplier_id', 'left');
        $this->db->join('account_coa c', 'b.account_number = c.account_number', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.name', 'asc');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#suppliers {border-collapse: collapse;width: 100%;font-size: 12px;}#suppliers td, #suppliers th {border: 1px solid #ddd;padding: 2px;}#suppliers tr:nth-child(even){background-color: #f2f2f2;}#suppliers tr:hover {background-color: #ddd;}#suppliers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MASTER BALANCE SUPPLIER</small>
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
        
        <table id="suppliers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Supplier Code</th>
                <th rowspan="2">Supplier Name</th>
                <th rowspan="2">Account Number</th>
                <th rowspan="2">Account Name</th>
                <th colspan="2">Original Currency</th>
                <th colspan="2">Local Currency</th>
                <th rowspan="2">Start Date</th>
                <th rowspan="2">Credit Limit</th>
                <th rowspan="2">Status</th>
            </tr>
            <tr>
                <th>Currency</th>
                <th>Balance</th>
                <th>Currency</th>
                <th>Balance</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['supplier_number'] . '</td>
                    <td>' . $data['supplier_name'] . '</td>
                    <td>' . $data['account_number'] . '</td>
                    <td>' . $data['account_name'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . @$data['balance'] . '</td>
                    <td>' . $data['currency_local'] . '</td>
                    <td>' . @$data['balance_local'] . '</td>
                    <td>' . @$data['start_date'] . '</td>
                    <td>' . @$data['debt_limit'] . '</td>
                    <td>' . @$data['status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
