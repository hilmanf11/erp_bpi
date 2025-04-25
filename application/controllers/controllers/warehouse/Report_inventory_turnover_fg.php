<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');

class Report_inventory_turnover_fg extends CI_Controller

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

            $this->load->view('template/header', $data);

            $this->load->view('warehouse/report_inventory_turnover_fg');

        } else {

            redirect('error_access');

        }

    }



    public function print($option = "")

    {

	ini_set('max_execution_time', 240);


        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=inventory_turnover_fg_$format.xls");

        }

        $filter_from = $this->input->get('filter_from');

        $filter_to   = $this->input->get('filter_to');

        $filter_items = $this->input->get('filter_items');

        $filter_display = $this->input->get("filter_display");

        $filter_trans_type = $this->input->get("filter_trans_type");



        $start = strtotime($filter_from);

        $finish = strtotime($filter_to);



        //Config

        $this->db->select('*');

        $this->db->from('config');

        $config = $this->db->get()->row();



        $records = $this->crud->query("SELECT

                a.id,

                a.number, 

                a.name, 

                a.uom, 

                COALESCE(0,0) as begin_stock,

                (

                    -- Subquery untuk qty_in dari checksheet

                    SELECT COALESCE(SUM(f.qty), 0)

                    FROM scan_item_receipts_fg f

                    JOIN checksheets e ON e.number = f.checksheet_number

                    WHERE a.id = e.item_fg_id

                    AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'

                ) + (

                    -- Subquery untuk qty_in dari item_fg tanpa checksheet

                    SELECT COALESCE(SUM(i.qty), 0)

                    FROM scan_item_receipts_fg i

                    WHERE i.item_fg_id = a.id

                    AND i.type = 'NBFG'

                    AND i.packing_date BETWEEN '$filter_from' AND '$filter_to'

                    AND NOT EXISTS (

                        SELECT 1

                        FROM checksheets e

                        WHERE e.number = i.checksheet_number

                    )

                ) + COALESCE(i.qty, 0) as qty_in,



                COALESCE(g.qty, 0) + COALESCE(h.qty, 0) as qty_out,

                (

                    COALESCE(g.qty,0) + COALESCE(h.qty,0) +

                    COALESCE(

                        (

                            SELECT COALESCE(SUM(qty), 0)

                            FROM delivery_notes 

                            WHERE delivery_note_date BETWEEN DATE_FORMAT(DATE_SUB('$filter_from', INTERVAL 1 MONTH), '%Y-%m-01') 

                            AND LAST_DAY(DATE_SUB('$filter_from', INTERVAL 1 MONTH)) 

                            AND item_fg_id = a.id

                        ), 0

                    ) + 

                    COALESCE(

                        (

                            SELECT COALESCE(SUM(f.qty), 0)

                            FROM scan_repair_of_goods f 

                            JOIN checksheets e ON e.number = f.checksheet_number 

                            WHERE DATE_FORMAT(f.created_date, '%Y-%m-%d') BETWEEN DATE_FORMAT(DATE_SUB('$filter_from', INTERVAL 1 MONTH), '%Y-%m-01') 

                            AND LAST_DAY(DATE_SUB('$filter_from', INTERVAL 1 MONTH)) 

                            AND e.item_fg_id = a.id

                        ), 0

                    ) +

                    COALESCE(

                        (

                            SELECT COALESCE(SUM(qty), 0)

                            FROM delivery_notes 

                            WHERE delivery_note_date BETWEEN DATE_FORMAT(DATE_SUB('$filter_from', INTERVAL 2 MONTH), '%Y-%m-01') 

                            AND LAST_DAY(DATE_SUB('$filter_from', INTERVAL 2 MONTH)) 

                            AND item_fg_id = a.id

                        ), 0

                    ) + 

                    COALESCE(

                        (

                            SELECT COALESCE(SUM(f.qty), 0)

                            FROM scan_repair_of_goods f 

                            JOIN checksheets e ON e.number = f.checksheet_number 

                            WHERE DATE_FORMAT(f.created_date, '%Y-%m-%d') BETWEEN DATE_FORMAT(DATE_SUB('$filter_from', INTERVAL 2 MONTH), '%Y-%m-01') 

                            AND LAST_DAY(DATE_SUB('$filter_from', INTERVAL 2 MONTH)) 

                            AND e.item_fg_id = a.id

                        ), 0

                    )



                )/3 as avg_usage_3_months,



                (

                    COALESCE(

                        (

                            -- Subquery untuk qty_in dari checksheet

                            SELECT COALESCE(SUM(f.qty), 0)

                            FROM scan_item_receipts_fg f

                            JOIN checksheets e ON e.number = f.checksheet_number

                            WHERE a.id = e.item_fg_id

                            AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'

                        ) + (

                            -- Subquery untuk qty_in dari item_fg tanpa checksheet

                            SELECT COALESCE(SUM(i.qty), 0)

                            FROM scan_item_receipts_fg i

                            WHERE i.item_fg_id = a.id

                            AND i.type = 'NBFG'

                            AND i.packing_date BETWEEN '$filter_from' AND '$filter_to'

                            AND NOT EXISTS (

                                SELECT 1

                                FROM checksheets e

                                WHERE e.number = i.checksheet_number

                            )

                        ), 0

                    ) - ( COALESCE(g.qty, 0) + COALESCE(h.qty, 0) )

                ) as end_stock



            FROM item_fg a 

            LEFT JOIN production_schedules d ON a.id = d.item_fg_id

            LEFT JOIN checksheets e ON d.wo_no = e.wo_no

            LEFT JOIN (SELECT item_fg_id, delivery_note_date, COALESCE(SUM(qty), 0) as qty FROM delivery_notes WHERE delivery_note_date between '$filter_from' and '$filter_to' GROUP BY item_fg_id) g ON a.id = g.item_fg_id

            LEFT JOIN (SELECT e.item_fg_id, COALESCE(SUM(f.qty), 0) as qty FROM scan_repair_of_goods f JOIN checksheets e ON e.number = f.checksheet_number WHERE DATE_FORMAT(f.created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY e.item_fg_id) h ON a.id = h.item_fg_id



            LEFT JOIN (

                SELECT a.item_fg_id, a.transaction_kind, SUM(a.qty) AS qty

                FROM transaction_fg a

                JOIN item_fg b ON a.item_fg_id = b.id

                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'

                GROUP BY a.item_fg_id, a.transaction_kind

            ) i ON a.id = i.item_fg_id and i.transaction_kind = 'IN'





            WHERE a.id like '%$filter_items%'

            GROUP BY a.id

            ORDER BY a.number

        ");





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

            <h3 style="margin:0;">INVENTORY TURN OVER - FG</h3>

            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>

        </center>

        <br>

            

            <table id="customers" border="1" style="font-size: 11px;">

                <tr>

                    <th width="20">No</th>

                    <th colspan="3">Product No</th>

                    <th>Product Name</th>

                    <th>Uom</th>

                    <th>Product Family</th>

                    <th width="100">Begin<br>Stock</th>

                    <th width="100">In</th>

                    <th width="100">Out</th>

                    <th width="100">Ending<br>Stock</th>

                    <th width="100">AVG USAGE<br>3 MONTH</th>

                    <th width="100">ITO<br>(day)</th>

                </tr>';

        $no = 1;

        foreach ($records as $record) {

            $item_fg_id = $record->id;



            $endstock = $this->crud->query("SELECT a.id,( COALESCE(SUM(f.qty),0) + COALESCE(h.qty, 0) - (COALESCE(g.qty, 0)+COALESCE(i.qty, 0)) ) as begin_stock

            FROM item_fg a

            -- LEFT JOIN production_schedules d ON a.id = d.item_fg_id

            LEFT JOIN wip_receipts d ON a.id = d.item_fg_id

            LEFT JOIN checksheets e ON d.wo_no = e.wo_no

            LEFT JOIN scan_item_receipts_fg f ON e.number = f.checksheet_number AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') < '$filter_from'

            LEFT JOIN (SELECT item_fg_id, delivery_note_date, COALESCE(SUM(qty), 0) as qty FROM delivery_notes WHERE delivery_note_date < '$filter_from' GROUP BY item_fg_id) g ON a.id = g.item_fg_id

            LEFT JOIN (SELECT e.item_fg_id, COALESCE(SUM(f.qty), 0) as qty FROM scan_repair_of_goods f JOIN checksheets e ON e.number = f.checksheet_number WHERE DATE_FORMAT(f.created_date, '%Y-%m-%d') < '$filter_from' GROUP BY e.item_fg_id) i ON a.id = i.item_fg_id



            LEFT JOIN (

                SELECT h.item_fg_id, COALESCE(SUM(h.qty), 0) as qty

                FROM scan_item_receipts_fg h

                WHERE h.packing_date < '$filter_from'

                AND h.item_fg_id = '$item_fg_id'

                AND h.type = 'NBFG'

                AND NOT EXISTS (

                    SELECT 1

                    FROM checksheets e

                    WHERE e.number = h.checksheet_number

                )

                GROUP BY h.item_fg_id

            ) h ON a.id = h.item_fg_id



            WHERE a.id = '$item_fg_id'

            GROUP BY a.id

            ORDER BY a.number");



            $html .= '  <tr>

                            <td style="text-align:center">' . $no . '</td>

                            <td colspan="3">' . $record->number . '</td>

                            <td>' . $record->name . '</td>

                            <td>' . $record->uom . '</td>

                            <td>FINISH GOOD</td>

                            <td style="text-align:right;">' . number_format(@$endstock[0]->begin_stock, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>

                            <td style="text-align:right;">' . number_format((@$endstock[0]->begin_stock + $record->qty_in - $record->qty_out), 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->avg_usage_3_months, 2) . '</td>

                            <td style="text-align:right;">' . number_format(($record->avg_usage_3_months > 0) ? ((@$endstock[0]->begin_stock + $record->qty_in - $record->qty_out) / $record->avg_usage_3_months) * 30 : 0, 2) . '</td>
                        </tr>';



            if ($filter_display == "DETAIL") {

                $html .= '  <tr>

                                <td colspan="11" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>

                            </tr>';

                $html .= '  <tr>

                                <th width="20"></th>

                                <th width="20">No</th>

                                <th>Trans Type</th>

                                <th>Created By</th>

                                <th>Trans Date</th>

                                <th>WO / DO</th>

                                <th>Doc. No</th>

                                <th>Begin</th>

                                <th>In</th>

                                <th>Out</th>

                                <th>Balance</th>

                            </tr>';

                $nod = 1;

                $begin = @$endstock[0]->begin_stock;

                $in_qty = 0;

                $end_qty = 0;

                $balance = 0;



                for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {

                    $working_date = date('Y-m-d', $i);



                    if ($filter_trans_type == '') {



                        //RECEIPT

                        $receipts = $this->crud->query("SELECT f.*, c.name as username

                            -- FROM production_schedules d

                            FROM wip_receipts d

                            LEFT JOIN checksheets e ON d.wo_no = e.wo_no

                            LEFT JOIN scan_item_receipts_fg f ON e.number = f.checksheet_number

                            LEFT JOIN users c ON f.created_by = c.username

                            WHERE d.item_fg_id = '$item_fg_id' and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$working_date' and '$working_date'");



                        if (empty($receipts)) {

                            $receipts = $this->crud->query("SELECT f.*, u.name as username

                                FROM new_barcode_fg a

                                LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id

                                LEFT JOIN users u ON f.created_by = u.username

                                WHERE a.item_fg_id = '$item_fg_id' 

                                AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$working_date' AND '$working_date'");

                            

                            $receipt_type = 'NEW BARCODE FG';

                        } else {

                            $receipt_type = 'RECEIPT FG';

                        }

                        

                        //DELIVERY NOTE

                        $delivery_notes = $this->crud->query("SELECT a.*,

                            d.name as username

                            FROM delivery_notes a 

                            JOIN users d ON a.created_by = d.username

                            WHERE a.item_fg_id = '$item_fg_id' and a.delivery_note_date between '$working_date' and '$working_date'");



                         // TRANSACTION RM (IN and OUT)

                         $transactions = $this->crud->query("SELECT

                            a.request_date,

                            a.transaction_type,

                            a.transaction_kind,

                            a.request_no,

                            a.qty,

                            b.name as username

                            FROM transaction_fg a

                            JOIN users b ON a.created_by = b.username

                            WHERE a.item_fg_id = '$item_fg_id' and a.request_date between '$working_date' and '$working_date'");





                        //-------------- Akhir query disini----------------------------------//







                        //RECEIPT

                        foreach ($receipts as $receipt) {

                            $balance = ($begin + ($receipt->qty - $end_qty));

                            $html .= '  <tr>

                                            <td></td>

                                            <td style="text-align:center">' . $nod . '</td>

                                            <td>' . $receipt_type . '</td>

                                            <td>' . $receipt->username . '</td>

                                            <td>' . $receipt->created_date . '</td>

                                            <td>' . $receipt->wo_no . '</td>

                                            <td>' . $receipt->checksheet_label . '</td>

                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>

                                            <td style="text-align:right;">' . number_format($receipt->qty, 2) . '</td>

                                            <td style="text-align:right;">' . number_format(0)  . '</td>

                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>

                                        </tr>';

                            $begin += $receipt->qty;

                            $nod++;

                        }



                        //DELIVERY NOTE

                        foreach ($delivery_notes as $delivery_note) {

                            $balance = ($begin - $delivery_note->qty);

                            $html .= '  <tr>

                                            <td></td>

                                            <td style="text-align:center">' . $nod . '</td>

                                            <td>DELIVERY NOTE</td>

                                            <td>' . $delivery_note->username . '</td>

                                            <td>' . $delivery_note->delivery_note_date . '</td>

                                            <td>' . $delivery_note->delivery_order_no  . '</td>

                                            <td>' . $delivery_note->delivery_note_no . '</td>

                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>

                                            <td style="text-align:right;">' . number_format(0) . '</td>

                                            <td style="text-align:right;">' . number_format($delivery_note->qty, 2)  . '</td>

                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>

                                        </tr>';

                            $begin -= $delivery_note->qty;

                            $nod++;

                        }



                        // TRANSACTION RM (IN and OUT)

                        foreach ($transactions as $transaction) {

                            $trans_type_label = $transaction->transaction_type;

                            $balance = ($transaction->transaction_kind == 'IN') ? ($begin + $transaction->qty) : ($begin - $transaction->qty);

                        

                            $html .= '  <tr>

                                            <td></td>

                                            <td style="text-align:center">' . $nod . '</td>

                                            <td>' . $trans_type_label . '</td>

                                            <td>' . $transaction->username . '</td>

                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>

                                            <td>-</td>

                                            <td>' . $transaction->request_no . '</td>

                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>

                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>

                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>

                                            <td style="text-align:right;">' . number_format($balance, 2) . '</td>

                                        </tr>';

                        

                            // Update balance

                            if ($transaction->transaction_kind == 'IN') {

                                $begin += $transaction->qty;

                            } else {

                                $begin -= $transaction->qty;

                            }

                            

                            $nod++;

                        }

                    }





                    if ($filter_trans_type == 'RECEIPT FG') {



                        //RECEIPT

                        $receipts = $this->crud->query("SELECT f.*, c.name as username

                            -- FROM production_schedules d

                            FROM wip_receipts d

                            LEFT JOIN checksheets e ON d.wo_no = e.wo_no

                            LEFT JOIN scan_item_receipts_fg f ON e.number = f.checksheet_number

                            LEFT JOIN users c ON f.created_by = c.username

                            WHERE d.item_fg_id = '$item_fg_id' and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$working_date' and '$working_date'");



                        if (empty($receipts)) {

                            $receipts = $this->crud->query("SELECT f.*, u.name as username

                                FROM new_barcode_fg a

                                LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id

                                LEFT JOIN users u ON f.created_by = u.username

                                WHERE a.item_fg_id = '$item_fg_id' 

                                AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$working_date' AND '$working_date'");

                            

                            $receipt_type = 'NEW BARCODE FG';

                        } else {

                            $receipt_type = 'RECEIPT FG';

                        }





                        //RECEIPT

                        foreach ($receipts as $receipt) {

                            $balance = ($begin + ($receipt->qty - $end_qty));

                            $html .= '  <tr>

                                            <td></td>

                                            <td style="text-align:center">' . $nod . '</td>

                                            <td>' . $receipt_type . '</td>

                                            <td>' . $receipt->username . '</td>

                                            <td>' . $receipt->created_date . '</td>

                                            <td>' . $receipt->wo_no . '</td>

                                            <td>' . $receipt->checksheet_label . '</td>

                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>

                                            <td style="text-align:right;">' . number_format($receipt->qty, 2) . '</td>

                                            <td style="text-align:right;">' . number_format(0)  . '</td>

                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>

                                        </tr>';

                            $begin += $receipt->qty;

                            $nod++;

                        }

                    }



                    if ($filter_trans_type == 'DELIVERY NOTE') {



                        //DELIVERY NOTE

                        $returns = $this->crud->query("SELECT a.*,

                            d.name as username

                            FROM delivery_notes a 

                            JOIN users d ON a.created_by = d.username

                            WHERE a.item_fg_id = '$item_fg_id' and a.delivery_note_date between '$working_date' and '$working_date'");



                        foreach ($returns as $return) {

                            $balance = ($begin - $return->qty);

                            $html .= '  <tr>

                                            <td></td>

                                            <td style="text-align:center">' . $nod . '</td>

                                            <td>DELIVERY NOTE</td>

                                            <td>' . $return->username . '</td>

                                            <td>' . $return->delivery_note_date . '</td>

                                            <td>' . $return->delivery_order_no  . '</td>

                                            <td>' . $return->delivery_note_no . '</td>

                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>

                                            <td style="text-align:right;">' . number_format(0) . '</td>

                                            <td style="text-align:right;">' . number_format($return->qty, 2)  . '</td>

                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>

                                        </tr>';

                            $begin -= $return->qty;

                            $nod++;

                        }

                    }



                    if ($filter_trans_type == 'ADJ IN STO') {

                        //TRANSACTION

                        $transactions = $this->crud->query("SELECT

                            a.request_date,

                            a.transaction_type,

                            a.transaction_kind,

                            a.request_no,

                            a.qty,

                            b.name as username

                        FROM transaction_fg a

                        JOIN users b ON a.created_by = b.username

                        WHERE a.item_fg_id = '$item_fg_id' and a.transaction_type = 'ADJ IN STO' and a.request_date between '$working_date' and '$working_date'");

            

                        foreach ($transactions as $transaction) {

                            $balance = ($transaction->transaction_kind == 'IN') 

                                        ? ($begin + $transaction->qty) 

                                        : ($begin - $transaction->qty);

                        

                            $html .= '  <tr>

                                            <td></td>

                                            <td style="text-align:center">' . $nod . '</td>

                                            <td>ADJ IN STO</td>

                                            <td>' . $transaction->username . '</td>

                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>

                                            <td>-</td>

                                            <td>' . $transaction->request_no . '</td>

                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>

                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>

                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>

                                            <td style="text-align:right;">' . number_format($balance, 2) . '</td>

                                        </tr>';

                        

                            // Update balance

                            if ($transaction->transaction_kind == 'IN') {

                                $begin += $transaction->qty;

                            } else {

                                $begin -= $transaction->qty;

                            }

                            

                            $nod++;

                        }

                    }



                    if ($filter_trans_type == 'ADJ OUT STO') {

                        //TRANSACTION

                        $transactions = $this->crud->query("SELECT

                            a.request_date,

                            a.transaction_type,

                            a.transaction_kind,

                            a.request_no,

                            a.qty,

                            b.name as username

                        FROM transaction_fg a

                        JOIN users b ON a.created_by = b.username

                        WHERE a.item_fg_id = '$item_fg_id' and a.transaction_type = 'ADJ OUT STO' and a.request_date between '$working_date' and '$working_date'");

            

                        foreach ($transactions as $transaction) {

                            $balance = ($transaction->transaction_kind == 'IN') 

                                        ? ($begin + $transaction->qty) 

                                        : ($begin - $transaction->qty);

                        

                            $html .= '  <tr>

                                            <td></td>

                                            <td style="text-align:center">' . $nod . '</td>

                                            <td>ADJ OUT STO</td>

                                            <td>' . $transaction->username . '</td>

                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>

                                            <td>-</td>

                                            <td>' . $transaction->request_no . '</td>

                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>

                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>

                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>

                                            <td style="text-align:right;">' . number_format($balance, 2) . '</td>

                                        </tr>';

                        

                            // Update balance

                            if ($transaction->transaction_kind == 'IN') {

                                $begin += $transaction->qty;

                            } else {

                                $begin -= $transaction->qty;

                            }

                            

                            $nod++;

                        }

                    }

                }

            }

            $no++;

        }

        $html .= '</table></body></html>';

        echo $html;

    }

}

