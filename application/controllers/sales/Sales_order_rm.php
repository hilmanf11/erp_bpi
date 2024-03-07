<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Sales_order_rm extends CI_Controller
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
        $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[20]|is_unique[customer_items.customer_id]');
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[customer_items.item_fg_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/sales_order_rm');
        } else {
            redirect('error_access');
        }
    }

    public function readItemFg($customer_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, b.number_customer, a.price, c.currency, b.uom, COALESCE(SUM(d.qty_del), 0) as delivery
            FROM customer_items a 
            JOIN item_fg b ON a.item_fg_id = b.id and b.type = 'RM'
            JOIN customers c ON a.customer_id = c.id
            LEFT JOIN delivery_orders d ON b.id = d.item_fg_id and d.status = 0 and c.id = d.customer_id
            WHERE a.customer_id = '$customer_id' and (b.number LIKE '%$post%' or b.name LIKE '%$post%') GROUP BY b.number");
        echo json_encode($send);
    }

    public function readItems($customer_id, $sales_order_no)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, a.qty
            FROM sales_order_rm a 
            JOIN item_fg b ON a.item_fg_id = b.id
            JOIN customers c ON a.customer_id = c.id
            WHERE a.customer_id = '$customer_id' and a.sales_order_no = '$sales_order_no' and a.status = 0 and (b.number LIKE '%$post%' or b.name LIKE '%$post%') ");
        echo json_encode($send);
    }

    public function readSalesOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT sales_order_no, sales_order_date FROM sales_order_rm WHERE customer_id = '$customer_id' and status = 0");
        echo json_encode($send);
    }

    public function readCustomerOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM sales_order_rm WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function number($customer_id, $sales_order_date)
    {
        $datenow    = "SO" . $customer_id . date("ymd", strtotime(base64_decode($sales_order_date)));
        $sqlGetID   = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_order_rm WHERE `sales_order_no` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        echo $datenow . $autoID;
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
            $filter_status = @base64_decode($get['filter_status']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, b.name as customer_name");
            $this->db->from('sales_order_rm a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.sales_order_date >=', $filter_from);
                $this->db->where('a.sales_order_date <=', $filter_to);
            }
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.sales_order_no', $filter_sales_order_no);
            $this->db->like('a.status', $filter_status);
            $this->db->group_by('a.sales_order_no');
            $this->db->order_by('a.status', 'ASC');
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
            $sales_order_no = base64_decode($this->input->get('sales_order_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
            $this->db->from('sales_order_rm a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.sales_order_no', $sales_order_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $sales_order_no = base64_decode($this->input->get('sales_order_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
            $this->db->from('sales_order_rm a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.sales_order_no', $sales_order_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $sales_order_rm = $this->crud->read("sales_order_rm", [], ["sales_order_no" => $post['sales_order_no'], "item_fg_id" => $post['item_fg_id']]);
            if (@$sales_order_rm->sales_order_no != "") {
                $send = $this->crud->update('sales_order_rm', ["sales_order_no" => $post['sales_order_no'], "item_fg_id" => $post['item_fg_id']], $post);
            } else {
                $send = $this->crud->create('sales_order_rm', $post);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function uploadatt()
    {
        // Pastikan file disimpan dalam direktori yang diinginkan
        $uploadDir = 'assets/image/sales_order_rm/';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Pastikan ada file yang diunggah dari permintaan
            if (isset($_FILES['file'])) {
                $file = $_FILES['file'];

                // Validasi ekstensi file yang diunggah
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($fileExtension, $allowedExtensions)) {
                    echo json_encode(['success' => false, 'message' => 'Only files with the extension .pdf, .jpg, or .png are allowed.']);
                    exit; // Menghentikan proses lebih lanjut jika ekstensi tidak valid
                }

                // Validasi ukuran file yang diunggah (maksimal 5MB)
                $maxFileSize = 2 * 1024 * 1024; // 5MB dalam bytes
                if ($file['size'] > $maxFileSize) {
                    echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB yang diperbolehkan.']);
                    exit; // Menghentikan proses lebih lanjut jika ukuran terlalu besar
                }

                // Pastikan tidak ada error dalam proses upload
                if ($file['error'] === UPLOAD_ERR_OK) {
                    // Buat nama unik untuk file yang diunggah
                    $fileName = uniqid() . '_' . $file['name'];
                    $uploadPath = $uploadDir . $fileName;

                    // Pindahkan file dari temporary directory ke lokasi yang diinginkan
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        // File berhasil diunggah
                        echo json_encode(['success' => true, 'message' => 'File Upload Success.', 'filename' => $fileName]);
                    } else {
                        // Gagal menyimpan file
                        echo json_encode(['success' => false, 'message' => 'File Upload Failed.']);
                    }
                } else {
                    // Ada error dalam proses upload
                    echo json_encode(['success' => false, 'message' => 'Error while Upload.']);
                }
            } else {
                // File tidak ditemukan dalam permintaan
                echo json_encode(['success' => false, 'message' => 'File Not Found.']);
            }
        } else {
            // Metode request yang diperlukan adalah POST
            echo json_encode(['success' => false, 'message' => 'Metode request yang diperlukan adalah POST.']);
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('sales_order_rm', $data);
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

        $customer_id = $data->val(2, 3);
        $sales_order_date = $data->val(3, 3);

        $datenow    = "SO" . $customer_id . date("ymd", strtotime($sales_order_date));
        $sqlGetID   = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_order_rm WHERE `sales_order_no` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }        

        $sales_order_no = $datenow . $autoID;

        $total_sub = 0;
         for ($i = 7; $i <= $total_row; $i++) {
            $item_fg_number = $data->val($i, 3);
            $cust_address = $data->val(2, 5);
            $item_fg = $this->crud->read('item_fg', [], ["number" => $item_fg_number]);
            $cust_add = $this->crud->read('customer_address', [], ["id" => $cust_address]);

            if (!empty($item_fg->number)) {
                $customer_items = $this->crud->read('customer_items', [], ["item_fg_id" => $item_fg->id,"customer_id" => $customer_id]);
                $total = ($data->val($i, 4) * $customer_items->price);
                $datas[] = array(
                    //excel
                    'customer_id' => $customer_id,
                    'sales_order_date' => $data->val(3, 3),
                    'delivery_date' => $data->val(4, 3),
                    'customer_address_id' => $data->val(2, 5),
                    'division' => $data->val(3, 5),
                    'remarks' => $data->val(4, 5),
                    'customer_order_no' => $data->val($i, 2),
                    'item_fg_id' => $item_fg->id,
                    'qty' => $data->val($i, 4),
                    'price' => $customer_items->price,
                    'sales_order_no' => $sales_order_no,
                    "total" => $total,
                    'uom' => $item_fg->uom,
                    'plant' => $cust_add->plant,
                    'department' => $cust_add->department,
                    'attention_to' => $cust_add->contact_person,
                
                );
                $total_sub += $total;
            }
         }

         $datas['total_sub'] = $total_sub;
         $datas['total'] = count($datas);
         echo json_encode($datas);
         unlink($_FILES['file_upload']['name']);
    }
     public function uploadclearFailed()
     {
         @unlink('failed/sales_order_rm.txt');
     }
     public function uploadcreateFailed()
     {
         if ($this->input->post()) {
             $message = $this->input->post('message');
             $textFailed = fopen('failed/sales_order_rm.txt', 'a');
             fwrite($textFailed, $message . "\n");
             fclose($textFailed);
         }
     }
 
     //UPLOAD DOWNLOAD FAILED
     public function uploadDownloadFailed()
     {
         $file = "failed/sales_order_rm.txt";
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
            $total_sub = $this->input->post('total_sub');

            //Cek Process Number                //table             //field           //field excel
            $customers = $this->crud->read('customers', [], ["id" => $data['customer_id']]);
            $customer_address = $this->crud->read('customer_address', [], ["id" => $data['customer_address_id'],"customer_id" => $data['customer_id']]);

            if (empty($customers->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customers ID " . $data['customer_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($customer_address->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customers Address ID " . $data['customer_address_id'] . " Not Found in Customers ID ". $data['customer_id'] . "", "theme" => "error"));
            } else {
                $customer_items = $this->crud->read('customer_items', [], ["item_fg_id" => $data['item_fg_id'],"customer_id" => $data['customer_id']]);
                $sales_order_rm = $this->crud->read('sales_order_rm', [], ["customer_order_no" => $data['customer_order_no'], "item_fg_id" => $data['item_fg_id']]);

                if (!empty($sales_order_rm->sales_order_no )) {
                    echo json_encode(array("title" => "Duplicated", "message" => "Product ID " . $data['item_fg_id'] . " and Customer Order No " . $data['customer_order_no'] . " Duplicated", "theme" => "error"));
                } else {
                    $dataFinal = array(
                        //field        //excel
                        "customer_id" => $data['customer_id'],
                        "sales_order_date" => $data['sales_order_date'],
                        "sales_order_no" => $data['sales_order_no'],
                        "delivery_date" => $data['delivery_date'],
                        "customer_address_id" => $data['customer_address_id'],
                        "plant" => $data['plant'],
                        "attention_to" => $data['attention_to'],
                        "department" => $data['department'],
                        "remarks" => $data['remarks'],
                        "division" => $data['division'],
                        "customer_order_no" => $data['customer_order_no'],
                        "item_fg_id" => $data['item_fg_id'],
                        "qty" => $data['qty'],
                        "uom" => $data['uom'],
                        "currency" => $customers->currency,
                        "price" => $data['price'],
                        "total" => $data['total'],
                        "total_sub" => $total_sub,
                        "total_tax" => ($total_sub * ($customers->taxes / 100)),
                        "total_pph" => 0,
                        "total_grand" => ($total_sub + ($total_sub * ($customers->taxes / 100))),
                        
                    );
                    $send   = $this->crud->create('sales_order_rm', $dataFinal);
                    echo $send;
                }
             }
         }
     }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_order_rm_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
        $filter_status = @base64_decode($get['filter_status']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.name as customer_name, c.number as item_fg_number, c.name as item_fg_name");
        $this->db->from('sales_order_rm a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.sales_order_date >=', $filter_from);
            $this->db->where('a.sales_order_date <=', $filter_to);
        }
        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.sales_order_no', $filter_sales_order_no);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.sales_order_no', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customer_items {border-collapse: collapse;width: 100%;font-size: 12px;}#customer_items td, #customer_items th {border: 1px solid #ddd;padding: 2px;}#customer_items tr:nth-child(even){background-color: #f2f2f2;}#customer_items tr:hover {background-color: #ddd;}#customer_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>' . $config->description . '</small>
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
                <h3>SALES ORDER</h3>
            </div>
        </center>
        
        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer Name</th>
                <th>Customer Order No</th>
                <th>Sales Order No</th>
                <th>Sales Order Date</th>
                <th>Division</th>
                <th>Delivery Date</th>
                <th>Remarks</th>
                <th>Product ID</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Uom</th>
                <th>Qty</th>
                <th>Delivery</th>
                <th>Outstanding</th>
                <th>Currency</th>
                <th>Price</th>
                <th>Total</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['customer_order_no'] . '</td>
                        <td>' . $data['sales_order_no'] . '</td>
                        <td>' . $data['sales_order_date'] . '</td>
                        <td>' . $data['division'] . '</td>
                        <td>' . $data['delivery_date'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['item_fg_id'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['delivery'] . '</td>
                        <td>' . $data['outstanding'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . $data['price'] . '</td>
                        <td>' . $data['total'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
