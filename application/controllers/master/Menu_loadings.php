<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Menu_loadings extends CI_Controller
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
            $this->load->view('master/menu_loadings');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('menu_loadings', ["name" => $post]);
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
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.number as machine_number, c.toonage as machine_toonage, d.model as mold_model, d.cavity_actual as mold_cavity_actual, d.cavity_standard as mold_cavity_standard');
            $this->db->from('menu_loadings a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('machines c', 'a.machine_id = c.id');
            $this->db->join('molds d', 'a.mold_id = d.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "item_fg_id"){
                        $this->db->like("b.id", $filter->value);
                    }elseif($filter->field == "item_fg_number"){
                        $this->db->like("b.number", $filter->value);
                    }elseif($filter->field == "item_fg_name"){
                        $this->db->like("b.name", $filter->value);
                    }elseif($filter->field == "machine_number"){
                        $this->db->like("c.number", $filter->value);
                    }elseif($filter->field == "machine_toonage"){
                        $this->db->like("c.toonage", $filter->value);
                    }elseif($filter->field == "mold_model"){
                        $this->db->like("d.model", $filter->value);
                    }elseif($filter->field == "mold_cavity_actual"){
                        $this->db->like("d.cavity_actual", $filter->value);
                    }elseif($filter->field == "mold_cavity_standard"){
                        $this->db->like("d.cavity_standard", $filter->value);
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
            $send   = $this->crud->create('menu_loadings', $post);
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
            $send = $this->crud->update('menu_loadings', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('menu_loadings', $data);
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
                'item_fg_id' => $data->val($i, 2),
                'mold_id' => $data->val($i, 3),
                'machine_id' => $data->val($i, 4),
                'shift' => $data->val($i, 5),
                'shift_hour' => $data->val($i, 6),
                'productcivity' => $data->val($i, 7),
                'cycle_time' => $data->val($i, 8),
                'cycle_time_process' => $data->val($i, 9),
                'manpower' => $data->val($i, 10),
                'runner' => $data->val($i, 11),
                'priority' => $data->val($i, 12)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/menu_loadings.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/menu_loadings.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/menu_loadings.txt";
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
            $item_fg = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);
            $molds = $this->crud->read('molds', [], ["id" => $data['mold_id']]);
            $machine = $this->crud->read('machines', [], ["number" => $data['machine_id']]);

            if (empty($item_fg->number)) {
                echo json_encode(array("title" => "Not Found", "message" => " Product No. " . $data['item_fg_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($molds->id)) {
                echo json_encode(array("title" => "Not Found", "message" => " Mold Model " . $data['mold_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($machine->number)) {
                echo json_encode(array("title" => "Not Found", "message" => " Machine No. " . $data['machine_id'] . " Not Found", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "item_fg_id" => $data['item_fg_id'],
                    "mold_id" => $data['mold_id'],
                    "machine_id" => $machine->id,
                    "shift" => $data['shift'],
                    "shift_hour" => $data['shift_hour'],
                    "productcivity" => $data['productcivity'],
                    "cycle_time" => $data['cycle_time'],
                    "cycle_time_process" => $data['cycle_time_process'],
                    "manpower" => $data['manpower'],
                    "runner" => $data['runner'],
                    "priority" => $data['priority'],
                );
                $send   = $this->crud->create('menu_loadings', $dataFinal);
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
            header("Content-Disposition: attachment; filename=menu_loadings_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.number as machine_number, c.toonage as machine_toonage, d.model as mold_model, d.cavity_actual as mold_cavity_actual, d.cavity_standard as mold_cavity_standard');
        $this->db->from('menu_loadings a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('machines c', 'a.machine_id = c.id');
        $this->db->join('molds d', 'a.mold_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#menu_loadings {border-collapse: collapse;width: 100%;font-size: 12px;}#menu_loadings td, #menu_loadings th {border: 1px solid #ddd;padding: 2px;}#menu_loadings tr:nth-child(even){background-color: #f2f2f2;}#menu_loadings tr:hover {background-color: #ddd;}#menu_loadings th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER MENU LOADING</h3>
            </div>
        </center>
        
        <table id="menu_loadings" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product ID</th>
                <th>Product No.</th>
                <th>Product Name</th>
                <th>Machine No.</th>
                <th>Toonage of Machine</th>
                <th>Mold ID</th>
                <th>Cavity Actual</th>
                <th>Cavity Standard</th>
                <th>Shift</th>
                <th>Hour/Shift</th>
                <th>Productivity Factor</th>
                <th>Cycle Time (Second)</th>
                <th>Cycle Time Second Process</th>
                <th>Man Power</th>
                <th>Runner/Shoot</th>
                <th>Priority</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['item_fg_id'] . '</td>
                    <td>' . $data['item_fg_number'] . '</td>
                    <td>' . $data['item_fg_name'] . '</td>
                    <td>' . $data['machine_number'] . '</td>
                    <td>' . $data['machine_toonage'] . '</td>
                    <td>' . $data['mold_id'] . '</td>
                    <td>' . $data['mold_cavity_actual'] . '</td>
                    <td>' . $data['mold_cavity_standard'] . '</td>
                    <td>' . $data['shift'] . '</td>
                    <td>' . $data['shift_hour'] . '</td>
                    <td>' . $data['productcivity'] . '</td>
                    <td>' . $data['cycle_time'] . '</td>
                    <td>' . $data['cycle_time_process'] . '</td>
                    <td>' . $data['manpower'] . '</td>
                    <td>' . $data['runner'] . '</td>
                    <td>' . $data['priority'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
