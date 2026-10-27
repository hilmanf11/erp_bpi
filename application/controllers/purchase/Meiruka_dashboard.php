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
class Meiruka_dashboard extends CI_Controller
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
            $this->load->view('template/header', $data);
            $this->load->view('purchase/meiruka_dashboard');
        } else {
            redirect('error_access');
        }
    }

    public function get_dashboard_data()
    {
        $filter_date    = $this->input->get('filter_date');
        $filter_display = $this->input->get('filter_display');

        if (empty($filter_date)) {
            $filter_date = date('Y-m-d');
        }

        if (empty($filter_display)) {
            $filter_display = 'MONTHLY';
        }

        if ($filter_display == 'WEEKLY') {
            $filter_from = date('Y-m-d', strtotime('monday this week', strtotime((string) $filter_date)));
            $filter_to   = date('Y-m-d', strtotime('sunday this week', strtotime((string) $filter_date)));

            $filter_from_minus1 = date('Y-m-d', strtotime('-1 week', strtotime((string) $filter_from)));
            $filter_to_minus1   = date('Y-m-d', strtotime('-1 day', strtotime((string) $filter_from)));
        } elseif ($filter_display == 'YEARLY') {
            $filter_from = date('Y-01-01', strtotime((string) $filter_date));
            $filter_to   = date('Y-12-31', strtotime((string) $filter_date));

            $filter_from_minus1 = date('Y-01-01', strtotime('-1 year', strtotime((string) $filter_from)));
            $filter_to_minus1   = date('Y-12-31', strtotime('-1 year', strtotime((string) $filter_from)));
        } else {
            // MONTHLY default
            $filter_from = date('Y-m-01', strtotime((string) $filter_date));
            $filter_to   = date('Y-m-t', strtotime((string) $filter_date));

            $filter_from_minus1 = date('Y-m-01', strtotime('-1 month', strtotime((string) $filter_from)));
            $filter_to_minus1   = date('Y-m-t',  strtotime('-1 month', strtotime((string) $filter_from)));
        }

        $month = date('m', strtotime((string) $filter_to));
        $year  = date('Y', strtotime((string) $filter_to));

        $query_main = "SELECT 
            a.id,
            a.number, 
            a.name, 
            a.division, 
            b.name as prodfam, 
            a.uom,
            c.name as category_name,
            COALESCE(m.supplier_name, '-') as supplier_name,
            COALESCE(l.need_1, 0) as plan_supply,
            COALESCE(n.min, 0) as min,
            COALESCE(n.max, 0) as max,
            COALESCE(n.leadtime, 0) as leadtime,
            COALESCE(o.rack, '-') as rack,
            COALESCE(h.qty_issued, 0) as qty_issued,
            COALESCE(j.begin_stock, 0) AS begin_stock,
            (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) AS qty_in,
            (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) AS qty_out
        FROM item_rm a
        JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
        JOIN item_categories c ON a.item_category_id = c.id
        LEFT JOIN (
            SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in
            FROM scan_item_receipts a 
            JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id 
            WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' 
            GROUP BY b.item_rm_id
        ) d ON a.id = d.item_rm_id
        LEFT JOIN (
            SELECT item_rm_id, SUM(qty) AS qty_os_rm
            FROM os_rm 
            WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' 
            GROUP BY item_rm_id
        ) e ON a.id = e.item_rm_id
        LEFT JOIN (
            SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in
            FROM transaction_rm 
            WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'IN'
            GROUP BY item_rm_id
        ) f ON a.id = f.item_rm_id
        LEFT JOIN (
            SELECT a.item_rm_id, SUM(c.qty) AS return_qty
            FROM return_materials a 
            JOIN return_material_labels b ON a.return_id = b.return_id 
            JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no 
            WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' 
            GROUP BY a.item_rm_id
        ) g ON a.id = g.item_rm_id
        LEFT JOIN (
            SELECT item_rm_id, SUM(qty) AS qty_issued
            FROM issued_material_details 
            WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' 
            GROUP BY item_rm_id
        ) h ON a.id = h.item_rm_id
        LEFT JOIN (
            SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out
            FROM transaction_rm 
            WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'OUT' 
            GROUP BY item_rm_id
        ) i ON a.id = i.item_rm_id
        LEFT JOIN (
            SELECT item_rm_id, SUM(qty) AS qty_scan_bpm
            FROM scan_item_bpm 
            WHERE DATE_FORMAT(request_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' 
            GROUP BY item_rm_id
        ) k ON a.id = k.item_rm_id
        LEFT JOIN (
            SELECT a.id, ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
            FROM item_rm a
            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_from' GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$filter_from' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
        ) j ON a.id = j.id
        LEFT JOIN (
            SELECT t.item_rm_id, t.need_1
            FROM generate_mrp_finals t
            JOIN (
                SELECT item_rm_id, MAX(revision) AS max_revisi
                FROM generate_mrp_finals
                WHERE p_month = '$month' AND p_year = '$year'
                GROUP BY item_rm_id
            ) x ON t.item_rm_id = x.item_rm_id AND t.revision = x.max_revisi
            WHERE t.p_month = '$month' AND t.p_year = '$year'
        ) l ON a.id = l.item_rm_id
        LEFT JOIN (
            SELECT DISTINCT c.name as supplier_name, a.item_rm_id, a.share_order 
            FROM supplier_items a
            LEFT JOIN item_rm b ON a.item_rm_id = b.id
            LEFT JOIN suppliers c ON a.supplier_id = c.id
            WHERE a.share_order = 100 AND b.item_family_id IN('P01','P02','P06')
        ) m ON a.id = m.item_rm_id
        LEFT JOIN (SELECT item_rm_id, min, max, leadtime FROM master_minmax WHERE status = 0 GROUP BY item_rm_id) n ON a.id = n.item_rm_id
        LEFT JOIN warehouse_location_items o ON a.id = o.item_rm_id
        WHERE a.item_family_id IN('P01','P02','P06')
        GROUP BY a.id";

        $records = $this->crud->query($query_main);

        $ok_items = 0;
        $under_items = 0;
        $over_items = 0;

        $supplier_stats = []; // [supplier => ['ok' => x, 'over' => y, 'under' => z]]
        $top_under_supplier = []; // [supplier => under_count]
        $top_over_supplier = [];  // [supplier => over_count]

        foreach ($records as $record) {
            $item_rm_id = $record->id;
            $supplier_name = trim((string) $record->supplier_name);
            if (empty($supplier_name)) {
                $supplier_name = '-';
            }

            $supplyperday_query = "SELECT sub.total_qty
                FROM (
                    SELECT SUM(b.qty * a.composition) AS total_qty
                    FROM bom a 
                    JOIN production_schedules b ON a.item_fg_id = b.item_fg_id 
                    WHERE DATE_FORMAT(b.trans_date, '%Y-%m-%d') BETWEEN '$filter_from_minus1' AND '$filter_to_minus1'
                    AND a.item_rm_id = '$item_rm_id'
                    GROUP BY b.trans_date
                ) AS sub
                ORDER BY sub.total_qty DESC
                LIMIT 1";

            $supplyperday = $this->crud->query($supplyperday_query);
            $daily_qty = $supplyperday[0]->total_qty ?? 0;

            $stock_current = ($record->begin_stock + $record->qty_in) - $record->qty_out;
            $min_stock = $daily_qty * $record->min;
            $max_stock = $daily_qty * $record->max;

            if ($stock_current > $max_stock) {
                $status = 'OVER';
                $over_items++;
            } elseif ($min_stock <= $stock_current) {
                $status = 'OK';
                $ok_items++;
            } else {
                $status = 'UNDER';
                $under_items++;
            }

            if (!isset($supplier_stats[$supplier_name])) {
                $supplier_stats[$supplier_name] = ['ok' => 0, 'over' => 0, 'under' => 0];
            }
            if (!isset($top_under_supplier[$supplier_name])) $top_under_supplier[$supplier_name] = 0;
            if (!isset($top_over_supplier[$supplier_name])) $top_over_supplier[$supplier_name] = 0;

            if ($status === 'OK') {
                $supplier_stats[$supplier_name]['ok']++;
            } elseif ($status === 'OVER') {
                $supplier_stats[$supplier_name]['over']++;
                $top_over_supplier[$supplier_name]++;
            } else {
                $supplier_stats[$supplier_name]['under']++;
                $top_under_supplier[$supplier_name]++;
            }
        }

        $total_items = $ok_items + $under_items + $over_items;

        // Sort Top Under and Top Over suppliers DESC
        arsort($top_under_supplier);
        arsort($top_over_supplier);

        // Format chart data for Status Supplier (Stack bar chart per supplier)
        $suppliers_chart = [];
        foreach ($supplier_stats as $sup => $counts) {
            $suppliers_chart[] = [
                'supplier' => $sup,
                'ok' => $counts['ok'],
                'over' => $counts['over'],
                'under' => $counts['under'],
                'total' => $counts['ok'] + $counts['over'] + $counts['under']
            ];
        }

        // Calculate Supplier level status counts
        $supplier_ok_count = 0;
        $supplier_over_count = 0;
        $supplier_under_count = 0;

        foreach ($supplier_stats as $sup => $counts) {
            if ($counts['under'] > 0) {
                $supplier_under_count++;
            } elseif ($counts['over'] > 0) {
                $supplier_over_count++;
            } else {
                $supplier_ok_count++;
            }
        }
        $total_suppliers = count($supplier_stats);

        echo json_encode([
            'summary' => [
                'total_items' => $total_items,
                'ok_items' => $ok_items,
                'ok_pct' => $total_items > 0 ? round(($ok_items / $total_items) * 100) : 0,
                'under_items' => $under_items,
                'under_pct' => $total_items > 0 ? round(($under_items / $total_items) * 100) : 0,
                'over_items' => $over_items,
                'over_pct' => $total_items > 0 ? round(($over_items / $total_items) * 100) : 0,
                'total_suppliers' => $total_suppliers,
                'supplier_ok_count' => $supplier_ok_count,
                'supplier_over_count' => $supplier_over_count,
                'supplier_under_count' => $supplier_under_count,
            ],
            'supplier_chart' => array_values($suppliers_chart),
            'top_under' => $top_under_supplier,
            'top_over' => $top_over_supplier
        ]);
    }
}



