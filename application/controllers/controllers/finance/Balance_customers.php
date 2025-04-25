<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Balance_customers extends CI_Controller
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
        $this->form_validation->set_rules('customer_id', 'Supplier', 'required|min_length[1]|max_length[30]|is_unique[account_balance_customers.customer_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/balance_customers');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('account_balance_customers', ["name" => $post]);
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
            $this->db->select('a.id as customer_ids, a.number as customer_number, a.name as customer_name, a.currency, b.*, c.account_name');
            $this->db->from('customers a');
            $this->db->join('account_balance_customers b', 'a.id = b.customer_id', 'left');
            $this->db->join('account_coa c', 'b.account_number = c.account_number', 'left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "customer_number"){
                        $this->db->like("a.number", $filter->value);
                    }elseif($filter->field == "customer_name"){
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
            
            if($post['credit_limit'] > 0){
                $status = "1";
            }else{
                $status = "0";
            }

            $account_balance_customers = $this->crud->reads("account_balance_customers", [], ["customer_id" => $post['customer_ids']]);
            if($account_balance_customers){
                $send = $this->crud->update('account_balance_customers', ["customer_id" => $post['customer_ids']], [
                    "customer_id" => $post['customer_ids'],
                    "account_number" => $post['account_number'],
                    "currency" => $post['currency'],
                    "currency_local" => $post['currency_local'],
                    "balance" => $post['balance'],
                    "balance_local" => $post['balance_local'],
                    "credit_limit" => $post['credit_limit'],
                    "start_date" => $post['start_date'],
                    "status" => $status,
                ]);
            }else{
                $send = $this->crud->create('account_balance_customers', [
                    "customer_id" => $post['customer_ids'],
                    "account_number" => $post['account_number'],
                    "currency" => $post['currency'],
                    "currency_local" => $post['currency_local'],
                    "balance" => $post['balance'],
                    "balance_local" => $post['balance_local'],
                    "credit_limit" => $post['credit_limit'],
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
                'customer_number' => $data->val($i, 2),
                'account_number' => $data->val($i, 3),
                'currency' => $data->val($i, 4),
                'balance' => $data->val($i, 5),
                'currency_local' => $data->val($i, 6),
                'balance_local' => $data->val($i, 7),
                'credit_limit' => $data->val($i, 8),
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
        @unlink('excel/failed/account_balance_customers.txt');
    }

    //UPLOAD CREATE FAILED
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/account_balance_customers.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/account_balance_customers.txt";
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
            $customers = $this->crud->read('customers', [], ["number" => $data['customer_number']]);
            $account_balance_customers = $this->crud->reads("account_balance_customers", [], ["customer_id" => @$customers->id]);
            $account_coa = $this->crud->reads("account_coa", [], ["account_number" => @$data['account_number']]);

            if (empty($customers->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customer Code " . $data['customer_number'] . " Not Found", "theme" => "error"));
            } else {
                if($data['credit_limit'] > 0){
                    $status = "1";
                }else{
                    $status = "0";
                }
                
                if($account_coa){
                    if($account_balance_customers){
                        $send = $this->crud->update('account_balance_customers', ["customer_id" => @$customers->id], [
                            "customer_id" => $data['customer_ids'],
                            "account_number" => $data['account_number'],
                            "currency" => $data['currency'],
                            "currency_local" => $data['currency_local'],
                            "balance" => $data['balance'],
                            "balance_local" => $data['balance_local'],
                            "credit_limit" => $data['credit_limit'],
                            "start_date" => $data['start_date'],
                            "status" => $status,
                        ]);
                    }else{
                        $send = $this->crud->create('account_balance_customers', [
                            "customer_id" => $data['customer_ids'],
                            "account_number" => $data['account_number'],
                            "currency" => $data['currency'],
                            "currency_local" => $data['currency_local'],
                            "balance" => $data['balance'],
                            "balance_local" => $data['balance_local'],
                            "credit_limit" => $data['credit_limit'],
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
            header("Content-Disposition: attachment; filename=account_balance_customers_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.id as customer_ids, a.number as customer_number, a.name as customer_name, a.currency, b.*, c.account_name');
        $this->db->from('customers a');
        $this->db->join('account_balance_customers b', 'a.id = b.customer_id', 'left');
        $this->db->join('account_coa c', 'b.account_number = c.account_number', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.name', 'asc');
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
                            <small>MASTER BALANCE CUSTOMER</small>
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
                <th rowspan="2">Customer Code</th>
                <th rowspan="2">Customer Name</th>
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
                        <td>' . $data['customer_number'] . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['account_number'] . '</td>
                        <td>' . $data['account_name'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . @$data['balance'] . '</td>
                        <td>' . $data['currency_local'] . '</td>
                        <td>' . @$data['balance_local'] . '</td>
                        <td>' . @$data['start_date'] . '</td>
                        <td>' . @$data['credit_limit'] . '</td>
                        <td>' . @$data['status'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
