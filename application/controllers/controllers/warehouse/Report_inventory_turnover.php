<?php

date_default_timezone_set("Asia/Bangkok");

defined('BASEPATH') or exit('No direct script access allowed');

class Report_inventory_turnover extends CI_Controller

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

        $this->form_validation->set_rules('po_no', 'PO No', 'required|min_length[1]|max_length[50]');

    }



    public function index()

    {

        if (empty($this->session->username)) {

            redirect('error_session');

        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {

            $data['button'] = $this->getbutton($this->id_menu());

            $this->load->view('template/header', $data);

            $this->load->view('warehouse/report_inventory_turnover');

        } else {

            redirect('error_access');

        }

    }



    public function readEndingStock()

    {

        if ($this->input->post()) {

            $item_rm_id = $this->input->post('item_rm_id');

            $trans_date = @$this->input->post('trans_date');



            if (@$trans_date == "") {

                $date = date("Y-m-d");

            } else {

                $date = $trans_date;

            }



            $records = $this->crud->query("SELECT

                a.id,

                a.number, 

                a.name, 

                b.name as prodfam, 

                a.uom, 

                COALESCE(0,0) as begin_stock,

                (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0)) as qty_in,

                f.qty as qty_out,

                (COALESCE(SUM(e.qty),0) - COALESCE(f.qty, 0) + COALESCE(g.return_qty, 0)) as end_stock

            FROM item_rm a 

            JOIN item_familys b ON a.item_family_id = b.id

            LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date <= '$date'

            LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id

            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id

            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty

                FROM return_materials a 

                JOIN return_material_labels b ON a.return_id = b.return_id

                JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no

                WHERE a.return_date <=  '$date'

                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id

            WHERE a.id like '$item_rm_id'

            GROUP BY a.id

            ORDER BY a.number");



            echo json_encode($records);

        }

    }



    public function readBalanceWip()

    {

        if ($this->input->post()) {

            $item_rm_id = $this->input->post('item_rm_id');

            $wip_balances = $this->crud->read("wip_balances", [], ["item_rm_id" => $item_rm_id], "", "id", "desc");



            echo json_encode($wip_balances);

        }

    }



    public function readItemFamily($item_category_id)

    {

        $this->db->select('*');

        $this->db->from('item_familys');

        $this->db->where('id !=', "P08"); 

        $this->db->where('deleted', 0);

        $this->db->where("item_category_id", $item_category_id);

        $this->db->order_by('name', 'ASC');

        $records = $this->db->get()->result_array();

        echo json_encode($records);

    }



    public function readItemFamilys()

    {

        $this->db->select('*');

        $this->db->from('item_familys');

        $this->db->where('id !=', "P08"); 

        $this->db->where('deleted', 0);

        // $this->db->where("item_category_id", $item_category_id);

        $this->db->order_by('name', 'ASC');

        $records = $this->db->get()->result_array();

        echo json_encode($records);

    }



    public function print($option = "")

    {

        if ($option == "excel") {

            $format  = date("Ymd");

            header("Content-type: application/vnd-ms-excel");

            header("Content-Disposition: attachment; filename=inventory_turnover_$format.xls");

        }

        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//



        $filter_from = $this->input->get('filter_from');

        $filter_to   = $this->input->get('filter_to');

        $filter_item_category = $this->input->get('filter_item_category');

        $filter_item_family = $this->input->get('filter_item_family');

        $filter_division = $this->input->get('filter_division');



        $start = strtotime($filter_from);

        $finish = strtotime($filter_to);

        //------------------------------------ Mengambil Filter dari Input GET berakhir disini----------------------------------//



        //Config

        $this->db->select('*');

        $this->db->from('config');

        $config = $this->db->get()->row();



        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//





        $records = $this->crud->query("SELECT

            a.id,

            a.number, 

            a.name, 

            a.division, 

            b.name as prodfam, 

            a.uom,

            c.name as category_name, 

            COALESCE(0,0) as begin_stock,

            (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0)) as qty_in,

            (COALESCE(f.qty,0) + COALESCE(j.qty, 0)) as qty_out,

            (
                COALESCE(f.qty,0) + COALESCE(j.qty,0) +
                COALESCE(
                    (
                        SELECT SUM(f2.qty)
                        FROM issued_material_details f2
                        WHERE f2.item_rm_id = a.id
                        AND DATE_FORMAT(f2.created_date, '%Y-%m-%d') BETWEEN DATE_FORMAT(DATE_SUB('$filter_from', INTERVAL 1 MONTH), '%Y-%m-01') 
                        AND LAST_DAY(DATE_SUB('$filter_from', INTERVAL 1 MONTH))
                    ), 0
                ) + 
                COALESCE(
                    (
                        SELECT SUM(j2.qty)
                        FROM transaction_rm j2
                        JOIN item_rm b2 ON j2.item_rm_id = b2.id
                        WHERE j2.item_rm_id = a.id
                        AND j2.transaction_kind = 'OUT'
                        AND j2.request_date BETWEEN DATE_FORMAT(DATE_SUB('$filter_from', INTERVAL 1 MONTH), '%Y-%m-01') 
                        AND LAST_DAY(DATE_SUB('$filter_from', INTERVAL 1 MONTH))
                    ), 0
                ) +
                COALESCE(
                    (
                        SELECT SUM(f3.qty)
                        FROM issued_material_details f3
                        WHERE f3.item_rm_id = a.id
                        AND DATE_FORMAT(f3.created_date, '%Y-%m-%d') BETWEEN DATE_FORMAT(DATE_SUB('$filter_from', INTERVAL 2 MONTH), '%Y-%m-01') 
                        AND LAST_DAY(DATE_SUB('$filter_from', INTERVAL 2 MONTH))
                    ), 0
                ) + 
                COALESCE(
                    (
                        SELECT SUM(j3.qty)
                        FROM transaction_rm j3
                        JOIN item_rm b3 ON j3.item_rm_id = b3.id
                        WHERE j3.item_rm_id = a.id
                        AND j3.transaction_kind = 'OUT'
                        AND j3.request_date BETWEEN DATE_FORMAT(DATE_SUB('$filter_from', INTERVAL 2 MONTH), '%Y-%m-01') 
                        AND LAST_DAY(DATE_SUB('$filter_from', INTERVAL 2 MONTH))
                    ), 0
                )

            )/3 as avg_usage_3_months
    



            FROM item_rm a 

            JOIN item_familys b ON a.item_family_id = b.id and b.number != 'FG'

            JOIN item_categories c ON a.item_category_id = c.id

            LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date between '$filter_from' and '$filter_to'

            LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id

            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) f ON a.id = f.item_rm_id

            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty

                FROM return_materials a 

                JOIN return_material_labels b ON a.return_id = b.return_id

                JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no

                WHERE a.return_date between '$filter_from' and '$filter_to'

                GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id

            

            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm

                FROM os_rm a

                JOIN item_rm b ON a.item_rm_id = b.id

                WHERE a.trans_date between '$filter_from' and '$filter_to'

                GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id



            LEFT JOIN (

                SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty

                FROM transaction_rm a

                JOIN item_rm b ON a.item_rm_id = b.id

                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'

                GROUP BY a.item_rm_id, a.transaction_kind

            ) i ON a.id = i.item_rm_id and i.transaction_kind = 'IN'



            LEFT JOIN (

                SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty

                FROM transaction_rm a

                JOIN item_rm b ON a.item_rm_id = b.id

                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to'

                GROUP BY a.item_rm_id, a.transaction_kind

            ) j ON a.id = j.item_rm_id and j.transaction_kind = 'OUT'


        -- LEFT JOIN transaction_rm i ON a.id = i.item_rm_id AND i.request_date between '$filter_from' and '$filter_to'

        

        WHERE c.id like '%$filter_item_category%' and b.number like '%$filter_item_family%' and a.division like '%$filter_division%' 

        GROUP BY a.id

        ORDER BY c.name DESC, b.name DESC, a.number");



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

                <h3 style="margin:0;">INVENTORY TURN OVER - RM</h3>

                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>

            </center>

            <br>

            

            <table id="customers" border="1" style="font-size: 11px;">

                <tr>

                    <th width="20">No</th>

                    <th colspan="3">Product No</th>

                    <th colspan="2">Product Name</th>

                    <th colspan="2">Uom</th>

                    <th colspan="2">Division</th>

                    <th colspan="2">Category</th>

                    <th>Product Family</th>

                    <th width="100">Begin<br>Stock</th>

                    <th width="100">In</th>

                    <th width="100">Out</th>

                    <th width="100">Ending<br>Stock</th>

                    <th width="100">AVG USAGE<br>3 MONTH</th>

                    <th width="100">ITO<br>(day)</th>



                </tr>';





        $no = 1;

        $totalBeginStock = 0;

        $totalIn = 0;

        $totalOut = 0;

        $totalEndingStock = 0;

        $totalQtyOut3Months = 0;

        $countQtyOutMonths = 0;

	$totalITO = 0;



        foreach ($records as $record) {

            $item_rm_id = $record->id;



            //Item Receipts

            $itemReceipts = $this->crud->query("SELECT

                    a.id,(COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0)) - (COALESCE(f.qty,0) + COALESCE(j.qty, 0)) as begin_stock   

                FROM item_rm a 

                JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'

                LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date < '$filter_from'

                LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id

                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id

                

                LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty

                    FROM return_materials a 

                    JOIN return_material_labels b ON a.return_id = b.return_id

                    JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no

                    WHERE a.return_date < '$filter_from'

                    GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id

                    

                LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm

                    FROM os_rm a

                    JOIN item_rm b ON a.item_rm_id = b.id

                    WHERE a.trans_date < '$filter_from'

                    GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id

                

                LEFT JOIN (

                    SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty

                    FROM transaction_rm a

                    JOIN item_rm b ON a.item_rm_id = b.id

                    WHERE a.request_date < '$filter_from'

                    GROUP BY a.item_rm_id, a.transaction_kind

                ) i ON a.id = i.item_rm_id and i.transaction_kind = 'IN'



                LEFT JOIN (

                    SELECT a.item_rm_id, a.transaction_kind, SUM(a.qty) AS qty

                    FROM transaction_rm a

                    JOIN item_rm b ON a.item_rm_id = b.id

                    WHERE a.request_date < '$filter_from'

                    GROUP BY a.item_rm_id, a.transaction_kind

                ) j ON a.id = j.item_rm_id and j.transaction_kind = 'OUT'

                    

                    WHERE a.id like '$item_rm_id'

                    GROUP BY a.id

                    ORDER BY a.number

            ");



            $totalBeginStock += @$itemReceipts[0]->begin_stock;

            $totalIn += $record->qty_in;

            $totalOut += $record->qty_out;

            $totalEndingStock += @(@$itemReceipts[0]->begin_stock + $record->qty_in) - $record->qty_out;

	    $totalQtyOut3Months += $record->avg_usage_3_months;
	    
            $endingStock = @(@$itemReceipts[0]->begin_stock + $record->qty_in) - $record->qty_out;

            $ito = ($record->avg_usage_3_months > 0) ? ($endingStock / $record->avg_usage_3_months) * 30 : 0;

            $totalITO += $ito;



            $html .= '  <tr>

                            <td style="text-align:center">' . $no . '</td>

                            <td colspan="3">' . $record->number . '</td>

                            <td colspan="2">' . $record->name . '</td>

                            <td colspan="2">' . $record->uom . '</td>

                            <td colspan="2">' . $record->division . '</td>

                            <td colspan="2">' . $record->category_name . '</td>

                            <td>' . $record->prodfam . '</td>

                            <td style="text-align:right;">' . number_format(@$itemReceipts[0]->begin_stock, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>

                            <td style="text-align:right;">' . number_format((@$itemReceipts[0]->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->avg_usage_3_months, 2) . '</td>

                            <td style="text-align:right;">' . number_format(($record->avg_usage_3_months > 0) ? (((@$itemReceipts[0]->begin_stock + $record->qty_in) - $record->qty_out) / $record->avg_usage_3_months) * 30 : 0, 2) . '</td>

                        </tr>';

            $no++;

        }



        $html .= '<tr>

            <td colspan="13" style="text-align:right;"><b>GRAND TOTAL</b></td>

            <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalQtyOut3Months, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalITO, 2) . '</td>

        </tr>';

      

        $html .= '</table></body></html>';

        echo $html;

    }



}

