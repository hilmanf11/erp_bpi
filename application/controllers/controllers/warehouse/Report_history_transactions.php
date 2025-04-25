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


        // $records = $this->crud->query("SELECT
        //     a.id,
        //     a.number, 
        //     a.name, 
        //     a.division, 
        //     b.name as prodfam, 
        //     a.uom,
        //     c.name as category_name, 
        //     COALESCE(0,0) as begin_stock,
        //     (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0)) as qty_in,
        //     (COALESCE(f.qty,0) + COALESCE(j.qty, 0)) as qty_out
    
        //     FROM item_rm a 
        //     JOIN item_familys b ON a.item_family_id = b.id and b.number != 'FG'
        //     JOIN item_categories c ON a.item_category_id = c.id
        //     LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date between '$filter_from' and '$filter_to'
        //     LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
        //     LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        //     LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
        //         FROM return_materials a 
        //         JOIN return_material_labels b ON a.return_id = b.return_id
        //         JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
        //         WHERE a.return_date between '$filter_from' and '$filter_to'
        //         GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            
        //     LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
        //         FROM os_rm a
        //         JOIN item_rm b ON a.item_rm_id = b.id
        //         WHERE a.trans_date between '$filter_from' and '$filter_to'
        //         GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id

        //     LEFT JOIN (
        //         SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty
        //         FROM transaction_rm a
        //         JOIN item_rm b ON a.item_rm_id = b.id
        //         WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'
        //         GROUP BY a.item_rm_id, a.transaction_kind
        //     ) i ON a.id = i.item_rm_id and i.transaction_kind = 'IN'

        //     LEFT JOIN (
        //         SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty
        //         FROM transaction_rm a
        //         JOIN item_rm b ON a.item_rm_id = b.id
        //         WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'
        //         GROUP BY a.item_rm_id, a.transaction_kind
        //     ) j ON a.id = j.item_rm_id and j.transaction_kind = 'OUT'
        
        // WHERE c.id like '%$filter_item_category%' and b.number like '%$filter_item_family%' and a.id like '%$filter_items%' and a.division like '%$filter_division%' 
        // GROUP BY a.id
        // ORDER BY c.name DESC, b.name DESC, a.number");

        // --------------------------IN-----------------------------------

        // // Step 1: Hitung qty_in dari `scan_item_receipts`
        // $query_qty_in_scan = "SELECT d.item_rm_id,SUM(e.qty) as qty_in_scan
        // FROM purchase_order_receipts d
        // LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
        // WHERE d.receipt_date BETWEEN '$filter_from' AND '$filter_to'
        // GROUP BY d.item_rm_id";

        // // Step 3: Hitung return_qty dari `return_materials`
        // $query_return_qty = "SELECT a.item_rm_id, SUM(c.qty) as return_qty
        // FROM return_materials a
        // JOIN return_material_labels b ON a.return_id = b.return_id
        // JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no
        // WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to'
        // GROUP BY a.item_rm_id";

        // // Step 4: Hitung qty_stock_rm dari `os_rm`
        // $query_qty_stock_rm = "SELECT item_rm_id, SUM(qty) as qty_stock_rm
        // FROM os_rm
        // WHERE trans_date BETWEEN '$filter_from' AND '$filter_to'
        // GROUP BY item_rm_id";

        // // Step 5: Hitung qty_in dari `transaction_rm` (kind = IN)
        // $query_transaction_in = "SELECT item_rm_id, SUM(qty) as qty_in_transaction
        // FROM transaction_rm
        // WHERE transaction_kind = 'IN'
        // AND request_date BETWEEN '$filter_from' AND '$filter_to'
        // GROUP BY item_rm_id";

        // //------------------------------OUT------------------------------------

        // // Step 2: Hitung qty_out dari `issued_material_details`
        // $query_qty_out_issued = "SELECT item_rm_id, SUM(qty) as qty_out_issued
        // FROM issued_material_details
        // WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
        // GROUP BY item_rm_id";

        // // Step 6: Hitung qty_out dari `transaction_rm` (kind = OUT)
        // $query_transaction_out = "SELECT item_rm_id, SUM(qty) as qty_out_transaction
        // FROM transaction_rm
        // WHERE transaction_kind = 'OUT'
        // AND request_date BETWEEN '$filter_from' AND '$filter_to'
        // GROUP BY item_rm_id";

        // // ---------------------------------------------------------------------

        // // Step 7: Gabungkan semua data dalam query utama
        // $query_main = "SELECT 
        //     a.id,
        //     a.number, 
        //     a.name, 
        //     a.division, 
        //     b.name as prodfam, 
        //     a.uom,
        //     c.name as category_name,
        //     0 AS begin_stock,
        //     (COALESCE(qs.qty_in_scan, 0) + COALESCE(rt.return_qty, 0) + COALESCE(sr.qty_stock_rm, 0) + COALESCE(ti.qty_in_transaction, 0)) as qty_in,
        //     (COALESCE(io.qty_out_issued, 0) + COALESCE(tr_out.qty_out_transaction, 0)) as qty_out
        // FROM item_rm a
        // JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
        // JOIN item_categories c ON a.item_category_id = c.id
        // LEFT JOIN ($query_qty_in_scan) qs ON a.id = qs.item_rm_id
        // LEFT JOIN ($query_qty_out_issued) `io` ON a.id = io.item_rm_id
        // LEFT JOIN ($query_return_qty) rt ON a.id = rt.item_rm_id
        // LEFT JOIN ($query_qty_stock_rm) sr ON a.id = sr.item_rm_id
        // LEFT JOIN ($query_transaction_in) ti ON a.id = ti.item_rm_id
        // LEFT JOIN ($query_transaction_out) tr_out ON a.id = tr_out.item_rm_id
        // WHERE c.id LIKE '%$filter_item_category%'
        // AND b.number LIKE '%$filter_item_family%'
        // AND a.id LIKE '%$filter_items%'
        // AND a.division LIKE '%$filter_division%'
        // GROUP BY a.id
        // ORDER BY c.name DESC, b.name DESC, a.number";

        // // Eksekusi query
        // $records = $this->crud->query($query_main);

        $query_main = "SELECT 
            a.id,
            a.number, 
            a.name, 
            a.division, 
            b.name as prodfam, 
            a.uom,
            c.name as category_name,
            COALESCE(j.begin_stock) AS begin_stock,
            (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) AS qty_in,
            (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) AS qty_out
        FROM item_rm a
        JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
        JOIN item_categories c ON a.item_category_id = c.id
        LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i ON a.id = i.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) k ON a.id = k.item_rm_id

        LEFT JOIN (SELECT a.id, a.number, ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
                        FROM item_rm a
                        LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_from'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$filter_from' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                    ) j ON a.id = j.id

        WHERE c.id LIKE '%$filter_item_category%'
        AND b.number LIKE '%$filter_item_family%'
        AND a.id LIKE '%$filter_items%'
        AND a.division LIKE '%$filter_division%'
        GROUP BY a.id
        ORDER BY c.name DESC, b.name DESC, a.number";

        // Eksekusi query
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

            $totalBeginStock += @$record->begin_stock;
            $totalIn += $record->qty_in;
            $totalOut += $record->qty_out;
            $totalEndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out;


            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3">' . $record->number . '</td>
                            <td colspan="2">' . $record->name . '</td>
                            <td colspan="2">' . $record->uom . '</td>
                            <td colspan="2">' . $record->division . '</td>
                            <td colspan="2">' . $record->category_name . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format((@$record->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>
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
                $begin = @$record->begin_stock;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;
                // for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                //     $working_date = date('Y-m-d', $i);

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
                        WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$filter_from' and '$filter_to'
                        GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");
                        
                        //ISSUED
                        $issueds = $this->crud->query("SELECT created_by, qty, created_date, label_no, request_no FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

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
                        WHERE a.item_rm_id = '$item_rm_id' and a.return_date between '$filter_from' and '$filter_to'
                        GROUP BY b.label_no");

                        // //OS RM
                        $os_rms = $this->crud->query("SELECT created_by, created_date, qty FROM os_rm WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(trans_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                        //SCAN BPM
                        $bpm_scans = $this->crud->query("SELECT 
                        created_by, 
                        qty, 
                        created_date, 
                        label, 
                        request_date, 
                        request_id 
                        FROM scan_item_bpm 
                        WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(request_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                        // // TRANSACTION RM (IN and OUT)
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.request_date between '$filter_from' and '$filter_to'");

                        //-------------- Akhir query disini----------------------------------//

                        $all_data = [];

                        // --- RECEIPT ---
                        foreach ($receipts as $r) {
                            $all_data[] = [
                                'type' => 'RECEIPT',
                                'date' => $r->receipt_date,
                                'username' => $r->username,
                                'qty_in' => $r->qty_receipt,
                                'qty_out' => 0,
                                'doc1' => $r->bc_kind,
                                'doc2' => $r->bc_aju,
                                'doc3' => $r->bc_document,
                                'doc4' => $r->bc_date
                            ];
                        }

                        // --- ISSUED ---
                        foreach ($issueds as $i) {
                            $user = $this->crud->read("users", [], ["username" => $i->created_by]);
                            $all_data[] = [
                                'type' => 'ISSUED',
                                'date' => $i->created_date,
                                'username' => $user->name,
                                'qty_in' => 0,
                                'qty_out' => $i->qty,
                                'doc1' => '-',
                                'doc2' => $i->label_no,
                                'doc3' => $i->request_no,
                                'doc4' => '-'
                            ];
                        }

                        // --- RETURN ---
                        foreach ($returns as $r) {
                            $all_data[] = [
                                'type' => 'RETURN',
                                'date' => $r->return_date,
                                'username' => $r->username,
                                'qty_in' => $r->qty,
                                'qty_out' => 0,
                                'doc1' => '-',
                                'doc2' => $r->label_no,
                                'doc3' => $r->return_no,
                                'doc4' => '-'
                            ];
                        }

                        // --- OS RM ---
                        foreach ($os_rms as $o) {
                            $user = $this->crud->read("users", [], ["username" => $o->created_by]);
                            $all_data[] = [
                                'type' => 'OS RM',
                                'date' => $o->created_date,
                                'username' => $user->name,
                                'qty_in' => $o->qty,
                                'qty_out' => 0,
                                'doc1' => '-',
                                'doc2' => '-',
                                'doc3' => '-',
                                'doc4' => '-'
                            ];
                        }

                        // --- SCAN BPM ---
                        foreach ($bpm_scans as $b) {
                            $user = $this->crud->read("users", [], ["username" => $b->created_by]);
                            $all_data[] = [
                                'type' => 'BPM',
                                'date' => $b->created_date,
                                'username' => $user->name,
                                'qty_in' => $b->qty,
                                'qty_out' => 0,
                                'doc1' => '-',
                                'doc2' => $b->label,
                                'doc3' => $b->request_id,
                                'doc4' => $b->request_date
                            ];
                        }

                        // --- TRANSACTION ---
                        foreach ($transactions as $t) {
                            $qty_in = $t->transaction_kind == 'IN' ? $t->qty : 0;
                            $qty_out = $t->transaction_kind == 'OUT' ? $t->qty : 0;

                            $all_data[] = [
                                'type' => $t->transaction_type,
                                'date' => $t->request_date,
                                'username' => $t->username,
                                'qty_in' => $qty_in,
                                'qty_out' => $qty_out,
                                'doc1' => '-',
                                'doc2' => '-',
                                'doc3' => $t->request_no,
                                'doc4' => '-'
                            ];
                        }

                        usort($all_data, function ($a, $b) {
                            return strtotime($a['date']) - strtotime($b['date']);
                        });

                        foreach ($all_data as $data) {
                            $balance = $begin + $data['qty_in'] - $data['qty_out'];
                        
                            $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td>' . $data['type'] . '</td>
                                <td>' . $data['username'] . '</td>
                                <td>' . date("Y-m-d", strtotime($data['date'])) . '</td>
                                <td>' . $data['doc1'] . '</td>
                                <td>' . $data['doc2'] . '</td>
                                <td>' . $data['doc3'] . '</td>
                                <td>' . $data['doc4'] . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_in'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_out'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        
                            $begin = $balance;
                            $nod++;
                        }
                        
                        // Dokumentasi : penerapan HTML tanpa sort Date ------------- //
                            // //Purchase Order Receipt
                            // foreach ($receipts as $receipt) {
                            //     $balance = ($begin + ($receipt->qty_receipt - $end_qty));
                            //     $html .= '  <tr>
                            //                     <td></td>
                            //                     <td style="text-align:center">' . $nod . '</td>
                            //                     <td>RECEIPT</td>
                            //                     <td>' . $receipt->username . '</td>
                            //                     <td>' . $receipt->receipt_date . '</td>
                            //                     <td>' . $receipt->bc_kind . '</td>
                            //                     <td>' . $receipt->bc_aju . '</td>
                            //                     <td>' . $receipt->bc_document . '</td>
                            //                     <td>' . $receipt->bc_date . '</td>
                            //                     <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                            //                     <td style="text-align:right;">' . number_format($receipt->qty_receipt, 2) . '</td>
                            //                     <td style="text-align:right;">' . number_format(0)  . '</td>
                            //                     <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                            //                 </tr>';
                            //     $begin += $receipt->qty_receipt;
                            //     $nod++;
                            // }
                            // //Issued Material
                            // foreach ($issueds as $issued) {
                            //     $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                            //     $balance = ($begin - $issued->qty);
                            //     $html .= '  <tr>
                            //                     <td></td>
                            //                     <td style="text-align:center">' . $nod . '</td>
                            //                     <td>ISSUED</td>
                            //                     <td>' . $user->name . '</td>
                            //                     <td>' . date("Y-m-d", strtotime($issued->created_date)) . '</td>
                            //                     <td>-</td>
                            //                     <td>' . $issued->label_no . '</td>
                            //                     <td>' . $issued->request_no . '</td>
                            //                     <td>-</td>
                            //                     <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                            //                     <td style="text-align:right;">' . number_format(0) . '</td>
                            //                     <td style="text-align:right;">' . number_format($issued->qty, 2)  . '</td>
                            //                     <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                            //                 </tr>';
                            //     $begin -= $issued->qty;
                            //     $nod++;
                            // }
                            // //Return Material
                            // foreach ($returns as $return) {
                            //     $balance = ($begin + $return->qty);
                            //     $html .= '  <tr>
                            //                     <td></td>
                            //                     <td style="text-align:center">' . $nod . '</td>
                            //                     <td>RETURN</td>
                            //                     <td>' . $return->username . '</td>
                            //                     <td>' . date("Y-m-d", strtotime($return->return_date)) . '</td>
                            //                     <td>-</td>
                            //                     <td>' . $return->label_no . '</td>
                            //                     <td>' . $return->return_no . '</td>
                            //                     <td>-</td>
                            //                     <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                            //                     <td style="text-align:right;">' . number_format($return->qty, 2)  . '</td>
                            //                     <td style="text-align:right;">' . number_format(0) . '</td>
                            //                     <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                            //                 </tr>';
                            //     $begin += $return->qty;
                            //     $nod++;
                            // }
                            // //OS RM
                            // foreach ($os_rms as $os_rm) {
                            //     $user = $this->crud->read("users", [], ["username" => $os_rm->created_by]);
                            //     $balance = ($begin + $os_rm->qty);
                            //     $html .= '  <tr>
                            //                     <td></td>
                            //                     <td style="text-align:center">' . $nod . '</td>
                            //                     <td>OS RM</td>
                            //                     <td>' . $user->name . '</td>
                            //                     <td>' . date("Y-m-d", strtotime($os_rm->created_date)) . '</td>
                            //                     <td>-</td>
                            //                     <td>-</td>
                            //                     <td>-</td>
                            //                     <td>-</td>
                            //                     <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                            //                     <td style="text-align:right;">' . number_format($os_rm->qty, 2)  . '</td>
                            //                     <td style="text-align:right;">' . number_format(0) . '</td>
                            //                     <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                            //                 </tr>';
                            //     $begin += $os_rm->qty;
                            //     $nod++;
                            // }
                            // //SCAN BPM
                            // foreach ($bpm_scans as $bpm_scan) {
                            //     $user = $this->crud->read("users", [], ["username" => $bpm_scan->created_by]);
                            //     $balance = ($begin + $bpm_scan->qty);
                            //     $html .= '  <tr>
                            //                     <td></td>
                            //                     <td style="text-align:center">' . $nod . '</td>
                            //                     <td>BPM</td>
                            //                     <td>' . $user->name . '</td>
                            //                     <td>' . date("Y-m-d", strtotime($bpm_scan->created_date)) . '</td>
                            //                     <td>-</td>
                            //                     <td>' . $bpm_scan->label . '</td>
                            //                     <td>' . $bpm_scan->request_id . '</td>
                            //                     <td>' . $bpm_scan->request_date . '</td>
                            //                     <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                            //                     <td style="text-align:right;">' . number_format($bpm_scan->qty, 2)  . '</td>
                            //                     <td style="text-align:right;">' . number_format(0) . '</td>
                            //                     <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                            //                 </tr>';
                            //     $begin += $bpm_scan->qty;
                            //     $nod++;
                            // }
                            // //TRANSACTION
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
                            //                     <td>-</td>
                            //                     <td>' . $transaction->request_no . '</td>
                            //                     <td>-</td>
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
                         // Dokumentasi berakhir disini ------------------------- //
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
                        WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$filter_from' and '$filter_to'
                        GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id
                        ORDER BY a.receipt_date");
            
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
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'ADJ IN STO' and a.request_date between '$filter_from' and '$filter_to'
                        ORDER BY a.request_date");
            
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
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'BPM' and a.request_date between '$filter_from' and '$filter_to'
                        ORDER BY a.request_date");
            
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

                        if(!$transactions){
                            $transactions = $this->crud->query("SELECT * 
                            FROM scan_item_bpm WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(request_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' ORDER BY request_date");

                            foreach ($transactions as $transaction) {
                                $user = $this->crud->read("users", [], ["username" => $transaction->created_by]);
                                $balance = ($begin + $transaction->qty);
                                $html .= '  <tr>
                                                <td></td>
                                                <td style="text-align:center">' . $nod . '</td>
                                                <td>BPM</td>
                                                <td>' . $user->name . '</td>
                                                <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                                <td>-</td>
                                                <td>' . $transaction->label . '</td>
                                                <td>' . $transaction->request_id . '</td>
                                                <td>' . $transaction->request_date . '</td>
                                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                                <td style="text-align:right;">' . number_format($transaction->qty, 2)  . '</td>
                                                <td style="text-align:right;">' . number_format(0) . '</td>
                                                <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                            </tr>';
                                $begin += $transaction->qty;
                                $nod++;
                            }
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
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'ADJ OUT STO' and a.request_date between '$filter_from' and '$filter_to'
                        ORDER BY a.request_date");
            
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
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'BPB' and a.request_date between '$filter_from' and '$filter_to'
                        ORDER BY a.request_date");
            
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
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'KANBAN WO' and a.request_date between '$filter_from' and '$filter_to'
                        ORDER BY a.request_date");
            
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
                        $issueds = $this->crud->query("SELECT * FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' ORDER BY created_date");
            
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
                    }
                //}
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
            COALESCE(i.qty,0) + COALESCE(o.qty_bpm_scan,0) as bpm_qty, 
            COALESCE(k.qty,0) as adj_in_qty, 

            COALESCE(f.qty,0) as qty_issued,
            COALESCE(j.qty,0) as qty_kanban,
            COALESCE(m.qty,0) as adj_out_qty,
            COALESCE(n.qty,0) as bpb_qty, 

            (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0) + COALESCE(k.qty, 0) + COALESCE(o.qty_bpm_scan, 0)) as qty_in,
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

            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_bpm_scan
                FROM scan_item_bpm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date between '$filter_from' and '$filter_to'
                GROUP BY a.item_rm_id) o ON a.id = o.item_rm_id

            -- IN TRANSACTION di mulai dari sini----------------------- 

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type,SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to' AND a.transaction_type = 'BPM'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) i ON a.id = i.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to' AND a.transaction_type = 'ADJ IN STO'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) k ON a.id = k.item_rm_id

            -- OUT TRANSACTION di mulai dari sini-----------------------

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to' and a.transaction_type = 'KANBAN WO'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) j ON a.id = j.item_rm_id
        
            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to' and a.transaction_type = 'ADJ OUT STO'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) m ON a.id = m.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to' and a.transaction_type = 'BPB'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) n ON a.id = n.item_rm_id
        
        WHERE c.id like '%$filter_item_category%' and b.number like '%$filter_item_family%' and a.id like '%$filter_items%' and a.division like '%$filter_division%' 
        GROUP BY a.id
        ORDER BY c.name DESC, b.name DESC, a.number");

        // $query_main = "SELECT 
        //     a.id,
        //     a.number, 
        //     a.name, 
        //     a.division, 
        //     b.name as prodfam, 
        //     a.uom,
        //     c.name as category_name,
        //     COALESCE(j.begin_stock) AS begin_stock,
        //     (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0)) AS qty_in,
        //     (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) AS qty_out
        // FROM item_rm a
        // JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
        // JOIN item_categories c ON a.item_category_id = c.id
        // LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
        // LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        // LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        // LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        // LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
        // LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i ON a.id = i.item_rm_id

        // LEFT JOIN (SELECT a.id, a.number, ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0)) - (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
        //             FROM item_rm a
        //             LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_from'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
        //             LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
        //             LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
        //             LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$filter_from' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
        //             LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        //             LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
        //         ) j ON a.id = j.id

        // WHERE c.id LIKE '%$filter_item_category%'
        // AND b.number LIKE '%$filter_item_family%'
        // AND a.id LIKE '%$filter_items%'
        // AND a.division LIKE '%$filter_division%'
        // GROUP BY a.id
        // ORDER BY c.name DESC, b.name DESC, a.number";

        // Eksekusi query
        // $records = $this->crud->query($query_main);


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
                    <th width="80">BPB</th>
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
                    a.id,(COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0) + COALESCE(o.qty_bpm_scan, 0)) - (COALESCE(f.qty,0) + COALESCE(j.qty, 0)) as begin_stock   
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

                LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_bpm_scan
                    FROM scan_item_bpm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.request_date < '$filter_from'
                    GROUP BY a.item_rm_id) o ON a.id = o.item_rm_id
                
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

            $totalQtyIn += ($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty);
            $totalQtyOut += ($record->qty_issued + $record->qty_kanban + $record->adj_out_qty + $record->bpb_qty);
            $totalQtySelisihIn += (($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty) - $record->qty_in);
            $totalQtySelisihOut += (($record->qty_issued + $record->qty_kanban + $record->adj_out_qty + $record->bpb_qty) - $record->qty_out);

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
                            <td style="text-align:right;">' . $record->bpb_qty . '</td>
                            <td style="text-align:right;">' . $record->adj_out_qty . '</td>

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
             <td style="text-align:right;">' . number_format($totalBpbQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalAdjOutQty, 2) . '</td>
           
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
            (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0) + COALESCE(k.qty_scan_bpm, 0)) as qty_in,
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

            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_scan_bpm
                FROM scan_item_bpm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date between '$filter_from' and '$filter_to'
                GROUP BY a.item_rm_id) k ON a.id = k.item_rm_id

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
                    a.id,(COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0) + COALESCE(k.qty_scan_bpm, 0)) - (COALESCE(f.qty,0) + COALESCE(j.qty, 0)) as begin_stock   
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
                
                LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_scan_bpm
                    FROM scan_item_bpm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.request_date < '$filter_from'
                    GROUP BY a.item_rm_id) k ON a.id = k.item_rm_id

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

                        //SCAN BPM
                        $bpm_scans = $this->crud->query("SELECT 
                        created_by, 
                        qty, 
                        created_date, 
                        label, 
                        request_date, 
                        request_id 
                        FROM scan_item_bpm 
                        WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(request_date, '%Y-%m-%d') between '$working_date' and '$working_date'");

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
                            $no++;
                        }

                        //SCAN BPM
                        foreach ($bpm_scans as $bpm_scan) {
                            $user = $this->crud->read("users", [], ["username" => $bpm_scan->created_by]);
                            $balance = ($begin + $bpm_scan->qty);
                            $html .= '  <tr>
                                             <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>BPM</td>
                                            <td>' . $user->name . '</td>
                                            <td>' . date("Y-m-d", strtotime($bpm_scan->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . date("Y-m-d", strtotime($bpm_scan->request_date)) . '</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($bpm_scan->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin += $bpm_scan->qty;
                            $no++;
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
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
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
