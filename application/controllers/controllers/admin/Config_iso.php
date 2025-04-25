<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Config_iso extends CI_Controller
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
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            //Configuration Data
            $data['config'] = $this->crud->read('config_iso');

            $this->load->view('template/header', $data);
            $this->load->view('admin/config_iso');
        } else {
            redirect('error_access');
        }
    }
    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->db->update("config_iso", $post);
            if ($send) {
                echo json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            } else {
                echo log_message('error', 'There is an error in your system or data');
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
}
