<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Inventory_rm extends CI_Controller
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
            $this->load->view('finance/inventory_rm');
        } else {
            redirect('error_access');
        }
    }

    public function readEndingStock()
    {
        if ($this->input->post()) {
            $item_rm_id = $this->input->post('item_rm_id');
            $trans_date = @$this->input->post('trans_date');

            if (@$trans_date == "") {
                $date = date("Y-m-d");
            } else {
                $date = $trans_date;
            }

            $records = $this->crud->query("SELECT
                a.id,
                a.number, 
                a.name, 
                b.name as prodfam, 
                a.uom, 
                COALESCE(0,0) as begin_stock,
                (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0)) as qty_in,
                f.qty as qty_out,
                (COALESCE(SUM(e.qty),0) - COALESCE(f.qty, 0) + COALESCE(g.return_qty, 0)) as end_stock
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id
            LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date <= '$date'
            LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                WHERE a.return_date <=  '$date'
                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            WHERE a.id like '$item_rm_id'
            GROUP BY a.id
            ORDER BY a.number");

            echo json_encode($records);
        }
    }

    public function readBalanceWip()
    {
        if ($this->input->post()) {
            $item_rm_id = $this->input->post('item_rm_id');
            $wip_balances = $this->crud->read("wip_balances", [], ["item_rm_id" => $item_rm_id], "", "id", "desc");

            echo json_encode($wip_balances);
        }
    }

    public function readItemFamily($number = "")
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('deleted', 0);
        if ($number != "001") {
            $this->db->where("number", $number);
        } else {
            $this->db->where("number !=", $number);
        }
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function getData(){
        $filter_from = $this->input->post('filter_from');
        $filter_to   = $this->input->post('filter_to');
        $filter_item_family = $this->input->post('filter_item_family');
        $filter_item_rm = $this->input->post('filter_item_rm');
        $period = date("Y-m", strtotime($filter_from));

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Item Receipts
        $itemReceipts = $this->crud->query("SELECT
            a.id, a.number, a.name, c.name as uom, a.uom, b.id as prodfam_id, COALESCE(d.qty, 0) as qty, COALESCE(d.amount, 0) as amount
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) as qty, SUM(amount) as amount FROM inventory_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
        WHERE a.id like '%$filter_item_rm%' and b.number like '%$filter_item_family%'
        GROUP BY a.id
        ORDER BY a.number");

        $data = array();
        foreach ($itemReceipts as $itemReceipt) {
            $item_rm_id = $itemReceipt->id;
            $prodfam_id = $itemReceipt->prodfam_id;

            if($itemReceipt->qty > 0){
                $receipt_qty = $itemReceipt->qty;
            }else{
                $receipt_qty = 1;
            }


            $price = $itemReceipt->amount / $receipt_qty;
            $begin = $itemReceipt->qty;
            $begin_price = @$price;
            $begin_amount = $itemReceipt->amount;

            for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                $working_date = date('Y-m-d', $i);

                //RECEIPT
                $receipts = $this->crud->query("SELECT
                        a.receipt_date, 
                        a.bc_kind, 
                        a.bc_aju, 
                        a.bc_document, 
                        a.bc_date, 
                        SUM(b.qty) as qty_receipt,
                        c.name as username,
                        d.po_date,
                        d.price, e.currency
                    FROM purchase_order_receipts a 
                    JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
                    JOIN users c ON a.created_by = c.username
                    LEFT JOIN purchase_orders d ON a.item_rm_id = d.item_rm_id and a.po_no = d.po_no
                    LEFT JOIN suppliers e ON d.supplier_id = e.id
                    WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$working_date' and '$working_date'
                    GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date");

                //ISSUED
                $issueds = $this->crud->query("SELECT a.*, d.po_date, d.price, e.currency, c.receipt_date
                    FROM issued_material_details a
                    LEFT JOIN purchase_order_labels b ON a.label_no = b.label_no
                    LEFT JOIN barcode_divides f ON a.label_no = f.label_divided
                    LEFT JOIN purchase_order_receipts c ON a.item_rm_id = c.item_rm_id and (b.receipt_id = c.receipt_id or f.reff = c.receipt_id) 
                    LEFT JOIN purchase_orders d ON a.item_rm_id = d.item_rm_id and c.po_no = d.po_no
                    LEFT JOIN suppliers e ON d.supplier_id = e.id
                    WHERE a.item_rm_id = '$item_rm_id' 
                    and DATE_FORMAT(a.created_date, '%Y-%m-%d') between '$working_date' and '$working_date'
                    GROUP BY a.label_no");

                $returns = $this->crud->query("SELECT
                        a.return_no,
                        a.return_id,
                        a.return_name,
                        a.return_date,
                        b.label_no,
                        b.qty,
                        d.name as username
                    FROM return_materials a 
                    JOIN return_material_labels b ON a.return_id = b.return_id
                    JOIN scan_item_receipts c ON a.return_id = c.receipt_id
                    JOIN users d ON a.created_by = d.username
                    WHERE a.item_rm_id = '$item_rm_id' and a.return_date between '$working_date' and '$working_date'
                    GROUP BY b.label_no");

                //Purchase Order Receipt
                foreach ($receipts as $receipt) {
                    $currency = @$receipt->currency;
                    $receipt_date = $receipt->receipt_date;
                    $search_date = date("d", strtotime(@$record['trans_date']));
                    if($search_date == "31"){
                        $receipt_date = date("Y-m-d", strtotime('-1 days', strtotime($receipt_date)));
                    }

                    $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($receipt_date)));
                    $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

                    if ($currency != "IDR") {
                        if ($exchange) {
                            $price = (@$receipt->price * @$exchange->middle);
                        } else {
                            $price = 0;
                        }
                    } else {
                        $price = @$receipt->price;
                    }

                    $amount = ($receipt->qty_receipt * $price);

                    $data[] = array(
                        "period" => $period,
                        "item_rm_id" => $item_rm_id,
                        "item_family_id" => $prodfam_id,
                        "trans_type" => "RECEIPT",
                        "created_name" => $receipt->username,
                        "po_date" => $receipt->po_date,
                        "trans_date" => $receipt->receipt_date,
                        "custom_kind" => $receipt->bc_kind,
                        "custom_no" => $receipt->bc_aju,
                        "document_no" => $receipt->bc_document,
                        "custom_date" => $receipt->bc_date,
                        "uom" => $itemReceipt->uom,
                        "qty" => $receipt->qty_receipt,
                        "price" => $price,
                        "amount" => $amount,
                    );

                    $begin += $receipt->qty_receipt;
                    $begin_amount += $amount;
                    $begin_price = ($begin_amount / $begin);
                }

                //Issued Material
                foreach ($issueds as $issued) {
                    $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                    $price = $begin_price;
                    $amount = ($issued->qty * $price);

                    $data[] = array(
                        "period" => $period,
                        "item_rm_id" => $item_rm_id,
                        "item_family_id" => $prodfam_id,
                        "trans_type" => "ISSUED",
                        "created_name" => $user->name,
                        "po_date" => $issued->po_date,
                        "trans_date" => date("Y-m-d", strtotime($issued->created_date)),
                        "custom_kind" => "-",
                        "custom_no" => $issued->label_no,
                        "document_no" => $issued->request_no,
                        "custom_date" => "-",
                        "uom" => $itemReceipt->uom,
                        "qty" => ($issued->qty * -1),
                        "price" => $price,
                        "amount" => ($amount * -1),
                    );

                    $begin -= $issued->qty;
                    $begin_amount -= $amount;
                    $price = $begin_price;
                }

                //Purchase Return
                foreach ($returns as $return) {
                    $price = $begin_price;
                    $amount = ($return->qty * $price);

                    $data[] = array(
                        "period" => $period,
                        "item_rm_id" => $item_rm_id,
                        "item_family_id" => $prodfam_id,
                        "trans_type" => "RECEIPT",
                        "created_name" => $return->username,
                        "po_date" => $return->return_date,
                        "trans_date" => $return->return_date,
                        "custom_kind" => $return->return_id,
                        "custom_no" => $return->label_no,
                        "document_no" => $return->return_no,
                        "custom_date" => "-",
                        "uom" => $itemReceipt->uom,
                        "qty" => $return->qty,
                        "price" => $price,
                        "amount" => $amount,
                    );

                    $begin += $return->qty;
                    $begin_amount += $amount;
                    $begin_price = ($begin_amount / $begin);
                }
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

            $inventory_rm = $this->crud->reads("inventory_rm", [], [
                "period" => $post['period'], 
                "item_rm_id" => $post['item_rm_id'], 
                "custom_no" => $post['custom_no'], 
                "trans_date" => $post['trans_date']
            ]);

            if(count($inventory_rm) > 0){
                $send = $this->crud->update('inventory_rm', [
                    "period" => $post['period'], 
                    "item_rm_id" => $post['item_rm_id'], 
                    "custom_no" => $post['custom_no'], 
                    "trans_date" => $post['trans_date']
                ], $post);

                echo $send;
            }else{
                $send = $this->crud->create('inventory_rm', $post);
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
            header("Content-Disposition: attachment; filename=stock_values_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_item_rm = $this->input->get('filter_item_rm');
        $filter_display = $this->input->get("filter_display");
        $filter_transtype = $this->input->get('filter_transtype');

        $period = date("Y-m", strtotime($filter_from));

        // $inventory_rm = $this->crud->reads("inventory_rm", [], ['period' => $period]);

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

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
                    <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION RAW MATERIAL</h3>
                    <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                </div>
            </center>
            <br><br>';

            // if(count($inventory_rm) == 0){
            //     $html .= "<center><h2>PLEASE GENERATE FIRST</h2></center>";

            //     die($html);
            // }

        //Item Receipts
        $itemReceipts = $this->crud->query("SELECT
            a.id, a.number, a.name, a.uom, b.name as prodfam, b.id as prodfam_id, COALESCE(d.qty, 0) as qty, COALESCE(d.amount, 0) as amount
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) as qty, SUM(amount) as amount FROM inventory_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            WHERE a.id like '%$filter_item_rm%' and b.number like '%$filter_item_family%'
            GROUP BY a.id
            ORDER BY a.number");

        if ($filter_display == "RECAP") {
            $html .= '<table id="customers" border="1" style="width: 100%">
                            <tr>
                                <th rowspan="2"width="20">No</th>
                                <th rowspan="2">Part No</th>
                                <th rowspan="2">Part Name</th>
                                <th rowspan="2">Uom</th>
                                <th rowspan="2">Product Family</th>
                                <th colspan="2" width="100">Begining Stock</th>
                                <th colspan="2" width="100">In</th>
                                <th colspan="2" width="100">Out</th>
                                <th colspan="2" width="100">Ending Stock</th>
                            </tr>
                            <tr>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>Qty</th>
                                <th>Amount</th>
                            </tr>';
        } else {
            $html .= '<table id="customers" style="width: 200%">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2">Part No</th>
                            <th rowspan="2">Part Name</th>
                            <th rowspan="2">Trans Type</th>
                            <th rowspan="2">Created By</th>
                            <th rowspan="2">PO Date</th>
                            <th rowspan="2">Receipt Date</th>
                            <th rowspan="2">Custom. Kind</th>
                            <th rowspan="2">Custom. No</th>
                            <th rowspan="2">Doc. No</th>
                            <th rowspan="2">Custom. Date</th>
                            <th colspan="3">Begining Stock</th>
                            <th colspan="3">In</th>
                            <th colspan="3">Out</th>
                            <th colspan="3">Ending Stock</th>
                        </tr>
                        <tr>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Amount</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Amount</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Amount</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Amount</th>
                        </tr>';
        }

        $noh = 1;
        $total_begin_recap = 0;
        $total_begin_amount_recap = 0;
        $total_in_recap = 0;
        $total_in_amount_recap = 0;
        $total_out_recap = 0;
        $total_out_amount_recap = 0;
        $total_end_recap = 0;
        $total_end_recap_amount = 0;
        foreach ($itemReceipts as $itemReceipt) {
            $item_rm_id = $itemReceipt->id;

            if($itemReceipt->qty > 0){
                $receipt_qty = $itemReceipt->qty;
            }else{
                $receipt_qty = 1;
            }

            $begin = @$itemReceipt->qty;
            $begin_price = (@$itemReceipt->amount / $receipt_qty);
            $begin_amount = @$itemReceipt->amount;

            //UNTUK REKAP
            $begin_recap = @$itemReceipt->qty;
            $begin_amount_recap = ($itemReceipt->amount);
            
            $in_recap = 0;
            $in_amount_recap = 0;
            $out_recap = 0;
            $out_amount_recap = 0;

            $end_qty = 0;
            $balance = 0;
            $balance_price = 0;
            $balance_amount = 0;

            if ($filter_display != "RECAP") {
                $html .= '  <tr style="background:#D8D8D8;">
                                <td>' . $noh . '</td>
                                <td>' . $itemReceipt->number . '</td>
                                <td>' . $itemReceipt->name . '</td>
                                <td>BEGIN STOCK</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format($begin_price, 4) . '</td>
                                <td style="text-align:right;">' . number_format($begin_amount, 2) . '</td>
                                <td style="text-align:right;">0</td>
                                <td style="text-align:right;">0</td>
                                <td style="text-align:right;">0</td>
                                <td style="text-align:right;">0</td>
                                <td style="text-align:right;">0</td>
                                <td style="text-align:right;">0</td>
                                <td style="text-align:right;">' . number_format($begin, 2)  . '</td>
                                <td style="text-align:right;">' . number_format($begin_price, 4)  . '</td>
                                <td style="text-align:right;">' . number_format($begin_amount, 2)  . '</td>
                            </tr>';
            }

            $nod = 1;
            for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                $working_date = date('Y-m-d', $i);

                //RECEIPT
                $receipts = $this->crud->query("SELECT * FROM inventory_rm WHERE item_rm_id = '$item_rm_id' and trans_date = '$working_date' and trans_type = 'RECEIPT' 
                    ORDER BY custom_no ASC");

                //ISSUED
                $issueds = $this->crud->query("SELECT * FROM inventory_rm WHERE item_rm_id = '$item_rm_id' and trans_date = '$working_date' and trans_type = 'ISSUED' 
                    ORDER BY custom_no ASC");

                //Purchase Order Receipt
                foreach ($receipts as $receipt) {
                    $balance = ($begin + $receipt->qty);
                    $balance_price = (($begin_amount + $receipt->amount) / $balance);
                    $balance_amount = ($begin_amount + $receipt->amount);

                    if ($filter_display != "RECAP") {
                        $html .= '  <tr>
                                    <td>' . $noh . '.' . $nod . '</td>
                                    <td>' . $itemReceipt->number . '</td>
                                    <td>' . $itemReceipt->name . '</td>
                                    <td>RECEIPT</td>
                                    <td>' . $receipt->created_name . '</td>
                                    <td>' . $receipt->po_date . '</td>
                                    <td>' . $receipt->trans_date . '</td>
                                    <td>' . $receipt->custom_kind . '</td>
                                    <td>' . $receipt->custom_no . '</td>
                                    <td>' . $receipt->document_no . '</td>
                                    <td>' . $receipt->custom_date . '</td>
                                    <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($begin_price, 4) . '</td>
                                    <td style="text-align:right;">' . number_format($begin_amount, 2) . '</td>
                                    <td style="text-align:right; color:green; font-weight:bold;">' . number_format($receipt->qty, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($receipt->price, 4) . '</td>
                                    <td style="text-align:right; color:green; font-weight:bold;">' . number_format($receipt->amount, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(0)  . '</td>
                                    <td style="text-align:right;">' . number_format(0)  . '</td>
                                    <td style="text-align:right;">' . number_format(0)  . '</td>
                                    <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($balance_price, 4)  . '</td>
                                    <td style="text-align:right;">' . number_format($balance_amount, 2)  . '</td>
                                </tr>';
                    }

                    $begin += $receipt->qty;
                    $begin_price = $receipt->price;
                    $begin_amount = $balance_amount;
                    $in_recap += $receipt->qty;
                    $in_amount_recap += $receipt->amount;
                    $nod++;
                }

                //Issued Material
                foreach ($issueds as $issued) {
                    $balance = ($begin - abs($issued->qty));
                    $balance_price = $issued->price;
                    $balance_amount = ($begin_amount - abs($issued->amount));
                    $begin_price = $issued->price;

                    if ($filter_display != "RECAP") {
                        $html .= '  <tr>
                                    <td>' . $noh . '.' . $nod . '</td>
                                    <td>' . $itemReceipt->number . '</td>
                                    <td>' . $itemReceipt->name . '</td>
                                    <td>ISSUED</td>
                                    <td>' . $issued->created_name . '</td>
                                    <td>' . $issued->po_date . '</td>
                                    <td>' . $issued->trans_date . '</td>
                                    <td>' . $issued->custom_kind . '</td>
                                    <td>' . $issued->custom_no . '</td>
                                    <td>' . $issued->document_no . '</td>
                                    <td>' . $issued->custom_date . '</td>
                                    <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($begin_price, 4) . '</td>
                                    <td style="text-align:right;">' . number_format($begin_amount, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(0) . '</td>
                                    <td style="text-align:right;">' . number_format(0) . '</td>
                                    <td style="text-align:right;">' . number_format(0) . '</td>
                                    <td style="text-align:right; color:red; font-weight:bold;">' . number_format(abs($issued->qty), 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($issued->price, 4)  . '</td>
                                    <td style="text-align:right; color:red; font-weight:bold;">' . number_format(abs($issued->amount), 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($balance_price, 4)  . '</td>
                                    <td style="text-align:right;">' . number_format($balance_amount, 2)  . '</td>
                                </tr>';
                    }

                    $begin -= abs($issued->qty);
                    $begin_price = @$issued->price;
                    $begin_amount -= abs($issued->amount);
                    //$begin_amount = ($begin * $begin_price);

                    $out_recap += abs($issued->qty);
                    $out_amount_recap += abs($issued->amount);
                    $nod++;
                }
            }

            // if(($begin_recap + $in_recap - $out_recap) <= 0){
            //     $balance_amount_recap = 0;
            // }else{
            //     $balance_amount_recap = ($begin_amount_recap + $in_amount_recap - $out_amount_recap);
            // }

            $balance_amount_recap = ($begin_amount_recap + $in_amount_recap - $out_amount_recap);

            if ($filter_display == "RECAP") {
                $html .= '  <tr>
                                <td>' . $noh . '</td>
                                <td>' . $itemReceipt->number . '</td>
                                <td>' . $itemReceipt->name . '</td>
                                <td>' . $itemReceipt->uom . '</td>
                                <td>' . $itemReceipt->prodfam . '</td>
                                <td style="text-align:right;">' . number_format($begin_recap, 2) . '</td>
                                <td style="text-align:right;">' . number_format($begin_amount_recap, 2) . '</td>
                                <td style="text-align:right; color:green;">' . number_format($in_recap, 2) . '</td>
                                <td style="text-align:right; color:green;">' . number_format($in_amount_recap, 2) . '</td>
                                <td style="text-align:right; color:red;">' . number_format($out_recap, 2) . '</td>
                                <td style="text-align:right; color:red;">' . number_format($out_amount_recap, 2) . '</td>
                                <td style="text-align:right;">' . number_format(($begin_recap + $in_recap - $out_recap), 2) . '</td>
                                <td style="text-align:right;">' . number_format(($balance_amount_recap), 2) . '</td>
                            </tr>';
            }


            $total_begin_recap += $begin_recap;
            $total_begin_amount_recap += $begin_amount_recap;
            $total_in_recap += $in_recap;
            $total_in_amount_recap += $in_amount_recap;
            $total_out_recap += $out_recap;
            $total_out_amount_recap += $out_amount_recap;
            $total_end_recap += ($begin_recap + $in_recap - $out_recap);
            $total_end_recap_amount += $balance_amount_recap;
            $noh++;
        }

        if ($filter_display == "RECAP") {
            $html .= '  <tr>
                            <td colspan="5" style="text-align:right;"><b>GRAND TOTAL</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_begin_recap, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . substr(number_format($total_begin_amount_recap, 3), 0, -1) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_in_recap, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_in_amount_recap, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_out_recap, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_out_amount_recap, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . number_format($total_end_recap, 2) . '</b></td>
                            <td style="text-align:right;"><b>' . substr(number_format($total_end_recap_amount, 3), 0, -1) . '</b></td>
                        </tr>';
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
