<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

// Memastikan namespace dikenali
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property CI_Output $output
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


    // ----- UPLOAD DATA -----
    public function upload_excel_old()
    {
        header('Content-Type: application/json');

        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';

        try {
            $target = basename($_FILES['file_upload']['name']);

            if (!move_uploaded_file($_FILES['file_upload']['tmp_name'], $target)) {
                echo json_encode(["title" => "Error", "message" => "Failed to upload file.", "theme" => "error"]);
                return;
            }

            chmod($_FILES['file_upload']['name'], 0777);
            $file = $_FILES['file_upload']['name'];
            $data = new Spreadsheet_Excel_Reader($file, false);
            $total_row = $data->rowcount($sheet_index = 0);

            for ($i = 3; $i <= $total_row; $i++) {
                // [^\x20-\x7E] menghapus semua karakter UTF-8 
                $datas[] = [
                    'part_no'     => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 2)),
                    'cutoff_date' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 3)),
                    'uom'         => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 4)),
                    'currency'    => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 5)),
                    'qty'         => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 6)),
                    'price'       => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 7)),
                ];
            }

            $response = [
                'total' => count($datas),
                'data'  => $datas
            ];
            
            echo json_encode($response);

            unlink($_FILES['file_upload']['name']);

        } catch (Exception $e) {
            // Handle upload errors gracefully
            http_response_code(500); // Set HTTP status code for server error
            echo json_encode(["title" => "Error", "message" => "Error upload file! " . $e->getMessage(), "theme" => "error"]);
        } finally {
            // Ensure the temporary file is deleted even if an error occurs
            if (isset($target) && file_exists($target)) {
                unlink($target);
            }
        }
    }

    public function upload()
    {
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/json');
        
        // Load PHPSpreadsheet autoloader
        require_once 'assets/vendors/phpspreadsheet/vendor/autoload.php';

        try {
            if (!isset($_FILES['file_upload']) || $_FILES['file_upload']['error'] !== UPLOAD_ERR_OK) {
                $msg = "File not found or an error occurred while uploading.";
                echo json_encode(["title" => "Error", "message" => $msg, "theme" => "error"]);
                return;
            }

            $tmpPath = $_FILES['file_upload']['tmp_name'];

            // Membaca file menggunakan IOFactory
            $spreadsheet = IOFactory::load($tmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            
            $datas = [];
            for ($i = 3; $i <= $highestRow; $i++) {
                // Menggunakan PhpSpreadsheet agar simbol "Ø" tidak hilang
                $partNo   = $sheet->getCell("B$i")->getValue();
                $cutoff   = $sheet->getCell("C$i")->getValue();
                $uom      = $sheet->getCell("D$i")->getValue();
                $currency = $sheet->getCell("E$i")->getValue();
                $qty      = $sheet->getCell("F$i")->getOldCalculatedValue() ?? $sheet->getCell("F$i")->getValue();
                $price    = $sheet->getCell("G$i")->getOldCalculatedValue() ?? $sheet->getCell("G$i")->getValue();

                // Validasi Date Format
                try {
                    if (is_numeric($cutoff)) {
                        $cutoffDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cutoff)->format('Y-m-d');
                    
                    } elseif (!empty($cutoff) && strtotime($cutoff)) {
                        // Jika ternyata formatnya string tanggal (misal: "2024-01-01")
                        $cutoffDate = date('Y-m-d', strtotime($cutoff));
                    
                    } else {
                        $cutoffDate = date('Y-01-01'); 
                    }
                } catch (Exception $e) {
                    $cutoffDate = date('Y-01-01');
                }

                $datas[] = [
                    'part_no'     => (string)$partNo,
                    'cutoff_date' => $cutoffDate,
                    'uom'         => (string)$uom,
                    'currency'    => (string)$currency,
                    'qty'         => (float)$qty,
                    'price'       => (float)$price,
                ];
            }

            echo json_encode([
                "total" => count($datas),
                "data"  => $datas
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "title"   => "Error",
                "message" => "Failed to read Excel! " . $e->getMessage(),
                "theme"   => "error"
            ]);
        }
    }

    // Insert process from upload
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            $user_session = $this->session->userdata('username') ?? $this->session->username; 

            // Check part_no
            $item_rm = $this->db->select('id, number, name')->from('item_rm')->where('number', $data['part_no'])->get()->row();

            if (empty($data['part_no']) && empty($data['cutoff_date']) && empty($data['uom']) && empty($data['qty']) && empty($data['price'])) {
                echo json_encode(array("title" => "Required", "message" => "All Data is Required!", "theme" => "error"));
                return;
            }
            if (empty($data['part_no'])) {
                echo json_encode(array("title" => "Required", "message" => "Part No is required!", "theme" => "error"));
                return;
            }
            if (empty($data['qty'])) {
                echo json_encode(array("title" => "Required", "message" => "Qty is required!", "theme" => "error"));
                return;
            }
            if (empty($data['price'])) {
                echo json_encode(array("title" => "Required", "message" => "Price is required!", "theme" => "error"));
                return;
            }
            if (empty($item_rm)) {
                echo json_encode(array("title" => "Not Found", "message" => "Item " . $data['part_no'] . " is Not Found in Master Item!", "theme" => "error"));
                return;
            }

            // Prepare Data
            $cutoff_date = date("Y-m-d", strtotime($data['cutoff_date']));
            $dataFinal = [
                'item_rm_id'  => $item_rm->id,
                'part_no'     => $data['part_no'],
                'cutoff_date' => $cutoff_date,
                'uom'         => $data['uom'],
                'currency'    => $data['currency'] ?? 'IDR',
                'qty'         => $data['qty'],
                'price'       => $data['price'],
                'upload'      => 'YES',
                'upload_date' => date('Y-m-d'),
            ];

            // Check existing
            $existing = $this->db->get_where('inventory_rm_actual', [
                'item_rm_id'  => $item_rm->id,
                'cutoff_date' => $cutoff_date,
                'qty'         => $data['qty'],
                'price'       => $data['price'],
            ])->row();

            if ($existing) {
                // UPDATE jika sudah ada
                $dataUpdate = [
                    'part_no'      => $data['part_no'],
                    'uom'          => $data['uom'],
                    'currency'     => $data['currency'] ?? 'IDR',
                    'qty'          => $data['qty'],
                    'price'        => $data['price'],
                    'updated_by'   => $user_session,
                    'updated_date' => date('Y-m-d H:i:s'),
                ];

                $this->db->where('id', $existing->id);
                $update = $this->db->update('inventory_rm_actual', $dataUpdate);
                
                if ($update) {
                    echo json_encode(array("title" => "Updated", "message" => "Data updated successfully!", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => "Failed to update data!", "theme" => "error"));
                }
            } else {
                // CREATE jika belum ada
                $send = $this->crud->create('inventory_rm_actual', $dataFinal);
                echo $send;
            }
        }
    }
    
    public function uploadclearFailed()
    {
        @unlink('failed/inventory_rm_standard_actual.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/inventory_rm_standard_actual.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/inventory_rm_standard_actual.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function get_upload_list()
    {
        $page = $this->input->post('page') ?? 1;
        $rows = $this->input->post('rows') ?? 10;
        $offset = ($page - 1) * $rows;

        // Filter hanya yang berasal dari upload
        $this->db->from('inventory_rm_actual');
        $this->db->where('upload', 'YES');
        $this->db->where('deleted', 0);

        // Hitung total untuk pagination
        $total = $this->db->count_all_results('', FALSE);

        // Ambil data dengan limit
        $this->db->order_by('upload_date', 'DESC');
        $this->db->limit($rows, $offset);
        $data = $this->db->get()->result();

        $result = [
            "total" => $total,
            "rows" => $data
        ];

        echo json_encode($result);
    }
    // ----- END UPLOAD FUNCTIONS ----- 

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

    private function nominalFormat($value)
    {
        $formatted = number_format($value, 2);
        if ($value < 0) {
            $formatted = '<span style="color: red;">(' . number_format(abs($value), 2) . ')</span>';
        }
        return $formatted;
    }

    // -------------- PRINT RECAP (HISTORY TRANSACTION INVENTORY RM) => LSB -------------
    public function print($option = "")
    {
        if (!$this->db->table_exists('inventory_rm_actual')) {
            echo "<pre> Database Error: Tabel Inventory RM Actual not found! Please contact admin.</pre>";
            return false;
        }

        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=inventory_rm_standard_actual_$format.xls");
        }
        
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get('filter_division');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_item_category = $this->input->get('filter_item_category');

        $display_title = ($filter_display == "DETAIL") ? '(DETAIL)' : '(RECAP)';

        // Config Logo & Name
        $config = $this->db->get('config')->row();

        //------------------------------------ OPTIMIZED QUERY ----------------------------------//
        $query = "SELECT
                a.id, 
                a.number, 
                a.name, 
                a.division, 
                a.uom,
                b.name as prodfam, 
                l.name as sub_prodfam, 
                c.name as category_name, 
                std_price.price AS standard_price, 
                std_price.currency AS standard_currency,
                item_spec.specification,
                
                -- BEGIN STOCK
                COALESCE(x.begin_stock, 0) AS begin_stock,

                -- QTY IN DETAILS
                COALESCE(d.qty_scan_in, 0) as receipt_qty, 
                (COALESCE(i.qty, 0) + COALESCE(o.qty_bpm_scan, 0)) as bpm_qty, 
                COALESCE(k.qty, 0) as adj_in_qty, 

                -- QTY OUT DETAILS
                COALESCE(f2.qty, 0) as qty_supply_sheet,
                COALESCE(f5.qty, 0) as qty_mat_request,
                (COALESCE(j.qty, 0) + COALESCE(f4.qty, 0) + COALESCE(f3.qty, 0)) as qty_kanban,
                COALESCE(f6.qty, 0) as qty_kanban_sj,
                COALESCE(f7.qty, 0) as qty_kanban_sp,
                COALESCE(n.qty, 0) as bpb_qty, 
                COALESCE(m.qty, 0) as adj_out_qty,

                -- ACTUAL FROM UPLOAD
                COALESCE(actual.price, 0) as actual_price, 
                COALESCE(actual.qty, 0) as actual_qty,

                -- ACTUAL IN AMOUNT (Uang Riil dari PO)
                COALESCE(act_in.total_actual_amt_in, 0) as total_actual_amt_in,                
                d.max_receipt_date
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN item_family_subs l ON a.item_sub_family_id = l.id

            -- get actual from upload
            LEFT JOIN inventory_rm_actual actual ON (actual.part_no = a.number OR actual.item_rm_id = a.id)
            
            -- get specification 
            LEFT JOIN (
                SELECT a.item_rm_id, f.specification, c.size 
                FROM purchase_order_receipts a
                LEFT JOIN item_rm c ON a.item_rm_id = c.id
                LEFT JOIN purchase_orders f ON a.po_no = f.po_no AND a.item_rm_id = f.item_rm_id and (a.item_rm_id = f.item_rm_id or a.specification = f.specification)
            ) item_spec ON item_spec.item_rm_id = a.id
            
            -- Standard Price Lookup
            LEFT JOIN (SELECT item_rm_id, currency, price FROM standard_price_rm WHERE '$filter_from' >= `start_date` AND '$filter_to' <= `end_date`) std_price ON a.id = std_price.item_rm_id

            -- Actual Amount In (Sum Qty * Price PO)
            LEFT JOIN (
                SELECT pr.item_rm_id, SUM(sr.qty * po.price) as total_actual_amt_in
                FROM purchase_order_receipts pr
                JOIN scan_item_receipts sr ON pr.receipt_id = sr.receipt_id
                JOIN purchase_orders po ON pr.po_no = po.po_no AND pr.item_rm_id = po.item_rm_id
                WHERE pr.receipt_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY pr.item_rm_id
            ) act_in ON a.id = act_in.item_rm_id

            -- Qty In Lookup
            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in, MAX(b.receipt_date) as max_receipt_date FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id

            -- Begin Stock Lookup
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

            -- BPM
            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type,SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) AND a.transaction_type = 'BPM'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) i ON a.id = i.item_rm_id

            -- BPM SCAN
            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_bpm_scan
                FROM scan_item_bpm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                GROUP BY a.item_rm_id) o ON a.id = o.item_rm_id
            
            -- ADJ IN 
            LEFT JOIN (
                SELECT a.item_rm_id, a.transaction_kind, a.transaction_type, SUM(a.qty) AS qty
                FROM transaction_rm a
                JOIN item_rm b ON a.item_rm_id = b.id
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) AND a.transaction_type = 'ADJ IN STO'
                GROUP BY a.item_rm_id, a.transaction_kind
            ) k ON a.id = k.item_rm_id

            -- ISSUED MATERIAL - Left Join (f2-f7, i, j, k, m, n)
            LEFT JOIN (SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) AND request_no LIKE '%SH-%' GROUP BY item_rm_id) f2 ON a.id = f2.item_rm_id
            
            LEFT JOIN (SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) AND request_no LIKE '%PRQ-%' GROUP BY item_rm_id) f5 ON a.id = f5.item_rm_id
            
            LEFT JOIN (SELECT item_rm_id, SUM(qty) as qty FROM issued_material_details WHERE created_date >= '$filter_from' AND created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY) AND `type` LIKE '%WIP%' GROUP BY item_rm_id) f4 ON a.id = f4.item_rm_id
            
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

            WHERE c.id LIKE '%$filter_item_category%' 
            AND b.number LIKE '%$filter_item_family%' 
            AND a.id LIKE '%$filter_items%' 
            AND a.division LIKE '%$filter_division%' 
            GROUP BY a.id
            ORDER BY c.name DESC, b.name DESC, a.number";

        $records = $this->crud->query($query);

        //------------------------------------ HTML OUTPUT ----------------------------------//
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
                <h3 style="margin:0;">INVENTORY RM STANDARD AND ACTUAL <i>' . $display_title . '</i> </h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';

        $html .= '<table id="customers" border="1" style="font-size: 11px;">
                <tr style="background-color: #eee;">
                    <th rowspan="5" width="20">No</th>
                    <th rowspan="5">Product No</th>
                    <th rowspan="5">Product Name</th>
                    <th rowspan="5">Uom</th>
                    <th rowspan="5">Division</th>
                    <th rowspan="5">Category</th>
                    <th rowspan="5">Product Family</th>
                    <th rowspan="5">Specification</th>

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
        $html .= '
                    <th colspan="5">PURCHASE</th>
                    <th colspan="5">BPM</th>
                    <th colspan="5">ADJ STO</th>

                    <th colspan="5">SUPPLY SHEET</th>
                    <th colspan="5">MATERIAL REQUEST</th>
                    <th colspan="5">KANBAN PRD</th>
                    <th colspan="5">KANBAN SUBCONT JASA</th>
                    <th colspan="5">KANBAN SUBCONT PRODUCT</th>
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
        $total_b_qty = 0;
        $total_i_qty = 0;
        $total_o_qty = 0;
        $total_e_qty = 0;

        $total_receipt_qty      = 0;
        $total_bpm_qty          = 0;
        $total_adj_in_qty       = 0;
        $total_qty_supply_sheet = 0;
        $total_qty_mat_request  = 0;
        $total_qty_kanban       = 0;
        $total_bpb_qty          = 0;
        $total_adj_out_qty      = 0;
        $total_qty_kanban_sj    = 0;
        $total_qty_kanban_sp    = 0;

        $total_std_b = 0;
        $total_std_i = 0;
        $total_std_o = 0;
        $total_std_e = 0;
        $total_act_b = 0;
        $total_act_i = 0;
        $total_act_o = 0;
        $total_act_e = 0;

        $total_std_purchase  = 0;
        $total_std_bpm       = 0;
        $total_std_adjin     = 0;
        $total_std_supply    = 0;
        $total_std_req       = 0;
        $total_std_kanban    = 0;
        $total_std_bpb       = 0;
        $total_std_adjout    = 0;
        $total_std_kanban_sj = 0;
        $total_std_kanban_sp = 0;

        $total_act_purchase  = 0;
        $total_act_bpm       = 0;
        $total_act_adjin     = 0;
        $total_act_supply    = 0;
        $total_act_req       = 0;
        $total_act_kanban    = 0;
        $total_act_bpb       = 0;
        $total_act_adjout    = 0;
        $total_act_kanban_sj = 0;
        $total_act_kanban_sp = 0;

        foreach ($records as $record) 
        {
            $rate = 1;

            // standard Price
            $std_p = (float)$record->standard_price * $rate;
            $act_p = (float)$record->actual_price * 1; // IDR

            /** --- existing
            // Begin
            $b_qty = (float)$record->begin_stock;
            $b_std_a = $b_qty * $std_p;
            $b_act_a = $b_qty * $std_p; // begin actual = std
            $b_variance = $b_act_a - $b_std_a;

            // In
            $i_qty = (float)$record->receipt_qty + (float)$record->bpm_qty + (float)$record->adj_in_qty;
            $i_std_a = $i_qty * $std_p;
            $i_act_a = (float)$record->total_actual_amt_in * $rate; // Hanya Purchase yang punya harga riil po
            $i_act_p = 0;
            if ($i_qty > 0) {
                $i_act_p = $i_act_a / $i_qty;
            }
            $i_variance = $i_act_a - $i_std_a;

            // HARGA RATA-RATA (Moving Average Price)
            $avg_act_p = 0;
            if (($b_qty + $i_qty) > 0) {
                $avg_act_p = ($b_act_a + $i_act_a) / ($b_qty + $i_qty);
            }

            // Out
            $o_qty = (float)$record->qty_supply_sheet + (float)$record->qty_mat_request + (float)$record->qty_kanban + (float)$record->qty_kanban_sj + (float)$record->qty_kanban_sp + (float)$record->bpb_qty + (float)$record->adj_out_qty;
            $o_std_a = $o_qty * $std_p;
            // Moving Average for Actual Out
            $o_act_a = 0;
            $o_act_p = 0;
            if (($b_qty + $i_qty) > 0) {
                $o_act_a = $o_qty * $avg_act_p;
            }
            if ($o_qty > 0) {
                $o_act_p = $o_act_a / $o_qty;
            }
            $o_variance = $o_act_a - $o_std_a;
            */
            

            // Begin
            $b_qty      = (float)$record->actual_qty;
            $b_std_a    = $b_qty * $std_p;
            $b_act_a    = $b_qty * $act_p;
            $b_variance = $b_act_a - $b_std_a;

            // In
            $i_qty = (float)$record->receipt_qty + (float)$record->bpm_qty + (float)$record->adj_in_qty;
            $i_std_a = $i_qty * $std_p;
            $i_act_p = (float)$record->actual_price;
            $i_act_a = $i_qty * $i_act_p;
            $i_variance = $i_act_a - $i_std_a;

            $avg_act_p = (float)$record->actual_price; // Bukan Moving Average Price, Tapi Actual Price dari upload

            // Out
            $o_qty = (float)$record->qty_supply_sheet + (float)$record->qty_mat_request + (float)$record->qty_kanban + (float)$record->qty_kanban_sj + (float)$record->qty_kanban_sp + (float)$record->bpb_qty + (float)$record->adj_out_qty;
            $o_std_a = $o_qty * $std_p;
            $o_act_a = 0;
            $o_act_p = (float)$record->actual_price;
            $o_variance = $o_act_a - $o_std_a;

            // Ending = (Begin + In) - Out
            $e_qty = ($b_qty + $i_qty) - $o_qty;
            $e_std_a = $e_qty * $std_p;
            $e_act_a = ($b_act_a + $i_act_a) - $o_act_a;
            $e_act_p = 0;
            if ($e_qty > 0) {
                $e_act_p = $e_act_a / $e_qty;
            }
            $e_variance = $e_act_a - $e_std_a;

            
            // -- CALCULATE AMOUNT IN (Based on Average Price) --
            $amt_act_purchase = $record->receipt_qty * $avg_act_p; 
            $amt_act_bpm      = $record->bpm_qty * $avg_act_p; 
            $amt_act_adjin    = $record->adj_in_qty * $avg_act_p;

            // -- CALCULATE AMOUNT OUT (Based on Average Price) --
            $amt_act_supply = $record->qty_supply_sheet * $avg_act_p;
            $amt_act_req    = $record->qty_mat_request * $avg_act_p;
            $amt_act_kanban = $record->qty_kanban * $avg_act_p;
            $amt_act_bpb    = $record->bpb_qty * $avg_act_p;
            $amt_act_adjout = $record->adj_out_qty * $avg_act_p;
            $amt_act_kanban_sj = $record->qty_kanban_sj * $avg_act_p;
            $amt_act_kanban_sp = $record->qty_kanban_sp * $avg_act_p;

            // Total QTY 
            $total_b_qty += $b_qty;
            $total_i_qty += $i_qty;
            $total_o_qty += $o_qty;
            $total_e_qty += $e_qty;

            $total_receipt_qty      += $record->receipt_qty;
            $total_bpm_qty          += $record->bpm_qty;
            $total_adj_in_qty       += $record->adj_in_qty;
            $total_qty_supply_sheet += $record->qty_supply_sheet;
            $total_qty_mat_request  += $record->qty_mat_request;
            $total_qty_kanban       += $record->qty_kanban;
            $total_bpb_qty          += $record->bpb_qty;
            $total_adj_out_qty      += $record->adj_out_qty;
            $total_qty_kanban_sj    += $record->qty_kanban_sj;
            $total_qty_kanban_sp    += $record->qty_kanban_sp;

            // Summary
            $total_std_b += $b_std_a;
            $total_std_i += $i_std_a;
            $total_std_o += $o_std_a;
            $total_std_e += $e_std_a;
            $total_act_b += $b_act_a;
            $total_act_i += $i_act_a;
            $total_act_o += $o_act_a;
            $total_act_e += $e_act_a;

            // Total STD 
            $total_std_purchase  += ($record->receipt_qty * $std_p);
            $total_std_bpm       += ($record->bpm_qty * $std_p);
            $total_std_adjin     += ($record->adj_in_qty * $std_p);
            $total_std_supply    += ($record->qty_supply_sheet * $std_p);
            $total_std_req       += ($record->qty_mat_request * $std_p);
            $total_std_kanban    += ($record->qty_kanban * $std_p);
            $total_std_bpb       += ($record->bpb_qty * $std_p);
            $total_std_adjout    += ($record->adj_out_qty * $std_p);
            $total_std_kanban_sj += ($record->qty_kanban_sj * $std_p);
            $total_std_kanban_sp += ($record->qty_kanban_sp * $std_p);

            // Total ACTUAL
            $total_act_purchase  += $amt_act_purchase;
            $total_act_bpm       += $amt_act_bpm;
            $total_act_adjin     += $amt_act_adjin;
            $total_act_supply    += $amt_act_supply;
            $total_act_req       += $amt_act_req;
            $total_act_kanban    += $amt_act_kanban;
            $total_act_bpb       += $amt_act_bpb;
            $total_act_adjout    += $amt_act_adjout;
            $total_act_kanban_sj += $amt_act_kanban_sj;
            $total_act_kanban_sp += $amt_act_kanban_sp;


            $html .= '<tr>
                    <td align="center">'.$no++.'</td>
                    <td>'.$record->number.'</td>
                    <td>'.$record->name.'</td>
                    <td>'.$record->uom.'</td>
                    <td>'.$record->division.'</td>
                    <td>'.$record->category_name.'</td>
                    <td>'.$record->prodfam.'</td>
                    <td>'.$record->specification.'</td>
                    
                    <td align="right">'.number_format($b_qty, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format($b_std_a, 2).'</td>
                    <td>'.number_format($act_p, 2).'</td>
                    <td>'.number_format($b_act_a, 2).'</td>
                    <td>'.number_format($b_variance, 2).'</td>
                    
                    <td align="right">'.number_format($i_qty, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format($i_std_a, 2).'</td>
                    <td>'.number_format($i_act_p, 2).'</td>
                    <td>'.number_format($i_act_a, 2).'</td>
                    <td>'.number_format($i_variance, 2).'</td>
                    
                    <td align="right">'.number_format($o_qty, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format($o_std_a, 2).'</td>
                    <td>'.number_format($o_act_p, 2).'</td>
                    <td>'.number_format($o_act_a, 2).'</td>
                    <td>'.number_format($o_variance, 2).'</td>

                    <td align="right">'.number_format($e_qty, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format($e_std_a, 2).'</td>
                    <td>'.number_format($e_act_p, 2).'</td>
                    <td>'.number_format($e_act_a, 2).'</td>
                    <td>'.number_format($e_variance, 2).'</td>

                    <td align="right">'.number_format($record->receipt_qty, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format($record->receipt_qty*$std_p, 2).'</td>
                    <td align="right">'.number_format($avg_act_p, 2).'</td>
                    <td align="right">'.number_format($amt_act_purchase, 2).'</td>

                    <td align="right">'.number_format($record->bpm_qty, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format($record->bpm_qty*$std_p, 2).'</td>
                    <td align="right">'.number_format($avg_act_p, 2).'</td>
                    <td align="right">'.number_format($amt_act_bpm, 2).'</td>

                    <td align="right">'.number_format($record->adj_in_qty, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format($record->adj_in_qty*$std_p, 2).'</td>
                    <td align="right">'.number_format($avg_act_p, 2).'</td>
                    <td align="right">'.number_format($amt_act_adjin, 2).'</td>

                    <td align="right">'.number_format($record->qty_supply_sheet, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format($record->qty_supply_sheet*$std_p, 2).'</td>
                    <td align="right">'.number_format($avg_act_p, 2).'</td>
                    <td align="right">'.number_format($amt_act_supply, 2).'</td>

                    <td align="right">'.number_format($record->qty_mat_request, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format($record->qty_mat_request*$std_p, 2).'</td>
                    <td align="right">'.number_format($avg_act_p, 2).'</td>
                    <td align="right">'.number_format($amt_act_req, 2).'</td>

                    <td align="right">'.number_format($record->qty_kanban, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format($record->qty_kanban*$std_p, 2).'</td>
                    <td align="right">'.number_format($avg_act_p, 2).'</td>
                    <td align="right">'.number_format($amt_act_kanban, 2).'</td>

                    <td align="right">'.number_format($record->qty_kanban_sj, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format(($record->qty_kanban_sj)*$std_p, 2).'</td>
                    <td align="right">'.number_format($avg_act_p, 2).'</td>
                    <td align="right">'.number_format($amt_act_kanban_sj, 2).'</td>

                    <td align="right">'.number_format($record->qty_kanban_sp, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format(($record->qty_kanban_sp)*$std_p, 2).'</td>
                    <td align="right">'.number_format($avg_act_p, 2).'</td>
                    <td align="right">'.number_format($amt_act_kanban_sp, 2).'</td>

                    <td align="right">'.number_format($record->bpb_qty, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format($record->bpb_qty*$std_p, 2).'</td>
                    <td align="right">'.number_format($avg_act_p, 2).'</td>
                    <td align="right">'.number_format($amt_act_bpb, 2).'</td>

                    <td align="right">'.number_format($record->adj_out_qty, 2).'</td>
                    <td>'.number_format($std_p, 2).'</td>
                    <td>'.number_format($record->adj_out_qty*$std_p, 2).'</td>
                    <td align="right">'.number_format($avg_act_p, 2).'</td>
                    <td align="right">'.number_format($amt_act_adjout, 2).'</td>
                </tr>
            ';
        }

        $html .= '<tr style="font-weight:bold;">
            <td colspan="8" style="text-align:right;"><b>GRAND TOTAL</b></td>

            <td align="right">'.number_format($total_b_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_b, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_b, 2).'</td>
            <td></td>

            <td align="right">'.number_format($total_i_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_i, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_i, 2).'</td>
            <td></td>

            <td align="right">'.number_format($total_o_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_o, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_o, 2).'</td>
            <td></td>

            <td align="right">'.number_format($total_e_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_e, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_e, 2).'</td>
            <td></td>

            <td align="right">'.number_format($total_receipt_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_purchase, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_purchase, 2).'</td>

            <td align="right">'.number_format($total_bpm_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_bpm, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_bpm, 2).'</td>

            <td align="right">'.number_format($total_adj_in_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_adjin, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_adjin, 2).'</td>

            <td align="right">'.number_format($total_qty_supply_sheet, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_supply, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_supply, 2).'</td>

            <td align="right">'.number_format($total_qty_mat_request, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_req, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_req, 2).'</td>

            <td align="right">'.number_format($total_qty_kanban, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_kanban, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_kanban, 2).'</td>

            <td align="right">'.number_format($total_qty_kanban_sj, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_kanban_sj, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_kanban_sj, 2).'</td>

            <td align="right">'.number_format($total_qty_kanban_sp, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_kanban_sp, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_kanban_sp, 2).'</td>

            <td align="right">'.number_format($total_bpb_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_bpb, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_bpb, 2).'</td>

            <td align="right">'.number_format($total_adj_out_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_std_adjout, 2).'</td>
            <td></td>
            <td align="right">'.number_format($total_act_adjout, 2).'</td>
        </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }

    // -------------- PRINT DETAIL (INVENTORY RM) -------------
    public function print_detail($option = "")
    {
        if (!$this->db->table_exists('inventory_rm_actual')) {
            echo "<pre> Database Error: Tabel Inventory RM Actual not found! Please contact admin.</pre>";
            return false;
        }

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

        // Config Logo & Name
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //------------------------------------ GET DATA AND CALCULATIONS ----------------------------------//

        $query_main = "SELECT 
                a.id, a.number, a.name, a.division, a.uom,
                b.name as prodfam, 
                subfam.name as sub_prodfam,
                c.name as category_name,
                item_spec.specification,

                COALESCE(aa.price, 0) as std_price,
                COALESCE(aa.currency, '-') as currency,
                COALESCE(j.begin_stock, 0) AS begin_stock,
                
                -- QTY IN & OUT
                (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(f.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) AS qty_in,
                (COALESCE(h.qty_issued, 0) + COALESCE(i.qty_trans_rm_out, 0)) AS qty_out,

                -- ACTUAL FROM UPLOAD
                COALESCE(actual.price, 0) as actual_price, 
                COALESCE(actual.qty, 0) as actual_qty,

                -- ACTUAL AMOUNT CALCULATION (JOIN TO PO)
                COALESCE(calc_in.total_actual_in, 0) as actual_amount_in,
                COALESCE(calc_out.total_actual_out, 0) as actual_amount_out,
                d.receipt_date -- used for rate lookup
            FROM item_rm a
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN item_family_subs subfam ON a.item_sub_family_id = subfam.id

            -- get actual from upload
            LEFT JOIN inventory_rm_actual actual ON (actual.part_no = a.number OR actual.item_rm_id = a.id)

            -- get specification 
            LEFT JOIN (
                SELECT a.item_rm_id, f.specification, c.size 
                FROM purchase_order_receipts a
                LEFT JOIN item_rm c ON a.item_rm_id = c.id
                LEFT JOIN purchase_orders f ON a.po_no = f.po_no AND a.item_rm_id = f.item_rm_id and (a.item_rm_id = f.item_rm_id or a.specification = f.specification)
            ) item_spec ON item_spec.item_rm_id = a.id
            
            -- Standard Price
            LEFT JOIN (SELECT item_rm_id, currency, price from standard_price_rm where '$filter_from' >= `start_date` and '$filter_to' <= `end_date`) aa on a.id = aa.item_rm_id

            -- QTY IN Logic
            LEFT JOIN (SELECT MAX(b.receipt_date) AS receipt_date, b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
            
            -- ACTUAL AMOUNT IN (Logic: Sum of Qty Scan * PO Price)
            LEFT JOIN (
                SELECT pr.item_rm_id, SUM(sr.qty * po.price) as total_actual_in
                FROM purchase_order_receipts pr
                JOIN scan_item_receipts sr ON pr.receipt_id = sr.receipt_id
                JOIN purchase_orders po ON pr.po_no = po.po_no AND pr.item_rm_id = po.item_rm_id
                WHERE pr.receipt_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY pr.item_rm_id
            ) calc_in ON a.id = calc_in.item_rm_id

            -- ACTUAL AMOUNT OUT (Logic: Sum of Issued Qty * PO Price)
            LEFT JOIN (
                SELECT imd.item_rm_id, SUM(imd.qty * po.price) as total_actual_out
                FROM issued_material_details imd
                LEFT JOIN (SELECT item_rm_id, MAX(price) as price FROM purchase_orders GROUP BY item_rm_id) po ON imd.item_rm_id = po.item_rm_id
                WHERE DATE_FORMAT(imd.created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY imd.item_rm_id
            ) calc_out ON a.id = calc_out.item_rm_id

            -- Query pendukung (e, f, g, h, i, k, j)
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_in FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'IN' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_trans_rm_out FROM transaction_rm WHERE request_date BETWEEN '$filter_from' AND '$filter_to' AND transaction_kind = 'OUT' GROUP BY item_rm_id) i ON a.id = i.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) k ON a.id = k.item_rm_id
            
            -- BEGIN STOCK Logic (Query j)
            LEFT JOIN (SELECT a.id, ((COALESCE(b.qty_scan_in, 0) + COALESCE(c.qty_os_rm, 0) + COALESCE(d.qty_trans_rm_in, 0) + COALESCE(e.return_qty, 0) + COALESCE(h.qty_scan_bpm, 0)) - (COALESCE(f.qty_issued, 0) + COALESCE(g.qty_trans_rm_out, 0))) AS begin_stock
                    FROM item_rm a
                    LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date < '$filter_from' GROUP BY b.item_rm_id) b ON a.id = b.item_rm_id
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
        
        // Build Table Header
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
                    <th rowspan="4">Specification</th>
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
        $grandtotals = [
            'begin_qty' => 0, 'begin_std_amt' => 0, 'begin_act_amt' => 0,
            'in_qty'    => 0, 'in_std_amt'    => 0, 'in_act_amt'    => 0,
            'out_qty'   => 0, 'out_std_amt'   => 0, 'out_act_amt'   => 0,
            'end_qty'   => 0, 'end_std_amt'   => 0, 'end_act_amt'   => 0
        ];

        foreach ($records as $record) {
            $item_rm_id = $record->id;

            // Exchange Rate Logic
            $rate = 1;
            if ($record->currency == 'USD' && !empty($record->receipt_date)) {
                $rate_query = $this->db->get_where('standard_exchange_rates', [
                    'currency_from' => 'USD',
                    'start_date <=' => $record->receipt_date,
                    'end_date >=' => $record->receipt_date
                ])->row();
                $rate = $rate_query ? $rate_query->middle : 1;
            }

            // Summary Calculations
            $std_price_rate = $record->std_price * $rate;
            $act_price_rate = $record->actual_price * 1; // IDR
            
            /** --- existing
            $begin_qty = $record->begin_stock;
            $begin_std_amt = $begin_qty * $std_price_rate;
            $begin_act_amt = $begin_std_amt; // Sesuai permintaan: begin actual sama dengan std
            $begin_act_price = $std_price_rate; // Sesuai permintaan: begin actual sama dengan std

            $in_qty = $record->qty_in;
            $in_std_amt = $in_qty * $std_price_rate;
            $in_act_amt = $record->actual_amount_in * $rate;

            $in_act_price = 0;
            if ($in_qty > 0) {
                $in_act_price = $in_act_amt / $in_qty;
            }

            $out_qty = $record->qty_out;
            $out_std_amt = $out_qty * $std_price_rate;
            $out_act_amt = $record->actual_amount_out * $rate;

            $out_act_price = 0;
            if ($out_qty > 0) {
                $out_act_price = $out_act_amt / $out_qty;
            }

            $end_qty = ($begin_qty + $in_qty) - $out_qty;
            $end_std_amt = ($begin_std_amt + $in_std_amt) - $out_std_amt;
            $end_act_amt = ($begin_act_amt + $in_act_amt) - $out_act_amt;

            $end_act_price = 0;
            if ($end_qty > 0) {
                $end_act_price = $end_act_amt / $end_qty;
            }
            */

            $begin_qty       = (float)$record->actual_qty;  // Begin Balance = Qty dari upload saja
            $begin_std_amt   = $begin_qty * $std_price_rate;
            $begin_act_price = $act_price_rate;
            $begin_act_amt   = $begin_qty * $begin_act_price;

            $in_qty          = $record->qty_in;
            $in_act_price    = $act_price_rate;
            $in_std_amt      = $in_qty * $std_price_rate;
            $in_act_amt      = $in_qty * $act_price_rate;

            $out_qty         = $record->qty_out;
            $out_act_price   = $act_price_rate;
            $out_std_amt     = $out_qty * $std_price_rate;
            $out_act_amt     = $out_qty * $act_price_rate;

            $end_qty = ($begin_qty + $in_qty) - $out_qty;
            $end_act_price = $act_price_rate;
            $end_std_amt = ($begin_std_amt + $in_std_amt) - $out_std_amt;
            $end_act_amt = ($begin_act_amt + $in_act_amt) - $out_act_amt;


            // Add to Grand Total
            /* Comment agar tidak double counting
            foreach ($grandtotals as $key => $val) {
                $grandtotals[$key] += ${$key};
            }
            */

            $html .= '<tr>
                        <td align="center">'.$no.'</td>
                        <td colspan="3">'.$record->number.'</td>
                        <td>'.$record->name.'</td>
                        <td>'.$record->uom.'</td>
                        <td>'.$record->division.'</td>
                        <td>'.$record->category_name.'</td>
                        <td>'.$record->prodfam.'</td>
                        <td>'.$record->specification.'</td>
                        <td align="center">'.$record->currency.'</td>
                        <td align="right">'.number_format($rate, 2).'</td>
                        
                        <td align="right">'.number_format($begin_qty, 2).'</td>
                        <td align="right">'.number_format($std_price_rate, 2).'</td>
                        <td align="right">'.number_format($begin_std_amt, 2).'</td>
                        <td align="right">'.number_format($begin_act_price, 2).'</td>
                        <td align="right">'.number_format($begin_act_amt, 2).'</td>

                        <td align="right">'.number_format($in_qty, 2).'</td>
                        <td align="right">'.number_format($std_price_rate, 2).'</td>
                        <td align="right">'.number_format($in_std_amt, 2).'</td>
                        <td align="right">'.number_format($in_act_price, 2).'</td>
                        <td align="right">'.number_format($in_act_amt, 2).'</td>

                        <td align="right">'.number_format($out_qty, 2).'</td>
                        <td align="right">'.number_format($std_price_rate, 2).'</td>
                        <td align="right">'.number_format($out_std_amt, 2).'</td>
                        <td align="right">'.number_format($out_act_price, 2).'</td>
                        <td align="right">'.number_format($out_act_amt, 2).'</td>

                        <td align="right">'.number_format($end_qty, 2).'</td>
                        <td align="right">'.number_format($std_price_rate, 2).'</td>
                        <td align="right">'.number_format($end_std_amt, 2).'</td>
                        <td align="right">'.number_format($end_act_price, 2).'</td>
                        <td align="right">'.number_format($end_act_amt, 2).'</td>
                    </tr>';

            $running_qty_bal = 0; 
            $running_act_amt_bal = 0;

            // (Logika Detail Transactions)
            if ($filter_display == "DETAIL") {
                $nod = 1;
                // Inisialisasi awal produk berdasarkan saldo upload
                $running_qty_bal     = (float)$record->actual_qty; 
                $running_act_amt_bal = (float)$record->actual_qty * (float)$act_price_rate;
                
                // Akumulasi BEGIN ke Grand Total (Hanya diambil sekali per item)
                $grandtotals['begin_qty']     += (float)$record->actual_qty;
                $grandtotals['begin_std_amt'] += ((float)$record->actual_qty * $std_price_rate);
                $grandtotals['begin_act_amt'] += $running_act_amt_bal;

                /** -- existing bug muncul QTY upload di In dan di Begin
                $running_qty_bal = (float)$record->begin_stock;
                $running_act_amt_bal = (float)$record->begin_stock * (float)$act_price_rate;
                */

                // 2. Kumpulkan semua data transaksi ke dalam satu array
                if ($filter_trans_type == '') {
                    //-------------- Awal Query disini----------------------------------//                    
                    // UPLOADS
                    $uploads = $this->crud->query("SELECT
                            a.cutoff_date, 
                            '-' as bc_kind, 
                            '-' as bc_aju, 
                            '-' as bc_document, 
                            '-' as bc_date, 
                            SUM(a.qty) as actual_qty,
                            MAX(a.price) actual_price,
                            a.created_by as username
                        FROM inventory_rm_actual a 
                        WHERE a.item_rm_id = '$item_rm_id' 
                        GROUP BY a.item_rm_id, a.id");

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

                    // --- UPLOADS ---
                    foreach ($uploads as $r) {
                        $all_data[] = [
                            'type'      => 'UPLOADS',
                            'date'      => $r->cutoff_date,
                            'username'  => $r->username,
                            'qty_in'    => $r->actual_qty,
                            'qty_out'   => 0,
                            'actual_price_begin' => $r->actual_price,
                            'doc1' => '-', 'doc2' => '-', 'doc3' => '-', 'doc4' => '-'
                        ];
                    }

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
                        // Jika ada tipe UPLOADS, maka jadi paling atas (-1)
                        if ($a['type'] === 'UPLOADS') return -1;
                        if ($b['type'] === 'UPLOADS') return 1;

                        // Transaksi lainnya diurutkan berdasarkan tanggal
                        return strtotime($a['date']) - strtotime($b['date']);
                    });
                }
                
                if (!empty($all_data)) {
                    // Baris judul detail
                    $html .= '<tr>
                                <td colspan="32" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
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

                    // --- PERBAIKAN LOGIKA LOOPING DETAIL (Bug Qty Upload ada di Begin dan In) ---
                    $running_qty_bal = 0; 
                    $running_act_amt_bal = 0;
                    $first_upload_captured = false; 

                    foreach ($all_data as $data) {
                        if ($data['type'] === 'UPLOADS') {
                            $current_begin_qty = (float)$data['qty_in'];
                            $current_begin_amt = $current_begin_qty * (float)$data['actual_price_begin'];
                            $current_in_qty = 0; 
                            $current_in_amt = 0;
                            $current_out_qty = 0;
                            $current_out_amt = 0;
                        } else {
                            // Transaksi Harian (RECEIPT, ISSUED, dll)
                            $current_begin_qty = $running_qty_bal;
                            $current_begin_amt = $running_act_amt_bal;
                            
                            $current_in_qty  = (float)$data['qty_in'];
                            $current_in_amt  = $current_in_qty * (float)$act_price_rate;
                            $current_out_qty = (float)$data['qty_out'];
                            $current_out_amt = $current_out_qty * (float)$act_price_rate;

                            $running_qty_bal     += ($current_in_qty - $current_out_qty);
                            $running_act_amt_bal += ($current_in_amt - $current_out_amt);

                            // Akumulasi IN & OUT ke Grand Total (Hanya transaksi non-upload)
                            $grandtotals['in_qty']      += $current_in_qty;
                            $grandtotals['in_std_amt']  += ($current_in_qty * $std_price_rate);
                            $grandtotals['in_act_amt']  += $current_in_amt;

                            $grandtotals['out_qty']     += $current_out_qty;
                            $grandtotals['out_std_amt'] += ($current_out_qty * $std_price_rate);
                            $grandtotals['out_act_amt'] += $current_out_amt;
                        }

                        // Render ke HTML menggunakan variabel hasil logika di atas
                        $html .= '<tr style="background:#fff;">
                                    <td></td>
                                    <td align="center">' . $nod . '</td>
                                    <td>' . $data['type'] . '</td>
                                    <td>' . $data['username'] . '</td>
                                    <td align="center">' . date("d-m-Y", strtotime($data['date'])) . '</td>
                                    <td>' . $data['doc1'] . '</td>
                                    <td>' . $data['doc2'] . '</td>
                                    <td>' . $data['doc3'] . '</td>
                                    <td>' . $data['doc4'] . '</td>
                                    <td align="center">' . $record->currency . '</td>
                                    <td align="right">' . number_format($record->std_price, 2) . '</td>
                                    <td align="right">' . number_format($rate, 2) . '</td>

                                    <td align="right">' . number_format($current_begin_qty, 2) . '</td>
                                    <td align="right">' . number_format($std_price_rate, 2) . '</td>
                                    <td align="right">' . number_format($current_begin_qty * $std_price_rate, 2) . '</td>
                                    <td align="right">' . number_format($act_price_rate, 2) . '</td>
                                    <td align="right">' . number_format($current_begin_amt, 2) . '</td>

                                    <td align="right">' . number_format($current_in_qty, 2) . '</td>
                                    <td align="right">' . number_format($std_price_rate, 2) . '</td>
                                    <td align="right">' . number_format($current_in_qty * $std_price_rate, 2) . '</td>
                                    <td align="right">' . number_format($act_price_rate, 2) . '</td>
                                    <td align="right">' . number_format($current_in_amt, 2) . '</td>

                                    <td align="right">' . number_format($current_out_qty, 2) . '</td>
                                    <td align="right">' . number_format($std_price_rate, 2) . '</td>
                                    <td align="right">' . number_format($current_out_qty * $std_price_rate, 2) . '</td>
                                    <td align="right">' . number_format($act_price_rate, 2) . '</td>
                                    <td align="right">' . number_format($current_out_amt, 2) . '</td>

                                    <td align="right">' . number_format($running_qty_bal, 2) . '</td>
                                    <td align="right">' . number_format($std_price_rate, 2) . '</td>
                                    <td align="right">' . number_format($running_qty_bal * $std_price_rate, 2) . '</td>
                                    <td align="right">' . number_format($act_price_rate, 2) . '</td>
                                    <td align="right">' . number_format($running_act_amt_bal, 2) . '</td>
                                </tr>';
                        $nod++;
                    }

                    // Akumulasi ENDING ke Grand Total (Posisi terakhir saldo produk ini)
                    $grandtotals['end_qty']     += $running_qty_bal;
                    $grandtotals['end_std_amt'] += ($running_qty_bal * $std_price_rate);
                    $grandtotals['end_act_amt'] += $running_act_amt_bal;
                }
            }

            $no++;
        }

        // Grand Total Row
        $html .= '<tr style="background:#eee; font-weight:bold;">
                    <td colspan="12" align="right">GRAND TOTAL</td>
                    <td align="right">'.number_format($grandtotals['begin_qty'], 2).'</td><td></td>
                    <td align="right">'.number_format($grandtotals['begin_std_amt'], 2).'</td><td></td>
                    <td align="right">'.number_format($grandtotals['begin_act_amt'], 2).'</td>
                    
                    <td align="right">'.number_format($grandtotals['in_qty'], 2).'</td><td></td>
                    <td align="right">'.number_format($grandtotals['in_std_amt'], 2).'</td><td></td>
                    <td align="right">'.number_format($grandtotals['in_act_amt'], 2).'</td>
                    
                    <td align="right">'.number_format($grandtotals['out_qty'], 2).'</td><td></td>
                    <td align="right">'.number_format($grandtotals['out_std_amt'], 2).'</td><td></td>
                    <td align="right">'.number_format($grandtotals['out_act_amt'], 2).'</td>
                    
                    <td align="right">'.number_format($grandtotals['end_qty'], 2).'</td><td></td>
                    <td align="right">'.number_format($grandtotals['end_std_amt'], 2).'</td><td></td>
                    <td align="right">'.number_format($grandtotals['end_act_amt'], 2).'</td>
                </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }
}
