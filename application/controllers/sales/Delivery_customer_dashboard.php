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
class Delivery_customer_dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
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
            $this->load->view('sales/delivery_customer_dashboard');
        } else {
            redirect('error_access');
        }
    }

    public function get_dashboard_data()
    {
        $month          = @base64_decode((string) $this->input->get('month'));
        $year           = @base64_decode((string) $this->input->get('year'));
        $filter_display = @base64_decode((string) $this->input->get('display'));

        if (empty($month) || empty($year)) {
            $month = date('m');
            $year  = date('Y');
        }

        if (empty($filter_display)) {
            $filter_display = 'MONTHLY';
        }

        if ($filter_display == 'WEEKLY') {
            $current_start = date('Y-m-d', strtotime('monday this week'));
            $current_end   = date('Y-m-d', strtotime('sunday this week'));
        } elseif ($filter_display == 'YEARLY') {
            $current_start = "$year-01-01";
            $current_end   = "$year-12-31";
        } else {
            // MONTHLY default
            $current_start = date('Y-m-01', strtotime("$year-$month-01"));
            $current_end   = date('Y-m-t', strtotime("$year-$month-01"));
        }

        // Tetap 3 bulan kebelakang untuk m3_pct
        $m3_start = date('Y-m-01', strtotime("$current_start -3 months"));
        $m3_end   = date('Y-m-t', strtotime("$current_start -1 month"));

        // Selected month data per customer
        $sql_current = "SELECT 
                            c.id AS customer_id, 
                            c.name AS customer_name,
                            COALESCE(s.plan_qty, 0) AS plan_qty,
                            COALESCE(d.actual_qty, 0) AS actual_qty
                        FROM customers c
                        INNER JOIN (
                            SELECT customer_id, SUM(qty) AS plan_qty
                            FROM sales_order_deliveries
                            WHERE trans_date BETWEEN '$current_start' AND '$current_end' AND status = 0
                            GROUP BY customer_id
                        ) s ON c.id = s.customer_id
                        LEFT JOIN (
                            SELECT customer_id, SUM(qty) AS actual_qty
                            FROM delivery_notes
                            WHERE delivery_note_date BETWEEN '$current_start' AND '$current_end'
                            GROUP BY customer_id
                        ) d ON c.id = d.customer_id
                        ORDER BY c.name ASC";

        $records = $this->db->query($sql_current)->result_array();

        // 3 month lookback data per customer
        $sql_m3 = "SELECT 
                        c.id AS customer_id,
                        COALESCE(s.plan_qty, 0) AS plan_qty,
                        COALESCE(d.actual_qty, 0) AS actual_qty
                    FROM customers c
                    INNER JOIN (
                        SELECT customer_id, SUM(qty) AS plan_qty
                        FROM sales_order_deliveries
                        WHERE trans_date BETWEEN '$m3_start' AND '$m3_end' AND status = 0
                        GROUP BY customer_id
                    ) s ON c.id = s.customer_id
                    LEFT JOIN (
                        SELECT customer_id, SUM(qty) AS actual_qty
                        FROM delivery_notes
                        WHERE delivery_note_date BETWEEN '$m3_start' AND '$m3_end'
                        GROUP BY customer_id
                    ) d ON c.id = d.customer_id";

        $records_m3 = $this->db->query($sql_m3)->result_array();
        $map_m3 = [];
        foreach ($records_m3 as $row) {
            $pct = $row['plan_qty'] > 0 ? min(100, round(($row['actual_qty'] / $row['plan_qty']) * 100, 2)) : 0;
            $map_m3[$row['customer_id']] = $pct;
        }

        $rows = [];
        $total_plan = 0;
        $total_actual = 0;

        foreach ($records as $row) {
            $c_id = $row['customer_id'];
            $plan = (float)$row['plan_qty'];
            $actual = (float)$row['actual_qty'];

            $total_plan += $plan;
            $total_actual += $actual;

            $pct_delivery = $plan > 0 ? min(100, round(($actual / $plan) * 100, 2)) : 0;
            $m3_pct = isset($map_m3[$c_id]) ? $map_m3[$c_id] : $pct_delivery;

            // Rating logic
            $perf = 'EXCELLENT';
            if ($pct_delivery < 60) {
                $perf = 'POOR';
            } elseif ($pct_delivery < 75) {
                $perf = 'AVERAGE';
            } elseif ($pct_delivery < 90) {
                $perf = 'GOOD';
            }

            $rows[] = [
                'customer_id'   => $c_id,
                'customer_name' => $row['customer_name'],
                'target'        => 100,
                'm3_pct'        => $m3_pct,
                'pct_delivery'  => $pct_delivery,
                'pct_quality'   => $pct_delivery,
                'performance'   => $perf
            ];
        }

        $overall_ontime = $total_plan > 0 ? min(100, round(($total_actual / $total_plan) * 100, 1)) : 100;

        echo json_encode([
            'total'          => count($rows),
            'rows'           => $rows,
            'overall_ontime' => $overall_ontime
        ]);
    }

    public function export_excel()
    {
        $month          = @base64_decode((string) $this->input->get('month'));
        $year           = @base64_decode((string) $this->input->get('year'));
        $filter_display = @base64_decode((string) $this->input->get('display'));

        if (empty($month) || empty($year)) {
            $month = date('m');
            $year  = date('Y');
        }

        if (empty($filter_display)) {
            $filter_display = 'MONTHLY';
        }

        if ($filter_display == 'WEEKLY') {
            $current_start = date('Y-m-d', strtotime('monday this week'));
            $current_end   = date('Y-m-d', strtotime('sunday this week'));
        } elseif ($filter_display == 'YEARLY') {
            $current_start = "$year-01-01";
            $current_end   = "$year-12-31";
        } else {
            // MONTHLY default
            $current_start = date('Y-m-01', strtotime("$year-$month-01"));
            $current_end   = date('Y-m-t', strtotime("$year-$month-01"));
        }

        // Tetap 3 bulan kebelakang untuk m3_pct
        $m3_start = date('Y-m-01', strtotime("$current_start -3 months"));
        $m3_end   = date('Y-m-t', strtotime("$current_start -1 month"));

        $sql_current = "SELECT 
                            c.id AS customer_id, 
                            c.name AS customer_name,
                            COALESCE(s.plan_qty, 0) AS plan_qty,
                            COALESCE(d.actual_qty, 0) AS actual_qty
                        FROM customers c
                        INNER JOIN (
                            SELECT customer_id, SUM(qty) AS plan_qty
                            FROM sales_order_deliveries
                            WHERE trans_date BETWEEN '$current_start' AND '$current_end' AND status = 0
                            GROUP BY customer_id
                        ) s ON c.id = s.customer_id
                        LEFT JOIN (
                            SELECT customer_id, SUM(qty) AS actual_qty
                            FROM delivery_notes
                            WHERE delivery_note_date BETWEEN '$current_start' AND '$current_end'
                            GROUP BY customer_id
                        ) d ON c.id = d.customer_id
                        ORDER BY c.name ASC";

        $records = $this->db->query($sql_current)->result_array();

        $sql_m3 = "SELECT 
                        c.id AS customer_id,
                        COALESCE(s.plan_qty, 0) AS plan_qty,
                        COALESCE(d.actual_qty, 0) AS actual_qty
                    FROM customers c
                    INNER JOIN (
                        SELECT customer_id, SUM(qty) AS plan_qty
                        FROM sales_order_deliveries
                        WHERE trans_date BETWEEN '$m3_start' AND '$m3_end' AND status = 0
                        GROUP BY customer_id
                    ) s ON c.id = s.customer_id
                    LEFT JOIN (
                        SELECT customer_id, SUM(qty) AS actual_qty
                        FROM delivery_notes
                        WHERE delivery_note_date BETWEEN '$m3_start' AND '$m3_end'
                        GROUP BY customer_id
                    ) d ON c.id = d.customer_id";

        $records_m3 = $this->db->query($sql_m3)->result_array();
        $map_m3 = [];
        foreach ($records_m3 as $row) {
            $pct = $row['plan_qty'] > 0 ? min(100, round(($row['actual_qty'] / $row['plan_qty']) * 100, 2)) : 0;
            $map_m3[$row['customer_id']] = $pct;
        }

        $format = date("Ymd_His");
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Delivery_Customer_Dashboard_$format.xls");

        $html = '<table border="1" style="width:100%; text-align:center;">
                    <thead>
                        <tr style="background:#244b82; color:#ffffff;">
                            <th>No</th>
                            <th>Customer</th>
                            <th>TARGET (%)</th>
                            <th>3 Month (%)</th>
                            <th>% DELIVERY Qty</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>';

        $no = 1;
        foreach ($records as $row) {
            $c_id = $row['customer_id'];
            $plan = (float)$row['plan_qty'];
            $actual = (float)$row['actual_qty'];

            $pct_delivery = $plan > 0 ? min(100, round(($actual / $plan) * 100, 2)) : 0;
            $m3_pct = isset($map_m3[$c_id]) ? $map_m3[$c_id] : $pct_delivery;

            $perf = 'EXCELLENT';
            if ($pct_delivery < 60) {
                $perf = 'POOR';
            } elseif ($pct_delivery < 75) {
                $perf = 'AVERAGE';
            } elseif ($pct_delivery < 90) {
                $perf = 'GOOD';
            }

            $html .= '<tr>
                        <td>' . $no++ . '</td>
                        <td style="text-align:left;">' . $row['customer_name'] . '</td>
                        <td style="mso-number-format:\'\@\';">100%</td>
                        <td style="mso-number-format:\'\@\';">' . $m3_pct . '</td>
                        <td style="mso-number-format:\'\@\';">' . $pct_delivery . '</td>
                        <td>' . $perf . '</td>
                    </tr>';
        }

        $html .= '</tbody></table>';
        echo $html;
    }
}



