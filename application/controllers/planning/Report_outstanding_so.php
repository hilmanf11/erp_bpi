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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[50]');
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
    public function readSalesOrders()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $customer_id = $this->input->get("customer_id");
        $item_id = $this->input->get("item_id");
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $sales_orders = $this->crud->query("SELECT `number`
        FROM sales_orders
        WHERE `number` like '%$post%'
        AND trans_date between '$filter_from' and '$filter_to'
        AND customer_id = '$customer_id' 
        AND item_id = '$item_id'
        GROUP BY `number` 
        ORDER BY `number` DESC");
        echo json_encode($sales_orders);
    }
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_orders_$format.xls");
        }
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_display = $this->input->get("filter_display");
        $filter_customer = $this->input->get("filter_customer");
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_sales_order = base64_decode($this->input->get("filter_sales_order"));
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as customer_number, b.name as customer_name, e.delivery');
        $this->db->from('sales_orders a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join('production_schedules d', 'a.number = d.so_number', 'left');
        $this->db->join('delivery_orders e', 'd.workorder = e.workorder and e.status = 1', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where("a.trans_date between '$filter_from' and '$filter_to'");
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->like('a.item_id', $filter_product_no);
        $this->db->like('a.number', $filter_sales_order);
        $this->db->order_by('a.status', 'ASC');
        $this->db->group_by('a.number');
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
                                <small>REPORT OUTSTANDING SALES ORDER</small><br>
                                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br><br>
            
            <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Sales Order No</th>
                <th>Sales Order Date</th>
                <th>Customer No</th>
                <th>Customer Name</th>
                <th>Quantity</th>
                <th>Delivery</th>
                <th>Outstanding</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            // $so_number = $data['number'];
            // $query = $this->crud->query("SELECT a.so_number, a.delivery FROM delivery_orders a JOIN delivery_notes b ON a.number = b.do_number WHERE a.so_number = '$so_number' GROUP BY a.workorder");
            
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['customer_number'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . number_format($data['qty'], 2) . '</td>
                            <td>' . number_format($data['delivery'], 2) . '</td>
                            <td>' . number_format(($data['qty'] - $data['delivery']), 2) . '</td>
                        </tr>';
            $no++;
            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="8" style="background:red;color:white;"><b>DETAIL SALES ORDER OF ' . $data['number'] . ' NOT FOUND</b></td>
                            </tr>';
            }
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
