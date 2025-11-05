<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Item_rm extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Product No.', 'required|min_length[1]|max_length[100]|is_unique[item_rm.number]');

        // Load the autoloader for PhpSpreadsheet
        $this->load->library('upload');
        require_once APPPATH . '/third_party/PhpSpreadsheet/autoload.php';
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/item_rm');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name FROM item_rm a JOIN item_familys b ON a.item_family_id = b.id WHERE a.number like '%$post%' or a.name like '$post'");
        echo json_encode($send);
    }


    public function readItems()
    {
       $post = isset($_POST['q']) ? $_POST['q'] : "";
       $item_family_id = explode(",", $this->input->get('item_family_id'));
       
       $this->db->select('a.*,
       a.number as item_number, 
       a.name as item_name,
       b.uom_default,
       b.uom_inventory');//c.specification
       $this->db->from('item_rm a');
       $this->db->join('supplier_items b','a.id = b.item_rm_id','LEFT');
       $this->db->where_in('a.item_family_id', $item_family_id);
       $this->db->where('a.status', 0);
       $this->db->like('a.number', $post);
       $this->db->group_by('a.id');
       $this->db->order_by('a.id', 'ASC');
       $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readFamily($categoryId)
    {
        $this->db->select("a.*,a.name, a.account_name, a.account_number, COALESCE(c.number,'-') as division_number");//c.specification
        $this->db->from('item_familys a');
        $this->db->join('item_categories b','a.item_category_id = b.id');
        $this->db->join('divisions c','a.division_id = c.id','left');
        $this->db->where('a.item_category_id', $categoryId);
        $this->db->where('a.status', 0);
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function read($id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name FROM item_rm a JOIN item_familys b ON a.item_family_id = b.id WHERE a.item_family_id like '%$id%'");
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
            $this->db->select('a.*, b.name as item_category_name, c.name as item_family_name, d.name as item_sub_family_name, c.account_number, c.account_name');
            $this->db->from('item_rm a');
            $this->db->join('item_categories b', 'a.item_category_id = b.id');
            $this->db->join('item_familys c', 'a.item_family_id = c.id');
            $this->db->join('item_family_subs d', 'a.item_sub_family_id = d.id','left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "item_category_name") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "item_family_name") {
                        $this->db->like("c.name", $filter->value);
                    } elseif ($filter->field == "item_sub_family_name") {
                        $this->db->like("d.name", $filter->value);
                    } else {
                        $this->db->like("a." . $filter->field, $filter->value);
                    }
                }
            }
            // $this->db->order_by('a.id', 'ASC');
            $this->db->order_by('a.created_date', 'DESC');
            $this->db->order_by('a.updated_date', 'DESC');
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
    public function autoid($category, $family)
    {
        $month = date('my');
        $combine = $category . "-" . $family;
        $format = "BPI" . $combine . $month;
        $sql = $this->db->query("SELECT max(id) as kode FROM item_rm WHERE id LIKE '%$format%'");
        $row = $sql->row();
        if ($row->kode == "") {
            $kode = 0;
        } else {
            $kode = substr($row->kode, -4);
        }
        $autoid = $format . sprintf("%04s", $kode + 1);
        echo $autoid;
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('item_rm', $post);
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
            $send = $this->crud->update('item_rm', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('item_rm', $data);
        echo $send;
    }

    //UPLOAD DATA
    public function upload()
    {
        $config['upload_path']   = './uploads/'; // Create an 'uploads' directory in your CI root
        $config['allowed_types'] = 'xls|xlsx|csv';
        $config['max_size']      = 2048; // 2MB

        $file_path = null;

        try {
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file_upload')) { // 'file_upload' is the name of your file input
                $error = json_encode($this->upload->display_errors());
                echo json_encode(["title" => "Error", "message" => "Error upload file!", "data" => $error, "theme" => "error"]);

            } else {
                $data = $this->upload->data();
                $file_path = './uploads/' . $data['file_name'];

                // Use IOFactory to load the spreadsheet
                $spreadsheet = IOFactory::load($file_path);
                // $sheetData = $spreadsheet->getActiveSheet()->toArray();
                // echo json_encode($sheetData);
                // exit();

                $worksheet  = $spreadsheet->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();
                $datas = [];

                // Mapping Kolom Excel ke Nama Field Database
                for ($i = 3; $i <= $highestRow; $i++) {
                    $datas[] = [
                        'number'             => trim($worksheet->getCell('B' . $i)->getValue()), // Kolom 2 di Excel = B
                        'name'               => trim($worksheet->getCell('C' . $i)->getValue()), // Kolom 3 = C
                        'uom'                => trim($worksheet->getCell('D' . $i)->getValue()), // Kolom 4 = D
                        'division'           => trim($worksheet->getCell('E' . $i)->getValue()), // Kolom 5 = E
                        'item_category_id'   => trim($worksheet->getCell('F' . $i)->getValue()), // Kolom 6 = F
                        'item_family_id'     => trim($worksheet->getCell('G' . $i)->getValue()), // Kolom 7 = G
                        'color'              => trim($worksheet->getCell('H' . $i)->getValue()), // Kolom 8 = H
                        'item_sub_family_id' => trim($worksheet->getCell('I' . $i)->getValue()), // Kolom 9 = I
                        'account_number'     => trim($worksheet->getCell('J' . $i)->getValue()), // Kolom 10 = J
                        'account_name'       => trim($worksheet->getCell('K' . $i)->getValue()), // Kolom 11 = K
                        'length'             => trim($worksheet->getCell('L' . $i)->getValue()), // Kolom 12 = L
                        'width'              => trim($worksheet->getCell('M' . $i)->getValue()), // Kolom 13 = M
                        'thickness'          => trim($worksheet->getCell('N' . $i)->getValue()), // Kolom 14 = N
                        'diameter'           => trim($worksheet->getCell('O' . $i)->getValue()), // Kolom 15 = O
                        'description'        => trim($worksheet->getCell('P' . $i)->getValue()), // Kolom 16 = P
                        'supply'             => trim($worksheet->getCell('Q' . $i)->getValue()), // Kolom 17 = Q
                        'status'             => trim($worksheet->getCell('R' . $i)->getValue()), // Kolom 18 = R
                        'action'             => trim($worksheet->getCell('S' . $i)->getValue()), // Kolom 19 = S
                    ];
                }
                
                // Hapus file sementara setelah selesai dibaca
                if (file_exists($file_path)) {
                    unlink($file_path);
                }

                $response = [
                    'total' => count($datas),
                    'data' => $datas
                ];
                
                header('Content-Type: application/json');
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
            }

        } catch (Exception $e) {
            // Jika file sudah ter-upload (file_path diset) tetapi terjadi error saat pembacaan
            if ($file_path && file_exists($file_path)) { 
                unlink($file_path);
            }
            
            // Handle upload errors gracefully
            http_response_code(500); // Set HTTP status code for server error
            echo json_encode(["title" => "Error", "message" => "Error upload file! " . $e->getMessage(), "theme" => "error"]);
        }
    }

    public function upload_bug_symbols() // simbol sudah dihapus dengan preg_match() tetapi Bu Septi ingin tetap ada
    {
        header('Content-Type: application/json');

        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';

        try {
            $target = basename($_FILES['file_upload']['name']);
            
            // Use a more robust check for file upload success
            if (!move_uploaded_file($_FILES['file_upload']['tmp_name'], $target)) {
                throw new Exception("Failed to upload file.");
            }
            
            chmod($target, 0777);
            $file = $target;
            $data = new Spreadsheet_Excel_Reader($file, false);
            $total_row = $data->rowcount($sheet_index = 0);
            $datas = [];

            for ($i = 3; $i <= $total_row; $i++) {
                $datas[] = [
                    'number' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 2)),
                    'name' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 3)),
                    'uom' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 4)),
                    'division' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 5)),
                    'item_category_id' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 6)),
                    'item_family_id' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 7)),
                    'color' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 8)),
                    'item_sub_family_id' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 9)),
                    'account_number' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 10)),
                    'account_name' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 11)),
                    'length' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 12)),
                    'width' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 13)),
                    'thickness' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 14)),
                    'diameter' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 15)),
                    'description' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 16)),
                    'supply' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 17)),
                    'status' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 18)),
                    'action' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 19)),
                ];
            }
            
            $response = [
                'total' => count($datas),
                'data' => $datas
            ];
            
            echo json_encode($response);

        } catch (Exception $e) {
            // Handle upload errors gracefully
            http_response_code(500); // Set HTTP status code for server error
            echo json_encode(['error' => $e->getMessage()]);
        } finally {
            // Ensure the temporary file is deleted even if an error occurs
            if (isset($target) && file_exists($target)) {
                unlink($target);
            }
        }
    }

    public function upload_backup() // error json in Google Chrome (excel_reader2.php) works in Edge
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
                'number' => $data->val($i, 2),
                'name' => $data->val($i, 3),
                'uom' => $data->val($i, 4),
                'division' => $data->val($i, 5),
                'item_category_id' => $data->val($i, 6),
                'item_family_id' => $data->val($i, 7),
                'color' => $data->val($i, 8),
                'item_sub_family_id' => $data->val($i, 9),
                'account_number' => $data->val($i, 10),
                'account_name' => $data->val($i, 11),
                'length' => $data->val($i, 12),
                'width' => $data->val($i, 13),
                'thickness' => $data->val($i, 14),
                'diameter' => $data->val($i, 15),
                'description' => $data->val($i, 16),
                'supply' => $data->val($i, 17),
                'status' => $data->val($i, 18),
                'action' => $data->val($i, 19),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/item_rm.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/item_rm.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/item_rm.txt";
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
    public function uploadCreate()
    {
        if (!$this->input->post()) {
            echo json_encode(["title" => "Error", "message" => "Invalid request. No data received.", "theme" => "error"]);
            return;
        }

        try {
            $data = $this->input->post('data');
            if (empty($data)) {
                echo json_encode(["title" => "Not Found", "message" => "Input data is empty.", "theme" => "error"]);
                return;
            }

            if (empty($data['number'])) {
                echo json_encode(["title" => "Not Found", "message" => "Product ID is not detected. Please check your file", "theme" => "error"]);
                return;
            }

            if (empty($data['item_category_id'])) {
                echo json_encode(["title" => "Not Found", "message" => "Category ID is not detected. Please check your file", "theme" => "error"]);
                return;
            }

            if (empty($data['item_family_id'])) {
                echo json_encode(["title" => "Not Found", "message" => "Family ID is not detected. Please check your file", "theme" => "error"]);
                return;
            }

            if (empty($data['item_sub_family_id'])) {
                $data['item_sub_family_id'] = "";
            }

            //Cek Process Number          //table       //field        //field excel
            $item_rm = $this->crud->read('item_rm', [], ["number" => $data['number']]);
            $category = $this->crud->read('item_categories', [], ["id" => $data['item_category_id']]);
            $product_family = $this->crud->read('item_familys', [], ["id" => $data['item_family_id']]);
            $product_family_sub = $this->crud->read('item_family_subs', [], ["id" => $data['item_sub_family_id']]);

            // Validate required data first.
            if (empty($category)) {
                echo json_encode(["title" => "Not Found", "message" => "Category " . $data['item_category_id'] . " Not Found", "theme" => "error"]);
                return;
            }
            if (empty($product_family)) {
                echo json_encode(["title" => "Not Found", "message" => "Product Family " . $data['item_family_id'] . " Not Found", "theme" => "error"]);
                return;
            }
            if (strtolower($data['action']) !== 'update' && !empty($item_rm)) {
                echo json_encode(["title" => "Duplicated", "message" => " Product No. " . $data['number'] . " is Duplicate Data", "theme" => "error"]);
                return;
            }
            
            // Prepare data for create/update.
            $kind = $product_family_sub->kind ?? null;
            $density = $product_family_sub->density ?? 0;

            $length = isset($data['length']) && is_numeric($data['length']) ? (float)$data['length'] : 0;
            $width = isset($data['width']) && is_numeric($data['width']) ? (float)$data['width'] : 0;
            $thickness = isset($data['thickness']) && is_numeric($data['thickness']) ? (float)$data['thickness'] : 0;
            $diameter = isset($data['diameter']) && is_numeric($data['diameter']) ? (float)$data['diameter'] : 0;

            if ($kind == "TUBE") {
                $volume = 3.14 * ($diameter/2) * ($diameter/2) * $length;
            } else {
                $volume = $length * $width * $thickness;
            }

            $weightGr = $density * $volume;
            $weightKg = $weightGr / 1000000;
            
            // Define data
            $dataFinal = [
                "number" => $data['number'],
                "name" => $data['name'],
                "uom" => $data['uom'],
                "division" => $data['division'],
                "item_category_id" => $data['item_category_id'],
                "item_family_id" => $data['item_family_id'],
                "color" => $data['color'],
                "item_sub_family_id" => $data['item_sub_family_id'],
                "account_number" => $data['account_number'],
                "account_name" => $data['account_name'],
                "kind" => $kind,
                "length" => $data['length'],
                "width" => $data['width'],
                "thickness" => $data['thickness'],
                "diameter" => $data['diameter'],
                "density" => $density,
                "volume" => $volume,
                "weight_gr" => $weightGr,
                "weight_kg" => $weightKg,
                "description" => $data['description'],
                "supply" => $data['supply'],
                "status" => $data['status'],
            ];

            // Validasi ACTION : UPDATE or NEW
            if (strtolower($data['action']) === 'update') {
                if (empty($item_rm)) {
                    $send = json_encode(["title" => "Error", "message" => "Item with Part No: " . $data['number'] . " not found for update. Please check data (special chars and symbols)", "theme" => "error"]);
                } else {                    
                    $send = $this->crud->update('item_rm', ["id" => $item_rm->id], $dataFinal);
                }
                echo $send;

            } else {
                // AUTOID 
                $month = date('my');
                $combine = @$category->number . "-" . @$product_family->number;
                $format = "BPI" . $combine . $month;
                
                $this->db->select_max('id', 'max_id');
                $this->db->like('id', $format, 'after');
                $query = $this->db->get('item_rm');
                $row = $query->row();
                
                $kode = ($row->max_id) ? substr($row->max_id, -4) : 0;
                $autoid = $format . sprintf("%04s", $kode + 1);

                $dataFinal['id'] = $autoid;
                $send = $this->crud->create('item_rm', $dataFinal);
                echo $send;
            }
        
        } catch (Exception $e) {
            echo json_encode(["title" => "Error", "Message: " => $e->getMessage(), "theme" => "error"]);
        }        
    }
    
    public function uploadcreate_backup()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Process Number          //table       //field        //field excel
            $item_rm = $this->crud->read('item_rm', [], ["number" => $data['number']]);
            $category = $this->crud->read('item_categories', [], ["id" => $data['item_category_id']]);
            $product_family = $this->crud->read('item_familys', [], ["id" => $data['item_family_id']]);
            $product_family_sub = $this->crud->read('item_family_subs', [], ["id" => $data['item_sub_family_id']]);

            //AUTOID
            $month = date('my');
            $combine = @$category->number . "-" . @$product_family->number;
            $format = "BPI" . $combine . $month;
            $sql = $this->db->query("SELECT max(id) as kode FROM item_rm WHERE id LIKE '%$format%'");
            $row = $sql->row();
            if ($row->kode == "") {
                $kode = 0;
            } else {
                $kode = substr($row->kode, -4);
            }
            $autoid = $format . sprintf("%04s", $kode + 1);
            $kind = $product_family_sub->kind;
            $density = $product_family_sub->density;
            

            if ($kind == "TUBE") {
                $volume = 3.14 * ($data['diameter']/2) * ($data['diameter']/2) * $data['length'];
            } else {
                $volume = $data['length'] * $data['width'] * $data['thickness'];
            }
    
            $weightGr = $density * $volume;
            $weightKg = $weightGr / 1000000;
    

            if (empty($category->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Category " . $data['item_category_id'] . " Not Found", "theme" => "error"));
            } elseif (empty($product_family->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product Family " . $data['item_family_id'] . " Not Found", "theme" => "error"));
            // } elseif (empty($product_family_sub->name)) {
            //     echo json_encode(array("title" => "Not Found", "message" => "Product Family Sub " . $data['item_sub_family_id'] . " Not Found", "theme" => "error"));
            } elseif (!empty($item_rm->number)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['number'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "id" => $autoid,
                    "number" => $data['number'],
                    "name" => $data['name'],
                    "uom" => $data['uom'],
                    "division" => $data['division'],
                    "item_category_id" => $data['item_category_id'],
                    "item_family_id" => $data['item_family_id'],
                    "color" => $data['color'],
                    "item_sub_family_id" => $data['item_sub_family_id'],
                    "account_number" => $data['account_number'],
                    "account_name" => $data['account_name'],
                    "kind" => $kind,
                    "length" => $data['length'],
                    "width" => $data['width'],
                    "thickness" => $data['thickness'],
                    "diameter" => $data['diameter'],
                    "density" => $density,
                    "volume" => $volume,
                    "weight_gr" => $weightGr,
                    "weight_kg" => $weightKg,
                    "description" => $data['description'],
                    "supply" => $data['supply'],
                    "status" => $data['status'],
                );
                $send   = $this->crud->create('item_rm', $dataFinal);
                echo $send;
            }
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format = date("Ymd");
            
            // Atur header untuk file Excel dengan encoding UTF-8
            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header("Content-Disposition: attachment; filename=item_rm_$format.xls");
            
            // Tambahkan Byte Order Mark (BOM) untuk membantu Excel mengenali encoding
            echo "\xEF\xBB\xBF";
            echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        }

        // Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Ambil semua data dari database
        $this->db->select('a.*, b.name as item_category_name, c.name as item_family_name, d.number as item_sub_family_number');
        $this->db->from('item_rm a');
        $this->db->join('item_categories b', 'a.item_category_id = b.id');
        $this->db->join('item_familys c', 'a.item_family_id = c.id');
        $this->db->join('item_family_subs d', 'a.item_sub_family_id = d.id','left');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        // Lakukan konversi encoding pada setiap string di dalam data
        $encoded_records = [];
        foreach ($records as $record) {
            $encoded_row = [];
            foreach ($record as $key => $value) {
                // Hanya konversi jika nilainya adalah string
                if (is_string($value)) {
                    // Mengubah karakter URL-encoded menjadi spasi biasa
                    $decoded_value = urldecode($value);
                    $clean_value = str_replace("\xC2\xA0", " ", $decoded_value);
                    
                    // Konversi dari UTF-8 ke ISO-8859-1
                    $encoded_row[$key] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $clean_value);
                } else {
                    $encoded_row[$key] = $value;
                }
            }
            $encoded_records[] = $encoded_row;
        }

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#item_rm {border-collapse: collapse;width: 100%;font-size: 12px;}#item_rm td, #item_rm th {border: 1px solid #ddd;padding: 2px;}#item_rm tr:nth-child(even){background-color: #f2f2f2;}#item_rm tr:hover {background-color: #ddd;}#item_rm th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>MASTER ITEM</h3>
                </div>
            </center>
            
            <table id="item_rm" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Product ID</th>
                    <th>Product No.</th>
                    <th>Part Name</th>
                    <th>UOM</th>
                    <th>Division</th>
                    <th>Category</th>
                    <th>Product Family</th>
                    <th>Color</th>
                    <th>Product Family Sub</th>
                    <th>Account No.</th>
                    <th>Account Name</th>
                    <th>Kind</th>
                    <th>Length</th>
                    <th>Width</th>
                    <th>Thickness</th>
                    <th>Diameter</th>
                    <th>Density</th>
                    <th>Volume</th>
                    <th>Weight GR</th>
                    <th>Weight KG</th>
                    <th>Description</th>
                    <th>Supply</th>
                    <th>Status</th>
                </tr>';
                
        $no = 1;
        // foreach data yang sudah dikonversi
        foreach ($encoded_records as $data) {
            $html .= '<tr>';
            $html .= '<td>' . $no . '</td>';
            $html .= '<td>' . $data['id'] . '</td>';
            $html .= '<td style="mso-number-format:\@;">' . $data['number'] . '</td>';
            $html .= '<td style="mso-number-format:\@;">' . $data['name'] . '</td>';
            $html .= '<td>' . $data['uom'] . '</td>';
            $html .= '<td>' . $data['division'] . '</td>';
            $html .= '<td>' . $data['item_category_name'] . '</td>';
            $html .= '<td>' . $data['item_family_name'] . '</td>';
            $html .= '<td>' . $data['color'] . '</td>';
            $html .= '<td>' . $data['item_sub_family_number'] . '</td>';
            $html .= '<td>' . $data['account_number'] . '</td>';
            $html .= '<td>' . $data['account_name'] . '</td>';
            $html .= '<td>' . $data['kind'] . '</td>';
            $html .= '<td>' . $data['length'] . '</td>';
            $html .= '<td>' . $data['width'] . '</td>';
            $html .= '<td>' . $data['thickness'] . '</td>';
            $html .= '<td>' . $data['diameter'] . '</td>';
            $html .= '<td>' . $data['volume'] . '</td>';
            $html .= '<td>' . $data['density'] . '</td>';
            $html .= '<td>' . $data['weight_gr'] . '</td>';
            $html .= '<td>' . $data['weight_kg'] . '</td>';
            $html .= '<td>' . $data['description'] . '</td>';
            $html .= '<td>' . $data['supply'] . '</td>';
            $html .= '<td>' . $data['status'] . '</td>';
            $html .= '</tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        
        echo $html;
    }

    public function print_backup($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            // header('Content-Type: application/vnd.ms-excel');
            // header("Content-Disposition: attachment; filename=item_rm_$format.xls");

            // export with special characters and symbol : perbaiki jg output excel dr modul Master Item ketika ada symbol ˚ atau symbol lainnya hasilnya disamakan dgn data gridnya (Bu Nina)
            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header("Content-Disposition: attachment; filename=item_rm_$format.xls");            
            echo "\xEF\xBB\xBF";
            echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as item_category_name, c.name as item_family_name, d.number as item_sub_family_number');
        $this->db->from('item_rm a');
        $this->db->join('item_categories b', 'a.item_category_id = b.id');
        $this->db->join('item_familys c', 'a.item_family_id = c.id');
        $this->db->join('item_family_subs d', 'a.item_sub_family_id = d.id','left');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#item_rm {border-collapse: collapse;width: 100%;font-size: 12px;}#item_rm td, #item_rm th {border: 1px solid #ddd;padding: 2px;}#item_rm tr:nth-child(even){background-color: #f2f2f2;}#item_rm tr:hover {background-color: #ddd;}#item_rm th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER ITEM</h3>
            </div>
        </center>
        
        <table id="item_rm" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product ID</th>
                <th>Product No.</th>
                <th>Part Name</th>
                <th>UOM</th>
                <th>Division</th>
                <th>Category</th>
                <th>Product Family</th>
                <th>Color</th>
                <th>Product Family Sub</th>
                <th>Account No.</th>
                <th>Account Name</th>
                <th>Kind</th>
                <th>Length</th>
                <th>Width</th>
                <th>Thickness</th>
                <th>Diameter</th>
                <th>Density</th>
                <th>Volume</th>
                <th>Weight GR</th>
                <th>Weight KG</th>
                <th>Description</th>
                <th>Supply</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['id'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['division'] . '</td>
                        <td>' . $data['item_category_name'] . '</td>
                        <td>' . $data['item_family_name'] . '</td>
                        <td>' . $data['color'] . '</td>
                        <td>' . $data['item_sub_family_number'] . '</td>
                        <td>' . $data['account_number'] . '</td>
                        <td>' . $data['account_name'] . '</td>
                        <td>' . $data['kind'] . '</td>
                        <td>' . $data['length'] . '</td>
                        <td>' . $data['width'] . '</td>
                        <td>' . $data['thickness'] . '</td>
                        <td>' . $data['diameter'] . '</td>
                        <td>' . $data['volume'] . '</td>
                        <td>' . $data['density'] . '</td>
                        <td>' . $data['weight_gr'] . '</td>
                        <td>' . $data['weight_kg'] . '</td>
                        <td>' . $data['description'] . '</td>
                        <td>' . $data['supply'] . '</td>
                        <td>' . $data['status'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
