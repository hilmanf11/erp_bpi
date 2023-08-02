<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Crud extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->default = $this->load->database('default', TRUE);
        $this->load->library('uuid');
    }

    function index()
    {
        show_404();
    }

    function uuid_key()
    {
        $id = $this->uuid->v4();
        $id = str_replace('-', '', $id);
        return $id;
    }

    function autoid($table)
    {
        $date = date("Ymd");
        $sql = $this->db->query("SELECT max(`id`) as kode FROM $table WHERE id like '%$date%'");
        $row = $sql->row();
        $kode = $row->kode;

        if ($kode == NULL) {
            $autoid        = $date . sprintf("%06s", $kode + 1);
        } else {
            $autoid        = (int) $kode + 1;
        }

        return $autoid;
    }

    function query($query)
    {
        $query = $this->db->query($query);
        $records = $query->result_object();
        return $records;
    }

    function read($table, $like = [], $where = [], $limit = "", $orderfield = "", $orderby = "", $groupby = [])
    {
        $this->db->select('*');
        $this->db->from($table);
        if ($where != []) {
            $this->db->where($where);
        }
        if ($like != []) {
            $this->db->like($like);
        }
        if ($limit != "") {
            $this->db->limit($limit);
        }
        if ($orderby != "") {
            $this->db->order_by($orderfield, $orderby);
        }
        if ($groupby != []) {
            $this->db->group_by($groupby);
        }
        $records = $this->db->get()->row();
        return $records;
    }

    function reads($table, $like = [], $where = [], $limit = "", $orderfield = "", $orderby = "", $groupby = [])
    {
        $this->db->select('*');
        $this->db->from($table);
        if ($where != []) {
            $this->db->where($where);
        }
        if ($like != []) {
            $this->db->like($like);
        }
        if ($limit != "") {
            $this->db->limit($limit);
        }
        if ($orderby != "") {
            $this->db->order_by($orderfield, $orderby);
        }
        if ($groupby != []) {
            $this->db->group_by($groupby);
        }
        $records = $this->db->get()->result_object();
        return $records;
    }

    function create($table, $values)
    {
        if ($this->session->username != "") {
            $id = $this->autoid($table);
            $data = array_merge($values, [
                "id" => $id,
                "created_by" => $this->session->username,
                "created_date" => date('Y-m-d H:i:s')
            ]);

            if ($this->db->insert($table, $data)) {
                $this->logs("Create", json_encode($data), $table);
                $this->approvals($table, $id);
                return json_encode(array("title" => "Good Job", "message" => "Data Saved Successfully", "theme" => "success"));
            } else {
                return log_message('error', 'There is an error in your system or data');
            }
        } else {
            return log_message('error', 'Your Session has been Expired');
        }
    }

    function update($table, $where, $values)
    {
        if ($this->session->username != "") {
            $data   = array_merge($values, [
                "updated_by" => $this->session->username,
                "updated_date" => date('Y-m-d H:i:s')
            ]);

            $dataBefore = $this->read($table, [], $where);

            $this->db->where($where);
            if ($this->db->update($table, $data)) {
                $this->logs("Update Before", json_encode($dataBefore), $table);
                $this->logs("Update New", json_encode($data), $table);
                
                $read = $this->read($table, [], $where);
                $this->approvals($table, @$read->id);

                return json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
            } else {
                return log_message('error', 'There is an error in your system or data');
            }
        } else {
            return log_message('error', 'Your Session has been Expired');
        }
    }

    function delete($table, $data)
    {
        if ($this->session->username != "") {
            $dataBefore = $this->read($table, [], $data);

            if ($this->db->delete($table, $data)) {
                $this->logs("Delete", json_encode($dataBefore), $table);
                return json_encode(array("title" => "Good Job", "message" => "Data Deleted Successfully", "theme" => "success"));
            } else {
                return log_message('error', 'There is an error in your system or data');
            }
        } else {
            return log_message('error', 'Your Session has been Expired');
        }
    }

    function upload($filename, $extension, $path, $id = [], $table = "", $field = "")
    {
        //Setting Upload Image
        $file = $_FILES[$filename]["name"];
        $extension_explode = explode('.', $file);
        $extension_final = strtolower(end($extension_explode));
        $size = $_FILES[$filename]['size'];
        $temporary = $_FILES[$filename]['tmp_name'];
        $newname  = round(microtime(true)) . '.' . $extension_final;
        $fileSave  = base_url($path) . round(microtime(true)) . '.' . $extension_final;

        if (in_array($extension_final, $extension) === true || $file == "") {
            if ($size < 2097152) {
                if ($file == "") {
                    if ($id == []) {
                        //
                    } else {
                        $records = $this->read($table, $id);
                        return @$records->$field;
                    }
                } else {
                    if ($id == []) {
                        //
                    } else {
                        $records = $this->read($table, $id);
                        @unlink('"' . $records->$field . '"');
                    }

                    @move_uploaded_file($temporary, $path . $newname);
                    return $fileSave;
                }
            } else {
                show_error("Your file is too big a maximum of 2 mb", 200, "File Upload Error");
                exit;
            }
        } else {
            show_error("Your extension file is not recognized", 200, "File Upload Error");
            exit;
        }
    }

    function logs($action, $records, $table)
    {
        $id = $this->autoid('logs');
        $data = array(
            "created_by" => $this->session->username,
            "created_date" => date('Y-m-d H:i:s'),
            "ip_address" => $this->input->ip_address(),
            "action" => $action,
            "menu" => $table,
            "description" => $records
        );

        $this->db->insert('logs', $data);
    }

    function approvals($table, $table_id)
    {
        $query = $this->db->query("DESCRIBE $table");
        $fields = $query->result_array();

        $user = $this->read('users', [], ["username" => $this->session->username]);
        $approval = $this->read('approvals', [], ["table_name" => $table]);

        $fieldExists = false;
        foreach ($fields as $field) {
            if ($field['Field'] == "approved") {
                $fieldExists = true;
                break;
            }
        }

        if ($fieldExists) {
            if (!empty($approval)) {
                $formApprove = [
                    "approved" => 1,
                    "approved_to" => $approval->user_approval_1,
                    "approved_by" => $this->session->username,
                ];

                $this->db->where(["id" => $table_id]);
                $this->db->update($table, $formApprove);
            }
        }
    }

    // function approvals($table, $table_id)
    // {
    //     $id = $this->autoid('notifications');

    //     $user = $this->read('users', [], ["username" => $this->session->username]);
    //     //Approval
    //     $approval = $this->read('approvals', [], ["table_name" => $table, "departement_id" => @$user->departement_id]);
    //     $notifications = $this->read('notifications', [], ["table_name" => $table, "table_id" => $table_id]);
        
    //     if (!empty($approval)) {
    //         if(empty($notifications->table_id)){
    //             $this->db->insert("notifications", [
    //                 "id" => $id,
    //                 "approvals_id" => $approval->id,
    //                 "users_id_from" => $this->session->username,
    //                 "users_id_to" => $approval->user_approval_1,
    //                 "table_id" => $table_id,
    //                 "table_name" => $table,
    //                 "name" => "CREATED APPROVAL",
    //                 "description" => "Sent a request on " . date("d F Y H:i:s") . "  to approve data <b>" . strtoupper(str_replace("_", " ", $table)) . "</b>",
    //                 "status" => 1,
    //                 "created_by" => $this->session->username,
    //                 "created_date" => date('Y-m-d H:i:s'),
    //                 "deleted" => 0
    //             ]);
    //         }else{
    //             $this->db->delete("notifications", ["table_id" => $table_id, "table_name" => $table]);
    //             $this->db->insert("notifications", [
    //                 "id" => $id,
    //                 "approvals_id" => $approval->id,
    //                 "users_id_from" => $this->session->username,
    //                 "users_id_to" => $approval->user_approval_1,
    //                 "table_id" => $table_id,
    //                 "table_name" => $table,
    //                 "name" => "CREATED APPROVAL",
    //                 "description" => "Sent a request on " . date("d F Y H:i:s") . "  to approve data <b>" . strtoupper(str_replace("_", " ", $table)) . "</b>",
    //                 "status" => 1,
    //                 "created_by" => $this->session->username,
    //                 "created_date" => date('Y-m-d H:i:s'),
    //                 "deleted" => 0
    //             ]);
    //         }
    //     }
    // }

    function connectionInfo()
    {
        switch (connection_status()) {
            case CONNECTION_NORMAL:
                $txt = json_encode(array("title" => "Normal", "message" => "Connection is in a normal state", "theme" => "conn_normal"));
                break;
            case CONNECTION_ABORTED:
                $txt = json_encode(array("title" => "Normal", "message" => "Connection is aborted", "theme" => "conn_aborted"));
                break;
            case CONNECTION_TIMEOUT:
                $txt = json_encode(array("title" => "Normal", "message" => "Connection is timed out", "theme" => "conn_timeout"));
                break;
            case (CONNECTION_ABORTED & CONNECTION_TIMEOUT):
                $txt = json_encode(array("title" => "Normal", "message" => "Connection is aborted and timed out", "theme" => "conn_aborted_timeout"));
                break;
            default:
                $txt = json_encode(array("title" => "Unknown", "message" => "UNKNOWN Connection", "theme" => "conn_unknown"));
                break;
        }

        echo $txt;
    }
}
