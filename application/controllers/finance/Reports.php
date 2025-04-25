<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Reports extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('assets/reports');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_assets_summary_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_category = $this->input->get("filter_category");

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        if ($filter_category == "SUMMARY") {

            $this->db->select('SUM(a.cost) as asset_cost, SUM(a.book_value) as book_value, a.estimate_month, b.name as asset_category_name');
            $this->db->from('asset_fixeds a');
            $this->db->join('asset_categories b', 'a.asset_category_number = b.number');
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
            $this->db->group_by('a.estimate_month');
            $this->db->group_by('b.name');
            $this->db->order_by('b.name', 'asc');
            $records = $this->db->get()->result_array();

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
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:i:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>
                </center>
                <br><br><br><br><br>
                <center>
                    <h4 style="margin:0;"><u>ANALYSIS OF DEPRECIATION FIXED ASSETS - SUMMARY</u></h4>
                    <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                </center>
                <br><br>
                
                <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Asset Category</th>
                    <th>Asset Cost</th>
                    <th>Estimated Economic Life<br>(Month)</th>
                    <th>Residual Value/Month</th>
                    <th>Residual Value/Years</th>
                    <th>Book Value</th>
                </tr>';

            $no = 1;
            foreach ($records as $record) {
                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td>' . $record['asset_category_name'] . '</td>
                            <td>' . number_format($record['asset_cost']) . '</td>
                            <td>' . $record['estimate_month'] . '</td>
                            <td>' . number_format($record['asset_cost'] / $record['estimate_month']) . '</td>
                            <td>' . number_format($record['asset_cost'] / ($record['estimate_month'] / 12)) . '</td>
                            <td>' . number_format($record['book_value']) . '</td>
                        </tr>';
                $no++;
            }

            $html .= '</table></body></html>';
            echo $html;
        } else {

            $this->db->select('a.*, b.name as asset_category_name, b.type as asset_category_type');
            $this->db->from('asset_fixeds a');
            $this->db->join('asset_categories b', 'a.asset_category_number = b.number');
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
            $this->db->group_by('b.number');
            $this->db->order_by('a.number', 'asc');
            $records = $this->db->get()->result_array();

            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
                <div style="width:1500px;">
                <center>
                    <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                    <img src="' . $config->favicon . '" width="30">
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <b>' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:i:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>
                </center>
                <br><br><br><br><br>
                <center>
                    <h4 style="margin:0;"><u>ANALYSIS OF DEPRECIATION FIXED ASSETS - DETAILS</u></h4>
                    <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                </center>
                <br><br>
                
                <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Asset Code</th>
                    <th>Asset Name</th>
                    <th>Asset Category</th>
                    <th>Purchase Invoice No</th>
                    <th>Purchase Date</th>
                    <th>Estimated Economic Life<br>(Month)</th>
                    <th>Asset Cost</th>
                    <th>Residual Value<br>(Month)</th>
                    <th>Residual Value<br>(Years)</th>
                    <th>Depreciation<br>(Amount)</th>
                    <th>Book Value</th>
                </tr>';

            $no = 1;
            foreach ($records as $record) {
                $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $record['number'] . '</td>
                        <td>' . $record['name'] . '</td>
                        <td>' . $record['asset_category_name'] . '</td>
                        <td>' . $record['purchase_invoice_number'] . '</td>
                        <td>' . $record['trans_date'] . '</td>
                        <td>' . $record['estimate_month'] . '</td>
                        <td>' . number_format($record['cost']) . '</td>
                        <td>' . number_format($record['cost'] / $record['estimate_month']) . '</td>
                        <td>' . number_format($record['cost'] / ($record['estimate_month'] / 12)) . '</td>
                        <td></td>
                        <td>' . number_format($record['book_value']) . '</td>
                    </tr>';
                $no++;
            }

            $html .= '</table></div></body></html>';
            echo $html;
        }
    }
}
