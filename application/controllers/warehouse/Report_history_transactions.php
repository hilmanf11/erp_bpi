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

    public function readItemFamily($item_category_id)
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('id !=', "P08"); 
        $this->db->where('deleted', 0);
        $this->db->where("item_category_id", $item_category_id);
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readItemFamilys()
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('id !=', "P08"); 
        $this->db->where('deleted', 0);
        // $this->db->where("item_category_id", $item_category_id);
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_rm_$format.xls");
        }
        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get('filter_division');
        $filter_trans_type = $this->input->get('filter_trans_type');

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);
        //------------------------------------ Mengambil Filter dari Input GET berakhir disini----------------------------------//

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//


        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name, 
            a.division, 
            b.name as prodfam, 
            a.uom,
            c.name as category_name, 
            COALESCE(0,0) as begin_stock,
            (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0)) as qty_in,
            (COALESCE(f.qty,0) + COALESCE(j.qty, 0)) as qty_out
    

            -- (COALESCE(SUM(e.qty), 0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(SUM(CASE WHEN i.transaction_kind = 'IN' THEN i.qty ELSE 0 END), 0)) as qty_in,
            -- (COALESCE(f.qty, 0) + COALESCE(SUM(CASE WHEN i.transaction_kind = 'OUT' THEN i.qty ELSE 0 END), 0)) as qty_out

            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id and b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date between '$filter_from' and '$filter_to'
            LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                WHERE a.return_date between '$filter_from' and '$filter_to'
                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            
            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
                FROM os_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.trans_date between '$filter_from' and '$filter_to'
                GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) i ON a.id = i.item_rm_id and i.transaction_kind = 'IN'

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) j ON a.id = j.item_rm_id and j.transaction_kind = 'OUT'

        -- LEFT JOIN transaction_rm i ON a.id = i.item_rm_id AND i.request_date between '$filter_from' and '$filter_to'
        
        WHERE c.id like '%$filter_item_category%' and b.number like '%$filter_item_family%' and a.id like '%$filter_items%' and a.division like '%$filter_division%' 
        GROUP BY a.id
        ORDER BY c.name DESC, b.name DESC, a.number");

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
            
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th width="20">No</th>
                    <th colspan="3">Product No</th>
                    <th colspan="2">Product Name</th>
                    <th colspan="2">Uom</th>
                    <th colspan="2">Division</th>
                    <th colspan="2">Category</th>
                    <th>Product Family</th>
                    <th width="100">Begin<br>Stock</th>
                    <th width="100">In</th>
                    <th width="100">Out</th>
                    <th width="100">Ending<br>Stock</th>
                </tr>';


        $no = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalEndingStock = 0;

        foreach ($records as $record) {
            $item_rm_id = $record->id;

            //Item Receipts
            $itemReceipts = $this->crud->query("SELECT
                    a.id,(COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0)) - (COALESCE(f.qty,0) + COALESCE(j.qty, 0)) as begin_stock   
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
                    
                LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
                    FROM os_rm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.trans_date < '$filter_from'
                    GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id
                
                LEFT JOIN (
                    SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty
                    FROM transaction_rm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.request_date < '$filter_from'
                    GROUP BY a.item_rm_id, a.transaction_kind
                ) i ON a.id = i.item_rm_id and i.transaction_kind = 'IN'

                LEFT JOIN (
                    SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty
                    FROM transaction_rm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.request_date < '$filter_from'
                    GROUP BY a.item_rm_id, a.transaction_kind
                ) j ON a.id = j.item_rm_id and j.transaction_kind = 'OUT'
                    
                    WHERE a.id like '$item_rm_id'
                    GROUP BY a.id
                    ORDER BY a.number
            ");

            $totalBeginStock += @$itemReceipts[0]->begin_stock;
            $totalIn += $record->qty_in;
            $totalOut += $record->qty_out;
            $totalEndingStock += @(@$itemReceipts[0]->begin_stock + $record->qty_in) - $record->qty_out;


            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3">' . $record->number . '</td>
                            <td colspan="2">' . $record->name . '</td>
                            <td colspan="2">' . $record->uom . '</td>
                            <td colspan="2">' . $record->division . '</td>
                            <td colspan="2">' . $record->category_name . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td style="text-align:right;">' . number_format(@$itemReceipts[0]->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format((@$itemReceipts[0]->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="13" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
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

                    if ($filter_trans_type == '' ) {
                        //-------------- Awal Query disini----------------------------------//                    
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
                        GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");
                        
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

                        //OS RM
                        $os_rms = $this->crud->query("SELECT * FROM os_rm WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(trans_date, '%Y-%m-%d') between '$working_date' and '$working_date'");

                        // TRANSACTION RM (IN and OUT)
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.request_date between '$working_date' and '$working_date'");

                        //-------------- Akhir query disini----------------------------------//




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

                        //OS RM
                        foreach ($os_rms as $os_rm) {
                            $user = $this->crud->read("users", [], ["username" => $os_rm->created_by]);
                            $balance = ($begin + $os_rm->qty);
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>OS RM</td>
                                            <td>' . $user->name . '</td>
                                            <td>' . date("Y-m-d", strtotime($os_rm->created_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($os_rm->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format(0) . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin += $os_rm->qty;
                            $nod++;
                        }

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
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
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
            
                    if ($filter_trans_type == 'RECEIPT') {
                        //RECEIPT
                        $batchSize = 50; // Jumlah data per batch
                        $offset = 0; // Offset awal
                        $isDone = false; // Status apakah semua data telah diproses
                        
                        while (!$isDone) {
                            // Query RECEIPT dengan LIMIT dan OFFSET
                            $receipts = $this->crud->query("
                                SELECT
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
                                WHERE a.item_rm_id = '$item_rm_id' 
                                AND a.receipt_date BETWEEN '$working_date' AND '$working_date'
                                GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id
                                LIMIT 50 OFFSET $offset
                            ");
                        
                            // Jika tidak ada data, berhenti
                            if (count($receipts) == 0) {
                                $isDone = true;
                                break;
                            }
                        
                            // Proses setiap batch RECEIPT
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
                        
                            // Tingkatkan OFFSET untuk batch berikutnya
                            $offset += $batchSize;
                        }
                        
                        // Reset offset untuk query berikutnya
                        $offset = 0; 
                        $isDone = false;
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
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'ADJ IN STO' and a.request_date between '$working_date' and '$working_date'");
            
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
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
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


                    if ($filter_trans_type == 'BPM') {
                        //TRANSACTION
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'BPM' and a.request_date between '$working_date' and '$working_date'");
            
                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN') 
                                        ? ($begin + $transaction->qty) 
                                        : ($begin - $transaction->qty);
                        
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>BPM</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
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
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'ADJ OUT STO' and a.request_date between '$working_date' and '$working_date'");
            
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
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
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

                    if ($filter_trans_type == 'BPB') {
                        //TRANSACTION
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'BPB' and a.request_date between '$working_date' and '$working_date'");
            
                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN') 
                                        ? ($begin + $transaction->qty) 
                                        : ($begin - $transaction->qty);
                        
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>BPB</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
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

                    if ($filter_trans_type == 'KANBAN WO') {
                        //TRANSACTION
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'KANBAN WO' and a.request_date between '$working_date' and '$working_date'");
            
                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN') 
                                        ? ($begin + $transaction->qty) 
                                        : ($begin - $transaction->qty);
                        
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>KANBAN WO</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
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

                    if ($filter_trans_type == 'ISSUED') {
                        //ISSUED
                        $issueds = $this->crud->query("SELECT * FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$working_date' and '$working_date'");
            
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
                                            <td style="text-align:right;">' . number_format($beginforisseud, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0) . '</td>
                                            <td style="text-align:right;">' . number_format($issued->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin -= $issued->qty;
                            $nod++;
                        }
                    }
                }
            }
            $no++;
        }

        $html .= '<tr>
            <td colspan="13" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>
        </tr>';
      
        $html .= '</table></body></html>';
        echo $html;
    }

    public function lsb($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_rm_$format.xls");
        }
        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get('filter_division');
        $filter_trans_type = $this->input->get('filter_trans_type');

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);
        //------------------------------------ Mengambil Filter dari Input GET berakhir disini----------------------------------//

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//


        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name, 
            a.division, 
            b.name as prodfam, 
            l.name as sub_prodfam, 
            a.uom,
            c.name as category_name, 
            COALESCE(0,0) as begin_stock,

            COALESCE(SUM(e.qty),0) as receipt_qty, 
            COALESCE(i.qty,0) as bpm_qty, 
            COALESCE(k.qty,0) as adj_in_qty, 

            COALESCE(f.qty,0) as qty_issued,
            COALESCE(j.qty,0) as qty_kanban,
            COALESCE(m.qty,0) as adj_out_qty,
            COALESCE(n.qty,0) as bpb_qty, 

            (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0) + COALESCE(k.qty, 0)) as qty_in,
            (COALESCE(f.qty,0) + COALESCE(j.qty, 0) + COALESCE(m.qty, 0)+ COALESCE(n.qty, 0)) as qty_out
    

            -- (COALESCE(SUM(e.qty), 0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(SUM(CASE WHEN i.transaction_kind = 'IN' THEN i.qty ELSE 0 END), 0)) as qty_in,
            -- (COALESCE(f.qty, 0) + COALESCE(SUM(CASE WHEN i.transaction_kind = 'OUT' THEN i.qty ELSE 0 END), 0)) as qty_out

            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id and b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date between '$filter_from' and '$filter_to'
            LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
            LEFT JOIN item_family_subs l ON a.item_sub_family_id = l.id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                WHERE a.return_date between '$filter_from' and '$filter_to'
                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            
            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
                FROM os_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.trans_date between '$filter_from' and '$filter_to'
                GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id

            -- IN TRANSACTION di mulai dari sini----------------------- 

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) i ON a.id = i.item_rm_id and i.transaction_kind = 'IN' and i.transaction_type = 'BPM'

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) k ON a.id = k.item_rm_id and k.transaction_kind = 'IN' and k.transaction_type = 'ADJ IN STO'

            -- OUT TRANSACTION di mulai dari sini-----------------------

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) j ON a.id = j.item_rm_id and j.transaction_kind = 'OUT' and j.transaction_type = 'KANBAN WO'
        
            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) m ON a.id = m.item_rm_id and m.transaction_kind = 'OUT' and m.transaction_type = 'ADJ OUT STO'

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) n ON a.id = n.item_rm_id and n.transaction_kind = 'OUT' and n.transaction_type = 'BPB'
        
        WHERE c.id like '%$filter_item_category%' and b.number like '%$filter_item_family%' and a.id like '%$filter_items%' and a.division like '%$filter_division%' 
        GROUP BY a.id
        ORDER BY c.name DESC, b.name DESC, a.number");

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
                <h3 style="margin:0;">LBS (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Product No</th>
                    <th rowspan="2">Product Name</th>
                    <th rowspan="2">Uom</th>
                    <th rowspan="2">Division</th>
                    <th rowspan="2">Category</th>
                    <th rowspan="2">Product Family</th>
                    <th rowspan="2">Sub Product <br>Family</th>
                    <th rowspan="2" width="100">Begin<br>Stock</th>
                    <th rowspan="2" width="100">In</th>
                    <th rowspan="2" width="100">Out</th>
                    <th rowspan="2" width="100">Ending<br>Stock</th>
                    <th colspan="3">IN</th>
                    <th colspan="4">OUT</th>
                    <th rowspan="2" width="100">Total<br>In</th>
                    <th rowspan="2" width="100">Total<br>Out</th>
                    <th rowspan="2" width="100">Selisih Summary <br>VS Detail (IN)</th>
                    <th rowspan="2" width="100">Selisih Summary <br>VS Detail (OUT)</th>
                </tr>
                <tr>
                    <th width="80">Purchase</th>
                    <th width="80">BPM</th>
                    <th width="80">ADJ STO</th>
                    <th width="80">Supply Sheet</th>
                    <th width="80">Kanban</th>
                    <th width="80">BPB STO</th>
                    <th width="80">ADJ STO</th>
                </tr>';

                
        $no = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalEndingStock = 0;

        $totalReceiptQty = 0;
        $totalBpmQty = 0;
        $totalAdjInQty = 0;

        $totalQtyIssued = 0;
        $totalQtyKanban = 0;
        $totalAdjOutQty = 0;
        $totalBpbQty = 0;

        $totalQtyIn = 0;
        $totalQtyOut = 0;
        $totalQtySelisihIn = 0;
        $totalQtySelisihOut = 0;

        foreach ($records as $record) {

            $item_rm_id = $record->id;
            //Item Receipts
            $itemReceipts = $this->crud->query("SELECT
                    a.id,(COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0)) - (COALESCE(f.qty,0) + COALESCE(j.qty, 0)) as begin_stock   
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
                    
                LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
                    FROM os_rm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.trans_date < '$filter_from'
                    GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id
                
                LEFT JOIN (
                    SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty
                    FROM transaction_rm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.request_date < '$filter_from'
                    GROUP BY a.item_rm_id, a.transaction_kind
                ) i ON a.id = i.item_rm_id and i.transaction_kind = 'IN'

                LEFT JOIN (
                    SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty
                    FROM transaction_rm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.request_date < '$filter_from'
                    GROUP BY a.item_rm_id, a.transaction_kind
                ) j ON a.id = j.item_rm_id and j.transaction_kind = 'OUT'
                    
                    WHERE a.id like '$item_rm_id'
                    GROUP BY a.id
                    ORDER BY a.number
            ");

            $totalBeginStock += @$itemReceipts[0]->begin_stock;
            $totalIn += $record->qty_in;
            $totalOut += $record->qty_out;
            $totalEndingStock += @(@$itemReceipts[0]->begin_stock + $record->qty_in) - $record->qty_out;
            
            $totalReceiptQty += $record->receipt_qty;
            $totalBpmQty += $record->bpm_qty;
            $totalAdjInQty += $record->adj_in_qty;

            $totalQtyIssued += $record->qty_issued;
            $totalQtyKanban += $record->qty_kanban;
            $totalAdjOutQty += $record->adj_out_qty;
            $totalBpbQty += $record->bpb_qty;

            $totalQtyIn = ($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty);
            $totalQtyOut = ($record->qty_issued + $record->qty_kanban + $record->adj_out_qty + $record->bpb_qty);
            $totalQtySelisihIn = (($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty) - $record->qty_in);
            $totalQtySelisihOut = (($record->qty_issued + $record->qty_kanban + $record->adj_out_qty + $record->bpb_qty) - $record->qty_out);

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $record->number . '</td>
                            <td>' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->division . '</td>
                            <td>' . $record->category_name . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td>' . $record->sub_prodfam . '</td>
                            <td style="text-align:right;">' . number_format(@$itemReceipts[0]->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format((@$itemReceipts[0]->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>
                            
                            <td style="text-align:right;">' . $record->receipt_qty . '</td>
                            <td style="text-align:right;">' . $record->bpm_qty . '</td>
                            <td style="text-align:right;">' . $record->adj_in_qty . '</td>

                            <td style="text-align:right;">' . $record->qty_issued . '</td>
                            <td style="text-align:right;">' . $record->qty_kanban . '</td>
                            <td style="text-align:right;">' . $record->adj_out_qty . '</td>
                            <td style="text-align:right;">' . $record->bpb_qty . '</td>

                            <td style="text-align:right;">' . number_format($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty,2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_issued + $record->qty_kanban + $record->adj_out_qty + $record->bpb_qty,2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty) - $record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->qty_issued + $record->qty_kanban + $record->adj_out_qty + $record->bpb_qty) - $record->qty_out, 2) . '</td>

                        </tr>';
            $no++;
        }

        $html .= '<tr>
            <td colspan="8" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalReceiptQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalBpmQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalAdjInQty, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalQtyIssued, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyKanban, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalAdjOutQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalBpbQty, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalQtyIn, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyOut, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtySelisihIn, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtySelisihOut, 2) . '</td>
            
        </tr>';
      
        $html .= '</table></body></html>';
        echo $html;
    }
  
    public function detail_transaction($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_rm_$format.xls");
        }
        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get('filter_division');
        $filter_trans_type = $this->input->get('filter_trans_type');

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);
        //------------------------------------ Mengambil Filter dari Input GET berakhir disini----------------------------------//

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//


        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name, 
            a.division, 
            b.name as prodfam, 
            l.name as sub_prodfam,
            a.uom,
            c.name as category_name, 
            COALESCE(0,0) as begin_stock,
            (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0)) as qty_in,
            (COALESCE(f.qty,0) + COALESCE(j.qty, 0)) as qty_out
    

            -- (COALESCE(SUM(e.qty), 0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(SUM(CASE WHEN i.transaction_kind = 'IN' THEN i.qty ELSE 0 END), 0)) as qty_in,
            -- (COALESCE(f.qty, 0) + COALESCE(SUM(CASE WHEN i.transaction_kind = 'OUT' THEN i.qty ELSE 0 END), 0)) as qty_out

            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id and b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date between '$filter_from' and '$filter_to'
            LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
            LEFT JOIN item_family_subs l ON a.item_sub_family_id = l.id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
                FROM return_materials a 
                JOIN return_material_labels b ON a.return_id = b.return_id
                JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                WHERE a.return_date between '$filter_from' and '$filter_to'
                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            
            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
                FROM os_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.trans_date between '$filter_from' and '$filter_to'
                GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) i ON a.id = i.item_rm_id and i.transaction_kind = 'IN'

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) j ON a.id = j.item_rm_id and j.transaction_kind = 'OUT'

        -- LEFT JOIN transaction_rm i ON a.id = i.item_rm_id AND i.request_date between '$filter_from' and '$filter_to'
        
        WHERE c.id like '%$filter_item_category%' and b.number like '%$filter_item_family%' and a.id like '%$filter_items%' and a.division like '%$filter_division%' 
        GROUP BY a.id
        ORDER BY c.name DESC, b.name DESC, a.number");

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
                <h3 style="margin:0;">DETAIL TRANSACTION (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th width="20">No</th>
                    <th>Part No</th>
                    <th>Category</th>
                    <th>Product Family</th>
                    <th>Sub Product <br>Family</th>
                    <th>Uom</th>
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


        $no = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalEndingStock = 0;

        foreach ($records as $record) {
            $item_rm_id = $record->id;

            //Item Receipts
            $itemReceipts = $this->crud->query("SELECT
                    a.id,(COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0)) - (COALESCE(f.qty,0) + COALESCE(j.qty, 0)) as begin_stock   
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
                    
                LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
                    FROM os_rm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.trans_date < '$filter_from'
                    GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id
                
                LEFT JOIN (
                    SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty
                    FROM transaction_rm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.request_date < '$filter_from'
                    GROUP BY a.item_rm_id, a.transaction_kind
                ) i ON a.id = i.item_rm_id and i.transaction_kind = 'IN'

                LEFT JOIN (
                    SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty
                    FROM transaction_rm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.request_date < '$filter_from'
                    GROUP BY a.item_rm_id, a.transaction_kind
                ) j ON a.id = j.item_rm_id and j.transaction_kind = 'OUT'
                    
                    WHERE a.id like '$item_rm_id'
                    GROUP BY a.id
                    ORDER BY a.number
            ");

            $totalBeginStock += @$itemReceipts[0]->begin_stock;
            $totalIn += $record->qty_in;
            $totalOut += $record->qty_out;
            $totalEndingStock += @(@$itemReceipts[0]->begin_stock + $record->qty_in) - $record->qty_out;

            if ($filter_display == "DETAIL") {
                $begin = @$itemReceipts[0]->begin_stock;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;
                for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $i);

                    if ($filter_trans_type == '' ) {
                        //-------------- Awal Query disini----------------------------------//                    
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
                        GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");
                        
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

                        //OS RM
                        $os_rms = $this->crud->query("SELECT * FROM os_rm WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(trans_date, '%Y-%m-%d') between '$working_date' and '$working_date'");

                        // TRANSACTION RM (IN and OUT)
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.request_date between '$working_date' and '$working_date'");

                        //-------------- Akhir query disini----------------------------------//

                        
                        //Purchase Order Receipt
                        foreach ($receipts as $receipt) {
                            $balance = ($begin + ($receipt->qty_receipt - $end_qty));
                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>RECEIPT</td>
                                            <td>' . $receipt->username . '</td>
                                            <td>' . $receipt->receipt_date . '</td>
                                            <td>' . $receipt->bc_kind . '</td>
                                            <td>' . $receipt->bc_aju . '</td>
                                            <td>' . $receipt->bc_document . '</td>
                                            <td>' . $receipt->bc_date . '</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($receipt->qty_receipt, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin += $receipt->qty_receipt;
                            $no++;
                        }

                        //Issued Material
                        foreach ($issueds as $issued) {
                            $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                            $balance = ($begin - $issued->qty);
                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>ISSUED</td>
                                            <td>' . $user->name . '</td>
                                            <td>' . date("Y-m-d", strtotime($issued->created_date)) . '</td>
                                            <td>-</td>
                                            <td>' . $issued->label_no . '</td>
                                            <td>' . $issued->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($issued->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin -= $issued->qty;
                            $no++;
                        }
                        //Return Material
                        foreach ($returns as $return) {
                            $balance = ($begin + $return->qty);
                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>RETURN</td>
                                            <td>' . $return->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($return->return_date)) . '</td>
                                            <td>-</td>
                                            <td>' . $return->label_no . '</td>
                                            <td>' . $return->return_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($return->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin += $return->qty;
                            $no++;
                        }

                        //OS RM
                        foreach ($os_rms as $os_rm) {
                            $user = $this->crud->read("users", [], ["username" => $os_rm->created_by]);
                            $balance = ($begin + $os_rm->qty);
                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>OS RM</td>
                                            <td>' . $user->name . '</td>
                                            <td>' . date("Y-m-d", strtotime($os_rm->created_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($os_rm->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin += $os_rm->qty;
                            $nod++;
                        }

                        foreach ($transactions as $transaction) {
                            $trans_type_label = $transaction->transaction_type;
                            $balance = ($transaction->transaction_kind == 'IN') ? ($begin + $transaction->qty) : ($begin - $transaction->qty);
                        
                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>' . $trans_type_label . '</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
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
                            
                            $no++;
                        }

                    }
            
                    if ($filter_trans_type == 'RECEIPT') {
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
                        GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");
            
                        foreach ($receipts as $receipt) {
                            $balance = ($begin + ($receipt->qty_receipt - $end_qty));
                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>RECEIPT</td>
                                            <td>' . $receipt->username . '</td>
                                            <td>' . $receipt->receipt_date . '</td>
                                            <td>' . $receipt->bc_kind . '</td>
                                            <td>' . $receipt->bc_aju . '</td>
                                            <td>' . $receipt->bc_document . '</td>
                                            <td>' . $receipt->bc_date . '</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($receipt->qty_receipt, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin += $receipt->qty_receipt;
                            $no++;
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
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'ADJ IN STO' and a.request_date between '$working_date' and '$working_date'");
            
                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN') 
                                        ? ($begin + $transaction->qty) 
                                        : ($begin - $transaction->qty);
                        
                                        $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>ADJ IN STO</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
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
                            
                            $no++;
                        }
                    }


                    if ($filter_trans_type == 'BPM') {
                        //TRANSACTION
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'BPM' and a.request_date between '$working_date' and '$working_date'");
            
                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN') 
                                        ? ($begin + $transaction->qty) 
                                        : ($begin - $transaction->qty);
                        
                                        $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>BPM</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
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
                            
                            $no++;
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
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'ADJ OUT STO' and a.request_date between '$working_date' and '$working_date'");
            
                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN') 
                                        ? ($begin + $transaction->qty) 
                                        : ($begin - $transaction->qty);
                        
                                        $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>ADJ OUT STO</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
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
                            
                            $no++;
                        }
                    }

                    if ($filter_trans_type == 'BPB') {
                        //TRANSACTION
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'BPB' and a.request_date between '$working_date' and '$working_date'");
            
                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN') 
                                        ? ($begin + $transaction->qty) 
                                        : ($begin - $transaction->qty);
                        
                                        $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>BPB</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
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
                            
                            $no++;
                        }
                    }

                    if ($filter_trans_type == 'KANBAN WO') {
                        //TRANSACTION
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'KANBAN WO' and a.request_date between '$working_date' and '$working_date'");
            
                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN') 
                                        ? ($begin + $transaction->qty) 
                                        : ($begin - $transaction->qty);
                        
                                        $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>KANBAN WO</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
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
                            
                            $no++;
                        }
                    }

                    if ($filter_trans_type == 'ISSUED') {
                        //ISSUED
                        $issueds = $this->crud->query("SELECT * FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$working_date' and '$working_date'");
            
                        foreach ($issueds as $issued) {
                            $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                            $balance = ($begin - $issued->qty);
                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>ISSUED</td>
                                            <td>' . $user->name . '</td>
                                            <td>' . date("Y-m-d", strtotime($issued->created_date)) . '</td>
                                            <td>-</td>
                                            <td>' . $issued->label_no . '</td>
                                            <td>' . $issued->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($beginforisseud, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0) . '</td>
                                            <td style="text-align:right;">' . number_format($issued->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin -= $issued->qty;
                            $no++;
                        }
                    }
                }
            }
            $no++;
        }

        // $html .= '<tr>
        //     <td colspan="14" style="text-align:right;"><b>GRAND TOTAL</b></td>
        //     <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>
        //     <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>
        //     <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>
        //     <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>
        // </tr>';
      
        $html .= '</table></body></html>';
        echo $html;
    }

}
