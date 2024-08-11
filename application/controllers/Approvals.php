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
        $approved_by = $this->input->post('approved_by');
        $table_name = $this->input->post('table_name');

        $datas = $this->crud->reads($table_name, [], ["approved_to" => $approved_to, "approved_by" => $approved_by]);

        foreach ($datas as $data) {
            $id = $data->id;
            $user = $this->crud->read('users', [], ["username" => $data->approved_by]);
            $approval = $this->crud->read('approvals', ["sub_department" => $user->sub_department, "department" => $user->department, "division" => $user->division], ["table_name" => $tablename]);

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
        $user = $this->crud->read('users', [], ["username" => $data->approved_by]);
        $approval = $this->crud->read('approvals', ["sub_department" => $user->sub_department, "department" => $user->department, "division" => $user->division], ["table_name" => $tablename]);
        
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
        $approved_by = $this->input->post('approved_by');
        $approved_to = $this->input->post('approved_to');
        $table_name = $this->input->post('table_name');
        $datas = $this->crud->reads($table_name, [], ["approved_to" => $approved_to, "approved_by" => $approved_by]);

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

        $this->crud->create("notifications", [
            "user_id" => $this->session->username,
            "table_name" => $tablename,
            "name" => "Disapprove",
            "description" => 'Data in Module ' . strtoupper(str_replace("_", " ", $tablename)) . ' has been disapproved',
            "log" => json_encode($read)
        ]);
        
        echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    }

    public function approvalCount()
    {
        $users = $this->crud->reads('users', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_requests = $this->crud->reads('purchase_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $delivery_notes = $this->crud->reads('delivery_notes', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sales_invoices = $this->crud->reads('sales_invoices', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);

        $totalRows = (count($users) + count($purchase_orders) + count($purchase_requests) + count($delivery_notes) + count($sales_invoices)); //+ count($forecasts) + count($stock_fg) + count($stock_wip) + count($os_so) + count($os_mpp) 
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
        $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_requests = $this->crud->reads('purchase_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $delivery_notes = $this->crud->reads('delivery_notes', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sales_invoices = $this->crud->reads('sales_invoices', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);

        foreach ($users as $user) {
            $this->approvalMessage($user->approved_by, $user->approved_to, "users");
        }

        foreach ($purchase_orders as $po) {
            $this->approvalMessage($po->approved_by, $po->approved_to, "purchase_orders");
        }

        foreach ($purchase_requests as $pr) {
            $this->approvalMessage($pr->approved_by, $pr->approved_to, "purchase_requests");
        }

        foreach ($delivery_notes as $dn) {
            $this->approvalMessage($dn->approved_by, $dn->approved_to, "delivery_notes");
        }

        foreach ($sales_invoices as $si) {
            $this->approvalMessage($si->approved_by, $si->approved_to, "sales_invoices");
        }
    }

    public function approvalMessage($approved_by, $approved_to, $table)
    {
        $user = $this->crud->read('users', [], ["username" => $approved_by]);

        if (empty($user->avatar)) {
            $avatar = base_url('assets/image/users/default.png');
        } else {
            $avatar = $user->avatar;
        }

        $link = "approvalDetail('$table', '$approved_to', '$approved_by')";
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

    public function users($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "users";
            $this->load->view('template/header', $data);
            $this->load->view('approval/users');
        }
    }

    public function purchase_requests($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_requests";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_requests');
        }
    }

    public function purchase_orders($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "purchase_orders";
            $this->load->view('template/header', $data);
            $this->load->view('approval/purchase_orders');
        }
    }

    public function delivery_notes($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "delivery_notes";
            $this->load->view('template/header', $data);
            $this->load->view('approval/delivery_notes');
        }
    }

    public function sales_invoices($approved_to, $approved_by){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "sales_invoices";
            $this->load->view('template/header', $data);
            $this->load->view('approval/sales_invoices');
        }
    }

    public function approvalUsers($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);
        
        $this->db->select('*');
        $this->db->from('users a');
        $this->db->where('approved_to', $approved_to);
        $this->db->where('approved_by', $approved_by);
        $this->db->order_by('created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPurchaseOrders($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

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
            a.month_4,
            a.discount,
            d.currency, 
            a.qty,
            a.price,
            a.total,
            a.total_sub');
        $this->db->from('purchase_orders a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
                // $this->db->join('(SELECT po_no, COUNT(status) as total_status_close FROM purchase_orders WHERE status = 1 GROUP BY po_no) g', 'a.po_no = g.po_no', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalPurchaseRequests($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.id, a.request_no, a.request_date, a.expected_date, a.request_name, a.division, 
            a.qty, 
            b.number as item_number, 
            b.name as item_name, 
            b.uom,  
            c.name as category_name');
        $this->db->from('purchase_requests a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        // $this->db->group_by('request_no');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalDeliveryNotes($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select("a.*, b.name as customer_name,
            e.delivery_order_no, 
            f.id as item_fg_id, 
            f.number as item_fg_number, 
            f.name as item_fg_name,
            c.customer_order_no, 
            c.sales_order_no,
            e.trans_type, 
            f.uom");
        $this->db->from('delivery_notes a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('customer_address d', 'b.id = d.customer_id');
        $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no and a.item_fg_id = c.item_fg_id and a.customer_id = c.customer_id');
        $this->db->join('delivery_orders e','a.delivery_order_no = e.delivery_order_no');
        $this->db->join('item_fg f', 'e.item_fg_id = f.id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSalesInvoices($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, c.number as gl_no, b.name as customer_name, a.delivery_note_no, a.sales_order_no, a.customer_order_no');
        $this->db->from('sales_invoices a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('journal_postings c', 'a.number = c.document_no', 'left');
        $this->db->join('delivery_notes d', 'a.delivery_note_no = d.delivery_note_no and a.item_fg_id = d.item_fg_id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }
}
