<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions_fg extends CI_Controller
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
            $this->load->view('warehouse/report_history_transactions_fg');
        } else {
            redirect('error_access');
        }
    }

    public function lsb_lock()
    {
        $filter_from = $this->input->post('filter_from');
        $filter_to   = $this->input->post('filter_to');
        $period      = date('Ym', strtotime($filter_from));

        $this->db->where("('$filter_from' BETWEEN lock_from AND lock_to OR 
                        '$filter_to' BETWEEN lock_from AND lock_to OR 
                        lock_from BETWEEN '$filter_from' AND '$filter_to')");
        $cek = $this->db->get('lsb_lock')->num_rows();

        if ($cek > 0) {
            echo json_encode([
                "status"  => false,
                "message" => "Period has been saved."
            ]);
            return;
        }

        // Simpan jika belum ada
        $data = array(
            "period"     => $period,
            "lock_from"  => $filter_from,
            "lock_to"    => $filter_to
        );

        $send = $this->crud->create('lsb_lock', $data);

        if ($send) {
            echo json_encode([
                "status"  => true,
                "message" => "Period saved successfully!: $period"
            ]);
        } else {
            echo json_encode([
                "status"  => false,
                "message" => "Gagal menyimpan data. Silakan coba lagi."
            ]);
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_fg_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_trans_type = $this->input->get("filter_trans_type");
        $filter_division = $this->input->get("filter_division");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $filter_from_minus1 = date('Y-m-01', strtotime('-1 month', strtotime($filter_from)));
        $filter_to_minus1   = date('Y-m-t',  strtotime('-1 month', strtotime($filter_from)));
        $filter_from_minus2 = date('Y-m-01', strtotime('-2 month', strtotime($filter_from)));
        $filter_to_minus2   = date('Y-m-t',  strtotime('-2 month', strtotime($filter_from)));
        $filter_from_minus3 = date('Y-m-01', strtotime('-3 month', strtotime($filter_from)));
        $filter_to_minus3   = date('Y-m-t',  strtotime('-3 month', strtotime($filter_from)));

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

        $query_delivery_notes_sales_minus1 = "SELECT item_fg_id, SUM(qty) as qty_notes_sales
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from_minus1' AND '$filter_to_minus1' AND trans_type = 'SALES'
        GROUP BY item_fg_id";

        $query_delivery_notes_sales_minus2 = "SELECT item_fg_id, SUM(qty) as qty_notes_sales
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from_minus2' AND '$filter_to_minus2' AND trans_type = 'SALES'
        GROUP BY item_fg_id";

        $query_delivery_notes_sales_minus3 = "SELECT item_fg_id, SUM(qty) as qty_notes_sales
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from_minus3' AND '$filter_to_minus3' AND trans_type = 'SALES'
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
            COALESCE(x.begin_stock,0) AS begin_stock,
            COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) AS qty_in,
            
            COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0) AS qty_out,
            
            COALESCE(dns1.qty_notes_sales, 0) as qty_out_sales_minus1,
            COALESCE(dns2.qty_notes_sales, 0) as qty_out_sales_minus2,
            COALESCE(dns3.qty_notes_sales, 0) as qty_out_sales_minus3,
            
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) - 
            (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS end_stock
        FROM item_fg a
        LEFT JOIN ($query_qty_in_checksheet) qc ON a.id = qc.item_fg_id
        LEFT JOIN ($query_qty_in_no_checksheet) qnc ON a.id = qnc.item_fg_id
        LEFT JOIN ($query_transaction_fg_in) qi ON a.id = qi.item_fg_id
        LEFT JOIN ($query_qty_out) qo ON a.id = qo.item_fg_id
        LEFT JOIN ($query_delivery_notes) qg ON a.id = qg.item_fg_id
        LEFT JOIN ($query_delivery_notes_sales_minus1) dns1 ON a.id = dns1.item_fg_id
        LEFT JOIN ($query_delivery_notes_sales_minus2) dns2 ON a.id = dns2.item_fg_id
        LEFT JOIN ($query_delivery_notes_sales_minus3) dns3 ON a.id = dns3.item_fg_id
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
        WHERE a.id LIKE '%$filter_items%' AND a.division_id LIKE '%$filter_division%'
        ORDER BY a.number
        ";

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
            <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION (FG)</h3>
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
                    <th width="100">ITO<br>(MONTH)</th>
                </tr>';
        $no = 1;
        foreach ($records as $record) {
            $item_fg_id = $record->id;

            $total_sales_minus = $record->qty_out_sales_minus1 + $record->qty_out_sales_minus2 + $record->qty_out_sales_minus3;
            $avg_sales_minus = ($total_sales_minus > 0) ? number_format($total_sales_minus / 3, 2) : '0';

            $stock_coverage = ($total_sales_minus > 0)
                ? number_format(((@$record->begin_stock + $record->qty_in) - $record->qty_out) / ($total_sales_minus / 3), 2)
                : '0'; // atau bisa diganti jadi '0.00' atau '-'

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3" style="mso-number-format:\@;">' . $record->number . '</td>
                            <td style="mso-number-format:\@;">' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>FINISH GOOD</td>

                            <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format((@$record->begin_stock + $record->qty_in - $record->qty_out), 2) . '</td>
                            <td style="text-align:right;">' . $stock_coverage . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="12" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
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
                                <th colspan ="2">Balance</th>
                            </tr>';
                $nod = 1;
                $begin = @$record->begin_stock;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;

                if ($filter_trans_type == '') {

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
                                    <td>' . $data['label'] . '</td>
                                    <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($data['qty_in'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($data['qty_out'], 2) . '</td>
                                    <td colspan = "2" style="text-align:right;">' . number_format($balance, 2) . '</td>
                                </tr>';

                    $begin = $balance;
                    $nod++;
                    }

                }

                if ($filter_trans_type == 'RECEIPT FG') {

                    //RECEIPT
                    $receipts = $this->crud->query("SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                        -- FROM production_schedules d
                        FROM scan_item_receipts_fg f
                        JOIN checksheets e ON e.number = f.checksheet_number
                        LEFT JOIN users c ON f.created_by = c.username
                        WHERE e.item_fg_id = '$item_fg_id' 
                        and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                    if (empty($receipts)) {
                        $receipts = $this->crud->query("SELECT f.*, u.name as username ,f.packing_date as trans_date, 'NEW BARCODE FG' AS receipt_type
                            FROM new_barcode_fg a
                            LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                            LEFT JOIN users u ON f.created_by = u.username
                            WHERE a.item_fg_id = '$item_fg_id' 
                            AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");
                        
                        if (empty($receipts)) {
                            $receipts = $this->crud->query("SELECT a.*, u.name as username, 'RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                                FROM wip_receipts a
                                LEFT JOIN users u ON a.created_by = u.username
                                WHERE a.item_fg_id = '$item_fg_id' AND a.division = 'MTS'
                                AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");
                        } 
                    }


                    //RECEIPT
                    foreach ($receipts as $receipt) {
                        $balance = ($begin + ($receipt->qty - $end_qty));
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>' . $receipt->receipt_type . '</td>
                                        <td>' . $receipt->username . '</td>
                                        <td>' . $receipt->trans_date . '</td>
                                        <td>' . $receipt->wo_no . '</td>
                                        <td>' . $receipt->checksheet_label . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($receipt->qty, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0)  . '</td>
                                        <td colspan = "2" style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin += $receipt->qty;
                        $nod++;
                    }
                }

                if ($filter_trans_type == 'NEW BARCODE') {

                    //RECEIPT
                
                    $receipts = $this->crud->query("SELECT f.*, 
                        u.name as username ,
                        f.packing_date as trans_date,
                        'NEW BARCODE FG' as receipt_type
                        FROM new_barcode_fg a
                        LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                        LEFT JOIN users u ON f.created_by = u.username
                        WHERE a.item_fg_id = '$item_fg_id' 
                        AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");
                        

                    //RECEIPT
                    foreach ($receipts as $receipt) {
                        $balance = ($begin + ($receipt->qty - $end_qty));
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>' . $receipt->receipt_type . '</td>
                                        <td>' . $receipt->username . '</td>
                                        <td>' . $receipt->trans_date . '</td>
                                        <td>' . $receipt->wo_no . '</td>
                                        <td>' . $receipt->checksheet_label . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($receipt->qty, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0)  . '</td>
                                        <td colspan = "2" style="text-align:right;">' . number_format($balance, 2)  . '</td>
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
                        WHERE a.item_fg_id = '$item_fg_id' and a.delivery_note_date between '$filter_from' and '$filter_to'");

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
                                        <td colspan = "2" style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin -= $return->qty;
                        $nod++;
                    }
                }

                if ($filter_trans_type == 'REPAIR OF GOODS') {

                    //DELIVERY NOTE
                    $repairs = $this->crud->query("SELECT f.wo_no, 
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

                    foreach ($repairs as $repair) {
                        $balance = ($begin - $repair->qty);
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>REPAIR OF GOODS</td>
                                        <td>' . $repair->username . '</td>
                                        <td>' . $repair->trans_date . '</td>
                                        <td>' . $repair->wo_no  . '</td>
                                        <td>' . $repair->document_no . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0) . '</td>
                                        <td style="text-align:right;">' . number_format($repair->qty, 2)  . '</td>
                                        <td colspan = "2" style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin -= $repair->qty;
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
                    WHERE a.item_fg_id = '$item_fg_id' and a.transaction_type = 'ADJ IN STO' and a.request_date between '$filter_from' and '$filter_to'");
        
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
                                        <td colspan = "2" style="text-align:right;">' . number_format($balance, 2) . '</td>
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
                    WHERE a.item_fg_id = '$item_fg_id' and a.transaction_type = 'ADJ OUT STO' and a.request_date between '$filter_from' and '$filter_to'");
        
                    foreach ($transactions as $transaction) {
                        $balance = ($transaction->transaction_kind == 'OUT') 
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
                                        <td colspan = "2" style="text-align:right;">' . number_format($balance, 2) . '</td>
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
                    FROM transaction_fg a
                    JOIN users b ON a.created_by = b.username
                    WHERE a.item_fg_id = '$item_fg_id' and a.transaction_type = 'BPB' and a.request_date between '$filter_from' and '$filter_to'");
        
                    foreach ($transactions as $transaction) {
                        $balance = ($transaction->transaction_kind == 'OUT') 
                                    ? ($begin + $transaction->qty) 
                                    : ($begin - $transaction->qty);
                    
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>BPB</td>
                                        <td>' . $transaction->username . '</td>
                                        <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                        <td>-</td>
                                        <td>' . $transaction->request_no . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                        <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                        <td colspan = "2" style="text-align:right;">' . number_format($balance, 2) . '</td>
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
            $no++;
        }
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
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_trans_type = $this->input->get("filter_trans_type");
        $filter_division = $this->input->get("filter_division");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $filter_from_minus1 = date('Y-m-01', strtotime('-1 month', strtotime($filter_from)));
        $filter_to_minus1   = date('Y-m-t',  strtotime('-1 month', strtotime($filter_from)));
        $filter_from_minus2 = date('Y-m-01', strtotime('-2 month', strtotime($filter_from)));
        $filter_to_minus2   = date('Y-m-t',  strtotime('-2 month', strtotime($filter_from)));
        $filter_from_minus3 = date('Y-m-01', strtotime('-3 month', strtotime($filter_from)));
        $filter_to_minus3   = date('Y-m-t',  strtotime('-3 month', strtotime($filter_from)));

        //------------------------------------ Mengambil Filter dari Input GET berakhir disini----------------------------------//

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//


        // Step 1: Hitung qty_in dari checksheet
        $query_qty_in_checksheet = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_checksheet
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY e.item_fg_id";

        $query_qty_in_checksheet_non_subcont = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_non_subcont
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND e.status_subcont = 'NO'
        GROUP BY e.item_fg_id";

        $query_qty_in_checksheet_subcont_jasa = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_subcont_jasa
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND e.subcont_type = 'Jasa'
        GROUP BY e.item_fg_id";

        $query_qty_in_checksheet_subcont_fg = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_subcont_fg
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND e.subcont_type = 'Finished Good'
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

        $query_transaction_fg_in_adj = "SELECT a.item_fg_id, SUM(a.qty) as initial_in_adj
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'ADJ IN STO'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        $query_transaction_fg_in_rfg = "SELECT a.item_fg_id, SUM(a.qty) as initial_in_rfg
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        // Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        $query_qty_out_bpb = "SELECT a.item_fg_id, SUM(a.qty) as qty_out_bpb
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT' AND a.transaction_type = 'BPB'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        $query_qty_out_adj = "SELECT a.item_fg_id, SUM(a.qty) as qty_out_adj
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT' AND a.transaction_type = 'ADJ OUT STO'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        // Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes = "SELECT item_fg_id, SUM(qty) as initial_out_g
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY item_fg_id";

        $query_delivery_notes_sales = "SELECT item_fg_id, SUM(qty) as qty_notes_sales
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to' AND trans_type = 'SALES'
        GROUP BY item_fg_id";

        $query_delivery_notes_sales_minus1 = "SELECT item_fg_id, SUM(qty) as qty_notes_sales
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from_minus1' AND '$filter_to_minus1' AND trans_type = 'SALES'
        GROUP BY item_fg_id";

        $query_delivery_notes_sales_minus2 = "SELECT item_fg_id, SUM(qty) as qty_notes_sales
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from_minus2' AND '$filter_to_minus2' AND trans_type = 'SALES'
        GROUP BY item_fg_id";

        $query_delivery_notes_sales_minus3 = "SELECT item_fg_id, SUM(qty) as qty_notes_sales
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from_minus3' AND '$filter_to_minus3' AND trans_type = 'SALES'
        GROUP BY item_fg_id";

        $query_delivery_notes_return = "SELECT item_fg_id, SUM(qty) as qty_notes_return
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to' AND trans_type = 'RETURN'
        GROUP BY item_fg_id";

        $query_delivery_notes_sample = "SELECT item_fg_id, SUM(qty) as qty_notes_sample
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to' AND trans_type = 'SAMPLE'
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
            b.number as division,
            '0' as subcont_qty,
            a.type,
            COALESCE(x.begin_stock,0) AS begin_stock,

            COALESCE(qins.qty_in_non_subcont, 0) + COALESCE(qir.initial_in_rfg, 0) + COALESCE(qw.qty_in_wip_receipt, 0) as qty_rfg,
            COALESCE(qi.initial_in, 0) as adj_in_qty,
            COALESCE(qia.initial_in_adj, 0) as qty_in_adj,
            COALESCE(qir.initial_in_rfg, 0) as qty_in_rfg,
            COALESCE(qnc.qty_in_no_checksheet, 0) as qty_in_new_barcode,
            COALESCE(qins.qty_in_non_subcont, 0) as qty_in_non_subcont,
            COALESCE(qisj.qty_in_subcont_jasa, 0) as qty_in_subcont_jasa,
            COALESCE(qisfg.qty_in_subcont_fg, 0) as qty_in_subcont_fg,

            COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) AS qty_in,
            
            COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0) AS qty_out,

            COALESCE(dns.qty_notes_sales, 0) as qty_out_sales,
            COALESCE(dns1.qty_notes_sales, 0) as qty_out_sales_minus1,
            COALESCE(dns2.qty_notes_sales, 0) as qty_out_sales_minus2,
            COALESCE(dns3.qty_notes_sales, 0) as qty_out_sales_minus3,
            COALESCE(dnr.qty_notes_return, 0) as qty_out_return,
            COALESCE(dnss.qty_notes_sample, 0) as qty_out_sample,

            COALESCE(qh.initial_out_h, 0) as qty_out_repair,

            COALESCE(qo.qty_out, 0) as adj_out_qty,
            COALESCE(qob.qty_out_bpb, 0) + COALESCE(qh.initial_out_h, 0) as qty_out_bpb,
            COALESCE(qoa.qty_out_adj, 0) as qty_out_adj,
            
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) - 
            (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS end_stock
        FROM item_fg a
        LEFT JOIN divisions b ON a.division_id = b.id
        LEFT JOIN ($query_qty_in_checksheet) qc ON a.id = qc.item_fg_id
        LEFT JOIN ($query_qty_in_no_checksheet) qnc ON a.id = qnc.item_fg_id
        LEFT JOIN ($query_transaction_fg_in) qi ON a.id = qi.item_fg_id
        LEFT JOIN ($query_transaction_fg_in_adj) qia ON a.id = qia.item_fg_id
        LEFT JOIN ($query_transaction_fg_in_rfg) qir ON a.id = qir.item_fg_id
        LEFT JOIN ($query_qty_out) qo ON a.id = qo.item_fg_id
        LEFT JOIN ($query_qty_out_bpb) qob ON a.id = qob.item_fg_id
        LEFT JOIN ($query_qty_out_adj) qoa ON a.id = qoa.item_fg_id
        LEFT JOIN ($query_delivery_notes) qg ON a.id = qg.item_fg_id
        LEFT JOIN ($query_delivery_notes_sales) dns ON a.id = dns.item_fg_id
        LEFT JOIN ($query_delivery_notes_sales_minus1) dns1 ON a.id = dns1.item_fg_id
        LEFT JOIN ($query_delivery_notes_sales_minus2) dns2 ON a.id = dns2.item_fg_id
        LEFT JOIN ($query_delivery_notes_sales_minus3) dns3 ON a.id = dns3.item_fg_id
        LEFT JOIN ($query_delivery_notes_return) dnr ON a.id = dnr.item_fg_id
        LEFT JOIN ($query_delivery_notes_sample) dnss ON a.id = dnss.item_fg_id
        LEFT JOIN ($query_scan_repair_of_goods) qh ON a.id = qh.item_fg_id
        LEFT JOIN ($query_qty_in_wip_receipt) qw ON a.id = qw.item_fg_id
        LEFT JOIN ($query_qty_in_checksheet_non_subcont) qins ON a.id = qins.item_fg_id
        LEFT JOIN ($query_qty_in_checksheet_subcont_jasa) qisj ON a.id = qisj.item_fg_id
        LEFT JOIN ($query_qty_in_checksheet_subcont_fg) qisfg ON a.id = qisfg.item_fg_id

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
        WHERE a.id LIKE '%$filter_items%' AND a.division_id LIKE '%$filter_division%'
        ORDER BY a.number
        ";

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
                <h3 style="margin:0;">LSB (FG)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th rowspan="3" width="20">No</th>
                    <th rowspan="3">Product No</th>
                    <th rowspan="3">Product Name</th>
                    <th rowspan="3">UOM</th>
                    <th rowspan="3">Type</th>
                    <th rowspan="3" width="100">Begin<br>Stock</th>
                    
                    <th colspan="5">IN</th>
                    <th rowspan="3" width="100">Total<br>In</th>
                    <th colspan="5">OUT</th>
                    <th rowspan="3" width="100">Total<br>Out</th>

                    <th rowspan="3" width="100">Ending<br>Stock</th>
                    <th rowspan="3" width="100">ITO<br>(MONTH)</th>
                </tr>
                <tr>
                    <th rowspan="2" width="80">IN RFG</th>
                    <th rowspan="2" width="80">NEW BARCODE</th>
                    <th colspan="2" width="80">SUBCONT</th>
                    <th rowspan="2" width="80">ADJ STO</th>

                    <th rowspan="2" width="80">OUT SJ</th>
                    <th rowspan="2" width="80">OUT BPB</th>
                    <th rowspan="2" width="80">OUT RETUR<br>TKG</th>
                    <th rowspan="2" width="80">OUT SAMPLE</th>
                    <th rowspan="2" width="80">OUT ADJ<br>(STO)</th>

                </tr>
                <tr>
                    <th width="80">FG</th>
                    <th width="80">JASA</th>
                </tr>';

                
        $no = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalEndingStock = 0;

        $totalRfgQty = 0;
        $totalNBQty = 0;
        $totalSubcontFGQty = 0;
        $totalSubcontJSQty = 0;
        $totalAdjInQty = 0;

        $totalOutSales = 0;
        $totalOutSalesMinus1 = 0;
        $totalOutSalesMinus2 = 0;
        $totalOutSalesMinus3 = 0;
        $totalOutReturn = 0;
        $totalOutSample = 0;
        $totalOutBpb = 0;
        $totalOutAdj = 0;

        $totalQtyIn = 0;
        $totalQtyOut = 0;
        $totalQtySelisihIn = 0;
        $totalQtySelisihOut = 0;

        $totalAverageOut = 0;
        $totalITOMonth = 0;

        foreach ($records as $record) {

            $item_fg_id = $record->id;
            //Item Receipts
            
            $totalBeginStock += @$record->begin_stock;
            $totalIn += $record->qty_in;
            $totalOut += $record->qty_out;
            $totalEndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out;
            
            $totalRfgQty += $record->qty_rfg;
            $totalNBQty += $record->qty_in_new_barcode;
            $totalSubcontFGQty += $record->qty_in_subcont_fg;
            $totalSubcontJSQty += $record->qty_in_subcont_jasa;
            $totalAdjInQty += $record->qty_in_adj;

            $totalOutSales += $record->qty_out_sales;
            $totalOutSalesMinus1 += $record->qty_out_sales_minus1;
            $totalOutSalesMinus2 += $record->qty_out_sales_minus2;
            $totalOutSalesMinus3 += $record->qty_out_sales_minus3;
            $totalOutReturn += $record->qty_out_return;
            $totalOutSample += $record->qty_out_sample;
            $totalOutBpb += $record->qty_out_bpb;
            $totalOutAdj += $record->qty_out_adj;

            $total_sales_minus = $record->qty_out_sales_minus1 + $record->qty_out_sales_minus2 + $record->qty_out_sales_minus3;

            // Perhitungan numerik asli
            $numeric_avg_sales_minus = ($total_sales_minus > 0) ? $total_sales_minus / 3 : 0;
            $numeric_stock_coverage = ($total_sales_minus > 0)
                ? ((@$record->begin_stock + $record->qty_in) - $record->qty_out) / $numeric_avg_sales_minus
                : 0;

            // Format hanya jika perlu ditampilkan
            $avg_sales_minus = number_format($numeric_avg_sales_minus, 2);
            $stock_coverage = number_format($numeric_stock_coverage, 2);

            // Jumlahkan dengan nilai numerik murni
            $totalAverageOut += $numeric_avg_sales_minus;
            $totalITOMonth += $numeric_stock_coverage;

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $record->number . '</td>
                            <td style="mso-number-format:\@;">' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->type . '</td>
                            
                            <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                            
                            <td style="text-align:right;">' . $record->qty_rfg . '</td>
                            <td style="text-align:right;">' . $record->qty_in_new_barcode . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in_subcont_fg, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in_subcont_jasa, 2) . '</td>
                            <td style="text-align:right;">' . $record->qty_in_adj . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_rfg + $record->qty_in_new_barcode + $record->qty_in_subcont_fg + $record->qty_in_subcont_jasa + $record->qty_in_adj,2) . '</td>

                            <td style="text-align:right;">' . $record->qty_out_sales . '</td>
                            <td style="text-align:right;">' . $record->qty_out_bpb . '</td>
                            <td style="text-align:right;">' . $record->qty_out_return . '</td>
                            <td style="text-align:right;">' . $record->qty_out_sample . '</td>
                            <td style="text-align:right;">' . $record->qty_out_adj . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_out_sales + $record->qty_out_bpb + $record->qty_out_return + $record->qty_out_sample + $record->qty_out_adj,2) . '</td>
                                                        
                            <td style="text-align:right;">' . number_format((@$record->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>

                            <td style="text-align:right;">' . $stock_coverage . '</td>
                        </tr>';
            $no++;
        }

        $html .= '<tr>
            <td colspan="5" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalRfgQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalNBQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalSubcontFGQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalSubcontJSQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalAdjInQty, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalOutSales, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalOutBpb, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalOutReturn, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalOutSample, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalOutAdj, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>
          
            <td style="text-align:right;">' . number_format($totalITOMonth, 2) . '</td>
        </tr>';
      
        $html .= '</table></body></html>';
        echo $html;
    }

    public function detail_transaction($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=detail_transaction_fg_$format.xls");
        }
        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_trans_type = $this->input->get("filter_trans_type");
        $filter_division = $this->input->get("filter_division");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);
        //------------------------------------ Mengambil Filter dari Input GET berakhir disini----------------------------------//

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//


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
            COALESCE(x.begin_stock,0) AS begin_stock,
            COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) AS qty_in,
            
            COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0) AS qty_out,
            
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) - 
            (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS end_stock
        FROM item_fg a
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
        WHERE a.id LIKE '%$filter_items%' AND a.division_id LIKE '%$filter_division%'
        ORDER BY a.number
        ";

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
                <h3 style="margin:0;">DETAIL TRANSACTION (FG)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th width="20">No</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Uom</th>
                    <th>Trans Type</th>
                    <th>DN Type</th>
                    <th>Created By</th>
                    <th>Doc. No</th>
                    <th>Transaction. Date</th>
                    <th>Begin</th>
                    <th>In</th>
                    <th>Out</th>
                    <th>Balance</th>
                </tr>';


        $no = 1;
        $nod = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalEndingStock = 0;

        foreach ($records as $record) {
            $item_fg_id = $record->id;

            $totalBeginStock += @$record->begin_stock;
            $totalIn += $record->qty_in;
            $totalOut += $record->qty_out;
            $totalEndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out;

            if ($filter_display == "DETAIL") {
                $begin = @$record->begin_stock;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;
            
                if ($filter_trans_type == '') {

                    // Ambil seluruh data untuk rentang tanggal dalam satu query per jenis transaksi
                    // $receipts = $this->crud->query("SELECT 
                    // a.document_no as wo_no, 
                    // '-' as checksheet_label, 
                    // SUM(a.qty) as qty, 
                    // c.name AS username, 
                    // e.packing_date AS trans_date, 
                    // 'WIP RECEIPT' AS receipt_type
                    // FROM wip_receipts a
                    // LEFT JOIN checksheets e ON a.checksheet_number = e.number
                    // LEFT JOIN users c ON a.created_by = c.username
                    // WHERE e.item_fg_id = '$item_fg_id'
                    // AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND a.status != 0
                    // GROUP BY a.document_no, a.item_fg_id");
                    
                    // if (empty($receipts)) {
                    //     $receipts = $this->crud->query("SELECT 
                    //     COALESCE(f.checksheet_label,'-') as wo_no, 
                    //     f.checksheet_label, 
                    //     f.qty, 
                    //     u.name AS username, 
                    //     f.packing_date AS trans_date, 
                    //     'NEW BARCODE FG' AS receipt_type

                    //     FROM new_barcode_fg a
                    //     LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                    //     LEFT JOIN users u ON f.created_by = u.username
                    //     WHERE a.item_fg_id = '$item_fg_id'
                    //     AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");

                    //     if (empty($receipts)) {
                    //         $receipts = $this->crud->query("SELECT a.*, 
                    //             u.name as username, 
                    //             'RECEIPT FG' AS receipt_type, 
                    //             a.document_no as wo_no, 
                    //             '-' as checksheet_label
                    //             FROM wip_receipts a
                    //             LEFT JOIN users u ON a.created_by = u.username
                    //             WHERE a.item_fg_id = '$item_fg_id' AND a.division = 'MTS'
                    //             AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");
                    //     } 
                    // }

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

                    $delivery_notes = $this->crud->query("SELECT a.*, d.name AS username, a.trans_type as dn_type
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
                    // foreach ($receipts as $receipt) {
                    // $all_data[] = [
                    //     'type' => $receipt->receipt_type,
                    //     'username' => $receipt->username,
                    //     'date' => $receipt->trans_date,
                    //     'wo_no' => $receipt->wo_no,
                    //     'dn_type' => '-',
                    //     'label' => $receipt->checksheet_label,
                    //     'qty_in' => $receipt->qty,
                    //     'qty_out' => 0,
                    // ];
                    // }

                    foreach ($receipts as $receipt) {
                    $all_data[] = [
                        'type' => $receipt->receipt_type,
                        'username' => $receipt->username,
                        'date' => $receipt->trans_date,
                        'wo_no' => $receipt->wo_no,
                        'dn_type' => '-',
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
                        'dn_type' => '-',
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
                        'dn_type' => '-',
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
                        'dn_type' => $delivery_note->dn_type,
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
                        'dn_type' => '-',
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
                        'dn_type' => '-',
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
                    
                    $balance = $begin;
                    foreach ($all_data as $data) {
                    $balance += $data['qty_in'] - $data['qty_out'];
                    $html .= '  <tr>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td style="mso-number-format:\@;">' . $record->number . '</td>
                                    <td style="mso-number-format:\@;">' . $record->name . '</td>
                                    <td>' . $record->uom . '</td>
                                    <td>' . $data['type'] . '</td>
                                    <td>' . $data['dn_type'] . '</td> 
                                    <td>' . $data['username'] . '</td>
                                    <td>' . $data['wo_no'] . '</td>
                                    <td>' . $data['date'] . '</td>
                                    <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($data['qty_in'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($data['qty_out'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                </tr>';

                    $begin = $balance;
                    $nod++;
                    }

                }

                if ($filter_trans_type == 'RECEIPT FG') {

                    //RECEIPT
                    // $receipts = $this->crud->query("SELECT 
                    //     a.document_no as wo_no, 
                    //     '-' as checksheet_label, 
                    //     SUM(a.qty) as qty, 
                    //     c.name AS username, 
                    //     e.packing_date AS trans_date, 
                    //     'WIP RECEIPT' AS receipt_type
                      
                    //     FROM wip_receipts a
                    //     LEFT JOIN checksheets e ON a.checksheet_number = e.number
                    //     LEFT JOIN users c ON a.created_by = c.username
                    //     WHERE e.item_fg_id = '$item_fg_id'
                    //     AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND a.status != 0
                    //     GROUP BY a.document_no, a.item_fg_id");

                    // if (empty($receipts)) {
                    //     $receipts = $this->crud->query("SELECT f.*, u.name as username ,f.packing_date as trans_date
                    //         FROM new_barcode_fg a
                    //         LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                    //         LEFT JOIN users u ON f.created_by = u.username
                    //         WHERE a.item_fg_id = '$item_fg_id' 
                    //         AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");
                        
                    //     $receipt_type = 'NEW BARCODE FG';
                    // } else {
                    //     $receipt_type = 'WIP RECEIPT';
                    // }

                    $receipts = $this->crud->query("SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                        FROM scan_item_receipts_fg f
                        JOIN checksheets e ON e.number = f.checksheet_number
                        LEFT JOIN users c ON f.created_by = c.username
                        WHERE e.item_fg_id = '$item_fg_id' 
                        and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");


                    //RECEIPT
                    $nod = 1;
                    foreach ($receipts as $receipt) {
                        $balance = ($begin + ($receipt->qty - $end_qty));
                        $html .= '  <tr>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td style="mso-number-format:\@;">' . $record->number . '</td>
                                        <td style="mso-number-format:\@;">' . $record->name . '</td>
                                        <td>' . $record->uom . '</td>
                                        <td>' . $receipt->receipt_type . '</td>
                                        <td>-</td> 
                                        <td>' . $receipt->username . '</td>
                                        <td>' . $receipt->wo_no  . '</td>
                                        <td>' . $receipt->trans_date . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($receipt->qty, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                    </tr>';
                        $begin += $receipt->qty;
                        $nod++;
                    }
                }

                if ($filter_trans_type == 'NEW BARCODE') {

                    //RECEIPT
                    // $receipts = $this->crud->query("SELECT 
                    // COALESCE(f.checksheet_label,'-') as wo_no, 
                    // f.checksheet_label, 
                    // f.qty, 
                    // u.name AS username, 
                    // f.packing_date AS trans_date, 
                    // 'NEW BARCODE FG' AS receipt_type

                    // FROM new_barcode_fg a
                    // LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                    // LEFT JOIN users u ON f.created_by = u.username
                    // WHERE a.item_fg_id = '$item_fg_id'
                    // AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");

                    $receiptsNB = $this->crud->query("SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
                        FROM new_barcode_fg a
                        LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                        LEFT JOIN users u ON f.created_by = u.username
                        WHERE a.item_fg_id = '$item_fg_id' 
                        AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");
                        
                    //RECEIPT
                    $nod = 1;
                    foreach ($receiptsNB as $receipt) {
                        $balance = ($begin + ($receipt->qty - $end_qty));
                        $html .= '  <tr>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td style="mso-number-format:\@;">' . $record->number . '</td>
                                        <td style="mso-number-format:\@;">' . $record->name . '</td>
                                        <td>' . $record->uom . '</td>
                                        <td>' . $receipt->receipt_type . '</td>
                                        <td>-</td> 
                                        <td>' . $receipt->username . '</td>
                                        <td>' . $receipt->wo_no  . '</td>
                                        <td>' . $receipt->trans_date . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format($receipt->qty, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                    </tr>';
                        $begin += $receipt->qty;
                        $nod++;
                    }
                }

                if ($filter_trans_type == 'DELIVERY NOTE') {

                    //DELIVERY NOTE
                    $returns = $this->crud->query("SELECT a.*,
                        d.name as username, 'DELIVERY NOTE' AS receipt_type, a.trans_type as dn_type
                        FROM delivery_notes a 
                        JOIN users d ON a.created_by = d.username
                        WHERE a.item_fg_id = '$item_fg_id' 
                        AND a.delivery_note_date between '$filter_from' and '$filter_to'");

                    foreach ($returns as $return) {
                        $balance = ($begin - $return->qty);
                        $html .= '  <tr>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td style="mso-number-format:\@;">' . $record->number . '</td>
                                        <td style="mso-number-format:\@;">' . $record->name . '</td>
                                        <td>' . $record->uom . '</td>
                                        <td>' . $return->receipt_type . '</td>
                                        <td>' . $return->dn_type . '</td>
                                        <td>' . $return->username . '</td>
                                        <td>' . $return->delivery_order_no  . '</td>
                                        <td>' . $return->delivery_note_date . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0) . '</td>
                                        <td style="text-align:right;">' . number_format($return->qty, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin -= $return->qty;
                        $nod++;
                    }
                }

                if ($filter_trans_type == 'REPAIR OF GOODS') {

                    //REPAIR OF GOODS
                    $repairs = $this->crud->query("SELECT f.wo_no, 
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

                    foreach ($repairs as $repair) {
                        $balance = ($begin - $repair->qty);
                        $html .= '  <tr>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td style="mso-number-format:\@;">' . $record->number . '</td>
                                        <td style="mso-number-format:\@;">' . $record->name . '</td>
                                        <td>' . $record->uom . '</td>
                                        <td>' . $repair->receipt_type . '</td>
                                        <td>-</td>
                                        <td>' . $repair->username . '</td>
                                        <td>' . $repair->document_no  . '</td>
                                        <td>' . $repair->trans_date . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . number_format(0) . '</td>
                                        <td style="text-align:right;">' . number_format($repair->qty, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';
                        $begin -= $repair->qty;
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
                        b.name as username,
                        'ADJ IN STO' AS trans_type
                    FROM transaction_fg a
                    JOIN users b ON a.created_by = b.username
                    WHERE a.item_fg_id = '$item_fg_id' and a.transaction_type = 'ADJ IN STO' and a.request_date between '$filter_from' and '$filter_to'");
        
                    foreach ($transactions as $transaction) {
                        $balance = ($transaction->transaction_kind == 'IN') 
                                    ? ($begin + $transaction->qty) 
                                    : ($begin - $transaction->qty);
                    
                        $html .= '  <tr>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td style="mso-number-format:\@;">' . $record->number . '</td>
                                        <td style="mso-number-format:\@;">' . $record->name . '</td>
                                        <td>' . $record->uom . '</td>
                                        <td>' . $transaction->trans_type . '</td>
                                        <td>-</td>
                                        <td>' . $transaction->username . '</td>
                                        <td>' . $transaction->request_no . '</td>
                                        <td>' . $transaction->request_date . '</td>
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
                        b.name as username,
                        'ADJ OUT STO' AS trans_type
                    FROM transaction_fg a
                    JOIN users b ON a.created_by = b.username
                    WHERE a.item_fg_id = '$item_fg_id' 
                    AND a.transaction_type = 'ADJ OUT STO' AND a.request_date between '$filter_from' and '$filter_to'");
        
                    foreach ($transactions as $transaction) {
                        $balance = ($transaction->transaction_kind == 'OUT') 
                                    ? ($begin - $transaction->qty) 
                                    : ($begin + $transaction->qty);
                    
                        $html .= '  <tr>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td style="mso-number-format:\@;">' . $record->number . '</td>
                                        <td style="mso-number-format:\@;">' . $record->name . '</td>
                                        <td>' . $record->uom . '</td>
                                        <td>' . $transaction->trans_type . '</td>
                                        <td>-</td>
                                        <td>' . $transaction->username . '</td>
                                        <td>' . $transaction->request_no . '</td>
                                        <td>' . $transaction->request_date . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                        <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                    </tr>';
                    
                        // Update balance
                        if ($transaction->transaction_kind == 'OUT') {
                            $begin -= $transaction->qty;
                        } else {
                            $begin += $transaction->qty;
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
                        b.name as username,
                        'BPB' AS trans_type
                    FROM transaction_fg a
                    JOIN users b ON a.created_by = b.username
                    WHERE a.item_fg_id = '$item_fg_id' 
                    AND a.transaction_type = 'BPB' AND a.request_date between '$filter_from' and '$filter_to'");
        
                    foreach ($transactions as $transaction) {
                        $balance = ($transaction->transaction_kind == 'OUT') 
                                    ? ($begin - $transaction->qty) 
                                    : ($begin + $transaction->qty);
                    
                        $html .= '  <tr>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td style="mso-number-format:\@;">' . $record->number . '</td>
                                        <td style="mso-number-format:\@;">' . $record->name . '</td>
                                        <td>' . $record->uom . '</td>
                                        <td>' . $transaction->trans_type . '</td>
                                        <td>-</td>
                                        <td>' . $transaction->username . '</td>
                                        <td>' . $transaction->request_no . '</td>
                                        <td>' . $transaction->request_date . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                        <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                        <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                    </tr>';
                    
                        // Update balance
                        if ($transaction->transaction_kind == 'OUT') {
                            $begin -= $transaction->qty;
                        } else {
                            $begin += $transaction->qty;
                        }
                        
                        $nod++;
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
