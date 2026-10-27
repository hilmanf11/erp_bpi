<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Report_achievment_daily_supp extends CI_Controller
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
            $this->load->view('warehouse/report_achievment_daily_supp');
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

    public function datatables()
    {
        if ($this->input->get()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            $page = isset($page) ? intval($page) : 1;
            $rows = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $filter_from = base64_decode((string) $this->input->get('filter_from'));
            $filter_to = base64_decode((string) $this->input->get('filter_to'));
            $filter_supplier = base64_decode((string) $this->input->get('filter_supplier'));
            $filter_item_rm = base64_decode((string) $this->input->get('filter_item_rm'));

            // Ambil kombinasi supplier+item dari purchase_orders yang masih OPEN (status=0), akumulasi dari awal tahun
            $year_start_dt = date('Y', strtotime((string) $filter_from)) . '-01-01';
            $sqlMain = "SELECT t.supplier_id, t.item_rm_id, b.number as item_number, b.name as item_name, b.uom,
                        x.name as division, c.name as supplier_name
                        FROM (
                            SELECT supplier_id, item_rm_id FROM request_materials WHERE request_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0
                            UNION
                            SELECT supplier_id, item_rm_id FROM purchase_order_receipts WHERE receipt_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0
                            UNION
                            SELECT supplier_id, item_rm_id FROM transaction_rm WHERE transaction_type = 'RETURN NG TO SUPPLIER' AND request_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0
                        ) t
                        JOIN item_rm b ON t.item_rm_id = b.id
                        JOIN suppliers c ON t.supplier_id = c.id
                        LEFT JOIN divisions x ON b.division = x.number
                        WHERE 1=1";

            if ($filter_supplier != "") {
                $sqlMain .= " AND t.supplier_id = " . $this->db->escape($filter_supplier);
            }
            if ($filter_item_rm != "") {
                $sqlMain .= " AND t.item_rm_id = " . $this->db->escape($filter_item_rm);
            }

            $sqlMain .= " ORDER BY c.name ASC, b.number ASC";

            $queryRes = $this->db->query($sqlMain);
            $recordsAll = $queryRes ? $queryRes->result_array() : array();

            // Bulk fetch PO Prev (sebelum filter_from): hanya open PO (status = 0)
            // Bulk fetch PO Prev (sebelum filter_from): hanya PO OPEN (status = 0)
            $sqlPoPrev = "SELECT supplier_id, item_rm_id, SUM(qty) as qty FROM purchase_orders WHERE po_date >= " . $this->db->escape($year_start_dt) . " AND po_date < " . $this->db->escape($filter_from) . " AND status = 0 AND deleted = 0";
            if ($filter_supplier != "") $sqlPoPrev .= " AND supplier_id = " . $this->db->escape($filter_supplier);
            if ($filter_item_rm != "") $sqlPoPrev .= " AND item_rm_id = " . $this->db->escape($filter_item_rm);
            $sqlPoPrev .= " GROUP BY supplier_id, item_rm_id";
            $mapPoPrev = [];
            foreach ($this->db->query($sqlPoPrev)->result_array() as $r) {
                $mapPoPrev[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
            }

            // Bulk fetch POR Prev — hanya receipt dari OPEN PO (status = 0)
            $sqlPorPrev = "SELECT por.supplier_id, por.item_rm_id, SUM(por.qty_receipt) as qty
                FROM purchase_order_receipts por
                INNER JOIN purchase_orders po ON por.po_no = po.po_no AND por.item_rm_id = po.item_rm_id AND po.status = 0
                WHERE por.receipt_date >= " . $this->db->escape($year_start_dt) . " AND por.receipt_date < " . $this->db->escape($filter_from) . " AND por.deleted = 0";
            if ($filter_supplier != "") $sqlPorPrev .= " AND por.supplier_id = " . $this->db->escape($filter_supplier);
            if ($filter_item_rm != "") $sqlPorPrev .= " AND por.item_rm_id = " . $this->db->escape($filter_item_rm);
            $sqlPorPrev .= " GROUP BY por.supplier_id, por.item_rm_id";
            $mapPorPrev = [];
            foreach ($this->db->query($sqlPorPrev)->result_array() as $r) {
                $mapPorPrev[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
            }

            // Bulk fetch PO Period (semua status untuk tampilan QTY PO)
            $sqlPoPeriod = "SELECT supplier_id, item_rm_id, SUM(qty) as qty FROM purchase_orders WHERE po_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0";
            if ($filter_supplier != "") $sqlPoPeriod .= " AND supplier_id = " . $this->db->escape($filter_supplier);
            if ($filter_item_rm != "") $sqlPoPeriod .= " AND item_rm_id = " . $this->db->escape($filter_item_rm);
            $sqlPoPeriod .= " GROUP BY supplier_id, item_rm_id";
            $mapPoPeriod = [];
            foreach ($this->db->query($sqlPoPeriod)->result_array() as $r) {
                $mapPoPeriod[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
            }

            // Bulk fetch PO Period Open Only (khusus hitung OS)
            $sqlPoPeriodOpen = "SELECT supplier_id, item_rm_id, SUM(qty) as qty FROM purchase_orders WHERE po_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND status = 0 AND deleted = 0";
            if ($filter_supplier != "") $sqlPoPeriodOpen .= " AND supplier_id = " . $this->db->escape($filter_supplier);
            if ($filter_item_rm != "") $sqlPoPeriodOpen .= " AND item_rm_id = " . $this->db->escape($filter_item_rm);
            $sqlPoPeriodOpen .= " GROUP BY supplier_id, item_rm_id";
            $mapPoPeriodOpen = [];
            foreach ($this->db->query($sqlPoPeriodOpen)->result_array() as $r) {
                $mapPoPeriodOpen[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
            }

            // Bulk fetch POR Period (semua status untuk tampilan QTY POR)
            $sqlPorPeriod = "SELECT supplier_id, item_rm_id, SUM(qty_receipt) as qty FROM purchase_order_receipts WHERE receipt_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0";
            if ($filter_supplier != "") $sqlPorPeriod .= " AND supplier_id = " . $this->db->escape($filter_supplier);
            if ($filter_item_rm != "") $sqlPorPeriod .= " AND item_rm_id = " . $this->db->escape($filter_item_rm);
            $sqlPorPeriod .= " GROUP BY supplier_id, item_rm_id";
            $mapPorPeriod = [];
            foreach ($this->db->query($sqlPorPeriod)->result_array() as $r) {
                $mapPorPeriod[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
            }

            // Bulk fetch POR Period Open Only (khusus hitung OS)
            $sqlPorPeriodOpen = "SELECT por.supplier_id, por.item_rm_id, SUM(por.qty_receipt) as qty
                FROM purchase_order_receipts por
                INNER JOIN purchase_orders po ON por.po_no = po.po_no AND por.item_rm_id = po.item_rm_id AND po.status = 0
                WHERE por.receipt_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND por.deleted = 0";
            if ($filter_supplier != "") $sqlPorPeriodOpen .= " AND por.supplier_id = " . $this->db->escape($filter_supplier);
            if ($filter_item_rm != "") $sqlPorPeriodOpen .= " AND por.item_rm_id = " . $this->db->escape($filter_item_rm);
            $sqlPorPeriodOpen .= " GROUP BY por.supplier_id, por.item_rm_id";
            $mapPorPeriodOpen = [];
            foreach ($this->db->query($sqlPorPeriodOpen)->result_array() as $r) {
                $mapPorPeriodOpen[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
            }

            foreach ($recordsAll as &$rec) {
                $key = $rec['supplier_id'] . '_' . $rec['item_rm_id'];
                $poPrevQty = isset($mapPoPrev[$key]) ? $mapPoPrev[$key] : 0;
                $porPrevQty = isset($mapPorPrev[$key]) ? $mapPorPrev[$key] : 0;
                $osPoPrev = max(0, $poPrevQty - $porPrevQty);

                $poPeriodQty = isset($mapPoPeriod[$key]) ? $mapPoPeriod[$key] : 0;
                $porPeriodQty = isset($mapPorPeriod[$key]) ? $mapPorPeriod[$key] : 0;

                $poPeriodOpenQty = isset($mapPoPeriodOpen[$key]) ? $mapPoPeriodOpen[$key] : 0;
                $porPeriodOpenQty = isset($mapPorPeriodOpen[$key]) ? $mapPorPeriodOpen[$key] : 0;

                $rec['qty_memo'] = $poPeriodQty + $osPoPrev;
                $rec['qty_por'] = $porPeriodQty;

                // OS Prev: hanya dari PO OPEN sebelum filter (yg belum close)
                // OS Period: semua PO dalam periode (open + closed)
                $rec['ost_memo'] = max(0, ($osPoPrev + $poPeriodQty) - $porPeriodQty);
            }
            unset($rec);

            // Filter out item yang qty_memo == 0 dan qty_por == 0
            $recordsAll = array_values(array_filter($recordsAll, function($item) {
                return ($item['qty_memo'] > 0 || $item['qty_por'] > 0);
            }));

            $totalRows = count($recordsAll);
            $records = array_slice($recordsAll, $offset, $rows);

            // Bulk fetch daily request_materials (PLAN)
            $sqlDailyRm = "SELECT supplier_id, item_rm_id, request_date, SUM(qty) as qty FROM request_materials WHERE request_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0";
            if ($filter_supplier != "") $sqlDailyRm .= " AND supplier_id = " . $this->db->escape($filter_supplier);
            if ($filter_item_rm != "") $sqlDailyRm .= " AND item_rm_id = " . $this->db->escape($filter_item_rm);
            $sqlDailyRm .= " GROUP BY supplier_id, item_rm_id, request_date";
            $mapDailyRm = [];
            foreach ($this->db->query($sqlDailyRm)->result_array() as $r) {
                $mapDailyRm[$r['supplier_id'] . '_' . $r['item_rm_id'] . '_' . $r['request_date']] = (float)$r['qty'];
            }

            // Bulk fetch daily purchase_order_receipts (ACTUAL)
            $sqlDailyPor = "SELECT supplier_id, item_rm_id, receipt_date, SUM(qty_receipt) as qty FROM purchase_order_receipts WHERE receipt_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0";
            if ($filter_supplier != "") $sqlDailyPor .= " AND supplier_id = " . $this->db->escape($filter_supplier);
            if ($filter_item_rm != "") $sqlDailyPor .= " AND item_rm_id = " . $this->db->escape($filter_item_rm);
            $sqlDailyPor .= " GROUP BY supplier_id, item_rm_id, receipt_date";
            $mapDailyPor = [];
            foreach ($this->db->query($sqlDailyPor)->result_array() as $r) {
                $mapDailyPor[$r['supplier_id'] . '_' . $r['item_rm_id'] . '_' . $r['receipt_date']] = (float)$r['qty'];
            }

            // Bulk fetch daily transaction_rm (NG: RETURN NG TO SUPPLIER) berdasarkan receipt_date dari POR
            $sqlDailyNg = "SELECT t.supplier_id, t.item_rm_id, COALESCE(por.receipt_date, t.request_date) as receipt_date, SUM(t.qty) as qty 
                           FROM transaction_rm t
                           LEFT JOIN purchase_order_receipts por ON ((por.receipt_id = t.request_no OR por.receipt_no = t.request_no) OR (t.po_no != '' AND por.po_no = t.po_no)) AND por.item_rm_id = t.item_rm_id AND por.deleted = 0
                           WHERE t.transaction_type = 'RETURN NG TO SUPPLIER' 
                             AND COALESCE(por.receipt_date, t.request_date) BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " 
                             AND t.deleted = 0";
            if ($filter_supplier != "") $sqlDailyNg .= " AND t.supplier_id = " . $this->db->escape($filter_supplier);
            if ($filter_item_rm != "") $sqlDailyNg .= " AND t.item_rm_id = " . $this->db->escape($filter_item_rm);
            $sqlDailyNg .= " GROUP BY t.supplier_id, t.item_rm_id, COALESCE(por.receipt_date, t.request_date)";
            $mapDailyNg = [];
            foreach ($this->db->query($sqlDailyNg)->result_array() as $r) {
                $mapDailyNg[$r['supplier_id'] . '_' . $r['item_rm_id'] . '_' . $r['receipt_date']] = (float)$r['qty'];
            }

            $dataFinal = array();
            foreach ($records as $record) {
                $arrNg = array();
                $arrPctQty = array();
                $arrPctQual = array();

                $typesArr = ["PLAN", "ACTUAL", "BALANCE", "NG", "%Quantity", "%Quality"];
                foreach ($typesArr as $type) {
                    $currentDate = $filter_from;

                    $total_qty = 0;
                    $total_del = 0;
                    $total_ng = 0;
                    $day = 1;

                    $prevBalance = 0;

                    while (strtotime((string) $currentDate) <= strtotime((string) $filter_to)) {
                        $trans_date = date("Y-m-d", strtotime((string) $currentDate));
                        $dailyKey = $record['supplier_id'] . '_' . $record['item_rm_id'] . '_' . $trans_date;

                        $actual_qty = isset($mapDailyRm[$dailyKey]) ? $mapDailyRm[$dailyKey] : 0;
                        $delivery_qty = isset($mapDailyPor[$dailyKey]) ? $mapDailyPor[$dailyKey] : 0;
                        $ng_qty = isset($mapDailyNg[$dailyKey]) ? $mapDailyNg[$dailyKey] : 0;

                        $currentBalance = $prevBalance + $delivery_qty - $actual_qty;

                        // Jika plan, actual, dan ng bernilai 0, kosongkan per tanggal
                        if ($actual_qty == 0 && $delivery_qty == 0 && $ng_qty == 0) {
                            $arrSales["qty_" . $day] = "";
                            $arrDelivery["qty_" . $day] = "";
                            $arrBalance["qty_" . $day] = "";
                            $arrNg["qty_" . $day] = "";
                            $arrPctQty["qty_" . $day] = "";
                            $arrPctQual["qty_" . $day] = "";
                        } else {
                            $arrSales["qty_" . $day] = number_format((float) $actual_qty, 2);
                            $arrDelivery["qty_" . $day] = number_format((float) $delivery_qty, 2);
                            $arrBalance["qty_" . $day] = number_format($delivery_qty - $actual_qty, 2);
                            $arrNg["qty_" . $day] = number_format((float) $ng_qty, 2);
                            
                            // %Quantity: (Actual/Plan)*100 jika Plan > 0, jika Plan == 0 dan Actual > 0 maka 100.00%, else 0.00%
                            $arrPctQty["qty_" . $day] = $actual_qty > 0 ? number_format(($delivery_qty / $actual_qty) * 100, 2) . "%" : ($delivery_qty > 0 ? "100.00%" : "0.00%");
                            
                            // %Quality: 100 - ((NG/Actual)*100) jika Actual > 0, else 100.00% (jika NG 0) atau 0.00%
                            $arrPctQual["qty_" . $day] = $delivery_qty > 0 ? number_format(100 - (($ng_qty / $delivery_qty) * 100), 2) . "%" : ($ng_qty > 0 ? "0.00%" : "100.00%");
                        }

                        $currentDate = date("Y-m-d", strtotime("+1 day", strtotime((string) $currentDate)));
                        $day++;
                        $total_qty += $actual_qty;
                        $total_del += $delivery_qty;
                        $total_ng += $ng_qty;
                    }

                    if ($type == "PLAN") {
                        $arr = array_merge($arrSales, ["total_qty" => $total_qty]);
                    } elseif ($type == "ACTUAL") {
                        $arr = array_merge($arrDelivery, ["total_qty" => $total_del]);
                    } elseif ($type == "BALANCE") {
                        $totBal = ($total_qty == 0 && $total_del == 0) ? "" : number_format($total_del - $total_qty, 2);
                        $arr = array_merge($arrBalance, ["total_qty" => $totBal]);
                    } elseif ($type == "NG") {
                        $arr = array_merge($arrNg, ["total_qty" => ($total_qty == 0 && $total_del == 0 && $total_ng == 0 ? "" : number_format((float) $total_ng, 2))]);
                    } elseif ($type == "%Quantity") {
                        $totPctQty = ($total_qty == 0 && $total_del == 0) ? "" : ($total_qty > 0 ? number_format(($total_del / $total_qty) * 100, 2) . "%" : ($total_del > 0 ? "100.00%" : "0.00%"));
                        $arr = array_merge($arrPctQty, ["total_qty" => $totPctQty]);
                    } elseif ($type == "%Quality") {
                        $totPctQual = ($total_qty == 0 && $total_del == 0) ? "" : ($total_del > 0 ? number_format(100 - (($total_ng / $total_del) * 100), 2) . "%" : "0.00%");
                        $arr = array_merge($arrPctQual, ["total_qty" => $totPctQual]);
                    }

                    $dataFinal[] = array_merge($arr, [
                        "item_rm_id" => $record['item_rm_id'],
                        "supplier_id" => $record['supplier_id'],
                        "supplier_name" => $record['supplier_name'],
                        "item_number" => $record['item_number'],
                        "division" => $record['division'],
                        "item_name" => $record['item_name'],
                        "uom" => $record['uom'],
                        "qty_memo" => $record['qty_memo'],
                        "qty_por" => $record['qty_por'],
                        "ost_memo" => $record['ost_memo'],
                        "type" => $type,
                    ]);
                }
            }

            $result['total'] = $totalRows;
            $result['rows'] = $dataFinal;
            echo json_encode($result);
        }
    }

    public function print($option = "")
    {
         if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_achievment_daily_supp_$format.xls");
        }
        $raw_from = $this->input->get('filter_from');
        $raw_to = $this->input->get('filter_to');
        $raw_supplier = $this->input->get('filter_supplier');
        $raw_item_rm = $this->input->get('filter_item_rm');

        $filter_from = (base64_encode(base64_decode($raw_from, true)) === $raw_from) ? base64_decode($raw_from) : $raw_from;
        $filter_to = (base64_encode(base64_decode($raw_to, true)) === $raw_to) ? base64_decode($raw_to) : $raw_to;
        $filter_supplier = (base64_encode(base64_decode($raw_supplier, true)) === $raw_supplier) ? base64_decode($raw_supplier) : $raw_supplier;
        $filter_item_rm = (base64_encode(base64_decode($raw_item_rm, true)) === $raw_item_rm) ? base64_decode($raw_item_rm) : $raw_item_rm;

        $year_start_pr = date('Y', strtotime((string) $filter_from)) . '-01-01';
        $sqlMain = "SELECT t.supplier_id, t.item_rm_id, b.number as item_number, b.name as item_name, b.uom,
                    c.name as supplier_name, x.name as division
                    FROM (
                        SELECT supplier_id, item_rm_id FROM request_materials WHERE memo_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0
                        UNION
                        SELECT supplier_id, item_rm_id FROM purchase_order_receipts WHERE receipt_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0
                    ) t
                    JOIN item_rm b ON t.item_rm_id = b.id
                    JOIN suppliers c ON t.supplier_id = c.id
                    LEFT JOIN divisions x ON b.division = x.number
                    WHERE 1=1";

        if ($filter_supplier != "") {
            $sqlMain .= " AND t.supplier_id = " . $this->db->escape($filter_supplier);
        }
        if ($filter_item_rm != "") {
            $sqlMain .= " AND t.item_rm_id = " . $this->db->escape($filter_item_rm);
        }

        $sqlMain .= " ORDER BY c.name ASC, b.number ASC";

        $queryRes = $this->db->query($sqlMain);
        $records = $queryRes ? $queryRes->result_array() : array();

        // Bulk fetch PO Prev (sebelum filter_from): hanya PO OPEN (status = 0)
        $sqlPoPrev = "SELECT supplier_id, item_rm_id, SUM(qty) as qty FROM purchase_orders WHERE po_date >= " . $this->db->escape($year_start_pr) . " AND po_date < " . $this->db->escape($filter_from) . " AND status = 0 AND deleted = 0";
        if ($filter_supplier != "") $sqlPoPrev .= " AND supplier_id = " . $this->db->escape($filter_supplier);
        if ($filter_item_rm != "") $sqlPoPrev .= " AND item_rm_id = " . $this->db->escape($filter_item_rm);
        $sqlPoPrev .= " GROUP BY supplier_id, item_rm_id";
        $mapPoPrev = [];
        foreach ($this->db->query($sqlPoPrev)->result_array() as $r) {
            $mapPoPrev[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
        }

        // Bulk fetch POR Prev — hanya receipt dari OPEN PO (status = 0)
        $sqlPorPrev = "SELECT por.supplier_id, por.item_rm_id, SUM(por.qty_receipt) as qty
            FROM purchase_order_receipts por
            INNER JOIN purchase_orders po ON por.po_no = po.po_no AND por.item_rm_id = po.item_rm_id AND po.status = 0
            WHERE por.receipt_date >= " . $this->db->escape($year_start_pr) . " AND por.receipt_date < " . $this->db->escape($filter_from) . " AND por.deleted = 0";
        if ($filter_supplier != "") $sqlPorPrev .= " AND por.supplier_id = " . $this->db->escape($filter_supplier);
        if ($filter_item_rm != "") $sqlPorPrev .= " AND por.item_rm_id = " . $this->db->escape($filter_item_rm);
        $sqlPorPrev .= " GROUP BY por.supplier_id, por.item_rm_id";
        $mapPorPrev = [];
        foreach ($this->db->query($sqlPorPrev)->result_array() as $r) {
            $mapPorPrev[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
        }

        // Bulk fetch PO Period (semua status untuk tampilan QTY PO)
        $sqlPoPeriod = "SELECT supplier_id, item_rm_id, SUM(qty) as qty FROM purchase_orders WHERE po_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0";
        if ($filter_supplier != "") $sqlPoPeriod .= " AND supplier_id = " . $this->db->escape($filter_supplier);
        if ($filter_item_rm != "") $sqlPoPeriod .= " AND item_rm_id = " . $this->db->escape($filter_item_rm);
        $sqlPoPeriod .= " GROUP BY supplier_id, item_rm_id";
        $mapPoPeriod = [];
        foreach ($this->db->query($sqlPoPeriod)->result_array() as $r) {
            $mapPoPeriod[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
        }

        // Bulk fetch PO Period Open Only (khusus hitung OS)
        $sqlPoPeriodOpen = "SELECT supplier_id, item_rm_id, SUM(qty) as qty FROM purchase_orders WHERE po_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND status = 0 AND deleted = 0";
        if ($filter_supplier != "") $sqlPoPeriodOpen .= " AND supplier_id = " . $this->db->escape($filter_supplier);
        if ($filter_item_rm != "") $sqlPoPeriodOpen .= " AND item_rm_id = " . $this->db->escape($filter_item_rm);
        $sqlPoPeriodOpen .= " GROUP BY supplier_id, item_rm_id";
        $mapPoPeriodOpen = [];
        foreach ($this->db->query($sqlPoPeriodOpen)->result_array() as $r) {
            $mapPoPeriodOpen[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
        }

        // Bulk fetch POR Period (semua status untuk tampilan QTY POR)
        $sqlPorPeriod = "SELECT supplier_id, item_rm_id, SUM(qty_receipt) as qty FROM purchase_order_receipts WHERE receipt_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0";
        if ($filter_supplier != "") $sqlPorPeriod .= " AND supplier_id = " . $this->db->escape($filter_supplier);
        if ($filter_item_rm != "") $sqlPorPeriod .= " AND item_rm_id = " . $this->db->escape($filter_item_rm);
        $sqlPorPeriod .= " GROUP BY supplier_id, item_rm_id";
        $mapPorPeriod = [];
        foreach ($this->db->query($sqlPorPeriod)->result_array() as $r) {
            $mapPorPeriod[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
        }

        // Bulk fetch POR Period Open Only (khusus hitung OS)
        $sqlPorPeriodOpen = "SELECT por.supplier_id, por.item_rm_id, SUM(por.qty_receipt) as qty
            FROM purchase_order_receipts por
            INNER JOIN purchase_orders po ON por.po_no = po.po_no AND por.item_rm_id = po.item_rm_id AND po.status = 0
            WHERE por.receipt_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND por.deleted = 0";
        if ($filter_supplier != "") $sqlPorPeriodOpen .= " AND por.supplier_id = " . $this->db->escape($filter_supplier);
        if ($filter_item_rm != "") $sqlPorPeriodOpen .= " AND por.item_rm_id = " . $this->db->escape($filter_item_rm);
        $sqlPorPeriodOpen .= " GROUP BY por.supplier_id, por.item_rm_id";
        $mapPorPeriodOpen = [];
        foreach ($this->db->query($sqlPorPeriodOpen)->result_array() as $r) {
            $mapPorPeriodOpen[$r['supplier_id'] . '_' . $r['item_rm_id']] = (float)$r['qty'];
        }

        foreach ($records as &$rec) {
            $key = $rec['supplier_id'] . '_' . $rec['item_rm_id'];
            $poPrevQty = isset($mapPoPrev[$key]) ? $mapPoPrev[$key] : 0;
            $porPrevQty = isset($mapPorPrev[$key]) ? $mapPorPrev[$key] : 0;
            $osPoPrev = max(0, $poPrevQty - $porPrevQty);

            $poPeriodQty = isset($mapPoPeriod[$key]) ? $mapPoPeriod[$key] : 0;
            $porPeriodQty = isset($mapPorPeriod[$key]) ? $mapPorPeriod[$key] : 0;

            $poPeriodOpenQty = isset($mapPoPeriodOpen[$key]) ? $mapPoPeriodOpen[$key] : 0;
            $porPeriodOpenQty = isset($mapPorPeriodOpen[$key]) ? $mapPorPeriodOpen[$key] : 0;

            $rec['qty_memo'] = $poPeriodQty + $osPoPrev;
            $rec['qty_por'] = $porPeriodQty;

            // OS Prev: hanya dari PO OPEN sebelum filter (yg belum close)
            // OS Period: semua PO dalam periode (open + closed)
            $rec['ost_memo'] = max(0, ($osPoPrev + $poPeriodQty) - $porPeriodQty);
        }
        unset($rec);

        // Filter out item yang qty_memo == 0 dan qty_por == 0
        $records = array_values(array_filter($records, function($item) {
            return ($item['qty_memo'] > 0 || $item['qty_por'] > 0);
        }));

        // Bulk fetch holidays
        $sqlHolidays = "SELECT working_date, remarks FROM calendars WHERE working_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to);
        $mapHolidays = [];
        foreach ($this->db->query($sqlHolidays)->result_array() as $h) {
            $mapHolidays[$h['working_date']] = $h['remarks'];
        }

        // Setting Header
        $styles = "";
        $date = '';
        $content = '';
        $no = 1;
        $colspan = 0;

        $currentDate = $filter_from;
        while (strtotime((string) $currentDate) <= strtotime((string) $filter_to)) {
            $working_date = date("Y-m-d", strtotime((string) $currentDate));
            $working_date2 = date("d F", strtotime((string) $currentDate));

            $holiday_remarks = isset($mapHolidays[$working_date]) ? $mapHolidays[$working_date] : null;

            if (date('w', strtotime((string) $currentDate)) !== '0' && date('w', strtotime((string) $currentDate)) !== '6') {
                if (!empty($holiday_remarks)) {
                    $styles = 'background:#FFD974;';
                } else {
                    $styles = "";
                }
            } else {
                $styles = 'background:#FFD974;';
            }

            $date .= '<th width="50" style="text-align:center; ' . $styles . '">' . $working_date2 . '</th>';

            $colspan++;
            $currentDate = date("Y-m-d", strtotime("+1 day", strtotime((string) $currentDate)));
        }

        // Bulk fetch daily request_materials (PLAN)
        $sqlDailyRm = "SELECT supplier_id, item_rm_id, request_date, SUM(qty) as qty FROM request_materials WHERE request_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0";
        if ($filter_supplier != "") $sqlDailyRm .= " AND supplier_id = " . $this->db->escape($filter_supplier);
        if ($filter_item_rm != "") $sqlDailyRm .= " AND item_rm_id = " . $this->db->escape($filter_item_rm);
        $sqlDailyRm .= " GROUP BY supplier_id, item_rm_id, request_date";
        $mapDailyRm = [];
        foreach ($this->db->query($sqlDailyRm)->result_array() as $r) {
            $mapDailyRm[$r['supplier_id'] . '_' . $r['item_rm_id'] . '_' . $r['request_date']] = (float)$r['qty'];
        }

        // Bulk fetch daily purchase_order_receipts (ACTUAL)
        $sqlDailyPor = "SELECT supplier_id, item_rm_id, receipt_date, SUM(qty_receipt) as qty FROM purchase_order_receipts WHERE receipt_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0";
        if ($filter_supplier != "") $sqlDailyPor .= " AND supplier_id = " . $this->db->escape($filter_supplier);
        if ($filter_item_rm != "") $sqlDailyPor .= " AND item_rm_id = " . $this->db->escape($filter_item_rm);
        $sqlDailyPor .= " GROUP BY supplier_id, item_rm_id, receipt_date";
        $mapDailyPor = [];
        foreach ($this->db->query($sqlDailyPor)->result_array() as $r) {
            $mapDailyPor[$r['supplier_id'] . '_' . $r['item_rm_id'] . '_' . $r['receipt_date']] = (float)$r['qty'];
        }

        // Bulk fetch daily transaction_rm (NG: RETURN NG TO SUPPLIER)
        $sqlDailyNg = "SELECT supplier_id, item_rm_id, request_date, SUM(qty) as qty FROM transaction_rm WHERE transaction_type = 'RETURN NG TO SUPPLIER' AND request_date BETWEEN " . $this->db->escape($filter_from) . " AND " . $this->db->escape($filter_to) . " AND deleted = 0";
        if ($filter_supplier != "") $sqlDailyNg .= " AND supplier_id = " . $this->db->escape($filter_supplier);
        if ($filter_item_rm != "") $sqlDailyNg .= " AND item_rm_id = " . $this->db->escape($filter_item_rm);
        $sqlDailyNg .= " GROUP BY supplier_id, item_rm_id, request_date";
        $mapDailyNg = [];
        foreach ($this->db->query($sqlDailyNg)->result_array() as $r) {
            $mapDailyNg[$r['supplier_id'] . '_' . $r['item_rm_id'] . '_' . $r['request_date']] = (float)$r['qty'];
        }

        foreach ($records as $record) {
            $typesArr = ["PLAN", "ACTUAL", "BALANCE", "NG", "%Quantity", "%Quality"];
            foreach ($typesArr as $type) {
                $currentDate = $filter_from;
                $isi = "";
                $total_qty = 0;
                $total_del = 0;
                $total_balance = 0;
                $total_ng = 0;

                $prevBalance = 0;

                while (strtotime((string) $currentDate) <= strtotime((string) $filter_to)) {
                    $trans_date = date("Y-m-d", strtotime((string) $currentDate));
                    $dailyKey = $record['supplier_id'] . '_' . $record['item_rm_id'] . '_' . $trans_date;

                    $actual_qty = isset($mapDailyRm[$dailyKey]) ? $mapDailyRm[$dailyKey] : 0;
                    $delivery_qty = isset($mapDailyPor[$dailyKey]) ? $mapDailyPor[$dailyKey] : 0;
                    $ng_qty = isset($mapDailyNg[$dailyKey]) ? $mapDailyNg[$dailyKey] : 0;

                    $currentBalance = $prevBalance + $delivery_qty - $actual_qty;

                    if ($type == "PLAN") {
                        $valPlan = ($actual_qty == 0 && $delivery_qty == 0) ? "" : number_format((float) $actual_qty, 2);
                        $isi .= "<td style='text-align:right;'>" . $valPlan . "</td>";
                        $total_qty += $actual_qty;
                    } elseif ($type == "ACTUAL") {
                        $valAct = ($actual_qty == 0 && $delivery_qty == 0) ? "" : number_format((float) $delivery_qty, 2);
                        $isi .= "<td style='text-align:right;'>" . $valAct . "</td>";
                        $total_del += $delivery_qty;
                    } elseif ($type == "BALANCE") {
                        $valBal = ($actual_qty == 0 && $delivery_qty == 0) ? "" : number_format($delivery_qty - $actual_qty, 2);
                        $isi .= "<td style='text-align:right;'>" . $valBal . "</td>";
                        $total_qty += $actual_qty;
                        $total_del += $delivery_qty;
                    } elseif ($type == "NG") {
                        $valNg = ($actual_qty == 0 && $delivery_qty == 0) ? "" : number_format((float) $ng_qty, 2);
                        $isi .= "<td style='text-align:right;'>" . $valNg . "</td>";
                        $total_ng += $ng_qty;
                    } elseif ($type == "%Quantity") {
                        $pctQty = ($actual_qty == 0 && $delivery_qty == 0) ? "" : ($actual_qty > 0 ? number_format(($delivery_qty / $actual_qty) * 100, 2) . "%" : ($delivery_qty > 0 ? "100.00%" : "0.00%"));
                        $isi .= "<td style='text-align:right;'>" . $pctQty . "</td>";
                        $total_qty += $actual_qty;
                        $total_del += $delivery_qty;
                    } elseif ($type == "%Quality") {
                        $pctQual = ($actual_qty == 0 && $delivery_qty == 0) ? "" : ($delivery_qty > 0 ? number_format(100 - (($ng_qty / $delivery_qty) * 100), 2) . "%" : "0.00%");
                        $isi .= "<td style='text-align:right;'>" . $pctQual . "</td>";
                        $total_del += $delivery_qty;
                        $total_ng += $ng_qty;
                    }

                    $currentDate = date("Y-m-d", strtotime("+1 day", strtotime((string) $currentDate)));
                    $prevBalance = $currentBalance;
                }

                if ($type == "PLAN") {
                    $isi .= "<td style='text-align:right; font-weight:bold;'>" . number_format((float) $total_qty, 2) . "</td>";
                } elseif ($type == "ACTUAL") {
                    $isi .= "<td style='text-align:right; font-weight:bold;'>" . number_format((float) $total_del, 2) . "</td>";
                } elseif ($type == "BALANCE") {
                    $totBalPrint = ($total_qty == 0 && $total_del == 0) ? "" : number_format($total_del - $total_qty, 2);
                    $isi .= "<td style='text-align:right; font-weight:bold;'>" . $totBalPrint . "</td>";
                } elseif ($type == "NG") {
                    $valTotNg = ($total_qty == 0 && $total_del == 0) ? "" : number_format((float) $total_ng, 2);
                    $isi .= "<td style='text-align:right; font-weight:bold;'>" . $valTotNg . "</td>";
                } elseif ($type == "%Quantity") {
                    $totPctQty = ($total_qty == 0 && $total_del == 0) ? "" : ($total_qty > 0 ? number_format(($total_del / $total_qty) * 100, 2) . "%" : ($total_del > 0 ? "100.00%" : "0.00%"));
                    $isi .= "<td style='text-align:right; font-weight:bold;'>" . $totPctQty . "</td>";
                } elseif ($type == "%Quality") {
                    $totPctQual = ($total_qty == 0 && $total_del == 0) ? "" : ($total_del > 0 ? number_format(100 - (($total_ng / $total_del) * 100), 2) . "%" : "0.00%");
                    $isi .= "<td style='text-align:right; font-weight:bold;'>" . $totPctQual . "</td>";
                }
                $content .= '<tr>';
                if ($type == "PLAN") {
                    $content .= '<td rowspan="6" style="text-align:center;">' . $no . '</td>';
                    $content .= '<td rowspan="6">' . $record['supplier_name'] . '</td>';
                    $content .= '<td rowspan="6">' . $record['division'] . '</td>';
                    $content .= '<td rowspan="6">' . $record['item_number'] . '</td>';
                    $content .= '<td rowspan="6">' . $record['item_name'] . '</td>';
                    $content .= '<td rowspan="6">' . $record['uom'] . '</td>';
                    $content .= '<td rowspan="6" style="text-align:right;">' . number_format((float) $record['qty_memo'], 2) . '</td>';
                    $content .= '<td rowspan="6" style="text-align:right;">' . number_format((float) $record['qty_por'], 2) . '</td>';
                    $content .= '<td rowspan="6" style="text-align:right;">' . number_format((float) $record['ost_memo'], 2) . '</td>';
                }

                $content .= '<td>' . $type . '</td>';
                $content .= $isi;
                $content .= '</tr>';
            }
            $no++;
        }

        $html = '<html><head><title>Print Data</title></head><body>
            <table id="customers" border="1" style="width:100%;">
                <tr>
                    <th style="text-align:center;" rowspan="2">NO</th>
                    <th style="text-align:center;" rowspan="2">Material No</th>
                    <th style="text-align:center;" rowspan="2">Material Name</th>
                    <th style="text-align:center;" rowspan="2">UoM</th>
                    <th style="text-align:center;" rowspan="2">Division</th>
                    <th style="text-align:center;" rowspan="2">Supplier</th>
                    <th style="text-align:center;" rowspan="2">QTY PO</th>
                    <th style="text-align:center;" rowspan="2">QTY POR</th>
                    <th style="text-align:center;" rowspan="2">Outstanding PO</th>
                    <th style="text-align:center;" rowspan="2">Type</th>
                    <th style="text-align:center;" colspan="' . $colspan . '">Report Daily Supplier Plan VS Actual</th>
                </tr>' . $date . $content . '</table></body></html>';

        echo $html;
    }
}
