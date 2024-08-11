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
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[50]');
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

    public function readPurchaseOrders()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $sales_orders = $this->crud->query("SELECT `po_no`
        FROM purchase_orders
        WHERE `po_no` like '%$post%'
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
        JOIN item_rm b on a.item_rm_id = b.id
        WHERE `po_no` like '%$post%'
        AND po_date between '$filter_from' and '$filter_to'
        AND supplier_id = '$supplier_id'
        AND po_no = '$po_no'
        GROUP BY a.item_rm_id");
        echo json_encode($sales_orders);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_outstanding_po_$format.xls");
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

        $this->db->select('a.*, SUM(a.qty) as qty_po, b.number as supplier_number, b.name as supplier_name, c.uom, c.number as item_number, c.name as item_name, a.status, h.total_status_complete,');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');       
        $this->db->join('(SELECT po_no, COUNT(status) as total_status_complete FROM purchase_orders WHERE status = 2 GROUP BY po_no) h', 'a.po_no = h.po_no', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where("a.po_date between '$filter_from' and '$filter_to'");
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.item_rm_id', $filter_product_no);
        $this->db->like('a.po_no', $filter_purchase_order);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.status', 'ASC');
        $this->db->group_by('a.po_no');
        $records = $this->db->get()->result_array();

        //RECAP
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
                <h3 style="margin:0;">OUTSTANDING PURCHASE ORDER</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>

            <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th colspan="3">Purchase Order No</th>
                <th>Purchase Order Date</th>
                <th>Supplier No</th>
                <th>Supplier Name</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Receipt</th>
                <th>Outstanding</th>
                <th colspan="2">Status</th>
            </tr>';

        $no = 1;

        foreach ($records as $data) {
            $po_no = $data['po_no'];
            $item_rm_id = $data['item_rm_id'];
            $supplier = $data['supplier_id'];
            // var_dump($data);
            if($filter_product_no != "" && $filter_purchase_order != ""){
                $receipt = $this->crud->query("SELECT a.po_no, SUM(b.qty_receipt) as qty_receipt 
                from purchase_orders a 
                JOIN (SELECT SUM(qty_receipt) as qty_receipt, po_no, item_rm_id 
                FROM purchase_order_receipts 
                GROUP BY po_no, item_rm_id) b ON a.po_no = b.po_no and a.item_rm_id = b.item_rm_id 
                WHERE a.po_no = '$po_no' and a.item_rm_id = '$item_rm_id' and a.status like '%$filter_status%'");
            }elseif($filter_purchase_order != ""){
                $receipt = $this->crud->query("SELECT a.po_no, SUM(b.qty_receipt) as qty_receipt 
                from purchase_orders a 
                JOIN (SELECT SUM(qty_receipt) as qty_receipt, po_no, item_rm_id 
                FROM purchase_order_receipts 
                GROUP BY po_no, item_rm_id) b ON a.po_no = b.po_no and a.item_rm_id = b.item_rm_id 
                WHERE a.po_no = '$po_no' and a.status like '%$filter_status%'");
            }elseif($filter_product_no != ""){
                $receipt = $this->crud->query("SELECT a.po_no, SUM(b.qty_receipt) as qty_receipt 
                from purchase_orders a 
                JOIN (SELECT SUM(qty_receipt) as qty_receipt, po_no, item_rm_id 
                FROM purchase_order_receipts 
                GROUP BY po_no, item_rm_id) b ON a.po_no = b.po_no and a.item_rm_id = b.item_rm_id 
                WHERE a.po_no = '$po_no' and a.item_rm_id = '$item_rm_id' and a.status like '%$filter_status%'");
            }else{
                $receipt = $this->crud->query("SELECT a.po_no, SUM(b.qty_receipt) as qty_receipt 
                from purchase_orders a 
                JOIN (SELECT SUM(qty_receipt) as qty_receipt, po_no, item_rm_id 
                FROM purchase_order_receipts 
                GROUP BY po_no, item_rm_id) b ON a.po_no = b.po_no and a.item_rm_id = b.item_rm_id 
                WHERE a.po_no = '$po_no' and a.status like '%$filter_status%'");
            }
            // $receipt = $this->crud->query("SELECT a.po_no, SUM(b.qty_receipt) as qty_receipt from purchase_orders a JOIN (SELECT SUM(qty_receipt) as qty_receipt, po_no, item_rm_id FROM purchase_order_receipts GROUP BY po_no, item_rm_id) b ON a.po_no = b.po_no and a.item_rm_id = b.item_rm_id WHERE (a.po_no = '$po_no' OR '$po_no' IS NULL OR '$po_no' = '') and (a.item_rm_id = '$item_rm_id' OR '$item_rm_id' IS NULL OR '$item_rm_id' = '') and a.status like '%$filter_status%'");
            $os_qty = $data['qty_po'] - @$receipt[0]->qty_receipt;

            if ($data['status'] == 2) {
                $status = "<b style='color:blue;'>COMPLETE</b>";
                $data['qty_po'] = 0;
                $os_qty = 0;
            }elseif ($data['total_status_complete'] >= 1 ) {
                $status = "<b style='color:blue;'>COMPLETE</b>";
                $data['qty_po'] = 0;
                $os_qty = 0;
            } elseif (($data['qty_po'] - @$receipt[0]->qty_receipt) > 0) {
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
                <td style="text-align:right">' . $data['uom'] . '</td>
                <td style="text-align:right">' . number_format(@$receipt[0]->qty_receipt, 2) . '</td>
                <td style="text-align:right">' . number_format($os_qty, 2) . '</td>
                <td colspan="2">' . $status . '</td>
            </tr>';
            $no++;

        
            // DETAIL
            if ($filter_display == "DETAIL") {
                $this->db->select('a.*, SUM(a.qty) as qty_po, d.qty_receipt, b.number as supplier_number, b.name as supplier_name, c.uom, c.number as item_number, c.name as item_name, a.status');
                $this->db->from('purchase_orders a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                $this->db->join('(SELECT po_no, item_rm_id, SUM(qty_receipt) as qty_receipt FROM purchase_order_receipts GROUP BY po_no, item_rm_id) d', 'a.po_no = d.po_no AND a.item_rm_id = d.item_rm_id', 'left');        
                $this->db->where('a.deleted', 0);
                $this->db->where("a.po_date between '$filter_from' and '$filter_to'");
                $this->db->where('a.po_no', $po_no);
                $this->db->like('a.supplier_id', $filter_supplier);
                $this->db->like('a.item_rm_id', $filter_product_no);
                $this->db->like('a.status', $filter_status);
                $this->db->order_by('a.status', 'ASC');
                $this->db->group_by('a.item_rm_id');
                $details = $this->db->get()->result_array();

                if ($details) {
                    $html .= '  <tr>
                        <td colspan="13" style="background:orange;"><b>DETAIL OF ' . $data['po_no'] . '</b></td>
                    </tr>';

                    $html .= '  <tr>
                                    <th width="20"></th>
                                    <th>Part No</th>
                                    <th>Part Name</th>
                                    <th>PO Qty</th>
                                    <th>Receipt Qty</th>
                                    <th>OS Qty</th>
                                </tr>';

                    foreach ($details as $detail) {
                        $item_rm_id = $detail['item_rm_id'];
                        $html .= '  <tr>
                                        <td></td>
                                        <td>' . $detail['item_number'] . '</td>
                                        <td>' . $detail['item_name'] . '</td>
                                        <td style="text-align:right">' . number_format($detail['qty_po'], 2) . '</td>
                                        <td style="text-align:right">' . number_format($detail['qty_receipt'], 2) . '</td>
                                        <td style="text-align:right">' . number_format(($detail['qty_po'] - $detail['qty_receipt']), 2) . '</td>
                                    </tr>';
                        
                        $html .= '  <tr>
                                        <td colspan="13" style="background:#D1FFC6;"><b>DETAIL OF ' . $detail['item_number'] . '</b></td>
                                    </tr>';
    
                        $html .= '  <tr>
                                        <th width="20"></th>
                                        <th>Custom No</th>
                                        <th>Custom Doc No</th>
                                        <th>Custom Date</th>
                                        <th>Receipt No</th>
                                        <th>Receipt Date</th>
                                        <th>PO Qty</th>
                                        <th>Receipt Qty</th>
                                        <th>OS Qty</th>
                                        <th>Receipt By</th>
                                    </tr>';

                        $os_qty = $detail['qty_po'];
                        $details2 = $this->crud->reads("purchase_order_receipts", [], ["item_rm_id" => $item_rm_id, "po_no" => $data['po_no']]);
                        foreach ($details2 as $detail2) {
                            $os_qty -= $detail2->qty_receipt;

                            $html .= '  <tr>
                                            <td></td>
                                            <td>' . $detail2->bc_kind . '</td>
                                            <td>' . $detail2->bc_document . '</td>
                                            <td>' . $detail2->bc_date . '</td>
                                            <td>' . $detail2->receipt_no . '</td>
                                            <td>' . $detail2->receipt_date . '</td>
                                            <td style="text-align:right">' . number_format($detail2->qty_po, 2) . '</td>
                                            <td style="text-align:right">' . number_format($detail2->qty_receipt, 2) . '</td>
                                            <td style="text-align:right">' . number_format($os_qty, 2) . '</td>
                                            <td>' . $detail2->created_by . '</td>
                                        </tr>';   
                        }

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

