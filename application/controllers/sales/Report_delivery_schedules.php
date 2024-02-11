<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_delivery_schedules extends CI_Controller
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
            $this->load->view('sales/report_delivery_schedules');
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
            FROM sales_orders
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
            FROM sales_orders a
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
        $filter_item_fg_name = base64_decode($this->input->get("filter_item_fg_name"));
        $filter_display = base64_decode($this->input->get("filter_display"));

        $customer = $this->crud->read("customers", [], ["id" => $filter_customer_name]);

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
                        <h3>' . $filter_display . ' REPORT DELIVERY SCHEDULES</h3>
                    </div>
                </center>
                <table style="width: 40%; font-size:12px;">
                    <tr>
                        <th style="width:100px; text-align:left;">Period</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $filter_so_date_from . ' To ' . $filter_so_date_to . '</td>
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
                    <tr>
                        <th style="width:100px; text-align:left;">Product No</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $filter_item_fg . '</td>
                    </tr>
                </table>
                <br>';

        if ($filter_display == "RECAP") {
            $this->db->select('a.sales_order_no, a.trans_date, a.qty, b.number as customer_number, b.name as customer_name, c.customer_order_no, d.id as item_fg_id, d.name as item_fg_name, d.number as item_fg_number, d.uom as item_fg_uom');
            $this->db->from('sales_order_deliveries a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('sales_orders c', 'a.customer_id = c.customer_id');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id');
            $this->db->where("a.trans_date between '$filter_so_date_from' and '$filter_so_date_to'");
            $this->db->like('a.customer_id', $filter_customer_name);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->like('a.sales_order_no', $filter_sales_order_no);
            $this->db->order_by('a.item_fg_id', 'DESC');
            $this->db->group_by('a.trans_date');
            $this->db->group_by('a.customer_id');
            $this->db->group_by('a.item_fg_id');
            $this->db->group_by('a.sales_order_no');
            $records = $this->db->get()->result_array();

            $html .= '<table id="customers" border="1">
                        <tr>
                            <th width="20">No</th>
                            <th>Delivery Date</th>
                            <th>Customer Name</th>
                            <th>Sales Order No.</th>
                            <th>Customer Order No.</th>
                            <th>Product ID</th>
                            <th>Product No</th>
                            <th>Product Name</th>
                            <th>UoM</th>
                            <th>Delivery Qty</th>
                        </tr>';

            $no = 1;
            
            foreach ($records as $data) {
            
                $html .= '<tr>
                            <td>' . $no . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['sales_order_no'] . '</td>
                            <td align="center">' . $data['customer_order_no'] . '</td>
                            <td>' . $data['item_fg_id'] . '</td>
                            <td>' . $data['item_fg_number'] . '</td>
                            <td>' . $data['item_fg_name'] . '</td>
                            <td>' . $data['item_fg_uom'] . '</td>
                            <td align="center">' . $data['qty'] . '</td>
                            
                           
                        </tr>';
                $no++;
            }
        } else {
            $this->db->select('a.sales_order_no, a.trans_date, a.qty, b.number as customer_number, b.name as customer_name, c.customer_order_no, d.id as item_fg_id, d.name as item_fg_name, d.number as item_fg_number, d.uom as item_fg_uom');
            $this->db->from('sales_order_deliveries a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('sales_orders c', 'a.customer_id = c.customer_id');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id');
            $this->db->where("a.trans_date between '$filter_so_date_from' and '$filter_so_date_to'");
            $this->db->like('a.customer_id', $filter_customer_name);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->like('c.customer_order_no', $filter_customer_order_no);
            $this->db->like('a.sales_order_no', $filter_sales_order_no);
           $this->db->group_by('a.item_fg_id');
            $this->db->order_by('a.status', 'ASC');
            $records = $this->db->get()->result_array();

            $p_date_start = date("Y-m-d", strtotime($filter_so_date_from));
            $p_date_to = date('Y-m-d', strtotime($filter_so_date_to));

            $colspan = 1;
            while (strtotime($p_date_start) <= strtotime($p_date_to)) {
                $colspan += 1;
                $p_date_start = date("Y-m-d", strtotime("+1 days", strtotime($p_date_start)));
            }

            $p_date_start = date("Y-m-d", strtotime($filter_so_date_from));
            $p_date_to = date('Y-m-d', strtotime($filter_so_date_to));

    $html .= '<table id="customers" border="1">
        <tr>
            <th rowspan="2" width="20">No</th>
            <th rowspan="2">Product ID</th>
            <th rowspan="2">Product No</th>
            <th rowspan="2">Product Name</th>
            <th rowspan="2">UoM</th>
            <th rowspan="2">Desc</th>
            <th align="center" colspan="'.$colspan.'">Delivery Date</th>
        </tr>
        <tr>';
        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $start_date = date("d", strtotime($p_date_start));
            $html .='<th>'.$start_date.'</th>';
            $p_date_start = date("Y-m-d", strtotime("+1 days", strtotime($p_date_start)));
        }
        $html .= '</tr>';

        $no = 1;

        foreach ($records as $data) {
            $p_date_start = date("Y-m-d", strtotime($filter_so_date_from));
            $p_date_to = date('Y-m-d', strtotime($filter_so_date_to));

            $html .= '<tr>
                        <td rowspan="4">' . $no . '</td>
                        <td rowspan="4">' . $data['item_fg_id'] . '</td>
                        <td rowspan="4">' . $data['item_fg_number'] . '</td>
                        <td rowspan="4">' . $data['item_fg_name'] . '</td>
                        <td rowspan="4">' . $data['item_fg_uom'] . '</td>
                    </tr>
            <tr>
                <td>Plan</td>';
                while (strtotime($p_date_start) <= strtotime($p_date_to)) {
                    $trans_date = date("Y-m-d", strtotime($p_date_start));
                    $delivery = $this->crud->read('sales_order_deliveries', [], ["trans_date" => $trans_date,"item_fg_id" => $data['item_fg_id']]);

                    if(@$delivery->qty > 0){
                        $html .='<td>'.@$delivery->qty.'</td>';
                    }else{
                        $html.='<td>0</td>';
                    }
                    
                    $p_date_start = date("Y-m-d", strtotime("+1 days", strtotime($p_date_start)));
                }

            $p_date_start = date("Y-m-d", strtotime($filter_so_date_from));
            $p_date_to = date('Y-m-d', strtotime($filter_so_date_to));
            
            $html .= '</tr>

            <tr>
                <td>Actual</td>';
                while (strtotime($p_date_start) <= strtotime($p_date_to)) {
                    $trans_date = date("Y-m-d", strtotime($p_date_start));
                        $html.='<td>0</td>';
                    
                    $p_date_start = date("Y-m-d", strtotime("+1 days", strtotime($p_date_start)));
                }
            $html .= '</tr>
            
            <tr>
                <td>Balance</td>';
                while (strtotime($p_date_start) <= strtotime($p_date_to)) {
                    $trans_date = date("Y-m-d", strtotime($p_date_start));
                        $html.='<td>0</td>';
                    
                    $p_date_start = date("Y-m-d", strtotime("+1 days", strtotime($p_date_start)));
                }
            $html .= '</tr>

            $no++';
        }



    $html .= '</table>';
     
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
