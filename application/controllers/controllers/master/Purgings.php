<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purgings extends CI_Controller
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
        // $this->form_validation->set_rules('number', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[purgings.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/purgings');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('purgings', ["machine_id" => $post]);
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
            $this->db->select('a.*, b.number as machine_number, b.name as machine_name');
            $this->db->from('purgings a');
            $this->db->join('machines b', 'a.machine_id = b.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "machine_id"){
                        $this->db->like("b.id", $filter->value);
                    }elseif($filter->field == "machine_number"){
                        $this->db->like("b.number", $filter->value);
                    }elseif($filter->field == "machine_name"){
                        $this->db->like("b.name", $filter->value);
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
            $send   = $this->crud->create('purgings', $post);
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
            $send = $this->crud->update('purgings', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purgings', $data);
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
                'machine_id' => $data->val($i, 2),
                'item_sub_family' => $data->val($i, 3),
                'kind' => $data->val($i, 4),
                'qty' => $data->val($i, 5),
                'uom' => $data->val($i, 6),
                'status' => $data->val($i, 7)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/purgings.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/purgings.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/purgings.txt";
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
            $machine = $this->crud->read('machines', [], ["id" => $data['machine_id']]);

            if (empty($machine->number)) {
                echo json_encode(array("title" => "Not Found", "message" => " Machine No. " . $data['machine_id'] . " Not Found", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "machine_id" => $data['machine_id'],
                    "item_sub_family" => $data['item_sub_family'],
                    "kind" => $data['kind'],
                    "qty" => $data['qty'],
                    "uom" => $data['uom'],
                    "total" => ($data['qty']*($machine->volume / 1000)),
                    "status" => $data['status'],
                );
                $send   = $this->crud->create('purgings', $dataFinal);
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
            header("Content-Disposition: attachment; filename=purgings_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as machine_number, b.name as machine_name');
        $this->db->from('purgings a');
        $this->db->join('machines b', 'a.machine_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#purgings {border-collapse: collapse;width: 100%;font-size: 12px;}#purgings td, #purgings th {border: 1px solid #ddd;padding: 2px;}#purgings tr:nth-child(even){background-color: #f2f2f2;}#purgings tr:hover {background-color: #ddd;}#purgings th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER PURGING</h3>
            </div>
        </center>
        
        <table id="purgings" border="1">
            <tr>
                <th width="20">No</th>
                <th>Machine ID</th>
                <th>Machine No.</th>
                <th>Name Of Machine</th>
                <th>Qty Maximum Purging</th>
                <th>Total Purging</th>
                <th>Sub Product Family</th>
                <th>Kind of Colors</th>
                <th>UOM</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['machine_id'] . '</td>
                    <td>' . $data['machine_number'] . '</td>
                    <td>' . $data['machine_name'] . '</td>
                    <td>' . $data['qty'] . '</td>
                    <td>' . $data['total'] . '</td>
                    <td>' . $data['item_sub_family'] . '</td>
                    <td>' . $data['kind'] . '</td>
                    <td>' . $data['uom'] . '</td>
                    <td>' . $data['status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
