<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Safety_stock_rm extends CI_Controller
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
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/safety_stock_rm');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('safety_stock', ["name" => $post]);
        echo json_encode($send);
    }

    //GET DATA
    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.id as item_rm_id, a.number as item_rm_number, a.name as item_rm_name
        FROM item_rm a 
        WHERE a.id LIKE '%$post%' or a.name LIKE '%$post%' or a.number LIKE '%$post%'");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));//filter langsung di datagrid
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*');
            $this->db->from('safety_stock_rm a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "item_rm_id"){
                        $this->db->like("b.id", $filter->value);
                    }elseif($filter->field == "item_rm_name"){
                        $this->db->like("a.item_rm_name", $filter->value);
                    }elseif($filter->field == "item_rm_number"){
                        $this->db->like("a.item_rm_number", $filter->value);
                    }elseif($filter->field == "safety_stock"){
                        $this->db->like("a.safety_stock", $filter->value);
                    }elseif($filter->field == "prod_plan"){
                        $this->db->like("a.prod_plan", $filter->value);
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->group_by('a.id');
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
            $post   = $this->input->post();
            $safety_stock = $this->crud->read('safety_stock_rm', [], ["item_rm_id" => $post['item_rm_id']]);
            $item_rm = $this->crud->read('item_rm', [], ["id" => $post['item_rm_id']]);

            if (!empty($safety_stock->item_rm_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $item_rm->number . " Duplicate Data", "theme" => "error"));
            } else {
                $send   = $this->crud->create('safety_stock_rm', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            
            $id = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $existing_data = $this->crud->read('safety_stock_rm', [], ["id" => $id]); // Membaca data yang ada

            // Periksa apakah item_rm_id dan machine_id tetap sama
            if (
                ($existing_data->item_rm_id == $post['item_rm_id'])
            ) {
                // item_rm_id dan machine_id tetap sama, lanjutkan dengan pembaruan
                $send = $this->crud->update('safety_stock_rm', ["id" => $id], $post);
                echo $send;
            } else {
                // item_rm_id atau machine_id telah berubah, lakukan validasi duplikasi
                $safety_stocks = $this->crud->read('safety_stock_rm', [], ["item_rm_id" => $post['item_rm_id']]);
                $item_rm = $this->crud->read('item_rm', [], ["id" => $post['item_rm_id']]);
                if (!empty($safety_stocks->item_rm_id)) {
                    echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $item_rm->number ." Duplicate Data", "theme" => "error"));
                } else {
                    // Tidak ada duplikasi, lanjutkan dengan pembaruan
                    $send = $this->crud->update('safety_stock_rm', ["id" => $id], $post);
                    echo $send;
                }
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('safety_stock_rm', $data);
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
                'item_rm_number' => $data->val($i, 2),
                'safety_stock' => $data->val($i, 3)
                
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/safety_stock_rm.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/safety_stock_rm.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/safety_stock_rm.txt";
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
            $safety_stock = $this->crud->read('safety_stock_rm', [], ["item_rm_number" => $data['item_rm_number']]);
            $item_rm = $this->crud->read('item_rm', [], ["number" => $data['item_rm_number']]);

            if (!empty($safety_stock->item_rm_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['item_rm_number'] ." Duplicate Data", "theme" => "error"));
            } else if(empty($item_rm->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['item_rm_number'] ." Not Found in Master Item Finish Good", "theme" => "error"));
            }else{
                $dataFinal = array(
                    //field
                    "item_rm_id" => $item_rm->id,
                    "item_rm_number" => $data['item_rm_number'],
                    "item_rm_name" => $item_rm->name,
                    "safety_stock" => $data['safety_stock'],
                );
                $send   = $this->crud->create('safety_stock_rm', $dataFinal);
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
            header("Content-Disposition: attachment; filename=safety_stock_rm_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*');
        $this->db->from('safety_stock_rm a');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
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
                <h3>SAFETY STOCK RM</h3>
            </div>
        </center>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product Id</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Safety Stock</th>
                <th>Prod Plan</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['item_rm_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_name'] . '</td>                    
                    <td>' . $data['safety_stock'] . '</td>
                    <td>' . $data['prod_plan'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
