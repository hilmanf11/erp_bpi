<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Component_rates extends CI_Controller
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
        $this->form_validation->set_rules('year', 'Year.', 'required|min_length[1]|max_length[20]|is_unique[component_rates.year]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/component_rates');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('component_rates', ["name" => $post]);
        echo json_encode($send);
    }

    public function readPeriod($select)
    {
        if ($select == "month") {
            $month = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
            foreach ($month as $key => $value) {
                $months[] = array("id" => $key, "name" => $value);
            }

            echo json_encode($months);
        } else if ($select == "year") {
            $year_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
            $year_now = date('Y', strtotime('+1 year', strtotime(date('Y'))));
            for ($i = $year_now; $i >= $year_before; $i--) {
                $years[] = array("id" => $i, "name" => $i);
            }

            echo json_encode($years);
        } else {
            show_error("Cannot Process your request");
        }
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
            $this->db->select('a.*, b.name as division');
            $this->db->from('component_rates a');
            $this->db->join('divisions b', 'a.division_id = b.id','left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {

                    if ($filter->field == 'division') {
                        $filter->field = 'b.name';
                    }

                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('a.id', 'asc');
            $this->db->order_by('a.year', 'asc');
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
        $format = "CPTR-".$month;
        $sql = $this->db->query("SELECT max(id) as kode FROM component_rates WHERE id LIKE '%$format%'");
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
                $send   = $this->crud->create('component_rates', $post);
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
            $send = $this->crud->update('component_rates', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('component_rates', $data);
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
                'year' => $data->val($i, 2),
                'division_id' => $data->val($i, 3),
                'salary' => $data->val($i, 4),
                'overhead' => $data->val($i, 5),
                'electricity' => $data->val($i, 6),
                'hkw' => $data->val($i, 7),
                'shift' => $data->val($i, 8),
                'hour' => $data->val($i, 9),
                'efisiensi' => $data->val($i, 10),
                'status' => $data->val($i, 11)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/component_rates.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/component_rates.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/component_rates.txt";
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
            $component_rates = $this->crud->read('component_rates', [], ["year" => $data['year']]);

            // var_dump($component_rates);
            // return;

            //AUTOID
            $month = date('my');
            $format = "CPTR-".$month;
            $sql = $this->db->query("SELECT max(id) as kode FROM component_rates WHERE id LIKE '%$format%'");
            $row = $sql->row();
            if ($row->kode == ""){
                $kode = 0;
            } else {
                $kode = substr($row->kode,-3);
            }
            $autoid =$format. sprintf("%03s", $kode + 1);

            if (!empty($component_rates)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Year. " . $data['year'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "id" => $autoid,
                    "year" => $data['year'],
                    "division_id" => $data['division_id'],
                    "salary" => $data['salary'],
                    "overhead" => $data['overhead'],
                    "electricity" => $data['electricity'],
                    "hkw" => $data['hkw'],
                    "shift" => $data['shift'],
                    "hour" => $data['hour'],
                    "efisiensi" => $data['efisiensi'],
                    "status" => $data['status'],
                );
                $send   = $this->crud->create('component_rates', $dataFinal);
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
            header("Content-Disposition: attachment; filename=component_rates_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as division');
        $this->db->from('component_rates a');
        $this->db->join('divisions b', 'a.division_id = b.id','left');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $this->db->order_by('a.year', 'asc');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#component_rates {border-collapse: collapse;width: 100%;font-size: 12px;}#component_rates td, #component_rates th {border: 1px solid #ddd;padding: 2px;}#component_rates tr:nth-child(even){background-color: #f2f2f2;}#component_rates tr:hover {background-color: #ddd;}#component_rates th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER MACHINE PRICES</h3>
            </div>
        </center>
        
        <table id="component_rates" border="1">
            <tr>
                <th width="20">No</th>
                <th>Year</th>
                <th>Division</th>
                <th>Salary</th>
                <th>Overhead Cost</th>
                <th>Electricity Cost</th>
                <th>HKW</th>
                <th>Shift</th>
                <th>Hour</th>
                <th>Efficiency</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['year'] . '</td>
                    <td>' . $data['division'] . '</td>
                    <td>' . number_format($data['salary'],2) . '</td>
                    <td>' . number_format($data['overhead'],2) . '</td>
                    <td>' . number_format($data['electricity'],2) . '</td>
                    <td>' . $data['hkw'] . '</td>
                    <td>' . $data['shift'] . '</td>
                    <td>' . $data['hour'] . '</td>
                    <td>' . $data['efisiensi'] . '</td>
                    <td>' . $data['status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
