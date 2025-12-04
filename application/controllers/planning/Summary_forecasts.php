<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Summary_forecasts extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/summary_forecasts');
        } else {
            redirect('error_access');
        }
    }

    //GET PERIOD
    public function readPeriod($select)
    {
        if ($select == "month") {
            $month = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
            foreach ($month as $key => $value) {
                $months[] = array("id" => $key, "name" => $value);
            }

            echo json_encode($months);
        } else if ($select == "year") {
            $year_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
            $year_now = date('Y', strtotime('+1 year', strtotime(date('Y'))));
            for ($i = $year_now; $i >= $year_before; $i--) {
                $years[] = array("id" => $i, "name" => $i);
            }

            echo json_encode($years);
        } else {
            show_error("Cannot Process your request");
        }
    }

    //GET PERIOD LISTS
    public function readPeriodLists()
    {
        $p_month = $this->input->post('p_month');
        $p_year = $this->input->post('p_year');
        $p_date_start = date("Y-m-d", strtotime($p_year . "-" . $p_month . "-01"));
        $p_date_to = date('Y-m-d', strtotime('+11 month', strtotime($p_date_start)));

        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
        }

        echo json_encode($dates);
    }

    // public function print($option = "")
    // {
    //     if ($option == "excel") {
    //         $format  = date("Ymd");
    //         header("Content-type: application/vnd-ms-excel");
    //         header("Content-Disposition: attachment; filename=summary_forecasts_$format.xls");
    //     }

    //     $filter_period_year = base64_decode($this->input->get("filter_period_year"));
    //     $filter_month_from = base64_decode($this->input->get("filter_month_from"));
    //     $filter_month_to = base64_decode($this->input->get("filter_month_to"));
    //     $filter_item_fg = $this->input->get("filter_item_fg");

    //     $p_date_start = date("Y-m-d", strtotime($filter_period_year . "-" . $filter_period_month . "-01"));
    //     $p_date_to = date('Y-m-d', strtotime('+11 month', strtotime($p_date_start)));
    //     while (strtotime($p_date_start) <= strtotime($p_date_to)) {
    //         $dates[] = array(
    //             "name" => date("M-y", strtotime($p_date_start))
    //         );

    //         $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
    //     }

    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();
    //     $sql = "
    //         SELECT 
    //             f.item_fg_id,
    //             i.number AS item_fg_number,
    //             i.name AS item_fg_name,
    //             SUM(f.month_1) AS month_1,
    //             SUM(f.month_2) AS month_2,
    //             SUM(f.month_3) AS month_3,
    //             SUM(f.month_4) AS month_4,
    //             SUM(f.month_5) AS month_5,
    //             SUM(f.month_6) AS month_6,
    //             SUM(f.month_7) AS month_7,
    //             SUM(f.month_8) AS month_8,
    //             SUM(f.month_9) AS month_9,
    //             SUM(f.month_10) AS month_10,
    //             SUM(f.month_11) AS month_11,
    //             SUM(f.month_12) AS month_12
    //         FROM forecasts f
    //         JOIN (
    //             SELECT customer_id, item_fg_id, MAX(revision) AS revision
    //             FROM forecasts
    //             WHERE deleted = 0
    //             AND p_year = '$filter_period_year'
    //             AND p_month = '$filter_period_month'
    //             GROUP BY customer_id, item_fg_id
    //         ) AS latest_rev ON 
    //             f.customer_id = latest_rev.customer_id AND 
    //             f.item_fg_id = latest_rev.item_fg_id AND 
    //             f.revision = latest_rev.revision
    //         JOIN item_fg i ON f.item_fg_id = i.id
    //         WHERE f.deleted = 0
    //         AND f.p_year = '$filter_period_year'
    //         AND f.p_month = '$filter_period_month'
    //         AND f.item_fg_id LIKE '%$filter_item_fg%'
    //         GROUP BY f.item_fg_id, i.number, i.name
    //         ORDER BY f.item_fg_id ASC
    //     ";

    //     $records = $this->db->query($sql)->result_array();

    //     if ($filter_period_month == "01") {
    //         $month_name = "JANUARY";
    //     } elseif ($filter_period_month == "02") {
    //         $month_name = "FEBRUARY";
    //     } elseif ($filter_period_month == "03") {
    //         $month_name = "MARCH";
    //     } elseif ($filter_period_month == "04") {
    //         $month_name = "APRIL";
    //     } elseif ($filter_period_month == "05") {
    //         $month_name = "MAY";
    //     } elseif ($filter_period_month == "06") {
    //         $month_name = "JUNE";
    //     } elseif ($filter_period_month == "07") {
    //         $month_name = "JULY";
    //     } elseif ($filter_period_month == "08") {
    //         $month_name = "AUGUST";
    //     } elseif ($filter_period_month == "09") {
    //         $month_name = "SEPTEMBER";
    //     } elseif ($filter_period_month == "10") {
    //         $month_name = "OCTOBER";
    //     } elseif ($filter_period_month == "11") {
    //         $month_name = "NOVEMBER";
    //     } elseif ($filter_period_month == "12") {
    //         $month_name = "DECEMBER";
    //     }

    //     if ($filter_item_fg == ""){
    //         $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#summary_forecasts {border-collapse: collapse;width: 100%;font-size: 12px;}#summary_forecasts td, #summary_forecasts th {border: 1px solid #ddd;padding: 2px;}#summary_forecasts tr:nth-child(even){background-color: #f2f2f2;}#summary_forecasts tr:hover {background-color: #ddd;}#summary_forecasts th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
    //         <center>
    //             <div style="float: left; font-size: 12px; text-align: left;">
    //                 <table style="width: 100%;">
    //                     <tr>
    //                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
    //                             <img src="' . $config->favicon . '" width="30">
    //                         </td>
    //                         <td style="font-size: 14px; text-align: left; margin:2px;">
    //                             <b>' . $config->name . '</b><br>
    //                         </td>
    //                     </tr>
    //                 </table>
    //             </div>
    //             <div style="float: right; font-size: 12px; text-align: right;">
    //                 Print Date ' . date("d M Y H:m:s") . ' <br>
    //                 Print By ' . $this->session->username . '  
    //             </div>
    //             <br><br>
    //             <div style="float: centet; font-size: 16px; text-align: center;">
    //                 <h3>SUMMARY FORECAST</h3>
    //             </div>
    //             <div style="float: left; font-size: 12px; text-align: left;">
    //                 <table style="width: 100%;">
    //                     <tr>
    //                         <td style="font-size: 14px; text-align: left; margin:2px;">
    //                             <small>PERIOD</small><br>
    //                             <small>PRODUCT NO.</small>
    //                         </td>
    //                         <td style="font-size: 14px; text-align: left; margin:2px;">
    //                             <small>: </small><br>
    //                             <small>: </small>
    //                         </td>
    //                         <td style="font-size: 14px; text-align: left; margin:2px;">
    //                             <small><b>' . $month_name . '</b>  <b>' . $filter_period_year . '</b></small><br>
    //                             <small><b>ALL</b></small>
    //                         </td>
    //                     </tr>
    //                 </table>
    //             </div>
    //         </center>
    //         <br><br>
    //         <table id="summary_forecasts" border="1">
    //         <tr>
    //             <th width="20">No</th>
    //             <th>Product No.</th>
    //             <th>Product Name</th>
    //             <th style="text-align: center;">' . $dates[0]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[1]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[2]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[3]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[4]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[5]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[6]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[7]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[8]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[9]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[10]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[11]['name'] . '</th>
    //         </tr>';

    //         $no = 1;
    //         $gt_month_1 = 0;
    //         $gt_month_2 = 0;
    //         $gt_month_3 = 0;
    //         $gt_month_4 = 0;
    //         $gt_month_5 = 0;
    //         $gt_month_6 = 0;
    //         $gt_month_7 = 0;
    //         $gt_month_8 = 0;
    //         $gt_month_9 = 0;
    //         $gt_month_10 = 0;
    //         $gt_month_11 = 0;
    //         $gt_month_12 = 0;
    //         $gt_1 = 0;
    //         $gt_2 = 0;
    //         $gt_3 = 0;
    //         $gt_4 = 0;
    //         $gt_5 = 0;
    //         $gt_6 = 0;
    //         $gt_7 = 0;
    //         $gt_8 = 0;
    //         $gt_9 = 0;
    //         $gt_10 = 0;
    //         $gt_11 = 0;
    //         $gt_12 = 0;
    //         foreach ($records as $data) {
    //             $gt_1 = $gt_month_1 += $data['month_1'];
    //             $gt_2 = $gt_month_2 += $data['month_2'];
    //             $gt_3 = $gt_month_3 += $data['month_3'];
    //             $gt_4 = $gt_month_4 += $data['month_4'];
    //             $gt_5 = $gt_month_5 += $data['month_5'];
    //             $gt_6 = $gt_month_6 += $data['month_6'];
    //             $gt_7 = $gt_month_7 += $data['month_7'];
    //             $gt_8 = $gt_month_8 += $data['month_8'];
    //             $gt_9 = $gt_month_9 += $data['month_9'];
    //             $gt_10 = $gt_month_10 += $data['month_10'];
    //             $gt_11 = $gt_month_11 += $data['month_11'];
    //             $gt_12 = $gt_month_12 += $data['month_12'];
    //             $html .= '<tr>
    //                     <td>' . $no . '</td>
    //                     <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
    //                     <td style="mso-number-format:\@;">' . $data['item_fg_name'] . '</td>
    //                     <td>' . number_format($data['month_1']) . '</td>
    //                     <td>' . number_format($data['month_2']) . '</td>
    //                     <td>' . number_format($data['month_3']) . '</td>
    //                     <td>' . number_format($data['month_4']) . '</td>
    //                     <td>' . number_format($data['month_5']) . '</td>
    //                     <td>' . number_format($data['month_6']) . '</td>
    //                     <td>' . number_format($data['month_7']) . '</td>
    //                     <td>' . number_format($data['month_8']) . '</td>
    //                     <td>' . number_format($data['month_9']) . '</td>
    //                     <td>' . number_format($data['month_10']) . '</td>
    //                     <td>' . number_format($data['month_11']) . '</td>
    //                     <td>' . number_format($data['month_12']) . '</td>';
    //             $no++;
    //         }
    //         $html .= '<tr>
    //                         <th colspan="3">Grand Total</th>
    //                         <th>' . number_format($gt_1) . '</th>
    //                         <th>' . number_format($gt_2) . '</th>
    //                         <th>' . number_format($gt_3) . '</th>
    //                         <th>' . number_format($gt_4) . '</th>
    //                         <th>' . number_format($gt_5) . '</th>
    //                         <th>' . number_format($gt_6) . '</th>
    //                         <th>' . number_format($gt_7) . '</th>
    //                         <th>' . number_format($gt_8) . '</th>
    //                         <th>' . number_format($gt_9) . '</th>
    //                         <th>' . number_format($gt_10) . '</th>
    //                         <th>' . number_format($gt_11) . '</th>
    //                         <th>' . number_format($gt_12) . '</th>';
    //         $html .= '</table></body></html>';
    //         echo $html;
    //     } elseif ($filter_item_fg != "") {
    //         foreach ($records as $data) {
    //             $filter_item_fg = $data['item_fg_name'];
    //         }
    //         $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#summary_forecasts {border-collapse: collapse;width: 100%;font-size: 12px;}#summary_forecasts td, #summary_forecasts th {border: 1px solid #ddd;padding: 2px;}#summary_forecasts tr:nth-child(even){background-color: #f2f2f2;}#summary_forecasts tr:hover {background-color: #ddd;}#summary_forecasts th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
    //         <center>
    //             <div style="float: left; font-size: 12px; text-align: left;">
    //                 <table style="width: 100%;">
    //                     <tr>
    //                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
    //                             <img src="' . $config->favicon . '" width="30">
    //                         </td>
    //                         <td style="font-size: 14px; text-align: left; margin:2px;">
    //                             <b>' . $config->name . '</b><br>
    //                         </td>
    //                     </tr>
    //                 </table>
    //             </div>
    //             <div style="float: right; font-size: 12px; text-align: right;">
    //                 Print Date ' . date("d M Y H:m:s") . ' <br>
    //                 Print By ' . $this->session->username . '  
    //             </div>
    //             <br><br>
    //             <div style="float: centet; font-size: 16px; text-align: center;">
    //                 <h3>SUMMARY FORECAST</h3>
    //             </div>
    //             <div style="float: left; font-size: 12px; text-align: left;">
    //                 <table style="width: 100%;">
    //                     <tr>
    //                         <td style="font-size: 14px; text-align: left; margin:2px;">
    //                             <small>PERIOD</small><br>
    //                             <small>PRODUCT NO.</small>
    //                         </td>
    //                         <td style="font-size: 14px; text-align: left; margin:2px;">
    //                             <small>: </small><br>
    //                             <small>: </small>
    //                         </td>
    //                         <td style="font-size: 14px; text-align: left; margin:2px;">
    //                             <small><b>' . $month_name . '</b>  <b>' . $filter_period_year . '</b></small><br>
    //                             <small><b>' . $filter_item_fg . '</b></small>
    //                         </td>
    //                     </tr>
    //                 </table>
    //             </div>
    //         </center>
    //         <br><br>
    //         <table id="summary_forecasts" border="1">
    //         <tr>
    //             <th width="20">No</th>
    //             <th>Product No.</th>
    //             <th>Product Name</th>
    //             <th style="text-align: center;">' . $dates[0]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[1]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[2]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[3]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[4]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[5]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[6]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[7]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[8]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[9]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[10]['name'] . '</th>
    //             <th style="text-align: center;">' . $dates[11]['name'] . '</th>
    //         </tr>';

    //         $no = 1;
    //         $gt_month_1 = 0;
    //         $gt_month_2 = 0;
    //         $gt_month_3 = 0;
    //         $gt_month_4 = 0;
    //         $gt_month_5 = 0;
    //         $gt_month_6 = 0;
    //         $gt_month_7 = 0;
    //         $gt_month_8 = 0;
    //         $gt_month_9 = 0;
    //         $gt_month_10 = 0;
    //         $gt_month_11 = 0;
    //         $gt_month_12 = 0;
    //         $gt_1 = 0;
    //         $gt_2 = 0;
    //         $gt_3 = 0;
    //         $gt_4 = 0;
    //         $gt_5 = 0;
    //         $gt_6 = 0;
    //         $gt_7 = 0;
    //         $gt_8 = 0;
    //         $gt_9 = 0;
    //         $gt_10 = 0;
    //         $gt_11 = 0;
    //         $gt_12 = 0;
    //         foreach ($records as $data) {
    //             $gt_1 = $gt_month_1 += $data['month_1'];
    //             $gt_2 = $gt_month_2 += $data['month_2'];
    //             $gt_3 = $gt_month_3 += $data['month_3'];
    //             $gt_4 = $gt_month_4 += $data['month_4'];
    //             $gt_5 = $gt_month_5 += $data['month_5'];
    //             $gt_6 = $gt_month_6 += $data['month_6'];
    //             $gt_7 = $gt_month_7 += $data['month_7'];
    //             $gt_8 = $gt_month_8 += $data['month_8'];
    //             $gt_9 = $gt_month_9 += $data['month_9'];
    //             $gt_10 = $gt_month_10 += $data['month_10'];
    //             $gt_11 = $gt_month_11 += $data['month_11'];
    //             $gt_12 = $gt_month_12 += $data['month_12'];
    //             $html .= '<tr>
    //                     <td>' . $no . '</td>
    //                     <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
    //                     <td style="mso-number-format:\@;">' . $data['item_fg_name'] . '</td>
    //                     <td>' . number_format($data['month_1']) . '</td>
    //                     <td>' . number_format($data['month_2']) . '</td>
    //                     <td>' . number_format($data['month_3']) . '</td>
    //                     <td>' . number_format($data['month_4']) . '</td>
    //                     <td>' . number_format($data['month_5']) . '</td>
    //                     <td>' . number_format($data['month_6']) . '</td>
    //                     <td>' . number_format($data['month_7']) . '</td>
    //                     <td>' . number_format($data['month_8']) . '</td>
    //                     <td>' . number_format($data['month_9']) . '</td>
    //                     <td>' . number_format($data['month_10']) . '</td>
    //                     <td>' . number_format($data['month_11']) . '</td>
    //                     <td>' . number_format($data['month_12']) . '</td>';
    //             $no++;
    //         }
    //         $html .= '<tr>
    //                     <th colspan="3">Grand Total</th>
    //                     <th>' . number_format($gt_1) . '</th>
    //                     <th>' . number_format($gt_2) . '</th>
    //                     <th>' . number_format($gt_3) . '</th>
    //                     <th>' . number_format($gt_4) . '</th>
    //                     <th>' . number_format($gt_5) . '</th>
    //                     <th>' . number_format($gt_6) . '</th>
    //                     <th>' . number_format($gt_7) . '</th>
    //                     <th>' . number_format($gt_8) . '</th>
    //                     <th>' . number_format($gt_9) . '</th>
    //                     <th>' . number_format($gt_10) . '</th>
    //                     <th>' . number_format($gt_11) . '</th>
    //                     <th>' . number_format($gt_12) . '</th>';
    //         $html .= '</table></body></html>';
    //         echo $html;
    //     }
    // }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=summary_forecasts_$format.xls");
        }

        // ==================== FILTER ====================
        $filter_period_year_from = (int) base64_decode($this->input->get("filter_period_year_from"));
        $filter_period_year_to   = (int) base64_decode($this->input->get("filter_period_year_to"));
        $filter_item_fg       = $this->input->get("filter_item_fg");

        // Month Range (default 1-12 jika kosong)
        $filter_month_from = (int)base64_decode($this->input->get("filter_month_from"));
        $filter_month_to   = (int)base64_decode($this->input->get("filter_month_to"));

        // ==================== GENERATE MONTH COLUMNS ====================
        $dates = [];
        $selectMonth = [];

        $y = $filter_period_year_from;
        $m = $filter_month_from;

        while (true) {

            $dates[] = [
                "year" => $y,
                "num"  => $m,
                "name" => strtoupper(date("M-y", strtotime("$y-$m-01"))),
                "col"  => "month_$m",
            ];

            $selectMonth[] = "SUM(f.month_$m) AS month_$m";

            // stop condition
            if ($y == $filter_period_year_to && $m == $filter_month_to) {
                break;
            }

            // next month
            $m++;
            if ($m == 13) {
                $m = 1;
                $y++;
            }
        }

        $selectMonthSql = implode(", ", $selectMonth);

        // var_dump($selectMonthSql);
        // return;

        $config = $this->db->get("config")->row();

        $targets = [];

        // Starting point
        $start_ts = strtotime($filter_period_year_from . '-' . $filter_month_from . '-01');
        $end_ts   = strtotime($filter_period_year_to   . '-' . $filter_month_to   . '-01');

        // Build list of target months between ranges
        $cur = $start_ts;
        while ($cur <= $end_ts) {
            $targets[] = [
                'year'  => (int)date('Y', $cur),
                'month' => (int)date('n', $cur)
            ];
            $cur = strtotime("+1 month", $cur);
        }

        // find earliest month we need to fetch from forecasts: min(target_month) - 3
        // handle year roll-over
        $firstTarget = $targets[0]; // bulan paling awal
        $earliest = $firstTarget;

        // fallback 3 bulan ke belakang
        for ($k = 0; $k < 3; $k++) {
            $earliest_ts = strtotime($earliest['year'] . '-' . $earliest['month'] . '-01');
            $earliest_ts = strtotime('-1 month', $earliest_ts);
            $earliest = [
                'year'  => (int)date('Y', $earliest_ts),
                'month' => (int)date('n', $earliest_ts)
            ];
        }
        for ($k = 0; $k < 3; $k++) {
            $earliest_ts = strtotime($earliest['year'] . '-' . $earliest['month'] . '-01');
            $earliest_ts = strtotime('-1 month', $earliest_ts);
            $earliest = ['year' => (int)date('Y', $earliest_ts), 'month' => (int)date('n', $earliest_ts)];
        }
        // latest we need is month_to of the same year (we assume user filters within single year)
        $lastTarget = end($targets);
        $latest = $lastTarget;

        // build list of (year,month) pairs to query
        $monthsToFetch = [];
        $cur = strtotime($earliest['year'] . '-' . $earliest['month'] . '-01');
        $end = strtotime($latest['year'] . '-' . $latest['month'] . '-01');

        while ($cur <= $end) {
            $monthsToFetch[] = [
                'year'  => (int)date('Y', $cur),
                'month' => (int)date('n', $cur)
            ];
            $cur = strtotime('+1 month', $cur);
        }

        $conds_noalias = [];
        $conds_falias  = [];
        foreach ($monthsToFetch as $mm) {
            $y = (int)$mm['year'];
            $m = (int)$mm['month'];
            $conds_noalias[] = "(p_year = " . $this->db->escape($y) . " AND p_month = " . $this->db->escape($m) . ")";
            $conds_falias[]  = "(f.p_year = " . $this->db->escape($y) . " AND f.p_month = " . $this->db->escape($m) . ")";
        }
        $where_months_sql_noalias = implode(" OR ", $conds_noalias);
        $where_months_sql_falias  = implode(" OR ", $conds_falias);

        // ------------------ Fetch latest revision rows for needed months ------------------
        // Select forecasts f joined to subquery that gets max revision per item/customer/year/month
        $sql = "
            SELECT f.*
            FROM forecasts f
            JOIN (
                SELECT item_fg_id, customer_id, p_year, p_month, MAX(revision) AS max_rev
                FROM forecasts
                WHERE deleted = 0
                AND ({$where_months_sql_noalias})
                GROUP BY item_fg_id, customer_id, p_year, p_month
            ) latest ON latest.item_fg_id = f.item_fg_id
                    AND latest.customer_id = f.customer_id
                    AND latest.p_year = f.p_year
                    AND latest.p_month = f.p_month
                    AND latest.max_rev = f.revision
            WHERE f.deleted = 0
            AND ({$where_months_sql_falias})
        ";
        $forecast_rows = $this->db->query($sql)->result_array();

        // ------------------ Build map: map[item][customer][year][month] = row ------------------
        $map = [];
        foreach ($forecast_rows as $r) {
            $iid = $r['item_fg_id'];
            $cid = $r['customer_id'];
            $py  = (int)$r['p_year'];
            $pm  = (int)$r['p_month'];
            if (!isset($map[$iid])) $map[$iid] = [];
            if (!isset($map[$iid][$cid])) $map[$iid][$cid] = [];
            if (!isset($map[$iid][$cid][$py])) $map[$iid][$cid][$py] = [];
            $map[$iid][$cid][$py][$pm] = $r; // store the forecast row (includes month_1..month_12)
        }

        // ------------------ Get list of items (same as your original item_fg query) ------------------
        // We'll reuse item_fg table - you may want to limit by filter_item_fg
        $items = $this->db->select('id, number, name')
                        ->from('item_fg')
                        ->where('deleted',0)
                        ->like('id', $filter_item_fg)
                        ->order_by('number','asc')
                        ->get()
                        ->result_array();

        // ------------------ For each item compute per-target-month sum (sum across customers) ------------------
        $records = []; // will contain rows like earlier ($raw transformed)
        foreach ($items as $it) {
            $item_id = $it['id'];
            $rowOut = [
                'item_fg_id' => $item_id,
                'item_fg_number' => $it['number'],
                'item_fg_name' => $it['name'],
                'months' => [] // keyed by month number
            ];

            foreach ($targets as $t) {
                $py = (int)$t['year'];
                $pm = (int)$t['month'];

                $total_for_month = 0;

                // we must consider all customers that appear for this item in target month OR in fallback months
                // gather customer set:
                $customers = [];
                if (isset($map[$item_id])) {
                    foreach ($map[$item_id] as $custId => $years) {
                        // check if any of the monthsToFetch include this customer (map already filtered)
                        $customers[$custId] = true;
                    }
                }

                // now iterate customers
                foreach ($customers as $custId => $_) {
                    $value = 0;
                    // Flag baru untuk menentukan apakah nilai definitif (bukan NULL) telah ditemukan
                    $final_value_found = false; 

                    // 1) Prioritas 1: Cek target month ($py, $pm) -> month_1
                    
                    if (isset($map[$item_id][$custId][$py][$pm])) {
                        $r0 = $map[$item_id][$custId][$py][$pm];
                        // Ambil nilai mentah (NULL, '0', atau '100')
                        $raw_v0 = $r0['month_1']; 

                        // Cek 1: Jika record ada DAN month_1 BUKAN NULL dan BUKAN string kosong
                        if ($raw_v0 !== null && $raw_v0 !== '') {
                            $value = floatval($raw_v0);
                            $final_value_found = true; 
                        }
                        // Jika raw_v0 adalah NULL, $final_value_found tetap false, dan kita lanjut ke fallback.
                    }

                    // 2) Logika Fallback jika P1 adalah NULL atau Missing (jika !$final_value_found)
                    if (!$final_value_found) {
                        
                        // Iterasi k=1..3 (fallback 1 => month_2 dari M-1; fallback 2 => month_3 dari M-2, dst)
                        for ($k = 1; $k <= 3; $k++) {
                            $check_ts = strtotime("$py-$pm-01");
                            $check_ts = strtotime("-{$k} month", $check_ts);
                            $cy = (int)date('Y', $check_ts);
                            $cm = (int)date('n', $check_ts);
                            $field = 'month_' . ($k + 1);

                            if (isset($map[$item_id][$custId][$cy][$cm])) {
                                $rr = $map[$item_id][$custId][$cy][$cm];
                                // Ambil nilai mentah dari kolom fallback
                                $raw_vv = $rr[$field] ?? null; 
                                
                                // Cek 2: Jika record fallback ada DAN nilainya BUKAN NULL/string kosong
                                if ($raw_vv !== null && $raw_vv !== '') {
                                    $value = floatval($raw_vv);
                                    $final_value_found = true;
                                    break; // Stop fallback karena nilai ditemukan
                                }
                                // Jika record ada tetapi nilainya NULL, loop berlanjut ke k++
                            }
                        }
                    }

                    // Jika tidak ada nilai yang ditemukan di P1 atau P2-P4, $value tetap 0.
                    $total_for_month += $value;
                } // end foreach ($customers)

                $key = $py . '-' . sprintf("%02d", $pm);
                $rowOut['months'][$key] = $total_for_month;
            } // targets

            $records[$item_id] = $rowOut;
        }

        // ------------------ Now you have $records per item with months sums ------------------
        // Build final array similar to your previous $raw => convert $records to $raw-like rows
        $final_rows = [];
        foreach ($records as $id => $d) {
            $r = [
                'item_fg_id' => $id,
                'item_fg_number' => $d['item_fg_number'],
                'item_fg_name' => $d['item_fg_name'],
                'months' => $d['months']
            ];
            $final_rows[] = $r;
        }

        // ==================== HEADER HTML ====================
        $first = $targets[0];
        $last  = end($targets);

        $month_title =
            strtoupper(date("F", strtotime($first["year"] . "-" . $first["month"] . "-01")))
            . " " . $first["year"] .
            " - " .
            strtoupper(date("F", strtotime($last["year"] . "-" . $last["month"] . "-01")))
            . " " . $last["year"];

        $html = '
        <html>
        <head><title>Summary Forecast</title></head>
        <style>
            body { font-family: Arial, Helvetica, sans-serif; }
            #summary_forecasts { border-collapse: collapse; width: 100%; font-size: 12px; }
            #summary_forecasts td, #summary_forecasts th { border: 1px solid #ddd; padding: 2px; }
            #summary_forecasts tr:nth-child(even) { background: #f2f2f2; }
            #summary_forecasts tr:hover { background: #ddd; }
        </style>
        <body>

        <center>
            <div style="float:left;font-size:12px;text-align:left;">
                <b>'.$config->name.'</b><br>
            </div>
            <div style="float:right;font-size:12px;">
                Print Date '.date("d M Y H:i:s").'<br>
                Print By '.$this->session->username.'
            </div>

            <br><br>
            <h3>SUMMARY FORECAST</h3>
            <div style="float:left;font-size:12px;">
                PERIOD : <b>'.$month_title.'</b>
            </div>
        </center>

        <br><br>

        <table id="summary_forecasts">
        <tr>
            <th>No</th>
            <th>Product No.</th>
            <th>Product Name</th>';

        // ===== generate header month =====
        foreach ($dates as $d) {
            $html .= '<th style="text-align:center;">'.$d["name"].'</th>';
        }

        $html .= '</tr>';

        // ==================== DATA ROWS ====================
        $no = 1;

        // Grand total index must use YEAR-MONTH keys
        $grand = [];
        foreach ($dates as $d) {
            $key = $d['year'] . '-' . sprintf("%02d", $d['num']);
            $grand[$key] = 0;
        }

        foreach ($records as $id => $data) {
            $html .= "<tr>
                <td>$no</td>
                <td style='mso-number-format:\\@;'>{$data['item_fg_number']}</td>
                <td>{$data['item_fg_name']}</td>";

            foreach ($dates as $d) {

                $key = $d['year'] . '-' . sprintf("%02d", $d['num']);

                // RECORDS now store:  months["YYYY-MM"] => value
                $qty = $data['months'][$key] ?? 0;

                $html .= "<td style='text-align:right;'>" . number_format($qty,2) . "</td>";

                $grand[$key] += $qty;
            }

            $html .= "</tr>";
            $no++;
        }

        // ==================== GRAND TOTAL ====================
        $html .= '<tr><th colspan="3">Grand Total</th>';

        foreach ($dates as $d) {
            $key = $d['year'] . '-' . sprintf("%02d", $d['num']);
            $html .= '<th style="text-align:right;">'.number_format($grand[$key],2).'</th>';
        }

        $html .= '
        </tr>
        </table>
        </body>
        </html>';

        echo $html;
    }

}
