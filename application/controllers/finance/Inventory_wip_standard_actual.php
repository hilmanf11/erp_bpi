<?php
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property CI_Form_validation $form_validation
 * @property Crud $crud
 */
class Inventory_wip_standard_actual extends CI_Controller
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
            $this->load->view('finance/inventory_wip_standard_actual');
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

    public function readWO()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('supply_sheets', ["workorder" => $post]);
        echo json_encode($send);
    }


    function customCss() 
    {
        $css = '<style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                        margin: 20px;
                        background-color: white;
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
                            background-color: #DEEBF7 !important;
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

                    /* Warna Nominal */
                    .negatif { color: red; }
                    .positif { color: black; }

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

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_wip_rm_$format.xls");
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
        $filter_workorder = $this->input->get('filter_workorder');

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);
        //------------------------------------ Mengambil Filter dari Input GET berakhir disini----------------------------------//

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_items_sql = "";

        if ($filter_division != "" && !empty($filter_items)) {
            $filter_items_sql = "AND a.id LIKE '%$filter_items%'";
        }

        elseif (!empty($filter_workorder)) {
            $items_from_wo = $this->crud->query("
                SELECT DISTINCT a.item_rm_id 
                FROM supply_sheets a 
                WHERE a.workorder LIKE '%$filter_workorder%'
            ");

            if (count($items_from_wo) > 0) {
                $filter_ids = implode(",", array_map(function($row) {
                    return "'{$row->item_rm_id}'";
                }, $items_from_wo));
                $filter_items_sql = "AND a.id IN ($filter_ids)";
            } else {
                // Kalau WO tidak ketemu item → kosongkan hasil
                $filter_items_sql = "AND a.id IN ('__NOT_FOUND__')";
            }
        }

        elseif (!empty($filter_items)) {
            $filter_items_sql = "AND a.id LIKE '%$filter_items%'";
        }

        function getQtyMap($db, $query, $keyField, $valueField) {
            $result = $db->query($query)->result_array();
            $map = [];
            foreach ($result as $row) {
                $map[$row[$keyField]] = (float)$row[$valueField];
            }
            return $map;
        }

        $filter_from_q = $this->db->escape($filter_from);
        $filter_to_q   = $this->db->escape($filter_to);

        // BEGIN
        // IN
        // $qss     = getQtyMap($this->db, "SELECT item_rm_id, SUM(qty) qty FROM issued_material_details WHERE created_date < $filter_from_q AND request_no LIKE '%SH-%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
        $qss = getQtyMap(
            $this->db,
            "SELECT 
                parent.id AS item_rm_id,
                SUM(imd.qty) AS qty
            FROM item_rm parent
            LEFT JOIN issued_material_details imd
                    ON imd.created_date < $filter_from_q
                AND (
                        imd.request_no LIKE '%SH-%'
                    OR imd.request_no LIKE '%PRQ-%'
                )
            LEFT JOIN item_rm child 
                    ON child.id = imd.item_rm_id
            WHERE 
                imd.item_rm_id = parent.id
                OR child.number LIKE CONCAT('CR-', parent.number)
                OR child.number LIKE CONCAT('PL-', parent.number)
            GROUP BY parent.id
            ORDER BY parent.number",
            'item_rm_id',
            'qty'
        );
        $qsns    = getQtyMap($this->db, "
            SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as qty 
            FROM issued_material_details a
            JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
            WHERE a.created_date < $filter_from_q and a.request_no like '%REQ-%' AND b.type = 'Issued Production'
            GROUP BY a.item_rm_id", 'item_rm_id', 'qty');
        $qtrb    = getQtyMap($this->db, "SELECT item_rm_id, SUM(qty) qty_bpb FROM transaction_rm WHERE transaction_type='BPB' AND request_date < $filter_from_q GROUP BY item_rm_id", 'item_rm_id', 'qty_bpb');
        $qtrk    = getQtyMap($this->db, "SELECT item_rm_id, SUM(qty) qty_kanban FROM transaction_rm WHERE transaction_type='KANBAN WO' AND request_date < $filter_from_q GROUP BY item_rm_id", 'item_rm_id', 'qty_kanban');
        $qiw     = getQtyMap($this->db, "SELECT item_rm_id, SUM(qty) qty FROM issued_material_details WHERE created_date < $filter_from_q AND type LIKE '%WIP%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
        $qm      = getQtyMap($this->db, "SELECT item_rm_id, SUM(qty) qty FROM issued_material_details WHERE created_date < $filter_from_q AND request_no LIKE '%PRQ-%' GROUP BY item_rm_id", 'item_rm_id', 'qty');
        $qai     = getQtyMap($this->db, "SELECT item_rm_id, SUM(qty) qty_adj_in FROM transaction_wip WHERE transaction_type='ADJ IN' AND request_date < $filter_from_q GROUP BY item_rm_id", 'item_rm_id', 'qty_adj_in');

        // OUT
        $qbw     = getQtyMap($this->db, "SELECT item_rm_id, SUM(qty) qty_bpm_whs FROM bpm WHERE status='1' AND request_date < $filter_from_q GROUP BY item_rm_id", 'item_rm_id', 'qty_bpm_whs');
        $qtrbpm  = getQtyMap($this->db, "SELECT item_rm_id, SUM(qty) qty_bpm_manual FROM transaction_rm WHERE transaction_type='BPM' AND request_date < $filter_from_q GROUP BY item_rm_id", 'item_rm_id', 'qty_bpm_manual');

        $qr      = getQtyMap($this->db, "
            SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) qty_in_checksheet
            FROM (
                SELECT b.item_fg_id, SUM(a.qty) total_qty
                FROM scan_item_receipts_fg a
                JOIN checksheets b ON b.number = a.checksheet_number
                WHERE b.packing_date < $filter_from_q
                GROUP BY b.item_fg_id
            ) t
            JOIN bom ON bom.item_fg_id = t.item_fg_id
            GROUP BY bom.item_rm_id
        ", 'item_rm_id', 'qty_in_checksheet');

        $qrnbfg  = getQtyMap($this->db, "
            SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) qty_in_no_checksheet
            FROM (
                SELECT a.item_fg_id, SUM(a.qty) total_qty
                FROM scan_item_receipts_fg a
                WHERE a.type = 'NBFG' AND a.packing_date < $filter_from_q
                GROUP BY a.item_fg_id
            ) t
            JOIN bom ON bom.item_fg_id = t.item_fg_id
            GROUP BY bom.item_rm_id
        ", 'item_rm_id', 'qty_in_no_checksheet');

        $qtrf    = getQtyMap($this->db, "
            SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) initial_in
            FROM (
                SELECT item_fg_id, SUM(qty) total_qty
                FROM transaction_fg
                WHERE transaction_kind = 'IN' AND transaction_type = 'RECEIPT FG'
                AND request_date < $filter_from_q
                GROUP BY item_fg_id
            ) t
            JOIN bom ON bom.item_fg_id = t.item_fg_id
            GROUP BY bom.item_rm_id
        ", 'item_rm_id', 'initial_in');

        $qwr     = getQtyMap($this->db, "
            SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) qty_in_wip_receipt
            FROM (
                SELECT item_fg_id, SUM(qty) total_qty
                FROM wip_receipts
                WHERE division = 'MTS' AND trans_date < $filter_from_q
                GROUP BY item_fg_id
            ) t
            JOIN bom ON bom.item_fg_id = t.item_fg_id
            GROUP BY bom.item_rm_id
        ", 'item_rm_id', 'qty_in_wip_receipt');

        $qin     = getQtyMap($this->db, "
            SELECT 
                    b.item_rm_id,
                    SUM(b.composition * COALESCE(d.qty_ng,0)) AS qty_ng
                FROM bom b
                JOIN (
                    SELECT a.id AS item_fg_id
                    FROM item_fg a
                ) fg ON b.item_fg_id = fg.item_fg_id
                LEFT JOIN (
                    SELECT aa.item_fg_id, SUM(aa.qty_product) AS qty_ng 
                    FROM (
                        SELECT DISTINCT document, item_fg_id, qty_product 
                        FROM item_ng 
                        WHERE trans_date < $filter_from_q
                    ) aa 
                    GROUP BY aa.item_fg_id
                ) d ON b.item_fg_id = d.item_fg_id
                GROUP BY b.item_rm_id", 
            'item_rm_id', 'qty_ng');
        $qtwo    = getQtyMap($this->db, "SELECT item_rm_id, SUM(qty) qty_adj_out FROM transaction_wip WHERE transaction_type='ADJ OUT' AND request_date < $filter_from_q GROUP BY item_rm_id", 'item_rm_id', 'qty_adj_out');

        // IN------------
        // SUPPLY------------------------------------------------------------------------------------------
        $query_supply_sheet = "SELECT 
            parent.id AS item_rm_id,
            parent.number AS parent_number,
            parent.name AS parent_name,

            -- Qty utama
            COALESCE((
                SELECT SUM(qty)
                FROM issued_material_details
                WHERE item_rm_id = parent.id
                AND created_date >= '$filter_from'
                AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                AND request_no LIKE '%SH-%'
            ), 0) AS qty,

            -- Qty other (CR- / PL- berdasarkan number parent)
            COALESCE((
                SELECT SUM(imd.qty)
                FROM issued_material_details imd
                JOIN item_rm child ON child.id = imd.item_rm_id
                WHERE (
                        child.number LIKE CONCAT('CR-', parent.number)
                    OR child.number LIKE CONCAT('PL-', parent.number)
                )
                AND imd.created_date >= '$filter_from'
                AND imd.created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                AND (
                    imd.request_no LIKE '%SH-%'
                    OR imd.request_no LIKE '%PRQ-%'
                )
            ), 0) AS qty_other

        FROM item_rm parent
        ORDER BY parent.number";

        $query_supply_non_sheet = "SELECT a.item_rm_id, COALESCE(SUM(a.qty), 0) as qty 
        FROM issued_material_details a
        JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
        WHERE a.created_date >= '$filter_from' AND a.created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and a.request_no like '%REQ-%' AND b.type = 'Issued Production'
        GROUP BY a.item_rm_id";

        $query_trans_rm_bpb = "SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty_bpb 
        FROM transaction_rm 
        WHERE transaction_type='BPB' AND request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
        GROUP BY item_rm_id";

        $query_trans_rm_kanban = "SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty_kanban 
        FROM transaction_rm 
        WHERE transaction_type='KANBAN WO' AND request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
        GROUP BY item_rm_id";

        $query_issued_wip = "SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty 
        FROM issued_material_details 
        WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) AND `type` LIKE '%WIP%' 
        GROUP BY item_rm_id";
        // MATREQ---------------------------------------------------------------------------------------------
        $query_matreq = "SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty 
        FROM issued_material_details 
        WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) and request_no like '%PRQ-%' 
        GROUP BY item_rm_id";
        // ADJIN---------------------------------------------------------------------------------------------
        $query_adj_in = "SELECT item_rm_id, sum(qty) as qty_adj_in 
        FROM transaction_wip 
        WHERE transaction_type='ADJ IN' AND request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
        GROUP BY item_rm_id";
        // OUT---------------
        // RETURN--------------------------------------------------------------------------------------------
        $query_bpm_whs = "SELECT item_rm_id, sum(qty) as qty_bpm_whs 
        FROM bpm
        WHERE status='1' and request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
        GROUP BY item_rm_id";

        $query_trans_rm_bpm = "SELECT item_rm_id, sum(qty) as qty_bpm_manual 
        FROM transaction_rm 
        WHERE transaction_type='BPM' and request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
        GROUP BY item_rm_id";
        // RFG-----------------------------------------------------------------------------------------------
        $query_receipt = "SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) AS qty_in_checksheet
        FROM (
            SELECT b.item_fg_id, SUM(a.qty) AS total_qty
            FROM scan_item_receipts_fg a
            JOIN checksheets b ON b.number = a.checksheet_number
            WHERE b.packing_date >= '$filter_from' AND b.packing_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
            GROUP BY b.item_fg_id
        ) t
        JOIN bom ON bom.item_fg_id = t.item_fg_id
        GROUP BY bom.item_rm_id
        ";

        $query_receipt_nbfg = "SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) AS qty_in_no_checksheet
        FROM (
            SELECT a.item_fg_id, SUM(a.qty) AS total_qty
            FROM scan_item_receipts_fg a
            WHERE a.type = 'NBFG' AND a.packing_date >= '$filter_from' AND a.packing_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
            GROUP BY a.item_fg_id
        ) t
        JOIN bom ON bom.item_fg_id = t.item_fg_id
        GROUP BY bom.item_rm_id";

        $query_trans_receipt_fg = "SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) AS initial_in
        FROM (
            SELECT item_fg_id, SUM(qty) AS total_qty
            FROM transaction_fg
            WHERE transaction_kind = 'IN'
            AND transaction_type = 'RECEIPT FG'
            AND request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
            GROUP BY item_fg_id
        ) t
        JOIN bom ON bom.item_fg_id = t.item_fg_id
        GROUP BY bom.item_rm_id";

        $query_wip_receipt = "SELECT bom.item_rm_id, SUM(t.total_qty * bom.composition) AS qty_in_wip_receipt
        FROM (
            SELECT item_fg_id, SUM(qty) AS total_qty
            FROM wip_receipts
            WHERE division = 'MTS'
            AND trans_date >= '$filter_from' AND trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
            GROUP BY item_fg_id
        ) t
        JOIN bom ON bom.item_fg_id = t.item_fg_id
        GROUP BY bom.item_rm_id";
        // NG-----------------------------------------------------------------------------------------------
        $query_item_ng_other = "
            SELECT 
                b.item_rm_id,
                SUM(b.composition * COALESCE(d.qty_ng,0)) AS qty_ng
            FROM bom b
            JOIN (
                SELECT a.id AS item_fg_id
                FROM item_fg a
            ) fg ON b.item_fg_id = fg.item_fg_id
            LEFT JOIN (
                SELECT aa.item_fg_id, SUM(aa.qty_product) AS qty_ng 
                FROM (
                    SELECT DISTINCT document, item_fg_id, qty_product 
                    FROM item_ng 
                    WHERE trans_date >= '$filter_from'
                    AND trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                    AND created_by = 'PRD01'
                ) aa 
                GROUP BY aa.item_fg_id
            ) d ON b.item_fg_id = d.item_fg_id
            GROUP BY b.item_rm_id
        ";

        $query_item_ng_process = "
            SELECT 
                b.item_rm_id,
                SUM(b.composition * COALESCE(d.qty_ng,0)) AS qty_ng
            FROM bom b
            JOIN (
                SELECT a.id AS item_fg_id
                FROM item_fg a
            ) fg ON b.item_fg_id = fg.item_fg_id
            LEFT JOIN (
                SELECT aa.item_fg_id, SUM(aa.qty_product) AS qty_ng 
                FROM (
                    SELECT DISTINCT document, item_fg_id, qty_product 
                    FROM item_ng 
                    WHERE trans_date >= '$filter_from'
                    AND trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                    AND created_by != 'PRD01'
                ) aa 
                GROUP BY aa.item_fg_id
            ) d ON b.item_fg_id = d.item_fg_id
            GROUP BY b.item_rm_id
        ";
        // ADJ OUT-----------------------------------------------------------------------------------------------
        $query_trans_wip_out = "SELECT item_rm_id, sum(qty) as qty_adj_out 
        FROM transaction_wip 
        WHERE transaction_type='ADJ OUT' AND request_date >= '$filter_from' AND request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
        GROUP BY item_rm_id";

        $main_query = "
            SELECT 
            a.id,
            a.number, 
            a.name, 
            a.division, 
            a.uom,
            o.name AS prodfam, 
            p.name AS category_name,

            COALESCE(qss.qty,0) AS qty_supply_sheets,
            COALESCE(qsns.qty,0) AS qty_non_supply_sheets,
            COALESCE(qtrb.qty_bpb,0) AS qty_bpb,
            COALESCE(qtrk.qty_kanban,0) AS qty_kanban,
            COALESCE(qiw.qty,0) AS qty_prodfam_wip,

            (COALESCE(qss.qty,0) + COALESCE(qsns.qty,0) + COALESCE(qtrb.qty_bpb,0) + COALESCE(qtrk.qty_kanban,0) + COALESCE(qiw.qty,0)) AS qty_supply,

            COALESCE(qm.qty,0) AS qty_matreq,
            COALESCE(qai.qty_adj_in,0) AS qty_adj_in,
            COALESCE(qss.qty_other,0) AS qty_other,

            COALESCE(qbw.qty_bpm_whs,0) AS qty_bpm_whs,
            COALESCE(qtrbpm.qty_bpm_manual,0) AS qty_bpm_manual,
            (COALESCE(qbw.qty_bpm_whs,0) + COALESCE(qtrbpm.qty_bpm_manual,0)) AS qty_return,

            (COALESCE(qr.qty_in_checksheet,0) + COALESCE(qrnbfg.qty_in_no_checksheet,0) + COALESCE(qtrf.initial_in,0) + COALESCE(qwr.qty_in_wip_receipt,0)) AS qty_rfg,

            COALESCE(qino.qty_ng,0) AS qty_ng_other,
            COALESCE(qinp.qty_ng,0) AS qty_ng_process,
            COALESCE(qtwo.qty_adj_out,0) AS qty_adj_out

        FROM item_rm a
        LEFT JOIN ($query_supply_sheet) qss        ON a.id = qss.item_rm_id
        LEFT JOIN ($query_supply_non_sheet) qsns   ON a.id = qsns.item_rm_id
        LEFT JOIN ($query_trans_rm_bpb) qtrb       ON a.id = qtrb.item_rm_id
        LEFT JOIN ($query_trans_rm_kanban) qtrk    ON a.id = qtrk.item_rm_id
        LEFT JOIN ($query_issued_wip) qiw          ON a.id = qiw.item_rm_id
        LEFT JOIN ($query_matreq) qm               ON a.id = qm.item_rm_id
        LEFT JOIN ($query_adj_in) qai              ON a.id = qai.item_rm_id
        LEFT JOIN ($query_bpm_whs) qbw             ON a.id = qbw.item_rm_id
        LEFT JOIN ($query_trans_rm_bpm) qtrbpm     ON a.id = qtrbpm.item_rm_id
        LEFT JOIN ($query_receipt) qr              ON a.id = qr.item_rm_id
        LEFT JOIN ($query_receipt_nbfg) qrnbfg     ON a.id = qrnbfg.item_rm_id
        LEFT JOIN ($query_trans_receipt_fg) qtrf   ON a.id = qtrf.item_rm_id
        LEFT JOIN ($query_wip_receipt) qwr         ON a.id = qwr.item_rm_id
        LEFT JOIN ($query_item_ng_other) qino      ON a.id = qino.item_rm_id
        LEFT JOIN ($query_item_ng_process) qinp    ON a.id = qinp.item_rm_id
        LEFT JOIN ($query_trans_wip_out) qtwo      ON a.id = qtwo.item_rm_id
        LEFT JOIN item_familys o                   ON a.item_family_id = o.id
        LEFT JOIN item_categories p                ON a.item_category_id = p.id

        WHERE p.id LIKE '%$filter_item_category%'
        AND o.number LIKE '%$filter_item_family%'
        $filter_items_sql
        AND a.division LIKE '%$filter_division%'
        ORDER BY o.name DESC, p.name DESC, a.number";

        // Eksekusi query
        $data = $this->db->query($main_query)->result_array();
        foreach ($data as &$row) {
            $id = $row['id'];
            $row['begin_stock'] =
                ($qss[$id]     ?? 0) +
                ($qsns[$id]    ?? 0) +
                ($qtrb[$id]    ?? 0) +
                ($qtrk[$id]    ?? 0) +
                ($qiw[$id]     ?? 0) +
                ($qm[$id]      ?? 0) +
                ($qai[$id]     ?? 0) -
                ($qbw[$id]     ?? 0) -
                ($qtrbpm[$id]  ?? 0) -
                ($qr[$id]      ?? 0) -
                ($qrnbfg[$id]  ?? 0) -
                ($qtrf[$id]    ?? 0) -
                ($qwr[$id]     ?? 0) -
                ($qin[$id]     ?? 0) -
                ($qtwo[$id]    ?? 0);
        }

        $query_main2 = "
                        select a.id,
                        a.number,
                        a.name, 
                        COALESCE((COALESCE(i.begin_balance,0)) + COALESCE(c.qty_actual,0) + COALESCE(c2.qty_wip,0) + COALESCE(f.qty_subcont_jasa,0) +COALESCE(j.qty_adj_in,0) - COALESCE(g.qty_in_checksheet,0) - COALESCE(gb.initial_in,0) - COALESCE(gc.qty_in_wip_receipt,0) - COALESCE(h.qty_rfg_jasa,0)- COALESCE(k.qty_adj_out,0), 0) as ending_balance
                        FROM item_fg a
                        LEFT JOIN (
                                    select item_fg_id, sum(qty) as qty_actual FROM output_productions where trans_date between '$filter_from' AND '$filter_to' group by item_fg_id
                        ) c on a.id = c.item_fg_id
                        LEFT JOIN (
                                    select item_fg_id, sum(qty_wip) as qty_wip FROM output_productions where trans_date between '$filter_from' AND '$filter_to' group by item_fg_id
                        ) c2 on a.id = c2.item_fg_id
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
                                    where ab.packing_date between '$filter_from' AND '$filter_to' and ab.subcont_type='Jasa'
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
                                    SELECT a.id,
                                        COALESCE(e.qty_balance_wip, 0) + COALESCE(c.qty_actual, 0) + COALESCE(c2.qty_wip, 0) + COALESCE(f.qty_subcont_jasa, 0) + COALESCE(j.qty_adj_in, 0) - COALESCE(g.qty_in_checksheet, 0) - COALESCE(gb.initial_in, 0) - COALESCE(gc.qty_in_wip_receipt, 0) - COALESCE(h.qty_rfg_jasa, 0) - COALESCE(k.qty_adj_out, 0) AS begin_balance
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
                                        GROUP BY item_fg_id
                                    ) c ON a.id = c.item_fg_id

                                    LEFT JOIN (
                                        SELECT item_fg_id, SUM(qty_wip) AS qty_wip
                                        FROM output_productions
                                        WHERE trans_date >= '2025-05-01' AND trans_date < '$filter_from'
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
                        WHERE a.type != 'RM'AND a.status = 0
                        ORDER BY a.number
            ";

            $records2 = $this->crud->query($query_main2);

            $endingBalancePerRM = [];

            foreach ($records2 as $fg) {
                // Dapatkan semua baris bom di mana item_fg_id == $fg->id
                $bomRows = $this->crud->query("
                    SELECT item_rm_id, composition 
                    FROM bom 
                    WHERE item_fg_id = '{$fg->id}'
                ");

                foreach ($bomRows as $bom) {
                    $item_rm_id = $bom->item_rm_id;
                    $composition = floatval($bom->composition);
                    $ending = floatval($fg->ending_balance);

                    if (!isset($endingBalancePerRM[$item_rm_id])) {
                        $endingBalancePerRM[$item_rm_id] = 0;
                    }

                    $endingBalancePerRM[$item_rm_id] += $ending * $composition;
                }
            }

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
                    <th rowspan="2" width="100">STOCK WIP <br> PRODUCTION</th>
                    <th rowspan="2" width="100">BALANCE RM</th>
                </tr>
                <tr>
                    <th width="100">Supply</th>
                    <th width="100">Matreq</th>
                    <th width="100">ADJ IN</th>
                    <th width="100">IN OTHER <br> COMPONENT</th>
                    
                    <th width="100">Return</th>
                    <th width="100">RFG</th>
                    <th width="100">NG Other</th>
                    <th width="100">NG Process</th>
                    <th width="100">ADJ OUT</th>
                </tr>';

        $totalBeginStock = 0;
        $totalSupply = 0;
        $totalMatreq = 0;
        $totalAdjIn = 0;
        $totalReturn = 0;
        $totalRfg = 0;
        $totalNg = 0;
        $totalAdjOut = 0;
        $totalEndingBalanceTotal = 0;
        $totalEndingStock = 0;

        // Pastikan index array normal
        $data = array_values($data);

        // Loop dengan nomor urut
        foreach ($data as $index => $record) {
            $item_rm_id = $record['id'];
            $totalEndingBalance = @$endingBalancePerRM[$item_rm_id] ?? 0;

            $html .= '<tr>
                <td style="text-align:center">' . ($index + 1) . '</td>
                <td colspan="3">' . $record['number'] . '</td>
                <td colspan="2">' . $record['name'] . '</td>
                <td colspan="2">' . $record['uom'] . '</td>
                <td colspan="2">' . $record['division'] . '</td>
                <td colspan="2">' . $record['category_name'] . '</td>
                <td>' . $record['prodfam'] . '</td>
                <td style="text-align:right;">' . number_format($record['begin_stock'], 2) . '</td>
                <td style="text-align:right;">' . number_format($record['qty_supply'], 2) . '</td>
                <td style="text-align:right;">' . number_format($record['qty_matreq'], 2) . '</td>
                <td style="text-align:right;">' . number_format($record['qty_adj_in'], 2) . '</td>
                <td style="text-align:right;">' . number_format($record['qty_other'], 2) . '</td>
                <td style="text-align:right;">' . number_format($record['qty_return'], 2) . '</td>
                <td style="text-align:right;">' . number_format($record['qty_rfg'], 2) . '</td>
                <td style="text-align:right;">' . number_format($record['qty_ng_other'], 2) . '</td>
                <td style="text-align:right;">' . number_format($record['qty_ng_process'], 2) . '</td>
                <td style="text-align:right;">' . number_format($record['qty_adj_out'], 2) . '</td>
                <td style="text-align:right;">' . number_format($totalEndingBalance, 2) . '</td>
                <td style="text-align:right;">' . number_format((@$record['begin_stock'] + @$record['qty_supply'] + @$record['qty_matreq'] + @$record['qty_adj_in'] + $record['qty_other']) - (@$record['qty_return'] + @$record['qty_rfg'] + @$record['qty_ng_other'] + @$record['qty_ng_process'] + @$record['qty_adj_out'] + @$totalEndingBalance), 2) . '</td>
            </tr>';


            if ($filter_display == "DETAIL") {
                $html .= '  <tr>
                                <td colspan="25" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record['number'] . ' - ' . $record['name'] . '</b></td>
                            </tr>
                            <tr>
                                <th rowspan="2" width="20"></th>
                                <th rowspan="2" width="20">No</th>
                                <th colspan="2"rowspan="2">Trans Type</th>
                                <th colspan="2"rowspan="2">Created By</th>
                                <th colspan="3"rowspan="2">Trans Date</th>
                                <th colspan="2"rowspan="2">WO NO</th>
                                <th colspan="2"rowspan="2">Doc. No</th>
                                <th rowspan="2">Begin</th>
                                <th colspan="4">IN</th>
                                <th colspan="5">OUT</th>
                                <th colspan="2"rowspan="2">Balance</th>
                            </tr>
                            <tr>
                                <th width="100">Supply</th>
                                <th width="100">Matreq</th>
                                <th width="100">ADJ IN</th>
                                <th width="100">IN OTHER <br>COMPONENT</th>
                                <th width="100">Return</th>
                                <th width="100">RFG</th>
                                <th width="100">NG Other</th>
                                <th width="100">NG Process</th>
                                <th width="100">ADJ OUT</th>
                            </tr>';

                $nod = 1;
                $begin = @$record->begin_stock;
                $balance = 0;

                if ($filter_workorder != '') {
                    $qsupply = $this->crud->query("
                        SELECT b.workorder as wo_no, a.item_rm_id, a.created_date as request_date, a.request_no as doc_no, b.request_name, COALESCE(a.qty, 0) as qty_supply , 0 AS qty_other
                        FROM issued_material_details a 
                        JOIN supply_sheets b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.request_no like '%SH-%' and a.item_rm_id = '$item_rm_id' and b.workorder like '%$filter_workorder%'
                        union all
                        SELECT b.workorder AS wo_no, a.item_rm_id, a.created_date AS request_date, a.request_no AS doc_no, b.request_name, 0 AS qty_supply, a.qty AS qty_other
                        FROM issued_material_details a
                        JOIN supply_sheets b ON a.request_no = b.request_no
                        JOIN item_rm child ON child.id = a.item_rm_id
                        JOIN item_rm parent ON parent.id = '$item_rm_id'
                        WHERE DATE(a.created_date) BETWEEN '$filter_from' AND '$filter_to'
                        AND a.request_no LIKE '%SH-%'
                        AND (child.number LIKE CONCAT('CR-', parent.number) OR child.number LIKE CONCAT('PL-', parent.number))
                        AND b.workorder LIKE '%$filter_workorder%'
                        union all
                        SELECT '-' as wo_no, a.item_rm_id, a.created_date as request_date, a.request_no as doc_no, b.request_name, COALESCE(a.qty, 0) as qty_supply , 0 AS qty_other
                        FROM issued_material_details a 
                        JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.request_no like '%REQ-%' and a.item_rm_id = '$item_rm_id' AND b.type = 'Issued Production'
                        union all
                        select item_rm_id, qty as qty_supply, '-' as wo_no, request_date, request_no as doc_no, request_name, 0 AS qty_other from transaction_rm
                        where transaction_type='BPB' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'
                        union all
                        select item_rm_id, qty as qty_supply, '-' as wo_no, request_date, request_no as doc_no, request_name, 0 AS qty_other from transaction_rm
                        where transaction_type='KANBAN WO' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to' 
                        union all
                        select '-' as wo_no,item_rm_id, created_date as request_date, request_no as doc_no, '-' as request_name, COALESCE(qty, 0) as qty_supply, 0 AS qty_other
                        FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' and `type` like '%WIP%' and item_rm_id = '$item_rm_id'
                        ORDER BY request_date"
                    );
              
                    $qmatreq = $this->crud->query("
                        SELECT b.workorder AS wo_no, a.item_rm_id, a.created_date AS request_date, a.request_no AS doc_no, b.request_name, a.qty AS qty_matreq, 0 AS qty_other
                        FROM issued_material_details a
                        JOIN supply_requestions b ON a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id
                        WHERE DATE(a.created_date) BETWEEN '$filter_from' AND '$filter_to'
                        AND a.request_no LIKE '%PRQ-%'
                        AND a.item_rm_id = '$item_rm_id'
                        AND b.workorder LIKE '%$filter_workorder%'
                        union all
                        SELECT b.workorder AS wo_no, a.item_rm_id, a.created_date AS request_date, a.request_no AS doc_no, b.request_name, 0 AS qty_matreq, a.qty AS qty_other
                        FROM issued_material_details a
                        JOIN supply_requestions b ON a.request_no = b.request_no
                        JOIN item_rm child ON child.id = a.item_rm_id
                        JOIN item_rm parent ON parent.id = '$item_rm_id'
                        WHERE DATE(a.created_date) BETWEEN '$filter_from' AND '$filter_to'
                        AND a.request_no LIKE '%PRQ-%'
                        AND (
                                child.number LIKE CONCAT('CR-', parent.number)
                                OR child.number LIKE CONCAT('PL-', parent.number)
                            )
                        AND b.workorder LIKE '%$filter_workorder%'
                        ORDER BY request_date
                    ");

                    $qadj_in = $this->crud->query("
                        select item_rm_id, request_date, request_no, request_name, workorder, qty
                        from transaction_wip
                        where transaction_type='ADJ IN' and item_rm_id like '%$item_rm_id%' and request_date BETWEEN '$filter_from' and '$filter_to' and workorder like '%$filter_workorder%'
                    ");

                    $qsto_in = $this->crud->query("
                        select item_rm_id, request_date, request_no, request_name, workorder, qty
                        from transaction_wip
                        where transaction_type='STO IN' and item_rm_id like '%$item_rm_id%' and request_date BETWEEN '$filter_from' and '$filter_to' and workorder like '%$filter_workorder%'
                    ");

                    $qreturn = $this->crud->query("
                        select item_rm_id, request_date, request_no, request_name, qty, null as workorder
                        from bpm
                        where status='1' and item_rm_id like '%$item_rm_id%' and request_date BETWEEN '$filter_from' and '$filter_to'
                        union
                        select item_rm_id, request_date, request_no, request_name, qty, workorder
                        from transaction_rm
                        where transaction_type='BPM' and item_rm_id like '%$item_rm_id%' and request_date BETWEEN '$filter_from' and '$filter_to' and workorder like '%$filter_workorder%'
                    ");

                    $receipts = $this->crud->query("
                        select c.item_rm_id, a.wo_no as workorder, a.checksheet_label as request_no, d.name as request_name, b.packing_date as request_date, a.qty*c.composition as qty_rfg
                        FROM scan_item_receipts_fg a
                        JOIN checksheets b ON b.number = a.checksheet_number
                        JOIN bom c on a.item_fg_id = c.item_fg_id
                        LEFT JOIN users d ON a.created_by = d.username
                        WHERE c.item_rm_id like '%$item_rm_id%' and DATE_FORMAT(b.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' and a.wo_no like '%$filter_workorder%'
                    ");

                    $receiptsNB = $this->crud->query("
                        SELECT c.item_rm_id, f.checksheet_number, f.wo_no, SUM(f.qty * c.composition) AS qty, u.name AS username, f.packing_date AS trans_date
                        FROM new_barcode_fg a 
                        LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                        JOIN users u ON f.created_by = u.username
                        JOIN bom c ON f.item_fg_id = c.item_fg_id
                        WHERE c.item_rm_id LIKE '%$item_rm_id%' AND f.packing_date IS NOT NULL AND f.wo_no IS NOT NULL AND DATE_FORMAT(f.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' AND f.wo_no like '%$filter_workorder%'
                        GROUP BY c.item_rm_id, f.checksheet_number, f.wo_no, u.name, f.packing_date
                    ");

                    $receiptsWIP = $this->crud->query("
                        SELECT c.item_rm_id, a.checksheet_number, a.document_no, a.wo_no, SUM(a.qty * c.composition) AS qty, a.trans_date, u.name AS username, a.document_no AS checksheet_label
                        FROM wip_receipts a
                        INNER JOIN users u ON a.created_by = u.username
                        INNER JOIN bom c ON a.item_fg_id = c.item_fg_id
                        WHERE c.item_rm_id LIKE '%$item_rm_id%' AND a.division = 'MTS' AND a.trans_date IS NOT NULL AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND a.wo_no LIKE '%$filter_workorder%'
                        GROUP BY c.item_rm_id, a.checksheet_number, a.document_no, a.wo_no, a.trans_date, u.name
                    ");

                    $transFgs = $this->crud->query("
                        SELECT c.item_rm_id, a.request_no, a.workorder, SUM(a.qty * c.composition) AS qty, a.request_name, a.request_date
                        FROM transaction_fg a
                        INNER JOIN bom c ON a.item_fg_id = c.item_fg_id
                        WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND c.item_rm_id LIKE '%$item_rm_id%' AND a.request_date IS NOT NULL AND a.request_date BETWEEN '$filter_from' AND '$filter_to' AND a.workorder LIKE '%$filter_workorder%'
                        GROUP BY c.item_rm_id, a.request_no, a.workorder, a.request_name, a.request_date
                    ");

                    $qngo = $this->crud->query("
                        SELECT 
                            b.item_rm_id,
                            a.trans_date,
                            a.workorder,
                            a.document,
                            u.name,
                            (b.composition * a.qty_product) AS qty
                        FROM item_ng a
                        JOIN bom b 
                            ON a.item_fg_id = b.item_fg_id
                        LEFT JOIN users u 
                            ON a.created_by = u.username
                        WHERE b.item_rm_id LIKE '%$item_rm_id%'
                        AND a.trans_date >= '$filter_from'
                        AND a.trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                        AND a.workorder LIKE '%$filter_workorder%'
                        AND a.created_by = 'PRD01'
                        ORDER BY a.trans_date ASC
                    ");

                    $qngp = $this->crud->query("
                        SELECT 
                            b.item_rm_id,
                            a.trans_date,
                            a.workorder,
                            a.document,
                            u.name,
                            (b.composition * a.qty_product) AS qty
                        FROM item_ng a
                        JOIN bom b 
                            ON a.item_fg_id = b.item_fg_id
                        LEFT JOIN users u 
                            ON a.created_by = u.username
                        WHERE b.item_rm_id LIKE '%$item_rm_id%'
                        AND a.trans_date >= '$filter_from'
                        AND a.trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                        AND a.workorder LIKE '%$filter_workorder%'
                        AND a.created_by != 'PRD01'
                        ORDER BY a.trans_date ASC
                    ");

                    $qadj_out = $this->crud->query("
                        select item_rm_id, request_date, request_no, request_name, workorder, qty
                        from transaction_wip
                        where transaction_type='ADJ OUT' and item_rm_id like '%$item_rm_id%' and request_date BETWEEN '$filter_from' and '$filter_to' and workorder like '%$filter_workorder%'
                    ");

                    $qsto_out = $this->crud->query("
                        select item_rm_id, request_date, request_no, request_name, workorder, qty
                        from transaction_wip
                        where transaction_type='STO OUT' and item_rm_id like '%$item_rm_id%' and request_date BETWEEN '$filter_from' and '$filter_to' and workorder like '%$filter_workorder%'
                    ");

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
                            'qty_other' => $supp->qty_other,
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
                            'qty_other' => $mr->qty_other,
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
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
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
                            'qty_other' => 0,
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
                            'qty_other' => 0,
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
                            'qty_other' => 0,
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
                            'qty_other' => 0,
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
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => $transFG->qty,
                            'qty_ng' => 0,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- NG Other ---
                    foreach ($qngo as $ngo) {
                        $all_data[] = [
                            'type' => 'NG',
                            'username' => $ngo->name,
                            'date' => $ngo->trans_date,
                            'wo_no' => $ngo->workorder,
                            'doc_no' => $ngo->document,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => $ngo->qty,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- NG Process ---
                    foreach ($qngp as $ngp) {
                        $all_data[] = [
                            'type' => 'NG',
                            'username' => $ngp->name,
                            'date' => $ngp->trans_date,
                            'wo_no' => $ngp->workorder,
                            'doc_no' => $ngp->document,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => $ngp->qty,
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
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => 0,
                            'qty_adj_out' => $adjout->qty,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // Sort the data by date
                    usort($all_data, function ($a, $b) {
                        return strtotime($a['date']) - strtotime($b['date']);
                    });

                    foreach ($all_data as $data) {
                        $balance = ($begin + $data['qty_supply'] + $data['qty_matreq'] + $data['qty_adj_in']) - ($data['qty_return'] + $data['qty_rfg'] + $data['qty_ng'] + $data['qty_adj_out']);

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
                                <td style="text-align:right;">' . number_format($data['qty_other'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_return'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_rfg'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_ng'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_adj_out'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_sto_out'], 2) . '</td>
                                <td colspan="2" style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';

                        $begin = $balance;
                        $nod++;
                    }
                } else if ($filter_workorder != '' && $filter_items !='') {
                    //SUPPLY
                    $qsupply = $this->crud->query("
                        SELECT b.workorder as wo_no, a.item_rm_id, a.created_date as request_date, a.request_no as doc_no, b.request_name, COALESCE(a.qty, 0) as qty_supply , 0 AS qty_other
                        FROM issued_material_details a 
                        JOIN supply_sheets b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.request_no like '%SH-%' and a.item_rm_id = '$item_rm_id' and b.workorder like '%$filter_workorder%'
                        union all
                        SELECT b.workorder AS wo_no, a.item_rm_id, a.created_date AS request_date, a.request_no AS doc_no, b.request_name, 0 AS qty_supply, a.qty AS qty_other
                        FROM issued_material_details a
                        JOIN supply_sheets b ON a.request_no = b.request_no
                        JOIN item_rm child ON child.id = a.item_rm_id
                        JOIN item_rm parent ON parent.id = '$item_rm_id'
                        WHERE DATE(a.created_date) BETWEEN '$filter_from' AND '$filter_to'
                        AND a.request_no LIKE '%SH-%'
                        AND (child.number LIKE CONCAT('CR-', parent.number) OR child.number LIKE CONCAT('PL-', parent.number))
                        AND b.workorder LIKE '%$filter_workorder%'
                        union all
                        SELECT '-' as wo_no, a.item_rm_id, a.created_date as request_date, a.request_no as doc_no, b.request_name, COALESCE(a.qty, 0) as qty_supply , 0 AS qty_other
                        FROM issued_material_details a 
                        JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.request_no like '%REQ-%' and a.item_rm_id = '$item_rm_id' AND b.type = 'Issued Production'
                        union all
                        select item_rm_id, qty as qty_supply, '-' as wo_no, request_date, request_no as doc_no, request_name, 0 AS qty_other from transaction_rm
                        where transaction_type='BPB' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'
                        union all
                        select item_rm_id, qty as qty_supply, '-' as wo_no, request_date, request_no as doc_no, request_name, 0 AS qty_other from transaction_rm
                        where transaction_type='KANBAN WO' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to' 
                        union all
                        select '-' as wo_no,item_rm_id, created_date as request_date, request_no as doc_no, '-' as request_name, COALESCE(qty, 0) as qty_supply, 0 AS qty_other
                        FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' and `type` like '%WIP%' and item_rm_id = '$item_rm_id'
                        ORDER BY request_date"
                    );
                    //MATREQ
                    $qmatreq = $this->crud->query("
                        SELECT b.workorder AS wo_no, a.item_rm_id, a.created_date AS request_date, a.request_no AS doc_no, b.request_name, a.qty AS qty_matreq, 0 AS qty_other
                        FROM issued_material_details a
                        JOIN supply_requestions b ON a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id
                        WHERE DATE(a.created_date) BETWEEN '$filter_from' AND '$filter_to'
                        AND a.request_no LIKE '%PRQ-%'
                        AND a.item_rm_id = '$item_rm_id'
                        AND b.workorder LIKE '%$filter_workorder%'
                        union all
                        SELECT b.workorder AS wo_no, a.item_rm_id, a.created_date AS request_date, a.request_no AS doc_no, b.request_name, 0 AS qty_matreq, a.qty AS qty_other
                        FROM issued_material_details a
                        JOIN supply_requestions b ON a.request_no = b.request_no
                        JOIN item_rm child ON child.id = a.item_rm_id
                        JOIN item_rm parent ON parent.id = '$item_rm_id'
                        WHERE DATE(a.created_date) BETWEEN '$filter_from' AND '$filter_to'
                        AND a.request_no LIKE '%PRQ-%'
                        AND (
                                child.number LIKE CONCAT('CR-', parent.number)
                                OR child.number LIKE CONCAT('PL-', parent.number)
                            )
                        AND b.workorder LIKE '%$filter_workorder%'
                        ORDER BY request_date
                    ");

                    //ADJ IN
                    $qadj_in = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='ADJ IN' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to' and workorder like '%$filter_workorder%'");

                    //RETURN
                    $qreturn = $this->crud->query("
                        select item_rm_id, request_date, request_no, request_name, qty, null as workorder from bpm where status='1' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'
                        union
                        select item_rm_id, request_date, request_no, request_name, qty, workorder from transaction_rm where transaction_type='BPM' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to' and workorder like '%$filter_workorder%'
                    ");

                    //RFG
                    $receipts = $this->crud->query("
                        select c.item_rm_id, a.wo_no as workorder, a.checksheet_label as request_no, d.name as request_name, b.packing_date as request_date, a.qty*c.composition as qty_rfg
                        FROM scan_item_receipts_fg a
                        JOIN checksheets b ON b.number = a.checksheet_number
                        JOIN bom c on a.item_fg_id = c.item_fg_id
                        LEFT JOIN users d ON a.created_by = d.username
                        WHERE c.item_rm_id = '$item_rm_id' and DATE_FORMAT(b.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' and a.wo_no like '%$filter_workorder%'
                    ");

                    $receiptsNB = $this->crud->query("
                        SELECT c.item_rm_id, f.checksheet_number, f.wo_no, SUM(f.qty * c.composition) AS qty, u.name AS username, f.packing_date AS trans_date
                        FROM new_barcode_fg a 
                        LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                        JOIN users u ON f.created_by = u.username
                        JOIN bom c ON f.item_fg_id = c.item_fg_id
                        WHERE c.item_rm_id LIKE '%$item_rm_id%' AND f.packing_date IS NOT NULL AND f.wo_no IS NOT NULL AND DATE_FORMAT(f.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' AND f.wo_no like '%$filter_workorder%'
                        GROUP BY c.item_rm_id, f.checksheet_number, f.wo_no, u.name, f.packing_date
                    ");

                    $receiptsWIP = $this->crud->query("
                        SELECT c.item_rm_id, a.checksheet_number, a.document_no, a.wo_no, SUM(a.qty * c.composition) AS qty, a.trans_date, u.name AS username, a.document_no AS checksheet_label
                        FROM wip_receipts a
                        INNER JOIN users u ON a.created_by = u.username
                        INNER JOIN bom c ON a.item_fg_id = c.item_fg_id
                        WHERE c.item_rm_id LIKE '%$item_rm_id%' AND a.division = 'MTS' AND a.trans_date IS NOT NULL AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND a.wo_no LIKE '%$filter_workorder%'
                        GROUP BY c.item_rm_id, a.checksheet_number, a.document_no, a.wo_no, a.trans_date, u.name
                    ");

                    $transFgs = $this->crud->query("
                        SELECT c.item_rm_id, a.request_no, a.workorder, SUM(a.qty * c.composition) AS qty, a.request_name, a.request_date
                        FROM transaction_fg a
                        INNER JOIN bom c ON a.item_fg_id = c.item_fg_id
                        WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND c.item_rm_id LIKE '%$item_rm_id%' AND a.request_date IS NOT NULL AND a.request_date BETWEEN '$filter_from' AND '$filter_to' AND a.workorder LIKE '%$filter_workorder%'
                        GROUP BY c.item_rm_id, a.request_no, a.workorder, a.request_name, a.request_date
                    ");

                    //NG
                    $qngo = $this->crud->query("
                        SELECT 
                            b.item_rm_id,
                            a.trans_date,
                            a.workorder,
                            a.document,
                            u.name,
                            (b.composition * a.qty_product) AS qty
                        FROM item_ng a
                        JOIN bom b 
                            ON a.item_fg_id = b.item_fg_id
                        LEFT JOIN users u 
                            ON a.created_by = u.username
                        WHERE b.item_rm_id LIKE '%$item_rm_id%'
                        AND a.trans_date >= '$filter_from'
                        AND a.trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                        AND a.workorder LIKE '%$filter_workorder%'
                        AND a.created_by = 'PRD01'
                        ORDER BY a.trans_date ASC
                    ");

                    $qngp = $this->crud->query("
                        SELECT 
                            b.item_rm_id,
                            a.trans_date,
                            a.workorder,
                            a.document,
                            u.name,
                            (b.composition * a.qty_product) AS qty
                        FROM item_ng a
                        JOIN bom b 
                            ON a.item_fg_id = b.item_fg_id
                        LEFT JOIN users u 
                            ON a.created_by = u.username
                        WHERE b.item_rm_id LIKE '%$item_rm_id%'
                        AND a.trans_date >= '$filter_from'
                        AND a.trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                        AND a.workorder LIKE '%$filter_workorder%'
                        AND a.created_by != 'PRD01'
                        ORDER BY a.trans_date ASC
                    ");

                    //ADJ OUT
                    $qadj_out = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='ADJ OUT' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to' and workorder like '%$filter_workorder%'");

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
                            'qty_other' => $supp->qty_other,
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
                            'qty_other' => $mr->qty_other,
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
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
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
                            'qty_other' => 0,
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
                            'qty_other' => 0,
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
                            'qty_other' => 0,
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
                            'qty_other' => 0,
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
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => $transFG->qty,
                            'qty_ng' => 0,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- NG Other ---
                    foreach ($qngo as $ngo) {
                        $all_data[] = [
                            'type' => 'NG',
                            'username' => $ngo->name,
                            'date' => $ngo->trans_date,
                            'wo_no' => $ngo->workorder,
                            'doc_no' => $ngo->document,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => $ngo->qty,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- NG Process ---
                    foreach ($qngp as $ngp) {
                        $all_data[] = [
                            'type' => 'NG',
                            'username' => $ngp->name,
                            'date' => $ngp->trans_date,
                            'wo_no' => $ngp->workorder,
                            'doc_no' => $ngp->document,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => $ngp->qty,
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
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => 0,
                            'qty_adj_out' => $adjout->qty,
                            'qty_sto_out' => 0,
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
                                <td style="text-align:right;">' . number_format($data['qty_other'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_return'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_rfg'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_ng'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_adj_out'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_sto_out'], 2) . '</td>
                                <td colspan="2" style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';

                        $begin = $balance;
                        $nod++;
                    }
                } else if ($filter_trans_type == '') {
                    //-------------- Awal Query disini----------------------------------//  

                    //SUPPLY
                    $qsupply = $this->crud->query("
                        SELECT b.workorder as wo_no, a.item_rm_id, a.created_date as request_date, a.request_no as doc_no, b.request_name, COALESCE(a.qty, 0) as qty_supply , 0 AS qty_other
                        FROM issued_material_details a 
                        JOIN supply_sheets b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.request_no like '%SH-%' and a.item_rm_id = '$item_rm_id' and b.workorder like '%$filter_workorder%'
                        union all
                        SELECT b.workorder AS wo_no, a.item_rm_id, a.created_date AS request_date, a.request_no AS doc_no, b.request_name, 0 AS qty_supply, a.qty AS qty_other
                        FROM issued_material_details a
                        JOIN supply_sheets b ON a.request_no = b.request_no
                        JOIN item_rm child ON child.id = a.item_rm_id
                        JOIN item_rm parent ON parent.id = '$item_rm_id'
                        WHERE DATE(a.created_date) BETWEEN '$filter_from' AND '$filter_to'
                        AND a.request_no LIKE '%SH-%'
                        AND (child.number LIKE CONCAT('CR-', parent.number) OR child.number LIKE CONCAT('PL-', parent.number))
                        AND b.workorder LIKE '%$filter_workorder%'
                        union all
                        SELECT '-' as wo_no, a.item_rm_id, a.created_date as request_date, a.request_no as doc_no, b.request_name, COALESCE(a.qty, 0) as qty_supply , 0 AS qty_other
                        FROM issued_material_details a 
                        JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.request_no like '%REQ-%' and a.item_rm_id = '$item_rm_id' AND b.type = 'Issued Production'
                        union all
                        select item_rm_id, qty as qty_supply, '-' as wo_no, request_date, request_no as doc_no, request_name, 0 AS qty_other from transaction_rm
                        where transaction_type='BPB' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'
                        union all
                        select item_rm_id, qty as qty_supply, '-' as wo_no, request_date, request_no as doc_no, request_name, 0 AS qty_other from transaction_rm
                        where transaction_type='KANBAN WO' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to' 
                        union all
                        select '-' as wo_no,item_rm_id, created_date as request_date, request_no as doc_no, '-' as request_name, COALESCE(qty, 0) as qty_supply, 0 AS qty_other
                        FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' and `type` like '%WIP%' and item_rm_id = '$item_rm_id'
                        ORDER BY request_date"
                    );
                    //MATREQ
                    $qmatreq = $this->crud->query("
                        SELECT b.workorder AS wo_no, a.item_rm_id, a.created_date AS request_date, a.request_no AS doc_no, b.request_name, a.qty AS qty_matreq, 0 AS qty_other
                        FROM issued_material_details a
                        JOIN supply_requestions b ON a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id
                        WHERE DATE(a.created_date) BETWEEN '$filter_from' AND '$filter_to'
                        AND a.request_no LIKE '%PRQ-%'
                        AND a.item_rm_id = '$item_rm_id'
                        AND b.workorder LIKE '%$filter_workorder%'
                        union all
                        SELECT b.workorder AS wo_no, a.item_rm_id, a.created_date AS request_date, a.request_no AS doc_no, b.request_name, 0 AS qty_matreq, a.qty AS qty_other
                        FROM issued_material_details a
                        JOIN supply_requestions b ON a.request_no = b.request_no
                        JOIN item_rm child ON child.id = a.item_rm_id
                        JOIN item_rm parent ON parent.id = '$item_rm_id'
                        WHERE DATE(a.created_date) BETWEEN '$filter_from' AND '$filter_to'
                        AND a.request_no LIKE '%PRQ-%'
                        AND (
                                child.number LIKE CONCAT('CR-', parent.number)
                                OR child.number LIKE CONCAT('PL-', parent.number)
                            )
                        AND b.workorder LIKE '%$filter_workorder%'
                        ORDER BY request_date
                    ");
                    //ADJ IN
                    $qadj_in = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='ADJ IN' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to' and workorder like '%$filter_workorder%'");

                    //RETURN
                    $qreturn = $this->crud->query("
                        select item_rm_id, request_date, request_no, request_name, qty, null as workorder from bpm where status='1' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'
                        union
                        select item_rm_id, request_date, request_no, request_name, qty, workorder from transaction_rm where transaction_type='BPM' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to' and workorder like '%$filter_workorder%'
                    ");

                    //RFG
                    $receipts = $this->crud->query("
                        select c.item_rm_id, a.wo_no as workorder, a.checksheet_label as request_no, d.name as request_name, b.packing_date as request_date, a.qty*c.composition as qty_rfg
                        FROM scan_item_receipts_fg a
                        JOIN checksheets b ON b.number = a.checksheet_number
                        JOIN bom c on a.item_fg_id = c.item_fg_id
                        LEFT JOIN users d ON a.created_by = d.username
                        WHERE c.item_rm_id = '$item_rm_id' and DATE_FORMAT(b.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' and a.wo_no like '%$filter_workorder%'
                    ");

                    $receiptsNB = $this->crud->query("
                        SELECT c.item_rm_id, f.checksheet_number, f.wo_no, SUM(f.qty * c.composition) AS qty, u.name AS username, f.packing_date AS trans_date
                        FROM new_barcode_fg a 
                        LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                        JOIN users u ON f.created_by = u.username
                        JOIN bom c ON f.item_fg_id = c.item_fg_id
                        WHERE c.item_rm_id LIKE '%$item_rm_id%' AND f.packing_date IS NOT NULL AND f.wo_no IS NOT NULL AND DATE_FORMAT(f.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' AND f.wo_no like '%$filter_workorder%'
                        GROUP BY c.item_rm_id, f.checksheet_number, f.wo_no, u.name, f.packing_date
                    ");

                    $receiptsWIP = $this->crud->query("
                        SELECT c.item_rm_id, a.checksheet_number, a.document_no, a.wo_no, SUM(a.qty * c.composition) AS qty, a.trans_date, u.name AS username, a.document_no AS checksheet_label
                        FROM wip_receipts a
                        INNER JOIN users u ON a.created_by = u.username
                        INNER JOIN bom c ON a.item_fg_id = c.item_fg_id
                        WHERE c.item_rm_id LIKE '%$item_rm_id%' AND a.division = 'MTS' AND a.trans_date IS NOT NULL AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND a.wo_no LIKE '%$filter_workorder%'
                        GROUP BY c.item_rm_id, a.checksheet_number, a.document_no, a.wo_no, a.trans_date, u.name
                    ");

                    $transFgs = $this->crud->query("
                        SELECT c.item_rm_id, a.request_no, a.workorder, SUM(a.qty * c.composition) AS qty, a.request_name, a.request_date
                        FROM transaction_fg a
                        INNER JOIN bom c ON a.item_fg_id = c.item_fg_id
                        WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND c.item_rm_id LIKE '%$item_rm_id%' AND a.request_date IS NOT NULL AND a.request_date BETWEEN '$filter_from' AND '$filter_to' AND a.workorder LIKE '%$filter_workorder%'
                        GROUP BY c.item_rm_id, a.request_no, a.workorder, a.request_name, a.request_date
                    ");

                    //NG
                    $qngo = $this->crud->query("
                        SELECT 
                            b.item_rm_id,
                            a.trans_date,
                            a.workorder,
                            a.document,
                            u.name,
                            (b.composition * a.qty_product) AS qty
                        FROM item_ng a
                        JOIN bom b 
                            ON a.item_fg_id = b.item_fg_id
                        LEFT JOIN users u 
                            ON a.created_by = u.username
                        WHERE b.item_rm_id LIKE '%$item_rm_id%'
                        AND a.trans_date >= '$filter_from'
                        AND a.trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                        AND a.workorder LIKE '%$filter_workorder%'
                        AND a.created_by = 'PRD01'
                        ORDER BY a.trans_date ASC
                    ");

                    $qngp = $this->crud->query("
                        SELECT 
                            b.item_rm_id,
                            a.trans_date,
                            a.workorder,
                            a.document,
                            u.name,
                            (b.composition * a.qty_product) AS qty
                        FROM item_ng a
                        JOIN bom b 
                            ON a.item_fg_id = b.item_fg_id
                        LEFT JOIN users u 
                            ON a.created_by = u.username
                        WHERE b.item_rm_id LIKE '%$item_rm_id%'
                        AND a.trans_date >= '$filter_from'
                        AND a.trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                        AND a.workorder LIKE '%$filter_workorder%'
                        AND a.created_by != 'PRD01'
                        ORDER BY a.trans_date ASC
                    ");

                    //ADJ OUT
                    $qadj_out = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='ADJ OUT' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to' and workorder like '%$filter_workorder%'");

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
                            'qty_other' => $supp->qty_other,
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
                            'wo_no' => $mr->wo_no,
                            'doc_no' => $mr->doc_no,
                            'qty_supply' => 0,
                            'qty_matreq' => $mr->qty_matreq,
                            'qty_adj_in' => 0,
                            'qty_other' => $mr->qty_other,
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
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
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
                            'qty_other' => 0,
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
                            'qty_other' => 0,
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
                            'qty_other' => 0,
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
                            'qty_other' => 0,
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
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => $transFG->qty,
                            'qty_ng' => 0,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- NG Other ---
                    foreach ($qngo as $ngo) {
                        $all_data[] = [
                            'type' => 'NG',
                            'username' => $ngo->name,
                            'date' => $ngo->trans_date,
                            'wo_no' => $ngo->workorder,
                            'doc_no' => $ngo->document,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => $ngo->qty,
                            'qty_adj_out' => 0,
                            'qty_sto_out' => 0,
                        ];
                    }

                    // --- NG Process ---
                    foreach ($qngp as $ngp) {
                        $all_data[] = [
                            'type' => 'NG',
                            'username' => $ngp->name,
                            'date' => $ngp->trans_date,
                            'wo_no' => $ngp->workorder,
                            'doc_no' => $ngp->document,
                            'qty_supply' => 0,
                            'qty_matreq' => 0,
                            'qty_adj_in' => 0,
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => $ngp->qty,
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
                            'qty_other' => 0,
                            'qty_sto_in' => 0,
                            'qty_return' => 0,
                            'qty_rfg' => 0,
                            'qty_ng' => 0,
                            'qty_adj_out' => $adjout->qty,
                            'qty_sto_out' => 0,
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
                                <td style="text-align:right;">' . number_format($data['qty_other'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_return'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_rfg'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_ng'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_adj_out'], 2) . '</td>
                                <td style="text-align:right;">' . number_format($data['qty_sto_out'], 2) . '</td>
                                <td colspan="2" style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';

                        $begin = $balance;
                        $nod++;
                    }
                } else if ($filter_trans_type == 'SUPPLY') {
                    //SUPPLY
                    $qsupply = $this->crud->query("
                        SELECT b.workorder as wo_no, a.item_rm_id, a.created_date as request_date, a.request_no as doc_no, b.request_name, COALESCE(a.qty, 0) as qty_supply , 0 AS qty_other
                        FROM issued_material_details a 
                        JOIN supply_sheets b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.request_no like '%SH-%' and a.item_rm_id = '$item_rm_id' and b.workorder like '%$filter_workorder%'
                        union all
                        SELECT b.workorder AS wo_no, a.item_rm_id, a.created_date AS request_date, a.request_no AS doc_no, b.request_name, 0 AS qty_supply, a.qty AS qty_other
                        FROM issued_material_details a
                        JOIN supply_sheets b ON a.request_no = b.request_no
                        JOIN item_rm child ON child.id = a.item_rm_id
                        JOIN item_rm parent ON parent.id = '$item_rm_id'
                        WHERE DATE(a.created_date) BETWEEN '$filter_from' AND '$filter_to'
                        AND a.request_no LIKE '%SH-%'
                        AND (child.number LIKE CONCAT('CR-', parent.number) OR child.number LIKE CONCAT('PL-', parent.number))
                        AND b.workorder LIKE '%$filter_workorder%'
                        union all
                        SELECT '-' as wo_no, a.item_rm_id, a.created_date as request_date, a.request_no as doc_no, b.request_name, COALESCE(a.qty, 0) as qty_supply , 0 AS qty_other
                        FROM issued_material_details a 
                        JOIN supply_materials b ON a.request_no = b.request_no and a.item_rm_id = b.item_rm_id
                        WHERE DATE_FORMAT(a.created_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and a.request_no like '%REQ-%' and a.item_rm_id = '$item_rm_id' AND b.type = 'Issued Production'
                        union all
                        select item_rm_id, qty as qty_supply, '-' as wo_no, request_date, request_no as doc_no, request_name, 0 AS qty_other from transaction_rm
                        where transaction_type='BPB' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'
                        union all
                        select item_rm_id, qty as qty_supply, '-' as wo_no, request_date, request_no as doc_no, request_name, 0 AS qty_other from transaction_rm
                        where transaction_type='KANBAN WO' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to' 
                        union all
                        select '-' as wo_no,item_rm_id, created_date as request_date, request_no as doc_no, '-' as request_name, COALESCE(qty, 0) as qty_supply, 0 AS qty_other
                        FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' and `type` like '%WIP%' and item_rm_id = '$item_rm_id'
                        ORDER BY request_date"
                    );

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
                                <td style="text-align:right;">' . number_format($supply->qty_other, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td colspan="2" style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin += $supply->qty_supply;
                        $nod++;
                    }
                } else if ($filter_trans_type == 'MATREQ') {
                    //MATREQ
                    $qmatreq = $this->crud->query("
                        SELECT b.workorder AS wo_no, a.item_rm_id, a.created_date AS request_date, a.request_no AS doc_no, b.request_name, a.qty AS qty_matreq, 0 AS qty_other
                        FROM issued_material_details a
                        JOIN supply_requestions b ON a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id
                        WHERE DATE(a.created_date) BETWEEN '$filter_from' AND '$filter_to'
                        AND a.request_no LIKE '%PRQ-%'
                        AND a.item_rm_id = '$item_rm_id'
                        AND b.workorder LIKE '%$filter_workorder%'
                        union all
                        SELECT b.workorder AS wo_no, a.item_rm_id, a.created_date AS request_date, a.request_no AS doc_no, b.request_name, 0 AS qty_matreq, a.qty AS qty_other
                        FROM issued_material_details a
                        JOIN supply_requestions b ON a.request_no = b.request_no
                        JOIN item_rm child ON child.id = a.item_rm_id
                        JOIN item_rm parent ON parent.id = '$item_rm_id'
                        WHERE DATE(a.created_date) BETWEEN '$filter_from' AND '$filter_to'
                        AND a.request_no LIKE '%PRQ-%'
                        AND (
                                child.number LIKE CONCAT('CR-', parent.number)
                                OR child.number LIKE CONCAT('PL-', parent.number)
                            )
                        AND b.workorder LIKE '%$filter_workorder%'
                        ORDER BY request_date
                    ");

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
                                <td style="text-align:right;">' . number_format($matreq->qty_matreq, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format($matreq->qty_other, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td colspan="2" style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin += $matreq->qty_matreq;
                        $nod++;
                    }
                } else if ($filter_trans_type == 'ADJ IN') {
                    //ADJ IN
                    $qadj_in = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='ADJ IN' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to' and workorder like '%$filter_workorder%'");
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
                                <td colspan="2" style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin += $adjin->qty;
                        $nod++;
                    }
                
                } else if ($filter_trans_type == 'RETURN') {
                    //RETURN
                    $qreturn = $this->crud->query("
                        select item_rm_id, request_date, request_no, request_name, qty, null as workorder from bpm where status='1' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'
                        union
                        select item_rm_id, request_date, request_no, request_name, qty, workorder from transaction_rm where transaction_type='BPM' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to' and workorder like '%$filter_workorder%'
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
                                <td style="text-align:right;">' . number_format($return->qty, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td colspan="2" style="text-align:right;">' . number_format($balance, 2) . '</td>
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
                        WHERE c.item_rm_id = '$item_rm_id' and DATE_FORMAT(b.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' and a.wo_no like '%$filter_workorder%'
                    ");

                    $receiptsNB = $this->crud->query("
                        SELECT c.item_rm_id, f.checksheet_number, f.wo_no, SUM(f.qty * c.composition) AS qty, u.name AS username, f.packing_date AS trans_date
                        FROM new_barcode_fg a 
                        LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                        JOIN users u ON f.created_by = u.username
                        JOIN bom c ON f.item_fg_id = c.item_fg_id
                        WHERE c.item_rm_id LIKE '%$item_rm_id%' AND f.packing_date IS NOT NULL AND f.wo_no IS NOT NULL AND DATE_FORMAT(f.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' AND f.wo_no like '%$filter_workorder%'
                        GROUP BY c.item_rm_id, f.checksheet_number, f.wo_no, u.name, f.packing_date
                    ");

                    $receiptsWIP = $this->crud->query("
                        SELECT c.item_rm_id, a.checksheet_number, a.document_no, a.wo_no, SUM(a.qty * c.composition) AS qty, a.trans_date, u.name AS username, a.document_no AS checksheet_label
                        FROM wip_receipts a
                        INNER JOIN users u ON a.created_by = u.username
                        INNER JOIN bom c ON a.item_fg_id = c.item_fg_id
                        WHERE c.item_rm_id LIKE '%$item_rm_id%' AND a.division = 'MTS' AND a.trans_date IS NOT NULL AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND a.wo_no LIKE '%$filter_workorder%'
                        GROUP BY c.item_rm_id, a.checksheet_number, a.document_no, a.wo_no, a.trans_date, u.name
                    ");

                    $transFgs = $this->crud->query("
                        SELECT c.item_rm_id, a.request_no, a.workorder, SUM(a.qty * c.composition) AS qty, a.request_name, a.request_date
                        FROM transaction_fg a
                        INNER JOIN bom c ON a.item_fg_id = c.item_fg_id
                        WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG' AND c.item_rm_id LIKE '%$item_rm_id%' AND a.request_date IS NOT NULL AND a.request_date BETWEEN '$filter_from' AND '$filter_to' AND a.workorder LIKE '%$filter_workorder%'
                        GROUP BY c.item_rm_id, a.request_no, a.workorder, a.request_name, a.request_date
                    ");
                    
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
                                <td style="text-align:right;">' . number_format($data['qty_rfg'], 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td colspan="2" style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin -= $data['qty_rfg'];
                        $nod++;
                    }
                } else if ($filter_trans_type == 'NG OTHER') {
                    //NG
                    $qngo = $this->crud->query("
                        SELECT 
                            b.item_rm_id,
                            a.trans_date,
                            a.workorder,
                            a.document,
                            u.name,
                            (b.composition * a.qty_product) AS qty
                        FROM item_ng a
                        JOIN bom b 
                            ON a.item_fg_id = b.item_fg_id
                        LEFT JOIN users u 
                            ON a.created_by = u.username
                        WHERE b.item_rm_id LIKE '%$item_rm_id%'
                        AND a.trans_date >= '$filter_from'
                        AND a.trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                        AND a.workorder LIKE '%$filter_workorder%'
                        AND a.created_by = 'PRD01'
                        ORDER BY a.trans_date ASC
                    ");

                    foreach ($qngo as $ng) {
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
                                <td style="text-align:right;">' . number_format($ng->qty, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td style="text-align:right;">' . number_format(0, 2) . '</td>
                                <td colspan="2" style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin -= $ng->qty;
                        $nod++;
                    }
                } else if ($filter_trans_type == 'NG PROCESS') {
                    //NG
                    $qngp = $this->crud->query("
                        SELECT 
                            b.item_rm_id,
                            a.trans_date,
                            a.workorder,
                            a.document,
                            u.name,
                            (b.composition * a.qty_product) AS qty
                        FROM item_ng a
                        JOIN bom b 
                            ON a.item_fg_id = b.item_fg_id
                        LEFT JOIN users u 
                            ON a.created_by = u.username
                        WHERE b.item_rm_id LIKE '%$item_rm_id%'
                        AND a.trans_date >= '$filter_from'
                        AND a.trans_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                        AND a.workorder LIKE '%$filter_workorder%'
                        AND a.created_by != 'PRD01'
                        ORDER BY a.trans_date ASC
                    ");

                    foreach ($qngp as $ng) {
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
                                <td colspan="2" style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin -= $ng->qty;
                        $nod++;
                    }
                } else if ($filter_trans_type == 'ADJ OUT') {
                    //ADJ OUT
                    $qadj_out = $this->crud->query("select item_rm_id, request_date, request_no, request_name, workorder, qty from transaction_wip where transaction_type='ADJ OUT' and item_rm_id = '$item_rm_id' and request_date BETWEEN '$filter_from' and '$filter_to'  and workorder like '%$filter_workorder%'");
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
                                <td colspan="2" style="text-align:right;">' . number_format($balance, 2) . '</td>
                            </tr>';
                        $begin -= $adjout->qty;
                        $nod++;
                    }
                } 
            }
        }

        // $html .= '<tr>
        //     <td colspan="13" style="text-align:right;"><b>GRAND TOTAL</b></td>
        //     <td style="text-align:right;"><b>' . number_format($totalBeginStock, 2) . '</b></td>
        //     <td style="text-align:right;"><b>' . number_format($totalSupply, 2) . '</b></td>
        //     <td style="text-align:right;"><b>' . number_format($totalMatreq, 2) . '</b></td>
        //     <td style="text-align:right;"><b>' . number_format($totalAdjIn, 2) . '</b></td>
        //     <td style="text-align:right;"><b>' . number_format($totalReturn, 2) . '</b></td>
        //     <td style="text-align:right;"><b>' . number_format($totalRfg, 2) . '</b></td>
        //     <td style="text-align:right;"><b>' . number_format($totalNg, 2) . '</b></td>
        //     <td style="text-align:right;"><b>' . number_format($totalAdjOut, 2) . '</b></td>
        //     <td style="text-align:right;"><b>' . number_format($totalEndingBalanceTotal, 2) . '</b></td>
        //     <td style="text-align:right;"><b>' . number_format($totalEndingStock, 2) . '</b></td>
        // </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }


    public function print_detail($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=inventory_wip_$format.xls");
        }
        $filter_shift = $this->input->get("filter_shift");
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division_input = $this->input->get('filter_division');
        $filter_trans_type = $this->input->get('filter_trans_type');
        $filter_workorder = $this->input->get('filter_workorder');

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $display_title = ($filter_display == "DETAIL") ? '(DETAIL)' : '(RECAP)';

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Division
        $getDivision = $this->db->from('divisions')->where('number', $filter_division_input)->get()->row();
        $filter_division = !empty($getDivision) ? $getDivision->id : $filter_division_input;

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

        $html = '<html><head><title>Inventory Report</title></head>';
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
                <h3 style="margin:0;">INVENTORY FG STANDARD AND ACTUAL <i>' . $display_title . '</i> </h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';

        $html .= '<table id="customers" border="1" style="font-size: 11px;">
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
