<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Mold_items extends CI_Controller
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
            $this->load->view('master/mold_items');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('mold_items', ["id" => $post]);
        echo json_encode($send);
    }
    
    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_mold_name = @base64_decode($get['filter_mold_name']);
            $filter_product_no = @base64_decode($get['filter_product_no']);
            $filter_mold_type = @base64_decode($get['filter_mold_type']);


            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*,b.project_year, b.mold_name, c.number as item_fg_id, d.number as item_fg_id_mold, c.name as item_fg_name, b.cavity_standard, b.cavity_actual, b.shoot_standard, b.shoot_actual, b.mold_type');
            $this->db->from('mold_items a');
            $this->db->join('molds b', 'a.mold_id = b.id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            $this->db->join('item_fg d', 'a.item_fg_id_mold = d.id', 'LEFT');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.mold_id', $filter_mold_name);
            $this->db->like('a.item_fg_id', $filter_product_no);
            $this->db->like('b.mold_type', $filter_mold_type);
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
   
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $send   = $this->crud->create('mold_items', $data);
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
            $send = $this->crud->update('mold_items', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('mold_items', $data);
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
                'mold_id' => $data->val($i, 2),
                'item_fg_id' => $data->val($i, 3),
                'item_fg_id_mold' => $data->val($i, 4),
                'remark' => $data->val($i, 5),
                'status' => $data->val($i, 6)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/mold_items.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/mold_items.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/mold_items.txt";
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

            // Cek Process Number
            $molds = $this->crud->read('molds', [], ["id" => $data['mold_id']]);
            $item_fg = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);
            $item_fg_id_molds = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id_mold']]);

            if (empty($molds->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Molds " . $data['mold_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['item_fg_id'] . " Not Found", "theme" => "error"));
            } else {
                $dataFinal = array(
                    "mold_id" => $data['mold_id'],
                    "item_fg_id" => $data['item_fg_id'],
                    "item_fg_id_mold" => $data['item_fg_id_mold'],
                    "remark" => $data['remark'],
                    "status" => $data['status'],
                );

                if ($data['item_fg_id_mold'] == "" && $molds->mold_type == "DOUBLE") {
                    echo json_encode(array("title" => "Alert", "message" => "Please Input Molds type Product ID Because " . $data['mold_id'] . " Mold Type is DOUBLE", "theme" => "error"));
                } elseif ($molds->mold_type == "SINGLE" && $data['item_fg_id_mold'] != "") {
                    echo json_encode(array("title" => "Alert", "message" => "Please Dont Input Molds type Product ID Because " . $data['mold_id'] . " Mold Type is SINGLE", "theme" => "error"));
                } else {
                    $send = $this->crud->create('mold_items', $dataFinal);
                    echo $send;
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
            header("Content-Disposition: attachment; filename=mold_items_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*,b.project_year, b.mold_name, c.number as item_fg_id, d.number as item_fg_id_mold, c.name as item_fg_name, b.cavity_standard, b.cavity_actual, b.shoot_standard, b.shoot_actual, b.mold_type');
        $this->db->from('mold_items a');
        $this->db->join('molds b', 'a.mold_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join('item_fg d', 'a.item_fg_id_mold = d.id', 'LEFT');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#mold_items {border-collapse: collapse;width: 100%;font-size: 12px;}#mold_items td, #mold_items th {border: 1px solid #ddd;padding: 2px;}#mold_items tr:nth-child(even){background-color: #f2f2f2;}#mold_items tr:hover {background-color: #ddd;}#mold_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER MOLD ITEM</h3>
            </div>
        </center>
        
        <table id="mold_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Mold ID</th>
                <th>Mold Name</th>
                <th>Product No.</th>
                <th>Product Name</th>
                <th>Project Year</th>
                <th>Standard Cavity</th>
                <th>Actual Cavity</th>
                <th>Standard Shoot</th>
                <th>Actual Shoot</th>
                <th>Mold Type</th>
                <th>Mold Type Product</th>
                <th>Remark</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['mold_id'] . '</td>
                    <td>' . $data['mold_name'] . '</td>
                    <td>' . $data['item_fg_id'] . '</td>
                    <td>' . $data['item_fg_name'] . '</td>
                    <td>' . $data['project_year'] . '</td>
                    <td>' . $data['cavity_standard'] . '</td>
                    <td>' . $data['cavity_actual'] . '</td>
                    <td>' . $data['shoot_standard'] . '</td>
                    <td>' . $data['shoot_actual'] . '</td>
                    <td>' . $data['mold_type']. '</td>
                    <td>' . $data['item_fg_id_mold'] . '</td>
                    <td>' . $data['remark'] . '</td>
                    <td>' . $data['status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
