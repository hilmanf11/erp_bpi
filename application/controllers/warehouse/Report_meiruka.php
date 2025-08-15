<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_meiruka extends CI_Controller
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
            $this->load->view('warehouse/report_meiruka');
        } else {
            redirect('error_access');
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
            header("Content-Disposition: attachment; filename=meiruka_board_$format.xls");
        }
        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_division = $this->input->get('filter_division');
        $filter_status = $this->input->get('filter_status');

        $month = date('m', strtotime($filter_to));
        $year = date('Y', strtotime($filter_to));

        $filter_from_minus1 = date('Y-m-01', strtotime('-1 month', strtotime($filter_from)));
        $filter_to_minus1   = date('Y-m-t',  strtotime('-1 month', strtotime($filter_from)));
        $filter_from_minus2 = date('Y-m-01', strtotime('-2 month', strtotime($filter_to)));
        $filter_to_minus2   = date('Y-m-t',  strtotime('-2 month', strtotime($filter_to)));
        $filter_from_minus3 = date('Y-m-01', strtotime('-3 month', strtotime($filter_to)));
        $filter_to_minus3   = date('Y-m-t',  strtotime('-3 month', strtotime($filter_to)));

        //------------------------------------ Mengambil Filter dari Input GET berakhir disini----------------------------------//

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//

        $query_main = "SELECT 
            a.id,
            a.number, 
            a.name, 
            a.division, 
            b.name as prodfam, 
            a.uom,
            c.name as category_name,
            COALESCE(m.supplier_name,'-') as supplier_name,
            COALESCE(l.need_1, 0) as plan_supply,
            COALESCE(n.min, 0) as min,
            COALESCE(n.max, 0) as max,
            COALESCE(n.leadtime, 0) as leadtime,
            COALESCE(o.rack, '-') as rack,
            COALESCE(h.qty_issued, 0) as qty_issued,
            COALESCE(j.begin_stock) AS begin_stock,
            (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) AS qty_in,
            COALESCE(h.qty_issued, 0) AS qty_out
        FROM item_rm a
        JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
        JOIN item_categories c ON a.item_category_id = c.id
        LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i ON a.id = i.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) k ON a.id = k.item_rm_id

        LEFT JOIN (SELECT a.id, a.number, ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
                        FROM item_rm a
                        LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_from'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$filter_from' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                    ) j ON a.id = j.id
        LEFT JOIN (SELECT t.item_rm_id, t.need_1
                        FROM generate_mrp_finals t
                        JOIN (
                            SELECT item_rm_id, MAX(revision) AS max_revisi
                            FROM generate_mrp_finals
                            WHERE p_month = '$month' AND p_year = '$year'
                            GROUP BY item_rm_id
                        ) x ON t.item_rm_id = x.item_rm_id AND t.revision = x.max_revisi
                        WHERE t.p_month = '$month' AND t.p_year = '$year') l ON a.id = l.item_rm_id
        LEFT JOIN (SELECT DISTINCT c.name as supplier_name,a.item_rm_id,a.share_order 
                        FROM supplier_items a
                        LEFT JOIN item_rm b ON a.item_rm_id = b.id
                        LEFT JOIN suppliers c ON a.supplier_id = c.id
                        WHERE a.share_order = 100 AND b.item_family_id IN('P01','P02','P06')
                    ) m ON a.id = m.item_rm_id
        LEFT JOIN (SELECT item_rm_id, min, max, leadtime FROM master_minmax WHERE status = 0 group by item_rm_id) n ON a.id = n.item_rm_id
        LEFT JOIN warehouse_location_items o ON a.id = o.item_rm_id

        WHERE c.id LIKE '%$filter_item_category%'
        AND b.number LIKE '%$filter_item_family%'
        AND a.id LIKE '%$filter_items%'
        AND a.division LIKE '%$filter_division%'
        AND a.item_family_id IN('P01','P02','P06')
        GROUP BY a.id
        ORDER BY c.name DESC, b.name DESC, a.number";

        // Eksekusi query
        $records = $this->crud->query($query_main);

        $html = '<html><head><title>Print Data</title></head>
        <style>
            body {
                font-family: Arial, Helvetica, sans-serif;
            }
            #customers {
                border-collapse: collapse;
                width: 100%;
                font-size: 12px;
            }
            #customers td, #customers th {
                border: 1px solid #ddd;
                padding: 2px;
            }
            #customers tr:nth-child(even) {
                background-color: #f2f2f2;
            }
            #customers tr:hover {
                background-color: #ddd;
            }
            #customers th {
                padding-top: 2px;
                padding-bottom: 2px;
                text-align: center;
                color: black;
            }
        </style>
        <body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; text-align: center;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>' . $config->description . '</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                <!-- Print Date ' . date("d M Y H:i:s") . ' <br> -->
                <!-- Print By ' . $this->session->username . '  -->
            </div>
            <br><br><br>
            <h2 style="margin:0; background-color:#000080; color:white; padding:5px;">MEIRUKA BOARD</h2>
            <div style="background-color:orange; color:black; font-size:12px; padding:3px;">
                <!-- PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b> -->
            </div>
        </center>
        <br>

        <table id="customers" border="1" style="font-size: 11px;">
            <tr>
                <th width="20">No</th>
                <th colspan="3">Part No</th>
                <th colspan="2">Part Name</th>
                <th>Product Family</th>
                <th>Supplier</th>
                <th>Plan <br>Supply</th>
                <th>Actual <br>Supply</th>
                <th>Remain <br>Supply</th>
                <th>Supply <br>perday</th>
                <th>Min Stock <br>(Day)</th>
                <th>Min <br>Stock</th>
                <th>Lead Time <br>Pickup</th>
                <th>Stock <br>Current</th>
                <th>Max Stock <br>(Day)</th>
                <th>Max <br>Stock</th>
                <th>Status</th>
                <th>Status <br>Supply</th>
                <th>Address</th>
            </tr>';



        $no = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalEndingStock = 0;
        $totalIto = 0;

        foreach ($records as $record) {
            $item_rm_id = $record->id;

            // SUPPLY per DAY AVERAGE di ambil dari minus 1 bulan 
            // $average_query = "SELECT COALESCE(AVG(qty_per_day),0) AS avg_qty_per_day
            //     FROM (
            //         SELECT DATE_FORMAT(created_date, '%Y-%m-%d') AS issued_date,
            //             SUM(qty) AS qty_per_day
            //         FROM issued_material_details
            //         WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from_minus1' AND '$filter_to_minus1'
            //         AND item_rm_id = '$item_rm_id'
            //         GROUP BY DATE_FORMAT(created_date, '%Y-%m-%d')
            //     ) AS daily_summary";

            // $average = $this->crud->query($average_query);

            // var_dump($filter_from_minus1);
            // var_dump($filter_to_minus1);
            // return;
            
            $supplyperday_query = "SELECT sub.item_rm_id, sub.trans_date, sub.total_qty
                FROM (
                    SELECT 
                        a.item_rm_id,
                        b.trans_date, 
                        SUM(b.qty * a.composition) AS total_qty
                    FROM bom a 
                    JOIN production_schedules b ON a.item_fg_id = b.item_fg_id 
                    WHERE 
                        DATE_FORMAT(b.trans_date, '%Y-%m-%d') BETWEEN '$filter_from_minus1' AND '$filter_to_minus1'
                        AND a.item_rm_id = '$item_rm_id'
                    GROUP BY b.trans_date
                ) AS sub
                ORDER BY sub.total_qty DESC
                LIMIT 1";

            $supplyperday = $this->crud->query($supplyperday_query);

            // var_dump($supplyperday[0]->total_qty);
            // return;

            $totalEndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out;

            //STATUS : IF(stock current > max stock;"OVER";IF(min stock <= stock current;"OK";"ROP"))

            if(((@$record->begin_stock + $record->qty_in) - $record->qty_out) > (($supplyperday[0]->total_qty ?? 0) * $record->max)){
                $status = 'OVER';
                $style = "background:yellow; color:black;";
            }elseif(($supplyperday[0]->total_qty ?? 0) * $record->min <= ((@$record->begin_stock + $record->qty_in) - $record->qty_out)){
                $status = 'OK';
                $style = "";
            }else{
                $status = 'UNDER';
                $style = "background:red; color:white;";
            }

            if($record->qty_issued >= $record->plan_supply){
                $status_supply = 'CLOSED';
            }else{
                $status_supply = 'OPEN';
            }

            $html .= '  <tr>
                <td style="text-align:center">' . $no . '</td>
                <td colspan="3">' . $record->number . '</td>
                <td colspan="2">' . $record->name . '</td>
                <td>' . $record->prodfam . '</td>
                <td>' . $record->supplier_name . '</td>
                <td>' . $record->plan_supply . '</td>
                <td>' . number_format($record->qty_issued, 2) . '</td>
                <td>' . number_format($record->plan_supply - $record->qty_issued, 2) . '</td>
                <td>' . number_format($supplyperday[0]->total_qty ?? 0, 2) . '</td>
                <td>' . $record->min . '</td>
                <td>' . number_format(($supplyperday[0]->total_qty ?? 0) * $record->min, 2) . '</td>
                <td>' . $record->leadtime . '</td>
                <td>' . number_format((@$record->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>
                <td>' . $record->max . '</td>
                <td>' . number_format(($supplyperday[0]->total_qty ?? 0) * $record->max, 2) . '</td>
                <td style="' . $style . '">' . $status . '</td>
                <td>' . $status_supply . '</td>
                <td>' . $record->rack . '</td>
            </tr>';
            $no++;
        }

        // $html .= '<tr>
        //     <td colspan="10" style="text-align:right;"><b>GRAND TOTAL</b></td>
        //     <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>
        //     <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>
        //     <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>
        //     <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>
        //     <td style="text-align:right;">' . number_format($totalIto, 2) . '</td>
        // </tr>';
      
        $html .= '</table></body></html>';
        echo $html;
    }
}
