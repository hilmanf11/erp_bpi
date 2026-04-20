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
        $filter_from = $this->input->post('from');
        $filter_to = $this->input->post('to');
        $filter_division = $this->input->post('division');
        $filter_supplier_id = $this->input->post('supplier_id');
        $filter_category_id = $this->input->post('category_id');

        // Main Query
        $query = "SELECT 
                a.receipt_date,
                f.name as supplier_name,
                a.po_no,
                a.qty_receipt2 as qty,
                (CASE 
                    WHEN COALESCE(d.discount_nominal,0) > 0 
                        THEN COALESCE(d.total,0) / COALESCE(d.qty,0) 
                    ELSE 
                        (COALESCE(d.total,0) - ((COALESCE(d.total,0) / NULLIF(COALESCE(d.total_sub,0),0)) * COALESCE(d.discount_total,0))) / NULLIF(COALESCE(d.qty,0),0)
                END) AS price 
            FROM purchase_order_receipts a
            LEFT JOIN item_rm b ON a.item_rm_id = b.id
            LEFT JOIN purchase_orders d ON a.po_no = d.po_no and a.item_rm_id = d.item_rm_id
            LEFT JOIN item_categories e ON b.item_category_id = e.id
            LEFT JOIN suppliers f ON d.supplier_id = f.id
            WHERE (a.supplier_id LIKE '%$filter_supplier_id%') 
            AND (b.division LIKE '%$filter_division%') 
            AND (b.item_category_id LIKE '%$filter_category_id%')
            AND (DATE_FORMAT(a.receipt_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to')
            ORDER BY a.receipt_date ASC";
        $records = $this->db->query($query)->result_array();

        // Mapping Data untuk Dashboard
        $total_amount     = 0;
        $unique_pos       = [];
        $purchase_data    = []; // Untuk Chart Line
        $supplier_summary = []; // Untuk Chart Pie/Doughnut

        foreach ($records as $row) {
            $subtotal = $row['qty'] * $row['price'];
            $total_amount += $subtotal;
            $unique_pos[$row['po_no']] = true;

            // Grouping Trend per Hari (atau per bulan jika range luas)
            $tgl = $row['receipt_date'];
            $purchase_data[$tgl] = ($purchase_data[$tgl] ?? 0) + $subtotal;

            // Grouping per Supplier
            $supp = $row['supplier_name'];
            $supplier_summary[$supp] = ($supplier_summary[$supp] ?? 0) + $subtotal;
        }

        // Sort descending berdasarkan VALUE (Amount)
        arsort($supplier_summary); 
        
        // Format Output JSON
        $output = [
            'total_amount_formatted' => 'Rp ' . number_format($total_amount, 0, ',', '.'),
            'total_po'      => count($unique_pos),
            'recent_pos'    => array_slice($records, -10), // Ambil 10 transaksi terakhir
            'trend_labels'  => array_keys($purchase_data),
            'trend_values'  => array_values($purchase_data),
            'supp_labels'   => array_keys($supplier_summary),
            'supp_values'   => array_values($supplier_summary),
        ];

        echo json_encode($output);
    }
}
