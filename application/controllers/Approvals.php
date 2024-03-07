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
        if ($send) {
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

        foreach ($datas as $data) {
            $id = $data->id;
            $read = $this->crud->read($table_name, [], ["id" => $id]);
            $data = json_decode($read->approved_data, false);

            if (empty($data)) {
                $send = $this->crud->delete($table_name, ["id" => $id]);
            } else {
                $send = $this->db->update($table_name, $data, ["id" => $id]);
            }
        }

        echo json_encode(array("title" => "Disapproved", "message" => "All Data Disapproved Successfully", "theme" => "success"));
    }



    public function disapprove()
    {
        $id = $this->input->post('id');
        $tablename = $this->input->post('tablename');
        $read = $this->crud->read($tablename, [], ["id" => $id]);
        $data = json_decode($read->approved_data, false);

        /* Default */
        if(empty($data)){
            $send = $this->crud->delete($tablename, ["id" => $id]);
        }else{
            $send = $this->db->update($tablename, $data, ["id" => $id]);
        }
        
        echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    }


    // public function disapproveall()
    // {
    //     $created_by = $this->input->post('created_by');
    //     $approved_to = $this->input->post('approved_to');
    //     $table_name = $this->input->post('table_name');
    //     $datas = $this->crud->reads($table_name, [], ["approved_to" => $approved_to, "created_by" => $created_by]);

    //     /* Default */
    //     foreach ($datas as $data) {
    //         $send = $this->crud->delete($table_name, ["id" => $data->id]);
    //     }

    //     echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    // }

    // public function disapprove()
    // {
    //     $id = $this->input->post('id');
    //     $tablename = $this->input->post('tablename');

    //     /* Default */
    //     $send = $this->crud->delete($tablename, ["id" => $id]);
    //     echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    // }

    public function approvalCount()
    {
        $users = $this->crud->reads('users', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $forecasts = $this->crud->reads('forecasts', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $stock_fg = $this->crud->reads('stock_fg', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $stock_wip = $this->crud->reads('stock_wip', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $os_so = $this->crud->reads('os_so', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $os_mpp = $this->crud->reads('os_mpp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);


        $totalRows = (count($users) + count($purchase_orders)); //+ count($forecasts) + count($stock_fg) + count($stock_wip) + count($os_so) + count($os_mpp) 
        if ($totalRows > 0) {
            echo '<span class="badge">' . $totalRows . '</span>';
        } else {
            echo '';
        }
    }

    public function approvalList()
    {
        //Users
        $users = $this->crud->reads('users', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $forecasts = $this->crud->reads('forecasts', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $stock_fg = $this->crud->reads('stock_fg', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $stock_wip = $this->crud->reads('stock_wip', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $os_so = $this->crud->reads('os_so', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        // $os_mpp = $this->crud->reads('os_mpp', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);


        foreach ($users as $user) {
            $this->approvalMessage($user->approved_by, $user->approved_to, $user->created_by, "users");
        }

        // foreach ($forecasts as $forecast) {
        //     $this->approvalMessage($forecast->approved_by, $forecast->approved_to, $forecast->created_by, "forecasts");
        // }

        // foreach ($stock_fg as $fg) {
        //     $this->approvalMessage($fg->approved_by, $fg->approved_to, $fg->created_by, "stock_fg");
        // }

        // foreach ($stock_wip as $wip) {
        //     $this->approvalMessage($wip->approved_by, $wip->approved_to, $wip->created_by, "stock_wip");
        // }

        // foreach ($os_so as $so) {
        //     $this->approvalMessage($so->approved_by, $so->approved_to, $so->created_by, "os_so");
        // }

        // foreach ($os_mpp as $mpp) {
        //     $this->approvalMessage($mpp->approved_by, $mpp->approved_to, $mpp->created_by, "os_mpp");
        // }

        foreach ($purchase_orders as $po) {
            $this->approvalMessage($po->approved_by, $po->approved_to, $po->created_by, "purchase_orders");
        }
    }

    public function approvalMessage($approved_by, $approved_to, $created_by, $table)
    {
        $user = $this->crud->read('users', [], ["username" => $approved_by]);

        if (empty($user->avatar)) {
            $avatar = base_url('assets/image/users/default.png');
        } else {
            $avatar = $user->avatar;
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
                                    <b>' . $user->name . '</b><br>
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

    public function approvalStockFg($approved_to, $created_by)
    {
        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
        $this->db->from('stock_fg a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalStockWip($approved_to, $created_by)
    {
        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
        $this->db->from('stock_wip a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalOsSo($approved_to, $created_by)
    {
        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
        $this->db->from('os_so a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalOsMpp($approved_to, $created_by)
    {
        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
        $this->db->from('os_mpp a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalForecasts($approved_to, $created_by)
    {
        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
        $this->db->from('forecasts a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPO($approved_to, $created_by)
    {
        $this->db->select('a.id,a.po_no, a.request_no, a.total_dp,
                    a.po_date,
                    a.remarks,
                    b.number as item_number,
                    b.name as item_name,
                    c.name as item_family_name, 
                    d.name as supplier_name, 
                    d.currency, e.mpq, 
                    e.moq,
                    b.uom,
                    a.month_1,
                    a.month_2,
                    a.month_3,
                    a.discount,
                    d.currency, 
                    SUM(a.qty) as qty, 
                    SUM(a.price) as price, 
                    SUM(a.total) as total,
                    a.total_sub');
                $this->db->from('purchase_orders a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('item_familys c', 'b.item_family_id = c.id');
                $this->db->join('suppliers d', 'a.supplier_id = d.id');
                $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
                $this->db->join('(SELECT po_no, COUNT(status) as total_status_close FROM purchase_orders WHERE status = 1 GROUP BY po_no) g', 'a.po_no = g.po_no', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.created_by', $created_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }
}
