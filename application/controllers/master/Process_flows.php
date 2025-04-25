<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Process_flows extends CI_Controller
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
         $this->form_validation->set_rules('process_id', 'Code', 'required|min_length[1]|max_length[30]|is_unique[process_flows.process_id]');
        
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/process_flows');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('process_flows', ["name" => $post]);
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
            $this->db->select('a.*, b.name');
            $this->db->from('process_flows a');
            $this->db->join('process b', 'a.process_id = b.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('id', 'ASC');
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
                $send   = $this->crud->create('process_flows', $post);
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
            if ($this->form_validation->run() == TRUE) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('process_flows', ["id" => $id], $post);
            echo $send;
        } else {
            show_error(validation_errors());
        }
    } else {
        show_error("Cannot Process your request");
    }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('process_flows', $data);
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
                 'process_id' => $data->val($i, 2),
                 'type_a' => $data->val($i, 3),
                 'type_b' => $data->val($i, 4),
                 'type_c' => $data->val($i, 5),
                 'type_d' => $data->val($i, 6)
                 
             );
         }
         $datas['total'] = count($datas);
         echo json_encode($datas);
         unlink($_FILES['file_upload']['name']);
     }
     public function uploadclearFailed()
     {
         @unlink('failed/process_flows.txt');
     }
     public function uploadcreateFailed()
     {
         if ($this->input->post()) {
             $message = $this->input->post('message');
             $textFailed = fopen('failed/process_flows.txt', 'a');
             fwrite($textFailed, $message . "\n");
             fclose($textFailed);
         }
     }
 
     //UPLOAD DOWNLOAD FAILED
     public function uploadDownloadFailed()
     {
         $file = "failed/process_flows.txt";
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
             $data = $this->input->post('data');//field excel

            //Cek Process Number            //table             //field           //field excel
            $process = $this->crud->read('process', [], ["id" => $data['process_id']]);
            $process_flows = $this->crud->read('process_flows', [],["process_id" => @$process->id] );

          if (empty($process->name)) {
              echo json_encode(array("title" => "Not Found", "message" => "Process ID " . $data['process_id'] ." is Not Found", "theme" => "error"));
            } elseif (!empty($process_flows->process_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Process ID " . $data['process_id'] . " Duplicate Data", "theme" => "error"));
            } else {
                 $dataFinal = array(
                     //field        //excel
                     "process_id" => $process->id,
                     "type_a" => $data['type_a'],
                     "type_b" => $data['type_b'],
                     "type_c" => $data['type_c'],
                     "type_d" => $data['type_d'],
                 );
                 $send   = $this->crud->create('process_flows', $dataFinal);
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
            header("Content-Disposition: attachment; filename=process_flows_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name');
        $this->db->from('process_flows a');
        $this->db->join('process b', 'a.process_id = b.id');
        $this->db->order_by('process_id', 'ASC');
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
                            <small>MASTER FLOW PROCESS</small>
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
                <th>Id</th>
                <th>Name</th>
                <th>Type A</th>
                <th>Type B</th>
                <th>Type C</th>
                <th>Type D</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['process_id'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['type_a'] . '</td>
                    <td>' . $data['type_b'] . '</td>
                    <td>' . $data['type_c'] . '</td>
                    <td>' . $data['type_d'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
