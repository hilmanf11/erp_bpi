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
            $this->load->view('shipping/shipping_orders');
        } else {
            redirect('error_access');
        }
    }
    public function getDeliveryOrders()
    {
        if ($this->input->get()) {
            $do_number = $this->input->get('do_number');
            $this->db->select('a.number, a.trans_date, a.so_number, a.trans_type, a.note, b.number as item_number, b.name as item_name, c.name as uom, d.name as customer_name, SUM(a.delivery) as delivery, COALESCE(e.shipping, 0) as shipping');
            $this->db->from('delivery_orders a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->join('customers d', 'a.customer_id = d.id');
            $this->db->join('(SELECT do_number, c.item_id, SUM(a.qty) as shipping FROM shipping_orders a JOIN scan_item_receipts_fg b on a.checksheet_label = b.checksheet_label JOIN sales_orders c on a.so_number = c.number and b.so_number = c.number WHERE b.status = 1 GROUP BY a.do_number, c.item_id) e', 'a.number = e.do_number and a.item_id = e.item_id', 'left');
            $this->db->where('a.number', $do_number);
            $this->db->group_by('b.number');
            // $this->db->group_by('a.so_number');
            // $this->db->group_by('a.workorder');

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
            $do_number = $this->input->post('do_number');

            // $this->db->select('a.qty, a.so_number, a.workorder, b.delivery');
            // $this->db->from('scan_item_receipts_fg a');
            // $this->db->join('delivery_orders b', 'a.workorder = b.workorder');
            // $this->db->where('b.number', $do_number);
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
                $shipping_orders = $this->crud->read("shipping_orders", [], ["do_number" => $post['do_number'], "checksheet_label" => $post['checksheet_label']]);
                if (!$shipping_orders) {
                    $send = $this->crud->create('shipping_orders', $post);
                    $update = $this->crud->update('scan_item_receipts_fg', ["checksheet_label" => $post['checksheet_label']], ["status" => "1"]);
                    echo $send;
                } else {
                    echo json_encode(array("title" => "Available", "message" => "Data Shipping Orders has been Scanning", "theme" => "error"));
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
