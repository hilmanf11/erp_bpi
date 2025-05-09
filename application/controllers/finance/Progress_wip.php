<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Progress_wip extends CI_Controller
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
            $data['menus_id'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('finance/progress_wip');
        } else {
            redirect('error_access');
        }
    }

    public function getData()
    {
        $filter_from = $this->input->post('filter_from');
        $filter_to   = $this->input->post('filter_to');
        $filter_item_fg = $this->input->post('filter_item_fg');
        $periode = date("Y-m", strtotime($filter_from));
        $periode_bf = date("Y-m", strtotime("-1 month", strtotime($filter_from)));

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Item Receipts
        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name, 
            b.name as prodfam, 
            a.uom,
            COALESCE(d.qty) as qty,
            COALESCE(d.amount) as amount
        FROM item_fg a
        LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty, SUM(amount) as amount FROM progress_wip WHERE trans_date < '$filter_from' GROUP BY item_fg_id) d ON a.id = d.item_fg_id
        WHERE a.id like '%$filter_item_fg%'
        GROUP BY a.id
        ORDER BY a.number");

        $data = array();
        foreach ($records as $record) {
            $item_fg_id = $record->id;

            $begin = $this->crud->query("SELECT item_fg_id, SUM(qty) as qty, SUM(amount) as amount, SUM(direct_material) as direct_material, SUM(direct_labor) as direct_labor, SUM(direct_foh) as direct_foh FROM progress_wip WHERE trans_date < '$filter_from' and item_fg_id = '$item_fg_id' GROUP BY item_fg_id");

            $in_qty = @$begin[0]->qty;
            $in_dm = @$begin[0]->direct_material;
            $in_dl = @$begin[0]->direct_labor;
            $in_foh = @$begin[0]->direct_foh;

            //RECEIPT
            $receipts = $this->crud->query("SELECT * FROM inventory_wip WHERE trans_type = 'SCAN FG' and item_fg_id = '$item_fg_id' and trans_date between '$filter_from' and '$filter_to'");

            //DELIVERY
            $returns = $this->crud->query("SELECT a.*, d.name as username
                            FROM delivery_notes a 
                            JOIN users d ON a.created_by = d.username
                            WHERE a.item_fg_id = '$item_fg_id' and a.trans_date between '$filter_from' and '$filter_to'");

            //Wip Receipt
            foreach ($receipts as $receipt) {
                $data[] = array(
                    "period" => $periode,
                    "item_fg_id" => $item_fg_id,
                    "trans_type" => "RECEIPT FG",
                    "created_name" => $receipt->created_name,
                    "trans_date" => $receipt->trans_date,
                    "invoice_no" => $receipt->invoice_no,
                    "customer_po" => "",
                    "document_no" => $receipt->document_no,
                    "uom" => $record->uom,
                    "qty" => abs($receipt->qty),
                    "direct_material" => abs($receipt->direct_material),
                    "direct_labor" => abs($receipt->direct_labor),
                    "direct_foh" => abs($receipt->direct_foh),
                    "price" => abs($receipt->price),
                    "amount" => abs($receipt->amount),
                );

                $in_qty += abs($receipt->qty);
                $in_dm += abs($receipt->direct_material);
                $in_dl += abs($receipt->direct_labor);
                $in_foh += abs($receipt->direct_foh);
            }

            //Delivery Note
            foreach ($returns as $return) {
                $direct_material = ((($in_dm / $in_qty) * $return->qty) * -1);
                $direct_labor = ((($in_dl / $in_qty) * $return->qty) * -1);
                $direct_foh = ((($in_foh / $in_qty) * $return->qty) * -1);
                $price_out = abs(($direct_material + $direct_labor + $direct_foh) / $return->qty);

                $data[] = array(
                    "period" => $periode,
                    "item_fg_id" => $item_fg_id,
                    "trans_type" => "DELIVERY NOTE",
                    "type_sales" => $return->trans_type,
                    "created_name" => $return->username,
                    "trans_date" => $return->trans_date,
                    "invoice_no" => $return->do_number,
                    "customer_po" => $return->customer_po,
                    "document_no" => $return->number,
                    "uom" => $record->uom,
                    "qty" => ($return->qty * -1),
                    "direct_material" => $direct_material,
                    "direct_labor" => $direct_labor,
                    "direct_foh" => $direct_foh,
                    "price" => $price_out,
                    "amount" => (($return->qty * -1) * $price_out),
                );

                $in_dm -= (($in_dm / $in_qty) * $return->qty);
                $in_dl -= (($in_dl / $in_qty) * $return->qty);
                $in_foh -= (($in_foh / $in_qty) * $return->qty);
                $in_qty -= $return->qty;
            }
        }

        $result['total'] = count($data);
        $result = array_merge($result, ['rows' => $data]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');

            $progress_wip = $this->crud->reads("progress_wip", [], [
                "period" => $post['period'],
                "item_fg_id" => $post['item_fg_id'],
                "invoice_no" => $post['invoice_no'],
                "document_no" => $post['document_no'],
                "customer_po" => $post['customer_po'],
                "trans_date" => $post['trans_date'],
                "qty" => $post['qty']
            ]);

            if (count($progress_wip) > 0) {
                $send = $this->crud->update('progress_wip', [
                    "period" => $post['period'],
                    "item_fg_id" => $post['item_fg_id'],
                    "invoice_no" => $post['invoice_no'],
                    "document_no" => $post['document_no'],
                    "customer_po" => $post['customer_po'],
                    "trans_date" => $post['trans_date'],
                    "qty" => $post['qty']
                ], $post);

                echo $send;
            } else {
                $send = $this->crud->create('progress_wip', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }


    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=progress_wip_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get("filter_division");
        $filter_shift = $this->input->get("filter_shift");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $query_main = "
                        select a.id,
                        a.number,
                        a.name, 
                        COALESCE(b.qty_wo,0) as qty_wo,
                        COALESCE(i.begin_balance,0) as begin_balance,
                        COALESCE(c.qty_actual,0) as qty_actual,
                        COALESCE(d.qty_ng,0) as qty_ng,
                        COALESCE((COALESCE(c.qty_actual,0)+COALESCE(d.qty_ng,0)),0) as total_production,
                        COALESCE(f.qty_subcont_jasa,0) as subconts_jasa,
                        COALESCE(g.qty_rfg,0) as rfg,
                        COALESCE(h.qty_rfg_jasa,0) as rfg_jasa,
                        COALESCE((COALESCE(i.begin_balance,0)) + COALESCE(c.qty_actual,0) + COALESCE(f.qty_subcont_jasa,0) - COALESCE(g.qty_rfg,0) - COALESCE(h.qty_rfg_jasa,0), 0) as ending_balance
                        FROM item_fg a
                        LEFT JOIN (
                                    select aa.item_fg_id,sum(aa.qty_wo) as qty_wo FROM (
                                            select distinct item_fg_id, workorder, period, qty_wo FROM  supply_sheets where request_date between '$filter_from' AND '$filter_to' 
                                    ) aa group by aa.item_fg_id
                        ) b on a.id = b.item_fg_id
                        LEFT JOIN (
                                    select item_fg_id, sum(qty) as qty_actual FROM output_productions where trans_date between '$filter_from' AND '$filter_to'  AND shift like '%$filter_shift%' group by item_fg_id
                        ) c on a.id = c.item_fg_id
                        LEFT JOIN (
                                    select aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                            select distinct item_fg_id, qty_product FROM  item_ng where trans_date between '$filter_from' AND '$filter_to' AND shift like '%$filter_shift%'
                                    ) aa group by aa.item_fg_id
                        ) d on a.id = d.item_fg_id
                        LEFT JOIN (
                                    select item_fg_id,sum(qty) as qty_balance_wip FROM wip_balances_fg where trans_date between '$filter_from' AND '$filter_to' group by item_fg_id
                        ) e on a.id = e.item_fg_id
                        LEFT JOIN (
                                    select aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                            select distinct ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                            FROM  supply_sheets ax 
                                            join item_fg ay on ax.item_fg_id=ay.id 
                                            where ax.request_date between '$filter_from' AND '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa'
                                    ) aa group by aa.item_fg_id
                        ) f on a.id = f.item_fg_id
                        LEFT JOIN (
                                    select aa.item_fg_id,sum(aa.receipt) as qty_rfg 
                                    FROM checksheets aa 
                                    JOIN item_fg ab on aa.item_fg_id = ab.id
                                    where aa.trans_date between '$filter_from' AND '$filter_to' and ab.status_subcont='NO'
                                    GROUP BY aa.item_fg_id
                        ) g on a.id = g.item_fg_id
                        LEFT JOIN (
                                    select aa.item_fg_id,sum(aa.receipt) as qty_rfg_jasa 
                                    FROM checksheets aa 
                                    JOIN item_fg ab on aa.item_fg_id = ab.id
                                    where aa.trans_date between '$filter_from' AND '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa'
                                    GROUP BY aa.item_fg_id
                        ) h on a.id = h.item_fg_id
                        LEFT JOIN (
                                    select a.id,
                                    case 
                                        when e.item_fg_id is not null then COALESCE(e.qty_balance_wip,0) else 
                                            COALESCE(COALESCE(e.qty_balance_wip,0) + COALESCE(c.qty_actual,0) + COALESCE(f.qty_subcont_jasa,0) - COALESCE(g.qty_rfg,0) - COALESCE(h.qty_rfg_jasa,0), 0)
                                    end as begin_balance
                                    FROM item_fg a
                                    LEFT JOIN (
                                                select aa.item_fg_id,sum(aa.qty_wo) as qty_wo FROM (
                                                        select distinct item_fg_id, workorder, period, qty_wo FROM  supply_sheets where request_date < '$filter_from' 
                                                ) aa group by aa.item_fg_id
                                    ) b on a.id = b.item_fg_id
                                    LEFT JOIN (
                                                select item_fg_id, sum(qty) as qty_actual FROM output_productions where trans_date < '$filter_from'  AND shift like '%$filter_shift%' group by item_fg_id
                                    ) c on a.id = c.item_fg_id
                                    LEFT JOIN (
                                                select aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                        select distinct item_fg_id, qty_product FROM  item_ng where trans_date < '$filter_from' AND shift like '%$filter_shift%'
                                                ) aa group by aa.item_fg_id
                                    ) d on a.id = d.item_fg_id
                                    LEFT JOIN (
                                                select item_fg_id,sum(qty) as qty_balance_wip FROM wip_balances_fg where trans_date < '$filter_from' group by item_fg_id
                                    ) e on a.id = e.item_fg_id
                                    LEFT JOIN (
                                                select aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                        select distinct ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                        FROM  supply_sheets ax 
                                                        join item_fg ay on ax.item_fg_id=ay.id 
                                                        where ax.request_date < '$filter_from' and ay.status_subcont='YES' and ay.subcont_type='Jasa'
                                                ) aa group by aa.item_fg_id
                                    ) f on a.id = f.item_fg_id
                                    LEFT JOIN (
                                                select aa.item_fg_id,sum(aa.receipt) as qty_rfg 
                                                FROM checksheets aa 
                                                JOIN item_fg ab on aa.item_fg_id = ab.id
                                                where aa.trans_date < '$filter_from' and ab.status_subcont='NO'
                                                GROUP BY aa.item_fg_id
                                    ) g on a.id = g.item_fg_id
                                    LEFT JOIN (
                                                select aa.item_fg_id,sum(aa.receipt) as qty_rfg_jasa 
                                                FROM checksheets aa 
                                                JOIN item_fg ab on aa.item_fg_id = ab.id
                                                where aa.trans_date < '$filter_from' and ab.status_subcont='YES' AND ab.subcont_type='Jasa'
                                                GROUP BY aa.item_fg_id
                                    ) h on a.id = h.item_fg_id
                        ) i on a.id = i.id
                        WHERE a.id LIKE '%$filter_items%' AND a.division_id LIKE '%$filter_division%'
                        ORDER BY a.number
        ";

        // echo $query_main;
        // die();

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
            <h3 style="margin:0;">PROGRESS WIP</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th width="20">No</th>
                    <th colspan="3">Product No</th>
                    <th colspan="2">Product Name</th>
                    <th colspan="2">Work Order Qty</th>
                    <th>Begin Balance</th>
                    <th>Actual Production</th>
                    <th>NG</th>
                    <th>Total Production</th>
                    <th>SubCont Jasa</th>
                    <th>RFG</th>
                    <th>RFG SubCont Jasa</th>
                    <th>Ending Balance</th>
                </tr>';
        $no = 1;
        foreach ($records as $record) {
            $item_fg_id = $record->id;
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3" style="mso-number-format:\@;">' . $record->number . '</td>
                            <td colspan="2" style="mso-number-format:\@;">' . $record->name . '</td>
                            <td colspan="2" style="text-align:right;">' . number_format($record->qty_wo, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->begin_balance, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_actual, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_ng, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->total_production, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->subconts_jasa, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->rfg, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->rfg_jasa, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->ending_balance, 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="23" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
                $html .= '  <tr>
                                <th width="20"></th>
                                <th width="20">No</th>
                                <th>Product No</th>
                                <th>Product Name</th>
                                <th>Type</th>
                                <th>Trans Date</th>
                                <th>Work Order No</th>
                                <th>Work Order Qty</th> 
                                <th>Begin Balance</th>
                                <th>Actual Production</th>
                                <th>NG</th>
                                <th>Total Production</th>
                                <th>SubCont Jasa</th>
                                <th>RFG</th>
                                <th>RFG SubCont Jasa</th>
                                <th>Ending Balance</th>
                            </tr>';
                $nod = 1;
                $begin = @$record->begin_balance;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;

                $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$item_fg_id' and trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%'");

                $dataNgs = $this->crud->query("
                                                select aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                        select distinct trans_date,document,item_fg_id, qty_product FROM item_ng where item_fg_id='$item_fg_id' and trans_date between '$filter_from' and '$filter_to' AND shift like '%$filter_shift%'
                                                ) aa group by aa.document,aa.trans_date,aa.item_fg_id
                ");

                $dataSubcontsJasas = $this->crud->query("
                                                select aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                        select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                        FROM supply_sheets ax 
                                                        join item_fg ay on ax.item_fg_id=ay.id 
                                                        where ax.item_fg_id='$item_fg_id' and ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa'
                                                ) aa group by aa.workorder,aa.request_date,aa.item_fg_id
                ");

                $dataRfgs = $this->crud->query("
                                                select aa.trans_date,aa.wo_no, aa.item_fg_id,sum(aa.receipt) as qty_rfg 
                                                FROM checksheets aa 
                                                JOIN item_fg ab on aa.item_fg_id = ab.id
                                                where aa.item_fg_id='$item_fg_id' and aa.trans_date between '$filter_from' and '$filter_to' and ab.status_subcont='NO'
                                                GROUP BY aa.trans_date,aa.wo_no,aa.item_fg_id
                ");

                $dataRfgSubcontsJasas = $this->crud->query("
                                                select aa.trans_date,aa.wo_no, aa.item_fg_id,sum(aa.receipt) as qty_rfg 
                                                FROM checksheets aa 
                                                JOIN item_fg ab on aa.item_fg_id = ab.id
                                                where aa.item_fg_id='$item_fg_id' and aa.trans_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa'
                                                GROUP BY aa.trans_date,aa.wo_no,aa.item_fg_id
                ");

                // Proses data berdasarkan tanggal
                $all_data = [];

                foreach ($dataActualProductions as $actualProduction) {
                    $all_data[] = [
                        'type' => 'ACTUAL PRODUCTION',
                        'date' => $actualProduction->trans_date,
                        'wo_no' => $actualProduction->wo_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => $actualProduction->qty,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                    ];
                }

                foreach ($dataNgs as $dataNg) {
                    $all_data[] = [
                        'type' => 'PRODUCT NG',
                        'date' => $dataNg->trans_date,
                        'wo_no' => $dataNg->document,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'ng' => $dataNg->qty_ng,
                        'subconts_jasa' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                    ];
                }

                foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                    $all_data[] = [
                        'type' => 'SUBCONTS JASA',
                        'date' => $dataSubcontsJasa->request_date,
                        'wo_no' => $dataSubcontsJasa->workorder,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'ng' => 0,
                        'subconts_jasa' => $dataSubcontsJasa->qty_subcont_jasa,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                    ];
                }

                foreach ($dataRfgs  as $dataRfg) {
                    $all_data[] = [
                        'type' => 'RFG',
                        'date' => $dataRfg->trans_date,
                        'wo_no' => $dataRfg->wo_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'rfg' => $dataRfg->qty_rfg,
                        'rfg_subconts_jasa' => 0,
                    ];
                }

                foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                    $all_data[] = [
                        'type' => 'RFG SUBCONTS JASA',
                        'date' => $dataRfgSubcontsJasa->trans_date,
                        'wo_no' => $dataRfgSubcontsJasa->wo_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => $dataRfgSubcontsJasa->qty_rfg,
                    ];
                }


                // Urutkan data berdasarkan tanggal
                usort($all_data, function ($a, $b) {
                    return strtotime($a['date']) - strtotime($b['date']);
                });

                // Generate HTML
                $nod = 1;
                $balance = $begin;
                foreach ($all_data as $data) {
                    $total_production = $data['actual_production'] + $data['ng'];
                    $balance += $data['actual_production'] + $data['subconts_jasa'] - $data['rfg'] - $data['rfg_subconts_jasa'];
                    $html .= '  <tr>
                                    <td></td>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td>' . $record->number  . '</td>
                                    <td>' . $record->name  . '</td>
                                    <td>' . $data['type']  . '</td>
                                    <td>' . $data['date']  . '</td>
                                    <td>' . $data['wo_no']  . '</td>
                                    <td style="text-align:right;">' . number_format($data['wo_qty'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($begin, 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['actual_production'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['ng'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($total_production, 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['subconts_jasa'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['rfg'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['rfg_subconts_jasa'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                </tr>';

                    $begin = $balance;
                    $nod++;
                }
            }
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
