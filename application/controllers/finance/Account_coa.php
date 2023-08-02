<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Account_coa extends CI_Controller
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
        $this->form_validation->set_rules('account_number', 'Account Code', 'required|min_length[1]|max_length[30]|is_unique[account_coa.account_number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/account_coa');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads($account_group_detail_id = "")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('account_coa', ["account_name" => $post, "account_group_detail_id" => $account_group_detail_id]);
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
            $this->db->select('a.*, b.name as account_group_detail_name');
            $this->db->from('account_coa a');
            $this->db->join('account_group_details b', 'a.account_group_detail_id = b.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "account_group_detail_name"){
                        $this->db->like("b.name", $filter->value);
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('a.account_number', 'asc');
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
                $send   = $this->crud->create('account_coa', $post);
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
            $send = $this->crud->update('account_coa', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('account_coa', $data);
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
        for ($i = 4; $i <= $total_row; $i++) {
            $datas[] = array(
                'category' => $data->val($i, 2),
                'account_number' => $data->val($i, 3),
                'account_name' => $data->val($i, 4),
                'original_currency' => $data->val($i, 5),
                'original_debit' => $data->val($i, 6),
                'original_kredit' => $data->val($i, 7),
                'local_currency' => $data->val($i, 8),
                'local_debit' => $data->val($i, 9),
                'local_kredit' => $data->val($i, 10),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    //UPLOAD CLEAR CACHE
    public function uploadclearFailed()
    {
        @unlink('excel/failed/account_coa.txt');
    }

    //UPLOAD CREATE FAILED
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/account_coa.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/account_coa.txt";
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
            $account_group_details = $this->crud->read('account_group_details', [], ["number" => $data['category']]);
            $account_coa = $this->crud->read('account_coa', [], ["account_number" => $data['account_number']]);

            if (empty($account_group_details->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Category " . $data['category'] . " Not Found", "theme" => "error"));
            }elseif (!empty($account_coa->id)) {
                echo json_encode(array("title" => "Available", "message" => "Code " . $data['account_number'] . " has been Available", "theme" => "error"));
            } else {
                $dataFinal = array(
                    "account_group_detail_id" => $account_group_details->id,
                    "account_number" => $data['account_number'],
                    "account_name" => $data['account_name'],
                    "original_currency" => $data['original_currency'],
                    "original_debit" => $data['original_debit'],
                    "original_kredit" => $data['original_kredit'],
                    "local_currency" => $data['local_currency'],
                    "local_debit" => $data['local_debit'],
                    "local_kredit" => $data['local_kredit'],
                );
                $send   = $this->crud->create('account_coa', $dataFinal);
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
            header("Content-Disposition: attachment; filename=account_coa_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as account_group_detail_name');
        $this->db->from('account_coa a');
        $this->db->join('account_group_details b', 'a.account_group_detail_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.account_number', 'asc');
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
                            <small>MASTER ACCOUNT CHART OF ACCOUNT</small>
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
                <th rowspan="2">Category</th>
                <th rowspan="2">Account Code</th>
                <th rowspan="2">Account Name</th>
                <th colspan="3">ORIGINAL CURRENCY</th>
                <th colspan="3">LOCAL CURRENCY</th>
            </tr>
            <tr>
                <th>Currency</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Currency</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['account_group_detail_name'] . '</td>
                    <td>' . $data['account_number'] . '</td>
                    <td>' . $data['account_name'] . '</td>
                    <td>' . $data['original_currency'] . '</td>
                    <td>' . $data['original_debit'] . '</td>
                    <td>' . $data['original_kredit'] . '</td>
                    <td>' . $data['local_currency'] . '</td>
                    <td>' . $data['local_debit'] . '</td>
                    <td>' . $data['local_kredit'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
