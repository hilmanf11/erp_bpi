<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Supplier_performance extends CI_Controller
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
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/supplier_performance');
        } else {
            redirect('error_access');
        }
    }

    public function readSupplier()
    {
        $send = $this->crud->query("SELECT b.id, b.number, b.name 
            FROM purchase_orders a 
            JOIN suppliers b ON a.supplier_id = b.id 
            WHERE a.deleted = 0
            GROUP BY a.supplier_id ORDER BY b.name ASC");
        echo json_encode($send);
    }

    public function get_supplier_info()
    {
        $supplier_id = $this->input->get('supplier_id');
        if (!empty($supplier_id)) {
            $supp = $this->db->get_where('suppliers', ['id' => $supplier_id])->row_array();
            echo json_encode($supp ? $supp : []);
        } else {
            echo json_encode([]);
        }
    }

    public function get_performance_data()
    {
        $filter_year = $this->input->get('year');
        if (empty($filter_year)) {
            $filter_year = date("Y");
        }
        $filter_supplier = $this->input->get('supplier_id');

        $months = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug',
            '09' => 'Sept', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'
        ];

        $resDelivery = [];
        $resQuality = [];
        $resSchedVsDel = [];
        $resTotal = [];

        $current_ym = date('Y-m');

        foreach ($months as $mNum => $mLabel) {
            $filter_from = $filter_year . '-' . $mNum . '-01';
            $filter_to = date('Y-m-t', strtotime($filter_from));
            $year_start_dt = $filter_year . '-01-01';

            // Jika bulan yang sedang di-loop melebihi bulan saat ini, anggap belum ada data (null)
            $loop_ym = $filter_year . '-' . $mNum;
            if ($loop_ym > $current_ym) {
                $resDelivery[$mLabel . ' ' . substr($filter_year, 2)] = null;
                $resQuality[$mLabel . ' ' . substr($filter_year, 2)] = null;
                $resSchedVsDel[$mLabel . ' ' . substr($filter_year, 2)] = null;
                $resTotal[$mLabel . ' ' . substr($filter_year, 2)] = null;
                continue;
            }

            // Query PO Prev (sebelum filter_from): hanya open PO (status = 0)
            $sqlPoPrev = "SELECT supplier_id, item_rm_id, SUM(qty) as qty FROM purchase_orders WHERE po_date >= '$year_start_dt' AND po_date < '$filter_from' AND status = 0 AND deleted = 0";
            if (!empty($filter_supplier)) $sqlPoPrev .= " AND supplier_id = " . $this->db->escape($filter_supplier);
            $sqlPoPrev .= " GROUP BY supplier_id, item_rm_id";
            $mapPoPrev = [];
            foreach ($this->db->query($sqlPoPrev)->result_array() as $r) {
                $mapPoPrev[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
            }

            // Query POR Prev — hanya receipt dari open PO (status = 0)
            $sqlPorPrev = "SELECT por.supplier_id, por.item_rm_id, SUM(por.qty_receipt) as qty
                FROM purchase_order_receipts por
                INNER JOIN purchase_orders po ON por.po_no = po.po_no AND por.item_rm_id = po.item_rm_id AND po.status = 0
                WHERE por.receipt_date >= '$year_start_dt' AND por.receipt_date < '$filter_from' AND por.deleted = 0";
            if (!empty($filter_supplier)) $sqlPorPrev .= " AND por.supplier_id = " . $this->db->escape($filter_supplier);
            $sqlPorPrev .= " GROUP BY por.supplier_id, por.item_rm_id";
            $mapPorPrev = [];
            foreach ($this->db->query($sqlPorPrev)->result_array() as $r) {
                $mapPorPrev[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
            }

            // Query Plan Period (dari request_materials)
            $sqlPoPeriod = "SELECT supplier_id, item_rm_id, SUM(qty) as qty FROM request_materials WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND deleted = 0";
            if (!empty($filter_supplier)) $sqlPoPeriod .= " AND supplier_id = " . $this->db->escape($filter_supplier);
            $sqlPoPeriod .= " GROUP BY supplier_id, item_rm_id";
            $mapPoPeriod = [];
            foreach ($this->db->query($sqlPoPeriod)->result_array() as $r) {
                $mapPoPeriod[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
            }

            // Query POR Period
            $sqlPorPeriod = "SELECT supplier_id, item_rm_id, SUM(qty_receipt) as qty FROM purchase_order_receipts WHERE receipt_date BETWEEN '$filter_from' AND '$filter_to' AND deleted = 0";
            if (!empty($filter_supplier)) $sqlPorPeriod .= " AND supplier_id = " . $this->db->escape($filter_supplier);
            $sqlPorPeriod .= " GROUP BY supplier_id, item_rm_id";
            $mapPorPeriod = [];
            foreach ($this->db->query($sqlPorPeriod)->result_array() as $r) {
                $mapPorPeriod[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
            }

            // Query NG (transaction_rm: RETURN NG TO SUPPLIER)
            $sqlNgPeriod = "SELECT supplier_id, item_rm_id, SUM(qty) as qty FROM transaction_rm WHERE transaction_type = 'RETURN NG TO SUPPLIER' AND request_date BETWEEN '$filter_from' AND '$filter_to' AND deleted = 0";
            if (!empty($filter_supplier)) $sqlNgPeriod .= " AND supplier_id = " . $this->db->escape($filter_supplier);
            $sqlNgPeriod .= " GROUP BY supplier_id, item_rm_id";
            $mapNgPeriod = [];
            foreach ($this->db->query($sqlNgPeriod)->result_array() as $r) {
                $mapNgPeriod[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
            }

            // Ambil semua kombinasi supplier + item_rm yang terlibat di periode berjalan saja
            $allKeys = array_unique(array_merge(
                array_keys($mapPoPeriod), array_keys($mapPorPeriod)
            ));

            $total_qty_memo = 0;
            $total_qty_por = 0;
            $total_qty_ng = 0;

            foreach ($allKeys as $key) {
                $poPeriodQty = isset($mapPoPeriod[$key]) ? $mapPoPeriod[$key] : 0;
                $porPeriodQty = isset($mapPorPeriod[$key]) ? $mapPorPeriod[$key] : 0;
                $ngPeriodQty = isset($mapNgPeriod[$key]) ? $mapNgPeriod[$key] : 0;

                // Qty Memo (Plan) murni dari PO Periode berjalan tanpa OS PO sebelumnya
                $qty_memo = $poPeriodQty;
                $qty_por = $porPeriodQty;

                $total_qty_memo += $qty_memo;
                $total_qty_por += $qty_por;
                $total_qty_ng += $ngPeriodQty;
            }

            if ($total_qty_memo == 0 && $total_qty_por == 0) {
                $valDelivery = null;
                $valQuality = null;
                $valSchedVsDel = null;
                $valTotal = null;
            } else {
                // Nilai Delivery % = (Total Qty POR / Total Qty Memo (PO + OS Prev)) * 100%, max 100%
                $valDelivery = $total_qty_memo > 0 ? min(100, round(($total_qty_por / $total_qty_memo) * 100, 2)) : 100;
                
                // Nilai Quality % = 100 - ((Total NG / Total Qty POR) * 100)%
                $valQuality = $total_qty_por > 0 ? max(0, round(100 - (($total_qty_ng / $total_qty_por) * 100), 2)) : 100;

                // Nilai Sched VS Del
                $valSchedVsDel = $valDelivery;

                // Total Nilai = Rata-rata ketiga indikator
                $valTotal = round(($valDelivery + $valQuality + $valSchedVsDel) / 3, 2);
            }

            $resDelivery[$mLabel . ' ' . substr($filter_year, 2)] = $valDelivery;
            $resQuality[$mLabel . ' ' . substr($filter_year, 2)] = $valQuality;
            $resSchedVsDel[$mLabel . ' ' . substr($filter_year, 2)] = $valSchedVsDel;
            $resTotal[$mLabel . ' ' . substr($filter_year, 2)] = $valTotal;
        }

        $result = [
            'categories' => array_keys($resDelivery),
            'delivery' => array_values($resDelivery),
            'quality' => array_values($resQuality),
            'sched_vs_del' => array_values($resSchedVsDel),
            'total' => array_values($resTotal),
        ];

        echo json_encode($result);
    }
}
