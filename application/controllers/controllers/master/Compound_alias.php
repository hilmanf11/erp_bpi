<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Compound_alias extends CI_Controller
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

        // $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[30]|is_unique[compound_alias.number]');
        
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/compound_alias');
        } else {
            redirect('error_access');
        }
    }
     //GET DATA
     public function reads()
     {
         $post = isset($_POST['q']) ? $_POST['q'] : "";
         $send = $this->crud->reads('compound_alias', ["name" => $post]);
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
             $this->db->select('a.*, b.number as item_fg_no, b.name as item_fg_name, c.number as item_rm_no, c.name as item_rm_name');
             $this->db->from('compound_alias a');
             $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
             $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');       
             $this->db->where('a.deleted', 0);
             if (@count($filters) > 0) {
                 foreach ($filters as $filter) {
                    if($filter->field == "item_fg_no"){
                        $this->db->like("b.number", $filter->value);
 
                    }elseif($filter->field == "item_fg_name"){
                        $this->db->like("b.name", $filter->value);

                    }elseif($filter->field == "item_rm_name"){
                        $this->db->like("c.name", $filter->value);
   
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                 }
             }
             $this->db->order_by('a.item_fg_id', 'DESC');
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

                $compound_alias = $this->crud->read('compound_alias', [], ["item_fg_id" => @$data['item_fg_id'],"item_rm_id" => @$data['item_rm_id']]);
                
                if (!empty($compound_alias->item_fg_id)) {
                    echo json_encode(array("title" => "Duplicated", "message" => "Product No " . @$data['item_fg_id'] . " & Part Id " . @$data['item_rm_id'] . " Duplicate Data", "theme" => "error"));
                } else {
                    $send   = $this->crud->create('compound_alias', $data);
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
                $id   = base64_decode($this->input->get('id'));
                $post = $this->input->post();

                $compound_alias = $this->crud->read('compound_alias', [], ["item_fg_id" => @$post['item_fg_id'],"item_rm_id" => @$post['item_rm_id']]);
                
                if (!empty($compound_alias->item_fg_id)) {
                    echo json_encode(array("title" => "Duplicated", "message" => "Product No " . @$data['item_fg_id'] . " & Part Id " . @$data['item_rm_id'] . " Duplicate Data", "theme" => "error"));
                }else{  
                    $send = $this->crud->update('compound_alias', ["id" => $id], $post);
                  echo $send;
                }

            } else {
            show_error("Cannot Process your request");
        
          }
      }
     //DELETE DATA
     public function delete()
     {
         $data = $this->input->post();
         $send = $this->crud->delete('compound_alias', $data);
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
                 'item_rm_id' => $data->val($i, 3),
             );
         }
         $datas['total'] = count($datas);
         echo json_encode($datas);
         unlink($_FILES['file_upload']['name']);
     }
     public function uploadclearFailed()
     {
         @unlink('failed/compound_alias.txt');
     }
     public function uploadcreateFailed()
     {
         if ($this->input->post()) {
             $message = $this->input->post('message');
             $textFailed = fopen('failed/compound_alias.txt', 'a');
             fwrite($textFailed, $message . "\n");
             fclose($textFailed);
         }
     }
 
     //UPLOAD DOWNLOAD FAILED
     public function uploadDownloadFailed()
     {
         $file = "failed/compound_alias.txt";
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
            $item_fg = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);
            $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);

            $compound_alias = $this->crud->read('compound_alias', [], ["item_fg_id" => @$data['item_fg_id'],"item_rm_id" => @$data['item_rm_id']]);

            if (empty($item_fg->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['item_fg_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($item_rm->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Part Id " . $data['item_rm_id'] . " Not Found", "theme" => "error"));
            } elseif (!empty($compound_alias->item_fg_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . @$data['item_fg_id'] . " & Part Id " . @$data['item_rm_id'] . " Duplicate Data", "theme" => "error"));
            } else {
                 $send   = $this->crud->create('compound_alias', $data);
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
             header("Content-Disposition: attachment; filename=compound_alias_$format.xls");
         }
         //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $config_iso = $this->db->get('config_iso')->row();

        $this->db->select('a.*, b.number as item_fg_no, b.name as item_fg_name, c.number as item_rm_no, c.name as item_rm_name');
        $this->db->from('compound_alias a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.item_fg_id', 'DESC');
        $records = $this->db->get()->result_array();

         
         
         $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#compound_alias {border-collapse: collapse;width: 100%;font-size: 12px;}#compound_alias td, #compound_alias th {border: 1px solid #ddd;padding: 2px;}#compound_alias tr:nth-child(even){background-color: #f2f2f2;}#compound_alias tr:hover {background-color: #ddd;}#compound_alias th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
         <center>
             <div style="float: left; font-size: 12px; text-align: left;">
                 <table style="width: 100%;">
                     <tr>
                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                             <img src="' . $config->favicon . '" width="30">
                         </td>
                         <td style="font-size: 14px; text-align: left; margin:2px;">
                             <b>' . $config->name . '</b><br>
                             <small>MASTER COMPOUND ALIAS</small>
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
         
         <table id="compound_alias" border="1">
             <tr>
                 <th width="20">No</th>
                 <th>Product Id</th>
                 <th>Product No</th>
                 <th>Part Id</th>
                 <th>Part No</th>
             </tr>';
         $no = 1;
         
         foreach ($records as $data) {
             $html .= '<tr>
                         <td>' . $no . '</td>
                         <td>' . $data['item_fg_id'] . '</td>
                         <td>' . $data['item_fg_no'] . '</td>
                         <td>' . $data['item_rm_id'] . '</td>
                         <td>' . $data['item_rm_no'] . '</td>
                     </tr>';
             $no++;
         }
         $html .= '</table></body></html>';
         echo $html;
     }
 }
