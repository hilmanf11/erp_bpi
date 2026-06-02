<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_history_transactions_other_component extends CI_Controller
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
            $this->load->view('warehouse/report_history_transactions_other_component');
        } else {
            redirect('error_access');
        }
    }

    public function readsItem()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name FROM item_rm a JOIN item_familys b ON a.item_family_id = b.id WHERE (a.number like '%$post%' or a.name like '$post') AND (a.item_category_id = 'C06' OR (a.division = 'INJ' AND a.item_category_id = 'C11' AND a.item_family_id IN ('P05','P33')))");
        echo json_encode($send);
    }

    public function readsItems($id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_family_name FROM item_rm a JOIN item_familys b ON a.item_family_id = b.id WHERE a.item_family_id like '%$id%'");
        echo json_encode($send);
    }

    public function readsnotfg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM item_categories WHERE name LIKE '%$post%' AND number != 'FG' AND id IN ('C06','C11') AND `status` = '0'");
        // $send = $this->crud->reads('item_categories', ["name" => $post]);
        echo json_encode($send);
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
            ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - 
            (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
                        FROM item_rm a
                        LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$date'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$date' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$date' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$date' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$date' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$date' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
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

        $filter_from_minus1 = date('Y-m-01', strtotime('-1 month', strtotime($filter_from)));
        $filter_to_minus1   = date('Y-m-t',  strtotime('-1 month', strtotime($filter_from)));
        $filter_from_minus2 = date('Y-m-01', strtotime('-2 month', strtotime($filter_from)));
        $filter_to_minus2   = date('Y-m-t',  strtotime('-2 month', strtotime($filter_from)));
        $filter_from_minus3 = date('Y-m-01', strtotime('-3 month', strtotime($filter_from)));
        $filter_to_minus3   = date('Y-m-t',  strtotime('-3 month', strtotime($filter_from)));

        //------------------------------------ Mengambil Filter dari Input GET berakhir disini----------------------------------//

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Config ISO
        $this->db->select('*');
        $this->db->from('config_iso');
        $config_iso = $this->db->get()->row();
        $formHistoricalRM = !empty($config_iso->form_historical_rm) ? $config_iso->form_historical_rm : 'DOC';
        
        //------------------------------------ Mengambil data dari Tabel Config berakhir disini----------------------------------//

        $query_main = "SELECT 
            a.id,
            a.number, 
            a.name, 
            a.division, 
            b.name as prodfam, 
            a.uom,
            c.name as category_name,
            ifs.name as prodfam_sub_name,
            COALESCE(j.begin_stock, 0) AS begin_stock,
            (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_trans_other_in, 0) + COALESCE(f.qty_trans_other_adj_in, 0) + COALESCE(g.qty_in_pur, 0)) AS qty_in,
            (COALESCE(h.qty_trans_other_out, 0) + COALESCE(i.qty_trans_other_adj_out, 0) + COALESCE(k.qty_scan_out, 0) + COALESCE(l.qty_dn_scrap, 0) + COALESCE(m.qty_issued, 0)) AS qty_out,
            (COALESCE(begin_whs.begin_bpi, 0) + COALESCE(y.in_bpi,0) - COALESCE(y.out_bpi,0) + COALESCE(d_bpi.qty_scan_in_bpi, 0)) AS qty_bpi,
            (COALESCE(begin_whs.begin_plant1, 0)  + COALESCE(y.in_plant1,0) - COALESCE(y.out_plant1,0) + COALESCE(d_plant1.qty_scan_in_plant1, 0)) AS qty_plant1
        
        FROM item_rm a
        JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
        JOIN item_categories c ON a.item_category_id = c.id
        LEFT JOIN item_family_subs ifs ON a.item_sub_family_id = ifs.id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_in FROM scan_item_receipt_crusher WHERE request_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in_bpi FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' AND a.plant = 'BPI' GROUP BY b.item_rm_id) d_bpi ON a.id = d_bpi.item_rm_id
        LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in_plant1 FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' AND a.plant = 'PLANT 1' GROUP BY b.item_rm_id) d_plant1 ON a.id = d_plant1.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_other_in FROM transaction_other_component WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_type = 'ITEM IN' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_other_adj_in FROM transaction_other_component WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_type = 'ADJ IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (
            SELECT 
                item_id,
                number,
                SUM(qty) AS qty_in_pur
            FROM (
                SELECT 
                    b.id AS item_id,
                    b.number,
                    SUM(ic.qty) AS qty
                FROM input_crushing ic
                JOIN item_rm b ON ic.item_rm_id = b.id
                LEFT JOIN item_family_subs c ON b.item_sub_family_id = c.id
                WHERE ic.trans_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY b.id, b.number

                UNION ALL

                SELECT 
                    pur.id AS item_id,
                    pur.number,
                    SUM(ic.qty) AS qty
                FROM input_crushing ic
                JOIN item_rm b ON ic.item_rm_id = b.id
                JOIN item_family_subs c ON b.item_sub_family_id = c.id
                JOIN item_rm pur ON pur.number IN ('PUR-PC','PUR-ABS','PUR-ASA','PUR-PA6','PUR-PA66','PUR-PBT','PUR-POM','PUR-PP','PUR-PVC')
                WHERE (
                    (c.id = 'PS005' AND pur.number = 'PUR-PC')
                    OR (c.id = 'PS002' AND pur.number = 'PUR-ABS')
                    OR (c.id = 'PS003' AND pur.number = 'PUR-ASA')
                    OR (c.id = 'PS007' AND pur.number = 'PUR-PA6')
                    OR (c.id = 'PS008' AND pur.number = 'PUR-PA66')
                    OR (c.id = 'PS009' AND pur.number = 'PUR-PBT')
                    OR (c.id = 'PS006' AND pur.number = 'PUR-PMMA')
                    OR (c.id = 'PS010' AND pur.number = 'PUR-POM')
                    OR (c.id = 'PS004' AND pur.number = 'PUR-PP')
                    OR (c.id = 'PS001' AND pur.number = 'PUR-PVC')
                )
                AND ic.trans_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY pur.id, pur.number
            ) combined
            GROUP BY item_id, number
        ) g ON a.id = g.item_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_other_out FROM transaction_other_component WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_type = 'ITEM OUT'  GROUP BY item_rm_id) h ON a.id = h.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_other_adj_out FROM transaction_other_component WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_type = 'ADJ OUT' GROUP BY item_rm_id) i ON a.id = i.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_out FROM scan_dn_crusher WHERE created_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) k ON a.id = k.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_dn_scrap FROM dn_scrap WHERE transaction_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) l ON a.id = l.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and type = 'Other' GROUP BY item_rm_id) m ON a.id = m.item_rm_id

        LEFT JOIN (SELECT a.id, a.number, (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_trans_other_in, 0) + COALESCE(f.qty_trans_other_adj_in, 0) + COALESCE(g.qty_in_pur, 0)) - (COALESCE(h.qty_trans_other_out, 0) + COALESCE(i.qty_trans_other_adj_out, 0) + COALESCE(k.qty_scan_out, 0) + COALESCE(l.qty_dn_scrap, 0) + COALESCE(m.qty_issued, 0)) AS begin_stock
                        FROM item_rm a
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_in FROM scan_item_receipt_crusher WHERE request_date <= '$filter_from' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_other_in FROM transaction_other_component WHERE request_date <= '$filter_from' AND transaction_type = 'ITEM IN' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_other_adj_in FROM transaction_other_component WHERE request_date <= '$filter_from' AND transaction_type = 'ADJ IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                        LEFT JOIN (
                            SELECT 
                                item_id,
                                number,
                                SUM(qty) AS qty_in_pur
                            FROM (
                                SELECT 
                                    b.id AS item_id,
                                    b.number,
                                    SUM(ic.qty) AS qty
                                FROM input_crushing ic
                                JOIN item_rm b ON ic.item_rm_id = b.id
                                LEFT JOIN item_family_subs c ON b.item_sub_family_id = c.id
                                WHERE ic.trans_date <= '$filter_from'
                                GROUP BY b.id, b.number

                                UNION ALL

                                SELECT 
                                    pur.id AS item_id,
                                    pur.number,
                                    SUM(ic.qty) AS qty
                                FROM input_crushing ic
                                JOIN item_rm b ON ic.item_rm_id = b.id
                                JOIN item_family_subs c ON b.item_sub_family_id = c.id
                                JOIN item_rm pur ON pur.number IN ('PUR-PC','PUR-ABS','PUR-ASA','PUR-PA6','PUR-PA66','PUR-PBT','PUR-POM','PUR-PP','PUR-PVC')
                                WHERE (
                                    (c.id = 'PS005' AND pur.number = 'PUR-PC')
                                    OR (c.id = 'PS002' AND pur.number = 'PUR-ABS')
                                    OR (c.id = 'PS003' AND pur.number = 'PUR-ASA')
                                    OR (c.id = 'PS007' AND pur.number = 'PUR-PA6')
                                    OR (c.id = 'PS008' AND pur.number = 'PUR-PA66')
                                    OR (c.id = 'PS009' AND pur.number = 'PUR-PBT')
                                    OR (c.id = 'PS006' AND pur.number = 'PUR-PMMA')
                                    OR (c.id = 'PS010' AND pur.number = 'PUR-POM')
                                    OR (c.id = 'PS004' AND pur.number = 'PUR-PP')
                                    OR (c.id = 'PS001' AND pur.number = 'PUR-PVC')
                                )
                                AND ic.trans_date <= '$filter_from'
                                GROUP BY pur.id, pur.number
                            ) combined
                            GROUP BY item_id, number
                        ) g ON a.id = g.item_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_other_out FROM transaction_other_component WHERE request_date <= '$filter_from' AND transaction_type = 'ITEM OUT' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_other_adj_out FROM transaction_other_component WHERE request_date <= '$filter_from' AND transaction_type = 'ADJ OUT' GROUP BY item_rm_id) i ON a.id = i.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_out FROM scan_dn_crusher WHERE created_date <= '$filter_from' GROUP BY item_rm_id) k ON a.id = k.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_dn_scrap FROM dn_scrap WHERE transaction_date <= '$filter_from' GROUP BY item_rm_id) l ON a.id = l.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date <= '$filter_from' and type = 'Other' GROUP BY item_rm_id) m ON a.id = m.item_rm_id
                    ) j ON a.id = j.id

        LEFT JOIN (
            SELECT 
                item_rm_id,
                SUM(CASE WHEN transfer_to   = 'BPI'    THEN qty ELSE 0 END) AS in_bpi,
                SUM(CASE WHEN transfer_from = 'BPI'    THEN qty ELSE 0 END) AS out_bpi,
                SUM(CASE WHEN transfer_to   = 'PLANT 1' THEN qty ELSE 0 END) AS in_plant1,
                SUM(CASE WHEN transfer_from = 'PLANT 1' THEN qty ELSE 0 END) AS out_plant1
            FROM scan_rm_transfer
           	WHERE DATE_FORMAT(transaction_date, '%Y-%m-%d') BETWEEN '$filter_from'
           	AND '$filter_to'
            GROUP BY item_rm_id
        ) y ON a.id = y.item_rm_id
        LEFT JOIN (
            SELECT 
                base.item_rm_id,
                COALESCE(base.qty_bpi,0)
                + COALESCE(rcv_bpi.qty_in,0)
                + COALESCE(trf_bpi.in_bpi,0)
                - COALESCE(trf_bpi.out_bpi,0) AS begin_bpi,
                COALESCE(base.qty_plant1,0)
                + COALESCE(rcv_plant1.qty_in,0)
                + COALESCE(trf_plant1.in_plant1,0)
                - COALESCE(trf_plant1.out_plant1,0) AS begin_plant1
            FROM (
                SELECT a.item_rm_id,
                       SUM(CASE WHEN a.transfer_from = 'BPI' THEN a.qty_from ELSE 0 END + CASE WHEN a.transfer_to = 'BPI' THEN a.qty_to ELSE 0 END) AS qty_bpi,
                       SUM(CASE WHEN a.transfer_from = 'PLANT 1' THEN a.qty_from ELSE 0 END + CASE WHEN a.transfer_to = 'PLANT 1' THEN a.qty_to ELSE 0 END) AS qty_plant1
                FROM upload_stock_whs_tf a
                WHERE a.trans_date = '2025-09-19'
                GROUP BY a.item_rm_id
            ) base
            LEFT JOIN (
                SELECT b.item_rm_id, SUM(a.qty) AS qty_in
                FROM scan_item_receipts a
                JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id
                WHERE a.plant = 'BPI' 
                  AND b.receipt_date >= '2025-09-18' 
                  AND b.receipt_date < '$filter_from'
                GROUP BY b.item_rm_id
            ) rcv_bpi ON base.item_rm_id = rcv_bpi.item_rm_id
            LEFT JOIN (
                SELECT b.item_rm_id, SUM(a.qty) AS qty_in
                FROM scan_item_receipts a
                JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id
                WHERE a.plant = 'PLANT 1' 
                  AND b.receipt_date >= '2025-09-18' 
                  AND b.receipt_date < '$filter_from'
                GROUP BY b.item_rm_id
            ) rcv_plant1 ON base.item_rm_id = rcv_plant1.item_rm_id
            LEFT JOIN (
                SELECT item_rm_id,
                       SUM(CASE WHEN transfer_to = 'BPI' THEN qty ELSE 0 END) AS in_bpi,
                       SUM(CASE WHEN transfer_from = 'BPI' THEN qty ELSE 0 END) AS out_bpi
                FROM scan_rm_transfer
                WHERE transaction_date >= '2025-09-18'
                  AND transaction_date < '$filter_from'
                GROUP BY item_rm_id
            ) trf_bpi ON base.item_rm_id = trf_bpi.item_rm_id
            LEFT JOIN (
                SELECT item_rm_id,
                       SUM(CASE WHEN transfer_to = 'PLANT 1' THEN qty ELSE 0 END) AS in_plant1,
                       SUM(CASE WHEN transfer_from = 'PLANT 1' THEN qty ELSE 0 END) AS out_plant1
                FROM scan_rm_transfer
                WHERE transaction_date >= '2025-09-18'
                  AND transaction_date < '$filter_from'
                GROUP BY item_rm_id
            ) trf_plant1 ON base.item_rm_id = trf_plant1.item_rm_id
        ) begin_whs ON a.id = begin_whs.item_rm_id


        WHERE c.id LIKE '%$filter_item_category%'
        AND b.number LIKE '%$filter_item_family%'
        AND a.id LIKE '%$filter_items%'
        AND a.division LIKE '%$filter_division%'
        AND (
            a.item_category_id = 'C06' OR (
                a.division = 'INJ' AND a.item_category_id = 'C11' AND (
                    a.item_family_id in ('P05','P33') 
                ) 
            ) 
        )
        GROUP BY a.id
        ORDER BY c.name DESC, b.name DESC, a.number";

        // Eksekusi query
        $records = $this->crud->query($query_main);

        $html = '<html><head><title>Print Data</title></head>
            <style>
                body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}
                /* Style khusus untuk media cetak */
                @media print {
                    #customers thead {
                        display: table-header-group;
                    }
                    #customers tbody tr {
                        page-break-inside: avoid;
                    }
                }
            </style><body>
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
                </div>';
                
        if ($option == "excel") {
            $html .= '<div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:i:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>';
        } else {
            $html .= '<div style="float: right; font-size: 12px; text-align: right;">
                        Print Date ' . date("d M Y H:i:s") . ' <br>
                        Print By ' . $this->session->username . '  
                    </div>
                <br><br>';
        }
        
        $html .= '<br><br>
                <h3 style="margin:0;">INVENTORY HISTORY TRANSACTION OTHER COMPONENT</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>
            
            <table id="customers" border="1" style="font-size: 11px;">
             <thead>
                <tr>
                    <th width="20">No</th>
                    <th colspan="3">Product No</th>
                    <th colspan="2">Product Name</th>
                    <th>Uom</th>
                    <th>Division</th>
                    <th>Category</th>
                    <th>Product Family</th>
                    <th>Sub Product Family</th>
                    <th width="100">Begin Stock</th>
                    <th width="100">In</th>
                    <th width="100">Out</th>
                    <th width="100">Ending Stock</th>
                    <th width="100">Stock Plant BPI</th>
                    <th width="100">Stock Plant 1</th>
                </tr>
             </thead>';


        $no = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalBpi = 0;
        $totalPlant1 = 0;
        $totalEndingStock = 0;
        $totalIto = 0;

        foreach ($records as $record) {
            $item_rm_id = $record->id;

            $totalBeginStock += @$record->begin_stock;
            $totalIn += $record->qty_in;
            $totalOut += $record->qty_out;
            $totalBpi += $record->qty_bpi;
            $totalPlant1 += $record->qty_plant1;
            $totalEndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out;

            $html .= '<tbody><tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3">' . $record->number . '</td>
                            <td colspan="2">' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->division . '</td>
                            <td>' . $record->category_name . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td>' . $record->prodfam_sub_name . '</td>
                            <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format((@$record->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_bpi, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_plant1, 2) . '</td>
                        </tr>';

            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="17" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>
                            <tr>
                                <th width="20"></th>
                                <th width="20">No</th>
                                <th>Trans Type</th>
                                <th>Created By</th>
                                <th colspan="2">Trans Date</th>
                                <th>Custom. Kind</th>
                                <th>Receipt No</th>
                                <th>Lot No</th>
                                <th>Doc. No</th>
                                <th>Custom. Date</th>
                                <th>Begin</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Balance</th>
                            </tr>';

                $nod = 1;
                $begin = @$record->begin_stock;
                $in_qty = 0;
                $end_qty = 0;
                $balance = 0;
                // for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                //     $working_date = date('Y-m-d', $i);

                    if ($filter_trans_type == '' ) {
                        //-------------- Awal Query disini----------------------------------//                    
                        //SCAN ITEM CRUESHERS
                        $scan_item_crushers = $this->crud->query("SELECT
                            a.request_date, 
                            a.request_no,  
                            b.qty,
                            a.lot_no,
                            c.name as username
                        FROM receipt_crusher a 
                        JOIN scan_item_receipt_crusher b ON a.request_id = b.request_id
                        JOIN users c ON a.created_by = c.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.request_date between '$filter_from' and '$filter_to'
                        GROUP BY b.label");

                        //TRANSACTION OTHER COMPONENT ITEM IN
                        $trans_other_item_ins = $this->crud->query("SELECT 
                            a.request_date, 
                            a.request_no,  
                            a.qty,
                            b.name as username
                        FROM transaction_other_component a
                        JOIN users b ON a.created_by = b.username
                        WHERE item_rm_id = '$item_rm_id' 
                        AND transaction_type = 'ITEM IN' 
                        AND DATE_FORMAT(request_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");
                        
                        //TRANSACTION OTHER COMPONENT ADJ IN
                        $trans_other_adj_ins = $this->crud->query("SELECT 
                            a.request_date, 
                            a.request_no,  
                            a.qty,
                            b.name as username
                        FROM transaction_other_component a
                        JOIN users b ON a.created_by = b.username
                        WHERE item_rm_id = '$item_rm_id' 
                        AND transaction_type = 'ADJ IN' 
                        AND DATE_FORMAT(request_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                        //INPUT CRUSHING
                        $input_crushing = $this->crud->query("
                            SELECT 
                                ic.trans_date,
                                ic.document,
                                ic.qty,
                                e.name AS username,
                                COALESCE(pur.number, d.number) AS item_number
                            FROM input_crushing ic
                            JOIN item_rm d ON ic.item_rm_id = d.id
                            LEFT JOIN item_family_subs c ON d.item_sub_family_id = c.id
                            JOIN users e ON ic.created_by = e.username
                            LEFT JOIN item_rm pur 
                                ON pur.number IN (
                                    'PUR-PC','PUR-ABS','PUR-ASA','PUR-PA6',
                                    'PUR-PA66','PUR-PBT','PUR-PMMA','PUR-POM',
                                    'PUR-PP','PUR-PVC'
                                )
                                AND (
                                    (c.id = 'PS005' AND pur.number = 'PUR-PC')
                                    OR (c.id = 'PS002' AND pur.number = 'PUR-ABS')
                                    OR (c.id = 'PS003' AND pur.number = 'PUR-ASA')
                                    OR (c.id = 'PS007' AND pur.number = 'PUR-PA6')
                                    OR (c.id = 'PS008' AND pur.number = 'PUR-PA66')
                                    OR (c.id = 'PS009' AND pur.number = 'PUR-PBT')
                                    OR (c.id = 'PS006' AND pur.number = 'PUR-PMMA')
                                    OR (c.id = 'PS010' AND pur.number = 'PUR-POM')
                                    OR (c.id = 'PS004' AND pur.number = 'PUR-PP')
                                    OR (c.id = 'PS001' AND pur.number = 'PUR-PVC')
                                )
                            WHERE ic.trans_date BETWEEN '$filter_from' AND '$filter_to'
                            AND (
                                d.id = '$item_rm_id'
                                OR pur.id = '$item_rm_id'
                            )
                            ORDER BY ic.trans_date
                        ");


                        //TRANSACTION OTHER COMPONENT ITEM OUT
                        $trans_other_item_outs = $this->crud->query("SELECT 
                            a.request_date, 
                            a.request_no,  
                            a.qty,
                            b.name as username
                        FROM transaction_other_component a
                        JOIN users b ON a.created_by = b.username
                        WHERE item_rm_id = '$item_rm_id' 
                        AND transaction_type = 'ITEM OUT' 
                        AND DATE_FORMAT(request_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");
                        
                        //TRANSACTION OTHER COMPONENT ADJ OUT
                        $trans_other_adj_outs = $this->crud->query("SELECT 
                            a.request_date, 
                            a.request_no,  
                            a.qty,
                            b.name as username
                        FROM transaction_other_component a
                        JOIN users b ON a.created_by = b.username
                        WHERE item_rm_id = '$item_rm_id' 
                        AND transaction_type = 'ADJ OUT' 
                        AND DATE_FORMAT(request_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                        //SCAN DN CRUSHERS
                        $scan_dn_crushers = $this->crud->query("SELECT
                            a.request_date, 
                            a.request_no,  
                            b.document_no,
                            b.qty,
                            a.lot_no,
                            c.name as username
                        FROM receipt_crusher a 
                        JOIN scan_dn_crusher b ON a.request_id = b.receipt_id
                        JOIN users c ON a.created_by = c.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.request_date between '$filter_from' and '$filter_to'
                        GROUP BY b.label_no");

                        //DN SCRAP
                        $dn_scraps = $this->crud->query("SELECT 
                            a.transaction_date, 
                            a.document_no,
                            a.lot_no,
                            a.qty,
                            b.name as username
                        FROM dn_scrap a
                        JOIN users b ON a.created_by = b.username
                        WHERE item_rm_id = '$item_rm_id' 
                        AND DATE_FORMAT(transaction_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                        //ISSUED
                        $issueds = $this->crud->query("SELECT 
                            a.created_by, 
                            a.qty, 
                            a.created_date, 
                            '-' as label_no, 
                            a.request_no,
                            '-' as lotno
                        FROM issued_material_details a 
                        WHERE a.item_rm_id = '$item_rm_id' 
                        AND a.type = 'Other'
                        AND DATE_FORMAT(a.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                        //-------------- Akhir query disini----------------------------------//

                        $all_data = [];

                        // --- SCAN ITEM CRUSHER ---
                        foreach ($scan_item_crushers as $sic) {
                            $all_data[] = [
                                'type' => 'SCAN RECEIPT CRUSHER',
                                'date' => $sic->request_date,
                                'username' => $sic->username,
                                'qty_in' => $sic->qty,
                                'qty_out' => 0,
                                'doc1' => '-',
                                'doc2' => $sic->request_no,
                                'lotno' => $sic->lot_no,
                                'doc3' => '-',
                                'doc4' => '-'
                            ];
                        }

                        // --- TRANS OTHER ITEM IN ---
                        foreach ($trans_other_item_ins as $toii) {
                            $all_data[] = [
                                'type' => 'TRANSACTION OTHER COMPONENT',
                                'date' => $toii->request_date,
                                'username' => $toii->username,
                                'qty_in' => $toii->qty,
                                'qty_out' => 0,
                                'doc1' => '-',
                                'doc2' => $toii->request_no,
                                'lotno' => '-',
                                'doc3' => '-',
                                'doc4' => '-'
                            ];
                        }

                        // --- TRANS OTHER ADJ IN ---
                        foreach ($trans_other_adj_ins as $toai) {
                            $all_data[] = [
                                'type' => 'TRANSACTION OTHER COMPONENT',
                                'date' => $toai->request_date,
                                'username' => $toai->username,
                                'qty_in' => $toai->qty,
                                'qty_out' => 0,
                                'doc1' => '-',
                                'doc2' => $toai->request_no,
                                'lotno' => '-',
                                'doc3' => '-',
                                'doc4' => '-'
                            ];
                        }

                        // --- INPUT CRUSHING---
                        foreach ($input_crushing as $ic) {
                            $all_data[] = [
                                'type' => 'INPUT CRUSHING',
                                'date' => $ic->trans_date,
                                'username' => $ic->username,
                                'qty_in' => $ic->qty,
                                'qty_out' => 0,
                                'doc1' => '-',
                                'doc2' => '-',
                                'lotno' => '-',
                                'doc3' => $ic->document,
                                'doc4' => '-'
                            ];
                        }

                        // --- TRANS OTHER ITEM OUT ---
                        foreach ($trans_other_item_outs as $toio) {
                            $all_data[] = [
                                'type' => 'TRANSACTION OTHER COMPONENT',
                                'date' => $toio->request_date,
                                'username' => $toio->username,
                                'qty_in' => 0,
                                'qty_out' => $toao->qty,
                                'doc1' => '-',
                                'doc2' => $toio->request_no,
                                'lotno' => '-',
                                'doc3' => '-',
                                'doc4' => '-'
                            ];
                        }

                        // --- TRANS OTHER ADJ OUT ---
                        foreach ($trans_other_adj_outs as $toao) {
                            $all_data[] = [
                                'type' => 'TRANSACTION OTHER COMPONENT',
                                'date' => $toao->request_date,
                                'username' => $toao->username,
                                'qty_in' => 0,
                                'qty_out' => $toao->qty,
                                'doc1' => '-',
                                'doc2' => $toao->request_no,
                                'lotno' => '-',
                                'doc3' => '-',
                                'doc4' => '-'
                            ];
                        }

                        //--- SCAN DN CRUSHERS ---
                        foreach ($scan_dn_crushers as $sdc) {
                            $all_data[] = [
                                'type' => 'DN CRUSHER',
                                'date' => $sdc->request_date,
                                'username' => $sdc->username,
                                'qty_in' => 0,
                                'qty_out' => $sdc->qty,
                                'doc1' => '-',
                                'doc2' => $sdc->request_no,
                                'lotno' => $sdc->lot_no,
                                'doc3' => $sdc->document_no,
                                'doc4' => '-'
                            ];
                        }

                        //--- DN SCRAP ---
                        foreach ($dn_scraps as $ds) {
                            $all_data[] = [
                                'type' => 'DN SCRAP',
                                'date' => $ds->transaction_date,
                                'username' => $ds->username,
                                'qty_in' => 0,
                                'qty_out' => $ds->qty,
                                'doc1' => '-',
                                'doc2' => '-',
                                'lotno' => $ds->lot_no,
                                'doc3' => $ds->document_no,
                                'doc4' => '-'
                            ];
                        }

                        // --- ISSUED ---
                        foreach ($issueds as $i) {
                            $user = $this->crud->read("users", [], ["username" => $i->created_by]);
                            $all_data[] = [
                                'type' => 'ISSUED',
                                'date' => $i->created_date,
                                'username' => $user->name,
                                'qty_in' => 0,
                                'qty_out' => $i->qty,
                                'doc1' => '-',
                                'doc2' => $i->label_no,
                                'lotno' => $i->lotno,
                                'doc3' => $i->request_no,
                                'doc4' => '-'
                            ];
                        }

                        usort($all_data, function ($a, $b) {
                            return strtotime($a['date']) - strtotime($b['date']);
                        });

                        foreach ($all_data as $data) {
                            $balance = $begin + $data['qty_in'] - $data['qty_out'];
                        
                            $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td>' . $data['type'] . '</td>
                                <td>' . $data['username'] . '</td>
                                <td colspan="2">' . date("Y-m-d", strtotime($data['date'])) . '</td>
                                <td>' . $data['doc1'] . '</td>
                                <td>' . $data['doc2'] . '</td>
                                <td>' . $data['lotno'] . '</td>
                                <td>' . $data['doc3'] . '</td>
                                <td>' . $data['doc4'] . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_in'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_out'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        
                            $begin = $balance;
                            $nod++;
                        }
                    }
            
                    if ($filter_trans_type == 'SCAN RECEIPT CRUSHER') {
                        //RECEIPT
                        $scan_item_crushers = $this->crud->query("SELECT
                            a.request_date, 
                            a.request_no,  
                            b.qty,
                            a.lot_no,
                            c.name as username
                        FROM receipt_crusher a 
                        JOIN scan_item_receipt_crusher b ON a.request_id = b.request_id
                        JOIN users c ON a.created_by = c.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.request_date between '$filter_from' and '$filter_to'
                        GROUP BY b.label
                        ORDER BY a.request_date");
            
                        foreach ($scan_item_crushers as $scan_item_crusher) {
                            $balance = ($begin + ($scan_item_crusher->qty - $end_qty));
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>SCAN RECEIPT CRUSHER</td>
                                            <td>' . $scan_item_crusher->username . '</td>
                                            <td colspan="2">' . $scan_item_crusher->request_date . '</td>
                                            <td>-</td>
                                            <td>' . $scan_item_crusher->request_no . '</td>
                                            <td>' . $scan_item_crusher->lot_no . '</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($scan_item_crusher->qty, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin += $scan_item_crusher->qty;
                            $nod++;
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
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'ADJ IN STO' and a.request_date between '$filter_from' and '$filter_to'
                        ORDER BY a.request_date");
            
                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN') 
                                        ? ($begin + $transaction->qty) 
                                        : ($begin - $transaction->qty);
                        
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>ADJ IN STO</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
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
                            
                            $nod++;
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
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'BPM' and a.request_date between '$filter_from' and '$filter_to'
                        ORDER BY a.request_date");
            
                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN') 
                                        ? ($begin + $transaction->qty) 
                                        : ($begin - $transaction->qty);
                        
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>BPM</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
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
                            
                            $nod++;
                        }

                        if(!$transactions){
                            $transactions = $this->crud->query("SELECT * 
                            FROM scan_item_bpm WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(request_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' ORDER BY request_date");

                            foreach ($transactions as $transaction) {
                                $user = $this->crud->read("users", [], ["username" => $transaction->created_by]);
                                $balance = ($begin + $transaction->qty);
                                $html .= '  <tr>
                                                <td></td>
                                                <td style="text-align:center">' . $nod . '</td>
                                                <td>BPM</td>
                                                <td>' . $user->name . '</td>
                                                <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                                <td>-</td>
                                                <td>-</td>
                                                <td>' . $transaction->label . '</td>
                                                <td>' . $transaction->request_id . '</td>
                                                <td>' . $transaction->request_date . '</td>
                                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                                <td style="text-align:right;">' . number_format($transaction->qty, 2)  . '</td>
                                                <td style="text-align:right;">' . number_format(0) . '</td>
                                                <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                            </tr>';
                                $begin += $transaction->qty;
                                $nod++;
                            }
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
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'ADJ OUT STO' and a.request_date between '$filter_from' and '$filter_to'
                        ORDER BY a.request_date");
            
                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN') 
                                        ? ($begin + $transaction->qty) 
                                        : ($begin - $transaction->qty);
                        
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>ADJ OUT STO</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
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
                            
                            $nod++;
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
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'BPB' and a.request_date between '$filter_from' and '$filter_to'
                        ORDER BY a.request_date");
            
                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN') 
                                        ? ($begin + $transaction->qty) 
                                        : ($begin - $transaction->qty);
                        
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>BPB</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
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
                            
                            $nod++;
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
                        WHERE a.item_rm_id = '$item_rm_id' and a.transaction_type = 'KANBAN WO' and a.request_date between '$filter_from' and '$filter_to'
                        ORDER BY a.request_date");
            
                        foreach ($transactions as $transaction) {
                            $balance = ($transaction->transaction_kind == 'IN') 
                                        ? ($begin + $transaction->qty) 
                                        : ($begin - $transaction->qty);
                        
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>KANBAN WO</td>
                                            <td>' . $transaction->username . '</td>
                                            <td>' . date("Y-m-d", strtotime($transaction->request_date)) . '</td>
                                            <td>-</td>
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
                            
                            $nod++;
                        }
                    }

                    if ($filter_trans_type == 'ISSUED') {
                        //ISSUED
                        // $issueds = $this->crud->query("SELECT * FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' ORDER BY created_date");
                       $issueds = $this->crud->query("SELECT 
                            a.created_by, 
                            a.qty, 
                            a.created_date, 
                            a.label_no, 
                            a.request_no,
                            c.lotno
                        FROM issued_material_details a 
                        JOIN purchase_order_labels b ON a.label_no = b.label_no
                        JOIN purchase_order_receipts c ON b.receipt_id = c.receipt_id
                        WHERE a.item_rm_id = '$item_rm_id' 
                        and DATE_FORMAT(a.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'
                        
                        UNION

                        SELECT 
                            a.created_by, 
                            a.qty, 
                            a.created_date, 
                            a.label_no, 
                            a.request_no,
                            c.lot_no as lotno
                        FROM issued_material_details a
                        JOIN bpm_labels b ON a.label_no = b.label_no
                        JOIN bpm c ON b.request_id = c.request_id
                        WHERE a.item_rm_id = '$item_rm_id' 
                        and DATE_FORMAT(a.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'
                        
                        UNION
                        
                        SELECT 
                            a.created_by, 
                            a.qty, 
                            a.created_date, 
                            a.label_no, 
                            a.request_no,
                            COALESCE(c.lotno,'-') as lotno
                        FROM issued_material_details a 
                        JOIN barcode_divides b ON a.label_no = b.label_divided
                        LEFT JOIN purchase_order_receipts c ON b.reff = c.receipt_id
                        LEFT JOIN new_barcode d ON b.reff = d.label_no
                        WHERE a.item_rm_id = '$item_rm_id' 
                        and DATE_FORMAT(a.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'
                        
                        UNION

                        SELECT 
                            a.created_by, 
                            a.qty, 
                            a.created_date, 
                            a.label_no, 
                            a.request_no,
                            '-' as lotno
                        FROM issued_material_details a 
                        JOIN new_barcode b ON a.label_no = b.label_no
                        WHERE a.item_rm_id = '$item_rm_id' 
                        and DATE_FORMAT(a.created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");
            
                        foreach ($issueds as $issued) {
                            $user = $this->crud->read("users", [], ["username" => $issued->created_by]);
                            $balance = ($begin - $issued->qty);
                            $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>ISSUED</td>
                                            <td>' . $user->name . '</td>
                                            <td>' . date("Y-m-d", strtotime($issued->created_date)) . '</td>
                                            <td>-</td>
                                            <td>' . $issued->label_no . '</td>
                                            <td>' . $issued->lotno . '</td>
                                            <td>' . $issued->request_no . '</td>
                                            <td>-</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0) . '</td>
                                            <td style="text-align:right;">' . number_format($issued->qty, 2)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                            $begin -= $issued->qty;
                            $nod++;
                        }
                    }
                //}
            }
            $no++;
        }

        $html .= '<tr>
            <td colspan="11" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalBpi, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalPlant1, 2) . '</td>
        </tr>
        </tbody>';
      
        $html .= '</table></body></html>';
        echo $html;
    }
}
