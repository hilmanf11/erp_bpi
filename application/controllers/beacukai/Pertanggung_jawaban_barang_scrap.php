<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Pertanggung_jawaban_barang_scrap extends CI_Controller
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
            $this->load->view('beacukai/pertanggung_jawaban_barang_scrap');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->db->select('a.*, b.name as item_family_name, c.name as uom');
        $this->db->from('items a');
        $this->db->join('item_familys b', 'b.id = a.item_family_id');
        $this->db->join('uom c', 'a.uom_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->where_in("b.number", ["003", "002"]);
        $this->db->like('a.number', $post);
        $this->db->order_by('a.number', 'asc');
        $records = $this->db->get()->result_array();
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
        $filter_items = $this->input->get('filter_items');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $records = $this->crud->query("SELECT
            a.id,
        	a.number, 
            a.name, 
            b.name as prodfam, 
            c.name as uom, 
        	f.begin_in,
            g.begin_out,
            COALESCE(d.qty_in, 0) as qty_in,
            COALESCE(e.qty_out, 0) as qty_out
        FROM items a 
        JOIN item_familys b ON a.item_family_id = b.id
        JOIN uom c ON a.uom_id = c.id
        LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_in FROM scraps WHERE `type` = 'IN' AND DATE_FORMAT(trans_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_id) d ON a.id = d.item_id
        LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty_out FROM scraps WHERE `type` = 'OUT' AND DATE_FORMAT(trans_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_id) e ON a.id = e.item_id
        LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as begin_in FROM scraps WHERE `type` = 'IN' AND DATE_FORMAT(trans_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_id) f ON a.id = f.item_id
        LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as begin_out FROM scraps WHERE `type` = 'OUT' AND DATE_FORMAT(trans_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_id) g ON a.id = g.item_id
        WHERE a.id like '%$filter_items%' and b.number IN ('002','003')
        GROUP BY a.id
        ORDER BY a.number");

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
                                <small>LAPORAN PERTANGGUNG JAWABAN MUTASI BAHAN BAKU DAN BAHAN PENOLONG</small><br>
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
                    <th width="20" style="text-align:center;">No</th>
                    <th style="text-align:center;">Kode Barang</th>
                    <th style="text-align:center;">Nama Barang</th>
                    <th style="text-align:center;">Satuan</th>
                    <th style="text-align:center;">Saldo Awal</th>
                    <th style="text-align:center;">Pemasukan</th>
                    <th style="text-align:center;">Pengeluaran</th>
                    <th style="text-align:center;">Saldo Akhir</th>
                    <th style="text-align:center;">Keterangan</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $item_id = $data->id;

            $this->db->select('a.*, b.number as item_number, b.name as item_name');
            $this->db->from('scraps a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->where('b.id', $item_id);
            $this->db->order_by('b.number', 'ASC');
            $this->db->order_by('a.trans_date', 'ASC');
            $details = $this->db->get()->result_object();

            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data->number . '</td>
                            <td>' . $data->name . '</td>
                            <td>' . $data->uom . '</td>
                            <td style="text-align:right">' . number_format($data->begin_in - $data->begin_out, 2) . '</td>
                            <td style="text-align:right">' . number_format($data->qty_in, 2) . '</td>
                            <td style="text-align:right">' . number_format($data->qty_out, 2) . '</td>
                            <td style="text-align:right">' . number_format(($data->begin_in - $data->begin_out) + ($data->qty_in - $data->qty_out), 2) . '</td>
                            <td style="text-align:right;"></td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
