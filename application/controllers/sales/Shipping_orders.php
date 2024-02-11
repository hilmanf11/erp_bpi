<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Shipping_orders extends CI_Controller
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
        $this->form_validation->set_rules('checksheet_label', 'Label No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/shipping_orders');
        } else {
            redirect('error_access');
        }
    }

    public function getDeliveryOrders()
    {
        if ($this->input->get()) {
            $delivery_order_no = $this->input->get('delivery_order_no');

            $this->db->select('a.delivery_order_no, a.delivery_order_date, a.uom, a.sales_order_no, a.trans_type, a.remarks, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name, SUM(a.qty_del) as delivery, a.created_by, a.created_date');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            //$this->db->join("(SELECT delivery_order_no, c.item_id, SUM(a.qty) as shipping FROM shipping_orders a JOIN scan_item_receipts_fg b on a.checksheet_label = b.checksheet_label JOIN sales_orders c on a.so_number = c.number and b.so_number = c.number WHERE a.delivery_order_no = '$delivery_order_no' GROUP BY a.delivery_order_no, c.item_id) e", 'a.number = e.delivery_order_no and a.item_id = e.item_id', 'left');
            $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->group_by('b.number');

            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function getChecksheetLabel()
    {
        if ($this->input->post()) {
            $checksheet_label = $this->input->post('checksheet_label');
            $delivery_order_no = $this->input->post('delivery_order_no');

            // $this->db->select('a.qty, a.so_number, a.workorder, b.delivery');
            // $this->db->from('scan_item_receipts_fg a');
            // $this->db->join('delivery_orders b', 'a.workorder = b.workorder');
            // $this->db->where('b.number', $delivery_order_no);
            // $this->db->where('a.checksheet_label', $checksheet_label);
            // $this->db->where('a.status', '0');
            // $this->db->group_by('a.checksheet_label');

            $this->db->select("qty, so_number, workorder, '0' as delivery");
            $this->db->from('scan_item_receipts_fg');
            $this->db->where('checksheet_label', $checksheet_label);
            $this->db->where('status', '0');
            $this->db->group_by('checksheet_label');

            $totalRows = $this->db->count_all_results('', false);
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
                $shipping_orders = $this->crud->read("shipping_orders", [], ["delivery_order_no" => $post['delivery_order_no'], "checksheet_label" => $post['checksheet_label']]);

                $this->db->select("a.*");
                $this->db->from('scan_item_receipts_fg a');
                $this->db->join('sales_orders b', 'a.so_number = b.number');
                $this->db->join('delivery_orders c', 'b.item_id = c.item_id and c.customer_id = b.customer_id');
                $this->db->where('a.checksheet_label', $post['checksheet_label']);
                $this->db->where('c.number', $post['delivery_order_no']);
                $this->db->where('a.status', '0');
                $this->db->group_by('a.checksheet_label');
                $label_items = $this->db->get()->result_array();

                if ($label_items) {
                    if (!$shipping_orders) {
                        $send = $this->crud->create('shipping_orders', $post);
                        $update = $this->crud->update('scan_item_receipts_fg', ["checksheet_label" => $post['checksheet_label']], ["status" => "1"]);
                        echo $send;
                    } else {
                        echo json_encode(array("title" => "Available", "message" => "Data Shipping Orders has been Scanning", "theme" => "error"));
                    }
                } else {
                    echo json_encode(array("title" => "Not Match", "message" => "Label does not match the list item", "theme" => "error"));
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
