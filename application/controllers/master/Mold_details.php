<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Mold_details extends CI_Controller
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
        $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[20]|is_unique[mold_details.customer_id]');
        
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/mold_details');
        } else {
            redirect('error_access');
        }
    }
     //GET DATA
    public function reads($customer_id)
    {
        $customer_id = base64_decode($customer_id);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, a.price FROM mold_details a 
            JOIN item_fg b ON a.item_id = b.id 
            WHERE a.customer_id = '$customer_id' and (b.number LIKE '%$post%' or b.name LIKE '%$post%')");
        echo json_encode($send);
    }

     //GET DATATABLES
     public function datatables()
     {
         if ($this->input->post()) {
            $get = $this->input->get();
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_item_id = @base64_decode($get['filter_item_id']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
             //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
             //Select Query
            $this->db->select('b.number as customers_number, b.currency, b.name as customer_name, b.type, b.id as customer_id, b.status, a.created_by, a.created_date, a.updated_by, a.updated_date');
            $this->db->from('mold_details a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.item_id', $filter_item_id);
            $this->db->group_by('b.name');
            $this->db->order_by('b.id', 'ASC');
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

     //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $number = base64_decode($this->input->get('number'));
            $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));

            $this->db->select('a.*, c.number as item_number, c.name as item_name, d.name as mold_name, d.type as mold_type, d.model as mold_model, d.standard as standard_cavity, d.project_year as project_year');
            $this->db->from('mold_details a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('item_fg c', 'a.item_id = c.id');
            $this->db->join('setting_molds e', 'a.item_id = e.item_fg_id');
            $this->db->join('molds d', 'e.mold_id = d.id');
            $this->db->where('b.number', $number);
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();


            echo json_encode($records);
        }
    }

     // GET DATATABLES UPDATE
     public function datatableUpdates()
     {
         if ($this->input->get()) {
             $customer_id = base64_decode($this->input->get('customer_id'));

            $this->db->select('a.*,  c.number as item_number, c.name as item_name');
            $this->db->from('mold_details a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('item_fg c', 'a.item_id = c.id');
            $this->db->where('a.customer_id', $customer_id);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();
 
             echo json_encode($records);
         }
     }

    // public function uploadatt()
    // {
    //     // Pastikan file disimpan dalam direktori yang diinginkan
    //     $uploadDir = 'assets/image/mold_details/';

    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         // Pastikan ada file yang diunggah dari permintaan
    //         if (isset($_FILES['file'])) {
    //             $file = $_FILES['file'];

    //             // Validasi ekstensi file yang diunggah
    //             $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
    //             $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    //             if (!in_array($fileExtension, $allowedExtensions)) {
    //                 echo json_encode(['success' => false, 'message' => 'Only files with the extension .pdf, .jpg, or .png are allowed.']);
    //                 exit; // Menghentikan proses lebih lanjut jika ekstensi tidak valid
    //             }

    //             // Validasi ukuran file yang diunggah (maksimal 5MB)
    //             $maxFileSize = 2 * 1024 * 1024; // 5MB dalam bytes
    //             if ($file['size'] > $maxFileSize) {
    //                 echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB yang diperbolehkan.']);
    //                 exit; // Menghentikan proses lebih lanjut jika ukuran terlalu besar
    //             }

    //             // Pastikan tidak ada error dalam proses upload
    //             if ($file['error'] === UPLOAD_ERR_OK) {
    //                 // Buat nama unik untuk file yang diunggah
    //                 $fileName = uniqid() . '_' . $file['name'];
    //                 $uploadPath = $uploadDir . $fileName;

    //                 // Pindahkan file dari temporary directory ke lokasi yang diinginkan
    //                 if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    //                     // File berhasil diunggah
    //                     echo json_encode(['success' => true, 'message' => 'File Upload Success.', 'filename' => $fileName]);
    //                 } else {
    //                     // Gagal menyimpan file
    //                     echo json_encode(['success' => false, 'message' => 'File Upload Failed.']);
    //                 }
    //             } else {
    //                 // Ada error dalam proses upload
    //                 echo json_encode(['success' => false, 'message' => 'Error while Upload.']);
    //             }
    //         } else {
    //             // File tidak ditemukan dalam permintaan
    //             echo json_encode(['success' => false, 'message' => 'File Not Found.']);
    //         }
    //     } else {
    //         // Metode request yang diperlukan adalah POST
    //         echo json_encode(['success' => false, 'message' => 'Metode request yang diperlukan adalah POST.']);
    //     }
    // }


    
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $mold_details = $this->crud->read("mold_details", [], ["customer_id" => $post['customer_id'], "item_id" => $post['item_id']]);
            $item_fg = $this->crud->read('item_fg', [], ["id" => $post['item_id']]);
            $customers = $this->crud->read('customers', [], ["id" => $post['customer_id']]);

            if (!empty($mold_details)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Customer Name " . $customers->name . " and Product No " . $item_fg->number . " has been inputed please Update previous Data", "theme" => "error"));
            } else {
                $send = $this->crud->create('mold_details', $post);
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
        $send = $this->crud->delete('mold_details', $data);
        echo $send;
    }
    

      //UPDATE DATA
      public function update()
      {
          if ($this->input->post()) {
                  $post = $this->input->post();
                  $id   = $post['id'];

                  $datas = array(
                    'customer_id' => $post['customer_id'],
                    'item_id' => $post['item_id'],
                    'price' => $post['price'],
                    'depreciation' => $post['depreciation'],
                    'qty_depreciation' => $post['qty_depreciation'],
                    'product_price' => $post['product_price'],
                    'tooling_price' => $post['tooling_price'],
                    'total_price' => $post['total_price'],
                    'status' => $post['status']
                );

                  $mold_details = $this->crud->reads("mold_details", [], $datas);


                if(count(@$mold_details) > 0){
                    show_error("Data tidak ada Perubahan");
                }else{
                    $send = $this->crud->update('mold_details', ["id" => $id], $post);
                    echo $send;
                }
              } else {
                show_error("Cannot Process your request");
              
          }
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
                 'customer_id' => $data->val($i, 2),
                 'product_no' => $data->val($i, 3),
                 'price' => $data->val($i, 4),
                 'depreciation' => $data->val($i, 5),
                 'qty_depreciation' => $data->val($i, 6),
                 'product_price' => $data->val($i, 7),
                 'tooling_price' => $data->val($i, 8),
                 'status' => $data->val($i, 9)
                 
             );
         }
         $datas['total'] = count($datas);
         echo json_encode($datas);
         unlink($_FILES['file_upload']['name']);
     }
     public function uploadclearFailed()
     {
         @unlink('failed/mold_details.txt');
     }
     public function uploadcreateFailed()
     {
         if ($this->input->post()) {
             $message = $this->input->post('message');
             $textFailed = fopen('failed/mold_details.txt', 'a');
             fwrite($textFailed, $message . "\n");
             fclose($textFailed);
         }
     }
 
     //UPLOAD DOWNLOAD FAILED
     public function uploadDownloadFailed()
     {
         $file = "failed/mold_details.txt";
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

             //Cek Process Number     //table        //field           //field excel
             $item = $this->crud->read('item_fg', [], ["id" => $data['product_no']]);
             $customer = $this->crud->read('customers', [], ["number" => $data['customer_id']]);
             $setting_molds = $this->crud->read('setting_molds',[],["item_fg_id"=> $data['product_no']]);
             $mold_details = $this->crud->read('mold_details', [], ["item_id" => @$item->id, "customer_id" => $data['customer_id']] );

            if (empty($item->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product ID " . $data['product_no'] . " Not Found", "theme" => "error"));
            } elseif (empty($customer->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customer " . $data['customer_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($setting_molds->item_fg_id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product ID " . $data['product_no'] . " Not Found in Setting Molds", "theme" => "error"));
            } elseif (!empty($mold_details->item_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['product_no'] . " Duplicate Data", "theme" => "error"));
            } else {
                 $dataFinal = array(
                     //field        //excel
                     "customer_id" => $customer->id,
                     "item_id" => $data['product_no'],
                     "price" => $data['price'],
                     "depreciation" => $data['depreciation'],
                     "qty_depreciation" => $data['qty_depreciation'],
                     "product_price" => $data['product_price'],
                     "tooling_price" => $data['tooling_price'],
                     "total_price" => ($data['tooling_price'] + $data['tooling_price']) ,
                     "status" => $data['status'],
                 );
                 $send= $this->crud->create('mold_details', $dataFinal);
                 echo $send;
             }
         }
    }
 
     //PRINT & EXCEL DATA
     public function print($option = "")
     {
        if ($option == "excel") {
            $format = date("Ymd");
            header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header("Content-Disposition: attachment; filename=mold_details_$format.xls");
         }

         $get = $this->input->get();
         $filter_customer_id = @base64_decode($get['filter_customer_id']);
         $filter_item_id = @base64_decode($get['filter_item_id']);

         //Config
         $this->db->select('*');
         $this->db->from('config');
         $config = $this->db->get()->row();
 
         $this->db->select('a.*, b.name as customer_name, b.currency, b.id as customer_id, c.number as item_number, c.name as item_name , d.name as mold_name, d.type as mold_type, d.model as mold_model, d.standard as standard_cavity, d.project_year as project_year');
         $this->db->from('mold_details a');
         $this->db->join('customers b', 'a.customer_id = b.id');
         $this->db->join('item_fg c', 'a.item_id = c.id');
         $this->db->join('setting_molds e', 'a.item_id = e.item_fg_id');
         $this->db->join('molds d', 'e.mold_id = d.id');
         $this->db->like('a.customer_id', $filter_customer_id);
         $this->db->like('a.item_id', $filter_item_id);
         $this->db->order_by('a.customer_id', 'ASC');
         $records = $this->db->get()->result_array();
         $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#mold_details {border-collapse: collapse;width: 100%;font-size: 12px;}#mold_details td, #mold_details th {border: 1px solid #ddd;padding: 2px;}#mold_details tr:nth-child(even){background-color: #f2f2f2;}#mold_details tr:hover {background-color: #ddd;}#mold_details th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                 <h3>MASTER CUSTOMER ITEM</h3>
             </div>
         </center>
         
         <table id="mold_details" border="1">
             <tr>
                 <th width="20">No</th>
                 <th>ID</th>
                 <th>Customer Name</th>
                 <th>Product No</th>
                 <th>Product Name</th>
                 <th>Currency</th>
                 <th>Mold Name</th>
                 <th>Mold Type</th>
                 <th>Model</th>
                 <th>Standard Cavity</th>
                 <th>Project Year</th>
                 <th>Mold Price</th>
                 <th>Depreciation (MONTH)</th>
                 <th>QTY Depreciation</th>
                 <th>Total Delivery</th>
                 <th>Product Price</th>
                 <th>Tooling Price</th>
                 <th>Total Price</th>
                 <th>Status</th>
             </tr>';
         $no = 1;
         foreach ($records as $data) {
            $number = $data['item_number'];
            if (strpos($data['item_number'], '0') === 0 || strpos($data['item_number'], '+') === 0) {
                $number = "'" . $data['item_number'];
            } else {
                // Leave the data unchanged
                $number = $data['item_number'];
            }
             $html .= '<tr>
                         <td>' . $no . '</td>
                         <td>' . $data['customer_id'] . '</td>
                         <td>' . $data['customer_name'] . '</td>
                         <td>' . $number . '</td>
                         <td>' . $data['item_name'] . '</td>
                         <td>' . $data['currency'] . '</td>
                         <td>' . $data['mold_name'] . '</td>
                         <td>' . $data['mold_type'] . '</td>
                         <td>' . $data['mold_model'] . '</td>
                         <td>' . $data['standard_cavity'] . '</td>
                         <td>' . $data['project_year'] . '</td>
                         <td>'. number_format($data['price']) . '</td>
                         <td>' . $data['depreciation'] . '</td>
                         <td>' . $data['qty_depreciation'] . '</td>
                         <td>' . $data['total_delivery'] . '</td>
                         <td>' . $data['product_price'] . '</td>
                         <td>' . $data['tooling_price'] . '</td>
                         <td>' . $data['total_price'] . '</td>
                         <td>' . $data['status'] . '</td>
                     </tr>';
             $no++;
         }
         $html .= '</table></body></html>';
         echo $html;
     }
 }
