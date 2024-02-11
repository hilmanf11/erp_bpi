<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions extends CI_Controller
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
            $this->load->view('warehouse/report_history_transactions');
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

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_transtype = $this->input->get('filter_transtype');

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
            b.name as prodfam, 
            a.uom, 
            COALESCE(0,0) as begin_stock,
            (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0)) as qty_in,
            f.qty as qty_out,
            (COALESCE(SUM(e.qty),0) - COALESCE(f.qty, 0) + COALESCE(g.return_qty, 0)) as end_stock
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'
        LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date between '$filter_from' and '$filter_to'
        LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
            FROM return_materials a 
            JOIN return_material_labels b ON a.return_id = b.return_id
            JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
            WHERE a.return_date between '$filter_from' and '$filter_to'
            GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        WHERE b.number like '%$filter_item_family%' and a.id like '%$filter_items%'
        GROUP BY a.id
        ORDER BY a.number");

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
                <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th colspan="3">Product No</th>
                    <th colspan="2">Product Name</th>
                    <th colspan="2">Uom</th>
                    <th>Product Family</th>
                    <th width="100">Begin<br>Stock</th>
                    <th width="100">In</th>
                    <th width="100">Out</th>
                    <th width="100">Ending<br>Stock</th>
                </tr>';
        $no = 1;
        foreach ($records as $record) {
            $item_rm_id = $record->id;

            //Item Receipts
            $itemReceipts = $this->crud->query("SELECT
                a.id,
                (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty,0) - COALESCE(f.qty, 0)) as begin_stock
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'
            LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date < '$filter_from'
            LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                WHERE a.return_date < '$filter_from'
                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            WHERE a.id like '$item_rm_id'
            GROUP BY a.id
            ORDER BY a.number");

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3">' . $record->number . '</td>
                            <td colspan="2">' . $record->name . '</td>
                            <td colspan="2">' . $record->uom . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td style="text-align:right;">' . number_format(@$itemReceipts[0]->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format((@$itemReceipts[0]->begin_stock + $record->end_stock), 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="13" style="background:#D1FFC6;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>
                            <tr>
                                <th width="20"></th>
                                <th width="20">No</th>
                                <th>Trans Type</th>
                                <th>Created By</th>
                                <th>Trans Date</th>
                                <th>Custom. Kind</th>
                                <th>Custom. No</th>
                                <th>Doc. No</th>
                                <th>Custom. Date</th>
                                <th>Begin</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Balance</th>
                            </tr>';

                $nod = 1;
                $begin = @$itemReceipts[0]->begin_stock;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;
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
                        c.name as username
                    FROM purchase_order_receipts a 
                    JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
                    JOIN users c ON a.created_by = c.username
                    WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$working_date' and '$working_date'
                    GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date");

                    //ISSUED
                    $issueds = $this->crud->query("SELECT * FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$working_date' and '$working_date'");

                    //RETURN
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
                        $balance = ($begin + ($receipt->qty_receipt - $end_qty));
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>RECEIPT</td>
                                        <td>' . $receipt->username . '</td>
                                        <td>' . $receipt->receipt_date . '</td>
                                        <td>' . $receipt->bc_kind . '</td>
                                        <td>' . $receipt->bc_aju . '</td>
                                        <td>' . $receipt->bc_document . '</td>
                                        <td>' . $receipt->bc_date . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($receipt->qty_receipt, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin += $receipt->qty_receipt;
                        $nod++;
                    }

                    //Issued Material
                    foreach ($issueds as $issued) {
                        $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                        $balance = ($begin - $issued->qty);
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>ISSUED</td>
                                        <td>' . $user->name . '</td>
                                        <td>' . date("Y-m-d", strtotime($issued->created_date)) . '</td>
                                        <td>-</td>
                                        <td>' . $issued->label_no . '</td>
                                        <td>' . $issued->request_no . '</td>
                                        <td>-</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0) . '</td>
                                        <td style="text-align:right;">' . number_format($issued->qty, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin -= $issued->qty;
                        $nod++;
                    }

                    //Return Material
                    foreach ($returns as $return) {
                        $balance = ($begin + $return->qty);
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>RETURN</td>
                                        <td>' . $return->username . '</td>
                                        <td>' . date("Y-m-d", strtotime($return->return_date)) . '</td>
                                        <td>-</td>
                                        <td>' . $return->label_no . '</td>
                                        <td>' . $return->return_no . '</td>
                                        <td>-</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($return->qty, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format(0) . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin += $return->qty;
                        $nod++;
                    }
                }
            }
            $no++;
        }
        // if ($filter_transtype == "T001") {
        //     $html .= $htmlReceipts;
        // } elseif ($filter_transtype == "T002") {
        //     $html .= $htmlIssueds;
        // } else {
        //     $html .= $htmlReceipts;
        //     $html .= $htmlIssueds;
        // }
        $html .= '</table></body></html>';
        echo $html;
    }
}
