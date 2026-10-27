<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property CI_Form_validation $form_validation
 * @property Crud $crud
 */
class Sales_dashboard extends CI_Controller
{
    /** @var array */
    private $_get_rates = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');

        // Query Rates disisipkan ke private variable
        $this->_get_rates = $this->db->get('exchange_rates')->result_array();
        if (empty($this->_get_rates)) {
            $this->_get_rates = $this->db->get('standard_exchange_rates')->result_array();
        }
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['approval'] = $this->crud->read('signatures');

            $this->load->view('template/header', $data);
            $this->load->view('sales/sales_dashboard');
        } else {
            redirect('error_access');
        }
    }

    // Dropdown Weeks
    public function get_iso_weeks() 
    {
        $year = $this->input->get('year') ? $this->input->get('year') : date('Y');
        $weeks = [];

        // Get jumlah weeks dalam setahun menurut ISO 8601
        $date = new DateTime();
        $date->setISODate($year, 53);
        $total_weeks = ($date->format("W") === "53" ? 53 : 52);

        for ($i = 1; $i <= $total_weeks; $i++) {
            $dto = new DateTime();
            // Set ke hari Senin di minggu tersebut
            $dto->setISODate($year, $i, 1);
            $start = $dto->format('j M');
            
            // Tambahkan 6 hari untuk mendapatkan hari Minggu
            $dto->modify('+6 days');
            $end = $dto->format('j M Y');

            $current_week = (int)date('W');
            
            $weeks[] = [
                'id'    => "{$year}-W" . sprintf("%02d", $i),
                'text'  => "W-{$i} ({$start} - {$end})",
                'selected' => ($i === $current_week)
            ];
        }

        echo json_encode($weeks);
    }

    public function get_years() 
    {
        // GET YEAR FROM TABLE
        $this->db->select('DISTINCT(YEAR(delivery_note_date)) as year_val', FALSE);
        $this->db->from('delivery_notes');
        $this->db->order_by('year_val', 'DESC');
        $query = $this->db->get()->result();
        
        $data = [];
        foreach ($query as $row) {
            if ($row->year_val) {
                $data[] = [
                    'id'   => $row->year_val,
                    'text' => $row->year_val,
                ];
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // Get Rate di memory (Optimasi mencegah N+1 Query)
    private function _find_rate_in_cache($date, $currency) {
        if ($currency == "IDR" || empty($currency)) return 1.0;
        $monthBf = date('Y-m-01', strtotime('-1 month', strtotime((string) $date)));
        
        foreach ($this->_get_rates as $r) {
            if ($r['currency_from'] == $currency && ($r['currency_to'] ?? 'IDR') == 'IDR') {
                if (isset($r['start_date']) && $r['start_date'] == $monthBf) {
                    return (float)$r['middle'];
                }
            }
        }
        foreach ($this->_get_rates as $r) {
            if ($r['currency_from'] == $currency && ($r['currency_to'] ?? 'IDR') == 'IDR') {
                if (isset($r['start_date']) && isset($r['end_date']) && $date >= $r['start_date'] && $date <= $r['end_date']) {
                    return (float)$r['middle'];
                }
            }
        }
        return 0.0;
    }

    // Unified Sales (Delivery Notes Type SALES) data fetcher
    private function _get_sales_dn_data($period_start, $period_end, $period_type, $filter_customer_id = '', $filter_division = '', $filter_item_fg_id = '')
    {
        $group_by_sql = "DATE_FORMAT(a.delivery_note_date, '%Y-%m')";
        if ($period_type == 'daily') {
            $group_by_sql = "a.delivery_note_date";
        } elseif ($period_type == 'weekly') {
            $group_by_sql = "YEARWEEK(a.delivery_note_date, 3)";
        } elseif ($period_type == 'yearly') {
            $group_by_sql = "YEAR(a.delivery_note_date)";
        }

        $where = "";
        $params = [$period_start, $period_end];

        if (!empty($filter_customer_id)) {
            $where .= " AND a.customer_id = ?";
            $params[] = $filter_customer_id;
        }

        if (!empty($filter_division)) {
            $where .= " AND (a.division = ? OR itm.division_id = ? OR (a.sales_order_no_rm IS NOT NULL AND ? = 'RM / SUBCONT'))";
            $params[] = $filter_division;
            $params[] = $filter_division;
            $params[] = $filter_division;
        }

        if (!empty($filter_item_fg_id)) {
            $where .= " AND a.item_fg_id = ?";
            $params[] = $filter_item_fg_id;
        }

        $query = "SELECT
                    a.id,
                    $group_by_sql as period_key,
                    c.name AS customer_name,
                    a.delivery_note_no,
                    a.delivery_note_date,
                    a.qty,
                    (CASE 
                        WHEN a.sales_order_no_rm IS NOT NULL THEN e.currency 
                        WHEN a.sales_order_no IS NOT NULL THEN d.currency
                        ELSE NULL 
                    END) AS currency,
                    (CASE 
                        WHEN a.sales_order_no_rm IS NOT NULL THEN e.price 
                        WHEN a.sales_order_no IS NOT NULL THEN d.price
                        ELSE NULL 
                    END) AS price
                FROM delivery_notes a
                LEFT JOIN item_fg itm ON a.item_fg_id = itm.id
                LEFT JOIN customers c ON a.customer_id = c.id
                LEFT JOIN sales_orders d ON a.sales_order_no = d.sales_order_no AND a.item_fg_id = d.item_fg_id
                LEFT JOIN sales_order_rm e ON a.sales_order_no_rm = e.sales_order_no AND a.item_fg_id = e.item_fg_id
                WHERE a.trans_type = 'SALES'
                AND a.delivery_note_date BETWEEN ? AND ?
                $where";

        $result = $this->db->query($query, $params);
        if ($result === false) {
            log_message('error', 'Sales_dashboard::_get_sales_dn_data query error: ' . $this->db->last_query());
            return [];
        }
        return $result->result_array();
    }

    // Unified Forecast data fetcher matching Summary_forecasts logic (Priority 1 + Fallback 1..3)
    private function _get_forecast_data($labels, $period_type, $filter_customer_id = '', $filter_division = '', $filter_item_fg_id = '')
    {
        $targets = [];
        if ($period_type == 'monthly') {
            foreach ($labels as $lbl) {
                $parts = explode('-', $lbl);
                $targets[] = [
                    'year'  => (int)$parts[0],
                    'month' => (int)$parts[1],
                    'key'   => $lbl
                ];
            }
        } else {
            foreach ($labels as $yr) {
                $y = (int)$yr;
                for ($m = 1; $m <= 12; $m++) {
                    $targets[] = [
                        'year'  => $y,
                        'month' => $m,
                        'key'   => (string)$y
                    ];
                }
            }
        }

        if (empty($targets)) {
            return [
                'qty_map'    => array_fill_keys($labels, 0),
                'amount_map' => array_fill_keys($labels, 0),
            ];
        }

        $firstTarget = $targets[0];
        $lastTarget  = end($targets);

        $earliest_ts = strtotime("-3 month", strtotime($firstTarget['year'] . '-' . sprintf('%02d', $firstTarget['month']) . '-01'));
        $latest_ts   = strtotime($lastTarget['year'] . '-' . sprintf('%02d', $lastTarget['month']) . '-01');

        $conds_sub  = [];
        $conds_main = [];
        $cur = $earliest_ts;
        while ($cur <= $latest_ts) {
            $cy = date('Y', $cur);
            $cm = (int)date('n', $cur);
            $cm_padded = sprintf('%02d', $cm);
            $conds_sub[]  = "(p_year = '$cy' AND (p_month = '$cm' OR p_month = '$cm_padded'))";
            $conds_main[] = "(f.p_year = '$cy' AND (f.p_month = '$cm' OR f.p_month = '$cm_padded'))";
            $cur = strtotime('+1 month', $cur);
        }
        $where_months_sub  = implode(' OR ', $conds_sub);
        $where_months_main = implode(' OR ', $conds_main);

        $filter_sub  = "";
        $filter_main = "";
        if (!empty($filter_customer_id)) {
            $c_esc = $this->db->escape($filter_customer_id);
            $filter_sub  .= " AND customer_id = $c_esc";
            $filter_main .= " AND f.customer_id = $c_esc";
        }
        if (!empty($filter_item_fg_id)) {
            $i_esc = $this->db->escape($filter_item_fg_id);
            $filter_sub  .= " AND item_fg_id = $i_esc";
            $filter_main .= " AND f.item_fg_id = $i_esc";
        }
        if (!empty($filter_division)) {
            $d_esc = $this->db->escape($filter_division);
            $filter_main .= " AND itm.division_id = $d_esc";
        }

        $sql = "SELECT f.*
                FROM forecasts f
                JOIN (
                    SELECT item_fg_id, customer_id, p_year, p_month, MAX(revision) AS max_rev
                    FROM forecasts
                    WHERE deleted = 0
                    AND ($where_months_sub)
                    $filter_sub
                    GROUP BY item_fg_id, customer_id, p_year, p_month
                ) latest ON latest.item_fg_id = f.item_fg_id
                        AND latest.customer_id = f.customer_id
                        AND latest.p_year = f.p_year
                        AND latest.p_month = f.p_month
                        AND latest.max_rev = f.revision
                LEFT JOIN item_fg itm ON f.item_fg_id = itm.id
                WHERE f.deleted = 0
                AND ($where_months_main)
                $filter_main";

        $forecast_rows = $this->db->query($sql)->result_array();

        $map = [];
        foreach ($forecast_rows as $r) {
            $iid = $r['item_fg_id'];
            $cid = $r['customer_id'];
            $py  = (int)$r['p_year'];
            $pm  = (int)$r['p_month'];
            $map[$iid][$cid][$py][$pm] = $r;
        }

        $prices = [];
        $price_rows = $this->db->query("SELECT customer_id, item_fg_id, price, currency FROM customer_items WHERE deleted = 0 ORDER BY id ASC")->result_array();
        foreach ($price_rows as $pr) {
            $prices[$pr['item_fg_id']][$pr['customer_id']] = [
                'price'    => (float)$pr['price'],
                'currency' => $pr['currency'] ?? 'IDR'
            ];
        }

        $qty_map    = array_fill_keys($labels, 0);
        $amount_map = array_fill_keys($labels, 0);

        foreach ($targets as $t) {
            $py = $t['year'];
            $pm = $t['month'];
            $pk = $t['key'];

            foreach ($map as $item_id => $cust_data) {
                foreach ($cust_data as $cust_id => $_) {
                    $qty = 0;
                    $found = false;

                    // 1) Priority 1: target month (py, pm) -> month_1
                    if (isset($map[$item_id][$cust_id][$py][$pm])) {
                        $raw = $map[$item_id][$cust_id][$py][$pm]['month_1'];
                        if ($raw !== null && $raw !== '') {
                            $qty = (float)$raw;
                            $found = true;
                        }
                    }

                    // 2) Fallback 1..3
                    if (!$found) {
                        for ($k = 1; $k <= 3; $k++) {
                            $check_ts = strtotime("-{$k} month", strtotime("$py-$pm-01"));
                            $cy = (int)date('Y', $check_ts);
                            $cm = (int)date('n', $check_ts);
                            $field = 'month_' . ($k + 1);

                            if (isset($map[$item_id][$cust_id][$cy][$cm])) {
                                $raw = $map[$item_id][$cust_id][$cy][$cm][$field] ?? null;
                                if ($raw !== null && $raw !== '') {
                                    $qty = (float)$raw;
                                    $found = true;
                                    break;
                                }
                            }
                        }
                    }

                    if ($qty > 0) {
                        $p_info = $prices[$item_id][$cust_id] ?? ['price' => 0, 'currency' => 'IDR'];
                        $price  = (float)$p_info['price'];
                        $curr   = $p_info['currency'];
                        $rate   = $this->_find_rate_in_cache(sprintf('%04d-%02d-01', $py, $pm), $curr);
                        $amount = $qty * $price * $rate;

                        if (isset($qty_map[$pk])) {
                            $qty_map[$pk]    += $qty;
                            $amount_map[$pk] += $amount;
                        }
                    }
                }
            }
        }

        return [
            'qty_map'    => $qty_map,
            'amount_map' => $amount_map,
        ];
    }


    public function get_dashboard_data() 
    {
        $filter_period_type  = strtolower($this->input->post('filter_period_type') ?? 'monthly');
        $filter_period_value = $this->input->post('filter_period_value');
        $filter_division     = $this->input->post('filter_division');
        $filter_customer_id  = $this->input->post('filter_customer_id');
        
        $labels = [];
        $trend_labels = [];
        $period_start_date = "";
        $period_end_date   = "";
        $label_format      = "";

        // Set Key from Periode Type
        if ($filter_period_type == "daily") {
            $end = new DateTime($filter_period_value);
            $start = (clone $end)->modify('-5 days');
            $period_start_date = $start->format('Y-m-d');
            $period_end_date = $end->format('Y-m-d');
            $label_format = "d M Y";
            
            $period = new DatePeriod($start, new DateInterval('P1D'), (clone $end)->modify('+1 day'));
            foreach ($period as $date) { 
                $key = $date->format('Y-m-d');
                $labels[] = $key;
                $trend_labels[] = $date->format('d M Y');
            }

        } elseif ($filter_period_type == "weekly") {
            $parts = explode('-W', $filter_period_value);
            $year = (int)$parts[0];
            $week = (int)$parts[1];

            $end = new DateTime();
            $end->setISODate($year, $week, 1); 
            $start = (clone $end)->modify('-5 weeks'); 
            
            $period_start_date = $start->format('Y-m-d'); 
            $period_end_date = (clone $end)->modify('+6 days')->format('Y-m-d');
            $label_format = "d M Y";

            $temp_date = clone $start;
            for ($i = 0; $i < 6; $i++) {
                $labels[] = $temp_date->format('oW');
                $week_num = $temp_date->format('W');
                $monday = $temp_date->format('d M');
                $sunday = (clone $temp_date)->modify('+6 days')->format('d M');
                $trend_labels[] = ["Week-$week_num", "($monday - $sunday)"];
                $temp_date->modify('+1 week');
            }

        } elseif ($filter_period_type == "monthly") {
            $end = new DateTime($filter_period_value . "-01");
            $start = (clone $end)->modify('-5 months');
            $period_start_date = $start->format('Y-m-01');
            $period_end_date = $end->format('Y-m-t');
            $label_format = "M Y";

            $period = new DatePeriod($start, new DateInterval('P1M'), (clone $end)->modify('+1 month'));
            foreach ($period as $date) { 
                $labels[] = $date->format('Y-m'); 
                $trend_labels[] = $date->format('M Y');
            }

        } elseif ($filter_period_type == "yearly") {
            $year = (int)$filter_period_value;
            $start_year = $year - 5;
            $period_start_date = "$start_year-01-01";
            $period_end_date = "$year-12-31";
            $label_format = "Y";

            for ($i = $start_year; $i <= $year; $i++) { 
                $labels[] = (string)$i; 
                $trend_labels[] = (string)$i;
            }
        }

        $raw_data = $this->_get_sales_dn_data($period_start_date, $period_end_date, $filter_period_type, $filter_customer_id, $filter_division);

        // Mapping Data
        $mapped_trend = array_fill_keys($labels, 0);
        $customer_totals = [];

        foreach ($raw_data as $row) {
            $rate = $this->_find_rate_in_cache($row['delivery_note_date'], $row['currency']);
            $amount = (float)$row['qty'] * (float)$row['price'] * (float)$rate;

            // Akumulasi Trend
            if (isset($mapped_trend[$row['period_key']])) {
                $mapped_trend[$row['period_key']] += $amount;
            }

            // Akumulasi Per Customer
            $c_name = $row['customer_name'] ?? 'Unknown';
            if (!isset($customer_totals[$c_name])) $customer_totals[$c_name] = 0;
            $customer_totals[$c_name] += $amount;
        }

        // Sort Top 10 Customers
        arsort($customer_totals);
        $top_10_customers = array_slice($customer_totals, 0, 10);

        // Set Average
        $trend_values = array_values($mapped_trend);
        $average = count($trend_values) > 0 ? (array_sum($trend_values) / count($trend_values)) : 0;

        $result = [
            'trend_labels'    => $trend_labels,
            'trend_values'    => array_map(function($v) { return round($v, 2); }, $trend_values),
            'customer_labels' => array_keys($top_10_customers),
            'customer_values' => array_values(array_map(function($v) { return round($v, 2); }, $top_10_customers)),
            'avg_values'      => array_fill(0, count($trend_values), round($average, 2)),
            'title'           => "Sales Amount (IDR) - " . (!empty($filter_division) ? strtoupper($filter_division) : "ALL"),
            'period'          => "Period: " . date($label_format, strtotime((string) $period_start_date)) . " to " . date($label_format, strtotime((string) $period_end_date)),
            'conclusion'      => null,
            'impact'          => null,
        ];

        echo json_encode($result);
    }


    // Forecast VS Sales (DN)
    public function get_forecast_vs_sales_data() 
    {
        $filter_period_type  = strtolower($this->input->post('filter_period_type') ?? 'monthly');
        $filter_period_value = $this->input->post('filter_period_value') ?? date('Y-m');
        $filter_division     = $this->input->post('filter_division');
        $filter_customer_id  = $this->input->post('filter_customer_id');
        $filter_item_fg_id   = $this->input->post('filter_item_fg_id') ?? null;

        $labels = [];
        $years  = [];

        if ($filter_period_type == 'monthly') {
            $start = date('Y-m-01', strtotime((string) $filter_period_value));
            $currentDate = new DateTime($start);
            
            for ($i = 5; $i >= 0; $i--) {
                $date = clone $currentDate;
                $date->sub(new DateInterval("P{$i}M"));
                $labels[] = $date->format('Y-m');
                $years[]  = $date->format('Y');
            }
            $period_start = $labels[0] . '-01';
            $period_end   = date('Y-m-t', strtotime(end($labels) . '-01'));
        } else {
            $currentDate = new DateTime($filter_period_value . "-01-01");
            for ($i = 5; $i >= 0; $i--) {
                $date = clone $currentDate;
                $date->modify("-$i years");
                $label = $date->format('Y');
                $labels[] = $label;
                $years[]  = $label;
            }
            $period_start = $labels[0] . '-01-01';
            $period_end   = end($labels) . '-12-31';
        }

        $years = array_unique($years);

        // 1. Get Actual Sales (Delivery Notes Type SALES)
        $sales_raw = $this->_get_sales_dn_data($period_start, $period_end, $filter_period_type, $filter_customer_id, $filter_division, $filter_item_fg_id);

        $sales_qty_map    = array_fill_keys($labels, 0);
        $sales_amount_map = array_fill_keys($labels, 0);

        foreach ($sales_raw as $row) {
            $pk = $row['period_key'];
            if (isset($sales_qty_map[$pk])) {
                $qty    = (float)$row['qty'];
                $rate   = $this->_find_rate_in_cache($row['delivery_note_date'], $row['currency']);
                $amount = $qty * (float)$row['price'] * $rate;

                $sales_qty_map[$pk]    += $qty;
                $sales_amount_map[$pk] += $amount;
            }
        }

        // 2. Get Forecast (Matching Summary_forecasts logic)
        $fc_data = $this->_get_forecast_data($labels, $filter_period_type, $filter_customer_id, $filter_division, $filter_item_fg_id);
        $forecast_qty_map    = $fc_data['qty_map'];
        $forecast_amount_map = $fc_data['amount_map'];

        echo json_encode([
            'labels'                 => $labels,
            'title'                  => "Forecast vs Sales - " . strtoupper($filter_period_type),
            'amount_title'           => "Forecast vs Sales in Amount - " . strtoupper($filter_period_type),
            'qty_title'              => "Forecast vs Sales in QTY - " . strtoupper($filter_period_type),
            'period'                 => "Period: " . date("F Y", strtotime((string) $period_start)) . " to " . date("F Y", strtotime((string) $period_end)),
            'forecast_qty_values'    => array_values($forecast_qty_map),
            'sales_qty_values'       => array_values($sales_qty_map),
            'forecast_amount_values' => array_values($forecast_amount_map),
            'sales_amount_values'    => array_values($sales_amount_map),
        ]);
    }

    // Forecast VS Sales Orders
    public function get_forecast_vs_sales_order_data() 
    {
        $filter_period_type  = strtolower($this->input->post('filter_period_type') ?? 'monthly');
        $filter_period_value = $this->input->post('filter_period_value') ?? date('Y-m');
        $filter_division     = $this->input->post('filter_division');
        $filter_customer_id  = $this->input->post('filter_customer_id');
        $filter_item_fg_id   = $this->input->post('filter_item_fg_id') ?? null;

        $labels = [];
        $years  = [];

        if ($filter_period_type == 'monthly') {
            $start = date('Y-m-01', strtotime((string) $filter_period_value));
            $currentDate = new DateTime($start);
            for ($i = 5; $i >= 0; $i--) {
                $date = clone $currentDate;
                $date->sub(new DateInterval("P{$i}M"));
                $labels[] = $date->format('Y-m');
                $years[]  = $date->format('Y');
            }
            $period_start = $labels[0] . '-01';
            $period_end   = date('Y-m-t', strtotime(end($labels) . '-01'));
        } else {
            $currentDate = new DateTime($filter_period_value . "-01-01");
            for ($i = 5; $i >= 0; $i--) {
                $date = clone $currentDate;
                $date->modify("-$i years");
                $label = $date->format('Y');
                $labels[] = $label;
                $years[]  = $label;
            }
            $period_start = $labels[0] . '-01-01';
            $period_end   = end($labels) . '-12-31';
        }

        $years = array_unique($years);

        // 1. Get Sales Orders
        $where_so = "";
        $params_so = [$period_start, $period_end];
        if (!empty($filter_customer_id)) {
            $where_so .= " AND s.customer_id = ?";
            $params_so[] = $filter_customer_id;
        }
        if (!empty($filter_division)) {
            $where_so .= " AND (s.division = ? OR itm.division_id = ?)";
            $params_so[] = $filter_division;
            $params_so[] = $filter_division;
        }
        if (!empty($filter_item_fg_id)) {
            $where_so .= " AND s.item_fg_id = ?";
            $params_so[] = $filter_item_fg_id;
        }

        $query_so = "SELECT 
            s.id,
            s.sales_order_date,
            " . ($filter_period_type == 'monthly' ? "DATE_FORMAT(s.sales_order_date, '%Y-%m')" : "DATE_FORMAT(s.sales_order_date, '%Y')") . " AS period_key,
            s.qty,
            s.currency,
            s.price
        FROM sales_orders s
        LEFT JOIN item_fg itm ON s.item_fg_id = itm.id
        WHERE s.deleted = 0
        AND s.sales_order_date BETWEEN ? AND ?
        $where_so";

        $so_raw = $this->db->query($query_so, $params_so)->result_array();

        $so_qty_map    = array_fill_keys($labels, 0);
        $so_amount_map = array_fill_keys($labels, 0);

        foreach ($so_raw as $row) {
            $pk = $row['period_key'];
            if (isset($so_qty_map[$pk])) {
                $qty    = (float)$row['qty'];
                $rate   = $this->_find_rate_in_cache($row['sales_order_date'], $row['currency']);
                $amount = $qty * (float)$row['price'] * $rate;

                $so_qty_map[$pk]    += $qty;
                $so_amount_map[$pk] += $amount;
            }
        }

        // 2. Get Forecast (Matching Summary_forecasts logic)
        $fc_data = $this->_get_forecast_data($labels, $filter_period_type, $filter_customer_id, $filter_division, $filter_item_fg_id);
        $forecast_qty_map    = $fc_data['qty_map'];
        $forecast_amount_map = $fc_data['amount_map'];

        echo json_encode([
            'labels'                 => $labels,
            'title'                  => "Forecast vs Sales Order - " . strtoupper($filter_period_type),
            'amount_title'           => "Forecast vs Sales Order in Amount - " . strtoupper($filter_period_type),
            'qty_title'              => "Forecast vs Sales Order in QTY - " . strtoupper($filter_period_type),
            'period'                 => "Period: " . date("F Y", strtotime((string) $period_start)) . " to " . date("F Y", strtotime((string) $period_end)),
            'forecast_qty_values'    => array_values($forecast_qty_map),
            'sales_qty_values'       => array_values($so_qty_map),
            'forecast_amount_values' => array_values($forecast_amount_map),
            'sales_amount_values'    => array_values($so_amount_map),
        ]);
    }

    // Sales Orders VS Sales (DN)
    public function get_sales_order_vs_sales_data() 
    {
        $filter_period_type  = strtolower($this->input->post('filter_period_type') ?? 'monthly');
        $filter_period_value = $this->input->post('filter_period_value') ?? date('Y-m');
        $filter_division     = $this->input->post('filter_division');
        $filter_customer_id  = $this->input->post('filter_customer_id');
        $filter_item_fg_id   = $this->input->post('filter_item_fg_id') ?? null;

        $labels = [];
        $years  = [];

        if ($filter_period_type == 'monthly') {
            $start = date('Y-m-01', strtotime((string) $filter_period_value));
            $currentDate = new DateTime($start);
            for ($i = 5; $i >= 0; $i--) {
                $date = clone $currentDate;
                $date->sub(new DateInterval("P{$i}M"));
                $labels[] = $date->format('Y-m');
                $years[]  = $date->format('Y');
            }
            $period_start = $labels[0] . '-01';
            $period_end   = date('Y-m-t', strtotime(end($labels) . '-01'));
        } else {
            $currentDate = new DateTime($filter_period_value . "-01-01");
            for ($i = 5; $i >= 0; $i--) {
                $date = clone $currentDate;
                $date->modify("-$i years");
                $label = $date->format('Y');
                $labels[] = $label;
                $years[]  = $label;
            }
            $period_start = $labels[0] . '-01-01';
            $period_end   = end($labels) . '-12-31';
        }

        // 1. Get Sales Orders
        $where_so = "";
        $params_so = [$period_start, $period_end];
        if (!empty($filter_customer_id)) {
            $where_so .= " AND s.customer_id = ?";
            $params_so[] = $filter_customer_id;
        }
        if (!empty($filter_division)) {
            $where_so .= " AND (s.division = ? OR itm.division_id = ?)";
            $params_so[] = $filter_division;
            $params_so[] = $filter_division;
        }
        if (!empty($filter_item_fg_id)) {
            $where_so .= " AND s.item_fg_id = ?";
            $params_so[] = $filter_item_fg_id;
        }

        $query_so = "SELECT 
            s.id,
            s.sales_order_date,
            " . ($filter_period_type == 'monthly' ? "DATE_FORMAT(s.sales_order_date, '%Y-%m')" : "DATE_FORMAT(s.sales_order_date, '%Y')") . " AS period_key,
            s.qty,
            s.currency,
            s.price
        FROM sales_orders s
        LEFT JOIN item_fg itm ON s.item_fg_id = itm.id
        WHERE s.deleted = 0
        AND s.sales_order_date BETWEEN ? AND ?
        $where_so";

        $so_raw = $this->db->query($query_so, $params_so)->result_array();

        $order_qty_map    = array_fill_keys($labels, 0);
        $order_amount_map = array_fill_keys($labels, 0);

        foreach ($so_raw as $row) {
            $pk = $row['period_key'];
            if (isset($order_qty_map[$pk])) {
                $qty    = (float)$row['qty'];
                $rate   = $this->_find_rate_in_cache($row['sales_order_date'], $row['currency']);
                $amount = $qty * (float)$row['price'] * $rate;

                $order_qty_map[$pk]    += $qty;
                $order_amount_map[$pk] += $amount;
            }
        }

        // 2. Get Actual Sales (Delivery Notes Type SALES)
        $sales_raw = $this->_get_sales_dn_data($period_start, $period_end, $filter_period_type, $filter_customer_id, $filter_division, $filter_item_fg_id);

        $sales_qty_map    = array_fill_keys($labels, 0);
        $sales_amount_map = array_fill_keys($labels, 0);

        foreach ($sales_raw as $row) {
            $pk = $row['period_key'];
            if (isset($sales_qty_map[$pk])) {
                $qty    = (float)$row['qty'];
                $rate   = $this->_find_rate_in_cache($row['delivery_note_date'], $row['currency']);
                $amount = $qty * (float)$row['price'] * $rate;

                $sales_qty_map[$pk]    += $qty;
                $sales_amount_map[$pk] += $amount;
            }
        }

        echo json_encode([
            'labels'              => $labels,
            'title'               => "Sales Order VS Actual Sales - " . strtoupper($filter_period_type),
            'amount_title'        => "Sales Order VS Actual Sales in Amount - " . strtoupper($filter_period_type),
            'qty_title'           => "Sales Order VS Actual Sales in QTY - " . strtoupper($filter_period_type),
            'period'              => "Period: " . date("F Y", strtotime((string) $period_start)) . " to " . date("F Y", strtotime((string) $period_end)),
            'order_qty_values'    => array_values($order_qty_map),
            'sales_qty_values'    => array_values($sales_qty_map),
            'order_amount_values' => array_values($order_amount_map),
            'sales_amount_values' => array_values($sales_amount_map),
        ]);
    }
}
