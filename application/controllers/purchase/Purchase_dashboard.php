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
            elseif ($filter_display == "WEEKLY_ISO8601") {
                $monday = clone $dt;
                if ($monday->format('N') != 1) {
                    $monday->modify('last monday');
                }
                
                $sunday = clone $monday;
                $sunday->modify('+6 days');

                // 'W' format ISO-8601 menghasilkan nomor minggu (01-53)
                // 'o' menghasilkan tahun ISO-8601 yang sesuai dengan nomor minggu tersebut
                $weekNumber = $monday->format('W');
                $year = $monday->format('o'); 
                
                $key = "W" . $weekNumber . " " . $year . " (" . $monday->format('j M') . " - " . $sunday->format('j M') . ")";
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
            elseif ($filter_display == "WEEKLY_ISO8601") {
                $mondayTime = (date('N', $time) == 1) ? $time : strtotime('last monday', $time);
                $sundayTime = strtotime('+6 days', $mondayTime);
                
                $weekNumber = date('W', $mondayTime);
                $year = date('o', $mondayTime);
                
                $key = "W" . $weekNumber . " " . $year . " (" . date('j M', $mondayTime) . " - " . date('j M', $sundayTime) . ")";
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
        ];

        echo json_encode($output);
    }

}
