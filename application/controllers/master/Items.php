<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Items extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[30]|is_unique[items.number]');
        
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/items');
        } else {
            redirect('error_access');
        }
    }
     //GET DATA
     public function reads()
     {
         $post = isset($_POST['q']) ? $_POST['q'] : "";
         $send = $this->crud->reads('items', ["name" => $post]);
         echo json_encode($send);
     }
     //CODE OTOMATIS
     public function autoid($item_category_number, $item_family_number, $item_family_sub_number = "NA"){
    
        $code = $item_category_number . $item_family_number . $item_family_sub_number; 
        $sql = $this->db->query("SELECT coalesce(max(`id`), 0) as kode From items where id like '%$code%'");
        $row = $sql->row();
        $kode = substr($row->kode, -4);
        $autoid = $code . "-" . sprintf("%04s", $kode + 1);
        echo $autoid;
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
            $this->db->select('a.*, b.name as item_category_name , c.name as item_familys_name , d.name as item_family_sub_name');
            $this->db->from('items a');
            $this->db->join('item_categories b', 'a.item_category_number = b.number');
            $this->db->join('item_familys c', 'a.item_family_number = c.number');
            $this->db->join('item_family_subs d', 'a.item_family_sub_number = d.number','left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "item_category_name"){
                        $this->db->like("b.name", $filter->value);

                    }elseif($filter->field == "item_familys_name"){
                        $this->db->like("c.name", $filter->value);

                    }elseif($filter->field == "item_family_sub_name"){
                        $this->db->like("d.name", $filter->value);

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
             if ($this->form_validation->run() == TRUE) {
                 $post   = $this->input->post();
                 $send   = $this->crud->create('items', $post);
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
                  $send = $this->crud->update('items', ["id" => $id], $post);
                  echo $send;
              } else {
                show_error("Cannot Process your request");
        
          }
      }
     //DELETE DATA
     public function delete()
     {
         $data = $this->input->post();
         $send = $this->crud->delete('items', $data);
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
                 'part_no' => $data->val($i, 2),
                 'part_name' => $data->val($i, 3),
                 'specification' => $data->val($i, 4),
                 'product_type' => $data->val($i, 5),
                 'category' => $data->val($i, 6),
                 'product_family' => $data->val($i, 7),
                 'sub_product_family' => $data->val($i, 8),
                 'unit_of_measure' => $data->val($i, 9),
                 'lead_time_production' => $data->val($i, 10),
                 'weight' => $data->val($i, 11),
                 'mpq' => $data->val($i, 12),
                 'moq' => $data->val($i, 13),
                 'safety_stock' => $data->val($i, 14),
                 'lot' => $data->val($i, 15),
                 'lifetime' => $data->val($i, 16),
                 'min' => $data->val($i, 17),
                 'max' => $data->val($i, 18),
                 'status' => $data->val($i, 19)
                 
             );
         }
         $datas['total'] = count($datas);
         echo json_encode($datas);
         unlink($_FILES['file_upload']['name']);
     }
     public function uploadclearFailed()
     {
         @unlink('failed/items.txt');
     }
     public function uploadcreateFailed()
     {
         if ($this->input->post()) {
             $message = $this->input->post('message');
             $textFailed = fopen('failed/items.txt', 'a');
             fwrite($textFailed, $message . "\n");
             fclose($textFailed);
         }
     }
 
     //UPLOAD DOWNLOAD FAILED
     public function uploadDownloadFailed()
     {
         $file = "failed/items.txt";
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

            //Cek Process Number        //table                   //field           //field excel
            $category = $this->crud->read('item_categories', [], ["number" => $data['category']]);
            $prod_fam = $this->crud->read('item_familys', [], ["number" => $data['product_family']]);
            $prod_sub_fam = $this->crud->read('item_family_subs', [], ["number" => $data['sub_product_family']]);
            $items = $this->crud->read('items', [], ["number" => $data['part_no']]);

            if (empty($category->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Category Code " . $data['category'] . " Not Found", "theme" => "error"));
            } elseif (empty($prod_fam->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product Family Code " . $data['product_family'] . " Not Found", "theme" => "error"));
            } elseif (!empty($items->number)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['part_no'] . " Duplicate Data", "theme" => "error"));
            } else {
                //autoid
                
                if (empty($prod_sub_fam->number)) {
                    $code = $data['category'] . $data['product_family']."NA";
                }else{
                    $code = $data['category'] . $data['product_family'].$data['sub_product_family'];
                }
                $sql = $this->db->query("SELECT coalesce(max(`id`), 0) as kode From items where id like '%$code%'");
                $row = $sql->row();
                $kode = substr($row->kode, -4);
                $autoid = $code . "-" . sprintf("%04s", $kode + 1);

                 $dataFinal = array(
                     //field        //excel
                     "id" => $autoid,
                     "number" => $data['part_no'],
                     "name" => $data['part_name'],
                     "specification" => $data['specification'],
                     "type" => $data['product_type'],
                     "item_category_number" => $data['category'],
                     "item_family_number" => $data['product_family'],
                     "item_family_sub_number" => $data['sub_product_family'],
                     "uom" => $data['unit_of_measure'],
                     "leadtime" => $data['lead_time_production'],
                     "weight" => $data['weight'],
                     "mpq" => $data['mpq'],
                     "moq" => $data['moq'],
                     "safety_stock" => $data['safety_stock'],
                     "lot" => $data['lot'],
                     "lifetime" => $data['lifetime'],
                     "min" => $data['min'],
                     "max" => $data['max'],
                     "status" => $data['status'],
                 );
                 $send   = $this->crud->create('items', $dataFinal);
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
             header("Content-Disposition: attachment; filename=items_$format.xls");
         }
         //Config
         $this->db->select('*');
         $this->db->from('config');
         $config = $this->db->get()->row();
         $config_iso = $this->db->get('config_iso')->row();
 
         $this->db->select('a.*, b.name as item_category_name , c.name as item_familys_name , d.name as item_family_sub_name');
         $this->db->from('items a');
         $this->db->join('item_categories b', 'a.item_category_number = b.number');
         $this->db->join('item_familys c', 'a.item_family_number = c.number');
         $this->db->join('item_family_subs d', 'a.item_family_sub_number = d.number','left');
         $this->db->where('a.deleted', 0);
         $this->db->order_by('id', 'ASC');
         $records = $this->db->get()->result_array();
         
         $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#items {border-collapse: collapse;width: 100%;font-size: 12px;}#items td, #items th {border: 1px solid #ddd;padding: 2px;}#items tr:nth-child(even){background-color: #f2f2f2;}#items tr:hover {background-color: #ddd;}#items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
         <center>
             <div style="float: left; font-size: 12px; text-align: left;">
                 <table style="width: 100%;">
                     <tr>
                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                             <img src="' . $config->favicon . '" width="30">
                         </td>
                         <td style="font-size: 14px; text-align: left; margin:2px;">
                             <b>' . $config->name . '</b><br>
                             <small>MASTER ITEMS</small>
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
         
         <table id="items" border="1">
             <tr>
                 <th width="20">No</th>
                 <th>Id</th>
                 <th>Part No</th>
                 <th>Part Name</th>
                 <th>Specification</th>
                 <th>Product Type</th>
                 <th>Category</th>
                 <th>Product Family</th>
                 <th>Product Sub Family</th>
                 <th>Unit Of Measure</th>
                 <th>Lead Time Production</th>
                 <th>Weight (gr)</th>
                 <th>MPQ</th>
                 <th>MOQ</th>
                 <th>Safety Stock (%)</th>
                 <th>Lot</th>
                 <th>Lifetime</th>
                 <th>Min</th>
                 <th>Max</th>
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
                         <td>' . $data['type'] . '</td>
                         <td>' . $data['item_category_name'] . '</td>
                         <td>' . $data['item_familys_name'] . '</td>
                         <td>' . $data['item_family_sub_name'] . '</td>
                         <td>' . $data['uom'] . '</td>
                         <td>' . $data['leadtime'] . '</td>
                         <td>' . $data['weight'] . '</td>
                         <td>' . $data['mpq'] . '</td>
                         <td>' . $data['moq'] . '</td>
                         <td>' . $data['safety_stock'] . '</td>
                         <td>' . $data['lot'] . '</td>
                         <td>' . $data['lifetime'] . '</td>
                         <td>' . $data['min'] . '</td>
                         <td>' . $data['max'] . '</td>
                         <td>' . $data['status'] . '</td>
                     </tr>';
             $no++;
         }
         $html .= '</table></body></html>';
         echo $html;
     }
 }
