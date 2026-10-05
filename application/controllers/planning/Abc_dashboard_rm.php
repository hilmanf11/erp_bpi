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
class Abc_dashboard_rm extends CI_Controller
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
            $this->load->view('planning/abc_dashboard_rm');
        } else {
            redirect('error_access');
        }
    }

    public function get_abc_data()
    {
        $filter_month       = @base64_decode($this->input->get('month'));
        $filter_year        = @base64_decode($this->input->get('year'));
        $currency_date_from = $filter_year . '-01-01';
        $currency_date_to   = $filter_year . '-12-31';

        $query = "SELECT 
                c.name AS prodfam,
                a.class,
                COUNT(a.item_rm_id) AS total_item,
                SUM(a.total) AS total_qty,
                SUM(a.total * COALESCE(bb.price,0) * COALESCE(bb.middle,1))   AS total_amount
            FROM generate_mrp_abcclass a
            LEFT JOIN item_rm b      ON a.item_rm_id     = b.id
            LEFT JOIN item_familys c ON b.item_family_id = c.id
            LEFT JOIN (
                SELECT sp.item_rm_id, sp.division, sp.price,
                       COALESCE(er.middle, 1) AS middle
                FROM standard_price_rm sp
                LEFT JOIN standard_exchange_rates er
                    ON  sp.currency    = er.currency_from
                    AND er.deleted     = 0
                    AND er.end_date   >= '$currency_date_to'
                    AND er.currency_to = 'IDR'
                WHERE sp.start_date = '$currency_date_from'
                  AND sp.end_date   = '$currency_date_to'
                  AND sp.deleted    = 0
            ) bb ON a.item_rm_id = bb.item_rm_id and b.division = bb.division
            WHERE a.p_month  = '$filter_month'
              AND a.p_year   = '$filter_year'
              AND a.revision = 0
            GROUP BY c.name, a.class
            ORDER BY c.name ASC, a.class ASC
        ";

        $records = $this->db->query($query)->result_array();

        // Pivot per prodfam untuk 3 metrik
        $map = [];
        foreach ($records as $row) {
            $pf  = $row['prodfam'] ?? 'Unknown';
            $cls = strtoupper(trim(substr($row['class'], -1))); // 'CLASS A' → 'A'

            if (!isset($map[$pf])) {
                $map[$pf] = [
                    'prodfam'    => $pf,
                    'item_A' => 0, 'item_B' => 0, 'item_C' => 0, 'item_total' => 0,
                    'qty_A'  => 0, 'qty_B'  => 0, 'qty_C'  => 0, 'qty_total'  => 0,
                    'amt_A'  => 0, 'amt_B'  => 0, 'amt_C'  => 0, 'amt_total'  => 0,
                ];
            }

            if (in_array($cls, ['A','B','C'])) {
                $map[$pf]["item_$cls"] += (int)$row['total_item'];
                $map[$pf]["qty_$cls"]  += (float)$row['total_qty'];
                $map[$pf]["amt_$cls"]  += (float)$row['total_amount'];
            }
            $map[$pf]['item_total'] += (int)$row['total_item'];
            $map[$pf]['qty_total']  += (float)$row['total_qty'];
            $map[$pf]['amt_total']  += (float)$row['total_amount'];
        }

        $rows = array_values($map);

        $chart_item = $chart_qty = $chart_amount = [];
        $summary_item = $summary_qty = $summary_amount = [];

        foreach ($rows as $r) {
            $chart_item[] = [
                'prodfam' => $r['prodfam'],
                'A' => $r['item_A'], 'B' => $r['item_B'], 'C' => $r['item_C'], 'total' => $r['item_total'],
            ];
            $chart_qty[] = [
                'prodfam' => $r['prodfam'],
                'A' => $r['qty_A'],  'B' => $r['qty_B'],  'C' => $r['qty_C'],  'total' => $r['qty_total'],
            ];
            $chart_amount[] = [
                'prodfam' => $r['prodfam'],
                'A' => round($r['amt_A']/1000000,2), 'B' => round($r['amt_B']/1000000,2),
                'C' => round($r['amt_C']/1000000,2), 'total' => round($r['amt_total']/1000000,2),
            ];

            // Summary: total per prodfam (untuk pie chart utama)
            $summary_item[]   = ['prodfam' => $r['prodfam'], 'value' => $r['item_total']];
            $summary_qty[]    = ['prodfam' => $r['prodfam'], 'value' => round($r['qty_total'],2)];
            $summary_amount[] = ['prodfam' => $r['prodfam'], 'value' => round($r['amt_total']/1000000,2)];
        }

        echo json_encode([
            'total'          => count($rows),
            'rows'           => $rows,
            'chart_item'     => $chart_item,
            'chart_qty'      => $chart_qty,
            'chart_amount'   => $chart_amount,
            'summary_item'   => $summary_item,
            'summary_qty'    => $summary_qty,
            'summary_amount' => $summary_amount,
        ]);
    }
}
