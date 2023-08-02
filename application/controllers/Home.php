<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
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
            $username = $this->session->username;

            $this->db->select('b.*');
            $this->db->from('logins a');
            $this->db->join('users b', 'a.username = b.username');
            $this->db->where('b.deleted', 0);
            $this->db->where('b.actived', 0);
            $this->db->where('b.status', 0);
            $this->db->where_not_in('b.username', $username);
            $this->db->like('a.created_date', date('Y-m-d'));
            $this->db->order_by('b.name', 'ASC');
            $logins = $this->db->get()->result_object();

            $data['users'] = $logins;
            $data['config'] = $this->crud->read('config');

            $this->load->view('template/header');
            $this->load->view('home', $data);
        } else {
            redirect('login');
        }
    }

    public function menus()
    {
        if ($this->session->username != "") {
            $username = $this->session->username;
            $this->db->select('a.*');
            $this->db->from('menus a');
            $this->db->join('setting_users b', 'a.id = b.menus_id');
            $this->db->join('users c', 'b.users_id = c.username');
            $this->db->where('a.deleted', 0);
            $this->db->where('c.username', $username);
            $this->db->where('b.v_view', 1);
            $this->db->order_by('a.sort', 'ASC');
            $menus = $this->db->get()->result_array();

            die(json_encode($menus));
        } else {
            show_error("Session Expired");
        }
    }

    public function updateProfile()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->crud->update('users', ["username" => $post['username']], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function chats()
    {
        if ($this->input->post()) {
            $to_users_id = $this->input->post('to_users_id');
            $from_users_id = $this->session->id;

            $this->crud->update("chats", ["from_users_id" => $to_users_id, "to_users_id" => $from_users_id], ["status" => 1]);

            $chats = $this->crud->query("SELECT * FROM chats WHERE (to_users_id='$to_users_id' and from_users_id = '$from_users_id') or (to_users_id='$from_users_id' and from_users_id = '$to_users_id')");
            foreach ($chats as $chat) {
                if ($chat->to_users_id == $to_users_id) {
                    echo '  <div class="alert alert-warning p-2" role="alert">
                                ' . $chat->messages . '
                            </div>';
                } else {
                    echo '  <div class="alert alert-info p-2" style="text-align: right;" role="alert">
                                ' . $chat->messages . '
                            </div>';
                }
            }
        }
    }

    public function createChats()
    {
        if ($this->input->post()) {
            $from_users_id = $this->session->id;
            $to_users_id = $this->input->post('to_users_id');
            $messages = $this->input->post('messages');

            $chats = $this->crud->create("chats", ["from_users_id" => $from_users_id, "to_users_id" => $to_users_id, "messages" => $messages]);
            echo json_encode($chats);
        }
    }
}
