<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class M_master_users extends CI_Controller
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

    public function user()
    {
        $post = $this->input->post();
        $user = $this->crud->read('users', [], [
            'deleted' => 0,
            'username' => $post['username'],
        ]);

        if (empty($user)) {
            show_error("User not found in BPI");
        } else {
            $value = array(
                // "number" => $user->number,
                "name" => $user->name,
                "username" => $user->username,
                // "password" => $user->password,
                "email" => $user->email,
                "phone" => $user->phone,
                "division" => $user->division,
                "department" => $user->department,
                "sub_department" => $user->sub_department,
                "position" => $user->position,
                "avatar" => $user->avatar,
                "api_key" => $user->api_key,
                "created_date" => date('Y-m-d H:i:s'),
            );
            //$this->db->insert('master_user', $value);
            die(json_encode(array("user" => $value)));
        }
    }
}
