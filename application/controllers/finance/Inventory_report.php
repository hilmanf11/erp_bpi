<?php
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Inventory_report extends CI_Controller
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
            $this->load->view('finance/inventory_report');
        } else {
            redirect('error_access');
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
        $filter_division = $this->input->get("filter_division");
        $filter_item_category = $this->input->get("filter_item_category");
        $filter_item_family = $this->input->get("filter_item_family");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $rm  = $this->getSummaryRM($filter_from, $filter_to, $filter_division, $filter_item_category, $filter_item_family);
        $wip = $this->getSummaryWIP($filter_from, $filter_to, $filter_division);
        $fg  = $this->getSummaryFG($filter_from, $filter_to, $filter_division);

        $summary = [];
        $current = strtotime(date("Y-m-01", strtotime($filter_from)));
        $end     = strtotime(date("Y-m-01", strtotime($filter_to)));

        while ($current <= $end) {
            $month_from = date("Y-m-01", $current); // awal bulan
            $month_to   = date("Y-m-t", $current);  // akhir bulan

            $rm  = $this->getSummaryRM($month_from, $month_to, $filter_division, $filter_item_category, $filter_item_family);
            $wip = $this->getSummaryWIP($month_from, $month_to, $filter_division);
            $fg  = $this->getSummaryFG($month_from, $month_to, $filter_division);

            $summary[] = [
                'month'    => date("M Y", $current),
                'category' => 'Raw Material',
                'totalBegin' => $rm['totalBegin'],
                'totalBeginAmount' => $rm['totalBeginAmount'],
                'totalIn' => $rm['totalIn'],
                'totalAmountIn' => $rm['totalAmountIn'],
                'totalOut' => $rm['totalOut'],
                'totalAmountOut' => $rm['totalAmountOut'],
                'totalEndingStock' => $rm['totalEndingStock'],
                'totalAmountEndingStock' => $rm['totalAmountEndingStock']
            ];
            $summary[] = [
                'month'    => date("M Y", $current),
                'category' => 'Work In Process',
                'totalBegin' => $wip['totalBegin'],
                'totalBeginAmount' => $wip['totalBeginAmount'],
                'totalIn' => $wip['totalIn'],
                'totalAmountIn' => $wip['totalAmountIn'],
                'totalOut' => $wip['totalOut'],
                'totalAmountOut' => $wip['totalAmountOut'],
                'totalEndingStock' => $wip['totalEndingStock'],
                'totalAmountEndingStock' => $wip['totalAmountEndingStock']
            ];
            $summary[] = [
                'month'    => date("M Y", $current),
                'category' => 'Finish Good',
                'totalBegin' => $fg['totalBegin'],
                'totalBeginAmount' => $fg['totalBeginAmount'],
                'totalIn' => $fg['totalIn'],
                'totalAmountIn' => $fg['totalAmountIn'],
                'totalOut' => $fg['totalOut'],
                'totalAmountOut' => $fg['totalAmountOut'],
                'totalEndingStock' => $fg['totalEndingStock'],
                'totalAmountEndingStock' => $fg['totalAmountEndingStock']
            ];
            $summary[] = [
                'month'    => date("M Y", $current),
                'category' => 'TOTAL',
                'totalBegin' => $rm['totalBegin'] + $wip['totalBegin'] + $fg['totalBegin'],
                'totalBeginAmount' => $rm['totalBeginAmount'] + $wip['totalBeginAmount'] + $fg['totalBeginAmount'],
                'totalIn' => $rm['totalIn'] + $wip['totalIn'] + $fg['totalIn'],
                'totalAmountIn' => $rm['totalAmountIn'] + $wip['totalAmountIn'] + $fg['totalAmountIn'],
                'totalOut' => $rm['totalOut'] + $wip['totalOut'] + $fg['totalOut'],
                'totalAmountOut' => $rm['totalAmountOut'] + $wip['totalAmountOut'] + $fg['totalAmountOut'],
                'totalEndingStock' => $rm['totalEndingStock'] + $wip['totalEndingStock'] + $fg['totalEndingStock'],
                'totalAmountEndingStock' => $rm['totalAmountEndingStock'] + $wip['totalAmountEndingStock'] + $fg['totalAmountEndingStock']
            ];

            // lanjut ke bulan berikutnya
            $current = strtotime("+1 month", $current);
        }

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
            <h3 style="margin:0;">INVENTORY REPORT PT. BANSHU PLASTIC INDONESIA</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            <table id="customers" border="1" style="font-size: 11px;">
                    <tr>
                        <th rowspan="2">Month</th>
                        <th rowspan="2">Category</th>
                        <th colspan="2">Begin</th>
                        <th colspan="2">In</th>
                        <th colspan="2">Out</th>
                        <th colspan="2">Balance</th>
                    </tr>
                    <tr>
                        <th width="150">QTY</th>
                        <th width="150">AMOUNT (IDR)</th>
                        <th width="150">QTY</th>
                        <th width="150">AMOUNT (IDR)</th>
                        <th width="150">QTY</th>
                        <th width="150">AMOUNT (IDR)</th>
                        <th width="150">QTY</th>
                        <th width="150">AMOUNT (IDR)</th>
                    </tr>';

        $currentMonth = "";
        foreach ($summary as $i => $record) {
            $html .= "<tr>";

            // Bold kalau kategori TOTAL
            $isTotal = ($record['category'] == 'TOTAL');
            $style = $isTotal ? 'font-weight:bold; background:#f2f2f2;' : '';

            if ($record['category'] == 'Raw Material') {
                $html .= '<td rowspan="4" style="text-align:center; ' . $style . '">' . $record['month'] . '</td>';
            }

            $html .= '<td style="text-align:center; ' . $style . '">' . $record['category'] . '</td>
                    <td style="text-align:right; ' . $style . '">' . number_format($record['totalBegin'], 2) . '</td>
                    <td style="text-align:right; ' . $style . '">' . number_format($record['totalBeginAmount'], 2) . '</td>
                    <td style="text-align:right; ' . $style . '">' . number_format($record['totalIn'], 2) . '</td>
                    <td style="text-align:right; ' . $style . '">' . number_format($record['totalAmountIn'], 2) . '</td>
                    <td style="text-align:right; ' . $style . '">' . number_format($record['totalOut'], 2) . '</td>
                    <td style="text-align:right; ' . $style . '">' . number_format($record['totalAmountOut'], 2) . '</td>
                    <td style="text-align:right; ' . $style . '">' . number_format($record['totalEndingStock'], 2) . '</td>
                    <td style="text-align:right; ' . $style . '">' . number_format($record['totalAmountEndingStock'], 2) . '</td>
                </tr>';
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    private function getSummaryRM($filter_from, $filter_to, $filter_division, $filter_item_category, $filter_item_family)
    {
        $divisions = $this->crud->read('divisions', [], ["id" => $filter_division]);
        $division_number = $divisions->number;
        $query_main = "SELECT 
            a.id,
            a.number, 
            a.name, 
            a.division, 
            b.name as prodfam, 
            COALESCE(aa.price,0) as price,
            COALESCE(aa.currency,'-') as currency,
            d.receipt_date,
            h.created_date as receipt_date_out,
            a.uom,
            c.name as category_name,
            COALESCE(j.begin_stock, 0) AS begin_stock,
            (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) AS qty_in,
            (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) AS qty_out
        FROM item_rm a
        JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
        JOIN item_categories c ON a.item_category_id = c.id
        LEFT JOIN (SELECT item_rm_id, currency, price from standard_price_rm where '$filter_from' >= `start_date` and '$filter_to' <= `end_date`) aa on a.id = aa.item_rm_id
        LEFT JOIN (SELECT MAX(b.price) AS price, MAX(b.currency) AS currency, MAX(b.receipt_date) AS receipt_date, b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        LEFT JOIN (SELECT MAX(price) AS price, MAX(currency) AS currency, MAX(created_date) AS created_date, item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
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
        AND a.division LIKE '%$division_number%'
        GROUP BY a.id
        ORDER BY c.name DESC, b.name DESC, a.number";
        $records = $this->crud->query($query_main);

        $totalBeginStock = 0;
        $totalBeginAmount = 0;
        $totalIn = 0;
        $totalAmountIn = 0;
        $totalOut = 0;
        $totalAmountOut = 0;
        $totalEndingStock = 0;
        $totalAmountEndingStock = 0;

        foreach ($records as $record) {
            $item_rm_id = $record->id;
            $receipt_date = @$record->receipt_date;
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
        }

        return [
            'totalBegin' => $totalBeginStock ?? 0,
            'totalBeginAmount' => $totalBeginAmount ?? 0,
            'totalIn' => $totalIn ?? 0,
            'totalAmountIn' => $totalAmountIn ?? 0,
            'totalOut' => $totalOut ?? 0,
            'totalAmountOut' => $totalAmountOut ?? 0,
            'totalEndingStock' => $totalEndingStock ?? 0,
            'totalAmountEndingStock' => $totalAmountEndingStock ?? 0
        ];
    }

    private function getSummaryFG($filter_from, $filter_to, $filter_division)
    {
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
        WHERE a.division_id LIKE '%$filter_division%'
        ORDER BY a.number
        ";

        // echo $query_main;
        // die();

        $records = $this->crud->query($query_main);

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
        }

        return [
            'totalBegin' => $totalBeginStock ?? 0,
            'totalBeginAmount' => $totalBeginAmount ?? 0,
            'totalIn' => $totalIn ?? 0,
            'totalAmountIn' => $totalAmountIn ?? 0,
            'totalOut' => $totalOut ?? 0,
            'totalAmountOut' => $totalAmountOut ?? 0,
            'totalEndingStock' => $totalEndingStock ?? 0,
            'totalAmountEndingStock' => $totalAmountEndingStock ?? 0
        ];
    }

    private function getSummaryWIP($filter_from, $filter_to, $filter_division)
    {
        $query_main = "
                        select a.id,
                        a.number,
                        a.name, 
                        a.uom,
                        j.number as division,
                        COALESCE(k.price,0) as price,
                        COALESCE(k.currency,'-') as currency,
                        COALESCE(b.qty_wo,0) as qty_wo,
                        COALESCE(i.begin_balance,0) as begin_balance,
                        COALESCE(c.qty_actual,0) as qty_actual,
                        COALESCE(c2.qty_wip,0) as qty_wip,
                        COALESCE(j2.qty_adj_in,0) as qty_adj_in,
                        COALESCE(d.qty_ng,0) as qty_ng,
                        COALESCE((COALESCE(c.qty_actual,0)+COALESCE(d.qty_ng,0)),0) as total_production,
                        COALESCE(f.qty_subcont_jasa,0) as subconts_jasa,
                        COALESCE(g.qty_in_checksheet,0) + COALESCE(gb.initial_in,0) + COALESCE(gc.qty_in_wip_receipt,0) as rfg,
                        COALESCE(h.qty_rfg_jasa,0) as rfg_jasa,
                        COALESCE(c.qty_actual,0) + COALESCE(f.qty_subcont_jasa,0) + COALESCE(c2.qty_wip,0) + COALESCE(j2.qty_adj_in,0) as qty_in,
                        COALESCE(ng_map.qty_ng,0) + COALESCE(g.qty_in_checksheet,0) + COALESCE(gb.initial_in,0) + COALESCE(gc.qty_in_wip_receipt,0) + COALESCE(h.qty_rfg_jasa,0) + COALESCE(k2.qty_adj_out,0) as qty_out,
                        COALESCE((COALESCE(i.begin_balance,0)) + COALESCE(c.qty_actual,0) + COALESCE(f.qty_subcont_jasa,0) + COALESCE(j2.qty_adj_in,0) + COALESCE(c2.qty_wip,0) - 
                               COALESCE(ng_map.qty_ng,0) - COALESCE(g.qty_in_checksheet,0) + COALESCE(gb.initial_in,0) + COALESCE(gc.qty_in_wip_receipt,0) + COALESCE(h.qty_rfg_jasa,0) + COALESCE(k2.qty_adj_out,0), 0) as ending_balance
                        FROM item_fg a
                        LEFT JOIN (
                                    select aa.item_fg_id,sum(aa.qty_wo) as qty_wo FROM (
                                            select distinct item_fg_id, workorder, period, qty_wo FROM  supply_sheets where request_date between '$filter_from' AND '$filter_to' 
                                    ) aa group by aa.item_fg_id
                        ) b on a.id = b.item_fg_id
                        LEFT JOIN (
                                    select item_fg_id, sum(qty) as qty_actual FROM output_productions where trans_date between '$filter_from' AND '$filter_to'   group by item_fg_id
                        ) c on a.id = c.item_fg_id
                        LEFT JOIN (
                                    select item_fg_id, sum(qty_wip) as qty_wip FROM output_productions where trans_date between '$filter_from' AND '$filter_to'   group by item_fg_id
                        ) c2 on a.id = c2.item_fg_id
                        LEFT JOIN (
                                    select aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                            select distinct item_fg_id, qty_product FROM  item_ng where trans_date between '$filter_from' AND '$filter_to' 
                                    ) aa group by aa.item_fg_id
                        ) d on a.id = d.item_fg_id
                        LEFT JOIN (
                            SELECT 
                                subs.item_fg_sa_id AS item_fg_id,
                                SUM(d.qty_ng) AS qty_ng
                            FROM (
                                SELECT 
                                    aa.item_fg_id, 
                                    SUM(aa.qty_product) AS qty_ng
                                FROM (
                                    SELECT DISTINCT document, item_fg_id, qty_product 
                                    FROM item_ng 
                                    WHERE trans_date BETWEEN '$filter_from' AND '$filter_to'
                                    
                                    AND created_by != 'PRD01'
                                ) aa 
                                GROUP BY aa.item_fg_id
                            ) d
                            JOIN item_fg_subs subs ON d.item_fg_id = subs.item_fg_id
                            GROUP BY subs.item_fg_sa_id
                        ) ng_map ON a.id = ng_map.item_fg_id
                        LEFT JOIN (
                                    select item_fg_id,sum(qty) as qty_balance_wip FROM wip_balances_fg where trans_date between '$filter_from' AND '$filter_to' group by item_fg_id
                        ) e on a.id = e.item_fg_id
                        LEFT JOIN (
                                    select aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                            select distinct ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                            FROM  supply_sheets ax 
                                            join item_fg ay on ax.item_fg_id=ay.id 
                                            where ax.request_date between '$filter_from' AND '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa'
                                    ) aa group by aa.item_fg_id
                        ) f on a.id = f.item_fg_id
                        LEFT JOIN (
                            SELECT 
                                main.id AS item_fg_id,
                                SUM(main.qty_rfg) AS qty_in_checksheet
                            FROM (
                                SELECT 
                                    b.item_fg_id AS id,
                                    SUM(a.qty) AS qty_rfg
                                FROM scan_item_receipts_fg a
                                JOIN checksheets b ON b.number = a.checksheet_number
                                WHERE DATE_FORMAT(b.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' 
                                    AND b.status_subcont='NO' 
                                GROUP BY b.item_fg_id

                                UNION ALL

                                SELECT 
                                    sub.item_fg_sa_id AS id,
                                    SUM(a.qty) AS qty_rfg
                                FROM scan_item_receipts_fg a
                                JOIN checksheets b ON b.number = a.checksheet_number
                                JOIN item_fg_subs sub ON sub.item_fg_id = b.item_fg_id
                                WHERE DATE_FORMAT(b.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' 
                                    AND b.status_subcont='NO' 
                                GROUP BY sub.item_fg_sa_id
                            ) main
                            GROUP BY main.id
                        ) g on a.id = g.item_fg_id
                        LEFT JOIN (
                                    SELECT a.item_fg_id, SUM(a.qty) as qty_in_no_checksheet
                                    FROM scan_item_receipts_fg a
                                    WHERE a.type = 'NBFG'
                                    AND a.packing_date BETWEEN '$filter_from' AND '$filter_to' 
                                    GROUP BY a.item_fg_id
                        ) ga on a.id = ga.item_fg_id
                        LEFT JOIN (
                                    SELECT a.item_fg_id, SUM(a.qty) as initial_in
                                    FROM transaction_fg a
                                    WHERE a.transaction_kind = 'IN'
                                    AND a.transaction_type = 'RECEIPT FG'
                                    AND a.request_date BETWEEN '$filter_from' AND '$filter_to' 
                                    GROUP BY a.item_fg_id
                        ) gb on a.id = gb.item_fg_id
                        LEFT JOIN (
                                    SELECT a.item_fg_id, SUM(a.qty) as qty_in_wip_receipt
                                    FROM wip_receipts a
                                    WHERE a.division = 'MTS'
                                    AND a.trans_date BETWEEN '$filter_from' AND '$filter_to' 
                                    GROUP BY a.item_fg_id
                        ) gc on a.id = gc.item_fg_id
                        LEFT JOIN (
                                    select aa.item_fg_id,sum(aa.qty) as qty_rfg_jasa 
                                    FROM scan_item_receipts_fg aa 
                                    JOIN checksheets ab on aa.checksheet_number = ab.number
                                    where ab.packing_date between '$filter_from' AND '$filter_to' and ab.subcont_type='Jasa'
                                    GROUP BY ab.item_fg_id
                        ) h on a.id = h.item_fg_id
                        LEFT JOIN (
                                    select a.item_fg_id,sum(a.qty) as qty_adj_in 
                                    FROM wip_adjustment_fg a
                                    where a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN'
                                    GROUP BY a.item_fg_id
                        ) j2 on a.id = j2.item_fg_id
                        LEFT JOIN (
                                    select a.item_fg_id,sum(a.qty) as qty_adj_out 
                                    FROM wip_adjustment_fg a
                                    where a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT'
                                    GROUP BY a.item_fg_id
                        ) k2 on a.id = k2.item_fg_id
                        LEFT JOIN (
                                    SELECT a.id,
                                        COALESCE(e.qty_balance_wip, 0) + COALESCE(c.qty_actual, 0)  + COALESCE(c2.qty_wip, 0) + COALESCE(f.qty_subcont_jasa, 0) + COALESCE(j.qty_adj_in, 0) - COALESCE(ng_map.qty_ng,0) - COALESCE(g.qty_in_checksheet, 0) - COALESCE(gb.initial_in, 0) - COALESCE(gc.qty_in_wip_receipt, 0) - COALESCE(h.qty_rfg_jasa, 0) - COALESCE(k.qty_adj_out, 0) AS begin_balance
                                    FROM item_fg a
                                    -- qty_balance_wip pada 2025-04-30 (cutoff)
                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_balance_wip
                                        FROM wip_balances_fg
                                        WHERE trans_date = '2025-04-30'
                                        GROUP BY item_fg_id
                                    ) e ON a.id = e.item_fg_id

                                    -- Transaksi setelah cutoff_date sampai < filter_from
                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_actual
                                        FROM output_productions
                                        WHERE trans_date >= '2025-05-01' AND trans_date < '$filter_from'
                                        
                                        GROUP BY item_fg_id
                                    ) c ON a.id = c.item_fg_id

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty_wip) AS qty_wip
                                        FROM output_productions
                                        WHERE trans_date >= '2025-05-01' AND trans_date < '$filter_from'
                                        
                                        GROUP BY item_fg_id
                                    ) c2 ON a.id = c2.item_fg_id

                                    LEFT JOIN (
                                        SELECT aa.item_fg_id, SUM(aa.qty_wo) AS qty_subcont_jasa
                                        FROM (
                                            SELECT DISTINCT ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo
                                            FROM supply_sheets ax
                                            JOIN item_fg ay ON ax.item_fg_id = ay.id
                                            WHERE ax.request_date >= '2025-05-01' AND ax.request_date < '$filter_from'
                                            AND ay.status_subcont = 'YES' AND ay.subcont_type = 'Jasa'
                                        ) aa
                                        GROUP BY aa.item_fg_id
                                    ) f ON a.id = f.item_fg_id

                                    LEFT JOIN (
                                        SELECT 
                                            main.id AS item_fg_id,
                                            SUM(main.qty_rfg) AS qty_in_checksheet
                                        FROM (
                                            SELECT 
                                                b.item_fg_id AS id,
                                                SUM(a.qty) AS qty_rfg
                                            FROM scan_item_receipts_fg a
                                            JOIN checksheets b ON b.number = a.checksheet_number
                                            WHERE DATE_FORMAT(b.packing_date, '%Y-%m-%d') >= '2025-05-01'
                                            AND DATE_FORMAT(b.packing_date, '%Y-%m-%d') < '$filter_from'
                                            AND b.status_subcont = 'NO'
                                            GROUP BY b.item_fg_id

                                            UNION ALL

                                            SELECT 
                                                sub.item_fg_sa_id AS id,
                                                SUM(a.qty) AS qty_rfg
                                            FROM scan_item_receipts_fg a
                                            JOIN checksheets b ON b.number = a.checksheet_number
                                            JOIN item_fg_subs sub ON sub.item_fg_id = b.item_fg_id
                                            WHERE DATE_FORMAT(b.packing_date, '%Y-%m-%d') >= '2025-05-01'
                                            AND DATE_FORMAT(b.packing_date, '%Y-%m-%d') < '$filter_from'
                                            AND b.status_subcont = 'NO'
                                            GROUP BY sub.item_fg_sa_id
                                        ) main
                                        GROUP BY main.id
                                    ) g ON a.id = g.item_fg_id

                                    LEFT JOIN (
                                        SELECT 
                                            subs.item_fg_sa_id AS item_fg_id,
                                            SUM(d.qty_ng) AS qty_ng
                                        FROM (
                                            SELECT 
                                                aa.item_fg_id, 
                                                SUM(aa.qty_product) AS qty_ng
                                            FROM (
                                                SELECT DISTINCT document, item_fg_id, qty_product 
                                                FROM item_ng 
                                                WHERE trans_date >= '2025-05-01' AND trans_date < '$filter_from'
                                                AND created_by != 'PRD01'
                                            ) aa 
                                            GROUP BY aa.item_fg_id
                                        ) d
                                        JOIN item_fg_subs subs ON d.item_fg_id = subs.item_fg_id
                                        GROUP BY subs.item_fg_sa_id
                                    ) ng_map ON a.id = ng_map.item_fg_id

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_in_no_checksheet
                                        FROM scan_item_receipts_fg
                                        WHERE type = 'NBFG'
                                        AND packing_date >= '2025-05-01'
                                        AND packing_date < '$filter_from'
                                        GROUP BY item_fg_id
                                    ) ga ON a.id = ga.item_fg_id

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS initial_in
                                        FROM transaction_fg
                                        WHERE transaction_kind = 'IN'
                                        AND transaction_type = 'RECEIPT FG'
                                        AND request_date >= '2025-05-01'
                                        AND request_date < '$filter_from'
                                        GROUP BY item_fg_id
                                    ) gb ON a.id = gb.item_fg_id

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_in_wip_receipt
                                        FROM wip_receipts
                                        WHERE division = 'MTS'
                                        AND trans_date >= '2025-05-01'
                                        AND trans_date < '$filter_from'
                                        GROUP BY item_fg_id
                                    ) gc ON a.id = gc.item_fg_id

                                    LEFT JOIN (
                                        SELECT ab.item_fg_id, SUM(aa.qty) AS qty_rfg_jasa
                                        FROM scan_item_receipts_fg aa
                                        JOIN checksheets ab ON aa.checksheet_number = ab.number
                                        WHERE ab.packing_date >= '2025-05-01'
                                        AND ab.packing_date < '$filter_from'
                                        AND ab.subcont_type = 'Jasa'
                                        GROUP BY ab.item_fg_id
                                    ) h ON a.id = h.item_fg_id

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_adj_in
                                        FROM wip_adjustment_fg
                                        WHERE request_date >= '2025-05-01'
                                        AND request_date < '$filter_from'
                                        AND transaction_type = 'ADJ IN'
                                        GROUP BY item_fg_id
                                    ) j ON a.id = j.item_fg_id

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_adj_out
                                        FROM wip_adjustment_fg
                                        WHERE request_date >= '2025-05-01'
                                        AND request_date < '$filter_from'
                                        AND transaction_type = 'ADJ OUT'
                                        GROUP BY item_fg_id
                                    ) k ON a.id = k.item_fg_id
                        ) i ON a.id = i.id
                        LEFT JOIN divisions j on a.division_id = j.id
                        LEFT JOIN (SELECT item_fg_id, currency, price from standard_price_fg where '$filter_from' >= `start_date` and '$filter_to' <= `end_date`) k on a.id = k.item_fg_id
                        WHERE a.type != 'RM' AND a.division_id LIKE '%$filter_division%' AND a.status = 0 AND a.id != 'BPIFG-INJ08240009'
                        ORDER BY a.number
        ";

        $records = $this->crud->query($query_main);

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
            $totalBeginStock += @$record->begin_balance;
            $totalBeginAmount += @$record->price * $rate * @$record->begin_balance;
            $totalIn += @$record->qty_in;
            $totalAmountIn += @$record->price * $rate * @$record->qty_in;
            $totalOut += @$record->qty_out;
            $totalAmountOut += @$record->price * $rate * @$record->qty_out;
            $totalEndingStock += @(@$record->begin_balance + $record->qty_in) - $record->qty_out;
            $totalAmountEndingStock += ((@$record->price * $rate) * @$record->qty_in) + ((@$record->price * $rate) * @$record->begin_balance) - ((@$record->price * $rate) * @$record->qty_out);
        }

        return [
            'totalBegin' => $totalBeginStock ?? 0,
            'totalBeginAmount' => $totalBeginAmount ?? 0,
            'totalIn' => $totalIn ?? 0,
            'totalAmountIn' => $totalAmountIn ?? 0,
            'totalOut' => $totalOut ?? 0,
            'totalAmountOut' => $totalAmountOut ?? 0,
            'totalEndingStock' => $totalEndingStock ?? 0,
            'totalAmountEndingStock' => $totalAmountEndingStock ?? 0
        ];
    }
}
