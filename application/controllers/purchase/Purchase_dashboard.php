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
class Purchase_dashboard extends CI_Controller
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
        $this->_get_rates = $this->db->get_where('exchange_rates', ['currency_to' => 'IDR', 'deleted' => 0])->result_array();
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['approval'] = $this->crud->read('signatures');

            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_dashboard');
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
        // GET YEAR FROM receipt_date
        $this->db->select('DISTINCT(YEAR(receipt_date)) as year_val', FALSE);
        $this->db->from('purchase_order_receipts');
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

    // Filter Monthly: bulan awal s/d bulan akhir pada tahun terpilih (default Januari s/d Desember tahun ini = 12 bulan)
    private function _get_monthly_range()
    {
        $year        = (int)($this->input->post('filter_year') ?: date('Y'));
        $month_start = (int)($this->input->post('filter_month_start') ?: 1);
        $month_end   = (int)($this->input->post('filter_month_end') ?: 12);

        $month_start = max(1, min(12, $month_start));
        $month_end   = max(1, min(12, $month_end));
        if ($month_start > $month_end) {
            list($month_start, $month_end) = [$month_end, $month_start];
        }

        $labels = [];
        for ($m = $month_start; $m <= $month_end; $m++) {
            $labels[] = sprintf('%04d-%02d', $year, $m);
        }

        return [
            'labels'       => $labels,
            'period_start' => sprintf('%04d-%02d-01', $year, $month_start),
            'period_end'   => date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $year, $month_end))),
        ];
    }

    public function get_dashboard_data()
    {
        $filter_period_type = strtolower($this->input->post('filter_period_type') ? $this->input->post('filter_period_type') : 'monthly');
        $filter_period_value = $this->input->post('filter_period_value');
        if (empty($filter_period_value)) {
            if ($filter_period_type == "daily") $filter_period_value = date('Y-m-d');
            elseif ($filter_period_type == "weekly") $filter_period_value = date('o-\\WW');
            elseif ($filter_period_type == "monthly") $filter_period_value = date('Y-m');
            elseif ($filter_period_type == "yearly") $filter_period_value = date('Y');
        }
        $filter_division    = $this->input->post('filter_division');
        $filter_supplier_id = $this->input->post('filter_supplier_id');
        
        $labels = [];
        $period_start_date  = "";
        $period_end_date    = "";
        $group_by_sql       = "";
        $label_format       = "";
        

        if ($filter_period_type == "daily") {
            $end = new DateTime($filter_period_value);
            $start = (clone $end)->modify('-5 days');
            
            $period_start_date = $start->format('Y-m-d');
            $period_end_date = $end->format('Y-m-d');

            $group_by_sql = "a.receipt_date"; // Default daily
            $label_format = "d M Y";
            
            $period = new DatePeriod($start, new DateInterval('P1D'), (clone $end)->modify('+1 day'));
            foreach ($period as $date) { $labels[] = $date->format('Y-m-d'); }

        } elseif (strtolower($filter_period_type) == "weekly") {
            // Get data per Week. Format Week ISO 8601
            $parts = explode('-W', $filter_period_value); // Input: "2026-W11"
            $year = (int)$parts[0];
            $week = (int)$parts[1];

            $end = new DateTime();
            $end->setISODate($year, $week, 1); 
            
            $start = (clone $end)->modify('-5 weeks'); 
            
            $period_start_date = $start->format('Y-m-d'); 
            $period_end_date = (clone $end)->modify('+6 days')->format('Y-m-d');

            $group_by_sql = "YEARWEEK(a.receipt_date, 3)"; 
            
            $labels = [];
            $trend_labels = [];

            // Generate Labels untuk mapping
            $temp_date = clone $start;
            for ($i = 0; $i < 6; $i++) {
                $iso_year_week = $temp_date->format('oW'); 
                $labels[] = $iso_year_week;

                // line 1 : Week-1
                $week_num = (int)$temp_date->format('W');
                // line 2 : (29 Dec - 04 Jan)
                $monday = $temp_date->format('d M');
                $sunday = (clone $temp_date)->modify('+6 days')->format('d M');
                
                // set as array untuk ApexCharts
                $trend_labels[] = ["Week-$week_num", "($monday - $sunday)"];

                $temp_date->modify('+1 week');
            }

        } elseif ($filter_period_type == "monthly") {
            $range = $this->_get_monthly_range();
            $period_start_date = $range['period_start'];
            $period_end_date = $range['period_end'];

            $group_by_sql = "DATE_FORMAT(a.receipt_date, '%Y-%m')";
            $label_format = "M-Y";

            $labels = $range['labels'];

        } elseif ($filter_period_type == "yearly") {
            $year = $filter_period_value; // Input: 2026
            $start_year = $year - 5;
            
            $period_start_date = "$start_year-01-01";
            $period_end_date = "$year-12-31";
            
            $group_by_sql = "YEAR(a.receipt_date)";
            $label_format = "Y";

            for ($i = $start_year; $i <= $year; $i++) { $labels[] = (string)$i; }

        } else {
            // Period Type tidak diketahui
            echo json_encode([
                'title'           => "Failed!", 
                'period'          => "Unknown Period Type",
                'trend_labels'    => [1, 2, 3, 4, 5, 6],
                'trend_values'    => [0, 0, 0, 0, 0, 0],
                'supplier_labels' => [1, 2, 3, 4, 5, 6],
                'supplier_values' => [0, 0, 0, 0, 0, 0],
                'avg_values'      => [0, 0, 0, 0, 0, 0],
                'conclusion'      => null,
                'impact'          => null,
            ]);
            return;
        }

        // Subquery Get Amount & Exchange Rate to IDR
        $query_amount = "((CASE 
                WHEN COALESCE(d.discount_nominal,0) > 0 THEN COALESCE(d.total,0) / NULLIF(COALESCE(d.qty,0),0) 
                ELSE (COALESCE(d.total,0) - ((COALESCE(d.total,0) / NULLIF(COALESCE(d.total_sub,0),0)) * COALESCE(d.discount_total,0))) / NULLIF(COALESCE(d.qty,0),0)
            END) * (CASE 
                WHEN COALESCE(d.currency, f.currency, 'IDR') = 'IDR' THEN 1.0 
                ELSE COALESCE(er.middle, 0.0) 
            END))";

        // Keywords Filter
        $params = [
            "%$filter_supplier_id%", 
            "%$filter_division%", 
            $period_start_date, 
            $period_end_date,
        ];

        // Update SQL Trend: Grouping dinamis menggunakan $group_by_sql
        $sql_trend = "SELECT $group_by_sql as period_key, SUM(a.qty_receipt2 * $query_amount) AS total_amount
                    FROM purchase_order_receipts a
                    LEFT JOIN item_rm b ON a.item_rm_id = b.id
                    LEFT JOIN purchase_orders d ON a.po_no = d.po_no AND a.item_rm_id = d.item_rm_id AND COALESCE(a.specification, '') = COALESCE(d.specification, '')
                    LEFT JOIN suppliers f ON d.supplier_id = f.id
                    LEFT JOIN exchange_rates er ON er.currency_to = 'IDR' AND er.currency_from = COALESCE(d.currency, f.currency, 'IDR') AND er.deleted = 0 AND er.start_date = DATE_FORMAT(DATE_SUB(a.receipt_date, INTERVAL 1 MONTH), '%Y-%m-01')
                    WHERE (a.supplier_id LIKE ?) AND (b.division LIKE ?) 
                    AND (a.receipt_date BETWEEN ? AND ?)
                    AND a.deleted = 0
                    GROUP BY period_key ORDER BY period_key ASC";
        $trend_result = $this->db->query($sql_trend, $params)->result_array();

        // Mapping Data
        $mapped_trend = array_fill_keys($labels, 0);
        foreach ($trend_result as $row) {
            if (isset($mapped_trend[$row['period_key']])) {
                $mapped_trend[$row['period_key']] = (float)$row['total_amount'];
            }
        }
        $trend_values = array_values($mapped_trend);

        // Label formatting
        if (empty($trend_labels)) {
            $trend_labels = array_map(function($l) use ($filter_period_type) {
                if ($filter_period_type == 'daily') return date('d M Y', strtotime((string) $l));
                if ($filter_period_type == 'monthly') return date('M Y', strtotime($l . "-01"));
                return $l; // Yearly
            }, $labels);
        }

        
        // QUERY TOP 10 SUPPLIER (Berdasarkan Nama Supplier)
        $sql_supplier = "SELECT COALESCE(f.name, 'Unknown') as supplier_name, SUM(a.qty_receipt2 * $query_amount) AS total_amount
                        FROM purchase_order_receipts a
                        LEFT JOIN item_rm b ON a.item_rm_id = b.id
                        LEFT JOIN purchase_orders d ON a.po_no = d.po_no AND a.item_rm_id = d.item_rm_id AND COALESCE(a.specification, '') = COALESCE(d.specification, '')
                        LEFT JOIN suppliers f ON d.supplier_id = f.id
                        LEFT JOIN exchange_rates er ON er.currency_to = 'IDR' AND er.currency_from = COALESCE(d.currency, f.currency, 'IDR') AND er.deleted = 0 AND er.start_date = DATE_FORMAT(DATE_SUB(a.receipt_date, INTERVAL 1 MONTH), '%Y-%m-01')
                        WHERE (a.supplier_id LIKE ?) AND (b.division LIKE ?) 
                        AND (a.receipt_date BETWEEN ? AND ?)
                        AND a.deleted = 0
                        GROUP BY f.name 
                        ORDER BY total_amount DESC 
                        LIMIT 10";
        $supplier_result = $this->db->query($sql_supplier, $params)->result_array();

        // --- MAPPING DATA SUPPLIER ---
        $supplier_labels = [];
        $supplier_values = [];
        foreach ($supplier_result as $row) {
            $supplier_labels[] = $row['supplier_name'] ?? 'Unknown';
            $supplier_values[] = (float)$row['total_amount'];
        }

        // --- PREPARE FINAL RESPONSE ---
        $trend_values = array_values($mapped_trend);
        // $trend_labels = array_map(function($l) { return date('d M Y', strtotime((string) $l)); }, $labels); // comment: bug labels yearly
        
        // Average hanya dari periode yang sudah berjalan (s/d hari/minggu/bulan/tahun ini), periode mendatang tidak dihitung
        $current_key = ['daily' => date('Y-m-d'), 'weekly' => date('oW'), 'monthly' => date('Y-m'), 'yearly' => date('Y')][$filter_period_type] ?? null;
        $elapsed_values = [];
        foreach ($mapped_trend as $key => $val) {
            if ($current_key === null || (string)$key <= $current_key) {
                $elapsed_values[] = $val;
            }
        }
        $average = count($elapsed_values) > 0 ? (array_sum($elapsed_values) / count($elapsed_values)) : 0;
        $avg_values = array_fill(0, count($trend_values), round($average, 2));

        $division_text = !empty($filter_division) ? strtoupper($filter_division) : "ALL Division";
        $title = "Purchase Amount (IDR) " . ucfirst($filter_period_type) . " - " . $division_text;

        if ($filter_period_type == 'yearly') {
            $period_text = "Period: " . $start_year . " to " . $year;
        } elseif ($filter_period_type == 'weekly') {
            $period_text = "Period: " . $start->format('d M') . " - " . date('d M Y', strtotime((string) $period_end_date)) . " (6 Weeks)";
        } else {
            $period_text = "Period: " . date($label_format, strtotime((string) $period_start_date)) . " to " . date($label_format, strtotime((string) $period_end_date));
        }

        $result = [
            'trend_labels'    => $trend_labels,
            'trend_values'    => $trend_values,
            'supplier_labels' => $supplier_labels,
            'supplier_values' => $supplier_values,
            'avg_values'      => $avg_values,
            'title'           => $title,
            'period'          => $period_text,
            'conclusion'      => null,
            'impact'          => null,
        ];

        echo json_encode($result);
    }


    public function get_plan_actual_data() 
    {
        $filter_period_type = strtolower($this->input->post('filter_period_type') ? $this->input->post('filter_period_type') : 'monthly');
        $filter_period_value = $this->input->post('filter_period_value');
        if (empty($filter_period_value)) {
            if ($filter_period_type == "daily") $filter_period_value = date('Y-m-d');
            elseif ($filter_period_type == "weekly") $filter_period_value = date('o-\\WW');
            elseif ($filter_period_type == "monthly") $filter_period_value = date('Y-m');
            elseif ($filter_period_type == "yearly") $filter_period_value = date('Y');
        }
        $filter_division    = $this->input->post('filter_division');
        $filter_supplier_id = $this->input->post('filter_supplier_id');
        
        $labels = [];
        $period_start_date  = "";
        $period_end_date    = "";
        $group_by_sql       = "";
        $label_format       = "";


        if ($filter_period_type == "daily") {
            $end = new DateTime($filter_period_value);
            $start = (clone $end)->modify('-5 days');
            
            $period_start_date = $start->format('Y-m-d');
            $period_end_date = $end->format('Y-m-d');

            $group_by_sql = "po_date"; // Default daily
            $label_format = "d M Y";
            
            $period = new DatePeriod($start, new DateInterval('P1D'), (clone $end)->modify('+1 day'));
            foreach ($period as $date) { $labels[] = $date->format('Y-m-d'); }

        } elseif (strtolower($filter_period_type) == "weekly") {
            // Get data per Week. Format Week ISO 8601
            $parts = explode('-W', $filter_period_value); // Input: "2026-W11"
            $year = (int)$parts[0];
            $week = (int)$parts[1];

            $end = new DateTime();
            $end->setISODate($year, $week, 1); 
            
            $start = (clone $end)->modify('-5 weeks'); 
            
            $period_start_date = $start->format('Y-m-d'); 
            $period_end_date = (clone $end)->modify('+6 days')->format('Y-m-d');

            $group_by_sql = "YEARWEEK(po_date, 3)"; 
            
            $labels = [];
            $trend_labels = [];

            // Generate Labels untuk mapping
            $temp_date = clone $start;
            for ($i = 0; $i < 6; $i++) {
                $iso_year_week = $temp_date->format('oW'); 
                $labels[] = $iso_year_week;

                // line 1 : Week-1
                $week_num = (int)$temp_date->format('W');
                // line 2 : (29 Dec - 04 Jan)
                $monday = $temp_date->format('d M');
                $sunday = (clone $temp_date)->modify('+6 days')->format('d M');
                
                // set as array untuk ApexCharts
                $trend_labels[] = ["Week-$week_num", "($monday - $sunday)"];

                $temp_date->modify('+1 week');
            }

        } elseif ($filter_period_type == "monthly") {
            $range = $this->_get_monthly_range();
            $period_start_date = $range['period_start'];
            $period_end_date = $range['period_end'];

            $group_by_sql = "DATE_FORMAT(po_date, '%Y-%m')";
            $label_format = "M-Y";

            $labels = $range['labels'];

        } elseif ($filter_period_type == "yearly") {
            $year = $filter_period_value; // Input: 2026
            $start_year = $year - 5;
            
            $period_start_date = "$start_year-01-01";
            $period_end_date = "$year-12-31";
            
            $group_by_sql = "YEAR(po_date)";
            $label_format = "Y";

            for ($i = $start_year; $i <= $year; $i++) { $labels[] = (string)$i; }

        } else {
            // Period Type tidak diketahui
            echo json_encode([
                'title'         => "Failed!", 
                'period'        => "Unknown Period Type",
                'labels'        => [1, 2, 3, 4, 5, 6],
                'plan_values'   => [0, 0, 0, 0, 0, 0],
                'actual_values' => [0, 0, 0, 0, 0, 0],
            ]);
            return;
        }

        $query_price_po = "(CASE 
            WHEN COALESCE(a.discount_nominal,0) > 0 THEN COALESCE(a.total,0) / NULLIF(COALESCE(a.qty,0),0) 
            ELSE (COALESCE(a.total,0) - ((COALESCE(a.total,0) / NULLIF(COALESCE(a.total_sub,0),0)) * COALESCE(a.discount_total,0))) / NULLIF(COALESCE(a.qty,0),0)
        END)";

        $query_price_por = "(CASE 
            WHEN COALESCE(d.discount_nominal,0) > 0 THEN COALESCE(d.total,0) / NULLIF(COALESCE(d.qty,0),0) 
            ELSE (COALESCE(d.total,0) - ((COALESCE(d.total,0) / NULLIF(COALESCE(d.total_sub,0),0)) * COALESCE(d.discount_total,0))) / NULLIF(COALESCE(d.qty,0),0)
        END)";

        // QTY PLAN (Dari purchase_orders)
        $query_plan = "SELECT 
                            a.item_rm_id, 
                            a.po_no as no_ref,
                            a.po_date as date_ref, 
                            a.qty, 
                            $query_price_po AS price,
                            COALESCE(a.currency, 'IDR') as currency,
                            (CASE 
                                WHEN COALESCE(a.currency, 'IDR') = 'IDR' THEN 1.0
                                ELSE COALESCE(er.middle, 0.0)
                            END) AS rate,
                            e.name as category_name,
                            c.name as family_name
                        FROM purchase_orders a
                        LEFT JOIN item_rm b ON a.item_rm_id = b.id
                        LEFT JOIN item_familys c ON b.item_family_id = c.id
                        LEFT JOIN item_categories e ON b.item_category_id = e.id
                        LEFT JOIN exchange_rates er ON er.currency_to = 'IDR' AND er.currency_from = COALESCE(a.currency, 'IDR') AND er.deleted = 0 AND er.start_date = DATE_FORMAT(DATE_SUB(a.po_date, INTERVAL 1 MONTH), '%Y-%m-01')
                        WHERE a.po_date BETWEEN '$period_start_date' AND '$period_end_date'
                        AND a.supplier_id LIKE '%$filter_supplier_id%'
                        AND b.division LIKE '%$filter_division%'
                        AND a.deleted = 0
                        ORDER BY a.po_date";
        $data_plan = $this->db->query($query_plan)->result_array();

        // QTY ACTUAL (Dari purchase_order_receipts)
        $query_actual = "SELECT 
                            a.item_rm_id,
                            a.receipt_no as no_ref,
                            a.receipt_date as date_ref, 
                            a.qty_receipt2 as qty, 
                            d.po_no,
                            $query_price_por AS price,
                            COALESCE(d.currency, f.currency, 'IDR') as currency,
                            (CASE 
                                WHEN COALESCE(d.currency, f.currency, 'IDR') = 'IDR' THEN 1.0
                                ELSE COALESCE(er.middle, 0.0)
                            END) AS rate,
                            e.name as category_name,
                            c.name as family_name
                        FROM purchase_order_receipts a
                        LEFT JOIN item_rm b ON a.item_rm_id = b.id
                        LEFT JOIN item_familys c ON b.item_family_id = c.id
                        LEFT JOIN purchase_orders d ON a.po_no = d.po_no AND a.item_rm_id = d.item_rm_id AND COALESCE(a.specification, '') = COALESCE(d.specification, '')
                        LEFT JOIN suppliers f ON d.supplier_id = f.id
                        LEFT JOIN exchange_rates er ON er.currency_to = 'IDR' AND er.currency_from = COALESCE(d.currency, f.currency, 'IDR') AND er.deleted = 0 AND er.start_date = DATE_FORMAT(DATE_SUB(a.receipt_date, INTERVAL 1 MONTH), '%Y-%m-01')
                        LEFT JOIN item_categories e ON b.item_category_id = e.id
                        WHERE a.receipt_date BETWEEN '$period_start_date' AND '$period_end_date'
                        AND a.supplier_id LIKE '%$filter_supplier_id%'
                        AND b.division LIKE '%$filter_division%'
                        AND a.deleted = 0
                        ORDER BY a.receipt_date";
        $data_actual = $this->db->query($query_actual)->result_array();

        // --- PROSES MAPPING DATA ---
        // Set default value 0 berdasarkan labels
        $mapped_plan = array_fill_keys($labels, 0);
        $mapped_actual = array_fill_keys($labels, 0);

        // Mapping Data Plan
        foreach ($data_plan as $row) {
            $key = "";
            $date = $row['date_ref'];
            
            if ($filter_period_type == "daily") {
                $key = date('Y-m-d', strtotime((string) $date));
            } elseif ($filter_period_type == "weekly") {
                $key = date('oW', strtotime((string) $date)); // ISO-8601 week year + week number
            } elseif ($filter_period_type == "monthly") {
                $key = date('Y-m', strtotime((string) $date));
            } elseif ($filter_period_type == "yearly") {
                $key = date('Y', strtotime((string) $date));
            }

            if (isset($mapped_plan[$key])) {
                // HITUNG AMOUNT
                $rate = (float)($row['rate'] ?? $this->_find_rate_in_cache($row['date_ref'], $row['currency']));
                $amount = (float)$row['qty'] * (float)$row['price'] * $rate;
                
                $mapped_plan[$key] += $amount;
            }
        }

        // Mapping Data Actual
        foreach ($data_actual as $row) {
            $key = "";
            $date = $row['date_ref'];

            if ($filter_period_type == "daily") {
                $key = date('Y-m-d', strtotime((string) $date));
            } elseif ($filter_period_type == "weekly") {
                $key = date('oW', strtotime((string) $date));
            } elseif ($filter_period_type == "monthly") {
                $key = date('Y-m', strtotime((string) $date));
            } elseif ($filter_period_type == "yearly") {
                $key = date('Y', strtotime((string) $date));
            }

            if (isset($mapped_actual[$key])) {
                // HITUNG AMOUNT
                $rate = (float)($row['rate'] ?? $this->_find_rate_in_cache($row['date_ref'], $row['currency']));
                $amount = (float)$row['qty'] * (float)$row['price'] * $rate;
                
                $mapped_actual[$key] += $amount;
            }
        }

        // --- PREPARE FINAL RESPONSE ---
        
        $display_labels = $labels;
        if ($filter_period_type == "weekly") {
            $display_labels = $trend_labels;
        } elseif ($filter_period_type == "daily") {
            $display_labels = array_map(function($l) { return date('d M Y', strtotime((string) $l)); }, $labels);
        } elseif ($filter_period_type == "monthly") {
            $display_labels = array_map(function($l) { return date('M Y', strtotime($l . "-01")); }, $labels);
        }

        $division_text = !empty($filter_division) ? strtoupper($filter_division) : "ALL Division";
        $title = "Purchase Amount (IDR) Plan VS Actual - " . $division_text;
        $period_text = "Period: " . date('d M Y', strtotime((string) $period_start_date)) . " to " . date('d M Y', strtotime((string) $period_end_date));

        echo json_encode([
            'title'         => $title, 
            'period'        => $period_text,
            'labels'        => $display_labels,
            'plan_values'   => array_values($mapped_plan),
            'actual_values' => array_values($mapped_actual),
        ]);
    }


    public function get_purchase_by_family_data() 
    {
        $filter_period_type = strtolower($this->input->post('filter_period_type') ? $this->input->post('filter_period_type') : 'monthly');
        $filter_period_value = $this->input->post('filter_period_value');
        if (empty($filter_period_value)) {
            if ($filter_period_type == "daily") $filter_period_value = date('Y-m-d');
            elseif ($filter_period_type == "weekly") $filter_period_value = date('o-\\WW');
            elseif ($filter_period_type == "monthly") $filter_period_value = date('Y-m');
            elseif ($filter_period_type == "yearly") $filter_period_value = date('Y');
        }
        $filter_division    = $this->input->post('filter_division');
        $filter_supplier_id = $this->input->post('filter_supplier_id');

        $family_charts = ['qty', 'child_part', 'virgin', 'consumable', 'master_batch', 'stamping', 'subcont'];
        
        $labels = [];
        $period_start_date  = "";
        $period_end_date    = "";
        $group_by_sql       = "";
        $label_format       = "";

        if ($filter_period_type == "daily") {
            $end = new DateTime($filter_period_value);
            $start = (clone $end)->modify('-5 days');
            
            $period_start_date = $start->format('Y-m-d');
            $period_end_date = $end->format('Y-m-d');

            $group_by_sql = "po_date"; // Default daily
            $label_format = "d M Y";
            
            $period = new DatePeriod($start, new DateInterval('P1D'), (clone $end)->modify('+1 day'));
            foreach ($period as $date) { $labels[] = $date->format('Y-m-d'); }

        } elseif (strtolower($filter_period_type) == "weekly") {
            // Get data per Week. Format Week ISO 8601
            $parts = explode('-W', $filter_period_value); // Input: "2026-W11"
            $year = (int)$parts[0];
            $week = (int)$parts[1];

            $end = new DateTime();
            $end->setISODate($year, $week, 1); 
            
            $start = (clone $end)->modify('-5 weeks'); 
            
            $period_start_date = $start->format('Y-m-d'); 
            $period_end_date = (clone $end)->modify('+6 days')->format('Y-m-d');

            $group_by_sql = "YEARWEEK(po_date, 3)"; 
            
            $labels = [];
            $trend_labels = [];

            // Generate Labels untuk mapping
            $temp_date = clone $start;
            for ($i = 0; $i < 6; $i++) {
                $iso_year_week = $temp_date->format('oW'); 
                $labels[] = $iso_year_week;

                // line 1 : Week-1
                $week_num = (int)$temp_date->format('W');
                // line 2 : (29 Dec - 04 Jan)
                $monday = $temp_date->format('d M');
                $sunday = (clone $temp_date)->modify('+6 days')->format('d M');
                
                // set as array untuk ApexCharts
                $trend_labels[] = ["Week-$week_num", "($monday - $sunday)"];

                $temp_date->modify('+1 week');
            }

        } elseif ($filter_period_type == "monthly") {
            $range = $this->_get_monthly_range();
            $period_start_date = $range['period_start'];
            $period_end_date = $range['period_end'];

            $group_by_sql = "DATE_FORMAT(po_date, '%Y-%m')";
            $label_format = "M-Y";

            $labels = $range['labels'];

        } elseif ($filter_period_type == "yearly") {
            $year = $filter_period_value; // Input: 2026
            $start_year = $year - 5;
            
            $period_start_date = "$start_year-01-01";
            $period_end_date = "$year-12-31";
            
            $group_by_sql = "YEAR(po_date)";
            $label_format = "Y";

            for ($i = $start_year; $i <= $year; $i++) { $labels[] = (string)$i; }

        } else {
            // Period Type tidak diketahui
            echo json_encode([
                'title'         => "Failed!", 
                'period'        => "Unknown Period Type",
                'labels'        => [1, 2, 3, 4, 5, 6],
                'plan_values'   => [0, 0, 0, 0, 0, 0],
                'actual_values' => [0, 0, 0, 0, 0, 0],
            ]);
            return;
        }

        $query_price_po = "(CASE 
            WHEN COALESCE(a.discount_nominal,0) > 0 THEN COALESCE(a.total,0) / NULLIF(COALESCE(a.qty,0),0) 
            ELSE (COALESCE(a.total,0) - ((COALESCE(a.total,0) / NULLIF(COALESCE(a.total_sub,0),0)) * COALESCE(a.discount_total,0))) / NULLIF(COALESCE(a.qty,0),0)
        END)";

        $query_price_por = "(CASE 
            WHEN COALESCE(d.discount_nominal,0) > 0 THEN COALESCE(d.total,0) / NULLIF(COALESCE(d.qty,0),0) 
            ELSE (COALESCE(d.total,0) - ((COALESCE(d.total,0) / NULLIF(COALESCE(d.total_sub,0),0)) * COALESCE(d.discount_total,0))) / NULLIF(COALESCE(d.qty,0),0)
        END)";

        // PLAN (Dari purchase_orders)
        $query_plan = "SELECT 
                            a.item_rm_id, 
                            a.po_no as no_ref,
                            a.po_date as date_ref, 
                            a.qty, 
                            $query_price_po AS price,
                            COALESCE(a.currency, 'IDR') as currency,
                            (CASE 
                                WHEN COALESCE(a.currency, 'IDR') = 'IDR' THEN 1.0
                                ELSE COALESCE(er.middle, 0.0)
                            END) AS rate,
                            e.name as category_name,
                            c.name as family_name
                        FROM purchase_orders a
                        LEFT JOIN item_rm b ON a.item_rm_id = b.id
                        LEFT JOIN item_familys c ON b.item_family_id = c.id
                        LEFT JOIN item_categories e ON b.item_category_id = e.id
                        LEFT JOIN exchange_rates er ON er.currency_to = 'IDR' AND er.currency_from = COALESCE(a.currency, 'IDR') AND er.deleted = 0 AND er.start_date = DATE_FORMAT(DATE_SUB(a.po_date, INTERVAL 1 MONTH), '%Y-%m-01')
                        WHERE a.po_date BETWEEN '$period_start_date' AND '$period_end_date'
                        AND a.supplier_id LIKE '%$filter_supplier_id%'
                        AND b.division LIKE '%$filter_division%'
                        AND a.deleted = 0
                        ORDER BY a.po_date";
        $data_plan = $this->db->query($query_plan)->result_array();

        // ACTUAL (Dari purchase_order_receipts)
        $query_actual = "SELECT 
                            a.item_rm_id,
                            a.receipt_no as no_ref,
                            a.receipt_date as date_ref, 
                            a.qty_receipt2 as qty, 
                            d.po_no,
                            $query_price_por AS price,
                            COALESCE(d.currency, f.currency, 'IDR') as currency,
                            (CASE 
                                WHEN COALESCE(d.currency, f.currency, 'IDR') = 'IDR' THEN 1.0
                                ELSE COALESCE(er.middle, 0.0)
                            END) AS rate,
                            e.name as category_name,
                            c.name as family_name
                        FROM purchase_order_receipts a
                        LEFT JOIN item_rm b ON a.item_rm_id = b.id
                        LEFT JOIN item_familys c ON b.item_family_id = c.id
                        LEFT JOIN purchase_orders d ON a.po_no = d.po_no AND a.item_rm_id = d.item_rm_id AND COALESCE(a.specification, '') = COALESCE(d.specification, '')
                        LEFT JOIN suppliers f ON d.supplier_id = f.id
                        LEFT JOIN exchange_rates er ON er.currency_to = 'IDR' AND er.currency_from = COALESCE(d.currency, f.currency, 'IDR') AND er.deleted = 0 AND er.start_date = DATE_FORMAT(DATE_SUB(a.receipt_date, INTERVAL 1 MONTH), '%Y-%m-01')
                        LEFT JOIN item_categories e ON b.item_category_id = e.id
                        WHERE a.receipt_date BETWEEN '$period_start_date' AND '$period_end_date'
                        AND a.supplier_id LIKE '%$filter_supplier_id%'
                        AND b.division LIKE '%$filter_division%'
                        AND a.deleted = 0
                        ORDER BY a.receipt_date";
        $data_actual = $this->db->query($query_actual)->result_array();
        

        // --- PROSES MAPPING DATA ---
        
        // Set data default 0
        $data_mapping = [];
        foreach ($family_charts as $chart_key) {
            $data_mapping[$chart_key]['plan'] = array_fill_keys($labels, 0);
            $data_mapping[$chart_key]['actual'] = array_fill_keys($labels, 0);
        }

        // Key by Period Type
        $period_type = function($date) use ($filter_period_type) {
            if ($filter_period_type == "daily") return date('Y-m-d', strtotime((string) $date));
            if ($filter_period_type == "weekly") return date('oW', strtotime((string) $date));
            if ($filter_period_type == "monthly") return date('Y-m', strtotime((string) $date));
            if ($filter_period_type == "yearly") return date('Y', strtotime((string) $date));
            return "";
        };

        // Mapping Data Plan
        foreach ($data_plan as $row) {
            $key = $period_type($row['date_ref']);
            $f_name = strtoupper($row['family_name'] ?? '');
            
            // Hitung Amount IDR
            $rate = (float)($row['rate'] ?? $this->_find_rate_in_cache($row['date_ref'], $row['currency']));
            $amount = (float)$row['qty'] * (float)$row['price'] * $rate;

            if (isset($data_mapping['qty']['plan'][$key])) {
                // Akumulasi Amount ke total
                $data_mapping['qty']['plan'][$key] += $amount;

                // Akumulasi ke Chart Family spesifik
                if (strpos($f_name, 'CHILD') !== false) $data_mapping['child_part']['plan'][$key] += $amount;
                elseif (strpos($f_name, 'VIRGIN') !== false) $data_mapping['virgin']['plan'][$key] += $amount;
                elseif (strpos($f_name, 'CONSUMABLE') !== false) $data_mapping['consumable']['plan'][$key] += $amount;
                elseif (strpos($f_name, 'MASTER') !== false) $data_mapping['master_batch']['plan'][$key] += $amount;
                elseif (strpos($f_name, 'STAMPING') !== false) $data_mapping['stamping']['plan'][$key] += $amount;
                elseif (strpos($f_name, 'SUBCONT') !== false) $data_mapping['subcont']['plan'][$key] += $amount;
            }
        }

        // Mapping Data Actual
        foreach ($data_actual as $row) {
            $key = $period_type($row['date_ref']);
            $f_name = strtoupper($row['family_name'] ?? '');

            // Hitung Amount IDR
            $rate = (float)($row['rate'] ?? $this->_find_rate_in_cache($row['date_ref'], $row['currency']));
            $amount = (float)$row['qty'] * (float)$row['price'] * $rate;

            if (isset($data_mapping['qty']['actual'][$key])) {
                // Akumulasi Amount ke total
                $data_mapping['qty']['actual'][$key] += $amount;

                // Akumulasi ke Chart Family spesifik
                if (strpos($f_name, 'CHILD') !== false) $data_mapping['child_part']['actual'][$key] += $amount;
                elseif (strpos($f_name, 'VIRGIN') !== false) $data_mapping['virgin']['actual'][$key] += $amount;
                elseif (strpos($f_name, 'CONSUMABLE') !== false) $data_mapping['consumable']['actual'][$key] += $amount;
                elseif (strpos($f_name, 'MASTER') !== false) $data_mapping['master_batch']['actual'][$key] += $amount;
                elseif (strpos($f_name, 'STAMPING') !== false) $data_mapping['stamping']['actual'][$key] += $amount;
                elseif (strpos($f_name, 'SUBCONT') !== false) $data_mapping['subcont']['actual'][$key] += $amount;
            }
        }

        // --- PREPARE FINAL RESPONSE ---

        $display_labels = $labels;
        if ($filter_period_type == "weekly") {
            $display_labels = $trend_labels;
        } elseif ($filter_period_type == "daily") {
            $display_labels = array_map(function($l) { return date('d M Y', strtotime((string) $l)); }, $labels);
        } elseif ($filter_period_type == "monthly") {
            $display_labels = array_map(function($l) { return date('M Y', strtotime($l . "-01")); }, $labels);
        }

        $division_text = !empty($filter_division) ? strtoupper($filter_division) : "ALL Division";
        
        // Bangun array response dasar
        $final_res = [
            'title'  => "Purchase Amount (IDR) by Product Family - " . $division_text,
            'period' => "Period: " . date('d M Y', strtotime((string) $period_start_date)) . " to " . date('d M Y', strtotime((string) $period_end_date)),
            'labels' => $display_labels,
        ];

        // Set data plan & actual untuk setiap kategori family
        foreach ($family_charts as $cat) {
            $final_res[$cat . '_title']  = "Purchase Amount (IDR) " . strtoupper(str_replace("_", " ", $cat)) . " - " . $division_text; // title per family
            $final_res[$cat . '_plan']   = array_values($data_mapping[$cat]['plan']);
            $final_res[$cat . '_actual'] = array_values($data_mapping[$cat]['actual']);
        }

        echo json_encode($final_res);
    }

    // Get Rate di memory (Optimasi mencegah N+1 Query)
    private function _find_rate_in_cache($date, $currency) {
        if ($currency == "IDR" || empty($currency)) return 1.0;
        
        $monthBf = date('Y-m-01', strtotime('-1 month', strtotime((string) $date)));
        foreach ($this->_get_rates as $r) {
            if ($r['currency_from'] == $currency && $r['start_date'] == $monthBf) {
                return (float)$r['middle'];
            }
        }
        return 0.0;
    }

}
