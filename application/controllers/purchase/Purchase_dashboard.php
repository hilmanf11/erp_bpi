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

    public function get_dashboard_data() 
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

            $supp = $row['supplier_name'] ?? 'Unknown';
            $supplier_summary[$supp] = ($supplier_summary[$supp] ?? 0) + $subtotal;
        }

        arsort($supplier_summary); 
        
        $output = [
            'total_amount_formatted' => 'Rp ' . number_format($total_amount, 0, ',', '.'),
            'total_po'     => count($unique_pos),
            'trend_labels' => array_keys($purchase_data),
            'trend_values' => array_values($purchase_data),
            'supp_labels'  => array_keys($supplier_summary),
            'supp_values'  => array_values($supplier_summary),
            'subtitle'     => "Period: " . date('d M Y', strtotime($filter_from)) . " to " . date('d M Y', strtotime($filter_to))
        ];

        echo json_encode($output);
    }

    public function get_plan_actual_data() 
    {
        $filter_from        = $this->input->post('from');
        $filter_to          = $this->input->post('to');
        $filter_display     = $this->input->post('display');
        $filter_division    = $this->input->post('division');
        $filter_supplier_id = $this->input->post('supplier_id');
        $filter_category_id = $this->input->post('category_id');


        // Prepare Data Variables
        $week_labels          = ['Date 01-07', 'Date 08-14', 'Date 15-21', 'Date 22-31'];
        $qty_plan            = [];
        $qty_actual          = [];
        $child_part_plan     = [];
        $child_part_actual   = [];
        $virgin_plan         = [];
        $virgin_actual       = [];
        $consumable_plan     = [];
        $consumable_actual   = [];
        $master_batch_plan   = [];
        $master_batch_actual = [];
        $stamping_plan       = [];
        $stamping_actual     = [];
        $subcont_plan        = [];
        $subcont_actual      = [];


        // GET QTY PLAN FROM PO

        
        // GET QTY ACTUAL FROM POR


        $output = [
            'period'               => "Period: " . date('d M Y', strtotime($filter_from)) . " to " . date('d M Y', strtotime($filter_to)),
            'week_labels'          => array_keys($week_labels),
            'qty_plan'             => array_values($qty_plan),
            'qty_actual'           => array_values($qty_actual),
            'child_part_plan'      => array_values($child_part_plan),
            'child_part_actual'    => array_values($child_part_actual),
            'virgin_plan'          => array_values($virgin_plan),
            'virgin_actual'        => array_values($virgin_actual),
            'consumable_plan'      => array_values($consumable_plan),
            'consumable_actual'    => array_values($consumable_actual),
            'master_batch_plan'    => array_values($master_batch_plan),
            'master_batch_actual'  => array_values($master_batch_actual),
            'stamping_plan'        => array_values($stamping_plan),
            'stamping_actual'      => array_values($stamping_actual),
            'subcont_plan'         => array_values($subcont_plan),
            'subcont_actual'       => array_values($subcont_actual),
        ];

        echo json_encode($output);
    }

}
