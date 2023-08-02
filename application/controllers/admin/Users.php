<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Users extends CI_Controller
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
        //VALIDASI FORM
        $this->form_validation->set_rules('name', 'Name', 'required|min_length[5]|max_length[50]');
        $this->form_validation->set_rules('username', 'Username', 'required|min_length[5]|max_length[30]|is_unique[users.username]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[5]|max_length[30]');
        $this->form_validation->set_rules('email', 'Email', 'required|min_length[5]|max_length[30]|is_unique[users.email]|valid_email');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) == 1) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('admin/users');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $users = $this->crud->reads('users');
        echo json_encode($users);
    }
    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page   = $this->input->post('page');
            $rows   = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('*');
            $this->db->from('users');
            $this->db->where('deleted', 0);
            //Filter Automatic
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('name', 'ASC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $avatar = $this->crud->upload('avatar', ["jpg", "png", "jpeg"], 'assets/image/users/', ["username" => $post['username']], "users", "avatar");
                $postFinal = array_merge($post, ["avatar" => $avatar]);
                $users = $this->crud->create('users', $postFinal);
                //$email = $this->emails->emailRegistration($post['email'], $post['name'], $post['username'], $post['password']);
                echo $users;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id = base64_decode($this->input->get('id'));
            $upload = $this->crud->upload('avatar', ["jpg", "png", "jpeg"], 'assets/image/users/', ["id" => $id], "users", "avatar");
            $postFinal   = array_merge($this->input->post(), ["avatar" => $upload]);
            $users = $this->crud->update('users', ["id" => $id], $postFinal);
            echo $users;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $users = $this->crud->update('users', ["id" => $data['id']], ["deleted" => 1]);
        echo $users;
    }
    //PRINT DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=users_$format.xls");
        }
        //Config
        $config = $this->crud->read('config');
        $post = $this->input->get();
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('deleted', 0);
        $this->db->like($post);
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MASTER USERS</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Number ID</th>
                <th>Fullname</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Position</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['actived'] == "0") {
                $status = "Active";
            } else {
                $status = "Not Active";
            }
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['name'] . '</td>
                            <td>' . $data['username'] . '</td>
                            <td>' . $data['email'] . '</td>
                            <td>' . $data['phone'] . '</td>
                            <td>' . $data['position'] . '</td>
                            <td>' . $status . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
