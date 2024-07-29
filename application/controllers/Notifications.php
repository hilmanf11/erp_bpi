<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Notifications extends CI_Controller
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

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->db->delete('notifications', $data);
        echo $send;
    }

    public function notificationCount()
    {
        $totalRows = 0;
        $notificationUser = [];
        $menuUser = $this->crud->read("setting_users", [], ["users_id" => $this->session->username, "menus_id" => "44964312f0264429978158ada88843", "v_view" => 1]);
        if(!empty($menuUser)){
            $notificationUser = $this->crud->reads('notifications', [], ["table_name" => "users"], "", "", "", ["user_id", "table_name", "name"]);
        }

        $notificationPr = [];
        $menuPr = $this->crud->read("setting_users", [], ["users_id" => $this->session->username, "menus_id" => "20231222000002", "v_view" => 1]);
        if(!empty($menuPr)){
            $notificationPr = $this->crud->reads('notifications', [], ["table_name" => "purchase_requests"], "", "", "", ["user_id", "table_name", "name"]);
        }

        $notificationPo = [];
        $menuPo = $this->crud->read("setting_users", [], ["users_id" => $this->session->username, "menus_id" => "20240103000001", "v_view" => 1]);
        if(!empty($menuPo)){
            $notificationPo = $this->crud->reads('notifications', [], ["table_name" => "purchase_orders"], "", "", "", ["user_id", "table_name", "name"]);
        }

        $totalRows = (count($notificationUser) + count($notificationPr) + count($notificationPo));
        if ($totalRows > 0) {
            echo '<span class="badge">' . $totalRows . '</span>';
        } else {
            echo '';
        }
    }

    public function notificationList()
    {
        //Users
        $menuUser = $this->crud->read("setting_users", [], ["users_id" => $this->session->username, "menus_id" => "44964312f0264429978158ada88843", "v_view" => 1]);
        if(!empty($menuUser)){
            $notificationUser = $this->crud->reads('notifications', [], ["table_name" => "users"], "", "", "", ["user_id", "table_name", "name"]);

            foreach ($notificationUser as $user) {
                $this->notificationMessage($user->user_id, $user->table_name, $user->description, $user->name);
            }
        }

        $menuPr = $this->crud->read("setting_users", [], ["users_id" => $this->session->username, "menus_id" => "20231222000002", "v_view" => 1]);
        if(!empty($menuPr)){
            $notificationPr = $this->crud->reads('notifications', [], ["table_name" => "purchase_requests"], "", "", "", ["user_id", "table_name", "name"]);

            foreach ($notificationPr as $pr) {
                $this->notificationMessage($pr->user_id, $pr->table_name, $pr->description, $pr->name);
            }
        }

        $menuPo = $this->crud->read("setting_users", [], ["users_id" => $this->session->username, "menus_id" => "20240103000001", "v_view" => 1]);
        if(!empty($menuPo)){
            $notificationPo = $this->crud->reads('notifications', [], ["table_name" => "purchase_orders"], "", "", "", ["user_id", "table_name", "name"]);

            foreach ($notificationPo as $po) {
                $this->notificationMessage($po->user_id, $po->table_name, $po->description, $po->name);
            }
        }
    }

    public function notificationMessage($user_id, $table, $description, $name)
    {
        $user = $this->crud->read('users', [], ["username" => $user_id]);

        if (empty($user->avatar)) {
            $avatar = "default.png";
        } else {
            $avatar = $user->avatar;
        }

        $link = "notificationDetail('$user_id', '$table', '$name')";
        echo '  <li class="list-isi">
                    <a onclick="' . $link . '">
                        <table style="width: 100%;">
                            <tr>
                                <td>
                                    <div class="icon-container">
                                        <img src="assets/image/users/' . $avatar . '" class="user-online" />
                                        <div class="status-circle"></div>
                                    </div>
                                </td>
                                <td style="padding-left: 10px;">
                                    <b>' . $user->name . '</b><br>
                                    <small>'.$description.'</small>
                                </td>
                            </tr>
                        </table>
                    </a>
                </li>';
    }

    public function users($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "users";
            
            $this->load->view('template/header', $data);
            $this->load->view('notification/users');
        }
    }

    public function purchase_requests($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "purchase_requests";
            
            $this->load->view('template/header', $data);
            $this->load->view('notification/purchase_requests');
        }
    }

    public function purchase_orders($user, $name){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user'] = base64_decode($user);
            $data['name'] = base64_decode($name);
            $data['table'] = "purchase_orders";
            
            $this->load->view('template/header', $data);
            $this->load->view('notification/purchase_orders');
        }
    }

    public function notificationData($table = "", $user = "", $name = "")
    {
        $user = base64_decode($user);
        $name = base64_decode($name);

        $filters = json_decode($this->input->post('filterRules'));
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');

        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();

        //Select Query
        $this->db->select('id, log');
        $this->db->from('notifications');
        $this->db->where('table_name', $table);
        $this->db->where('user_id', $user);
        $this->db->where('name', $name);
        if (@count($filters) > 0) {
            foreach ($filters as $filter) {
                $this->db->like("log", $filter->value);
            }
        }
        $this->db->order_by('created_date', 'desc');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        //$this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();

        $hasil = array();
        foreach ($records as $record) {
            $id_notification = array("id_notification" => $record['id']);
            $log = json_decode($record['log'], true);

            if($table == "purchase_orders"){
                $supplier = $this->crud->read("suppliers", [], ["id" => $log['supplier_id']]);
                $item_rm = $this->crud->read("item_rm", [], ["id" => $log['item_rm_id']]);
                $hasil[] = ($id_notification + $log + array("supplier_name" => $supplier->name) + array("item_rm_number" => $item_rm->number));
            }elseif($table == "purchase_requests"){
                $item_rm = $this->crud->read("item_rm", [], ["id" => $log['item_rm_id']]);
                $hasil[] = ($id_notification + $log + array("item_rm_number" => $item_rm->number));
            }elseif($table == "users"){
                $divisions = $this->crud->read("divisions", [], ["number" => $log['division']]);
                $hasil[] = ($id_notification + $log + array("division" => $divisions->number));
            }else{
                $hasil[] = ($id_notification + $log);
            }
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $hasil]);
        echo json_encode($result);
    }
}
