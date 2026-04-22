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

    public function readWO()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('supply_sheets', ["workorder" => $post]);
        echo json_encode($send);
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
        $filter_workorder = $this->input->get("filter_workorder");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $exclude_ids = [
            'BPIFG-INJ08240009',
            'BPIFG-INJ01250007',
            'BPIFG-INJ08240029',
            'BPIFG-INJ08240027',
            'BPIFG-INJ08240024',
            'BPIFG-INJ08240030',
            'BPIFG-INJ08240026',
            'BPIFG-INJ01250013',
            'BPIFG-INJ08240031',
            'BPIFG-INJ08240025',
            'BPIFG-INJ08240028',
            'BPIFG-INJ01250012'
        ];

        $exclude_str = "'" . implode("','", $exclude_ids) . "'";

        $where_extra = "";

        // Filter Division
        if (!empty($filter_division)) {
            $where_extra .= " AND a.division_id LIKE '%$filter_division%'";
        }
    
        // Filter Items (langsung atau dari WO)
        if (!empty($filter_items)) {
            $where_extra .= " AND a.id LIKE '%$filter_items%'";
        } else {
            // Tidak ada filter item, cek apakah workorder diisi
            if (!empty($filter_workorder)) {
                $items_from_wo = $this->crud->query("
                    SELECT DISTINCT a.item_fg_id 
                    FROM supply_sheets a 
                    WHERE a.workorder LIKE '%$filter_workorder%'
                ");

                if (count($items_from_wo) > 0) {
                    $ids = implode(",", array_map(function($row) {
                        return "'{$row->item_fg_id}'";
                    }, $items_from_wo));
                    $where_extra .= " AND a.id IN ($ids)";
                } else {
                    // Workorder diisi tapi tidak ada item ditemukan
                    $where_extra .= " AND a.id IN ('__NOT_FOUND__')";
                }
            } else {
                // Tidak ada filter division, items, dan workorder
                // => tampilkan semua item
                $where_extra .= "";
            }
        }

        $query_main = "
                        select a.id,
                        a.number,
                        a.name, 
                        COALESCE(b.qty_wo,0) as qty_wo,
                        COALESCE(i.begin_balance,0) as begin_balance,
                        COALESCE(c.qty_actual,0) as qty_actual,
                        COALESCE(c2.qty_wip,0) as qty_wip,
                        COALESCE(outmap.qty_output, 0) AS qty_output,
                        COALESCE(d.qty_ng,0) as qty_ng,
                        COALESCE(ng_map.qty_ng,0) as qty_ng_sa,
                        COALESCE((COALESCE(c.qty_actual,0)+COALESCE(d.qty_ng,0)+COALESCE(c2.qty_wip,0)),0) as total_production,
                        COALESCE(f.qty_subcont_jasa,0) as subconts_jasa,
                        COALESCE(j.qty_adj_in,0) as qty_adj_in,
                        COALESCE(g.qty_in_checksheet,0) + COALESCE(gb.initial_in,0) + COALESCE(gc.qty_in_wip_receipt,0) as qty_rfg,
                        COALESCE(h.qty_rfg_jasa,0) as rfg_jasa,
                        COALESCE(k.qty_adj_out,0) as qty_adj_out,
                        COALESCE(k2.qty_ng_wip,0) as qty_ng_wip,
                        COALESCE((COALESCE(i.begin_balance,0)) + COALESCE(c.qty_actual,0) + COALESCE(f.qty_subcont_jasa,0) +COALESCE(j.qty_adj_in,0) +COALESCE(c2.qty_wip,0) - COALESCE(ng_map.qty_ng,0) - COALESCE(g.qty_in_checksheet,0) - COALESCE(gb.initial_in,0) - COALESCE(gc.qty_in_wip_receipt,0) - COALESCE(h.qty_rfg_jasa,0)- COALESCE(k.qty_adj_out,0) - COALESCE(k2.qty_ng_wip,0) - COALESCE(outmap.qty_output, 0), 0) as ending_balance
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
                            SELECT 
                                sub.item_fg_sa_id AS item_fg_id,
                                SUM(
                                    COALESCE(p.qty_actual, 0) + 
                                    COALESCE(p.qty_wip, 0)
                                ) AS qty_output
                            FROM item_fg_subs sub
                            
                            LEFT JOIN (
                                SELECT 
                                    item_fg_id,
                                    SUM(qty) AS qty_actual,
                                    SUM(qty_wip) AS qty_wip
                                FROM output_productions
                                WHERE trans_date BETWEEN '$filter_from' AND '$filter_to'
                                AND shift LIKE '%$filter_shift%'
                                GROUP BY item_fg_id
                            ) p ON sub.item_fg_id = p.item_fg_id   -- PARENT
                            
                            GROUP BY sub.item_fg_sa_id
                        ) outmap ON a.id = outmap.item_fg_id
                        LEFT JOIN (
                                    select aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                            select distinct document,item_fg_id, qty_product FROM  item_ng where trans_date between '$filter_from' AND '$filter_to' AND shift like '%$filter_shift%' AND kind LIKE 'Ng Process Production'
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
                                    AND kind LIKE 'Ng Process Production'
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
                        ) j on a.id = j.item_fg_id
                        LEFT JOIN (
                                    select a.item_fg_id,sum(a.qty) as qty_adj_out 
                                    FROM wip_adjustment_fg a
                                    where a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT'
                                    GROUP BY a.item_fg_id
                        ) k on a.id = k.item_fg_id
                        LEFT JOIN (
                                    select a.item_fg_id,sum(a.qty) as qty_ng_wip 
                                    FROM wip_adjustment_fg a
                                    where a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='NG WIP'
                                    GROUP BY a.item_fg_id
                        ) k2 on a.id = k2.item_fg_id
                        LEFT JOIN (
                                    SELECT a.id,
                                        COALESCE(e.qty_balance_wip, 0) + COALESCE(c.qty_actual, 0) + COALESCE(c2.qty_wip, 0) + COALESCE(f.qty_subcont_jasa, 0) + COALESCE(j.qty_adj_in, 0) - COALESCE(ng_map.qty_ng,0) - COALESCE(g.qty_in_checksheet, 0) - COALESCE(gb.initial_in, 0) - COALESCE(gc.qty_in_wip_receipt, 0) - COALESCE(h.qty_rfg_jasa, 0) - COALESCE(k.qty_adj_out, 0) - COALESCE(k2.qty_ng_wip, 0) - COALESCE(outmap.qty_output, 0) AS begin_balance
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
                                        SELECT 
                                            sub.item_fg_sa_id AS item_fg_id,
                                            SUM(
                                                COALESCE(p.qty_actual, 0) +
                                                COALESCE(p.qty_wip, 0)
                                            ) AS qty_output
                                        FROM item_fg_subs sub
                                        
                                        LEFT JOIN (
                                            SELECT 
                                                item_fg_id,
                                                SUM(qty) AS qty_actual,
                                                SUM(qty_wip) AS qty_wip
                                            FROM output_productions
                                            WHERE trans_date >= '2025-05-01'
                                            AND trans_date < '$filter_from'
                                            AND shift LIKE '%$filter_shift%'
                                            GROUP BY item_fg_id
                                        ) p ON sub.item_fg_id = p.item_fg_id   -- PARENT
                                        
                                        GROUP BY sub.item_fg_sa_id
                                    ) outmap ON a.id = outmap.item_fg_id

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
                                                AND kind LIKE 'Ng Process Production'
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

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty) AS qty_ng_wip
                                        FROM wip_adjustment_fg
                                        WHERE request_date >= '2025-05-01'
                                        AND request_date < '$filter_from'
                                        AND transaction_type = 'NG WIP'
                                        GROUP BY item_fg_id
                                    ) k2 ON a.id = k2.item_fg_id
                                ) i ON a.id = i.id
                        WHERE a.type != 'RM'
                        AND a.status = 0
                        AND a.division_id != 'DIV02'
                        $where_extra
                        AND a.id NOT IN ($exclude_str)
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
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2" colspan="3">Product No</th>
                    <th rowspan="2" colspan="2">Product Name</th>
                    <th rowspan="2" colspan="2">Total WO Qty</th>
                    <th rowspan="2">Begin Balance</th>
                    <th colspan="2">Output Production</th>
                    <th rowspan="2">NG Process</th>
                    <th rowspan="2">Total Production</th>
                    <th rowspan="2">SubCont Jasa</th>
                    <th rowspan="2">ADJ IN</th>
                    <th rowspan="2">NG ASSY</th>
                    <th rowspan="2">NG WIP</th>
                    <th rowspan="2">OUTPUT ASSY</th>
                    <th rowspan="2">RFG</th>
                    <th rowspan="2">RFG SubCont Jasa</th>
                    <th rowspan="2">ADJ OUT</th>
                    <th rowspan="2">Ending Balance</th>
                </tr>
                <tr>
                    <th>Qty FG</th>
                    <th>Qty WIP</th>
                </tr>';
        $no = 1;

        $totalBeginBalance = 0;
        $totalQtyAct = 0;
        $totalQtyWip = 0;
        $totalQtyNg = 0;
        $totalTotalProduction = 0;
        $totalSubcontsJasa = 0;
        $totalQtyAdjIn = 0;
        $totalQtyNgSa = 0;
        $totalQtyNgWip = 0;
        $totalQtyOutput = 0;
        $totalQtyRfg = 0;
        $totalQtyJasa = 0;
        $totalQtyAdjOut = 0;
        $totalEndingBalance = 0;

        foreach ($records as $record) {
            $item_fg_id = $record->id;

            $totalBeginBalance += @$record->begin_balance;
            $totalQtyAct += @$record->qty_actual;
            $totalQtyWip += @$record->qty_wip;
            $totalQtyNg += @$record->qty_ng;
            $totalTotalProduction += @$record->total_production;
            $totalSubcontsJasa += @$record->subconts_jasa;
            $totalQtyAdjIn += @$record->qty_adj_in;
            $totalQtyNgSa += @$record->qty_ng_sa;
            $totalQtyNgWip += @$record->qty_ng_wip;
            $totalQtyOutput += @$record->qty_output;
            $totalQtyRfg += @$record->qty_rfg;
            $totalQtyJasa += @$record->rfg_jasa;
            $totalQtyAdjOut += @$record->qty_adj_out;
            $totalEndingBalance += @$record->ending_balance;

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3" style="mso-number-format:\@;">' . $record->number . '</td>
                            <td colspan="2" style="mso-number-format:\@;">' . $record->name . '</td>
                            <td colspan="2" style="text-align:right;">' . number_format($record->qty_wo, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->begin_balance, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_actual, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_wip, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_ng, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->total_production, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->subconts_jasa, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_adj_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_ng_sa, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_ng_wip, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_output, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_rfg, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->rfg_jasa, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_adj_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->ending_balance, 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL" && $filter_workorder !="" && $filter_items !="") {
                $html .= '  <tr>
                                <td colspan="23" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
                $html .= '  <tr>
                                <th rowspan="2" width="20"></th>
                                <th rowspan="2" width="20">No</th>
                                <th rowspan="2" >Product No</th>
                                <th rowspan="2" >Product Name</th>
                                <th rowspan="2" >Type</th>
                                <th rowspan="2" >Trans Date</th>
                                <th rowspan="2" >WO / Doc</th>
                                <th rowspan="2" >WO Qty</th> 
                                <th rowspan="2" >Begin Balance</th>
                                <th colspan="2" >Output Production</th>
                                <th rowspan="2" >NG</th>
                                <th rowspan="2" >Total Production</th>
                                <th rowspan="2" >SubCont Jasa</th>
                                <th rowspan="2" >ADJ IN</th>
                                <th rowspan="2" >RFG</th>
                                <th rowspan="2" >RFG SubCont Jasa</th>
                                <th rowspan="2" >ADJ OUT</th>
                                <th rowspan="2" >Ending Balance</th>
                           </tr>
                            <tr>
                                <th>Qty FG</th>
                                <th>Qty WIP</th>
                            </tr>';

                $nod = 1;
                $begin = @$record->begin_balance;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;

                $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$item_fg_id' and trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%' AND wo_no like '%$filter_workorder%'");

                $dataNgs = $this->crud->query("
                                                select aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                        select distinct trans_date,document,item_fg_id, qty_product FROM item_ng where item_fg_id='$item_fg_id' and trans_date between '$filter_from' and '$filter_to' AND shift like '%$filter_shift%' AND document like '%$filter_workorder%' AND kind LIKE 'Ng Process Production'
                                                ) aa group by aa.document,aa.trans_date,aa.item_fg_id
                ");

                $dataSubcontsJasas = $this->crud->query("
                                                select aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                        select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                        FROM supply_sheets ax 
                                                        join item_fg ay on ax.item_fg_id=ay.id 
                                                        where ax.item_fg_id='$item_fg_id' and ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa' AND ax.workorder like '%$filter_workorder%'
                                                ) aa group by aa.workorder,aa.request_date,aa.item_fg_id
                ");

                $dataRfgSubcontsJasas = $this->crud->query("
                                                select ab.packing_date as trans_date,ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg 
                                                FROM scan_item_receipts_fg aa 
                                                JOIN checksheets ab on aa.checksheet_number = ab.number
                                                where aa.item_fg_id='$item_fg_id' and ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%'
                                                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
                ");

                $dataAdjIns = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$item_fg_id' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN' AND request_no like '%$filter_workorder%'
                ");

                $dataAdjOuts = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$item_fg_id' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT' AND request_no like '%$filter_workorder%'
                ");

                $receipts = $this->crud->query("
                                                SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                                                FROM scan_item_receipts_fg f
                                                JOIN checksheets e ON e.number = f.checksheet_number
                                                LEFT JOIN users c ON f.created_by = c.username
                                                WHERE e.item_fg_id = '$item_fg_id'  and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' and e.status_subcont='NO' and e.shift like '%$filter_shift%' and f.wo_no like '%$filter_workorder%'");

                // $receiptsNB = $this->crud->query("
                //                                 SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
                //                                 FROM new_barcode_fg a
                //                                 LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                //                                 LEFT JOIN users u ON f.created_by = u.username
                //                                 WHERE a.item_fg_id = '$item_fg_id'  AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and f.wo_no like '%$filter_workorder%'");

                $receiptsWIP = $this->crud->query("
                                                SELECT a.*, u.name as username, 'WIP RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                                                FROM wip_receipts a
                                                LEFT JOIN users u ON a.created_by = u.username
                                                WHERE a.item_fg_id = '$item_fg_id' AND a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.wo_no like '%$filter_workorder%'");

                $transFgs = $this->crud->query("
                                                SELECT *
                                                FROM transaction_fg a
                                                WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND a.item_fg_id = '$item_fg_id' AND a.request_date BETWEEN '$filter_from' and '$filter_to' and a.request_no like '%$filter_workorder%'");

                // Proses data berdasarkan tanggal
                $all_data = [];

                foreach ($dataActualProductions as $actualProduction) {//ada wo_no
                    $all_data[] = [
                        'type' => 'OUTPUT PRODUCTION',
                        'date' => $actualProduction->trans_date,
                        'wo_no' => $actualProduction->wo_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => $actualProduction->qty,
                        'qty_wip' => $actualProduction->qty_wip,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataNgs as $dataNg) {
                    $all_data[] = [
                        'type' => 'PRODUCT NG',
                        'date' => $dataNg->trans_date,
                        'wo_no' => $dataNg->document,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => $dataNg->qty_ng,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                    $all_data[] = [
                        'type' => 'SUBCONTS JASA',
                        'date' => $dataSubcontsJasa->request_date,
                        'wo_no' => $dataSubcontsJasa->workorder,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => $dataSubcontsJasa->qty_subcont_jasa,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($receipts as $receipt) {
                    $all_data[] = [
                        'type' => $receipt->receipt_type,
                        'date' => $receipt->trans_date,
                        'wo_no' => $receipt->wo_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $receipt->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                // foreach ($receiptsNB as $receiptNB) {
                //     $all_data[] = [
                //         'type' => $receiptNB->receipt_type,
                //         'date' => $receiptNB->trans_date,
                //         'wo_no' => $receiptNB->wo_no,
                //         'wo_qty' => $record->qty_wo,
                //         'actual_production' => 0,
                //         'qty_wip' => 0,
                //         'ng' => 0,
                //         'subconts_jasa' => 0,
                //         'qty_adj_in' => 0,
                //         'rfg' => $receiptNB->qty,
                //         'rfg_subconts_jasa' => 0,
                //         'qty_adj_out' => 0,
                //     ];
                // }

                foreach ($receiptsWIP as $receiptWIP) {
                    $all_data[] = [
                        'type' => $receiptWIP->receipt_type,
                        'date' => $receiptWIP->trans_date,
                        'wo_no' => $receiptWIP->wo_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $receiptWIP->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($transFgs as $transFg) {
                    $all_data[] = [
                        'type' => 'TRANSACTION FG',
                        'date' => $transFg->request_date,
                        'wo_no' => $transFg->request_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $transFg->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                    $all_data[] = [
                        'type' => 'RFG SUBCONTS JASA',
                        'date' => $dataRfgSubcontsJasa->trans_date,
                        'wo_no' => $dataRfgSubcontsJasa->wo_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => $dataRfgSubcontsJasa->qty_rfg,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataAdjIns  as $dataAdjIn) {
                    $all_data[] = [
                        'type' => $dataAdjIn->transaction_type,
                        'date' => $dataAdjIn->request_date,
                        'wo_no' => $dataAdjIn->request_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => $dataAdjIn->qty,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataAdjOuts  as $dataAdjOut) {
                    $all_data[] = [
                        'type' => $dataAdjOut->transaction_type,
                        'date' => $dataAdjOut->request_date,
                        'wo_no' => $dataAdjOut->request_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => $dataAdjOut->qty,
                    ];
                }

                // Urutkan data berdasarkan tanggal
                usort($all_data, function ($a, $b) {
                    return strtotime($a['date']) - strtotime($b['date']);
                });

                $qty_wo_read = $this->crud->query("select qty FROM production_schedules where item_fg_id='$item_fg_id' and wo_no like '%$filter_workorder%'");
                $qty_wo = 0;
                if (!empty($qty_wo_read) && isset($qty_wo_read[0]->qty)) {
                    $qty_wo = $qty_wo_read[0]->qty;
                }

                $total_actual_production = 0;
                $total_qty_wip = 0;
                $total_ng = 0;
                $total_subconts_jasa = 0;
                $total_qty_adj_in = 0;
                $total_rfg = 0;
                $total_rfg_subconts_jasa = 0;
                $total_qty_adj_out = 0;

                foreach ($all_data as $data) {
                    $total_actual_production += $data['actual_production'];
                    $total_qty_wip += $data['qty_wip'];
                    $total_ng += $data['ng'];
                    $total_subconts_jasa += $data['subconts_jasa'];
                    $total_qty_adj_in += $data['qty_adj_in'];
                    $total_rfg += $data['rfg'];
                    $total_rfg_subconts_jasa += $data['rfg_subconts_jasa'];
                    $total_qty_adj_out += $data['qty_adj_out'];
                }

                $total_production = $total_actual_production + $total_qty_wip + $total_ng;

                // Buat header total sebelum data detail
                $html .= '<tr style="background:#EEE; font-weight:bold;">
                            <td></td>
                            <td style="text-align:center">-</td>
                            <td>' . $record->number  . '</td>
                            <td>' . $record->name  . '</td>
                            <td>-</td>
                            <td>-</td>
                            <td>' . $data['wo_no']  . '</td>
                            <td style="text-align:right;">' . number_format($qty_wo, 2)  . '</td>
                            <td style="text-align:right;">' . number_format($begin, 2)  . '</td>
                            <td style="text-align:right;">' . number_format($total_actual_production, 2) . '</td>
                            <td style="text-align:right;">' . number_format($total_qty_wip, 2) . '</td>
                            <td style="text-align:right;">' . number_format($total_ng, 2) . '</td>
                            <td style="text-align:right;">' . number_format($total_production, 2) . '</td>
                            <td style="text-align:right;">' . number_format($total_subconts_jasa, 2) . '</td>
                            <td style="text-align:right;">' . number_format($total_qty_adj_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($total_rfg, 2) . '</td>
                            <td style="text-align:right;">' . number_format($total_rfg_subconts_jasa, 2) . '</td>
                            <td style="text-align:right;">' . number_format($total_qty_adj_out, 2) . '</td>
                            <td></td>
                        </tr>';

                // Generate HTML
                $nod = 1;
                $balance = $begin;
                foreach ($all_data as $data) {
                    $total_production = $data['actual_production'] + $data['qty_wip'] + $data['ng'];
                    $balance += $data['actual_production'] + $data['qty_wip'] + $data['subconts_jasa'] + $data['qty_adj_in'] - $data['rfg'] - $data['rfg_subconts_jasa'] - $data['qty_adj_out'];
                    $html .= '  <tr>
                                    <td></td>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td>' . $record->number  . '</td>
                                    <td>' . $record->name  . '</td>
                                    <td>' . $data['type']  . '</td>
                                    <td>' . $data['date']  . '</td>
                                    <td>' . $data['wo_no']  . '</td>
                                    <td style="text-align:right;">' . number_format($qty_wo, 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($begin, 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['actual_production'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['qty_wip'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['ng'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($total_production, 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['subconts_jasa'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['qty_adj_in'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['rfg'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['rfg_subconts_jasa'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['qty_adj_out'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                </tr>';

                    $begin = $balance;
                    $nod++;
                }
            } elseif ($filter_display == "DETAIL" && $filter_workorder !="") {
                $wos = $this->crud->query("
                    SELECT DISTINCT a.item_fg_id, b.number, b.name , a.qty_wo
                    FROM supply_sheets a 
                    JOIN item_fg b ON a.item_fg_id = b.id 
                    WHERE a.workorder LIKE '%$filter_workorder%'
                ");

                foreach ($wos as $record) {
                    $item_fg_id = $record->item_fg_id;

                    $html .= '  <tr>
                                <td colspan="23" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
                    $html .= '  <tr>
                                    <th rowspan="2" width="20"></th>
                                    <th rowspan="2" width="20">No</th>
                                    <th rowspan="2" >Product No</th>
                                    <th rowspan="2" >Product Name</th>
                                    <th rowspan="2" >Type</th>
                                    <th rowspan="2" >Trans Date</th>
                                    <th rowspan="2" >WO / Doc</th>
                                    <th rowspan="2" >WO Qty</th> 
                                    <th rowspan="2" >Begin Balance</th>
                                    <th colspan="2" >Output Production</th>
                                    <th rowspan="2" >NG</th>
                                    <th rowspan="2" >Total Production</th>
                                    <th rowspan="2" >SubCont Jasa</th>
                                    <th rowspan="2" >ADJ IN</th>
                                    <th rowspan="2" >RFG</th>
                                    <th rowspan="2" >RFG SubCont Jasa</th>
                                    <th rowspan="2" >ADJ OUT</th>
                                    <th rowspan="2" >Ending Balance</th>
                            </tr>
                                <tr>
                                    <th>Qty FG</th>
                                    <th>Qty WIP</th>
                                </tr>';

                    $nod = 1;
                    $begin = @$record->begin_balance;
                    $in_qty = 0;
                    $end_qty = 0;
                    $balance = 0;

                    $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$item_fg_id' and trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%' AND wo_no like '%$filter_workorder%'");

                    $dataNgs = $this->crud->query("
                                                    select aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                            select distinct trans_date,document,item_fg_id, qty_product FROM item_ng where item_fg_id='$item_fg_id' and trans_date between '$filter_from' and '$filter_to' AND shift like '%$filter_shift%' AND document like '%$filter_workorder%' AND kind LIKE 'Ng Process Production'
                                                    ) aa group by aa.document,aa.trans_date,aa.item_fg_id
                    ");

                    $dataSubcontsJasas = $this->crud->query("
                                                    select aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                            select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                            FROM supply_sheets ax 
                                                            join item_fg ay on ax.item_fg_id=ay.id 
                                                            where ax.item_fg_id='$item_fg_id' and ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa' AND ax.workorder like '%$filter_workorder%'
                                                    ) aa group by aa.workorder,aa.request_date,aa.item_fg_id
                    ");

                    $dataRfgSubcontsJasas = $this->crud->query("
                                                select ab.packing_date as trans_date,ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg 
                                                FROM scan_item_receipts_fg aa 
                                                JOIN checksheets ab on aa.checksheet_number = ab.number
                                                where aa.item_fg_id='$item_fg_id' and ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%'
                                                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
                    ");

                    $dataAdjIns = $this->crud->query("
                                                    select *
                                                    FROM wip_adjustment_fg a
                                                    where a.item_fg_id='$item_fg_id' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN' AND request_no like '%$filter_workorder%'
                    ");

                    $dataAdjOuts = $this->crud->query("
                                                    select *
                                                    FROM wip_adjustment_fg a
                                                    where a.item_fg_id='$item_fg_id' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT' AND request_no like '%$filter_workorder%'
                    ");

                    $receipts = $this->crud->query("
                                                    SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                                                    FROM scan_item_receipts_fg f
                                                    JOIN checksheets e ON e.number = f.checksheet_number
                                                    LEFT JOIN users c ON f.created_by = c.username
                                                    WHERE e.item_fg_id = '$item_fg_id'  and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' and e.status_subcont='NO' and e.shift like '%$filter_shift%' and f.wo_no like '%$filter_workorder%'");

                    // $receiptsNB = $this->crud->query("
                    //                                 SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
                    //                                 FROM new_barcode_fg a
                    //                                 LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                    //                                 LEFT JOIN users u ON f.created_by = u.username
                    //                                 WHERE a.item_fg_id = '$item_fg_id'  AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and f.wo_no like '%$filter_workorder%'");

                    $receiptsWIP = $this->crud->query("
                                                    SELECT a.*, u.name as username, 'WIP RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                                                    FROM wip_receipts a
                                                    LEFT JOIN users u ON a.created_by = u.username
                                                    WHERE a.item_fg_id = '$item_fg_id' AND a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.wo_no like '%$filter_workorder%'");

                    $transFgs = $this->crud->query("
                                                    SELECT *
                                                    FROM transaction_fg a
                                                    WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND a.item_fg_id = '$item_fg_id' AND a.request_date BETWEEN '$filter_from' and '$filter_to' and a.request_no like '%$filter_workorder%'");

                    // Proses data berdasarkan tanggal
                    $all_data = [];

                    foreach ($dataActualProductions as $actualProduction) {//ada wo_no
                        $all_data[] = [
                            'type' => 'OUTPUT PRODUCTION',
                            'date' => $actualProduction->trans_date,
                            'wo_no' => $actualProduction->wo_no,
                            'wo_qty' => $record->qty_wo,
                            'actual_production' => $actualProduction->qty,
                            'qty_wip' => $actualProduction->qty_wip,
                            'ng' => 0,
                            'subconts_jasa' => 0,
                            'qty_adj_in' => 0,
                            'rfg' => 0,
                            'rfg_subconts_jasa' => 0,
                            'qty_adj_out' => 0,
                        ];
                    }

                    foreach ($dataNgs as $dataNg) {
                        $all_data[] = [
                            'type' => 'PRODUCT NG',
                            'date' => $dataNg->trans_date,
                            'wo_no' => $dataNg->document,
                            'wo_qty' => $record->qty_wo,
                            'actual_production' => 0,
                            'qty_wip' => 0,
                            'ng' => $dataNg->qty_ng,
                            'subconts_jasa' => 0,
                            'qty_adj_in' => 0,
                            'rfg' => 0,
                            'rfg_subconts_jasa' => 0,
                            'qty_adj_out' => 0,
                        ];
                    }

                    foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                        $all_data[] = [
                            'type' => 'SUBCONTS JASA',
                            'date' => $dataSubcontsJasa->request_date,
                            'wo_no' => $dataSubcontsJasa->workorder,
                            'wo_qty' => $record->qty_wo,
                            'actual_production' => 0,
                            'qty_wip' => 0,
                            'ng' => 0,
                            'subconts_jasa' => $dataSubcontsJasa->qty_subcont_jasa,
                            'qty_adj_in' => 0,
                            'rfg' => 0,
                            'rfg_subconts_jasa' => 0,
                            'qty_adj_out' => 0,
                        ];
                    }

                    foreach ($receipts as $receipt) {
                        $all_data[] = [
                            'type' => $receipt->receipt_type,
                            'date' => $receipt->trans_date,
                            'wo_no' => $receipt->wo_no,
                            'wo_qty' => $record->qty_wo,
                            'actual_production' => 0,
                            'qty_wip' => 0,
                            'ng' => 0,
                            'subconts_jasa' => 0,
                            'qty_adj_in' => 0,
                            'rfg' => $receipt->qty,
                            'rfg_subconts_jasa' => 0,
                            'qty_adj_out' => 0,
                        ];
                    }

                    // foreach ($receiptsNB as $receiptNB) {
                    //     $all_data[] = [
                    //         'type' => $receiptNB->receipt_type,
                    //         'date' => $receiptNB->trans_date,
                    //         'wo_no' => $receiptNB->wo_no,
                    //         'wo_qty' => $record->qty_wo,
                    //         'actual_production' => 0,
                    //         'qty_wip' => 0,
                    //         'ng' => 0,
                    //         'subconts_jasa' => 0,
                    //         'qty_adj_in' => 0,
                    //         'rfg' => $receiptNB->qty,
                    //         'rfg_subconts_jasa' => 0,
                    //         'qty_adj_out' => 0,
                    //     ];
                    // }

                    foreach ($receiptsWIP as $receiptWIP) {
                        $all_data[] = [
                            'type' => $receiptWIP->receipt_type,
                            'date' => $receiptWIP->trans_date,
                            'wo_no' => $receiptWIP->wo_no,
                            'wo_qty' => $record->qty_wo,
                            'actual_production' => 0,
                            'qty_wip' => 0,
                            'ng' => 0,
                            'subconts_jasa' => 0,
                            'qty_adj_in' => 0,
                            'rfg' => $receiptWIP->qty,
                            'rfg_subconts_jasa' => 0,
                            'qty_adj_out' => 0,
                        ];
                    }

                    foreach ($transFgs as $transFg) {
                        $all_data[] = [
                            'type' => 'TRANSACTION FG',
                            'date' => $transFg->request_date,
                            'wo_no' => $transFg->request_no,
                            'wo_qty' => $record->qty_wo,
                            'actual_production' => 0,
                            'qty_wip' => 0,
                            'ng' => 0,
                            'subconts_jasa' => 0,
                            'qty_adj_in' => 0,
                            'rfg' => $transFg->qty,
                            'rfg_subconts_jasa' => 0,
                            'qty_adj_out' => 0,
                        ];
                    }

                    foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                        $all_data[] = [
                            'type' => 'RFG SUBCONTS JASA',
                            'date' => $dataRfgSubcontsJasa->trans_date,
                            'wo_no' => $dataRfgSubcontsJasa->wo_no,
                            'wo_qty' => $record->qty_wo,
                            'actual_production' => 0,
                            'qty_wip' => 0,
                            'ng' => 0,
                            'subconts_jasa' => 0,
                            'qty_adj_in' => 0,
                            'rfg' => 0,
                            'rfg_subconts_jasa' => $dataRfgSubcontsJasa->qty_rfg,
                            'qty_adj_out' => 0,
                        ];
                    }

                    foreach ($dataAdjIns  as $dataAdjIn) {
                        $all_data[] = [
                            'type' => $dataAdjIn->transaction_type,
                            'date' => $dataAdjIn->request_date,
                            'wo_no' => $dataAdjIn->request_no,
                            'wo_qty' => $record->qty_wo,
                            'actual_production' => 0,
                            'qty_wip' => 0,
                            'ng' => 0,
                            'subconts_jasa' => 0,
                            'qty_adj_in' => $dataAdjIn->qty,
                            'rfg' => 0,
                            'rfg_subconts_jasa' => 0,
                            'qty_adj_out' => 0,
                        ];
                    }

                    foreach ($dataAdjOuts  as $dataAdjOut) {
                        $all_data[] = [
                            'type' => $dataAdjOut->transaction_type,
                            'date' => $dataAdjOut->request_date,
                            'wo_no' => $dataAdjOut->request_no,
                            'wo_qty' => $record->qty_wo,
                            'actual_production' => 0,
                            'qty_wip' => 0,
                            'ng' => 0,
                            'subconts_jasa' => 0,
                            'qty_adj_in' => 0,
                            'rfg' => 0,
                            'rfg_subconts_jasa' => 0,
                            'qty_adj_out' => $dataAdjOut->qty,
                        ];
                    }

                    // Urutkan data berdasarkan tanggal
                    usort($all_data, function ($a, $b) {
                        return strtotime($a['date']) - strtotime($b['date']);
                    });

                    $qty_wo_read = $this->crud->query("select qty FROM production_schedules where item_fg_id='$item_fg_id' and wo_no like '%$filter_workorder%'");
                    $qty_wo = 0;
                    if (!empty($qty_wo_read) && isset($qty_wo_read[0]->qty)) {
                        $qty_wo = $qty_wo_read[0]->qty;
                    }

                    $total_actual_production = 0;
                    $total_qty_wip = 0;
                    $total_ng = 0;
                    $total_subconts_jasa = 0;
                    $total_qty_adj_in = 0;
                    $total_rfg = 0;
                    $total_rfg_subconts_jasa = 0;
                    $total_qty_adj_out = 0;

                    foreach ($all_data as $data) {
                        $total_actual_production += $data['actual_production'];
                        $total_qty_wip += $data['qty_wip'];
                        $total_ng += $data['ng'];
                        $total_subconts_jasa += $data['subconts_jasa'];
                        $total_qty_adj_in += $data['qty_adj_in'];
                        $total_rfg += $data['rfg'];
                        $total_rfg_subconts_jasa += $data['rfg_subconts_jasa'];
                        $total_qty_adj_out += $data['qty_adj_out'];
                    }

                    $total_production = $total_actual_production + $total_qty_wip + $total_ng;

                    // Buat header total sebelum data detail
                    $html .= '<tr style="background:#EEE; font-weight:bold;">
                                <td></td>
                                <td style="text-align:center">-</td>
                                <td>' . $record->number  . '</td>
                                <td>' . $record->name  . '</td>
                                <td>-</td>
                                <td>-</td>
                                <td>' . $data['wo_no']  . '</td>
                                <td style="text-align:right;">' . number_format($qty_wo, 2)  . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2)  . '</td>
                                <td style="text-align:right;">' . number_format($total_actual_production, 2) . '</td>
                                <td style="text-align:right;">' . number_format($total_qty_wip, 2) . '</td>
                                <td style="text-align:right;">' . number_format($total_ng, 2) . '</td>
                                <td style="text-align:right;">' . number_format($total_production, 2) . '</td>
                                <td style="text-align:right;">' . number_format($total_subconts_jasa, 2) . '</td>
                                <td style="text-align:right;">' . number_format($total_qty_adj_in, 2) . '</td>
                                <td style="text-align:right;">' . number_format($total_rfg, 2) . '</td>
                                <td style="text-align:right;">' . number_format($total_rfg_subconts_jasa, 2) . '</td>
                                <td style="text-align:right;">' . number_format($total_qty_adj_out, 2) . '</td>
                                <td></td>
                            </tr>';

                    // Generate HTML
                    $nod = 1;
                    $balance = $begin;
                    foreach ($all_data as $data) {
                        $total_production = $data['actual_production'] + $data['qty_wip'] + $data['ng'];
                        $balance += $data['actual_production'] + $data['qty_wip'] + $data['subconts_jasa'] + $data['qty_adj_in'] - $data['rfg'] - $data['rfg_subconts_jasa'] - $data['qty_adj_out'];
                        $html .= '  <tr>
                                        <td></td>
                                        <td style="text-align:center">' . $nod . '</td>
                                        <td>' . $record->number  . '</td>
                                        <td>' . $record->name  . '</td>
                                        <td>' . $data['type']  . '</td>
                                        <td>' . $data['date']  . '</td>
                                        <td>' . $data['wo_no']  . '</td>
                                        <td style="text-align:right;">' . number_format($qty_wo, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($begin, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($data['actual_production'], 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($data['qty_wip'], 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($data['ng'], 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($total_production, 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($data['subconts_jasa'], 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($data['qty_adj_in'], 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($data['rfg'], 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($data['rfg_subconts_jasa'], 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($data['qty_adj_out'], 2)  . '</td>
                                        <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                    </tr>';

                        $begin = $balance;
                        $nod++;
                    }
                }
            } elseif ($filter_display == "DETAIL" && $filter_workorder == "") {
                $html .= '  <tr>
                                <td colspan="23" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
                $html .= '  <tr>
                                <th rowspan="2" width="20"></th>
                                <th rowspan="2" width="20">No</th>
                                <th rowspan="2" >Product No</th>
                                <th rowspan="2" >Product Name</th>
                                <th rowspan="2" >Type</th>
                                <th rowspan="2" >Trans Date</th>
                                <th rowspan="2" >WO / Doc</th>
                                <th rowspan="2" >WO Qty</th> 
                                <th rowspan="2" >Begin Balance</th>
                                <th colspan="2" >Output Production</th>
                                <th rowspan="2" >NG</th>
                                <th rowspan="2" >Total Production</th>
                                <th rowspan="2" >SubCont Jasa</th>
                                <th rowspan="2" >ADJ IN</th>
                                <th rowspan="2" >RFG</th>
                                <th rowspan="2" >RFG SubCont Jasa</th>
                                <th rowspan="2" >ADJ OUT</th>
                                <th rowspan="2" >Ending Balance</th>
                           </tr>
                            <tr>
                                <th>Qty FG</th>
                                <th>Qty WIP</th>
                            </tr>';
                $nod = 1;
                $begin = @$record->begin_balance;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;

                $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$item_fg_id' and trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%'");

                $dataNgs = $this->crud->query("
                                                select aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                        select distinct trans_date,document,item_fg_id, qty_product FROM item_ng where item_fg_id='$item_fg_id' and trans_date between '$filter_from' and '$filter_to' AND shift like '%$filter_shift%' AND kind LIKE 'Ng Process Production'
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

                $dataRfgSubcontsJasas = $this->crud->query("
                                                select ab.packing_date as trans_date,ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg 
                                                FROM scan_item_receipts_fg aa 
                                                JOIN checksheets ab on aa.checksheet_number = ab.number
                                                where aa.item_fg_id='$item_fg_id' and ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%'
                                                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
                ");

                $dataAdjIns = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$item_fg_id' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN'
                ");

                $dataAdjOuts = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$item_fg_id' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT'
                ");

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
                                                WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND a.item_fg_id = '$item_fg_id' AND a.request_date BETWEEN '$filter_from' and '$filter_to'");

                // Proses data berdasarkan tanggal
                $all_data = [];

                foreach ($dataActualProductions as $actualProduction) {
                    $all_data[] = [
                        'type' => 'OUTPUT PRODUCTION',
                        'date' => $actualProduction->trans_date,
                        'wo_no' => $actualProduction->wo_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => $actualProduction->qty,
                        'qty_wip' => $actualProduction->qty_wip,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataNgs as $dataNg) {
                    $all_data[] = [
                        'type' => 'PRODUCT NG',
                        'date' => $dataNg->trans_date,
                        'wo_no' => $dataNg->document,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => $dataNg->qty_ng,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                    $all_data[] = [
                        'type' => 'SUBCONTS JASA',
                        'date' => $dataSubcontsJasa->request_date,
                        'wo_no' => $dataSubcontsJasa->workorder,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => $dataSubcontsJasa->qty_subcont_jasa,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($receipts as $receipt) {
                    $all_data[] = [
                        'type' => $receipt->receipt_type,
                        'date' => $receipt->trans_date,
                        'wo_no' => $receipt->wo_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $receipt->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                // foreach ($receiptsNB as $receiptNB) {
                //     $all_data[] = [
                //         'type' => $receiptNB->receipt_type,
                //         'date' => $receiptNB->trans_date,
                //         'wo_no' => $receiptNB->wo_no,
                //         'wo_qty' => $record->qty_wo,
                //         'actual_production' => 0,
                //         'qty_wip' => 0,
                //         'ng' => 0,
                //         'subconts_jasa' => 0,
                //         'qty_adj_in' => 0,
                //         'rfg' => $receiptNB->qty,
                //         'rfg_subconts_jasa' => 0,
                //         'qty_adj_out' => 0,
                //     ];
                // }

                foreach ($receiptsWIP as $receiptWIP) {
                    $all_data[] = [
                        'type' => $receiptWIP->receipt_type,
                        'date' => $receiptWIP->trans_date,
                        'wo_no' => $receiptWIP->wo_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $receiptWIP->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($transFgs as $transFg) {
                    $all_data[] = [
                        'type' => 'TRANSACTION FG',
                        'date' => $transFg->request_date,
                        'wo_no' => $transFg->request_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $transFg->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                    $all_data[] = [
                        'type' => 'RFG SUBCONTS JASA',
                        'date' => $dataRfgSubcontsJasa->trans_date,
                        'wo_no' => $dataRfgSubcontsJasa->wo_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => $dataRfgSubcontsJasa->qty_rfg,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataAdjIns  as $dataAdjIn) {
                    $all_data[] = [
                        'type' => $dataAdjIn->transaction_type,
                        'date' => $dataAdjIn->request_date,
                        'wo_no' => $dataAdjIn->request_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => $dataAdjIn->qty,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataAdjOuts  as $dataAdjOut) {
                    $all_data[] = [
                        'type' => $dataAdjOut->transaction_type,
                        'date' => $dataAdjOut->request_date,
                        'wo_no' => $dataAdjOut->request_no,
                        'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => $dataAdjOut->qty,
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
                    $total_production = $data['actual_production'] + $data['qty_wip'] + $data['ng'];
                    $balance += $data['actual_production'] + $data['qty_wip'] + $data['subconts_jasa'] + $data['qty_adj_in'] - $data['rfg'] - $data['rfg_subconts_jasa'] - $data['qty_adj_out'];
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
                                    <td style="text-align:right;">' . number_format($data['qty_wip'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['ng'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($total_production, 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['subconts_jasa'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['qty_adj_in'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['rfg'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['rfg_subconts_jasa'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($data['qty_adj_out'], 2)  . '</td>
                                    <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                </tr>';

                    $begin = $balance;
                    $nod++;
                }
            }
            $no++;
        }


        $html .= '<tr>
            <td colspan="8" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;">' . number_format($totalBeginBalance, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyAct, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyWip, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyNg, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalTotalProduction, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalSubcontsJasa, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyAdjIn, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyNgSa, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyNgWip, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyOutput, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyRfg, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyJasa, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyAdjOut, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalEndingBalance, 2) . '</td>

        </tr>
        </tbody>';

        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_wo($option = "")
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
        $filter_workorder = $this->input->get("filter_workorder");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        if ($filter_workorder !="" && $filter_items !="") {
            $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$filter_items' and trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%' AND wo_no like '%$filter_workorder%'");

            $dataNgs = $this->crud->query("
                                            select aa.workorder,aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                    select distinct workorder,trans_date,document,item_fg_id, qty_product FROM item_ng where item_fg_id='$filter_items' and trans_date between '$filter_from' and '$filter_to' AND shift like '%$filter_shift%' AND workorder like '%$filter_workorder%' AND kind LIKE 'Ng Process Production'
                                            ) aa group by aa.document,aa.trans_date,aa.item_fg_id
            ");

            $dataSubcontsJasas = $this->crud->query("
                                            select aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                    select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                    FROM supply_sheets ax 
                                                    join item_fg ay on ax.item_fg_id=ay.id 
                                                    where ax.item_fg_id='$filter_items' and ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa' AND ax.workorder like '%$filter_workorder%'
                                            ) aa group by aa.workorder,aa.request_date,aa.item_fg_id
            ");

            $dataRfgSubcontsJasas = $this->crud->query("
                                                select ab.packing_date as trans_date, ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg 
                                                FROM scan_item_receipts_fg aa 
                                                JOIN checksheets ab on aa.checksheet_number = ab.number
                                                where aa.item_fg_id='$filter_items' and ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%' AND ab.wo_no like '%$filter_workorder%'
                                                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
            ");

            $dataAdjIns = $this->crud->query("
                                            select *
                                            FROM wip_adjustment_fg a
                                            where a.item_fg_id='$filter_items' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN' AND request_no like '%$filter_workorder%'
            ");

            $dataAdjOuts = $this->crud->query("
                                            select *
                                            FROM wip_adjustment_fg a
                                            where a.item_fg_id='$filter_items' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT' AND request_no like '%$filter_workorder%'
            ");

            //RFG---------------------------------------------------------------------------------------------------------------------------

            $receipts = $this->crud->query("
                                            SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                                            FROM scan_item_receipts_fg f
                                            JOIN checksheets e ON e.number = f.checksheet_number
                                            LEFT JOIN users c ON f.created_by = c.username
                                            WHERE e.item_fg_id = '$filter_items' and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' and e.status_subcont='NO' and e.shift like '%$filter_shift%' and f.wo_no like '%$filter_workorder%'");

            // $receiptsNB = $this->crud->query("
            //                                 SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
            //                                 FROM new_barcode_fg a
            //                                 LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
            //                                 LEFT JOIN users u ON f.created_by = u.username
            //                                 WHERE a.item_fg_id = '$filter_items' AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and f.wo_no like '%$filter_workorder%'");

            $receiptsWIP = $this->crud->query("
                                            SELECT a.*, u.name as username, 'WIP RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                                            FROM wip_receipts a
                                            LEFT JOIN users u ON a.created_by = u.username
                                            WHERE a.item_fg_id = '$filter_items' AND a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.wo_no like '%$filter_workorder%'");

            $transFgs = $this->crud->query("
                                            SELECT *
                                            FROM transaction_fg a
                                            WHERE a.transaction_kind = 'IN'  AND a.transaction_type = 'RECEIPT FG' AND a.item_fg_id = '$filter_items' AND a.request_date BETWEEN '$filter_from' and '$filter_to' and a.request_no like '%$filter_workorder%'");

            // ---------------------------------------------------------------------------------------------------------------------------------
            
            // Proses data berdasarkan tanggal
            $all_data = [];

            foreach ($dataActualProductions as $actualProduction) {
                $all_data[] = [
                    'period' => $actualProduction->period,
                    'date' => $actualProduction->trans_date,
                    'wo_no' => $actualProduction->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => $actualProduction->qty,
                    'qty_wip' => $actualProduction->qty_wip,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataNgs as $dataNg) {
                $all_data[] = [
                    'period' => '',
                    'date' => $dataNg->trans_date,
                    'wo_no' => $dataNg->workorder,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => $dataNg->qty_ng,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                $all_data[] = [
                    'period' => $dataSubcontsJasa->period,
                    'date' => $dataSubcontsJasa->request_date,
                    'wo_no' => $dataSubcontsJasa->workorder,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => $dataSubcontsJasa->qty_subcont_jasa,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($receipts as $receipt) {
                $all_data[] = [
                    'period' => '',
                    'date' => $receipt->trans_date,
                    'wo_no' => $receipt->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $receipt->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            // foreach ($receiptsNB as $receiptNB) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $receiptNB->trans_date,
            //         'wo_no' => $receiptNB->wo_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => 0,
            //         'rfg' => $receiptNB->qty,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => 0,
            //     ];
            // }

            foreach ($receiptsWIP as $receiptWIP) {
                $all_data[] = [
                    'period' => '',
                    'date' => $receiptWIP->trans_date,
                    'wo_no' => $receiptWIP->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $receiptWIP->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($transFgs as $transFg) {
                $all_data[] = [
                    'period' => '',
                    'date' => $transFg->request_date,
                    'wo_no' => $transFg->request_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $transFg->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                $all_data[] = [
                    'period' => '',
                    'date' => $dataRfgSubcontsJasa->trans_date,
                    'wo_no' => $dataRfgSubcontsJasa->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => $dataRfgSubcontsJasa->qty_rfg,
                    'qty_adj_out' => 0,
                ];
            }

            // foreach ($dataAdjIns  as $dataAdjIn) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $dataAdjIn->request_date,
            //         'wo_no' => $dataAdjIn->request_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => $dataAdjIn->qty,
            //         'rfg' => 0,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => 0,
            //     ];
            // }

            // foreach ($dataAdjOuts  as $dataAdjOut) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $dataAdjOut->request_date,
            //         'wo_no' => $dataAdjOut->request_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => 0,
            //         'rfg' => 0,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => $dataAdjOut->qty,
            //     ];
            // }

            // Urutkan data berdasarkan tanggal
            usort($all_data, function ($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });


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
                        <th rowspan="2" width="20">No</th>
                        <th rowspan="2">WO No</th>
                        <th rowspan="2">Period</th>
                        <th rowspan="2">Lot No</th>
                        <th rowspan="2">Wo Date</th>
                        <th rowspan="2">Product No</th>
                        <th rowspan="2">Product Name</th>
                        <th rowspan="2">UOM</th>
                        <th rowspan="2">WO Qty</th>
                        <th colspan="2">PRD QTY</th>
                        <th rowspan="2">NG Process</th>
                        <th rowspan="2">TOTAL PRODUCTION</th>
                        <th rowspan="2">RFG QTY</th>
                        <th rowspan="2">TOT PRD - RFG</th>
                        <th rowspan="2">(QTY FG+WIP) - RFG</th>
                    </tr>
                    <tr>
                        <th>Qty FG</th>
                        <th>Qty WIP</th>
                    </tr>';
            $no = 1;
            $grouped_data = [];
            $added_assembly_qty = []; // Untuk melacak apakah assembly qty sudah ditambahkan

            // foreach ($all_data as $row) {
            //     $key = $row['wo_no'];
            //     if (!isset($grouped_data[$key])) {
            //         $grouped_data[$key] = (object) $row;
            //     } else {
                    
            //         $grouped_data[$key]->actual_production += $row['actual_production'];
            //         $grouped_data[$key]->qty_wip += $row['qty_wip'];
            //         $grouped_data[$key]->ng += $row['ng'];
            //         $grouped_data[$key]->subconts_jasa += $row['subconts_jasa'];
            //         // $grouped_data[$key]->qty_adj_in += $row['qty_adj_in'];
            //         // $grouped_data[$key]->qty_adj_out += $row['qty_adj_out'];
            //         $grouped_data[$key]->rfg += $row['rfg'];
            //         $grouped_data[$key]->rfg_subconts_jasa += $row['rfg_subconts_jasa'];
            //     }
            // }

            foreach ($all_data as $row) {
                $key = $row['wo_no'];
                if (!isset($grouped_data[$key])) {
                    $grouped_data[$key] = (object) $row;
                } else {
                    $grouped_data[$key]->actual_production += $row['actual_production'];
                    $grouped_data[$key]->qty_wip += $row['qty_wip'];
                    $grouped_data[$key]->ng += $row['ng'];
                    $grouped_data[$key]->subconts_jasa += $row['subconts_jasa'];
                    $grouped_data[$key]->rfg += $row['rfg'];
                    $grouped_data[$key]->rfg_subconts_jasa += $row['rfg_subconts_jasa'];
                }

                // Tambahkan hanya sekali per $key
                if (!isset($added_assembly_qty[$key])) {
                    $wo_no = $row['wo_no'];
                  
                    $ps_query = $this->crud->query("SELECT wo_no_assembly FROM production_schedules WHERE wo_no = '$wo_no' LIMIT 1");

                    if (!empty($ps_query)) {
                        $wo_assembly = $ps_query[0]->wo_no_assembly;

                        $qty_query = $this->crud->query("
                            SELECT SUM(f.qty) as total_qty
                            FROM scan_item_receipts_fg f
                            JOIN checksheets e ON e.number = f.checksheet_number
                            WHERE f.wo_no = '$wo_assembly'
                            AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                            AND e.status_subcont = 'NO'
                        ");

                        $additional_qty = !empty($qty_query[0]->total_qty) ? $qty_query[0]->total_qty : 0;

                        $grouped_data[$key]->rfg += $additional_qty;
                    }

                    // Tandai bahwa sudah ditambahkan
                    $added_assembly_qty[$key] = true;
                }
            }

            $wo_nos = array_map(function($r) {
                return $r->wo_no;
            }, $grouped_data);

            $wo_nos_str = "'" . implode("','", $wo_nos) . "'";

            $query = $this->db->query("SELECT a.workorder as wo_no, a.period, a.lot_no, a.qty_wo, b.trans_date , c.name , c.number, c.uom
            FROM supply_sheets a
            LEFT JOIN production_schedules b ON a.workorder = b.wo_no
            JOIN item_fg c ON a.item_fg_id = c.id 
            WHERE a.workorder IN ($wo_nos_str)");
            $supplyData = [];
            foreach ($query->result() as $row) {
                $supplyData[$row->wo_no] = $row;
            }

            // Cari wo_no yang tidak ditemukan di supply_sheets
            $missing_wo_nos = array_diff($wo_nos, array_keys($supplyData));

            if (!empty($missing_wo_nos)) {
                $missing_wo_nos_str = "'" . implode("','", $missing_wo_nos) . "'";

                // Ambil data dari item_ng sebagai fallback
                $query_ng = $this->db->query(" SELECT DISTINCT a.document AS wo_no, '-' AS period, '-' AS lot_no, a.qty_sh AS qty_wo, a.trans_date, b.name, b.number
                    FROM item_ng a
                    LEFT JOIN item_fg b ON a.item_fg_id = b.id
                    WHERE a.document IN ($missing_wo_nos_str)
                ");

                foreach ($query_ng->result() as $row) {
                    $supplyData[$row->wo_no] = $row; // Tambahkan sebagai fallback
                }
            }

            $missing_wo_nos2 = array_diff($wo_nos, array_keys($supplyData));

            if (!empty($missing_wo_nos2)) {
                $missing_wo_nos2_str = "'" . implode("','", $missing_wo_nos2) . "'";

                // Ambil data dari checksheet sebagai fallback terakhir
                $query_checksheet = $this->db->query("
                    SELECT DISTINCT a.wo_no, '-' AS period, '-' AS lot_no, a.qty AS qty_wo, a.trans_date, b.name, b.number, b.uom
                    FROM checksheets a
                    LEFT JOIN item_fg b ON a.item_fg_id = b.id
                    WHERE a.wo_no IN ($missing_wo_nos2_str)
                ");

                foreach ($query_checksheet->result() as $row) {
                    $supplyData[$row->wo_no] = $row; // Tambahkan hasil checksheet
                }
            }
            
            foreach ($grouped_data as $record) {
                $supply = isset($supplyData[$record->wo_no]) ? $supplyData[$record->wo_no] : null;
                $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $record->wo_no . '</td>
                            <td style="mso-number-format:\@;">' . ($record->period ?: ($supply->period ?? '-')) . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->lot_no ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->trans_date ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->number ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->name ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->uom ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->qty_wo ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($record->actual_production?? '0') . '</td>
                            <td style="mso-number-format:\@;">' . ($record->qty_wip?? '0') . '</td>
                            <td style="mso-number-format:\@;">' . ($record->ng?? '0') . '</td>
                            <td style="mso-number-format:\@;">' . ($record->actual_production + $record->qty_wip + $record->ng) . '</td>
                            <td style="mso-number-format:\@;">' . ($record->rfg?? '0') . '</td>
                            <td style="mso-number-format:\@;">' . (($record->actual_production + $record->qty_wip + $record->ng) - $record->rfg) . '</td>
                            <td style="mso-number-format:\@;">' . (($record->actual_production + $record->qty_wip) - $record->rfg) . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;

        }elseif ($filter_items !="") {
            $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$filter_items' and trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%'");

            $dataNgs = $this->crud->query("
                                            select aa.workorder, aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                    select distinct workorder,trans_date,document,item_fg_id, qty_product FROM item_ng where item_fg_id='$filter_items' and trans_date between '$filter_from' and '$filter_to' AND shift like '%$filter_shift%' AND kind LIKE 'Ng Process Production'
                                            ) aa group by aa.document,aa.trans_date,aa.item_fg_id
            ");

            $dataSubcontsJasas = $this->crud->query("
                                            select aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                    select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                    FROM supply_sheets ax 
                                                    join item_fg ay on ax.item_fg_id=ay.id 
                                                    where ax.item_fg_id='$filter_items' and ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa'
                                            ) aa group by aa.workorder,aa.request_date,aa.item_fg_id
            ");

            $dataRfgSubcontsJasas = $this->crud->query("
                                                select ab.packing_date as trans_date,ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg 
                                                FROM scan_item_receipts_fg aa 
                                                JOIN checksheets ab on aa.checksheet_number = ab.number
                                                where aa.item_fg_id='$filter_items' and ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%'
                                                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
            ");

            $dataAdjIns = $this->crud->query("
                                            select *
                                            FROM wip_adjustment_fg a
                                            where a.item_fg_id='$filter_items' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN'
            ");

            $dataAdjOuts = $this->crud->query("
                                            select *
                                            FROM wip_adjustment_fg a
                                            where a.item_fg_id='$filter_items' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT'
            ");

            $receipts = $this->crud->query("
                                            SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                                            FROM scan_item_receipts_fg f
                                            JOIN checksheets e ON e.number = f.checksheet_number
                                            LEFT JOIN users c ON f.created_by = c.username
                                            WHERE e.item_fg_id = '$filter_items'  and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' and e.status_subcont='NO' and e.shift like '%$filter_shift%'");

            // $receipts = $this->crud->query("
            //     SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
            //     FROM scan_item_receipts_fg f
            //     JOIN checksheets e ON e.number = f.checksheet_number
            //     LEFT JOIN users c ON f.created_by = c.username
            //     WHERE (
            //         e.item_fg_id = '$filter_items'
            //         OR e.item_fg_id IN (
            //             SELECT item_fg_id FROM item_fg_subs WHERE item_fg_sa_id = '$filter_items'
            //         )
            //     )
            //     AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
            //     AND e.status_subcont = 'NO'
            //     AND e.shift LIKE '%$filter_shift%'
            // ");

            // $receiptsNB = $this->crud->query("
            //                                 SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
            //                                 FROM new_barcode_fg a
            //                                 LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
            //                                 LEFT JOIN users u ON f.created_by = u.username
            //                                 WHERE a.item_fg_id = '$filter_items'  AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

            $receiptsWIP = $this->crud->query("
                                            SELECT a.*, u.name as username, 'WIP RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                                            FROM wip_receipts a
                                            LEFT JOIN users u ON a.created_by = u.username
                                            WHERE a.item_fg_id = '$filter_items' AND a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

            $transFgs = $this->crud->query("
                                            SELECT *
                                            FROM transaction_fg a
                                            WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND a.item_fg_id = '$filter_items' AND a.request_date BETWEEN '$filter_from' and '$filter_to'");

            // Proses data berdasarkan tanggal
            $all_data = [];

            foreach ($dataActualProductions as $actualProduction) {
                $all_data[] = [
                    'period' => $actualProduction->period,
                    'date' => $actualProduction->trans_date,
                    'wo_no' => $actualProduction->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => $actualProduction->qty,
                    'qty_wip' => $actualProduction->qty_wip,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataNgs as $dataNg) {
                $all_data[] = [
                    'period' => '',
                    'date' => $dataNg->trans_date,
                    'wo_no' => $dataNg->workorder,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => $dataNg->qty_ng,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                $all_data[] = [
                    'period' => $dataSubcontsJasa->period,
                    'date' => $dataSubcontsJasa->request_date,
                    'wo_no' => $dataSubcontsJasa->workorder,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => $dataSubcontsJasa->qty_subcont_jasa,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($receipts as $receipt) {
                $all_data[] = [
                    'period' => '',
                    'date' => $receipt->trans_date,
                    'wo_no' => $receipt->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $receipt->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            // foreach ($receiptsNB as $receiptNB) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $receiptNB->trans_date,
            //         'wo_no' => $receiptNB->wo_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => 0,
            //         'rfg' => $receiptNB->qty,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => 0,
            //     ];
            // }

            foreach ($receiptsWIP as $receiptWIP) {
                $all_data[] = [
                    'period' => '',
                    'date' => $receiptWIP->trans_date,
                    'wo_no' => $receiptWIP->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $receiptWIP->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($transFgs as $transFg) {
                $all_data[] = [
                    'period' => '',
                    'date' => $transFg->request_date,
                    'wo_no' => $transFg->request_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $transFg->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                $all_data[] = [
                    'period' => '',
                    'date' => $dataRfgSubcontsJasa->trans_date,
                    'wo_no' => $dataRfgSubcontsJasa->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => $dataRfgSubcontsJasa->qty_rfg,
                    'qty_adj_out' => 0,
                ];
            }

            // foreach ($dataAdjIns  as $dataAdjIn) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $dataAdjIn->request_date,
            //         'wo_no' => $dataAdjIn->request_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => $dataAdjIn->qty,
            //         'rfg' => 0,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => 0,
            //     ];
            // }

            // foreach ($dataAdjOuts  as $dataAdjOut) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $dataAdjOut->request_date,
            //         'wo_no' => $dataAdjOut->request_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => 0,
            //         'rfg' => 0,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => $dataAdjOut->qty,
            //     ];
            // }

            // Urutkan data berdasarkan tanggal
            usort($all_data, function ($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });


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
                        <th rowspan="2" width="20">No</th>
                        <th rowspan="2">WO No</th>
                        <th rowspan="2">Period</th>
                        <th rowspan="2">Lot No</th>
                        <th rowspan="2">Wo Date</th>
                        <th rowspan="2">Product No</th>
                        <th rowspan="2">Product Name</th>
                        <th rowspan="2">UOM</th>
                        <th rowspan="2">WO Qty</th>
                        <th colspan="2">PRD QTY</th>
                        <th rowspan="2">NG Process</th>
                        <th rowspan="2">TOTAL PRODUCTION</th>
                        <th rowspan="2">RFG QTY</th>
                        <th rowspan="2">TOT PRD - RFG</th>
                        <th rowspan="2">(QTY FG+WIP) - RFG</th>
                    </tr>
                    <tr>
                        <th>Qty FG</th>
                        <th>Qty WIP</th>
                    </tr>';
            $no = 1;
            $grouped_data = [];
            $added_assembly_qty = []; // Untuk melacak apakah assembly qty sudah ditambahkan

            // foreach ($all_data as $row) {
            //     $key = $row['wo_no'];
            //     if (!isset($grouped_data[$key])) {
            //         $grouped_data[$key] = (object) $row;
            //     } else {
                    
            //         $grouped_data[$key]->actual_production += $row['actual_production'];
            //         $grouped_data[$key]->qty_wip += $row['qty_wip'];
            //         $grouped_data[$key]->ng += $row['ng'];
            //         $grouped_data[$key]->subconts_jasa += $row['subconts_jasa'];
            //         // $grouped_data[$key]->qty_adj_in += $row['qty_adj_in'];
            //         // $grouped_data[$key]->qty_adj_out += $row['qty_adj_out'];
            //         $grouped_data[$key]->rfg += $row['rfg'];
            //         $grouped_data[$key]->rfg_subconts_jasa += $row['rfg_subconts_jasa'];
            //     }
            // }

            foreach ($all_data as $row) {
                $key = $row['wo_no'];
                if (!isset($grouped_data[$key])) {
                    $grouped_data[$key] = (object) $row;
                } else {
                    $grouped_data[$key]->actual_production += $row['actual_production'];
                    $grouped_data[$key]->qty_wip += $row['qty_wip'];
                    $grouped_data[$key]->ng += $row['ng'];
                    $grouped_data[$key]->subconts_jasa += $row['subconts_jasa'];
                    $grouped_data[$key]->rfg += $row['rfg'];
                    $grouped_data[$key]->rfg_subconts_jasa += $row['rfg_subconts_jasa'];
                }

                // Tambahkan hanya sekali per $key
                if (!isset($added_assembly_qty[$key])) {
                    $wo_no = $row['wo_no'];
                  
                    $ps_query = $this->crud->query("SELECT wo_no_assembly FROM production_schedules WHERE wo_no = '$wo_no' LIMIT 1");

                    if (!empty($ps_query)) {
                        $wo_assembly = $ps_query[0]->wo_no_assembly;

                        $qty_query = $this->crud->query("
                            SELECT SUM(f.qty) as total_qty
                            FROM scan_item_receipts_fg f
                            JOIN checksheets e ON e.number = f.checksheet_number
                            WHERE f.wo_no = '$wo_assembly'
                            AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                            AND e.status_subcont = 'NO'
                        ");

                        $additional_qty = !empty($qty_query[0]->total_qty) ? $qty_query[0]->total_qty : 0;

                        $grouped_data[$key]->rfg += $additional_qty;
                    }

                    // Tandai bahwa sudah ditambahkan
                    $added_assembly_qty[$key] = true;
                }
            }

            $wo_nos = array_map(function($r) {
                return $r->wo_no;
            }, $grouped_data);

            $wo_nos_str = "'" . implode("','", $wo_nos) . "'";

            $query = $this->db->query("SELECT a.workorder as wo_no, a.period, a.lot_no, a.qty_wo, b.trans_date , c.name , c.number, c.uom
            FROM supply_sheets a
            LEFT JOIN production_schedules b ON a.workorder = b.wo_no
            JOIN item_fg c ON a.item_fg_id = c.id 
            WHERE a.workorder IN ($wo_nos_str)");
            $supplyData = [];
            foreach ($query->result() as $row) {
                $supplyData[$row->wo_no] = $row;
            }

            // Cari wo_no yang tidak ditemukan di supply_sheets
            $missing_wo_nos = array_diff($wo_nos, array_keys($supplyData));

            if (!empty($missing_wo_nos)) {
                $missing_wo_nos_str = "'" . implode("','", $missing_wo_nos) . "'";

                // Ambil data dari item_ng sebagai fallback
                $query_ng = $this->db->query(" SELECT DISTINCT a.document AS wo_no, '-' AS period, '-' AS lot_no, a.qty_sh AS qty_wo, a.trans_date, b.name, b.number
                    FROM item_ng a
                    LEFT JOIN item_fg b ON a.item_fg_id = b.id
                    WHERE a.document IN ($missing_wo_nos_str)
                ");

                foreach ($query_ng->result() as $row) {
                    $supplyData[$row->wo_no] = $row; // Tambahkan sebagai fallback
                }
            }

            $missing_wo_nos2 = array_diff($wo_nos, array_keys($supplyData));

            if (!empty($missing_wo_nos2)) {
                $missing_wo_nos2_str = "'" . implode("','", $missing_wo_nos2) . "'";

                // Ambil data dari checksheet sebagai fallback terakhir
                $query_checksheet = $this->db->query("
                    SELECT DISTINCT a.wo_no, '-' AS period, '-' AS lot_no, a.qty AS qty_wo, a.trans_date, b.name, b.number, b.uom
                    FROM checksheets a
                    LEFT JOIN item_fg b ON a.item_fg_id = b.id
                    WHERE a.wo_no IN ($missing_wo_nos2_str)
                ");

                foreach ($query_checksheet->result() as $row) {
                    $supplyData[$row->wo_no] = $row; // Tambahkan hasil checksheet
                }
            }
            
            foreach ($grouped_data as $record) {
                $supply = isset($supplyData[$record->wo_no]) ? $supplyData[$record->wo_no] : null;
                $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $record->wo_no . '</td>
                            <td style="mso-number-format:\@;">' . ($record->period ?: ($supply->period ?? '-')) . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->lot_no ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->trans_date ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->number ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->name ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->uom ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->qty_wo ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($record->actual_production?? '0') . '</td>
                            <td style="mso-number-format:\@;">' . ($record->qty_wip?? '0') . '</td>
                            <td style="mso-number-format:\@;">' . ($record->ng?? '0') . '</td>
                            <td style="mso-number-format:\@;">' . ($record->actual_production + $record->qty_wip + $record->ng) . '</td>
                            <td style="mso-number-format:\@;">' . ($record->rfg?? '0') . '</td>
                            <td style="mso-number-format:\@;">' . (($record->actual_production + $record->qty_wip + $record->ng) - $record->rfg) . '</td>
                            <td style="mso-number-format:\@;">' . (($record->actual_production + $record->qty_wip) - $record->rfg) . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
        }elseif ($filter_workorder !="") {
            $dataActualProductions = $this->crud->query("select * FROM output_productions where trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%' AND wo_no like '%$filter_workorder%'");

            $dataNgs = $this->crud->query("
                                            select aa.workorder,aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                    select distinct workorder,trans_date,document,item_fg_id, qty_product FROM item_ng where trans_date between '$filter_from' and '$filter_to' AND shift like '%$filter_shift%' AND workorder like '%$filter_workorder%' AND kind LIKE 'Ng Process Production'
                                            ) aa group by aa.document,aa.trans_date,aa.item_fg_id
            ");

            $dataSubcontsJasas = $this->crud->query("
                                            select aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                    select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                    FROM supply_sheets ax 
                                                    join item_fg ay on ax.item_fg_id=ay.id 
                                                    where ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa' AND ax.workorder like '%$filter_workorder%'
                                            ) aa group by aa.workorder,aa.request_date,aa.item_fg_id
            ");

            $dataRfgSubcontsJasas = $this->crud->query("
                                                select ab.packing_date as trans_date, ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg 
                                                FROM scan_item_receipts_fg aa 
                                                JOIN checksheets ab on aa.checksheet_number = ab.number
                                                where ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%' AND ab.wo_no like '%$filter_workorder%'
                                                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
            ");

            $dataAdjIns = $this->crud->query("
                                            select *
                                            FROM wip_adjustment_fg a
                                            where a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN' AND request_no like '%$filter_workorder%'
            ");

            $dataAdjOuts = $this->crud->query("
                                            select *
                                            FROM wip_adjustment_fg a
                                            where a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT' AND request_no like '%$filter_workorder%'
            ");

            $receipts = $this->crud->query("
                                            SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                                            FROM scan_item_receipts_fg f
                                            JOIN checksheets e ON e.number = f.checksheet_number
                                            LEFT JOIN users c ON f.created_by = c.username
                                            WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' and e.status_subcont='NO' and e.shift like '%$filter_shift%' and f.wo_no like '%$filter_workorder%'");

            // $receiptsNB = $this->crud->query("
            //                                 SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
            //                                 FROM new_barcode_fg a
            //                                 LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
            //                                 LEFT JOIN users u ON f.created_by = u.username
            //                                 WHERE DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and f.wo_no like '%$filter_workorder%'");

            $receiptsWIP = $this->crud->query("
                                            SELECT a.*, u.name as username, 'WIP RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                                            FROM wip_receipts a
                                            LEFT JOIN users u ON a.created_by = u.username
                                            WHERE a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.wo_no like '%$filter_workorder%'");

            $transFgs = $this->crud->query("
                                            SELECT *
                                            FROM transaction_fg a
                                            WHERE a.transaction_kind = 'IN'  AND a.transaction_type = 'RECEIPT FG' AND a.request_date BETWEEN '$filter_from' and '$filter_to' and a.request_no like '%$filter_workorder%'");

            // Proses data berdasarkan tanggal
            $all_data = [];

            foreach ($dataActualProductions as $actualProduction) {
                $all_data[] = [
                    'period' => $actualProduction->period,
                    'date' => $actualProduction->trans_date,
                    'wo_no' => $actualProduction->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => $actualProduction->qty,
                    'qty_wip' => $actualProduction->qty_wip,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataNgs as $dataNg) {
                $all_data[] = [
                    'period' => '',
                    'date' => $dataNg->trans_date,
                    'wo_no' => $dataNg->workorder,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => $dataNg->qty_ng,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                $all_data[] = [
                    'period' => $dataSubcontsJasa->period,
                    'date' => $dataSubcontsJasa->request_date,
                    'wo_no' => $dataSubcontsJasa->workorder,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => $dataSubcontsJasa->qty_subcont_jasa,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($receipts as $receipt) {
                $all_data[] = [
                    'period' => '',
                    'date' => $receipt->trans_date,
                    'wo_no' => $receipt->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $receipt->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            // foreach ($receiptsNB as $receiptNB) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $receiptNB->trans_date,
            //         'wo_no' => $receiptNB->wo_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => 0,
            //         'rfg' => $receiptNB->qty,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => 0,
            //     ];
            // }

            foreach ($receiptsWIP as $receiptWIP) {
                $all_data[] = [
                    'period' => '',
                    'date' => $receiptWIP->trans_date,
                    'wo_no' => $receiptWIP->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $receiptWIP->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($transFgs as $transFg) {
                $all_data[] = [
                    'period' => '',
                    'date' => $transFg->request_date,
                    'wo_no' => $transFg->request_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $transFg->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                $all_data[] = [
                    'period' => '',
                    'date' => $dataRfgSubcontsJasa->trans_date,
                    'wo_no' => $dataRfgSubcontsJasa->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => $dataRfgSubcontsJasa->qty_rfg,
                    'qty_adj_out' => 0,
                ];
            }

            // foreach ($dataAdjIns  as $dataAdjIn) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $dataAdjIn->request_date,
            //         'wo_no' => $dataAdjIn->request_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => $dataAdjIn->qty,
            //         'rfg' => 0,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => 0,
            //     ];
            // }

            // foreach ($dataAdjOuts  as $dataAdjOut) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $dataAdjOut->request_date,
            //         'wo_no' => $dataAdjOut->request_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => 0,
            //         'rfg' => 0,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => $dataAdjOut->qty,
            //     ];
            // }

            // Urutkan data berdasarkan tanggal
            usort($all_data, function ($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });


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
                        <th rowspan="2" width="20">No</th>
                        <th rowspan="2">WO No</th>
                        <th rowspan="2">Period</th>
                        <th rowspan="2">Lot No</th>
                        <th rowspan="2">Wo Date</th>
                        <th rowspan="2">Product No</th>
                        <th rowspan="2">Product Name</th>
                        <th rowspan="2">UOM</th>
                        <th rowspan="2">WO Qty</th>
                        <th colspan="2">PRD QTY</th>
                        <th rowspan="2">NG Process</th>
                        <th rowspan="2">TOTAL PRODUCTION</th>
                        <th rowspan="2">RFG QTY</th>
                        <th rowspan="2">TOT PRD - RFG</th>
                        <th rowspan="2">(QTY FG+WIP) - RFG</th>
                    </tr>
                    <tr>
                        <th>Qty FG</th>
                        <th>Qty WIP</th>
                    </tr>';
            $no = 1;
            $grouped_data = [];
            $added_assembly_qty = []; // Untuk melacak apakah assembly qty sudah ditambahkan
            
            // foreach ($all_data as $row) {
            //     $key = $row['wo_no'];
            //     if (!isset($grouped_data[$key])) {
            //         $grouped_data[$key] = (object) $row;
            //     } else {
                    
            //         $grouped_data[$key]->actual_production += $row['actual_production'];
            //         $grouped_data[$key]->qty_wip += $row['qty_wip'];
            //         $grouped_data[$key]->ng += $row['ng'];
            //         $grouped_data[$key]->subconts_jasa += $row['subconts_jasa'];
            //         // $grouped_data[$key]->qty_adj_in += $row['qty_adj_in'];
            //         // $grouped_data[$key]->qty_adj_out += $row['qty_adj_out'];
            //         $grouped_data[$key]->rfg += $row['rfg'];
            //         $grouped_data[$key]->rfg_subconts_jasa += $row['rfg_subconts_jasa'];
            //     }
            // }

             foreach ($all_data as $row) {
                $key = $row['wo_no'];
                if (!isset($grouped_data[$key])) {
                    $grouped_data[$key] = (object) $row;
                } else {
                    $grouped_data[$key]->actual_production += $row['actual_production'];
                    $grouped_data[$key]->qty_wip += $row['qty_wip'];
                    $grouped_data[$key]->ng += $row['ng'];
                    $grouped_data[$key]->subconts_jasa += $row['subconts_jasa'];
                    $grouped_data[$key]->rfg += $row['rfg'];
                    $grouped_data[$key]->rfg_subconts_jasa += $row['rfg_subconts_jasa'];
                }

                // Tambahkan hanya sekali per $key
                if (!isset($added_assembly_qty[$key])) {
                    $wo_no = $row['wo_no'];
                  
                    $ps_query = $this->crud->query("SELECT wo_no_assembly FROM production_schedules WHERE wo_no = '$wo_no' LIMIT 1");

                    if (!empty($ps_query)) {
                        $wo_assembly = $ps_query[0]->wo_no_assembly;

                        $qty_query = $this->crud->query("
                            SELECT SUM(f.qty) as total_qty
                            FROM scan_item_receipts_fg f
                            JOIN checksheets e ON e.number = f.checksheet_number
                            WHERE f.wo_no = '$wo_assembly'
                            AND DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                            AND e.status_subcont = 'NO'
                        ");

                        $additional_qty = !empty($qty_query[0]->total_qty) ? $qty_query[0]->total_qty : 0;

                        $grouped_data[$key]->rfg += $additional_qty;
                    }

                    // Tandai bahwa sudah ditambahkan
                    $added_assembly_qty[$key] = true;
                }
            }

            $wo_nos = array_map(function($r) {
                return $r->wo_no;
            }, $grouped_data);

            $wo_nos_str = "'" . implode("','", $wo_nos) . "'";

            $query = $this->db->query("SELECT a.workorder as wo_no, a.period, a.lot_no, a.qty_wo, b.trans_date , c.name , c.number, c.uom
            FROM supply_sheets a
            LEFT JOIN production_schedules b ON a.workorder = b.wo_no
            JOIN item_fg c ON a.item_fg_id = c.id 
            WHERE a.workorder IN ($wo_nos_str)");
            $supplyData = [];
            foreach ($query->result() as $row) {
                $supplyData[$row->wo_no] = $row;
            }

            // Cari wo_no yang tidak ditemukan di supply_sheets
            $missing_wo_nos = array_diff($wo_nos, array_keys($supplyData));

            if (!empty($missing_wo_nos)) {
                $missing_wo_nos_str = "'" . implode("','", $missing_wo_nos) . "'";

                // Ambil data dari item_ng sebagai fallback
                $query_ng = $this->db->query(" SELECT DISTINCT a.document AS wo_no, '-' AS period, '-' AS lot_no, a.qty_sh AS qty_wo, a.trans_date, b.name, b.number
                    FROM item_ng a
                    LEFT JOIN item_fg b ON a.item_fg_id = b.id
                    WHERE a.document IN ($missing_wo_nos_str)
                ");

                foreach ($query_ng->result() as $row) {
                    $supplyData[$row->wo_no] = $row; // Tambahkan sebagai fallback
                }
            }

            $missing_wo_nos2 = array_diff($wo_nos, array_keys($supplyData));

            if (!empty($missing_wo_nos2)) {
                $missing_wo_nos2_str = "'" . implode("','", $missing_wo_nos2) . "'";

                // Ambil data dari checksheet sebagai fallback terakhir
                $query_checksheet = $this->db->query("
                    SELECT DISTINCT a.wo_no, '-' AS period, '-' AS lot_no, a.qty AS qty_wo, a.trans_date, b.name, b.number, b.uom
                    FROM checksheets a
                    LEFT JOIN item_fg b ON a.item_fg_id = b.id
                    WHERE a.wo_no IN ($missing_wo_nos2_str)
                ");

                foreach ($query_checksheet->result() as $row) {
                    $supplyData[$row->wo_no] = $row; // Tambahkan hasil checksheet
                }
            }
            
            foreach ($grouped_data as $record) {
                $supply = isset($supplyData[$record->wo_no]) ? $supplyData[$record->wo_no] : null;
                $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $record->wo_no . '</td>
                            <td style="mso-number-format:\@;">' . ($record->period ?: ($supply->period ?? '-')) . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->lot_no ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->trans_date ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->number ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->name ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->uom ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->qty_wo ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($record->actual_production?? '0') . '</td>
                            <td style="mso-number-format:\@;">' . ($record->qty_wip?? '0') . '</td>
                            <td style="mso-number-format:\@;">' . ($record->ng?? '0') . '</td>
                            <td style="mso-number-format:\@;">' . ($record->actual_production + $record->qty_wip + $record->ng) . '</td>
                            <td style="mso-number-format:\@;">' . ($record->rfg?? '0') . '</td>
                            <td style="mso-number-format:\@;">' . (($record->actual_production + $record->qty_wip + $record->ng) - $record->rfg) . '</td>
                            <td style="mso-number-format:\@;">' . (($record->actual_production + $record->qty_wip) - $record->rfg) . '</td>
                        </tr>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
        }elseif ($filter_division !=""){
            $itemFgs = $this->crud->query("select id FROM item_fg where status = '0' AND type !='RM' AND status_subcont = 'NO' AND division_id like '$filter_division' order by id");
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
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2">WO No</th>
                            <th rowspan="2">Period</th>
                            <th rowspan="2">Lot No</th>
                            <th rowspan="2">Wo Date</th>
                            <th rowspan="2">Product No</th>
                            <th rowspan="2">Product Name</th>
                            <th rowspan="2">UOM</th>
                            <th rowspan="2">WO Qty</th>
                            <th colspan="2">PRD QTY</th>
                            <th rowspan="2">NG Process</th>
                            <th rowspan="2">TOTAL PRODUCTION</th>
                            <th rowspan="2">RFG QTY</th>
                            <th rowspan="2">TOT PRD - RFG</th>
                            <th rowspan="2">(QTY FG+WIP) - RFG</th>
                        </tr>
                        <tr>
                            <th>Qty FG</th>
                            <th>Qty WIP</th>
                        </tr>';
            foreach ($itemFgs as $item) {
                $itemId = $item->id;
                $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$itemId' and trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%'");

                $dataNgs = $this->crud->query("
                                                select aa.workorder, aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                        select distinct workorder,trans_date,document,item_fg_id, qty_product FROM item_ng where item_fg_id='$itemId' and trans_date between '$filter_from' and '$filter_to' AND shift like '%$filter_shift%' AND kind LIKE 'Ng Process Production'
                                                ) aa group by aa.document,aa.trans_date,aa.item_fg_id
                ");

                $dataSubcontsJasas = $this->crud->query("
                                                select aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                        select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                        FROM supply_sheets ax 
                                                        join item_fg ay on ax.item_fg_id=ay.id 
                                                        where ax.item_fg_id='$itemId' and ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa'
                                                ) aa group by aa.workorder,aa.request_date,aa.item_fg_id
                ");

                $dataRfgSubcontsJasas = $this->crud->query("
                                                select ab.packing_date as trans_date,ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg 
                                                FROM scan_item_receipts_fg aa 
                                                JOIN checksheets ab on aa.checksheet_number = ab.number
                                                where aa.item_fg_id='$itemId' and ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%'
                                                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
                ");

                $dataAdjIns = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$itemId' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN'
                ");

                $dataAdjOuts = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$itemId' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT'
                ");

                $receipts = $this->crud->query("
                                                SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                                                FROM scan_item_receipts_fg f
                                                JOIN checksheets e ON e.number = f.checksheet_number
                                                LEFT JOIN users c ON f.created_by = c.username
                                                WHERE e.item_fg_id = '$itemId'  and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' and e.status_subcont='NO' and e.shift like '%$filter_shift%'");

                // $receiptsNB = $this->crud->query("
                //                                 SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
                //                                 FROM new_barcode_fg a
                //                                 LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                //                                 LEFT JOIN users u ON f.created_by = u.username
                //                                 WHERE a.item_fg_id = '$itemId'  AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                $receiptsWIP = $this->crud->query("
                                                SELECT a.*, u.name as username, 'WIP RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                                                FROM wip_receipts a
                                                LEFT JOIN users u ON a.created_by = u.username
                                                WHERE a.item_fg_id = '$itemId' AND a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                $transFgs = $this->crud->query("
                                                SELECT *
                                                FROM transaction_fg a
                                                WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND a.item_fg_id = '$itemId' AND a.request_date BETWEEN '$filter_from' and '$filter_to'");

                // Proses data berdasarkan tanggal
                $all_data = [];

                foreach ($dataActualProductions as $actualProduction) {
                    $all_data[] = [
                        'period' => $actualProduction->period,
                        'date' => $actualProduction->trans_date,
                        'wo_no' => $actualProduction->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => $actualProduction->qty,
                        'qty_wip' => $actualProduction->qty_wip,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataNgs as $dataNg) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $dataNg->trans_date,
                        'wo_no' => $dataNg->workorder,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => $dataNg->qty_ng,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                    $all_data[] = [
                        'period' => $dataSubcontsJasa->period,
                        'date' => $dataSubcontsJasa->request_date,
                        'wo_no' => $dataSubcontsJasa->workorder,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => $dataSubcontsJasa->qty_subcont_jasa,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($receipts as $receipt) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $receipt->trans_date,
                        'wo_no' => $receipt->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $receipt->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                // foreach ($receiptsNB as $receiptNB) {
                //     $all_data[] = [
                //         'period' => '',
                //         'date' => $receiptNB->trans_date,
                //         'wo_no' => $receiptNB->wo_no,
                //         // 'wo_qty' => $record->qty_wo,
                //         'actual_production' => 0,
                //         'qty_wip' => 0,
                //         'ng' => 0,
                //         'subconts_jasa' => 0,
                //         'qty_adj_in' => 0,
                //         'rfg' => $receiptNB->qty,
                //         'rfg_subconts_jasa' => 0,
                //         'qty_adj_out' => 0,
                //     ];
                // }

                foreach ($receiptsWIP as $receiptWIP) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $receiptWIP->trans_date,
                        'wo_no' => $receiptWIP->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $receiptWIP->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($transFgs as $transFg) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $transFg->request_date,
                        'wo_no' => $transFg->request_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $transFg->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $dataRfgSubcontsJasa->trans_date,
                        'wo_no' => $dataRfgSubcontsJasa->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => $dataRfgSubcontsJasa->qty_rfg,
                        'qty_adj_out' => 0,
                    ];
                }

                // foreach ($dataAdjIns  as $dataAdjIn) {
                //     $all_data[] = [
                //         'period' => '',
                //         'date' => $dataAdjIn->request_date,
                //         'wo_no' => $dataAdjIn->request_no,
                //         // 'wo_qty' => $record->qty_wo,
                //         'actual_production' => 0,
                //         'qty_wip' => 0,
                //         'ng' => 0,
                //         'subconts_jasa' => 0,
                //         'qty_adj_in' => $dataAdjIn->qty,
                //         'rfg' => 0,
                //         'rfg_subconts_jasa' => 0,
                //         'qty_adj_out' => 0,
                //     ];
                // }

                // foreach ($dataAdjOuts  as $dataAdjOut) {
                //     $all_data[] = [
                //         'period' => '',
                //         'date' => $dataAdjOut->request_date,
                //         'wo_no' => $dataAdjOut->request_no,
                //         // 'wo_qty' => $record->qty_wo,
                //         'actual_production' => 0,
                //         'qty_wip' => 0,
                //         'ng' => 0,
                //         'subconts_jasa' => 0,
                //         'qty_adj_in' => 0,
                //         'rfg' => 0,
                //         'rfg_subconts_jasa' => 0,
                //         'qty_adj_out' => $dataAdjOut->qty,
                //     ];
                // }

                // Urutkan data berdasarkan tanggal
                usort($all_data, function ($a, $b) {
                    return strtotime($a['date']) - strtotime($b['date']);
                });

                $no = 1;
                $grouped_data = [];
                foreach ($all_data as $row) {
                    $key = $row['wo_no'];
                    if (!isset($grouped_data[$key])) {
                        $grouped_data[$key] = (object) $row;
                    } else {
                        
                        $grouped_data[$key]->actual_production += $row['actual_production'];
                        $grouped_data[$key]->qty_wip += $row['qty_wip'];
                        $grouped_data[$key]->ng += $row['ng'];
                        $grouped_data[$key]->subconts_jasa += $row['subconts_jasa'];
                        // $grouped_data[$key]->qty_adj_in += $row['qty_adj_in'];
                        // $grouped_data[$key]->qty_adj_out += $row['qty_adj_out'];
                        $grouped_data[$key]->rfg += $row['rfg'];
                        $grouped_data[$key]->rfg_subconts_jasa += $row['rfg_subconts_jasa'];
                    }
                }

                $wo_nos = array_map(function($r) {
                    return $r->wo_no;
                }, $grouped_data);

                $wo_nos_str = "'" . implode("','", $wo_nos) . "'";

                $query = $this->db->query("SELECT a.workorder as wo_no, a.period, a.lot_no, a.qty_wo, b.trans_date , c.name , c.number, c.uom
                FROM supply_sheets a
                LEFT JOIN production_schedules b ON a.workorder = b.wo_no
                JOIN item_fg c ON a.item_fg_id = c.id 
                WHERE a.workorder IN ($wo_nos_str)");
                $supplyData = [];
                foreach ($query->result() as $row) {
                    $supplyData[$row->wo_no] = $row;
                }

                // Cari wo_no yang tidak ditemukan di supply_sheets
                $missing_wo_nos = array_diff($wo_nos, array_keys($supplyData));

                if (!empty($missing_wo_nos)) {
                    $missing_wo_nos_str = "'" . implode("','", $missing_wo_nos) . "'";

                    // Ambil data dari item_ng sebagai fallback
                    $query_ng = $this->db->query(" SELECT DISTINCT a.document AS wo_no, '-' AS period, '-' AS lot_no, a.qty_sh AS qty_wo, a.trans_date, b.name, b.number
                        FROM item_ng a
                        LEFT JOIN item_fg b ON a.item_fg_id = b.id
                        WHERE a.document IN ($missing_wo_nos_str)
                    ");

                    foreach ($query_ng->result() as $row) {
                        $supplyData[$row->wo_no] = $row; // Tambahkan sebagai fallback
                    }
                }

                $missing_wo_nos2 = array_diff($wo_nos, array_keys($supplyData));

                if (!empty($missing_wo_nos2)) {
                    $missing_wo_nos2_str = "'" . implode("','", $missing_wo_nos2) . "'";

                    // Ambil data dari checksheet sebagai fallback terakhir
                    $query_checksheet = $this->db->query("
                        SELECT DISTINCT a.wo_no, '-' AS period, '-' AS lot_no, a.qty AS qty_wo, a.trans_date, b.name, b.number, b.uom
                        FROM checksheets a
                        LEFT JOIN item_fg b ON a.item_fg_id = b.id
                        WHERE a.wo_no IN ($missing_wo_nos2_str)
                    ");

                    foreach ($query_checksheet->result() as $row) {
                        $supplyData[$row->wo_no] = $row; // Tambahkan hasil checksheet
                    }
                }
                                
                foreach ($grouped_data as $record) {
                    $supply = isset($supplyData[$record->wo_no]) ? $supplyData[$record->wo_no] : null;
                    $html .= '<tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $record->wo_no . '</td>
                                <td style="mso-number-format:\@;">' . ($record->period ?: ($supply->period ?? '-')) . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->lot_no ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->trans_date ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->number ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->name ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->uom ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->qty_wo ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($record->actual_production?? '0') . '</td>
                                <td style="mso-number-format:\@;">' . ($record->qty_wip?? '0') . '</td>
                                <td style="mso-number-format:\@;">' . ($record->ng?? '0') . '</td>
                                <td style="mso-number-format:\@;">' . ($record->actual_production + $record->qty_wip + $record->ng) . '</td>
                                <td style="mso-number-format:\@;">' . ($record->rfg?? '0') . '</td>
                                <td style="mso-number-format:\@;">' . (($record->actual_production + $record->qty_wip + $record->ng) - $record->rfg) . '</td>
                                <td style="mso-number-format:\@;">' . (($record->actual_production + $record->qty_wip) - $record->rfg) . '</td>
                            </tr>';
                    $no++;
                }
            }
            $html .= '</table></body></html>';
            echo $html;
        }else{
            $itemFgs = $this->crud->query("select id FROM item_fg where status = '0' AND type !='RM' AND status_subcont = 'NO' order by id");
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
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2">WO No</th>
                            <th rowspan="2">Period</th>
                            <th rowspan="2">Lot No</th>
                            <th rowspan="2">Wo Date</th>
                            <th rowspan="2">Product No</th>
                            <th rowspan="2">Product Name</th>
                            <th rowspan="2">UOM</th>
                            <th rowspan="2">WO Qty</th>
                            <th colspan="2">PRD QTY</th>
                            <th rowspan="2">NG Process</th>
                            <th rowspan="2">TOTAL PRODUCTION</th>
                            <th rowspan="2">RFG QTY</th>
                            <th rowspan="2">TOT PRD - RFG</th>
                            <th rowspan="2">(QTY FG+WIP) - RFG</th>
                        </tr>
                        <tr>
                            <th>Qty FG</th>
                            <th>Qty WIP</th>
                        </tr>';
            foreach ($itemFgs as $item) {
                $itemId = $item->id;
                $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$itemId' and trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%'");

                $dataNgs = $this->crud->query("
                                                select aa.workorder, aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                        select distinct workorder,trans_date,document,item_fg_id, qty_product FROM item_ng where item_fg_id='$itemId' and trans_date between '$filter_from' and '$filter_to' AND shift like '%$filter_shift%' AND kind LIKE 'Ng Process Production'
                                                ) aa group by aa.document,aa.trans_date,aa.item_fg_id
                ");

                $dataSubcontsJasas = $this->crud->query("
                                                select aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                        select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                        FROM supply_sheets ax 
                                                        join item_fg ay on ax.item_fg_id=ay.id 
                                                        where ax.item_fg_id='$itemId' and ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa'
                                                ) aa group by aa.workorder,aa.request_date,aa.item_fg_id
                ");

                $dataRfgSubcontsJasas = $this->crud->query("
                                                select ab.packing_date as trans_date,ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg 
                                                FROM scan_item_receipts_fg aa 
                                                JOIN checksheets ab on aa.checksheet_number = ab.number
                                                where aa.item_fg_id='$itemId' and ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%'
                                                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
                ");

                $dataAdjIns = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$itemId' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN'
                ");

                $dataAdjOuts = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$itemId' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT'
                ");

                $receipts = $this->crud->query("
                                                SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                                                FROM scan_item_receipts_fg f
                                                JOIN checksheets e ON e.number = f.checksheet_number
                                                LEFT JOIN users c ON f.created_by = c.username
                                                WHERE e.item_fg_id = '$itemId'  and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' and e.status_subcont='NO' and e.shift like '%$filter_shift%'");

                // $receiptsNB = $this->crud->query("
                //                                 SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
                //                                 FROM new_barcode_fg a
                //                                 LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                //                                 LEFT JOIN users u ON f.created_by = u.username
                //                                 WHERE a.item_fg_id = '$itemId'  AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                $receiptsWIP = $this->crud->query("
                                                SELECT a.*, u.name as username, 'WIP RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                                                FROM wip_receipts a
                                                LEFT JOIN users u ON a.created_by = u.username
                                                WHERE a.item_fg_id = '$itemId' AND a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                $transFgs = $this->crud->query("
                                                SELECT *
                                                FROM transaction_fg a
                                                WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND a.item_fg_id = '$itemId' AND a.request_date BETWEEN '$filter_from' and '$filter_to'");

                // Proses data berdasarkan tanggal
                $all_data = [];

                foreach ($dataActualProductions as $actualProduction) {
                    $all_data[] = [
                        'period' => $actualProduction->period,
                        'date' => $actualProduction->trans_date,
                        'wo_no' => $actualProduction->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => $actualProduction->qty,
                        'qty_wip' => $actualProduction->qty_wip,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataNgs as $dataNg) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $dataNg->trans_date,
                        'wo_no' => $dataNg->workorder,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => $dataNg->qty_ng,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                    $all_data[] = [
                        'period' => $dataSubcontsJasa->period,
                        'date' => $dataSubcontsJasa->request_date,
                        'wo_no' => $dataSubcontsJasa->workorder,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => $dataSubcontsJasa->qty_subcont_jasa,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($receipts as $receipt) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $receipt->trans_date,
                        'wo_no' => $receipt->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $receipt->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                // foreach ($receiptsNB as $receiptNB) {
                //     $all_data[] = [
                //         'period' => '',
                //         'date' => $receiptNB->trans_date,
                //         'wo_no' => $receiptNB->wo_no,
                //         // 'wo_qty' => $record->qty_wo,
                //         'actual_production' => 0,
                //         'qty_wip' => 0,
                //         'ng' => 0,
                //         'subconts_jasa' => 0,
                //         'qty_adj_in' => 0,
                //         'rfg' => $receiptNB->qty,
                //         'rfg_subconts_jasa' => 0,
                //         'qty_adj_out' => 0,
                //     ];
                // }

                foreach ($receiptsWIP as $receiptWIP) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $receiptWIP->trans_date,
                        'wo_no' => $receiptWIP->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $receiptWIP->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($transFgs as $transFg) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $transFg->request_date,
                        'wo_no' => $transFg->request_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $transFg->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $dataRfgSubcontsJasa->trans_date,
                        'wo_no' => $dataRfgSubcontsJasa->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => $dataRfgSubcontsJasa->qty_rfg,
                        'qty_adj_out' => 0,
                    ];
                }

                // foreach ($dataAdjIns  as $dataAdjIn) {
                //     $all_data[] = [
                //         'period' => '',
                //         'date' => $dataAdjIn->request_date,
                //         'wo_no' => $dataAdjIn->request_no,
                //         // 'wo_qty' => $record->qty_wo,
                //         'actual_production' => 0,
                //         'qty_wip' => 0,
                //         'ng' => 0,
                //         'subconts_jasa' => 0,
                //         'qty_adj_in' => $dataAdjIn->qty,
                //         'rfg' => 0,
                //         'rfg_subconts_jasa' => 0,
                //         'qty_adj_out' => 0,
                //     ];
                // }

                // foreach ($dataAdjOuts  as $dataAdjOut) {
                //     $all_data[] = [
                //         'period' => '',
                //         'date' => $dataAdjOut->request_date,
                //         'wo_no' => $dataAdjOut->request_no,
                //         // 'wo_qty' => $record->qty_wo,
                //         'actual_production' => 0,
                //         'qty_wip' => 0,
                //         'ng' => 0,
                //         'subconts_jasa' => 0,
                //         'qty_adj_in' => 0,
                //         'rfg' => 0,
                //         'rfg_subconts_jasa' => 0,
                //         'qty_adj_out' => $dataAdjOut->qty,
                //     ];
                // }

                // Urutkan data berdasarkan tanggal
                usort($all_data, function ($a, $b) {
                    return strtotime($a['date']) - strtotime($b['date']);
                });

                $no = 1;
                $grouped_data = [];
                foreach ($all_data as $row) {
                    $key = $row['wo_no'];
                    if (!isset($grouped_data[$key])) {
                        $grouped_data[$key] = (object) $row;
                    } else {
                        
                        $grouped_data[$key]->actual_production += $row['actual_production'];
                        $grouped_data[$key]->qty_wip += $row['qty_wip'];
                        $grouped_data[$key]->ng += $row['ng'];
                        $grouped_data[$key]->subconts_jasa += $row['subconts_jasa'];
                        // $grouped_data[$key]->qty_adj_in += $row['qty_adj_in'];
                        // $grouped_data[$key]->qty_adj_out += $row['qty_adj_out'];
                        $grouped_data[$key]->rfg += $row['rfg'];
                        $grouped_data[$key]->rfg_subconts_jasa += $row['rfg_subconts_jasa'];
                    }
                }

                $wo_nos = array_map(function($r) {
                    return $r->wo_no;
                }, $grouped_data);

                $wo_nos_str = "'" . implode("','", $wo_nos) . "'";

                $query = $this->db->query("SELECT a.workorder as wo_no, a.period, a.lot_no, a.qty_wo, b.trans_date , c.name , c.number, c.uom
                FROM supply_sheets a
                LEFT JOIN production_schedules b ON a.workorder = b.wo_no
                JOIN item_fg c ON a.item_fg_id = c.id 
                WHERE a.workorder IN ($wo_nos_str)");
                $supplyData = [];
                foreach ($query->result() as $row) {
                    $supplyData[$row->wo_no] = $row;
                }

                // Cari wo_no yang tidak ditemukan di supply_sheets
                $missing_wo_nos = array_diff($wo_nos, array_keys($supplyData));

                if (!empty($missing_wo_nos)) {
                    $missing_wo_nos_str = "'" . implode("','", $missing_wo_nos) . "'";

                    // Ambil data dari item_ng sebagai fallback
                    $query_ng = $this->db->query(" SELECT DISTINCT a.document AS wo_no, '-' AS period, '-' AS lot_no, a.qty_sh AS qty_wo, a.trans_date, b.name, b.number
                        FROM item_ng a
                        LEFT JOIN item_fg b ON a.item_fg_id = b.id
                        WHERE a.document IN ($missing_wo_nos_str)
                    ");

                    foreach ($query_ng->result() as $row) {
                        $supplyData[$row->wo_no] = $row; // Tambahkan sebagai fallback
                    }
                }

                $missing_wo_nos2 = array_diff($wo_nos, array_keys($supplyData));

                if (!empty($missing_wo_nos2)) {
                    $missing_wo_nos2_str = "'" . implode("','", $missing_wo_nos2) . "'";

                    // Ambil data dari checksheet sebagai fallback terakhir
                    $query_checksheet = $this->db->query("
                        SELECT DISTINCT a.wo_no, '-' AS period, '-' AS lot_no, a.qty AS qty_wo, a.trans_date, b.name, b.number, b.uom
                        FROM checksheets a
                        LEFT JOIN item_fg b ON a.item_fg_id = b.id
                        WHERE a.wo_no IN ($missing_wo_nos2_str)
                    ");

                    foreach ($query_checksheet->result() as $row) {
                        $supplyData[$row->wo_no] = $row; // Tambahkan hasil checksheet
                    }
                }
                                
                foreach ($grouped_data as $record) {
                    $supply = isset($supplyData[$record->wo_no]) ? $supplyData[$record->wo_no] : null;
                    $html .= '<tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $record->wo_no . '</td>
                                <td style="mso-number-format:\@;">' . ($record->period ?: ($supply->period ?? '-')) . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->lot_no ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->trans_date ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->number ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->name ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->uom ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->qty_wo ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($record->actual_production?? '0') . '</td>
                                <td style="mso-number-format:\@;">' . ($record->qty_wip?? '0') . '</td>
                                <td style="mso-number-format:\@;">' . ($record->ng?? '0') . '</td>
                                <td style="mso-number-format:\@;">' . ($record->actual_production + $record->qty_wip + $record->ng) . '</td>
                                <td style="mso-number-format:\@;">' . ($record->rfg?? '0') . '</td>
                                <td style="mso-number-format:\@;">' . (($record->actual_production + $record->qty_wip + $record->ng) - $record->rfg) . '</td>
                                <td style="mso-number-format:\@;">' . (($record->actual_production + $record->qty_wip) - $record->rfg) . '</td>
                            </tr>';
                    $no++;
                }
            }
            $html .= '</table></body></html>';
            echo $html;
        }
    }

    public function print_rm($option = "")
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
        $filter_workorder = $this->input->get("filter_workorder");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        if ($filter_items !="") {
            $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$filter_items' and trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%'");

            $dataNgs = $this->crud->query("
                                            select aa.workorder, aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                    select distinct workorder,trans_date,document,item_fg_id, qty_product FROM item_ng where item_fg_id='$filter_items' and trans_date between '$filter_from' and '$filter_to' AND shift like '%$filter_shift%' AND kind LIKE 'Ng Process Production'
                                            ) aa group by aa.document,aa.trans_date,aa.item_fg_id
            ");

            $dataSubcontsJasas = $this->crud->query("
                                            select aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                    select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                    FROM supply_sheets ax 
                                                    join item_fg ay on ax.item_fg_id=ay.id 
                                                    where ax.item_fg_id='$filter_items' and ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa'
                                            ) aa group by aa.workorder,aa.request_date,aa.item_fg_id
            ");

            $dataRfgSubcontsJasas = $this->crud->query("
                                                select ab.packing_date as trans_date,ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg 
                                                FROM scan_item_receipts_fg aa 
                                                JOIN checksheets ab on aa.checksheet_number = ab.number
                                                where ab.item_fg_id='$filter_items' AND ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%'
                                                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
            ");

            $dataAdjIns = $this->crud->query("
                                            select *
                                            FROM wip_adjustment_fg a
                                            where a.item_fg_id='$filter_items' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN'
            ");

            $dataAdjOuts = $this->crud->query("
                                            select *
                                            FROM wip_adjustment_fg a
                                            where a.item_fg_id='$filter_items' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT'
            ");

            $receipts = $this->crud->query("
                                            SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                                            FROM scan_item_receipts_fg f
                                            JOIN checksheets e ON e.number = f.checksheet_number
                                            LEFT JOIN users c ON f.created_by = c.username
                                            WHERE e.item_fg_id = '$filter_items'  and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' and e.status_subcont='NO' and e.shift like '%$filter_shift%'");

            // $receiptsNB = $this->crud->query("
            //                                 SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
            //                                 FROM new_barcode_fg a
            //                                 LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
            //                                 LEFT JOIN users u ON f.created_by = u.username
            //                                 WHERE a.item_fg_id = '$filter_items'  AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

            $receiptsWIP = $this->crud->query("
                                            SELECT a.*, u.name as username, 'WIP RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                                            FROM wip_receipts a
                                            LEFT JOIN users u ON a.created_by = u.username
                                            WHERE a.item_fg_id = '$filter_items' AND a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

            $transFgs = $this->crud->query("
                                            SELECT *
                                            FROM transaction_fg a
                                            WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND a.item_fg_id = '$filter_items' AND a.request_date BETWEEN '$filter_from' and '$filter_to'");

            // Proses data berdasarkan tanggal
            $all_data = [];

            foreach ($dataActualProductions as $actualProduction) {
                $all_data[] = [
                    'period' => $actualProduction->period,
                    'date' => $actualProduction->trans_date,
                    'wo_no' => $actualProduction->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => $actualProduction->qty,
                    'qty_wip' => $actualProduction->qty_wip,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataNgs as $dataNg) {
                $all_data[] = [
                    'period' => '',
                    'date' => $dataNg->trans_date,
                    'wo_no' => $dataNg->workorder,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => $dataNg->qty_ng,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                $all_data[] = [
                    'period' => $dataSubcontsJasa->period,
                    'date' => $dataSubcontsJasa->request_date,
                    'wo_no' => $dataSubcontsJasa->workorder,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => $dataSubcontsJasa->qty_subcont_jasa,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($receipts as $receipt) {
                $all_data[] = [
                    'period' => '',
                    'date' => $receipt->trans_date,
                    'wo_no' => $receipt->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $receipt->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            // foreach ($receiptsNB as $receiptNB) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $receiptNB->trans_date,
            //         'wo_no' => $receiptNB->wo_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => 0,
            //         'rfg' => $receiptNB->qty,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => 0,
            //     ];
            // }

            foreach ($receiptsWIP as $receiptWIP) {
                $all_data[] = [
                    'period' => '',
                    'date' => $receiptWIP->trans_date,
                    'wo_no' => $receiptWIP->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $receiptWIP->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($transFgs as $transFg) {
                $all_data[] = [
                    'period' => '',
                    'date' => $transFg->request_date,
                    'wo_no' => $transFg->request_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $transFg->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                $all_data[] = [
                    'period' => '',
                    'date' => $dataRfgSubcontsJasa->trans_date,
                    'wo_no' => $dataRfgSubcontsJasa->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => $dataRfgSubcontsJasa->qty_rfg,
                    'qty_adj_out' => 0,
                ];
            }

            // foreach ($dataAdjIns  as $dataAdjIn) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $dataAdjIn->request_date,
            //         'wo_no' => $dataAdjIn->request_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => $dataAdjIn->qty,
            //         'rfg' => 0,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => 0,
            //     ];
            // }

            // foreach ($dataAdjOuts  as $dataAdjOut) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $dataAdjOut->request_date,
            //         'wo_no' => $dataAdjOut->request_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => 0,
            //         'rfg' => 0,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => $dataAdjOut->qty,
            //     ];
            // }

            // Urutkan data berdasarkan tanggal
            usort($all_data, function ($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });


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
                        <th rowspan="2" width="20">No</th>
                        <th rowspan="2">WO No</th>
                        <th rowspan="2">Period</th>
                        <th rowspan="2">Lot No</th>
                        <th rowspan="2">Wo Date</th>
                        <th rowspan="2">Product No</th>
                        <th rowspan="2">Product Name</th>
                        <th rowspan="2">UOM</th>
                        <th rowspan="2">WO Qty</th>
                        <th rowspan="2">Part No</th>
                        <th rowspan="2">Part Name</th>
                        <th rowspan="2">Uom</th>
                        <th rowspan="2">WO Qty</th>
                        <th colspan="2">OUTPUT PRODUCTION</th>
                        <th rowspan="2">NG Process</th>
                        <th rowspan="2">TOTAL PRODUCTION</th>
                        <th rowspan="2">RFG QTY</th>
                        <th rowspan="2">TOT PRD - RFG</th>
                        <th rowspan="2">(QTY FG+WIP) - RFG</th>
                    </tr>
                    <tr>
                        <th>Qty FG</th>
                        <th>Qty WIP</th>
                    </tr>';
            $no = 1;
            $grouped_data = [];
            foreach ($all_data as $row) {
                $key = $row['wo_no'];
                if (!isset($grouped_data[$key])) {
                    $grouped_data[$key] = (object) $row;
                } else {
                    
                    $grouped_data[$key]->actual_production += $row['actual_production'];
                    $grouped_data[$key]->qty_wip += $row['qty_wip'];
                    $grouped_data[$key]->ng += $row['ng'];
                    $grouped_data[$key]->subconts_jasa += $row['subconts_jasa'];
                    // $grouped_data[$key]->qty_adj_in += $row['qty_adj_in'];
                    // $grouped_data[$key]->qty_adj_out += $row['qty_adj_out'];
                    $grouped_data[$key]->rfg += $row['rfg'];
                    $grouped_data[$key]->rfg_subconts_jasa += $row['rfg_subconts_jasa'];
                }
            }

            $wo_nos = array_map(function($r) {
                return $r->wo_no;
            }, $grouped_data);

            $wo_nos_str = "'" . implode("','", $wo_nos) . "'";

            // Ambil dari supply_sheets
            $query = $this->db->query("SELECT DISTINCT a.workorder as wo_no, a.period, a.lot_no, a.qty_wo, b.trans_date, c.name, c.number, c.uom, e.name as name_rm, e.number as number_rm, e.uom as uom_rm, d.composition
                FROM supply_sheets a
                LEFT JOIN production_schedules b ON a.workorder = b.wo_no
                JOIN item_fg c ON a.item_fg_id = c.id 
                LEFT JOIN bom d ON c.id = d.item_fg_id
                LEFT JOIN item_rm e ON d.item_rm_id = e.id
                WHERE a.workorder IN ($wo_nos_str)");

            $supplyData = [];
            foreach ($query->result() as $row) {
                $supplyData[$row->wo_no][] = $row; // tampung banyak row per wo_no
            }

            // Cari yang belum dapat data
            $missing_wo_nos = array_diff($wo_nos, array_keys($supplyData));
            if (!empty($missing_wo_nos)) {
                $missing_wo_nos_str = "'" . implode("','", $missing_wo_nos) . "'";

                // Ambil dari item_ng
                $query_ng = $this->db->query("SELECT a.document AS wo_no, '-' AS period, '-' AS lot_no, a.qty_sh AS qty_wo, a.trans_date, b.name, b.number, b.uom, d.name as name_rm, d.number as number_rm, d.uom as uom_rm, c.composition
                    FROM item_ng a
                    LEFT JOIN item_fg b ON a.item_fg_id = b.id
                    LEFT JOIN bom c ON b.id = c.item_fg_id
                    LEFT JOIN item_rm d ON c.item_rm_id = d.id
                    WHERE a.document IN ($missing_wo_nos_str)");

                foreach ($query_ng->result() as $row) {
                    $supplyData[$row->wo_no][] = $row;
                }
            }

            // Cek lagi
            $missing_wo_nos2 = array_diff($wo_nos, array_keys($supplyData));
            if (!empty($missing_wo_nos2)) {
                $missing_wo_nos2_str = "'" . implode("','", $missing_wo_nos2) . "'";

                // Ambil dari checksheets
                $query_checksheet = $this->db->query("SELECT a.wo_no, '-' AS period, '-' AS lot_no, a.qty AS qty_wo, a.trans_date, b.name, b.number, b.uom, d.name as name_rm, d.number as number_rm, d.uom as uom_rm, c.composition
                    FROM checksheets a
                    LEFT JOIN item_fg b ON a.item_fg_id = b.id
                    LEFT JOIN bom c ON b.id = c.item_fg_id
                    LEFT JOIN item_rm d ON c.item_rm_id = d.id
                    WHERE a.wo_no IN ($missing_wo_nos2_str)");

                foreach ($query_checksheet->result() as $row) {
                    $supplyData[$row->wo_no][] = $row;
                }
            }
            
            foreach ($grouped_data as $record) {
                $supplies = isset($supplyData[$record->wo_no]) ? $supplyData[$record->wo_no] : [null];
                foreach ($supplies as $supply) {
                    $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td style="mso-number-format:\@;">' . $record->wo_no . '</td>
                        <td style="mso-number-format:\@;">' . ($record->period ?: ($supply->period ?? '-')) . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->lot_no ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->trans_date ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->number ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->name ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->uom ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->qty_wo ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->name_rm ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->number_rm ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->uom_rm ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . number_format($supply->qty_wo * $supply->composition,2) . '</td>
                        <td style="mso-number-format:\@;">' . number_format($record->actual_production * $supply->composition,2) . '</td>
                        <td style="mso-number-format:\@;">' . number_format(($record->qty_wip ?? '0') * ($supply->composition ?? 1) ,2) . '</td>
                        <td style="mso-number-format:\@;">' . number_format($record->ng * $supply->composition,2)  . '</td>
                        <td style="mso-number-format:\@;">' . number_format(((($record->actual_production ?? 0) * ($supply->composition ?? 1)) + (($record->qty_wip ?? 0) * ($supply->composition ?? 1)) + ($record->ng * $supply->composition)) , 2) . '</td>
                        <td style="mso-number-format:\@;">' . number_format($record->rfg * $supply->composition,2) . '</td>
                        <td style="mso-number-format:\@;">' . number_format((((($record->actual_production ?? 0) * ($supply->composition ?? 1)) + (($record->qty_wip ?? 0) * ($supply->composition ?? 1)) + ($record->ng * $supply->composition))) - ($record->rfg * $supply->composition) , 2) . '</td>
                        <td style="mso-number-format:\@;">' . number_format((($record->actual_production + $record->qty_wip) - $record->rfg) * $supply->composition,2) . '</td>
                    </tr>';
                    $no++;
                }
            }
            $html .= '</table></body></html>';
            echo $html;
        }elseif ($filter_workorder !="") {
            $dataActualProductions = $this->crud->query("select * FROM output_productions where trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%' AND wo_no like '%$filter_workorder%'");

            $dataNgs = $this->crud->query("
                                            select aa.workorder,aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                    select distinct workorder,trans_date,document,item_fg_id, qty_product FROM item_ng where trans_date between '$filter_from' and '$filter_to' AND shift like '%$filter_shift%' AND workorder like '%$filter_workorder%' AND kind LIKE 'Ng Process Production'
                                            ) aa group by aa.document,aa.trans_date,aa.item_fg_id
            ");

            $dataSubcontsJasas = $this->crud->query("
                                            select aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                    select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                    FROM supply_sheets ax 
                                                    join item_fg ay on ax.item_fg_id=ay.id 
                                                    where ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa' AND ax.workorder like '%$filter_workorder%'
                                            ) aa group by aa.workorder,aa.request_date,aa.item_fg_id
            ");

            $dataRfgSubcontsJasas = $this->crud->query("
                                                select ab.packing_date as trans_date,ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg 
                                                FROM scan_item_receipts_fg aa 
                                                JOIN checksheets ab on aa.checksheet_number = ab.number
                                                where ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%' AND ab.wo_no like '%$filter_workorder%'
                                                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
            ");

            $dataAdjIns = $this->crud->query("
                                            select *
                                            FROM wip_adjustment_fg a
                                            where a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN' AND request_no like '%$filter_workorder%'
            ");

            $dataAdjOuts = $this->crud->query("
                                            select *
                                            FROM wip_adjustment_fg a
                                            where a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT' AND request_no like '%$filter_workorder%'
            ");

            $receipts = $this->crud->query("
                                            SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                                            FROM scan_item_receipts_fg f
                                            JOIN checksheets e ON e.number = f.checksheet_number
                                            LEFT JOIN users c ON f.created_by = c.username
                                            WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' and e.status_subcont='NO' and e.shift like '%$filter_shift%' and f.wo_no like '%$filter_workorder%'");

            // $receiptsNB = $this->crud->query("
            //                                 SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
            //                                 FROM new_barcode_fg a
            //                                 LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
            //                                 LEFT JOIN users u ON f.created_by = u.username
            //                                 WHERE DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and f.wo_no like '%$filter_workorder%'");

            $receiptsWIP = $this->crud->query("
                                            SELECT a.*, u.name as username, 'WIP RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                                            FROM wip_receipts a
                                            LEFT JOIN users u ON a.created_by = u.username
                                            WHERE a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.wo_no like '%$filter_workorder%'");

            $transFgs = $this->crud->query("
                                            SELECT *
                                            FROM transaction_fg a
                                            WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND a.request_date BETWEEN '$filter_from' and '$filter_to' and a.request_no like '%$filter_workorder%'");

            // Proses data berdasarkan tanggal
            $all_data = [];

            foreach ($dataActualProductions as $actualProduction) {
                $all_data[] = [
                    'period' => $actualProduction->period,
                    'date' => $actualProduction->trans_date,
                    'wo_no' => $actualProduction->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => $actualProduction->qty,
                    'qty_wip' => $actualProduction->qty_wip,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataNgs as $dataNg) {
                $all_data[] = [
                    'period' => '',
                    'date' => $dataNg->trans_date,
                    'wo_no' => $dataNg->workorder,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => $dataNg->qty_ng,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                $all_data[] = [
                    'period' => $dataSubcontsJasa->period,
                    'date' => $dataSubcontsJasa->request_date,
                    'wo_no' => $dataSubcontsJasa->workorder,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => $dataSubcontsJasa->qty_subcont_jasa,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($receipts as $receipt) {
                $all_data[] = [
                    'period' => '',
                    'date' => $receipt->trans_date,
                    'wo_no' => $receipt->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $receipt->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            // foreach ($receiptsNB as $receiptNB) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $receiptNB->trans_date,
            //         'wo_no' => $receiptNB->wo_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => 0,
            //         'rfg' => $receiptNB->qty,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => 0,
            //     ];
            // }

            foreach ($receiptsWIP as $receiptWIP) {
                $all_data[] = [
                    'period' => '',
                    'date' => $receiptWIP->trans_date,
                    'wo_no' => $receiptWIP->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $receiptWIP->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($transFgs as $transFg) {
                $all_data[] = [
                    'period' => '',
                    'date' => $transFg->request_date,
                    'wo_no' => $transFg->request_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => $transFg->qty,
                    'rfg_subconts_jasa' => 0,
                    'qty_adj_out' => 0,
                ];
            }

            foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                $all_data[] = [
                    'period' => '',
                    'date' => $dataRfgSubcontsJasa->trans_date,
                    'wo_no' => $dataRfgSubcontsJasa->wo_no,
                    // 'wo_qty' => $record->qty_wo,
                    'actual_production' => 0,
                    'qty_wip' => 0,
                    'ng' => 0,
                    'subconts_jasa' => 0,
                    'qty_adj_in' => 0,
                    'rfg' => 0,
                    'rfg_subconts_jasa' => $dataRfgSubcontsJasa->qty_rfg,
                    'qty_adj_out' => 0,
                ];
            }

            // foreach ($dataAdjIns  as $dataAdjIn) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $dataAdjIn->request_date,
            //         'wo_no' => $dataAdjIn->request_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => $dataAdjIn->qty,
            //         'rfg' => 0,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => 0,
            //     ];
            // }

            // foreach ($dataAdjOuts  as $dataAdjOut) {
            //     $all_data[] = [
            //         'period' => '',
            //         'date' => $dataAdjOut->request_date,
            //         'wo_no' => $dataAdjOut->request_no,
            //         // 'wo_qty' => $record->qty_wo,
            //         'actual_production' => 0,
            //         'qty_wip' => 0,
            //         'ng' => 0,
            //         'subconts_jasa' => 0,
            //         'qty_adj_in' => 0,
            //         'rfg' => 0,
            //         'rfg_subconts_jasa' => 0,
            //         'qty_adj_out' => $dataAdjOut->qty,
            //     ];
            // }

            // Urutkan data berdasarkan tanggal
            usort($all_data, function ($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });


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
                        <th rowspan="2" width="20">No</th>
                        <th rowspan="2">WO No</th>
                        <th rowspan="2">Period</th>
                        <th rowspan="2">Lot No</th>
                        <th rowspan="2">Wo Date</th>
                        <th rowspan="2">Product No</th>
                        <th rowspan="2">Product Name</th>
                        <th rowspan="2">UOM</th>
                        <th rowspan="2">WO Qty</th>
                        <th rowspan="2">Part No</th>
                        <th rowspan="2">Part Name</th>
                        <th rowspan="2">Uom</th>
                        <th rowspan="2">WO Qty</th>
                        <th colspan="2">OUTPUT PRODUCTION</th>
                        <th rowspan="2">NG Process</th>
                        <th rowspan="2">TOTAL PRODUCTION</th>
                        <th rowspan="2">RFG QTY</th>
                        <th rowspan="2">TOT PRD - RFG</th>
                        <th rowspan="2">(QTY FG+WIP) - RFG</th>
                    </tr>
                    <tr>
                        <th>Qty FG</th>
                        <th>Qty WIP</th>
                    </tr>';
            $no = 1;
            $grouped_data = [];
            foreach ($all_data as $row) {
                $key = $row['wo_no'];
                if (!isset($grouped_data[$key])) {
                    $grouped_data[$key] = (object) $row;
                } else {
                    
                    $grouped_data[$key]->actual_production += $row['actual_production'];
                    $grouped_data[$key]->qty_wip += $row['qty_wip'];
                    $grouped_data[$key]->ng += $row['ng'];
                    $grouped_data[$key]->subconts_jasa += $row['subconts_jasa'];
                    // $grouped_data[$key]->qty_adj_in += $row['qty_adj_in'];
                    // $grouped_data[$key]->qty_adj_out += $row['qty_adj_out'];
                    $grouped_data[$key]->rfg += $row['rfg'];
                    $grouped_data[$key]->rfg_subconts_jasa += $row['rfg_subconts_jasa'];
                }
            }

            $wo_nos = array_map(function($r) {
                return $r->wo_no;
            }, $grouped_data);

            $wo_nos_str = "'" . implode("','", $wo_nos) . "'";

            // Ambil dari supply_sheets
            $query = $this->db->query("SELECT DISTINCT a.workorder as wo_no, a.period, a.lot_no, a.qty_wo, b.trans_date, c.name, c.number, c.uom, e.name as name_rm, e.number as number_rm, e.uom as uom_rm, d.composition
                FROM supply_sheets a
                JOIN production_schedules b ON a.workorder = b.wo_no
                JOIN item_fg c ON a.item_fg_id = c.id 
                JOIN bom d ON c.id = d.item_fg_id
                JOIN item_rm e ON d.item_rm_id = e.id
                WHERE a.workorder IN ($wo_nos_str)");

            $supplyData = [];
            foreach ($query->result() as $row) {
                $supplyData[$row->wo_no][] = $row; // tampung banyak row per wo_no
            }

            // Cari yang belum dapat data
            $missing_wo_nos = array_diff($wo_nos, array_keys($supplyData));
            if (!empty($missing_wo_nos)) {
                $missing_wo_nos_str = "'" . implode("','", $missing_wo_nos) . "'";

                // Ambil dari item_ng
                $query_ng = $this->db->query("SELECT a.document AS wo_no, '-' AS period, '-' AS lot_no, a.qty_sh AS qty_wo, a.trans_date, b.name, b.number, b.uom, d.name as name_rm, d.number as number_rm, d.uom as uom_rm, c.composition
                    FROM item_ng a
                    JOIN item_fg b ON a.item_fg_id = b.id
                    JOIN bom c ON b.id = c.item_fg_id
                    JOIN item_rm d ON c.item_rm_id = d.id
                    WHERE a.document IN ($missing_wo_nos_str)");

                foreach ($query_ng->result() as $row) {
                    $supplyData[$row->wo_no][] = $row;
                }
            }

            // Cek lagi
            $missing_wo_nos2 = array_diff($wo_nos, array_keys($supplyData));
            if (!empty($missing_wo_nos2)) {
                $missing_wo_nos2_str = "'" . implode("','", $missing_wo_nos2) . "'";

                // Ambil dari checksheets
                $query_checksheet = $this->db->query("SELECT a.wo_no, '-' AS period, '-' AS lot_no, a.qty AS qty_wo, a.trans_date, b.name, b.number, b.uom, d.name as name_rm, d.number as number_rm, d.uom as uom_rm, c.composition
                    FROM checksheets a
                    JOIN item_fg b ON a.item_fg_id = b.id
                    JOIN bom c ON b.id = c.item_fg_id
                    JOIN item_rm d ON c.item_rm_id = d.id
                    WHERE a.wo_no IN ($missing_wo_nos2_str)");

                foreach ($query_checksheet->result() as $row) {
                    $supplyData[$row->wo_no][] = $row;
                }
            }
            
            foreach ($grouped_data as $record) {
                $supplies = isset($supplyData[$record->wo_no]) ? $supplyData[$record->wo_no] : [null];

                foreach ($supplies as $supply) {
                    $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td style="mso-number-format:\@;">' . $record->wo_no . '</td>
                        <td style="mso-number-format:\@;">' . ($record->period ?: ($supply->period ?? '-')) . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->lot_no ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->trans_date ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->number ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->name ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->uom ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->qty_wo ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->number_rm ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->name_rm ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . ($supply->uom_rm ?? '-') . '</td>
                        <td style="mso-number-format:\@;">' . number_format($supply->qty_wo * $supply->composition,2) . '</td>
                        <td style="mso-number-format:\@;">' . number_format($record->actual_production * $supply->composition,2) . '</td>
                        <td style="mso-number-format:\@;">' . number_format(($record->qty_wip ?? '0') * ($supply->composition ?? 1) ,2) . '</td>
                        <td style="mso-number-format:\@;">' . number_format($record->ng * $supply->composition,2)  . '</td>
                        <td style="mso-number-format:\@;">' . number_format(((($record->actual_production ?? 0) * ($supply->composition ?? 1)) + (($record->qty_wip ?? 0) * ($supply->composition ?? 1)) + ($record->ng * $supply->composition)) , 2) . '</td>
                        <td style="mso-number-format:\@;">' . number_format($record->rfg * $supply->composition,2) . '</td>
                        <td style="mso-number-format:\@;">' . number_format((((($record->actual_production ?? 0) * ($supply->composition ?? 1)) + (($record->qty_wip ?? 0) * ($supply->composition ?? 1)) + ($record->ng * $supply->composition))) - ($record->rfg * $supply->composition) , 2) . '</td>
                        <td style="mso-number-format:\@;">' . number_format((($record->actual_production + $record->qty_wip) - $record->rfg) * $supply->composition,2) . '</td>
                    </tr>';
                    $no++;
                }
            }
            $html .= '</table></body></html>';
            echo $html;
        }elseif ($filter_division !=""){
            $itemFgs = $this->crud->query("select id FROM item_fg where status = '0' AND type !='RM' AND status_subcont = 'NO' AND division_id like '$filter_division' order by id");
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
                        <th rowspan="2" width="20">No</th>
                        <th rowspan="2">WO No</th>
                        <th rowspan="2">Period</th>
                        <th rowspan="2">Lot No</th>
                        <th rowspan="2">Wo Date</th>
                        <th rowspan="2">Product No</th>
                        <th rowspan="2">Product Name</th>
                        <th rowspan="2">UOM</th>
                        <th rowspan="2">WO Qty</th>
                        <th rowspan="2">Part No</th>
                        <th rowspan="2">Part Name</th>
                        <th rowspan="2">Uom</th>
                        <th rowspan="2">WO Qty</th>
                        <th colspan="2">OUTPUT PRODUCTION</th>
                        <th rowspan="2">NG Process</th>
                        <th rowspan="2">TOTAL PRODUCTION</th>
                        <th rowspan="2">RFG QTY</th>
                        <th rowspan="2">TOT PRD - RFG</th>
                        <th rowspan="2">(QTY FG+WIP) - RFG</th>
                    </tr>
                    <tr>
                        <th>Qty FG</th>
                        <th>Qty WIP</th>
                    </tr>';
            foreach ($itemFgs as $item) {
                $itemId = $item->id;
                $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$itemId' and trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%'");

                $dataNgs = $this->crud->query("
                                                select aa.workorder, aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                        select distinct workorder,trans_date,document,item_fg_id, qty_product FROM item_ng where item_fg_id='$itemId' and trans_date between '$filter_from' and '$filter_to' AND shift like '%$filter_shift%' AND kind LIKE 'Ng Process Production'
                                                ) aa group by aa.document,aa.trans_date,aa.item_fg_id
                ");

                $dataSubcontsJasas = $this->crud->query("
                                                select aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                        select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                        FROM supply_sheets ax 
                                                        join item_fg ay on ax.item_fg_id=ay.id 
                                                        where ax.item_fg_id='$itemId' and ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa'
                                                ) aa group by aa.workorder,aa.request_date,aa.item_fg_id
                ");

                $dataRfgSubcontsJasas = $this->crud->query("
                                                select ab.packing_date as trans_date,ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg 
                                                FROM scan_item_receipts_fg aa 
                                                JOIN checksheets ab on aa.checksheet_number = ab.number
                                                where ab.item_fg_id='$itemId' AND ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%'
                                                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
                ");

                $dataAdjIns = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$itemId' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN'
                ");

                $dataAdjOuts = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$itemId' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT'
                ");

                $receipts = $this->crud->query("
                                                SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                                                FROM scan_item_receipts_fg f
                                                JOIN checksheets e ON e.number = f.checksheet_number
                                                LEFT JOIN users c ON f.created_by = c.username
                                                WHERE e.item_fg_id = '$itemId'  and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' and e.status_subcont='NO' and e.shift like '%$filter_shift%'");

                // $receiptsNB = $this->crud->query("
                //                                 SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
                //                                 FROM new_barcode_fg a
                //                                 LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                //                                 LEFT JOIN users u ON f.created_by = u.username
                //                                 WHERE a.item_fg_id = '$itemId'  AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                $receiptsWIP = $this->crud->query("
                                                SELECT a.*, u.name as username, 'WIP RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                                                FROM wip_receipts a
                                                LEFT JOIN users u ON a.created_by = u.username
                                                WHERE a.item_fg_id = '$itemId' AND a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                $transFgs = $this->crud->query("
                                                SELECT *
                                                FROM transaction_fg a
                                                WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND a.item_fg_id = '$itemId' AND a.request_date BETWEEN '$filter_from' and '$filter_to'");

                // Proses data berdasarkan tanggal
                $all_data = [];

                foreach ($dataActualProductions as $actualProduction) {
                    $all_data[] = [
                        'period' => $actualProduction->period,
                        'date' => $actualProduction->trans_date,
                        'wo_no' => $actualProduction->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => $actualProduction->qty,
                        'qty_wip' => $actualProduction->qty_wip,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataNgs as $dataNg) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $dataNg->trans_date,
                        'wo_no' => $dataNg->workorder,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => $dataNg->qty_ng,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                    $all_data[] = [
                        'period' => $dataSubcontsJasa->period,
                        'date' => $dataSubcontsJasa->request_date,
                        'wo_no' => $dataSubcontsJasa->workorder,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => $dataSubcontsJasa->qty_subcont_jasa,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($receipts as $receipt) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $receipt->trans_date,
                        'wo_no' => $receipt->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $receipt->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                // foreach ($receiptsNB as $receiptNB) {
                //     $all_data[] = [
                //         'period' => '',
                //         'date' => $receiptNB->trans_date,
                //         'wo_no' => $receiptNB->wo_no,
                //         // 'wo_qty' => $record->qty_wo,
                //         'actual_production' => 0,
                //         'qty_wip' => 0,
                //         'ng' => 0,
                //         'subconts_jasa' => 0,
                //         'qty_adj_in' => 0,
                //         'rfg' => $receiptNB->qty,
                //         'rfg_subconts_jasa' => 0,
                //         'qty_adj_out' => 0,
                //     ];
                // }

                foreach ($receiptsWIP as $receiptWIP) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $receiptWIP->trans_date,
                        'wo_no' => $receiptWIP->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $receiptWIP->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($transFgs as $transFg) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $transFg->request_date,
                        'wo_no' => $transFg->request_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $transFg->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $dataRfgSubcontsJasa->trans_date,
                        'wo_no' => $dataRfgSubcontsJasa->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => $dataRfgSubcontsJasa->qty_rfg,
                        'qty_adj_out' => 0,
                    ];
                }

                // foreach ($dataAdjIns  as $dataAdjIn) {
                //     $all_data[] = [
                //         'period' => '',
                //         'date' => $dataAdjIn->request_date,
                //         'wo_no' => $dataAdjIn->request_no,
                //         // 'wo_qty' => $record->qty_wo,
                //         'actual_production' => 0,
                //         'qty_wip' => 0,
                //         'ng' => 0,
                //         'subconts_jasa' => 0,
                //         'qty_adj_in' => $dataAdjIn->qty,
                //         'rfg' => 0,
                //         'rfg_subconts_jasa' => 0,
                //         'qty_adj_out' => 0,
                //     ];
                // }

                // foreach ($dataAdjOuts  as $dataAdjOut) {
                //     $all_data[] = [
                //         'period' => '',
                //         'date' => $dataAdjOut->request_date,
                //         'wo_no' => $dataAdjOut->request_no,
                //         // 'wo_qty' => $record->qty_wo,
                //         'actual_production' => 0,
                //         'qty_wip' => 0,
                //         'ng' => 0,
                //         'subconts_jasa' => 0,
                //         'qty_adj_in' => 0,
                //         'rfg' => 0,
                //         'rfg_subconts_jasa' => 0,
                //         'qty_adj_out' => $dataAdjOut->qty,
                //     ];
                // }

                // Urutkan data berdasarkan tanggal
                usort($all_data, function ($a, $b) {
                    return strtotime($a['date']) - strtotime($b['date']);
                });

                $no = 1;
                $grouped_data = [];
                foreach ($all_data as $row) {
                    $key = $row['wo_no'];
                    if (!isset($grouped_data[$key])) {
                        $grouped_data[$key] = (object) $row;
                    } else {
                        
                        $grouped_data[$key]->actual_production += $row['actual_production'];
                        $grouped_data[$key]->qty_wip += $row['qty_wip'];
                        $grouped_data[$key]->ng += $row['ng'];
                        $grouped_data[$key]->subconts_jasa += $row['subconts_jasa'];
                        // $grouped_data[$key]->qty_adj_in += $row['qty_adj_in'];
                        // $grouped_data[$key]->qty_adj_out += $row['qty_adj_out'];
                        $grouped_data[$key]->rfg += $row['rfg'];
                        $grouped_data[$key]->rfg_subconts_jasa += $row['rfg_subconts_jasa'];
                    }
                }

                $wo_nos = array_map(function($r) {
                    return $r->wo_no;
                }, $grouped_data);

                $wo_nos_str = "'" . implode("','", $wo_nos) . "'";

                $query = $this->db->query("SELECT a.workorder as wo_no, a.period, a.lot_no, a.qty_wo, b.trans_date, c.name, c.number, c.uom, e.name as name_rm, e.number as number_rm, e.uom as uom_rm, d.composition
                    FROM supply_sheets a
                    LEFT JOIN production_schedules b ON a.workorder = b.wo_no
                    JOIN item_fg c ON a.item_fg_id = c.id 
                    LEFT JOIN bom d ON c.id = d.item_fg_id
                    LEFT JOIN item_rm e ON d.item_rm_id = e.id
                    WHERE a.workorder IN ($wo_nos_str)");

                $supplyData = [];
                foreach ($query->result() as $row) {
                    $supplyData[$row->wo_no][] = $row; // tampung banyak row per wo_no
                }

                // Cari yang belum dapat data
                $missing_wo_nos = array_diff($wo_nos, array_keys($supplyData));
                if (!empty($missing_wo_nos)) {
                    $missing_wo_nos_str = "'" . implode("','", $missing_wo_nos) . "'";

                    // Ambil dari item_ng
                    $query_ng = $this->db->query("SELECT a.document AS wo_no, '-' AS period, '-' AS lot_no, a.qty_sh AS qty_wo, a.trans_date, b.name, b.number, b.uom, d.name as name_rm, d.number as number_rm, d.uom as uom_rm, c.composition
                        FROM item_ng a
                        LEFT JOIN item_fg b ON a.item_fg_id = b.id
                        LEFT JOIN bom c ON b.id = c.item_fg_id
                        LEFT JOIN item_rm d ON c.item_rm_id = d.id
                        WHERE a.document IN ($missing_wo_nos_str)");

                    foreach ($query_ng->result() as $row) {
                        $supplyData[$row->wo_no][] = $row;
                    }
                }

                // Cek lagi
                $missing_wo_nos2 = array_diff($wo_nos, array_keys($supplyData));
                if (!empty($missing_wo_nos2)) {
                    $missing_wo_nos2_str = "'" . implode("','", $missing_wo_nos2) . "'";

                    // Ambil dari checksheets
                    $query_checksheet = $this->db->query("SELECT a.wo_no, '-' AS period, '-' AS lot_no, a.qty AS qty_wo, a.trans_date, b.name, b.number, b.uom, d.name as name_rm, d.number as number_rm, d.uom as uom_rm, c.composition
                        FROM checksheets a
                        LEFT JOIN item_fg b ON a.item_fg_id = b.id
                        LEFT JOIN bom c ON b.id = c.item_fg_id
                        LEFT JOIN item_rm d ON c.item_rm_id = d.id
                        WHERE a.wo_no IN ($missing_wo_nos2_str)");

                    foreach ($query_checksheet->result() as $row) {
                        $supplyData[$row->wo_no][] = $row;
                    }
                }
                                
                 foreach ($grouped_data as $record) {
                    $supplies = isset($supplyData[$record->wo_no]) ? $supplyData[$record->wo_no] : [null];
                    foreach ($supplies as $supply) {
                        $composition = isset($supply->composition) ? $supply->composition : 0;
                        $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $record->wo_no . '</td>
                            <td style="mso-number-format:\@;">' . ($record->period ?: ($supply->period ?? '-')) . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->lot_no ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->trans_date ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->number ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->name ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->uom ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->qty_wo ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->name_rm ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->number_rm ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . ($supply->uom_rm ?? '-') . '</td>
                            <td style="mso-number-format:\@;">' . number_format($supply->qty_wo ?? 0 * $composition ?? 0,2) . '</td>
                            <td style="mso-number-format:\@;">' . number_format($record->actual_production * $composition ?? 0,2) . '</td>
                            <td style="mso-number-format:\@;">' . number_format(($record->qty_wip ?? 0) * ($composition ?? 0) ,2) . '</td>
                            <td style="mso-number-format:\@;">' . number_format($record->ng * $composition ?? 0,2)  . '</td>
                            <td style="mso-number-format:\@;">' . number_format(((($record->actual_production ?? 0) * ($composition ?? 0)) + (($record->qty_wip ?? 0) * ($composition ?? 0)) + ($record->ng * $composition ?? 0)) , 2) . '</td>
                            <td style="mso-number-format:\@;">' . number_format($record->rfg * $composition ?? 0,2) . '</td>
                            <td style="mso-number-format:\@;">' . number_format((((($record->actual_production ?? 0) * ($composition ?? 0)) + (($record->qty_wip ?? 0) * ($composition ?? 0)) + ($record->ng * $composition))) - ($record->rfg * $composition ?? 0) , 2) . '</td>
                            <td style="mso-number-format:\@;">' . number_format((($record->actual_production + $record->qty_wip ?? 0) - $record->rfg) * $composition ?? 0,2) . '</td>
                        </tr>';
                        $no++;
                    }
                }
            }
            $html .= '</table></body></html>';
            echo $html;
        }else{ //belum di tambahkan data rm
            $itemFgs = $this->crud->query("select id FROM item_fg where status = '0' AND type !='RM' AND status_subcont = 'NO' order by id");
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
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2">WO No</th>
                            <th rowspan="2">Period</th>
                            <th rowspan="2">Lot No</th>
                            <th rowspan="2">Wo Date</th>
                            <th rowspan="2">Product No</th>
                            <th rowspan="2">Product Name</th>
                            <th rowspan="2">UOM</th>
                            <th rowspan="2">WO Qty</th>
                            <th colspan="3">PRD QTY</th>
                            <th rowspan="2">NG Process</th>
                            <th rowspan="2">RFG QTY</th>
                            <th rowspan="2">PROD QTY - RFG QTY</th>
                        </tr>
                        <tr>
                            <th>Qty FG</th>
                            <th>Qty WIP</th>
                            <th>Qty TOTAL</th>
                        </tr>';
            foreach ($itemFgs as $item) {
                $itemId = $item->id;
                $dataActualProductions = $this->crud->query("select * FROM output_productions where item_fg_id='$itemId' and trans_date between '$filter_from' and '$filter_to'  AND shift like '%$filter_shift%'");

                $dataNgs = $this->crud->query("
                                                select aa.workorder, aa.trans_date,aa.document,aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                                        select distinct workorder,trans_date,document,item_fg_id, qty_product FROM item_ng where item_fg_id='$itemId' and trans_date between '$filter_from' and '$filter_to' AND shift like '%$filter_shift%' AND kind LIKE 'Ng Process Production'
                                                ) aa group by aa.document,aa.trans_date,aa.item_fg_id
                ");

                $dataSubcontsJasas = $this->crud->query("
                                                select aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa FROM (
                                                        select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo 
                                                        FROM supply_sheets ax 
                                                        join item_fg ay on ax.item_fg_id=ay.id 
                                                        where ax.item_fg_id='$itemId' and ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa'
                                                ) aa group by aa.workorder,aa.request_date,aa.item_fg_id
                ");

                $dataRfgSubcontsJasas = $this->crud->query("
                                                select ab.packing_date as trans_date,ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg 
                                                FROM scan_item_receipts_fg aa 
                                                JOIN checksheets ab on aa.checksheet_number = ab.number
                                                where ab.item_fg_id='$itemId' AND ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%'
                                                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
                ");

                $dataAdjIns = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$itemId' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ IN'
                ");

                $dataAdjOuts = $this->crud->query("
                                                select *
                                                FROM wip_adjustment_fg a
                                                where a.item_fg_id='$itemId' and a.request_date between '$filter_from' AND '$filter_to' and a.transaction_type='ADJ OUT'
                ");

                $receipts = $this->crud->query("
                                                SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                                                FROM scan_item_receipts_fg f
                                                JOIN checksheets e ON e.number = f.checksheet_number
                                                LEFT JOIN users c ON f.created_by = c.username
                                                WHERE e.item_fg_id = '$itemId'  and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' and e.status_subcont='NO' and e.shift like '%$filter_shift%'");

                // $receiptsNB = $this->crud->query("
                //                                 SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
                //                                 FROM new_barcode_fg a
                //                                 LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                //                                 LEFT JOIN users u ON f.created_by = u.username
                //                                 WHERE a.item_fg_id = '$itemId'  AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                $receiptsWIP = $this->crud->query("
                                                SELECT a.*, u.name as username, 'WIP RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                                                FROM wip_receipts a
                                                LEFT JOIN users u ON a.created_by = u.username
                                                WHERE a.item_fg_id = '$itemId' AND a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                $transFgs = $this->crud->query("
                                                SELECT *
                                                FROM transaction_fg a
                                                WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND a.item_fg_id = '$itemId' AND a.request_date BETWEEN '$filter_from' and '$filter_to'");

                // Proses data berdasarkan tanggal
                $all_data = [];

                foreach ($dataActualProductions as $actualProduction) {
                    $all_data[] = [
                        'period' => $actualProduction->period,
                        'date' => $actualProduction->trans_date,
                        'wo_no' => $actualProduction->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => $actualProduction->qty,
                        'qty_wip' => $actualProduction->qty_wip,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataNgs as $dataNg) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $dataNg->trans_date,
                        'wo_no' => $dataNg->workorder,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => $dataNg->qty_ng,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                    $all_data[] = [
                        'period' => $dataSubcontsJasa->period,
                        'date' => $dataSubcontsJasa->request_date,
                        'wo_no' => $dataSubcontsJasa->workorder,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => $dataSubcontsJasa->qty_subcont_jasa,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($receipts as $receipt) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $receipt->trans_date,
                        'wo_no' => $receipt->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $receipt->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                // foreach ($receiptsNB as $receiptNB) {
                //     $all_data[] = [
                //         'period' => '',
                //         'date' => $receiptNB->trans_date,
                //         'wo_no' => $receiptNB->wo_no,
                //         // 'wo_qty' => $record->qty_wo,
                //         'actual_production' => 0,
                //         'qty_wip' => 0,
                //         'ng' => 0,
                //         'subconts_jasa' => 0,
                //         'qty_adj_in' => 0,
                //         'rfg' => $receiptNB->qty,
                //         'rfg_subconts_jasa' => 0,
                //         'qty_adj_out' => 0,
                //     ];
                // }

                foreach ($receiptsWIP as $receiptWIP) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $receiptWIP->trans_date,
                        'wo_no' => $receiptWIP->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $receiptWIP->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($transFgs as $transFg) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $transFg->request_date,
                        'wo_no' => $transFg->request_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => $transFg->qty,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $dataRfgSubcontsJasa->trans_date,
                        'wo_no' => $dataRfgSubcontsJasa->wo_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => $dataRfgSubcontsJasa->qty_rfg,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataAdjIns  as $dataAdjIn) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $dataAdjIn->request_date,
                        'wo_no' => $dataAdjIn->request_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => $dataAdjIn->qty,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => 0,
                    ];
                }

                foreach ($dataAdjOuts  as $dataAdjOut) {
                    $all_data[] = [
                        'period' => '',
                        'date' => $dataAdjOut->request_date,
                        'wo_no' => $dataAdjOut->request_no,
                        // 'wo_qty' => $record->qty_wo,
                        'actual_production' => 0,
                        'qty_wip' => 0,
                        'ng' => 0,
                        'subconts_jasa' => 0,
                        'qty_adj_in' => 0,
                        'rfg' => 0,
                        'rfg_subconts_jasa' => 0,
                        'qty_adj_out' => $dataAdjOut->qty,
                    ];
                }

                // Urutkan data berdasarkan tanggal
                usort($all_data, function ($a, $b) {
                    return strtotime($a['date']) - strtotime($b['date']);
                });

                $no = 1;
                $grouped_data = [];
                foreach ($all_data as $row) {
                    $key = $row['wo_no'];
                    if (!isset($grouped_data[$key])) {
                        $grouped_data[$key] = (object) $row;
                    } else {
                        
                        $grouped_data[$key]->actual_production += $row['actual_production'];
                        $grouped_data[$key]->qty_wip += $row['qty_wip'];
                        $grouped_data[$key]->ng += $row['ng'];
                        $grouped_data[$key]->subconts_jasa += $row['subconts_jasa'];
                        $grouped_data[$key]->qty_adj_in += $row['qty_adj_in'];
                        $grouped_data[$key]->qty_adj_out += $row['qty_adj_out'];
                        $grouped_data[$key]->rfg += $row['rfg'];
                        $grouped_data[$key]->rfg_subconts_jasa += $row['rfg_subconts_jasa'];
                    }
                }

                $wo_nos = array_map(function($r) {
                    return $r->wo_no;
                }, $grouped_data);

                $wo_nos_str = "'" . implode("','", $wo_nos) . "'";

                $query = $this->db->query("SELECT a.workorder as wo_no, a.period, a.lot_no, a.qty_wo, b.trans_date , c.name , c.number, c.uom
                FROM supply_sheets a
                LEFT JOIN production_schedules b ON a.workorder = b.wo_no
                JOIN item_fg c ON a.item_fg_id = c.id 
                WHERE a.workorder IN ($wo_nos_str)");
                $supplyData = [];
                foreach ($query->result() as $row) {
                    $supplyData[$row->wo_no] = $row;
                }

                // Cari wo_no yang tidak ditemukan di supply_sheets
                $missing_wo_nos = array_diff($wo_nos, array_keys($supplyData));

                if (!empty($missing_wo_nos)) {
                    $missing_wo_nos_str = "'" . implode("','", $missing_wo_nos) . "'";

                    // Ambil data dari item_ng sebagai fallback
                    $query_ng = $this->db->query(" SELECT DISTINCT a.document AS wo_no, '-' AS period, '-' AS lot_no, a.qty_sh AS qty_wo, a.trans_date, b.name, b.number
                        FROM item_ng a
                        LEFT JOIN item_fg b ON a.item_fg_id = b.id
                        WHERE a.document IN ($missing_wo_nos_str)
                    ");

                    foreach ($query_ng->result() as $row) {
                        $supplyData[$row->wo_no] = $row; // Tambahkan sebagai fallback
                    }
                }

                $missing_wo_nos2 = array_diff($wo_nos, array_keys($supplyData));

                if (!empty($missing_wo_nos2)) {
                    $missing_wo_nos2_str = "'" . implode("','", $missing_wo_nos2) . "'";

                    // Ambil data dari checksheet sebagai fallback terakhir
                    $query_checksheet = $this->db->query("
                        SELECT DISTINCT a.wo_no, '-' AS period, '-' AS lot_no, a.qty AS qty_wo, a.trans_date, b.name, b.number, b.uom
                        FROM checksheets a
                        LEFT JOIN item_fg b ON a.item_fg_id = b.id
                        WHERE a.wo_no IN ($missing_wo_nos2_str)
                    ");

                    foreach ($query_checksheet->result() as $row) {
                        $supplyData[$row->wo_no] = $row; // Tambahkan hasil checksheet
                    }
                }
                                
                foreach ($grouped_data as $record) {
                    $supply = isset($supplyData[$record->wo_no]) ? $supplyData[$record->wo_no] : null;
                    $html .= '<tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td style="mso-number-format:\@;">' . $record->wo_no . '</td>
                                <td style="mso-number-format:\@;">' . ($record->period ?: ($supply->period ?? '-')) . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->lot_no ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->trans_date ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->number ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->name ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->uom ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($supply->qty_wo ?? '-') . '</td>
                                <td style="mso-number-format:\@;">' . ($record->actual_production?? '0') . '</td>
                                <td style="mso-number-format:\@;">' . ($record->qty_wip?? '0') . '</td>
                                <td style="mso-number-format:\@;">' . ($record->actual_production + $record->qty_wip) . '</td>
                                <td style="mso-number-format:\@;">' . ($record->ng?? '0') . '</td>
                                <td style="mso-number-format:\@;">' . ($record->rfg?? '0') . '</td>
                                <td style="mso-number-format:\@;">' . (($record->actual_production + $record->qty_wip) - $record->rfg) . '</td>
                            </tr>';
                    $no++;
                }
            }
            $html .= '</table></body></html>';
            echo $html;
        }
    }
}
