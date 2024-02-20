<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Account_banks extends CI_Controller
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
        $this->form_validation->set_rules('bank_account', 'Account', 'required|min_length[1]|max_length[30]|is_unique[account_banks.bank_account]');
        $this->form_validation->set_rules('bank_name', 'Name', 'required|min_length[1]|max_length[100]|is_unique[account_banks.bank_name]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/account_banks');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('account_banks', ["bank_name" => $post, "bank_account" => $post]);
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
            $this->db->select('a.*, b.account_name');
            $this->db->from('account_banks a');
            $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field != "account_name"){
                        $this->db->like("a.".$filter->field, $filter->value);
                    }else{
                        $this->db->like("b.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('a.bank_name', 'asc');
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
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('account_banks', $post);
                echo $send;
            } else {
                show_error(validation_errors());
            }
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
            $send = $this->crud->update('account_banks', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('account_banks', $data);
        echo $send;
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
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'bank_name' => $data->val($i, 2),
                'bank_account' => $data->val($i, 3),
                'bank_code' => $data->val($i, 4),
                'currency' => $data->val($i, 5),
                'balance' => $data->val($i, 6),
                'start_date' => $data->val($i, 7),
                'p_supplier' => $data->val($i, 8),
                'p_customer' => $data->val($i, 9),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    //UPLOAD CLEAR CACHE
    public function uploadclearFailed()
    {
        @unlink('excel/failed/account_banks.txt');
    }

    //UPLOAD CREATE FAILED
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/account_banks.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/account_banks.txt";
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
            $account_banks = $this->crud->read('account_banks', [], ["bank_account" => $data['bank_account']]);

            if (!empty($account_banks->id)) {
                echo json_encode(array("title" => "Available", "message" => "Bank Account " . $data['bank_account'] . " has been Available", "theme" => "error"));
            } else {
                $dataFinal = array(
                    "bank_name" => $data['bank_name'],
                    "bank_account" => $data['bank_account'],
                    "bank_code" => $data['bank_code'],
                    "currency" => $data['currency'],
                    "balance" => $data['balance'],
                    "start_date" => $data['start_date'],
                    "p_supplier" => $data['p_supplier'],
                    "p_customer" => $data['p_customer'],
                );
                $send   = $this->crud->create('account_banks', $dataFinal);
                echo $send;
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=account_banks_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.account_name');
        $this->db->from('account_banks a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.bank_name', 'ASC');
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
                            <small>MASTER ACCOUNT BANK</small>
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
                <th rowspan="2">Account No</th>
                <th rowspan="2">Account Name</th>
                <th rowspan="2">Bank Name</th>
                <th rowspan="2">Bank Account</th>
                <th rowspan="2">Bank Code</th>
                <th rowspan="2">Start Date</th>
                <th colspan="2">Original Currency</th>
                <th colspan="2">Local Currency</th>
                <th rowspan="2">Payment Supplier</th>
                <th rowspan="2">Customer Receipt</th>
            </tr>
            <tr>
                <th>Currency</th>
                <th>Local</th>
                <th>Currency</th>
                <th>Local</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['account_number'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                            <td>' . $data['bank_name'] . '</td>
                            <td>' . $data['bank_account'] . '</td>
                            <td>' . $data['bank_code'] . '</td>
                            <td>' . $data['start_date'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . number_format($data['balance'], 2) . '</td>
                            <td>' . $data['currency_local'] . '</td>
                            <td>' . number_format($data['balance_local']) . '</td>
                            <td>' . $data['p_supplier'] . '</td>
                            <td>' . $data['p_customer'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
