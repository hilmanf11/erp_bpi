<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Standard_price_fg extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/standard_price_fg');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('standard_price_fg', ["item_fg_id" => $post]);
        echo json_encode($send);
    }

    public function readItems($division)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name 
        FROM item_fg a
        JOIN item_familys b ON a.item_family_id = b.id
        WHERE a.division_id like '$division' and (a.number like '%$post%' or a.number_customer like '%$post%' or a.name like '%$post%')");
        echo json_encode($send);
    }

    public function checkYear()
    {
        $this->db->select_max('end_date');
        $query = $this->db->get('standard_price_fg')->row();

        if ($query && $query->end_date) {
            $maxYear = date('Y', strtotime($query->end_date));
            $currentYear = date('Y');

            if ($maxYear != $currentYear) {
                $response = [
                    'show' => true,
                    'message' => "Attention!, Please Update Price."
                ];
            } else {
                $response = ['show' => false];
            }
        } 

        echo json_encode($response);
    }

    public function number($date)
    {
        $decodedDate = base64_decode($date);
        $yearShort = date("y", strtotime($decodedDate)); // ambil 2 digit tahun
        $prefix = "SP-FG" . $yearShort;

        // Ambil nomor terbesar berdasarkan prefix & divisi
        $this->db->select('MAX(`number`) as kode');
        $this->db->from('standard_price_fg');
        $this->db->like('number', $prefix); // nomor yang mengandung SP-FG + 2 digit tahun
        $query = $this->db->get();
        $result = $query->row();

        if ($result->kode == NULL) {
            $autoID = "01";
        } else {
            $lastNumber = (int) substr($result->kode, -2); // ambil 2 digit terakhir
            $lastNumber++;
            $autoID = sprintf("%02d", $lastNumber);
        }

        echo $prefix . $autoID;
    }

     //GET DATA
     public function readNumber()
     {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('standard_price_fg', ["number" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_start_date = @base64_decode($get['filter_start_date']);
            $filter_end_date = @base64_decode($get['filter_end_date']);
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
            $filter_number = @base64_decode($get['filter_number']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as division');
            $this->db->from('standard_price_fg a');
            $this->db->join('divisions b', 'a.division_id = b.id');
            // $this->db->like('a.item_fg_id', $filter_item_fg_id);
            // $this->db->like('a.number', $filter_number);
            if (!empty($filter_start_date) && !empty($filter_end_date)) {
                $this->db->where('a.start_date <=', $filter_end_date); 
                $this->db->where('a.end_date >=', $filter_start_date);
            }

            if (!empty($filter_number)) {
                $this->db->where('a.number', $filter_number);
            }
            if (!empty($filter_item_fg_id)) {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }
            $this->db->group_by('a.number, a.start_date, a.end_date');
            $this->db->order_by('a.created_date', 'DESC');
            // $this->db->order_by('b.number', 'ASC');
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
            $filter_item_fg_id = base64_decode($this->input->get('filter_item_fg_id'));

            $this->db->select('a.*, b.number as division, "Finished Good" as category');
            $this->db->from('standard_price_fg a');
            $this->db->join('divisions b', 'a.division_id = b.id');
            $this->db->where('a.number', $number);
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLE HISTORY PRICE
    public function datatableHistories()
    {
        if ($this->input->get()) {
            $item_fg_id = base64_decode($this->input->get('item_fg_id'));

            $this->db->select('*');
            $this->db->from('standard_price_fg_histories');
            $this->db->where('item_fg_id', $item_fg_id);
            $this->db->order_by('created_date', 'DESC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // UPDATE DATA
    public function datatableUpdates()
    {
        $number = base64_decode($this->input->get('number'));

        if ($this->input->get()) {
            // $this->db->select('a.*,c.number as item_rm_number, c.name as item_rm_name, c.uom, d.name as item_family_name');
            $this->db->select('a.*, b.number as division, "Finished Good" as category');
            $this->db->from('standard_price_fg a');
            $this->db->join('divisions b', 'a.division_id = b.id');
            $this->db->where('a.number', $number);
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $standard_price_fg = $this->crud->read("standard_price_fg", [], ["number" => $post['number'], "item_fg_id" => $post['item_fg_id'], "start_date" => $post['start_date'], "end_date" => $post['end_date']]);
            $dataFinal = array(
                //field
                "price" => $post['price'],
                "currency" => $post['currency'],
                "remarks" => $post['remarks'],
            );
            
            if ($standard_price_fg) {
                $send = $this->crud->update('standard_price_fg', ["number" => $post['number'], "item_fg_id" => $post['item_fg_id'], "start_date" => $post['start_date'], "end_date" => $post['end_date']], $dataFinal);
                $send2 = $this->crud->create('standard_price_fg_histories', $post);
            } else {
                $send = $this->crud->create('standard_price_fg', $post);
                $send2 = $this->crud->create('standard_price_fg_histories', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

   //DELETE DATA
   public function delete()
   {
       $data = $this->input->post();
       $send = $this->crud->delete('standard_price_fg', $data);
       $send2 = $this->crud->delete('standard_price_fg_histories', $data);
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
                 'number' => $data->val($i, 2),
                 'start_date' => $data->val($i, 3),
                 'end_date' => $data->val($i, 4),
                 'item_fg_id' => $data->val($i, 5),
                 'price' => $data->val($i, 6),
                 'currency' => $data->val($i, 7),
                 'remarks' => $data->val($i, 8),
             );
         }
         $datas['total'] = count($datas);
         echo json_encode($datas);
         unlink($_FILES['file_upload']['name']);
     }

  //UPLOAD CLEAR CACHE
  public function uploadclearFailed()
  {
      @unlink('excel/failed/standard_price_fg.txt');
  }

  //UPLOAD CREATE FAILED
  public function uploadcreateFailed()
  {
      if ($this->input->post()) {
          $message = $this->input->post('message');
          $textFailed = fopen('excel/failed/standard_price_fg.txt', 'a');
          fwrite($textFailed, $message . "\n");
          fclose($textFailed);
      }
  }

  //UPLOAD DOWNLOAD FAILED
  public function uploadDownloadFailed()
  {
      $file = "excel/failed/standard_price_fg.txt";
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
           //Cek Process Number
           $item_fg_id = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);
           $item_family = $this->crud->read('item_familys', [], ["id" => $item_fg_id->item_family_id]);
           $standard_price_fg = $this->crud->read('standard_price_fg', [], ["item_fg_id" => $data['item_fg_id']]);

           if (empty($item_fg_id->id)) {
               echo json_encode(array("title" => "Not Found", "message" => " Item id " . $data['item_fg_id'] . " is Not Found", "theme" => "error"));
           }else if (!empty($standard_price_fg->item_fg_id)) {
               $send  = $this->db->update('standard_price_fg',["price" => $data['price'],"start_date" => $data['start_date'],"end_date" => $data['end_date'],"remarks" => $data['remarks']], ["number" => $data['number'], "item_fg_id" => $data['item_fg_id'], "start_date" => $data['start_date'], "end_date" => $data['end_date']]);
               
               $dataFinal = array(
                   //field
                   "number" => $data['number'],
                   "start_date" => $data['start_date'],
                   "end_date" => $data['end_date'],
                   "item_fg_id" => $data['item_fg_id'],
                   "item_fg_number" => $item_fg_id->number,
                   "item_fg_name" => $item_fg_id->name,
                   "uom" => $item_fg_id->uom,
                   "division_id" => $item_fg_id->division_id,
                   "category" => "Finished Good",
                   "item_family_id" => $item_fg_id->item_family_id,
                   "item_family_name" => $item_family->name,
                   "currency" => $data['currency'],
                   "price" => $data['price'],
                   "remarks" => $data['remarks'],
               );

               $send2 = $this->crud->createNotLog('standard_price_fg_histories', $dataFinal);
               echo json_encode(array("title" => "Update", "message" => " Product No " . $item_fg_id->number . " Data Updated", "theme" => "success"));
           } else {
               $dataFinal = array(
                   //field
                   "number" => $data['number'],
                   "start_date" => $data['start_date'],
                   "end_date" => $data['end_date'],
                   "item_fg_id" => $data['item_fg_id'],
                   "item_fg_number" => $item_fg_id->number,
                   "item_fg_name" => $item_fg_id->name,
                   "uom" => $item_fg_id->uom,
                   "division_id" => $item_fg_id->division_id,
                   "category" => "Finished Good",
                   "item_family_id" => $item_fg_id->item_family_id,
                   "item_family_name" => $item_family->name,
                   "currency" => $data['currency'],
                   "price" => $data['price'],
                   "remarks" => $data['remarks'],
               );
               $send   = $this->crud->create('standard_price_fg', $dataFinal);
               $send2 = $this->crud->create('standard_price_fg_histories', $dataFinal);
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
            header("Content-Disposition: attachment; filename=standard_price_fg_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as division');
        $this->db->from('standard_price_fg a');
        $this->db->join('divisions b', 'a.division_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.created_date', 'desc');
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
                            <small>STANDARD PRICE FG</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Number</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Product Id</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Uom</th>
                <th>Division</th>
                <th>Category</th>
                <th>Product Family</th>
                <th>Currency</th>
                <th>Price</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['start_date'] . '</td>
                    <td>' . $data['end_date'] . '</td>
                    <td>' . $data['item_fg_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_fg_name'] . '</td>
                    <td>' . $data['uom'] . '</td>
                    <td>' . $data['division'] . '</td>
                    <td>' . $data['category'] . '</td>
                    <td>' . $data['item_family_name'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . $data['price'] . '</td>
                    <td>' . $data['remarks'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
