<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_outstanding_so extends CI_Controller
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
        // $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[50]');
        // $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/report_outstanding_so');
        } else {
            redirect('error_access');
        }
    }

    public function readCustomerOrder()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $customer_id = $this->input->get("customer_id");
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $customer_orders = $this->crud->query("SELECT `customer_order_no`
        FROM sales_orders
        WHERE `customer_order_no` like '%$post%'
        AND sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'
        AND customer_id = '$customer_id'
        GROUP BY `customer_order_no` 
        ORDER BY `customer_order_no` DESC");
        echo json_encode($customer_orders);
    }

    public function readSalesOrder()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $customer_id = $this->input->get("customer_id");
        $customer_order_no = $this->input->get("customer_order_no");
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $sales_orders = $this->crud->query("SELECT sales_order_no
        FROM sales_orders
        WHERE `customer_order_no` like '%$post%'
        AND sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'
        AND customer_id = '$customer_id'
        AND customer_order_no = '$customer_order_no'
        GROUP BY sales_order_no");
        echo json_encode($sales_orders);
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
    // public function readPeriodLists()
    // {
    //     $p_month = $this->input->post('p_month');
    //     $p_year = $this->input->post('p_year');
    //     $p_date_start = date("Y-m-d", strtotime($p_year . "-" . $p_month . "-01"));
    //     $p_date_to = date('Y-m-d', strtotime('+11 month', strtotime($p_date_start)));

    //     while (strtotime($p_date_start) <= strtotime($p_date_to)) {
    //         $dates[] = array(
    //             "name" => date("M-y", strtotime($p_date_start))
    //         );

    //         $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
    //     }

    //     echo json_encode($dates);
    // }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_outstanding_so_$format.xls");
        }

        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $filter_customer_name = $this->input->get("filter_customer_name");
        $filter_customer_order_no = $this->input->get("filter_customer_order_no");
        $filter_sales_order_no = $this->input->get("filter_sales_order_no");
        $filter_item_fg = $this->input->get("filter_item_fg");
        $filter_division = $this->input->get("filter_division");
        $filter_display = $this->input->get("filter_display");

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, SUM(a.qty) as qty_order, SUM(a.delivery) as qty_delivery, SUM(a.outstanding) as qty_outstanding, b.number as customer_number, b.name as customer_name, c.number as item_fg_number, c.name as item_fg_name');
        $this->db->from('sales_orders a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->where("a.sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'");
        $this->db->like('a.customer_id', $filter_customer_name);
        $this->db->like('a.item_fg_id', $filter_item_fg);
        $this->db->like('a.customer_order_no', $filter_customer_order_no);
        $this->db->like('a.sales_order_no', $filter_sales_order_no);
        $this->db->like('a.division', $filter_division);
        $this->db->order_by('a.status', 'ASC');
        $this->db->group_by('a.customer_order_no');
        $records = $this->db->get()->result_array();

        if ($filter_customer_name == "" && $filter_item_fg == "" && $filter_display == "RECAP"){
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
                    <h3>REPORT OUTSTANDING SALES ORDER</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PERIOD</small><br>
                                <small>CUSTOMER NAME</small><br>
                                <small>SALES ORDER NO.</small><br>
                                <small>CUSTOMER ORDER NO.</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small><br>
                                <small>: </small><br>
                                <small>: </small><br>
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $filter_so_date_from . '</b> To <b>' . $filter_so_date_to . '</b></small><br>
                                <small><b>ALL</b></small><br>
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
                <th>Sales Order No.</th>
                <th>Customer Order No.</th>
                <th>SO Date</th>
                <th>Customer Name</th>
                <th>Qty Order</th>
                <th>Qty Delivery</th>
                <th>Outstanding</th>
                <th>Status</th>
            </tr>';

            $no = 1;
            foreach ($records as $data) {

                if (($data['qty_order'] - $data['qty_delivery']) > 0) {
                    $status = "<b style='color:green;'>OPEN</b>";
                } else {
                    $status = "<b style='color:red;'>CLOSE</b>";
                }

                $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['sales_order_no'] . '</td>
                        <td>' . $data['customer_order_no'] . '</td>
                        <td>' . $data['sales_order_date'] . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td style="text-align:right">' . number_format($data['qty_order'], 2) . '</td>
                        <td style="text-align:right">' . number_format($data['qty_delivery'], 2) . '</td>
                        <td style="text-align:right">' . number_format($data['qty_order'] - $data['qty_delivery'], 2) . '</td>
                        <td>' . $status . '</td>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;      
        }
        if ($filter_customer_name == "" && $filter_item_fg == "" && $filter_display == "DETAIL BY PRODUCT NO.") {
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
                        <h3>REPORT OUTSTANDING BY PRODUCT</h3>
                    </div>
                    <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small>PERIOD</small><br>
                                    <small>PRODUCT NO.</small><br>
                                    <small>CUSTOMER NAME</small><br>
                                    <small>SALES ORDER NO.</small><br>
                                    <small>CUSTOMER ORDER NO.</small>
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small>: </small><br>
                                    <small>: </small><br>
                                    <small>: </small><br>
                                    <small>: </small><br>
                                    <small>: </small>
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small><b>' . $filter_so_date_from . '</b> To <b>' . $filter_so_date_to . '</b></small><br>
                                    <small><b>ALL</b></small><br>
                                    <small><b>ALL</b></small><br>
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
                    <th>Customer Order No.</th>
                    <th>Sales Order No.</th>
                    <th>SO Date</th>
                    <th>Customer Name</th>
                    <th>Qty Order</th>
                    <th>Qty Delivery</th>
                    <th>Outstanding</th>
                </tr>';

                $no = 1;
                foreach ($records as $detail) {
                    $so_no = $detail['sales_order_no'];
                    $customer_id = $detail['customer_id'];
                    $item_fg_id = $detail['item_fg_id'];
                    $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
                    $this->db->from('sales_orders a');
                    $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                    $this->db->join('customers c', 'a.customer_id = c.id');
                    $this->db->where('a.deleted', 0);
                    $this->db->where('a.sales_order_no', $so_no);
                    $this->db->where('a.customer_id', $customer_id);
                    $this->db->like('a.item_fg_id', $filter_item_fg);
                    $this->db->order_by('b.number', 'ASC');
                    $this->db->group_by('a.sales_order_no');
                    $details = $this->db->get()->result_array();

                    $html .= '<tr>
                                <td rowspan="2">' . $no . '</td>
                                <td rowspan="2">' . $detail['item_fg_number'] . '</td>
                                <td rowspan="2">' . $detail['item_fg_name'] . '</td>';
                            $no++;
                    $html .= '<tr>
                            <td>' . $detail['customer_order_no'] . '</td>
                            <td>' . $detail['sales_order_no'] . '</td>
                            <td>' . $detail['sales_order_date'] . '</td>
                            <td>' . $detail['customer_name'] . '</td>
                            <td style="text-align:right">' . number_format($detail['qty'], 2) . '</td>
                            <td style="text-align:right">' . number_format($detail['delivery'], 2) . '</td>
                            <td style="text-align:right">' . number_format($detail['qty'] - $detail['delivery'], 2) . '</td>';     
                }
                $html .= '</table></body></html>';
            echo $html;
        }
    }
}
