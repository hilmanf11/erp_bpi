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
        $this->load->model('emails');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    public function approve($api_key = "")
    {
        if($api_key == ""){
            show_error("API KEY is Empty");
        }else{
            $user = $this->crud->read("users", [], ["api_key" => $api_key]);

            if(!empty($user)){
                $id = $this->input->post('id');
                $tablename = $this->input->post('tablename');
                $data = $this->crud->read($tablename, [], ["id" => $id]);
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
                    "approved_by" => $user->username,
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
            }else{
                show_error("API KEY Cannot Found");
            }
        }
    }

    public function disapprove($api_key = "")
    {
        if($api_key == ""){
            show_error("API KEY is Empty");
        }else{
            $user = $this->crud->read("users", [], ["api_key" => $api_key]);

            $id = $this->input->post('id');
            $tablename = $this->input->post('tablename');
            $read = $this->crud->read($tablename, [], ["id" => $id]);
            $data = json_decode($read->approved_data, false);

            /* Default */
            if(empty($data)){
                $this->db->delete($tablename, ["id" => $id]);
            }else{
                $this->db->update($tablename, $data, ["id" => $id]);
            }

            $this->db->insert("notifications", [
                "created_by" => @$user->username,
                "created_date" => date("Y-m-d H:i:s"),
                "id_user" => @$user->username,
                "table_name" => $tablename,
                "name" => "Disapprove",
                "description" => 'Data in Module ' . strtoupper(str_replace("_", " ", $tablename)) . ' has been disapproved',
                "log" => json_encode($read)
            ]);
            
            echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
        }
    }

    public function approvalCount($api_key = "")
    {
        if($api_key == ""){
            show_error("API KEY is Empty");
        }else{
            $user = $this->crud->read("users", [], ["api_key" => $api_key]);

            if(empty($user)){
                show_error("API KEY Cannot Found");
            }else{
                $users = $this->crud->reads('users', [], ["approved_to" => $user->username], "", "", "", ["approved_to", "approved_by"]);
                $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $user->username], "", "", "", ["approved_to", "approved_by"]);
                $purchase_requests = $this->crud->reads('purchase_requests', [], ["approved_to" => $user->username], "", "", "", ["approved_to", "approved_by"]);

                $totalRows = (count($users) + count($purchase_orders) + count($purchase_requests));
                die(json_encode(array("total" => $totalRows)));
            }
        }
    }

    public function approvalList($api_key = "")
    {
        if($api_key == ""){
            show_error("API KEY is Empty");
        }else{
            $user = $this->crud->read("users", [], ["api_key" => $api_key]);
            if(empty($user)){
                show_error("API KEY Cannot Found");
            }else{
                //Users
                $users = $this->crud->reads('users', [], ["approved_to" => $user->username], "", "", "", ["approved_to", "approved_by"]);
                $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $user->username], "", "", "", ["approved_to", "approved_by"]);
                $purchase_requests = $this->crud->reads('purchase_requests', [], ["approved_to" => $user->username], "", "", "", ["approved_to", "approved_by"]);
                
                $data = array();
                foreach ($users as $user) {
                    $name = $this->crud->read('users', [], ["username" => $user->approved_by]);

                    if(empty($user->updated_date)){
                        $created_date = $user->created_date;
                    }else{
                        $created_date = $user->updated_date;
                    }

                    $data[] = array(
                        "approved_by" => $user->approved_by,
                        "approved_to" => $user->approved_to,
                        "module" => "users",
                        "name" => $name->name,
                        "avatar" => $name->avatar,
                        "message" => "Sent a request to approve data USER",
                        "created_date" => $created_date,
                    );
                }
                foreach ($purchase_orders as $po) {
                    $name = $this->crud->read('users', [], ["username" => $po->approved_by]);

                    if(empty($po->updated_date)){
                        $created_date = $po->created_date;
                    }else{
                        $created_date = $po->updated_date;
                    }

                    $data[] = array(
                        "approved_by" => $po->approved_by,
                        "approved_to" => $po->approved_to,
                        "module" => "purchase_orders",
                        "name" => $name->name,
                        "avatar" => $name->avatar,
                        "message" => "Sent a request to approve data PURCHASE ORDERS",
                        "created_date" => $created_date,
                    );
                }
                foreach ($purchase_requests as $pr) {
                    $name = $this->crud->read('users', [], ["username" => $pr->approved_by]);

                    if(empty($pr->updated_date)){
                        $created_date = $pr->created_date;
                    }else{
                        $created_date = $pr->updated_date;
                    }

                    $data[] = array(
                        "approved_by" => $pr->approved_by,
                        "approved_to" => $pr->approved_to,
                        "module" => "purchase_requests",
                        "name" => $name->name,
                        "avatar" => $name->avatar,
                        "message" => "Sent a request to approve data PURCHASE REQUESTS",
                        "created_date" => $created_date,
                    );
                }

                die(json_encode(array("results" => $data)));
            }
        }
    }

    public function approvalUsers()
    {
        if($this->input->post()){
            $approved_to = $this->input->post('approved_to');
            $approved_by = $this->input->post('approved_by');

            //Select Query
            $this->db->select('*');
            $this->db->from('users');
            $this->db->where('approved_to', $approved_to);
            $this->db->where('approved_by', $approved_by);
            $this->db->order_by('created_date', 'desc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }else{
            show_error("Cannot Process your Request");
        }
    }

    public function approvalPurchaseOrders()
    {
        if($this->input->post()){
            $approved_to = $this->input->post('approved_to');
            $approved_by = $this->input->post('approved_by');

            //Select Query
            $this->db->select('a.purchase_request_no, a.purchase_order_no, a.purchase_order_date, a.product_family_name, b.name as supplier_name, a.approved_to, a.approved_by, d.name as maker_name, SUM(a.qty) as total_qty, SUM(a.total) as total_amount');
            $this->db->from('purchase_orders a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('supplier_items c', 'c.supplier_id = b.id and a.item_id = c.item_id');
            $this->db->join('mst_maker d', 'c.maker_code = d.number');
            $this->db->where('a.approved_to', $approved_to);
            $this->db->where('a.approved_by', $approved_by);
            $this->db->group_by('a.purchase_order_no');
            $this->db->order_by('a.created_date', 'desc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }else{
            show_error("Cannot Process your Request");
        }
    }

    public function approvalPurchaseOrderDetails()
    {
        if($this->input->post()){
            $purchase_order_no = $this->input->post('purchase_order_no');
            $approved_to = $this->input->post('approved_to');
            $approved_by = $this->input->post('approved_by');

            //Select Query
            $this->db->select('a.*, b.name as supplier_name, d.name as maker_name');
            $this->db->from('purchase_orders a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('supplier_items c', 'c.supplier_id = b.id and a.item_id = c.item_id');
            $this->db->join('mst_maker d', 'c.maker_code = d.number');
            $this->db->where('a.purchase_order_no', $purchase_order_no);
            $this->db->where('a.approved_to', $approved_to);
            $this->db->where('a.approved_by', $approved_by);
            $this->db->group_by('a.id');
            $this->db->order_by('a.created_date', 'desc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }else{
            show_error("Cannot Process your Request");
        }
    }

    public function approvalPurchaseRequests()
    {
        if($this->input->post()){
            $approved_to = $this->input->post('approved_to');
            $approved_by = $this->input->post('approved_by');

            //Select Query
            $this->db->select('a.approved_to, a.approved_by, a.p_month, a.p_year, a.revision, a.purchase_request_no, a.purchase_request_date, a.product_family, b.name as supplier_name, SUM(a.purchase_request) as total_pr');
            $this->db->from('purchase_requests a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->where('a.approved_to', $approved_to);
            $this->db->where('a.approved_by', $approved_by);
            $this->db->group_by('a.purchase_request_no');
            $this->db->order_by('a.purchase_request_no', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }else{
            show_error("Cannot Process your Request");
        }
    }

    public function approvalPurchaseRequestDetails()
    {
        if($this->input->post()){
            $purchase_request_no = $this->input->post('purchase_request_no');
            $approved_to = $this->input->post('approved_to');
            $approved_by = $this->input->post('approved_by');

            //Select Query
            $this->db->select('a.*, b.name as supplier_name');
            $this->db->from('purchase_requests a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->where('a.approved_to', $approved_to);
            $this->db->where('a.approved_by', $approved_by);
            $this->db->where('a.purchase_request_no', $purchase_request_no);
            $this->db->order_by('a.created_date', 'desc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }else{
            show_error("Cannot Process your Request");
        }
    }
}
