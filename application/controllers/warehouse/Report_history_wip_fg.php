<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_wip_fg extends CI_Controller
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
            $this->load->view('warehouse/report_history_wip_fg');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_wip_fg_$format.xls");
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

    //    // Step 1: Hitung qty_in dari checksheet
    //     $query_qty_in_checksheet = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_checksheet
    //     FROM scan_item_receipts_fg f
    //     JOIN checksheets e ON e.number = f.checksheet_number
    //     WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
    //     GROUP BY e.item_fg_id";

    //     // Step 2: Hitung qty_in tanpa checksheet
    //     $query_qty_in_no_checksheet = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
    //     FROM scan_item_receipts_fg i
    //     WHERE i.type = 'NBFG'
    //     AND i.packing_date BETWEEN '$filter_from' AND '$filter_to'
    //     GROUP BY i.item_fg_id";

    //     $query_production_schedule = "SELECT a.item_fg_id, SUM(a.qty) as qty_production_schedules
    //     FROM production_schedules a
    //     WHERE DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
    //     GROUP BY a.item_fg_id";

    //     //-----------------------------------------------------------------

    //     $query_qty_in_checksheet2 = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_checksheet
    //     FROM scan_item_receipts_fg f
    //     JOIN checksheets e ON e.number = f.checksheet_number
    //     WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') < '$filter_from'
    //     GROUP BY e.item_fg_id";

    //     // Step 2: Hitung qty_in tanpa checksheet
    //     $query_qty_in_no_checksheet2 = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
    //     FROM scan_item_receipts_fg i
    //     WHERE i.type = 'NBFG'
    //     AND i.packing_date < '$filter_from'
    //     GROUP BY i.item_fg_id";

    //     $query_production_schedule2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_production_schedules
    //     FROM production_schedules a
    //     WHERE DATE_FORMAT(a.trans_date, '%Y-%m-%d') < '$filter_from'
    //     GROUP BY a.item_fg_id";


    //     // Step 7: Gabungan query
    //     $query_main = "SELECT 
    //         a.id, 
    //         a.number, 
    //         a.name, 
    //         a.uom,
    //         COALESCE(x.begin_stock,0) AS begin_stock,
    //         COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) AS qty_out,
            
    //         COALESCE(qps.qty_production_schedules, 0) AS qty_in,
            
    //         (COALESCE(qps.qty_production_schedules, 0) - (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0))) AS end_stock
        
    //     FROM item_fg a
    //     LEFT JOIN ($query_qty_in_checksheet) qc ON a.id = qc.item_fg_id
    //     LEFT JOIN ($query_qty_in_no_checksheet) qnc ON a.id = qnc.item_fg_id
    //     LEFT JOIN ($query_production_schedule) qps ON a.id = qps.item_fg_id
    //     LEFT JOIN ( SELECT a.id,
    //         (COALESCE(qps.qty_production_schedules, 0) - (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0))) AS begin_stock
    //         FROM item_fg a
    //         LEFT JOIN ($query_qty_in_checksheet2) qc ON a.id = qc.item_fg_id
    //         LEFT JOIN ($query_qty_in_no_checksheet2) qnc ON a.id = qnc.item_fg_id
    //         LEFT JOIN ($query_production_schedule2) qps ON a.id = qps.item_fg_id
            
    //         GROUP BY a.id) x ON a.id = x.id
    //     WHERE a.id LIKE '%$filter_items%'
    //     ORDER BY a.number
    //     ";

    //     $records = $this->crud->query($query_main);


     // Step 1: Hitung qty_in dari checksheet
     $query_qty_in_checksheet = "SELECT e.item_fg_id, SUM(e.receipt) as qty_in_checksheet
     FROM checksheets e
     WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
     GROUP BY e.item_fg_id";

     $query_production_schedule = "SELECT a.item_fg_id, SUM(a.qty) as qty_production_schedules
     FROM production_schedules a
     WHERE DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
     GROUP BY a.item_fg_id";

     //-----------------------------------------------------------------

     $query_qty_in_checksheet2 = "SELECT e.item_fg_id, SUM(e.qty) as qty_in_checksheet
     FROM checksheets e
     WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') < '$filter_from'
     GROUP BY e.item_fg_id";

     $query_production_schedule2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_production_schedules
     FROM production_schedules a
     WHERE DATE_FORMAT(a.trans_date, '%Y-%m-%d') < '$filter_from'
     GROUP BY a.item_fg_id";


     // Step 7: Gabungan query
     $query_main = "SELECT 
         a.id, 
         a.number, 
         a.name, 
         a.uom,
         COALESCE(x.begin_stock,0) AS begin_stock,
         COALESCE(qc.qty_in_checksheet, 0) AS qty_out,
         
         COALESCE(qps.qty_production_schedules, 0) AS qty_in,
         
         (COALESCE(qps.qty_production_schedules, 0) - (COALESCE(qc.qty_in_checksheet, 0))) AS end_stock
     
     FROM item_fg a
     LEFT JOIN ($query_qty_in_checksheet) qc ON a.id = qc.item_fg_id
     LEFT JOIN ($query_production_schedule) qps ON a.id = qps.item_fg_id
     LEFT JOIN ( SELECT a.id,
         (COALESCE(qps.qty_production_schedules, 0) - (COALESCE(qc.qty_in_checksheet, 0))) AS begin_stock
         FROM item_fg a
         LEFT JOIN ($query_qty_in_checksheet2) qc ON a.id = qc.item_fg_id
         LEFT JOIN ($query_production_schedule2) qps ON a.id = qps.item_fg_id
         
         GROUP BY a.id) x ON a.id = x.id
     WHERE a.id LIKE '%$filter_items%'
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
            <h3 style="margin:0;">REPORT WIP (FG)</h3>
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
                </tr>';
        $no = 1;
        foreach ($records as $record) {
            $item_fg_id = $record->id;

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
                $begin = @$record->begin_stock;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;

                if ($filter_trans_type == '') {

                    // Ambil seluruh data untuk rentang tanggal dalam satu query per jenis transaksi
                    $receipts = $this->crud->query("SELECT e.wo_no, e.receipt, c.name AS username, e.packing_date AS trans_date, 'CHECKSHEET' AS receipt_type
                    FROM checksheets e
                    JOIN users c ON e.created_by = c.username
                    WHERE e.item_fg_id = '$item_fg_id'
                    AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");

                    $production_schedules = $this->crud->query("SELECT a.*, d.name AS username
                    FROM production_schedules a
                    JOIN users d ON a.created_by = d.username
                    WHERE a.item_fg_id = '$item_fg_id'
                    AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");

                    // Proses data berdasarkan tanggal
                    $all_data = [];

                    // Gabungkan data receipts
                    foreach ($receipts as $receipt) {
                    $all_data[] = [
                        'type' => $receipt->receipt_type,
                        'username' => $receipt->username,
                        'date' => $receipt->trans_date,
                        'wo_no' => $receipt->wo_no,
                        'label' => '-',
                        'qty_in' => 0,
                        'qty_out' => $receipt->receipt,
                    ];
                    }

                    // Gabungkan data delivery notes
                    foreach ($production_schedules as $production_schedule) {
                    $all_data[] = [
                        'type' => 'PRODUCTION SCHEDULE',
                        'username' => $production_schedule->username,
                        'date' => $production_schedule->trans_date,
                        'wo_no' => $production_schedule->wo_no,
                        'label' => '-',
                        'qty_in' => $production_schedule->qty,
                        'qty_out' => 0,
                    ];
                    }

                    // Urutkan data berdasarkan tanggal
                    usort($all_data, function ($a, $b) {
                    // return strtotime($a['date']) - strtotime($b['date']);
                    return strcmp($a['type'], $b['type']);
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
                                    <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                </tr>';

                    $begin = $balance;
                    $nod++;
                    }

                }

                // if ($filter_trans_type == 'RECEIPT FG') {

                //     //RECEIPT
                //     $receipts = $this->crud->query("SELECT f.*, c.name as username, e.packing_date as trans_date
                //         -- FROM production_schedules d
                //         FROM scan_item_receipts_fg f
                //         JOIN checksheets e ON e.number = f.checksheet_number
                //         LEFT JOIN users c ON f.created_by = c.username
                //         WHERE e.item_fg_id = '$item_fg_id' 
                //         and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                //     if (empty($receipts)) {
                //         $receipts = $this->crud->query("SELECT f.*, u.name as username ,f.packing_date as trans_date
                //             FROM new_barcode_fg a
                //             LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                //             LEFT JOIN users u ON f.created_by = u.username
                //             WHERE a.item_fg_id = '$item_fg_id' 
                //             AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");
                        
                //         $receipt_type = 'NEW BARCODE FG';
                //     } else {
                //         $receipt_type = 'RECEIPT FG';
                //     }


                //     //RECEIPT
                //     foreach ($receipts as $receipt) {
                //         $balance = ($begin + ($receipt->qty - $end_qty));
                //         $html .= '  <tr>
                //                         <td></td>
                //                         <td style="text-align:center">' . $nod . '</td>
                //                         <td>' . $receipt_type . '</td>
                //                         <td>' . $receipt->username . '</td>
                //                         <td>' . $receipt->trans_date . '</td>
                //                         <td>' . $receipt->wo_no . '</td>
                //                         <td>' . $receipt->checksheet_label . '</td>
                //                         <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                //                         <td style="text-align:right;">' . number_format($receipt->qty, 2) . '</td>
                //                         <td style="text-align:right;">' . number_format(0)  . '</td>
                //                         <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                //                     </tr>';
                //         $begin += $receipt->qty;
                //         $nod++;
                //     }
                // }

                // if ($filter_trans_type == 'NEW BARCODE') {

                //     //RECEIPT
                
                //     $receipts = $this->crud->query("SELECT f.*, 
                //         u.name as username ,
                //         f.packing_date as trans_date,
                //         'NEW BARCODE FG' as receipt_type
                //         FROM new_barcode_fg a
                //         LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                //         LEFT JOIN users u ON f.created_by = u.username
                //         WHERE a.item_fg_id = '$item_fg_id' 
                //         AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");
                        

                //     //RECEIPT
                //     foreach ($receipts as $receipt) {
                //         $balance = ($begin + ($receipt->qty - $end_qty));
                //         $html .= '  <tr>
                //                         <td></td>
                //                         <td style="text-align:center">' . $nod . '</td>
                //                         <td>' . $receipt->receipt_type . '</td>
                //                         <td>' . $receipt->username . '</td>
                //                         <td>' . $receipt->trans_date . '</td>
                //                         <td>' . $receipt->wo_no . '</td>
                //                         <td>' . $receipt->checksheet_label . '</td>
                //                         <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                //                         <td style="text-align:right;">' . number_format($receipt->qty, 2) . '</td>
                //                         <td style="text-align:right;">' . number_format(0)  . '</td>
                //                         <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                //                     </tr>';
                //         $begin += $receipt->qty;
                //         $nod++;
                //     }
                // }

                // if ($filter_trans_type == 'DELIVERY NOTE') {

                //     //DELIVERY NOTE
                //     $returns = $this->crud->query("SELECT a.*,
                //         d.name as username
                //         FROM delivery_notes a 
                //         JOIN users d ON a.created_by = d.username
                //         WHERE a.item_fg_id = '$item_fg_id' and a.delivery_note_date between '$filter_from' and '$filter_to'");

                //     foreach ($returns as $return) {
                //         $balance = ($begin - $return->qty);
                //         $html .= '  <tr>
                //                         <td></td>
                //                         <td style="text-align:center">' . $nod . '</td>
                //                         <td>DELIVERY NOTE</td>
                //                         <td>' . $return->username . '</td>
                //                         <td>' . $return->delivery_note_date . '</td>
                //                         <td>' . $return->delivery_order_no  . '</td>
                //                         <td>' . $return->delivery_note_no . '</td>
                //                         <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                //                         <td style="text-align:right;">' . number_format(0) . '</td>
                //                         <td style="text-align:right;">' . number_format($return->qty, 2)  . '</td>
                //                         <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                //                     </tr>';
                //         $begin -= $return->qty;
                //         $nod++;
                //     }
                // }

                // if ($filter_trans_type == 'REPAIR OF GOODS') {

                //     //DELIVERY NOTE
                //     $repairs = $this->crud->query("SELECT f.wo_no, 
                //     f.checksheet_label, 
                //     f.qty, 
                //     c.name AS username, 
                //     e.packing_date AS trans_date, 
                //     'REPAIR OF GOODS' AS receipt_type
                //     FROM scan_repair_of_goods f
                //     LEFT JOIN checksheets e ON e.number = f.checksheet_number
                //     LEFT JOIN users c ON f.created_by = c.username
                //     WHERE e.item_fg_id = '$item_fg_id'
                //     AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");

                //     foreach ($repairs as $repair) {
                //         $balance = ($begin - $repair->qty);
                //         $html .= '  <tr>
                //                         <td></td>
                //                         <td style="text-align:center">' . $nod . '</td>
                //                         <td>REPAIR OF GOODS</td>
                //                         <td>' . $repair->username . '</td>
                //                         <td>' . $repair->trans_date . '</td>
                //                         <td>' . $repair->wo_no  . '</td>
                //                         <td>' . $repair->checksheet_label . '</td>
                //                         <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                //                         <td style="text-align:right;">' . number_format(0) . '</td>
                //                         <td style="text-align:right;">' . number_format($repair->qty, 2)  . '</td>
                //                         <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                //                     </tr>';
                //         $begin -= $repair->qty;
                //         $nod++;
                //     }
                // }

                // if ($filter_trans_type == 'ADJ IN STO') {
                //     //TRANSACTION
                //     $transactions = $this->crud->query("SELECT
                //         a.request_date,
                //         a.transaction_type,
                //         a.transaction_kind,
                //         a.request_no,
                //         a.qty,
                //         b.name as username
                //     FROM transaction_fg a
                //     JOIN users b ON a.created_by = b.username
                //     WHERE a.item_fg_id = '$item_fg_id' and a.transaction_type = 'ADJ IN STO' and a.request_date between '$filter_from' and '$filter_to'");
        
                //     foreach ($transactions as $transaction) {
                //         $balance = ($transaction->transaction_kind == 'IN') 
                //                     ? ($begin + $transaction->qty) 
                //                     : ($begin - $transaction->qty);
                    
                //         $html .= '  <tr>
                //                         <td></td>
                //                         <td style="text-align:center">' . $nod . '</td>
                //                         <td>ADJ IN STO</td>
                //                         <td>' . $transaction->username . '</td>
                //                         <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                //                         <td>-</td>
                //                         <td>' . $transaction->request_no . '</td>
                //                         <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                //                         <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                //                         <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                //                         <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                //                     </tr>';
                    
                //         // Update balance
                //         if ($transaction->transaction_kind == 'IN') {
                //             $begin += $transaction->qty;
                //         } else {
                //             $begin -= $transaction->qty;
                //         }
                        
                //         $nod++;
                //     }
                // }

                // if ($filter_trans_type == 'ADJ OUT STO') {
                //     //TRANSACTION
                //     $transactions = $this->crud->query("SELECT
                //         a.request_date,
                //         a.transaction_type,
                //         a.transaction_kind,
                //         a.request_no,
                //         a.qty,
                //         b.name as username
                //     FROM transaction_fg a
                //     JOIN users b ON a.created_by = b.username
                //     WHERE a.item_fg_id = '$item_fg_id' and a.transaction_type = 'ADJ OUT STO' and a.request_date between '$filter_from' and '$filter_to'");
        
                //     foreach ($transactions as $transaction) {
                //         $balance = ($transaction->transaction_kind == 'OUT') 
                //                     ? ($begin + $transaction->qty) 
                //                     : ($begin - $transaction->qty);
                    
                //         $html .= '  <tr>
                //                         <td></td>
                //                         <td style="text-align:center">' . $nod . '</td>
                //                         <td>ADJ OUT STO</td>
                //                         <td>' . $transaction->username . '</td>
                //                         <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                //                         <td>-</td>
                //                         <td>' . $transaction->request_no . '</td>
                //                         <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                //                         <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                //                         <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                //                         <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                //                     </tr>';
                    
                //         // Update balance
                //         if ($transaction->transaction_kind == 'IN') {
                //             $begin += $transaction->qty;
                //         } else {
                //             $begin -= $transaction->qty;
                //         }
                        
                //         $nod++;
                //     }
                // }

                // if ($filter_trans_type == 'BPB') {
                //     //TRANSACTION
                //     $transactions = $this->crud->query("SELECT
                //         a.request_date,
                //         a.transaction_type,
                //         a.transaction_kind,
                //         a.request_no,
                //         a.qty,
                //         b.name as username
                //     FROM transaction_fg a
                //     JOIN users b ON a.created_by = b.username
                //     WHERE a.item_fg_id = '$item_fg_id' and a.transaction_type = 'BPB' and a.request_date between '$filter_from' and '$filter_to'");
        
                //     foreach ($transactions as $transaction) {
                //         $balance = ($transaction->transaction_kind == 'OUT') 
                //                     ? ($begin + $transaction->qty) 
                //                     : ($begin - $transaction->qty);
                    
                //         $html .= '  <tr>
                //                         <td></td>
                //                         <td style="text-align:center">' . $nod . '</td>
                //                         <td>BPB</td>
                //                         <td>' . $transaction->username . '</td>
                //                         <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                //                         <td>-</td>
                //                         <td>' . $transaction->request_no . '</td>
                //                         <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                //                         <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                //                         <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                //                         <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                //                     </tr>';
                    
                //         // Update balance
                //         if ($transaction->transaction_kind == 'IN') {
                //             $begin += $transaction->qty;
                //         } else {
                //             $begin -= $transaction->qty;
                //         }
                        
                //         $nod++;
                //     }
                // }
            
            }
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
