<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_check_serialno_sto_rm extends CI_Controller
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
            $this->load->view('warehouse/report_check_serialno_sto_rm');
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
            header("Content-Disposition: attachment; filename=check_serial_no_sto_rm_$format.xls");
        }
        
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_label_no = base64_decode($this->input->get("filter_label_no"));
        $filter_division = base64_decode($this->input->get("filter_division"));
        $filter_item_category = base64_decode($this->input->get("filter_item_category"));
        $filter_item_family = base64_decode($this->input->get("filter_item_family"));
        $filter_items = base64_decode($this->input->get("filter_items"));

        
        //Details
        $this->db->select('a.label_no, b.number as item_number, b.name as item_name, a.qty, a.division, c.name as prodfam_name, 
        d.name as category_name, a.uom, a.created_by, a.created_date');
        $this->db->from('sto_raw_materials a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'a.product_family_id = c.id');
        $this->db->join('item_categories d', 'a.category_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->where("a.created_date between '$filter_from' and '$filter_to'");
        $this->db->like('a.item_rm_id', $filter_items);
        $this->db->like('a.label_no', $filter_label_no);
        $this->db->like('a.division', $filter_division);
        $this->db->like('a.category_id', $filter_item_category);
        $this->db->like('a.product_family_id', $filter_item_family);
        
        $this->db->order_by('a.label_no', 'ASC');
        $this->db->order_by('b.name', 'ASC');
        $records = $this->db->get()->result_array();
        
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
                <h3 style="margin:0;">CHECK SERIAL NO STO (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Label No</th>
                <th>Part No</th>
                <th>Part Name</th>
                <th>Division</th>
                <th>Category</th>
                <th>Product Family</th>
                <th>Uom</th>
                <th>Quantity</th>
                <th>Created By</th>
                <th>Created Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
           
            $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $data['label_no'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['item_number'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['item_name'] . '</td>
                                <td>' . $data['division'] . '</td>
                                <td>' . $data['category_name'] . '</td>
                                <td>' . $data['prodfam_name'] . '</td>
                                <td>' . $data['uom'] . '</td>
                                <td style="text-align:right">' . number_format($data['qty'], 2) . '</td>
                                <td>' . $data['created_by'] . '</td>
                                <td>' . $data['created_date'] . '</td>
                            </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
