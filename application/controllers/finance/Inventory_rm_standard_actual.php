<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property Crud $crud
 */
class Inventory_rm_standard_actual extends CI_Controller
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
            $this->load->view('finance/inventory_rm_standard_actual');
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

    function customCss() 
    {
        $css = '<style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                        margin: 20px;
                        background-color: white;
                        zoom: 90%;
                    }
                    .header-section {
                        overflow: hidden;
                        margin-bottom: 20px;
                    }
                    .company-info {
                        float: left;
                        width: 60%;
                        font-size: 12px;
                        text-align: left;
                    }
                    .print-info {
                        float: right;
                        width: 38%;
                        font-size: 12px;
                        text-align: right;
                    }
                    .company-logo {
                        vertical-align: top;
                        padding-right: 10px;
                    }
                    .company-details b {
                        font-size: 14px;
                    }
                    .company-details span {
                        font-size: 10px;
                    }
                    .report-title {
                        text-align: center;
                        margin-top: 20px;
                        margin-bottom: 20px;
                    }
                    .report-title h3 {
                        margin: 0;
                        font-size: 18px;
                    }
                    .report-title small {
                        font-size: 12px;
                    }
                    #customers {
                        border-collapse: collapse;
                        width: 100%;
                        font-size: 13px; 
                        margin-top: 15px;
                    }
                    #customers th,
                    #customers td {
                        border: 1px solid black;
                        padding: 0.6rem; 
                    }
                    #customers th {
                        /* background-color: #4E73BE !important; */
                        text-align: center;
                        color: black; 
                        font-weight: bold;
                    }
                    #customers tr:nth-child(even) {
                        background-color: #FFF;
                    }
                    #customers tr:hover {
                        background-color: #DEEBF7;
                    }

                    /* Aturan CSS khusus untuk print */
                    @media print {
                        body {
                            zoom: 90%;
                        }

                        /* Memaksa warna latar belakang untuk muncul saat dicetak */
                        #customers th {
                            background-color: #EEE !important;
                            /* background-color: #4E73BE !important; */
                            -webkit-print-color-adjust: exact;
                        }
                        #customers tr:nth-child(even) {
                            background-color: #DEEBF7; !important;
                            -webkit-print-color-adjust: exact;
                        }
                        #customers tr:hover {
                            background-color: #f1f1f1 !important;
                            -webkit-print-color-adjust: exact;
                        }
                        
                        /* Styling untuk baris ERP */
                        .bg-erp-row { /* Menambahkan class baru untuk baris ERP */
                            background-color: #DEEBF7 !important;
                            -webkit-print-color-adjust: exact;
                        }
                    }


                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    .font-bold { font-weight: bold; }
                    .bg-light-green { background-color: #CAFFB3; } /* Untuk baris kelompok akun */
                    .bg-grey { background-color: #EBEBEB; } /* Untuk grand total */

                    .table-custom-summary {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 11pt;
                        color: #495057;
                    }
                    
                    /* Specific styling to match the image */
                    .table-custom-summary thead {
                        border-top: 2px solid black;
                        border-bottom: 2px solid black;
                    }

                    .table-custom-summary thead th {
                        background-color: transparent; /* No background color in the header */
                        color: black;
                        padding: 0.3rem;
                        font-weight: bold;
                    }
                    
                    .table-custom-summary tbody tr {
                        border-bottom: 1px solid #dee2e6; /* Border at the bottom of each row */
                    }
                    
                    .table-custom-summary tbody tr:last-child {
                        border-bottom: none; /* No border for the last row */
                    }
                    
                    .table-custom-summary tbody tr td {
                        padding: 0.3rem;
                        vertical-align: middle;
                        border: none; /* Remove all cell borders */
                    }

                    .table-custom-summary .text-end {
                        text-align: right;
                    }

                    .table-custom-summary .text-danger {
                        color: #dc3545;
                        font-weight: bold;
                    }
                    
                    .clearfix::after {
                        content: "";
                        clear: both;
                        display: table;
                    }

                    /* Warna Header */
                    .bg-summary { background-color: #f2f2f2; font-weight: bold; }
                    .bg-standard { background-color: #D1FFC6; }
                    .bg-actual { background-color: #cfe6f9; }
                    .bg-variance { background-color: #d1eeee; }
                    .bg-white { background-color: #fff; }
                    .bg-gray { background-color: #e7e6e6; }
                    .bg-gray-darker { background-color: #d5d5d5; }
                    .bg-gray-lighter { background-color: #eee; }
                    .bg-yellow { background-color: #fffccc; }
                    .bg-blue { background-color: #81a1d1; color: white; }
                    
                    /* Warna Header */
                    .bg-grand-total { background-color: #cfffcc; }

                    /* Tooltip */
                    .has-tooltip {
                        position: relative;
                        cursor: help; /* Mengubah kursor menjadi tanda tanya */
                    }

                    /* Membuat kotak tooltip */
                    .has-tooltip::after {
                        content: attr(data-tooltip); /* Mengambil teks dari data-tooltip */
                        position: absolute;
                        bottom: 125%; /* Muncul di atas sel */
                        left: 50%;
                        transform: translateX(-50%);
                        background-color: #333;
                        color: #fff;
                        padding: 5px 10px;
                        border-radius: 4px;
                        white-space: nowrap;
                        font-size: 14px;
                        visibility: hidden;
                        opacity: 0;
                        transition: opacity 0.3s;
                        z-index: 10;
                    }

                    /* Munculkan saat hover */
                    .has-tooltip:hover::after {
                        visibility: visible;
                        opacity: 1;
                    }

                    /* Opsional: Tambahkan panah kecil di bawah tooltip */
                    .has-tooltip::before {
                        content: "";
                        position: absolute;
                        bottom: 115%;
                        left: 50%;
                        transform: translateX(-50%);
                        border-width: 5px;
                        border-style: solid;
                        border-color: #333 transparent transparent transparent;
                        visibility: hidden;
                        opacity: 0;
                        transition: opacity 0.3s;
                    }

                    .has-tooltip:hover::before {
                        visibility: visible;
                        opacity: 1;
                    }
                    </style>';
        return $css;
    }

    // -------------- PRINT RECAP (HISTORY TRANSACTION INVENTORY RM) => LSB -------------
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_rm_$format.xls");
        }
        
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get('filter_division');
        $filter_trans_type = $this->input->get('filter_trans_type');

        $display_title = ($filter_display == "DETAIL") ? '(DETAIL)' : '(RECAP)';

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $filter_from_minus1 = date('Y-m-01', strtotime('-1 month', strtotime($filter_from)));
        $filter_to_minus1   = date('Y-m-t',  strtotime('-1 month', strtotime($filter_from)));
        $filter_from_minus2 = date('Y-m-01', strtotime('-2 month', strtotime($filter_from)));
        $filter_to_minus2   = date('Y-m-t',  strtotime('-2 month', strtotime($filter_from)));
        $filter_from_minus3 = date('Y-m-01', strtotime('-3 month', strtotime($filter_from)));
        $filter_to_minus3   = date('Y-m-t',  strtotime('-3 month', strtotime($filter_from)));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();


        //------------------------------------ GET DATA AND CALCULATIONS ----------------------------------//

        $records = $this->crud->query("SELECT
            a.id,
            a.number, 
            a.name, 
            a.division, 
            b.name as prodfam, 
            l.name as sub_prodfam, 
            a.uom,
            c.name as category_name, 
            std_price.price AS standard_price,
            std_price.currency AS standard_currency,
            MAX(po.price) actual_price_in,

            COALESCE(x.begin_stock) AS begin_stock,
            COALESCE(d.qty_scan_in,0) as receipt_qty, 
            COALESCE(i.qty,0) + COALESCE(o.qty_bpm_scan,0) as bpm_qty, 
            COALESCE(k.qty,0) as adj_in_qty, 
            COALESCE(f.qty,0) as qty_issued,
            COALESCE(f2.qty,0) as qty_issued_supply_sheet,
            COALESCE(f3.qty,0) as qty_issued_non_supply_sheet,
            COALESCE(j.qty,0) + COALESCE(f4.qty,0) + COALESCE(f3.qty,0) as qty_kanban,
            COALESCE(f5.qty,0) as qty_issued_material_request,
            COALESCE(f6.qty,0) as qty_issued_non_supply_sheet_SJ,
            COALESCE(f7.qty,0) as qty_issued_non_supply_sheet_SP,
            COALESCE(m.qty,0) as adj_out_qty,
            COALESCE(n.qty,0) as bpb_qty, 

            (COALESCE(h1.qty_issued, 0) + COALESCE(i1.qty_trans_rm_out, 0)) AS qty_out_minus1,
            (COALESCE(h2.qty_issued, 0) + COALESCE(i2.qty_trans_rm_out, 0)) AS qty_out_minus2,
            (COALESCE(h3.qty_issued, 0) + COALESCE(i3.qty_trans_rm_out, 0)) AS qty_out_minus3,

            (COALESCE(d.qty_scan_in,0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0) + COALESCE(k.qty, 0) + COALESCE(o.qty_bpm_scan, 0)) as qty_in,
            (COALESCE(f.qty,0) + COALESCE(j.qty, 0) + COALESCE(m.qty, 0)+ COALESCE(n.qty, 0)) as qty_out

            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id and b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN item_family_subs l ON a.item_sub_family_id = l.id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) f ON a.id = f.item_rm_id

            LEFT JOIN (
                SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in, c.po_no, c.price 
                FROM scan_item_receipts a 
                JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id 
                JOIN purchase_orders c ON c.po_no = b.po_no AND c.item_rm_id = b.item_rm_id
                WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id
            ) po ON a.id = po.item_rm_id

            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from_minus1' AND '$filter_to_minus1' GROUP BY item_rm_id) h1 ON a.id = h1.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from_minus1' AND '$filter_to_minus1' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i1 ON a.id = i1.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from_minus2' AND '$filter_to_minus2' GROUP BY item_rm_id) h2 ON a.id = h2.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from_minus2' AND '$filter_to_minus2' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i2 ON a.id = i2.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from_minus3' AND '$filter_to_minus3' GROUP BY item_rm_id) h3 ON a.id = h3.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from_minus3' AND '$filter_to_minus3' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i3 ON a.id = i3.item_rm_id
            
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_stock_rm FROM os_rm WHERE trans_date >= '$filter_from' AND trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) GROUP BY item_rm_id) h ON a.id = h.item_rm_id            

            LEFT JOIN (SELECT item_rm_id, currency, price from standard_price_rm where '$filter_from' >= `start_date` and '$filter_to' <= `end_date`) std_price on a.id = std_price.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type,SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) AND a.transaction_type = 'BPM'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) i ON a.id = i.item_rm_id

            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_bpm_scan
                FROM scan_item_bpm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                GROUP BY a.item_rm_id) o ON a.id = o.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) AND a.transaction_type = 'ADJ IN STO'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) k ON a.id = k.item_rm_id

            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and request_no like '%SH-%' GROUP BY item_rm_id) f2 ON a.id = f2.item_rm_id
            
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and request_no like '%PRQ-%' GROUP BY item_rm_id) f5 ON a.id = f5.item_rm_id

            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and `type` like '%WIP%' GROUP BY item_rm_id) f4 ON a.id = f4.item_rm_id
            LEFT JOIN (
                SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as qty 
                FROM issued_material_details a
                JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                WHERE a.created_date >= '$filter_from' AND a.created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.request_no like '%REQ-%' AND b.type = 'Issued Production'
                GROUP BY a.item_rm_id
            ) f3 ON a.id = f3.item_rm_id
        
            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.transaction_type = 'KANBAN WO'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) j ON a.id = j.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as qty 
                FROM issued_material_details a
                JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                WHERE a.created_date >= '$filter_from' AND a.created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.request_no like '%REQ-%' AND b.type = 'Issued Subcont'
                GROUP BY a.item_rm_id
            ) f6 ON a.id = f6.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as qty 
                FROM issued_material_details a
                JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                WHERE a.created_date >= '$filter_from' AND a.created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.request_no like '%REQ-%' AND b.type = 'Issued Customer'
                GROUP BY a.item_rm_id
            ) f7 ON a.id = f7.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.transaction_type = 'ADJ OUT STO'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) m ON a.id = m.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.transaction_type = 'BPB'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) n ON a.id = n.item_rm_id

            LEFT JOIN (SELECT a.id, a.number, ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
                        FROM item_rm a
                        LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_from'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$filter_from' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                    ) x ON a.id = x.id
        
        WHERE c.id LIKE '%$filter_item_category%' 
        AND b.number LIKE '%$filter_item_family%' 
        AND a.id LIKE '%$filter_items%' 
        AND a.division LIKE '%$filter_division%' 
        GROUP BY a.id
        ORDER BY c.name DESC, b.name DESC, a.number");

        $html = '<html><head><title>Print Data</title></head>';
        $html .= $this->customCss();
        $html .= '<body>
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
                <h3 style="margin:0;">INVENTORY RM STANDARD AND ACTUAL <i>' . $display_title . '</i> </h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            
            <table id="customers" border="1" style="font-size: 11px;">
                <tr style="background-color: #eee;">
                    <th rowspan="5" width="20">No</th>
                    <th rowspan="5">Product No</th>
                    <th rowspan="5">Product Name</th>
                    <th rowspan="5">Uom</th>
                    <th rowspan="5">Division</th>
                    <th rowspan="5">Category</th>
                    <th rowspan="5">Product Family</th>
                    <th rowspan="5">Sub Product Family</th>

                    <th colspan="24">SUMMARY</th>
                    <th colspan="50">DETAIL</th>
                </tr>

                <tr style="background-color:#d5d5d5;">
                    <th colspan="6" width="100">BEGIN</th>
                    <th colspan="6" width="100">IN</th>
                    <th colspan="6" width="100">OUT</th>
                    <th colspan="6" width="100">ENDING</th>

                    <th colspan="15">IN</th>
                    <th colspan="35">OUT</th>
                </tr>';
        
        $variance_title = "VARIANCE = Amount Actual - Amount Standard";
        $out_amount_title = "OUT = (Amount BEGIN + Amount IN) / (Qty BEGIN + Qty IN)";

        // SUMMARY
        $html .= '<tr class="bg-yellow">
                <th rowspan="3" class="bg-grey">QTY</th>
                <th rowspan="2" colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th rowspan="2" colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                <td rowspan="3" class="has-tooltip" data-tooltip="' . $variance_title . '">
                    VARIANCE
                </td>

                <th rowspan="3" class="bg-grey">QTY</th>
                <th rowspan="2" colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th rowspan="2" colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                <td rowspan="3" class="has-tooltip" data-tooltip="' . $variance_title . '">
                    VARIANCE
                </td>

                <th rowspan="3" class="bg-grey">QTY</th>
                <th rowspan="2" colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th rowspan="2" colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                <td rowspan="3" class="has-tooltip" data-tooltip="' . $variance_title . '">
                    VARIANCE
                </td>

                <th rowspan="3" class="bg-grey">QTY</th>
                <th rowspan="2" colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th rowspan="2" colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                <td rowspan="3" class="has-tooltip" data-tooltip="' . $variance_title . '">
                    VARIANCE
                </td>
            ';

        // DETAIL                
        $html .= '<th colspan="5">Purchase</th>
                    <th colspan="5">BPM</th>
                    <th colspan="5">ADJ STO</th>

                    <th colspan="5">Supply Sheet</th>
                    <th colspan="5">Material Request</th>
                    <th colspan="5">Kanban PRD</th>
                    <th colspan="5">Kanban Subcont Jasa</th>
                    <th colspan="5">Kanban Subcont Product</th>
                    <th colspan="5">BPB</th>
                    <th colspan="5">ADJ STO</th>
                </tr>';
        $html .= '
                <th rowspan="2" class="bg-grey">QTY</th>
                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                
                <th rowspan="2" class="bg-grey">QTY</th>
                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>

                <th rowspan="2" class="bg-grey">QTY</th>
                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>

                <th rowspan="2" class="bg-grey">QTY</th>
                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>

                <th rowspan="2" class="bg-grey">QTY</th>
                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>

                <th rowspan="2" class="bg-grey">QTY</th>
                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>

                <th rowspan="2" class="bg-grey">QTY</th>
                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>

                <th rowspan="2" class="bg-grey">QTY</th>
                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>

                <th rowspan="2" class="bg-grey">QTY</th>
                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>

                <th rowspan="2" class="bg-grey">QTY</th>
                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
            </tr>';

        $html .= '<tr>
                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;">AMOUNT</th>
                
                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;">AMOUNT</th>
                
                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;" class="has-tooltip" data-tooltip="' . $out_amount_title . '">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;" class="has-tooltip" data-tooltip="' . $out_amount_title . '">AMOUNT</th>
                
                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;">AMOUNT</th>


                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;">AMOUNT</th>
                
                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;">AMOUNT</th>
                
                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;">AMOUNT</th>
                
                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;">AMOUNT</th>
                
                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;">AMOUNT</th>
                
                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;">AMOUNT</th>
                
                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;">AMOUNT</th>
                
                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;">AMOUNT</th>
                
                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;">AMOUNT</th>
                
                <th style="background-color: #D1FFC6;">PRICE</th>
                <th style="background-color: #D1FFC6;">AMOUNT</th>
                <th style="background-color: #CFE6F9;">PRICE</th>
                <th style="background-color: #CFE6F9;">AMOUNT</th>
            </tr>';
                
        $no = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalEndingStock = 0;

        $totalReceiptQty = 0;
        $totalBpmQty = 0;
        $totalAdjInQty = 0;

        $totalQtyIssuedSupplySheet = 0;
        $totalQtyIssuedMaterialRequest = 0;
        $totalQtyKanban = 0;
        $totalQtyKanbanSJ = 0;
        $totalQtyKanbanSP = 0;
        $totalAdjOutQty = 0;
        $totalBpbQty = 0;

        $totalQtyIn = 0;
        $totalQtyOut = 0;
        $totalQtySelisihIn = 0;
        $totalQtySelisihOut = 0;

        $totalIto = 0;

        $standard_price = 0;
        $rate = 1;

        $beginAmountSTD     = 0;
        $beginAmountActual  = 0;
        $inAmountSTD        = 0;
        $inAmountActual     = 0;
        $outAmountSTD       = 0;
        $outAmountActual    = 0;
        $endingAmountSTD    = 0;
        $endingAmountActual = 0;

        foreach ($records as $record) {
            $item_rm_id = $record->id;

            $totalBeginStock += @$record->begin_stock;
            $totalIn += $record->qty_in;
            $totalOut += $record->qty_out;
            $totalEndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out;
            
            $totalReceiptQty += $record->receipt_qty;
            $totalBpmQty += $record->bpm_qty;
            $totalAdjInQty += $record->adj_in_qty;

            $totalQtyIssuedSupplySheet += $record->qty_issued_supply_sheet;
            $totalQtyIssuedMaterialRequest += $record->qty_issued_material_request;
            $totalQtyKanban += $record->qty_kanban;
            $totalQtyKanbanSJ += $record->qty_issued_non_supply_sheet_SJ;
            $totalQtyKanbanSP += $record->qty_issued_non_supply_sheet_SP;
            $totalAdjOutQty += $record->adj_out_qty;
            $totalBpbQty += $record->bpb_qty;

            $totalQtyIn += ($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty);
            $totalQtyOut += ($record->qty_issued_supply_sheet + $record->qty_issued_material_request + $record->qty_kanban + $record->qty_issued_non_supply_sheet_SJ + $record->qty_issued_non_supply_sheet_SP + $record->adj_out_qty + $record->bpb_qty);
            $totalQtySelisihIn += (($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty) - $record->qty_in);
            $totalQtySelisihOut += (($record->qty_issued_supply_sheet + $record->qty_issued_material_request + $record->qty_kanban + $record->qty_issued_non_supply_sheet_SJ + $record->qty_issued_non_supply_sheet_SP + $record->adj_out_qty + $record->bpb_qty) - $record->qty_out);

            $total_sales_minus = $record->qty_out_minus1 + $record->qty_out_minus2 + $record->qty_out_minus3;
            
            $avg_sales_minus_numeric = ($total_sales_minus > 0) ? ($total_sales_minus / 3) : 0;
            $avg_sales_minus = number_format($avg_sales_minus_numeric, 2); // Hanya untuk tampilan

            $ending_stock = (@$record->begin_stock + $record->qty_in) - $record->qty_out;

            $_stock_coverage_numeric = 0;
            if ($avg_sales_minus_numeric > 0) {
                $_stock_coverage_numeric = $ending_stock / $avg_sales_minus_numeric;
            }

            $totalIto += $_stock_coverage_numeric;

            $stock_coverage = ($avg_sales_minus_numeric > 0)
                ? number_format($_stock_coverage_numeric, 2)
                : '0'; // atau bisa diganti jadi '0.00' atau '-'


            $standard_price = $record->standard_price * $rate;

            // ---- BEGIN ----
            // actual begin price dari upload user 
            // sementara menggunakan standard_price
            $begin_qty             = @$record->begin_stock;
            $begin_standard_amount = $standard_price * $begin_qty;
            $begin_actual_price    = $standard_price;
            $begin_actual_amount   = $begin_actual_price * $begin_qty;
            $begin_variance        = $begin_actual_amount - $begin_standard_amount;

            $beginAmountSTD += $begin_standard_amount;
            $beginAmountActual += $begin_actual_amount;

            // ---- IN ----
            $in_qty             = $record->qty_in;
            $in_standard_amount = $standard_price * $in_qty;
            $in_actual_price    = $record->actual_price_in * $rate;
            $in_actual_amount   = $in_actual_price * $in_qty;
            $in_variance        = $in_actual_amount - $in_standard_amount;

            $inAmountSTD += $in_standard_amount;
            $inAmountActual += $in_actual_amount;

            // ---- OUT ---- 
            // OUT = (Amount BEGIN + Amount IN) / (Qty BEGIN + Qty IN)
            $out_qty             = $record->qty_out;
            $out_standard_amount = $standard_price * $out_qty;

            $out_actual_price = 0;
            if (($begin_qty + $in_qty) > 0) {
                $out_actual_price = ($begin_actual_amount + $in_actual_amount) / ($begin_qty + $in_qty);
            }

            $out_actual_amount   = $out_actual_price * $out_qty;
            $out_variance        = $out_actual_amount - $out_standard_amount;

            $outAmountSTD += $out_standard_amount;
            $outAmountActual += $out_actual_amount;

            // ---- ENDING ----
            // QTY BALANCE = qty begin + qty in - qty out
            $ending_qty = ((@$record->begin_stock + $record->qty_in) - $record->qty_out);

            // AMOUNT BALANCE = amount begin + amount in - amount out 
            $ending_actual_amount = ($begin_actual_amount + $in_actual_amount) - $out_actual_amount;

            // PRICE BALANCE = amount ending / qty ending
            $ending_actual_price = 0;
            if ($ending_qty > 0) {
                $ending_actual_price = $ending_actual_amount / $ending_qty;
            }

            $ending_standard_amount = ($standard_price * ((@$record->begin_stock + $record->qty_in) - $record->qty_out));
            $ending_variance = $ending_actual_amount - $ending_standard_amount;

            $endingAmountSTD += $ending_standard_amount;
            $endingAmountActual += $ending_actual_amount;


            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $record->number . '</td>
                            <td>' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->division . '</td>
                            <td>' . $record->category_name . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td>' . $record->sub_prodfam . '</td>

                            <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * @$record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($begin_actual_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($begin_actual_amount, 2) . '</td>
                            <td style="text-align:right;">' . number_format($begin_variance, 2) . '</td>                            

                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($in_actual_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($in_actual_amount, 2) . '</td>
                            <td style="text-align:right;">' . number_format($in_variance, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($out_actual_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($out_actual_amount, 2) . '</td>
                            <td style="text-align:right;">' . number_format($out_variance, 2) . '</td>

                            <td style="text-align:right;">' . number_format((@$record->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * ((@$record->begin_stock + $record->qty_in) - $record->qty_out), 2) . '</td>
                            <td style="text-align:right;">' . number_format($ending_actual_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($ending_actual_amount, 2) . '</td>
                            <td style="text-align:right;">' . number_format($ending_variance, 2) . '</td>
                            
                            <td style="text-align:right;">' . $record->receipt_qty . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $record->receipt_qty, 2) . '</td>
                            <td></td>
                            <td></td>

                            <td style="text-align:right;">' . $record->bpm_qty . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $record->bpm_qty, 2) . '</td>
                            <td></td>
                            <td></td>

                            <td style="text-align:right;">' . $record->adj_in_qty . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $record->adj_in_qty, 2) . '</td>
                            <td></td>
                            <td></td>


                            <td style="text-align:right;">' . number_format($record->qty_issued_supply_sheet,2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $record->qty_issued_supply_sheet, 2) . '</td>
                            <td></td>
                            <td></td>

                            <td style="text-align:right;">' . $record->qty_issued_material_request . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $record->qty_issued_material_request, 2) . '</td>
                            <td></td>
                            <td></td>

                            <td style="text-align:right;">' . $record->qty_kanban . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $record->qty_kanban, 2) . '</td>
                            <td></td>
                            <td></td>

                            <td style="text-align:right;">' . $record->qty_issued_non_supply_sheet_SJ . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $record->qty_issued_non_supply_sheet_SJ, 2) . '</td>
                            <td></td>
                            <td></td>

                            <td style="text-align:right;">' . $record->qty_issued_non_supply_sheet_SP . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $record->qty_issued_non_supply_sheet_SP, 2) . '</td>
                            <td></td>
                            <td></td>

                            <td style="text-align:right;">' . $record->bpb_qty . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $record->bpb_qty, 2) . '</td>
                            <td></td>
                            <td></td>

                            <td style="text-align:right;">' . $record->adj_out_qty . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $record->adj_out_qty, 2) . '</td>
                            <td></td>
                            <td></td>
                        </tr>';
            $no++;
        }

        $html .= '<tr>
            <td colspan="8" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>
            <td></td>
            <td style="text-align:right;">' . number_format($beginAmountSTD, 2) . '</td>
            <td></td>
            <td style="text-align:right;">' . number_format($beginAmountActual, 2) . '</td>
            <td style="text-align:right;">' . number_format($beginAmountActual - $beginAmountSTD, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>
            <td></td>
            <td style="text-align:right;">' . number_format($inAmountSTD, 2) . '</td>
            <td></td>
            <td style="text-align:right;">' . number_format($inAmountActual, 2) . '</td>
            <td style="text-align:right;">' . number_format($inAmountSTD - $inAmountActual, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>
            <td></td>
            <td style="text-align:right;">' . number_format($outAmountSTD, 2) . '</td>
            <td></td>
            <td style="text-align:right;">' . number_format($outAmountActual, 2) . '</td>
            <td style="text-align:right;">' . number_format($outAmountSTD - $outAmountActual, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>
            <td></td>
            <td style="text-align:right;">' . number_format($endingAmountSTD, 2) . '</td>
            <td></td>
            <td style="text-align:right;">' . number_format($endingAmountActual, 2) . '</td>
            <td style="text-align:right;">' . number_format($endingAmountSTD - $endingAmountActual, 2) . '</td>


            <td style="text-align:right;">' . number_format($totalReceiptQty, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td style="text-align:right;">' . number_format($totalBpmQty, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td style="text-align:right;">' . number_format($totalAdjInQty, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td style="text-align:right;">' . number_format($totalQtyIssuedSupplySheet, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td style="text-align:right;">' . number_format($totalQtyIssuedMaterialRequest, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td style="text-align:right;">' . number_format($totalQtyKanban, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td style="text-align:right;">' . number_format($totalQtyKanbanSJ, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td style="text-align:right;">' . number_format($totalQtyKanbanSP, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td style="text-align:right;">' . number_format($totalBpbQty, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td style="text-align:right;">' . number_format($totalAdjOutQty, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>';
      
        $html .= '</table></body></html>';
        echo $html;
    }


    // -------------- PRINT DETAIL (INVENTORY RM) -------------
    public function print_detail($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=inventory_rm_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get('filter_division');
        $filter_trans_type = $this->input->get('filter_trans_type');

        $display_title = ($filter_display == "DETAIL") ? '(DETAIL)' : '(RECAP)';

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //------------------------------------ GET DATA AND CALCULATIONS ----------------------------------//

        $query_main = "SELECT 
            a.id,
            a.number, 
            a.name, 
            a.division, 
            b.name as prodfam, 
            subfam.name as sub_prodfam,
            COALESCE(aa.price,0) as price,
            COALESCE(aa.price,0) as std_price,
            COALESCE(aa.currency,'-') as currency,
            d.receipt_date,
            h.created_date as receipt_date_out,
            a.uom,
            c.name as category_name,
            COALESCE(j.begin_stock) AS begin_stock,
            (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) AS qty_in,
            (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) AS qty_out
        FROM item_rm a
        JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
        JOIN item_categories c ON a.item_category_id = c.id
        LEFT JOIN item_family_subs subfam ON a.item_sub_family_id = subfam.id

        LEFT JOIN (SELECT item_rm_id, currency, price from standard_price_rm where '$filter_from' >= `start_date` and '$filter_to' <= `end_date`) aa on a.id = aa.item_rm_id

        LEFT JOIN (SELECT MAX(b.price) AS price, MAX(b.currency) AS currency, MAX(b.receipt_date) AS receipt_date, b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        LEFT JOIN (SELECT MAX(price) AS price, MAX(currency) AS currency, MAX(created_date) AS created_date, item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
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

        WHERE c.id LIKE '%$filter_item_category%'
        AND b.number LIKE '%$filter_item_family%'
        AND a.id LIKE '%$filter_items%'
        AND a.division LIKE '%$filter_division%'
        GROUP BY a.id
        ORDER BY c.name DESC, b.name DESC, a.number";

        // Eksekusi query
        $records = $this->crud->query($query_main);

        $html = '<html><head><title>Print Data</title></head>';
        $html .= $this->customCss();
        $html .= '<body>
                <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td></td>
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
                <h3 style="margin:0;">INVENTORY RM STANDARD AND ACTUAL <i>' . $display_title . '</i> </h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';

        $html .= '<table id="customers" border="1" style="font-size: 11px;">
            <thead>
                <tr style="background-color:#eee;">
                    <th rowspan="4" width="20">No</th>
                    <th rowspan="4" colspan="3">Product No</th>
                    <th rowspan="4">Product Name</th>
                    <th rowspan="4">Uom</th>
                    <th rowspan="4">Division</th>
                    <th rowspan="4">Category</th>
                    <th rowspan="4">Product Family</th>
                    <th rowspan="4">Sub Product Family</th>
                    <th rowspan="4">Currency</th>
                    <th rowspan="4">Rate</th>

                    <th colspan="20">SUMMARY</th>
                </tr>
                
                <tr style="background-color:#d5d5d5;">
                    <th colspan="5" >BEGIN</th>
                    <th colspan="5" width="100">IN</th>
                    <th colspan="5" width="100">OUT</th>
                    <th colspan="5" width="100">BALANCE</th>
                </tr>

                <tr>
                    <th width="80" rowspan="2">QTY</th>
                    <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                    
                    <th width="80" rowspan="2">QTY</th>
                    <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                    
                    <th width="80" rowspan="2">QTY</th>
                    <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                    
                    <th width="80" rowspan="2">QTY</th>
                    <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                </tr>

                <tr>
                    <th style="background-color: #D1FFC6;">PRICE</th>
                    <th style="background-color: #D1FFC6;">AMOUNT</th>
                    <th style="background-color: #CFE6F9;">PRICE</th>
                    <th style="background-color: #CFE6F9;">AMOUNT</th>

                    <th style="background-color: #D1FFC6;">PRICE</th>
                    <th style="background-color: #D1FFC6;">AMOUNT</th>
                    <th style="background-color: #CFE6F9;">PRICE</th>
                    <th style="background-color: #CFE6F9;">AMOUNT</th>
                    
                    <th style="background-color: #D1FFC6;">PRICE</th>
                    <th style="background-color: #D1FFC6;">AMOUNT</th>
                    <th style="background-color: #CFE6F9;">PRICE</th>
                    <th style="background-color: #CFE6F9;">AMOUNT</th>
                    
                    <th style="background-color: #D1FFC6;">PRICE</th>
                    <th style="background-color: #D1FFC6;">AMOUNT</th>
                    <th style="background-color: #CFE6F9;">PRICE</th>
                    <th style="background-color: #CFE6F9;">AMOUNT</th>
                </tr>
            </thead>';

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
            $item_rm_id = $record->id;
            $receipt_date = @$record->receipt_date;
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

            $totalBeginStock += @$record->begin_stock;
            $totalBeginAmount += @$record->price * $rate * @$record->begin_stock;
            $totalIn += @$record->qty_in;
            $totalAmountIn += @$record->price * $rate * @$record->qty_in;
            $totalOut += @$record->qty_out;
            $totalAmountOut += @$record->price * $rate * @$record->qty_out;
            $totalEndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out;
            $totalAmountEndingStock += ((@$record->price * $rate) * @$record->qty_in) + ((@$record->price * $rate) * @$record->begin_stock) - ((@$record->price * $rate) * @$record->qty_out);


            // actual begin price dari upload user 
            // sementara menggunakan standard_price
            $begin_actual_price  = $record->price * $rate;
            $begin_qty = @$record->begin_stock;
            $begin_actual_amount = $begin_actual_price * $begin_qty;

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3">' . $record->number . '</td>
                            <td>' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->division . '</td>
                            <td>' . $record->category_name . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td>' . $record->sub_prodfam . '</td>
                            <td style="text-align:center;">' . $record->currency . '</td>
                            <td style="text-align:right;">' . number_format($rate, 2) . '</td>
                            
                            <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->price * $rate) * $record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($begin_actual_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($begin_actual_amount, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->price * $rate) * $record->qty_in, 2) . '</td>
                            <td></td>
                            <td></td>

                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->price * $rate) * $record->qty_out, 2) . '</td>
                            <td></td>
                            <td></td>

                            <td style="text-align:right;">' . number_format((@$record->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format((@($record->price * $rate) * $record->qty_in) + (($record->price * $rate) * $record->begin_stock) - (($record->price * $rate) * $record->qty_out), 2) . '</td>
                            <td></td>
                            <td></td>
                        </tr>';


                // DETAIL TRANSACTIONS
                $nod = 1;
                $begin = @$record->begin_stock;
                $price = @$record->price;
                $currency = @$record->currency;
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

                // for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                //     $working_date = date('Y-m-d', $i);

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
                            MAX(po.price) actual_price_in,
                            c.name as username
                        FROM purchase_order_receipts a 
                        JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
                        JOIN users c ON a.created_by = c.username 
                        LEFT JOIN purchase_orders po ON a.po_no = po.po_no AND a.item_rm_id = po.item_rm_id
                        WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$filter_from' and '$filter_to'
                        GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");

                    //ISSUED
                    $issueds = $this->crud->query("SELECT created_by, qty, created_date, label_no, request_no FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

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
                        WHERE a.item_rm_id = '$item_rm_id' and a.return_date between '$filter_from' and '$filter_to'
                        GROUP BY b.label_no");

                    // //OS RM
                    $os_rms = $this->crud->query("SELECT created_by, created_date, qty FROM os_rm WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(trans_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                    //SCAN BPM
                    $bpm_scans = $this->crud->query("SELECT 
                        created_by, 
                        qty, 
                        created_date, 
                        label, 
                        request_date, 
                        request_id 
                        FROM scan_item_bpm 
                        WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(request_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                    // // TRANSACTION RM (IN and OUT)
                    $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.request_date between '$filter_from' and '$filter_to'");

                    //-------------- Akhir query disini----------------------------------//

                    $all_data = [];
                    $actual_price_in = 0;

                    // --- RECEIPT ---
                    foreach ($receipts as $r) {
                        $actual_price_in = $r->actual_price_in;

                        $all_data[] = [
                            'type' => 'RECEIPT',
                            'date' => $r->receipt_date,
                            'username' => $r->username,
                            'qty_in' => $r->qty_receipt,
                            'qty_out' => 0,
                            'actual_price_in' => $actual_price_in,
                            'doc1' => $r->bc_kind,
                            'doc2' => $r->bc_aju,
                            'doc3' => $r->bc_document,
                            'doc4' => $r->bc_date
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
                            'actual_price_in' => $actual_price_in,
                            'doc1' => '-',
                            'doc2' => $i->label_no,
                            'doc3' => $i->request_no,
                            'doc4' => '-'
                        ];
                    }

                    // --- RETURN ---
                    foreach ($returns as $r) {
                        $all_data[] = [
                            'type' => 'RETURN',
                            'date' => $r->return_date,
                            'username' => $r->username,
                            'qty_in' => $r->qty,
                            'qty_out' => 0,
                            'actual_price_in' => $actual_price_in,
                            'doc1' => '-',
                            'doc2' => $r->label_no,
                            'doc3' => $r->return_no,
                            'doc4' => '-'
                        ];
                    }

                    // --- OS RM ---
                    foreach ($os_rms as $o) {
                        $user = $this->crud->read("users", [], ["username" => $o->created_by]);
                        $all_data[] = [
                            'type' => 'OS RM',
                            'date' => $o->created_date,
                            'username' => $user->name,
                            'qty_in' => $o->qty,
                            'qty_out' => 0,
                            'actual_price_in' => $actual_price_in,
                            'doc1' => '-',
                            'doc2' => '-',
                            'doc3' => '-',
                            'doc4' => '-'
                        ];
                    }

                    // --- SCAN BPM ---
                    foreach ($bpm_scans as $b) {
                        $user = $this->crud->read("users", [], ["username" => $b->created_by]);
                        $all_data[] = [
                            'type' => 'BPM',
                            'date' => $b->created_date,
                            'username' => $user->name,
                            'qty_in' => $b->qty,
                            'qty_out' => 0,
                            'actual_price_in' => $actual_price_in,
                            'doc1' => '-',
                            'doc2' => $b->label,
                            'doc3' => $b->request_id,
                            'doc4' => $b->request_date
                        ];
                    }

                    // --- TRANSACTION ---
                    foreach ($transactions as $t) {
                        $qty_in = $t->transaction_kind == 'IN' ? $t->qty : 0;
                        $qty_out = $t->transaction_kind == 'OUT' ? $t->qty : 0;

                        $all_data[] = [
                            'type' => $t->transaction_type,
                            'date' => $t->request_date,
                            'username' => $t->username,
                            'qty_in' => $qty_in,
                            'qty_out' => $qty_out,
                            'actual_price_in' => $actual_price_in,
                            'doc1' => '-',
                            'doc2' => '-',
                            'doc3' => $t->request_no,
                            'doc4' => '-'
                        ];
                    }

                    usort($all_data, function ($a, $b) {
                        return strtotime($a['date']) - strtotime($b['date']);
                    });

                    $html .= '<tr>
                                <td colspan="32" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';

                    if (!empty($all_data)) {
                        $html .= '<thead>
                            <tr>
                                <th rowspan="3" width="20"></th>
                                <th rowspan="3" width="20">No</th>
                                <th rowspan="3">Trans Type</th>
                                <th rowspan="3">Created By</th>
                                <th rowspan="3">Transaction Date</th>
                                <th rowspan="3">Custom. Kind</th>
                                <th rowspan="3">Custom. No</th>
                                <th rowspan="3">Doc. No</th>
                                <th rowspan="3">Custom. Date</th>
                                <th rowspan="3">CCY</th>
                                <th rowspan="3">Price</th>
                                <th rowspan="3">Rate</th>

                                <th colspan="5">BEGIN</th>
                                <th colspan="5">IN</th>
                                <th colspan="5">OUT</th>
                                <th colspan="5">BALANCE</th>
                            </tr>
                            
                            <tr>
                                <th width="80" rowspan="2">QTY</th>
                                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                                
                                <th width="80" rowspan="2">QTY</th>
                                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                                
                                <th width="80" rowspan="2">QTY</th>
                                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                                
                                <th width="80" rowspan="2">QTY</th>
                                <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                                <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                            </tr>

                            <tr>
                                <th style="background-color: #D1FFC6;">PRICE</th>
                                <th style="background-color: #D1FFC6;">AMOUNT</th>
                                <th style="background-color: #CFE6F9;">PRICE</th>
                                <th style="background-color: #CFE6F9;">AMOUNT</th>

                                <th style="background-color: #D1FFC6;">PRICE</th>
                                <th style="background-color: #D1FFC6;">AMOUNT</th>
                                <th style="background-color: #CFE6F9;">PRICE</th>
                                <th style="background-color: #CFE6F9;">AMOUNT</th>
                                
                                <th style="background-color: #D1FFC6;">PRICE</th>
                                <th style="background-color: #D1FFC6;">AMOUNT</th>
                                <th style="background-color: #CFE6F9;">PRICE</th>
                                <th style="background-color: #CFE6F9;">AMOUNT</th>
                                
                                <th style="background-color: #D1FFC6;">PRICE</th>
                                <th style="background-color: #D1FFC6;">AMOUNT</th>
                                <th style="background-color: #CFE6F9;">PRICE</th>
                                <th style="background-color: #CFE6F9;">AMOUNT</th>
                            </tr>
                        </thead>';
                    }

                    foreach ($all_data as $data) {
                        $balance = $begin + $data['qty_in'] - $data['qty_out'];

                        $begin_qty_detail           = $begin;
                        $begin_actual_price_detail  = $record->price * $rate; // begin price dari upload user, sementara dari standard price
                        $begin_actual_amount_detail = $begin_qty_detail * $begin_actual_price_detail;

                        $in_qty  = $data['qty_in'];
                        $in_actual_price  = $data['actual_price_in'] * $rate;
                        $in_actual_amount = $in_qty * $actual_price_in;
                        
                        $out_qty = $data['qty_out'];

                        $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td>' . $data['type'] . '</td>
                                <td>' . $data['username'] . '</td>
                                <td>' . date("Y-m-d", strtotime($data['date'])) . '</td>
                                <td>' . $data['doc1'] . '</td>
                                <td>' . $data['doc2'] . '</td>
                                <td>' . $data['doc3'] . '</td>
                                <td>' . $data['doc4'] . '</td>
                                <td style="text-align:right;">' . $currency . '</td>
                                <td style="text-align:right;">' . number_format($record->price, 2) . '</td>
                                <td style="text-align:right;">' . number_format($rate, 2) . '</td>
                                
                                
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                <td style="text-align:right;">' . number_format(($rate * $price) * $begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format($begin_actual_price_detail, 2) . '</td>
                                <td style="text-align:right;">' . number_format($begin_actual_amount_detail, 2) . '</td>

                                <td style="text-align:right;">' . number_format($data['qty_in'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                <td style="text-align:right;">' . number_format(($rate * $price) * $data['qty_in'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($in_actual_price, 2) . '</td>
                                <td style="text-align:right;">' . number_format($in_actual_amount, 2) . '</td>

                                <td style="text-align:right;">' . number_format($data['qty_out'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                <td style="text-align:right;">' . number_format(($rate * $price) * $data['qty_out'], 2) . '</td>
                                <td></td>
                                <td></td>

                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                <td style="text-align:right;">' . number_format(($rate * $price) * $balance, 2) . '</td>
                                <td></td>
                                <td></td>
                            </tr>';

                        $begin = $balance;
                        $nod++;
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
                        WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$filter_from' and '$filter_to'
                        GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id
                        ORDER BY a.receipt_date");

                    foreach ($receipts as $receipt) {
                        $balance = ($begin + ($receipt->qty_receipt - $end_qty));
                        $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>RECEIPT</td>
                                            <td>' . $receipt->username . '</td>
                                            <td>' . $receipt->receipt_date . '</td>
                                            <td>' . $receipt->bc_kind . '</td>
                                            <td>' . $receipt->bc_aju . '</td>
                                            <td>' . $receipt->bc_document . '</td>
                                            <td>' . $receipt->bc_date . '</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($receipt->qty_receipt, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                        $begin += $receipt->qty_receipt;
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

                    if (!$transactions) {
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
                    $issueds = $this->crud->query("SELECT * FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' ORDER BY created_date");

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
            
            $no++;
        }


        $html .= '<tr class="bg-blue">
            <td colspan="12" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;"><b>' . number_format($totalBeginStock, 2) . '</b></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"><b>' . number_format($totalBeginAmount, 2) . '</b></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>
            
            <td style="text-align:right;">' . number_format($totalIn, 2) . '</b></td>
            <td style="text-align:right;"><b></td>
            <td style="text-align:right;"><b>' . number_format($totalAmountIn, 2) . '</b></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>
            
            <td style="text-align:right;"><b>' . number_format($totalOut, 2) . '</b></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"><b>' . number_format($totalAmountOut, 2) . '</b></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>
            
            <td style="text-align:right;"><b>' . number_format($totalEndingStock, 2) . '</b></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"><b>' . number_format($totalAmountEndingStock, 2) . '</b></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>
        </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_backup($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=inventory_rm_standard_actual_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get('filter_division');
        $filter_trans_type = $this->input->get('filter_trans_type');

        $display_title = ($filter_display == "DETAIL") ? '(DETAIL)' : '(RECAP)';

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();


        //------------------------------------ GET DATA AND CALCULATIONS ------------------------------------------------------//

        $query_main = "SELECT 
            a.id,
            a.number, 
            a.name, 
            a.division, 
            b.name as prodfam, 
            l.name as sub_prodfam,
            COALESCE(aa.price,0) as price,
            COALESCE(aa.currency,'-') as currency,
            d.receipt_date,
            h.created_date as receipt_date_out,
            a.uom,
            c.name as category_name,

            /** QTY */
            COALESCE(j.begin_stock) AS begin_stock,
            COALESCE(d.qty_scan_in,0) as receipt_qty, 
            COALESCE(k.qty_scan_bpm,0) as bpm_qty, 

            (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) AS qty_in,
            (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) AS qty_out,

            /** PRICE ACTUAL */
            COALESCE(actual_in.price, 0) as price_actual_in 
        
        FROM item_rm a
        JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
        JOIN item_categories c ON a.item_category_id = c.id
        LEFT JOIN item_family_subs l ON a.item_sub_family_id = l.id
        LEFT JOIN (SELECT item_rm_id, currency, price from standard_price_rm where '$filter_from' >= `start_date` and '$filter_to' <= `end_date`) aa on a.id = aa.item_rm_id
        
        LEFT JOIN (SELECT MAX(b.price) AS price, MAX(b.currency) AS currency, MAX(b.receipt_date) AS receipt_date, b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
        
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        
        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
        
        LEFT JOIN (SELECT MAX(price) AS price, MAX(currency) AS currency, MAX(created_date) AS created_date, item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
        
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

        /** Find actual price IN */
        LEFT JOIN (SELECT a.price, a.item_rm_id 
            FROM purchase_orders a
            LEFT JOIN purchase_order_receipts b ON a.po_no = b.po_no
            WHERE b.receipt_date < '$filter_from'
            GROUP BY a.item_rm_id
        ) actual_in ON a.id = actual_in.item_rm_id

        WHERE c.id LIKE '%$filter_item_category%'
        AND b.number LIKE '%$filter_item_family%'
        AND a.id LIKE '%$filter_items%'
        AND a.division LIKE '%$filter_division%'
        GROUP BY a.id
        ORDER BY c.name DESC, b.name DESC, a.number";

        // Eksekusi query
        $records = $this->crud->query($query_main);

        $html = '<html><head><title>Print Data</title></head>';
        $html .= $this->customCss();
        $html .= '<body>
                <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td></td>
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
                <h3 style="margin:0;">REPORT INVENTORY RM STANDARD AND ACTUAL <i>' . $display_title . '</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';
            
        $html .= '<table id="customers" border="1" style="font-size: 11px;">
                <thead>
                <tr class="bg-gray-lighter" style="background-color: #eee;">
                    <th rowspan="5" width="20">No</th>
                    <th rowspan="5" colspan="3">Product No</th>
                    <th rowspan="5">Product Name</th>
                    <th rowspan="5">Uom</th>
                    <th rowspan="5">Division</th>
                    <th rowspan="5">Category</th>
                    <th rowspan="5">Product Family</th>
                    <th rowspan="5">Sub Product Family</th>
                    <th rowspan="5">CCY</th>
                    <th rowspan="5">Rate</th>
                    <th colspan="24">SUMMARY</th>
                    <th colspan="50">DETAIL</th>
                </tr>
                <tr style="background-color: #d5d5d5;">
                    <th colspan="6" class="bg-gray">BEGIN</th>
                    <th colspan="6" class="bg-gray">IN</th>
                    <th colspan="6" class="bg-gray-darker">OUT</th>
                    <th colspan="6" class="bg-gray">ENDING</th>
                    <th colspan="15" class="bg-gray">IN</th>
                    <th colspan="35" class="bg-gray-darker">OUT</th>
                </tr>

                <tr style="background-color: #eee;">
                    <th rowspan="3">QTY</th>
                    <th rowspan="2" colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th rowspan="2" colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>
                    <th rowspan="3">VARIANCE</th>
                    
                    <th rowspan="3">QTY</th>
                    <th rowspan="2" colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th rowspan="2" colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>
                    <th rowspan="3">VARIANCE</th>

                    <th rowspan="3">QTY</th>
                    <th rowspan="2" colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th rowspan="2" colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>
                    <th rowspan="3">VARIANCE</th>

                    <th rowspan="3">QTY</th>
                    <th rowspan="2" colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th rowspan="2" colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>
                    <th rowspan="3">VARIANCE</th>
                    

                    <th colspan="5" class="bg-yellow">PURCHASE</th>
                    <th colspan="5" class="bg-yellow">BPM</th>
                    <th colspan="5" class="bg-yellow">ODJ STO</th>
                    
                    <th colspan="5" class="bg-yellow"> Supply Sheet </th>
                    <th colspan="5" class="bg-yellow"> Material Request </th>
                    <th colspan="5" class="bg-yellow"> Kanban PRD </th>
                    <th colspan="5" class="bg-yellow"> Kanban Subcont Jasa </th>
                    <th colspan="5" class="bg-yellow"> Kanban Subcont Product </th>
                    <th colspan="5" class="bg-yellow"> BPB </th>
                    <th colspan="5" class="bg-yellow"> ADJ STO </th>
                </tr>
                
                <tr>
                    <th rowspan="2">QTY</th>
                    <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>

                    <th rowspan="2">QTY</th>
                    <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>

                    <th rowspan="2">QTY</th>
                    <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>

                    <th rowspan="2">QTY</th>
                    <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>

                    <th rowspan="2">QTY</th>
                    <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>

                    <th rowspan="2">QTY</th>
                    <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>

                    <th rowspan="2">QTY</th>
                    <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>

                    <th rowspan="2">QTY</th>
                    <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>

                    <th rowspan="2">QTY</th>
                    <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>

                    <th rowspan="2">QTY</th>
                    <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>
                </tr>

                <tr>
                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>


                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                    <th class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                    <th class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                    <th class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>
                </tr>
                <thead>';

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
            $item_rm_id = $record->id;
            $receipt_date = @$record->receipt_date;
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

            // ----- GRAND TOTAL -----
            $totalBeginStock += @$record->begin_stock;
            $totalBeginAmount += @$record->price * $rate * @$record->begin_stock;
            $totalIn += @$record->qty_in;
            $totalAmountIn += @$record->price * $rate * @$record->qty_in;
            $totalOut += @$record->qty_out;
            $totalAmountOut += @$record->price * $rate * @$record->qty_out;
            $totalEndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out;
            $totalAmountEndingStock += ((@$record->price * $rate) * @$record->qty_in) + ((@$record->price * $rate) * @$record->begin_stock) - ((@$record->price * $rate) * @$record->qty_out);

            // ---- QTY ----
            $qty_begin  = @$record->begin_stock;
            $qty_in     = $record->qty_in;
            $qty_out    = $record->qty_out;
            $qty_ending = ($qty_begin + $qty_in) - $qty_out;

            // ---- STANDARD ----
            $standard_price = $record->price * $rate;

            $amount_standard_begin  = $standard_price * $qty_begin;
            $amount_standard_in     = $standard_price * $qty_in;
            $amount_standard_out    = $standard_price * $qty_out;
            $amount_standard_ending = ($amount_standard_in) + ($amount_standard_in) - ($amount_standard_out);
            
            // ---- ACTUAL ----            
            // actual begin price dari upload user 
            // sementara menggunakan standard_price
            $actual_price_begin  = $record->price * $rate;
            $actual_price_in     = $record->price_actual_in * $rate;
            
            $amount_actual_begin  = $actual_price_begin * $qty_begin;
            $amount_actual_in     = $actual_price_in * $qty_in;
            
            // OUT = (Amount BEGIN + Amount IN) / (QTY BEGIN + QTY IN)
            if ($qty_out > 0) {
                $actual_price_out = ($amount_actual_begin + $amount_actual_in) / ($qty_begin + $qty_in);
            } else {
                $actual_price_out = $standard_price;
            }
            $amount_actual_out = $actual_price_out * $qty_out;
            
            // ---- VARIANCE = amount actual - amount standard
            $variance_begin  = $amount_actual_begin - $amount_standard_begin;
            $variance_in     = $amount_actual_in - $amount_standard_in;
            $variance_out    = $amount_actual_out - $amount_standard_out;
            
            // ---- ENDING ----
            $amount_actual_ending = ($amount_actual_begin + $amount_actual_in) - ($amount_actual_out);
            $variance_ending      = $amount_actual_ending - $amount_standard_ending;
            
            if ($qty_ending > 0) {
                // Jika ada stok, hitung rata-rata harga
                $actual_price_ending = $amount_actual_ending / $qty_ending;
            } elseif ($qty_ending < 0) {
                $actual_price_ending = $standard_price; 
            } else {
                // Jika stok 0, maka harga juga 0
                $actual_price_ending = 0;
            }
            

            // SUMMARY
            $html .= '<tbody>
                        <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3">' . $record->number . '</td>
                            <td>' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->division . '</td>
                            <td>' . $record->category_name . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td>' . $record->sub_prodfam . '</td>
                            <td>' . $record->currency . '</td>
                            <td>' . $rate . '</td>
                            
                            <td style="text-align:right;">' . number_format($qty_begin, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amount_standard_begin, 2) . '</td>
                            <td style="text-align:right;">' . number_format($actual_price_begin, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amount_actual_begin, 2) . '</td>
                            <td style="text-align:right;">' . number_format($variance_begin, 2) . '</td>


                            <td style="text-align:right;">' . number_format($qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amount_standard_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($actual_price_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amount_actual_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($variance_in, 2) . '</td>


                            <td style="text-align:right;">' . number_format($qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amount_standard_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($actual_price_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amount_actual_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($variance_out, 2) . '</td>


                            <td style="text-align:right;">' . number_format($qty_ending, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amount_standard_ending, 2) . '</td>
                            <td style="text-align:right;">' . number_format($actual_price_ending, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amount_actual_ending, 2) . '</td>
                            <td style="text-align:right;">' . number_format($variance_ending, 2) . '</td>
                        ';

            // IN - DETAIL
            $qty_purchase = $record->receipt_qty ?? 0;
            $qty_bpm      = $record->bpm_qty ?? 0;
            $qty_adj_in   = $record->adj_in_qty ?? 0;
            
            // OUT - DETAIL
            $qty_supply_sheet       = $record->qty_issued_supply_sheet ?? 0;
            $qty_material_req       = $record->qty_issued_material_request ?? 0;
            $qty_kanban             = $record->qty_kanban ?? 0;
            $qty_kanban_sub_jasa    = $record->qty_issued_non_supply_sheet_SJ ?? 0;
            $qty_kanban_sub_product = $record->qty_issued_non_supply_sheet_SP ?? 0;
            $qty_bpb                = $record->bpb_qty ?? 0;
            $qty_adj_out            = $record->adj_out_qty ?? 0;            


            // DETAIL 
            $html .= '  
                            <td style="text-align:right;">' . number_format($qty_purchase, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $qty_purchase, 2) . '</td>
                            <td></td>
                            <td></td>
                            
                            <td style="text-align:right;">' . number_format($qty_bpm, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $qty_bpm, 2) . '</td>
                            <td></td>
                            <td></td>
                            
                            <td style="text-align:right;">' . number_format($qty_adj_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price * $qty_adj_in, 2) . '</td>
                            <td></td>
                            <td></td>
                            
                            
                            <td></td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td></td>
                            <td></td>
                            <td></td>

                            <td></td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td></td>
                            <td></td>
                            <td></td>

                            <td></td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td></td>
                            <td></td>
                            <td></td>

                            <td></td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td></td>
                            <td></td>
                            <td></td>

                            <td></td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td></td>
                            <td></td>
                            <td></td>

                            <td></td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td></td>
                            <td></td>
                            <td></td>

                            <td></td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        ';
            $html .= '</tr>
                    </tbody>';
            

            if ($filter_display == "DETAIL") {

                $nod = 1;
                $begin = @$record->begin_stock;
                $price = @$record->price;
                $currency = @$record->currency;
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

                // for ($i = $start; $i <= $finish; $i += (60 * 60 * 24)) {
                //     $working_date = date('Y-m-d', $i);

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
                        WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$filter_from' and '$filter_to'
                        GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");

                    //ISSUED
                    $issueds = $this->crud->query("SELECT created_by, qty, created_date, label_no, request_no FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

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
                        WHERE a.item_rm_id = '$item_rm_id' and a.return_date between '$filter_from' and '$filter_to'
                        GROUP BY b.label_no");

                    // //OS RM
                    $os_rms = $this->crud->query("SELECT created_by, created_date, qty FROM os_rm WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(trans_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                    //SCAN BPM
                    $bpm_scans = $this->crud->query("SELECT 
                        created_by, 
                        qty, 
                        created_date, 
                        label, 
                        request_date, 
                        request_id 
                        FROM scan_item_bpm 
                        WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(request_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                    // // TRANSACTION RM (IN and OUT)
                    $transactions = $this->crud->query("SELECT
                            a.request_date,
                            a.transaction_type,
                            a.transaction_kind,
                            a.request_no,
                            a.qty,
                            b.name as username
                        FROM transaction_rm a
                        JOIN users b ON a.created_by = b.username
                        WHERE a.item_rm_id = '$item_rm_id' and a.request_date between '$filter_from' and '$filter_to'");

                    //-------------- Akhir query disini----------------------------------//

                    $all_data = [];

                    // --- RECEIPT ---
                    foreach ($receipts as $r) {
                        $all_data[] = [
                            'type' => 'RECEIPT',
                            'date' => $r->receipt_date,
                            'username' => $r->username,
                            'qty_in' => $r->qty_receipt,
                            'qty_out' => 0,
                            'doc1' => $r->bc_kind,
                            'doc2' => $r->bc_aju,
                            'doc3' => $r->bc_document,
                            'doc4' => $r->bc_date
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
                            'doc3' => $i->request_no,
                            'doc4' => '-'
                        ];
                    }

                    // --- RETURN ---
                    foreach ($returns as $r) {
                        $all_data[] = [
                            'type' => 'RETURN',
                            'date' => $r->return_date,
                            'username' => $r->username,
                            'qty_in' => $r->qty,
                            'qty_out' => 0,
                            'doc1' => '-',
                            'doc2' => $r->label_no,
                            'doc3' => $r->return_no,
                            'doc4' => '-'
                        ];
                    }

                    // --- OS RM ---
                    foreach ($os_rms as $o) {
                        $user = $this->crud->read("users", [], ["username" => $o->created_by]);
                        $all_data[] = [
                            'type' => 'OS RM',
                            'date' => $o->created_date,
                            'username' => $user->name,
                            'qty_in' => $o->qty,
                            'qty_out' => 0,
                            'doc1' => '-',
                            'doc2' => '-',
                            'doc3' => '-',
                            'doc4' => '-'
                        ];
                    }

                    // --- SCAN BPM ---
                    foreach ($bpm_scans as $b) {
                        $user = $this->crud->read("users", [], ["username" => $b->created_by]);
                        $all_data[] = [
                            'type' => 'BPM',
                            'date' => $b->created_date,
                            'username' => $user->name,
                            'qty_in' => $b->qty,
                            'qty_out' => 0,
                            'doc1' => '-',
                            'doc2' => $b->label,
                            'doc3' => $b->request_id,
                            'doc4' => $b->request_date
                        ];
                    }

                    // --- TRANSACTION ---
                    foreach ($transactions as $t) {
                        $qty_in = $t->transaction_kind == 'IN' ? $t->qty : 0;
                        $qty_out = $t->transaction_kind == 'OUT' ? $t->qty : 0;

                        $all_data[] = [
                            'type' => $t->transaction_type,
                            'date' => $t->request_date,
                            'username' => $t->username,
                            'qty_in' => $qty_in,
                            'qty_out' => $qty_out,
                            'doc1' => '-',
                            'doc2' => '-',
                            'doc3' => $t->request_no,
                            'doc4' => '-'
                        ];
                    }

                    usort($all_data, function ($a, $b) {
                        return strtotime($a['date']) - strtotime($b['date']);
                    });

                    $html .= '<tr>
                                <td colspan="32" style="background:#CAFFB3; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';

                    if (!empty($all_data)) {
                        $html .= '
                            <tr>
                                <th rowspan="3" width="20"></th>
                                <th rowspan="3" width="20">No</th>
                                <th rowspan="3">Trans Type</th>
                                <th rowspan="3">Created By</th>
                                <th rowspan="3">Trans Date</th>
                                <th rowspan="3">Custom. Kind</th>
                                <th rowspan="3">Custom. No</th>
                                <th rowspan="3">Doc. No</th>
                                <th rowspan="3">Custom. Date</th>
                                <th rowspan="3">CCY</th>
                                <th rowspan="3">Price PO</th>
                                <th rowspan="3">Rate</th>
                                
                                <th colspan="5">BEGIN</th>
                                <th colspan="5">IN</th>
                                <th colspan="5">OUT</th>
                                <th colspan="5">BALANCE</th>
                            </tr>

                            <tr>
                                <th rowspan="2" width="80">QTY</th>
                                <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                                <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>

                                <th rowspan="2" width="80">QTY</th>
                                <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                                <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>

                                <th rowspan="2" width="80">QTY</th>
                                <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                                <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>

                                <th rowspan="2" width="80">QTY</th>
                                <th colspan="2" class="bg-standard" style="background-color: #D1FFC6;">STANDARD</th>
                                <th colspan="2" class="bg-actual" style="background-color: #DEEAF6;">ACTUAL</th>
                            </tr>

                            <tr>
                                <th width="80" class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                                <th width="80" class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                                <th width="80" class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                                <th width="80" class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>
                                
                                <th width="80" class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                                <th width="80" class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                                <th width="80" class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                                <th width="80" class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                                <th width="80" class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                                <th width="80" class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                                <th width="80" class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                                <th width="80" class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>

                                <th width="80" class="bg-standard" style="background-color: #D1FFC6;">PRICE</th>
                                <th width="80" class="bg-standard" style="background-color: #D1FFC6;">AMOUNT</th>
                                <th width="80" class="bg-actual" style="background-color: #DEEAF6;">PRICE</th>
                                <th width="80" class="bg-actual" style="background-color: #DEEAF6;">AMOUNT</th>
                            </tr>
                        ';
                    }

                    foreach ($all_data as $data) {
                        $balance = $begin + $data['qty_in'] - $data['qty_out'];

                        $html .= '<tr>
                                <td></td>
                                <td style="text-align:center">' . $nod . '</td>
                                <td>' . $data['type'] . '</td>
                                <td>' . $data['username'] . '</td>
                                <td>' . date("Y-m-d", strtotime($data['date'])) . '</td>
                                <td>' . $data['doc1'] . '</td>
                                <td>' . $data['doc2'] . '</td>
                                <td>' . $data['doc3'] . '</td>
                                <td>' . $data['doc4'] . '</td>
                                <td style="text-align:right;">' . $currency . '</td>
                                <td style="text-align:right;">' . number_format($price, 2) . '</td>
                                <td style="text-align:right;">' . number_format($rate, 2) . '</td>

                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                <td style="text-align:right;">' . number_format(($rate * $price) * $begin, 2) . '</td>
                                <td></td>
                                <td></td>
                                
                                <td style="text-align:right;">' . number_format($data['qty_in'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                <td style="text-align:right;">' . number_format(($rate * $price) * $data['qty_in'], 2) . '</td>
                                <td></td>
                                <td></td>
                                
                                <td style="text-align:right;">' . number_format($data['qty_out'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                <td style="text-align:right;">' . number_format(($rate * $price) * $data['qty_out'], 2) . '</td>
                                <td></td>
                                <td></td>
                                
                                <td style="text-align:right;">' . number_format($balance, 2) . '</td>
                                <td style="text-align:right;">' . number_format($rate * $price, 2) . '</td>
                                <td style="text-align:right;">' . number_format(($rate * $price) * $balance, 2) . '</td>
                                <td></td>
                                <td></td>
                            </tr>';

                        $begin = $balance;
                        $nod++;
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
                        WHERE a.item_rm_id = '$item_rm_id' and a.receipt_date between '$filter_from' and '$filter_to'
                        GROUP BY a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id
                        ORDER BY a.receipt_date");

                    foreach ($receipts as $receipt) {
                        $balance = ($begin + ($receipt->qty_receipt - $end_qty));
                        $html .= '  <tr>
                                            <td></td>
                                            <td style="text-align:center">' . $nod . '</td>
                                            <td>RECEIPT</td>
                                            <td>' . $receipt->username . '</td>
                                            <td>' . $receipt->receipt_date . '</td>
                                            <td>' . $receipt->bc_kind . '</td>
                                            <td>' . $receipt->bc_aju . '</td>
                                            <td>' . $receipt->bc_document . '</td>
                                            <td>' . $receipt->bc_date . '</td>
                                            <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                            <td style="text-align:right;">' . number_format($receipt->qty_receipt, 2) . '</td>
                                            <td style="text-align:right;">' . number_format(0)  . '</td>
                                            <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                                        </tr>';
                        $begin += $receipt->qty_receipt;
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

                    if (!$transactions) {
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
                    $issueds = $this->crud->query("SELECT * FROM issued_material_details WHERE item_rm_id = '$item_rm_id' and DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' ORDER BY created_date");

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

        if ($filter_display == "DETAIL") {
            $cols = 12;
            $html .= '<tr>
                <td colspan="' . $cols . '" style="text-align:right;"><b>GRAND TOTAL</b></td>
                <td style="text-align:right;"><b>' . number_format($totalBeginStock, 2) . '</b></td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"><b>' . number_format($totalBeginAmount, 2) . '</b></td>
                <td></td>
                <td></td>

                <td style="text-align:right;">' . number_format($totalIn, 2) . '</b></td>
                <td style="text-align:right;"><b></td>
                <td style="text-align:right;"><b>' . number_format($totalAmountIn, 2) . '</b></td>
                <td></td>
                <td></td>

                <td style="text-align:right;"><b>' . number_format($totalOut, 2) . '</b></td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"><b>' . number_format($totalAmountOut, 2) . '</b></td>
                <td></td>
                <td></td>

                <td style="text-align:right;"><b>' . number_format($totalEndingStock, 2) . '</b></td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"><b>' . number_format($totalAmountEndingStock, 2) . '</b></td>
                <td></td>
                <td></td>
            </tr>';

        } else {
            $cols = 12; 
            
            // SUMMARY
            $html .= '<tr class="bg-grand-total">
                <td colspan="' . $cols . '" style="text-align:right;"><b>GRAND TOTAL</b></td>
                <td style="text-align:right;"><b>' . number_format($totalBeginStock, 2) . '</b></td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"><b>' . number_format($totalBeginAmount, 2) . '</b></td>
                <td></td>
                <td></td>
                <td></td>

                <td style="text-align:right;">' . number_format($totalIn, 2) . '</b></td>
                <td style="text-align:right;"><b></td>
                <td style="text-align:right;"><b>' . number_format($totalAmountIn, 2) . '</b></td>
                <td></td>
                <td></td>
                <td></td>
                
                <td style="text-align:right;"><b>' . number_format($totalOut, 2) . '</b></td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"><b>' . number_format($totalAmountOut, 2) . '</b></td>
                <td></td>
                <td></td>
                <td></td>

                <td style="text-align:right;"><b>' . number_format($totalEndingStock, 2) . '</b></td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"><b>' . number_format($totalAmountEndingStock, 2) . '</b></td>
                <td></td>
                <td></td>
                <td></td>
            ';

            // DETAIL 
            $html .= '
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>';

        }


        $html .= '</table></body></html>';
        echo $html;
    }

    public function lsb_new($option = "")//berubah
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=inventory_rm_standard_actual_$format.xls");
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

        // FILTER PRICE PERIOD
        $year = $filter_from ? date('Y', strtotime($filter_from)) : date('Y');
        $period_year_from = $year . '-01-01';
        $period_year_to   = date($year . '-12-t');

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
            COALESCE(x.begin_stock) AS begin_stock,
            
            COALESCE(std.price, 0) AS std_price,

            COALESCE(d.qty_scan_in,0) as receipt_qty, 
            COALESCE(i.qty,0) + COALESCE(o.qty_bpm_scan,0) as bpm_qty, 
            COALESCE(k.qty,0) as adj_in_qty, 
            COALESCE(f.qty,0) as qty_issued,
            COALESCE(f2.qty,0) as qty_issued_supply_sheet,
            COALESCE(f3.qty,0) as qty_issued_non_supply_sheet,
            COALESCE(j.qty,0) + COALESCE(f4.qty,0) + COALESCE(f3.qty,0) as qty_kanban,
            COALESCE(f5.qty,0) as qty_issued_material_request,
            COALESCE(f6.qty,0) as qty_issued_non_supply_sheet_SJ,
            COALESCE(f7.qty,0) as qty_issued_non_supply_sheet_SP,
            COALESCE(m.qty,0) as adj_out_qty,
            COALESCE(n.qty,0) as bpb_qty, 

            (COALESCE(h1.qty_issued, 0) + COALESCE(i1.qty_trans_rm_out, 0)) AS qty_out_minus1,
            (COALESCE(h2.qty_issued, 0) + COALESCE(i2.qty_trans_rm_out, 0)) AS qty_out_minus2,
            (COALESCE(h3.qty_issued, 0) + COALESCE(i3.qty_trans_rm_out, 0)) AS qty_out_minus3,

            (COALESCE(d.qty_scan_in,0) + COALESCE(h.qty_stock_rm, 0) + COALESCE(i.qty, 0) + COALESCE(k.qty, 0) + COALESCE(o.qty_bpm_scan, 0)) as qty_in,
            (COALESCE(f.qty,0) + COALESCE(j.qty, 0) + COALESCE(m.qty, 0)+ COALESCE(n.qty, 0)) as qty_out

            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id and b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
            LEFT JOIN item_family_subs l ON a.item_sub_family_id = l.id
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) f ON a.id = f.item_rm_id

            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from_minus1' AND '$filter_to_minus1' GROUP BY item_rm_id) h1 ON a.id = h1.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from_minus1' AND '$filter_to_minus1' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i1 ON a.id = i1.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from_minus2' AND '$filter_to_minus2' GROUP BY item_rm_id) h2 ON a.id = h2.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from_minus2' AND '$filter_to_minus2' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i2 ON a.id = i2.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from_minus3' AND '$filter_to_minus3' GROUP BY item_rm_id) h3 ON a.id = h3.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from_minus3' AND '$filter_to_minus3' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i3 ON a.id = i3.item_rm_id
            
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_stock_rm FROM os_rm WHERE trans_date >= '$filter_from' AND trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) GROUP BY item_rm_id) h ON a.id = h.item_rm_id            

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type,SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) AND a.transaction_type = 'BPM'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) i ON a.id = i.item_rm_id

            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_bpm_scan
                FROM scan_item_bpm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                GROUP BY a.item_rm_id) o ON a.id = o.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) AND a.transaction_type = 'ADJ IN STO'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) k ON a.id = k.item_rm_id

            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and request_no like '%SH-%' GROUP BY item_rm_id) f2 ON a.id = f2.item_rm_id
            
            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and request_no like '%PRQ-%' GROUP BY item_rm_id) f5 ON a.id = f5.item_rm_id

            LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and `type` like '%WIP%' GROUP BY item_rm_id) f4 ON a.id = f4.item_rm_id
            LEFT JOIN (
                SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as qty 
                FROM issued_material_details a
                JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                WHERE a.created_date >= '$filter_from' AND a.created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.request_no like '%REQ-%' AND b.type = 'Issued Production'
                GROUP BY a.item_rm_id
            ) f3 ON a.id = f3.item_rm_id
        
            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.transaction_type = 'KANBAN WO'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) j ON a.id = j.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as qty 
                FROM issued_material_details a
                JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                WHERE a.created_date >= '$filter_from' AND a.created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.request_no like '%REQ-%' AND b.type = 'Issued Subcont'
                GROUP BY a.item_rm_id
            ) f6 ON a.id = f6.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as qty 
                FROM issued_material_details a
                JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                WHERE a.created_date >= '$filter_from' AND a.created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.request_no like '%REQ-%' AND b.type = 'Issued Customer'
                GROUP BY a.item_rm_id
            ) f7 ON a.id = f7.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.transaction_type = 'ADJ OUT STO'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) m ON a.id = m.item_rm_id

            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.transaction_type = 'BPB'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) n ON a.id = n.item_rm_id

            LEFT JOIN (SELECT a.id, a.number, ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
                        FROM item_rm a
                        LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_from'  GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date < '$filter_from' GROUP BY item_rm_id) c ON a.id = c.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'IN' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
                        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date < '$filter_from' GROUP BY a.item_rm_id) e ON a.id = e.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE created_date < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date < '$filter_from' AND transaction_kind = 'OUT' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
                        LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
                    ) x ON a.id = x.id
            
            LEFT JOIN (
                SELECT a.* 
                FROM standard_price_rm a
                WHERE a.deleted = 0 AND a.start_date >= '$period_year_from' AND a.end_date <= '$period_year_to' 
            ) std ON std.item_rm_id = a.id
        
        WHERE c.id LIKE '%$filter_item_category%' 
        AND b.number LIKE '%$filter_item_family%' 
        AND a.id LIKE '%$filter_items%' 
        AND a.division LIKE '%$filter_division%' 
        GROUP BY a.id
        ORDER BY c.name DESC, b.name DESC, a.number");

        $html = '<html><head><title>Print Data</title></head>
            <style>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                    }
                    .header-section {
                        overflow: hidden;
                        margin-bottom: 20px;
                    }
                    .company-info {
                        float: left;
                        width: 60%;
                        font-size: 12px;
                        text-align: left;
                    }
                    .print-info {
                        float: right;
                        width: 38%;
                        font-size: 12px;
                        text-align: right;
                    }
                    .company-logo {
                        vertical-align: top;
                        padding-right: 10px;
                    }
                    .company-details b {
                        font-size: 14px;
                    }
                    .company-details span {
                        font-size: 10px;
                    }
                    .report-title {
                        text-align: center;
                        margin-top: 20px;
                        margin-bottom: 20px;
                    }
                    .report-title h3 {
                        margin: 0;
                        font-size: 18px;
                    }
                    .report-title small {
                        font-size: 12px;
                    }
                    #customers {
                        border-collapse: collapse;
                        width: 100%;
                        font-size: 13px; 
                        margin-top: 15px;
                    }
                    #customers th,
                    #customers td {
                        border: 1px solid #ddd;
                        padding: 4px 8px; 
                    }
                    #customers th {
                        background-color: #f0f0f0;
                        text-align: center;
                        color: black;
                        font-weight: bold;
                    }
                    #customers tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    #customers tr:hover {
                        background-color: #f1f1f1;
                    }
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    .font-bold { font-weight: bold; }
                    .bg-light-green { background-color: #CAFFB3; } /* Untuk baris kelompok akun */
                    .bg-grey { background-color: #EBEBEB; } /* Untuk grand total */

                    .link-transaction {
                        color: inherit;
                        text-decoration: none;
                    }
                    .link-transaction:hover {
                        color: inherit;
                        font-weight: bolder;
                        text-decoration: underline;
                    }

                    .clearfix::after {
                        content: "";
                        clear: both;
                        display: table;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        font-family: Arial, sans-serif;
                        font-size: 11px; /* Ukuran font sedikit diperkecil karena kolom bertambah */
                        text-align: center;
                    }

                    /* Warna Header */
                    .bg-summary { background-color: #f2f2f2; font-weight: bold; }
                    .bg-standard { background-color: #c6efce; }
                    .bg-actual { background-color: #deeaf6; }
                    .bg-white { background-color: #fff; }
                    .bg-grey { background-color: #e7e6e6; }
                    .bg-yellow { background-color: #fffccc; }
                    .bg-blue { background-color: #81a1d1; color: white; }
                </style>
            </style>
            <body>
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
                <h3 style="margin:0;">REPORT INVENTORY RM STANDARD AND ACTUAL <i>(RECAP)</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';

        $html .= '<table id="customers" border="1" style="font-size: 11px;">
            <thead>
                <tr class="bg-summary">
                    <th rowspan="4">No</th>
                    <th rowspan="4">Product No</th>
                    <th rowspan="4">Product Name</th>
                    <th rowspan="4">Uom</th>
                    <th rowspan="4">Division</th>
                    <th rowspan="4">Category</th>
                    <th rowspan="4">Product Family</th>
                    <th rowspan="4">Sub Product Family</th>
                    <th colspan="24" class="bg-blue">SUMMARY</th>
                    <th colspan="15" class="bg-blue">DETAIL</th>
                </tr>
                
                <tr>
                    <th colspan="6" class="bg-grey">BEGIN</th>
                    <th colspan="6" class="bg-grey">IN</th>
                    <th colspan="6" class="bg-grey">OUT</th>
                    <th colspan="6" class="bg-grey">ENDING</th>

                    
                    <th rowspan="2" colspan="3">IN</th>
                    <th rowspan="2" colspan="7">OUT</th>
                    <th rowspan="3" width="100">Total<br>In</th>
                    <th rowspan="3" width="100">Total<br>Out</th>
                    <th rowspan="3" width="100">Selisih Summary <br>VS Detail (IN)</th>
                    <th rowspan="3" width="100">Selisih Summary <br>VS Detail (OUT)</th>
                    <th rowspan="3" width="100">ITO (MONTH)</th>
                </tr>

                <tr>
                    <th rowspan="2">QTY</th>
                    <th colspan="2" style="background-color: #c6efce;">STANDARD</th>
                    <th colspan="2" style="background-color: #deeaf6;">ACTUAL</th>
                    <th rowspan="2" style="background-color: #e8e8e8;">VARIANCE</th>
                    
                    <th rowspan="2">QTY</th>
                    <th colspan="2" style="background-color: #c6efce;">STANDARD</th>
                    <th colspan="2" style="background-color: #deeaf6;">ACTUAL</th>
                    <th rowspan="2" style="background-color: #e8e8e8;">VARIANCE</th>

                    <th rowspan="2">QTY</th>
                    <th colspan="2" style="background-color: #c6efce;">STANDARD</th>
                    <th colspan="2" style="background-color: #deeaf6;">ACTUAL</th>
                    <th rowspan="2" style="background-color: #e8e8e8;">VARIANCE</th>

                    <th rowspan="2">QTY</th>
                    <th colspan="2" style="background-color: #c6efce;">STANDARD</th>
                    <th colspan="2" style="background-color: #deeaf6;">ACTUAL</th>
                    <th rowspan="2" style="background-color: #e8e8e8;">VARIANCE</th>
                </tr>

                <tr>
                    <th style="background-color: #c6efce;">PRICE</th>
                    <th style="background-color: #c6efce;">AMOUNT</th>
                    <th style="background-color: #deeaf6;">PRICE</th>
                    <th style="background-color: #deeaf6;">AMOUNT</th>

                    <th style="background-color: #c6efce;">PRICE</th>
                    <th style="background-color: #c6efce;">AMOUNT</th>
                    <th style="background-color: #deeaf6;">PRICE</th>
                    <th style="background-color: #deeaf6;">AMOUNT</th>

                    <th style="background-color: #c6efce;">PRICE</th>
                    <th style="background-color: #c6efce;">AMOUNT</th>
                    <th style="background-color: #deeaf6;">PRICE</th>
                    <th style="background-color: #deeaf6;">AMOUNT</th>

                    <th style="background-color: #c6efce;">PRICE</th>
                    <th style="background-color: #c6efce;">AMOUNT</th>
                    <th style="background-color: #deeaf6;">PRICE</th>
                    <th style="background-color: #deeaf6;">AMOUNT</th>


                    <th rowspan="2" width="80">Purchase</th>
                    <th rowspan="2" width="80">BPM</th>
                    <th rowspan="2" width="80">ADJ STO</th>
                    <th rowspan="2" width="80">Supply Sheet</th>
                    <th rowspan="2" width="80">Material Request</th>
                    <th rowspan="2" width="80">Kanban PRD</th>
                    <th rowspan="2" width="80">Kanban Subcont Jasa</th>
                    <th rowspan="2" width="80">Kanban Subcont Product</th>
                    <th rowspan="2" width="80">BPB</th>
                    <th rowspan="2" width="80">ADJ STO</th>
                </tr>
            </thead>';

                
        $no = 1;
        $totalBeginStock = 0;
        $totalIn = 0;
        $totalOut = 0;
        $totalEndingStock = 0;

        $totalReceiptQty = 0;
        $totalBpmQty = 0;
        $totalAdjInQty = 0;

        $totalQtyIssuedSupplySheet = 0;
        $totalQtyIssuedMaterialRequest = 0;
        $totalQtyKanban = 0;
        $totalQtyKanbanSJ = 0;
        $totalQtyKanbanSP = 0;
        $totalAdjOutQty = 0;
        $totalBpbQty = 0;

        $totalQtyIn = 0;
        $totalQtyOut = 0;
        $totalQtySelisihIn = 0;
        $totalQtySelisihOut = 0;

        $totalIto = 0;

        foreach ($records as $record) {
            $item_rm_id = $record->id;

            $totalBeginStock += @$record->begin_stock;
            $totalIn += $record->qty_in;
            $totalOut += $record->qty_out;
            $totalEndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out;
            
            $totalReceiptQty += $record->receipt_qty;
            $totalBpmQty += $record->bpm_qty;
            $totalAdjInQty += $record->adj_in_qty;

            $totalQtyIssuedSupplySheet += $record->qty_issued_supply_sheet;
            $totalQtyIssuedMaterialRequest += $record->qty_issued_material_request;
            $totalQtyKanban += $record->qty_kanban;
            $totalQtyKanbanSJ += $record->qty_issued_non_supply_sheet_SJ;
            $totalQtyKanbanSP += $record->qty_issued_non_supply_sheet_SP;
            $totalAdjOutQty += $record->adj_out_qty;
            $totalBpbQty += $record->bpb_qty;

            $totalQtyIn += ($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty);
            $totalQtyOut += ($record->qty_issued_supply_sheet + $record->qty_issued_material_request + $record->qty_kanban + $record->qty_issued_non_supply_sheet_SJ + $record->qty_issued_non_supply_sheet_SP + $record->adj_out_qty + $record->bpb_qty);
            $totalQtySelisihIn += (($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty) - $record->qty_in);
            $totalQtySelisihOut += (($record->qty_issued_supply_sheet + $record->qty_issued_material_request + $record->qty_kanban + $record->qty_issued_non_supply_sheet_SJ + $record->qty_issued_non_supply_sheet_SP + $record->adj_out_qty + $record->bpb_qty) - $record->qty_out);

            $total_sales_minus = $record->qty_out_minus1 + $record->qty_out_minus2 + $record->qty_out_minus3;
            
            $avg_sales_minus_numeric = ($total_sales_minus > 0) ? ($total_sales_minus / 3) : 0;
            $avg_sales_minus = number_format($avg_sales_minus_numeric, 2); // Hanya untuk tampilan

            $ending_stock = (@$record->begin_stock + $record->qty_in) - $record->qty_out;

            $_stock_coverage_numeric = 0;
            if ($avg_sales_minus_numeric > 0) {
                $_stock_coverage_numeric = $ending_stock / $avg_sales_minus_numeric;
            }

            $totalIto += $_stock_coverage_numeric;

            $stock_coverage = ($avg_sales_minus_numeric > 0)
                ? number_format($_stock_coverage_numeric, 2)
                : '0'; // atau bisa diganti jadi '0.00' atau '-'

            
            // VARIABLES AND CALCULATIONS
            $standard_price     = $record->std_price ?? 0;

            $begin_qty              = @$record->begin_stock;
            $begin_standard_amount  = $begin_qty * $standard_price;
            $begin_actual_price     = 0; // data dari upload master nanti oleh user
            $begin_actual_amount    = $begin_qty * $begin_actual_price;
            $begin_variance         = 0;
            // $begin_variance      = $begin_actual_amount - $begin_standard_amount;

            $html .= '<tbody>';
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $record->number . '</td>
                            <td>' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->division . '</td>
                            <td>' . $record->category_name . '</td>
                            <td>' . $record->prodfam . '</td>
                            <td>' . $record->sub_prodfam . '</td>
                            
                            <td style="text-align:right;">' . number_format($begin_qty, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($begin_standard_amount, 2) . '</td>
                            <td style="text-align:right;">' . number_format($begin_actual_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($begin_actual_amount, 2) . '</td>
                            <td style="text-align:right;">' . number_format($begin_variance, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>

                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>

                            <td style="text-align:right;">' . number_format((@$record->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($standard_price, 2) . '</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            
                            <td style="text-align:right;">' . $record->receipt_qty . '</td>
                            <td style="text-align:right;">' . $record->bpm_qty . '</td>
                            <td style="text-align:right;">' . $record->adj_in_qty . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_issued_supply_sheet,2) . '</td>
                            <td style="text-align:right;">' . $record->qty_issued_material_request . '</td>
                            <td style="text-align:right;">' . $record->qty_kanban . '</td>
                            <td style="text-align:right;">' . $record->qty_issued_non_supply_sheet_SJ . '</td>
                            <td style="text-align:right;">' . $record->qty_issued_non_supply_sheet_SP . '</td>
                            <td style="text-align:right;">' . $record->bpb_qty . '</td>
                            <td style="text-align:right;">' . $record->adj_out_qty . '</td>

                            <td style="text-align:right;">' . number_format($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty,2) . '</td>
                            <td style="text-align:right;">' . number_format($record->qty_issued_supply_sheet + $record->qty_issued_material_request + $record->qty_kanban + $record->qty_issued_non_supply_sheet_SJ + $record->qty_issued_non_supply_sheet_SP + $record->adj_out_qty + $record->bpb_qty,2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->receipt_qty + $record->bpm_qty + $record->adj_in_qty) - $record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->qty_issued_supply_sheet + $record->qty_issued_material_request + $record->qty_kanban + $record->qty_issued_non_supply_sheet_SJ + $record->qty_issued_non_supply_sheet_SP + $record->adj_out_qty + $record->bpb_qty) - $record->qty_out, 2) . '</td>

                            <td style="text-align:right;">' . $stock_coverage . '</td>
                        </tr>';
            $no++;
        }

        $html .= '<tr>
            <td colspan="8" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>

            <td style="text-align:right;">' . number_format($totalReceiptQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalBpmQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalAdjInQty, 2) . '</td>

            <td style="text-align:right;">' . number_format($totalQtyIssuedSupplySheet, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyIssuedMaterialRequest, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyKanban, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyKanbanSJ, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyKanbanSP, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalBpbQty, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalAdjOutQty, 2) . '</td>
           
            <td style="text-align:right;">' . number_format($totalQtyIn, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtyOut, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtySelisihIn, 2) . '</td>
            <td style="text-align:right;">' . number_format($totalQtySelisihOut, 2) . '</td>

            <td style="text-align:right;">' . $totalIto . '</td>
            
        </tr>';
        $html .= '</tbody>';
      
        $html .= '</table></body></html>';
        echo $html;
    }
}
