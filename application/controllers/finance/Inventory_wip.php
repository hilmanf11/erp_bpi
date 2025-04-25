<?php
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Inventory_wip extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('po_no', 'PO No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();
            
            $this->load->view('template/header', $data);
            $this->load->view('finance/inventory_wip');
        } else {
            redirect('error_access');
        }
    }

    public function getData(){
        $filter_from = $this->input->post('filter_from');
        $filter_to   = $this->input->post('filter_to');
        $filter_item_fg = $this->input->post('filter_item_fg');
        $periode = date("Y-m", strtotime($filter_from));
        $periode_bf = date("Y-m", strtotime("-1 month", strtotime($filter_from)));
        $month = date("m", strtotime($filter_from));
        $year = date("m", strtotime($filter_from));

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Item Receipts
        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name, 
            a.uom,
            COALESCE(d.price) as price
        FROM item_fg a
        LEFT JOIN (SELECT DISTINCT item_fg_id, price FROM inventory_wip WHERE trans_date < '$filter_from' and trans_type = 'SCAN FG' ORDER BY trans_date DESC LIMIT 1) d ON a.id = d.item_fg_id
        WHERE a.id like '%$filter_item_fg%'
        GROUP BY a.id
        ORDER BY a.number");
        
        $data = array();
        foreach ($records as $record) {
            $item_fg_id = $record->id;
            
            $begin = $this->crud->query("SELECT item_fg_id, SUM(qty) as qty, SUM(amount) as amount, SUM(direct_material) as direct_material, SUM(direct_labor) as direct_labor, SUM(direct_foh) as direct_foh FROM inventory_wip WHERE trans_date < '$filter_from' and item_fg_id = '$item_fg_id' GROUP BY item_fg_id");

            //RECEIPT
            $receipts = $this->crud->query("SELECT a.* FROM production_schedules a WHERE a.item_fg_id = '$item_fg_id' and a.trans_date between '$filter_from' and '$filter_to'");

            //DELIVERY
            $returns = $this->crud->query("SELECT b.qty, b.workorder, b.created_by, DATE_FORMAT(b.created_date, '%Y-%m-%d') as trans_date, 
                            b.checksheet_label
                            FROM production_schedules a 
                            LEFT JOIN checksheets e ON a.workorder = e.workorder
                            LEFT JOIN scan_item_receipts_fg b ON e.number = b.checksheet_number and a.so_number = b.so_number and a.workorder = b.workorder
                            WHERE a.item_fg_id = '$item_fg_id' and DATE_FORMAT(b.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'
                            GROUP BY b.checksheet_label");
                            
            //RETURN
            $returns2 =  $this->crud->query("SELECT a.* FROM return_materials a 
                            JOIN production_schedules b ON a.workorder = b.workorder
                            WHERE b.item_fg_id = '$item_fg_id' and a.return_date between '$filter_from' and '$filter_to' GROUP BY a.return_id");
                            
            //Wip Receipt
            $in_qty = @$begin[0]->qty;
            $in_dm = @$begin[0]->direct_material;
            $in_dl = @$begin[0]->direct_labor;
            $in_foh = @$begin[0]->direct_foh;
            foreach ($receipts as $receipt) {
                $costing_product_workorder = $this->crud->read("costing_product_workorders", [], ["item_fg_id" => $item_fg_id, "periode" => $periode, "workorder" => $receipt->workorder]);

                $receipt_qty = $receipt->qty;
                $direct_material = (@$costing_product_workorder->direct_material + @$costing_product_workorder->direct_requestion);
                $direct_labor = (@$costing_product_workorder->direct_labor + @$costing_product_workorder->direct_overtime);
                $direct_foh = @$costing_product_workorder->direct_foh;
                $price_in = ((@$direct_material + $direct_labor + $direct_foh) / $receipt_qty);
                $amount_in = ($receipt_qty * $price_in);

                $data[] = array(
                    "period" => $periode,
                    "item_fg_id" => $item_fg_id,
                    "trans_type" => "PRODUCTION",
                    "created_name" => $receipt->created_by,
                    "trans_date" => $receipt->trans_date,
                    "invoice_no" => $receipt->workorder,
                    "document_no" => $receipt->so_number,
                    "uom" => $record->uom,
                    "qty" => $receipt_qty,
                    "direct_material" => $direct_material,
                    "direct_labor" => $direct_labor,
                    "direct_foh" => $direct_foh,
                    "price" => $price_in,
                    "amount" => $amount_in,
                );

                $in_qty += $receipt_qty;
                $in_dm += $direct_material;
                $in_dl += $direct_labor;
                $in_foh += $direct_foh;
            }

            //Delivery Note
            foreach ($returns as $return) {
                $direct_material = ((($in_dm / $in_qty) * $return->qty) * -1);
                $direct_labor = ((($in_dl / $in_qty) * $return->qty) * -1);
                $direct_foh = ((($in_foh / $in_qty) * $return->qty) * -1);
                $price_out = (($direct_material + $direct_labor + $direct_foh) / $return->qty);

                $data[] = array(
                    "period" => $periode,
                    "item_fg_id" => $item_fg_id,
                    "trans_type" => "SCAN FG",
                    "created_name" => $return->created_by,
                    "trans_date" => $return->trans_date,
                    "invoice_no" => $return->workorder,
                    "document_no" => $return->checksheet_label,
                    "uom" => $record->uom,
                    "qty" => ($return->qty * -1),
                    "direct_material" => $direct_material,
                    "direct_labor" => $direct_labor,
                    "direct_foh" => $direct_foh,
                    "price" => $price_out,
                    "amount" => (($return->qty * $price_out) * -1),
                );

                $in_dm -= (($in_dm / $in_qty) * $return->qty);
                $in_dl -= (($in_dl / $in_qty) * $return->qty);
                $in_foh -= (($in_foh / $in_qty) * $return->qty);
                $in_qty -= $return->qty;
            }

            //Delivery Note
            foreach ($returns2 as $return2) {
                $direct_material = ((($in_dm / $in_qty) * $return2->qty) * -1);
                $direct_labor = ((($in_dl / $in_qty) * $return2->qty) * -1);
                $direct_foh = ((($in_foh / $in_qty) * $return2->qty) * -1);
                $price_out = (($direct_material + $direct_labor + $direct_foh) / $return2->qty);

                $data[] = array(
                    "period" => $periode,
                    "item_fg_id" => $item_fg_id,
                    "trans_type" => "SCAN FG",
                    "created_name" => $return2->return_name,
                    "trans_date" => $return2->return_date,
                    "invoice_no" => $return2->workorder,
                    "document_no" => $return2->return_id,
                    "uom" => $record->uom,
                    "qty" => ($return2->qty * -1),
                    "direct_material" => $direct_material,
                    "direct_labor" => $direct_labor,
                    "direct_foh" => $direct_foh,
                    "price" => $price_out,
                    "amount" => (($return2->qty * $price_out) * -1),
                );

                $in_dm -= (($in_dm / $in_qty) * $return2->qty);
                $in_dl -= (($in_dl / $in_qty) * $return2->qty);
                $in_foh -= (($in_foh / $in_qty) * $return2->qty);
                $in_qty -= $return2->qty;
            }
        }

        $result['total'] = count($data);
        $result = array_merge($result, ['rows' => $data]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');

            $inventory_wip = $this->crud->reads("inventory_wip", [], [
                "period" => $post['period'], 
                "item_fg_id" => $post['item_fg_id'], 
                "invoice_no" => $post['invoice_no'],
                "document_no" => $post['document_no'], 
                "trans_date" => $post['trans_date']
            ]);

            if(count($inventory_wip) > 0){
                $send = $this->crud->update('inventory_wip', [
                    "period" => $post['period'], 
                    "item_fg_id" => $post['item_fg_id'], 
                    "invoice_no" => $post['invoice_no'],
                    "document_no" => $post['document_no'], 
                    "trans_date" => $post['trans_date']
                ], $post);

                echo $send;
            }else{
                $send = $this->crud->create('inventory_wip', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=inventory_wip_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_fg = $this->input->get('filter_item_fg');
        $filter_display = $this->input->get("filter_display");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $period_tb = date("Ym", strtotime($filter_from));
        $periode_tb_bf = date("Ym", strtotime("-1 month", strtotime($filter_from)));

        $periode = date("Y-m", strtotime($filter_from));
        $periode_bf = date("Y-m", strtotime("-1 month", strtotime($filter_from)));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
                <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>'.$config->description.'</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <div style="float: centet; font-size: 16px; text-align: center;">
                    <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION WIP</h3>
                    <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                </div>
            </center>
            <br><br>';

        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name, 
            a.uom,
            COALESCE(d.qty) as qty_in,
            COALESCE(d.direct_material) as direct_material_in,
            COALESCE(d.direct_labor) as direct_labor_in,
            COALESCE(d.direct_foh) as direct_foh_in,
            COALESCE(d.price) as price_in,
            COALESCE(d.amount) as amount_in,
            COALESCE(e.qty) as qty_out,
            COALESCE(e.direct_material) as direct_material_out,
            COALESCE(e.direct_labor) as direct_labor_out,
            COALESCE(e.direct_foh) as direct_foh_out,
            COALESCE(e.price) as price_out,
            COALESCE(e.amount) as amount_out
        FROM item_fg a
        LEFT JOIN (SELECT item_fg_id, price, SUM(qty) as qty, SUM(amount) as amount, SUM(direct_material) as direct_material, SUM(direct_labor) as direct_labor, SUM(direct_foh) as direct_foh FROM inventory_wip WHERE trans_date between '$filter_from' and '$filter_to' and trans_type = 'PRODUCTION' GROUP BY item_fg_id) d ON a.id = d.item_fg_id
        LEFT JOIN (SELECT item_fg_id, price, SUM(qty) as qty, SUM(amount) as amount, SUM(direct_material) as direct_material, SUM(direct_labor) as direct_labor, SUM(direct_foh) as direct_foh FROM inventory_wip WHERE trans_date between '$filter_from' and '$filter_to' and trans_type = 'SCAN FG' GROUP BY item_fg_id) e ON a.id = e.item_fg_id
        WHERE a.id like '%$filter_item_fg%'
        GROUP BY a.id
        ORDER BY a.number");

        // $trial_balance = $this->crud->query("SELECT account_number, (SUM(ending_debit) - SUM(ending_credit)) as ending_tb FROM trial_balances 
        //     WHERE period = '$period_tb' 
        //     and account_number = '4512' GROUP BY account_number");

        // $trial_balance_bf = $this->crud->query("SELECT account_number, (SUM(ending_debit) - SUM(ending_credit)) as ending_tb FROM trial_balances 
        //     WHERE period = '$periode_tb_bf' 
        //     and account_number = '4512' GROUP BY account_number");

        $html .= '<table id="customers" style="width: 200%">
                    <tr>
                        <th rowspan="2" width="20">No</th>
                        <th colspan="2" rowspan="2">Product No</th>
                        <th colspan="3" rowspan="2">Product Name</th>
                        <th rowspan="2">Uom</th>
                        <th style="background-color:#CACACA;" colspan="6">Begining Stock</th>
                        <th style="background-color:#B0FFA0;" colspan="6">In</th>
                        <th style="background-color:#FFA0A0;" colspan="6">Out</th>
                        <th style="background-color:#FCFFA0;" colspan="6">Ending Stock</th>
                    </tr>
                    <tr>
                        <th style="background-color:#CACACA;">Qty</th>
                        <th style="background-color:#CACACA;">Material</th>
                        <th style="background-color:#CACACA;">Labor</th>
                        <th style="background-color:#CACACA;">FOH</th>
                        <th style="background-color:#CACACA;">Price</th>
                        <th style="background-color:#CACACA;">Amount</th>
                        <th style="background-color:#B0FFA0;">Qty</th>
                        <th style="background-color:#B0FFA0;">Material</th>
                        <th style="background-color:#B0FFA0;">Labor</th>
                        <th style="background-color:#B0FFA0;">FOH</th>
                        <th style="background-color:#B0FFA0;">Price</th>
                        <th style="background-color:#B0FFA0;">Amount</th>
                        <th style="background-color:#FFA0A0;">Qty</th>
                        <th style="background-color:#FFA0A0;">Material</th>
                        <th style="background-color:#FFA0A0;">Labor</th>
                        <th style="background-color:#FFA0A0;">FOH</th>
                        <th style="background-color:#FFA0A0;">Price</th>
                        <th style="background-color:#FFA0A0;">Amount</th>
                        <th style="background-color:#FCFFA0;">Qty</th>
                        <th style="background-color:#FCFFA0;">Material</th>
                        <th style="background-color:#FCFFA0;">Labor</th>
                        <th style="background-color:#FCFFA0;">FOH</th>
                        <th style="background-color:#FCFFA0;">Price</th>
                        <th style="background-color:#FCFFA0;">Amount</th>
                    </tr>';

        $noh = 1;
        $total_begin = 0;
        $total_direct_material = 0;
        $total_direct_labor = 0;
        $total_direct_foh = 0;
        $total_begin_price = 0;
        $total_qty_in = 0;
        $total_direct_material_in = 0;
        $total_direct_labor_in = 0;
        $total_direct_foh_in = 0;
        $total_qty_in_price = 0;
        $total_qty_out = 0;
        $total_direct_material_out = 0;
        $total_direct_labor_out = 0;
        $total_direct_foh_out = 0;
        $total_qty_out_price = 0;
        $total_end = 0;
        $total_direct_material_end = 0;
        $total_direct_labor_end = 0;
        $total_direct_foh_end = 0;
        $total_end_price = 0;

        // $ending_qty_tb = 0;
        // foreach ($records as $record) {
        //     $item_fg_id = $record->id;
        //     $begin = $this->crud->query("SELECT item_fg_id, SUM(qty) as qty, SUM(amount) as amount FROM inventory_wip WHERE trans_date < '$filter_from' and item_fg_id = '$item_fg_id' GROUP BY item_fg_id");
        //     $ending_qty_tb += abs((@$begin[0]->qty + $record->qty_in) - abs($record->qty_out));
        // }

        foreach ($records as $record) {
            $item_fg_id = $record->id;

            $begin = $this->crud->query("SELECT item_fg_id, SUM(qty) as qty, SUM(amount) as amount, SUM(direct_material) as direct_material, SUM(direct_labor) as direct_labor, SUM(direct_foh) as direct_foh FROM inventory_wip WHERE trans_date < '$filter_from' and item_fg_id = '$item_fg_id' GROUP BY item_fg_id");
            $price = $this->crud->query("SELECT DISTINCT price FROM inventory_wip WHERE trans_date < '$filter_from' and trans_type = 'SCAN FG' and item_fg_id = '$item_fg_id' ORDER BY trans_date DESC");

            $begin_qty = @$begin[0]->qty;
            $begin_direct_material = @$begin[0]->direct_material;
            $begin_direct_labor = @$begin[0]->direct_labor;
            $begin_direct_foh = @$begin[0]->direct_foh;
            
            if($filter_from == date("Y-01-01", strtotime($filter_from))){
                $begin_price = @$price[0]->price;
            }else{
                if(@$begin[0]->qty > 0){
                    $begin_price = ((@$begin[0]->direct_material + @$begin[0]->direct_labor + @$begin[0]->direct_foh)/ @$begin[0]->qty);
                }else{
                    $begin_price = 0;
                }
            }
            
            $begin_amount = ($begin_qty * $begin_price);


            if($record->qty_in > 0){
                $price_in = ($record->amount_in / $record->qty_in);
            }else{
                $price_in = 0;
            }

            if(abs($record->qty_out) > 0){
                $price_out = abs($record->amount_out / $record->qty_out);
            }else{
                $price_out = 0;
            }

            $ending_qty = abs(@$begin[0]->qty + $record->qty_in - abs($record->qty_out));
            $ending_direct_material = abs(@$begin[0]->direct_material + $record->direct_material_in - abs($record->direct_material_out));
            $ending_direct_labor = abs(@$begin[0]->direct_labor + $record->direct_labor_in - abs($record->direct_labor_out));
            $ending_direct_foh = abs(@$begin[0]->direct_foh + $record->direct_foh_in - abs($record->direct_foh_out));
            $ending_amount = abs($begin_amount + $record->amount_in - abs($record->amount_out));

            if($ending_qty > 0){
                $ending_price = ($ending_amount / $ending_qty);
            }else{
                $ending_price = 0;
            }

            $html .= '  <tr>
                            <td style="text-align:center">' . $noh . '</td>
                            <td colspan="2">' . $record->number . '</td>
                            <td colspan="3">' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td style="text-align:right;">' . number_format($begin_qty, 2) . '</td>
                            <td style="text-align:right;">' . number_format(@$begin[0]->direct_material, 2) . '</td>
                            <td style="text-align:right;">' . number_format(@$begin[0]->direct_labor, 2) . '</td>
                            <td style="text-align:right;">' . number_format(@$begin[0]->direct_foh, 2) . '</td>
                            <td style="text-align:right;">' . number_format($begin_price, 6) . '</td>
                            <td style="text-align:right;">' . number_format(@$begin_amount, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->direct_material_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->direct_labor_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->direct_foh_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($price_in, 6) . '</td>
                            <td style="text-align:right;">' . number_format($record->amount_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format(abs($record->qty_out), 2) . '</td>
                            <td style="text-align:right;">' . number_format(abs($record->direct_material_out), 2) . '</td>
                            <td style="text-align:right;">' . number_format(abs($record->direct_labor_out), 2) . '</td>
                            <td style="text-align:right;">' . number_format(abs($record->direct_foh_out), 2) . '</td>
                            <td style="text-align:right;">' . number_format($price_out, 6) . '</td>
                            <td style="text-align:right;">' . number_format(abs($record->amount_out), 2) . '</td>
                            <td style="text-align:right;">' . number_format($ending_qty, 2) . '</td>
                            <td style="text-align:right;">' . number_format($ending_direct_material, 2) . '</td>
                            <td style="text-align:right;">' . number_format($ending_direct_labor, 2) . '</td>
                            <td style="text-align:right;">' . number_format($ending_direct_foh, 2) . '</td>
                            <td style="text-align:right;">' . number_format($ending_price, 6) . '</td>
                            <td style="text-align:right;">' . number_format($ending_amount, 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="19" style="background:#D1FFC6;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
                $html .= '  <tr>
                                <th rowspan="2" width="20"></th>
                                <th rowspan="2">No</th>
                                <th rowspan="2">Trans Type</th>
                                <th rowspan="2">Trans Date</th>
                                <th rowspan="2">Created By</th>
                                <th rowspan="2">WO / DO</th>
                                <th rowspan="2">Doc No</th>
                                <th style="background-color:#CACACA;" colspan="6">Begining Stock</th>
                                <th style="background-color:#B0FFA0;" colspan="6">In</th>
                                <th style="background-color:#FFA0A0;" colspan="6">Out</th>
                                <th style="background-color:#FCFFA0;" colspan="6">Ending Stock</th>
                            </tr>
                            <tr>
                                <th style="background-color:#CACACA;">Qty</th>
                                <th style="background-color:#CACACA;">Material</th>
                                <th style="background-color:#CACACA;">Labor</th>
                                <th style="background-color:#CACACA;">FOH</th>
                                <th style="background-color:#CACACA;">Price</th>
                                <th style="background-color:#CACACA;">Amount</th>
                                <th style="background-color:#B0FFA0;">Qty</th>
                                <th style="background-color:#B0FFA0;">Material</th>
                                <th style="background-color:#B0FFA0;">Labor</th>
                                <th style="background-color:#B0FFA0;">FOH</th>
                                <th style="background-color:#B0FFA0;">Price</th>
                                <th style="background-color:#B0FFA0;">Amount</th>
                                <th style="background-color:#FFA0A0;">Qty</th>
                                <th style="background-color:#FFA0A0;">Material</th>
                                <th style="background-color:#FFA0A0;">Labor</th>
                                <th style="background-color:#FFA0A0;">FOH</th>
                                <th style="background-color:#FFA0A0;">Price</th>
                                <th style="background-color:#FFA0A0;">Amount</th>
                                <th style="background-color:#FCFFA0;">Qty</th>
                                <th style="background-color:#FCFFA0;">Material</th>
                                <th style="background-color:#FCFFA0;">Labor</th>
                                <th style="background-color:#FCFFA0;">FOH</th>
                                <th style="background-color:#FCFFA0;">Price</th>
                                <th style="background-color:#FCFFA0;">Amount</th>
                            </tr>';
                $nod = 1;
                for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $i);
                    $inventories = $this->crud->query("SELECT * FROM inventory_wip WHERE item_fg_id = '$item_fg_id' and trans_date = '$working_date'");

                    //Wip Receipt
                    foreach ($inventories as $inventory) {
                        if($inventory->trans_type == "PRODUCTION"){
                            $qty_in = $inventory->qty;
                            $direct_material_in = $inventory->direct_material;
                            $direct_labor_in = $inventory->direct_labor;
                            $direct_foh_in = $inventory->direct_foh;
                            $price_in = $inventory->price;
                            $amount_in = $inventory->amount;
                            $qty_out = 0;
                            $direct_material_out = 0;
                            $direct_labor_out = 0;
                            $direct_foh_out = 0;
                            $price_out = 0;
                            $amount_out = 0;
                        }else{
                            $qty_in = 0;
                            $direct_material_in = 0;
                            $direct_labor_in = 0;
                            $direct_foh_in = 0;
                            $price_in = 0;
                            $amount_in = 0;
                            $qty_out = $inventory->qty;
                            $direct_material_out = $inventory->direct_material;
                            $direct_labor_out = $inventory->direct_labor;
                            $direct_foh_out = $inventory->direct_foh;
                            $price_out = $inventory->price;
                            $amount_out = $inventory->amount;
                        }

                        $qty_end = ($begin_qty + $qty_in - abs($qty_out));
                        $amount_end = abs($begin_amount + $amount_in - abs($amount_out));
                        $price_end = ($amount_end / $qty_end);

                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>'.$inventory->trans_type.'</td>
                                        <td>' . $inventory->trans_date . '</td>
                                        <td>' . $inventory->created_name . '</td>
                                        <td>' . $inventory->invoice_no . '</td>
                                        <td>' . $inventory->document_no . '</td>
                                        <td style="text-align:right; background-color:#CACACA;">' . number_format($begin_qty, 2) . '</td>
                                        <td style="text-align:right; background-color:#CACACA;">' . number_format($begin_direct_material, 2) . '</td>
                                        <td style="text-align:right; background-color:#CACACA;">' . number_format($begin_direct_labor, 2) . '</td>
                                        <td style="text-align:right; background-color:#CACACA;">' . number_format($begin_direct_foh, 2) . '</td>
                                        <td style="text-align:right; background-color:#CACACA;">' . number_format($begin_price, 6) . '</td>
                                        <td style="text-align:right; background-color:#CACACA;">' . number_format(abs($begin_amount), 2) . '</td>
                                        <td style="text-align:right; background-color:#B0FFA0;">' . number_format($qty_in, 2) . '</td>
                                        <td style="text-align:right; background-color:#B0FFA0;">' . number_format($direct_material_in, 2) . '</td>
                                        <td style="text-align:right; background-color:#B0FFA0;">' . number_format($direct_labor_in, 2) . '</td>
                                        <td style="text-align:right; background-color:#B0FFA0;">' . number_format($direct_foh_in, 2) . '</td>
                                        <td style="text-align:right; background-color:#B0FFA0;">' . number_format($price_in, 6) . '</td>
                                        <td style="text-align:right; background-color:#B0FFA0;">' . number_format($amount_in, 2) . '</td>
                                        <td style="text-align:right; background-color:#FFA0A0;">' . number_format(abs($qty_out), 2)  . '</td>
                                        <td style="text-align:right; background-color:#FFA0A0;">' . number_format(abs($direct_material_out), 2)  . '</td>
                                        <td style="text-align:right; background-color:#FFA0A0;">' . number_format(abs($direct_labor_out), 2)  . '</td>
                                        <td style="text-align:right; background-color:#FFA0A0;">' . number_format(abs($direct_foh_out), 2)  . '</td>
                                        <td style="text-align:right; background-color:#FFA0A0;">' . number_format(abs($price_out), 6)  . '</td>
                                        <td style="text-align:right; background-color:#FFA0A0;">' . number_format(abs($amount_out), 2)  . '</td>
                                        <td style="text-align:right; background-color:#FCFFA0;">' . number_format($qty_end, 2)  . '</td>
                                        <td style="text-align:right; background-color:#FCFFA0;">' . number_format(abs($begin_direct_material + $direct_material_in - abs($direct_material_out)), 2)  . '</td>
                                        <td style="text-align:right; background-color:#FCFFA0;">' . number_format(abs($begin_direct_labor + $direct_labor_in - abs($direct_labor_out)), 2)  . '</td>
                                        <td style="text-align:right; background-color:#FCFFA0;">' . number_format(abs($begin_direct_foh + $direct_foh_in - abs($direct_foh_out)), 2)  . '</td>
                                        <td style="text-align:right; background-color:#FCFFA0;">' . number_format($price_end, 6)  . '</td>
                                        <td style="text-align:right; background-color:#FCFFA0;">' . number_format($amount_end, 2)  . '</td>
                                    </tr>';

                        $begin_qty += ($qty_in - abs($qty_out));
                        $begin_direct_material += ($direct_material_in) - abs($direct_material_out);
                        $begin_direct_labor += ($direct_labor_in) - abs($direct_labor_out);
                        $begin_direct_foh += ($direct_foh_in) - abs($direct_foh_out);
                        $begin_amount += ($amount_in - abs($amount_out));
                        $begin_price = (abs($begin_amount) / $begin_qty);
                        $nod++;
                    }
                }
            }

            $noh++;

            $total_begin += @$begin_qty;
            $total_direct_material += @$begin[0]->direct_material;
            $total_direct_labor += @$begin[0]->direct_labor;
            $total_direct_foh += @$begin[0]->direct_foh;
            $total_begin_price += @$begin_amount;
            $total_qty_in += $record->qty_in;
            $total_direct_material_in += $record->direct_material_in;
            $total_direct_labor_in += $record->direct_labor_in;
            $total_direct_foh_in += $record->direct_foh_in;
            $total_qty_in_price += $record->amount_in;
            $total_qty_out += $record->qty_out;
            $total_direct_material_out += $record->direct_material_out;
            $total_direct_labor_out += $record->direct_labor_out;
            $total_direct_foh_out += $record->direct_foh_out;
            $total_qty_out_price += $record->amount_out;
            $total_end += $ending_qty;
            $total_direct_material_end += $ending_direct_material;
            $total_direct_labor_end += $ending_direct_labor;
            $total_direct_foh_end += $ending_direct_foh;
            $total_end_price += $ending_amount;
        }

        if ($filter_display == "RECAP") {
            $html .= '  <tr>
                            <td colspan="7" style="text-align:right;"><b>GRAND TOTAL</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_begin, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_direct_material, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_direct_labor, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_direct_foh, 2) . '</b></td>
                            <td style="text-align:right;"><b>-</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_begin_price, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_qty_in, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_direct_material_in, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_direct_labor_in, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_direct_foh_in, 2) . '</b></td>
                            <td style="text-align:right;"><b>-</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_qty_in_price, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format(abs($total_qty_out), 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format(abs($total_direct_material_out), 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format(abs($total_direct_labor_out), 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format(abs($total_direct_foh_out), 2) . '</b></td>
                            <td style="text-align:right;"><b>-</b></td>
                            <td style="text-align:right;"><b>' . number_format(abs($total_qty_out_price), 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_end, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_direct_material_end, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_direct_labor_end, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_direct_foh_end, 2) . '</b></td>
                            <td style="text-align:right;"><b>-</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_end_price, 2) . '</b></td>
                        </tr>';
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
