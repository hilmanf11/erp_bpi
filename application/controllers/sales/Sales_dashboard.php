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

    public function get_dashboard_data() 
    {
        $filter_from        = $this->input->post('from');
        $filter_to          = $this->input->post('to');
        $filter_display     = $this->input->post('display');
        $filter_division    = $this->input->post('division');
        $filter_customer_id = $this->input->post('customer_id');
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
        $sales_data  = [];
        $sales_count = [];

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
            
            if (!isset($sales_data[$key])) {
                $sales_data[$key] = 0;
            }
        }

        // Main Query
        $query = "SELECT
            a.id,
            c.name AS customer_name,
            c.id AS customer_id,
            (CASE WHEN a.sales_order_no_rm IS NOT NULL THEN 'RM / SUBCONT' ELSE a.division END) AS division,
            a.delivery_note_no,
            a.delivery_note_date,
            a.item_fg_id,
            b.number AS item_fg_number,
            b.name AS item_fg_name,
            COALESCE(a.sales_order_no, a.sales_order_no_rm) AS sales_order_no,
            COALESCE(d.sales_order_date, e.sales_order_date) AS sales_order_date,
            a.customer_order_no,
            a.uom,
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
            LEFT JOIN item_fg b ON a.item_fg_id = b.id
            LEFT JOIN customers c ON a.customer_id = c.id
            LEFT JOIN sales_orders d ON a.sales_order_no = d.sales_order_no and a.item_fg_id = d.item_fg_id
            LEFT JOIN sales_order_rm e ON a.sales_order_no_rm = e.sales_order_no and a.item_fg_id = e.item_fg_id
            WHERE a.customer_id LIKE ? 
            AND a.division LIKE ? 
            AND DATE_FORMAT(a.delivery_note_date, '%Y-%m-%d') BETWEEN ? and ? 
            AND a.trans_type = 'SALES'
            GROUP BY a.id  
            ORDER BY c.name ASC, a.delivery_note_no ASC, b.number ASC";
        
        $params = [
            "%$filter_customer_id%", 
            "%$filter_division%", 
            $filter_from, 
            $filter_to
        ];
        
        $records = $this->db->query($query, $params)->result_array();

        
        // Mapping Data untuk Dashboard
        $total_amount     = 0;
        $unique_so        = [];
        $customer_summary = [];

        foreach ($records as $row) {
            $subtotal = (float)$row['qty'] * (float)$row['price'];
            $total_amount += $subtotal;
            $unique_so[$row['sales_order_no']] = true;

            // Grouping berdasarkan Filter Display
            $time = strtotime($row['delivery_note_date']);
            if ($filter_display == "DAILY") {
                $key = $row['delivery_note_date'];
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
                $key = $row['delivery_note_date'];
            }

            // Sisipkan data ke sales_data
            if (isset($sales_data[$key])) {
                $sales_data[$key] += $subtotal;
            }

            // Hitung jumlah transaksi per label
            $sales_count[$key] = ($sales_count[$key] ?? 0) + 1;

            $supp = $row['customer_name'] ?? 'Unknown';
            $customer_summary[$supp] = ($customer_summary[$supp] ?? 0) + $subtotal;
        }

        arsort($customer_summary); 
        
        $output = [
            'total_amount_formatted' => 'Rp ' . number_format($total_amount, 0, ',', '.'),
            'total_so'     => count($unique_so),
            'trend_labels' => array_keys($sales_data),
            'trend_values' => array_values($sales_data),
            'counts'       => array_values($sales_count),
            'cust_labels'  => array_keys($customer_summary),
            'cust_values'  => array_values($customer_summary),
            'subtitle'     => "Period: " . date('d M Y', strtotime($filter_from)) . " to " . date('d M Y', strtotime($filter_to))
        ];

        echo json_encode($output);
    }
}
