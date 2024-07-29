<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Master extends CI_Controller
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
            $this->load->view('planning/master');
        } else {
            redirect('error_access');
        }
    }

    public function readProdfam()
    {
        $get = $this->input->get();

        //Select Query
        $this->db->select('number, name');
        $this->db->from('mst_item_family');
        if($get != ""){
            $this->db->like($get);
        }
        $this->db->order_by('number', 'asc');
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['data' => $records]);
        die(json_encode($result));
    }

    public function readItems()
    {
        if($this->input->get()){
            $get = $this->input->get();

            //Select Query
            $this->db->select('item_id, item_name');
            $this->db->from('mst_item_raw');
            if($get != ""){
                $this->db->like($get);
            }
            $this->db->order_by('item_id', 'asc');
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