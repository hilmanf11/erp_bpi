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

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=summary_forecasts_$format.xls");
        }

        $filter_period_year = base64_decode($this->input->get("filter_period_year"));
        $filter_period_month = base64_decode($this->input->get("filter_period_month"));
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

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
        $this->db->from('forecasts a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->like('a.p_month', $filter_period_month);
        $this->db->like('a.p_year', $filter_period_year);
        $this->db->like('a.item_fg_id', $filter_item_fg);
        // $this->db->where("a.p_year '$filter_period_year'");
        // $this->db->where("a.p_month '$filter_period_month'");
        $this->db->group_by('a.item_fg_id');
        // $this->db->group_by('a.p_month');
        // $this->db->group_by('a.p_year');
        $this->db->order_by('a.item_fg_id', 'ASC');
        $records = $this->db->get()->result_array();

        if ($filter_item_fg == ""){
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#summary_forecasts {border-collapse: collapse;width: 100%;font-size: 12px;}#summary_forecasts td, #summary_forecasts th {border: 1px solid #ddd;padding: 2px;}#summary_forecasts tr:nth-child(even){background-color: #f2f2f2;}#summary_forecasts tr:hover {background-color: #ddd;}#summary_forecasts th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>SUMMARY FORECAST</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PERIOD : <b>' . $filter_period_month . '</b>  <b>' . $filter_period_year . '</b></small><br>
                                <small>PRODUCT NO. : <b>ALL</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            <br><br><br><br>
            <table id="summary_forecasts" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product No.</th>
                <th>Product Name</th>
                <th>' . $dates[0]['name'] . '</th>
                <th>' . $dates[1]['name'] . '</th>
                <th>' . $dates[2]['name'] . '</th>
                <th>' . $dates[3]['name'] . '</th>
                <th>' . $dates[4]['name'] . '</th>
                <th>' . $dates[5]['name'] . '</th>
                <th>' . $dates[6]['name'] . '</th>
                <th>' . $dates[7]['name'] . '</th>
                <th>' . $dates[8]['name'] . '</th>
                <th>' . $dates[9]['name'] . '</th>
                <th>' . $dates[10]['name'] . '</th>
                <th>' . $dates[11]['name'] . '</th>
            </tr>';

            $no = 1;
            $gt_month_1 = 0;
            $gt_month_2 = 0;
            $gt_month_3 = 0;
            $gt_month_4 = 0;
            $gt_month_5 = 0;
            $gt_month_6 = 0;
            $gt_month_7 = 0;
            $gt_month_8 = 0;
            $gt_month_9 = 0;
            $gt_month_10 = 0;
            $gt_month_11 = 0;
            $gt_month_12 = 0;
            foreach ($records as $data) {
                $gt_1 = $gt_month_1 += $data['month_1'];
                $gt_2 = $gt_month_2 += $data['month_2'];
                $gt_3 = $gt_month_3 += $data['month_3'];
                $gt_4 = $gt_month_4 += $data['month_4'];
                $gt_5 = $gt_month_5 += $data['month_5'];
                $gt_6 = $gt_month_6 += $data['month_6'];
                $gt_7 = $gt_month_7 += $data['month_7'];
                $gt_8 = $gt_month_8 += $data['month_8'];
                $gt_9 = $gt_month_9 += $data['month_9'];
                $gt_10 = $gt_month_10 += $data['month_10'];
                $gt_11 = $gt_month_11 += $data['month_11'];
                $gt_12 = $gt_month_12 += $data['month_12'];
                $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['month_1'] . '</td>
                        <td>' . $data['month_2'] . '</td>
                        <td>' . $data['month_3'] . '</td>
                        <td>' . $data['month_4'] . '</td>
                        <td>' . $data['month_5'] . '</td>
                        <td>' . $data['month_6'] . '</td>
                        <td>' . $data['month_7'] . '</td>
                        <td>' . $data['month_8'] . '</td>
                        <td>' . $data['month_9'] . '</td>
                        <td>' . $data['month_10'] . '</td>
                        <td>' . $data['month_11'] . '</td>
                        <td>' . $data['month_12'] . '</td>';
                $no++;
            }
            $html .= '<tr>
                            <th colspan="3">Grand Total</th>
                            <th>' . $gt_1 . '</th>
                            <th>' . $gt_2 . '</th>
                            <th>' . $gt_3 . '</th>
                            <th>' . $gt_4 . '</th>
                            <th>' . $gt_5 . '</th>
                            <th>' . $gt_6 . '</th>
                            <th>' . $gt_7 . '</th>
                            <th>' . $gt_8 . '</th>
                            <th>' . $gt_9 . '</th>
                            <th>' . $gt_10 . '</th>
                            <th>' . $gt_11 . '</th>
                            <th>' . $gt_12 . '</th>';
            $html .= '</table></body></html>';
            echo $html;
        } else {
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#summary_forecasts {border-collapse: collapse;width: 100%;font-size: 12px;}#summary_forecasts td, #summary_forecasts th {border: 1px solid #ddd;padding: 2px;}#summary_forecasts tr:nth-child(even){background-color: #f2f2f2;}#summary_forecasts tr:hover {background-color: #ddd;}#summary_forecasts th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>SUMMARY FORECAST</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PERIOD : <b>' . $filter_period_month . '</b>  <b>' . $filter_period_year . '</b></small><br>
                                <small>PRODUCT NO. : <b>' . $filter_item_fg . '</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            <br><br><br><br>
            <table id="summary_forecasts" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product No.</th>
                <th>Product Name</th>
                <th>' . $dates[0]['name'] . '</th>
                <th>' . $dates[1]['name'] . '</th>
                <th>' . $dates[2]['name'] . '</th>
                <th>' . $dates[3]['name'] . '</th>
                <th>' . $dates[4]['name'] . '</th>
                <th>' . $dates[5]['name'] . '</th>
                <th>' . $dates[6]['name'] . '</th>
                <th>' . $dates[7]['name'] . '</th>
                <th>' . $dates[8]['name'] . '</th>
                <th>' . $dates[9]['name'] . '</th>
                <th>' . $dates[10]['name'] . '</th>
                <th>' . $dates[11]['name'] . '</th>
            </tr>';

            $no = 1;
            $gt_month_1 = 0;
            $gt_month_2 = 0;
            $gt_month_3 = 0;
            $gt_month_4 = 0;
            $gt_month_5 = 0;
            $gt_month_6 = 0;
            $gt_month_7 = 0;
            $gt_month_8 = 0;
            $gt_month_9 = 0;
            $gt_month_10 = 0;
            $gt_month_11 = 0;
            $gt_month_12 = 0;
            foreach ($records as $data) {
                $gt_1 = $gt_month_1 += $data['month_1'];
                $gt_2 = $gt_month_2 += $data['month_2'];
                $gt_3 = $gt_month_3 += $data['month_3'];
                $gt_4 = $gt_month_4 += $data['month_4'];
                $gt_5 = $gt_month_5 += $data['month_5'];
                $gt_6 = $gt_month_6 += $data['month_6'];
                $gt_7 = $gt_month_7 += $data['month_7'];
                $gt_8 = $gt_month_8 += $data['month_8'];
                $gt_9 = $gt_month_9 += $data['month_9'];
                $gt_10 = $gt_month_10 += $data['month_10'];
                $gt_11 = $gt_month_11 += $data['month_11'];
                $gt_12 = $gt_month_12 += $data['month_12'];
                $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['month_1'] . '</td>
                        <td>' . $data['month_2'] . '</td>
                        <td>' . $data['month_3'] . '</td>
                        <td>' . $data['month_4'] . '</td>
                        <td>' . $data['month_5'] . '</td>
                        <td>' . $data['month_6'] . '</td>
                        <td>' . $data['month_7'] . '</td>
                        <td>' . $data['month_8'] . '</td>
                        <td>' . $data['month_9'] . '</td>
                        <td>' . $data['month_10'] . '</td>
                        <td>' . $data['month_11'] . '</td>
                        <td>' . $data['month_12'] . '</td>';
                $no++;
            }
            $html .= '<tr>
                        <th colspan="3">Grand Total</th>
                        <th>' . $gt_1 . '</th>
                        <th>' . $gt_2 . '</th>
                        <th>' . $gt_3 . '</th>
                        <th>' . $gt_4 . '</th>
                        <th>' . $gt_5 . '</th>
                        <th>' . $gt_6 . '</th>
                        <th>' . $gt_7 . '</th>
                        <th>' . $gt_8 . '</th>
                        <th>' . $gt_9 . '</th>
                        <th>' . $gt_10 . '</th>
                        <th>' . $gt_11 . '</th>
                        <th>' . $gt_12 . '</th>';
            $html .= '</table></body></html>';
            echo $html;
        }
    }
}
