<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Rm_locations extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[30]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/rm_locations');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function readLocations()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('warehouse_locations', ["location" => $post], ["type" => "RM"], "", "location", "asc", ["location"]);
        echo json_encode($send);
    }
    public function readAreas()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $location = $this->input->get('location');
        $send = $this->crud->reads('warehouse_locations', ["area" => $post], ["type" => "RM", "location" => $location], "", "area", "asc", ["area"]);
        echo json_encode($send);
    }
    public function readRacks()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $location = $this->input->get('location');
        $area = $this->input->get('area');
        $send = $this->crud->reads('warehouse_locations', ["rack" => $post], ["type" => "RM", "location" => $location, "area" => $area], "", "rack", "asc", ["rack"]);
        echo json_encode($send);
    }
    public function readLevels()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $location = $this->input->get('location');
        $area = $this->input->get('area');
        $rack = $this->input->get('rack');
        $send = $this->crud->reads('warehouse_locations', ["level" => $post], ["type" => "RM", "location" => $location, "area" => $area, "rack" => $rack], "", "level", "asc", ["level"]);
        echo json_encode($send);
    }
    public function readLevelSubs()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $location = $this->input->get('location');
        $area = $this->input->get('area');
        $rack = $this->input->get('rack');
        $level = $this->input->get('level');
        $send = $this->crud->reads('warehouse_locations', ["level_sub" => $post], ["type" => "RM", "location" => $location, "area" => $area, "rack" => $rack, "level" => $level], "", "level_sub", "asc", ["level_sub"]);
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
            $this->db->select('*');
            $this->db->from('warehouse_locations');
            $this->db->where('deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->where('type', 'RM');
            $this->db->order_by('number', 'ASC');
            $this->db->order_by('location', 'ASC');
            $this->db->order_by('area', 'ASC');
            $this->db->order_by('rack', 'ASC');
            $this->db->order_by('level', 'ASC');
            $this->db->order_by('level_sub', 'ASC');
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
                $send   = $this->crud->create('warehouse_locations', $post);
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
            $send = $this->crud->update('warehouse_locations', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('warehouse_locations', $data);
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
                'type' => "RM",
                'number' => $data->val($i, 2),
                'location' => $data->val($i, 3),
                'area' => $data->val($i, 4),
                'rack' => $data->val($i, 5),
                'level' => $data->val($i, 6),
                'level_sub' => $data->val($i, 7)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('excel/failed/warehouse_locations.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/warehouse_locations.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/warehouse_locations.txt";
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
            $data = $this->input->post('data');
            $warehouse_locations = $this->crud->read('warehouse_locations', [], ["number" => $data['number'], "type" => "RM"]);
            if (!empty($warehouse_locations)) {
                echo json_encode(array("title" => "Duplicate", "message" => "Code " . $data['number'] . " Duplicate", "theme" => "error"));
            } else {
                $send   = $this->crud->create('warehouse_locations', $data);
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
            header("Content-Disposition: attachment; filename=warehouse_locations_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('*');
        $this->db->from('warehouse_locations');
        $this->db->where('deleted', 0);
        $this->db->where('type', 'RM');
        $this->db->order_by('number', 'ASC');
        $this->db->order_by('location', 'ASC');
        $this->db->order_by('area', 'ASC');
        $this->db->order_by('rack', 'ASC');
        $this->db->order_by('level', 'ASC');
        $this->db->order_by('level_sub', 'ASC');
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
                            <small>WAREHOUSE RM LOCATION</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Code</th>
                <th>Location</th>
                <th>Area</th>
                <th>Rack</th>
                <th>Level</th>
                <th>Level Sub</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['location'] . '</td>
                    <td>' . $data['area'] . '</td>
                    <td>' . $data['rack'] . '</td>
                    <td>' . $data['level'] . '</td>
                    <td>' . $data['level_sub'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
