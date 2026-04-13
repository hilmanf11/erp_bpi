<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transaction_boxs extends CI_Controller
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
            $this->load->view('warehouse/report_history_transaction_boxs');
        } else {
            redirect('error_access');
        }
    }

    // public function readEndingStock()
    // {
    //     if ($this->input->post()) {
    //         $item_rm_id = $this->input->post('item_rm_id');
    //         $trans_date = @$this->input->post('trans_date');

    //         if (@$trans_date == "") {
    //             $date = date("Y-m-d");
    //         } else {
    //             $date = $trans_date;
    //         }

    //         $records = $this->crud->query("SELECT 
    //         a.id, 
    //         a.number, 
    //         ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - 
    //         (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
    //                     FROM item_rm a
    //                     LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$date'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
    //                     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
    //                     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$date' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
    //                     LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$date' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
    //                     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
    //                     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$date' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
    //                     LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
    //         WHERE a.id like '$item_rm_id'
    //         GROUP BY a.id
    //         ORDER BY a.number");

    //         echo json_encode($records);
    //     }
    // }

    public function readItemBox()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT id, color, name, uom , size, code
        FROM item_boxs 
        WHERE  `size` like '%$post%' or `name` like '$post'
        ORDER BY `id` ASC");
        echo json_encode($records);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_boxs_$format.xls");
        }
        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get('filter_division');
        $filter_trans_type = $this->input->get('filter_trans_type');
        $filter_customer_name = $this->input->get('filter_customer_name');

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

        //Config ISO
        $this->db->select('*');
        $this->db->from('config_iso');
        $config_iso = $this->db->get()->row();
        $formHistoricalRM = !empty($config_iso->form_historical_rm) ? $config_iso->form_historical_rm : 'DOC';
        
        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//

        if ($filter_display != "RECAP CUSTOMER") {

            $query_main = "SELECT
                a.id,
                a.name,
                a.code,
                a.color,
                -- BEGIN EMPTY
                SUM(CASE 
                    WHEN t.request_date < '$filter_from' 
                        AND t.transaction_type = 'ADJ IN STO - EMPTY' 
                    THEN t.qty
                    WHEN t.request_date < '$filter_from' 
                        AND t.transaction_type = 'ADJ OUT STO - EMPTY' 
                    THEN -t.qty
                    ELSE 0 
                END) AS empty_begin,

                -- BEGIN WIP/FG
                SUM(CASE 
                    WHEN t.request_date < '$filter_from' 
                        AND t.transaction_type = 'ADJ IN STO - WIP/FG' 
                    THEN t.qty
                    WHEN t.request_date < '$filter_from' 
                        AND t.transaction_type = 'ADJ OUT STO - WIP/FG' 
                    THEN -t.qty
                    ELSE 0 
                END) AS wip_begin,

                -- BEGIN SUPP/CUST
                SUM(CASE 
                    WHEN t.request_date < '$filter_from' 
                        AND t.transaction_type = 'ADJ IN STO - SUPPLIER/CUST' 
                    THEN t.qty
                    WHEN t.request_date < '$filter_from' 
                        AND t.transaction_type = 'ADJ OUT STO - SUPPLIER/CUST' 
                    THEN -t.qty
                    ELSE 0 
                END) AS supp_begin,

                -- EMPTY IN/OUT
                SUM(CASE 
                    WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                        AND (
                            (t.transaction_type NOT LIKE 'ADJ%STO%' AND t.transaction_to = 'EMPTY')
                            OR t.transaction_type = 'ADJ IN STO - EMPTY'
                        )
                    THEN t.qty ELSE 0 END
                ) AS empty_in,

                SUM(CASE 
                    WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                        AND (
                            (t.transaction_type NOT LIKE 'ADJ%STO%' AND t.transaction_from = 'EMPTY')
                            OR t.transaction_type = 'ADJ OUT STO - EMPTY'
                        )
                    THEN t.qty ELSE 0 END
                ) AS empty_out,

                -- WIP IN/OUT
                SUM(CASE 
                    WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                        AND (
                            (t.transaction_type NOT LIKE 'ADJ%STO%' AND t.transaction_to = 'WIP/FG')
                            OR t.transaction_type = 'ADJ IN STO - WIP/FG'
                        )
                    THEN t.qty ELSE 0 END
                ) AS wip_in,

                (
                    SUM(CASE 
                        WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                            AND (
                                (t.transaction_type NOT LIKE 'ADJ%STO%' AND t.transaction_from = 'WIP/FG')
                                OR t.transaction_type = 'ADJ OUT STO - WIP/FG'
                            )
                        THEN t.qty ELSE 0 END
                    )  
                    +   
                    -- OUT dari DN boxs
                        IFNULL(dn.dn_in, 0)
                )AS wip_out,

                -- SUPP/CUST IN (gabung dengan DN)
                (
                    -- IN dari transaksi normal
                    SUM(CASE 
                        WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                            AND (
                                (t.transaction_type NOT LIKE 'ADJ%' AND t.transaction_to = 'SUPPLIER/CUST')
                                OR t.transaction_type = 'ADJ IN STO - SUPPLIER/CUST'
                            )
                        THEN t.qty ELSE 0 END
                    )
                    +
                    -- IN dari DN boxs
                    IFNULL(dn.dn_in, 0)
                ) AS supp_in,

                -- SUPP/CUST OUT
                SUM(CASE 
                    WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                        AND (
                            (t.transaction_type NOT LIKE 'ADJ%' AND t.transaction_from = 'SUPPLIER/CUST')
                            OR t.transaction_type = 'ADJ OUT STO - SUPPLIER/CUST'
                            OR t.transaction_type IN ('Receipt from cust/sup (emp)', 'Receipt from cust/sup (full)')
                        )
                    THEN t.qty ELSE 0 END
                ) AS supp_out

            FROM item_boxs a
            LEFT JOIN transaction_boxs t ON a.id = t.item_box_id

            LEFT JOIN (
                SELECT item_box_id, SUM(qty) AS dn_in
                FROM dn_boxs
                WHERE transaction_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY item_box_id
            ) dn ON dn.item_box_id = a.id

            WHERE a.id LIKE '%$filter_items%'
            GROUP BY a.id, a.name, a.code, a.color
            ORDER BY a.id ASC";

            // Eksekusi query
            $records = $this->crud->query($query_main);
            
            $html = '<html><head><title>Print Data</title></head>
                <style>
                    body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}
                    /* Style khusus untuk media cetak */
                    @media print {
                        #customers thead {
                            display: table-header-group;
                        }
                        #customers tbody tr {
                            page-break-inside: avoid;
                        }
                    }
                </style><body>
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
                    </div>';
                    
            if ($option == "excel") {
                $html .= '<div style="float: right; font-size: 12px; text-align: right;">
                            Print Date ' . date("d M Y H:i:s") . ' <br>
                            Print By ' . $this->session->username . '  
                        </div>';
            } else {
                $html .= '<div style="float: right; font-size: 12px; text-align: right;">
                            Print Date ' . date("d M Y H:i:s") . ' <br>
                            Print By ' . $this->session->username . '  
                        </div>
                    <br><br>';
            }
            
            $html .= '<br><br>
                    <h3 style="margin:0;">HISTORY TRANSACTION BOX</h3>
                    <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                </center>
                <br><br>
                
                <table id="customers" border="1" style="font-size: 11px;">
                <thead>
                    <tr>
                        <th rowspan="3" width="20">No</th>
                        <th rowspan="3">Id Box</th>
                        <th rowspan="3">Name</th>
                        <th rowspan="3">Box Code</th>
                        <th rowspan="3">Color</th>
                        <th rowspan="3">Begin</th>
                        <th colspan="8">BPI</th>
                        <th colspan="4">-</th>
                        <th colspan="5" rowspan="3">BALANCE</th>
                    </tr>
                    <tr>
                        <th colspan="4">EMPTY BOX</th>
                        <th colspan="4">WIP/FG</th>
                        <th colspan="4">SUPPLIER/CUSTOMER</th>
                    </tr>
                    <tr>
                        <th>BEGIN</th>
                        <th>IN</th>
                        <th>OUT</th>
                        <th>ENDING</th>
                        <th>BEGIN</th>
                        <th>IN</th>
                        <th>OUT</th>
                        <th>ENDING</th>
                        <th>BEGIN</th>
                        <th>IN</th>
                        <th>OUT</th>
                        <th>ENDING</th>
                    </tr>
                </thead>';


            $no = 1;
            $totalBeginStock = 0;
            $totalIn = 0;
            $totalOut = 0;
            $totalBpi = 0;
            $totalPlant1 = 0;
            $totalEndingStock = 0;
            $totalIto = 0;

            function isAdjustmentSTO($type) {
                return (stripos($type, 'ADJ') !== false && stripos($type, 'STO') !== false);
            }

            foreach ($records as $record) {
                    $item_box_id = $record->id;
                    $empty_balance = $record->empty_begin + $record->empty_in - $record->empty_out;
                    $wip_balance   = $record->wip_begin + $record->wip_in - $record->wip_out;
                    $supp_balance  = $record->supp_begin + $record->supp_in - $record->supp_out;
                    $total_begin_utama = $record->empty_begin + $record->wip_begin + $record->supp_begin;
                    $total_balance_utama = $empty_balance + $wip_balance + $supp_balance;
                    // $totalBeginStock += $total_begin_utama;
                    
                    $html .= '<tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record->id . '</td>
                                <td>' . $record->name . '</td>
                                <td>' . $record->code . '</td>
                                <td>' . $record->color . '</td>
                                <td style="text-align:right">' . number_format($total_begin_utama) . '</td>
                                <td style="text-align:right">' . number_format($record->empty_begin) . '</td>
                                <td style="text-align:right">' . number_format($record->empty_in) . '</td>
                                <td style="text-align:right">' . number_format($record->empty_out) . '</td>
                                <td style="text-align:right">' . number_format($empty_balance) . '</td>
                                <td style="text-align:right">' . number_format($record->wip_begin) . '</td>
                                <td style="text-align:right">' . number_format($record->wip_in) . '</td>
                                <td style="text-align:right">' . number_format($record->wip_out) . '</td>
                                <td style="text-align:right">' . number_format($wip_balance) . '</td>
                                <td style="text-align:right">' . number_format($record->supp_begin) . '</td>
                                <td style="text-align:right">' . number_format($record->supp_in) . '</td>
                                <td style="text-align:right">' . number_format($record->supp_out) . '</td>
                                <td style="text-align:right">' . number_format($supp_balance) . '</td>
                                <td style="text-align:center" colspan="5" >' . number_format($total_balance_utama) . '</td>
                            </tr>';
                if ($filter_display == "DETAIL") {
                    $html .= '
                            <tr>
                                <td colspan="23" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->name . ' - ' . $record->color . '</b></td>
                            </tr>
                            <tr>
                                <th rowspan="3" width="20"></th>
                                <th rowspan="3" width="20">No</th>
                                <th rowspan="3">Id Box</th>
                                <th rowspan="3">Name</th>
                                <th rowspan="3">Box Code</th>
                                <th rowspan="3">Color</th>
                                <th rowspan="3">Type Transaction</th>
                                <th rowspan="3">Description</th>
                                <th rowspan="3">Date</th>
                                <th rowspan="3">Begin</th>
                                <th colspan="8">BPI</th>
                                <th colspan="4">-</th>
                                <th rowspan="3">BALANCE</th>
                            </tr>
                            <tr>
                                <th colspan="4">EMPTY BOX</th>
                                <th colspan="4">WIP/FG</th>
                                <th colspan="4">SUPPLIER/CUSTOMER</th>
                            </tr>
                            <tr>
                                <th>BEGIN</th>
                                <th>IN</th>
                                <th>OUT</th>
                                <th>ENDING</th>
                                <th>BEGIN</th>
                                <th>IN</th>
                                <th>OUT</th>
                                <th>ENDING</th>
                                <th>BEGIN</th>
                                <th>IN</th>
                                <th>OUT</th>
                                <th>ENDING</th>
                            </tr>';

                        $nod = 1;

                        // --- Ambil nilai begin per area dari recap utama
                        $begin_empty = $record->empty_begin ?? 0;
                        $begin_wip   = $record->wip_begin ?? 0;
                        $begin_supp  = $record->supp_begin ?? 0;
                        $balance     = $begin_empty + $begin_wip + $begin_supp;
                        if ($filter_trans_type == 'Choose All' ) {
                            //-------------- Awal Query disini----------------------------------//                    
                            $detail = $this->crud->query("SELECT
                                    a.id,
                                    a.name,
                                    a.code,
                                    a.color,
                                    t.transaction_type AS type,
                                    t.request_date AS date,
                                    d.remarks AS `desc`,
                                    t.qty,
                                    t.transaction_to,
                                    t.transaction_from,
                                    t.created_date,
                                    0 AS is_dn
                                FROM item_boxs a
                                LEFT JOIN transaction_boxs t ON a.id = t.item_box_id
                                JOIN master_transaction_boxs d ON t.transaction_id = d.id
                                WHERE a.id = '$item_box_id'
                                AND t.request_date BETWEEN '$filter_from' AND '$filter_to'

                                UNION ALL

                                -- DN dianggap sebagai transaksi OUT dari WIP/FG dan IN ke SUPPLIER/CUST
                                SELECT
                                    a.id,
                                    a.name,
                                    a.code,
                                    a.color,
                                    'DN to Cust/Sup' AS type,
                                    dn.transaction_date AS date,
                                    'FROM WIP/FG TO SUPPLIER/CUST' AS `desc`,
                                    dn.qty,
                                    'SUPPLIER/CUST' AS transaction_to,
                                    'WIP/FG' AS transaction_from,
                                    dn.created_date,
                                    1 AS is_dn
                                FROM dn_boxs dn
                                JOIN item_boxs a ON dn.item_box_id = a.id
                                LEFT JOIN customers c ON dn.customer_id = c.id
                                WHERE a.id = '$item_box_id' 
                                AND dn.transaction_date BETWEEN '$filter_from' AND '$filter_to'

                                ORDER BY date ASC, created_date ASC
                            ");
                            
                            // --- Dalam bagian foreach ($detail as $row) ---
                            foreach ($detail as $row) {
                                // Default 0
                                $empty_in = $empty_out = 0;
                                $wip_in = $wip_out = 0;
                                $supp_in = $supp_out = 0;

                                // === EMPTY ===
                                if (
                                    ($row->transaction_to == 'EMPTY' && !isAdjustmentSTO($row->type))
                                    || $row->type == 'ADJ IN STO - EMPTY'
                                ) $empty_in = $row->qty;

                                if (
                                    ($row->transaction_from == 'EMPTY' && !isAdjustmentSTO($row->type))
                                    || $row->type == 'ADJ OUT STO - EMPTY'
                                ) $empty_out = $row->qty;


                                // === WIP / FG ===
                                if (
                                    ($row->transaction_to == 'WIP/FG' && !isAdjustmentSTO($row->type))
                                    || $row->type == 'ADJ IN STO - WIP/FG'
                                ) $wip_in = $row->qty;

                                if (
                                    ($row->transaction_from == 'WIP/FG' && !isAdjustmentSTO($row->type))
                                    || $row->type == 'ADJ OUT STO - WIP/FG'
                                    || ($row->is_dn == 1)  // dari DN keluar dari WIP/FG
                                ) $wip_out = $row->qty;


                                // === SUPPLIER / CUSTOMER ===
                                if (
                                    ($row->transaction_to == 'SUPPLIER/CUST' && !isAdjustmentSTO($row->type))
                                    || $row->type == 'ADJ IN STO - SUPPLIER/CUST'
                                    || ($row->is_dn == 1)  // dari DN masuk ke SUPP/CUST
                                ) $supp_in = $row->qty;

                                if (
                                    ($row->transaction_from == 'SUPPLIER/CUST' && !isAdjustmentSTO($row->type))
                                    || $row->type == 'ADJ OUT STO - SUPPLIER/CUST'
                                    || in_array($row->type, ['Receipt from cust/sup (emp)', 'Receipt from cust/sup (full)'])
                                ) $supp_out = $row->qty;


                                // === Hitung Ending ===
                                $end_empty = $begin_empty + $empty_in - $empty_out;
                                $end_wip   = $begin_wip   + $wip_in   - $wip_out;
                                $end_supp  = $begin_supp  + $supp_in  - $supp_out;
                                $balance   = $end_empty + $end_wip + $end_supp;

                                // === Output ke tabel HTML ===
                                $html .= '<tr>
                                    <td></td>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td>' . $row->id . '</td>
                                    <td>' . $row->name . '</td>
                                    <td>' . $row->code . '</td>
                                    <td>' . $row->color . '</td>
                                    <td>' . $row->type . '</td>
                                    <td>' . $row->desc . '</td>
                                    <td>' . date("Y-m-d", strtotime($row->date)) . '</td>
                                    <td style="text-align:right;">' . number_format($begin_empty + $begin_wip + $begin_supp) . '</td>

                                    <!-- EMPTY -->
                                    <td style="text-align:right;">' . number_format($begin_empty) . '</td>
                                    <td style="text-align:right;">' . number_format($empty_in) . '</td>
                                    <td style="text-align:right;">' . number_format($empty_out) . '</td>
                                    <td style="text-align:right;">' . number_format($end_empty) . '</td>

                                    <!-- WIP / FG -->
                                    <td style="text-align:right;">' . number_format($begin_wip) . '</td>
                                    <td style="text-align:right;">' . number_format($wip_in) . '</td>
                                    <td style="text-align:right;">' . number_format($wip_out) . '</td>
                                    <td style="text-align:right;">' . number_format($end_wip) . '</td>

                                    <!-- SUPPLIER / CUSTOMER -->
                                    <td style="text-align:right;">' . number_format($begin_supp) . '</td>
                                    <td style="text-align:right;">' . number_format($supp_in) . '</td>
                                    <td style="text-align:right;">' . number_format($supp_out) . '</td>
                                    <td style="text-align:right;">' . number_format($end_supp) . '</td>

                                    <!-- BALANCE -->
                                    <td style="text-align:right;">' . number_format($balance) . '</td>
                                </tr>';

                                // Update saldo awal untuk baris berikutnya
                                $begin_empty = $end_empty;
                                $begin_wip   = $end_wip;
                                $begin_supp  = $end_supp;

                                $nod++;
                            }
                        }
                
                        // if ($filter_trans_type == 'RECEIPT') {
                        //     //RECEIPT
                        //     $receipts = $this->crud->query("SELECT
                        //         a.receipt_date, 
                        //         a.bc_kind, 
                        //         a.receipt_no,
                        //         a.bc_document, 
                        //         a.bc_date, 
                        //         a.lotno,
                        //         SUM(b.qty) as qty_receipt,
                        //         c.name as username
                        //     FROM purchase_order_receipts a 
                        //     JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
                        //     JOIN users c ON a.created_by = c.username
                        //     WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$filter_from' and '$filter_to'
                        //     GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id
                        //     ORDER BY a.receipt_date");
                
                        //     foreach ($receipts as $receipt) {
                        //         $balance = ($begin + ($receipt->qty_receipt - $end_qty));
                        //         $html .= '  <tr>
                        //                         <td></td>
                        //                         <td style="text-align:center">' . $nod . '</td>
                        //                         <td>RECEIPT</td>
                        //                         <td>' . $receipt->username . '</td>
                        //                         <td>' . $receipt->receipt_date . '</td>
                        //                         <td>' . $receipt->bc_kind . '</td>
                        //                         <td>' . $receipt->receipt_no . '</td>
                        //                         <td>' . $receipt->lotno . '</td>
                        //                         <td>' . $receipt->bc_document . '</td>
                        //                         <td>' . $receipt->bc_date . '</td>
                        //                         <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                        //                         <td style="text-align:right;">' . number_format($receipt->qty_receipt, 2) . '</td>
                        //                         <td style="text-align:right;">' . number_format(0)  . '</td>
                        //                         <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                        //                     </tr>';
                        //         $begin += $receipt->qty_receipt;
                        //         $nod++;
                        //     }
                        // }
                }
                $no++;
            }
        }

        if ($filter_display == "RECAP CUSTOMER") {

            $query_main = "SELECT
    a.id,
    a.name,
    a.code,
    a.color,
    cu.name AS customer_name,

    /* BEGIN */
    SUM(
        CASE 
            WHEN t.request_date < '$filter_from'
                 AND t.transaction_from = cu.name
                 AND t.transaction_type IN ('ADJ IN STO - SUPPLIER/CUST', 'ADJ OUT STO - SUPPLIER/CUST')
            THEN 
                CASE 
                    WHEN t.transaction_type = 'ADJ IN STO - SUPPLIER/CUST' THEN t.qty
                    WHEN t.transaction_type = 'ADJ OUT STO - SUPPLIER/CUST' THEN -t.qty
                END
            ELSE 0
        END
    ) AS supp_begin,

    /* IN = transaksi + dn */
    (
        SUM(
            CASE
                WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                     AND t.transaction_from = cu.name
                     AND (
                        (t.transaction_type NOT LIKE 'ADJ%' AND t.transaction_to = 'SUPPLIER/CUST')
                        OR t.transaction_type = 'ADJ IN STO - SUPPLIER/CUST'
                     )
                THEN t.qty ELSE 0
            END
        )
        + IFNULL(dn.dn_in, 0)
    ) AS supp_in,

    /* OUT */
    SUM(
        CASE
            WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                 AND t.transaction_from = cu.name
                 AND (
                    (t.transaction_type NOT LIKE 'ADJ%' AND t.transaction_from = 'SUPPLIER/CUST')
                    OR t.transaction_type = 'ADJ OUT STO - SUPPLIER/CUST'
                    OR t.transaction_type IN ('Receipt from cust/sup (emp)', 'Receipt from cust/sup (full)')
                 )
            THEN t.qty ELSE 0
        END
    ) AS supp_out

FROM item_boxs a

LEFT JOIN transaction_boxs t 
    ON a.id = t.item_box_id

/* PASTIKAN 1 TRANSAKSI MATCH KE 1 CUSTOMER SAJA */
LEFT JOIN customers cu 
    ON cu.name = t.transaction_from

/* DN per customer */
LEFT JOIN (
    SELECT 
        dn.item_box_id,
        dn.customer_id,
        SUM(dn.qty) AS dn_in
    FROM dn_boxs dn
    WHERE dn.transaction_date BETWEEN '$filter_from' AND '$filter_to'
    GROUP BY dn.item_box_id, dn.customer_id
) dn 
    ON dn.item_box_id = a.id 
    AND dn.customer_id = cu.id   /* <-- paling penting */

WHERE 1=1
    AND (a.id LIKE '%$filter_items%' OR '$filter_items' = '')
    AND (cu.name LIKE '%$filter_customer_name%' OR '$filter_customer_name' = '')

GROUP BY 
    a.id, a.name, a.code, a.color, cu.name

HAVING cu.name IS NOT NULL    /* supaya hanya item yg punya customer tampil */

ORDER BY 
    a.id ASC, cu.name ASC";

            // Eksekusi query
            $records = $this->crud->query($query_main);
            
            $html = '<html><head><title>Print Data</title></head>
                <style>
                    body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}
                    /* Style khusus untuk media cetak */
                    @media print {
                        #customers thead {
                            display: table-header-group;
                        }
                        #customers tbody tr {
                            page-break-inside: avoid;
                        }
                    }
                </style><body>
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
                    </div>';
                    
            if ($option == "excel") {
                $html .= '<div style="float: right; font-size: 12px; text-align: right;">
                            Print Date ' . date("d M Y H:i:s") . ' <br>
                            Print By ' . $this->session->username . '  
                        </div>';
            } else {
                $html .= '<div style="float: right; font-size: 12px; text-align: right;">
                            Print Date ' . date("d M Y H:i:s") . ' <br>
                            Print By ' . $this->session->username . '  
                        </div>
                    <br><br>';
            }
            
            $html .= '<br><br>
                    <h3 style="margin:0;">HISTORY TRANSACTION BOX</h3>
                    <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                </center>
                <br><br>
                
                <table id="customers" border="1" style="font-size: 11px;">
                <thead>
                    <tr>
                        <th rowspan="3" width="20">No</th>
                        <th rowspan="3">Id Box</th>
                        <th rowspan="3">Name</th>
                        <th rowspan="3">Customer</th>
                        <th rowspan="3">Box Code</th>
                        <th rowspan="3">Color</th>
                        <th colspan="4">-</th>
                    </tr>
                    <tr>
                        <th colspan="4">SUPPLIER/CUSTOMER</th>
                    </tr>
                    <tr>
                        <th>BEGIN</th>
                        <th>IN</th>
                        <th>OUT</th>
                        <th>ENDING</th>
                    </tr>
                </thead>';


            $no = 1;
            $totalBeginStock = 0;
            $totalIn = 0;
            $totalOut = 0;
            $totalBpi = 0;
            $totalPlant1 = 0;
            $totalEndingStock = 0;
            $totalIto = 0;

            function isAdjustmentSTO($type) {
                return (stripos($type, 'ADJ') !== false && stripos($type, 'STO') !== false);
            }

            foreach ($records as $record) {
                    $item_box_id = $record->id;
                    $supp_balance  = $record->supp_begin + $record->supp_in - $record->supp_out;
                    
                    $html .= '<tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record->id . '</td>
                                <td>' . $record->name . '</td>
                                <td>' . $record->customer_name . '</td>
                                <td>' . $record->code . '</td>
                                <td>' . $record->color . '</td>
                                <td style="text-align:right">' . number_format($record->supp_begin) . '</td>
                                <td style="text-align:right">' . number_format($record->supp_in) . '</td>
                                <td style="text-align:right">' . number_format($record->supp_out) . '</td>
                                <td style="text-align:right">' . number_format($supp_balance) . '</td>
                            </tr>';
                $no++;
            }
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    public function recap_customer($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_boxs_$format.xls");
        }
        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get('filter_division');
        $filter_trans_type = $this->input->get('filter_trans_type');

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

        //Config ISO
        $this->db->select('*');
        $this->db->from('config_iso');
        $config_iso = $this->db->get()->row();
        $formHistoricalRM = !empty($config_iso->form_historical_rm) ? $config_iso->form_historical_rm : 'DOC';
        
        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//

        $customers = $this->crud->query("
            SELECT id, name, number 
            FROM customers
            ORDER BY number ASC
        ");
        $customer_count = count($customers);

        $query_main = "SELECT
            a.id,
            a.name,
            a.code,
            a.color,

            -- BEGIN EMPTY
            SUM(CASE 
                WHEN t.request_date < '$filter_from' 
                    AND t.transaction_type = 'ADJ IN STO - EMPTY' 
                THEN t.qty
                WHEN t.request_date < '$filter_from' 
                    AND t.transaction_type = 'ADJ OUT STO - EMPTY' 
                THEN -t.qty
                ELSE 0 
            END) AS empty_begin,

            -- BEGIN WIP/FG
            SUM(CASE 
                WHEN t.request_date < '$filter_from' 
                    AND t.transaction_type = 'ADJ IN STO - WIP/FG' 
                THEN t.qty
                WHEN t.request_date < '$filter_from' 
                    AND t.transaction_type = 'ADJ OUT STO - WIP/FG' 
                THEN -t.qty
                ELSE 0 
            END) AS wip_begin,

            -- BEGIN SUPP/CUST
            SUM(CASE 
                WHEN t.request_date < '$filter_from' 
                    AND t.transaction_type = 'ADJ IN STO - SUPPLIER/CUST' 
                THEN t.qty
                WHEN t.request_date < '$filter_from' 
                    AND t.transaction_type = 'ADJ OUT STO - SUPPLIER/CUST' 
                THEN -t.qty
                ELSE 0 
            END) AS supp_begin,

            -- EMPTY IN/OUT
            SUM(CASE 
                WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                    AND (
                        (t.transaction_type NOT LIKE 'ADJ%STO%' AND t.transaction_to = 'EMPTY')
                        OR t.transaction_type = 'ADJ IN STO - EMPTY'
                    )
                THEN t.qty ELSE 0 END
            ) AS empty_in,

            SUM(CASE 
                WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                    AND (
                        (t.transaction_type NOT LIKE 'ADJ%STO%' AND t.transaction_from = 'EMPTY')
                        OR t.transaction_type = 'ADJ OUT STO - EMPTY'
                    )
                THEN t.qty ELSE 0 END
            ) AS empty_out,

            -- WIP IN/OUT
            SUM(CASE 
                WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                    AND (
                        (t.transaction_type NOT LIKE 'ADJ%STO%' AND t.transaction_to = 'WIP/FG')
                        OR t.transaction_type = 'ADJ IN STO - WIP/FG'
                    )
                THEN t.qty ELSE 0 END
            ) AS wip_in,

            (
                SUM(CASE 
                    WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                        AND (
                            (t.transaction_type NOT LIKE 'ADJ%STO%' AND t.transaction_from = 'WIP/FG')
                            OR t.transaction_type = 'ADJ OUT STO - WIP/FG'
                        )
                    THEN t.qty ELSE 0 END
                )
                + IFNULL(dn.dn_in, 0)
            ) AS wip_out,

            -- SUPP/CUST IN
            (
                SUM(CASE 
                    WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                        AND (
                            (t.transaction_type NOT LIKE 'ADJ%' AND t.transaction_to = 'SUPPLIER/CUST')
                            OR t.transaction_type = 'ADJ IN STO - SUPPLIER/CUST'
                        )
                    THEN t.qty ELSE 0 END
                )
                + IFNULL(dn.dn_in, 0)
            ) AS supp_in,

            -- SUPP/CUST OUT
            SUM(CASE 
                WHEN t.request_date BETWEEN '$filter_from' AND '$filter_to'
                    AND (
                        (t.transaction_type NOT LIKE 'ADJ%' AND t.transaction_from = 'SUPPLIER/CUST')
                        OR t.transaction_type = 'ADJ OUT STO - SUPPLIER/CUST'
                        OR t.transaction_type IN ('Receipt from cust/sup (emp)', 'Receipt from cust/sup (full)')
                    )
                THEN t.qty ELSE 0 END
            ) AS supp_out

        FROM item_boxs a
        LEFT JOIN transaction_boxs t ON a.id = t.item_box_id
        LEFT JOIN (
            SELECT item_box_id, SUM(qty) AS dn_in
            FROM dn_boxs
            WHERE transaction_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY item_box_id
        ) dn ON dn.item_box_id = a.id

        WHERE a.id LIKE '%$filter_items%'
        GROUP BY a.id, a.name, a.code, a.color
        ORDER BY a.id ASC
        ";

        $records = $this->crud->query($query_main);


        $dn_rows = $this->crud->query("SELECT
                dn.item_box_id,
                dn.customer_id,
                c.number AS customer_number,
                SUM(dn.qty) AS dn_qty
            FROM dn_boxs dn
            LEFT JOIN customers c ON c.id = dn.customer_id
            WHERE dn.transaction_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY dn.item_box_id, dn.customer_id
        ");

        $tx_rows = $this->crud->query("SELECT
                tx.item_box_id,
                cu.id AS customer_id,
                cu.number AS customer_number,
                SUM(
                    CASE
                        WHEN tx.request_date BETWEEN '$filter_from' AND '$filter_to'
                            AND (
                                (tx.transaction_type NOT LIKE 'ADJ%' AND tx.transaction_to = 'SUPPLIER/CUST')
                                OR tx.transaction_type = 'ADJ IN STO - SUPPLIER/CUST'
                            )
                        THEN tx.qty ELSE 0
                    END
                ) AS tx_in,
                SUM(
                    CASE
                        WHEN tx.request_date BETWEEN '$filter_from' AND '$filter_to'
                            AND (
                                (tx.transaction_type NOT LIKE 'ADJ%' AND tx.transaction_from = 'SUPPLIER/CUST')
                                OR tx.transaction_type = 'ADJ OUT STO - SUPPLIER/CUST'
                                OR tx.transaction_type IN ('Receipt from cust/sup (emp)', 'Receipt from cust/sup (full)')
                            )
                        THEN tx.qty ELSE 0
                    END
                ) AS tx_out
            FROM transaction_boxs tx
            LEFT JOIN customers cu ON cu.name = tx.transaction_from
            WHERE tx.request_date BETWEEN '$filter_from' AND '$filter_to'
            AND cu.id IS NOT NULL
            GROUP BY tx.item_box_id, cu.id
        ");

        $per_item_customer = [];

        foreach ($dn_rows as $r) {
            $item = $r->item_box_id;
            $cust = $r->customer_number ?? ('CUST' . $r->customer_id);
            if (!isset($per_item_customer[$item])) $per_item_customer[$item] = [];
            if (!isset($per_item_customer[$item][$cust])) $per_item_customer[$item][$cust] = 0;
            $per_item_customer[$item][$cust] += (float)$r->dn_qty;
        }

        // TX IN/OUT
        foreach ($tx_rows as $r) {
            $item = $r->item_box_id;
            $cust = $r->customer_number ?? ('CUST' . $r->customer_id);
            if (!isset($per_item_customer[$item])) $per_item_customer[$item] = [];
            if (!isset($per_item_customer[$item][$cust])) $per_item_customer[$item][$cust] = 0;
            $per_item_customer[$item][$cust] += (float)$r->tx_in;
            $per_item_customer[$item][$cust] -= (float)$r->tx_out;
        }

        $html = '<html><head><title>Print Data</title></head>
            <style>
                body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}
                /* Style khusus untuk media cetak */
                @media print {
                    #customers thead {
                        display: table-header-group;
                    }
                    #customers tbody tr {
                        page-break-inside: avoid;
                    }
                }
            </style><body>
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
                </div>';
                
        if ($option == "excel") {
            $html .= '<div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:i:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>';
        } else {
            $html .= '<div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:i:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>
                <br><br>';
        }
        
        $html .= '<br><br>
            <h3 style="margin:0;">TRANSACTION BOX RECAP CUSTOMER</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br><br>

        <table id="customers" border="1" style="font-size: 11px;">
            <thead>
                <tr>
                    <th rowspan="3" width="20">No</th>
                    <th rowspan="3">Id Box</th>
                    <th rowspan="3">Name</th>
                    <th rowspan="3">Box Code</th>
                    <th rowspan="3">Color</th>
                    <th colspan="' . (3 + $customer_count) . '">ENDING STOCK</th>
                    <th rowspan="3">BALANCE</th>
                </tr>
                <tr>
                    <th colspan="3">BPI</th>
                    <th colspan="' . $customer_count . '">CUSTOMER</th>
                </tr>
                <tr>
                    <th>EMPTY</th>
                    <th>WIP/FG</th>
                    <th>TOTAL</th>';

                    // header untuk setiap customer
                    foreach ($customers as $cust) {
                        $html .= '<th>' . htmlspecialchars($cust->number) . '</th>';
                    }

        $html .= '</tr></thead><tbody>';

        $no = 1;
        foreach ($records as $rec) {
            $empty_balance = $rec->empty_begin + $rec->empty_in - $rec->empty_out;
            $wip_balance   = $rec->wip_begin + $rec->wip_in - $rec->wip_out;
            $supp_balance  = $rec->supp_begin + $rec->supp_in - $rec->supp_out;
            $total_bpi     = $empty_balance + $wip_balance;
            $balance_total = $total_bpi + $supp_balance;

            $customer_qtys_for_item = isset($per_item_customer[$rec->id]) ? $per_item_customer[$rec->id] : [];

            $html .= '<tr>
                <td style="text-align:center;">' . $no++ . '</td>
                <td>' . $rec->id . '</td>
                <td>' . $rec->name . '</td>
                <td>' . $rec->code . '</td>
                <td>' . $rec->color . '</td>
                <td style="text-align:right;">' . number_format($empty_balance) . '</td>
                <td style="text-align:right;">' . number_format($wip_balance) . '</td>
                <td style="text-align:right;">' . number_format($total_bpi) . '</td>';

            // Kolom customer dinamis
            foreach ($customers as $cust) {
                $cust_num = $cust->number;
                $qty = isset($customer_qtys_for_item[$cust_num]) ? $customer_qtys_for_item[$cust_num] : 0;
                $html .= '<td style="text-align:right;">' . number_format($qty) . '</td>';
            }

            $html .= '<td style="text-align:right;">' . number_format($balance_total) . '</td></tr>';
        }
      
        $html .= '</table></body></html>';
        echo $html;
    }
}
