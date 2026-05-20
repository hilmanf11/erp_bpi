<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_ng extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('po_no', 'PO No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('control/report_ng');
        } else {
            redirect('error_access');
        }
    }

    public function readCust()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('customers', ["name" => $post]);
        echo json_encode($send);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_ng_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_customer = $this->input->get("filter_customer");
        $filter_division = $this->input->get("filter_division");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $query_main = "SELECT a.id,
                    a.number,
                    a.name, 
                    b.customer_name,
                    COALESCE(c.qty_actual,0) as qty_actual,
                    COALESCE(c2.qty_wip,0) as qty_wip,
                    COALESCE(d.qty_ng,0) as qty_ng
                FROM item_fg a
                INNER JOIN (
                    SELECT x.item_fg_id,GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS customer_name
                    FROM (
                        SELECT ci.item_fg_id, ci.customer_id
                        FROM customer_items ci
                        WHERE ci.customer_id LIKE '%$filter_customer%'

                        UNION

                        SELECT s.item_fg_sa_id AS item_fg_id, ci.customer_id
                        FROM item_fg_subs s
                        JOIN customer_items ci ON s.item_fg_id = ci.item_fg_id
                        WHERE ci.customer_id LIKE '%$filter_customer%'
                    ) x
                    JOIN customers c ON x.customer_id = c.id
                    GROUP BY x.item_fg_id
                ) b ON a.id = b.item_fg_id
                LEFT JOIN (SELECT item_fg_id, SUM(qty) AS qty_actual FROM output_productions WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_fg_id) c ON a.id = c.item_fg_id
                LEFT JOIN (SELECT item_fg_id, SUM(qty_wip) AS qty_wip FROM output_productions WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_fg_id) c2 ON a.id = c2.item_fg_id
                LEFT JOIN (SELECT aa.item_fg_id, SUM(aa.qty_product) AS qty_ng 
                    FROM (
                        SELECT DISTINCT document, item_fg_id, qty_product 
                        FROM item_ng 
                        WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' AND kind LIKE 'Ng Process Production'
                    ) aa 
                    GROUP BY aa.item_fg_id
                ) d ON a.id = d.item_fg_id
                WHERE a.type != 'RM' 
                AND a.id LIKE '%$filter_items%' 
                AND a.division_id LIKE '%$filter_division%' 
                AND a.status = 0
                ORDER BY a.number";

        $records = $this->crud->query($query_main);

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>' . $config->description . '</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br><br>
            <h3 style="margin:0;">REPORT NG</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th width="20">No</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th colspan ="2">Customer</th>
                    <th colspan ="2">Total Production</th>
                    <th colspan ="2">Output Production</th>
                    <th>NG process</th>
                    <th>% NG process</th>
                </tr>';
        $no = 1;
        $totalTotalProduction = 0;
        $totalOutputProduction = 0;
        $totalNG = 0;
        $totalPersen = 0;
        foreach ($records as $record) {
            $item_fg_id = $record->id;

            $total = $record->qty_actual + $record->qty_wip + $record->qty_ng;
            $ng_percent = $total > 0 ? number_format(($record->qty_ng / $total) * 100, 2) . "%" : "0.00%";

            $totalTotalProduction += $total;
            $totalOutputProduction += ($record->qty_actual + $record->qty_wip);
            $totalNG += $record->qty_ng;

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $record->number . '</td>
                            <td style="mso-number-format:\@;">' . $record->name . '</td>
                            <td colspan="2">' . $record->customer_name . '</td>
                            <td colspan ="2"; style="text-align:right;">' . number_format($total, 2) . '</td>
                            <td colspan ="2"; style="text-align:right;">' . number_format($record->qty_actual + $record->qty_wip, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_ng, 2) . '</td>
                            <td style="text-align:right;">' . $ng_percent . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="23" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
                $html .= '  <tr>
                                <th rowspan="2" width="20"></th>
                                <th rowspan="2" width="20">No</th>
                                <th rowspan="2" >Product No</th>
                                <th rowspan="2" >Product Name</th>
                                <th rowspan="2" >Type</th>
                                <th rowspan="2" >Trans Date</th>
                                <th rowspan="2" >WO / Doc</th>
                                <th colspan="2" >Output Production</th>
                                <th rowspan="2" >NG process</th>
                                <th rowspan="2" >Total Production</th>
                           </tr>
                            <tr>
                                <th>Qty FG</th>
                                <th>Qty WIP</th>
                            </tr>';

                $nod = 1;
                $in_qty = 0;
                $end_qty = 0;

                $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$item_fg_id' and trans_date between '$filter_from' and '$filter_to'");

                $dataNgs = $this->crud->query("select aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng 
                            FROM (select distinct trans_date,document,item_fg_id, qty_product FROM item_ng where item_fg_id='$item_fg_id' and trans_date between '$filter_from' and '$filter_to' AND kind LIKE 'Ng Process Production'
                    ) aa group by aa.document,aa.trans_date,aa.item_fg_id
                ");

                // Proses data berdasarkan tanggal
                $all_data = [];

                foreach ($dataActualProductions as $actualProduction) {
                    $all_data[] = [
                        'type' => 'ACTUAL PRODUCTION',
                        'date' => $actualProduction->trans_date,
                        'wo_no' => $actualProduction->wo_no,
                        'actual_production' => $actualProduction->qty,
                        'qty_wip' => $actualProduction->qty_wip,
                        'ng' => 0,
                    ];
                }

                foreach ($dataNgs as $dataNg) {
                    $all_data[] = [
                        'type' => 'PRODUCT NG',
                        'date' => $dataNg->trans_date,
                        'wo_no' => $dataNg->document,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => $dataNg->qty_ng,
                    ];
                }

                // Urutkan data berdasarkan tanggal
                usort($all_data, function ($a, $b) {
                    return strtotime($a['date']) - strtotime($b['date']);
                });
             
                // Generate HTML
                $nod = 1;
                foreach ($all_data as $data) {
                    $total_production = $data['actual_production'] + $data['qty_wip'] + $data['ng'];
                    $html .= '  <tr>
                                    <td></td>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td>' . $record->number  . '</td>
                                    <td>' . $record->name  . '</td>
                                    <td>' . $data['type']  . '</td>
                                    <td>' . $data['date']  . '</td>
                                    <td>' . $data['wo_no']  . '</td>
                                    <td style="text-align:right;">' . number_format($data['actual_production'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['qty_wip'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['ng'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($total_production, 2)  . '</td>
                                </tr>';
                    $nod++;
                }
            } 
            $no++;
        }

        $html .= '<tr>
            <td colspan="5" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td colspan="2"; style="text-align:right;">' . number_format($totalTotalProduction, 2) . '</td>
            <td colspan="2"; style="text-align:right;">' . number_format($totalOutputProduction, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalNG, 2) . '</td>
            <td style="text-align:right;">' . ($totalTotalProduction > 0 ? number_format(($totalNG / $totalTotalProduction) * 100, 2) . '%' : '0.00%') . '</td>
        </tr>
        </tbody>';
      
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_sum_customer($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_ng_summary_customer_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_customer = $this->input->get("filter_customer");
        $filter_division = $this->input->get("filter_division");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $query_main = "
            SELECT 
                customer_name,
                SUM(qty_actual + qty_wip + qty_ng) AS total_production,
                SUM(qty_actual + qty_wip) AS output_production,
                SUM(qty_ng) AS qty_ng,
                CASE 
                    WHEN SUM(qty_actual + qty_wip + qty_ng) > 0 
                    THEN ROUND(SUM(qty_ng) / SUM(qty_actual + qty_wip + qty_ng) * 100, 2)
                    ELSE 0
                END AS ng_percent
            FROM (
                SELECT 
                    a.id AS item_fg_id,
                    a.number,
                    a.name,
                    c.name AS customer_name,
                    COALESCE(op.qty_actual,0) AS qty_actual,
                    COALESCE(op.qty_wip,0) AS qty_wip,
                    COALESCE(ng.qty_ng,0) AS qty_ng
                FROM item_fg a
                JOIN (
                    SELECT 
                        x.item_fg_id,
                        x.customer_id
                    FROM (
                        SELECT ci.item_fg_id, ci.customer_id
                        FROM customer_items ci
                        WHERE ci.customer_id LIKE '%$filter_customer%'

                        UNION

                        SELECT s.item_fg_sa_id AS item_fg_id, ci.customer_id
                        FROM item_fg_subs s
                        JOIN customer_items ci ON s.item_fg_id = ci.item_fg_id
                        WHERE ci.customer_id LIKE '%$filter_customer%'
                    ) x
                ) rel ON a.id = rel.item_fg_id
                JOIN customers c ON c.id = rel.customer_id

                LEFT JOIN (
                    SELECT item_fg_id, 
                        SUM(qty) AS qty_actual,
                        SUM(qty_wip) AS qty_wip
                    FROM output_productions 
                    WHERE trans_date BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY item_fg_id
                ) op ON a.id = op.item_fg_id

                LEFT JOIN (
                    SELECT item_fg_id, 
                        SUM(qty_product) AS qty_ng
                    FROM (
                        SELECT DISTINCT document, item_fg_id, qty_product
                        FROM item_ng
                        WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' AND kind LIKE 'Ng Process Production'
                    ) ng1
                    GROUP BY item_fg_id
                ) ng ON a.id = ng.item_fg_id

                WHERE a.type != 'RM'
                AND a.status = 0
                AND a.id LIKE '%$filter_items%'
                AND a.division_id LIKE '%$filter_division%'
            ) AS sub
            GROUP BY customer_name
            ORDER BY customer_name
        ";

        $records = $this->crud->query($query_main);

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>' . $config->description . '</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br><br>
            <h3 style="margin:0;">REPORT NG SUMMARY CUSTOMER</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th width="20">No</th>
                    <th colspan ="2">Customer</th>
                    <th colspan ="2">Total Production</th>
                    <th colspan ="2">Output Production</th>
                    <th>NG process</th>
                    <th>% NG process</th>
                </tr>';
        $no = 1;
        $totalTotalProduction = 0;
        $totalOutputProduction = 0;
        $totalNG = 0;
        $totalPersen = 0;
        foreach ($records as $record) {
            $totalTotalProduction += $record->total_production;
            $totalOutputProduction += $record->output_production;
            $totalNG += $record->qty_ng;

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="2">' . $record->customer_name . '</td>
                            <td colspan ="2"; style="text-align:right;">' . number_format($record->total_production, 2) . '</td>
                            <td colspan ="2"; style="text-align:right;">' . number_format($record->output_production, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_ng, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->ng_percent, 2) . '</td>
                        </tr>';

            // if ($filter_display == "DETAIL") {
            //     $html .= '  <tr>
            //                     <td colspan="23" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
            //                 </tr>';
            //     $html .= '  <tr>
            //                     <th rowspan="2" width="20"></th>
            //                     <th rowspan="2" width="20">No</th>
            //                     <th rowspan="2" >Product No</th>
            //                     <th rowspan="2" >Product Name</th>
            //                     <th rowspan="2" >Type</th>
            //                     <th rowspan="2" >Trans Date</th>
            //                     <th rowspan="2" >WO / Doc</th>
            //                     <th colspan="2" >Output Production</th>
            //                     <th rowspan="2" >NG</th>
            //                     <th rowspan="2" >Total Production</th>
            //                </tr>
            //                 <tr>
            //                     <th>Qty FG</th>
            //                     <th>Qty WIP</th>
            //                 </tr>';

            //     $nod = 1;
            //     $in_qty = 0;
            //     $end_qty = 0;

            //     $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$item_fg_id' and trans_date between '$filter_from' and '$filter_to'");

            //     $dataNgs = $this->crud->query("select aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng 
            //                 FROM (select distinct trans_date,document,item_fg_id, qty_product FROM item_ng where item_fg_id='$item_fg_id' and trans_date between '$filter_from' and '$filter_to'
            //         ) aa group by aa.document,aa.trans_date,aa.item_fg_id
            //     ");

            //     // Proses data berdasarkan tanggal
            //     $all_data = [];

            //     foreach ($dataActualProductions as $actualProduction) {
            //         $all_data[] = [
            //             'type' => 'ACTUAL PRODUCTION',
            //             'date' => $actualProduction->trans_date,
            //             'wo_no' => $actualProduction->wo_no,
            //             'actual_production' => $actualProduction->qty,
            //             'qty_wip' => $actualProduction->qty_wip,
            //             'ng' => 0,
            //         ];
            //     }

            //     foreach ($dataNgs as $dataNg) {
            //         $all_data[] = [
            //             'type' => 'PRODUCT NG',
            //             'date' => $dataNg->trans_date,
            //             'wo_no' => $dataNg->document,
            //             'actual_production' => 0,
            //             'qty_wip' => 0,
            //             'ng' => $dataNg->qty_ng,
            //         ];
            //     }

            //     // Urutkan data berdasarkan tanggal
            //     usort($all_data, function ($a, $b) {
            //         return strtotime($a['date']) - strtotime($b['date']);
            //     });
             
            //     // Generate HTML
            //     $nod = 1;
            //     foreach ($all_data as $data) {
            //         $total_production = $data['actual_production'] + $data['qty_wip'] + $data['ng'];
            //         $html .= '  <tr>
            //                         <td></td>
            //                         <td style="text-align:center">' . $nod . '</td>
            //                         <td>' . $record->number  . '</td>
            //                         <td>' . $record->name  . '</td>
            //                         <td>' . $data['type']  . '</td>
            //                         <td>' . $data['date']  . '</td>
            //                         <td>' . $data['wo_no']  . '</td>
            //                         <td style="text-align:right;">' . number_format($data['actual_production'], 2)  . '</td>
            //                         <td style="text-align:right;">' . number_format($data['qty_wip'], 2)  . '</td>
            //                         <td style="text-align:right;">' . number_format($data['ng'], 2)  . '</td>
            //                         <td style="text-align:right;">' . number_format($total_production, 2)  . '</td>
            //                     </tr>';
            //         $nod++;
            //     }
            // } 
            $no++;
        }

        $html .= '<tr>
            <td colspan="3" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td colspan="2"; style="text-align:right;">' . number_format($totalTotalProduction, 2) . '</td>
            <td colspan="2"; style="text-align:right;">' . number_format($totalOutputProduction, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalNG, 2) . '</td>
            <td style="text-align:right;">' . ($totalTotalProduction > 0 ? number_format(($totalNG / $totalTotalProduction) * 100, 2) . '%' : '0.00%') . '</td>
        </tr>
        </tbody>';
      
        $html .= '</table></body></html>';
        echo $html;
    }
}
