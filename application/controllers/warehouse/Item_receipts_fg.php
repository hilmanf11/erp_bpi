<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Item_receipts_fg extends CI_Controller
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
            $this->load->view('warehouse/item_receipts_fg');
        } else {
            redirect('error_access');
        }
    }
    public function getChecksheets()
    {
        if ($this->input->get()) {
            $checksheet_number = $this->input->get('checksheet_number');
            $this->db->select('f.checksheet_number, c.so_number, c.workorder, d.number as item_number, d.name as item_name, e.name as uom, COALESCE(g.qty, 0) as qty, g.created_by, g.created_date');
            $this->db->from('wip_receipts a');
            $this->db->join('checksheets b', 'a.checksheet_number = b.number');
            $this->db->join('production_schedules c', 'b.workorder = c.workorder');
            $this->db->join('items d', 'c.item_id = d.id');
            $this->db->join('uom e', 'd.uom_id = e.id');
            $this->db->join('wip_receipt_boxs f', 'a.checksheet_number = f.checksheet_number');
            $this->db->join('scan_item_receipts_fg g', 'a.checksheet_number = g.checksheet_number and f.checksheet_label = g.checksheet_label', 'left');
            $this->db->where('a.checksheet_number', $checksheet_number);
            $this->db->group_by('f.checksheet_label');

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
            $checksheet_number = $this->input->post('checksheet_number');

            $this->db->select('f.checksheet_label, c.so_number, c.workorder, COALESCE(f.qty, 0) as qty');
            $this->db->from('wip_receipts a');
            $this->db->join('checksheets b', 'a.checksheet_number = b.number');
            $this->db->join('production_schedules c', 'b.workorder = c.workorder');
            $this->db->join('items d', 'c.item_id = d.id');
            $this->db->join('uom e', 'd.uom_id = e.id');
            $this->db->join('wip_receipt_boxs f', 'a.checksheet_number = f.checksheet_number');
            $this->db->where('f.checksheet_number', $checksheet_number);
            $this->db->where('f.checksheet_label', $checksheet_label);
            $this->db->group_by('f.checksheet_label');

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
                $item_receipts_fg = $this->crud->read("scan_item_receipts_fg", [], ["checksheet_number" => $post['checksheet_number'], "checksheet_label" => $post['checksheet_label']]);
                if (!$item_receipts_fg) {
                    $send   = $this->crud->create('scan_item_receipts_fg', $post);
                    echo $send;
                } else {
                    echo json_encode(array("title" => "Available", "message" => "Data Receipt FG has been Scanning", "theme" => "error"));
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
