<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Setting_molds extends CI_Controller
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

        // $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[30]|is_unique[setting_molds.number]');
        
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/setting_molds');
        } else {
            redirect('error_access');
        }
    }
     //GET DATA
     public function reads()
     {
         $post = isset($_POST['q']) ? $_POST['q'] : "";
         $send = $this->crud->reads('setting_molds', ["item_fg_id" => $post]);
         echo json_encode($send);
     }

     public function readss()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name
            FROM setting_molds a 
            JOIN item_fg b ON a.item_fg_id = b.id");
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
             $this->db->select('a.*, b.number as item_fg_no, b.name as item_fg_name, c.number as machine_no , d.model as mold_model, d.actual as mold_actual, d.standard as mold_standard');
             $this->db->from('setting_molds a');
             $this->db->join('item_fg b', 'a.item_fg_id = b.id');
             $this->db->join('machines c', 'a.machine_id = c.id');
             $this->db->join('molds d', 'a.mold_id = d.id');
             $this->db->where('a.deleted', 0);
             if (@count($filters) > 0) {
                 foreach ($filters as $filter) {
                     if($filter->field == "item_fg_no"){
                         $this->db->like("b.number", $filter->value);
 
                     }elseif($filter->field == "item_fg_name"){
                         $this->db->like("b.name", $filter->value);
 
                     }elseif($filter->field == "machine_no"){
                         $this->db->like("c.number", $filter->value);
                        
                     }elseif($filter->field == "mold_model"){
                         $this->db->like("d.model", $filter->value);

                    }elseif($filter->field == "mold_actual"){
                        $this->db->like("d.actual", $filter->value);

                    }elseif($filter->field == "mold_standard"){
                        $this->db->like("d.standard", $filter->value);
   
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
     //CREATE DATA
     public function create()
     {
         if ($this->input->post()) {
                 $post   = $this->input->post();
                 $setting_molds = $this->crud->read('setting_molds', [], ["item_fg_id" => $post['item_fg_id'],"machine_id" => $post['machine_id'],"mold_id" => $post['mold_id']]);
                
                if (!empty($setting_molds->item_fg_id)) {
                    echo json_encode(array("title" => "Duplicated", "message" => "Product Id " . $post['item_fg_id'] ." & Machine Id " . $post['machine_id'] . " Duplicate Data", "theme" => "error"));
                } else {
                    $send   = $this->crud->create('setting_molds', $post);
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
            $existing_data = $this->crud->read('setting_molds', [], ["id" => $id]); // Membaca data yang ada

            // Periksa apakah item_fg_id dan machine_id tetap sama
            if (
                ($existing_data->item_fg_id == $post['item_fg_id']) &&
                ($existing_data->machine_id == $post['machine_id']) &&
                ($existing_data->mold_id == $post['mold_id'])
            ) {
                // Item_fg_id dan machine_id tetap sama, lanjutkan dengan pembaruan
                $send = $this->crud->update('setting_molds', ["id" => $id], $post);
                echo $send;
            } else {
                // Item_fg_id atau machine_id telah berubah, lakukan validasi duplikasi
                $setting_molds = $this->crud->read('setting_molds', [], ["item_fg_id" => $post['item_fg_id'], "machine_id" => $post['machine_id'], "mold_id" => $post['mold_id']]);
                if (!empty($setting_molds->item_fg_id)) {
                    echo json_encode(array("title" => "Duplicated", "message" => "Product Id " . $post['item_fg_id'] . " & Machine Id " . $post['machine_id'] . " Duplicate Data", "theme" => "error"));
                } else {
                    // Tidak ada duplikasi, lanjutkan dengan pembaruan
                    $send = $this->crud->update('setting_molds', ["id" => $id], $post);
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
         $send = $this->crud->delete('setting_molds', $data);
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
                 'machine_id' => $data->val($i, 3),
                 'mold_id' => $data->val($i, 4),
                 'cycle_time' => $data->val($i, 5),
                 'lot_size' => $data->val($i, 6),
                 'efficiency' => $data->val($i, 7),
                 'priority' => $data->val($i, 8),
             );
         }
         $datas['total'] = count($datas);
         echo json_encode($datas);
         unlink($_FILES['file_upload']['name']);
     }
     public function uploadclearFailed()
     {
         @unlink('failed/setting_molds.txt');
     }
     public function uploadcreateFailed()
     {
         if ($this->input->post()) {
             $message = $this->input->post('message');
             $textFailed = fopen('failed/setting_molds.txt', 'a');
             fwrite($textFailed, $message . "\n");
             fclose($textFailed);
         }
     }
 
     //UPLOAD DOWNLOAD FAILED
     public function uploadDownloadFailed()
     {
         $file = "failed/setting_molds.txt";
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

            //Cek Process Number        //table          //field          //field excel
            $product = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);
            $machine = $this->crud->read('machines', [], ["id" => $data['machine_id']]);
            $molds = $this->crud->read('molds', [], ["id" => $data['mold_id']]);
            $setting_molds = $this->crud->read('setting_molds', [], ["item_fg_id" => $data['item_fg_id'],"machine_id" => $data['machine_id'],"mold_id" => $data['mold_id']]);

            if (empty($product->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product Id " . $data['item_fg_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($machine->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Machine Id " . $data['machine_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($molds->name)) {
                echo json_encode(array("title" => "Not Found", "message" => "Mold Id " . $data['mold_id'] . " Not Found", "theme" => "error"));
            } elseif (!empty($setting_molds->item_fg_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product Id " . $data['item_fg_id'] . " Duplicate Data", "theme" => "error"));
            } else {
                 $send   = $this->crud->create('setting_molds', $data);
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
             header("Content-Disposition: attachment; filename=setting_molds_$format.xls");
         }
         //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $config_iso = $this->db->get('config_iso')->row();

        $this->db->select('a.*, b.number as item_fg_no, b.name as item_fg_name , c.number as machine_no , d.model as mold_model, d.actual as mold_actual, d.standard as mold_standard');
        $this->db->from('setting_molds a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('machines c', 'a.machine_id = c.id');
        $this->db->join('molds d', 'a.mold_id = d.id');
        $this->db->where('a.deleted', 0);
         $this->db->order_by('id', 'ASC');
         $records = $this->db->get()->result_array();
         
         $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#setting_molds {border-collapse: collapse;width: 100%;font-size: 12px;}#setting_molds td, #setting_molds th {border: 1px solid #ddd;padding: 2px;}#setting_molds tr:nth-child(even){background-color: #f2f2f2;}#setting_molds tr:hover {background-color: #ddd;}#setting_molds th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
         <center>
             <div style="float: left; font-size: 12px; text-align: left;">
                 <table style="width: 100%;">
                     <tr>
                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                             <img src="' . $config->favicon . '" width="30">
                         </td>
                         <td style="font-size: 14px; text-align: left; margin:2px;">
                             <b>' . $config->name . '</b><br>
                             <small>MASTER SETTING MOLDS</small>
                         </td>
                     </tr>
                 </table>
             </div>
             <div style="float: right; font-size: 12px; text-align: right;">
                 <table style="width:100%; font-size:10px;">
                     <tr>
                         <td width="60">Doc No</td>
                         <td width="5">:</td>
                         <td width="100">' . $config_iso->doc_customer . '</td>
                     </tr>
                     <tr>
                         <td>Form</td>
                         <td>:</td>
                         <td>' . $config_iso->form_customer . '</td>
                     </tr>
                     <tr>
                         <td>Print Date</td>
                         <td>:</td>
                         <td>' . date("Y-m-d H:i") . '</td>
                     </tr>
                     <tr>
                         <td>Print By</td>
                         <td>:</td>
                         <td>' . $this->session->name . '</td>
                     </tr>
                 </table> 
             </div>
         </center>
         <br><br><br><br>
         
         <table id="setting_molds" border="1">
             <tr>
                 <th width="20">No</th>
                 <th>Product ID</th>
                 <th>Product No</th>
                 <th>Product Name</th>
                 <th>Machine ID</th>
                 <th>Machine No</th>
                 <th>Mold No</th>
                 <th>Model</th>
                 <th>Cavity Actual</th>
                 <th>cavity Standard</th>
                 <th>Cycle Time (Shot/Second)</th>
                 <th>Lot Size</th>
                 <th>Effiency (%)</th>
                 <th>Priority</th>
             </tr>';
         $no = 1;
         foreach ($records as $data) {
             $html .= '<tr>
                         <td>' . $no . '</td>
                         <td>' . $data['item_fg_id'] . '</td>
                         <td>' . $data['item_fg_no'] . '</td>
                         <td>' . $data['item_fg_name'] . '</td>
                         <td>' . $data['machine_id'] . '</td>
                         <td>' . $data['machine_no'] . '</td>
                         <td>' . $data['mold_id'] . '</td>
                         <td>' . $data['mold_model'] . '</td>
                         <td>' . $data['mold_actual'] . '</td>
                         <td>' . $data['mold_standard'] . '</td>
                         <td>' . $data['cycle_time'] . '</td>
                         <td>' . $data['lot_size'] . '</td>
                         <td>' . $data['efficiency'] . '</td>
                         <td>' . $data['priority'] . '</td>
                     </tr>';
             $no++;
         }
         $html .= '</table></body></html>';
         echo $html;
     }
 }
