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
                'text'  => "Week-{$i} ({$start} - {$end})",
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

    public function get_dashboard_data_existing() 
    {
        $filter_from        = $this->input->post('from');
        $filter_to          = $this->input->post('to');
        $filter_display     = $this->input->post('display');
        $filter_division    = $this->input->post('division');
        $filter_supplier_id = $this->input->post('supplier_id');
        $filter_category_id = $this->input->post('category_id');

        // --- VALIDASI PERIODE 6 BULAN ---
        if ($filter_display == "MONTHLY") {
            $start_check = new DateTime($filter_from);
            $end_check   = new DateTime($filter_to);
            $diff        = $start_check->diff($end_check);
            
            // Hitung total bulan dari selisih tahun dan bulan
            $total_months = ($diff->y * 12) + $diff->m;
    
            // Jika selisih kurang dari 6 bulan, ubah filter_from jadi 6 bulan ke belakang dari filter_to
            if ($total_months < 6) {
                $new_start = clone $end_check;
                $new_start->modify('-6 months');
                $filter_from = $new_start->format('Y-m-d');
            }
        }

        // Prepare Data kosong bernilai 0
        $purchase_data = [];
        $purchase_count = [];

        $start = new DateTime($filter_from);
        $end   = new DateTime($filter_to);
        $end->modify('+1 day'); // Include end date
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $dt) {
            $key = "";
            
            if ($filter_display == "DAILY") {
                $key = $dt->format("Y-m-d");
            } 
            elseif ($filter_display == "WEEKLY") {
                // Cari hari Senin di minggu tersebut
                $monday = clone $dt;
                if ($monday->format('N') != 1) {
                    $monday->modify('last monday');
                }
                
                // Cari hari Minggu (6 hari setelah Senin)
                $sunday = clone $monday;
                $sunday->modify('+6 days');

                $weekOfMonth = ceil($monday->format('j') / 7);
                
                // Format: W1 April 2026 (6 April - 12 April)
                $key = "W" . $weekOfMonth . " " . $monday->format('M Y') . " (" . $monday->format('j M') . " - " . $sunday->format('j M') . ")";
            } 
            elseif ($filter_display == "MONTHLY") {
                $key = $dt->format("M Y");
            } 
            else {
                $key = $dt->format("Y-m-d");
            }
            
            if (!isset($purchase_data[$key])) {
                $purchase_data[$key] = 0;
            }
        }

        // Main Query
        $query = "SELECT 
                a.receipt_date,
                f.name as supplier_name,
                a.po_no,
                a.qty_receipt2 as qty,
                (CASE 
                    WHEN COALESCE(d.discount_nominal,0) > 0 
                        THEN COALESCE(d.total,0) / NULLIF(COALESCE(d.qty,0),0) 
                    ELSE 
                        (COALESCE(d.total,0) - ((COALESCE(d.total,0) / NULLIF(COALESCE(d.total_sub,0),0)) * COALESCE(d.discount_total,0))) / NULLIF(COALESCE(d.qty,0),0)
                END) AS price 
            FROM purchase_order_receipts a
            LEFT JOIN item_rm b ON a.item_rm_id = b.id
            LEFT JOIN purchase_orders d ON a.po_no = d.po_no AND a.item_rm_id = d.item_rm_id
            LEFT JOIN item_categories e ON b.item_category_id = e.id
            LEFT JOIN suppliers f ON d.supplier_id = f.id
            WHERE (a.supplier_id LIKE ?) 
            AND (b.division LIKE ?) 
            AND (b.item_category_id LIKE ?)
            AND (a.receipt_date BETWEEN ? AND ?)
            ORDER BY a.receipt_date ASC";

        $params = [
            "%$filter_supplier_id%", 
            "%$filter_division%", 
            "%$filter_category_id%", 
            $filter_from, 
            $filter_to
        ];
        
        $records = $this->db->query($query, $params)->result_array();

        // Mapping Data untuk Dashboard
        $total_amount     = 0;
        $unique_pos       = [];
        $supplier_summary = [];

        foreach ($records as $row) {
            $subtotal = (float)$row['qty'] * (float)$row['price'];
            $total_amount += $subtotal;
            $unique_pos[$row['po_no']] = true;

            // Grouping berdasarkan Filter Display
            $time = strtotime($row['receipt_date']);
            if ($filter_display == "DAILY") {
                $key = $row['receipt_date'];
            } 
            elseif ($filter_display == "WEEKLY") {
                $mondayTime = (date('N', $time) == 1) ? $time : strtotime('last monday', $time);
                $sundayTime = strtotime('+6 days', $mondayTime);
                
                $weekOfMonth = ceil(date('j', $mondayTime) / 7);
                
                // Format harus sama : W1 April 2026 (6 April - 12 April)
                $key = "W" . $weekOfMonth . " " . date('M Y', $mondayTime) . " (" . date('j M', $mondayTime) . " - " . date('j M', $sundayTime) . ")";
            } 
            elseif ($filter_display == "MONTHLY") {
                $key = date('M Y', $time);
            } 
            else {
                $key = $row['receipt_date'];
            }

            // Sisipkan data ke purchase_data
            if (isset($purchase_data[$key])) {
                $purchase_data[$key] += $subtotal;
            }

            // Hitung jumlah transaksi per label
            $purchase_count[$key] = ($purchase_count[$key] ?? 0) + 1;

            $supp = $row['supplier_name'] ?? 'Unknown';
            $supplier_summary[$supp] = ($supplier_summary[$supp] ?? 0) + $subtotal;
        }

        // --- HITUNG AVERAGE PER LABEL ---
        $avg_values = [];
        foreach ($purchase_data as $key => $total_val) {
            $count = $purchase_count[$key] ?? 0;
            // Jika ada transaksi, hitung rata-ratanya. Jika tidak, tetap 0.
            $avg_values[] = ($count > 0) ? ($total_val / $count) : 0;
        }

        arsort($supplier_summary); 
        
        $output = [
            'total_amount_formatted' => 'Rp ' . number_format($total_amount, 0, ',', '.'),
            'total_po'     => count($unique_pos),
            'trend_labels' => array_keys($purchase_data),
            'trend_values' => array_values($purchase_data),
            'avg_values'   => $avg_values,
            'counts'       => array_values($purchase_count),
            'supp_labels'  => array_keys($supplier_summary),
            'supp_values'  => array_values($supplier_summary),
            'subtitle'     => "Period: " . date('d M Y', strtotime($filter_from)) . " to " . date('d M Y', strtotime($filter_to))
        ];

        echo json_encode($output);
    }

    public function get_dashboard_data() 
    {
        $filter_period_type = $this->input->post('filter_period_type');
        $filter_period_value = $this->input->post('filter_period_value');
        $filter_division    = $this->input->post('filter_division');
        $filter_supplier_id = $this->input->post('filter_supplier_id');
        
        $labels = [];
        $period_start_date = '';
        $period_end_date = '';

        if (strtolower($filter_period_type) == "daily") {
            $end = new DateTime($filter_period_value);
            $start = clone $end;
            $start->modify('-5 days');

            $period_start_date = $start->format('Y-m-d');
            $period_end_date = $end->format('Y-m-d');

            // Generate Labels: Looping dari start ke end date
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($start, $interval, $end->modify('+1 day')); // +1 agar end date terbawa

            foreach ($period as $date) {
                $labels[] = $date->format('Y-m-d');
            }

        } else {
            echo json_encode([
                'trend_labels' => [],
                'trend_values' => [],
                'title'        => "Failed!",
                'period'       => "Unknown Period Type",
            ]);
            return;
        }

        // Main Query
        $sql = "SELECT 
                    a.receipt_date,
                    SUM(a.qty_receipt2 * (CASE 
                        WHEN COALESCE(d.discount_nominal,0) > 0 
                            THEN COALESCE(d.total,0) / NULLIF(COALESCE(d.qty,0),0) 
                        ELSE 
                            (COALESCE(d.total,0) - ((COALESCE(d.total,0) / NULLIF(COALESCE(d.total_sub,0),0)) * COALESCE(d.discount_total,0))) / NULLIF(COALESCE(d.qty,0),0)
                    END)) AS total_amount
                FROM purchase_order_receipts a
                LEFT JOIN item_rm b ON a.item_rm_id = b.id
                LEFT JOIN purchase_orders d ON a.po_no = d.po_no AND a.item_rm_id = d.item_rm_id
                LEFT JOIN suppliers f ON d.supplier_id = f.id
                WHERE (a.supplier_id LIKE ?) 
                AND (b.division LIKE ?) 
                AND (a.receipt_date BETWEEN ? AND ?)
                GROUP BY a.receipt_date
                ORDER BY a.receipt_date ASC";

        $params = ["%$filter_supplier_id%", "%$filter_division%", $period_start_date, $period_end_date];
        $query_result = $this->db->query($sql, $params)->result_array();

        // Mapping Data ke Labels (Agar tanggal kosong terisi 0)
        $mapped_data = [];
        foreach ($labels as $lbl) {
            $mapped_data[$lbl] = 0; // Default 0
        }

        foreach ($query_result as $row) {
            if (isset($mapped_data[$row['receipt_date']])) {
                $mapped_data[$row['receipt_date']] = (float)$row['total_amount'];
            }
        }

        // Indexed array untuk Chart.js
        $trend_values = array_values($mapped_data);
        $trend_labels = [];
        foreach($labels as $l) { $trend_labels[] = date('d M Y', strtotime($l)); }

        $division_text = !empty($filter_division) ? strtoupper($filter_division) : "ALL Division";
        $period_text   = "Purchase Amount (IDR) ". ucfirst($filter_period_type) . " - " . $division_text;

        echo json_encode([
            'trend_labels' => $trend_labels,
            'trend_values' => $trend_values,
            'title'        => $period_text,
            'period'       => "Period: $period_start_date to $period_end_date",
        ]);
    }


    // Generate KEY Label Date
    private function _generate_date_key($dt, $display) {
        if ($display == "DAILY") return $dt->format("Y-m-d");
        if ($display == "MONTHLY") return $dt->format("M Y");
        
        // Weekly Logic
        $monday = clone $dt;
        if ($monday->format('N') != 1) $monday->modify('last monday');
        $sunday = clone $monday; $sunday->modify('+6 days');
        $weekOfMonth = ceil($monday->format('j') / 7);
        return "W$weekOfMonth " . $monday->format('M Y') . " (" . $monday->format('j M') . " - " . $sunday->format('j M') . ")";
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

    public function get_plan_actual_data() 
    {
        $filter_from        = $this->input->post('from');
        $filter_to          = $this->input->post('to');
        $filter_display     = $this->input->post('display');
        $filter_division    = $this->input->post('division');
        $filter_supplier_id = $this->input->post('supplier_id');
        $filter_category_id = $this->input->post('category_id');

        // Query Rates disisipkan ke private variable
        $this->_get_rates = $this->db->get('standard_exchange_rates')->result_array();

        $chart_types = ['qty', 'child_part', 'virgin', 'consumable', 'master_batch', 'stamping', 'subcont'];
        $data_map = [];

        // --- VALIDASI PERIODE 6 BULAN (MONTHLY) ---
        if ($filter_display == "MONTHLY") {
            $start_check = new DateTime($filter_from);
            $end_check   = new DateTime($filter_to);
            $diff        = $start_check->diff($end_check);
            $total_months = ($diff->y * 12) + $diff->m;
            if ($total_months < 6) {
                $start_check->modify('-6 months');
                $filter_from = $start_check->format('Y-m-d');
            }
        }

        // Generate Label
        $labels = [];
        $start = new DateTime($filter_from);
        $end   = new DateTime($filter_to);
        $end->modify('+1 day');
        $period = new DatePeriod($start, new DateInterval('P1D'), $end);

        foreach ($period as $dt) {
            $key = $this->_generate_date_key($dt, $filter_display);
            if (!in_array($key, $labels)) {
                $labels[] = $key;
                foreach ($chart_types as $cat) {
                    $data_map[$cat]['plan'][$key] = 0;
                    $data_map[$cat]['actual'][$key] = 0;
                }
            }
        }

        // QTY PLAN (Dari purchase_orders)
        $query_plan = "SELECT 
                            a.item_rm_id, 
                            a.po_no as no_ref,
                            a.po_date as date_ref, 
                            a.qty, 
                            a.price,
                            a.currency,
                            e.name as category_name,
                            c.name as family_name
                        FROM purchase_orders a
                        LEFT JOIN item_rm b ON a.item_rm_id = b.id
                        LEFT JOIN item_familys c ON b.item_family_id = c.id
                        LEFT JOIN item_categories e ON b.item_category_id = e.id
                        WHERE a.po_date BETWEEN '$filter_from' AND '$filter_to'
                        AND a.supplier_id LIKE '%$filter_supplier_id%'
                        AND b.division LIKE '%$filter_division%'
                        AND b.item_category_id LIKE '%$filter_category_id%'";
        $res_plan = $this->db->query($query_plan)->result_array();

        $this->_mapping_chart_data($res_plan, $data_map, 'plan', $filter_display);

        // QTY ACTUAL (Dari purchase_order_receipts)
        $query_actual = "SELECT 
                            a.item_rm_id,
                            a.receipt_no as no_ref,
                            a.receipt_date as date_ref, 
                            a.qty_receipt2 as qty, 
                            d.price,
                            d.currency,
                            e.name as category_name,
                            c.name as family_name
                        FROM purchase_order_receipts a
                        LEFT JOIN item_rm b ON a.item_rm_id = b.id
                        LEFT JOIN item_familys c ON b.item_family_id = c.id
                        LEFT JOIN purchase_orders d ON a.po_no = d.po_no AND a.item_rm_id = d.item_rm_id
                        LEFT JOIN item_categories e ON b.item_category_id = e.id
                        WHERE a.receipt_date BETWEEN '$filter_from' AND '$filter_to'
                        AND a.supplier_id LIKE '%$filter_supplier_id%'
                        AND b.division LIKE '%$filter_division%'
                        AND b.item_category_id LIKE '%$filter_category_id%'";
        $res_actual = $this->db->query($query_actual)->result_array();

        $this->_mapping_chart_data($res_actual, $data_map, 'actual', $filter_display);

        // PREPARE OUTPUT
        $output = [
            'period'      => "Period: " . date('d M Y', strtotime($filter_from)) . " to " . date('d M Y', strtotime($filter_to)),
            'week_labels' => $labels,
        ];

        foreach ($chart_types as $cat) {
            $output[$cat . '_plan']   = array_values($data_map[$cat]['plan']);
            $output[$cat . '_actual'] = array_values($data_map[$cat]['actual']);
        }

        echo json_encode($output);
    }

    private function _mapping_chart_data($records, &$data_map, $type, $display) 
    {
        foreach ($records as $row) {
            $key = $this->_generate_date_key(new DateTime($row['date_ref']), $display);
            $f_name = strtoupper($row['family_name'] ?? '');
            
            // Get Rate and Amount
            $rate = $this->_find_rate_in_cache($row['date_ref'], $row['currency']);
            $amount = (float)$row['qty'] * (float)$row['price'] * (float)$rate;

            if (isset($data_map['qty'][$type][$key])) {
                $data_map['qty'][$type][$key] += $amount;

                // Grouping Item Family berdasarkan nama
                if (strpos($f_name, 'CHILD') !== false) $data_map['child_part'][$type][$key] += $amount;
                elseif (strpos($f_name, 'VIRGIN') !== false) $data_map['virgin'][$type][$key] += $amount;
                elseif (strpos($f_name, 'CONSUMABLE') !== false) $data_map['consumable'][$type][$key] += $amount;
                elseif (strpos($f_name, 'MASTER') !== false) $data_map['master_batch'][$type][$key] += $amount;
                elseif (strpos($f_name, 'STAMPING') !== false) $data_map['stamping'][$type][$key] += $amount;
                elseif (strpos($f_name, 'SUBCONT') !== false) $data_map['subcont'][$type][$key] += $amount;
            }
        }
    }

}
