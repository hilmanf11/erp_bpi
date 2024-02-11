<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_check_serialno extends CI_Controller
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
            $this->load->view('warehouse/report_check_serialno');
        } else {
            redirect('error_access');
        }
    }
    public function readReceiptNo()
    {
        $supplier = $this->input->get('supplier');
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE supplier_id = '$supplier' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }
    public function readItems()
    {
        $supplier = $this->input->get('supplier');
        $receipt_no = base64_decode($this->input->get('receipt_no'));
        $send = $this->crud->query("SELECT b.id as item_rm_id, b.number as item_number, b.name as item_name
            FROM purchase_order_receipts a
            JOIN item_rm b on a.item_rm_id = b.id
            WHERE a.supplier_id = '$supplier' and a.receipt_no = '$receipt_no' ORDER BY a.receipt_no DESC");
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
        $filter_serial_no = base64_decode($this->input->get("filter_serial_no"));
        $filter_supplier = $this->input->get("filter_supplier");
        $filter_receipt = base64_decode($this->input->get("filter_receipt"));
        $filter_product_no = $this->input->get("filter_product_no");
        $filter_status = $this->input->get("filter_status");
        
        //Details
        $this->db->select('a.*, b.number as item_number, b.name as item_name, c.label_no, c.status as status_label, c.qty');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('purchase_order_labels c', 'a.receipt_id = c.receipt_id');
        $this->db->where('a.deleted', 0);
        $this->db->where("a.receipt_date between '$filter_from' and '$filter_to'");
        $this->db->like('a.receipt_id', $filter_serial_no);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.receipt_no', $filter_receipt);
        $this->db->like('a.item_rm_id', $filter_product_no);
        if ($filter_status != "-") {
            $this->db->like('c.status', $filter_status);
        }
        $this->db->order_by('a.receipt_no', 'ASC');
        $this->db->order_by('c.label_no', 'ASC');
        $records = $this->db->get()->result_array();
        
        $supplier = $this->crud->read("suppliers", [], ["id" => @$records[0]['supplier_id']]);
        
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
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
                <h3 style="margin:0;">CHECK SERIAL NO (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Receipt No</th>
                <th>Receipt Date</th>
                <th>Serial No</th>
                <th>Label No</th>
                <th>Component No</th>
                <th>Component Name</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Created By</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status_label'] == 0) {
                $status = "<b style='color:green;'>OPEN</b>";
            } else {
                $status = "<b style='color:red;'>CLOSE</b>";
            }
            $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $data['receipt_no'] . '</td>
                                <td>' . $data['receipt_date'] . '</td>
                                <td>' . $data['receipt_id'] . '</td>
                                <td>' . $data['label_no'] . '</td>
                                <td>' . $data['item_number'] . '</td>
                                <td>' . $data['item_name'] . '</td>
                                <td style="text-align:right">' . number_format($data['qty'], 2) . '</td>
                                <td>' . $status . '</td>
                                <td>' . $data['created_by'] . '</td>
                            </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
