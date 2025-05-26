<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Sto_finish_goods extends CI_Controller
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
        $this->form_validation->set_rules('label_no', 'Label No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/sto_finish_goods');
        } else {
            redirect('error_access');
        }
    }

    public function readsDivision()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('divisions', ["name" => $post]);
        echo json_encode($send);
    }

    public function getPoReceipt()
    {
        if ($this->input->post()) {
            $label_no = $this->input->post('label_no');
            $this->db->select('f.checksheet_label as label_no, a.item_fg_id, d.uom, c.lot_no, f.qty, b.prod_date, b.packing_date, b.shift,
            b.op_1, qc_1');
            $this->db->from('wip_receipts a');
            $this->db->join('checksheets b', 'a.checksheet_number = b.number');
            $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id');
            $this->db->join('wip_receipt_labels f', 'a.checksheet_number = f.checksheet_number');
            $this->db->join('scan_item_receipts_fg g', 'f.checksheet_label = g.checksheet_label');
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
            
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    
    public function datatables($label_no = "")
    {
        $date = date("Y-m-d");
        $period_month = $this->input->get('period_month');
        $period_year = $this->input->get('period_year');
        $filter_items = $this->input->get('filter_items');
        $user = $this->session->username;

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 20;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $this->db->select('a.label_no, c.number as item_number, c.name as item_name, a.uom, a.qty, a.lot_no, a.qc, a.op, 
        a.shift ,a.prod_date, a.packing_date, f.location, a.created_by, a.created_date, b.number as division');
        $this->db->from('sto_finish_goods a');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id','left');
        $this->db->join('warehouse_location_items f', 'a.item_fg_id = f.item_fg_id','left');
        $this->db->join('divisions b', 'c.division_id = b.id','left');

        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        // $this->db->like('a.created_date', $date);
        $this->db->where('a.created_by', @$user);

        if (!empty($period_month) && !empty($period_year)) {
            $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m') =", "$period_year-$period_month");
        }

        if ($filter_items != "") {
            $this->db->where('a.item_fg_id', $filter_items);
        }

        $this->db->group_by('a.label_no');
        $this->db->order_by('a.created_date', 'DESC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        
        if (!$records) {
            // $new_barcode = $this->crud->read('new_barcode', [], ["label_no" => base64_decode($label_no)]); 

            $this->db->select('a.label_no, c.number as item_number, c.name as item_name, a.uom, a.qty, a.lot_no, a.qc, a.op, 
            a.shift ,a.prod_date, a.packing_date, f.location, a.created_by, a.created_date, d.number as division');
            $this->db->from('sto_finish_goods a');
            $this->db->join('new_barcode b', 'a.label_no = b.label_no','left');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id','left');
            $this->db->join('warehouse_location_items f', 'a.item_fg_id = f.item_fg_id','left');
            $this->db->join('divisions d', 'c.division_id = d.id','left');

            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            // $this->db->like('a.created_date', $date);
            $this->db->where('a.created_by', @$user);

            if (!empty($period_month) && !empty($period_year)) {
                $this->db->where("DATE_FORMAT(a.created_date, '%Y-%m') =", "$period_year-$period_month");
            }

            if ($filter_items != "") {
                $this->db->where('a.item_fg_id', $filter_items);
            }
            
            $this->db->group_by('a.label_no');
            $this->db->order_by('a.created_date', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();

                $item_receipts = $this->crud->read("sto_finish_goods", [], ["label_no" => $post['label_no'],"period_month" => $post['period_month'],"period_year" => $post['period_year']]);
                $division = $this->crud->read("divisions", [], ["number" => $post['division']]);
                $item_fg = $this->crud->read("item_fg", [], ["id" => $post['item_fg_id']]);
                $divisions = $this->crud->read("divisions", [], ["id" => $item_fg->division_id]);

                if (!$item_fg) {
                    echo json_encode(array("title" => "Not Found", "message" => "Item FG not found", "theme" => "error"));
                    return;
                }

                if ($division->id != $item_fg->division_id) {
                    $message = "Division mismatch. This Label Division is {$divisions->number}";
                    echo json_encode(array("title" => "Mismatch","message" => $message,"theme" => "error"));
                    return;
                }

                if ($item_receipts) {
                    echo json_encode(array("title" => "Available", "message" => "Data Label No has been Scanned", "theme" => "error"));
                    return;
                }

                $send = $this->crud->create('sto_finish_goods', $post);

                if ($send) {
                    echo json_encode(array("title" => "Success", "message" => "Data has been saved", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => "Failed to save data", "theme" => "error"));
                }

            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
