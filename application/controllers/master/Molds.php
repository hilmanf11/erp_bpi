<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Molds extends CI_Controller
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
        // $this->form_validation->set_rules('number', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[molds.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/molds');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('molds', ["id" => $post]);
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
            $this->db->select('a.*,c.name as customer_name');
            $this->db->from('molds a');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "customer_name"){
                        $this->db->like("c.name", $filter->value);
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('a.id', 'ASC');
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
    //AUTO ID
    public function autoid(){
        $month = date('my');
        $format = "M-".$month;
        $sql = $this->db->query("SELECT max(id) as kode FROM molds WHERE id LIKE '%$format%'");
        $row = $sql->row();
        if ($row->kode == ""){
            $kode = 0;
        } else {
            $kode = substr($row->kode,-3);
        }
        $autoid =$format. sprintf("%03s", $kode + 1);
        echo $autoid;
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $send   = $this->crud->create('molds', $data);
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
            $send = $this->crud->update('molds', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('molds', $data);
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
                //excel
                'mold_name' => $data->val($i, 2),
                'type' => $data->val($i, 3),
                'customer_id' => $data->val($i, 4),
                'model' => $data->val($i, 5),
                'mold_size' => $data->val($i, 6),
                'project_year' => $data->val($i, 7),
                'cavity_standard' => $data->val($i, 8),
                'cavity_actual' => $data->val($i, 9),
                'shoot_standard' => $data->val($i, 10),
                'shoot_actual' => $data->val($i, 11),
                'mold_type' => $data->val($i, 12),
                'remark' => $data->val($i, 13),
                'status' => $data->val($i, 14)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/molds.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/molds.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/molds.txt";
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

            //Cek Process Number          //table       //field        //field excel
            // $molds = $this->crud->read('molds', [], ["number" => $data['number']]);
            // $item_fg = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);
            $customer = $this->crud->read('customers', [], ["id" => $data['customer_id']]);

            //AUTOID
            $month = date('my');
            $format = "M-".$month;
            $sql = $this->db->query("SELECT max(id) as kode FROM molds WHERE id LIKE '%$format%'");
            $row = $sql->row();
            if ($row->kode == ""){
                $kode = 0;
            } else {
                $kode = substr($row->kode,-3);
            }
            $autoid =$format. sprintf("%03s", $kode + 1);

            
            if (empty($customer->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customer " . $data['customer_id'] . " Not Found", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "id" => $autoid,
                    "mold_name" => $data['mold_name'],
                    "type" => $data['type'],
                    "customer_id" => $data['customer_id'],
                    "model" => $data['model'],
                    "mold_size" => $data['mold_size'],
                    "project_year" => $data['project_year'],
                    "cavity_standard" => $data['cavity_standard'],
                    "cavity_actual" => $data['cavity_actual'],
                    "shoot_standard" => $data['shoot_standard'],
                    "shoot_actual" => $data['shoot_actual'],
                    "mold_type" => $data['mold_type'],
                    "remark" => $data['remark'],
                    "status" => $data['status'],
                );
                $send   = $this->crud->create('molds', $dataFinal);
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
            header("Content-Disposition: attachment; filename=molds_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*,c.name as customer_name');
        $this->db->from('molds a');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#molds {border-collapse: collapse;width: 100%;font-size: 12px;}#molds td, #molds th {border: 1px solid #ddd;padding: 2px;}#molds tr:nth-child(even){background-color: #f2f2f2;}#molds tr:hover {background-color: #ddd;}#molds th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br>
            <div style="float: centet; font-size: 16px; text-align: center;">
                <h3>MASTER ITEM MOLD</h3>
            </div>
        </center>
        
        <table id="molds" border="1">
            <tr>
                <th width="20">No</th>
                <th>Mold ID</th>
                <th>Mold Name</th>
                <th>Type</th>
                <th>Customer Name</th>
                <th>Model</th>
                <th>Mold Size</th>
                <th>Project Year</th>
                <th>Standard Cavity</th>
                <th>Actual Cavity</th>
                <th>Standard Shoot</th>
                <th>Actual Shoot</th>
                <th>Mold Type</th>
                <th>Remarks</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['id'] . '</td>
                    <td>' . $data['mold_name'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td>' . $data['customer_name'] . '</td>
                    <td>' . $data['model'] . '</td>
                    <td>' . $data['mold_size'] . '</td>
                    <td>' . $data['project_year'] . '</td>
                    <td>' . $data['cavity_standard'] . '</td>
                    <td>' . $data['cavity_actual'] . '</td>
                    <td>' . $data['shoot_standard'] . '</td>
                    <td>' . $data['shoot_standard'] . '</td>
                    <td>' . $data['mold_type']. '</td>
                    <td>' . $data['remark'] . '</td>
                    <td>' . $data['status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
