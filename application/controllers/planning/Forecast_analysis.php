<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Forecast_analysis extends CI_Controller
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
        $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[50]');
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/forecast_analysis');
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

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=forecast_analysis_$format.xls");
        }

        $filter_period_year = base64_decode($this->input->get("filter_period_year"));
        $filter_period_month = base64_decode($this->input->get("filter_period_month"));
        $filter_customer_name = $this->input->get("filter_customer_name");
        $filter_item_fg = $this->input->get("filter_item_fg");

        $p_date_start = date("Y-m-d", strtotime($filter_period_year . "-" . $filter_period_month . "-01"));
        $p_date_to = date('Y-m-d', strtotime('+11 month', strtotime($p_date_start)));
        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
        $this->db->from('forecasts a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->like('a.p_month', $filter_period_month);
        $this->db->like('a.p_year', $filter_period_year);
        $this->db->like('a.customer_id', $filter_customer_name);
        $this->db->like('a.item_fg_id', $filter_item_fg);
        // $this->db->where("a.p_year '$filter_period_year'");
        // $this->db->where("a.p_month '$filter_period_month'");
        $this->db->group_by('a.customer_id');
        $this->db->group_by('a.item_fg_id');
        // $this->db->group_by('a.p_month');
        // $this->db->group_by('a.p_year');
        $this->db->order_by('a.item_fg_id', 'ASC');
        $records = $this->db->get()->result_array();

        if ($filter_period_month == "01") {
            $month_name = "JANUARY";
        } elseif ($filter_period_month == "02") {
            $month_name = "FEBRUARY";
        } elseif ($filter_period_month == "03") {
            $month_name = "MARCH";
        } elseif ($filter_period_month == "04") {
            $month_name = "APRIL";
        } elseif ($filter_period_month == "05") {
            $month_name = "MAY";
        } elseif ($filter_period_month == "06") {
            $month_name = "JUNE";
        } elseif ($filter_period_month == "07") {
            $month_name = "JULY";
        } elseif ($filter_period_month == "08") {
            $month_name = "AUGUST";
        } elseif ($filter_period_month == "09") {
            $month_name = "SEPTEMBER";
        } elseif ($filter_period_month == "10") {
            $month_name = "OCTOBER";
        } elseif ($filter_period_month == "11") {
            $month_name = "NOVEMBER";
        } elseif ($filter_period_month == "12") {
            $month_name = "DECEMBER";
        }

        if ($filter_customer_name == "" && $filter_item_fg == ""){
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#forecast_analysis {border-collapse: collapse;width: 100%;font-size: 12px;}#forecast_analysis td, #forecast_analysis th {border: 1px solid #ddd;padding: 2px;}#forecast_analysis tr:nth-child(even){background-color: #f2f2f2;}#forecast_analysis tr:hover {background-color: #ddd;}#forecast_analysis th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:m:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br>
                <div style="float: centet; font-size: 16px; text-align: center;">
                    <h3>FORECAST ANALYSIS</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PERIOD</small><br>
                                <small>CUSTOMER NAME</small><br>
                                <small>PRODUCT NO.</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small><br>
                                <small>: </small><br>
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $month_name . '</b>  <b>' . $filter_period_year . '</b></small><br>
                                <small><b>ALL</b></small><br>
                                <small><b>ALL</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            <br><br>
            <table id="forecast_analysis" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product No.</th>
                <th>Product Name</th>
                <th>Month</th>
                <th>Forecast</th>
                <th>4 Month</th>
                <th>6 Month</th>
                <th>12 Month</th>
            </tr>';

            $no = 1;
            $f_4_month_4 = 0;
            $f_4_month_5 = 0;
            $f_4_month_6 = 0;
            $f_4_month_7 = 0;
            $f_4_month_8 = 0;
            $f_4_month_9 = 0;
            $f_4_month_10 = 0;
            $f_4_month_11 = 0;
            $f_4_month_12 = 0;

            $f_6_month_6 = 0;
            $f_6_month_7 = 0;
            $f_6_month_8 = 0;
            $f_6_month_9 = 0;
            $f_6_month_10 = 0;
            $f_6_month_11 = 0;
            $f_6_month_12 = 0;

            $f_12_month_12 = 0;

            foreach ($records as $data) {

                $f_4_month_4 = ($data['month_1'] + $data['month_2'] + $data['month_3'] + $data['month_4']) / 4;
                $f_4_month_5 = ($data['month_2'] + $data['month_3'] + $data['month_4'] + $data['month_5']) / 4;
                $f_4_month_6 = ($data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6']) / 4;
                $f_4_month_7 = ($data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7']) / 4;
                $f_4_month_8 = ($data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8']) / 4;
                $f_4_month_9 = ($data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9']) / 4;
                $f_4_month_10 = ($data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10']) / 4;
                $f_4_month_11 = ($data['month_8'] + $data['month_9'] + $data['month_10'] + $data['month_11']) / 4;
                $f_4_month_12 = ($data['month_9'] + $data['month_10'] + $data['month_11'] + $data['month_12']) / 4;

                $f_6_month_6 = ($data['month_1'] + $data['month_2'] + $data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6']) / 6;
                $f_6_month_7 = ($data['month_2'] + $data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7']) / 6;
                $f_6_month_8 = ($data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8']) / 6;
                $f_6_month_9 = ($data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9']) / 6;
                $f_6_month_10 = ($data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10']) / 6;
                $f_6_month_11 = ($data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10'] + $data['month_11']) / 6;
                $f_6_month_12 = ($data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10'] + $data['month_11'] + $data['month_12']) / 6;

                $f_12_month_12 = ($data['month_1'] + $data['month_2'] + $data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10'] + $data['month_11'] + $data['month_12']) / 12;

                $html .= '<tr>
                        <td rowspan="13">' . $no . '</td>
                        <td rowspan="13">' . $data['item_fg_number'] . '</td>
                        <td rowspan="13">' . $data['item_fg_name'] . '</td>';
                $no++;
                $html .= '<tr>
                        <td>' . $dates[0]['name'] . '</td>
                        <td>' . number_format($data['month_1']) . '</td>
                        <td></td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[1]['name'] . '</td>
                        <td>' . number_format($data['month_2']) . '</td>
                        <td></td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[2]['name'] . '</td>
                        <td>' . number_format($data['month_3']) . '</td>
                        <td></td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[3]['name'] . '</td>
                        <td>' . number_format($data['month_4']) . '</td>
                        <td>' . number_format(round($f_4_month_4)) . '</td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[4]['name'] . '</td>
                        <td>' . number_format($data['month_5']) . '</td>
                        <td>' . number_format(round($f_4_month_5)) . '</td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[5]['name'] . '</td>
                        <td>' . number_format($data['month_6']) . '</td>
                        <td>' . number_format(round($f_4_month_6)) . '</td>
                        <td>' . number_format(round($f_6_month_6)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[6]['name'] . '</td>
                        <td>' . number_format($data['month_7']) . '</td>
                        <td>' . number_format(round($f_4_month_7)) . '</td>
                        <td>' . number_format(round($f_6_month_7)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[7]['name'] . '</td>
                        <td>' . number_format($data['month_8']) . '</td>
                        <td>' . number_format(round($f_4_month_8)) . '</td>
                        <td>' . number_format(round($f_6_month_8)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[8]['name'] . '</td>
                        <td>' . number_format($data['month_9']) . '</td>
                        <td>' . number_format(round($f_4_month_9)) . '</td>
                        <td>' . number_format(round($f_6_month_9)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[9]['name'] . '</td>
                        <td>' . number_format($data['month_10']) . '</td>
                        <td>' . number_format(round($f_4_month_10)) . '</td>
                        <td>' . number_format(round($f_6_month_10)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[10]['name'] . '</td>
                        <td>' . number_format($data['month_11']) . '</td>
                        <td>' . number_format(round($f_4_month_11)) . '</td>
                        <td>' . number_format(round($f_6_month_11)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[11]['name'] . '</td>
                        <td>' . number_format($data['month_12']) . '</td>
                        <td>' . number_format(round($f_4_month_12)) . '</td>
                        <td>' . number_format(round($f_6_month_12)) . '</td>
                        <td>' . number_format(round($f_12_month_12)) . '</td>';
            }
            $html .= '</table></body></html>';
            echo $html;
        } elseif ($filter_customer_name != "" && $filter_item_fg == "") {
            foreach ($records as $data) {
                $filter_customer_name = $data['customer_name'];
            }
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#forecast_analysis {border-collapse: collapse;width: 100%;font-size: 12px;}#forecast_analysis td, #forecast_analysis th {border: 1px solid #ddd;padding: 2px;}#forecast_analysis tr:nth-child(even){background-color: #f2f2f2;}#forecast_analysis tr:hover {background-color: #ddd;}#forecast_analysis th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:m:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br>
                <div style="float: centet; font-size: 16px; text-align: center;">
                    <h3>FORECAST ANALYSIS</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PERIOD</small><br>
                                <small>CUSTOMER NAME</small><br>
                                <small>PRODUCT NO.</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small><br>
                                <small>: </small><br>
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $month_name . '</b>  <b>' . $filter_period_year . '</b></small><br>
                                <small><b>' . $filter_customer_name . '</b></small><br>
                                <small><b>ALL</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            <br><br>
            <table id="forecast_analysis" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product No.</th>
                <th>Product Name</th>
                <th>Month</th>
                <th>Forecast</th>
                <th>4 Month</th>
                <th>6 Month</th>
                <th>12 Month</th>
            </tr>';

            $no = 1;
            $f_4_month_4 = 0;
            $f_4_month_5 = 0;
            $f_4_month_6 = 0;
            $f_4_month_7 = 0;
            $f_4_month_8 = 0;
            $f_4_month_9 = 0;
            $f_4_month_10 = 0;
            $f_4_month_11 = 0;
            $f_4_month_12 = 0;

            $f_6_month_6 = 0;
            $f_6_month_7 = 0;
            $f_6_month_8 = 0;
            $f_6_month_9 = 0;
            $f_6_month_10 = 0;
            $f_6_month_11 = 0;
            $f_6_month_12 = 0;

            $f_12_month_12 = 0;

            foreach ($records as $data) {

                $f_4_month_4 = ($data['month_1'] + $data['month_2'] + $data['month_3'] + $data['month_4']) / 4;
                $f_4_month_5 = ($data['month_2'] + $data['month_3'] + $data['month_4'] + $data['month_5']) / 4;
                $f_4_month_6 = ($data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6']) / 4;
                $f_4_month_7 = ($data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7']) / 4;
                $f_4_month_8 = ($data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8']) / 4;
                $f_4_month_9 = ($data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9']) / 4;
                $f_4_month_10 = ($data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10']) / 4;
                $f_4_month_11 = ($data['month_8'] + $data['month_9'] + $data['month_10'] + $data['month_11']) / 4;
                $f_4_month_12 = ($data['month_9'] + $data['month_10'] + $data['month_11'] + $data['month_12']) / 4;

                $f_6_month_6 = ($data['month_1'] + $data['month_2'] + $data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6']) / 6;
                $f_6_month_7 = ($data['month_2'] + $data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7']) / 6;
                $f_6_month_8 = ($data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8']) / 6;
                $f_6_month_9 = ($data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9']) / 6;
                $f_6_month_10 = ($data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10']) / 6;
                $f_6_month_11 = ($data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10'] + $data['month_11']) / 6;
                $f_6_month_12 = ($data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10'] + $data['month_11'] + $data['month_12']) / 6;

                $f_12_month_12 = ($data['month_1'] + $data['month_2'] + $data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10'] + $data['month_11'] + $data['month_12']) / 12;

                $html .= '<tr>
                        <td rowspan="13">' . $no . '</td>
                        <td rowspan="13">' . $data['item_fg_number'] . '</td>
                        <td rowspan="13">' . $data['item_fg_name'] . '</td>';
                $no++;
                $html .= '<tr>
                        <td>' . $dates[0]['name'] . '</td>
                        <td>' . number_format($data['month_1']) . '</td>
                        <td></td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[1]['name'] . '</td>
                        <td>' . number_format($data['month_2']) . '</td>
                        <td></td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[2]['name'] . '</td>
                        <td>' . number_format($data['month_3']) . '</td>
                        <td></td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[3]['name'] . '</td>
                        <td>' . number_format($data['month_4']) . '</td>
                        <td>' . number_format(round($f_4_month_4)) . '</td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[4]['name'] . '</td>
                        <td>' . number_format($data['month_5']) . '</td>
                        <td>' . number_format(round($f_4_month_5)) . '</td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[5]['name'] . '</td>
                        <td>' . number_format($data['month_6']) . '</td>
                        <td>' . number_format(round($f_4_month_6)) . '</td>
                        <td>' . number_format(round($f_6_month_6)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[6]['name'] . '</td>
                        <td>' . number_format($data['month_7']) . '</td>
                        <td>' . number_format(round($f_4_month_7)) . '</td>
                        <td>' . number_format(round($f_6_month_7)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[7]['name'] . '</td>
                        <td>' . number_format($data['month_8']) . '</td>
                        <td>' . number_format(round($f_4_month_8)) . '</td>
                        <td>' . number_format(round($f_6_month_8)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[8]['name'] . '</td>
                        <td>' . number_format($data['month_9']) . '</td>
                        <td>' . number_format(round($f_4_month_9)) . '</td>
                        <td>' . number_format(round($f_6_month_9)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[9]['name'] . '</td>
                        <td>' . number_format($data['month_10']) . '</td>
                        <td>' . number_format(round($f_4_month_10)) . '</td>
                        <td>' . number_format(round($f_6_month_10)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[10]['name'] . '</td>
                        <td>' . number_format($data['month_11']) . '</td>
                        <td>' . number_format(round($f_4_month_11)) . '</td>
                        <td>' . number_format(round($f_6_month_11)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[11]['name'] . '</td>
                        <td>' . number_format($data['month_12']) . '</td>
                        <td>' . number_format(round($f_4_month_12)) . '</td>
                        <td>' . number_format(round($f_6_month_12)) . '</td>
                        <td>' . number_format(round($f_12_month_12)) . '</td>';
            }
            $html .= '</table></body></html>';
            echo $html;
        } elseif ($filter_customer_name != "" && $filter_item_fg != "") {
            foreach ($records as $data) {
                $filter_customer_name = $data['customer_name'];
                $filter_item_fg = $data['item_fg_name'];
            }
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#forecast_analysis {border-collapse: collapse;width: 100%;font-size: 12px;}#forecast_analysis td, #forecast_analysis th {border: 1px solid #ddd;padding: 2px;}#forecast_analysis tr:nth-child(even){background-color: #f2f2f2;}#forecast_analysis tr:hover {background-color: #ddd;}#forecast_analysis th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:m:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br>
                <div style="float: centet; font-size: 16px; text-align: center;">
                    <h3>FORECAST ANALYSIS</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PERIOD</small><br>
                                <small>CUSTOMER NAME</small><br>
                                <small>PRODUCT NO.</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small><br>
                                <small>: </small><br>
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $month_name . '</b>  <b>' . $filter_period_year . '</b></small><br>
                                <small><b>' . $filter_customer_name . '</b></small><br>
                                <small><b>' . $filter_item_fg . '</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            <br><br>
            <table id="forecast_analysis" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product No.</th>
                <th>Product Name</th>
                <th>Month</th>
                <th>Forecast</th>
                <th>4 Month</th>
                <th>6 Month</th>
                <th>12 Month</th>
            </tr>';

            $no = 1;
            $f_4_month_4 = 0;
            $f_4_month_5 = 0;
            $f_4_month_6 = 0;
            $f_4_month_7 = 0;
            $f_4_month_8 = 0;
            $f_4_month_9 = 0;
            $f_4_month_10 = 0;
            $f_4_month_11 = 0;
            $f_4_month_12 = 0;

            $f_6_month_6 = 0;
            $f_6_month_7 = 0;
            $f_6_month_8 = 0;
            $f_6_month_9 = 0;
            $f_6_month_10 = 0;
            $f_6_month_11 = 0;
            $f_6_month_12 = 0;

            $f_12_month_12 = 0;

            foreach ($records as $data) {

                $f_4_month_4 = ($data['month_1'] + $data['month_2'] + $data['month_3'] + $data['month_4']) / 4;
                $f_4_month_5 = ($data['month_2'] + $data['month_3'] + $data['month_4'] + $data['month_5']) / 4;
                $f_4_month_6 = ($data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6']) / 4;
                $f_4_month_7 = ($data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7']) / 4;
                $f_4_month_8 = ($data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8']) / 4;
                $f_4_month_9 = ($data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9']) / 4;
                $f_4_month_10 = ($data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10']) / 4;
                $f_4_month_11 = ($data['month_8'] + $data['month_9'] + $data['month_10'] + $data['month_11']) / 4;
                $f_4_month_12 = ($data['month_9'] + $data['month_10'] + $data['month_11'] + $data['month_12']) / 4;

                $f_6_month_6 = ($data['month_1'] + $data['month_2'] + $data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6']) / 6;
                $f_6_month_7 = ($data['month_2'] + $data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7']) / 6;
                $f_6_month_8 = ($data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8']) / 6;
                $f_6_month_9 = ($data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9']) / 6;
                $f_6_month_10 = ($data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10']) / 6;
                $f_6_month_11 = ($data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10'] + $data['month_11']) / 6;
                $f_6_month_12 = ($data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10'] + $data['month_11'] + $data['month_12']) / 6;

                $f_12_month_12 = ($data['month_1'] + $data['month_2'] + $data['month_3'] + $data['month_4'] + $data['month_5'] + $data['month_6'] + $data['month_7'] + $data['month_8'] + $data['month_9'] + $data['month_10'] + $data['month_11'] + $data['month_12']) / 12;

                $html .= '<tr>
                        <td rowspan="13">' . $no . '</td>
                        <td rowspan="13">' . $data['item_fg_number'] . '</td>
                        <td rowspan="13">' . $data['item_fg_name'] . '</td>';
                $no++;
                $html .= '<tr>
                        <td>' . $dates[0]['name'] . '</td>
                        <td>' . number_format($data['month_1']) . '</td>
                        <td></td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[1]['name'] . '</td>
                        <td>' . number_format($data['month_2']) . '</td>
                        <td></td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[2]['name'] . '</td>
                        <td>' . number_format($data['month_3']) . '</td>
                        <td></td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[3]['name'] . '</td>
                        <td>' . number_format($data['month_4']) . '</td>
                        <td>' . number_format(round($f_4_month_4)) . '</td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[4]['name'] . '</td>
                        <td>' . number_format($data['month_5']) . '</td>
                        <td>' . number_format(round($f_4_month_5)) . '</td>
                        <td></td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[5]['name'] . '</td>
                        <td>' . number_format($data['month_6']) . '</td>
                        <td>' . number_format(round($f_4_month_6)) . '</td>
                        <td>' . number_format(round($f_6_month_6)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[6]['name'] . '</td>
                        <td>' . number_format($data['month_7']) . '</td>
                        <td>' . number_format(round($f_4_month_7)) . '</td>
                        <td>' . number_format(round($f_6_month_7)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[7]['name'] . '</td>
                        <td>' . number_format($data['month_8']) . '</td>
                        <td>' . number_format(round($f_4_month_8)) . '</td>
                        <td>' . number_format(round($f_6_month_8)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[8]['name'] . '</td>
                        <td>' . number_format($data['month_9']) . '</td>
                        <td>' . number_format(round($f_4_month_9)) . '</td>
                        <td>' . number_format(round($f_6_month_9)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[9]['name'] . '</td>
                        <td>' . number_format($data['month_10']) . '</td>
                        <td>' . number_format(round($f_4_month_10)) . '</td>
                        <td>' . number_format(round($f_6_month_10)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[10]['name'] . '</td>
                        <td>' . number_format($data['month_11']) . '</td>
                        <td>' . number_format(round($f_4_month_11)) . '</td>
                        <td>' . number_format(round($f_6_month_11)) . '</td>
                        <td></td>';
                $html .= '<tr>
                        <td>' . $dates[11]['name'] . '</td>
                        <td>' . number_format($data['month_12']) . '</td>
                        <td>' . number_format(round($f_4_month_12)) . '</td>
                        <td>' . number_format(round($f_6_month_12)) . '</td>
                        <td>' . number_format(round($f_12_month_12)) . '</td>';
            }
            $html .= '</table></body></html>';
            echo $html;
        }
    }
}
