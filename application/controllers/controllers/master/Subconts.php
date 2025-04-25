<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Subconts extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Subcont Code', 'required|min_length[1]|max_length[20]|is_unique[subconts.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/subconts');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('subconts', ["name" => $post]);
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
            $this->db->select('a.*, b.name as subcont_type_name, c.name as delivery_area_name');
            $this->db->from('subconts a');
            $this->db->join('subcont_types b', 'a.subcont_type_id = b.id');
            $this->db->join('delivery_areas c', 'a.delivery_area_id = c.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "subcont_type_name") {
                        $this->db->like("b.id", $filter->value);
                    } elseif ($filter->field == "delivery_area_name") {
                        $this->db->like("c.id", $filter->value);
                    } else {
                        $this->db->like("a." . $filter->field, $filter->value);
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
        $sql = $this->db->query("SELECT max(id) as kode FROM subconts");
        $row = $sql->row();
        $kode = substr($row->kode,3);
        $autoid ="S". sprintf("%03s", $kode + 1);
        echo $autoid;
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('subconts', $post);
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
            $send = $this->crud->update('subconts', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('subconts', $data);
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
                'subcont_type_id' => $data->val($i, 2),
                'delivery_area_id' => $data->val($i, 3),
                'number' => $data->val($i, 4),
                'name' => $data->val($i, 5),
                'address' => $data->val($i, 6),
                'contact_person' => $data->val($i, 7),
                'telp' => $data->val($i, 8),
                'fax' => $data->val($i, 9),
                'email' => $data->val($i, 10),
                'website' => $data->val($i, 11),
                'status' => $data->val($i, 12)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/subconts.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/subconts.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/subconts.txt";
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
            $subcont = $this->crud->read('subconts', [], ["number" => $data['number']]);
            $subcont_type = $this->crud->read('subcont_types', [], ["id" => $data['subcont_type_id']]);
            $delivery_area = $this->crud->read('delivery_areas', [], ["id" => $data['delivery_area_id']]);

            //AUTOID
            $sql = $this->db->query("SELECT max(id) as kode FROM subconts");
            $row = $sql->row();
            $kode = substr($row->kode,3);
            $autoid ="S". sprintf("%03s", $kode + 1);

            if (empty($subcont_type->name)) {
                echo json_encode(array("title" => "Not Found", "message" => " Type " . $data['subcont_type_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($delivery_area->name)) {
                echo json_encode(array("title" => "Not Found", "message" => " Area " . $data['delivery_area_id'] . " Not Found", "theme" => "error"));
            } elseif (!empty($subcont->number)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Subcont Code " . $data['number'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "id" => $autoid,
                    "subcont_type_id" => $data['subcont_type_id'],
                    "delivery_area_id" => $data['delivery_area_id'],
                    "number" => $data['number'],
                    "name" => $data['name'],
                    "address" => $data['address'],
                    "contact_person" => $data['contact_person'],
                    "telp" => $data['telp'],
                    "fax" => $data['fax'],
                    "email" => $data['email'],
                    "website" => $data['website'],
                    "status" => $data['status'],
                );
                $send   = $this->crud->create('subconts', $dataFinal);
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
            header("Content-Disposition: attachment; filename=subconts_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as subcont_type_name, c.name as delivery_area_name');
        $this->db->from('subconts a');
        $this->db->join('subcont_types b', 'a.subcont_type_id = b.id');
        $this->db->join('delivery_areas c', 'a.delivery_area_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#subconts {border-collapse: collapse;width: 100%;font-size: 12px;}#subconts td, #subconts th {border: 1px solid #ddd;padding: 2px;}#subconts tr:nth-child(even){background-color: #f2f2f2;}#subconts tr:hover {background-color: #ddd;}#subconts th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER SUBCONT</h3>
            </div>
        </center>
        
        <table id="subconts" border="1">
            <tr>
                <th width="20">No</th>
                <th>Subcont ID</th>
                <th>Subcont Name</th>
                <th>Subcont Code</th>
                <th>Type</th>
                <th>Address</th>
                <th>Area</th>
                <th>Contact Person</th>
                <th>Telepon</th>
                <th>Fax</th>
                <th>Email</th>
                <th>Website</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['id'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['subcont_type_name'] . '</td>
                    <td>' . $data['address'] . '</td>
                    <td>' . $data['delivery_area_name'] . '</td>
                    <td>' . $data['contact_person'] . '</td>
                    <td>' . $data['telp'] . '</td>
                    <td>' . $data['fax'] . '</td>
                    <td>' . $data['email'] . '</td>
                    <td>' . $data['website'] . '</td>
                    <td>' . $data['status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
