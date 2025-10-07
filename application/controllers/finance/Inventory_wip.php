<?php
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Inventory_wip extends CI_Controller
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
            $this->load->view('finance/inventory_wip');
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
        $month = date("m", strtotime($filter_from));
        $year = date("m", strtotime($filter_from));

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Item Receipts
        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name, 
            a.uom,
            COALESCE(d.price) as price
        FROM item_fg a
        LEFT JOIN (SELECT DISTINCT item_fg_id, price FROM inventory_wip WHERE trans_date < '$filter_from' and trans_type = 'SCAN FG' ORDER BY trans_date DESC LIMIT 1) d ON a.id = d.item_fg_id
        WHERE a.id like '%$filter_item_fg%'
        GROUP BY a.id
        ORDER BY a.number");

        $data = array();
        foreach ($records as $record) {
            $item_fg_id = $record->id;

            $begin = $this->crud->query("SELECT item_fg_id, SUM(qty) as qty, SUM(amount) as amount, SUM(direct_material) as direct_material, SUM(direct_labor) as direct_labor, SUM(direct_foh) as direct_foh FROM inventory_wip WHERE trans_date < '$filter_from' and item_fg_id = '$item_fg_id' GROUP BY item_fg_id");

            //RECEIPT
            $receipts = $this->crud->query("SELECT a.* FROM production_schedules a WHERE a.item_fg_id = '$item_fg_id' and a.trans_date between '$filter_from' and '$filter_to'");

            //DELIVERY
            $returns = $this->crud->query("SELECT b.qty, b.workorder, b.created_by, DATE_FORMAT(b.created_date, '%Y-%m-%d') as trans_date, 
                            b.checksheet_label
                            FROM production_schedules a 
                            LEFT JOIN checksheets e ON a.workorder = e.workorder
                            LEFT JOIN scan_item_receipts_fg b ON e.number = b.checksheet_number and a.so_number = b.so_number and a.workorder = b.workorder
                            WHERE a.item_fg_id = '$item_fg_id' and DATE_FORMAT(b.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'
                            GROUP BY b.checksheet_label");

            //RETURN
            $returns2 =  $this->crud->query("SELECT a.* FROM return_materials a 
                            JOIN production_schedules b ON a.workorder = b.workorder
                            WHERE b.item_fg_id = '$item_fg_id' and a.return_date between '$filter_from' and '$filter_to' GROUP BY a.return_id");

            //Wip Receipt
            $in_qty = @$begin[0]->qty;
            $in_dm = @$begin[0]->direct_material;
            $in_dl = @$begin[0]->direct_labor;
            $in_foh = @$begin[0]->direct_foh;
            foreach ($receipts as $receipt) {
                $costing_product_workorder = $this->crud->read("costing_product_workorders", [], ["item_fg_id" => $item_fg_id, "periode" => $periode, "workorder" => $receipt->workorder]);

                $receipt_qty = $receipt->qty;
                $direct_material = (@$costing_product_workorder->direct_material + @$costing_product_workorder->direct_requestion);
                $direct_labor = (@$costing_product_workorder->direct_labor + @$costing_product_workorder->direct_overtime);
                $direct_foh = @$costing_product_workorder->direct_foh;
                $price_in = ((@$direct_material + $direct_labor + $direct_foh) / $receipt_qty);
                $amount_in = ($receipt_qty * $price_in);

                $data[] = array(
                    "period" => $periode,
                    "item_fg_id" => $item_fg_id,
                    "trans_type" => "PRODUCTION",
                    "created_name" => $receipt->created_by,
                    "trans_date" => $receipt->trans_date,
                    "invoice_no" => $receipt->workorder,
                    "document_no" => $receipt->so_number,
                    "uom" => $record->uom,
                    "qty" => $receipt_qty,
                    "direct_material" => $direct_material,
                    "direct_labor" => $direct_labor,
                    "direct_foh" => $direct_foh,
                    "price" => $price_in,
                    "amount" => $amount_in,
                );

                $in_qty += $receipt_qty;
                $in_dm += $direct_material;
                $in_dl += $direct_labor;
                $in_foh += $direct_foh;
            }

            //Delivery Note
            foreach ($returns as $return) {
                $direct_material = ((($in_dm / $in_qty) * $return->qty) * -1);
                $direct_labor = ((($in_dl / $in_qty) * $return->qty) * -1);
                $direct_foh = ((($in_foh / $in_qty) * $return->qty) * -1);
                $price_out = (($direct_material + $direct_labor + $direct_foh) / $return->qty);

                $data[] = array(
                    "period" => $periode,
                    "item_fg_id" => $item_fg_id,
                    "trans_type" => "SCAN FG",
                    "created_name" => $return->created_by,
                    "trans_date" => $return->trans_date,
                    "invoice_no" => $return->workorder,
                    "document_no" => $return->checksheet_label,
                    "uom" => $record->uom,
                    "qty" => ($return->qty * -1),
                    "direct_material" => $direct_material,
                    "direct_labor" => $direct_labor,
                    "direct_foh" => $direct_foh,
                    "price" => $price_out,
                    "amount" => (($return->qty * $price_out) * -1),
                );

                $in_dm -= (($in_dm / $in_qty) * $return->qty);
                $in_dl -= (($in_dl / $in_qty) * $return->qty);
                $in_foh -= (($in_foh / $in_qty) * $return->qty);
                $in_qty -= $return->qty;
            }

            //Delivery Note
            foreach ($returns2 as $return2) {
                $direct_material = ((($in_dm / $in_qty) * $return2->qty) * -1);
                $direct_labor = ((($in_dl / $in_qty) * $return2->qty) * -1);
                $direct_foh = ((($in_foh / $in_qty) * $return2->qty) * -1);
                $price_out = (($direct_material + $direct_labor + $direct_foh) / $return2->qty);

                $data[] = array(
                    "period" => $periode,
                    "item_fg_id" => $item_fg_id,
                    "trans_type" => "SCAN FG",
                    "created_name" => $return2->return_name,
                    "trans_date" => $return2->return_date,
                    "invoice_no" => $return2->workorder,
                    "document_no" => $return2->return_id,
                    "uom" => $record->uom,
                    "qty" => ($return2->qty * -1),
                    "direct_material" => $direct_material,
                    "direct_labor" => $direct_labor,
                    "direct_foh" => $direct_foh,
                    "price" => $price_out,
                    "amount" => (($return2->qty * $price_out) * -1),
                );

                $in_dm -= (($in_dm / $in_qty) * $return2->qty);
                $in_dl -= (($in_dl / $in_qty) * $return2->qty);
                $in_foh -= (($in_foh / $in_qty) * $return2->qty);
                $in_qty -= $return2->qty;
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

            $inventory_wip = $this->crud->reads("inventory_wip", [], [
                "period" => $post['period'],
                "item_fg_id" => $post['item_fg_id'],
                "invoice_no" => $post['invoice_no'],
                "document_no" => $post['document_no'],
                "trans_date" => $post['trans_date']
            ]);

            if (count($inventory_wip) > 0) {
                $send = $this->crud->update('inventory_wip', [
                    "period" => $post['period'],
                    "item_fg_id" => $post['item_fg_id'],
                    "invoice_no" => $post['invoice_no'],
                    "document_no" => $post['document_no'],
                    "trans_date" => $post['trans_date']
                ], $post);

                echo $send;
            } else {
                $send = $this->crud->create('inventory_wip', $post);
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
            header("Content-Disposition: attachment; filename=inventory_wip_$format.xls");
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
                        a.uom,
                        j.number as division,
                        COALESCE(k.price,0) as price,
                        COALESCE(k.currency,'-') as currency,
                        COALESCE(b.qty_wo,0) as qty_wo,
                        COALESCE(i.begin_balance,0) as begin_balance,
                        COALESCE(c.qty_actual,0) as qty_actual,
                        COALESCE(c2.qty_wip,0) as qty_wip,
                        COALESCE(j2.qty_adj_in,0) as qty_adj_in,
                        COALESCE(d.qty_ng,0) as qty_ng,
                        COALESCE((COALESCE(c.qty_actual,0)+COALESCE(d.qty_ng,0)),0) as total_production,
                        COALESCE(f.qty_subcont_jasa,0) as subconts_jasa,
                        COALESCE(g.qty_in_checksheet,0) + COALESCE(gb.initial_in,0) + COALESCE(gc.qty_in_wip_receipt,0) as rfg,
                        COALESCE(h.qty_rfg_jasa,0) as rfg_jasa,
                        COALESCE(c.qty_actual,0) + COALESCE(f.qty_subcont_jasa,0) + COALESCE(c2.qty_wip,0) + COALESCE(j2.qty_adj_in,0) as qty_in,
                        COALESCE(ng_map.qty_ng,0) + COALESCE(g.qty_in_checksheet,0) + COALESCE(gb.initial_in,0) + COALESCE(gc.qty_in_wip_receipt,0) + COALESCE(h.qty_rfg_jasa,0) + COALESCE(k2.qty_adj_out,0) as qty_out,
                        COALESCE((COALESCE(i.begin_balance,0)) + COALESCE(c.qty_actual,0) + COALESCE(f.qty_subcont_jasa,0) + COALESCE(j2.qty_adj_in,0) + COALESCE(c2.qty_wip,0) - 
                               COALESCE(ng_map.qty_ng,0) - COALESCE(g.qty_in_checksheet,0) + COALESCE(gb.initial_in,0) + COALESCE(gc.qty_in_wip_receipt,0) + COALESCE(h.qty_rfg_jasa,0) + COALESCE(k2.qty_adj_out,0), 0) as ending_balance
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
                                    select item_fg_id, sum(qty_wip) as qty_wip FROM output_productions where trans_date between '$filter_from' AND '$filter_to'  AND shift like '%$filter_shift%' group by item_fg_id
                        ) c2 on a.id = c2.item_fg_id
                        LEFT JOIN (
                                    select aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                            select distinct item_fg_id, qty_product FROM  item_ng where trans_date between '$filter_from' AND '$filter_to' AND shift like '%$filter_shift%'
                                    ) aa group by aa.item_fg_id
                        ) d on a.id = d.item_fg_id
                        LEFT JOIN (
                            SELECT 
                                subs.item_fg_sa_id AS item_fg_id,
                                SUM(d.qty_ng) AS qty_ng
                            FROM (
                                SELECT 
                                    aa.item_fg_id, 
                                    SUM(aa.qty_product) AS qty_ng
                                FROM (
                                    SELECT DISTINCT document, item_fg_id, qty_product 
                                    FROM item_ng 
                                    WHERE trans_date BETWEEN '$filter_from' AND '$filter_to'
                                    AND shift LIKE '%$filter_shift%'
                                    AND created_by != 'PRD01'
                                ) aa 
                                GROUP BY aa.item_fg_id
                            ) d
                            JOIN item_fg_subs subs ON d.item_fg_id = subs.item_fg_id
                            GROUP BY subs.item_fg_sa_id
                        ) ng_map ON a.id = ng_map.item_fg_id
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
                            SELECT 
                                main.id AS item_fg_id,
                                SUM(main.qty_rfg) AS qty_in_checksheet
                            FROM (
                                SELECT 
                                    b.item_fg_id AS id,
                                    SUM(a.qty) AS qty_rfg
                                FROM scan_item_receipts_fg a
                                JOIN checksheets b ON b.number = a.checksheet_number
                                WHERE DATE_FORMAT(b.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' 
                                    AND b.status_subcont='NO' 
                                    AND b.shift LIKE '%$filter_shift%'
                                GROUP BY b.item_fg_id

                                UNION ALL

                                SELECT 
                                    sub.item_fg_sa_id AS id,
                                    SUM(a.qty) AS qty_rfg
                                FROM scan_item_receipts_fg a
                                JOIN checksheets b ON b.number = a.checksheet_number
                                JOIN item_fg_subs sub ON sub.item_fg_id = b.item_fg_id
                                WHERE DATE_FORMAT(b.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' 
                                    AND b.status_subcont='NO' 
                                    AND b.shift LIKE '%$filter_shift%'
                                GROUP BY sub.item_fg_sa_id
                            ) main
                            GROUP BY main.id
                        ) g on a.id = g.item_fg_id
                        LEFT JOIN (
                                    SELECT a.item_fg_id, SUM(a.qty) as qty_in_no_checksheet
                                    FROM scan_item_receipts_fg a
                                    WHERE a.type = 'NBFG'
                                    AND a.packing_date BETWEEN '$filter_from' AND '$filter_to' 
                                    GROUP BY a.item_fg_id
                        ) ga on a.id = ga.item_fg_id
                        LEFT JOIN (
                                    SELECT a.item_fg_id, SUM(a.qty) as initial_in
                                    FROM transaction_fg a
                                    WHERE a.transaction_kind = 'IN'
                                    AND a.transaction_type = 'RECEIPT FG'
                                    AND a.request_date BETWEEN '$filter_from' AND '$filter_to' 
                                    GROUP BY a.item_fg_id
                        ) gb on a.id = gb.item_fg_id
                        LEFT JOIN (
                                    SELECT a.item_fg_id, SUM(a.qty) as qty_in_wip_receipt
                                    FROM wip_receipts a
                                    WHERE a.division = 'MTS'
                                    AND a.trans_date BETWEEN '$filter_from' AND '$filter_to' 
                                    GROUP BY a.item_fg_id
                        ) gc on a.id = gc.item_fg_id
                        LEFT JOIN (
                                    select aa.item_fg_id,sum(aa.qty) as qty_rfg_jasa 
                                    FROM scan_item_receipts_fg aa 
                                    JOIN checksheets ab on aa.checksheet_number = ab.number
                                    where ab.packing_date between '$filter_from' AND '$filter_to' and ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%'
                                    GROUP BY ab.item_fg_id
                        ) h on a.id = h.item_fg_id
                        LEFT JOIN (
                                    select a.item_fg_id,sum(a.qty) as qty_adj_in 
                                    FROM wip_adjustment_fg a
                                    where a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN'
                                    GROUP BY a.item_fg_id
                        ) j2 on a.id = j2.item_fg_id
                        LEFT JOIN (
                                    select a.item_fg_id,sum(a.qty) as qty_adj_out 
                                    FROM wip_adjustment_fg a
                                    where a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT'
                                    GROUP BY a.item_fg_id
                        ) k2 on a.id = k2.item_fg_id
                        LEFT JOIN (
                                    SELECT a.id,
                                        COALESCE(e.qty_balance_wip, 0) + COALESCE(c.qty_actual, 0)  + COALESCE(c2.qty_wip, 0) + COALESCE(f.qty_subcont_jasa, 0) + COALESCE(j.qty_adj_in, 0) - COALESCE(ng_map.qty_ng,0) - COALESCE(g.qty_in_checksheet, 0) - COALESCE(gb.initial_in, 0) - COALESCE(gc.qty_in_wip_receipt, 0) - COALESCE(h.qty_rfg_jasa, 0) - COALESCE(k.qty_adj_out, 0) AS begin_balance
                                    FROM item_fg a
                                    -- qty_balance_wip pada 2025-04-30 (cutoff)
                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_balance_wip
                                        FROM wip_balances_fg
                                        WHERE trans_date = '2025-04-30'
                                        GROUP BY item_fg_id
                                    ) e ON a.id = e.item_fg_id

                                    -- Transaksi setelah cutoff_date sampai < filter_from
                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_actual
                                        FROM output_productions
                                        WHERE trans_date >= '2025-05-01' AND trans_date < '$filter_from'
                                        AND shift LIKE '%$filter_shift%'
                                        GROUP BY item_fg_id
                                    ) c ON a.id = c.item_fg_id

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty_wip) AS qty_wip
                                        FROM output_productions
                                        WHERE trans_date >= '2025-05-01' AND trans_date < '$filter_from'
                                        AND shift LIKE '%$filter_shift%'
                                        GROUP BY item_fg_id
                                    ) c2 ON a.id = c2.item_fg_id

                                    LEFT JOIN (
                                        SELECT aa.item_fg_id, SUM(aa.qty_wo) AS qty_subcont_jasa
                                        FROM (
                                            SELECT DISTINCT ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo
                                            FROM supply_sheets ax
                                            JOIN item_fg ay ON ax.item_fg_id = ay.id
                                            WHERE ax.request_date >= '2025-05-01' AND ax.request_date < '$filter_from'
                                            AND ay.status_subcont = 'YES' AND ay.subcont_type = 'Jasa'
                                        ) aa
                                        GROUP BY aa.item_fg_id
                                    ) f ON a.id = f.item_fg_id

                                    LEFT JOIN (
                                        SELECT 
                                            main.id AS item_fg_id,
                                            SUM(main.qty_rfg) AS qty_in_checksheet
                                        FROM (
                                            SELECT 
                                                b.item_fg_id AS id,
                                                SUM(a.qty) AS qty_rfg
                                            FROM scan_item_receipts_fg a
                                            JOIN checksheets b ON b.number = a.checksheet_number
                                            WHERE DATE_FORMAT(b.packing_date, '%Y-%m-%d') >= '2025-05-01'
                                            AND DATE_FORMAT(b.packing_date, '%Y-%m-%d') < '$filter_from'
                                            AND b.status_subcont = 'NO'
                                            AND b.shift LIKE '%$filter_shift%'
                                            GROUP BY b.item_fg_id

                                            UNION ALL

                                            SELECT 
                                                sub.item_fg_sa_id AS id,
                                                SUM(a.qty) AS qty_rfg
                                            FROM scan_item_receipts_fg a
                                            JOIN checksheets b ON b.number = a.checksheet_number
                                            JOIN item_fg_subs sub ON sub.item_fg_id = b.item_fg_id
                                            WHERE DATE_FORMAT(b.packing_date, '%Y-%m-%d') >= '2025-05-01'
                                            AND DATE_FORMAT(b.packing_date, '%Y-%m-%d') < '$filter_from'
                                            AND b.status_subcont = 'NO'
                                            AND b.shift LIKE '%$filter_shift%'
                                            GROUP BY sub.item_fg_sa_id
                                        ) main
                                        GROUP BY main.id
                                    ) g ON a.id = g.item_fg_id

                                    LEFT JOIN (
                                        SELECT 
                                            subs.item_fg_sa_id AS item_fg_id,
                                            SUM(d.qty_ng) AS qty_ng
                                        FROM (
                                            SELECT 
                                                aa.item_fg_id, 
                                                SUM(aa.qty_product) AS qty_ng
                                            FROM (
                                                SELECT DISTINCT document, item_fg_id, qty_product 
                                                FROM item_ng 
                                                WHERE trans_date >= '2025-05-01' AND trans_date < '$filter_from'
                                                AND shift LIKE '%$filter_shift%'
                                                AND created_by != 'PRD01'
                                            ) aa 
                                            GROUP BY aa.item_fg_id
                                        ) d
                                        JOIN item_fg_subs subs ON d.item_fg_id = subs.item_fg_id
                                        GROUP BY subs.item_fg_sa_id
                                    ) ng_map ON a.id = ng_map.item_fg_id

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_in_no_checksheet
                                        FROM scan_item_receipts_fg
                                        WHERE type = 'NBFG'
                                        AND packing_date >= '2025-05-01'
                                        AND packing_date < '$filter_from'
                                        GROUP BY item_fg_id
                                    ) ga ON a.id = ga.item_fg_id

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS initial_in
                                        FROM transaction_fg
                                        WHERE transaction_kind = 'IN'
                                        AND transaction_type = 'RECEIPT FG'
                                        AND request_date >= '2025-05-01'
                                        AND request_date < '$filter_from'
                                        GROUP BY item_fg_id
                                    ) gb ON a.id = gb.item_fg_id

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_in_wip_receipt
                                        FROM wip_receipts
                                        WHERE division = 'MTS'
                                        AND trans_date >= '2025-05-01'
                                        AND trans_date < '$filter_from'
                                        GROUP BY item_fg_id
                                    ) gc ON a.id = gc.item_fg_id

                                    LEFT JOIN (
                                        SELECT ab.item_fg_id, SUM(aa.qty) AS qty_rfg_jasa
                                        FROM scan_item_receipts_fg aa
                                        JOIN checksheets ab ON aa.checksheet_number = ab.number
                                        WHERE ab.packing_date >= '2025-05-01'
                                        AND ab.packing_date < '$filter_from'
                                        AND ab.subcont_type = 'Jasa'
                                        AND ab.shift LIKE '%$filter_shift%'
                                        GROUP BY ab.item_fg_id
                                    ) h ON a.id = h.item_fg_id

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_adj_in
                                        FROM wip_adjustment_fg
                                        WHERE request_date >= '2025-05-01'
                                        AND request_date < '$filter_from'
                                        AND transaction_type = 'ADJ IN'
                                        GROUP BY item_fg_id
                                    ) j ON a.id = j.item_fg_id

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_adj_out
                                        FROM wip_adjustment_fg
                                        WHERE request_date >= '2025-05-01'
                                        AND request_date < '$filter_from'
                                        AND transaction_type = 'ADJ OUT'
                                        GROUP BY item_fg_id
                                    ) k ON a.id = k.item_fg_id
                        ) i ON a.id = i.id
                        LEFT JOIN divisions j on a.division_id = j.id
                        LEFT JOIN (SELECT item_fg_id, currency, price from standard_price_fg where '$filter_from' >= `start_date` and '$filter_to' <= `end_date`) k on a.id = k.item_fg_id
                        WHERE a.type != 'RM' and a.id LIKE '%$filter_items%' AND a.division_id LIKE '%$filter_division%' AND a.status = 0 AND a.id != 'BPIFG-INJ08240009'
                        ORDER BY a.number
        ";

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
            <h3 style="margin:0;">INVENTORY WIP</h3>
            <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
        </center>
        <br>
            <table id="customers" border="1" style="font-size: 11px;">
                 <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2" colspan="2">Product No</th>
                    <th rowspan="2">Product Name</th>
                    <th rowspan="2">Uom</th>
                    <th rowspan="2">Division</th>
                    <th rowspan="2">Product Family</th>
                    <th rowspan="2">Currency</th>
                    <th rowspan="2">Price</th>
                    <th rowspan="2">Rate</th>
                    <th colspan="3" width="100">Begin</th>
                    <th colspan="3" width="100">In</th>
                    <th colspan="3" width="100">Out</th>
                    <th colspan="3" width="100">Balance</th>
                </tr>
                <tr>
                    <th width="80">QTY</th>
                    <th width="80">PRICE</th>
                    <th width="80">AMOUNT</th>

                    <th width="80">QTY</th>
                    <th width="80">PRICE</th>
                    <th width="80">AMOUNT</th>

                    <th width="80">QTY</th>
                    <th width="80">PRICE</th>
                    <th width="80">AMOUNT</th>

                    <th width="80">QTY</th>
                    <th width="80">PRICE</th>
                    <th width="80">AMOUNT</th>
                </tr';
        $no = 1;
        $totalBeginStock = 0;
        $totalBeginAmount = 0;
        $totalIn = 0;
        $totalAmountIn = 0;
        $totalOut = 0;
        $totalAmountOut = 0;
        $totalEndingStock = 0;
        $totalAmountEndingStock = 0;

        foreach ($records as $record) {
            $item_fg_id = $record->id;
            $currency = @$record->currency;
            $rate = 1;

            if ($currency == 'USD') {
                if (empty($receipt_date)) {
                    $rate = 0;
                } else {
                    $this->db->where('currency_from', 'USD');
                    $this->db->where('start_date <=', $receipt_date);
                    $this->db->where('end_date >=', $receipt_date);
                    $query = $this->db->get('standard_exchange_rates');

                    if ($query->num_rows() > 0) {
                        $rate = $query->row()->middle;
                    }
                }
            }
            $totalBeginStock += @$record->begin_balance;
            $totalBeginAmount += @$record->price * $rate * @$record->begin_balance;
            $totalIn += @$record->qty_in;
            $totalAmountIn += @$record->price * $rate * @$record->qty_in;
            $totalOut += @$record->qty_out;
            $totalAmountOut += @$record->price * $rate * @$record->qty_out;
            $totalEndingStock += @(@$record->begin_balance + $record->qty_in) - $record->qty_out;
            $totalAmountEndingStock += ((@$record->price * $rate) * @$record->qty_in) + ((@$record->price * $rate) * @$record->begin_balance) - ((@$record->price * $rate) * @$record->qty_out);


            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="2" style="mso-number-format:\@;">' . $record->number . '</td>
                            <td style="mso-number-format:\@;">' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->division . '</td>
                            <td>FINISH GOOD</td>
                            <td style="text-align:center;">' . $record->currency . '</td>
                            <td style="text-align:right;">' . number_format($record->price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($rate, 2) . '</td>

                            <td style="text-align:right;">' . number_format(@$record->begin_balance, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->price * $rate) * $record->begin_balance, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->price * $rate) * $record->qty_in, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->price * $rate) * $record->qty_out, 2) . '</td>

                            <td style="text-align:right;">' . number_format((@$record->begin_balance + $record->qty_in) - $record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format((@($record->price * $rate) * $record->qty_in) + (($record->price * $rate) * $record->begin_balance) - (($record->price * $rate) * $record->qty_out), 2) . '</td>
                        
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="22" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
                $html .= '  <tr>
                                <th rowspan="2" width="20"></th>
                                <th rowspan="2" width="20">No</th>
                                <th rowspan="2">Trans Type</th>
                                <th rowspan="2" colspan="2">Trans Date</th>
                                <th rowspan="2" colspan="2">WO / DOC</th>
                                <th rowspan="2">CCY</th>
                                <th rowspan="2">Price</th>
                                <th rowspan="2">Rate</th>
                                <th colspan="3">Begin</th>
                                <th colspan="3">In</th>
                                <th colspan="3">Out</th>
                                <th colspan="3">Balance</th>
                            </tr>
                            <tr>
                                <th>QTY</th>
                                <th>PRICE</th>
                                <th>AMOUNT</th>

                                <th>QTY</th>
                                <th>PRICE</th>
                                <th>AMOUNT</th>

                                <th>QTY</th>
                                <th>PRICE</th>
                                <th>AMOUNT</th>

                                <th>QTY</th>
                                <th>PRICE</th>
                                <th>AMOUNT</th>
                            </tr>';
                $nod = 1;
                $begin = @$record->begin_balance;
                $price = @$record->price;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;
                $rate = 1;

                if ($currency == 'USD') {
                    if (empty($receipt_date)) {
                        $rate = 0;
                    } else {
                        $this->db->where('currency_from', 'USD');
                        $this->db->where('start_date <=', $receipt_date);
                        $this->db->where('end_date >=', $receipt_date);
                        $query = $this->db->get('standard_exchange_rates');

                        if ($query->num_rows() > 0) {
                            $rate = $query->row()->middle;
                        }
                    }
                }

                //IN--------------------------------
                $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$item_fg_id' and trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%'");

                $dataSubcontsJasas = $this->crud->query("
                                                select aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                        select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                        FROM supply_sheets ax 
                                                        join item_fg ay on ax.item_fg_id=ay.id 
                                                        where ax.item_fg_id='$item_fg_id' and ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa'
                                                ) aa group by aa.workorder,aa.request_date,aa.item_fg_id
                ");

                $dataAdjIns = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$item_fg_id' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN'
                ");
                //----------------------------------

                //OUT-------------------------------
                $receipts = $this->crud->query("
                                                SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                                                FROM scan_item_receipts_fg f
                                                JOIN checksheets e ON e.number = f.checksheet_number
                                                LEFT JOIN users c ON f.created_by = c.username
                                                WHERE (
                                                    e.item_fg_id = '$item_fg_id'
                                                    OR e.item_fg_id IN (
                                                        SELECT item_fg_id FROM item_fg_subs WHERE item_fg_sa_id = '$item_fg_id'
                                                    )
                                                )
                                                AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                                                AND e.status_subcont = 'NO'
                                                AND e.shift LIKE '%$filter_shift%'
                                            ");
                
                // $receiptsNB = $this->crud->query("
                //                                 SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
                //                                 FROM new_barcode_fg a
                //                                 LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                //                                 LEFT JOIN users u ON f.created_by = u.username
                //                                 WHERE a.item_fg_id = '$item_fg_id'  AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                $receiptsWIP = $this->crud->query("
                                                SELECT a.*, u.name as username, 'WIP RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                                                FROM wip_receipts a
                                                LEFT JOIN users u ON a.created_by = u.username
                                                WHERE a.item_fg_id = '$item_fg_id' AND a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");
                $transFgs = $this->crud->query("
                                                SELECT *
                                                FROM transaction_fg a
                                                WHERE a.transaction_kind = 'IN'  AND a.transaction_type = 'RECEIPT FG' AND a.item_fg_id = '$item_fg_id' AND a.request_date BETWEEN '$filter_from' and '$filter_to'");
                
                $dataRfgSubcontsJasas = $this->crud->query("
                                                select ab.packing_date as trans_date,ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg 
                                                FROM scan_item_receipts_fg aa 
                                                JOIN checksheets ab on aa.checksheet_number = ab.number
                                                where aa.item_fg_id='$item_fg_id' and ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%'
                                                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
                ");

                $dataAdjOuts = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$item_fg_id' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT'
                ");


                // Proses data berdasarkan tanggal
                $all_data = [];
                //IN--------------------------------
                foreach ($dataActualProductions as $actualProduction) {
                    $all_data[] = [
                        'type' => 'ACTUAL PRODUCTION',
                        'date' => $actualProduction->trans_date,
                        'wo_no' => $actualProduction->wo_no,
                        'qty_in' => $actualProduction->qty,
                        'qty_out' => 0,
                    ];
                }

                foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                    $all_data[] = [
                        'type' => 'SUBCONTS JASA',
                        'date' => $dataSubcontsJasa->request_date,
                        'wo_no' => $dataSubcontsJasa->workorder,
                        'qty_in' => $dataSubcontsJasa->qty_subcont_jasa,
                        'qty_out' => 0,
                    ];
                }

                foreach ($dataActualProductions as $actualProduction) {
                    $all_data[] = [
                        'type' => 'ACTUAL PRODUCTION WIP',
                        'date' => $actualProduction->trans_date,
                        'wo_no' => $actualProduction->wo_no,
                        'qty_in' => $actualProduction->qty_wip,
                        'qty_out' => 0,
                    ];
                }

                
                foreach ($dataAdjIns as $dataAdjIn) {
                    $all_data[] = [
                        'type' => $dataAdjIn->transaction_type,
                        'date' => $dataAdjIn->request_date,
                        'wo_no' => $dataAdjIn->request_no,
                        'qty_in' => $dataAdjIn->qty,
                        'qty_out' => 0,
                    ];
                }
                //------------------------------------------------

                //OUT---------------------------------------------
                foreach ($receipts  as $receipt) {
                    $all_data[] = [
                        'type' => $receipt->receipt_type,
                        'date' => $receipt->trans_date,
                        'wo_no' => $receipt->wo_no,
                        'qty_in' => 0,
                        'qty_out' => $receipt->qty,
                    ];
                }

                // foreach ($receiptsNB as $receiptNB) {
                //     $all_data[] = [
                //         'type' => $receiptNB->receipt_type,
                //         'date' => $receiptNB->trans_date,
                //         'wo_no' => $receiptNB->wo_no,
                //         'qty_in' => 0,
                //         'qty_out' => $receiptNB->qty,
                //     ];
                // }

                foreach ($receiptsWIP as $receiptWIP) {
                    $all_data[] = [
                        'type' => $receiptWIP->receipt_type,
                        'date' => $receiptWIP->trans_date,
                        'wo_no' => $receiptWIP->wo_no,
                        'qty_in' => 0,
                        'qty_out' => $receiptWIP->qty,
                    ];
                }

                foreach ($transFgs as $transFg) {
                    $all_data[] = [
                        'type' => 'TRANSACTION FG',
                        'date' => $transFg->request_date,
                        'wo_no' => $transFg->request_no,
                        'qty_in' => 0,
                        'qty_out' => $transFgs->qty,
                    ];
                }

                foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                    $all_data[] = [
                        'type' => 'RFG SUBCONTS JASA',
                        'date' => $dataRfgSubcontsJasa->trans_date,
                        'wo_no' => $dataRfgSubcontsJasa->wo_no,
                        'qty_in' => 0,
                        'qty_out' => $dataRfgSubcontsJasa->qty_rfg,
                    ];
                }

                foreach ($dataAdjOuts as $dataAdjOut) {
                    $all_data[] = [
                        'type' => $dataAdjOut->transaction_type,
                        'date' => $dataAdjOut->request_date,
                        'wo_no' => $dataAdjOut->request_no,
                        'qty_in' => 0,
                        'qty_out' => $dataAdjOut->qty,
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
                    $balance += $data['qty_in'] - $data['qty_out'];
                    $html .= '  <tr>
                                    <td></td>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td>' . $data['type'] . '</td>
                                    <td colspan="2">' . $data['date'] . '</td>
                                    <td colspan="2">' . $data['wo_no'] . '</td>
                                    <td style="text-align:center;">' . $currency . '</td>
                                    <td style="text-align:right;">' . number_format($price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($rate, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(($rate * $price) * $begin, 2) . '</td>

                                    <td style="text-align:right;">' . number_format($data['qty_in'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(($rate * $price) * $data['qty_in'], 2) . '</td>

                                    <td style="text-align:right;">' . number_format($data['qty_out'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(($rate * $price) * $data['qty_out'], 2) . '</td>

                                    <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format(($rate * $price) * $balance, 2) . '</td>
                                </tr>';

                    $begin = $balance;
                    $nod++;
                }
            }
            $no++;
        }
        $html .= '<tr>
            <td colspan="10" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;"><b>' . number_format($totalBeginStock, 2) . '</b></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"><b>' . number_format($totalBeginAmount, 2) . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalIn, 2) . '</b></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"><b>' . number_format($totalAmountIn, 2) . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalOut, 2) . '</b></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"><b>' . number_format($totalAmountOut, 2) . '</b></td>
            <td style="text-align:right;"><b>' . number_format($totalEndingStock, 2) . '</b></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"><b>' . number_format($totalAmountEndingStock, 2) . '</b></td>
        </tr>';
        $html .= '</table></body></html>';
        echo $html;
    }
}
