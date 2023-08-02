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
}
