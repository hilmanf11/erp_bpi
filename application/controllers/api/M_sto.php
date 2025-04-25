<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class M_sto extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->model('emails');
    }

    public function autoid()
    {
        $date = date("Ymd");
        $sql = $this->db->query("SELECT max(`id`) as kode FROM sto_raw_materials WHERE id like '%$date%'");
        $row = $sql->row();
        $kode = $row->kode;

        if ($kode == NULL) {
            $autoid        = $date . sprintf("%06s", $kode + 1);
        } else {
            $autoid        = (int) $kode + 1;
        }

        return $autoid;
    }

    public function autoidFG()
    {
        $date = date("Ymd");
        $sql = $this->db->query("SELECT max(`id`) as kode FROM sto_finish_goods WHERE id like '%$date%'");
        $row = $sql->row();
        $kode = $row->kode;

        if ($kode == NULL) {
            $autoid        = $date . sprintf("%06s", $kode + 1);
        } else {
            $autoid        = (int) $kode + 1;
        }

        return $autoid;
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    public function readDivision()
    {
        $querys = $this->crud->query("SELECT id,`name`,`number` from divisions where deleted = '0' ");
        die(json_encode($querys));
    }

    public function readCategory()
    {
        $querys = $this->crud->query("SELECT id,`name`,`number` from item_categories where number != 'FG'");
        die(json_encode($querys));
    }

    //https://bpi.astechno.net/erp-bpi/api/m_sto/readProdFam/C01
    public function readProdFam($item_category_id)
    {
        $querys = $this->crud->reads('item_familys', ["item_category_id" => $item_category_id]);
        die(json_encode($querys));
    }

    // -----------------------------------------------------RM--------------------------------------------------------

    public function getPoReceiptRM()
    {
        // Pastikan API menerima permintaan POST
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $label_no = $this->input->post('label_no');
            if (!$label_no) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400) // Bad Request
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Label No is required']));
                return;
            }

            $this->db->select('a.label_no, a.qty, b.item_rm_id, c.uom');
            $this->db->from('purchase_order_labels a');
            $this->db->join('scan_item_receipts d', 'a.label_no = d.label_no','left');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            $this->db->join('item_rm c', 'b.item_rm_id = c.id');
            $this->db->where('a.label_no', $label_no);
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

            if (!$records) {
                $this->db->select("a.label_no, a.qty, a.item_rm_id, b.uom");
                $this->db->from('new_barcode a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('scan_item_receipts c', 'a.label_no = c.label_no','left');
                $this->db->where('a.label_no', $label_no);
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();

                if(!$records){
                    $this->db->select('a.label_no, a.qty, b.item_rm_id, c.uom');
                    $this->db->from('barcode_divides a');
                    $this->db->join('new_barcode b', 'a.reff = b.label_no');
                    $this->db->join('item_rm c', 'b.item_rm_id = c.id');
                    $this->db->where('a.deleted', 0);
                    $this->db->where('a.label_divided', $label_no);
                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();

                    if (!$records) {
                        $this->db->select('a.label_no, a.qty, c.item_rm_id, d.uom, "barcode_divides_por" as type');
                        $this->db->from('barcode_divides a');
                        $this->db->join('purchase_order_labels b', 'a.reff = b.receipt_id');
                        $this->db->join('purchase_order_receipts c', 'b.receipt_id = c.receipt_id');
                        $this->db->join('item_rm d', 'c.item_rm_id = d.id');
                        $this->db->where('a.deleted', 0);
                        $this->db->where('a.label_divided', $label_no);
                        $this->db->group_by('a.label_divided');
                        $totalRows = $this->db->count_all_results('', false);
                        $records = $this->db->get()->result_array();
                    }
                }
            }

            // Format response
            $response = [
                'status' => 'success',
                'total' => $totalRows,
                'data' => $records
            ];

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            // Jika bukan POST, return error
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(405) // Method Not Allowed
                ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid request method']));
        }
    }

    public function saveDataRM()
    {
        // Pastikan ini adalah request POST
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            // Ambil data dari POST request
            $post = json_decode(file_get_contents('php://input'), true); // untuk data JSON dari Android

            if (empty($post['label_no']) || empty($post['item_rm_id']) || empty($post['category_id']) || empty($post['product_family_id']) || empty($post['division'])) {
                echo json_encode(array("status" => "error", "message" => "Invalid data input"));
                return;
            }

            $category = $post['category_id'];
            $prod_fam = $post['product_family_id'];
            $division = $post['division'];

            $item_receipts = $this->crud->read("sto_raw_materials", [], ["label_no" => $post['label_no'],"period_month" => $post['period_month'],"period_year" => $post['period_year']]);

            $item_rm = $this->crud->read("item_rm", [], ["id" => $post['item_rm_id']]);

            $item_category = $this->crud->read("item_categories", [], ["id" => $item_rm->item_category_id ?? null]);
            $item_familys = $this->crud->read("item_familys", [], ["id" => $item_rm->item_family_id ?? null]);

            if (!$item_rm) {
                echo json_encode(array("status" => "error", "message" => "Item RM not found"));
                return;
            }

            if ($category != $item_rm->item_category_id || $prod_fam != $item_rm->item_family_id || $division != $item_rm->division) {
                $message = "Category or Product Family mismatch. This Label Division is {$item_rm->division}, Category is {$item_category->name}, and Prod Fam is {$item_familys->name}";
                echo json_encode(array("status" => "error", "message" => $message));
                return;
            }

            if ($item_receipts) {
                echo json_encode(array("status" => "error", "message" => "Data Label No has already been scanned"));
                return;
            }

            // $send = $this->crud->create('sto_raw_materials', $post);
            $id = $this->autoid();

            $insert_data = [
                'id' => $id,
                'label_no' => $post['label_no'],
                'item_rm_id' => $post['item_rm_id'],
                'category_id' => $post['category_id'],
                'product_family_id' => $post['product_family_id'],
                'period_month' => $post['period_month'],
                'period_year' => $post['period_year'],
                'division' => $post['division'],
                'uom' => $post['uom'],
                'qty' => $post['qty'],
                'created_by' => $post['username'],
                'created_date' => date('Y-m-d H:i:s'),
            ];

            $send = $this->db->insert('sto_raw_materials', $insert_data);

            if ($send) {
                echo json_encode(array("status" => "success", "message" => "Data has been saved successfully"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Failed to save data"));
            }
        } else {
            echo json_encode(array("status" => "error", "message" => "Invalid request method"));
        }
    }

    public function reportRM()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            // $postData = json_decode(file_get_contents('php://input'), true); // untuk RAW
            $postData = $this->input->post(); // untuk FORM
            $username = $postData['username'] ?? null;
            $date = $postData['date'] ?? null;
            if (!$username) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400) // Bad Request
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Username is required']));
                return;
            }

            if (!$date) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400) // Bad Request
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Date is required']));
                return;
            }

            $this->db->select('a.label_no, c.number as item_number, c.name as item_name, a.uom, a.qty, 
                c.division, d.name as category, e.name as product_family, f.location, 
                a.created_by, a.created_date');
            $this->db->from('sto_raw_materials a');
            $this->db->join('purchase_order_labels b', 'a.label_no = b.label_no', 'left');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
            $this->db->join('item_categories d', 'a.category_id = d.id', 'left');
            $this->db->join('item_familys e', 'a.product_family_id = e.id', 'left');
            $this->db->join('warehouse_location_items f', 'a.item_rm_id = f.item_rm_id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->where('a.created_by', $username);
            $this->db->like('a.created_date', $date);
            $this->db->group_by('a.label_no');
            $this->db->order_by('a.created_date', 'DESC');
            $this->db->order_by('d.name', 'ASC');
            $this->db->order_by('e.name', 'ASC');

            $records = $this->db->get()->result_array();

            if (!$records) {
                $this->db->reset_query();
                $this->db->select('a.label_no, c.number as item_number, c.name as item_name, a.uom, a.qty, 
                    c.division, d.name as category, e.name as product_family, f.location, 
                    a.created_by, a.created_date');
                $this->db->from('sto_raw_materials a');
                $this->db->join('new_barcode b', 'a.label_no = b.label_no', 'left');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
                $this->db->join('item_categories d', 'a.category_id = d.id', 'left');
                $this->db->join('item_familys e', 'a.product_family_id = e.id', 'left');
                $this->db->join('warehouse_location_items f', 'a.item_rm_id = f.item_rm_id', 'left');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.status', 0);
                $this->db->like('a.created_date', $date);
                $this->db->where('a.created_by', $username);
                $this->db->group_by('a.label_no');
                $this->db->order_by('a.created_date', 'DESC');
                $this->db->order_by('d.name', 'ASC');
                $this->db->order_by('e.name', 'ASC');
                $records = $this->db->get()->result_array();
            }

            // Format response
            $response = [
                'status' => 'success',
                'data' => $records
            ];

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(405) // Method Not Allowed
                ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid request method']));
        }
    }

    // -----------------------------------------------------FG--------------------------------------------------------

    public function getPoReceiptFG()
    {
        // Pastikan API menerima permintaan POST
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $label_no = $this->input->post('label_no');
            if (!$label_no) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400) // Bad Request
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Label No is required']));
                return;
            }

            $this->db->select('f.checksheet_label as label_no, a.item_fg_id, d.uom, c.lot_no, f.qty, b.prod_date, b.packing_date, b.shift,
            b.op_1, qc_1');
            $this->db->from('wip_receipts a');
            $this->db->join('checksheets b', 'a.checksheet_number = b.number');
            $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id');
            $this->db->join('wip_receipt_labels f', 'a.checksheet_number = f.checksheet_number');
            $this->db->join('scan_item_receipts_fg g', 'f.checksheet_label = g.checksheet_label','left');
            $this->db->where('f.checksheet_label', $label_no);
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();

            if (!$records) {
                $this->db->select('f.checksheet_label as label_no, a.item_fg_id, d.uom, c.lot_no, f.qty, b.prod_date, b.packing_date, b.shift,
                b.op_1, qc_1');
                $this->db->from('wip_receipts a');
                $this->db->join('checksheets b', 'a.checksheet_number = b.number');
                $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
                $this->db->join('item_fg d', 'a.item_fg_id = d.id');
                $this->db->join('wip_receipt_boxs f', 'a.checksheet_number = f.checksheet_number');
                $this->db->join('scan_item_receipts_fg g', 'f.checksheet_label = g.checksheet_label');
                $this->db->where('f.checksheet_label', $label_no);
    
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();

                if (!$records) {
                    $this->db->select('a.label_no, a.item_fg_id, b.uom, a.lot_no, a.qty, a.prod_date, a.packing_date, a.shift,
                    a.op_1, a.qc_1');
                    $this->db->from('new_barcode_fg a');
                    $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                    $this->db->join('scan_item_receipts_fg c', 'a.label_no = c.checksheet_label');
                    $this->db->where('a.label_no', $label_no);

                    $totalRows = $this->db->count_all_results('', false);
                    $records = $this->db->get()->result_array();

                    if (!$records) {
                        $this->db->select('a.label_divided as label_no, b.item_fg_id, c.uom, b.lot_no, a.qty, b.prod_date, b.packing_date, b.shift,
                        b.op_1, b.qc_1,"new_barcode_devided" as type');
                        $this->db->from('barcode_divides_fg a');
                        $this->db->join('new_barcode_fg b', 'a.reff = b.label_no');
                        $this->db->join('item_fg c', 'b.item_fg_id = c.id','left');
                        // $this->db->join('scan_item_receipts_fg d', 'a.label_divided = d.checksheet_label');
                        $this->db->where('a.label_divided', $label_no);

                        $totalRows = $this->db->count_all_results('', false);
                        $records = $this->db->get()->result_array();
    
                        if (!$records) {
                            $this->db->select('a.label_divided as label_no, c.item_fg_id, d.uom, c.lot_no, a.qty, c.prod_date, c.packing_date, c.shift,
                            c.op_1, c.qc_1,"wip_boxs_divided" as type');
                            $this->db->from('barcode_divides_fg a');
                            $this->db->join('wip_receipt_boxs b', 'a.reff = b.checksheet_label');
                            $this->db->join('checksheets c', 'b.checksheet_number = c.number','left');
                            $this->db->join('item_fg d', 'c.item_fg_id = d.id','left');
                            $this->db->where('a.label_divided', $label_no);

                            $totalRows = $this->db->count_all_results('', false);
                            $records = $this->db->get()->result_array();
    
                            if (!$records) {
                                $this->db->select('a.label_divided as label_no, c.item_fg_id, d.uom, c.lot_no, a.qty, c.prod_date, c.packing_date, c.shift,
                                c.op_1, c.qc_1,"wip_boxs_label" as type');
                                $this->db->from('barcode_divides_fg a');
                                $this->db->join('wip_receipt_labels b', 'a.reff = b.checksheet_label');
                                $this->db->join('checksheets c', 'b.checksheet_number = c.number','left');
                                $this->db->join('item_fg d', 'c.item_fg_id = d.id','left');
                                $this->db->where('a.label_divided', $label_no);

                                $totalRows = $this->db->count_all_results('', false);
                                $records = $this->db->get()->result_array();
                            }
                        }
                    }
                }
            }

            // Format response
            $response = [
                'status' => 'success',
                'total' => $totalRows,
                'data' => $records
            ];

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            // Jika bukan POST, return error
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(405) // Method Not Allowed
                ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid request method']));
        }
    }

    public function saveDataFG()
    {
        // Pastikan ini adalah request POST
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            // Ambil data dari POST request
            $post = json_decode(file_get_contents('php://input'), true); // untuk data JSON dari Android

            if (empty($post['label_no']) || empty($post['item_fg_id']) || empty($post['division'])) {
                echo json_encode(array("status" => "error", "message" => "Invalid data input"));
                return;
            }

            // $item_receipts = $this->crud->read("sto_finish_goods", [], ["label_no" => $post['label_no']]);
            $item_receipts = $this->crud->read("sto_finish_goods", [], ["label_no" => $post['label_no'],"period_month" => $post['period_month'],"period_year" => $post['period_year']]);

            $division = $this->crud->read("divisions", [], ["number" => $post['division']]);
            $item_fg = $this->crud->read("item_fg", [], ["id" => $post['item_fg_id']]);
            $divisions = $this->crud->read("divisions", [], ["id" => $item_fg->division_id]);

            if (!$item_fg) {
                echo json_encode(array("status" => "error", "message" => "Item FG not found"));
                return;
            }

            if ($division->id != $item_fg->division_id) {
                $message = "Division mismatch. This Label Division is {$divisions->number}";
                echo json_encode(array("status" => "error", "message" => $message));
                return;
            }

            if ($item_receipts) {
                echo json_encode(array("status" => "error", "message" => "Data Label No has already been scanned"));
                return;
            }

            // $send = $this->crud->create('sto_raw_materials', $post);
            $id = $this->autoidFG();

            $insert_data = [
                'id' => $id,
                'label_no' => $post['label_no'],
                'item_fg_id' => $post['item_fg_id'],
                'division' => $post['division'],
                'lot_no' => $post['lot_no'],
                'prod_date' => $post['prod_date'],
                'packing_date' => $post['packing_date'],
                'period_month' => $post['period_month'],
                'period_year' => $post['period_year'],
                'shift' => $post['shift'],
                'op' => $post['op_1'],
                'qc' => $post['qc_1'],
                'uom' => $post['uom'],
                'qty' => $post['qty'],
                'created_by' => $post['username'],
                'created_date' => date('Y-m-d H:i:s'),
            ];

            $send = $this->db->insert('sto_finish_goods', $insert_data);

            if ($send) {
                echo json_encode(array("status" => "success", "message" => "Data has been saved successfully"));
            } else {
                echo json_encode(array("status" => "error", "message" => "Failed to save data"));
            }
        } else {
            echo json_encode(array("status" => "error", "message" => "Invalid request method"));
        }
    }

    public function reportFG()
    {
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            // $postData = json_decode(file_get_contents('php://input'), true); // untuk RAW
            $postData = $this->input->post(); // untuk FORM
            $username = $postData['username'] ?? null;
            // $date = $postData['date'] ?? null;
            $date = date("Y-m-d");
            if (!$username) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400) // Bad Request
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Username is required']));
                return;
            }

            // if (!$date) {
            //     $this->output
            //         ->set_content_type('application/json')
            //         ->set_status_header(400) // Bad Request
            //         ->set_output(json_encode(['status' => 'error', 'message' => 'Date is required']));
            //     return;
            // }

            $this->db->select('a.label_no, c.number as item_number, c.name as item_name, a.uom, a.qty, a.lot_no, a.qc, a.op, 
            a.shift ,a.prod_date, a.packing_date, f.location, a.created_by, a.created_date, b.number as division');
            $this->db->from('sto_finish_goods a');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id','left');
            $this->db->join('warehouse_location_items f', 'a.item_fg_id = f.item_fg_id','left');
            $this->db->join('divisions b', 'c.division_id = b.id','left');
    
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->like('a.created_date', $date);
            $this->db->where('a.created_by', $username);
            $this->db->group_by('a.label_no');
            $this->db->order_by('a.created_date', 'DESC');

            $records = $this->db->get()->result_array();
        
            if (!$records) {
                $this->db->select('a.label_no, c.number as item_number, c.name as item_name, a.uom, a.qty, a.lot_no, a.qc, a.op, 
                a.shift ,a.prod_date, a.packing_date, f.location, a.created_by, a.created_date, d.number as division');
                $this->db->from('sto_finish_goods a');
                $this->db->join('new_barcode_fg b', 'a.label_no = b.label_no','left');
                $this->db->join('item_fg c', 'a.item_fg_id = c.id','left');
                $this->db->join('warehouse_location_items f', 'a.item_fg_id = f.item_fg_id','left');
                $this->db->join('divisions d', 'c.division_id = d.id','left');

                $this->db->where('a.deleted', 0);
                $this->db->where('a.status', 0);
                $this->db->like('a.created_date', $date);
                $this->db->where('a.created_by', @$username);
                $this->db->group_by('a.label_no');
                $this->db->order_by('a.created_date', 'DESC');
              
                $records = $this->db->get()->result_array();
            }

            // Format response
            $response = [
                'status' => 'success',
                'data' => $records
            ];

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_status_header(405) // Method Not Allowed
                ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid request method']));
        }
    }

    // public function reportFG()
    // {
    //     if ($this->input->server('REQUEST_METHOD') == 'POST') {
    //         $postData = $this->input->post(); // untuk FORM
    //         $username = $postData['username'] ?? null;
    //         $date = $postData['date'] ?? null;
    //         $page = $postData['page'] ?? 1; // Default page 1
    //         $perPage = 20; // Limit per page

    //         if (!$username) {
    //             $this->output
    //                 ->set_content_type('application/json')
    //                 ->set_status_header(400)
    //                 ->set_output(json_encode(['status' => 'error', 'message' => 'Username is required']));
    //             return;
    //         }

    //         if (!$date) {
    //             $this->output
    //                 ->set_content_type('application/json')
    //                 ->set_status_header(400)
    //                 ->set_output(json_encode(['status' => 'error', 'message' => 'Date is required']));
    //             return;
    //         }

    //         $offset = ($page - 1) * $perPage; // Rumus offset
    //         $this->db->select('a.label_no, c.number as item_number, c.name as item_name, a.uom, a.qty, a.lot_no, a.qc, a.op, 
    //         a.shift ,a.prod_date, a.packing_date, f.location, a.created_by, a.created_date, b.number as division');
    //         $this->db->from('sto_finish_goods a');
    //         $this->db->join('item_fg c', 'a.item_fg_id = c.id', 'left');
    //         $this->db->join('warehouse_location_items f', 'a.item_fg_id = f.item_fg_id', 'left');
    //         $this->db->join('divisions b', 'c.division_id = b.id', 'left');
    //         $this->db->where('a.deleted', 0);
    //         $this->db->where('a.status', 0);
    //         $this->db->like('a.created_date', $date);
    //         $this->db->where('a.created_by', $username);
    //         $this->db->group_by('a.label_no');
    //         $this->db->order_by('a.created_date', 'DESC');
    //         $this->db->limit($perPage, $offset); // Tambah Limit & Offset

    //         $records = $this->db->get()->result_array();

    //         // Total Data
    //         $this->db->select('COUNT(*) as total');
    //         $this->db->from('sto_finish_goods a');
    //         $this->db->where('a.deleted', 0);
    //         $this->db->where('a.status', 0);
    //         $this->db->like('a.created_date', $date);
    //         $this->db->where('a.created_by', $username);
    //         $totalRows = $this->db->count_all_results();

    //         // Format Response
    //         $response = [
    //             'status' => 'success',
    //             'data' => $records,
    //             'total_rows' => $totalRows,
    //             'total_pages' => ceil($totalRows / $perPage),
    //             'current_page' => (int) $page,
    //         ];

    //         // Return JSON Response
    //         $this->output
    //             ->set_content_type('application/json')
    //             ->set_output(json_encode($response));
    //     } else {
    //         $this->output
    //             ->set_content_type('application/json')
    //             ->set_status_header(405)
    //             ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid request method']));
    //     }
    // }

}
