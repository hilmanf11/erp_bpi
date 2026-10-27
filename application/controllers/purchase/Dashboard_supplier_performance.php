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
class Dashboard_supplier_performance extends CI_Controller
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
            $this->load->view('purchase/dashboard_supplier_performance');
        } else {
            redirect('error_access');
        }
    }

    private function get_supplier_calc_data($month, $year)
    {
        $filter_from = "$year-$month-01";
        $filter_to   = date('Y-m-t', strtotime((string) $filter_from));

        // 1. Plan (request_materials) grouped by supplier_id
        $sqlPo = "SELECT supplier_id, SUM(qty) as qty
                  FROM request_materials
                  WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND deleted = 0
                  GROUP BY supplier_id";
        $mapPo = [];
        foreach ((array)$this->db->query($sqlPo)->result_array() as $r) {
            $mapPo[$r['supplier_id']] = (float)$r['qty'];
        }

        // 2. Actual Receipt (purchase_order_receipts) grouped by supplier_id
        $sqlPor = "SELECT supplier_id, SUM(qty_receipt) as qty
                   FROM purchase_order_receipts
                   WHERE receipt_date BETWEEN '$filter_from' AND '$filter_to' AND deleted = 0
                   GROUP BY supplier_id";
        $mapPor = [];
        foreach ((array)$this->db->query($sqlPor)->result_array() as $r) {
            $mapPor[$r['supplier_id']] = (float)$r['qty'];
        }

        // 3. NG (transaction_rm) grouped by supplier_id
        $sqlNg = "SELECT supplier_id, SUM(qty) as qty
                  FROM transaction_rm
                  WHERE transaction_type = 'RETURN NG TO SUPPLIER'
                    AND request_date BETWEEN '$filter_from' AND '$filter_to' AND deleted = 0
                  GROUP BY supplier_id";
        $mapNg = [];
        foreach ((array)$this->db->query($sqlNg)->result_array() as $r) {
            $mapNg[$r['supplier_id']] = (float)$r['qty'];
        }

        // Show suppliers that have data in POR OR request_materials, exclude null/empty keys
        $activeIds = array_unique(array_merge(array_keys($mapPo), array_keys($mapPor)));
        $activeIds = array_filter($activeIds, function ($id) { return !empty($id); });

        if (empty($activeIds)) {
            return ['rows' => [], 'top10' => [], 'overall_ontime' => 0];
        }

        // Fetch supplier names for active IDs
        $inClause = implode(',', array_map(function ($id) { return (int)$id; }, $activeIds));
        $suppList = $this->db->query("SELECT id, name FROM suppliers WHERE id IN ($inClause) ORDER BY name ASC")->result_array();
        $suppMap  = [];
        foreach ((array)$suppList as $s) {
            $suppMap[$s['id']] = $s['name'];
        }

        // Only process supplier_ids that exist in master suppliers
        $activeIds = array_filter($activeIds, function ($id) use ($suppMap) {
            return isset($suppMap[$id]);
        });

        $rows = [];
        $total_delivery_pct = 0;

        foreach ($activeIds as $s_id) {
            $tot_memo = isset($mapPo[$s_id])  ? $mapPo[$s_id]  : 0;
            $tot_por  = isset($mapPor[$s_id]) ? $mapPor[$s_id] : 0;

            if ($tot_memo == 0 && $tot_por == 0) continue;

            $valDelivery = 0;
            if ($tot_por > 0 && $tot_memo == 0) {
                $valDelivery = 100; // actual ada tapi plan 0 = 100%
            } elseif ($tot_memo > 0) {
                $raw = round(($tot_por / $tot_memo) * 100, 2);
                $valDelivery = $tot_por >= $tot_memo ? 100 : $raw;
            }
            $valTotal = $valDelivery;

            $perf = 'EXCELLENT';
            if ($valTotal < 60)      $perf = 'POOR';
            elseif ($valTotal < 75)  $perf = 'AVERAGE';
            elseif ($valTotal < 90)  $perf = 'GOOD';

            $rows[] = [
                'supplier_id'   => $s_id,
                'supplier_name' => isset($suppMap[$s_id]) ? $suppMap[$s_id] : 'Unknown',
                'target'        => 100,
                'plan_qty'      => $tot_memo,
                'actual_qty'    => $tot_por,
                'pct_delivery'  => $valDelivery,
                'total_score'   => $valTotal,
                'performance'   => $perf
            ];

            $total_delivery_pct += $valDelivery;
        }

        // Sort ascending for top10 (worst performers)
        usort($rows, function ($a, $b) { return $a['total_score'] <=> $b['total_score']; });
        $top10 = array_slice($rows, 0, 10);

        // Re-sort descending for full rows
        usort($rows, function ($a, $b) { return $b['total_score'] <=> $a['total_score']; });

        $overall_ontime = count($rows) > 0 ? round($total_delivery_pct / count($rows), 1) : 0;

        return [
            'rows'           => $rows,
            'top10'          => $top10,
            'overall_ontime' => $overall_ontime
        ];
    }

    public function get_dashboard_data()
    {
        $month = @base64_decode((string) $this->input->get('month'));
        $year  = @base64_decode((string) $this->input->get('year'));

        if (empty($month) || empty($year)) {
            $month = date('m');
            $year  = date('Y');
        }

        $res = $this->get_supplier_calc_data($month, $year);

        echo json_encode([
            'total'          => count($res['rows']),
            'rows'           => $res['rows'],
            'top10'          => $res['top10'],
            'overall_ontime' => $res['overall_ontime']
        ]);
    }

    public function export_excel()
    {
        $month = @base64_decode((string) $this->input->get('month'));
        $year  = @base64_decode((string) $this->input->get('year'));

        if (empty($month) || empty($year)) {
            $month = date('m');
            $year  = date('Y');
        }

        $res = $this->get_supplier_calc_data($month, $year);
        $top10 = $res['top10'];

        $format = date("Ymd_His");
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=Supplier_Performance_Dashboard_$format.xls");

        $html = '<table border="1" style="width:100%; text-align:center;">
                    <thead>
                        <tr style="background:#244b82; color:#ffffff;">
                            <th>No</th>
                            <th>Supplier Name</th>
                            <th>Target (%)</th>
                            <th>Delivery Qty (%)</th>
                            <th>Quality (%)</th>
                            <th>Sched vs Del (%)</th>
                            <th>Total Score (%)</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>';

        $no = 1;
        foreach ($top10 as $row) {
            $html .= '<tr>
                        <td>' . $no++ . '</td>
                        <td style="text-align:left;">' . $row['supplier_name'] . '</td>
                        <td style="mso-number-format:\'\@\';">' . $row['target'] . '%</td>
                        <td style="mso-number-format:\'\@\';">' . $row['pct_delivery'] . '%</td>
                        <td style="mso-number-format:\'\@\';">' . $row['pct_quality'] . '%</td>
                        <td style="mso-number-format:\'\@\';">' . $row['sched_vs_del'] . '%</td>
                        <td style="mso-number-format:\'\@\';">' . $row['total_score'] . '%</td>
                        <td>' . $row['performance'] . '</td>
                    </tr>';
        }

        $html .= '</tbody></table>';
        echo $html;
    }
}



