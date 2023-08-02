<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Issued_materials extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('item_id', 'Item ID', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/issued_materials');
        } else {
            redirect('error_access');
        }
    }
    public function getSupplySheet()
    {
        if ($this->input->post()) {
            $request_no = $this->input->post('request_no');
            $this->db->select('a.*, b.number as item_number, c.number as component_number, c.name as component_name, e.name as uom, d.period, d.wp');
            $this->db->from('supply_sheets a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('items c', 'a.component_id = c.id');
            $this->db->join('production_schedules d', 'a.workorder = d.workorder and a.item_id = d.item_id');
            $this->db->join('uom e', 'c.uom_id = e.id');
            $this->db->where('a.request_no', $request_no);
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            if ($totalRows <= 0) {
                $this->db->select("a.*, '-' as workorder, a.qty as qty_req, b.number as item_number, b.id as component_id, b.number as component_number, b.name as component_name, e.name as uom");
                $this->db->from('supply_materials a');
                $this->db->join('items b', 'a.item_id = b.id');
                $this->db->join('uom e', 'b.uom_id = e.id');
                $this->db->where('a.request_no', $request_no);
                $totalRows = $this->db->count_all_results('', false);
                //Get Data Array
                $records = $this->db->get()->result_array();
                if ($totalRows <= 0) {
                    $this->db->select("a.*, '-' as workorder, a.qty as qty_req, b.number as item_number, b.id as component_id, b.number as component_number, b.name as component_name, e.name as uom");
                    $this->db->from('supply_requestions a');
                    $this->db->join('items b', 'a.item_id = b.id');
                    $this->db->join('uom e', 'b.uom_id = e.id');
                    $this->db->where('a.request_no', $request_no);
                    $totalRows = $this->db->count_all_results('', false);
                    //Get Data Array
                    $records = $this->db->get()->result_array();
                }
            }
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    public function getPoReceipt()
    {
        if ($this->input->post()) {
            $receipt_id = $this->input->post('receipt_id');
            $this->db->select('a.label_no, b.item_id, a.qty');
            $this->db->from('purchase_order_labels a');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            $this->db->where('a.label_no', $receipt_id);
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            if (!$records) {
                $this->db->select('a.label_divided as label_no, b.item_id, a.qty');
                $this->db->from('barcode_divides a');
                $this->db->join('purchase_order_receipts b', 'a.reff = b.receipt_id');
                $this->db->where('a.label_divided', $receipt_id);
                $totalRows = $this->db->count_all_results('', false);
                $records = $this->db->get()->result_array();
            }
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    public function datatables()
    {
        if ($this->input->get()) {
            $request_no = base64_decode($this->input->get('request_no'));
            //Select Query
            $this->db->select('a.*, b.number as item_number, c.number as component_number, c.name as component_name, e.name as uom, sum(d.qty) as qty_req, f.warehouse, (sum(COALESCE(d.qty,0) + f.balance)) as balance, (sum(COALESCE(d.qty,0) - a.qty)) as qty_bal');
            $this->db->from('issued_materials a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('items c', 'a.component_id = c.id');
            $this->db->join('issued_material_details d', 'd.request_no = a.request_no and d.item_id = a.component_id', 'left');
            $this->db->join('uom e', 'c.uom_id = e.id');
            $this->db->join('wip_balances f', 'a.component_id = f.item_id and a.request_no = f.request_no', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            if ($request_no != "") {
                $this->db->where('a.request_no', $request_no);
            }
            $this->db->group_by('a.request_no');
            $this->db->group_by('a.component_id');
            $this->db->order_by('a.component_id', 'ASC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $post['request_no'], "workorder" => $post['workorder'], "item_id" => $post['item_id'], "component_id" => $post['component_id']]);
                if (!$issued_materials) {
                    $send   = $this->crud->create('issued_materials', $post);
                    echo $send;
                } else {
                    $send   = $this->crud->update('issued_materials', ["request_no" => $post['request_no'], "workorder" => $post['workorder'], "item_id" => $post['item_id'], "component_id" => $post['component_id']], $post);
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    public function create_label()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $request_no = $post['request_no'];
            $item_id = $post['item_id'];
            $totalSupply = $this->crud->query("SELECT SUM(qty) as qty FROM issued_material_details WHERE request_no = '$request_no' and item_id='$item_id'");
            $issued_material_details = $this->crud->read("issued_material_details", [], ["label_no" => $post['label_no']]);
            $purchase_order_labels = $this->crud->read("purchase_order_labels", [], ["label_no" => $post['label_no'], "status" => 1]);
            $barcode_divides = $this->crud->read("barcode_divides", [], ["label_divided" => $post['label_no'], "status" => 0]);
            $issued_materials = $this->crud->read("issued_materials", [], ["request_no" => $post['request_no'], "component_id" => $post['item_id']]);

            if (!$issued_material_details) {
                if ($purchase_order_labels) {
                    if ($issued_materials) {

                        $purchase_order_receipts = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $purchase_order_labels->receipt_id]);
                        $checkItems = $this->crud->query("SELECT a.receipt_date, b.label_no, a.receipt_id, b.receipt_id, c.label_no, d.label_no
                        FROM purchase_order_receipts a 
                        LEFT JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id
                        LEFT JOIN barcode_divides c ON b.label_no = c.label_no
                        LEFT JOIN issued_material_details d ON a.item_id = d.item_id and b.label_no = d.label_no
                        WHERE a.item_id = '$purchase_order_receipts->item_id' and a.receipt_date < '$purchase_order_receipts->receipt_date' AND d.label_no is null and c.label_no is null
                        ORDER BY receipt_date ASC");
                        
                        if (($totalSupply[0]->qty + $post['qty']) <= $issued_materials->qty || $issued_materials->qty == "0") {
                            if(count($checkItems) <= 0){
                                $send   = $this->crud->create('issued_material_details', $post);
                                echo $send;
                            }else{
                                echo json_encode(array("title" => "FIFO violations", "message" => "Please Scan Sequentially", "theme" => "error"));
                            }
                        } else {
                            echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
                        }
                    } else {
                        echo json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error"));
                    }
                } elseif ($barcode_divides) {
                    if ($issued_materials) {

                        $purchase_order_receipts = $this->crud->read("purchase_order_receipts", [], ["receipt_id" => $barcode_divides->reff]);
                        $checkItems = $this->crud->query("SELECT a.receipt_date, c.label_divided, c.label_no, a.receipt_id, b.receipt_id, d.label_no
                        FROM purchase_order_receipts a
                        LEFT JOIN purchase_order_labels b ON a.receipt_id = b.receipt_id
                        LEFT JOIN barcode_divides c ON b.label_no = c.label_no and c.type = 'SUPPLY'
                        LEFT JOIN issued_material_details d ON a.item_id = d.item_id and (b.label_no = d.label_no or c.label_divided = d.label_no)
                        WHERE a.item_id = '$purchase_order_receipts->item_id' and a.receipt_date < '$purchase_order_receipts->receipt_date' AND d.label_no is null
                        ORDER BY receipt_date ASC");

                        if (($totalSupply[0]->qty + $post['qty']) <= $issued_materials->qty || $issued_materials->qty == "0") {
                            if(count($checkItems) <= 0){
                                $send = $this->crud->create('issued_material_details', $post);
                                $update = $this->crud->update('barcode_divides', ["label_divided" => $post['label_no']], ["status" => 1]);
                                echo $send;
                            }else{
                                echo json_encode(array("title" => "FIFO violations", "message" => "Please Scan Sequentially", "theme" => "error"));
                            }
                        } else {
                            echo json_encode(array("title" => "More Then Qty", "message" => "Qty Issued <= Qty Supply", "theme" => "error"));
                        }
                    } else {
                        echo json_encode(array("title" => "Not Registered", "message" => "This label has not been registered in Supply Sheet", "theme" => "error"));
                    }
                } else {
                    echo json_encode(array("title" => "Not Scanned In", "message" => "This label has not been scanned in", "theme" => "error"));
                }
            } else {
                echo json_encode(array("title" => "Available", "message" => "Data label has been Scanning", "theme" => "error"));
            }
            
        } else {
            show_error("Cannot Process your request");
        }
    }
}
