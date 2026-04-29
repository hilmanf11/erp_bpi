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
        $this->_get_rates = $this->db->get('standard_exchange_rates')->result_array();
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
        
        foreach ($this->_get_rates as $r) {
            if ($r['currency_from'] == $currency && $date >= $r['start_date'] && $date <= $r['end_date']) {
                return (float)$r['middle'];
            }
        }
        return 1.0;
    }


    public function get_dashboard_data() 
    {
        $filter_period_type  = strtolower($this->input->post('filter_period_type'));
        $filter_period_value = $this->input->post('filter_period_value');
        $filter_division     = $this->input->post('filter_division');
        $filter_customer_id  = $this->input->post('filter_customer_id');
        
        $labels = [];
        $trend_labels = [];
        $period_start_date = "";
        $period_end_date   = "";
        $group_by_sql      = "";
        $label_format      = "";

        // Set Key from Periode Type
        if ($filter_period_type == "daily") {
            $end = new DateTime($filter_period_value);
            $start = (clone $end)->modify('-5 days');
            $period_start_date = $start->format('Y-m-d');
            $period_end_date = $end->format('Y-m-d');
            $group_by_sql = "a.delivery_note_date";
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
            $group_by_sql = "YEARWEEK(a.delivery_note_date, 3)"; 
            $label_format = "d M Y";

            $temp_date = clone $start;
            for ($i = 0; $i < 6; $i++) {
                $labels[] = $temp_date->format('oW'); // Format 202611
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
            $group_by_sql = "DATE_FORMAT(a.delivery_note_date, '%Y-%m')";
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
            $group_by_sql = "YEAR(a.delivery_note_date)";
            $label_format = "Y";

            for ($i = $start_year; $i <= $year; $i++) { 
                $labels[] = (string)$i; 
                $trend_labels[] = (string)$i;
            }
        }

        // Main Query
        $query = "SELECT
                    $group_by_sql as period_key,
                    c.name AS customer_name,
                    a.delivery_note_no,
                    a.qty,
                    (CASE 
                        WHEN a.sales_order_no_rm IS NOT NULL THEN e.currency 
                        ELSE d.currency 
                    END) AS currency,
                    (CASE 
                        WHEN a.sales_order_no_rm IS NOT NULL THEN e.price 
                        ELSE d.price 
                    END) AS price
                FROM delivery_notes a
                LEFT JOIN customers c ON a.customer_id = c.id
                LEFT JOIN sales_orders d ON a.sales_order_no = d.sales_order_no AND a.item_fg_id = d.item_fg_id
                LEFT JOIN sales_order_rm e ON a.sales_order_no_rm = e.sales_order_no AND a.item_fg_id = e.item_fg_id
                WHERE a.customer_id LIKE ? 
                AND a.division LIKE ? 
                AND a.delivery_note_date BETWEEN ? AND ? 
                AND a.trans_type = 'SALES'";

        $params = ["%$filter_customer_id%", "%$filter_division%", $period_start_date, $period_end_date];
        $raw_data = $this->db->query($query, $params)->result_array();

        // Mapping Data
        $mapped_trend = array_fill_keys($labels, 0);
        $customer_totals = [];

        foreach ($raw_data as $row) {
            $rate = $this->_find_rate_in_cache($row['delivery_note_no'], $row['currency']);
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
            'period'          => "Period: " . date($label_format, strtotime($period_start_date)) . " to " . date($label_format, strtotime($period_end_date)),
            'conclusion'      => null,
            'impact'          => null,
        ];

        echo json_encode($result);
    }


    public function get_forecast_vs_sales_existing() 
    {
        $filter_from        = $this->input->post('from');
        $filter_to          = $this->input->post('to');
        $filter_display     = $this->input->post('display'); // DAILY, WEEKLY, MONTHLY
        $filter_division    = $this->input->post('division');
        $filter_customer_id = $this->input->post('customer_id');

        // Validasi Periode Minimal 6 Bulan untuk MONTHLY
        if ($filter_display == "MONTHLY") {
            $start_check = new DateTime($filter_from);
            $end_check   = new DateTime($filter_to);
            $diff        = $start_check->diff($end_check);
            $total_months = ($diff->y * 12) + $diff->m;

            if ($total_months < 6) {
                $new_start = clone $end_check;
                $new_start->modify('-6 months');
                $filter_from = $new_start->format('Y-m-d');
            }
        }

        // Generate Time Slots (Sumbu X)
        $start = new DateTime($filter_from);
        $end   = new DateTime($filter_to);
        $end->modify('+1 day'); 
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);

        $final_data = [];
        $final_data_amount = [];

        foreach ($period as $dt) {
            if ($filter_display == "DAILY") {
                $key = $dt->format("Y-m-d");
            } elseif ($filter_display == "WEEKLY") {
                $monday = clone $dt;
                if ($monday->format('N') != 1) $monday->modify('last monday');
                $sunday = clone $monday;
                $sunday->modify('+6 days');
                $weekNum = ceil($monday->format('j') / 7);
                $key = "W" . $weekNum . " " . $monday->format('M Y') . " (" . $monday->format('j M') . ")";
            } else { // MONTHLY
                $key = $dt->format("M Y");
            }

            if (!isset($final_data[$key])) {
                $final_data[$key] = [
                    'forecast' => 0,
                    'sales'    => 0,
                ];
            }
            
            if (!isset($final_data_amount[$key])) {
                $final_data_amount[$key] = [
                    'forecast' => 0,
                    'sales'    => 0,
                ];
            }
        }

        // Get Data Sales (Detail per Tanggal)
        $this->db->select("delivery_note_date as date, SUM(qty) as qty");
        $this->db->select("s.price, s.currency");
        $this->db->from("delivery_notes dn");
        $this->db->join("item_fg a", "dn.item_fg_id = a.id");
        $this->db->join("standard_price_fg s", "s.item_fg_id = a.id");
        if($filter_customer_id) $this->db->where("dn.customer_id", $filter_customer_id);
        if($filter_division) $this->db->where("a.division_id", $filter_division);
        $this->db->where("delivery_note_date >=", $filter_from);
        $this->db->where("delivery_note_date <=", $filter_to);
        $this->db->group_by("delivery_note_date");
        $sales_records = $this->db->get()->result_array();

        // Mapping Sales ke Final Data
        foreach ($sales_records as $row) {
            $dt = new DateTime($row['date']);
            // Gunakan logic key yang sama dengan di atas
            if ($filter_display == "DAILY") $k = $dt->format("Y-m-d");
            elseif ($filter_display == "WEEKLY") {
                $m = clone $dt; if ($m->format('N') != 1) $m->modify('last monday');
                $w = ceil($m->format('j') / 7);
                $k = "W" . $w . " " . $m->format('M Y') . " (" . $m->format('j M') . ")";
            }
            else $k = $dt->format("M Y");

            if (isset($final_data[$k])) {
                $final_data[$k]['sales'] += $row['qty'];
            }
            
            if (isset($final_data[$k])) {
                $final_data_amount[$k]['sales'] += $row['qty'] * $row['price'];
            }
        }

        // Get Data Forecast
        $start_dt = new DateTime($filter_from);
        $end_dt   = new DateTime($filter_to);
        
        $this->db->select("f.*");
        $this->db->select("s.price, s.currency");
        $this->db->from("forecasts f");
        $this->db->join("item_fg a", "f.item_fg_id = a.id");
        $this->db->join("standard_price_fg s", "s.item_fg_id = a.id");
        $this->db->where("f.p_year >=", $start_dt->format('Y'));
        $this->db->where("f.p_year <=", $end_dt->format('Y'));
        if($filter_customer_id) $this->db->where("f.customer_id", $filter_customer_id);
        if($filter_division) $this->db->where("a.division_id", $filter_division);
        $forecast_records = $this->db->get()->result_array();

        foreach ($forecast_records as $f) {
            for ($m = 1; $m <= 12; $m++) {
                $month_name = date("M Y", mktime(0, 0, 0, $m, 1, $f['p_year']));
                $qty = $f['month_' . $m];
                $price = $f['price'];

                // Masukkan ke key yang sesuai di final_data
                foreach ($final_data as $key => $val) {
                    // Jika MONTHLY, match langsung ke "Jan 2026"
                    if ($filter_display == "MONTHLY" && $key == $month_name) {
                        $final_data[$key]['forecast'] += $qty;
                    }
                    if ($filter_display == "MONTHLY" && $key == $month_name) {
                        $final_data_amount[$key]['forecast'] += $qty * $price;
                    }
                    // Jika DAILY/WEEKLY, bagi forecast bulanan ke hari
                }
            }
        }

        // Format Output untuk Chart
        $output = [
            'labels'                 => array_keys($final_data),
            'forecast_values'        => array_column($final_data, 'forecast'),
            'sales_values'           => array_column($final_data, 'sales'),
            'forecast_amount_values' => array_column($final_data_amount, 'forecast'),
            'sales_amount_values'    => array_column($final_data_amount, 'sales'),
            'period'                 => "Period: " . $start_dt->format('d M Y') . " to " . $end_dt->format('d M Y')
        ];

        echo json_encode($output);
    }


    public function get_forecast_vs_sales_data()
    {
        $filter_period_type  = strtolower($this->input->post('filter_period_type'));
        $filter_period_value = $this->input->post('filter_period_value');
        $filter_division     = $this->input->post('filter_division');
        $filter_customer_id  = $this->input->post('filter_customer_id');
        
        $labels = [];
        $trend_labels = [];
        $period_start_date = "";
        $period_end_date   = "";
        $group_by_sql      = "";
        $label_format      = "";

        // Periode Hanya Month & Year
        if ($filter_period_type == "monthly") {
            $end = new DateTime($filter_period_value . "-01");
            $start = (clone $end)->modify('-5 months');
            $period_start_date = $start->format('Y-m-01');
            $period_end_date = $end->format('Y-m-t');
            $group_by_sql = "DATE_FORMAT(dn.delivery_note_date, '%Y-%m')";
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
            $group_by_sql = "YEAR(dn.delivery_note_date)";
            $label_format = "Y";

            for ($i = $start_year; $i <= $year; $i++) { 
                $labels[] = (string)$i; 
                $trend_labels[] = (string)$i;
            }
        } else {
            echo json_encode([
                'labels'       => $trend_labels,
                'title'        => "Failed!",
                'amount_title' => "Forecast vs Sales in Amount - " . strtoupper($filter_period_type),
                'qty_title'    => "Forecast vs Sales in QTY - " . strtoupper($filter_period_type),
                'period'       => "Invalid Period Type. Use Monthly or Yearly.",
                // default value for chart
                'forecast_amount_values' => [0, 0, 0, 0, 0, 0],
                'sales_amount_values'    => [0, 0, 0, 0, 0, 0],
                'forecast_qty_values'    => [0, 0, 0, 0, 0, 0],
                'sales_qty_values'       => [0, 0, 0, 0, 0, 0],
            ]);
            return;
        }

        // Main Query
        $query = "SELECT 
                    $group_by_sql as period_key,
                    dn.delivery_note_no,
                    dn.total_qty as actual_qty,
                    dn.currency,
                    dn.price,
                    f.month_1, f.month_2, f.month_3, f.month_4, f.month_5, f.month_6,
                    f.month_7, f.month_8, f.month_9, f.month_10, f.month_11, f.month_12,
                    s.price as forecast_price,
                    s.currency as forecast_currency
                FROM item_fg a
                LEFT JOIN forecasts f ON a.id = f.item_fg_id 
                LEFT JOIN standard_price_fg s ON s.item_fg_id = f.item_fg_id
                LEFT JOIN (
                    SELECT 
                        a.item_fg_id, a.customer_id, a.delivery_note_date, a.delivery_note_no,
                        SUM(a.qty) AS total_qty,
                        (CASE WHEN a.sales_order_no_rm IS NOT NULL THEN e.currency ELSE d.currency END) AS currency,
                        (CASE WHEN a.sales_order_no_rm IS NOT NULL THEN e.price ELSE d.price END) AS price
                    FROM delivery_notes a
                    LEFT JOIN sales_orders d ON a.sales_order_no = d.sales_order_no AND a.item_fg_id = d.item_fg_id
                    LEFT JOIN sales_order_rm e ON a.sales_order_no_rm = e.sales_order_no AND a.item_fg_id = e.item_fg_id
                    WHERE a.trans_type = 'SALES'
                    AND a.delivery_note_date BETWEEN ? AND ?
                    GROUP BY a.id -- Group by ID untuk detail rate per DN
                ) dn ON a.id = dn.item_fg_id AND f.customer_id = dn.customer_id
                WHERE f.customer_id LIKE ? 
                AND f.p_year = ? 
                AND a.division_id LIKE ? ";

        $params = [$period_start_date, $period_end_date, "%$filter_customer_id%", date('Y', strtotime($period_end_date)), "%$filter_division%"];
        $raw_data = $this->db->query($query, $params)->result_array();

        // INITIALIZE MAPPING ARRAYS
        $mapping_forecast_qty = array_fill_keys($labels, 0);
        $mapping_forecast_amt = array_fill_keys($labels, 0);
        $mapping_actual_qty   = array_fill_keys($labels, 0);
        $mapping_actual_amt   = array_fill_keys($labels, 0);

        foreach ($raw_data as $row) {
            // --- ACTUAL MAPPING (Qty & Amount) ---
            if (isset($mapping_actual_qty[$row['period_key']])) {
                $qty = (float)$row['actual_qty'];
                $rate = $this->_find_rate_in_cache($row['delivery_note_no'], $row['currency']);
                
                $mapping_actual_qty[$row['period_key']] += $qty;
                $mapping_actual_amt[$row['period_key']] += ($qty * (float)$row['price'] * (float)$rate);
            }

            // --- FORECAST MAPPING (Qty & Amount) ---
            foreach ($labels as $label_key) {
                $month_idx = 0;
                if ($filter_period_type == 'monthly') {
                    $month_idx = (int)substr($label_key, 5, 2);
                }

                // Ambil Rate Forecast
                $f_rate = $this->_find_rate_in_cache(null, $row['forecast_currency']); 
                $f_price = (float)$row['forecast_price'];

                if ($month_idx > 0) {
                    $qty_f = (float)$row["month_" . $month_idx];
                    $mapping_forecast_qty[$label_key] += $qty_f;
                    $mapping_forecast_amt[$label_key] += ($qty_f * $f_price * $f_rate);
                } elseif ($filter_period_type == 'yearly') {
                    // hitung by month_ kolom table forecasts
                    for ($m=1; $m<=12; $m++) { 
                        $qty_f = (float)$row["month_$m"];
                        $mapping_forecast_qty[$label_key] += $qty_f;
                        $mapping_forecast_amt[$label_key] += ($qty_f * $f_price * $f_rate);
                    }
                }
            }
        }

        $result = [
            'labels'       => $trend_labels,
            'title'        => "Forecast vs Sales - " . strtoupper($filter_period_type),
            'amount_title' => "Forecast vs Sales in Amount - " . strtoupper($filter_period_type),
            'qty_title'    => "Forecast vs Sales in QTY - " . strtoupper($filter_period_type),
            'period'       => "Period: " . date($label_format, strtotime($period_start_date)) . " to " . date($label_format, strtotime($period_end_date)),

            'forecast_amount_values' => array_values(array_map('round', array_values($mapping_forecast_amt))),
            'sales_amount_values'    => array_values(array_map('round', array_values($mapping_actual_amt))),
            'forecast_qty_values'    => array_values($mapping_forecast_qty),
            'sales_qty_values'       => array_values($mapping_actual_qty),
        ];

        echo json_encode($result);
    }
}
