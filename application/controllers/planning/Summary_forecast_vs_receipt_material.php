<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Summary_forecast_vs_receipt_material extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/summary_forecast_vs_receipt_material');
        } else {
            redirect('error_access');
        }
    }

    //GET PERIOD
    public function readPeriod($select)
    {
        if ($select == "month") {
            $month = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
            foreach ($month as $key => $value) {
                $months[] = array("id" => $key, "name" => $value);
            }

            echo json_encode($months);
        } else if ($select == "year") {
            $year_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
            $year_now = date('Y', strtotime('+1 year', strtotime(date('Y'))));
            for ($i = $year_now; $i >= $year_before; $i--) {
                $years[] = array("id" => $i, "name" => $i);
            }

            echo json_encode($years);
        } else {
            show_error("Cannot Process your request");
        }
    }

    //GET PERIOD LISTS
    public function readPeriodLists()
    {
        $p_month = $this->input->post('p_month');
        $p_year = $this->input->post('p_year');
        $p_date_start = date("Y-m-d", strtotime($p_year . "-" . $p_month . "-01"));
        $p_date_to = date('Y-m-d', strtotime('+11 month', strtotime($p_date_start)));

        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
        }

        echo json_encode($dates);
    }

    public function readNotFg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM item_familys WHERE number IN ('CP','MB','VG') AND name LIKE '%$post%'");
        echo json_encode($send);
    }

    // public function print($option = "")
    // {
    //     if ($option == "excel") {
    //         $format  = date("Ymd");
    //         header("Content-type: application/vnd-ms-excel");
    //         header("Content-Disposition: attachment; filename=summary_forecast_vs_receipt_material_$format.xls");
    //     }

    //     // ==================== 1. FILTER & CONFIG ====================
    //     $filter_period_year = (int) base64_decode($this->input->get("filter_period_year"));
    //     $filter_month = (int)base64_decode($this->input->get("filter_month"));
    //     $filter_item_rm = $this->input->get("filter_item_rm"); 
    //     $filter_status = $this->input->get("filter_status");
    //     $filter_product_family = $this->input->get("filter_product_family");
    //     $config = $this->db->get("config")->row();

    //     $targets = [['year' => $filter_period_year, 'month' => $filter_month]];

    //     // ==================== 2. LOGIKA FALLBACK ====================
    //     $firstTarget = $targets[0];
    //     $earliest = $firstTarget;
    //     for ($k = 0; $k < 6; $k++) {
    //         $earliest_ts = strtotime($earliest['year'] . '-' . $earliest['month'] . '-01');
    //         $earliest_ts = strtotime('-1 month', $earliest_ts);
    //         $earliest = ['year' => (int)date('Y', $earliest_ts), 'month' => (int)date('n', $earliest_ts)];
    //     }

    //     $monthsToFetch = [];
    //     $cur = strtotime($earliest['year'] . '-' . $earliest['month'] . '-01');
    //     $end = strtotime($targets[0]['year'] . '-' . $targets[0]['month'] . '-01');
    //     while ($cur <= $end) {
    //         $monthsToFetch[] = ['year' => (int)date('Y', $cur), 'month' => (int)date('n', $cur)];
    //         $cur = strtotime('+1 month', $cur);
    //     }

    //     $conds_noalias = [];
    //     $conds_falias  = [];
    //     foreach ($monthsToFetch as $mm) {
    //         $y = (int)$mm['year']; $m = (int)$mm['month'];
    //         $conds_noalias[] = "(p_year = $y AND p_month = $m)";
    //         $conds_falias[]  = "(f.p_year = $y AND f.p_month = $m)";
    //     }
    //     $where_months_sql_noalias = implode(" OR ", $conds_noalias);
    //     $where_months_sql_falias  = implode(" OR ", $conds_falias);

    //     // ==================== 3. FETCH DATA FORECAST (FG) ====================
    //     $sql_forecast = "
    //         SELECT f.*
    //         FROM forecasts f
    //         JOIN (
    //             SELECT item_fg_id, customer_id, p_year, p_month, MAX(revision) AS max_rev
    //             FROM forecasts
    //             WHERE deleted = 0 AND ({$where_months_sql_noalias})
    //             GROUP BY item_fg_id, customer_id, p_year, p_month
    //         ) latest ON latest.item_fg_id = f.item_fg_id
    //                 AND latest.customer_id = f.customer_id
    //                 AND latest.p_year = f.p_year
    //                 AND latest.p_month = f.p_month
    //                 AND latest.max_rev = f.revision
    //         WHERE f.deleted = 0 AND ({$where_months_sql_falias})
    //     ";
    //     $forecast_rows = $this->db->query($sql_forecast)->result_array();

    //     $map_fg = [];
    //     foreach ($forecast_rows as $r) {
    //         $map_fg[$r['item_fg_id']][$r['customer_id']][(int)$r['p_year']][(int)$r['p_month']] = $r;
    //     }

    //     // ==================== 4. FETCH DATA BOM & MATERIAL ====================
    //     $this->db->select('b.item_fg_id, b.item_rm_id, rm.number as rm_number, rm.name as rm_name, fg.number as fg_number, if.name as fg_name');
    //     $this->db->from('bom b');
    //     $this->db->join('item_rm rm', 'b.item_rm_id = rm.id');
    //     $this->db->join('item_fg fg', 'b.item_fg_id = fg.id');
    //     $this->db->join('item_familys if', 'if.id = rm.item_family_id');
    //     $this->db->where('rm.status', 0);
    //     $this->db->where('rm.division', 'INJ');
    //     $this->db->where_in('rm.item_family_id', ['P01', 'P02', 'P06']);
    //     if ($filter_item_rm) $this->db->where('rm.id', $filter_item_rm);
    //     if ($filter_product_family) $this->db->where('rm.item_family_id', $filter_product_family);
    //     $bom_list = $this->db->get()->result_array();

    //     // ==================== 5. FETCH DATA RECEIPT PO ====================
    //     $this->db->select('item_rm_id, SUM(qty_receipt2) as total_receipt');
    //     $this->db->from('purchase_order_receipts');
    //     $this->db->where('YEAR(receipt_date)', $filter_period_year);
    //     $this->db->where('MONTH(receipt_date)', $filter_month);
    //     $this->db->group_by('item_rm_id');
    //     $receipt_data = $this->db->get()->result_array();

    //     $map_receipt = [];
    //     foreach ($receipt_data as $rd) {
    //         $map_receipt[$rd['item_rm_id']] = $rd['total_receipt'];
    //     }

    //     // ==================== 6. PROCESSING & MAPPING FINAL DATA ====================
    //     $temp_rm_data = [];
    //     foreach ($bom_list as $bom) {
    //         $fg_id = $bom['item_fg_id'];
    //         $rm_id = $bom['item_rm_id'];

    //         if (!isset($temp_rm_data[$rm_id])) {
    //             $temp_rm_data[$rm_id] = [
    //                 'rm_number' => $bom['rm_number'],
    //                 'rm_name'   => $bom['rm_name'],
    //                 'fg_name'   => $bom['fg_name'],
    //                 'forecast'  => 0,
    //                 'receipt'   => (float)($map_receipt[$rm_id] ?? 0)
    //             ];
    //         }

    //         $current_fg_forecast = 0;
    //         if (isset($map_fg[$fg_id])) {
    //             foreach ($map_fg[$fg_id] as $custId => $years) {
    //                 $value = 0; $final_value_found = false;
    //                 $py = $filter_period_year; $pm = $filter_month;

    //                 if (isset($map_fg[$fg_id][$custId][$py][$pm])) {
    //                     $raw_v0 = $map_fg[$fg_id][$custId][$py][$pm]['month_1'];
    //                     if ($raw_v0 !== null && $raw_v0 !== '') { $value = floatval($raw_v0); $final_value_found = true; }
    //                 }
    //                 if (!$final_value_found) {
    //                     for ($k = 1; $k <= 3; $k++) {
    //                         $check_ts = strtotime("-{$k} month", strtotime("$py-$pm-01"));
    //                         $cy = (int)date('Y', $check_ts); $cm = (int)date('n', $check_ts);
    //                         if (isset($map_fg[$fg_id][$custId][$cy][$cm])) {
    //                             $raw_vv = $map_fg[$fg_id][$custId][$cy][$cm]['month_' . ($k + 1)] ?? null;
    //                             if ($raw_vv !== null && $raw_vv !== '') { $value = floatval($raw_vv); $final_value_found = true; break; }
    //                         }
    //                     }
    //                 }
    //                 $current_fg_forecast += $value;
    //             }
    //         }
    //         $temp_rm_data[$rm_id]['forecast'] += $current_fg_forecast;
    //     }

    //     // Kalkulasi 
    //     $final_records = [];
    //     foreach ($temp_rm_data as $key => $row) {
    //         $fc = $row['forecast'];
    //         $rc = $row['receipt'];
            
    //         $tol_minus = $fc - (0.10 * $fc);
    //         $tol_plus  = $fc + (0.10 * $fc);
    //         $diff      = $rc - $fc;
            
    //         $status = "OK";
    //         if ($rc > $tol_plus) { 
    //             $status = "PLUS"; 
    //         } elseif ($rc < $tol_minus) { 
    //             $status = "MINUS"; 
    //         }

    //         if ($filter_status && $filter_status != "" && $status != $filter_status) {
    //             continue; 
    //         }

    //         $row_data = $row;
    //         $row_data['tol_minus'] = $tol_minus;
    //         $row_data['tol_plus']  = $tol_plus;
    //         $row_data['diff']      = $diff;
    //         $row_data['status']    = $status;

    //         $final_records[] = $row_data;
    //     }

    //     // ==================== 7. GENERATE HTML VIEW ====================
    //     $period_title = strtoupper(date("F Y", strtotime("$filter_period_year-$filter_month-01")));

    //     $html = '<html><head><title>Material Monitoring</title></head>
    //     <style>
    //         body {font-family: Arial, sans-serif;}
    //         #customers {border-collapse: collapse; width: 100%; font-size: 10px;}
    //         #customers td, #customers th {border: 1px solid #ddd; padding: 4px;}
    //         #customers th {background-color: #f2f2f2; text-align: center;}
    //         .text-right {text-align: right;}
    //         .text-center {text-align: center;}
    //     </style>
    //     <body>
    //         <div style="float: left; font-size: 12px;">
    //             <b>' . $config->name . '</b><br><small>'.$config->description.'</small>
    //         </div>
    //         <div style="float: right; font-size: 12px; text-align: right;">
    //             Print Date ' . date("d M Y H:i:s") . '
    //         </div>
    //         <br><br><center><h3>SUMMARY FORECAST VS RECEIPT MATERIAL</h3><h4>Period: '.$period_title.'</h4></center>
            
    //         <table id="customers" border="1">
    //             <thead>
    //                 <tr>
    //                     <th>No</th>
    //                     <th>Part No</th>
    //                     <th>Part Name</th>
    //                     <th>Product Family</th>
    //                     <th>Forecast</th>
    //                     <th>Receipt Material</th>
    //                     <th>Tolarence Receipt Minus</th>
    //                     <th>Tolarence Receipt Plus</th>
    //                     <th>Difference</th>
    //                     <th>Status</th>
    //                 </tr>
    //             </thead>
    //             <tbody>';
                
    //     $no = 1;
    //     foreach ($final_records as $row) {
    //         $status_style = "";
    //         if($row['status'] == "MINUS") $status_style = "style='color:red; font-weight:bold;'";
    //         if($row['status'] == "PLUS") $status_style = "style='color:blue; font-weight:bold;'";

    //         $html .= '<tr>
    //             <td class="text-center">' . $no++ . '</td>
    //             <td>' . $row['rm_number'] . '</td>
    //             <td>' . $row['rm_name'] . '</td>
    //             <td>' . $row['fg_name'] . '</td>
    //             <td class="text-right">' . number_format($row['forecast'], 2) . '</td>
    //             <td class="text-right" style="background-color:#f9f9f9;">' . number_format($row['receipt'], 2) . '</td>
    //             <td class="text-right">' . number_format($row['tol_minus'], 2) . '</td>
    //             <td class="text-right">' . number_format($row['tol_plus'], 2) . '</td>
    //             <td class="text-right">' . number_format($row['diff'], 2) . '</td>
    //             <td class="text-center" '.$status_style.'>' . $row['status'] . '</td>
    //         </tr>';
    //     }

    //     if (empty($final_records)) {
    //         $html .= '<tr><td colspan="10" class="text-center">No data found for status: '.$filter_status.'</td></tr>';
    //     }

    //     $html .= '</tbody></table></body></html>';
    //     echo $html;
    // }

    // public function print($option = "")//dokumentasi: penambahan begin stock
    // {
    //     if ($option == "excel") {
    //         $format  = date("Ymd");
    //         header("Content-type: application/vnd-ms-excel");
    //         header("Content-Disposition: attachment; filename=summary_forecast_vs_receipt_material_$format.xls");
    //     }

    //     // ==================== 1. FILTER & CONFIG ====================
    //     $filter_period_year = (int) base64_decode($this->input->get("filter_period_year"));
    //     $filter_month = (int)base64_decode($this->input->get("filter_month"));
    //     $filter_item_rm = $this->input->get("filter_item_rm"); 
    //     $filter_status = $this->input->get("filter_status");
    //     $filter_product_family = $this->input->get("filter_product_family");
    //     $config = $this->db->get("config")->row();

    //     $targets = [['year' => $filter_period_year, 'month' => $filter_month]];

    //     // ==================== 2. LOGIKA FALLBACK ====================
    //     $firstTarget = $targets[0];
    //     $earliest = $firstTarget;
    //     for ($k = 0; $k < 3; $k++) {
    //         $earliest_ts = strtotime($earliest['year'] . '-' . $earliest['month'] . '-01');
    //         $earliest_ts = strtotime('-1 month', $earliest_ts);
    //         $earliest = ['year' => (int)date('Y', $earliest_ts), 'month' => (int)date('n', $earliest_ts)];
    //     }

    //     $monthsToFetch = [];
    //     $cur = strtotime($earliest['year'] . '-' . $earliest['month'] . '-01');
    //     $end = strtotime($targets[0]['year'] . '-' . $targets[0]['month'] . '-01');
    //     while ($cur <= $end) {
    //         $monthsToFetch[] = ['year' => (int)date('Y', $cur), 'month' => (int)date('n', $cur)];
    //         $cur = strtotime('+1 month', $cur);
    //     }

    //     $conds_noalias = [];
    //     $conds_falias  = [];
    //     foreach ($monthsToFetch as $mm) {
    //         $y = (int)$mm['year']; $m = (int)$mm['month'];
    //         $conds_noalias[] = "(p_year = $y AND p_month = $m)";
    //         $conds_falias[]  = "(f.p_year = $y AND f.p_month = $m)";
    //     }
    //     $where_months_sql_noalias = implode(" OR ", $conds_noalias);
    //     $where_months_sql_falias  = implode(" OR ", $conds_falias);

    //     // ==================== 3. FETCH DATA FORECAST (FG) ====================
    //     $sql_forecast = "
    //         SELECT f.*
    //         FROM forecasts f
    //         JOIN (
    //             SELECT item_fg_id, customer_id, p_year, p_month, MAX(revision) AS max_rev
    //             FROM forecasts
    //             WHERE deleted = 0 AND ({$where_months_sql_noalias})
    //             GROUP BY item_fg_id, customer_id, p_year, p_month
    //         ) latest ON latest.item_fg_id = f.item_fg_id
    //                 AND latest.customer_id = f.customer_id
    //                 AND latest.p_year = f.p_year
    //                 AND latest.p_month = f.p_month
    //                 AND latest.max_rev = f.revision
    //         WHERE f.deleted = 0 AND ({$where_months_sql_falias})
    //     ";
    //     $forecast_rows = $this->db->query($sql_forecast)->result_array();

    //     $map_fg = [];
    //     foreach ($forecast_rows as $r) {
    //         $map_fg[$r['item_fg_id']][$r['customer_id']][(int)$r['p_year']][(int)$r['p_month']] = $r;
    //     }

    //     // ==================== 4. FETCH DATA BOM & MATERIAL ====================
    //     $this->db->select('b.item_fg_id, b.item_rm_id, rm.number as rm_number, rm.name as rm_name, fg.number as fg_number, if.name as fg_name');
    //     $this->db->from('bom b');
    //     $this->db->join('item_rm rm', 'b.item_rm_id = rm.id');
    //     $this->db->join('item_fg fg', 'b.item_fg_id = fg.id');
    //     $this->db->join('item_familys if', 'if.id = rm.item_family_id');
    //     $this->db->where('rm.status', 0);
    //     $this->db->where('rm.division', 'INJ');
    //     $this->db->where_in('rm.item_family_id', ['P01', 'P02', 'P06']);
    //     if ($filter_item_rm) $this->db->where('rm.id', $filter_item_rm);
    //     if ($filter_product_family) $this->db->where('rm.item_family_id', $filter_product_family);
    //     $bom_list = $this->db->get()->result_array();

    //     // ==================== 5. FETCH DATA RECEIPT PO ====================
    //     $this->db->select('item_rm_id, SUM(qty_receipt2) as total_receipt');
    //     $this->db->from('purchase_order_receipts');
    //     $this->db->where('YEAR(receipt_date)', $filter_period_year);
    //     $this->db->where('MONTH(receipt_date)', $filter_month);
    //     $this->db->group_by('item_rm_id');
    //     $receipt_data = $this->db->get()->result_array();

    //     $map_receipt = [];
    //     foreach ($receipt_data as $rd) {
    //         $map_receipt[$rd['item_rm_id']] = $rd['total_receipt'];
    //     }

    //     // ==================== 5.5 FETCH DATA BEGIN STOCK ====================
    //     $filter_from = sprintf("%04d-%02d-01", $filter_period_year, $filter_month);
        
    //     $sql_stock = "
    //         SELECT a.id AS item_rm_id, 
    //         ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - 
    //          (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
    //         FROM item_rm a
    //         LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_from' GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
    //         LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
    //         LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
    //         LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$filter_from' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
    //         LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
    //         LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
    //         LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
    //         WHERE a.division = 'INJ' AND a.status = 0
    //     ";
        
    //     $stock_data = $this->db->query($sql_stock)->result_array();
    //     $map_stock = [];
    //     foreach ($stock_data as $sd) {
    //         $map_stock[$sd['item_rm_id']] = $sd['begin_stock'];
    //     }

    //     // ==================== 6. PROCESSING & MAPPING FINAL DATA ====================
    //     $temp_rm_data = [];
    //     foreach ($bom_list as $bom) {
    //         $fg_id = $bom['item_fg_id'];
    //         $rm_id = $bom['item_rm_id'];

    //         if (!isset($temp_rm_data[$rm_id])) {
    //             $temp_rm_data[$rm_id] = [
    //                 'rm_number' => $bom['rm_number'],
    //                 'rm_name'   => $bom['rm_name'],
    //                 'fg_name'   => $bom['fg_name'],
    //                 'forecast'  => 0,
    //                 'receipt'   => (float)($map_receipt[$rm_id] ?? 0),
    //                 'stock'     => (float)($map_stock[$rm_id] ?? 0) 
    //             ];
    //         }

    //         $current_fg_forecast = 0;
    //         if (isset($map_fg[$fg_id])) {
    //             foreach ($map_fg[$fg_id] as $custId => $years) {
    //                 $value = 0; $final_value_found = false;
    //                 $py = $filter_period_year; $pm = $filter_month;

    //                 if (isset($map_fg[$fg_id][$custId][$py][$pm])) {
    //                     $raw_v0 = $map_fg[$fg_id][$custId][$py][$pm]['month_1'];
    //                     if ($raw_v0 !== null && $raw_v0 !== '') { $value = floatval($raw_v0); $final_value_found = true; }
    //                 }
    //                 if (!$final_value_found) {
    //                     for ($k = 1; $k <= 3; $k++) {
    //                         $check_ts = strtotime("-{$k} month", strtotime("$py-$pm-01"));
    //                         $cy = (int)date('Y', $check_ts); $cm = (int)date('n', $check_ts);
    //                         if (isset($map_fg[$fg_id][$custId][$cy][$cm])) {
    //                             $raw_vv = $map_fg[$fg_id][$custId][$cy][$cm]['month_' . ($k + 1)] ?? null;
    //                             if ($raw_vv !== null && $raw_vv !== '') { $value = floatval($raw_vv); $final_value_found = true; break; }
    //                         }
    //                     }
    //                 }
    //                 $current_fg_forecast += $value;
    //             }
    //         }
    //         $temp_rm_data[$rm_id]['forecast'] += $current_fg_forecast;
    //     }

    //     // Kalkulasi 
    //     $final_records = [];
    //     foreach ($temp_rm_data as $key => $row) {
    //         $fc = $row['forecast'];
    //         $rc = $row['receipt'];
            
    //         $tol_minus = $fc - (0.10 * $fc);
    //         $tol_plus  = $fc + (0.10 * $fc);
    //         $diff      = $rc - $fc;
            
    //         $status = "OK";
    //         if ($rc > $tol_plus) { 
    //             $status = "PLUS"; 
    //         } elseif ($rc < $tol_minus) { 
    //             $status = "MINUS"; 
    //         }

    //         if ($filter_status && $filter_status != "" && $status != $filter_status) {
    //             continue; 
    //         }

    //         $row_data = $row;
    //         $row_data['tol_minus'] = $tol_minus;
    //         $row_data['tol_plus']  = $tol_plus;
    //         $row_data['diff']      = $diff;
    //         $row_data['status']    = $status;

    //         $final_records[] = $row_data;
    //     }

    //     // ==================== 7. GENERATE HTML VIEW ====================
    //     $period_title = strtoupper(date("F Y", strtotime("$filter_period_year-$filter_month-01")));

    //     $html = '<html><head><title>Material Monitoring</title></head>
    //     <style>
    //         body {font-family: Arial, sans-serif;}
    //         #customers {border-collapse: collapse; width: 100%; font-size: 10px;}
    //         #customers td, #customers th {border: 1px solid #ddd; padding: 4px;}
    //         #customers th {background-color: #f2f2f2; text-align: center;}
    //         .text-right {text-align: right;}
    //         .text-center {text-align: center;}
    //     </style>
    //     <body>
    //         <div style="float: left; font-size: 12px;">
    //             <b>' . $config->name . '</b><br><small>'.$config->description.'</small>
    //         </div>
    //         <div style="float: right; font-size: 12px; text-align: right;">
    //             Print Date ' . date("d M Y H:i:s") . '
    //         </div>
    //         <br><br><center><h3>SUMMARY FORECAST VS RECEIPT MATERIAL</h3><h4>Period: '.$period_title.'</h4></center>
            
    //         <table id="customers" border="1">
    //             <thead>
    //                 <tr>
    //                     <th>No</th>
    //                     <th>Part No</th>
    //                     <th>Part Name</th>
    //                     <th>Product Family</th>
    //                     <th>Begin Stock</th>
    //                     <th>Forecast</th>
    //                     <th>Receipt Material</th>
    //                     <th>Tolerance Receipt Minus</th>
    //                     <th>Tolerance Receipt Plus</th>
    //                     <th>Difference</th>
    //                     <th>Status</th>
    //                 </tr>
    //             </thead>
    //             <tbody>';
                
    //     $no = 1;
    //     foreach ($final_records as $row) {
    //         $status_style = "";
    //         if($row['status'] == "MINUS") $status_style = "style='color:red; font-weight:bold;'";
    //         if($row['status'] == "PLUS") $status_style = "style='color:blue; font-weight:bold;'";

    //         $html .= '<tr>
    //             <td class="text-center">' . $no++ . '</td>
    //             <td>' . $row['rm_number'] . '</td>
    //             <td>' . $row['rm_name'] . '</td>
    //             <td>' . $row['fg_name'] . '</td>
    //             <td class="text-right">' . number_format($row['stock'], 2) . '</td>
    //             <td class="text-right">' . number_format($row['forecast'], 2) . '</td>
    //             <td class="text-right" style="background-color:#f9f9f9;">' . number_format($row['receipt'], 2) . '</td>
    //             <td class="text-right">' . number_format($row['tol_minus'], 2) . '</td>
    //             <td class="text-right">' . number_format($row['tol_plus'], 2) . '</td>
    //             <td class="text-right">' . number_format($row['diff'], 2) . '</td>
    //             <td class="text-center" '.$status_style.'>' . $row['status'] . '</td>
    //         </tr>';
    //     }

    //     if (empty($final_records)) {
    //         $html .= '<tr><td colspan="11" class="text-center">No data found for status: '.$filter_status.'</td></tr>';
    //     }

    //     $html .= '</tbody></table></body></html>';
    //     echo $html;
    // }

    public function print($option = "")//dokumentasi: penambahan * composition BOM
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=summary_forecast_vs_receipt_material_$format.xls");
        }

        // ==================== 1. FILTER & CONFIG ====================
        $filter_period_year = (int) base64_decode($this->input->get("filter_period_year"));
        $filter_month = (int)base64_decode($this->input->get("filter_month"));
        $filter_item_rm = $this->input->get("filter_item_rm"); 
        $filter_status = $this->input->get("filter_status");
        $filter_product_family = $this->input->get("filter_product_family");
        $config = $this->db->get("config")->row();

        $targets = [['year' => $filter_period_year, 'month' => $filter_month]];

        // ==================== 2. LOGIKA FALLBACK ====================
        $firstTarget = $targets[0];
        $earliest = $firstTarget;
        for ($k = 0; $k < 3; $k++) {
            $earliest_ts = strtotime($earliest['year'] . '-' . $earliest['month'] . '-01');
            $earliest_ts = strtotime('-1 month', $earliest_ts);
            $earliest = ['year' => (int)date('Y', $earliest_ts), 'month' => (int)date('n', $earliest_ts)];
        }

        $monthsToFetch = [];
        $cur = strtotime($earliest['year'] . '-' . $earliest['month'] . '-01');
        $end = strtotime($targets[0]['year'] . '-' . $targets[0]['month'] . '-01');
        while ($cur <= $end) {
            $monthsToFetch[] = ['year' => (int)date('Y', $cur), 'month' => (int)date('n', $cur)];
            $cur = strtotime('+1 month', $cur);
        }

        $conds_noalias = [];
        $conds_falias  = [];
        foreach ($monthsToFetch as $mm) {
            $y = (int)$mm['year']; $m = (int)$mm['month'];
            $conds_noalias[] = "(p_year = $y AND p_month = $m)";
            $conds_falias[]  = "(f.p_year = $y AND f.p_month = $m)";
        }
        $where_months_sql_noalias = implode(" OR ", $conds_noalias);
        $where_months_sql_falias  = implode(" OR ", $conds_falias);

        // ==================== 3. FETCH DATA FORECAST (FG) ====================
        $sql_forecast = "
            SELECT f.*
            FROM forecasts f
            JOIN (
                SELECT item_fg_id, customer_id, p_year, p_month, MAX(revision) AS max_rev
                FROM forecasts
                WHERE deleted = 0 AND ({$where_months_sql_noalias})
                GROUP BY item_fg_id, customer_id, p_year, p_month
            ) latest ON latest.item_fg_id = f.item_fg_id
                    AND latest.customer_id = f.customer_id
                    AND latest.p_year = f.p_year
                    AND latest.p_month = f.p_month
                    AND latest.max_rev = f.revision
            WHERE f.deleted = 0 AND ({$where_months_sql_falias})
        ";
        $forecast_rows = $this->db->query($sql_forecast)->result_array();

        $map_fg = [];
        foreach ($forecast_rows as $r) {
            $map_fg[$r['item_fg_id']][$r['customer_id']][(int)$r['p_year']][(int)$r['p_month']] = $r;
        }

        // ==================== 4. FETCH DATA BOM & MATERIAL ====================
        // UPDATE: Tambahkan b.composition di dalam query select ini
        $this->db->select('b.item_fg_id, b.item_rm_id, b.composition, rm.number as rm_number, rm.name as rm_name, fg.number as fg_number, if.name as fg_name');
        $this->db->from('bom b');
        $this->db->join('item_rm rm', 'b.item_rm_id = rm.id');
        $this->db->join('item_fg fg', 'b.item_fg_id = fg.id');
        $this->db->join('item_familys if', 'if.id = rm.item_family_id');
        $this->db->where('rm.status', 0);
        $this->db->where('rm.division', 'INJ');
        $this->db->where_in('rm.item_family_id', ['P01', 'P02', 'P06']);
        if ($filter_item_rm) $this->db->where('rm.id', $filter_item_rm);
        if ($filter_product_family) $this->db->where('rm.item_family_id', $filter_product_family);
        $bom_list = $this->db->get()->result_array();

        // ==================== 5. FETCH DATA RECEIPT PO ====================
        $this->db->select('item_rm_id, SUM(qty_receipt2) as total_receipt');
        $this->db->from('purchase_order_receipts');
        $this->db->where('YEAR(receipt_date)', $filter_period_year);
        $this->db->where('MONTH(receipt_date)', $filter_month);
        $this->db->group_by('item_rm_id');
        $receipt_data = $this->db->get()->result_array();

        $map_receipt = [];
        foreach ($receipt_data as $rd) {
            $map_receipt[$rd['item_rm_id']] = $rd['total_receipt'];
        }

        // ==================== 5.5 FETCH DATA BEGIN STOCK ====================
        $filter_from = sprintf("%04d-%02d-01", $filter_period_year, $filter_month);
        
        $sql_stock = "
            SELECT a.id AS item_rm_id, 
            ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - 
             (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
            FROM item_rm a
            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_from' GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$filter_from' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            WHERE a.division = 'INJ' AND a.status = 0
        ";
        
        $stock_data = $this->db->query($sql_stock)->result_array();
        $map_stock = [];
        foreach ($stock_data as $sd) {
            $map_stock[$sd['item_rm_id']] = $sd['begin_stock'];
        }

        // ==================== 6. PROCESSING & MAPPING FINAL DATA ====================
        $temp_rm_data = [];
        foreach ($bom_list as $bom) {
            $fg_id = $bom['item_fg_id'];
            $rm_id = $bom['item_rm_id'];
            // UPDATE: Tangkap nilai composition (default ke 1 jika karena alasan tertentu kosong/tidak terbaca)
            $composition = isset($bom['composition']) ? (float)$bom['composition'] : 1;

            if (!isset($temp_rm_data[$rm_id])) {
                $temp_rm_data[$rm_id] = [
                    'rm_number' => $bom['rm_number'],
                    'rm_name'   => $bom['rm_name'],
                    'fg_name'   => $bom['fg_name'],
                    'forecast'  => 0,
                    'receipt'   => (float)($map_receipt[$rm_id] ?? 0),
                    'stock'     => (float)($map_stock[$rm_id] ?? 0) 
                ];
            }

            $current_fg_forecast = 0;
            if (isset($map_fg[$fg_id])) {
                foreach ($map_fg[$fg_id] as $custId => $years) {
                    $value = 0; $final_value_found = false;
                    $py = $filter_period_year; $pm = $filter_month;

                    if (isset($map_fg[$fg_id][$custId][$py][$pm])) {
                        $raw_v0 = $map_fg[$fg_id][$custId][$py][$pm]['month_1'];
                        if ($raw_v0 !== null && $raw_v0 !== '') { $value = floatval($raw_v0); $final_value_found = true; }
                    }
                    if (!$final_value_found) {
                        for ($k = 1; $k <= 3; $k++) {
                            $check_ts = strtotime("-{$k} month", strtotime("$py-$pm-01"));
                            $cy = (int)date('Y', $check_ts); $cm = (int)date('n', $check_ts);
                            if (isset($map_fg[$fg_id][$custId][$cy][$cm])) {
                                $raw_vv = $map_fg[$fg_id][$custId][$cy][$cm]['month_' . ($k + 1)] ?? null;
                                if ($raw_vv !== null && $raw_vv !== '') { $value = floatval($raw_vv); $final_value_found = true; break; }
                            }
                        }
                    }
                    
                    // UPDATE: Kalikan $value (QTY FG) dengan $composition (Kebutuhan RM per FG)
                    $current_fg_forecast += ($value * $composition);
                }
            }
            $temp_rm_data[$rm_id]['forecast'] += $current_fg_forecast;
        }

        // Kalkulasi 
        $final_records = [];
        foreach ($temp_rm_data as $key => $row) {
            $fc = $row['forecast'];
            $rc = $row['receipt'];
            
            $tol_minus = $fc - (0.10 * $fc);
            $tol_plus  = $fc + (0.10 * $fc);
            $diff      = $rc - $fc;
            
            $status = "OK";
            if ($rc > $tol_plus) { 
                $status = "PLUS"; 
            } elseif ($rc < $tol_minus) { 
                $status = "MINUS"; 
            }

            if ($filter_status && $filter_status != "" && $status != $filter_status) {
                continue; 
            }

            $row_data = $row;
            $row_data['tol_minus'] = $tol_minus;
            $row_data['tol_plus']  = $tol_plus;
            $row_data['diff']      = $diff;
            $row_data['status']    = $status;

            $final_records[] = $row_data;
        }

        // ==================== 7. GENERATE HTML VIEW ====================
        $period_title = strtoupper(date("F Y", strtotime("$filter_period_year-$filter_month-01")));

        $html = '<html><head><title>Material Monitoring</title></head>
        <style>
            body {font-family: Arial, sans-serif;}
            #customers {border-collapse: collapse; width: 100%; font-size: 10px;}
            #customers td, #customers th {border: 1px solid #ddd; padding: 4px;}
            #customers th {background-color: #f2f2f2; text-align: center;}
            .text-right {text-align: right;}
            .text-center {text-align: center;}
        </style>
        <body>
            <div style="float: left; font-size: 12px;">
                <b>' . $config->name . '</b><br><small>'.$config->description.'</small>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . '
            </div>
            <br><br><center><h3>SUMMARY FORECAST VS RECEIPT MATERIAL</h3><h4>Period: '.$period_title.'</h4></center>
            
            <table id="customers" border="1">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Part No</th>
                        <th>Part Name</th>
                        <th>Product Family</th>
                        <th>Begin Stock</th>
                        <th>Forecast</th>
                        <th>Receipt Material</th>
                        <th>Tolerance Receipt Minus</th>
                        <th>Tolerance Receipt Plus</th>
                        <th>Difference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
                
        $no = 1;
        foreach ($final_records as $row) {
            $status_style = "";
            if($row['status'] == "MINUS") $status_style = "style='color:red; font-weight:bold;'";
            if($row['status'] == "PLUS") $status_style = "style='color:blue; font-weight:bold;'";

            $html .= '<tr>
                <td class="text-center">' . $no++ . '</td>
                <td>' . $row['rm_number'] . '</td>
                <td>' . $row['rm_name'] . '</td>
                <td>' . $row['fg_name'] . '</td>
                <td class="text-right">' . number_format($row['stock'], 2) . '</td>
                <td class="text-right">' . number_format($row['forecast'], 2) . '</td>
                <td class="text-right" style="background-color:#f9f9f9;">' . number_format($row['receipt'], 2) . '</td>
                <td class="text-right">' . number_format($row['tol_minus'], 2) . '</td>
                <td class="text-right">' . number_format($row['tol_plus'], 2) . '</td>
                <td class="text-right">' . number_format($row['diff'], 2) . '</td>
                <td class="text-center" '.$status_style.'>' . $row['status'] . '</td>
            </tr>';
        }

        if (empty($final_records)) {
            $html .= '<tr><td colspan="11" class="text-center">No data found for status: '.$filter_status.'</td></tr>';
        }

        $html .= '</tbody></table></body></html>';
        echo $html;
    }
}
