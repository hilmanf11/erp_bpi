<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
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

    public function login()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            //Cek login username dan password
            $user = $this->crud->read("users", [], [
                "username" => $post['username'],
                "password" => $post['password']
            ]);

            //Jika username dan password terdaftar di table users
            if ($user) {
                //Jika status actived = 1
                if ($user->actived == 1) {
                    show_error("Your account is not active");
                } else {
                    $data['message'] = 'Login Success';
                    $data['title'] = 'Login';
                    $data['theme'] = 'success';
                    $data['results'] = $user;

                    echo json_encode($data);
                }
            } else {
                show_error("Username or Password Not Exist");
            }
        } else {
            show_error("Cannot Process your Request");
        }
    }
}
