<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_outstanding_so_rm extends CI_Controller
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
            $this->load->view('sales/report_outstanding_so_rm');
        } else {
            redirect('error_access');
        }
    }

    public function readCustomerOrder()
    {
        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $customer_id = $this->input->get("customer_id");

        $customer_orders = $this->crud->query("SELECT customer_order_no, sales_order_no
            FROM sales_order_rm
            WHERE sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'
            AND customer_id = '$customer_id'
            GROUP BY sales_order_no
            ORDER BY sales_order_no ASC");
        echo json_encode($customer_orders);
    }

    public function readItems()
    {
        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $filter_sales_order_no = base64_decode($this->input->get("filter_sales_order_no"));
        $customer_id = $this->input->get("customer_id");

        $customer_orders = $this->crud->query("SELECT b.id, b.number, b.name
            FROM sales_order_rm a
            JOIN item_fg b ON a.item_fg_id = b.id
            WHERE a.sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'
            AND a.customer_id = '$customer_id' AND a.sales_order_no = '$filter_sales_order_no'
            GROUP BY a.item_fg_id");
        echo json_encode($customer_orders);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_outstanding_so_$format.xls");
        }

        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $filter_customer_name = base64_decode($this->input->get("filter_customer_name"));
        $filter_customer_order_no = base64_decode($this->input->get("filter_customer_order_no"));
        $filter_sales_order_no = base64_decode($this->input->get("filter_sales_order_no"));
        $filter_item_fg = base64_decode($this->input->get("filter_item_fg"));
        $filter_division = base64_decode($this->input->get("filter_division"));
        $filter_display = base64_decode($this->input->get("filter_display"));

        $customer = $this->crud->read("customers", [], ["id" => $filter_customer_name]);
        $item_fg = $this->crud->read("item_fg", [], ["id" => $filter_item_fg]);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                        Print Date ' . date("d M Y H:m:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>
                    <br><br>
                    <div style="float: centet; font-size: 16px; text-align: center;">
                        <h3>' . $filter_display . ' OUTSTANDING SALES ORDER <br>RAW MATERIAL</h3>
                    </div>
                </center>
                <table style="width: 40%; font-size:12px;">
                    <tr>
                        <th style="width:100px; text-align:left;">Period</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $filter_so_date_from . ' To ' . $filter_so_date_to . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Product No</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . @$item_fg->number . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Customer Name</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . @$customer->name . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Customer Order No</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $filter_customer_order_no . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Sales Order No</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $filter_sales_order_no . '</td>
                    </tr>
                </table>
                <br>';

        if ($filter_display == "RECAP") {
            $this->db->select('a.sales_order_no, a.sales_order_date, a.customer_order_no, SUM(a.qty) as qty_order, SUM(a.delivery) as qty_delivery, SUM(a.outstanding) as qty_outstanding, b.number as customer_number, b.name as customer_name');
            $this->db->from('sales_order_rm a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->where("a.sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'");
            $this->db->like('a.customer_id', $filter_customer_name);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->like('a.customer_order_no', $filter_customer_order_no);
            $this->db->like('a.sales_order_no', $filter_sales_order_no);
            $this->db->like('a.division', $filter_division);
            $this->db->order_by('a.status', 'ASC');
            $this->db->group_by('a.sales_order_no');
            $records = $this->db->get()->result_array();

            $html .= '<table id="customers" border="1">
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
            $qty_order = 0;
            $qty_delivery = 0;
            $qty_outstanding = 0;
            $status = "";
            foreach ($records as $data) {
                $qty_order += $data['qty_order'];
                $qty_delivery += $data['qty_delivery'];
                $qty_outstanding += $data['qty_outstanding'];

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
                            <td>' . number_format($data['qty_order']) . '</td>
                            <td>' . number_format($data['qty_delivery']) . '</td>
                            <td>' . number_format($data['qty_outstanding']) . '</td>
                            <td>' . $status . '</td>
                        </tr>';
                $no++;
            }

            $html .= '<tr>
                        <th colspan="5" style="text-align:right;">TOTAL</th>
                        <th>' . number_format($qty_order) . '</th>
                        <th>' . number_format($qty_delivery) . '</th>
                        <th>' . number_format($qty_outstanding) . '</th>
                        <th>' . $status . '</th>
                    </tr>';
        } else {
            $this->db->select('a.sales_order_no, a.sales_order_date, a.customer_order_no, 
                a.qty, 
                a.delivery, 
                a.outstanding, 
                b.number as customer_number, 
                b.name as customer_name,
                c.number as item_fg_number,
                c.name as item_fg_name');
            $this->db->from('sales_order_rm a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            $this->db->where("a.sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'");
            $this->db->like('a.customer_id', $filter_customer_name);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->like('a.customer_order_no', $filter_customer_order_no);
            $this->db->like('a.sales_order_no', $filter_sales_order_no);
            $this->db->like('a.division', $filter_division);
            $this->db->order_by('a.status', 'ASC');
            $records = $this->db->get()->result_array();

            $html .= '<table id="customers" border="1">
                        <tr>
                            <th width="20">No</th>
                            <th>Product No</th>
                            <th>Product Name</th>
                            <th>Sales Order No</th>
                            <th>Customer Order No</th>
                            <th>SO Date</th>
                            <th>Customer Name</th>
                            <th>Qty Order</th>
                            <th>Qty Delivery</th>
                            <th>Outstanding</th>
                        </tr>';

            $no = 1;
            $qty_order = 0;
            $qty_delivery = 0;
            $qty_outstanding = 0;
            foreach ($records as $data) {
                $qty_order += $data['qty'];
                $qty_delivery += $data['delivery'];
                $qty_outstanding += $data['outstanding'];

                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td>' . $data['item_fg_number'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
                            <td>' . $data['sales_order_no'] . '</td>
                            <td>' . $data['customer_order_no'] . '</td>
                            <td>' . $data['sales_order_date'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . number_format($data['qty']) . '</td>
                            <td>' . number_format($data['delivery']) . '</td>
                            <td>' . number_format($data['outstanding']) . '</td>
                        </tr>';
                $no++;
            }

            $html .= '<tr>
                        <th colspan="7" style="text-align:right;">TOTAL</th>
                        <th>' . number_format($qty_order) . '</th>
                        <th>' . number_format($qty_delivery) . '</th>
                        <th>' . number_format($qty_outstanding) . '</th>
                    </tr>';
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
