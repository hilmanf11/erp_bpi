<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_outstanding_po extends CI_Controller
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
            $this->load->view('purchase/report_outstanding_po');
        } else {
            redirect('error_access');
        }
    }

    public function readPurchaseOrder()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $supplier_id = $this->input->get("supplier_id");
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $sales_orders = $this->crud->query("SELECT `po_no`
        FROM purchase_orders
        WHERE `po_no` like '%$post%'
        AND po_date between '$filter_from' and '$filter_to'
        AND supplier_id = '$supplier_id'
        GROUP BY `po_no` 
        ORDER BY `po_no` DESC");
        echo json_encode($sales_orders);
    }

    public function readPurchaseOrderItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $supplier_id = $this->input->get("supplier_id");
        $po_no = $this->input->get("po_no");
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $sales_orders = $this->crud->query("SELECT b.id, b.number, b.name
        FROM purchase_orders a
        JOIN items b on a.item_id = b.id
        WHERE `po_no` like '%$post%'
        AND po_date between '$filter_from' and '$filter_to'
        AND supplier_id = '$supplier_id'
        AND po_no = '$po_no'
        GROUP BY a.item_id");
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
        $filter_supplier = $this->input->get("filter_supplier");
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_status = $this->input->get("filter_status");
        $filter_purchase_order = base64_decode($this->input->get("filter_purchase_order"));
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, SUM(a.qty) as qty_po, d.qty_receipt, b.number as supplier_number, b.name as supplier_name');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join('(SELECT po_no, SUM(qty_receipt) as qty_receipt FROM purchase_order_receipts GROUP BY po_no) d', 'a.po_no = d.po_no', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where("a.po_date between '$filter_from' and '$filter_to'");
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.item_id', $filter_product_no);
        $this->db->like('a.po_no', $filter_purchase_order);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.status', 'ASC');
        $this->db->group_by('a.po_no');
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
                                <small>REPORT OUTSTANDING PURCHASE ORDER</small><br>
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
                <th colspan="3">Purchase Order No</th>
                <th>Purchase Order Date</th>
                <th>Supplier No</th>
                <th>Supplier Name</th>
                <th>Quantity</th>
                <th>Receipt</th>
                <th>Outstanding</th>
                <th colspan="2">Status</th>
            </tr>';

        $no = 1;
        foreach ($records as $data) {
            $po_no = $data['po_no'];
            $supplier_id = $data['supplier_id'];
            $this->db->select('a.*, b.number as item_number, b.name as item_name, c.qty');
            $this->db->from('purchase_orders c');
            $this->db->join('items b', 'c.item_id = b.id');
            $this->db->join('purchase_order_receipts a', 'a.po_no = c.po_no and a.item_id = c.item_id and a.supplier_id = c.supplier_id', 'left');
            $this->db->where('c.deleted', 0);
            $this->db->where('c.po_no', $po_no);
            $this->db->where('c.supplier_id', $supplier_id);
            $this->db->like('c.item_id', $filter_product_no);
            $this->db->order_by('b.number', 'ASC');
            $details = $this->db->get()->result_array();

            if (($data['qty_po'] - $data['qty_receipt']) > 0) {
                $status = "<b style='color:green;'>OPEN</b>";
            } else {
                $status = "<b style='color:red;'>CLOSE</b>";
            }

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3">' . $data['po_no'] . '</td>
                            <td>' . $data['po_date'] . '</td>
                            <td>' . $data['supplier_number'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td style="text-align:right">' . number_format($data['qty_po'], 2) . '</td>
                            <td style="text-align:right">' . number_format($data['qty_receipt'], 2) . '</td>
                            <td style="text-align:right">' . number_format($data['qty_po'] - $data['qty_receipt'], 2) . '</td>
                            <td colspan="2">' . $status . '</td>
                        </tr>';
            $no++;
            if ($filter_display == "DETAIL") {
                if ($details) {
                    $html .= '  <tr>
                                    <td colspan="13" style="background:#D1FFC6;"><b>DETAIL OF ' . $data['po_no'] . '</b></td>
                                </tr>';
                    $html .= '  <tr>
                                    <th width="20"></th>
                                    <th>Custom No</th>
                                    <th>Custom Doc No</th>
                                    <th>Custom Date</th>
                                    <th>Component No</th>
                                    <th>Component Name</th>
                                    <th>Receipt No</th>
                                    <th>Receipt Date</th>
                                    <th>PO Qty</th>
                                    <th>Receipt Qty</th>
                                    <th>OS Qty</th>
                                    <th>Receipt By</th>
                                </tr>';
                    foreach ($details as $detail) {
                        $html .= '  <tr>
                                        <td></td>
                                        <td>' . $detail['bc_kind'] . '</td>
                                        <td>' . $detail['bc_document'] . '</td>
                                        <td>' . $detail['bc_date'] . '</td>
                                        <td>' . $detail['item_number'] . '</td>
                                        <td>' . $detail['item_name'] . '</td>
                                        <td>' . $detail['receipt_no'] . '</td>
                                        <td>' . $detail['receipt_date'] . '</td>
                                        <td style="text-align:right">' . number_format($detail['qty'], 2) . '</td>
                                        <td style="text-align:right">' . number_format($detail['qty_receipt'], 2) . '</td>
                                        <td style="text-align:right">' . number_format($detail['qty'] - $detail['qty_receipt'], 2)  . '</td>
                                        <td >' . $detail['created_by'] . '</td>
                                    </tr>';
                    }
                } else {
                    $html .= '  <tr>
                                    <td colspan="13" style="background:#FFC6C6;"><b>DETAIL OF ' . $data['po_no'] . '</b></td>
                                </tr>';
                }
            }
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
