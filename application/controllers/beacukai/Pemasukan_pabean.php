<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Pemasukan_pabean extends CI_Controller
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
            $this->load->view('beacukai/pemasukan_pabean');
        } else {
            redirect('error_access');
        }
    }
    public function readItemFamily($number = "")
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('deleted', 0);
        if ($number != "001") {
            $this->db->where("number", $number);
        } else {
            $this->db->where("number !=", $number);
        }
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }
    public function readReceiptNo($itemfam)
    {
        $records = $this->crud->query("SELECT a.receipt_no 
            FROM purchase_order_receipts a 
            JOIN items b ON a.item_id = b.id 
            JOIN item_familys c ON b.item_family_id = c.id 
            WHERE c.number = '$itemfam' GROUP BY a.receipt_no ORDER BY a.created_date desc");
        echo json_encode($records);
    }
    public function readAjuNo($itemfam, $receipt_no)
    {
        $receipt_no = base64_decode($receipt_no);
        $records = $this->crud->query("SELECT a.bc_aju 
            FROM purchase_order_receipts a 
            JOIN items b ON a.item_id = b.id 
            JOIN item_familys c ON b.item_family_id = c.id 
            WHERE c.number = '$itemfam' and a.receipt_no = '$receipt_no' GROUP BY a.bc_aju ORDER BY a.created_date desc");
        echo json_encode($records);
    }
    public function readAwbNo($itemfam, $receipt_no)
    {
        $receipt_no = base64_decode($receipt_no);
        $records = $this->crud->query("SELECT a.awb_no 
            FROM purchase_order_receipts a 
            JOIN items b ON a.item_id = b.id 
            JOIN item_familys c ON b.item_family_id = c.id 
            WHERE c.number = '$itemfam' and a.receipt_no = '$receipt_no'  GROUP BY a.awb_no ORDER BY a.created_date desc");
        echo json_encode($records);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_receipt_no = $this->input->get('filter_receipt_no');
        $filter_aju = $this->input->get('filter_aju');
        $filter_awb = $this->input->get('filter_awb');
        $item_family = $this->crud->read("item_familys", [], ["number" => $filter_item_family]);

        $this->db->select('a.*, b.number as item_number, b.name as item_name, c.name as supplier_name, c.currency, e.name as uom, d.price');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('suppliers c', 'a.supplier_id = c.id');
        $this->db->join('supplier_items d', 'a.supplier_id = d.supplier_id and a.item_id = d.item_id');
        $this->db->join('uom e', 'b.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->where("a.receipt_date between '$filter_from' and '$filter_to'");
        $this->db->like('b.item_family_id', @$item_family->id);
        $this->db->like('a.receipt_no', @$filter_receipt_no);
        $this->db->like('a.bc_aju', @$filter_aju);
        $this->db->like('a.awb_no', @$filter_awb);
        $this->db->order_by('a.receipt_no', 'ASC');
        $records = $this->db->get()->result_array();
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
                                <small>LAPORAN PEMASUKAN BAHAN BAKU PER DOKUMEN PABEAN</small><br>
                                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small><br>
                                <small>PRODUCT FAMILY : <b>' . @$item_family->name . '</b></small>
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
                    <th rowspan="2" width="20" style="text-align:center;">No</th>
                    <th colspan="3" style="text-align:center;">Dokumen Pabean</th>
                    <th colspan="2" style="text-align:center;">Dokumen Penerimaan</th>
                    <th rowspan="2" style="text-align:center;">Pemasok</th>
                    <th rowspan="2" style="text-align:center;">Kode Barang</th>
                    <th rowspan="2" style="text-align:center;">Nama Barang</th>
                    <th rowspan="2" style="text-align:center;">Satuan</th>
                    <th rowspan="2" style="text-align:center;">Harga</th>
                    <th colspan="3" style="text-align:center;">Nilai Barang</th>
                </tr>
                <tr>
                    <th style="text-align:center;">Jenis</th>
                    <th style="text-align:center;">Nomor</th>
                    <th style="text-align:center;">Tanggal</th>
                    <th style="text-align:center;">Nomor</th>
                    <th style="text-align:center;">Tanggal</th>
                    <th style="text-align:center;">Mata Uang</th>
                    <th style="text-align:center;">Jumlah</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                                <td>' . $no . '</td>
                                <td>' . $data['bc_kind'] . '</td>
                                <td>' . $data['bc_aju'] . '</td>
                                <td>' . $data['bc_date'] . '</td>
                                <td>' . $data['receipt_no'] . '</td>
                                <td>' . $data['receipt_date'] . '</td>
                                <td>' . $data['supplier_name'] . '</td>
                                <td>' . $data['item_number'] . '</td>
                                <td>' . $data['item_name'] . '</td>
                                <td>' . $data['uom'] . '</td>
                                <td>' . $data['price'] . '</td>
                                <td>' . $data['currency'] . '</td>
                                <td>' . number_format($data['qty_receipt'], "0", ",", ".") . '</td>
                            </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
