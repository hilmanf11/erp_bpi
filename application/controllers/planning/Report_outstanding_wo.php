<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_outstanding_wo extends CI_Controller
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
            $this->load->view('planning/report_outstanding_wo');
        } else {
            redirect('error_access');
        }
    }
    public function readPeriod()
    {
        $send = $this->crud->query("SELECT DISTINCT `period` FROM production_schedules ORDER BY `period` DESC");
        echo json_encode($send);
    }
    public function readWp()
    {
        $period = base64_decode($this->input->get('period'));
        $send = $this->crud->query("SELECT DISTINCT wp, workorder, so_number FROM production_schedules WHERE `period` = '$period' ORDER BY `wp` DESC");
        echo json_encode($send);
    }
    public function readCustomer()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $send = $this->crud->query("SELECT a.customer_id, b.number as customer_number, b.name as customer_name 
            FROM production_schedules a
            JOIN customers b on a.customer_id = b.id
            WHERE a.period = '$period' and a.wp = '$wp' ORDER BY a.workorder DESC");
        echo json_encode($send);
    }
    public function readItems()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $customer_id = $this->input->get('customer_id');
        $send = $this->crud->query("SELECT a.workorder, b.id as item_fg_id, b.number as item_number, b.name as item_name, c.name as customer_name  
            FROM production_schedules a
            JOIN item_fg b on a.item_fg_id = b.id
            JOIN customers c on a.customer_id = c.id
            WHERE a.period = '$period' and a.wp = '$wp' and a.customer_id = '$customer_id' ORDER BY a.workorder DESC");
        echo json_encode($send);
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
        $filter_period = $this->input->get("filter_period");
        $filter_wp = base64_decode($this->input->get("filter_wp"));
        $filter_customer = $this->input->get("filter_customer");
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_status = $this->input->get("filter_status");
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        
        $this->db->select('*');
        $this->db->from('production_schedules');
        $this->db->where('deleted', 0);
        $this->db->where("trans_date between '$filter_from' and '$filter_to'");
        $this->db->like('period', $filter_period);
        $this->db->like('wp', $filter_wp);
        $this->db->like('customer_id', $filter_customer);
        $this->db->like('item_fg_id', $filter_product_no);
        if ($filter_status != "-") {
            $this->db->like('status', $filter_status);
        }
        $this->db->order_by('status', 'ASC');
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
                <h3 style="margin:0;">OUTSTANDING WORK ORDER REPORT</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Sales Order No</th>
                <th>Work Order No</th>
                <th>Work Order Period</th>
                <th>Work Order Date</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Created By</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status'] == 0) {
                $status = "<b style='color:green;'>OPEN</b>";
            } else {
                $status = "<b style='color:red;'>CLOSE</b>";
            }
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['so_number'] . '</td>
                            <td>' . $data['workorder'] . '</td>
                            <td>' . $data['period'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . number_format($data['qty'], 2) . '</td>
                            <td>' . $status . '</td>
                            <td>' . $data['created_by'] . '</td>
                        </tr>';
            $no++;
            // if ($filter_display == "DETAIL") {
            //     if ($details) {
            //         $html .= '  <tr>
            //                         <td colspan="10" style="background:green;color:white;"><b>DETAIL OF ' . $data['po_no'] . '</b></td>
            //                     </tr>';
            //         $html .= '  <tr>
            //                         <th width="20"></th>
            //                         <th>Custom No</th>
            //                         <th>Custom Doc No</th>
            //                         <th>Custom Date</th>
            //                         <th>Component No</th>
            //                         <th>Component Name</th>
            //                         <th>Receipt No</th>
            //                         <th>Receipt Date</th>
            //                         <th>Receipt Qty</th>
            //                         <th>Receipt By</th>
            //                     </tr>';
            //         foreach ($details as $detail) {
            //             $html .= '  <tr>
            //                             <td></td>
            //                             <td>' . $detail['bc_kind'] . '</td>
            //                             <td>' . $detail['bc_document'] . '</td>
            //                             <td>' . $detail['bc_date'] . '</td>
            //                             <td>' . $detail['item_number'] . '</td>
            //                             <td>' . $detail['item_name'] . '</td>
            //                             <td>' . $detail['receipt_no'] . '</td>
            //                             <td>' . $detail['receipt_date'] . '</td>
            //                             <td>' . number_format($detail['qty_receipt']) . '</td>
            //                             <td>' . $detail['created_by'] . '</td>
            //                         </tr>';
            //         }
            //     } else {
            //         $html .= '  <tr>
            //                         <td colspan="10" style="background:red;color:white;"><b>DETAIL OF ' . $data['po_no'] . ' NOT FOUND</b></td>
            //                     </tr>';
            //     }
            // }
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
