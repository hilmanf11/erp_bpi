<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Approvals extends CI_Controller
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

    public function approveall()
    {
        $approved_to = $this->input->post('approved_to');
        $created_by = $this->input->post('created_by');
        $table_name = $this->input->post('table_name');

        $datas = $this->crud->reads($table_name, [], ["approved_to" => $approved_to, "created_by" => $created_by]);
        
        foreach ($datas as $data) {
            $id = $data->id;
            $user = $this->crud->read('users', [], ["username" => $data->created_by]);
            $approval = $this->crud->read('approvals', [], ["table_name" => $table_name]);
            
            if ($data->approved == 1) {
                $users_id = @$approval->user_approval_2;
                $approved = 2;
            } elseif ($data->approved == 2) {
                $users_id = @$approval->user_approval_3;
                $approved = 3;
            } elseif ($data->approved == 3) {
                $users_id = @$approval->user_approval_4;
                $approved = 4;
            } elseif ($data->approved == 4) {
                $users_id = @$approval->user_approval_5;
                $approved = 5;
            } else {
                $users_id = "";
                $approved = 0;
            }

            $values = array(
                "approved_by" => $this->session->username,
                "approved_date" => date('Y-m-d H:i:s'),
                "approved_to" => $users_id,
                "approved" => $approved,
            );

            $send = $this->db->update($table_name, $values, ["id" => $id]);
        }

        echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
    }

    public function approve()
    {
        $id = $this->input->post('id');
        $tablename = $this->input->post('tablename');
        $data = $this->crud->read($tablename, [], ["id" => $id]);
        $user = $this->crud->read('users', [], ["username" => $data->created_by]);
        $approval = $this->crud->read('approvals', [], ["table_name" => $tablename]);

        if ($data->approved == 1) {
            $users_id = @$approval->user_approval_2;
            $approved = 2;
        } elseif ($data->approved == 2) {
            $users_id = @$approval->user_approval_3;
            $approved = 3;
        } elseif ($data->approved == 3) {
            $users_id = @$approval->user_approval_4;
            $approved = 4;
        } elseif ($data->approved == 4) {
            $users_id = @$approval->user_approval_5;
            $approved = 5;
        } else {
            $users_id = "";
            $approved = 0;
        }

        $values = array(
            "approved_by" => $this->session->username,
            "approved_date" => date('Y-m-d H:i:s'),
            "approved_to" => $users_id,
            "approved" => $approved,
        );

        $send = $this->db->update($tablename, $values, ["id" => $id]);
        if($send){
            echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
        } else {
            echo log_message('error', 'There is an error in your system or data');
        }
    }

    public function disapproveall()
    {
        $created_by = $this->input->post('created_by');
        $approved_to = $this->input->post('approved_to');
        $table_name = $this->input->post('table_name');
        $datas = $this->crud->reads($table_name, [], ["approved_to" => $approved_to, "created_by" => $created_by]);

        /* Default */
        foreach ($datas as $data) {
            $send = $this->crud->delete($table_name, ["id" => $data->id]);
        }

        echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    }

    public function disapprove()
    {
        $id = $this->input->post('id');
        $tablename = $this->input->post('tablename');

        /* Default */
        $send = $this->crud->delete($tablename, ["id" => $id]);
        echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    }

    public function approvalCount()
    {
        $users = $this->crud->reads("users", [], ["approved_to" => $this->session->username], "", "", "", ["approved_by"]);

        $totalRows = (count($users));
        if ($totalRows > 0) {
            echo '<span class="badge">' . $totalRows . '</span>';
        } else {
            echo '';
        }
    }

    public function approvalList()
    {
        //Users
        $this->db->select('b.name as fullname, a.approved_to, a.created_by, b.avatar');
        $this->db->from('users a');
        $this->db->join('users b', 'a.approved_by = b.username');
        $this->db->join('users c', 'a.approved_to = c.username');
        $this->db->where('a.approved_to', $this->session->username);
        $this->db->group_by('a.created_by');
        $users = $this->db->get()->result_object();

        if (count($users) > 0) {
            foreach ($users as $user) {
                $this->approvalMessage($user->avatar, $user->fullname, $user->approved_to, $user->created_by, "users");
            }
        }else{
            echo '  <div class="alert alert-info" role="alert">
                        Approval Not Found
                    </div>';
        }
    }

    public function approvalMessage($foto, $fullname, $approved_to, $created_by, $table){
        if ($foto == "") {
            $avatar = base_url('assets/image/users/default.png');
        } else {
            $avatar = $foto;
        }

        $link = "approvalDetail('$table', '$approved_to', '$created_by')";
        echo '  <li class="list-isi">
                    <a onclick="' . $link . '">
                        <table style="width: 100%;">
                            <tr>
                                <td>
                                    <div class="icon-container">
                                        <img src="' . $avatar . '" class="user-online" />
                                        <div class="status-circle"></div>
                                    </div>
                                </td>
                                <td style="padding-left: 10px;">
                                    <b>' . $fullname . '</b><br>
                                    <small>Sent a request to approve data <b>' . strtoupper(str_replace("_", " ", $table)) . '</b></small>
                                </td>
                            </tr>
                        </table>
                    </a>
                </li>';
    }

    public function approvalUsers($approved_to, $created_by)
    {
        $this->db->select('*');
        $this->db->from('users a');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('created_by', $created_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }
}
