<?php
date_default_timezone_set("Asia/Bangkok");
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Pengeluaran_pabean extends CI_Controller
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
            $this->load->view('beacukai/pengeluaran_pabean');
        } else {
            redirect('error_access');
        }
    }

    public function readItemFamily($number = "")
    {
        $this->db->select('*');
        $this->db->from('item_familys');
        $this->db->where('deleted', 0);
        $this->db->where_in("number", ["001","005","007"] );
        $this->db->order_by('name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readDeliveryNote()
    {
        $records = $this->crud->query("SELECT DISTINCT `number` FROM delivery_notes ORDER BY `number` DESC");
        echo json_encode($records);
    }

    public function readBcNo()
    {
        $records = $this->crud->query("SELECT DISTINCT bc_no FROM delivery_notes ORDER BY bc_no ASC");
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
        $filter_delivery_note = $this->input->get('filter_delivery_note');
        $filter_aju = $this->input->get('filter_aju');

        $this->db->select('a.bc_kind, a.bc_no, a.bc_date, a.trans_date, a.number, a.created_date, 
            b.name as customer_name, c.number as item_number, c.name as item_name, d.name as uom,
            e.price, b.currency, a.qty');
        $this->db->from('delivery_notes a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join('uom d', 'c.uom_id = d.id');
        $this->db->join('customer_items e', 'a.customer_id = e.customer_id and a.item_id = e.item_id', 'left');
        $this->db->where("a.trans_date between '$filter_from' and '$filter_to'");
        $this->db->like('a.number', $filter_delivery_note);
        if($filter_aju != ""){
            $this->db->like('a.bc_no', $filter_aju);
        }
        $this->db->group_by('a.id');
        $records = $this->db->get()->result_array();

        $item_family = $this->crud->read("item_familys", [], ["number" => $filter_item_family]);
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
                                <small>LAPORAN PENGELUARAN BAHAN BAKU PER DOKUMEN PABEAN</small><br>
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
                    <th rowspan="2" style="text-align:center;">Penerima Barang</th>
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
                                <td>' . $data['bc_no'] . '</td>
                                <td>' . $data['bc_date'] . '</td>
                                <td>' . $data['number'] . '</td>
                                <td>' . date("Y-m-d", strtotime($data['trans_date'])) . '</td>
                                <td>' . $data['customer_name'] . '</td>
                                <td>' . $data['item_number'] . '</td>
                                <td>' . $data['item_name'] . '</td>
                                <td>' . $data['uom'] . '</td>
                                <td>' . $data['price'] . '</td>
                                <td>' . $data['currency'] . '</td>
                                <td>' . number_format($data['qty'], "0", ",", ".") . '</td>
                            </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
