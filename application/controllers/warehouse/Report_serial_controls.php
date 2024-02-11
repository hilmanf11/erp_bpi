<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_serial_controls extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
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
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/report_serial_controls');
        } else {
            redirect('error_access');
        }
    }

    public function readDeliveryOrder()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $customer_id = $this->input->get("customer_id");
        $item_fg_id = $this->input->get("item_fg_id");

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $sales_orders = $this->crud->query("SELECT `number`
        FROM delivery_orders
        WHERE customer_id = '$customer_id' 
        AND item_fg_id = '$item_fg_id'
        GROUP BY `number` 
        ORDER BY `number` DESC");
        
        echo json_encode($sales_orders);
    }

    public function readDeliveryNote()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $do_number = base64_decode($this->input->get("do_number"));

        $post = isset($_POST['q']) ? $_POST['q'] : "";

        $sales_orders = $this->crud->query("SELECT `number`
        FROM delivery_notes
        WHERE do_number = '$do_number'
        GROUP BY `number` 
        ORDER BY `number` DESC");
        
        echo json_encode($sales_orders);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_serial_controls_$format.xls");
        }

        $filter_customer = $this->input->get("filter_customer");
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_do_no = base64_decode($this->input->get("filter_do_no"));
        $filter_dn_no = base64_decode($this->input->get("filter_dn_no"));
        $filter_serial_no = base64_decode($this->input->get("filter_serial_no"));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.delivery_order_no, h.delivery_note_no, b.name as customer_name, c.number as item_no, c.name as item_name, a.delivery_order_date, a.sales_order_no, f.checksheet_label, f.qty');
        $this->db->from('delivery_orders a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join('delivery_notes h', 'a.delivery_order_no = h.delivery_order_no', 'left');
        $this->db->join('shipping_orders f', 'a.delivery_order_no = f.delivery_order_no and a.sales_order_no = f.sales_order_no', 'left');
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->like('a.item_fg_id', $filter_product_no);
        $this->db->like('a.delivery_order_no ', $filter_do_no);
        if($filter_dn_no != ""){
            $this->db->like('h.delivery_note_no', $filter_dn_no);
        }
        if($filter_serial_no != ""){
            $this->db->like('f.checksheet_label', $filter_serial_no);
        }
        $this->db->order_by('a.delivery_order_no', 'ASC');
        $this->db->order_by('f.checksheet_label', 'ASC');
        $this->db->group_by('f.checksheet_label');
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
                                <small>'.$config->description.'</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <h3 style="margin:0;">CHECK SERIAL NO (FG)</h3>
            </center>
            <br>
            
            <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Delivery Note</th>
                <th>Delivery Order</th>
                <th>Trans Date</th>
                <th>Sales Order</th>
                <th>Customer</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Serial No</th>
                <th>Qty</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['delivery_note_no'] . '</td>
                            <td>' . $data['delivery_order_no'] . '</td>
                            <td>' . $data['delivery_order_date'] . '</td>
                            <td>' . $data['sales_order_no'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['item_no'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['checksheet_label'] . '</td>
                            <td>' . number_format($data['qty'], 2) . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
