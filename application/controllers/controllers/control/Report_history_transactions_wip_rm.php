<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions_wip_rm extends CI_Controller
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
            $this->load->view('control/report_history_transactions_wip_rm');
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
            header("Content-Disposition: attachment; filename=history_transactions_rm_$format.xls");
        }
        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get('filter_division');
        $filter_trans_type = $this->input->get('filter_trans_type');

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);
        //------------------------------------ Mengambil Filter dari Input GET berakhir disini----------------------------------//

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//

        $query_main = "
            select 
                a.id,
                a.number, 
                a.name, 
                a.division, 
                a.uom,
                o.name as prodfam, 
                p.name as category_name,
                COALESCE(n.begin_stock,0) as begin_stock,
                COALESCE(b.qty_supply_sheets,0) as qty_supply_sheets,
                COALESCE(c.qty_non_supply_sheets,0) as qty_non_supply_sheets,
                COALESCE(d.qty_bpb,0) as qty_bpb,
                COALESCE(b.qty_supply_sheets,0) + COALESCE(c.qty_non_supply_sheets,0) + COALESCE(d.qty_bpb,0) as qty_supply,
                COALESCE(e.qty_matreq,0) as qty_matreq,
                COALESCE(f.qty_adj_in,0) as qty_adj_in,
                COALESCE(g.qty_sto_in,0) as qty_sto_in,
                COALESCE(h.qty_bpm_whs,0) as qty_bpm_whs,
                COALESCE(i.qty_bpm_manual,0) as qty_bpm_manual,
                COALESCE(h.qty_bpm_whs,0) + COALESCE(i.qty_bpm_manual,0) as qty_return,
                COALESCE(j.qty_in_checksheet,0) + COALESCE(ja.qty_in_no_checksheet,0) + COALESCE(jb.initial_in,0) + COALESCE(jc.qty_in_wip_receipt,0) as qty_rfg,
                COALESCE(k.qty_ng,0) as qty_ng,
                COALESCE(l.qty_adj_out,0) as qty_adj_out,
                COALESCE(m.qty_sto_out,0) as qty_sto_out

                FROM item_rm a
                LEFT JOIN (select item_rm_id, sum(qty_act) as qty_supply_sheets from supply_sheets where request_date BETWEEN '$filter_from' AND '$filter_to' group by item_rm_id) b on a.id=b.item_rm_id
                LEFT JOIN (select item_rm_id, sum(qty) as qty_non_supply_sheets from supply_materials where type='Issued Production' and request_date BETWEEN '$filter_from' AND '$filter_to' group by item_rm_id) c on a.id=c.item_rm_id
                LEFT JOIN (select item_rm_id, sum(qty) as qty_bpb from transaction_rm where transaction_type='BPB' and request_date BETWEEN '$filter_from' AND '$filter_to' group by item_rm_id) d on a.id=d.item_rm_id
                LEFT JOIN (select item_rm_id, sum(qty) as qty_matreq from supply_requestions where request_date BETWEEN '$filter_from' AND '$filter_to' group by item_rm_id) e on a.id=e.item_rm_id
                LEFT JOIN (select item_rm_id, sum(qty) as qty_adj_in from transaction_wip where transaction_type='ADJ IN' and request_date BETWEEN '$filter_from' AND '$filter_to' group by item_rm_id) f on a.id=f.item_rm_id
                LEFT JOIN (select item_rm_id, sum(qty) as qty_sto_in from transaction_wip where transaction_type='STO IN' and request_date BETWEEN '$filter_from' AND '$filter_to' group by item_rm_id) g on a.id=g.item_rm_id
                LEFT JOIN (select item_rm_id, sum(qty) as qty_bpm_whs from bpm where status='1' and request_date BETWEEN '$filter_from' AND '$filter_to' group by item_rm_id) h on a.id=h.item_rm_id
                LEFT JOIN (select item_rm_id, sum(qty) as qty_bpm_manual from transaction_rm where transaction_type='BPM' and request_date BETWEEN '$filter_from' AND '$filter_to' group by item_rm_id) i on a.id=i.item_rm_id
                LEFT JOIN (
                        SELECT c.item_rm_id, sum(a.qty*c.composition) as qty_in_checksheet
                        FROM scan_item_receipts_fg a
                        JOIN checksheets b ON b.number = a.checksheet_number
                        JOIN bom c on b.item_fg_id = c.item_fg_id
                        WHERE DATE_FORMAT(b.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                        GROUP BY c.item_rm_id
                ) j on a.id=j.item_rm_id
                LEFT JOIN (
                        SELECT a.item_fg_id, sum(a.qty*b.composition) as qty_in_no_checksheet
                        FROM scan_item_receipts_fg a
                        JOIN bom b on a.item_fg_id = b.item_fg_id
                        WHERE a.type = 'NBFG'
                        AND a.packing_date BETWEEN '$filter_from' AND '$filter_to' 
                        GROUP BY a.item_fg_id
                ) ja on a.id = ja.item_fg_id
                    LEFT JOIN (
                        SELECT a.item_fg_id, sum(a.qty*b.composition)  as initial_in
                        FROM transaction_fg a
                        JOIN bom b on a.item_fg_id = b.item_fg_id
                        WHERE a.transaction_kind = 'IN'
                        AND a.request_date BETWEEN '$filter_from' AND '$filter_to' 
                        GROUP BY a.item_fg_id
                ) jb on a.id = jb.item_fg_id
                    LEFT JOIN (
                        SELECT a.item_fg_id, sum(a.qty*b.composition) as qty_in_wip_receipt
                        FROM wip_receipts a
                        JOIN bom b on a.item_fg_id = b.item_fg_id
                        WHERE a.division = 'MTS'
                        AND a.trans_date BETWEEN '$filter_from' AND '$filter_to' 
                        GROUP BY a.item_fg_id
                ) jc on a.id = jc.item_fg_id
                LEFT JOIN (select item_rm_id, sum(qty) as qty_ng from item_ng where trans_date BETWEEN '$filter_from' AND '$filter_to' group by item_rm_id) k on a.id=k.item_rm_id
                LEFT JOIN (select item_rm_id, sum(qty) as qty_adj_out from transaction_wip where transaction_type='ADJ OUT' and request_date BETWEEN '$filter_from' AND '$filter_to' group by item_rm_id) l on a.id=l.item_rm_id
                LEFT JOIN (select item_rm_id, sum(qty) as qty_sto_out from transaction_wip where transaction_type='STO OUT' and request_date BETWEEN '$filter_from' AND '$filter_to' group by item_rm_id) m on a.id=m.item_rm_id
                LEFT JOIN (
                                    select 
                                    a.id,
                                    a.number, 
                                    COALESCE(b.qty_supply_sheets,0) + COALESCE(c.qty_non_supply_sheets,0) + COALESCE(d.qty_bpb,0) + COALESCE(e.qty_matreq,0) + COALESCE(f.qty_adj_in,0) + COALESCE(g.qty_sto_in,0) - 
                                    COALESCE(h.qty_bpm_whs,0) - COALESCE(i.qty_bpm_manual,0) - COALESCE(j.qty_in_checksheet,0) - COALESCE(ja.qty_in_no_checksheet,0) - COALESCE(jb.initial_in,0) - COALESCE(jc.qty_in_wip_receipt,0) - COALESCE(k.qty_ng,0) - COALESCE(l.qty_adj_out,0) - COALESCE(m.qty_sto_out,0) as begin_stock
                                    FROM item_rm a
                                    LEFT JOIN (select item_rm_id, sum(qty_act) as qty_supply_sheets from supply_sheets where request_date < '$filter_from'  group by item_rm_id) b on a.id=b.item_rm_id
                                    LEFT JOIN (select item_rm_id, sum(qty) as qty_non_supply_sheets from supply_materials where type='Issued Production' and request_date < '$filter_from'  group by item_rm_id) c on a.id=c.item_rm_id
                                    LEFT JOIN (select item_rm_id, sum(qty) as qty_bpb from transaction_rm where transaction_type='BPB' and request_date < '$filter_from'  group by item_rm_id) d on a.id=d.item_rm_id
                                    LEFT JOIN (select item_rm_id, sum(qty) as qty_matreq from supply_requestions where request_date < '$filter_from'  group by item_rm_id) e on a.id=e.item_rm_id
                                    LEFT JOIN (select item_rm_id, sum(qty) as qty_adj_in from transaction_wip where transaction_type='ADJ IN' and request_date < '$filter_from'  group by item_rm_id) f on a.id=f.item_rm_id
                                    LEFT JOIN (select item_rm_id, sum(qty) as qty_sto_in from transaction_wip where transaction_type='STO IN' and request_date < '$filter_from'  group by item_rm_id) g on a.id=g.item_rm_id
                                    LEFT JOIN (select item_rm_id, sum(qty) as qty_bpm_whs from bpm where status='1' and request_date < '$filter_from'  group by item_rm_id) h on a.id=h.item_rm_id
                                    LEFT JOIN (select item_rm_id, sum(qty) as qty_bpm_manual from transaction_rm where transaction_type='BPM' and request_date < '$filter_from'  group by item_rm_id) i on a.id=i.item_rm_id
                                    LEFT JOIN (
                                            SELECT c.item_rm_id, sum(a.qty*c.composition) as qty_in_checksheet
                                            FROM scan_item_receipts_fg a
                                            JOIN checksheets b ON b.number = a.checksheet_number
                                            JOIN bom c on b.item_fg_id = c.item_fg_id
                                            WHERE DATE_FORMAT(b.packing_date, '%Y-%m-%d') < '$filter_from'
                                            GROUP BY c.item_rm_id
                                    ) j on a.id=j.item_rm_id
                                    LEFT JOIN (
                                            SELECT a.item_fg_id, sum(a.qty*b.composition) as qty_in_no_checksheet
                                            FROM scan_item_receipts_fg a
                                            JOIN bom b on a.item_fg_id = b.item_fg_id
                                            WHERE a.type = 'NBFG'
                                            AND a.packing_date < '$filter_from' 
                                            GROUP BY a.item_fg_id
                                    ) ja on a.id = ja.item_fg_id
                                        LEFT JOIN (
                                            SELECT a.item_fg_id, sum(a.qty*b.composition)  as initial_in
                                            FROM transaction_fg a
                                            JOIN bom b on a.item_fg_id = b.item_fg_id
                                            WHERE a.transaction_kind = 'IN'
                                            AND a.request_date < '$filter_from' 
                                            GROUP BY a.item_fg_id
                                    ) jb on a.id = jb.item_fg_id
                                        LEFT JOIN (
                                            SELECT a.item_fg_id, sum(a.qty*b.composition) as qty_in_wip_receipt
                                            FROM wip_receipts a
                                            JOIN bom b on a.item_fg_id = b.item_fg_id
                                            WHERE a.division = 'MTS'
                                            AND a.trans_date < '$filter_from' 
                                            GROUP BY a.item_fg_id
                                    ) jc on a.id = jc.item_fg_id
                                    LEFT JOIN (select item_rm_id, sum(qty) as qty_ng from item_ng where trans_date < '$filter_from'  group by item_rm_id) k on a.id=k.item_rm_id
                                    LEFT JOIN (select item_rm_id, sum(qty) as qty_adj_out from transaction_wip where transaction_type='ADJ OUT' and request_date < '$filter_from'  group by item_rm_id) l on a.id=l.item_rm_id
                                    LEFT JOIN (select item_rm_id, sum(qty) as qty_sto_out from transaction_wip where transaction_type='STO OUT' and request_date < '$filter_from'  group by item_rm_id) m on a.id=m.item_rm_id
                ) n on a.id = n.id
                JOIN item_familys o ON a.item_family_id = o.id AND o.number != 'FG'
                JOIN item_categories p ON a.item_category_id = p.id

        WHERE p.id LIKE '%$filter_item_category%'
        AND o.number LIKE '%$filter_item_family%'
        AND a.id LIKE '%$filter_items%'
        AND a.division LIKE '%$filter_division%'
        GROUP BY a.id
        ORDER BY o.name DESC, p.name DESC, a.number";

        // Eksekusi query
        $records = $this->crud->query($query_main);

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
                                <small>' . $config->description . '</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <h3 style="margin:0;">HISTORY TRANSACTION WIP (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2" colspan="3">Product No</th>
                    <th rowspan="2" colspan="2">Product Name</th>
                    <th rowspan="2" colspan="2">Uom</th>
                    <th rowspan="2" colspan="2">Division</th>
                    <th rowspan="2" colspan="2">Category</th>
                    <th rowspan="2" >Product Family</th>
                    <th rowspan="2" width="100">BEGIN</th>
                    <th colspan="4">IN</th>
                    <th colspan="5">OUT</th>
                    <th rowspan="2" width="100">BALANCE</th>
                </tr>
                <tr>
                    <th width="100">Supply</th>
                    <th width="100">Matreq</th>
                    <th width="100">ADJ IN</th>
                    <th width="100">STO IN</th>
                    <th width="100">Return</th>
                    <th width="100">RFG</th>
                    <th width="100">NG</th>
                    <th width="100">ADJ OUT</th>
                    <th width="100">STO OUT</th>
                </tr>';


        $no = 1;
        $totalBeginStock = 0;
        $totalSupply = 0;
        $totalMatreq = 0;
        $totalAdjIn = 0;
        $totalStoIn = 0;
        $totalReturn = 0;
        $totalRfg = 0;
        $totalNg = 0;
        $totalAdjOut = 0;
        $totalStoOut = 0;
        $totalEndingStock = 0;

        foreach ($records as $record) {
            $item_rm_id = $record->id;

            $totalBeginStock += @$record->begin_stock;
            $totalSupply += @$record->qty_supply;
            $totalMatreq += @$record->qty_matreq;
            $totalAdjIn += @$record->qty_adj_in;
            $totalStoIn += @$record->qty_sto_in;
            $totalReturn += @$record->qty_return;
            $totalRfg += @$record->qty_rfg;
            $totalNg += @$record->qty_ng;
            $totalAdjOut += @$record->qty_adj_out;
            $totalStoOut += @$record->qty_sto_out;
            $totalEndingStock += (@$record->begin_stock + @$record->qty_supply + @$record->qty_matreq + @$record->qty_adj_in + @$record->qty_sto_in) - (@$record->qty_return + @$record->qty_rfg + @$record->qty_ng + @$record->qty_adj_out + @$record->qty_sto_out);


            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3">' . $record->number . '</td>
                            <td colspan="2">' . $record->name . '</td>
                            <td colspan="2">' . $record->uom . '</td>
                            <td colspan="2">' . $record->division . '</td>
                            <td colspan="2">' . $record->category_name . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_supply, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_matreq, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_adj_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_sto_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_return, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_rfg, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_ng, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_adj_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_sto_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format((@$record->begin_stock + @$record->qty_supply + @$record->qty_matreq + @$record->qty_adj_in + @$record->qty_sto_in) - (@$record->qty_return + @$record->qty_rfg + @$record->qty_ng + @$record->qty_adj_out + @$record->qty_sto_out), 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="24" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>
                            <tr>
                                <th rowspan="2" width="20"></th>
                                <th rowspan="2" width="20">No</th>
                                <th  colspan="2"rowspan="2">Trans Type</th>
                                <th  colspan="2"rowspan="2">Created By</th>
                                <th  colspan="3"rowspan="2">Trans Date</th>
                                <th  colspan="2"rowspan="2">WO NO</th>
                                <th  colspan="2"rowspan="2">Doc. No</th>
                                <th rowspan="2">Begin</th>
                                <th colspan="4">IN</th>
                                <th colspan="5">OUT</th>
                                <th rowspan="2">Balance</th>
                            </tr>
                            <tr>
                                <th width="100">Supply</th>
                                <th width="100">Matreq</th>
                                <th width="100">ADJ IN</th>
                                <th width="100">STO IN</th>
                                <th width="100">Return</th>
                                <th width="100">RFG</th>
                                <th width="100">NG</th>
                                <th width="100">ADJ OUT</th>
                                <th width="100">STO OUT</th>
                            </tr>';

                $nod = 1;
                $begin = @$record->begin_stock;
                $balance = 0;

                if ($filter_trans_type == '') {
                    //-------------- Awal Query disini----------------------------------//  

                    //SUPPLY
                    $qsupply = $this->crud->query("
                    select item_rm_id, qty_act as qty_supply, workorder as wo_no, request_date, request_no as doc_no, request_name  from supply_sheets 
                    where item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'
                    union
                    select item_rm_id, qty as qty_supply, '-' as wo_no, request_date, request_no as doc_no, request_name from supply_materials
                    where item_rm_id = '$item_rm_id' and type='Issued Production' and request_date BETWEEN '$filter_from' and '$filter_to'
                    union
                    select item_rm_id, qty as qty_supply, '-' as wo_no, request_date, request_no as doc_no, request_name from transaction_rm
                    where transaction_type='BPB' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'");

                    //MATREQ
                    $qmatreq = $this->crud->query("select item_rm_id, workorder, request_date, qty, request_no, request_name from supply_requestions where item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'");

                    //ADJ IN
                    $qadj_in = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='ADJ IN' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'");

                    //STO IN
                    $qsto_in = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='STO IN' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'");

                    //RETURN
                    $qreturn = $this->crud->query("
                        select item_rm_id, request_date, request_no, request_name, qty, null as workorder from bpm where status='1' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'
                        union
                        select item_rm_id, request_date, request_no, request_name, qty, workorder from transaction_rm where transaction_type='BPM' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'
                    ");

                    //RFG
                    $receipts = $this->crud->query("
                        select c.item_rm_id, a.wo_no as workorder, a.checksheet_label as request_no, d.name as request_name, b.packing_date as request_date, a.qty*c.composition as qty_rfg
                        FROM scan_item_receipts_fg a
                        JOIN checksheets b ON b.number = a.checksheet_number
                        JOIN bom c on a.item_fg_id = c.item_fg_id
                        LEFT JOIN users d ON a.created_by = d.username
                        WHERE c.item_rm_id = '$item_rm_id' and DATE_FORMAT(b.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                    ");

                    $receiptsNB = $this->crud->query("
                    SELECT c.item_rm_id, f.checksheet_number, f.wo_no,sum(f.qty*c.composition) as qty, u.name as username ,f.packing_date as trans_date
                    FROM new_barcode_fg a
                    LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                    JOIN users u ON f.created_by = u.username
                    JOIN bom c on f.item_fg_id = c.item_fg_id
                    WHERE c.item_rm_id = '$item_rm_id'  AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                    $receiptsWIP = $this->crud->query("
                    SELECT c.item_rm_id,a.checksheet_number, a.document_no, a.wo_no, sum(a.qty*c.composition) as qty, a.trans_date, u.name as username, a.document_no as checksheet_label
                    FROM wip_receipts a
                    JOIN users u ON a.created_by = u.username
                    JOIN bom c on a.item_fg_id = c.item_fg_id
                    WHERE c.item_rm_id = '$item_rm_id' AND a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                    $transFgs = $this->crud->query("
                    SELECT c.item_rm_id,a.request_no, a.workorder,sum(a.qty*c.composition) as qty, a.request_name, a.request_date
                    FROM transaction_fg a
                    JOIN bom c on a.item_fg_id = c.item_fg_id
                    WHERE a.transaction_kind = 'IN' AND c.item_rm_id = '$item_rm_id' AND a.request_date BETWEEN '$filter_from' and '$filter_to'");


                    //NG
                    $qng = $this->crud->query("
                        select a.item_rm_id, a.trans_date, a.workorder, a.document,b.name, qty
                        from item_ng a 
                        left join users b on a.created_by=b.username 
                        where a.item_rm_id = '$item_rm_id' AND a.trans_date BETWEEN '$filter_from' and '$filter_to'
                    ");

                    //ADJ OUT
                    $qadj_out = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='ADJ OUT' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'");

                    //STO OUT
                    $qsto_out = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='STO OUT' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'");



                    //-------------- Akhir query disini----------------------------------//

                    $all_data = [];

                    // --- SUPPLY ---
                    foreach ($qsupply as $supp) {
                        $all_data[] = [
                            'type' => 'SUPPLY',
                            'username' => $supp->request_name,
                            'date' => $supp->request_date,
                            'wo_no' => $supp->wo_no,
                            'doc_no' => $supp->doc_no,
                            'qty_supply' => $supp->qty_supply,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => 0,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- MATREQ ---
                    foreach ($qmatreq as $mr) {
                        $all_data[] = [
                            'type' => 'MATREQ',
                            'username' => $mr->request_name,
                            'date' => $mr->request_date,
                            'wo_no' => $mr->workorder,
                            'doc_no' => $mr->request_no,
                            'qty_supply' => 0,
                            'qty_matreq' => $mr->qty,
                            'qty_adj_in' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => 0,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- ADJ IN ---
                    foreach ($qadj_in as $adjin) {
                        $all_data[] = [
                            'type' => 'ADJ IN',
                            'username' => $adjin->request_name,
                            'date' => $adjin->request_date,
                            'wo_no' => $adjin->workorder,
                            'doc_no' => $adjin->request_no,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => $adjin->qty,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => 0,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- STO IN ---
                    foreach ($qsto_in as $stoin) {
                        $all_data[] = [
                            'type' => 'STO IN',
                            'username' => $stoin->request_name,
                            'date' => $stoin->request_date,
                            'wo_no' => $stoin->workorder,
                            'doc_no' => $stoin->request_no,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_sto_in' => $stoin->qty,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => 0,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- RETURN ---
                    foreach ($qreturn as $return) {
                        $all_data[] = [
                            'type' => 'RETURN',
                            'username' => $return->request_name,
                            'date' => $return->request_date,
                            'wo_no' => $return->workorder,
                            'doc_no' => $return->request_no,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => $return->qty,
                            'qty_rfg' => 0,
                            'qty_ng' => 0,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- RFG ---
                    foreach ($receipts as $receipt) {
                        $all_data[] = [
                            'type' => 'RECEIPT FG',
                            'username' => $receipt->request_name,
                            'date' => $receipt->request_date,
                            'wo_no' => $receipt->workorder,
                            'doc_no' => $receipt->request_no,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => $receipt->qty_rfg,
                            'qty_ng' => 0,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- RFG ---
                    foreach ($receiptsNB as $receiptNB) {
                        $all_data[] = [
                            'type' => 'NEW BARCODE FG',
                            'username' => $receiptNB->username,
                            'date' => $receiptNB->trans_date,
                            'wo_no' => $receiptNB->wo_no,
                            'doc_no' => $receiptNB->checksheet_number,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => $receiptNB->qty,
                            'qty_ng' => 0,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- RFG ---
                    foreach ($receiptsWIP as $receiptWIP) {
                        $all_data[] = [
                            'type' => 'WIP RECEIPT FG',
                            'username' => $receiptWIP->username,
                            'date' => $receiptWIP->trans_date,
                            'wo_no' => $receiptWIP->wo_no,
                            'doc_no' => $receiptWIP->checksheet_number,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => $receiptWIP->qty,
                            'qty_ng' => 0,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- RFG ---
                    foreach ($transFgs as $transFG) {
                        $all_data[] = [
                            'type' => 'TRANSACTION FG',
                            'username' => $transFG->request_name,
                            'date' => $transFG->request_date,
                            'wo_no' => $transFG->workorder,
                            'doc_no' => $transFG->request_no,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => $transFG->qty,
                            'qty_ng' => 0,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- NG ---
                    foreach ($qng as $ng) {
                        $all_data[] = [
                            'type' => 'NG',
                            'username' => $ng->name,
                            'date' => $ng->trans_date,
                            'wo_no' => $ng->workorder,
                            'doc_no' => $ng->document,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => $ng->qty,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- ADJ OUT ---
                    foreach ($qadj_out as $adjout) {
                        $all_data[] = [
                            'type' => 'ADJ OUT',
                            'username' => $adjout->request_name,
                            'date' => $adjout->request_date,
                            'wo_no' => $adjout->workorder,
                            'doc_no' => $adjout->request_no,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => 0,
                            'qty_adj_out' => $adjout->qty,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- STO OUT ---
                    foreach ($qsto_out as $stoout) {
                        $all_data[] = [
                            'type' => 'STO OUT',
                            'username' => $stoout->request_name,
                            'date' => $stoout->request_date,
                            'wo_no' => $stoout->workorder,
                            'doc_no' => $stoout->request_no,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => 0,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => $stoout->qty,
                        ];
                    }

                    // Sort the data by date
                    usort($all_data, function ($a, $b) {
                        return strtotime($a['date']) - strtotime($b['date']);
                    });

                    foreach ($all_data as $data) {
                        $balance = ($begin + $data['qty_supply'] + $data['qty_matreq'] + $data['qty_adj_in'] + $data['qty_sto_in']) - ($data['qty_return'] + $data['qty_rfg'] + $data['qty_ng'] + $data['qty_adj_out'] + $data['qty_sto_out']);

                        $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td colspan="2">' . $data['type'] . '</td>
                                <td colspan="2">' . $data['username'] . '</td>
                                <td colspan="3">' . date("Y-m-d", strtotime($data['date'])) . '</td>
                                <td colspan="2">' . $data['wo_no'] . '</td>
                                <td colspan="2">' . $data['doc_no'] . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_supply'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_matreq'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_adj_in'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_sto_in'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_return'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_rfg'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_ng'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_adj_out'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_sto_out'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';

                        $begin = $balance;
                        $nod++;
                    }
                }

                if ($filter_trans_type == 'SUPPLY') {
                    //SUPPLY
                    $qsupply = $this->crud->query("
                    select item_rm_id, qty_act as qty_supply, workorder as wo_no, request_date, request_no as doc_no, request_name  from supply_sheets 
                    where item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'
                    union
                    select item_rm_id, qty as qty_supply, '-' as wo_no, request_date, request_no as doc_no, request_name from supply_materials
                    where item_rm_id = '$item_rm_id' and type='Issued Production' and request_date BETWEEN '$filter_from' and '$filter_to'
                    union
                    select item_rm_id, qty as qty_supply, '-' as wo_no, request_date, request_no as doc_no, request_name from transaction_rm
                    where transaction_type='BPB' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to' ORDER BY request_date");

                    foreach ($qsupply as $supply) {
                        $balance = ($begin + ($supply->qty_supply));
                        $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td colspan="2">SUPPLY</td>
                                <td colspan="2">' . $supply->request_name . '</td>
                                <td colspan="3">' . date("Y-m-d", strtotime($supply->request_date)) . '</td>
                                <td colspan="2">' . $supply->wo_no . '</td>
                                <td colspan="2">' . $supply->doc_no . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format($supply->qty_supply, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin += $supply->qty_supply;
                        $nod++;
                    }
                } else if ($filter_trans_type == 'MATREQ') {
                    //MATREQ
                    $qmatreq = $this->crud->query("select item_rm_id, workorder, request_date, qty, request_no, request_name from supply_requestions where item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'");

                    foreach ($qmatreq as $matreq) {
                        $balance = ($begin + ($matreq->qty));
                        $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td colspan="2">MATREQ</td>
                                <td colspan="2">' . $matreq->request_name . '</td>
                                <td colspan="3">' . date("Y-m-d", strtotime($matreq->request_date)) . '</td>
                                <td colspan="2">' . $matreq->workorder . '</td>
                                <td colspan="2">' . $matreq->request_no . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($matreq->qty, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin += $matreq->qty;
                        $nod++;
                    }
                } else if ($filter_trans_type == 'ADJ IN') {
                    //ADJ IN
                    $qadj_in = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='ADJ IN' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'");
                    foreach ($qadj_in as $adjin) {
                        $balance = ($begin + ($adjin->qty));
                        $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td colspan="2">ADJ IN</td>
                                <td colspan="2">' . $adjin->request_name . '</td>
                                <td colspan="3">' . date("Y-m-d", strtotime($adjin->request_date)) . '</td>
                                <td colspan="2">' . $adjin->workorder . '</td>
                                <td colspan="2">' . $adjin->request_no . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($adjin->qty, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin += $adjin->qty;
                        $nod++;
                    }
                } else if ($filter_trans_type == 'STO IN') {
                    //STO IN
                    $qsto_in = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='STO IN' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'");
                    foreach ($qsto_in as $stoin) {
                        $balance = ($begin + ($stoin->qty));
                        $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td colspan="2">STO IN</td>
                                <td colspan="2">' . $stoin->request_name . '</td>
                                <td colspan="3">' . date("Y-m-d", strtotime($stoin->request_date)) . '</td>
                                <td colspan="2">' . $stoin->workorder . '</td>
                                <td colspan="2">' . $stoin->request_no . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($stoin->qty, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin += $stoin->qty;
                        $nod++;
                    }
                } else if ($filter_trans_type == 'RETURN') {
                    //RETURN
                    $qreturn = $this->crud->query("
                        select item_rm_id, request_date, request_no, request_name, qty, null as workorder from bpm where status='1' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'
                        union
                        select item_rm_id, request_date, request_no, request_name, qty, workorder from transaction_rm where transaction_type='BPM' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'
                    ");
                    foreach ($qreturn as $return) {
                        $balance = ($begin - ($return->qty));
                        $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td colspan="2">RETURN</td>
                                <td colspan="2">' . $return->request_name . '</td>
                                <td colspan="3">' . date("Y-m-d", strtotime($return->request_date)) . '</td>
                                <td colspan="2">' . $return->workorder . '</td>
                                <td colspan="2">' . $return->request_no . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($return->qty, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin -= $return->qty;
                        $nod++;
                    }
                } else if ($filter_trans_type == 'RFG') {
                    //RFG
                    $all_dataRFG = [];
                    $receipts = $this->crud->query("
                        select c.item_rm_id, a.wo_no as workorder, a.checksheet_label as request_no, d.name as request_name, b.packing_date as request_date, a.qty*c.composition as qty_rfg
                        FROM scan_item_receipts_fg a
                        JOIN checksheets b ON b.number = a.checksheet_number
                        JOIN bom c on a.item_fg_id = c.item_fg_id
                        LEFT JOIN users d ON a.created_by = d.username
                        WHERE c.item_rm_id = '$item_rm_id' and DATE_FORMAT(b.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                    ");

                    $receiptsNB = $this->crud->query("
                    SELECT c.item_rm_id, f.checksheet_number, f.wo_no,sum(f.qty*c.composition) as qty, u.name as username ,f.packing_date as trans_date
                    FROM new_barcode_fg a
                    LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                    JOIN users u ON f.created_by = u.username
                    JOIN bom c on f.item_fg_id = c.item_fg_id
                    WHERE c.item_rm_id = '$item_rm_id'  AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                    $receiptsWIP = $this->crud->query("
                    SELECT c.item_rm_id,a.checksheet_number, a.document_no, a.wo_no, sum(a.qty*c.composition) as qty, a.trans_date, u.name as username, a.document_no as checksheet_label
                    FROM wip_receipts a
                    JOIN users u ON a.created_by = u.username
                    JOIN bom c on a.item_fg_id = c.item_fg_id
                    WHERE c.item_rm_id = '$item_rm_id' AND a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                    $transFgs = $this->crud->query("
                    SELECT c.item_rm_id,a.request_no, a.workorder,sum(a.qty*c.composition) as qty, a.request_name, a.request_date
                    FROM transaction_fg a
                    JOIN bom c on a.item_fg_id = c.item_fg_id
                    WHERE a.transaction_kind = 'IN' AND c.item_rm_id = '$item_rm_id' AND a.request_date BETWEEN '$filter_from' and '$filter_to'");

                    // --- RFG ---
                    foreach ($receipts as $receipt) {
                        $all_dataRFG[] = [
                            'type' => 'RECEIPT FG',
                            'username' => $receipt->request_name,
                            'date' => $receipt->request_date,
                            'wo_no' => $receipt->workorder,
                            'doc_no' => $receipt->request_no,
                            'qty_rfg' => $receipt->qty_rfg,
                        ];
                    }

                    // --- RFG ---
                    foreach ($receiptsNB as $receiptNB) {
                        $all_dataRFG[] = [
                            'type' => 'NEW BARCODE FG',
                            'username' => $receiptNB->username,
                            'date' => $receiptNB->trans_date,
                            'wo_no' => $receiptNB->wo_no,
                            'doc_no' => $receiptNB->checksheet_number,
                            'qty_rfg' => $receiptNB->qty,
                        ];
                    }

                    // --- RFG ---
                    foreach ($receiptsWIP as $receiptWIP) {
                        $all_dataRFG[] = [
                            'type' => 'WIP RECEIPT FG',
                            'username' => $receiptWIP->username,
                            'date' => $receiptWIP->trans_date,
                            'wo_no' => $receiptWIP->wo_no,
                            'doc_no' => $receiptWIP->checksheet_number,
                            'qty_rfg' => $receiptWIP->qty,
                        ];
                    }

                    // --- RFG ---
                    foreach ($transFgs as $transFG) {
                        $all_dataRFG[] = [
                            'type' => 'TRANSACTION FG',
                            'username' => $transFG->request_name,
                            'date' => $transFG->request_date,
                            'wo_no' => $transFG->workorder,
                            'doc_no' => $transFG->request_no,
                            'qty_rfg' => $transFG->qty,
                        ];
                    }
                    // Sort the data by date
                    usort($all_dataRFG, function ($a, $b) {
                        return strtotime($a['date']) - strtotime($b['date']);
                    });

                    foreach ($all_dataRFG as $data) {
                        $balance = ($begin - ($data['qty_rfg']));
                        $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td colspan="2">' . $data['type'] . '</td>
                                <td colspan="2">' . $data['username'] . '</td>
                                <td colspan="3">' . date("Y-m-d", strtotime($data['date'])) . '</td>
                                <td colspan="2">' . $data['wo_no'] . '</td>
                                <td colspan="2">' . $data['doc_no'] . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_rfg'], 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin -= $data['qty_rfg'];
                        $nod++;
                    }
                } else if ($filter_trans_type == 'NG') {
                    //NG
                    $qng = $this->crud->query("
                        select a.item_rm_id, a.trans_date, a.workorder, a.document,b.name, qty
                        from item_ng a 
                        left join users b on a.created_by=b.username 
                        where a.item_rm_id = '$item_rm_id' AND a.trans_date BETWEEN '$filter_from' and '$filter_to'
                    ");
                    foreach ($qng as $ng) {
                        $balance = ($begin - ($ng->qty));
                        $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td colspan="2">NG</td>
                                <td colspan="2">' . $ng->name . '</td>
                                <td colspan="3">' . date("Y-m-d", strtotime($ng->trans_date)) . '</td>
                                <td colspan="2">' . $ng->workorder . '</td>
                                <td colspan="2">' . $ng->document . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($ng->qty, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin -= $ng->qty;
                        $nod++;
                    }
                } else if ($filter_trans_type == 'ADJ OUT') {
                    //ADJ OUT
                    $qadj_out = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='ADJ OUT' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'");
                    foreach ($qadj_out as $adjout) {
                        $balance = ($begin - ($adjout->qty));
                        $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td colspan="2">ADJ OUT</td>
                                <td colspan="2">' . $adjout->request_name . '</td>
                                <td colspan="3">' . date("Y-m-d", strtotime($adjout->request_date)) . '</td>
                                <td colspan="2">' . $adjout->workorder . '</td>
                                <td colspan="2">' . $adjout->request_no . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($adjout->qty, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin -= $adjout->qty;
                        $nod++;
                    }
                } else if ($filter_trans_type == 'STO OUT') {
                    //STO OUT
                    $qsto_out = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='STO OUT' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'");
                    foreach ($qsto_out as $stoout) {
                        $balance = ($begin - ($stoout->qty));
                        $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td colspan="2">STO OUT</td>
                                <td colspan="2">' . $stoout->request_name . '</td>
                                <td colspan="3">' . date("Y-m-d", strtotime($stoout->request_date)) . '</td>
                                <td colspan="2">' . $stoout->workorder . '</td>
                                <td colspan="2">' . $stoout->request_no . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($stoout->qty, 2) . '</td>
                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin -= $stoout->qty;
                        $nod++;
                    }
                }

                //}
            }
            $no++;
        }

        $html .= '<tr>
            <td colspan="13" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;"><b>' . number_format($totalBeginStock, 2) . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalSupply, 2) . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalMatreq, 2) . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalAdjIn, 2) . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalStoIn, 2) . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalReturn, 2) . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalRfg, 2) . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalNg, 2) . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalAdjOut, 2) . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalStoOut, 2) . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalEndingStock, 2) . '</b></td>
        </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }

    public function lsb($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_rm_$format.xls");
        }
        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get('filter_division');
        $filter_trans_type = $this->input->get('filter_trans_type');

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
            l.name as sub_prodfam, 
            a.uom,
            c.name as category_name, 
            COALESCE(0,0) as begin_stock,

            COALESCE(SUM(e.qty),0) as receipt_qty, 
            COALESCE(i.qty,0) + COALESCE(o.qty_bpm_scan,0) as bpm_qty, 
            COALESCE(k.qty,0) as adj_in_qty, 

            COALESCE(f.qty,0) as qty_issued,
            COALESCE(j.qty,0) as qty_kanban,
            COALESCE(m.qty,0) as adj_out_qty,
            COALESCE(n.qty,0) as bpb_qty, 

            (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0) + COALESCE(k.qty, 0) + COALESCE(o.qty_bpm_scan, 0)) as qty_in,
            (COALESCE(f.qty,0) + COALESCE(j.qty, 0) + COALESCE(m.qty, 0)+ COALESCE(n.qty, 0)) as qty_out
    

            -- (COALESCE(SUM(e.qty), 0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(SUM(CASE WHEN i.transaction_kind = 'IN' THEN i.qty ELSE 0 END), 0)) as qty_in,
            -- (COALESCE(f.qty, 0) + COALESCE(SUM(CASE WHEN i.transaction_kind = 'OUT' THEN i.qty ELSE 0 END), 0)) as qty_out

            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id and b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date between '$filter_from' and '$filter_to'
            LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
            LEFT JOIN item_family_subs l ON a.item_sub_family_id = l.id
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

            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_bpm_scan
                FROM scan_item_bpm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date between '$filter_from' and '$filter_to'
                GROUP BY a.item_rm_id) o ON a.id = o.item_rm_id

            -- IN TRANSACTION di mulai dari sini----------------------- 

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type,SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to' AND a.transaction_type = 'BPM'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) i ON a.id = i.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to' AND a.transaction_type = 'ADJ IN STO'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) k ON a.id = k.item_rm_id

            -- OUT TRANSACTION di mulai dari sini-----------------------

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to' and a.transaction_type = 'KANBAN WO'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) j ON a.id = j.item_rm_id
        
            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to' and a.transaction_type = 'ADJ OUT STO'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) m ON a.id = m.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date BETWEEN '$filter_from' AND '$filter_to' and a.transaction_type = 'BPB'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) n ON a.id = n.item_rm_id
        
        WHERE c.id like '%$filter_item_category%' and b.number like '%$filter_item_family%' and a.id like '%$filter_items%' and a.division like '%$filter_division%' 
        GROUP BY a.id
        ORDER BY c.name DESC, b.name DESC, a.number");

        // $query_main = "SELECT 
        //     a.id,
        //     a.number, 
        //     a.name, 
        //     a.division, 
        //     b.name as prodfam, 
        //     a.uom,
        //     c.name as category_name,
        //     COALESCE(j.begin_stock) AS begin_stock,
        //     (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0)) AS qty_in,
        //     (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) AS qty_out
        // FROM item_rm a
        // JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
        // JOIN item_categories c ON a.item_category_id = c.id
        // LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
        // LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        // LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        // LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        // LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
        // LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i ON a.id = i.item_rm_id

        // LEFT JOIN (SELECT a.id, a.number, ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0)) - (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
        //             FROM item_rm a
        //             LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_from'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
        //             LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
        //             LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
        //             LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$filter_from' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
        //             LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        //             LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
        //         ) j ON a.id = j.id

        // WHERE c.id LIKE '%$filter_item_category%'
        // AND b.number LIKE '%$filter_item_family%'
        // AND a.id LIKE '%$filter_items%'
        // AND a.division LIKE '%$filter_division%'
        // GROUP BY a.id
        // ORDER BY c.name DESC, b.name DESC, a.number";

        // Eksekusi query
        // $records = $this->crud->query($query_main);


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
                                <small>' . $config->description . '</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <h3 style="margin:0;">LBS (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Product No</th>
                    <th rowspan="2">Product Name</th>
                    <th rowspan="2">Uom</th>
                    <th rowspan="2">Division</th>
                    <th rowspan="2">Category</th>
                    <th rowspan="2">Product Family</th>
                    <th rowspan="2">Sub Product <br>Family</th>
                    <th rowspan="2" width="100">Begin<br>Stock</th>
                    <th rowspan="2" width="100">In</th>
                    <th rowspan="2" width="100">Out</th>
                    <th rowspan="2" width="100">Ending<br>Stock</th>
                    <th colspan="3">IN</th>
                    <th colspan="4">OUT</th>
                    <th rowspan="2" width="100">Total<br>In</th>
                    <th rowspan="2" width="100">Total<br>Out</th>
                    <th rowspan="2" width="100">Selisih Summary <br>VS Detail (IN)</th>
                    <th rowspan="2" width="100">Selisih Summary <br>VS Detail (OUT)</th>
                </tr>
                <tr>
                    <th width="80">Purchase</th>
                    <th width="80">BPM</th>
                    <th width="80">ADJ STO</th>

                    <th width="80">Supply Sheet</th>
                    <th width="80">Kanban</th>
                    <th width="80">BPB</th>
                    <th width="80">ADJ STO</th>
                </tr>';


        $no = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalEndingStock = 0;

        $totalReceiptQty = 0;
        $totalBpmQty = 0;
        $totalAdjInQty = 0;

        $totalQtyIssued = 0;
        $totalQtyKanban = 0;
        $totalAdjOutQty = 0;
        $totalBpbQty = 0;

        $totalQtyIn = 0;
        $totalQtyOut = 0;
        $totalQtySelisihIn = 0;
        $totalQtySelisihOut = 0;

        foreach ($records as $record) {

            $item_rm_id = $record->id;
            //Item Receipts
            $itemReceipts = $this->crud->query("SELECT
                    a.id,(COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0) + COALESCE(o.qty_bpm_scan, 0)) - (COALESCE(f.qty,0) + COALESCE(j.qty, 0)) as begin_stock   
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

                LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_bpm_scan
                    FROM scan_item_bpm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.request_date < '$filter_from'
                    GROUP BY a.item_rm_id) o ON a.id = o.item_rm_id
                
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

            $totalReceiptQty += $record->receipt_qty;
            $totalBpmQty += $record->bpm_qty;
            $totalAdjInQty += $record->adj_in_qty;

            $totalQtyIssued += $record->qty_issued;
            $totalQtyKanban += $record->qty_kanban;
            $totalAdjOutQty += $record->adj_out_qty;
            $totalBpbQty += $record->bpb_qty;

            $totalQtyIn += ($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty);
            $totalQtyOut += ($record->qty_issued + $record->qty_kanban + $record->adj_out_qty + $record->bpb_qty);
            $totalQtySelisihIn += (($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty) - $record->qty_in);
            $totalQtySelisihOut += (($record->qty_issued + $record->qty_kanban + $record->adj_out_qty + $record->bpb_qty) - $record->qty_out);

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $record->number . '</td>
                            <td>' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->division . '</td>
                            <td>' . $record->category_name . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td>' . $record->sub_prodfam . '</td>
                            <td style="text-align:right;">' . number_format(@$itemReceipts[0]->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format((@$itemReceipts[0]->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>
                            
                            <td style="text-align:right;">' . $record->receipt_qty . '</td>
                            <td style="text-align:right;">' . $record->bpm_qty . '</td>
                            <td style="text-align:right;">' . $record->adj_in_qty . '</td>

                            <td style="text-align:right;">' . $record->qty_issued . '</td>
                            <td style="text-align:right;">' . $record->qty_kanban . '</td>
                            <td style="text-align:right;">' . $record->bpb_qty . '</td>
                            <td style="text-align:right;">' . $record->adj_out_qty . '</td>

                            <td style="text-align:right;">' . number_format($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_issued + $record->qty_kanban + $record->adj_out_qty + $record->bpb_qty, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty) - $record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->qty_issued + $record->qty_kanban + $record->adj_out_qty + $record->bpb_qty) - $record->qty_out, 2) . '</td>

                        </tr>';
            $no++;
        }

        $html .= '<tr>
            <td colspan="8" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalReceiptQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalBpmQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalAdjInQty, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalQtyIssued, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyKanban, 2) . '</td>
             <td style="text-align:right;">' . number_format($totalBpbQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalAdjOutQty, 2) . '</td>
           
            <td style="text-align:right;">' . number_format($totalQtyIn, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyOut, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtySelisihIn, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtySelisihOut, 2) . '</td>
            
        </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }

    public function detail_transaction($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_rm_$format.xls");
        }
        //------------------------------------ Opsi print berakhir disini------------------------------------------------------//

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get('filter_division');
        $filter_trans_type = $this->input->get('filter_trans_type');

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
            l.name as sub_prodfam,
            a.uom,
            c.name as category_name, 
            COALESCE(0,0) as begin_stock,
            (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0) + COALESCE(k.qty_scan_bpm, 0)) as qty_in,
            (COALESCE(f.qty,0) + COALESCE(j.qty, 0)) as qty_out
    

            -- (COALESCE(SUM(e.qty), 0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(SUM(CASE WHEN i.transaction_kind = 'IN' THEN i.qty ELSE 0 END), 0)) as qty_in,
            -- (COALESCE(f.qty, 0) + COALESCE(SUM(CASE WHEN i.transaction_kind = 'OUT' THEN i.qty ELSE 0 END), 0)) as qty_out

            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id and b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date between '$filter_from' and '$filter_to'
            LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
            LEFT JOIN item_family_subs l ON a.item_sub_family_id = l.id
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

            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_scan_bpm
                FROM scan_item_bpm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date between '$filter_from' and '$filter_to'
                GROUP BY a.item_rm_id) k ON a.id = k.item_rm_id

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
        
        WHERE c.id like '%$filter_item_category%' and b.number like '%$filter_item_family%' and a.id like '%$filter_items%' and a.division like '%$filter_division%' 
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
                                <small>' . $config->description . '</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <h3 style="margin:0;">DETAIL TRANSACTION (RM)</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th width="20">No</th>
                    <th>Part No</th>
                    <th>Category</th>
                    <th>Product Family</th>
                    <th>Sub Product <br>Family</th>
                    <th>Uom</th>
                    <th>Trans Type</th>
                    <th>Created By</th>
                    <th>Trans Date</th>
                    <th>Custom. Kind</th>
                    <th>Custom. No</th>
                    <th>Doc. No</th>
                    <th>Custom. Date</th>
                    <th>Begin</th>
                    <th>In</th>
                    <th>Out</th>
                    <th>Balance</th>
                </tr>';


        $no = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalEndingStock = 0;

        foreach ($records as $record) {
            $item_rm_id = $record->id;

            //Item Receipts
            $itemReceipts = $this->crud->query("SELECT
                    a.id,(COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0) + COALESCE(k.qty_scan_bpm, 0)) - (COALESCE(f.qty,0) + COALESCE(j.qty, 0)) as begin_stock   
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
                
                LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_scan_bpm
                    FROM scan_item_bpm a
                    JOIN item_rm b ON a.item_rm_id = b.id
                    WHERE a.request_date < '$filter_from'
                    GROUP BY a.item_rm_id) k ON a.id = k.item_rm_id

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

            if ($filter_display == "DETAIL") {
                $begin = @$itemReceipts[0]->begin_stock;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;
                for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                    $working_date = date('Y-m-d', $i);

                    if ($filter_trans_type == '') {
                        //-------------- Awal Query disini----------------------------------//                    
                        //RECEIPT
                        $receipts = $this->crud->query("SELECT
                            a.receipt_date, 
                            a.bc_kind, 
                            a.bc_aju, 
                            a.bc_document, 
                            a.bc_date, 
                            SUM(b.qty) as qty_receipt,
                            c.name as username
                        FROM purchase_order_receipts a 
                        JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
                        JOIN users c ON a.created_by = c.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$working_date' and '$working_date'
                        GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");

                        //ISSUED
                        $issueds = $this->crud->query("SELECT * FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$working_date' and '$working_date'");

                        //RETURN
                        $returns = $this->crud->query("SELECT
                            a.return_no,
                            a.return_id,
                            a.return_name,
                            a.return_date,
                            b.label_no,
                            b.qty,
                            d.name as username
                        FROM return_materials a 
                        JOIN return_material_labels b ON a.return_id = b.return_id
                        JOIN scan_item_receipts c ON a.return_id = c.receipt_id
                        JOIN users d ON a.created_by = d.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.return_date between '$working_date' and '$working_date'
                        GROUP BY b.label_no");

                        //OS RM
                        $os_rms = $this->crud->query("SELECT * FROM os_rm WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(trans_date, '%Y-%m-%d') between '$working_date' and '$working_date'");

                        //SCAN BPM
                        $bpm_scans = $this->crud->query("SELECT 
                        created_by, 
                        qty, 
                        created_date, 
                        label, 
                        request_date, 
                        request_id 
                        FROM scan_item_bpm 
                        WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(request_date, '%Y-%m-%d') between '$working_date' and '$working_date'");

                        // TRANSACTION RM (IN and OUT)
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.request_date between '$working_date' and '$working_date'");

                        //-------------- Akhir query disini----------------------------------//


                        //Purchase Order Receipt
                        foreach ($receipts as $receipt) {
                            $balance = ($begin + ($receipt->qty_receipt - $end_qty));
                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>RECEIPT</td>
                                            <td>' . $receipt->username . '</td>
                                            <td>' . $receipt->receipt_date . '</td>
                                            <td>' . $receipt->bc_kind . '</td>
                                            <td>' . $receipt->bc_aju . '</td>
                                            <td>' . $receipt->bc_document . '</td>
                                            <td>' . $receipt->bc_date . '</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($receipt->qty_receipt, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin += $receipt->qty_receipt;
                            $no++;
                        }

                        //Issued Material
                        foreach ($issueds as $issued) {
                            $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                            $balance = ($begin - $issued->qty);
                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>ISSUED</td>
                                            <td>' . $user->name . '</td>
                                            <td>' . date("Y-m-d", strtotime($issued->created_date)) . '</td>
                                            <td>-</td>
                                            <td>' . $issued->label_no . '</td>
                                            <td>' . $issued->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($issued->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin -= $issued->qty;
                            $no++;
                        }
                        //Return Material
                        foreach ($returns as $return) {
                            $balance = ($begin + $return->qty);
                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>RETURN</td>
                                            <td>' . $return->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($return->return_date)) . '</td>
                                            <td>-</td>
                                            <td>' . $return->label_no . '</td>
                                            <td>' . $return->return_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($return->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin += $return->qty;
                            $no++;
                        }

                        //OS RM
                        foreach ($os_rms as $os_rm) {
                            $user = $this->crud->read("users", [], ["username" => $os_rm->created_by]);
                            $balance = ($begin + $os_rm->qty);
                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>OS RM</td>
                                            <td>' . $user->name . '</td>
                                            <td>' . date("Y-m-d", strtotime($os_rm->created_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($os_rm->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin += $os_rm->qty;
                            $no++;
                        }

                        //SCAN BPM
                        foreach ($bpm_scans as $bpm_scan) {
                            $user = $this->crud->read("users", [], ["username" => $bpm_scan->created_by]);
                            $balance = ($begin + $bpm_scan->qty);
                            $html .= '  <tr>
                                             <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>BPM</td>
                                            <td>' . $user->name . '</td>
                                            <td>' . date("Y-m-d", strtotime($bpm_scan->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . date("Y-m-d", strtotime($bpm_scan->request_date)) . '</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($bpm_scan->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin += $bpm_scan->qty;
                            $no++;
                        }

                        foreach ($transactions as $transaction) {
                            $trans_type_label = $transaction->transaction_type;
                            $balance = ($transaction->transaction_kind == 'IN') ? ($begin + $transaction->qty) : ($begin - $transaction->qty);

                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>' . $trans_type_label . '</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                        </tr>';

                            // Update balance
                            if ($transaction->transaction_kind == 'IN') {
                                $begin += $transaction->qty;
                            } else {
                                $begin -= $transaction->qty;
                            }

                            $no++;
                        }
                    }

                    if ($filter_trans_type == 'RECEIPT') {
                        //RECEIPT
                        $receipts = $this->crud->query("SELECT
                            a.receipt_date, 
                            a.bc_kind, 
                            a.bc_aju, 
                            a.bc_document, 
                            a.bc_date, 
                            SUM(b.qty) as qty_receipt,
                            c.name as username
                        FROM purchase_order_receipts a 
                        JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
                        JOIN users c ON a.created_by = c.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$working_date' and '$working_date'
                        GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");

                        foreach ($receipts as $receipt) {
                            $balance = ($begin + ($receipt->qty_receipt - $end_qty));
                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>RECEIPT</td>
                                            <td>' . $receipt->username . '</td>
                                            <td>' . $receipt->receipt_date . '</td>
                                            <td>' . $receipt->bc_kind . '</td>
                                            <td>' . $receipt->bc_aju . '</td>
                                            <td>' . $receipt->bc_document . '</td>
                                            <td>' . $receipt->bc_date . '</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($receipt->qty_receipt, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin += $receipt->qty_receipt;
                            $no++;
                        }
                    }

                    if ($filter_trans_type == 'ADJ IN STO') {
                        //TRANSACTION
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'ADJ IN STO' and a.request_date between '$working_date' and '$working_date'");

                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN')
                                ? ($begin + $transaction->qty)
                                : ($begin - $transaction->qty);

                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>ADJ IN STO</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                        </tr>';

                            // Update balance
                            if ($transaction->transaction_kind == 'IN') {
                                $begin += $transaction->qty;
                            } else {
                                $begin -= $transaction->qty;
                            }

                            $no++;
                        }
                    }


                    if ($filter_trans_type == 'BPM') {
                        //TRANSACTION
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'BPM' and a.request_date between '$working_date' and '$working_date'");

                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN')
                                ? ($begin + $transaction->qty)
                                : ($begin - $transaction->qty);

                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>BPM</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                        </tr>';

                            // Update balance
                            if ($transaction->transaction_kind == 'IN') {
                                $begin += $transaction->qty;
                            } else {
                                $begin -= $transaction->qty;
                            }

                            $no++;
                        }
                    }

                    if ($filter_trans_type == 'ADJ OUT STO') {
                        //TRANSACTION
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'ADJ OUT STO' and a.request_date between '$working_date' and '$working_date'");

                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN')
                                ? ($begin + $transaction->qty)
                                : ($begin - $transaction->qty);

                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>ADJ OUT STO</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                        </tr>';

                            // Update balance
                            if ($transaction->transaction_kind == 'IN') {
                                $begin += $transaction->qty;
                            } else {
                                $begin -= $transaction->qty;
                            }

                            $no++;
                        }
                    }

                    if ($filter_trans_type == 'BPB') {
                        //TRANSACTION
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'BPB' and a.request_date between '$working_date' and '$working_date'");

                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN')
                                ? ($begin + $transaction->qty)
                                : ($begin - $transaction->qty);

                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>BPB</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                        </tr>';

                            // Update balance
                            if ($transaction->transaction_kind == 'IN') {
                                $begin += $transaction->qty;
                            } else {
                                $begin -= $transaction->qty;
                            }

                            $no++;
                        }
                    }

                    if ($filter_trans_type == 'KANBAN WO') {
                        //TRANSACTION
                        $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'KANBAN WO' and a.request_date between '$working_date' and '$working_date'");

                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN')
                                ? ($begin + $transaction->qty)
                                : ($begin - $transaction->qty);

                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>KANBAN WO</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>' . $transaction->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'IN' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                            <td style="text-align:right;">' . ($transaction->transaction_kind == 'OUT' ? number_format($transaction->qty, 2) : number_format(0)) . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                        </tr>';

                            // Update balance
                            if ($transaction->transaction_kind == 'IN') {
                                $begin += $transaction->qty;
                            } else {
                                $begin -= $transaction->qty;
                            }

                            $no++;
                        }
                    }

                    if ($filter_trans_type == 'ISSUED') {
                        //ISSUED
                        $issueds = $this->crud->query("SELECT * FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$working_date' and '$working_date'");

                        foreach ($issueds as $issued) {
                            $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                            $balance = ($begin - $issued->qty);
                            $html .= '  <tr>
                                            <td style="text-align:center">' . $no . '</td>
                                            <td>' . $record->number . '</td>
                                            <td>' . $record->category_name . '</td>
                                            <td>' . $record->prodfam . '</td>
                                            <td>' . $record->sub_prodfam . '</td>
                                            <td>' . $record->uom . '</td>
                                            <td>ISSUED</td>
                                            <td>' . $user->name . '</td>
                                            <td>' . date("Y-m-d", strtotime($issued->created_date)) . '</td>
                                            <td>-</td>
                                            <td>' . $issued->label_no . '</td>
                                            <td>' . $issued->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0) . '</td>
                                            <td style="text-align:right;">' . number_format($issued->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin -= $issued->qty;
                            $no++;
                        }
                    }
                }
            }
            $no++;
        }

        // $html .= '<tr>
        //     <td colspan="14" style="text-align:right;"><b>GRAND TOTAL</b></td>
        //     <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>
        //     <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>
        //     <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>
        //     <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>
        // </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }
}
