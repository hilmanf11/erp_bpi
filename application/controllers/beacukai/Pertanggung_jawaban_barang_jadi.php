<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Pertanggung_jawaban_barang_jadi extends CI_Controller
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
            $this->load->view('beacukai/pertanggung_jawaban_barang_jadi');
        } else {
            redirect('error_access');
        }
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
        	COALESCE(0,0) as begin_stock,
            COALESCE(SUM(f.qty),0) as qty_in,
            g.qty as qty_out,
            (COALESCE(SUM(f.qty),0) - COALESCE(g.qty, 0)) as end_stock
        FROM items a 
        JOIN item_familys b ON a.item_family_id = b.id
        JOIN uom c ON a.uom_id = c.id
        LEFT JOIN production_schedules d ON a.id = d.item_id
        LEFT JOIN checksheets e ON d.workorder = e.workorder
        LEFT JOIN scan_item_receipts_fg f ON e.number = f.checksheet_number
        LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty FROM delivery_notes GROUP BY item_id) g ON a.id = g.item_id
        WHERE f.created_date between '$filter_from' and '$filter_to' and a.id like '%$filter_items%'
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
                                <small>LAPORAN PERTANGGUNG JAWABAN MUTASI BARANG JADI</small><br>
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
                    <th style="text-align:center;">Adjustment</th>
                    <th style="text-align:center;">Saldo Akhir</th>
                    <th style="text-align:center;">STO</th>
                    <th style="text-align:center;">Selisih</th>
                    <th style="text-align:center;">Keterangan</th>
                </tr>';

        $no = 1;
        foreach ($records as $data) {
            $item_id = $data->id;

            $sto_fg = $this->crud->query("SELECT SUM(qty) as sto FROM sto_fg WHERE item_id = '$item_id' AND trans_date BETWEEN '$filter_from' AND '$filter_to'");

            //Item Receipts
            $endstock = $this->crud->query("SELECT a.id,
                (COALESCE(SUM(f.qty),0) - COALESCE(g.qty, 0)) as begin_stock
            FROM items a 
            JOIN item_familys b ON a.item_family_id = b.id
            JOIN uom c ON a.uom_id = c.id
            LEFT JOIN production_schedules d ON a.id = d.item_id
            LEFT JOIN checksheets e ON d.workorder = e.workorder
            LEFT JOIN scan_item_receipts_fg f ON e.number = f.checksheet_number
            LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty FROM delivery_notes GROUP BY item_id) g ON a.id = g.item_id
            WHERE f.created_date < '$filter_from' and a.id = '$item_id'
            GROUP BY a.id
            ORDER BY a.number");

            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data->number . '</td>
                            <td>' . $data->name . '</td>
                            <td>' . $data->uom . '</td>
                            <td style="text-align:right;">' . number_format(@$endstock[0]->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($data->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($data->qty_out, 2) . '</td>
                            <td style="text-align:right;">0</td>
                            <td style="text-align:right;">' . number_format((@$endstock[0]->begin_stock + $data->end_stock), 2) . '</td>
                            <td style="text-align:right;">' . number_format(@$sto_fg[0]->sto, 2) . '</td>
                            <td style="text-align:right;">' . number_format((@$endstock[0]->begin_stock + $data->end_stock - @$sto_fg[0]->sto), 2) . '</td>
                            <td style="text-align:right;"></td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
