<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_outstanding_pr extends CI_Controller
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
            $this->load->view('purchase/report_outstanding_pr');
        } else {
            redirect('error_access');
        }
    }
    public function readPurchaseRequest()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $item_family_id = $this->input->get("item_family_id");
        $item_id = $this->input->get("item_id");
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $sales_orders = $this->crud->query("SELECT a.request_no
        FROM purchase_requests a
        JOIN items b ON a.item_id = b.id
        WHERE a.request_no like '%$post%'
        AND a.request_date between '$filter_from' and '$filter_to'
        AND b.item_family_id = '$item_family_id' 
        AND a.item_id = '$item_id'
        GROUP BY a.request_no 
        ORDER BY a.request_no DESC");
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
        $filter_item_family = $this->input->get("filter_item_family");
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_purchase_request = base64_decode($this->input->get("filter_purchase_request"));
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, c.name as user_name');
        $this->db->from('purchase_requests a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('users c', 'a.created_by = c.username');
        $this->db->join('item_familys d', 'b.item_family_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->where("a.request_date between '$filter_from' and '$filter_to'");
        $this->db->like('d.id', $filter_item_family);
        $this->db->like('a.item_id', $filter_product_no);
        $this->db->like('a.request_no', $filter_purchase_request);
        $this->db->order_by('a.status', 'ASC');
        $this->db->group_by('a.request_no');
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
                                <small>REPORT OUTSTANDING PURCHASE REQUEST</small><br>
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
                <th>Purchase Request No</th>
                <th>Purchase Request Date</th>
                <th>Created By</th>
                <th>Created Date</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $request_no = $data['request_no'];
            $this->db->select('a.*, c.po_no, b.number as item_number, b.name as item_name, b.description as item_description, d.name as uom');
            $this->db->from('purchase_requests a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('purchase_orders c', 'a.request_no = c.request_no', 'left');
            $this->db->join('uom d', 'b.uom_id = d.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.request_no', $request_no);
            $this->db->like('a.item_id', $filter_product_no);
            $this->db->order_by('b.number', 'ASC');
            $this->db->group_by('a.item_id');
            $details = $this->db->get()->result_array();
            if ($data['status'] == 1) {
                $status = "<b style='color:green;'>CONVERTED</b>";
            } else {
                $status = "<b style='color:red;'>UNCONVERTED</b>";
            }
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['request_no'] . '</td>
                            <td>' . $data['request_date'] . '</td>
                            <td>' . $data['user_name'] . '</td>
                            <td>' . $data['created_date'] . '</td>
                            <td>' . $status . '</td>
                        </tr>';
            $no++;
            if ($filter_display == "DETAIL") {
                if ($details) {
                    $html .= '  <tr>
                                    <td colspan="10" style="background:green;color:white;"><b>DETAIL OF ' . $data['request_no'] . '</b></td>
                                </tr>';
                    $html .= '  <tr>
                                    <th width="20"></th>
                                    <th>Component No</th>
                                    <th width="600">Specification</th>
                                    <th>UoM</th>
                                    <th>Quantity</th>
                                    <th>PO No</th>
                                </tr>';
                    foreach ($details as $detail) {
                        $html .= '  <tr>
                                        <td></td>
                                        <td>' . $detail['item_number'] . '</td>
                                        <td>' . $detail['item_description'] . '</td>
                                        <td>' . $detail['uom'] . '</td>
                                        <td style="text-align:right">' . number_format($detail['qty'], 2) . '</td>
                                        <td>' . @$detail['po_no'] . '</td>
                                    </tr>';
                    }
                } else {
                    $html .= '  <tr>
                                    <td colspan="10" style="background:red;color:white;"><b>DETAIL OF ' . $data['po_no'] . ' NOT FOUND</b></td>
                                </tr>';
                }
            }
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
