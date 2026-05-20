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
        $filter_report_category = $this->input->get("filter_report_category");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $div_id = null;
        $div_number = null;

        // if (!empty($filter_division_raw)) {
        //     $this->db->group_start()
        //             ->where('id', $filter_division_raw)
        //             ->or_where('number', $filter_division_raw)
        //             ->group_end();
        //     $div_data = $this->db->get('divisions')->row();

        //     if ($div_data) {
        //         $div_id = $div_data->id;         // FG
        //         $div_number = $div_data->number; // RM
        //     } else {
        //         // Jika tidak ketemu di tabel (fallback/jaga-jaga)
        //         $div_id = $filter_division_raw;
        //         $div_number = $filter_division_raw;
        //     }
        // }

        if($filter_report_category == ""){
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

        }else if($filter_report_category == "RM"){
            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            //Config ISO
            $this->db->select('*');
            $this->db->from('config_iso');
            $config_iso = $this->db->get()->row();
            $formHistoricalRM = !empty($config_iso->form_historical_rm) ? $config_iso->form_historical_rm : 'DOC';

            $current = strtotime(date("Y-m-01", strtotime($filter_from)));

            $query_main = "SELECT 
            a.id,
            a.number, 
            a.name, 
            d.receipt_date,
            COALESCE(aa.price,0) as price,
            COALESCE(aa.currency,'-') as currency,
            COALESCE(j.begin_stock, 0) AS begin_stock,
            (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) AS qty_in,
            (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) AS qty_out

            FROM item_rm a
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN (SELECT item_rm_id, currency, price from standard_price_rm where '$filter_from' >= `start_date` and '$filter_to' <= `end_date`) aa on a.id = aa.item_rm_id
            LEFT JOIN (SELECT MAX(b.receipt_date) AS receipt_date, b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
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
            AND b.number IN ('VG', 'MB','CP','SM')
            AND a.division LIKE '%$filter_division%'
            AND a.item_category_id NOT IN ('C06','C11')
            GROUP BY a.id
            ORDER BY c.name DESC, b.name DESC, a.number";

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
                    <h3 style="margin:0;">INVENTORY REPORT PT. BANSHU PLASTIC INDONESIA DETAILS (RM)</h3>
                    <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                </center>
                <br><br>
                
                <table id="customers" border="1" style="font-size: 11px;">
                <thead>
                    <tr>
                        <th rowspan="2" width="20">No</th>
                        <th rowspan="2" width="80">Month</th>
                        <th rowspan="2" width="80">Category</th>
                        <th rowspan="2" colspan="3">Part No</th>
                        <th rowspan="2" colspan="2">Part Name</th>
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
                    </tr>
                </thead>';


            $no = 1;
            $totalBeginStock = 0;
            $totalBeginAmount = 0;
            $totalIn = 0;
            $totalAmountIn = 0;
            $totalOut = 0;
            $totalAmountOut = 0;
            $totalEndingStock = 0;
            $totalAmountEndingStock = 0;

            $rowCount = count($records);

            foreach ($records as $record) {
                $item_rm_id = $record->id;

                $receipt_date = @$record->receipt_date;
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
                $totalAmountEndingStock += @$record->price * $rate * ((@$record->begin_stock + $record->qty_in) - $record->qty_out);

                $html .= '<tr>
                    <td style="text-align:center">' . $no . '</td>';

                    // 3. Logika Rowspan: Hanya muncul di baris pertama ($no == 1)
                    if ($no == 1) {
                        $html .= '<td style="text-align:center" rowspan="' . $rowCount . '">' . date("M Y", strtotime($filter_from)) . '</td>';
                        $html .= '<td style="text-align:center" rowspan="' . $rowCount . '">Raw Material</td>';
                    }

                    $html .= '      <td colspan="3">' . $record->number . '</td>
                                    <td colspan="2">' . $record->name . '</td>
                                    <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(@$record->price * $rate * @$record->begin_stock, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(@$record->price * $rate * @$record->qty_in, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(@$record->price * $rate * @$record->qty_out, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(@(@$record->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(@$record->price * $rate * ((@$record->begin_stock + $record->qty_in) - $record->qty_out), 2) . '</td>
                                </tr>';
                    $no++;
            }

            $html .= '<tr>
                <td colspan="8" style="text-align:right;"><b>GRAND TOTAL</b></td>
                <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>
                <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>
                <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>
                <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>
            </tr>
            </tbody>';
        
            $html .= '</table></body></html>';
            echo $html;
        }else if($filter_report_category == "WIP"){
            // 1. PENGATURAN AWAL & MEMORI
            ini_set('memory_limit', '1024M');
            set_time_limit(600);
            $from_q = $this->db->escape($filter_from);
            $to_plus_1 = $this->db->escape($filter_to . ' 23:59:59');

            // Ambil Config Perusahaan
            $config = $this->db->get('config')->row();
            $username = $this->session->userdata('username') ? $this->session->userdata('username') : 'System';

            $divisions = $this->crud->read('divisions', [], ["id" => $filter_division]);
            $division_number = $divisions->number;

            // 2. MASTER ITEM & HARGA
            $price_query = "
                SELECT a.id, a.number, a.name, a.uom, COALESCE(k.price, 0) as price
                FROM item_rm a
                LEFT JOIN (
                    SELECT item_fg_id, price FROM standard_price_fg 
                    WHERE start_date <= $from_q AND end_date >= $from_q
                ) k ON a.id = k.item_fg_id
                LEFT JOIN item_categories p ON a.item_category_id = p.id 
                LEFT JOIN item_familys o ON a.item_family_id = o.id
                WHERE p.id LIKE '%$filter_item_category%' 
                AND a.division LIKE '%$division_number%' 
                AND (o.number LIKE '%$filter_item_family%' OR o.number IS NULL)
                ORDER BY a.number ASC";
            $records = $this->db->query($price_query)->result();

            // Mapping Number to ID (untuk pencarian CR-/PL-)
            $all_items_raw = $this->db->query("SELECT id, number FROM item_rm")->result_array();
            $numberToId = [];
            foreach ($all_items_raw as $i) { $numberToId[$i['number']] = $i['id']; }

            // 3. MAPPING DATA TRANSAKSI (SALDO AWAL & PERIODE)
            // --- SALDO AWAL (< From) ---
            $qss_awal = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date < $from_q AND (request_no LIKE '%SH-%' OR request_no LIKE '%PRQ-%') GROUP BY item_rm_id", 'item_rm_id', 'qty');
            $qsns_awal = $this->getQtyMap("SELECT a.item_rm_id, SUM(a.qty) as qty FROM issued_material_details a JOIN supply_materials b ON a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id WHERE a.created_date < $from_q AND b.type = 'Issued Production' GROUP BY a.item_rm_id", 'item_rm_id', 'qty');
            $qtrb_awal = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='BPB' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
            $qtrk_awal = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='KANBAN WO' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
            $qiw_awal  = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date < $from_q AND type LIKE '%WIP%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
            $qai_awal  = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_wip WHERE transaction_type='ADJ IN' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
            
            $qbw_awal  = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM bpm WHERE status='1' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
            $qout_rfg_awal = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.qty * bom.composition) as qty FROM (SELECT b.item_fg_id, SUM(a.qty) qty FROM scan_item_receipts_fg a JOIN checksheets b ON b.number = a.checksheet_number WHERE b.packing_date < $from_q GROUP BY b.item_fg_id UNION ALL SELECT item_fg_id, SUM(qty) FROM transaction_fg WHERE transaction_kind='IN' AND transaction_type='RECEIPT FG' AND request_date < $from_q GROUP BY item_fg_id) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id", 'item_rm_id', 'qty');

            // --- PERIODE BERJALAN (From - To) ---
            $curr_in_sh  = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date >= $from_q AND created_date <= $to_plus_1 AND request_no LIKE '%SH-%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
            $curr_in_prq = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date >= $from_q AND created_date <= $to_plus_1 AND request_no LIKE '%PRQ-%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
            $curr_in_bpb = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='BPB' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');
            $curr_out_rfg = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.qty * bom.composition) as qty FROM (SELECT b.item_fg_id, SUM(a.qty) qty FROM scan_item_receipts_fg a JOIN checksheets b ON b.number = a.checksheet_number WHERE b.packing_date >= $from_q AND b.packing_date <= $to_plus_1 GROUP BY b.item_fg_id UNION ALL SELECT item_fg_id, SUM(qty) FROM transaction_fg WHERE transaction_kind='IN' AND transaction_type='RECEIPT FG' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_fg_id) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id", 'item_rm_id', 'qty');
            $curr_out_bpm = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM bpm WHERE status='1' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');

            // 4. EXCHANGE RATE
            $rate = 1;
            if ($currency == 'USD') {
                $q_rate = $this->db->query("SELECT middle FROM standard_exchange_rates WHERE currency_from = 'USD' AND start_date <= $from_q AND end_date >= $from_q LIMIT 1")->row();
                $rate = $q_rate ? (float)$q_rate->middle : 0;
            }

            // 5. RENDER HTML HEADER
            $html = '<html><head><style>
                body { font-family: Arial, Helvetica, sans-serif; }
                #customers { border-collapse: collapse; width: 100%; font-size: 11px; }
                #customers td, #customers th { border: 1px solid #ddd; padding: 5px; }
                #customers th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                @media print { .no-print { display: none; } }
            </style></head><body>';

            $html .= '<div style="width:100%; margin-bottom:20px;">
                        <div style="float:left; width:50%;">
                            <img src="'.$config->favicon.'" width="30" style="vertical-align:middle;">
                            <span style="font-size:14px; font-weight:bold;">'.$config->name.'</span><br>
                            <small>'.$config->description.'</small>
                        </div>
                        <div style="float:right; width:50%; text-align:right; font-size:11px;">
                            Print Date: '.date("d M Y H:i:s").'<br>Print By: '.$username.'
                        </div>
                        <div style="clear:both;"></div>
                    </div>';

            $html .= '<center>
                        <h3 style="margin:0;">INVENTORY REPORT PT. BANSHU PLASTIC INDONESIA DETAILS (WIP)</h3>
                        <small>PERIOD: <b>'.$filter_from.'</b> To <b>'.$filter_to.'</b></small>
                    </center><br>';

            $html .= '<table id="customers">
                        <thead>
                            <tr>
                                <th rowspan="2" width="30">No</th>
                                <th rowspan="2" width="70">Month</th>
                                <th rowspan="2">Part No</th>
                                <th rowspan="2">Part Name</th>
                                <th colspan="2">Begin Balance</th>
                                <th colspan="2">In</th>
                                <th colspan="2">Out</th>
                                <th colspan="2">Ending Balance</th>
                            </tr>
                            <tr>
                                <th width="80">QTY</th><th width="100">AMOUNT</th>
                                <th width="80">QTY</th><th width="100">AMOUNT</th>
                                <th width="80">QTY</th><th width="100">AMOUNT</th>
                                <th width="80">QTY</th><th width="100">AMOUNT</th>
                            </tr>
                        </thead><tbody>';

            // 6. LOOPING DATA & KALKULASI TOTAL
            $no = 1;
            $t = [
                'bQ' => 0, 'bA' => 0, 
                'iQ' => 0, 'iA' => 0, 
                'oQ' => 0, 'oA' => 0, 
                'eQ' => 0, 'eA' => 0
            ];

            foreach ($records as $record) {
                $id = $record->id;
                $num = $record->number;
                $prc = (float)$record->price;

                // Parent-Child Logic
                $id_cr = $numberToId["CR-".$num] ?? null;
                $id_pl = $numberToId["PL-".$num] ?? null;
                $is_child = (substr($num, 0, 3) === 'CR-' || substr($num, 0, 3) === 'PL-');

                // CALC BEGIN STOCK
                $qss_total_awal = ($qss_awal[$id] ?? 0);
                if (!$is_child) { $qss_total_awal += ($qss_awal[$id_cr] ?? 0) + ($qss_awal[$id_pl] ?? 0); }
                
                $begin = ($qss_total_awal + ($qsns_awal[$id] ?? 0) + ($qtrb_awal[$id] ?? 0) + ($qtrk_awal[$id] ?? 0) + ($qiw_awal[$id] ?? 0) + ($qai_awal[$id] ?? 0))
                        - (($qbw_awal[$id] ?? 0) + ($qout_rfg_awal[$id] ?? 0));

                // CALC IN
                $qty_other = (!$is_child) ? (($curr_in_sh[$id_cr] ?? 0) + ($curr_in_prq[$id_cr] ?? 0) + ($curr_in_sh[$id_pl] ?? 0) + ($curr_in_prq[$id_pl] ?? 0)) : 0;
                $qty_in = ($curr_in_sh[$id] ?? 0) + ($curr_in_prq[$id] ?? 0) + ($curr_in_bpb[$id] ?? 0) + $qty_other;

                // CALC OUT
                $qty_out = ($curr_out_rfg[$id] ?? 0) + ($curr_out_bpm[$id] ?? 0);

                // ENDING
                $ending = ($begin + $qty_in) - $qty_out;

                // AMOUNTS
                $amtB = $begin * $prc * $rate;
                $amtI = $qty_in * $prc * $rate;
                $amtO = $qty_out * $prc * $rate;
                $amtE = $ending * $prc * $rate;

                // Akumulasi Total Grand Total
                $t['bQ'] += $begin; $t['bA'] += $amtB;
                $t['iQ'] += $qty_in; $t['iA'] += $amtI;
                $t['oQ'] += $qty_out; $t['oA'] += $amtO;
                $t['eQ'] += $ending; $t['eA'] += $amtE;

                $html .= '<tr>
                            <td class="text-center">'.$no.'</td>';
                if ($no == 1) {
                    $html .= '<td class="text-center" rowspan="'.count($records).'">'.date("M Y", strtotime($filter_from)).'</td>';
                }
                $html .= '<td>'.$num.'</td>
                            <td>'.$record->name.'</td>
                            <td class="text-right">'.number_format($begin, 2).'</td>
                            <td class="text-right">'.number_format($amtB, 2).'</td>
                            <td class="text-right">'.number_format($qty_in, 2).'</td>
                            <td class="text-right">'.number_format($amtI, 2).'</td>
                            <td class="text-right">'.number_format($qty_out, 2).'</td>
                            <td class="text-right">'.number_format($amtO, 2).'</td>
                            <td class="text-right">'.number_format($ending, 2).'</td>
                            <td class="text-right">'.number_format($amtE, 2).'</td>
                        </tr>';
                $no++;
            }

            // 7. FOOTER GRAND TOTAL
            $html .= '</tbody><tfoot>
                        <tr style="background:#f9f9f9; font-weight:bold;">
                            <td colspan="4" class="text-right">GRAND TOTAL</td>
                            <td class="text-right">'.number_format($t['bQ'], 2).'</td>
                            <td class="text-right">'.number_format($t['bA'], 2).'</td>
                            <td class="text-right">'.number_format($t['iQ'], 2).'</td>
                            <td class="text-right">'.number_format($t['iA'], 2).'</td>
                            <td class="text-right">'.number_format($t['oQ'], 2).'</td>
                            <td class="text-right">'.number_format($t['oA'], 2).'</td>
                            <td class="text-right">'.number_format($t['eQ'], 2).'</td>
                            <td class="text-right">'.number_format($t['eA'], 2).'</td>
                        </tr>
                    </tfoot></table>';

            $html .= '</body></html>';

            // OUTPUT
            if ($option == "excel") {
                header("Content-type: application/vnd-ms-excel");
                header("Content-Disposition: attachment; filename=Inventory_Report_WIP_Detail.xls");
            }
            echo $html;
        
        }else{
            //Config
            $this->db->select('*');
            $this->db->from('config');
            $config = $this->db->get()->row();

            $current = strtotime(date("Y-m-01", strtotime($filter_from)));

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
            WHERE a.division_id LIKE '%$filter_division%' AND a.status = 0
            ORDER BY a.number
            ";

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
                    <h3 style="margin:0;">INVENTORY REPORT PT. BANSHU PLASTIC INDONESIA DETAILS (FG)</h3>
                    <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                </center>
                <br><br>
                
                <table id="customers" border="1" style="font-size: 11px;">
                <thead>
                    <tr>
                        <th rowspan="2" width="20">No</th>
                        <th rowspan="2" width="80">Month</th>
                        <th rowspan="2" width="80">Category</th>
                        <th rowspan="2" colspan="3">Product No</th>
                        <th rowspan="2" colspan="2">Product Name</th>
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
                    </tr>
                </thead>';


            $no = 1;
            $totalBeginStock = 0;
            $totalBeginAmount = 0;
            $totalIn = 0;
            $totalAmountIn = 0;
            $totalOut = 0;
            $totalAmountOut = 0;
            $totalEndingStock = 0;
            $totalAmountEndingStock = 0;

            $rowCount = count($records);

            foreach ($records as $record) {
                $item_fg_id = $record->id;
                // $receipt_date = @$record->receipt_date;
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
                $totalAmountEndingStock += @$record->price * $rate * ((@$record->begin_stock + $record->qty_in) - $record->qty_out);

                $html .= '<tr>
                    <td style="text-align:center">' . $no . '</td>';

                    // 3. Logika Rowspan: Hanya muncul di baris pertama ($no == 1)
                    if ($no == 1) {
                        $html .= '<td style="text-align:center" rowspan="' . $rowCount . '">' . date("M Y", strtotime($filter_from)) . '</td>';
                        $html .= '<td style="text-align:center" rowspan="' . $rowCount . '">Finish Good</td>';
                    }

                    $html .= '      <td colspan="3">' . $record->number . '</td>
                                    <td colspan="2">' . $record->name . '</td>
                                    <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(@$record->price * $rate * @$record->begin_stock, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(@$record->price * $rate * @$record->qty_in, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(@$record->price * $rate * @$record->qty_out, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(@(@$record->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(@$record->price * $rate * ((@$record->begin_stock + $record->qty_in) - $record->qty_out), 2) . '</td>
                                </tr>';
                    $no++;
            }

            $html .= '<tr>
                <td colspan="8" style="text-align:right;"><b>GRAND TOTAL</b></td>
                <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>
                <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>
                <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>
                <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>
            </tr>
            </tbody>';
        
            $html .= '</table></body></html>';
            echo $html;
        }
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
        LEFT JOIN (SELECT MAX(b.receipt_date) AS receipt_date, b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        LEFT JOIN (SELECT MAX(created_date) AS created_date, item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
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
        AND b.number IN ('VG', 'MB','CP','SM')
        AND a.division LIKE '%$division_number%'
        AND a.item_category_id NOT IN ('C06','C11')
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
            $totalAmountEndingStock += @$record->price * $rate * ((@$record->begin_stock + $record->qty_in) - $record->qty_out);
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
        WHERE a.division_id LIKE '%$filter_division%' AND a.status = 0
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
            $totalAmountEndingStock += @$record->price * $rate * ((@$record->begin_stock + $record->qty_in) - $record->qty_out);
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
        $exclude_ids = [
            'BPIFG-INJ08240009',
            'BPIFG-INJ01250007',
            'BPIFG-INJ08240029',
            'BPIFG-INJ08240027',
            'BPIFG-INJ08240024',
            'BPIFG-INJ08240030',
            'BPIFG-INJ08240026',
            'BPIFG-INJ01250013',
            'BPIFG-INJ08240031',
            'BPIFG-INJ08240025',
            'BPIFG-INJ08240028',
            'BPIFG-INJ01250012'
        ];

        $exclude_str = "'" . implode("','", $exclude_ids) . "'";

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
                        COALESCE(outmap.qty_output, 0) AS qty_output,
                        COALESCE(k3.qty_ng_wip, 0) as qty_ng_wip,
                        COALESCE(j2.qty_adj_in,0) as qty_adj_in,
                        COALESCE(d.qty_ng,0) as qty_ng,
                        COALESCE((COALESCE(c.qty_actual,0)+COALESCE(d.qty_ng,0)+COALESCE(c2.qty_wip,0)),0) as total_production,
                        COALESCE(f.qty_subcont_jasa,0) as subconts_jasa,
                        COALESCE(g.qty_in_checksheet,0) + COALESCE(gb.initial_in,0) + COALESCE(gc.qty_in_wip_receipt,0) as rfg,
                        COALESCE(h.qty_rfg_jasa,0) as rfg_jasa,
                        COALESCE(c.qty_actual,0) + COALESCE(f.qty_subcont_jasa,0) + COALESCE(c2.qty_wip,0) + COALESCE(j2.qty_adj_in,0) as qty_in,
                        COALESCE(ng_map.qty_ng,0) + COALESCE(g.qty_in_checksheet,0) + COALESCE(gb.initial_in,0) + COALESCE(gc.qty_in_wip_receipt,0) + COALESCE(h.qty_rfg_jasa,0) + COALESCE(k2.qty_adj_out,0) + COALESCE(k3.qty_ng_wip, 0) + COALESCE(outmap.qty_output, 0) as qty_out,
                        (
                            COALESCE(i.begin_balance, 0) 
                            + 
                            (
                                COALESCE(c.qty_actual, 0) + 
                                COALESCE(f.qty_subcont_jasa, 0) + 
                                COALESCE(c2.qty_wip, 0) + 
                                COALESCE(j2.qty_adj_in, 0)
                            ) 
                            - 
                            (
                                COALESCE(ng_map.qty_ng, 0) + 
                                COALESCE(g.qty_in_checksheet, 0) + 
                                COALESCE(gb.initial_in, 0) + 
                                COALESCE(gc.qty_in_wip_receipt, 0) + 
                                COALESCE(h.qty_rfg_jasa, 0) + 
                                COALESCE(k2.qty_adj_out, 0) + 
                                COALESCE(k3.qty_ng_wip, 0) + 
                                COALESCE(outmap.qty_output, 0)
                            )
                        ) AS ending_balance
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
                            SELECT 
                                sub.item_fg_sa_id AS item_fg_id,
                                SUM(
                                    COALESCE(p.qty_actual, 0) + 
                                    COALESCE(p.qty_wip, 0)
                                ) AS qty_output
                            FROM item_fg_subs sub
                            
                            LEFT JOIN (
                                SELECT 
                                    item_fg_id,
                                    SUM(qty) AS qty_actual,
                                    SUM(qty_wip) AS qty_wip
                                FROM output_productions
                                WHERE trans_date BETWEEN '$filter_from' AND '$filter_to'
                                GROUP BY item_fg_id
                            ) p ON sub.item_fg_id = p.item_fg_id   -- PARENT
                            
                            GROUP BY sub.item_fg_sa_id
                        ) outmap ON a.id = outmap.item_fg_id
                        LEFT JOIN (
                                    select aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                            select distinct item_fg_id, qty_product FROM  item_ng where trans_date between '$filter_from' AND '$filter_to' AND kind LIKE 'Ng Process Production'
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
                                    
                                    AND kind LIKE 'Ng Process Production'
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
                                    select a.item_fg_id,sum(a.qty) as qty_ng_wip 
                                    FROM wip_adjustment_fg a
                                    where a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='NG WIP'
                                    GROUP BY a.item_fg_id
                        ) k3 on a.id = k3.item_fg_id
                        LEFT JOIN (
                                    SELECT a.id,
                                        COALESCE(e.qty_balance_wip, 0) + COALESCE(c.qty_actual, 0)  + COALESCE(c2.qty_wip, 0) + COALESCE(f.qty_subcont_jasa, 0) + COALESCE(j.qty_adj_in, 0) - COALESCE(ng_map.qty_ng,0) - COALESCE(g.qty_in_checksheet, 0) - COALESCE(gb.initial_in, 0) - COALESCE(gc.qty_in_wip_receipt, 0) - COALESCE(h.qty_rfg_jasa, 0) - COALESCE(k.qty_adj_out, 0) - COALESCE(k2.qty_ng_wip, 0) - COALESCE(outmap.qty_output, 0) AS begin_balance
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
                                        SELECT 
                                            sub.item_fg_sa_id AS item_fg_id,
                                            SUM(
                                                COALESCE(p.qty_actual, 0) +
                                                COALESCE(p.qty_wip, 0)
                                            ) AS qty_output
                                        FROM item_fg_subs sub
                                        
                                        LEFT JOIN (
                                            SELECT 
                                                item_fg_id,
                                                SUM(qty) AS qty_actual,
                                                SUM(qty_wip) AS qty_wip
                                            FROM output_productions
                                            WHERE trans_date >= '2025-05-01'
                                            AND trans_date < '$filter_from'
                                            GROUP BY item_fg_id
                                        ) p ON sub.item_fg_id = p.item_fg_id   -- PARENT
                                        
                                        GROUP BY sub.item_fg_sa_id
                                    ) outmap ON a.id = outmap.item_fg_id

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
                                                AND kind LIKE 'Ng Process Production'
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

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_ng_wip
                                        FROM wip_adjustment_fg
                                        WHERE request_date >= '2025-05-01'
                                        AND request_date < '$filter_from'
                                        AND transaction_type = 'NG WIP'
                                        GROUP BY item_fg_id
                                    ) k2 ON a.id = k2.item_fg_id
                        ) i ON a.id = i.id
                        LEFT JOIN divisions j on a.division_id = j.id
                        LEFT JOIN (SELECT item_fg_id, currency, price from standard_price_fg where '$filter_from' >= `start_date` and '$filter_to' <= `end_date`) k on a.id = k.item_fg_id
                        WHERE a.type != 'RM' 
                        AND a.status = 0 
                        AND a.division_id != 'DIV02'
                        AND a.id NOT IN ($exclude_str)
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
            $totalAmountEndingStock += @$record->price * $rate * ((@$record->begin_balance + $record->qty_in) - $record->qty_out);
                                            
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

    //UJI COBA 3
    // private function getQtyMap($query, $keyField, $valueField) {
    //     $result = $this->db->query($query)->result_array();
    //     $map = [];
    //     foreach ($result as $row) {
    //         $map[$row[$keyField]] = (float)$row[$valueField];
    //     }
    //     return $map;
    // }

    // private function getSummaryWIP($filter_from, $filter_to, $filter_division, $filter_item_category = "", $filter_item_family = "", $currency = "IDR")
    // {
    //     $from_q = $this->db->escape($filter_from);
    //     $to_plus_1 = $this->db->escape($filter_to . ' 23:59:59');

    //     $divisions = $this->crud->read('divisions', [], ["id" => $filter_division]);
    //     $division_number = $divisions->number;

    //     // 1. Ambil Data Master Item
    //     $price_query = "
    //         SELECT a.id, a.number, a.name, k.price
    //         FROM item_rm a
    //         LEFT JOIN (
    //             SELECT item_fg_id, price FROM standard_price_fg 
    //             WHERE start_date <= $from_q AND end_date >= $from_q
    //         ) k ON a.id = k.item_fg_id
    //         LEFT JOIN item_categories p ON a.item_category_id = p.id 
    //         LEFT JOIN item_familys o ON a.item_family_id = o.id
    //         WHERE p.id LIKE '%$filter_item_category%' 
    //         AND a.division LIKE '%$division_number%' 
    //         AND (o.number LIKE '%$filter_item_family%' OR o.number IS NULL)
    //         ORDER BY a.number ASC";

    //     $main_data = $this->db->query($price_query)->result_array();

    //     // 2. Mapping Number -> ID untuk pencarian Child (CR-/PL-)
    //     $all_items_raw = $this->db->query("SELECT id, number FROM item_rm")->result_array();
    //     $numberToId = [];
    //     foreach ($all_items_raw as $i) { 
    //         $numberToId[$i['number']] = $i['id']; 
    //     }

    //     // --- 3. SALDO AWAL (History < $from_q) ---
        
    //     // QSS Awal (Khusus logika Parent-Child di Saldo Awal)
    //     $qss_raw = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date < $from_q AND (request_no LIKE '%SH-%' OR request_no LIKE '%PRQ-%') GROUP BY item_rm_id", 'item_rm_id', 'qty');
        
    //     $qsns     = $this->getQtyMap("SELECT a.item_rm_id, SUM(a.qty) as qty FROM issued_material_details a JOIN supply_materials b ON a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id WHERE a.created_date < $from_q AND a.request_no LIKE '%REQ-%' AND b.type = 'Issued Production' GROUP BY a.item_rm_id", 'item_rm_id', 'qty');
    //     $qtrb     = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='BPB' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qtrk     = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='KANBAN WO' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qiw      = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date < $from_q AND type LIKE '%WIP%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qm       = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date < $from_q AND request_no LIKE '%PRQ-%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qai      = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_wip WHERE transaction_type='ADJ IN' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
        
    //     $qbw      = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM bpm WHERE status='1' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qtrbpm   = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='BPM' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
        
    //     // RFG Awal (Saldo Awal OUT dari Produksi)
    //     $rfg_awal_sql = "
    //         SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) as qty FROM (
    //             SELECT b.item_fg_id, SUM(a.qty) total_qty FROM scan_item_receipts_fg a JOIN checksheets b ON b.number = a.checksheet_number WHERE b.packing_date < $from_q GROUP BY b.item_fg_id
    //             UNION ALL SELECT a.item_fg_id, SUM(a.qty) FROM scan_item_receipts_fg a WHERE a.type = 'NBFG' AND a.packing_date < $from_q GROUP BY a.item_fg_id
    //             UNION ALL SELECT item_fg_id, SUM(qty) FROM transaction_fg WHERE transaction_kind = 'IN' AND transaction_type = 'RECEIPT FG' AND request_date < $from_q GROUP BY item_fg_id
    //             UNION ALL SELECT item_fg_id, SUM(qty) FROM wip_receipts WHERE division = 'MTS' AND trans_date < $from_q GROUP BY item_fg_id
    //         ) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id";
    //     $qr_all_awal = $this->getQtyMap($rfg_awal_sql, 'item_rm_id', 'qty');

    //     $qin      = $this->getQtyMap("SELECT b.item_rm_id, SUM(b.composition * d.qty_ng) as qty FROM bom b JOIN (SELECT aa.item_fg_id, SUM(aa.qty_product) AS qty_ng FROM (SELECT DISTINCT document, item_fg_id, qty_product FROM item_ng WHERE trans_date < $from_q) aa GROUP BY aa.item_fg_id) d ON b.item_fg_id = d.item_fg_id GROUP BY b.item_rm_id", 'item_rm_id', 'qty');
    //     $qtwo     = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_wip WHERE transaction_type='ADJ OUT' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');

    //     // --- 4. PERIODE BERJALAN ($from s/d $to) ---
        
    //     $curr_in_sh   = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date >= $from_q AND created_date <= $to_plus_1 AND request_no LIKE '%SH-%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $curr_in_prq  = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date >= $from_q AND created_date <= $to_plus_1 AND request_no LIKE '%PRQ-%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $curr_in_ns   = $this->getQtyMap("SELECT a.item_rm_id, SUM(a.qty) as qty FROM issued_material_details a JOIN supply_materials b ON a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id WHERE a.created_date >= $from_q AND a.created_date <= $to_plus_1 AND a.request_no LIKE '%REQ-%' AND b.type = 'Issued Production' GROUP BY a.item_rm_id", 'item_rm_id', 'qty');
    //     $curr_in_bpb  = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='BPB' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $curr_in_kb   = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='KANBAN WO' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $curr_in_wip  = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date >= $from_q AND created_date <= $to_plus_1 AND type LIKE '%WIP%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $curr_in_adj  = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_wip WHERE transaction_type='ADJ IN' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');

    //     $curr_out_whs = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM bpm WHERE status='1' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $curr_out_bpm = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='BPM' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');
        
    //     // RFG Periode Berjalan
    //     $rfg_curr_sql = "
    //         SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) as qty FROM (
    //             SELECT b.item_fg_id, SUM(a.qty) total_qty FROM scan_item_receipts_fg a JOIN checksheets b ON b.number = a.checksheet_number WHERE b.packing_date >= $from_q AND b.packing_date <= $to_plus_1 GROUP BY b.item_fg_id
    //             UNION ALL SELECT a.item_fg_id, SUM(a.qty) FROM scan_item_receipts_fg a WHERE a.type = 'NBFG' AND a.packing_date >= $from_q AND a.packing_date <= $to_plus_1 GROUP BY a.item_fg_id
    //             UNION ALL SELECT item_fg_id, SUM(qty) FROM transaction_fg WHERE transaction_kind = 'IN' AND transaction_type = 'RECEIPT FG' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_fg_id
    //             UNION ALL SELECT item_fg_id, SUM(qty) FROM wip_receipts WHERE division = 'MTS' AND trans_date >= $from_q AND trans_date <= $to_plus_1 GROUP BY item_fg_id
    //         ) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id";
    //     $curr_out_rfg = $this->getQtyMap($rfg_curr_sql, 'item_rm_id', 'qty');

    //     // NG Periode Berjalan (Pemisahan sesuai SQL Asli)
    //     $curr_out_ng_other = $this->getQtyMap("SELECT b.item_rm_id, SUM(b.composition * d.qty_ng) as qty FROM bom b JOIN (SELECT aa.item_fg_id, SUM(aa.qty_product) AS qty_ng FROM (SELECT DISTINCT document, item_fg_id, qty_product FROM item_ng WHERE trans_date >= $from_q AND trans_date <= $to_plus_1 AND kind LIKE 'Ng Process Production') aa GROUP BY aa.item_fg_id) d ON b.item_fg_id = d.item_fg_id GROUP BY b.item_rm_id", 'item_rm_id', 'qty');
    //     $curr_out_ng_proc  = $this->getQtyMap("SELECT b.item_rm_id, SUM(b.composition * d.qty_ng) as qty FROM bom b JOIN (SELECT aa.item_fg_id, SUM(aa.qty_product) AS qty_ng FROM (SELECT DISTINCT document, item_fg_id, qty_product FROM item_ng WHERE trans_date >= $from_q AND trans_date <= $to_plus_1 AND created_by != 'PRD01') aa GROUP BY aa.item_fg_id) d ON b.item_fg_id = d.item_fg_id GROUP BY b.item_rm_id", 'item_rm_id', 'qty');
    //     $curr_out_adj      = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_wip WHERE transaction_type='ADJ OUT' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');

    //     // 5. EXCHANGE RATE
    //     $rate = 1;
    //     if ($currency == 'USD') {
    //         $q_rate = $this->db->query("SELECT middle FROM standard_exchange_rates WHERE currency_from = 'USD' AND start_date <= $from_q AND end_date >= $from_q LIMIT 1")->row();
    //         $rate = $q_rate ? (float)$q_rate->middle : 0;
    //     }

    //     // 6. FINAL CALCULATION
    //     $t = ['totalBegin'=>0, 'totalBeginAmount'=>0, 'totalIn'=>0, 'totalAmountIn'=>0, 'totalOut'=>0, 'totalAmountOut'=>0, 'totalEndingStock'=>0, 'totalAmountEndingStock'=>0];

    //     foreach ($main_data as $row) {

    //     if ($num == 'NOMOR_ITEM_YANG_SALAH') {
    //         echo "Item: $num | Begin: $begin | In: $qty_in | Out: $qty_out | End: $ending <br>";
    //     }
    //         $id  = $row['id'];
    //         $num = $row['number'];
    //         $prc = (float)$row['price'];
            
    //         // Cari ID Child
    //         $id_cr = $numberToId["CR-".$num] ?? null;
    //         $id_pl = $numberToId["PL-".$num] ?? null;

    //         // Cek apakah item ini sendiri adalah Child (CR- atau PL-) 
    //         // Menggantikan str_starts_with untuk kompatibilitas PHP 5.6 / 7.x
    //         $is_child = (substr($num, 0, 3) === 'CR-' || substr($num, 0, 3) === 'PL-');

    //         // --- SALDO AWAL ---
    //         $qss_total = ($qss_raw[$id] ?? 0);
            
    //         // Jika BUKAN item child, maka serap history milik child-nya
    //         if (!$is_child) {
    //             $qss_total += ($qss_raw[$id_cr] ?? 0) + ($qss_raw[$id_pl] ?? 0);
    //         }

    //         $begin = ($qss_total + ($qsns[$id] ?? 0) + ($qtrb[$id] ?? 0) + ($qtrk[$id] ?? 0) + ($qiw[$id] ?? 0) + ($qm[$id] ?? 0) + ($qai[$id] ?? 0))
    //                 - (($qbw[$id] ?? 0) + ($qtrbpm[$id] ?? 0) + ($qr_all_awal[$id] ?? 0) + ($qin[$id] ?? 0) + ($qtwo[$id] ?? 0));

    //         // --- QTY IN ---
    //         $qty_other = 0;
    //         if (!$is_child) {
    //             $qty_other = ($curr_in_sh[$id_cr] ?? 0) + ($curr_in_prq[$id_cr] ?? 0) + ($curr_in_sh[$id_pl] ?? 0) + ($curr_in_prq[$id_pl] ?? 0);
    //         }
            
    //         $qty_in = ($curr_in_sh[$id] ?? 0) + ($curr_in_ns[$id] ?? 0) + ($curr_in_bpb[$id] ?? 0) + ($curr_in_kb[$id] ?? 0) + ($curr_in_wip[$id] ?? 0) + ($curr_in_prq[$id] ?? 0) + ($curr_in_adj[$id] ?? 0) + $qty_other;

    //         // --- SISANYA TETAP SAMA ---
    //         $qty_out = ($curr_out_whs[$id] ?? 0) + ($curr_out_bpm[$id] ?? 0) + ($curr_out_rfg[$id] ?? 0) + ($curr_out_ng_other[$id] ?? 0) + ($curr_out_ng_proc[$id] ?? 0) + ($curr_out_adj[$id] ?? 0);
    //         $ending = ($begin + $qty_in) - $qty_out;

    //         // Akumulasi Totals
    //         $t['totalBegin']              += $begin;
    //         $t['totalBeginAmount']        += $begin * $prc * $rate;
    //         $t['totalIn']                 += $qty_in;
    //         $t['totalAmountIn']           += $qty_in * $prc * $rate;
    //         $t['totalOut']                += $qty_out;
    //         $t['totalAmountOut']          += $qty_out * $prc * $rate;
    //         $t['totalEndingStock']        += $ending;
    //         $t['totalAmountEndingStock']  += $ending * $prc * $rate;
    //     }

    //     return $t;
    // }

    //UJI COBA 1
    // private function getQtyMap($query, $keyField, $valueField) {
    //     $result = $this->db->query($query)->result_array();
    //     $map = [];
    //     foreach ($result as $row) {
    //         $map[$row[$keyField]] = (float)$row[$valueField];
    //     }
    //     return $map;
    // }

    // private function getSummaryWIP($filter_from, $filter_to, $filter_division, $filter_item_category = "", $filter_item_family = "")
    // {
    //     $filter_from_q = $this->db->escape($filter_from);
    //     $filter_to_q   = $this->db->escape($filter_to);

    //     // BEGIN
    //     // IN
    //     $qss     = $this->getQtyMap("SELECT parent.id AS item_rm_id,SUM(imd.qty) AS qty FROM item_rm parent LEFT JOIN issued_material_details imd ON imd.created_date < $filter_from_q AND (imd.request_no LIKE '%SH-%'OR imd.request_no LIKE '%PRQ-%') LEFT JOIN item_rm child ON child.id = imd.item_rm_id WHERE imd.item_rm_id = parent.id OR child.number LIKE CONCAT('CR-', parent.number) OR child.number LIKE CONCAT('PL-', parent.number) GROUP BY parent.id ORDER BY parent.number", 'item_rm_id','qty');
    //     $qsns    = $this->getQtyMap("SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as qty FROM issued_material_details a JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id WHERE a.created_date < $filter_from_q and a.request_no like '%REQ-%' AND b.type = 'Issued Production' GROUP BY a.item_rm_id", 'item_rm_id', 'qty');
    //     $qtrb    = $this->getQtyMap("SELECT item_rm_id, SUM(qty) qty_bpb FROM transaction_rm WHERE transaction_type='BPB' AND request_date < $filter_from_q GROUP BY item_rm_id", 'item_rm_id', 'qty_bpb');
    //     $qtrk    = $this->getQtyMap("SELECT item_rm_id, SUM(qty) qty_kanban FROM transaction_rm WHERE transaction_type='KANBAN WO' AND request_date < $filter_from_q GROUP BY item_rm_id", 'item_rm_id', 'qty_kanban');
    //     $qiw     = $this->getQtyMap("SELECT item_rm_id, SUM(qty) qty FROM issued_material_details WHERE created_date < $filter_from_q AND type LIKE '%WIP%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qm      = $this->getQtyMap("SELECT item_rm_id, SUM(qty) qty FROM issued_material_details WHERE created_date < $filter_from_q AND request_no LIKE '%PRQ-%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qai     = $this->getQtyMap("SELECT item_rm_id, SUM(qty) qty_adj_in FROM transaction_wip WHERE transaction_type='ADJ IN' AND request_date < $filter_from_q GROUP BY item_rm_id", 'item_rm_id', 'qty_adj_in');

    //     // OUT
    //     $qbw     = $this->getQtyMap("SELECT item_rm_id, SUM(qty) qty_bpm_whs FROM bpm WHERE status='1' AND request_date < $filter_from_q GROUP BY item_rm_id", 'item_rm_id', 'qty_bpm_whs');
    //     $qtrbpm  = $this->getQtyMap("SELECT item_rm_id, SUM(qty) qty_bpm_manual FROM transaction_rm WHERE transaction_type='BPM' AND request_date < $filter_from_q GROUP BY item_rm_id", 'item_rm_id', 'qty_bpm_manual');
    //     $qr      = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) qty_in_checksheet FROM (SELECT b.item_fg_id, SUM(a.qty) total_qty FROM scan_item_receipts_fg a JOIN checksheets b ON b.number = a.checksheet_number WHERE b.packing_date < $filter_from_q GROUP BY b.item_fg_id ) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id", 'item_rm_id', 'qty_in_checksheet');
    //     $qrnbfg  = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) qty_in_no_checksheet FROM (SELECT a.item_fg_id, SUM(a.qty) total_qty FROM scan_item_receipts_fg a WHERE a.type = 'NBFG' AND a.packing_date < $filter_from_q GROUP BY a.item_fg_id) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id", 'item_rm_id', 'qty_in_no_checksheet');
    //     $qtrf    = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) initial_in FROM (SELECT item_fg_id, SUM(qty) total_qty FROM transaction_fg WHERE transaction_kind = 'IN' AND transaction_type = 'RECEIPT FG' AND request_date < $filter_from_q GROUP BY item_fg_id) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id ", 'item_rm_id', 'initial_in');
    //     $qwr     = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) qty_in_wip_receipt FROM (SELECT item_fg_id, SUM(qty) total_qty FROM wip_receipts WHERE division = 'MTS' AND trans_date < $filter_from_q GROUP BY item_fg_id) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id", 'item_rm_id', 'qty_in_wip_receipt');
    //     $qin     = $this->getQtyMap("SELECT b.item_rm_id,SUM(b.composition * COALESCE(d.qty_ng,0)) AS qty_ng FROM bom b JOIN (SELECT a.id AS item_fg_id FROM item_fg a) fg ON b.item_fg_id = fg.item_fg_id LEFT JOIN (SELECT aa.item_fg_id, SUM(aa.qty_product) AS qty_ng FROM (SELECT DISTINCT document, item_fg_id, qty_product FROM item_ng WHERE trans_date < $filter_from_q) aa GROUP BY aa.item_fg_id) d ON b.item_fg_id = d.item_fg_id GROUP BY b.item_rm_id",'item_rm_id', 'qty_ng');
    //     $qtwo    = $this->getQtyMap("SELECT item_rm_id, SUM(qty) qty_adj_out FROM transaction_wip WHERE transaction_type='ADJ OUT' AND request_date < $filter_from_q GROUP BY item_rm_id", 'item_rm_id', 'qty_adj_out');

    //     // IN------------
    //     // SUPPLY------------------------------------------------------------------------------------------
    //     $query_supply_sheet = "SELECT 
    //         parent.id AS item_rm_id,
    //         parent.number AS parent_number,
    //         parent.name AS parent_name,

    //         -- Qty utama
    //         COALESCE((
    //             SELECT SUM(qty)
    //             FROM issued_material_details
    //             WHERE item_rm_id = parent.id
    //             AND created_date >= '$filter_from'
    //             AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //             AND request_no LIKE '%SH-%'
    //         ), 0) AS qty,

    //         -- Qty other (CR- / PL- berdasarkan number parent)
    //         COALESCE((
    //             SELECT SUM(imd.qty)
    //             FROM issued_material_details imd
    //             JOIN item_rm child ON child.id = imd.item_rm_id
    //             WHERE (
    //                     child.number LIKE CONCAT('CR-', parent.number)
    //                 OR child.number LIKE CONCAT('PL-', parent.number)
    //             )
    //             AND imd.created_date >= '$filter_from'
    //             AND imd.created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //             AND (
    //                 imd.request_no LIKE '%SH-%'
    //                 OR imd.request_no LIKE '%PRQ-%'
    //             )
    //         ), 0) AS qty_other

    //     FROM item_rm parent
    //     ORDER BY parent.number";

    //     $query_supply_non_sheet = "SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as qty 
    //     FROM issued_material_details a
    //     JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
    //     WHERE a.created_date >= '$filter_from' AND a.created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.request_no like '%REQ-%' AND b.type = 'Issued Production'
    //     GROUP BY a.item_rm_id";

    //     $query_trans_rm_bpb = "SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty_bpb 
    //     FROM transaction_rm 
    //     WHERE transaction_type='BPB' AND request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //     GROUP BY item_rm_id";

    //     $query_trans_rm_kanban = "SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty_kanban 
    //     FROM transaction_rm 
    //     WHERE transaction_type='KANBAN WO' AND request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //     GROUP BY item_rm_id";

    //     $query_issued_wip = "SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty 
    //     FROM issued_material_details 
    //     WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) AND `type` LIKE '%WIP%' 
    //     GROUP BY item_rm_id";
    //     // MATREQ---------------------------------------------------------------------------------------------
    //     $query_matreq = "SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty 
    //     FROM issued_material_details 
    //     WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and request_no like '%PRQ-%' 
    //     GROUP BY item_rm_id";
    //     // ADJIN---------------------------------------------------------------------------------------------
    //     $query_adj_in = "SELECT item_rm_id, sum(qty) as qty_adj_in 
    //     FROM transaction_wip 
    //     WHERE transaction_type='ADJ IN' AND request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //     GROUP BY item_rm_id";
    //     // OUT---------------
    //     // RETURN--------------------------------------------------------------------------------------------
    //     $query_bpm_whs = "SELECT item_rm_id, sum(qty) as qty_bpm_whs 
    //     FROM bpm
    //     WHERE status='1' and request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //     GROUP BY item_rm_id";

    //     $query_trans_rm_bpm = "SELECT item_rm_id, sum(qty) as qty_bpm_manual 
    //     FROM transaction_rm 
    //     WHERE transaction_type='BPM' and request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //     GROUP BY item_rm_id";
    //     // RFG-----------------------------------------------------------------------------------------------
    //     $query_receipt = "SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) AS qty_in_checksheet
    //     FROM (
    //         SELECT b.item_fg_id, SUM(a.qty) AS total_qty
    //         FROM scan_item_receipts_fg a
    //         JOIN checksheets b ON b.number = a.checksheet_number
    //         WHERE b.packing_date >= '$filter_from' AND b.packing_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //         GROUP BY b.item_fg_id
    //     ) t
    //     JOIN bom ON bom.item_fg_id = t.item_fg_id
    //     GROUP BY bom.item_rm_id
    //     ";

    //     $query_receipt_nbfg = "SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) AS qty_in_no_checksheet
    //     FROM (
    //         SELECT a.item_fg_id, SUM(a.qty) AS total_qty
    //         FROM scan_item_receipts_fg a
    //         WHERE a.type = 'NBFG' AND a.packing_date >= '$filter_from' AND a.packing_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //         GROUP BY a.item_fg_id
    //     ) t
    //     JOIN bom ON bom.item_fg_id = t.item_fg_id
    //     GROUP BY bom.item_rm_id";

    //     $query_trans_receipt_fg = "SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) AS initial_in
    //     FROM (
    //         SELECT item_fg_id, SUM(qty) AS total_qty
    //         FROM transaction_fg
    //         WHERE transaction_kind = 'IN'
    //         AND transaction_type = 'RECEIPT FG'
    //         AND request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //         GROUP BY item_fg_id
    //     ) t
    //     JOIN bom ON bom.item_fg_id = t.item_fg_id
    //     GROUP BY bom.item_rm_id";

    //     $query_wip_receipt = "SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) AS qty_in_wip_receipt
    //     FROM (
    //         SELECT item_fg_id, SUM(qty) AS total_qty
    //         FROM wip_receipts
    //         WHERE division = 'MTS'
    //         AND trans_date >= '$filter_from' AND trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //         GROUP BY item_fg_id
    //     ) t
    //     JOIN bom ON bom.item_fg_id = t.item_fg_id
    //     GROUP BY bom.item_rm_id";
    //     // NG-----------------------------------------------------------------------------------------------
    //     $query_item_ng_other = "
    //         SELECT 
    //             b.item_rm_id,
    //             SUM(b.composition * COALESCE(d.qty_ng,0)) AS qty_ng
    //         FROM bom b
    //         JOIN (
    //             SELECT a.id AS item_fg_id
    //             FROM item_fg a
    //         ) fg ON b.item_fg_id = fg.item_fg_id
    //         LEFT JOIN (
    //             SELECT aa.item_fg_id, SUM(aa.qty_product) AS qty_ng 
    //             FROM (
    //                 SELECT DISTINCT document, item_fg_id, qty_product 
    //                 FROM item_ng 
    //                 WHERE trans_date >= '$filter_from'
    //                 AND trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //                 AND created_by = 'PRD01'
    //             ) aa 
    //             GROUP BY aa.item_fg_id
    //         ) d ON b.item_fg_id = d.item_fg_id
    //         GROUP BY b.item_rm_id
    //     ";

    //     $query_item_ng_process = "
    //         SELECT 
    //             b.item_rm_id,
    //             SUM(b.composition * COALESCE(d.qty_ng,0)) AS qty_ng
    //         FROM bom b
    //         JOIN (
    //             SELECT a.id AS item_fg_id
    //             FROM item_fg a
    //         ) fg ON b.item_fg_id = fg.item_fg_id
    //         LEFT JOIN (
    //             SELECT aa.item_fg_id, SUM(aa.qty_product) AS qty_ng 
    //             FROM (
    //                 SELECT DISTINCT document, item_fg_id, qty_product 
    //                 FROM item_ng 
    //                 WHERE trans_date >= '$filter_from'
    //                 AND trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //                 AND created_by != 'PRD01'
    //             ) aa 
    //             GROUP BY aa.item_fg_id
    //         ) d ON b.item_fg_id = d.item_fg_id
    //         GROUP BY b.item_rm_id
    //     ";
    //     // ADJ OUT-----------------------------------------------------------------------------------------------
    //     $query_trans_wip_out = "SELECT item_rm_id, sum(qty) as qty_adj_out 
    //     FROM transaction_wip 
    //     WHERE transaction_type='ADJ OUT' AND request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
    //     GROUP BY item_rm_id";

    //     $main_query = "
    //         SELECT 
    //         a.id,
    //         a.number, 
    //         a.name, 
    //         a.division, 
    //         a.uom,
    //         o.name AS prodfam, 
    //         p.name AS category_name,

    //         (COALESCE(qss.qty,0) + COALESCE(qsns.qty,0) + COALESCE(qtrb.qty_bpb,0) + COALESCE(qtrk.qty_kanban,0) + COALESCE(qiw.qty,0) + 
    //          COALESCE(qm.qty,0) + COALESCE(qai.qty_adj_in,0) + COALESCE(qss.qty_other,0)) AS qty_in,

    //         (COALESCE(qr.qty_in_checksheet,0) + COALESCE(qrnbfg.qty_in_no_checksheet,0) + COALESCE(qtrf.initial_in,0) + COALESCE(qwr.qty_in_wip_receipt,0) + 
    //          COALESCE(qbw.qty_bpm_whs,0) + COALESCE(qtrbpm.qty_bpm_manual,0) + COALESCE(qino.qty_ng,0) + COALESCE(qinp.qty_ng,0) + COALESCE(qtwo.qty_adj_out,0)) AS qty_out

    //     FROM item_rm a
    //     LEFT JOIN ($query_supply_sheet) qss        ON a.id = qss.item_rm_id
    //     LEFT JOIN ($query_supply_non_sheet) qsns   ON a.id = qsns.item_rm_id
    //     LEFT JOIN ($query_trans_rm_bpb) qtrb       ON a.id = qtrb.item_rm_id
    //     LEFT JOIN ($query_trans_rm_kanban) qtrk    ON a.id = qtrk.item_rm_id
    //     LEFT JOIN ($query_issued_wip) qiw          ON a.id = qiw.item_rm_id
    //     LEFT JOIN ($query_matreq) qm               ON a.id = qm.item_rm_id
    //     LEFT JOIN ($query_adj_in) qai              ON a.id = qai.item_rm_id
    //     LEFT JOIN ($query_bpm_whs) qbw             ON a.id = qbw.item_rm_id
    //     LEFT JOIN ($query_trans_rm_bpm) qtrbpm     ON a.id = qtrbpm.item_rm_id
    //     LEFT JOIN ($query_receipt) qr              ON a.id = qr.item_rm_id
    //     LEFT JOIN ($query_receipt_nbfg) qrnbfg     ON a.id = qrnbfg.item_rm_id
    //     LEFT JOIN ($query_trans_receipt_fg) qtrf   ON a.id = qtrf.item_rm_id
    //     LEFT JOIN ($query_wip_receipt) qwr         ON a.id = qwr.item_rm_id
    //     LEFT JOIN ($query_item_ng_other) qino      ON a.id = qino.item_rm_id
    //     LEFT JOIN ($query_item_ng_process) qinp    ON a.id = qinp.item_rm_id
    //     LEFT JOIN ($query_trans_wip_out) qtwo      ON a.id = qtwo.item_rm_id
    //     LEFT JOIN item_familys o                   ON a.item_family_id = o.id
    //     LEFT JOIN item_categories p                ON a.item_category_id = p.id

    //     WHERE p.id LIKE '%$filter_item_category%'
    //     AND o.number LIKE '%$filter_item_family%'
    //     AND a.division LIKE '%$filter_division%'
    //     ORDER BY o.name DESC, p.name DESC, a.number";

    //     // Eksekusi query
    //     $data = $this->db->query($main_query)->result_array();
    //     foreach ($data as &$row) {
    //         $id = $row['id'];
    //         $row['begin_stock'] =
    //             ($qss[$id]     ?? 0) +
    //             ($qsns[$id]    ?? 0) +
    //             ($qtrb[$id]    ?? 0) +
    //             ($qtrk[$id]    ?? 0) +
    //             ($qiw[$id]     ?? 0) +
    //             ($qm[$id]      ?? 0) +
    //             ($qai[$id]     ?? 0) -
    //             ($qbw[$id]     ?? 0) -
    //             ($qtrbpm[$id]  ?? 0) -
    //             ($qr[$id]      ?? 0) -
    //             ($qrnbfg[$id]  ?? 0) -
    //             ($qtrf[$id]    ?? 0) -
    //             ($qwr[$id]     ?? 0) -
    //             ($qin[$id]     ?? 0) -
    //             ($qtwo[$id]    ?? 0);
    //     }

    //     $totalBeginStock = 0;
    //     $totalBeginAmount = 0;
    //     $totalIn = 0;
    //     $totalAmountIn = 0;
    //     $totalOut = 0;
    //     $totalAmountOut = 0;
    //     $totalEndingStock = 0;
    //     $totalAmountEndingStock = 0;

    //     // Pastikan index array normal
    //     $data = array_values($data);

    //     // Loop dengan nomor urut
    //     foreach ($data as $index => $record) {
    //         $item_fg_id = $record['id'];
    //         // $receipt_date = @$record->receipt_date;
    //         $rate = 1;

    //         if ($currency == 'USD') {
    //             if (empty($receipt_date)) {
    //                 $rate = 0;
    //             } else {
    //                 $this->db->where('currency_from', 'USD');
    //                 $this->db->where('start_date <=', $receipt_date);
    //                 $this->db->where('end_date >=', $receipt_date);
    //                 $query = $this->db->get('standard_exchange_rates');

    //                 if ($query->num_rows() > 0) {
    //                     $rate = $query->row()->middle;
    //                 }
    //             }
    //         }
    //         $totalBeginStock += @$record['begin_stock'];
    //         $totalBeginAmount += @$record['price'] * $rate * @$record['begin_stock'];
    //         $totalIn += @$record['qty_in'];
    //         $totalAmountIn += @$record['price'] * $rate * @$record['qty_in'];
    //         $totalOut += @$record['qty_out'];
    //         $totalAmountOut += @$record['price'] * $rate * @$record['qty_out'];
    //         $totalEndingStock += @(@$record['begin_stock'] + $record['qty_in']) - $record['qty_out'];
    //         $totalAmountEndingStock += @$record['price'] * $rate * ((@$record['begin_stock'] + $record['qty_in']) - $record['qty_out']);
                                            
    //     }

    //     return [
    //         'totalBegin' => $totalBeginStock ?? 0,
    //         'totalBeginAmount' => $totalBeginAmount ?? 0,
    //         'totalIn' => $totalIn ?? 0,
    //         'totalAmountIn' => $totalAmountIn ?? 0,
    //         'totalOut' => $totalOut ?? 0,
    //         'totalAmountOut' => $totalAmountOut ?? 0,
    //         'totalEndingStock' => $totalEndingStock ?? 0,
    //         'totalAmountEndingStock' => $totalAmountEndingStock ?? 0
    //     ];
    // }

    //UJI COBA 2
    // private function getQtyMap($query, $keyField, $valueField) {
    //     $result = $this->db->query($query)->result_array();
    //     $map = [];
    //     foreach ($result as $row) {
    //         $map[$row[$keyField]] = (float)$row[$valueField];
    //     }
    //     return $map;
    // }

    // private function getSummaryWIP($filter_from, $filter_to, $filter_division, $filter_item_category = "", $filter_item_family = "", $currency = "IDR")
    // {
    //     $from_q = $this->db->escape($filter_from);
    //     $to_q   = $this->db->escape($filter_to);
    //     $to_plus_1 = $this->db->escape($filter_to . ' 23:59:59');

    //     // 1. MEMORY MAPPING: Ambil data Item RM & Price (Filter Price hanya From & To)
    //     $price_query = "
    //         SELECT a.id, a.number, a.division, k.price
    //         FROM item_rm a
    //         LEFT JOIN (
    //             SELECT item_fg_id, price 
    //             FROM standard_price_fg 
    //             WHERE start_date <= $from_q AND end_date >= $to_q
    //         ) k ON a.id = k.item_fg_id
    //         LEFT JOIN item_categories p ON a.item_category_id = p.id 
    //         LEFT JOIN item_familys o ON a.item_family_id = o.id
    //         WHERE p.id LIKE '%$filter_item_category%' 
    //         AND a.division LIKE '%$filter_division%' 
    //         AND (o.number LIKE '%$filter_item_family%' OR o.number IS NULL)";

    //     $main_data = $this->db->query($price_query)->result_array();

    //     // Mapping untuk mempermudah pencarian Child (CR-/PL-) dan Harga
    //     $numberToId = [];
    //     $itemPrices = [];
    //     foreach ($main_data as $row) {
    //         $numberToId[$row['number']] = $row['id'];
    //         $itemPrices[$row['id']] = (float)$row['price'];
    //     }

    //     // 2. SALDO AWAL (Data < $filter_from)
    //     $qss_raw = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date < $from_q AND (request_no LIKE '%SH-%' OR request_no LIKE '%PRQ-%') GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qsns   = $this->getQtyMap("SELECT a.item_rm_id, SUM(a.qty) as qty FROM issued_material_details a JOIN supply_materials b ON a.request_no = b.request_no WHERE a.created_date < $from_q AND a.request_no LIKE '%REQ-%' AND b.type = 'Issued Production' GROUP BY a.item_rm_id", 'item_rm_id', 'qty');
    //     $qtrb   = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='BPB' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qtrk   = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='KANBAN WO' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qiw    = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date < $from_q AND type LIKE '%WIP%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qm     = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date < $from_q AND request_no LIKE '%PRQ-%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qai    = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_wip WHERE transaction_type='ADJ IN' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
        
    //     $qbw    = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM bpm WHERE status='1' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qtrbpm = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='BPM' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $qr     = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) as qty FROM (SELECT b.item_fg_id, SUM(a.qty) total_qty FROM scan_item_receipts_fg a JOIN checksheets b ON b.number = a.checksheet_number WHERE b.packing_date < $from_q GROUP BY b.item_fg_id) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id", 'item_rm_id', 'qty');
    //     $qrnbfg = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) as qty FROM (SELECT a.item_fg_id, SUM(a.qty) total_qty FROM scan_item_receipts_fg a WHERE a.type = 'NBFG' AND a.packing_date < $from_q GROUP BY a.item_fg_id) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id", 'item_rm_id', 'qty');
    //     $qtrf   = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) as qty FROM (SELECT item_fg_id, SUM(qty) total_qty FROM transaction_fg WHERE transaction_kind = 'IN' AND transaction_type = 'RECEIPT FG' AND request_date < $from_q GROUP BY item_fg_id) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id", 'item_rm_id', 'qty');
    //     $qwr    = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) as qty FROM (SELECT item_fg_id, SUM(qty) total_qty FROM wip_receipts WHERE division = 'MTS' AND trans_date < $from_q GROUP BY item_fg_id) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id", 'item_rm_id', 'qty');
    //     $qin    = $this->getQtyMap("SELECT b.item_rm_id, SUM(b.composition * d.qty_p) as qty FROM bom b JOIN (SELECT DISTINCT item_fg_id, qty_product as qty_p FROM item_ng WHERE trans_date < $from_q) d ON b.item_fg_id = d.item_fg_id GROUP BY b.item_rm_id", 'item_rm_id', 'qty');
    //     $qtwo   = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_wip WHERE transaction_type='ADJ OUT' AND request_date < $from_q GROUP BY item_rm_id", 'item_rm_id', 'qty');

    //     // 3. PERIODE BERJALAN (IN & OUT)
    //     $curr_in_sh   = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date >= $from_q AND created_date <= $to_plus_1 AND request_no LIKE '%SH-%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $curr_in_prq  = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date >= $from_q AND created_date <= $to_plus_1 AND request_no LIKE '%PRQ-%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $curr_in_ns   = $this->getQtyMap("SELECT a.item_rm_id, SUM(a.qty) as qty FROM issued_material_details a JOIN supply_materials b ON a.request_no = b.request_no WHERE a.created_date >= $from_q AND a.created_date <= $to_plus_1 AND b.type = 'Issued Production' GROUP BY a.item_rm_id", 'item_rm_id', 'qty');
    //     $curr_in_bpb  = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='BPB' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $curr_in_kb   = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='KANBAN WO' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $curr_in_wip  = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date >= $from_q AND created_date <= $to_plus_1 AND type LIKE '%WIP%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $curr_in_adj  = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_wip WHERE transaction_type='ADJ IN' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');

    //     $curr_out_whs = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM bpm WHERE status='1' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $curr_out_bpm = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type='BPM' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');
    //     $curr_out_cs  = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) as qty FROM (SELECT b.item_fg_id, SUM(a.qty) total_qty FROM scan_item_receipts_fg a JOIN checksheets b ON b.number = a.checksheet_number WHERE b.packing_date >= $from_q AND b.packing_date <= $to_plus_1 GROUP BY b.item_fg_id) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id", 'item_rm_id', 'qty');
    //     $curr_out_nb  = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) as qty FROM (SELECT a.item_fg_id, SUM(a.qty) total_qty FROM scan_item_receipts_fg a WHERE a.type = 'NBFG' AND a.packing_date >= $from_q AND a.packing_date <= $to_plus_1 GROUP BY a.item_fg_id) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id", 'item_rm_id', 'qty');
    //     $curr_out_fg  = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) as qty FROM (SELECT item_fg_id, SUM(qty) total_qty FROM transaction_fg WHERE transaction_kind = 'IN' AND transaction_type = 'RECEIPT FG' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_fg_id) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id", 'item_rm_id', 'qty');
    //     $curr_out_wr  = $this->getQtyMap("SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) as qty FROM (SELECT item_fg_id, SUM(qty) total_qty FROM wip_receipts WHERE division = 'MTS' AND trans_date >= $from_q AND trans_date <= $to_plus_1 GROUP BY item_fg_id) t JOIN bom ON bom.item_fg_id = t.item_fg_id GROUP BY bom.item_rm_id", 'item_rm_id', 'qty');
    //     $curr_out_ng1 = $this->getQtyMap("SELECT b.item_rm_id, SUM(b.composition * d.qty_p) as qty FROM bom b JOIN (SELECT DISTINCT item_fg_id, qty_product as qty_p FROM item_ng WHERE trans_date >= $from_q AND trans_date <= $to_plus_1 AND kind LIKE 'Ng Process Production') d ON b.item_fg_id = d.item_fg_id GROUP BY b.item_rm_id", 'item_rm_id', 'qty');
    //     $curr_out_ng2 = $this->getQtyMap("SELECT b.item_rm_id, SUM(b.composition * d.qty_p) as qty FROM bom b JOIN (SELECT DISTINCT item_fg_id, qty_product as qty_p FROM item_ng WHERE trans_date >= $from_q AND trans_date <= $to_plus_1 AND created_by != 'PRD01') d ON b.item_fg_id = d.item_fg_id GROUP BY b.item_rm_id", 'item_rm_id', 'qty');
    //     $curr_out_adj = $this->getQtyMap("SELECT item_rm_id, SUM(qty) as qty FROM transaction_wip WHERE transaction_type='ADJ OUT' AND request_date >= $from_q AND request_date <= $to_plus_1 GROUP BY item_rm_id", 'item_rm_id', 'qty');

    //     // 4. EXCHANGE RATE (Sekali jalan)
    //     $rate = 1;
    //     if ($currency == 'USD') {
    //         $q_rate = $this->db->query("SELECT middle FROM standard_exchange_rates WHERE currency_from = 'USD' AND start_date <= $from_q AND end_date >= $from_q LIMIT 1")->row();
    //         $rate = $q_rate ? (float)$q_rate->middle : 0;
    //     }

    //     // 5. FINAL CALCULATION
    //     $t = ['totalBegin'=>0, 'totalBeginAmount'=>0, 'totalIn'=>0, 'totalAmountIn'=>0, 'totalOut'=>0, 'totalAmountOut'=>0, 'totalEndingStock'=>0, 'totalAmountEndingStock'=>0];

    //     foreach ($main_data as $row) {
    //         $id = $row['id'];
    //         $num = $row['number'];
    //         $prc = (float)$row['price'];
            
    //         // Logika Parent-Child (CR-/PL-)
    //         $id_cr = $numberToId["CR-".$num] ?? null;
    //         $id_pl = $numberToId["PL-".$num] ?? null;

    //         // --- SALDO AWAL (Historical) ---
    //         $qss_b = ($qss_raw[$id] ?? 0) + ($qss_raw[$id_cr] ?? 0) + ($qss_raw[$id_pl] ?? 0);
    //         $begin = ($qss_b + ($qsns[$id] ?? 0) + ($qtrb[$id] ?? 0) + ($qtrk[$id] ?? 0) + ($qiw[$id] ?? 0) + ($qm[$id] ?? 0) + ($qai[$id] ?? 0))
    //             - (($qbw[$id] ?? 0) + ($qtrbpm[$id] ?? 0) + ($qr[$id] ?? 0) + ($qrnbfg[$id] ?? 0) + ($qtrf[$id] ?? 0) + ($qwr[$id] ?? 0) + ($qin[$id] ?? 0) + ($qtwo[$id] ?? 0));

    //         // --- QTY IN (Current Period) ---
    //         $q_in_sh = ($curr_in_sh[$id] ?? 0);
    //         $q_in_oth = ($curr_in_sh[$id_cr] ?? 0) + ($curr_in_prq[$id_cr] ?? 0) + ($curr_in_sh[$id_pl] ?? 0) + ($curr_in_prq[$id_pl] ?? 0);
    //         $qty_in = $q_in_sh + $q_in_oth + ($curr_in_prq[$id] ?? 0) + ($curr_in_ns[$id] ?? 0) + ($curr_in_bpb[$id] ?? 0) + ($curr_in_kb[$id] ?? 0) + ($curr_in_wip[$id] ?? 0) + ($curr_in_adj[$id] ?? 0);

    //         // --- QTY OUT (Current Period) ---
    //         $qty_out = ($curr_out_whs[$id] ?? 0) + ($curr_out_bpm[$id] ?? 0) + ($curr_out_cs[$id] ?? 0) + ($curr_out_nb[$id] ?? 0) + ($curr_out_fg[$id] ?? 0) + ($curr_out_wr[$id] ?? 0) + ($curr_out_ng1[$id] ?? 0) + ($curr_out_ng2[$id] ?? 0) + ($curr_out_adj[$id] ?? 0);

    //         $ending = ($begin + $qty_in) - $qty_out;

    //         // Akumulasi Totals
    //         $t['totalBegin'] += $begin;
    //         $t['totalBeginAmount'] += $begin * $prc * $rate;
    //         $t['totalIn'] += $qty_in;
    //         $t['totalAmountIn'] += $qty_in * $prc * $rate;
    //         $t['totalOut'] += $qty_out;
    //         $t['totalAmountOut'] += $qty_out * $prc * $rate;
    //         $t['totalEndingStock'] += $ending;
    //         $t['totalAmountEndingStock'] += $ending * $prc * $rate;
    //     }

    //     return $t;
    // }
}
