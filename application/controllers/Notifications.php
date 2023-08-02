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

    public function notificationCount()
    {
        $this->db->select('a.*, b.name as fullname, b.avatar');
        $this->db->from('notifications a');
        $this->db->join('users b', 'a.users_id_to = b.username');
        $this->db->where('b.username', $this->session->username);
        $this->db->group_by('a.table_name');
        $this->db->order_by('a.created_date', 'DESC');
        $totalRows = $this->db->count_all_results('', false);

        if ($totalRows > 0) {
            echo '<span class="badge">' . $totalRows . '</span>';
        } else {
            echo '';
        }
    }

    public function notificationList()
    {
        $this->db->select('a.*, c.name as fullname, c.avatar');
        $this->db->from('notifications a');
        $this->db->join('users b', 'a.users_id_to = b.username');
        $this->db->join('users c', 'a.users_id_from = c.username');
        $this->db->where('b.username', $this->session->username);
        $this->db->group_by('a.table_name');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_object();

        if (count($records) > 0) {
            foreach ($records as $record) {
                if ($record->avatar == "") {
                    $avatar = base_url('assets/image/users/default.png');
                } else {
                    $avatar = $record->avatar;
                }

                $link = "notificationDetail('" . $record->table_name . "')";
                echo '<li class="list-isi">
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
                                    <b>' . $record->fullname . '</b><br>
                                    <small>' . $record->description . '</small>
                                </td>
                            </tr>
                        </table>
                    </a>
                </li>';
            }
        } else {
            echo '  <div class="alert alert-info" role="alert">
                        Notification Not Found
                    </div>';
        }
    }
}
