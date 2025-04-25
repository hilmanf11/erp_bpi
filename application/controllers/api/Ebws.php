<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Ebws extends CI_Controller
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

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    public function koneksi()
    {
        $this->bpi = $this->load->database('pg', TRUE);
        $this->bpi->select("*");
        $this->bpi->from("mst_item");
        $this->bpi->limit(1);
        $totalRows = $this->bpi->count_all_results('', false);
        $records = $this->bpi->get()->result_array();

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, $records);

        die(json_encode($result));
    }
}
