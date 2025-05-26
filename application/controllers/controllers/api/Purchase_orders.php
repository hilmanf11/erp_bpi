<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_orders extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('api');
        $this->load->library('Ciqrcode');
    }

    public function index()
    {
        if (empty($this->session->api_key)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/purchase_orders');
        } else {
            redirect('error_access');
        }
    }

    public function readSummary()
    {
        if($this->input->get()){
            $filter_month = $this->input->get('pmonth');
            $filter_year = $this->input->get('pyear');
            $filter_revision = $this->input->get('rev');
            $filter_supplier = $this->input->get('supplier');
            $filter_product_family = $this->input->get('prodfam');
            $filter_po_no = $this->input->get('pono');
            $filter_part_no = base64_decode($this->input->get('partno'));

            //Select Query
            $this->db->select('a.approved_by, a.approved_date, a.approved_to, a.purchase_order_no, a.purchase_order_date, a.product_family, 
                a.product_family_name, b.number as supplier_no, b.name as supplier_name, a.currency, SUM(a.total) as total_amount,
                SUM(a.qty) as total_qty, COUNT(c.po_no) as print_sd');
            $this->db->from('purchase_orders a');
            $this->db->join('suppliers b', "a.supplier_id = b.id", 'left');
            $this->db->join('(select DISTINCT po_no from delivery_schedules) c', "a.purchase_order_no = c.po_no", 'left');
            // $this->db->where("(a.approved_to = '' or a.approved_to is null)");
            $this->db->where('a.p_month', $filter_month);
            $this->db->where('a.p_year', $filter_year);
            $this->db->where('a.revision', $filter_revision);
            if($filter_supplier != ""){
                $this->db->where('b.number', $filter_supplier);
            }
            if($filter_product_family != ""){
                $this->db->where('a.product_family', $filter_product_family);
            }
            if($filter_po_no != ""){
                $this->db->where('a.purchase_order_no', $filter_po_no);
            }
            if($filter_part_no != ""){
                $this->db->where('a.item_id', $filter_part_no);
            }
            // $this->db->group_by('a.product_family');
            $this->db->group_by('a.purchase_order_no');
            $this->db->order_by('b.number');
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['data' => $records]);
            die(json_encode($result));
        }else{
            show_error("Cannot Process your Request");
        }
    }

    public function readDetails(){
        if($this->input->get()){
            $filter_po_no = $this->input->get('pono');

            //Select Query
            $this->db->select('a.*, b.number as supplier_no, b.name as supplier_name');
            $this->db->from('purchase_orders a');
            $this->db->join('suppliers b', "a.supplier_id = b.id", 'left');
            $this->db->where('a.purchase_order_no', $filter_po_no);
            $this->db->order_by('a.item_id');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();

            $data = array();
            $total_qty = 0;
            $total_amount = 0;
            foreach ($records as $record) {
                $total_qty += $record['qty'];
                $total_amount += $record['total'];
                array_push($data, $record);
            }

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, [
                'rows' => $data,
                'footer' => array([
                    "item_id" => "Grand Total",
                    "qty" => $total_qty,
                    "price" => ($total_amount / $total_qty),
                    "total" => $total_amount
                ])
            ]);

            echo json_encode($result);
        }else{
            show_error("Cannot Process your Request");
        }
    }

    public function readPoNo()
    {
        if($this->input->get()){
            $get = $this->input->get();

            //Select Query
            $this->db->select('purchase_order_no, purchase_order_date');
            $this->db->from('purchase_orders');
            if($get != ""){
                $this->db->like($get);
            }
            $this->db->group_by('purchase_order_no');
            $this->db->order_by('purchase_order_no', 'asc');
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();

            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['data' => $records]);
            die(json_encode($result));
        }else{
            show_error("Cannot Process your Request");
        }
    }
}