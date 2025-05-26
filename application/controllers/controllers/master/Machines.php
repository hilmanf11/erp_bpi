<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Machines extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Machine No.', 'required|min_length[1]|max_length[20]|is_unique[machines.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/machines');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('machines', ["name" => $post]);
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
            $this->db->from('machines');
            $this->db->where('deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('id', 'asc');
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
        $format = "MC-".$month;
        $sql = $this->db->query("SELECT max(id) as kode FROM machines WHERE id LIKE '%$format%'");
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
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('machines', $post);
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
            $send = $this->crud->update('machines', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('machines', $data);
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
                'number' => $data->val($i, 2),
                'name' => $data->val($i, 3),
                'specification' => $data->val($i, 4),
                'purchase_date' => $data->val($i, 5),
                'manufacturing_date' => $data->val($i, 6),
                'maker' => $data->val($i, 7),
                'toonage' => $data->val($i, 8),
                'tiebar' => $data->val($i, 9),
                'uom_tiebar' => $data->val($i, 10),
                'min_closing' => $data->val($i, 11),
                'uom_min' => $data->val($i, 12),
                'max_open' => $data->val($i, 13),
                'uom_max' => $data->val($i, 14),
                'volume' => $data->val($i, 15),
                'uom_volume' => $data->val($i, 16),
                'diameter' => $data->val($i, 17),
                'uom_diameter' => $data->val($i, 18),
                'brand' => $data->val($i, 19),
                'status' => $data->val($i, 20)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/machines.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/machines.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/machines.txt";
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
            $machines = $this->crud->read('machines', [], ["number" => $data['number']]);

            //AUTOID
            $month = date('my');
            $format = "MC-".$month;
            $sql = $this->db->query("SELECT max(id) as kode FROM machines WHERE id LIKE '%$format%'");
            $row = $sql->row();
            if ($row->kode == ""){
                $kode = 0;
            } else {
                $kode = substr($row->kode,-3);
            }
            $autoid =$format. sprintf("%03s", $kode + 1);

            if (!empty($machines->number)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Machine No. " . $data['number'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "id" => $autoid,
                    "number" => $data['number'],
                    "name" => $data['name'],
                    "specification" => $data['specification'],
                    "purchase_date" => $data['purchase_date'],
                    "manufacturing_date" => $data['manufacturing_date'],
                    "maker" => $data['maker'],
                    "toonage" => $data['toonage'],
                    "tiebar" => $data['tiebar'],
                    "uom_tiebar" => $data['uom_tiebar'],
                    "min_closing" => $data['min_closing'],
                    "uom_min" => $data['uom_min'],
                    "max_open" => $data['max_open'],
                    "uom_max" => $data['uom_max'],
                    "volume" => $data['volume'],
                    "uom_volume" => $data['uom_volume'],
                    "diameter" => $data['diameter'],
                    "uom_diameter" => $data['uom_diameter'],
                    "brand" => $data['brand'],
                    "status" => $data['status'],
                );
                $send   = $this->crud->create('machines', $dataFinal);
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
            header("Content-Disposition: attachment; filename=machines_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('machines');
        $this->db->where('deleted', 0);
        $this->db->order_by('id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#machines {border-collapse: collapse;width: 100%;font-size: 12px;}#machines td, #machines th {border: 1px solid #ddd;padding: 2px;}#machines tr:nth-child(even){background-color: #f2f2f2;}#machines tr:hover {background-color: #ddd;}#machines th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER MACHINES</h3>
            </div>
        </center>
        
        <table id="machines" border="1">
            <tr>
                <th width="20">No</th>
                <th>Machine ID</th>
                <th>Machine No.</th>
                <th>Name Of Machine</th>
                <th>Spesification</th>
                <th>Purchase Date</th>
                <th>Manufacturing Date</th>
                <th>Maker</th>
                <th>Toonage Of Machine</th>
                <th>Tie Bar</th>
                <th>UOM</th>
                <th>Minimum Closing</th>
                <th>UOM</th>
                <th>Maximum Open</th>
                <th>UOM</th>
                <th>Barrel Volume</th>
                <th>UOM</th>
                <th>Screw Diameter</th>
                <th>UOM</th>
                <th>Brand</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['id'] . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['specification'] . '</td>
                    <td>' . $data['purchase_date'] . '</td>
                    <td>' . $data['manufacturing_date'] . '</td>
                    <td>' . $data['maker'] . '</td>
                    <td>' . $data['toonage'] . '</td>
                    <td>' . $data['tiebar'] . '</td>
                    <td>' . $data['uom_tiebar'] . '</td>
                    <td>' . $data['min_closing'] . '</td>
                    <td>' . $data['uom_min'] . '</td>
                    <td>' . $data['max_open'] . '</td>
                    <td>' . $data['uom_max'] . '</td>
                    <td>' . $data['volume'] . '</td>
                    <td>' . $data['uom_volume'] . '</td>
                    <td>' . $data['diameter'] . '</td>
                    <td>' . $data['uom_diameter'] . '</td>
                    <td>' . $data['brand'] . '</td>
                    <td>' . $data['status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
