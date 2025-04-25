<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    public function index()
    {
        if ($this->session->username != "") {
            $data['config'] = $this->crud->read('config');
            $data['users'] = $this->crud->reads('users', [], ["actived" => 0, "deleted" => 0], "", "name", "asc");
            $data['session_name'] = $this->session->name;

            if (date("H:i:s") >= "05:00:00" and date("H:i:s") <= "11:00:00") {
                $data['day'] = "Good Morning";
                $data['background'] = base_url('assets/image/morning.jpg');
                $data['color'] = "black";
            } elseif (date("H:i:s") >= "11:00:00" and date("H:i:s") <= "18:00:00") {
                $data['day'] = "Good Afternoon";
                $data['background'] = base_url('assets/image/afternoon.jpg');
                $data['color'] = "black";
            } else {
                $data['day'] = "Good Night";
                $data['background'] = base_url('assets/image/night.jpg');
                $data['color'] = "white";
            }

            $this->load->view('template/header');
            $this->load->view('dashboard/dashboard', $data);
        } else {
            redirect('error_session');
        }
    }
    
    public function testBpi()
    {
        $this->bpi = $this->load->database('bpi', TRUE);
        
        $this->bpi->select("*");
        $this->bpi->from('worko');
        $this->bpi->where("wo_no" , '001/PPC/05/24');
        // $this->bpi->limit(10);
        $data = $this->bpi->get()->result_array();

        die(json_encode($data));
    }

    public function bpiPeriod()
    {
        $this->bpi = $this->load->database('bpi', TRUE);
        
        $this->bpi->select("TO_CHAR(datesupply::date, 'yyyymm') as period");
        $this->bpi->from('worko');
        $this->bpi->group_by("TO_CHAR(datesupply::date, 'yyyymm')");
        // $this->bpi->like("TO_CHAR(datesupply::date, 'yyyymm')", "2024");
        $this->bpi->order_by("period", "desc");
        $data = $this->bpi->get()->result_array();

        die(json_encode($data));
    }

    public function bpiWp($period)
    {
        $this->bpi = $this->load->database('bpi', TRUE);
        
        $this->bpi->select("lotno");
        $this->bpi->from('worko');
        $this->bpi->where("TO_CHAR(datesupply::date, 'yyyymm') = '$period'");
        $this->bpi->group_by("lotno");
        $this->bpi->order_by("lotno", "asc");
        $data = $this->bpi->get()->result_array();

        die(json_encode($data));
    }

    public function bpiWo($period, $wp)
    {
        $this->bpi = $this->load->database('bpi', TRUE);
        
        $this->bpi->select("wo_no, partno as product_no");
        $this->bpi->from('worko');
        $this->bpi->where("TO_CHAR(datesupply::date, 'yyyymm') = '$period'");
        $this->bpi->where("lotno = '$wp'");
        $this->bpi->group_by("wo_no, partno");
        $this->bpi->order_by("wo_no", "asc");
        $data = $this->bpi->get()->result_array();

        die(json_encode($data));
    }
}
