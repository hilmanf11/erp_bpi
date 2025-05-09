<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Inventory_fg extends CI_Controller
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
            $this->load->view('finance/inventory_fg');
        } else {
            redirect('error_access');
        }
    }

    public function getData()
    {
        $filter_from = $this->input->post('filter_from');
        $filter_to   = $this->input->post('filter_to');
        $filter_item_fg = $this->input->post('filter_item_fg');
        $periode = date("Y-m", strtotime($filter_from));
        $periode_bf = date("Y-m", strtotime("-1 month", strtotime($filter_from)));

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Item Receipts
        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name, 
            b.name as prodfam, 
            a.uom,
            COALESCE(d.qty) as qty,
            COALESCE(d.amount) as amount
        FROM item_fg a
        LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty, SUM(amount) as amount FROM inventory_fg WHERE trans_date < '$filter_from' GROUP BY item_fg_id) d ON a.id = d.item_fg_id
        WHERE a.id like '%$filter_item_fg%'
        GROUP BY a.id
        ORDER BY a.number");

        $data = array();
        foreach ($records as $record) {
            $item_fg_id = $record->id;

            $begin = $this->crud->query("SELECT item_fg_id, SUM(qty) as qty, SUM(amount) as amount, SUM(direct_material) as direct_material, SUM(direct_labor) as direct_labor, SUM(direct_foh) as direct_foh FROM inventory_fg WHERE trans_date < '$filter_from' and item_fg_id = '$item_fg_id' GROUP BY item_fg_id");

            $in_qty = @$begin[0]->qty;
            $in_dm = @$begin[0]->direct_material;
            $in_dl = @$begin[0]->direct_labor;
            $in_foh = @$begin[0]->direct_foh;

            //RECEIPT
            $receipts = $this->crud->query("SELECT * FROM inventory_wip WHERE trans_type = 'SCAN FG' and item_fg_id = '$item_fg_id' and trans_date between '$filter_from' and '$filter_to'");

            //DELIVERY
            $returns = $this->crud->query("SELECT a.*, d.name as username
                            FROM delivery_notes a 
                            JOIN users d ON a.created_by = d.username
                            WHERE a.item_fg_id = '$item_fg_id' and a.trans_date between '$filter_from' and '$filter_to'");

            //Wip Receipt
            foreach ($receipts as $receipt) {
                $data[] = array(
                    "period" => $periode,
                    "item_fg_id" => $item_fg_id,
                    "trans_type" => "RECEIPT FG",
                    "created_name" => $receipt->created_name,
                    "trans_date" => $receipt->trans_date,
                    "invoice_no" => $receipt->invoice_no,
                    "customer_po" => "",
                    "document_no" => $receipt->document_no,
                    "uom" => $record->uom,
                    "qty" => abs($receipt->qty),
                    "direct_material" => abs($receipt->direct_material),
                    "direct_labor" => abs($receipt->direct_labor),
                    "direct_foh" => abs($receipt->direct_foh),
                    "price" => abs($receipt->price),
                    "amount" => abs($receipt->amount),
                );

                $in_qty += abs($receipt->qty);
                $in_dm += abs($receipt->direct_material);
                $in_dl += abs($receipt->direct_labor);
                $in_foh += abs($receipt->direct_foh);
            }

            //Delivery Note
            foreach ($returns as $return) {
                $direct_material = ((($in_dm / $in_qty) * $return->qty) * -1);
                $direct_labor = ((($in_dl / $in_qty) * $return->qty) * -1);
                $direct_foh = ((($in_foh / $in_qty) * $return->qty) * -1);
                $price_out = abs(($direct_material + $direct_labor + $direct_foh) / $return->qty);

                $data[] = array(
                    "period" => $periode,
                    "item_fg_id" => $item_fg_id,
                    "trans_type" => "DELIVERY NOTE",
                    "type_sales" => $return->trans_type,
                    "created_name" => $return->username,
                    "trans_date" => $return->trans_date,
                    "invoice_no" => $return->do_number,
                    "customer_po" => $return->customer_po,
                    "document_no" => $return->number,
                    "uom" => $record->uom,
                    "qty" => ($return->qty * -1),
                    "direct_material" => $direct_material,
                    "direct_labor" => $direct_labor,
                    "direct_foh" => $direct_foh,
                    "price" => $price_out,
                    "amount" => (($return->qty * -1) * $price_out),
                );

                $in_dm -= (($in_dm / $in_qty) * $return->qty);
                $in_dl -= (($in_dl / $in_qty) * $return->qty);
                $in_foh -= (($in_foh / $in_qty) * $return->qty);
                $in_qty -= $return->qty;
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

            $inventory_fg = $this->crud->reads("inventory_fg", [], [
                "period" => $post['period'],
                "item_fg_id" => $post['item_fg_id'],
                "invoice_no" => $post['invoice_no'],
                "document_no" => $post['document_no'],
                "customer_po" => $post['customer_po'],
                "trans_date" => $post['trans_date'],
                "qty" => $post['qty']
            ]);

            if (count($inventory_fg) > 0) {
                $send = $this->crud->update('inventory_fg', [
                    "period" => $post['period'],
                    "item_fg_id" => $post['item_fg_id'],
                    "invoice_no" => $post['invoice_no'],
                    "document_no" => $post['document_no'],
                    "customer_po" => $post['customer_po'],
                    "trans_date" => $post['trans_date'],
                    "qty" => $post['qty']
                ], $post);

                echo $send;
            } else {
                $send = $this->crud->create('inventory_fg', $post);
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
            header("Content-Disposition: attachment; filename=inventory_fg_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get("filter_division");
        $filter_type = $this->input->get("filter_type");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // $records = $this->crud->query("SELECT
        //     a.id,
        //     a.number, 
        //     a.name, 
        //     a.uom, 
        //     COALESCE(0,0) as begin_stock,
        //     (COALESCE(SUM(f.qty),0)) as qty_in,
        //     -- (COALESCE(SUM(f.qty),0) + COALESCE(SUM(i.qty),0)) as qty_in,
        //     g.qty as qty_out,
        //     (COALESCE(SUM(f.qty),0) - COALESCE(g.qty, 0)) as end_stock
        // FROM item_fg a 
        // LEFT JOIN production_schedules d ON a.id = d.item_fg_id
        // LEFT JOIN checksheets e ON d.wo_no = e.wo_no
        // LEFT JOIN scan_item_receipts_fg f ON e.number = f.checksheet_number and DATE_FORMAT(e.trans_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'
        // -- LEFT JOIN scan_item_receipts_fg i ON a.id = i.item_fg_id AND 'NBFG'= i.type AND i.packing_date between '$filter_from' and '$filter_to'
        // LEFT JOIN (SELECT item_fg_id, delivery_note_date, COALESCE(SUM(qty), 0) as qty FROM delivery_notes WHERE delivery_note_date between '$filter_from' and '$filter_to' GROUP BY item_fg_id) g ON a.id = g.item_fg_id

        // WHERE a.id like '%$filter_items%'
        // GROUP BY a.id
        // ORDER BY a.number");

        // Step 1: Hitung qty_in dari checksheet
        $query_qty_in_checksheet = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_checksheet
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY e.item_fg_id";

        // Step 2: Hitung qty_in tanpa checksheet
        $query_qty_in_no_checksheet = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
        FROM scan_item_receipts_fg i
        WHERE i.type = 'NBFG'
        AND i.packing_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY i.item_fg_id";

        // Step 3: Hitung initial `i` dari transaction_fg (kind IN)
        $query_transaction_fg_in = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        // Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        // Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes = "SELECT item_fg_id, SUM(qty) as initial_out_g
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY item_fg_id";

        // Step 6: Hitung initial `h` (scan_repair_of_goods)
        $query_scan_repair_of_goods = "SELECT e.item_fg_id, SUM(f.qty) as initial_out_h
        FROM scan_repair_of_goods f
        JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
        WHERE DATE_FORMAT(e.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY f.item_fg_id";

        // Step 7: Hitung qty_in WIP division MTS
        $query_qty_in_wip_receipt = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_wip_receipt
        FROM wip_receipts i
        WHERE i.division = 'MTS'
        AND i.trans_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY i.item_fg_id";

        //-----------------------------------------------------------------

        $query_qty_in_checksheet2 = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_checksheet
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') < '$filter_from'
        GROUP BY e.item_fg_id";

        // Step 2: Hitung qty_in tanpa checksheet
        $query_qty_in_no_checksheet2 = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
        FROM scan_item_receipts_fg i
        WHERE i.type = 'NBFG'
        AND i.packing_date < '$filter_from'
        GROUP BY i.item_fg_id";

        // Step 3: Hitung initial `i` dari transaction_fg (kind IN)
        $query_transaction_fg_in2 = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN'
        AND a.request_date < '$filter_from'
        GROUP BY a.item_fg_id";

        // Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT'
        AND a.request_date < '$filter_from'
        GROUP BY a.item_fg_id";

        // Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes2 = "SELECT item_fg_id, SUM(qty) as initial_out_g
        FROM delivery_notes
        WHERE delivery_note_date < '$filter_from'
        GROUP BY item_fg_id";

        // Step 6: Hitung initial `h` (scan_repair_of_goods)
        $query_scan_repair_of_goods2 = "SELECT e.item_fg_id, SUM(f.qty) as initial_out_h
        FROM scan_repair_of_goods f
        JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
        WHERE DATE_FORMAT(e.trans_date, '%Y-%m-%d') < '$filter_from'
        GROUP BY f.item_fg_id";

        // Step 8: Hitung qty_in WIP division MTS
        $query_qty_in_wip_receipt2 = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_wip_receipt
        FROM wip_receipts i
        WHERE i.division = 'MTS'
        AND i.trans_date < '$filter_from'
        GROUP BY i.item_fg_id";

        // Step 9: Gabungan query
        $query_main = "SELECT 
            a.id, 
            a.number, 
            a.name, 
            a.uom,
            a.type,
            xy.number as division,
            COALESCE(aa.price,0) as price,
            COALESCE(aa.currency,'-') as currency,
            COALESCE(x.begin_stock,0) AS begin_stock,
            COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) AS qty_in,
            
            COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0) AS qty_out,
            
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) - 
            (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS end_stock
        FROM item_fg a
        LEFT JOIN divisions xy on a.division_id = xy.id
        LEFT JOIN (SELECT item_fg_id, currency, price from standard_price_fg where '$filter_from' >= `start_date` and '$filter_to' <= `end_date`) aa on a.id = aa.item_fg_id
        LEFT JOIN ($query_qty_in_checksheet) qc ON a.id = qc.item_fg_id
        LEFT JOIN ($query_qty_in_no_checksheet) qnc ON a.id = qnc.item_fg_id
        LEFT JOIN ($query_transaction_fg_in) qi ON a.id = qi.item_fg_id
        LEFT JOIN ($query_qty_out) qo ON a.id = qo.item_fg_id
        LEFT JOIN ($query_delivery_notes) qg ON a.id = qg.item_fg_id
        LEFT JOIN ($query_scan_repair_of_goods) qh ON a.id = qh.item_fg_id
        LEFT JOIN ($query_qty_in_wip_receipt) qw ON a.id = qw.item_fg_id

        LEFT JOIN ( SELECT a.id,
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) - 
            (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS begin_stock
            FROM item_fg a
            LEFT JOIN ($query_qty_in_checksheet2) qc ON a.id = qc.item_fg_id
            LEFT JOIN ($query_qty_in_no_checksheet2) qnc ON a.id = qnc.item_fg_id
            LEFT JOIN ($query_transaction_fg_in2) qi ON a.id = qi.item_fg_id
            LEFT JOIN ($query_qty_out2) qo ON a.id = qo.item_fg_id
            LEFT JOIN ($query_delivery_notes2) qg ON a.id = qg.item_fg_id
            LEFT JOIN ($query_scan_repair_of_goods2) qh ON a.id = qh.item_fg_id
            LEFT JOIN ($query_qty_in_wip_receipt2) qw ON a.id = qw.item_fg_id
            GROUP BY a.id) x ON a.id = x.id
        WHERE a.id LIKE '%$filter_items%' AND a.division_id LIKE '%$filter_division%' AND a.type LIKE '%$filter_type%'
        ORDER BY a.number
        ";

        // echo $query_main;
        // die();

        $records = $this->crud->query($query_main);

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>' . $config->description . '</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br><br>
            <h3 style="margin:0;">INVENTORY FG</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2" colspan="3">Product No</th>
                    <th rowspan="2">Product Name</th>
                    <th rowspan="2">Uom</th>
                    <th rowspan="2">Division</th>
                    <th rowspan="2">Product Family</th>
                    <th rowspan="2">Type</th>
                    <th rowspan="2">Currency</th>
                    <th rowspan="2">Price</th>
                    <th rowspan="2">Rate</th>
                    <th colspan="3" width="100">Begin</th>
                    <th colspan="3" width="100">In</th>
                    <th colspan="3" width="100">Out</th>
                    <th colspan="3" width="100">Balance</th>
                </tr>
                <tr>
                    <th width="80">QTY</th>
                    <th width="80">PRICE</th>
                    <th width="80">AMOUNT</th>

                    <th width="80">QTY</th>
                    <th width="80">PRICE</th>
                    <th width="80">AMOUNT</th>

                    <th width="80">QTY</th>
                    <th width="80">PRICE</th>
                    <th width="80">AMOUNT</th>

                    <th width="80">QTY</th>
                    <th width="80">PRICE</th>
                    <th width="80">AMOUNT</th>
                </tr>';
        $no = 1;
        $totalBeginStock = 0;
        $totalBeginAmount = 0;
        $totalIn = 0;
        $totalAmountIn = 0;
        $totalOut = 0;
        $totalAmountOut = 0;
        $totalEndingStock = 0;
        $totalAmountEndingStock = 0;
        foreach ($records as $record) {
            $item_fg_id = $record->id;
            $currency = @$record->currency;
            $rate = 1;

            if ($currency == 'USD') {
                if (empty($receipt_date)) {
                    $rate = 0;
                } else {
                    $this->db->where('currency_from', 'USD');
                    $this->db->where('start_date <=', $receipt_date);
                    $this->db->where('end_date >=', $receipt_date);
                    $query = $this->db->get('standard_exchange_rates');

                    if ($query->num_rows() > 0) {
                        $rate = $query->row()->middle;
                    }
                }
            }

            $totalBeginStock += @$record->begin_stock;
            $totalBeginAmount += @$record->price * $rate * @$record->begin_stock;
            $totalIn += @$record->qty_in;
            $totalAmountIn += @$record->price * $rate * @$record->qty_in;
            $totalOut += @$record->qty_out;
            $totalAmountOut += @$record->price * $rate * @$record->qty_out;
            $totalEndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out;
            $totalAmountEndingStock += ((@$record->price * $rate) * @$record->qty_in) + ((@$record->price * $rate) * @$record->begin_stock) - ((@$record->price * $rate) * @$record->qty_out);


            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3" style="mso-number-format:\@;">' . $record->number . '</td>
                            <td style="mso-number-format:\@;">' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->division . '</td>
                            <td>FINISH GOOD</td>
                            <td>' . $record->type . '</td>
                            <td style="text-align:center;">' . $record->currency . '</td>
                            <td style="text-align:right;">' . number_format($record->price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($rate, 2) . '</td>

                            <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->price * $rate) * $record->begin_stock, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->price * $rate) * $record->qty_in, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->price * $rate) * $record->qty_out, 2) . '</td>

                            <td style="text-align:right;">' . number_format((@$record->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format((@($record->price * $rate) * $record->qty_in) + (($record->price * $rate) * $record->begin_stock) - (($record->price * $rate) * $record->qty_out), 2) . '</td>
                        
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="24" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
                $html .= '  <tr>
                                <th rowspan="2" width="20"></th>
                                <th rowspan="2" width="20">No</th>
                                <th rowspan="2">Trans Type</th>
                                <th rowspan="2">Created By</th>
                                <th rowspan="2">Trans Date</th>
                                <th rowspan="2">WO / DO</th>
                                <th rowspan="2" colspan="3" >Doc. No</th>
                                <th rowspan="2">CCY</th>
                                <th rowspan="2">Price</th>
                                <th rowspan="2">Rate</th>
                                <th colspan="3">Begin</th>
                                <th colspan="3">In</th>
                                <th colspan="3">Out</th>
                                <th colspan="3">Balance</th>
                            </tr>
                            <tr>
                                <th>QTY</th>
                                <th>PRICE</th>
                                <th>AMOUNT</th>

                                <th>QTY</th>
                                <th>PRICE</th>
                                <th>AMOUNT</th>

                                <th>QTY</th>
                                <th>PRICE</th>
                                <th>AMOUNT</th>

                                <th>QTY</th>
                                <th>PRICE</th>
                                <th>AMOUNT</th>
                            </tr>';
                $nod = 1;
                $begin = @$record->begin_stock;
                $price = @$record->price;
                $currency = @$record->currency;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;
                $rate = 1;

                if ($currency == 'USD') {
                    if (empty($receipt_date)) {
                        $rate = 0;
                    } else {
                        $this->db->where('currency_from', 'USD');
                        $this->db->where('start_date <=', $receipt_date);
                        $this->db->where('end_date >=', $receipt_date);
                        $query = $this->db->get('standard_exchange_rates');

                        if ($query->num_rows() > 0) {
                            $rate = $query->row()->middle;
                        }
                    }
                }

                //RECEIPT
                // $receipts = $this->crud->query("SELECT f.*, c.name as username, e.packing_date as trans_date
                //     -- FROM production_schedules d
                //     FROM wip_receipts d
                //     LEFT JOIN checksheets e ON d.wo_no = e.wo_no
                //     LEFT JOIN scan_item_receipts_fg f ON e.number = f.checksheet_number
                //     LEFT JOIN users c ON f.created_by = c.username
                //     WHERE d.item_fg_id = '$item_fg_id' and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                //     if (empty($receipts)) {
                //         $receipts = $this->crud->query("SELECT f.*, u.name as username, f.packing_date as trans_date
                //             FROM new_barcode_fg a
                //             LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                //             LEFT JOIN users u ON f.created_by = u.username
                //             WHERE a.item_fg_id = '$item_fg_id' 
                //             AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                //         $receipt_type = 'NEW BARCODE FG';
                //     } else {
                //         $receipt_type = 'RECEIPT FG';
                //     }

                // //DELIVERY NOTE
                // $delivery_notes = $this->crud->query("SELECT a.*,
                //     d.name as username
                //     FROM delivery_notes a 
                //     JOIN users d ON a.created_by = d.username
                //     WHERE a.item_fg_id = '$item_fg_id' and a.delivery_note_date between '$filter_from' and '$filter_to'");

                // // TRANSACTION RM (IN and OUT)
                // $transactions = $this->crud->query("SELECT
                //     a.request_date,
                //     a.transaction_type,
                //     a.transaction_kind,
                //     a.request_no,
                //     a.qty,
                //     b.name as username
                //     FROM transaction_fg a
                //     JOIN users b ON a.created_by = b.username
                //     WHERE a.item_fg_id = '$item_fg_id' and a.request_date between '$filter_from' and '$filter_to'");


                // //-------------- Akhir query disini----------------------------------//

                // //RECEIPT
                // foreach ($receipts as $receipt) {
                //     $balance = ($begin + ($receipt->qty - $end_qty));
                //     $html .= '  <tr>
                //                     <td></td>
                //                     <td style="text-align:center">' . $nod . '</td>
                //                     <td>' . $receipt_type . '</td>
                //                     <td>' . $receipt->username . '</td>
                //                     <td>' . $receipt->trans_date . '</td>
                //                     <td>' . $receipt->wo_no . '</td>
                //                     <td>' . $receipt->checksheet_label . '</td>
                //                     <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                //                     <td style="text-align:right;">' . number_format($receipt->qty, 2) . '</td>
                //                     <td style="text-align:right;">' . number_format(0)  . '</td>
                //                     <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                //                 </tr>';
                //     $begin += $receipt->qty;
                //     $nod++;
                // }
                // //DELIVERY NOTE
                // foreach ($delivery_notes as $delivery_note) {
                //     $balance = ($begin - $delivery_note->qty);
                //     $html .= '  <tr>
                //                     <td></td>
                //                     <td style="text-align:center">' . $nod . '</td>
                //                     <td>DELIVERY NOTE</td>
                //                     <td>' . $delivery_note->username . '</td>
                //                     <td>' . $delivery_note->delivery_note_date . '</td>
                //                     <td>' . $delivery_note->delivery_order_no  . '</td>
                //                     <td>' . $delivery_note->delivery_note_no . '</td>
                //                     <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                //                     <td style="text-align:right;">' . number_format(0) . '</td>
                //                     <td style="text-align:right;">' . number_format($delivery_note->qty, 2)  . '</td>
                //                     <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                //                 </tr>';
                //     $begin -= $delivery_note->qty;
                //     $nod++;
                // }
                // // TRANSACTION RM (IN and OUT)
                // foreach ($transactions as $transaction) {
                //     $trans_type_label = $transaction->transaction_type;
                //     $balance = ($transaction->transaction_kind == 'IN') ? ($begin + $transaction->qty) : ($begin - $transaction->qty);

                //     $html .= '  <tr>
                //                     <td></td>
                //                     <td style="text-align:center">' . $nod . '</td>
                //                     <td>' . $trans_type_label . '</td>
                //                     <td>' . $transaction->username . '</td>
                //                     <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                //                     <td>-</td>
                //                     <td>' . $transaction->request_no . '</td>
                //                     <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                //                     <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                //                     <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                //                     <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                //                 </tr>';

                //     // Update balance
                //     if ($transaction->transaction_kind == 'IN') {
                //         $begin += $transaction->qty;
                //     } else {
                //         $begin -= $transaction->qty;
                //     }

                //     $nod++;
                // }

                // Ambil seluruh data untuk rentang tanggal dalam satu query per jenis transaksi
                // $receipts = $this->crud->query("SELECT f.wo_no, f.checksheet_label, f.qty, c.name AS username, e.packing_date AS trans_date, 'RECEIPT FG' AS receipt_type
                // FROM checksheets e
                // LEFT JOIN scan_item_receipts_fg f ON e.number = f.checksheet_number
                // LEFT JOIN users c ON f.created_by = c.username
                // WHERE e.item_fg_id = '$item_fg_id'
                // AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                // UNION ALL
                // SELECT '-' as wo_no, f.checksheet_label, f.qty, u.name AS username, f.packing_date AS trans_date, 'NEW BARCODE FG' AS receipt_type
                // FROM new_barcode_fg a
                // LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                // LEFT JOIN users u ON f.created_by = u.username
                // WHERE a.item_fg_id = '$item_fg_id'
                // AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");

                $receipts = $this->crud->query("SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                        FROM scan_item_receipts_fg f
                        JOIN checksheets e ON e.number = f.checksheet_number
                        LEFT JOIN users c ON f.created_by = c.username
                        WHERE e.item_fg_id = '$item_fg_id' 
                        and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                $receiptsNB = $this->crud->query("SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
                        FROM new_barcode_fg a
                        LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                        LEFT JOIN users u ON f.created_by = u.username
                        WHERE a.item_fg_id = '$item_fg_id' 
                        AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                $receiptsWIP = $this->crud->query("SELECT a.*, u.name as username, 'RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                        FROM wip_receipts a
                        LEFT JOIN users u ON a.created_by = u.username
                        WHERE a.item_fg_id = '$item_fg_id' AND a.division = 'MTS'
                        AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");



                $delivery_notes = $this->crud->query("SELECT a.*, d.name AS username
                    FROM delivery_notes a
                    JOIN users d ON a.created_by = d.username
                    WHERE a.item_fg_id = '$item_fg_id'
                    AND a.delivery_note_date BETWEEN '$filter_from' AND '$filter_to'");

                $transactions = $this->crud->query("SELECT
                    a.request_date,
                    a.transaction_type,
                    a.transaction_kind,
                    a.request_no,
                    a.qty,
                    b.name AS username
                    FROM transaction_fg a
                    JOIN users b ON a.created_by = b.username
                    WHERE a.item_fg_id = '$item_fg_id'
                    AND a.request_date BETWEEN '$filter_from' AND '$filter_to'");

                $scan_repair_of_goods = $this->crud->query("SELECT f.wo_no, 
                    f.document_no, 
                    f.qty, 
                    c.name AS username, 
                    e.trans_date AS trans_date, 
                    'REPAIR OF GOODS' AS receipt_type
                    FROM scan_repair_of_goods f
                    LEFT JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
                    LEFT JOIN users c ON f.created_by = c.username
                    WHERE f.item_fg_id = '$item_fg_id'
                    AND DATE_FORMAT(e.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");

                // Proses data berdasarkan tanggal
                $all_data = [];

                // Gabungkan data receipts
                foreach ($receipts as $receipt) {
                    $all_data[] = [
                        'type' => $receipt->receipt_type,
                        'username' => $receipt->username,
                        'date' => $receipt->trans_date,
                        'wo_no' => $receipt->wo_no,
                        'label' => $receipt->checksheet_label,
                        'qty_in' => $receipt->qty,
                        'qty_out' => 0,
                    ];
                }

                foreach ($receiptsNB as $receiptNB) {
                    $all_data[] = [
                        'type' => $receiptNB->receipt_type,
                        'username' => $receiptNB->username,
                        'date' => $receiptNB->trans_date,
                        'wo_no' => $receiptNB->wo_no,
                        'label' => $receiptNB->checksheet_label,
                        'qty_in' => $receiptNB->qty,
                        'qty_out' => 0,
                    ];
                }

                foreach ($receiptsWIP as $receiptWIP) {
                    $all_data[] = [
                        'type' => $receiptWIP->receipt_type,
                        'username' => $receiptWIP->username,
                        'date' => $receiptWIP->trans_date,
                        'wo_no' => $receiptWIP->wo_no,
                        'label' => $receiptWIP->checksheet_label,
                        'qty_in' => $receiptWIP->qty,
                        'qty_out' => 0,
                    ];
                }

                // Gabungkan data delivery notes
                foreach ($delivery_notes as $delivery_note) {
                    $all_data[] = [
                        'type' => 'DELIVERY NOTE',
                        'username' => $delivery_note->username,
                        'date' => $delivery_note->delivery_note_date,
                        'wo_no' => $delivery_note->delivery_order_no,
                        'label' => $delivery_note->delivery_note_no,
                        'qty_in' => 0,
                        'qty_out' => $delivery_note->qty,
                    ];
                }

                // Gabungkan data transactions
                foreach ($transactions as $transaction) {
                    $all_data[] = [
                        'type' => $transaction->transaction_type,
                        'username' => $transaction->username,
                        'date' => $transaction->request_date,
                        'wo_no' => '-',
                        'label' => $transaction->request_no,
                        'qty_in' => $transaction->transaction_kind == 'IN' ? $transaction->qty : 0,
                        'qty_out' => $transaction->transaction_kind == 'OUT' ? $transaction->qty : 0,
                    ];
                }

                foreach ($scan_repair_of_goods as $scan_repair_of_good) {
                    $all_data[] = [
                        'type' => $scan_repair_of_good->receipt_type,
                        'username' => $scan_repair_of_good->username,
                        'date' => $scan_repair_of_good->trans_date,
                        'wo_no' => $scan_repair_of_good->wo_no,
                        'label' => $scan_repair_of_good->document_no,
                        'qty_in' => 0,
                        'qty_out' => $scan_repair_of_good->qty,
                    ];
                }

                // Urutkan data berdasarkan tanggal
                usort($all_data, function ($a, $b) {
                    return strtotime($a['date']) - strtotime($b['date']);
                });

                // Generate HTML
                $nod = 1;
                $balance = $begin;
                foreach ($all_data as $data) {
                    $balance += $data['qty_in'] - $data['qty_out'];
                    $html .= '  <tr>
                                    <td></td>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td>' . $data['type'] . '</td>
                                    <td>' . $data['username'] . '</td>
                                    <td>' . $data['date'] . '</td>
                                    <td>' . $data['wo_no'] . '</td>
                                    <td colspan="3">' . $data['label'] . '</td>
                                    <td style="text-align:center;">' . $currency . '</td>
                                    <td style="text-align:right;">' . number_format($price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($rate, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(($rate * $price) * $begin, 2) . '</td>

                                    <td style="text-align:right;">' . number_format($data['qty_in'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(($rate * $price) * $data['qty_in'], 2) . '</td>

                                    <td style="text-align:right;">' . number_format($data['qty_out'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(($rate * $price) * $data['qty_out'], 2) . '</td>

                                    <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(($rate * $price) * $balance, 2) . '</td>
                                </tr>';

                    $begin = $balance;
                    $nod++;
                }
            }
            $no++;
        }

        $html .= '<tr>
                    <td colspan="12" style="text-align:right;"><b>GRAND TOTAL</b></td>
                    <td style="text-align:right;"><b>' . number_format($totalBeginStock, 2) . '</b></td>
                    <td style="text-align:right;"></td>
                    <td style="text-align:right;"><b>' . number_format($totalBeginAmount, 2) . '</b></td>
                    <td style="text-align:right;"><b>' . number_format($totalIn, 2) . '</b></td>
                    <td style="text-align:right;"></td>
                    <td style="text-align:right;"><b>' . number_format($totalAmountIn, 2) . '</b></td>
                    <td style="text-align:right;"><b>' . number_format($totalOut, 2) . '</b></td>
                    <td style="text-align:right;"></td>
                    <td style="text-align:right;"><b>' . number_format($totalAmountOut, 2) . '</b></td>
                    <td style="text-align:right;"><b>' . number_format($totalEndingStock, 2) . '</b></td>
                    <td style="text-align:right;"></td>
                    <td style="text-align:right;"><b>' . number_format($totalAmountEndingStock, 2) . '</b></td>
                </tr>';
        $html .= '</table></body></html>';
        echo $html;
    }
}
