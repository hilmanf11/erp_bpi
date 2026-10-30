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
        if (empty($data)) {
            $send = $this->crud->delete($tablename, ["id" => $id]);
        } else {
            $send = $this->db->update($tablename, $data, ["id" => $id]);
        }

        // $this->crud->create("notifications", [
        //     "user_id" => $this->session->username,
        //     "table_name" => $tablename,
        //     "name" => "Disapprove",
        //     "description" => 'Data in Module ' . strtoupper(str_replace("_", " ", $tablename)) . ' has been disapproved',
        //     "log" => json_encode($read)
        // ]);

        echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
    }

    public function approvalCount()
    {
        // Session habis (polling AJAX tetap jalan): jangan hitung data approved_to IS NULL
        if (empty($this->session->username)) {
            return;
        }

        $users = $this->crud->reads('users', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_requests = $this->crud->reads('purchase_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $delivery_notes = $this->crud->reads('delivery_notes', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sales_invoices = $this->crud->reads('sales_invoices', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $standard_price_rm = $this->crud->reads('standard_price_rm', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $standard_exchange_rates = $this->crud->reads('standard_exchange_rates', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $request_material_memos = $this->crud->reads('request_materials', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $scan_rm_transfers = $this->crud->reads('scan_rm_transfer', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $scan_dn_crushers = $this->crud->reads('scan_dn_crusher', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $dn_scraps = $this->crud->reads('dn_scrap', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $dn_boxs = $this->crud->reads('dn_boxs', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $cost_patterns = $this->crud->reads('cost_patterns', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $breakdown_prices = $this->crud->reads('breakdown_prices', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $setting_parameters = $this->crud->reads('setting_parameters', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $generate_mrps = $this->crud->reads('generate_mrp_finals', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $quotations = $this->crud->reads('quotations', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supply_requestions = $this->crud->reads('supply_requestions', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);

        $totalRows = (count($users) + count($purchase_orders) + count($purchase_requests) + count($delivery_notes) + count($sales_invoices) + count($standard_price_rm) + count($standard_exchange_rates) + 
        count($request_material_memos) + count($scan_rm_transfers) + count($scan_dn_crushers) + count($dn_scraps) + count($dn_boxs) + count($cost_patterns) + count($breakdown_prices) + count($setting_parameters) + 
        count($generate_mrps) + count($quotations) + count($supply_requestions)); //+ count($forecasts) + count($stock_fg) + count($stock_wip) + count($os_so) + count($os_mpp) 
        if ($totalRows > 0) {
            echo '<span class="badge">' . $totalRows . '</span>';
        } else {
            echo '';
        }
    }

    public function approvalList()
    {
        // Session habis (polling AJAX tetap jalan): jangan tampilkan data approved_to IS NULL
        if (empty($this->session->username)) {
            return;
        }

        //Users
        $users = $this->crud->reads('users', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $purchase_requests = $this->crud->reads('purchase_requests', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $delivery_notes = $this->crud->reads('delivery_notes', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $sales_invoices = $this->crud->reads('sales_invoices', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $request_material_memos = $this->crud->reads('request_materials', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $scan_rm_transfers = $this->crud->reads('scan_rm_transfer', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $scan_dn_crushers = $this->crud->reads('scan_dn_crusher', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $dn_scraps = $this->crud->reads('dn_scrap', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $dn_boxs = $this->crud->reads('dn_boxs', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $cost_patterns = $this->crud->reads('cost_patterns', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $breakdown_prices = $this->crud->reads('breakdown_prices', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $setting_parameters = $this->crud->reads('setting_parameters', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $generate_mrps = $this->crud->reads('generate_mrp_finals', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $quotations = $this->crud->reads('quotations', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);
        $supply_requestions = $this->crud->reads('supply_requestions', [], ["approved_to" => $this->session->username], "", "", "", ["approved_to", "approved_by"]);

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

        foreach ($request_material_memos as $rmm) {
            $this->approvalMessage($rmm->approved_by, $rmm->approved_to, "request_materials");
        }

        foreach ($scan_rm_transfers as $srt) {
            $this->approvalMessage($srt->approved_by, $srt->approved_to, "scan_rm_transfer");
        }

        foreach ($scan_dn_crushers as $sdc) {
            $this->approvalMessage($sdc->approved_by, $sdc->approved_to, "scan_dn_crusher");
        }

        foreach ($dn_scraps as $ds) {
            $this->approvalMessage($ds->approved_by, $ds->approved_to, "dn_scrap");
        }

        foreach ($dn_boxs as $dbx) {
            $this->approvalMessage($dbx->approved_by, $dbx->approved_to, "dn_boxs");
        }

        foreach ($cost_patterns as $cp) {
            $this->approvalMessage($cp->approved_by, $cp->approved_to, "cost_patterns");
        }

        foreach ($breakdown_prices as $bp) {
            $this->approvalMessage($bp->approved_by, $bp->approved_to, "breakdown_prices");
        }

        foreach ($setting_parameters as $sp) {
            $this->approvalMessage($sp->approved_by, $sp->approved_to, "setting_parameters");
        }

        foreach ($generate_mrps as $gm) {
            $this->approvalMessage($gm->approved_by, $gm->approved_to, "generate_mrp_finals");
        }

        foreach ($quotations as $qt) {
            $this->approvalMessage($qt->approved_by, $qt->approved_to, "quotations");
        }

        foreach ($supply_requestions as $sr) {
            $this->approvalMessage($sr->approved_by, $sr->approved_to, "supply_requestions");
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
                                    <b>' . (!empty($user) ? $user->name : "Unknown User") . '</b><br>
                                    <small>Sent a request to approve data <b>' . strtoupper(str_replace("_", " ", $table)) . '</b></small>
                                </td>
                            </tr>
                        </table>
                    </a>
                </li>';
    }

    public function users($approved_to, $approved_by)
    {
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

    public function purchase_requests($approved_to, $approved_by)
    {
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

    public function purchase_orders($approved_to, $approved_by)
    {
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

    public function delivery_notes($approved_to, $approved_by)
    {
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

    public function sales_invoices($approved_to, $approved_by)
    {
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

    public function standard_price_rm($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "standard_price_rm";
            $this->load->view('template/header', $data);
            $this->load->view('approval/standard_price_rm');
        }
    }

    public function standard_exchage_rates($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "standard_exchange_rates";
            $this->load->view('template/header', $data);
            $this->load->view('approval/standard_exchange_rates');
        }
    }

    public function request_materials($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "request_materials";
            $this->load->view('template/header', $data);
            $this->load->view('approval/request_materials');
        }
    }

    public function scan_rm_transfer($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "scan_rm_transfer";
            $this->load->view('template/header', $data);
            $this->load->view('approval/scan_rm_transfer');
        }
    }

    public function scan_dn_crusher($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "scan_dn_crusher";
            $this->load->view('template/header', $data);
            $this->load->view('approval/scan_dn_crusher');
        }
    }

    public function dn_scrap($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "dn_scrap";
            $this->load->view('template/header', $data);
            $this->load->view('approval/dn_scrap');
        }
    }

    public function dn_boxs($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "dn_boxs";
            $this->load->view('template/header', $data);
            $this->load->view('approval/dn_boxs');
        }
    }

    public function cost_patterns($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "cost_patterns";
            $this->load->view('template/header', $data);
            $this->load->view('approval/cost_patterns');
        }
    }

    public function breakdown_prices($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "breakdown_prices";
            $this->load->view('template/header', $data);
            $this->load->view('approval/breakdown_prices');
        }
    }

    public function setting_parameters($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "setting_parameters";
            $this->load->view('template/header', $data);
            $this->load->view('approval/setting_parameters');
        }
    }

    public function generate_mrp_finals($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "generate_mrp_finals";
            $this->load->view('template/header', $data);
            $this->load->view('approval/generate_mrp_finals');
        }
    }

    public function quotations($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "quotations";
            $this->load->view('template/header', $data);
            $this->load->view('approval/quotations');
        }
    }

    public function supply_requestions($approved_to, $approved_by)
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['approved_to'] = base64_decode($approved_to);
            $data['approved_by'] = base64_decode($approved_by);
            $data['table'] = "supply_requestions";
            $this->load->view('template/header', $data);
            $this->load->view('approval/supply_requestions');
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
        $this->db->order_by('d.name', 'ASC');
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
        $this->db->join('delivery_orders e', 'a.delivery_order_no = e.delivery_order_no');
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

    public function approvalStandardPriceRm($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*,b.number as item_rm_number, b.name as item_rm_name, b.uom, c.name as item_category_name, d.name as item_family_name, e.name as division_name');
        // $this->db->select('a.*');
        $this->db->from('standard_price_rm a');
        $this->db->join('item_rm b', 'a.item_rm_id=b.id', 'left');
        $this->db->join('item_categories c', 'b.item_category_id=c.id', 'left');
        $this->db->join('item_familys d', 'b.item_family_id=d.id', 'left');
        $this->db->join('divisions e', 'b.division=e.number', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalStandardExchangeRates($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('*');
        $this->db->from('standard_exchange_rates a');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalRequestMaterialMemos($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.name as item_name, b.number as item_number, c.name as supplier_name, b.uom');
        $this->db->from('request_materials a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id', 'left');
        $this->db->join('suppliers c', 'a.supplier_id = c.id','left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalScanRmTransfer($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.name as item_name, b.number as item_number,b.uom');
        $this->db->from('scan_rm_transfer a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalScanDnCrusher($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.name as item_name, b.number as item_number,b.uom');
        $this->db->from('scan_dn_crusher a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalDnScrap($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.name as item_name, b.number as item_number,b.uom');
        $this->db->from('dn_scrap a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalDnBoxs($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, b.name as item_box_name, b.code as item_box_code, b.size as item_box_size, b.color as item_box_color, b.uom');
        $this->db->from('dn_boxs a');
        $this->db->join('item_boxs b', 'a.item_box_id = b.id', 'left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalCostPattern($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select("a.*, b.uom");
        $this->db->from('cost_patterns a');
        $this->db->join('item_fg b','b.id = a.item_fg_id');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.item_fg_id', 'ASC');
        $this->db->order_by('a.p_year', 'ASC');
        $this->db->order_by('a.p_month', 'ASC');
        $this->db->order_by('a.revision', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalBreakdownPrices($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select("a.*");
        $this->db->from('breakdown_prices a');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.p_month');
        $this->db->group_by('a.p_year');
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.revision');
        $this->db->group_by('a.customer_id');
        $this->db->group_by('a.revision_quotation_number');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSettingParameters($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);


        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 100;
        $offset = ($page - 1) * $rows;
        $result = array();
        
        $this->db->select('a.*, b.number as machine_number');
        $this->db->from('setting_parameters a');
        $this->db->join('machines b','a.machine_id = b.id','left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.id', 'ASC');
        $totalRows = $this->db->count_all_results('', false);
        //Limit 1 - 10
        $this->db->limit($rows, $offset);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);

        die(json_encode($result));
    }

    public function approvalGenerateMrp($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select('a.*, CONCAT(a.p_year, a.p_month) as period, b.number as item_rm_number, b.name as item_rm_name, e.id as supplier_number, e.name as supplier_name, f.leadtime as leadtime2, c.name as product_family, d.class, d.composition, d.safety');
        $this->db->from('generate_mrp_finals a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('generate_mrp_abcclass d', 'a.item_rm_id = d.item_rm_id and a.p_month = d.p_month and a.p_year = d.p_year and a.revision = d.revision', 'left');
        $this->db->join('suppliers e', 'a.supplier_id = e.id','left');
        $this->db->join('supplier_items f', 'a.supplier_id = f.supplier_id and a.item_rm_id = f.item_rm_id','left');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.item_rm_id');
        $this->db->group_by('a.supplier_id');
        $this->db->order_by('c.name', 'asc');
        $this->db->order_by('d.class', 'asc');
        $this->db->order_by('b.number', 'asc');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalQuotations($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select("a.*");
        $this->db->from('quotations a');
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->order_by('a.item_fg_number', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function approvalSupplyReq($approved_to, $approved_by)
    {
        $approved_to = base64_decode($approved_to);
        $approved_by = base64_decode($approved_by);

        $this->db->select("a.*, 
        e.number as item_number, 
        e.name as item_name, 
        b.uom,
        f.qty_sh as qty_wo,
        f.qty_product as qty_ng,
        f.shift,
        f.type");
        $this->db->from('supply_requestions a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('supply_sheets c', 'a.workorder = c.workorder','left');
        $this->db->join('item_fg e', 'c.item_fg_id = e.id','left');
        $this->db->join('item_ng f', 'a.document = f.document','left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.approved_to', $approved_to);
        $this->db->where('a.approved_by', $approved_by);
        $this->db->group_by('a.request_no');
        $this->db->order_by('a.request_no', 'DESC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }
}
