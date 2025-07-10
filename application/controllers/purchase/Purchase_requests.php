<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_requests extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('item_rm_id', 'Part No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_requests');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $request_no = $this->input->get('request_no');
        //Select Query
        $this->db->select('a.*, 
            b.id as item_rm_id,
            b.number as item_number, 
            b.name as item_name, 
            
            a.expected_date as delivery_date,
            c.name as category_name');
        $this->db->from('purchase_requests a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.request_no', $request_no);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    function readTotalPo() 
    {
        $item_rm = base64_decode($this->input->post('item_rm_id'));
        $item_no = base64_decode($this->input->post('item_number'));

        $this->db->select('a.id, a.item_rm_id, SUM(a.qty) as qty_po,
            b.number as supplier_number, 
            b.name as supplier_name, 
            c.uom, 
            c.number as item_number, 
            c.name as item_name, 
            d.qty_receipt,
            a.status, 
            h.total_status_complete');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');       
        $this->db->join('(SELECT po_no, COUNT(status) as total_status_complete FROM purchase_orders WHERE status = 2 GROUP BY po_no) h', 'a.po_no = h.po_no', 'left');
        $this->db->join('(SELECT po_no, item_rm_id, SUM(qty_receipt) as qty_receipt FROM purchase_order_receipts GROUP BY po_no, item_rm_id) d', 'a.po_no = d.po_no AND a.item_rm_id = d.item_rm_id', 'left');        
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->like('a.item_rm_id', $item_rm);
        $this->db->like('c.number', $item_no);
        $this->db->order_by('a.status', 'ASC'); 
        $this->db->group_by('a.po_no');
        $this->db->group_by('a.item_rm_id');
        $records = $this->db->get()->result_array();

        $mapping_data = [];

        foreach ($records as $record) {
            $item_id = $record['id'];

            if (!isset($mapping_data[$item_id])) {
                $mapping_data[$item_id] = [
                    'id'          => $item_id,
                    'item_rm_id'  => $record['item_rm_id'],
                    'item_number' => $record['item_number'],
                    'item_name'   => $record['item_name'],
                    'qty'         => $record['qty_po'],
                    'qty_receipt' => $record['qty_receipt'] ?? 0,
                ];
            }

            $mapping_data[$item_id]['os_qty'] = $record['qty_po'] - @$record['qty_receipt'];

            // -- Get Outstanding PO status
            if ($record['status'] == 2) {
                $mapping_data[$item_id]['os_status'] = "COMPLETE";
                $data['qty'] = 0;
                $mapping_data[$item_id]['os_qty'] = 0;
            } elseif (($record['qty_po'] - @$record['qty_receipt']) > 0) {
                $mapping_data[$item_id]['os_status'] = "OPEN";
            } else {
                $mapping_data[$item_id]['os_status'] = "CLOSE";
            }
        }

        $result = array_values($mapping_data);
        echo json_encode($result);
    }

    function readUserAccess(){
        $username = $this->session->username;
        $user = $this->crud->read("users", [], ["username" => $username]);

        if($user->access == 0){
            $data = array();
        }else{
            $data = array("division" => $user->division, "department" => $user->department, "sub_department" => $user->sub_department);
        }

        return $data;
    }

    public function readRequestnumber()
    {
        $records = $this->crud->query("SELECT request_no, request_date, request_name FROM purchase_requests WHERE `status` = '0' AND (approved_to IS NULL OR approved_to = '') GROUP BY request_no ORDER BY created_date DESC");
        echo json_encode($records);
    }

    public function readRequestnumbers()
    {
        $records = $this->crud->query("SELECT request_no, request_date, request_name FROM purchase_requests WHERE `deleted` = '0' GROUP BY request_no ORDER BY created_date desc");// WHERE `status` = '0'
        echo json_encode($records);
    }

    public function readRequestno($filter_from, $filter_to)
    {
        $filter_from = base64_decode($filter_from);
        $filter_to = base64_decode($filter_to);
        $filter_access = $this->readUserAccess();

        $where = "";
        if(!empty($filter_access)){
            $division = $filter_access['division'];
            $department = $filter_access['department'];
            $sub_department = $filter_access['sub_department'];
            $where = "AND division = '$division' and department = '$department' and sub_department = $sub_department";
        }

        if(isset($filter_from) && isset($filter_to)) {
            $records = $this->crud->query("SELECT request_no, request_date, request_name FROM purchase_requests WHERE request_date BETWEEN '$filter_from' AND '$filter_to' $where GROUP BY request_no ORDER BY created_date DESC");
            echo json_encode($records);
        } else {
            echo json_encode(['error' => 'Parameters are missing']);
        }
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $item_family_id = explode(",", $this->input->get('item_family_id'));
       
        $this->db->select('a.*,a.id as item_rm_id, a.number as item_number, a.name as item_name');//c.specification
        $this->db->from('item_rm a');
        $this->db->where_in('a.item_family_id', $item_family_id);
        $this->db->like('a.number', $post);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readCategoryno()
    {
        $request_no = $this->input->get('request_no');
        $supplier_id = $this->input->get('supplier_id');
        $records = $this->crud->query("SELECT d.id, d.number, d.name
            FROM purchase_requests a
            JOIN supplier_items b on a.item_rm_id = b.item_rm_id
            JOIN item_rm c on b.item_rm_id = c.id
            JOIN item_familys d on c.item_family_id = d.id
            WHERE a.status = '0' and a.request_no = '$request_no' and b.supplier_id = '$supplier_id'
            GROUP BY d.number");
        echo json_encode($records);
    }

    public function request_no($category = "", $request_no="", $methode="add")
    {
        $requestno = base64_decode($request_no);

        $datenow    = $category . date("ymd");
        $sqlGetID   = $this->db->query("SELECT max(request_no) as kode FROM purchase_requests WHERE request_no like '%$datenow%' and upload = 'NO'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;

        if($methode == "add"){
            if ($kode == NULL) {
                $autoID = sprintf("%04s", $kode + 1);
            } else {
                $urutan = (int) substr($kode, -4);
                $urutan++;
                $autoID = sprintf("%04s", $urutan);
            }
            
            echo "PR-" . $datenow . "-" . $autoID;
        }else{
            echo $requestno;
        }
        
    }

    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_category_id = $this->input->get('filter_category_id');
        $filter_item_familys = $this->input->get('filter_item_familys');
        $filter_status = $this->input->get('filter_status');
        $filter_access = $this->readUserAccess();

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $id = $_POST['id'];
        if ($id === "0") {
            $this->db->select('a.request_no as request_no, 
            a.request_date, 
            a.expected_date, 
            a.request_name, 
            a.division, 
            a.department, 
            a.sub_department, 
            sum(a.qty) as qty, 
            a.status, 
            c.id as item_family_id, 
            c.number as item_family_number, 
            c.item_category_id, 
            a.approved_to, 
            a.attachment');
            $this->db->from('purchase_requests a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', 'b.item_family_id = c.id');
            $this->db->join('purchase_orders d', 'a.request_no = d.request_no and a.item_rm_id = d.item_rm_id','left');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.request_date >=', $filter_from);
                $this->db->where('a.request_date <=', $filter_to);
            }
            if(!empty($filter_access)){
                $this->db->where('a.division', $filter_access['division']);
                $this->db->where('a.department', $filter_access['department']);
                $this->db->where('a.sub_department', $filter_access['sub_department']);
            }
            $this->db->like('a.request_no', $filter_request_no);
            $this->db->like('c.id', $filter_item_familys);
            $this->db->like('c.item_category_id', $filter_category_id);
            $this->db->like('a.status', $filter_status);
            $this->db->group_by('request_no');
            $this->db->order_by('a.created_date','DESC');
            $this->db->order_by('a.updated_date', 'DESC');
            $this->db->order_by('a.request_date', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                if($record['approved_to'] == "" || $record['approved_to'] == null){
                    $approved_to = "";
                } else {
                    $approved_to = "Checking";
                }

                $this->db->select('1');
                $this->db->from('purchase_orders');
                $this->db->where('request_no', $record['request_no']);
                $exists = $this->db->get()->num_rows() > 0;
            
                // Tentukan status berdasarkan hasil pengecekan
                $status = $exists ? "1" : "0";
            
                // Update status di purchase_requests
                $this->db->where('request_no', $record['request_no']);
                $this->db->update('purchase_requests', ['status' => $status]);
                

                $arr[] = array(
                    "id" => $record['request_no'],
                    "item_family_id" => $record['item_family_id'],
                    "item_family_number" => $record['item_family_number'],
                    "item_category_id" => $record['item_category_id'],
                    "request_no" => $record['request_no'],
                    "request_date" => $record['request_date'],
                    "expected_date" => $record['expected_date'],
                    "request_name" => $record['request_name'],
                    "division" => $record['division'],
                    "department" => $record['department'],
                    "sub_department" => $record['sub_department'],
                    "qty" => $record['qty'],
                    "approved_to" => $approved_to,
                    "status" => $status,
                    "state" => "closed",
                    "datatable" => 1
                );
            }
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        } else {
            $this->db->select('a.*, 
                b.number as item_number, 
                b.name as item_name, 
                b.uom, 
                d.po_no, 
                d.status as status_po, 
                c.name as category_name');
            $this->db->from('purchase_requests a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', 'b.item_family_id = c.id','left');
            $this->db->join('purchase_orders d', 'a.request_no = d.request_no and a.item_rm_id = d.item_rm_id', 'left');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.request_date >=', $filter_from);
                $this->db->where('a.request_date <=', $filter_to);
            }
            $this->db->where('a.request_no', $id);
            $this->db->like('c.id', $filter_item_familys);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function datatable_updates(){
        $request_no = base64_decode($this->input->get('request_no'));
        $records = $this->crud->query("SELECT a.id, c.number as item_number, c.name as item_name, c.id as item_rm_id, a.qty, a.remarks
            FROM purchase_requests a
            JOIN item_rm c on a.item_rm_id = c.id
            WHERE a.status = '0' and a.request_no = '$request_no'
            GROUP BY c.number");
        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();

            if ($post['id'] != "") {
                $post_final = [
                    "qty" => $post['qty'],
                    "remarks" => $post['remarks']
                ];
                $send = $this->crud->update('purchase_requests', ["id" => $post['id']], $post_final);
            } else {
                $send = $this->crud->create('purchase_requests', $post);
            }

            echo $send;
        
        } else {
            show_error("Cannot Process your request");
        }
    }

    // public function update()
    // {
    //     if ($this->input->post()) {
    //         $id   = $this->input->post('id');
    //         $post = $this->input->post();
    //         $send = $this->crud->update('purchase_requests', ["id" => $id], [
    //             "qty" => $post['qty'], 
    //             "remarks" => $post['remarks'],
    //             "division" => $post['division']
    //         ]);

    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_requests', $data);
        echo $send;
    }

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
        $category = $data->val(2, 4);
        $item_categories = $this->crud->read('item_categories', [], ["number" => $category]);

        if (!empty($item_categories)) {
            // $datenow    = $item_categories->number . date("ymd");
            // $sqlGetID   = $this->db->query("SELECT max(request_no) as kode FROM purchase_requests WHERE request_no like '%$datenow%'");
            // $rowID      = $sqlGetID->row();
            // $kode       = $rowID->kode;
            // if ($kode == NULL) {
            //     $autoID = sprintf("%04s", $kode + 1);
            // } else {
            //     $urutan = (int) substr($kode, -4);
            //     $urutan++;
            //     $autoID = sprintf("%04s", $urutan);
            // }
            // $request_no = "PR-" . $datenow . "-" . $autoID;


            for ($i = 4; $i <= $total_row; $i++) {
                $datas[] = array(
                    'request_no' => $data->val($i, 2),
                    'request_date' => $data->val($i, 3),
                    'expected_date' => $data->val($i, 4),
                    'request_name' => $data->val($i, 5),
                    'division' => $data->val($i, 6),
                    'department' => html_entity_decode($data->val($i, 7)),
                    'sub_department' => html_entity_decode($data->val($i, 8)),
                    'product_number' => html_entity_decode($data->val($i, 9)),
                    'qty' => $data->val($i, 10),
                    'remarks' => $data->val($i, 11),
                    'upload' => $data->val($i, 12),
                );
            }
            $datas['total'] = count($datas);
            echo json_encode($datas);
        } else {
            echo json_encode(array("title" => "Not Found", "message" => "Product Family " . $category . " Not Found Data", "theme" => "error"));
        }
        unlink($_FILES['file_upload']['name']);
    }

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
    //     $category = $data->val(2, 3);
    //     $item_categories = $this->crud->read('item_categories', [], ["number" => $category]);
    //     if (!empty($item_categories)) {
    //         $datenow    = $item_categories->number . date("ymd");
    //         $sqlGetID   = $this->db->query("SELECT max(request_no) as kode FROM purchase_requests WHERE request_no like '%$datenow%'");
    //         $rowID      = $sqlGetID->row();
    //         $kode       = $rowID->kode;
    //         if ($kode == NULL) {
    //             $autoID = sprintf("%04s", $kode + 1);
    //         } else {
    //             $urutan = (int) substr($kode, -4);
    //             $urutan++;
    //             $autoID = sprintf("%04s", $urutan);
    //         }
    //         $request_no = "PR-" . $datenow . "-" . $autoID;
    //         for ($i = 4; $i <= $total_row; $i++) {
    //             $datas[] = array(
    //                 'request_no' => $request_no,
    //                 'request_date' => $data->val($i, 2),
    //                 'expected_date' => $data->val($i, 3),
    //                 'request_name' => $data->val($i, 4),
    //                 'division' => $data->val($i, 5),
    //                 'product_number' => $data->val($i, 6),
    //                 'qty' => $data->val($i, 7),
    //                 'remarks' => $data->val($i, 8)
    //             );
    //         }
    //         $datas['total'] = count($datas);
    //         echo json_encode($datas);
    //     } else {
    //         echo json_encode(array("title" => "Not Found", "message" => "Product Family " . $category . " Not Found Data", "theme" => "error"));
    //     }
    //     unlink($_FILES['file_upload']['name']);
    // }

    public function uploadclearFailed()
    {
        @unlink('failed/purchase_requests.txt');
    }
    
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/purchase_requests.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/purchase_requests.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data       = $this->input->post('data');
            $product_number = html_entity_decode($data['product_number']);

            //Cek Process Number
            $item = $this->crud->read('item_rm', [], ["number" => $product_number]);
            $purchase_requests = $this->crud->read('purchase_requests', [], ["request_no" => $data['request_no'], "item_rm_id" => $item->id]);
            $table = "purchase_requests";
            $user = $this->crud->read('users', [], ["name" => $data['request_name']]);
            $approval = $this->crud->read('approvals', ["sub_department" => $data['sub_department'], "department" => $data['department'], "division" => $data['division']], ["table_name" => $table]);
            
            if (empty($item->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['product_number'] . " Not Found", "theme" => "error"));
            } elseif (!empty($purchase_requests->id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['product_number'] . " Duplicate Data", "theme" => "error"));
            } else {
                $send   = $this->crud->create('purchase_requests',([
                    "item_rm_id" => $item->id, 
                    "request_no" => $data['request_no'], 
                    "request_date" => $data['request_date'],
                    "request_name" => $data['request_name'] ,
                    "division" => $data['division'],
                    "department" => html_entity_decode($data['department']),
                    "sub_department" => html_entity_decode($data['sub_department']),
                    "qty" => $data['qty'],
                    "expected_date" => $data['expected_date'], 
                    "remarks" => $data['remarks'], 
                    "approved" => 1, 
                    "approved_to" => $approval->user_approval_1, 
                    "approved_by" => $user->username, 
                    "upload" => $data['upload']
                ]));
                echo $send;
            }
        }
    }

    public function uploadatt()
    {
        // Pastikan file disimpan dalam direktori yang diinginkan
        $uploadDir = 'assets/image/purchase_requests/';

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
                    echo json_encode(['success' => false, 'message' => 'File Size > 2 MB.']);
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

    public function print_request($request_no)
    {
        $request_no = base64_decode($request_no);
        $purchase_request_total = $this->crud->reads('purchase_requests', [], ["request_no" => $request_no]);
        $purchase_requests = $this->crud->read('purchase_requests', [], ["request_no" => $request_no]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 15;
        $page = ceil(count($purchase_request_total) / $rows);
        //Generate QRcode
        $this->createQrcode($request_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $purchase_requests->request_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center>
                    <h1>Press CTRL + P for Print</h1>
                    <p>Display pages for 15 rows</p>
                    <p>Paper Size A4, Layout Landscape</p>
                    <p>Margin Default, Scale 98</p>
                </center></div><div class="print">';
        //Loop Page
        $no = 1;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_rm_id, b.name as item_name, b.uom');
            $this->db->from('purchase_requests a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.request_no', $request_no);
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(15, ($i * 15));
            $records = $this->db->get()->result_array();
            $html .= '  <table style="width:100%;" border="1">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $purchase_requests->request_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_purchase_request . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_purchase_request . '</td>
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
                                </th>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3><u>PURCHASE ORDER REQUESTION</u></h3>
                                </center>
                                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                    <tr>
                                        <td width="100">Request No</td>
                                        <td width="30">:</td>
                                        <td><b>' . @$purchase_requests->request_no . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Request Date</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$purchase_requests->request_date . '</b></td>
                                    </tr>
                                </table>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th width="120">Product No</th>
                                        <th>Product Name</th>
                                        <th width="60">Qty</th>
                                        <th width="50">Uom</th>
                                        <th>Remarks</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_rm_id'] . '</td>
                                <td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>
                                <td style="text-align:right">' . number_format($record['qty'], 2, ",", ".") . '</td>
                                <td>' . $record['uom'] . '</tdstyle=>
                                <td>' . $record['remarks'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>';
            if ($i + 1 != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            if (($i + 1) == $page) {
                $html .= '  <table id="customers" style="margin-top:20px;">
                                <tr>
                                    <th width="200" style="text-align:center;">Prepared By</th>
                                    <th width="200" style="text-align:center;">Knowed By</th>
                                    <th width="200" style="text-align:center;">Approved By</th>
                                </tr>
                                <tr>
                                    <th style="height:80px;"></th>
                                    <th style="height:80px;"></th>
                                    <th style="height:80px;"></th>
                                </tr>
                                <tr>
                                    <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                                    <th style="height:20px; text-align:center;"></th>
                                    <th style="height:20px; text-align:center;"></th>
                                </tr>
                            </table>';
            }
            $html .= '</div></div>';
        }
        $html .= '</div><script>window.print()</script>';
        die($html);
    }
    
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_requests_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_category_id = $this->input->get('filter_category_id');
        $filter_item_familys = $this->input->get('filter_item_familys');
        $filter_access = $this->readUserAccess();

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as item_rm_id, b.name as item_name, c.name as item_family_name, e.po_no, e.status as status_po, b.uom');
        $this->db->from('purchase_requests a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('purchase_orders e', 'a.request_no = e.request_no and a.item_rm_id = e.item_rm_id', 'left');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.request_date >=', $filter_from);
            $this->db->where('a.request_date <=', $filter_to);
        }
        if(!empty($filter_access)){
            $this->db->where('a.division', $filter_access['division']);
            $this->db->where('a.department', $filter_access['department']);
            $this->db->where('a.sub_department', $filter_access['sub_department']);
        }
        $this->db->like('a.request_no', $filter_request_no);
        $this->db->like('c.id', $filter_item_familys);
        $this->db->order_by('a.request_date', 'DESC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' .  $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>PURCHASE REQUEST</small>
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
                <th>Request No</th>
                <th>Status</th>
                <th>Request Date</th>
                <th>Expected Date</th>
                <th>Request Name</th>
                <th>Division</th>
                <th>Department</th>
                <th>Sub Department</th>
                <th>Part No</th>
                <th>Part Name</th>
                <th>Qty</th>
                <th>Uom</th>
                <th>Remarks</th>
                <th>PO No</th>
                <th>Status PO</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status'] == 0) {
                $status = "UNCONVERT";
            } else {
                $status = "CONVERTED";
            }

            if ($data['status_po'] == 0) {
                $status_po = "OPEN";
            } else {
                $status_po = "CLOSED";
            }
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['request_no'] . '</td>
                        <td>' . $status . '</td>
                        <td>' . $data['request_date'] . '</td>
                        <td>' . $data['expected_date'] . '</td>
                        <td>' . $data['request_name'] . '</td>
                        <td>' . $data['division'] . '</td>
                        <td>' . $data['department'] . '</td>
                        <td>' . $data['sub_department'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['qty'], 2) . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $status_po . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
    //
}
