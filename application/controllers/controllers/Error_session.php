<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Error_session extends CI_Controller
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
        $this->load->view('template/header');
        $this->load->view('error_session');
    }
}
