<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Sales_orders extends CI_Controller
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
            $this->load->view('sales/sales_orders');
        } else {
            redirect('error_access');
        }
    }

    public function readItemFg($customer_id,$customer_address_id,$division,$type_so)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        if($type_so == 'FG'){
            $divisions = $this->crud->read('divisions', [], ["number" => $division]);
            $division_id = $divisions->id;
            $send = $this->crud->query("SELECT b.id, b.number, b.name, b.number_customer, a.price, a.currency, b.uom, '0' as delivery
                FROM customer_items a 
                JOIN item_fg b ON a.item_fg_id = b.id and b.type = 'FG'
                JOIN customers c ON a.customer_id = c.id
                LEFT JOIN delivery_orders d ON b.id = d.item_fg_id and d.status = 0 and c.id = d.customer_id
                WHERE a.customer_id = '$customer_id' and a.customer_address_id = '$customer_address_id' and a.division_id = '$division_id' and (b.number LIKE '%$post%' or b.name LIKE '%$post%') GROUP BY b.number");
            echo json_encode($send);
        }else{
            $divisions = $this->crud->read('divisions', [], ["number" => $division]);
            $division_id = $divisions->id;
            $send = $this->crud->query("SELECT b.id, b.number, b.name, b.number_customer, a.price, a.currency, b.uom, '0' as delivery
                FROM customer_items a 
                JOIN item_fg b ON a.item_fg_id = b.id and b.type = 'FG' and b.item_family_id = 'P41'
                JOIN customers c ON a.customer_id = c.id
                LEFT JOIN delivery_orders d ON b.id = d.item_fg_id and d.status = 0 and c.id = d.customer_id
                WHERE a.customer_id = '$customer_id' and a.customer_address_id = '$customer_address_id' and a.division_id = '$division_id' and (b.number LIKE '%$post%' or b.name LIKE '%$post%') GROUP BY b.number");
            echo json_encode($send);
        }
       
    }

    public function readPrice($customer_id, $item_fg_id, $customer_address_id, $division)
    {
        $divisions = $this->crud->read('divisions', [], ["number" => $division]);
        $division_id = $divisions->id;
        $item_id = base64_decode($item_fg_id);
        $send = $this->crud->query("SELECT a.price
            FROM customer_items a 
            WHERE a.customer_id = '$customer_id' and a.item_fg_id = '$item_id' and a.customer_address_id = '$customer_address_id' and a.division_id = '$division_id'");
        echo json_encode($send);
    }

    public function readAddress($customer_id, $division)
    {
        $divisions = $this->crud->read('divisions', [], ["number" => $division]);
        $division_id = $divisions->id;
        $send = $this->crud->query("SELECT DISTINCT a.id, a.contact_person, a.address, a.department, a.taxes_plant, a.plant
        FROM customer_address a  
        JOIN customer_items b ON a.id = b.customer_address_id 
        WHERE b.customer_id = '$customer_id' AND b.division_id = '$division_id'");
        echo json_encode($send);
    }

    public function checkSalesInvoice($sales_order_no, $customer_order_no, $item_fg_id)
    {
        $customer_order_no = base64_decode($customer_order_no);
        $item_fg_id        = base64_decode($item_fg_id);

        $this->db->select('1');
        $this->db->from('sales_invoices');
        $this->db->where('sales_order_no', $sales_order_no);
        $this->db->where('customer_order_no', $customer_order_no);
        $this->db->where('item_fg_id', $item_fg_id);
        $this->db->limit(1);

        $query = $this->db->get();

        echo json_encode(['exists' => ($query->num_rows() > 0)]);
    }

    public function readItems($customer_id, $sales_order_no)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, a.qty
            FROM sales_orders a 
            JOIN item_fg b ON a.item_fg_id = b.id
            JOIN customers c ON a.customer_id = c.id
            WHERE a.customer_id = '$customer_id' and a.sales_order_no = '$sales_order_no' and a.status = 0 and (b.number LIKE '%$post%' or b.name LIKE '%$post%') ");
        echo json_encode($send);
    }

    public function readSalesOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT sales_order_no, sales_order_date FROM sales_orders WHERE customer_id = '$customer_id' and status = 0");
        echo json_encode($send);
    }

    public function readSalesOrders()
    {
        $send = $this->crud->query("SELECT DISTINCT sales_order_no, sales_order_date FROM sales_orders WHERE `status` = 0");
        echo json_encode($send);
    }

    public function readCustomerOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM sales_orders WHERE customer_id = '$customer_id' ORDER BY created_date DESC");
        echo json_encode($send);
    }

    public function readCustomerOrders()
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM sales_orders WHERE `status` = 0 ORDER BY created_date DESC");
        echo json_encode($send);
    }

    public function getTaxes($customer_id, $plant)
    {
        $plants = base64_decode($plant);
        $send = $this->crud->query("SELECT DISTINCT taxes_plant as taxes FROM customer_address WHERE customer_id = '$customer_id' and plant = '$plants'");

        // Asumsikan bahwa $send adalah array, kita kembalikan objek pertama jika ada hasil
        if ($send) {
            echo json_encode([
                'success' => true,
                'taxes' => $send[0]->taxes
            ]);
        } else {
            // Jika tidak ada data yang ditemukan
            echo json_encode([
                'success' => false,
                'message' => 'No taxes found for the given customer ID'
            ]);
        }
    }

    public function check_customer_order_no()
    {
        $customer_order_no = base64_decode($this->input->get('customer_order_no'));
        
        // Menggunakan query builder CodeIgniter
        $this->db->select('customer_order_no');
        $this->db->from('sales_orders');
        $this->db->where('customer_order_no', $customer_order_no);
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            echo json_encode(['exists' => true]);
        } else {
            echo json_encode(['exists' => false]);
        }
    }

    public function number($customer_id, $sales_order_date)
    {
        $datenow    = "SO" . $customer_id . date("ymd", strtotime(base64_decode($sales_order_date)));
        $sqlGetID   = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_orders WHERE `sales_order_no` like '%$datenow%'");
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
            $filter_delivery_date_to = @base64_decode($get['filter_delivery_date_to']);
            $filter_delivery_date_from = @base64_decode($get['filter_delivery_date_from']);
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
            $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
            $filter_status = @base64_decode($get['filter_status']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);
            $filter_division = @base64_decode($get['filter_division']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            // $this->db->select("a.*, b.name as customer_name, c.plant as address_plant,
            $this->db->select("a.*, b.name as customer_name,
            (CASE 
                WHEN c.plant IS NULL THEN a.plant 
                ELSE c.plant 
            END) AS address_plant,
            d.total_status_open, 
            e.total_status_close, 
            (SELECT COUNT(*) FROM sales_orders so2 WHERE so2.sales_order_no = a.sales_order_no) as total_status,
            (CASE 
                WHEN d.total_status_open = (SELECT COUNT(*) FROM sales_orders so2 WHERE so2.sales_order_no = a.sales_order_no) THEN '0'
                WHEN e.total_status_close = (SELECT COUNT(*) FROM sales_orders so2 WHERE so2.sales_order_no = a.sales_order_no) THEN '1'
                WHEN d.total_status_open >= 1 THEN '0'
                WHEN e.total_status_close >= 1 THEN '1'
                ELSE '0'
            END) as status2");
            $this->db->from('sales_orders a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('customer_address c', 'a.customer_address_id = c.id','left');
            $this->db->join('(SELECT sales_order_no, COUNT(status) as total_status_close FROM sales_orders WHERE status = 1 GROUP BY sales_order_no) e', 'a.sales_order_no = e.sales_order_no', 'left');
            $this->db->join('(SELECT sales_order_no, COUNT(status) as total_status_open FROM sales_orders WHERE status = 0 GROUP BY sales_order_no) d', 'a.sales_order_no = d.sales_order_no', 'left');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.sales_order_date >=', $filter_from);
                $this->db->where('a.sales_order_date <=', $filter_to);
            }

            if ($filter_delivery_date_from != "" && $filter_delivery_date_to != "") {
                $this->db->where('a.delivery_date >=', $filter_delivery_date_from);
                $this->db->where('a.delivery_date <=', $filter_delivery_date_to);
            }

            if ($filter_status !== "") {
                $this->db->having('status2', $filter_status);
            }

            if ($filter_division != "") {
                $this->db->where('a.division', $filter_division);
            }

            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->like('a.sales_order_no', $filter_sales_order_no);
            // $this->db->like('a.status', $filter_status);
            $this->db->like('a.customer_order_no', $filter_customer_order_no);
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

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, COALESCE(c.qty,0) as delivery, a.qty - COALESCE(c.qty, 0) as outstanding');
            $this->db->from('sales_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            // $this->db->join('delivery_notes c', 'a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id', 'left');
            $this->db->join("(SELECT item_fg_id, sales_order_no, COALESCE(SUM(qty),0) as qty FROM delivery_notes GROUP BY item_fg_id, sales_order_no ) c",'a.sales_order_no = c.sales_order_no and a.item_fg_id = c.item_fg_id','left');
            $this->db->where('a.sales_order_no', $sales_order_no);
            $this->db->order_by('b.number', 'ASC');
            $this->db->group_by('b.id');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $sales_order_no = base64_decode($this->input->get('sales_order_no'));

            $this->db->select('a.item_fg_id, 
            a.id, 
            a.uom, 
            a.qty, 
            a.delivery, 
            a.outstanding, 
            a.price, 
            a.total, 
            a.njo_number, 
            b.number as item_fg_number, 
            b.name as item_fg_name, 
            c.currency');
            $this->db->from('sales_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customer_items c', 'a.item_fg_id = c.item_fg_id AND a.customer_address_id = c.customer_address_id AND a.customer_id = c.customer_id','left');            
            $this->db->where('a.sales_order_no', $sales_order_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    // public function create()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post();
    //         $sales_order = $this->crud->read("sales_orders", [], ["sales_order_no" => $post['sales_order_no'], "item_fg_id" => $post['item_fg_id']]);

    //             if (@$sales_order->sales_order_no != "") {
    //                 $send = $this->crud->update('sales_orders', ["sales_order_no" => $post['sales_order_no'], "item_fg_id" => $post['item_fg_id']], $post);
    //             } else {
    //                 $send = $this->crud->create('sales_orders', $post);
    //             } 
    //         
    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post();
    //         $sales_orders = $this->crud->reads("sales_orders", [], ["sales_order_no" => $post['sales_order_no'],"customer_order_no" => $post['customer_order_no'],"item_fg_id" => $post['item_fg_id']]);


    //         var_dump($post);
    //         if (count($sales_orders) > 0) {
    //             echo json_encode(array("title" => "Duplicate", "message" => "Duplicate Data", "theme" => "error"));
    //         } else {
    //             $sales_order = $this->crud->read("sales_orders", [], ["sales_order_no" => $post['sales_order_no'],"item_fg_id" => $post['item_fg_id']]);

    //             if ($sales_order) {
    //                 $send = $this->crud->update('sales_orders', ["sales_order_no" => $sales_order->sales_order_no], $post);
    //             } else {
    //                 $send = $this->crud->create('sales_orders', $post);
    //             }

    //             echo ($send);
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            
            $id = isset($post['id']) ? $post['id'] : null;

            $duplicate_orders = $this->crud->reads("sales_orders", [], ["sales_order_no" => $post['sales_order_no'],"customer_order_no" => $post['customer_order_no'],"item_fg_id" => $post['item_fg_id'],
                "id !=" => $id 
            ]);

            if (count($duplicate_orders) > 0) {
                echo json_encode(array("title" => "Duplicate", "message" => "Duplicate Data", "theme" => "error"));
            } else {
                if ($id) {
                    $send = $this->crud->update('sales_orders', ["id" => $id], $post);
                } else {
                    $send = $this->crud->create('sales_orders', $post);
                }

                echo ($send);
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post();
    //         $sales_orders = $this->crud->reads("sales_orders", [], ["customer_order_no" => $post['customer_order_no'],"item_fg_id" => $post['item_fg_id']]);

    //         if(count($sales_orders) > 0){
    //             echo json_encode(array("title" => "Duplicate", "message" => "Duplicate Product No", "theme" => "error"));
    //         }else{
    //             $send = $this->crud->create('sales_orders', $post);
    //             echo $send;
    //         }
            
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    // public function update()
    // {
    //     if ($this->input->post()) {
    //         $id   = base64_decode($this->input->get('id'));
    //         $post = $this->input->post();
    //         $send = $this->crud->update('sales_orders', ["id" => $id], $post);
    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function uploadatt()
    {
        // Pastikan file disimpan dalam direktori yang diinginkan
        $uploadDir = 'assets/image/sales_orders/';

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
    // public function delete()
    // {
    //     $data = $this->input->post();
    //     $sales_order_deliveries = $this->crud->reads("sales_order_deliveries", [], ["sales_order_no" => $data['sales_order_no']]);

    //     if (count($sales_order_deliveries) > 0) {
    //         echo json_encode(array("title" => "Error", "message" => "Cannot be deleted because there is already delivery", "theme" => "error"));
    //     } else {
    //         $send = $this->crud->delete('sales_orders', $data);
    //         echo $send;
    //     }
    // }

    public function delete()
    {
        $data = $this->input->post();
        $sales_order_deliveries = $this->crud->reads("sales_order_deliveries", [], ["sales_order_no" => $data['sales_order_no']]);

        if (count($sales_order_deliveries) > 0) {
            echo json_encode([
                "success" => false,
                "title" => "Error",
                "message" => "Cannot be deleted because there is already delivery"
            ]);
        } else {
            $send = $this->crud->delete('sales_orders', $data);

            if ($send) {
                echo json_encode([
                    "success" => true,
                    "title" => "Success",
                    "message" => "Sales order deleted successfully"
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "title" => "Error",
                    "message" => "Failed to delete sales order"
                ]);
            }
        }
    }


    //UPLOAD DATA
    // public function upload()
    // {
    //     error_reporting(0);
    //     require_once 'assets/vendors/excel_reader2.php';
    //     $target = basename($_FILES['file_upload']['name']);
    //     move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
    //     chmod($_FILES['file_upload']['name'], 0777);
    //     $file = $_FILES['file_upload']['name'];
    //     $data = new Spreadsheet_Excel_Reader($file, false);
    //     $total_row = $data->rowcount($sheet_index = 0);

    //     $customer_id = $data->val(2, 3);
    //     $sales_order_date = $data->val(3, 3);

    //     $datenow    = "SO" . $customer_id . date("ymd", strtotime($sales_order_date));
    //     $sqlGetID   = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_orders WHERE `sales_order_no` like '%$datenow%'");
    //     $rowID      = $sqlGetID->row();
    //     $kode       = $rowID->kode;
    //     if ($kode == NULL) {
    //         $autoID = sprintf("%03s", $kode + 1);
    //     } else {
    //         $urutan = (int) substr($kode, -3);
    //         $urutan++;
    //         $autoID = sprintf("%03s", $urutan);
    //     }        

    //     $sales_order_no = $datenow . $autoID;

    //     $total_sub = 0;
    //      for ($i = 7; $i <= $total_row; $i++) {
    //         $item_fg_number = $data->val($i, 3);
    //         $cust_address = $data->val(2, 5);
    //         $item_fg = $this->crud->read('item_fg', [], ["number" => $item_fg_number]);
    //         $cust_add = $this->crud->read('customer_address', [], ["id" => $cust_address]);

    //         if (!empty($item_fg->number)) {
    //             $customer_items = $this->crud->read('customer_items', [], ["item_fg_id" => $item_fg->id,"customer_id" => $customer_id]);
    //             $total = ($data->val($i, 4) * $customer_items->price);
    //             $datas[] = array(
    //                 //excel
    //                 'customer_id' => $customer_id,
    //                 'sales_order_date' => $data->val(3, 3),
    //                 'delivery_date' => $data->val(4, 3),
    //                 'customer_address_id' => $data->val(2, 5),
    //                 'division' => $data->val(3, 5),
    //                 'remarks' => $data->val(4, 5),
    //                 'customer_order_no' => $data->val($i, 2),
    //                 'item_fg_id' => $item_fg->id,
    //                 'qty' => $data->val($i, 4),
    //                 'price' => $customer_items->price,
    //                 'sales_order_no' => $sales_order_no,
    //                 "total" => $total,
    //                 'uom' => $item_fg->uom,
    //                 'plant' => $cust_add->plant,
    //                 'department' => $cust_add->department,
    //                 'attention_to' => $cust_add->contact_person,
                
    //             );
    //             $total_sub += $total;
    //         }
    //      }

    //      $datas['total_sub'] = $total_sub;
    //      $datas['total'] = count($datas);
    //      echo json_encode($datas);
    //      unlink($_FILES['file_upload']['name']);
    // }

    // public function upload() 
    // {
    //     error_reporting(0);
    //     require_once 'assets/vendors/excel_reader2.php';
    //     $target = basename($_FILES['file_upload']['name']);
    //     move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
    //     chmod($_FILES['file_upload']['name'], 0777);
    //     $file = $_FILES['file_upload']['name'];
    //     $data = new Spreadsheet_Excel_Reader($file, false);
    //     $total_row = $data->rowcount($sheet_index = 0);
    
    //     $total_grouped = []; // Untuk menyimpan total berdasarkan customer_order_no
    //     $grouped_data = [];  // Untuk mengelompokkan data berdasarkan customer_order_no

    //     $customer_id = $data->val(2, 3);
    
    //     for ($i = 7; $i <= $total_row; $i++) {
    //         $item_fg_number = $data->val($i, 3);
    //         $customer_order_no = $data->val($i, 2);
    //         $cust_address = $data->val(2, 5);
    //         $item_fg = $this->crud->read('item_fg', [], ["number" => $item_fg_number]);
    //         $cust_add = $this->crud->read('customer_address', [], ["id" => $cust_address]);
    
    //         if (!empty($item_fg->number)) {
    //             $customer_items = $this->crud->read('customer_items', [], ["item_fg_id" => $item_fg->id, "customer_id" => $customer_id]);
    //             $total = ($data->val($i, 4) * $customer_items->price);
    
    //             // Masukkan data ke dalam kelompok berdasarkan customer_order_no
    //             $grouped_data[$customer_order_no][] = array(
    //                 'customer_id' => $customer_id,
    //                 'sales_order_date' => $data->val(3, 3),
    //                 'delivery_date' => $data->val(4, 3),
    //                 'customer_address_id' => $data->val(2, 5),
    //                 'division' => $data->val(3, 5),
    //                 'remarks' => $data->val(4, 5),
    //                 'customer_order_no' => $customer_order_no,
    //                 'item_fg_id' => $item_fg->id,
    //                 'qty' => $data->val($i, 4),
    //                 'price' => $customer_items->price,
    //                 'uom' => $item_fg->uom,
    //                 'plant' => $cust_add->plant,
    //                 'department' => $cust_add->department,
    //                 'attention_to' => $cust_add->contact_person,
    //                 'total' => $total
    //             );
    
    //             // Hitung subtotal per customer_order_no
    //             if (!isset($total_grouped[$customer_order_no])) {
    //                 $total_grouped[$customer_order_no] = 0;
    //             }
    //             $total_grouped[$customer_order_no] += $total;
    //         }
    //     }
    
    //     // Tambahkan total per kelompok ke dalam hasil akhir
    //     $result = [];
    //     foreach ($grouped_data as $order_no => $items) {
    //         $result[] = [
    //             'customer_order_no' => $order_no,
    //             'sales_order_date' => $data->val(3, 3),
    //             'delivery_date' => $data->val(4, 3),
    //             'total_sub' => $total_grouped[$order_no],
    //             'items' => $items // Semua item dalam kelompok ini
    //         ];
    //     }
    
    //     echo json_encode($result);
    //     unlink($_FILES['file_upload']['name']);
    // }

    // public function upload()
    // {
    //     error_reporting(0);
    //     require_once 'assets/vendors/excel_reader2.php';
    //     $target = basename($_FILES['file_upload']['name']);
    //     move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
    //     chmod($_FILES['file_upload']['name'], 0777);
    //     $file = $_FILES['file_upload']['name'];
    //     $data = new Spreadsheet_Excel_Reader($file, false);
    //     $total_row = $data->rowcount($sheet_index = 0);

    //     $customer_id = $data->val(2, 3);
    //     $sales_order_date = $data->val(3, 3);

    //     $datenow = "SO" . $customer_id . date("ymd", strtotime($sales_order_date));
    //     $sqlGetID = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_orders WHERE `sales_order_no` like '%$datenow%'");
    //     $rowID = $sqlGetID->row();
    //     $kode = $rowID->kode;
    //     $urutan = ($kode == NULL) ? 1 : ((int)substr($kode, -3) + 1);

    //     $datas_grouped = [];
    //     for ($i = 7; $i <= $total_row; $i++) {
    //         $customer_order_no = $data->val($i, 2);
    //         $item_fg_number = $data->val($i, 3);
    //         $cust_address = $data->val(2, 5);
    //         $item_fg = $this->crud->read('item_fg', [], ["number" => $item_fg_number]);
    //         $cust_add = $this->crud->read('customer_address', [], ["id" => $cust_address]);

    //         if (!empty($item_fg->number)) {
    //             if (!isset($datas_grouped[$customer_order_no])) {
    //                 $sales_order_no = $datenow . sprintf("%03s", $urutan++);
    //                 $datas_grouped[$customer_order_no] = [
    //                     'sales_order_no' => $sales_order_no,
    //                     'customer_order_no' => $customer_order_no,
    //                     'sales_order_date' => $data->val(3, 3),
    //                     'delivery_date' => $data->val(4, 3),
    //                     'customer_id' => $customer_id,
    //                     'customer_address_id' => $cust_address,
    //                     'division' => $data->val(3, 5),
    //                     'remarks' => $data->val(4, 5),
    //                     'items' => []
    //                 ];
    //             }

    //             $customer_items = $this->crud->read('customer_items', [], ["item_fg_id" => $item_fg->id, "customer_id" => $customer_id]);
    //             $total = ($data->val($i, 4) * $customer_items->price);
                
    //             $datas_grouped[$customer_order_no]['items'][] = [
    //                 'item_fg_id' => $item_fg->id,
    //                 'customer_order_no' => $customer_order_no,
    //                 'qty' => $data->val($i, 4),
    //                 'price' => $customer_items->price,
    //                 'total' => $total,
    //                 'uom' => $item_fg->uom,
    //                 'plant' => $cust_add->plant,
    //                 'department' => $cust_add->department,
    //                 'attention_to' => $cust_add->contact_person,
    //             ];
    //         }

    //     }

    //     $datas = [];
    //     foreach ($datas_grouped as $group) {
    //         $total_sub = array_sum(array_column($group['items'], 'total'));
    //         $group['total_sub'] = $total_sub;
    //         $group['total'] = count($group['items']);
    //         $datas[] = $group;
    //     }

    //     // var_dump($datas);
    //     // var_dump($datas_grouped);

    //     echo json_encode($datas);
    //     unlink($_FILES['file_upload']['name']);
    // }

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

        $datenow = "SO" . $customer_id . date("ymd", strtotime($sales_order_date));
        $sqlGetID = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_orders WHERE `sales_order_no` like '%$datenow%'");
        $rowID = $sqlGetID->row();
        $kode = $rowID->kode;
        $urutan = ($kode == NULL) ? 1 : ((int)substr($kode, -3) + 1);

        $datas_grouped = [];
        //$total_data = 0; // Initialize total data counter

        for ($i = 7; $i <= $total_row; $i++) {
            $customer_order_no = $data->val($i, 2);
            $item_fg_number = $data->val($i, 3);
            $cust_address = $data->val(2, 5);
            $item_fg = $this->crud->read('item_fg', [], ["number" => $item_fg_number]);
            $cust_add = $this->crud->read('customer_address', [], ["id" => $cust_address]);

            // Debug: Print each row being processed
            // echo "Processing Row $i: Customer Order No: $customer_order_no, Product No: $item_fg_number<br>";

            if (!empty($item_fg->number)) {
                if (!isset($datas_grouped[$customer_order_no])) {
                    $sales_order_no = $datenow . sprintf("%03s", $urutan++);
                    $datas_grouped[$customer_order_no] = [
                        'sales_order_no' => $sales_order_no,
                        'customer_order_no' => $customer_order_no,
                        'sales_order_date' => $data->val(3, 3),
                        'delivery_date' => $data->val(4, 3),
                        'customer_id' => $customer_id,
                        'customer_address_id' => $cust_address,
                        'division' => $data->val(3, 5),
                        'remarks' => $data->val(4, 5),
                        'type_so' => $data->val(5, 5),
                        'items' => []
                    ];
                }

                $customer_items = $this->crud->read('customer_items', [], ["item_fg_id" => $item_fg->id, "customer_id" => $customer_id]);
                $total = ($data->val($i, 4) * $customer_items->price);

                $datas_grouped[$customer_order_no]['items'][] = [
                    'item_fg_id' => $item_fg->id,
                    'customer_order_no' => $customer_order_no,
                    'qty' => $data->val($i, 4),
                    'price' => $customer_items->price,
                    'total' => $total,
                    'uom' => $item_fg->uom,
                    'plant' => $cust_add->plant,
                    'department' => $cust_add->department,
                    'attention_to' => $cust_add->contact_person,
                ];
                //$total_data++; // Increment total data processed
            } else {
                // Debug: Print missing item_fg_number
                // echo "Missing Item FG Number for Row $i<br>";
            }
        }

        // Debug: Print grouped data
        // echo "<pre>";
        // print_r($datas_grouped);
        // echo "</pre>";

        $datas = [];
        foreach ($datas_grouped as $group) {
            $total_sub = array_sum(array_column($group['items'], 'total'));
            $group['total_sub'] = $total_sub;
            $group['total'] = count($group['items']);
            $datas[] = $group;
        }

        // echo json_encode([
        //     'total' => $total_data,  // Total data processed
        //     'data' => $datas         // Grouped data
        // ]);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/sales_orders.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/sales_orders.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
 
     //UPLOAD DOWNLOAD FAILED
     public function uploadDownloadFailed()
     {
         $file = "failed/sales_orders.txt";
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
    //  public function uploadcreate()
    //  {
    //      if ($this->input->post()) {
    //         $data = $this->input->post('data');//field excel
    //         $total_sub = $this->input->post('total_sub');

    //         //Cek Process Number                //table             //field           //field excel
    //         $customers = $this->crud->read('customers', [], ["id" => $data['customer_id']]);
    //         $customer_address = $this->crud->read('customer_address', [], ["id" => $data['customer_address_id'],"customer_id" => $data['customer_id']]);

    //         if (empty($customers->id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Customers ID " . $data['customer_id'] . " Not Found", "theme" => "error"));
    //         } elseif (empty($customer_address->id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Customers Address ID " . $data['customer_address_id'] . " Not Found in Customers ID ". $data['customer_id'] . "", "theme" => "error"));
    //         } else {
    //             $customer_items = $this->crud->read('customer_items', [], ["item_fg_id" => $data['item_fg_id'],"customer_id" => $data['customer_id']]);
    //             $sales_orders = $this->crud->read('sales_orders', [], ["customer_order_no" => $data['customer_order_no'], "item_fg_id" => $data['item_fg_id']]);

    //             if (!empty($sales_orders->sales_order_no )) {
    //                 echo json_encode(array("title" => "Duplicated", "message" => "Product ID " . $data['item_fg_id'] . " and Customer Order No " . $data['customer_order_no'] . " Duplicated", "theme" => "error"));
    //             } else {
    //                 $dataFinal = array(
    //                     //field        //excel
    //                     "customer_id" => $data['customer_id'],
    //                     "sales_order_date" => $data['sales_order_date'],
    //                     "sales_order_no" => $data['sales_order_no'],
    //                     "delivery_date" => $data['delivery_date'],
    //                     "customer_address_id" => $data['customer_address_id'],
    //                     "plant" => $data['plant'],
    //                     "attention_to" => $data['attention_to'],
    //                     "department" => $data['department'],
    //                     "remarks" => $data['remarks'],
    //                     "division" => $data['division'],
    //                     "customer_order_no" => $data['customer_order_no'],
    //                     "item_fg_id" => $data['item_fg_id'],
    //                     "qty" => $data['qty'],
    //                     "uom" => $data['uom'],
    //                     "currency" => $customers->currency,
    //                     "price" => $data['price'],
    //                     "total" => $data['total'],
    //                     "total_sub" => $total_sub,
    //                     "total_tax" => ($total_sub * ($customers->taxes / 100)),
    //                     "total_pph" => 0,
    //                     "total_grand" => ($total_sub + ($total_sub * ($customers->taxes / 100))),
                        
    //                 );
    //                 $send   = $this->crud->create('sales_orders', $dataFinal);
    //                 echo $send;
    //             }
    //          }
    //      }
    //  }

    // public function uploadcreate() 
    // {
    //     if ($this->input->post()) {
    //         $data = $this->input->post('data'); // Data hasil upload()
                
    //         // Pastikan data adalah array
    //         if (!is_array($data)) {
    //             echo json_encode(["title" => "Error", "message" => "Invalid data format", "theme" => "error"]);
    //             return;
    //         }

    //         if (isset($data['items']) && is_array($data['items']) && count($data['items']) > 0) {
    //             foreach ($data['items'] as $item) {
    //                 // var_dump($item);  // Menampilkan setiap item untuk debugging
    //             }
    //         } else {
    //             echo json_encode([
    //                 "title" => "Error",
    //                 "message" => "Items data is missing or not structured correctly",
    //                 "theme" => "error"
    //             ]);
    //         }

    //         // Debugging: Periksa data yang dikirim
    //         var_dump($data);

    //         foreach ($data['items'] as $item) {
    //             // Debugging: Periksa setiap item
    //             var_dump($item);
    //             exit;

    //             // Validasi apakah item memiliki data yang diperlukan
    //             if (
    //                 !isset($item['customer_id'], $item['sales_order_date'], $item['customer_order_no'], 
    //                     $item['item_fg_id'], $item['qty'], $item['uom'], $item['price'], $item['total'])
    //             ) {
    //                 echo json_encode(["title" => "Error", "message" => "Invalid item structure", "theme" => "error"]);
    //                 return;
    //             }

    //             $customer_id = $item['customer_id'];
    //             $sales_order_date = $item['sales_order_date'];
    //             $customer_order_no = $item['customer_order_no'];

    //             // Generate sales_order_no unik untuk setiap item
    //             $datenow = "SO" . $customer_id . date("ymd", strtotime($sales_order_date));
    //             $sqlGetID = $this->db->query("SELECT max(`sales_order_no`) as kode FROM sales_orders WHERE `sales_order_no` like '%$datenow%'");
    //             $rowID = $sqlGetID->row();
    //             $kode = $rowID->kode;
    //             $autoID = ($kode == NULL) ? sprintf("%03s", 1) : sprintf("%03s", (int) substr($kode, -3) + 1);
    //             $sales_order_no = $datenow . $autoID;

    //             // Validasi customer dan address
    //             $customers = $this->crud->read('customers', [], ["id" => $customer_id]);
    //             $customer_address_id = $item['customer_address_id'];
    //             $customer_address = $this->crud->read('customer_address', [], ["id" => $customer_address_id, "customer_id" => $customer_id]);

    //             if (empty($customers->id)) {
    //                 echo json_encode(["title" => "Not Found", "message" => "Customers ID $customer_id Not Found", "theme" => "error"]);
    //                 return;
    //             } elseif (empty($customer_address->id)) {
    //                 echo json_encode(["title" => "Not Found", "message" => "Customer Address ID $customer_address_id Not Found", "theme" => "error"]);
    //                 return;
    //             }

    //             // Simpan data untuk setiap item
    //             $dataFinal = [
    //                 "customer_id" => $customer_id,
    //                 "sales_order_date" => $sales_order_date,
    //                 "sales_order_no" => $sales_order_no,
    //                 "delivery_date" => $item['delivery_date'],  // Perhatikan, ini berasal dari setiap item
    //                 "customer_address_id" => $item['customer_address_id'],
    //                 "plant" => $item['plant'] ?? null,
    //                 "attention_to" => $item['attention_to'] ?? null,
    //                 "department" => $item['department'] ?? null,
    //                 "remarks" => $item['remarks'] ?? null,
    //                 "division" => $item['division'] ?? null,
    //                 "customer_order_no" => $customer_order_no,
    //                 "item_fg_id" => $item['item_fg_id'],
    //                 "qty" => $item['qty'],
    //                 "uom" => $item['uom'],
    //                 "currency" => $customers->currency,
    //                 "price" => $item['price'],
    //                 "total" => $item['total'],
    //                 "total_sub" => $item['total_sub'] ?? 0,
    //                 "total_tax" => (($item['total_sub'] ?? 0) * ($customers->taxes / 100)),
    //                 "total_pph" => 0,
    //                 "total_grand" => (($item['total_sub'] ?? 0) + (($item['total_sub'] ?? 0) * ($customers->taxes / 100))),
    //             ];

    //             // Masukkan ke dalam database
    //             $this->crud->create('sales_orders', $dataFinal);
    //         }    
    //         echo json_encode(["title" => "Success", "message" => "Data successfully saved!", "theme" => "success"]);
    //     }
    // }    

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            
            // Debug: Print the received data
            // echo "Data received:<br><pre>";
            // print_r($data);
            // echo "</pre>";
            // die;

            // Pastikan $data adalah array
            if (!is_array($data)) {
                echo json_encode([
                    "title" => "Error",
                    "message" => "Invalid data format.",
                    "theme" => "error"
                ]);
                return;
            }

            // Iterasi setiap sales order dalam $data
            foreach ($data as $salesOrder) {
                // Validasi apakah items ada di dalam sales order
                if (!isset($salesOrder['items']) || !is_array($salesOrder['items'])) {
                    echo json_encode([
                        "title" => "Error",
                        "message" => "No items found for Sales Order No " . $salesOrder['sales_order_no'],
                        "theme" => "error"
                    ]);
                    continue;
                }

                $duplicate_orders = $this->crud->reads("sales_orders", [], ["customer_order_no" => $salesOrder['customer_order_no']]);

                if (count($duplicate_orders) > 0) {
                    echo json_encode([
                        "title" => "Duplicated",
                        "message" => "Customer Order No " . $salesOrder['customer_order_no'] . " Duplicated",
                        "theme" => "error"
                    ]);
                    return;
                }

                // Debug: Print sales order and items
                // echo "Processing Sales Order:<br><pre>";
                // print_r($salesOrder);
                // echo "</pre>";

                $customer_id = $salesOrder['customer_id'];
                $customers = $this->crud->read('customers', [], ["id" => $customer_id]);

                $dataFinal = [];
                foreach ($salesOrder['items'] as $row) {
                    $sales_order_no = $salesOrder['sales_order_no'];

                    $dataFinal[] = [
                        "sales_order_no" => $sales_order_no,
                        "customer_order_no" => $row['customer_order_no'],
                        "customer_id" => $salesOrder['customer_id'],
                        "sales_order_date" => $salesOrder['sales_order_date'],
                        "delivery_date" => $salesOrder['delivery_date'],
                        "customer_address_id" => $salesOrder['customer_address_id'],
                        "plant" => $row['plant'],
                        "attention_to" => $row['attention_to'],
                        "department" => $row['department'],
                        "remarks" => $salesOrder['remarks'],
                        "division" => $salesOrder['division'],
                        "type_so" => $salesOrder['type_so'],
                        "item_fg_id" => $row['item_fg_id'],
                        "qty" => $row['qty'],
                        "uom" => $row['uom'],
                        "currency" => $customers->currency,
                        "taxes" => $customers->taxes,
                        "price" => $row['price'],
                        "total" => $row['total'],
                        "total_sub" => $salesOrder['total_sub'],
                        "total_tax" => ($salesOrder['total_sub'] * $customers->taxes / 100),
                        "total_grand" => ($salesOrder['total_sub'] + ($salesOrder['total_sub'] * ($customers->taxes / 100))),
                    ];
                }

                // Debug: Print final data to save
                // echo "Final Data to Save for Sales Order No " . $sales_order_no . ":<br><pre>";
                // print_r($dataFinal);
                // echo "</pre>";

                foreach ($dataFinal as $row) {
                    $result = $this->crud->create('sales_orders', $row);
                    if (!$result) {
                        echo json_encode([
                            "title" => "Error",
                            "message" => "Failed to save data for Sales Order No " . $row['sales_order_no'],
                            "theme" => "error"
                        ]);
                        return;
                    }
                }
            }

            echo json_encode([
                "title" => "Success",
                "message" => "All data saved successfully.",
                "theme" => "success"
            ]);
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_orders_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
        $filter_status = @base64_decode($get['filter_status']);
        $filter_division = @base64_decode($get['filter_division']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.name as customer_name, c.number as item_fg_number, c.name as item_fg_name, d.address as customer_address,  COALESCE(e.qty,0) as deliverys, a.qty - COALESCE(e.qty, 0) as outstandings");
        $this->db->from('sales_orders a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join('customer_address d', 'a.customer_address_id = d.id');
        $this->db->join("(SELECT item_fg_id, sales_order_no, COALESCE(SUM(qty),0) as qty FROM delivery_notes GROUP BY item_fg_id, sales_order_no ) e",'a.sales_order_no = e.sales_order_no and a.item_fg_id = e.item_fg_id','left');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.sales_order_date >=', $filter_from);
            $this->db->where('a.sales_order_date <=', $filter_to);
        }

        if ($filter_division != "") {
            $this->db->where('a.division', $filter_division);
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
                <th>Customer Address</th>
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
                        <td>' . $data['deliverys'] . '</td>
                        <td>' . $data['outstandings'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . $data['customer_address'] . '</td>
                        <td>' . $data['price'] . '</td>
                        <td>' . $data['total'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
