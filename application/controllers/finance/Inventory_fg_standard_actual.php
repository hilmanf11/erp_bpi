<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

// Library Excel PhpOffice
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property CI_Form_validation $form_validation
 * @property Crud $crud
 */
class Inventory_fg_standard_actual extends CI_Controller
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
            $data['menus_id'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('finance/inventory_fg_standard_actual');
        } else {
            redirect('error_access');
        }
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

            // check part_no
            $item_fg = $this->db->select('id, number, name')->from('item_fg')->where('number', $data['part_no'])->get()->row();

            if (empty($data['part_no']) && empty($data['cutoff_date']) && empty($data['uom']) && empty($data['qty']) && empty($data['price']) ) {
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
            if (empty($item_fg)) {
                echo json_encode(array("title" => "Not Found", "message" => "Item " . $data['part_no'] . " is Not Found in Master Item!", "theme" => "error"));
                return;
            }

            // Prepare Data
            $cutoff_date = date("Y-m-d", strtotime($data['cutoff_date']));
            $dataFinal = [
                'item_fg_id'  => $item_fg->id,
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
            $existing = $this->db->get_where('inventory_fg_actual', [
                'item_fg_id'  => $item_fg->id,
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
                $update = $this->db->update('inventory_fg_actual', $dataUpdate);
                
                if ($update) {
                    echo json_encode(array("title" => "Updated", "message" => "Data updated successfully!", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => "Failed to update data!", "theme" => "error"));
                }
            } else {
                // CREATE jika belum ada
                $send   = $this->crud->create('inventory_fg_actual', $dataFinal);
                echo $send;
            }
        }
    }
    
    public function uploadclearFailed()
    {
        @unlink('failed/inventory_fg_standard_actual.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/inventory_fg_standard_actual.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/inventory_fg_standard_actual.txt";

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
        // Get parameter POST dari EasyUI
        $page   = intval($this->input->post('page') ?? 1);
        $rows   = intval($this->input->post('rows') ?? 10);
        $offset = ($page - 1) * $rows;

        // Get filter yang diketik user
        $filterRules = $this->input->post('filterRules');

        $this->db->from('inventory_fg_actual'); // Tabel FG
        $this->db->where('upload', 'YES');
        $this->db->where('deleted', 0);

        // --- PROSES FILTER DARI SEARCH BOX ---
        if (!empty($filterRules)) {
            $rules = json_decode($filterRules);
            foreach ($rules as $rule) {
                $field = $rule->field;
                $value = $rule->value;
                
                if ($value !== '') {
                    // Gunakan like untuk pencarian string/part number
                    $this->db->like($field, $value);
                }
            }
        }

        // Hitung total data SETELAH filter (Gunakan FALSE agar query tidak reset)
        $total = $this->db->count_all_results('', FALSE);

        // Get data sesuai pagination
        $this->db->order_by('upload_date', 'DESC');
        $this->db->limit($rows, $offset);
        $data = $this->db->get()->result();

        $result = [
            "total" => (int)$total,
            "page"  => (int)$page,
            "rows"  => $data,
        ];

        header('Content-Type: application/json');
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

    public function print_without_actual($option = "") 
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_fg_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_trans_type = $this->input->get("filter_trans_type");
        $filter_division = $this->input->get("filter_division");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $display_title = ($filter_display == "DETAIL") ? '(DETAIL)' : '(RECAP)';

        // Config Logo & Name
        $config = $this->db->get('config')->row();


        // mengambil 'price' (standard price) dari standard_price_fg
        $query_standard_price = "SELECT item_fg_id, currency, price 
        FROM standard_price_fg 
        WHERE '$filter_from' >= `start_date` AND '$filter_to' <= `end_date` 
        GROUP BY item_fg_id";

        // Step 1: Hitung qty_in dari checksheet
        $query_qty_in_checksheet = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_checksheet
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY e.item_fg_id";

        //Pecahan dari IN Checksheet
        $query_qty_in_checksheet_non_subcont = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_non_subcont
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND e.status_subcont = 'NO' AND e.wo_no not like '%RG-%'
        GROUP BY e.item_fg_id";

        $query_qty_in_checksheet_subcont_jasa = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_subcont_jasa
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND e.subcont_type = 'Jasa' AND e.wo_no not like '%RG-%'
        GROUP BY e.item_fg_id";

        $query_qty_in_checksheet_subcont_fg = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_subcont_fg
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND e.subcont_type = 'Finished Good' AND e.wo_no not like '%RG-%'
        GROUP BY e.item_fg_id";

        $query_qty_in_checksheet_repair_fg = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_repair_fg
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' AND e.wo_no like '%RG-%'
        GROUP BY e.item_fg_id";
        //------------------------------------

        // Step 2: Hitung qty_in tanpa checksheet
        $query_qty_in_no_checksheet = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
        FROM scan_item_receipts_fg i
        WHERE i.type = 'NBFG'
        AND i.packing_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY i.item_fg_id";

        // Step 3: Hitung initial `i` dari transaction_fg (kind IN)
        $query_transaction_fg_in = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        $query_transaction_fg_in_adj = "SELECT a.item_fg_id, SUM(a.qty) as initial_in_adj
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'ADJ IN STO'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        $query_transaction_fg_in_rfg = "SELECT a.item_fg_id, SUM(a.qty) as initial_in_rfg
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN' AND a.transaction_type = 'RECEIPT FG'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        // Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        $query_qty_out_bpb = "SELECT a.item_fg_id, SUM(a.qty) as qty_out_bpb
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT' AND a.transaction_type = 'BPB'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        $query_qty_out_adj = "SELECT a.item_fg_id, SUM(a.qty) as qty_out_adj
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT' AND a.transaction_type = 'ADJ OUT STO'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        // Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes = "SELECT item_fg_id, SUM(qty) as initial_out_g
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY item_fg_id";

        $query_delivery_notes_sales = "SELECT item_fg_id, SUM(qty) as qty_notes_sales
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to' AND trans_type = 'SALES'
        GROUP BY item_fg_id";


        $query_delivery_notes_return = "SELECT item_fg_id, SUM(qty) as qty_notes_return
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to' AND trans_type = 'RETURN'
        GROUP BY item_fg_id";

        $query_delivery_notes_sample = "SELECT item_fg_id, SUM(qty) as qty_notes_sample
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to' AND trans_type = 'SAMPLE'
        GROUP BY item_fg_id";

        // Step 6: Hitung initial `h` (scan_repair_of_goods)
        $query_scan_repair_of_goods = "SELECT e.item_fg_id, SUM(f.qty) as initial_out_h
        FROM scan_repair_of_goods f
        JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
        WHERE DATE_FORMAT(e.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY f.item_fg_id";

        // Step 7: Hitung qty_in WIP division MTS
        $query_qty_in_wip_receipt = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_wip_receipt
        FROM wip_receipts i
        WHERE i.division = 'MTS'
        AND i.trans_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY i.item_fg_id";

        //-----------------------------------------------------------------

        $query_qty_in_checksheet2 = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_checksheet
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') < '$filter_from'
        GROUP BY e.item_fg_id";

        // Step 2: Hitung qty_in tanpa checksheet
        $query_qty_in_no_checksheet2 = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
        FROM scan_item_receipts_fg i
        WHERE i.type = 'NBFG'
        AND i.packing_date < '$filter_from'
        GROUP BY i.item_fg_id";

        // Step 3: Hitung initial `i` dari transaction_fg (kind IN)
        $query_transaction_fg_in2 = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN'
        AND a.request_date < '$filter_from'
        GROUP BY a.item_fg_id";

        // Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT'
        AND a.request_date < '$filter_from'
        GROUP BY a.item_fg_id";

        // Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes2 = "SELECT item_fg_id, SUM(qty) as initial_out_g
        FROM delivery_notes
        WHERE delivery_note_date < '$filter_from'
        GROUP BY item_fg_id";

        // Step 6: Hitung initial `h` (scan_repair_of_goods)
        $query_scan_repair_of_goods2 = "SELECT e.item_fg_id, SUM(f.qty) as initial_out_h
        FROM scan_repair_of_goods f
        JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
        WHERE DATE_FORMAT(e.trans_date, '%Y-%m-%d') < '$filter_from'
        GROUP BY f.item_fg_id";

        // Step 8: Hitung qty_in WIP division MTS
        $query_qty_in_wip_receipt2 = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_wip_receipt
        FROM wip_receipts i
        WHERE i.division = 'MTS'
        AND i.trans_date < '$filter_from'
        GROUP BY i.item_fg_id";

        // Step 9: Gabungan query
        $query_main = "SELECT 
            a.id, 
            a.number, 
            a.name, 
            a.uom,
            b.number as division,
            '0' as subcont_qty,
            a.type,
            COALESCE(x.begin_stock,0) AS begin_stock,
            COALESCE(sp.price, 0) as std_price,
            sp.currency AS standard_currency,

            COALESCE(qins.qty_in_non_subcont, 0) + COALESCE(qir.initial_in_rfg, 0) + COALESCE(qw.qty_in_wip_receipt, 0) as qty_rfg,
            COALESCE(qi.initial_in, 0) as adj_in_qty,
            COALESCE(qia.initial_in_adj, 0) as qty_in_adj,
            COALESCE(qir.initial_in_rfg, 0) as qty_in_rfg,
            COALESCE(qnc.qty_in_no_checksheet, 0) as qty_in_new_barcode,
            -- pecahan dari IN checksheet
            COALESCE(qins.qty_in_non_subcont, 0) as qty_in_non_subcont,
            COALESCE(qisj.qty_in_subcont_jasa, 0) as qty_in_subcont_jasa,
            COALESCE(qisfg.qty_in_subcont_fg, 0) as qty_in_subcont_fg,
            COALESCE(qirfg.qty_in_repair_fg, 0) as qty_in_repair_fg,
            ------------------------------
            COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) AS qty_in,
            
            COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0) AS qty_out,

            COALESCE(dns.qty_notes_sales, 0) as qty_out_sales,
            COALESCE(dnr.qty_notes_return, 0) as qty_out_return,
            COALESCE(dnss.qty_notes_sample, 0) as qty_out_sample,

            COALESCE(qh.initial_out_h, 0) as qty_out_repair,

            COALESCE(qo.qty_out, 0) as adj_out_qty,
            COALESCE(qob.qty_out_bpb, 0) + COALESCE(qh.initial_out_h, 0) as qty_out_bpb,
            COALESCE(qoa.qty_out_adj, 0) as qty_out_adj,
            
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) - 
            (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS end_stock
        FROM item_fg a
        LEFT JOIN divisions b ON a.division_id = b.id
        LEFT JOIN ($query_standard_price) sp ON a.id = sp.item_fg_id
        LEFT JOIN ($query_qty_in_checksheet) qc ON a.id = qc.item_fg_id
        LEFT JOIN ($query_qty_in_no_checksheet) qnc ON a.id = qnc.item_fg_id
        LEFT JOIN ($query_transaction_fg_in) qi ON a.id = qi.item_fg_id
        LEFT JOIN ($query_transaction_fg_in_adj) qia ON a.id = qia.item_fg_id
        LEFT JOIN ($query_transaction_fg_in_rfg) qir ON a.id = qir.item_fg_id
        LEFT JOIN ($query_qty_out) qo ON a.id = qo.item_fg_id
        LEFT JOIN ($query_qty_out_bpb) qob ON a.id = qob.item_fg_id
        LEFT JOIN ($query_qty_out_adj) qoa ON a.id = qoa.item_fg_id
        LEFT JOIN ($query_delivery_notes) qg ON a.id = qg.item_fg_id
        LEFT JOIN ($query_delivery_notes_sales) dns ON a.id = dns.item_fg_id
        LEFT JOIN ($query_delivery_notes_return) dnr ON a.id = dnr.item_fg_id
        LEFT JOIN ($query_delivery_notes_sample) dnss ON a.id = dnss.item_fg_id
        LEFT JOIN ($query_scan_repair_of_goods) qh ON a.id = qh.item_fg_id
        LEFT JOIN ($query_qty_in_wip_receipt) qw ON a.id = qw.item_fg_id
        LEFT JOIN ($query_qty_in_checksheet_non_subcont) qins ON a.id = qins.item_fg_id
        LEFT JOIN ($query_qty_in_checksheet_subcont_jasa) qisj ON a.id = qisj.item_fg_id
        LEFT JOIN ($query_qty_in_checksheet_subcont_fg) qisfg ON a.id = qisfg.item_fg_id
        LEFT JOIN ($query_qty_in_checksheet_repair_fg) qirfg ON a.id = qirfg.item_fg_id

        LEFT JOIN ( SELECT a.id,
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) - 
            (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS begin_stock
            FROM item_fg a
            LEFT JOIN ($query_qty_in_checksheet2) qc ON a.id = qc.item_fg_id
            LEFT JOIN ($query_qty_in_no_checksheet2) qnc ON a.id = qnc.item_fg_id
            LEFT JOIN ($query_transaction_fg_in2) qi ON a.id = qi.item_fg_id
            LEFT JOIN ($query_qty_out2) qo ON a.id = qo.item_fg_id
            LEFT JOIN ($query_delivery_notes2) qg ON a.id = qg.item_fg_id
            LEFT JOIN ($query_scan_repair_of_goods2) qh ON a.id = qh.item_fg_id
            LEFT JOIN ($query_qty_in_wip_receipt2) qw ON a.id = qw.item_fg_id

            GROUP BY a.id) x ON a.id = x.id
        WHERE a.id LIKE '%$filter_items%' AND a.division_id LIKE '%$filter_division%'
        ORDER BY a.number
        ";

        $records = $this->crud->query($query_main);

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
                <h3 style="margin:0;">INVENTORY FG STANDARD AND ACTUAL <i>' . $display_title . '</i> </h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';

        $html .= '<table id="customers" border="1" style="font-size: 11px;">
                <tr style="background-color: #eee;">
                    <th rowspan="5" width="20">No</th>
                    <th rowspan="5">Product No</th>
                    <th rowspan="5">Product Name</th>
                    <th rowspan="5">UOM</th>
                    <th rowspan="5">Type</th>
                    
                    <th colspan="24">SUMMARY</th>
                    <th colspan="55">DETAIL</th>
                </tr>

                <tr style="background-color:#d5d5d5;">
                    <th colspan="6" width="100">BEGIN</th>
                    <th colspan="6" width="100">IN</th>
                    <th colspan="6" width="100">OUT</th>
                    <th colspan="6" width="100">ENDING</th>

                    <th colspan="15">IN</th>
                    <th colspan="40">OUT</th>
                </tr>';

        // SUMMARY
        $html .= '<tr class="bg-yellow">
                <th rowspan="3" class="bg-grey">QTY</th>
                <th rowspan="2" colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th rowspan="2" colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                <th rowspan="3">
                    VARIANCE
                </th>

                <th rowspan="3" class="bg-grey">QTY</th>
                <th rowspan="2" colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th rowspan="2" colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                <th rowspan="3">
                    VARIANCE
                </th>

                <th rowspan="3" class="bg-grey">QTY</th>
                <th rowspan="2" colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th rowspan="2" colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                <th rowspan="3">
                    VARIANCE
                </th>

                <th rowspan="3" class="bg-grey">QTY</th>
                <th rowspan="2" colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th rowspan="2" colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                <th rowspan="3">
                    VARIANCE
                </th>
            ';
        // DETAIL
        $html .= '
                <th colspan="5" >IN RFG</th>
                <th colspan="5">IN REPAIR FG</th>
                <th colspan="5">NEW BARCODE</th>
                <th colspan="5">SUBCONT FG</th>
                <th colspan="5">SUBCONT JASA</th>
                <th colspan="5">ADJ STO</th>

                <th colspan="5">OUT SJ</th>
                <th colspan="5">OUT BPB</th>
                <th colspan="5">OUT RETUR<br>TKG</th>
                <th colspan="5">OUT SAMPLE</th>
                <th colspan="5">OUT ADJ<br>(STO)</th>
            </tr>';

        $html .= '<tr>
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

        $totalRfgQty = 0;
        $totalRfgRepairQty = 0;
        $totalNBQty = 0;
        $totalSubcontFGQty = 0;
        $totalSubcontJSQty = 0;
        $totalAdjInQty = 0;

        $totalOutSales = 0;
        $totalOutSalesMinus1 = 0;
        $totalOutSalesMinus2 = 0;
        $totalOutSalesMinus3 = 0;
        $totalOutReturn = 0;
        $totalOutSample = 0;
        $totalOutBpb = 0;
        $totalOutAdj = 0;

        $totalQtyIn = 0;
        $totalQtyOut = 0;
        $totalQtySelisihIn = 0;
        $totalQtySelisihOut = 0;

        $totalSTD_BeginStock = 0;
        $totalSTD_In = 0;
        $totalSTD_Out = 0;
        $totalSTD_EndingStock = 0;        
        $totalSTD_RfgQty = 0;
        $totalSTD_RfgRepairQty = 0;
        $totalSTD_NBQty = 0;
        $totalSTD_SubcontFGQty = 0;
        $totalSTD_SubcontJSQty = 0;
        $totalSTD_AdjInQty = 0;
        $totalSTD_OutSales = 0;
        $totalSTD_OutReturn = 0;
        $totalSTD_OutSample = 0;
        $totalSTD_OutBpb = 0;
        $totalSTD_OutAdj = 0;

        foreach ($records as $record) 
        {
            $item_fg_id = $record->id;

            // Exchange Rate Logic
            $rate = 1;

            // standard Price
            $std_p = (float)$record->std_price * $rate;
            
            //Item Receipts
            $totalBeginStock += @$record->begin_stock;
            $totalIn += $record->qty_in;
            $totalOut += $record->qty_out;
            $totalEndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out;
            
            $totalRfgQty += $record->qty_rfg;
            $totalRfgRepairQty += $record->qty_in_repair_fg;
            $totalNBQty += $record->qty_in_new_barcode;
            $totalSubcontFGQty += $record->qty_in_subcont_fg;
            $totalSubcontJSQty += $record->qty_in_subcont_jasa;
            $totalAdjInQty += $record->qty_in_adj;

            $totalOutSales += $record->qty_out_sales;
            $totalOutReturn += $record->qty_out_return;
            $totalOutSample += $record->qty_out_sample;
            $totalOutBpb += $record->qty_out_bpb;
            $totalOutAdj += $record->qty_out_adj;


            // Total Amount STD
            $totalSTD_BeginStock += @$record->begin_stock * $std_p;
            $totalSTD_In += $record->qty_in * $std_p;
            $totalSTD_Out += $record->qty_out * $std_p;
            $totalSTD_EndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out * $std_p;
            
            $totalSTD_RfgQty += $record->qty_rfg * $std_p;
            $totalSTD_RfgRepairQty += $record->qty_in_repair_fg * $std_p;
            $totalSTD_NBQty += $record->qty_in_new_barcode * $std_p;
            $totalSTD_SubcontFGQty += $record->qty_in_subcont_fg * $std_p;
            $totalSTD_SubcontJSQty += $record->qty_in_subcont_jasa * $std_p;
            $totalSTD_AdjInQty += $record->qty_in_adj * $std_p;

            $totalSTD_OutSales += $record->qty_out_sales * $std_p;
            $totalSTD_OutReturn += $record->qty_out_return * $std_p;
            $totalSTD_OutSample += $record->qty_out_sample * $std_p;
            $totalSTD_OutBpb += $record->qty_out_bpb * $std_p;
            $totalSTD_OutAdj += $record->qty_out_adj * $std_p;


            // Begin
            $b_qty = (float)$record->begin_stock;
            $b_std_a = $b_qty * $std_p;
            $b_act_a = $b_qty * $std_p; // begin actual = std
            $b_variance = $b_act_a - $b_std_a;

            // In
            $i_qty   = ($record->qty_rfg + $record->qty_in_repair_fg + $record->qty_in_new_barcode + $record->qty_in_subcont_fg + $record->qty_in_subcont_jasa + $record->qty_in_adj);
            $i_std_a = $std_p * $i_qty;
            $i_act_a = 0 * $rate; // Actual price belum pasti apakah dari SO, DN, atau lainnya
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
            $o_qty = ($record->qty_out_sales + $record->qty_out_bpb + $record->qty_out_return + $record->qty_out_sample + $record->qty_out_adj);
            $o_std_a = $std_p * $o_qty;
            $o_variance = 0;
            // Moving Average for Actual Out
            // NOTE: Actual Price belum pasti get dari Sales Order, Delivery Note, dll
            $o_act_a = 0;
            $o_act_p = 0;
            // if (($b_qty + $i_qty) > 0) {
            //     $o_act_a = $o_qty * $avg_act_p;
            // }
            // if ($o_qty > 0) {
            //     $o_act_p = $o_act_a / $o_qty;
            // }
            $o_variance = $o_act_a - $o_std_a;

            // Ending
            $e_qty = (@$record->begin_stock + $record->qty_in) - $record->qty_out;
            $e_std_a = $e_qty * $std_p;
            $e_act_a = ($b_act_a + $i_act_a) - $o_act_a;
            $e_act_p = 0;
            if ($e_qty > 0) {
                $e_act_p = $e_act_a / $e_qty;
            }
            $e_variance = $e_act_a - $e_std_a;

            // -- STANDARD AMOUNT DETAILS (Based on Standard Price) --
            $amt_std_rfg        = $std_p * $record->qty_rfg;
            $amt_std_repairfg   = $std_p * $record->qty_in_repair_fg;
            $amt_std_newbarcode = $std_p * $record->qty_in_new_barcode;
            $amt_std_subcontfg  = $std_p * $record->qty_in_subcont_fg;
            $amt_std_subcontjs  = $std_p * $record->qty_in_subcont_jasa;
            $amt_std_adjin      = $std_p * $record->qty_in_adj;
            $amt_std_sales      = $std_p * $record->qty_out_sales;
            $amt_std_bpb        = $std_p * $record->qty_out_bpb;
            $amt_std_return     = $std_p * $record->qty_out_return;
            $amt_std_sample     = $std_p * $record->qty_out_sample;
            $amt_std_adjout     = $std_p * $record->qty_out_adj;


            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $record->number . '</td>
                            <td style="mso-number-format:\@;">' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->type . '</td>
                            
                            <td style="text-align:right;">' . number_format($b_qty, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($b_std_a * $record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($b_act_a, 2) . '</td>
                            <td style="text-align:right;">' . number_format($b_variance, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_rfg + $record->qty_in_repair_fg + $record->qty_in_new_barcode + $record->qty_in_subcont_fg + $record->qty_in_subcont_jasa + $record->qty_in_adj, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($i_std_a, 2) . '</td>
                            <td style="text-align:right;">' . number_format($i_act_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($i_act_a, 2) . '</td>
                            <td style="text-align:right;">' . number_format($i_variance, 2) . '</td>

                            <td style="text-align:right;">' . number_format($record->qty_out_sales + $record->qty_out_bpb + $record->qty_out_return + $record->qty_out_sample + $record->qty_out_adj, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($o_std_a, 2) . '</td>
                            <td style="text-align:right;">' . number_format($o_act_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($o_act_a, 2) . '</td>
                            <td style="text-align:right;">' . number_format($o_variance, 2) . '</td>

                            <td style="text-align:right;">' . number_format($e_qty, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($e_std_a, 2) . '</td>
                            <td style="text-align:right;">' . number_format($e_act_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($e_act_a, 2) . '</td>
                            <td style="text-align:right;">' . number_format($e_variance, 2) . '</td>
                            
                            <td style="text-align:right;">' . number_format($record->qty_rfg, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amt_std_rfg, 2) . '</td>
                            <td style="text-align:right;"></td>
                            <td style="text-align:right;"></td>

                            <td style="text-align:right;">' . number_format($record->qty_in_repair_fg, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amt_std_repairfg, 2) . '</td>
                            <td style="text-align:right;"></td>
                            <td style="text-align:right;"></td>

                            <td style="text-align:right;">' . number_format($record->qty_in_new_barcode, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amt_std_newbarcode, 2) . '</td>
                            <td style="text-align:right;"></td>
                            <td style="text-align:right;"></td>

                            <td style="text-align:right;">' . number_format($record->qty_in_subcont_fg, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amt_std_subcontfg, 2) . '</td>
                            <td style="text-align:right;"></td>
                            <td style="text-align:right;"></td>

                            <td style="text-align:right;">' . number_format($record->qty_in_subcont_jasa, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amt_std_subcontjs, 2) . '</td>
                            <td style="text-align:right;"></td>
                            <td style="text-align:right;"></td>

                            <td style="text-align:right;">' . number_format($record->qty_in_adj, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amt_std_adjin, 2) . '</td>
                            <td style="text-align:right;"></td>
                            <td style="text-align:right;"></td>

                            <td style="text-align:right;">' . number_format($record->qty_out_sales, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amt_std_sales, 2) . '</td>
                            <td style="text-align:right;"></td>
                            <td style="text-align:right;"></td>

                            <td style="text-align:right;">' . number_format($record->qty_out_bpb, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amt_std_bpb, 2) . '</td>
                            <td style="text-align:right;"></td>
                            <td style="text-align:right;"></td>

                            <td style="text-align:right;">' . number_format($record->qty_out_return, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amt_std_return, 2) . '</td>
                            <td style="text-align:right;"></td>
                            <td style="text-align:right;"></td>

                            <td style="text-align:right;">' . number_format($record->qty_out_sample, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amt_std_sample, 2) . '</td>
                            <td style="text-align:right;"></td>
                            <td style="text-align:right;"></td>

                            <td style="text-align:right;">' . number_format($record->qty_out_adj, 2) . '</td>
                            <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                            <td style="text-align:right;">' . number_format($amt_std_adjout, 2) . '</td>
                            <td style="text-align:right;"></td>
                            <td style="text-align:right;"></td>
                        </tr>';
            $no++;
        }

        $html .= '<tr style="font-weight:bold;">
            <td colspan="5" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;">' . number_format($totalBeginStock, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_BeginStock, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($totalIn, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_In, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($totalOut, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_Out, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($totalEndingStock, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_EndingStock, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>


            <td style="text-align:right;">' . number_format($totalRfgQty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_RfgQty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($totalRfgRepairQty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_RfgRepairQty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($totalNBQty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_NBQty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($totalSubcontFGQty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_SubcontFGQty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($totalSubcontJSQty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_SubcontJSQty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($totalAdjInQty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_AdjInQty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($totalOutSales, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_OutSales, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($totalOutBpb, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_OutBpb, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($totalOutReturn, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_OutReturn, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($totalOutSample, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_OutSample, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($totalOutAdj, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($totalSTD_OutAdj, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;"></td>
        </tr>';
      
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print($option = "")
    {
        if (!$this->db->table_exists('inventory_fg_actual')) {
            echo "<pre> Database Error: Tabel Inventory FG Actual not found! Please contact admin.</pre>";
            return false;
        }

        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_fg_$format.xls");
        }

        $filter_from        = $this->input->get('filter_from');
        $filter_to          = $this->input->get('filter_to');
        $filter_items       = $this->input->get('filter_items');
        $filter_division    = $this->input->get("filter_division");
        $filter_display     = $this->input->get("filter_display");
        $filter_type        = $this->input->get("filter_type");
        $filter_trans_type  = $this->input->get("filter_trans_type");
        
        $start  = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $display_title = ($filter_display == "DETAIL") ? '(DETAIL)' : '(RECAP)';

        // Config Logo & Name
        $config = $this->db->get('config')->row();

        // Get Exchange Rates
        $rates = $this->db->get_where('standard_exchange_rates', ['currency_from' => 'USD'])->result();
        $get_rate = function($date) use ($rates) {
            if (empty($date)) return 1.0;
            foreach ($rates as $r) {
                if ($date >= $r->start_date && $date <= $r->end_date) return (float)$r->middle;
            }
            return 1.0;
        };

        // Get cutoff_date terbaru yang tidak melebihi filter_from
        $cutoff_data  = $this->db->select('cutoff_date')
                        ->where('cutoff_date <=', $filter_from)
                        ->order_by('cutoff_date', 'DESC')
                        ->limit(1)
                        ->get('inventory_fg_actual')
                        ->row();
        $start_system = ($cutoff_data) ? $cutoff_data->cutoff_date : '2026-01-01';

        //------------------------------------ GET DATA ----------------------------------//

        // Combine semua kategori Checksheet (Periode Berjalan)
        $query_checksheet = "SELECT 
                e.item_fg_id, 
                SUM(f.qty) as qty_in_checksheet,
                SUM(CASE WHEN e.status_subcont = 'NO' AND e.wo_no NOT LIKE '%RG-%' THEN f.qty ELSE 0 END) as qty_in_non_subcont,
                SUM(CASE WHEN e.subcont_type = 'Jasa' AND e.wo_no NOT LIKE '%RG-%' THEN f.qty ELSE 0 END) as qty_in_subcont_jasa,
                SUM(CASE WHEN e.subcont_type = 'Finished Good' AND e.wo_no NOT LIKE '%RG-%' THEN f.qty ELSE 0 END) as qty_in_subcont_fg,
                SUM(CASE WHEN e.wo_no LIKE '%RG-%' THEN f.qty ELSE 0 END) as qty_in_repair_fg
            FROM scan_item_receipts_fg f
            JOIN checksheets e ON e.number = f.checksheet_number
            WHERE e.packing_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY e.item_fg_id";

        // Combine semua kategori Transaction FG (Periode Berjalan)
        $query_trans_fg = "SELECT 
                item_fg_id,
                SUM(CASE WHEN transaction_kind = 'IN' THEN qty ELSE 0 END) as initial_in,
                SUM(CASE WHEN transaction_kind = 'IN' AND transaction_type = 'ADJ IN STO' THEN qty ELSE 0 END) as initial_in_adj,
                SUM(CASE WHEN transaction_kind = 'IN' AND transaction_type = 'RECEIPT FG' THEN qty ELSE 0 END) as initial_in_rfg,
                SUM(CASE WHEN transaction_kind = 'OUT' THEN qty ELSE 0 END) as qty_out,
                SUM(CASE WHEN transaction_kind = 'OUT' AND transaction_type = 'BPB' THEN qty ELSE 0 END) as qty_out_bpb,
                SUM(CASE WHEN transaction_kind = 'OUT' AND transaction_type = 'ADJ OUT STO' THEN qty ELSE 0 END) as qty_out_adj
            FROM transaction_fg
            WHERE request_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY item_fg_id";

        // Combine semua kategori Delivery Notes (Periode Berjalan)
        $query_dn = "SELECT 
                item_fg_id,
                SUM(qty) as initial_out_g,
                SUM(CASE WHEN trans_type = 'SALES' THEN qty ELSE 0 END) as qty_notes_sales,
                SUM(CASE WHEN trans_type = 'RETURN' THEN qty ELSE 0 END) as qty_notes_return,
                SUM(CASE WHEN trans_type = 'SAMPLE' THEN qty ELSE 0 END) as qty_notes_sample
            FROM delivery_notes
            WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY item_fg_id";

        // Sub-Query Stok Awal (Saldo Sblm Tanggal Filter)
        /** -- existing
        $query_begin_stock = "SELECT 
                a.id,
                (COALESCE(qc2.qty, 0) + COALESCE(qnc2.qty, 0) + COALESCE(qti2.qty, 0) + COALESCE(qw2.qty, 0)) - 
                (COALESCE(qto2.qty, 0) + COALESCE(qdn2.qty, 0) + COALESCE(qrep2.qty, 0)) as begin_stock
            FROM item_fg a
            LEFT JOIN (SELECT e.item_fg_id, SUM(f.qty) as qty FROM scan_item_receipts_fg f JOIN checksheets e ON e.number = f.checksheet_number WHERE e.packing_date < '$filter_from' GROUP BY 1) qc2 ON a.id = qc2.item_fg_id
            LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM scan_item_receipts_fg WHERE type = 'NBFG' AND packing_date < '$filter_from' GROUP BY 1) qnc2 ON a.id = qnc2.item_fg_id
            LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM transaction_fg WHERE transaction_kind = 'IN' AND request_date < '$filter_from' GROUP BY 1) qti2 ON a.id = qti2.item_fg_id
            LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM transaction_fg WHERE transaction_kind = 'OUT' AND request_date < '$filter_from' GROUP BY 1) qto2 ON a.id = qto2.item_fg_id
            LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM delivery_notes WHERE delivery_note_date < '$filter_from' GROUP BY 1) qdn2 ON a.id = qdn2.item_fg_id
            LEFT JOIN (SELECT e.item_fg_id, SUM(f.qty) as qty FROM scan_repair_of_goods f JOIN repair_of_goods e ON e.document_no = f.document_no WHERE e.trans_date < '$filter_from' GROUP BY 1) qrep2 ON a.id = qrep2.item_fg_id
            LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM wip_receipts WHERE division = 'MTS' AND trans_date < '$filter_from' GROUP BY 1) qw2 ON a.id = qw2.item_fg_id";
        */
        
        // PERBAIKAN: Get data setelah cutoff_date dan sebelum filter_from untuk carry-over QTY
        $query_begin_stock = "SELECT 
            a.id,
            (COALESCE(qc2.qty, 0) + COALESCE(qnc2.qty, 0) + COALESCE(qti2.qty, 0) + COALESCE(qw2.qty, 0)) - 
            (COALESCE(qto2.qty, 0) + COALESCE(qdn2.qty, 0) + COALESCE(qrep2.qty, 0)) as begin_stock
        FROM item_fg a
        LEFT JOIN (SELECT e.item_fg_id, SUM(f.qty) as qty FROM scan_item_receipts_fg f JOIN checksheets e ON e.number = f.checksheet_number WHERE e.packing_date >= '$start_system' AND e.packing_date < '$filter_from' GROUP BY 1) qc2 ON a.id = qc2.item_fg_id
        LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM scan_item_receipts_fg WHERE type = 'NBFG' AND packing_date >= '$start_system' AND packing_date < '$filter_from' GROUP BY 1) qnc2 ON a.id = qnc2.item_fg_id
        LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM transaction_fg WHERE transaction_kind = 'IN' AND request_date >= '$start_system' AND request_date < '$filter_from' GROUP BY 1) qti2 ON a.id = qti2.item_fg_id
        LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM transaction_fg WHERE transaction_kind = 'OUT' AND request_date >= '$start_system' AND request_date < '$filter_from' GROUP BY 1) qto2 ON a.id = qto2.item_fg_id
        LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM delivery_notes WHERE delivery_note_date >= '$start_system' AND delivery_note_date < '$filter_from' GROUP BY 1) qdn2 ON a.id = qdn2.item_fg_id
        LEFT JOIN (SELECT e.item_fg_id, SUM(f.qty) as qty FROM scan_repair_of_goods f JOIN repair_of_goods e ON e.document_no = f.document_no WHERE e.trans_date >= '$start_system' AND e.trans_date < '$filter_from' GROUP BY 1) qrep2 ON a.id = qrep2.item_fg_id
        LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM wip_receipts WHERE division = 'MTS' AND trans_date >= '$start_system' AND trans_date < '$filter_from' GROUP BY 1) qw2 ON a.id = qw2.item_fg_id";


        $query_scan_repair_of_goods = "SELECT e.item_fg_id, SUM(f.qty) as initial_out_h
            FROM scan_repair_of_goods f
            JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
            WHERE DATE_FORMAT(e.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY f.item_fg_id";
        
        $query_qty_in_wip_receipt = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_wip_receipt
            FROM wip_receipts i
            WHERE i.division = 'MTS'
            AND i.trans_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY i.item_fg_id";
        
        $query_qty_in_no_checksheet = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
        FROM scan_item_receipts_fg i
        WHERE i.type = 'NBFG'
        AND i.packing_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY i.item_fg_id";

        // mengambil 'price' (standard price) dari standard_price_fg
        $query_standard_price = "SELECT item_fg_id, currency, price 
            FROM standard_price_fg 
            WHERE '$filter_from' >= `start_date` AND '$filter_to' <= `end_date` 
            GROUP BY item_fg_id";


        $query_main = "SELECT 
            a.id, a.number, a.name, a.uom, a.type,
            divs.number as division,
            COALESCE(bst.begin_stock, 0) AS begin_stock,
            COALESCE(sp.price, 0) as std_price,
            sp.currency AS standard_currency,
            
            -- Actual Price and Qty from upload
            COALESCE(actual.price, 0) as actual_price, 
            COALESCE(actual.qty, 0) as actual_qty,

            -- Mutasi Masuk
            COALESCE(qc.qty_in_non_subcont, 0) + COALESCE(tf.initial_in_rfg, 0) + COALESCE(qw.qty_in_wip_receipt, 0) as qty_rfg,
            COALESCE(tf.initial_in, 0) as adj_in_qty,
            COALESCE(tf.initial_in_adj, 0) as qty_in_adj,
            COALESCE(tf.initial_in_rfg, 0) as qty_in_rfg,
            COALESCE(qnc.qty_in_no_checksheet, 0) as qty_in_new_barcode,
            COALESCE(qc.qty_in_non_subcont, 0) as qty_in_non_subcont,
            COALESCE(qc.qty_in_subcont_jasa, 0) as qty_in_subcont_jasa,
            COALESCE(qc.qty_in_subcont_fg, 0) as qty_in_subcont_fg,
            COALESCE(qc.qty_in_repair_fg, 0) as qty_in_repair_fg,
            
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(tf.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0)) AS qty_in,

            -- Mutasi Keluar
            COALESCE(tf.qty_out, 0) + COALESCE(dn.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0) AS qty_out,
            COALESCE(dn.qty_notes_sales, 0) as qty_out_sales,
            COALESCE(dn.qty_notes_return, 0) as qty_out_return,
            COALESCE(dn.qty_notes_sample, 0) as qty_out_sample,
            COALESCE(qh.initial_out_h, 0) as qty_out_repair,
            COALESCE(tf.qty_out, 0) as adj_out_qty,
            COALESCE(tf.qty_out_bpb, 0) + COALESCE(qh.initial_out_h, 0) as qty_out_bpb,
            COALESCE(tf.qty_out_adj, 0) as qty_out_adj,

            -- End Stock
            (COALESCE(bst.begin_stock, 0) + 
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(tf.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0)) - 
            (COALESCE(tf.qty_out, 0) + COALESCE(dn.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))
            ) AS end_stock

        FROM item_fg a
        LEFT JOIN divisions divs ON a.division_id = divs.id
        LEFT JOIN inventory_fg_actual actual ON (actual.part_no = a.number OR actual.item_fg_id = a.id)
            AND actual.cutoff_date = '$start_system' -- perbaikan perhitungan begin qty per bulan
        
        LEFT JOIN ($query_standard_price) sp ON a.id = sp.item_fg_id
        LEFT JOIN ($query_checksheet) qc ON a.id = qc.item_fg_id
        LEFT JOIN ($query_trans_fg) tf ON a.id = tf.item_fg_id
        LEFT JOIN ($query_dn) dn ON a.id = dn.item_fg_id
        LEFT JOIN ($query_begin_stock) bst ON a.id = bst.id
        LEFT JOIN ($query_qty_in_no_checksheet) qnc ON a.id = qnc.item_fg_id
        LEFT JOIN ($query_scan_repair_of_goods) qh ON a.id = qh.item_fg_id
        LEFT JOIN ($query_qty_in_wip_receipt) qw ON a.id = qw.item_fg_id

        WHERE a.id LIKE '%$filter_items%' AND a.division_id LIKE '%$filter_division%'
        AND a.type LIKE '%$filter_type%'
        ORDER BY a.number";

        $records = $this->db->query($query_main)->result();

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
                <h3 style="margin:0;">INVENTORY FG STANDARD AND ACTUAL <i>' . $display_title . '</i> </h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';

        $html .= '<table id="customers" border="1" style="font-size: 11px;">
            <thead>
                <tr style="background-color: #eee;">
                    <th rowspan="5" width="20">No</th>
                    <th rowspan="5">Product No</th>
                    <th rowspan="5">Product Name</th>
                    <th rowspan="5">UOM</th>
                    <th rowspan="5">Type</th>
                    
                    <th colspan="24">SUMMARY</th>
                    <th colspan="55">DETAIL</th>
                </tr>

                <tr style="background-color:#d5d5d5;">
                    <th colspan="6" width="100">BEGIN</th>
                    <th colspan="6" width="100">IN</th>
                    <th colspan="6" width="100">OUT</th>
                    <th colspan="6" width="100">ENDING</th>

                    <th colspan="15">IN</th>
                    <th colspan="40">OUT</th>
                </tr>';

        // SUMMARY & DETAIL
        $html .= '<tr class="bg-yellow">
                <th rowspan="3" class="bg-grey">QTY</th>
                <th rowspan="2" colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th rowspan="2" colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                <th rowspan="3">VARIANCE</th>

                <th rowspan="3" class="bg-grey">QTY</th>
                <th rowspan="2" colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th rowspan="2" colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                <th rowspan="3">VARIANCE</th>

                <th rowspan="3" class="bg-grey">QTY</th>
                <th rowspan="2" colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th rowspan="2" colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                <th rowspan="3">VARIANCE</th>

                <th rowspan="3" class="bg-grey">QTY</th>
                <th rowspan="2" colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                <th rowspan="2" colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>
                <th rowspan="3">VARIANCE</th>


                <th colspan="5" >IN RFG</th>
                <th colspan="5">IN REPAIR FG</th>
                <th colspan="5">NEW BARCODE</th>
                <th colspan="5">SUBCONT FG</th>
                <th colspan="5">SUBCONT JASA</th>
                <th colspan="5">ADJ STO</th>

                <th colspan="5">OUT SJ</th>
                <th colspan="5">OUT BPB</th>
                <th colspan="5">OUT RETUR<br>TKG</th>
                <th colspan="5">OUT SAMPLE</th>
                <th colspan="5">OUT ADJ<br>(STO)</th>
            </tr>';

        $html .= '<tr>
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
            
            <th style="background-color: #D1FFC6;">PRICE</th>
            <th style="background-color: #D1FFC6;">AMOUNT</th>
            <th style="background-color: #CFE6F9;">PRICE</th>
            <th style="background-color: #CFE6F9;">AMOUNT</th>
            
            <th style="background-color: #D1FFC6;">PRICE</th>
            <th style="background-color: #D1FFC6;">AMOUNT</th>
            <th style="background-color: #CFE6F9;">PRICE</th>
            <th style="background-color: #CFE6F9;">AMOUNT</th>
        </tr>';
        $html .= '</thead><tbody>';

        $no = 1;
        $total_b_qty = 0;
        $total_i_qty = 0;
        $total_o_qty = 0;
        $total_e_qty = 0;

        $total_b_std_amount = 0;
        $total_i_std_amount = 0;
        $total_o_std_amount = 0;
        $total_e_std_amount = 0;

        $total_b_act_amount = 0;
        $total_i_act_amount = 0;
        $total_o_act_amount = 0;
        $total_e_act_amount = 0;

        // IN Details
        $total_rfg             = 0;
        $total_in_repair_fg    = 0;
        $total_in_new_barcode  = 0;
        $total_in_subcont_fg   = 0;
        $total_in_subcont_jasa = 0;
        $total_in_adj          = 0;
        
        $total_std_rfg             = 0;
        $total_std_in_repair_fg    = 0;
        $total_std_in_new_barcode  = 0;
        $total_std_in_subcont_fg   = 0;
        $total_std_in_subcont_jasa = 0;
        $total_std_in_adj          = 0;
        
        $total_act_rfg             = 0;
        $total_act_in_repair_fg    = 0;
        $total_act_in_new_barcode  = 0;
        $total_act_in_subcont_fg   = 0;
        $total_act_in_subcont_jasa = 0;
        $total_act_in_adj          = 0;
        
        // OUT Details
        $total_out_sales  = 0;
        $total_out_bpb    = 0;
        $total_out_return = 0;
        $total_out_sample = 0;
        $total_out_adj    = 0;

        $total_std_out_sales  = 0;
        $total_std_out_bpb    = 0;
        $total_std_out_return = 0;
        $total_std_out_sample = 0;
        $total_std_out_adj    = 0;

        $total_act_out_sales  = 0;
        $total_act_out_bpb    = 0;
        $total_act_out_return = 0;
        $total_act_out_sample = 0;
        $total_act_out_adj    = 0;

        foreach ($records as $record) {
            $item_fg_id  = $record->id;
            $currency = $record->standard_currency;
            $rate     = 1; // IDR

            // Get Price
            $std_price = (float)$record->std_price * $rate;
            $act_price = (float)$record->actual_price * 1; // IDR

            // Get QTY
            $actual_upload_qty = (float)$record->actual_qty;
            $mutation_before = (float)$record->begin_stock;
            $b_qty = $actual_upload_qty + $mutation_before;

            $i_qty = (float)$record->qty_in;
            $o_qty = (float)$record->qty_out;
            $e_qty = ($b_qty + $i_qty) - $o_qty;

            // Begin
            $b_std_amount = $b_qty * $std_price;
            $b_act_amount = $b_qty * $act_price;
            $b_variance   = $b_act_amount - $b_std_amount;

            // IN
            $i_std_amount = $i_qty * $std_price;
            $i_act_amount = $i_qty * $act_price;
            $i_variance   = $i_act_amount - $i_std_amount;

            // OUT
            $o_std_amount = $o_qty * $std_price;
            $o_act_amount = $o_qty * $act_price;
            $o_variance   = $o_act_amount - $o_std_amount;

            // Ending
            $e_std_amount = $e_qty * $std_price;
            $e_act_amount = $e_qty * $act_price;
            $e_variance   = $e_act_amount - $e_std_amount;


            // IN Details            
            $std_rfg             = $record->qty_rfg * $std_price;
            $std_in_repair_fg    = $record->qty_in_repair_fg * $std_price;
            $std_in_new_barcode  = $record->qty_in_new_barcode * $std_price;
            $std_in_subcont_fg   = $record->qty_in_subcont_fg * $std_price;
            $std_in_subcont_jasa = $record->qty_in_subcont_jasa * $std_price;
            $std_in_adj          = $record->qty_in_adj * $std_price;
            
            $act_rfg             = $record->qty_rfg * $act_price;
            $act_in_repair_fg    = $record->qty_in_repair_fg * $act_price;
            $act_in_new_barcode  = $record->qty_in_new_barcode * $act_price;
            $act_in_subcont_fg   = $record->qty_in_subcont_fg * $act_price;
            $act_in_subcont_jasa = $record->qty_in_subcont_jasa * $act_price;
            $act_in_adj          = $record->qty_in_adj * $act_price;
            
            // OUT Details
            $std_out_sales  = $record->qty_out_sales * $std_price;
            $std_out_bpb    = $record->qty_out_bpb * $std_price;
            $std_out_return = $record->qty_out_return * $std_price;
            $std_out_sample = $record->qty_out_sample * $std_price;
            $std_out_adj    = $record->qty_out_adj * $std_price;

            $act_out_sales  = $record->qty_out_sales * $act_price;
            $act_out_bpb    = $record->qty_out_bpb * $act_price;
            $act_out_return = $record->qty_out_return * $act_price;
            $act_out_sample = $record->qty_out_sample * $act_price;
            $act_out_adj    = $record->qty_out_adj * $act_price;

            $html .= '  <tr>
                    <td style="text-align:center">' . $no++ . '</td>
                    <td style="mso-number-format:\@;">' . $record->number . '</td>
                    <td style="mso-number-format:\@;">' . $record->name . '</td>
                    <td>' . $record->uom . '</td>
                    <td>' . $record->type . '</td>
                    
                    <td style="text-align:right;">' . number_format($b_qty, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($b_std_amount, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($b_act_amount, 2) . '</td>
                    <td style="text-align:right;">' . number_format($b_variance, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($i_qty, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($i_std_amount, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($i_act_amount, 2) . '</td>
                    <td style="text-align:right;">' . number_format($i_variance, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($o_qty, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($o_std_amount, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($o_act_amount, 2) . '</td>
                    <td style="text-align:right;">' . number_format($o_variance, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($e_qty, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($e_std_amount, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($e_act_amount, 2) . '</td>
                    <td style="text-align:right;">' . number_format($e_variance, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($record->qty_rfg, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_rfg, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_rfg, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($record->qty_in_repair_fg, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_in_repair_fg, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_in_repair_fg, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($record->qty_in_new_barcode, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_in_new_barcode, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_in_new_barcode, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($record->qty_in_subcont_fg, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_in_subcont_fg, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_in_subcont_fg, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($record->qty_in_subcont_jasa, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_in_subcont_jasa, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_in_subcont_jasa, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($record->qty_in_adj, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_in_adj, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_in_adj, 2) . '</td>


                    <td style="text-align:right;">' . number_format($record->qty_out_sales, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_out_sales, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_out_sales, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($record->qty_out_bpb, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_out_bpb, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_out_bpb, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($record->qty_out_return, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_out_return, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_out_return, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($record->qty_out_sample, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_out_sample, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_out_sample, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($record->qty_out_adj, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_out_adj, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_out_adj, 2) . '</td>
                </tr>
            </tbody>';

            // Total Summary
            $total_b_qty += $b_qty;
            $total_i_qty += $i_qty;
            $total_o_qty += $o_qty;
            $total_e_qty += $e_qty;

            $total_b_std_amount += $b_std_amount;
            $total_i_std_amount += $i_std_amount;
            $total_o_std_amount += $o_std_amount;
            $total_e_std_amount += $e_std_amount;

            $total_b_act_amount += $b_act_amount;
            $total_i_act_amount += $i_act_amount;
            $total_o_act_amount += $o_act_amount;
            $total_e_act_amount += $e_act_amount;
            
            // Total IN Details
            $total_rfg             += $record->qty_rfg;
            $total_in_repair_fg    += $record->qty_in_repair_fg;
            $total_in_new_barcode  += $record->qty_in_new_barcode;
            $total_in_subcont_fg   += $record->qty_in_subcont_fg;
            $total_in_subcont_jasa += $record->qty_in_subcont_jasa;
            $total_in_adj          += $record->qty_in_adj;

            $total_std_rfg             += $std_rfg;
            $total_std_in_repair_fg    += $std_in_repair_fg;
            $total_std_in_new_barcode  += $std_in_new_barcode;
            $total_std_in_subcont_fg   += $std_in_subcont_fg;
            $total_std_in_subcont_jasa += $std_in_subcont_jasa;
            $total_std_in_adj          += $std_in_adj;
            
            $total_act_rfg             += $act_rfg;
            $total_act_in_repair_fg    += $act_in_repair_fg;
            $total_act_in_new_barcode  += $act_in_new_barcode;
            $total_act_in_subcont_fg   += $act_in_subcont_fg;
            $total_act_in_subcont_jasa += $act_in_subcont_jasa;
            $total_act_in_adj          += $act_in_adj;
            
            // Total OUT Details
            $total_out_sales  += $record->qty_out_sales;
            $total_out_bpb    += $record->qty_out_bpb;
            $total_out_return += $record->qty_out_return;
            $total_out_sample += $record->qty_out_sample;
            $total_out_adj    += $record->qty_out_adj;

            $total_std_out_sales  += $std_out_sales;
            $total_std_out_bpb    += $std_out_bpb;
            $total_std_out_return += $std_out_return;
            $total_std_out_sample += $std_out_sample;
            $total_std_out_adj    += $std_out_adj;

            $total_act_out_sales  += $act_out_sales;
            $total_act_out_bpb    += $act_out_bpb;
            $total_act_out_return += $act_out_return;
            $total_act_out_sample += $act_out_sample;
            $total_act_out_adj    += $act_out_adj;
        }

        $html .= '<tfooter>
        <tr style="font-weight:bold;">
            <td colspan="5" style="text-align:right;"><b>GRAND TOTAL</b></td>
            <td style="text-align:right;">' . number_format($total_b_qty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_b_std_amount, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_b_act_amount, 2) . '</td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($total_i_qty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_i_std_amount, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_i_act_amount, 2) . '</td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($total_o_qty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_o_std_amount, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_o_act_amount, 2) . '</td>
            <td style="text-align:right;"></td>

            <td style="text-align:right;">' . number_format($total_e_qty, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_e_std_amount, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_e_act_amount, 2) . '</td>
            <td style="text-align:right;"></td>


            <td style="text-align:right;">' . number_format($total_rfg, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_std_rfg, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_act_rfg, 2) . '</td>

            <td style="text-align:right;">' . number_format($total_in_repair_fg, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_std_in_repair_fg, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_act_in_repair_fg, 2) . '</td>

            <td style="text-align:right;">' . number_format($total_in_new_barcode, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_std_in_new_barcode, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_act_in_new_barcode, 2) . '</td>

            <td style="text-align:right;">' . number_format($total_in_subcont_fg, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_std_in_subcont_fg, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_act_in_subcont_fg, 2) . '</td>

            <td style="text-align:right;">' . number_format($total_in_subcont_jasa, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_std_in_subcont_jasa, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_act_in_subcont_jasa, 2) . '</td>

            <td style="text-align:right;">' . number_format($total_in_adj, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_std_in_adj, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_act_in_adj, 2) . '</td>
            
            <td style="text-align:right;">' . number_format($total_out_sales, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_std_out_sales, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_act_out_sales, 2) . '</td>

            <td style="text-align:right;">' . number_format($total_out_bpb, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_std_out_bpb, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_act_out_bpb, 2) . '</td>

            <td style="text-align:right;">' . number_format($total_out_return, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_std_out_return, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_act_out_return, 2) . '</td>

            <td style="text-align:right;">' . number_format($total_out_sample, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_std_out_sample, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_act_out_sample, 2) . '</td>

            <td style="text-align:right;">' . number_format($total_out_adj, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_std_out_adj, 2) . '</td>
            <td style="text-align:right;"></td>
            <td style="text-align:right;">' . number_format($total_act_out_adj, 2) . '</td>
        </tr>
        </tfooter>';

        $html .= '</table></body></html>';
        echo $html;
    }


    public function print_detail($option = "")
    {
        set_time_limit(300);

        if (!$this->db->table_exists('inventory_fg_actual')) {
            echo "<pre> Database Error: Tabel Inventory FG Actual not found! Please contact admin.</pre>";
            return false;
        }

        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_fg_$format.xls");
        }

        $filter_from        = $this->input->get('filter_from');
        $filter_to          = $this->input->get('filter_to');
        $filter_items       = $this->input->get('filter_items');
        $filter_division    = $this->input->get("filter_division");
        $filter_display     = $this->input->get("filter_display");
        $filter_type        = $this->input->get("filter_type");
        $filter_trans_type  = $this->input->get("filter_trans_type");
        
        $start  = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $display_title = ($filter_display == "DETAIL") ? '(DETAIL)' : '(RECAP)';

        // Config Logo & Name
        $config = $this->db->get('config')->row();

        // Get Exchange Rates
        $rates = $this->db->get_where('standard_exchange_rates', ['currency_from' => 'USD'])->result();
        $get_rate = function($date) use ($rates) {
            if (empty($date)) return 1.0;
            foreach ($rates as $r) {
                if ($date >= $r->start_date && $date <= $r->end_date) return (float)$r->middle;
            }
            return 1.0;
        };

        // Get cutoff_date terbaru yang tidak melebihi filter_from
        $cutoff_data  = $this->db->select('cutoff_date')
                        ->where('cutoff_date <=', $filter_from)
                        ->order_by('cutoff_date', 'DESC')
                        ->limit(1)
                        ->get('inventory_fg_actual')
                        ->row();
        $start_system = ($cutoff_data) ? $cutoff_data->cutoff_date : '2026-01-01';

        //------------------------------------ GET DATA ----------------------------------//

        // Combine semua kategori Checksheet (Periode Berjalan)
        $query_checksheet = "SELECT 
                e.item_fg_id, 
                SUM(f.qty) as qty_in_checksheet,
                SUM(CASE WHEN e.status_subcont = 'NO' AND e.wo_no NOT LIKE '%RG-%' THEN f.qty ELSE 0 END) as qty_in_non_subcont,
                SUM(CASE WHEN e.subcont_type = 'Jasa' AND e.wo_no NOT LIKE '%RG-%' THEN f.qty ELSE 0 END) as qty_in_subcont_jasa,
                SUM(CASE WHEN e.subcont_type = 'Finished Good' AND e.wo_no NOT LIKE '%RG-%' THEN f.qty ELSE 0 END) as qty_in_subcont_fg,
                SUM(CASE WHEN e.wo_no LIKE '%RG-%' THEN f.qty ELSE 0 END) as qty_in_repair_fg
            FROM scan_item_receipts_fg f
            JOIN checksheets e ON e.number = f.checksheet_number
            WHERE e.packing_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY e.item_fg_id";

        // Combine semua kategori Transaction FG (Periode Berjalan)
        $query_trans_fg = "SELECT 
                item_fg_id,
                SUM(CASE WHEN transaction_kind = 'IN' THEN qty ELSE 0 END) as initial_in,
                SUM(CASE WHEN transaction_kind = 'IN' AND transaction_type = 'ADJ IN STO' THEN qty ELSE 0 END) as initial_in_adj,
                SUM(CASE WHEN transaction_kind = 'IN' AND transaction_type = 'RECEIPT FG' THEN qty ELSE 0 END) as initial_in_rfg,
                SUM(CASE WHEN transaction_kind = 'OUT' THEN qty ELSE 0 END) as qty_out,
                SUM(CASE WHEN transaction_kind = 'OUT' AND transaction_type = 'BPB' THEN qty ELSE 0 END) as qty_out_bpb,
                SUM(CASE WHEN transaction_kind = 'OUT' AND transaction_type = 'ADJ OUT STO' THEN qty ELSE 0 END) as qty_out_adj
            FROM transaction_fg
            WHERE request_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY item_fg_id";

        // Combine semua kategori Delivery Notes (Periode Berjalan)
        $query_dn = "SELECT 
                item_fg_id,
                SUM(qty) as initial_out_g,
                SUM(CASE WHEN trans_type = 'SALES' THEN qty ELSE 0 END) as qty_notes_sales,
                SUM(CASE WHEN trans_type = 'RETURN' THEN qty ELSE 0 END) as qty_notes_return,
                SUM(CASE WHEN trans_type = 'SAMPLE' THEN qty ELSE 0 END) as qty_notes_sample
            FROM delivery_notes
            WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY item_fg_id";

        // Sub-Query Stok Awal (Saldo Sblm Tanggal Filter)
        /** -- existing
        $query_begin_stock = "SELECT 
                a.id,
                (COALESCE(qc2.qty, 0) + COALESCE(qnc2.qty, 0) + COALESCE(qti2.qty, 0) + COALESCE(qw2.qty, 0)) - 
                (COALESCE(qto2.qty, 0) + COALESCE(qdn2.qty, 0) + COALESCE(qrep2.qty, 0)) as begin_stock
            FROM item_fg a
            LEFT JOIN (SELECT e.item_fg_id, SUM(f.qty) as qty FROM scan_item_receipts_fg f JOIN checksheets e ON e.number = f.checksheet_number WHERE e.packing_date < '$filter_from' GROUP BY 1) qc2 ON a.id = qc2.item_fg_id
            LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM scan_item_receipts_fg WHERE type = 'NBFG' AND packing_date < '$filter_from' GROUP BY 1) qnc2 ON a.id = qnc2.item_fg_id
            LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM transaction_fg WHERE transaction_kind = 'IN' AND request_date < '$filter_from' GROUP BY 1) qti2 ON a.id = qti2.item_fg_id
            LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM transaction_fg WHERE transaction_kind = 'OUT' AND request_date < '$filter_from' GROUP BY 1) qto2 ON a.id = qto2.item_fg_id
            LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM delivery_notes WHERE delivery_note_date < '$filter_from' GROUP BY 1) qdn2 ON a.id = qdn2.item_fg_id
            LEFT JOIN (SELECT e.item_fg_id, SUM(f.qty) as qty FROM scan_repair_of_goods f JOIN repair_of_goods e ON e.document_no = f.document_no WHERE e.trans_date < '$filter_from' GROUP BY 1) qrep2 ON a.id = qrep2.item_fg_id
            LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM wip_receipts WHERE division = 'MTS' AND trans_date < '$filter_from' GROUP BY 1) qw2 ON a.id = qw2.item_fg_id";
        */
        
        // PERBAIKAN: Get data setelah cutoff_date dan sebelum filter_from untuk carry-over QTY
        $query_begin_stock = "SELECT 
            a.id,
            (COALESCE(qc2.qty, 0) + COALESCE(qnc2.qty, 0) + COALESCE(qti2.qty, 0) + COALESCE(qw2.qty, 0)) - 
            (COALESCE(qto2.qty, 0) + COALESCE(qdn2.qty, 0) + COALESCE(qrep2.qty, 0)) as begin_stock
        FROM item_fg a
        LEFT JOIN (SELECT e.item_fg_id, SUM(f.qty) as qty FROM scan_item_receipts_fg f JOIN checksheets e ON e.number = f.checksheet_number WHERE e.packing_date >= '$start_system' AND e.packing_date < '$filter_from' GROUP BY 1) qc2 ON a.id = qc2.item_fg_id
        LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM scan_item_receipts_fg WHERE type = 'NBFG' AND packing_date >= '$start_system' AND packing_date < '$filter_from' GROUP BY 1) qnc2 ON a.id = qnc2.item_fg_id
        LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM transaction_fg WHERE transaction_kind = 'IN' AND request_date >= '$start_system' AND request_date < '$filter_from' GROUP BY 1) qti2 ON a.id = qti2.item_fg_id
        LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM transaction_fg WHERE transaction_kind = 'OUT' AND request_date >= '$start_system' AND request_date < '$filter_from' GROUP BY 1) qto2 ON a.id = qto2.item_fg_id
        LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM delivery_notes WHERE delivery_note_date >= '$start_system' AND delivery_note_date < '$filter_from' GROUP BY 1) qdn2 ON a.id = qdn2.item_fg_id
        LEFT JOIN (SELECT e.item_fg_id, SUM(f.qty) as qty FROM scan_repair_of_goods f JOIN repair_of_goods e ON e.document_no = f.document_no WHERE e.trans_date >= '$start_system' AND e.trans_date < '$filter_from' GROUP BY 1) qrep2 ON a.id = qrep2.item_fg_id
        LEFT JOIN (SELECT item_fg_id, SUM(qty) as qty FROM wip_receipts WHERE division = 'MTS' AND trans_date >= '$start_system' AND trans_date < '$filter_from' GROUP BY 1) qw2 ON a.id = qw2.item_fg_id";


        $query_scan_repair_of_goods = "SELECT e.item_fg_id, SUM(f.qty) as initial_out_h
            FROM scan_repair_of_goods f
            JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
            WHERE DATE_FORMAT(e.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY f.item_fg_id";
        
        $query_qty_in_wip_receipt = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_wip_receipt
            FROM wip_receipts i
            WHERE i.division = 'MTS'
            AND i.trans_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY i.item_fg_id";
        
        $query_qty_in_no_checksheet = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
        FROM scan_item_receipts_fg i
        WHERE i.type = 'NBFG'
        AND i.packing_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY i.item_fg_id";

        // mengambil 'price' (standard price) dari standard_price_fg
        $query_standard_price = "SELECT item_fg_id, currency, price 
            FROM standard_price_fg 
            WHERE '$filter_from' >= `start_date` AND '$filter_to' <= `end_date` 
            GROUP BY item_fg_id";


        $query_main = "SELECT 
            a.id, a.number, a.name, a.uom, a.type,
            divs.number as division,
            COALESCE(bst.begin_stock, 0) AS begin_stock,
            COALESCE(sp.price, 0) as std_price,
            sp.currency AS standard_currency,
            
            -- Actual Price and Qty from upload
            COALESCE(actual.price, 0) as actual_price, 
            COALESCE(actual.qty, 0) as actual_qty,
            actual.currency as upload_currency,
            actual.created_by as upload_by,
            actual.upload_date,

            -- Mutasi Masuk
            COALESCE(qc.qty_in_non_subcont, 0) + COALESCE(tf.initial_in_rfg, 0) + COALESCE(qw.qty_in_wip_receipt, 0) as qty_rfg,
            COALESCE(tf.initial_in, 0) as adj_in_qty,
            COALESCE(tf.initial_in_adj, 0) as qty_in_adj,
            COALESCE(tf.initial_in_rfg, 0) as qty_in_rfg,
            COALESCE(qnc.qty_in_no_checksheet, 0) as qty_in_new_barcode,
            COALESCE(qc.qty_in_non_subcont, 0) as qty_in_non_subcont,
            COALESCE(qc.qty_in_subcont_jasa, 0) as qty_in_subcont_jasa,
            COALESCE(qc.qty_in_subcont_fg, 0) as qty_in_subcont_fg,
            COALESCE(qc.qty_in_repair_fg, 0) as qty_in_repair_fg,
            
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(tf.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0)) AS qty_in,

            -- Mutasi Keluar
            COALESCE(tf.qty_out, 0) + COALESCE(dn.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0) AS qty_out,
            COALESCE(dn.qty_notes_sales, 0) as qty_out_sales,
            COALESCE(dn.qty_notes_return, 0) as qty_out_return,
            COALESCE(dn.qty_notes_sample, 0) as qty_out_sample,
            COALESCE(qh.initial_out_h, 0) as qty_out_repair,
            COALESCE(tf.qty_out, 0) as adj_out_qty,
            COALESCE(tf.qty_out_bpb, 0) + COALESCE(qh.initial_out_h, 0) as qty_out_bpb,
            COALESCE(tf.qty_out_adj, 0) as qty_out_adj,

            -- End Stock
            (COALESCE(bst.begin_stock, 0) + 
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(tf.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0)) - 
            (COALESCE(tf.qty_out, 0) + COALESCE(dn.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))
            ) AS end_stock

        FROM item_fg a
        LEFT JOIN divisions divs ON a.division_id = divs.id
        LEFT JOIN inventory_fg_actual actual ON (actual.part_no = a.number OR actual.item_fg_id = a.id)
            AND actual.cutoff_date = '$start_system' -- perbaikan perhitungan begin qty per bulan
        
        LEFT JOIN ($query_standard_price) sp ON a.id = sp.item_fg_id
        LEFT JOIN ($query_checksheet) qc ON a.id = qc.item_fg_id
        LEFT JOIN ($query_trans_fg) tf ON a.id = tf.item_fg_id
        LEFT JOIN ($query_dn) dn ON a.id = dn.item_fg_id
        LEFT JOIN ($query_begin_stock) bst ON a.id = bst.id
        LEFT JOIN ($query_qty_in_no_checksheet) qnc ON a.id = qnc.item_fg_id
        LEFT JOIN ($query_scan_repair_of_goods) qh ON a.id = qh.item_fg_id
        LEFT JOIN ($query_qty_in_wip_receipt) qw ON a.id = qw.item_fg_id

        WHERE a.id LIKE '%$filter_items%' AND a.division_id LIKE '%$filter_division%'
        AND a.type LIKE '%$filter_type%'
        ORDER BY a.number";

        $records = $this->db->query($query_main)->result();

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
                <h3 style="margin:0;">INVENTORY FG STANDARD AND ACTUAL <i>' . $display_title . '</i> </h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';

        // Build Table Header
        $html .= '<table id="customers" border="1" style="font-size: 11px;">
            <thead>
                <tr style="background-color:#eee;">
                    <th rowspan="4" width="20">No</th>
                    <th rowspan="4" colspan="3">Product No</th>
                    <th rowspan="4" colspan="2">Product Name</th>
                    <th rowspan="4">Uom</th>
                    <th rowspan="4">Division</th>
                    <th rowspan="4">Product Family</th>
                    <th rowspan="4">Type</th>
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
        $total_b_qty = 0;
        $total_i_qty = 0;
        $total_o_qty = 0;
        $total_e_qty = 0;

        $total_b_std_amount = 0;
        $total_i_std_amount = 0;
        $total_o_std_amount = 0;
        $total_e_std_amount = 0;

        $total_b_act_amount = 0;
        $total_i_act_amount = 0;
        $total_o_act_amount = 0;
        $total_e_act_amount = 0;

        $grandtotals = [
            'b_qty' => 0, 'b_std' => 0, 'b_act' => 0,
            'i_qty' => 0, 'i_std' => 0, 'i_act' => 0,
            'o_qty' => 0, 'o_std' => 0, 'o_act' => 0,
            'e_qty' => 0, 'e_std' => 0, 'e_act' => 0,
        ];

        // OPTIMASI: Get All Data Receipts
        $all_receipts = $this->db->query("SELECT f.*, c.name as username, e.packing_date as trans_date, e.item_fg_id
            FROM scan_item_receipts_fg f
            JOIN checksheets e ON e.number = f.checksheet_number
            LEFT JOIN users c ON f.created_by = c.username
            WHERE e.packing_date BETWEEN '$filter_from' AND '$filter_to'")
            ->result();

        // Mapping data berdasarkan item_fg_id agar mudah diakses di dalam loop
        $receipts_by_item = [];
        foreach ($all_receipts as $r) {
            $receipts_by_item[$r->item_fg_id][] = $r;
        }


        foreach ($records as $record) {
            $item_fg_id  = $record->id;
            $currency = $record->standard_currency;
            $rate     = 1; // IDR

            // Get Price
            $std_price = (float)$record->std_price * $rate;
            $act_price = (float)$record->actual_price * $rate;

            // Get QTY
            $actual_upload_qty = (float)$record->actual_qty;
            $mutation_before = (float)$record->begin_stock;
            $b_qty = $actual_upload_qty + $mutation_before;

            $i_qty = (float)$record->qty_in;
            $o_qty = (float)$record->qty_out;
            $e_qty = ($b_qty + $i_qty) - $o_qty;

            // Begin
            $b_std_amount = $b_qty * $std_price;
            $b_act_amount = $b_qty * $act_price;
            $b_variance   = $b_act_amount - $b_std_amount;

            // IN
            $i_std_amount = $i_qty * $std_price;
            $i_act_amount = $i_qty * $act_price;
            $i_variance   = $i_act_amount - $i_std_amount;

            // OUT
            $o_std_amount = $o_qty * $std_price;
            $o_act_amount = $o_qty * $act_price;
            $o_variance   = $o_act_amount - $o_std_amount;

            // Ending
            $e_std_amount = $e_qty * $std_price;
            $e_act_amount = $e_qty * $act_price;
            $e_variance   = $e_act_amount - $e_std_amount;


            $html .= '  <tr>
                    <td style="text-align:center">' . $no++ . '</td>
                    <td colspan="3" style="mso-number-format:\@;">' . $record->number . '</td>
                    <td colspan="2" style="mso-number-format:\@;">' . $record->name . '</td>
                    <td>' . $record->uom . '</td>
                    <td>' . $record->division . '</td>
                    <td>FINISH GOOD</td>
                    <td>' . $record->type . '</td>
                    <td style="text-align:center;">' . $record->standard_currency . '</td>
                    <td style="text-align:right;">' . number_format($rate, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($b_qty, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($b_std_amount, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($b_act_amount, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($i_qty, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($i_std_amount, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($i_act_amount, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($o_qty, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($o_std_amount, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($o_act_amount, 2) . '</td>
                    
                    <td style="text-align:right;">' . number_format($e_qty, 2) . '</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($e_std_amount, 2) . '</td>
                    <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                    <td style="text-align:right;">' . number_format($e_act_amount, 2) . '</td>
                </tr>';

            // Total Summary
            $total_b_qty += $b_qty;
            $total_i_qty += $i_qty;
            $total_o_qty += $o_qty;
            $total_e_qty += $e_qty;

            $total_b_std_amount += $b_std_amount;
            $total_i_std_amount += $i_std_amount;
            $total_o_std_amount += $o_std_amount;
            $total_e_std_amount += $e_std_amount;

            $total_b_act_amount += $b_act_amount;
            $total_i_act_amount += $i_act_amount;
            $total_o_act_amount += $o_act_amount;
            $total_e_act_amount += $e_act_amount;


            // DETAILS
            /** OPTIMASI: dipindah ke sebelum loop karena error Maximum execution time of 120 seconds 
            $receipts = $this->crud->query("SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                    FROM scan_item_receipts_fg f
                    JOIN checksheets e ON e.number = f.checksheet_number
                    LEFT JOIN users c ON f.created_by = c.username
                    WHERE e.item_fg_id = '$item_fg_id' 
                    and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");
            */
            // Get data dari array hasi mapping
            $receipts = isset($receipts_by_item[$item_fg_id]) ? $receipts_by_item[$item_fg_id] : [];

            $receiptsNB = $this->crud->query("SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
                    FROM new_barcode_fg a
                    LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                    LEFT JOIN users u ON f.created_by = u.username
                    WHERE a.item_fg_id = '$item_fg_id' 
                    AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

            $receiptsWIP = $this->crud->query("SELECT a.*, u.name as username, 'RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                    FROM wip_receipts a
                    LEFT JOIN users u ON a.created_by = u.username
                    WHERE a.item_fg_id = '$item_fg_id' AND a.division = 'MTS'
                    AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

            $delivery_notes = $this->crud->query("SELECT a.*, d.name AS username, so.price as act_price
                FROM delivery_notes a
                JOIN users d ON a.created_by = d.username
                LEFT JOIN sales_orders so ON so.sales_order_no = a.sales_order_no
                WHERE a.item_fg_id = '$item_fg_id'
                AND a.delivery_note_date BETWEEN '$filter_from' AND '$filter_to'");

            $transactions = $this->crud->query("SELECT
                a.request_date,
                a.transaction_type,
                a.transaction_kind,
                a.request_no,
                a.qty,
                b.name AS username
                FROM transaction_fg a
                JOIN users b ON a.created_by = b.username
                WHERE a.item_fg_id = '$item_fg_id'
                AND a.request_date BETWEEN '$filter_from' AND '$filter_to'");

            $scan_repair_of_goods = $this->crud->query("SELECT f.wo_no, 
                f.document_no, 
                f.qty, 
                c.name AS username, 
                e.trans_date AS trans_date, 
                'REPAIR OF GOODS' AS receipt_type
                FROM scan_repair_of_goods f
                LEFT JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
                LEFT JOIN users c ON f.created_by = c.username
                WHERE f.item_fg_id = '$item_fg_id'
                AND DATE_FORMAT(e.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");

            // Proses data berdasarkan tanggal
            $all_data = [];

            // Gabungkan data receipts
            foreach ($receipts as $receipt) {
                $all_data[] = [
                    'type'      => $receipt->type,
                    'username'  => $receipt->username,
                    'date'      => $receipt->trans_date,
                    'wo_no'     => $receipt->wo_no,
                    'label'     => $receipt->checksheet_label,
                    'qty_in'    => $receipt->qty,
                    'qty_out'   => 0,
                    'price'     => 0,
                ];
            }

            foreach ($receiptsNB as $receiptNB) {
                $all_data[] = [
                    'type'      => $receiptNB->receipt_type,
                    'username'  => $receiptNB->username,
                    'date'      => $receiptNB->trans_date,
                    'wo_no'     => $receiptNB->wo_no,
                    'label'     => $receiptNB->checksheet_label,
                    'qty_in'    => $receiptNB->qty,
                    'qty_out'   => 0,
                    'price'     => 0,
                ];
            }

            foreach ($receiptsWIP as $receiptWIP) {
                $all_data[] = [
                    'type'      => $receiptWIP->receipt_type,
                    'username'  => $receiptWIP->username,
                    'date'      => $receiptWIP->trans_date,
                    'wo_no'     => $receiptWIP->wo_no,
                    'label'     => $receiptWIP->checksheet_label,
                    'qty_in'    => $receiptWIP->qty,
                    'qty_out'   => 0,
                    'price'     => 0,
                ];
            }

            // Gabungkan data delivery notes
            foreach ($delivery_notes as $delivery_note) {
                $all_data[] = [
                    'type'      => 'DELIVERY NOTE',
                    'username'  => $delivery_note->username,
                    'date'      => $delivery_note->delivery_note_date,
                    'wo_no'     => $delivery_note->delivery_order_no,
                    'label'     => $delivery_note->delivery_note_no,
                    'qty_in'    => 0,
                    'qty_out'   => $delivery_note->qty,
                    'price'     => $delivery_note->act_price,
                ];
            }

            // Gabungkan data transactions
            foreach ($transactions as $transaction) {
                $all_data[] = [
                    'type'      => $transaction->transaction_type,
                    'username'  => $transaction->username,
                    'date'      => $transaction->request_date,
                    'wo_no'     => '-',
                    'label'     => $transaction->request_no,
                    'qty_in'    => $transaction->transaction_kind == 'IN' ? $transaction->qty : 0,
                    'qty_out'   => $transaction->transaction_kind == 'OUT' ? $transaction->qty : 0,
                    'price'     => 0,
                ];
            }

            foreach ($scan_repair_of_goods as $scan_repair_of_good) {
                $all_data[] = [
                    'type'      => $scan_repair_of_good->receipt_type,
                    'username'  => $scan_repair_of_good->username,
                    'date'      => $scan_repair_of_good->trans_date,
                    'wo_no'     => $scan_repair_of_good->wo_no,
                    'label'     => $scan_repair_of_good->document_no,
                    'qty_in'    => 0,
                    'qty_out'   => $scan_repair_of_good->qty,
                    'price'     => 0,
                ];
            }

            usort($all_data, function ($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });

            $html .= '  <tr>
                            <td colspan="32" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                        </tr>';
            $html .= '<thead>
                    <tr>
                        <th rowspan="3" width="20"></th>
                        <th rowspan="3" width="20">No</th>
                        <th rowspan="3">Trans Type</th>
                        <th rowspan="3">Created By</th>
                        <th rowspan="3">Trans Date</th>
                        <th rowspan="3">WO / DO</th>
                        <th rowspan="3" colspan="3" >Doc. No</th>
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

            // PERBAIKAN: Saldo awal baris detail adalah saldo upload + mutasi carry-over sebelum filter_from
            $actual_upload_qty = (float)$record->actual_qty;
            $mutation_before   = (float)$record->begin_stock;
            $running_qty_bal = $actual_upload_qty + $mutation_before;

            // Akumulasi ke Grand Total (Kolom BEGIN)
            $grandtotals['b_qty'] += $running_qty_bal;
            $grandtotals['b_std'] += ($running_qty_bal * $std_price);
            $grandtotals['b_act'] += ($running_qty_bal * $act_price);

            $is_upload_month = (date('Y-m', strtotime($start_system)) == date('Y-m', strtotime($filter_from)));

            // Tampilkan Baris Kuning HANYA jika ini adalah bulan Upload
            if ($is_upload_month) {
                $html .= '<tr style="background: #fffbf1">
                        <td></td>
                        <td style="text-align:center">#</td>
                        <td>UPLOAD</td>
                        <td>' . $record->upload_by . '</td>
                        <td>' . $record->upload_date . '</td>
                        <td>-</td>
                        <td colspan="3">BEGIN BALANCE (UPLOAD)</td>
                        <td style="text-align:center;">' . $record->upload_currency . '</td>
                        <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($rate, 2) . '</td>

                        <td style="text-align:right;">' . number_format($actual_upload_qty, 2) . '</td>
                        <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($actual_upload_qty * $std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($actual_upload_qty * $act_price, 2) . '</td>

                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>

                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>
                        <td style="text-align:right;">0.00</td>

                        <td style="text-align:right;">' . number_format($actual_upload_qty, 2) . '</td>
                        <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($actual_upload_qty * $std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($actual_upload_qty * $act_price, 2) . '</td>
                    </tr>';
            } 

            $nod = 1;
            foreach ($all_data as $data) {
                $trans_in  = (float)$data['qty_in'];
                $trans_out = (float)$data['qty_out'];
                
                // Simpan saldo sebelum transaksi untuk kolom "BEGIN" di baris ini
                $row_begin_qty = $running_qty_bal;
                
                // Update Running Balance untuk baris ini
                $running_qty_bal += ($trans_in - $trans_out);

                $html .= '<tr>
                        <td></td>
                        <td style="text-align:center">' . $nod . '</td>
                        <td>' . $data['type'] . '</td>
                        <td>' . $data['username'] . '</td>
                        <td>' . $data['date'] . '</td>
                        <td>' . ($data['wo_no'] ?: '-') . '</td>
                        <td colspan="3">' . $data['label'] . '</td>
                        <td style="text-align:center;">' . $currency . '</td>
                        <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($rate, 2) . '</td>

                        <td style="text-align:right;">' . number_format($row_begin_qty, 2) . '</td>
                        <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($row_begin_qty * $std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($row_begin_qty * $act_price, 2) . '</td>

                        <td style="text-align:right;">' . number_format($trans_in, 2) . '</td>
                        <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($trans_in * $std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($trans_in * $act_price, 2) . '</td>

                        <td style="text-align:right;">' . number_format($trans_out, 2) . '</td>
                        <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($trans_out * $std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($trans_out * $act_price, 2) . '</td>

                        <td style="text-align:right; font-weight:bold;">' . number_format($running_qty_bal, 2) . '</td>
                        <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($running_qty_bal * $std_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                        <td style="text-align:right;">' . number_format($running_qty_bal * $act_price, 2) . '</td>
                    </tr>';
                $nod++;

                // Akumulasi ke Grand Total (IN & OUT)
                $grandtotals['i_qty'] += $trans_in;
                $grandtotals['i_std'] += ($trans_in * $std_price);
                $grandtotals['i_act'] += ($trans_in * $act_price);
                $grandtotals['o_qty'] += $trans_out;
                $grandtotals['o_std'] += ($trans_out * $std_price);
                $grandtotals['o_act'] += ($trans_out * $act_price);
            }

            // Akumulasi ke Grand Total (Kolom ENDING/BALANCE)
            $grandtotals['e_qty'] += $running_qty_bal;
            $grandtotals['e_std'] += ($running_qty_bal * $std_price);
            $grandtotals['e_act'] += ($running_qty_bal * $act_price);
        }

       $html .= '<tfooter>
            <tr style="background:#eee; font-weight:bold;">
                <td colspan="12" align="right">GRAND TOTAL</td>
                <td align="right">' . number_format($grandtotals['b_qty'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format($grandtotals['b_std'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format($grandtotals['b_act'], 2) . '</td>

                <td align="right">' . number_format($grandtotals['i_qty'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format($grandtotals['i_std'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format($grandtotals['i_act'], 2) . '</td>

                <td align="right">' . number_format($grandtotals['o_qty'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format($grandtotals['o_std'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format($grandtotals['o_act'], 2) . '</td>

                <td align="right">' . number_format($grandtotals['e_qty'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format($grandtotals['e_std'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format($grandtotals['e_act'], 2) . '</td>
            </tr>
        </tfooter>';

        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_detail_without_actual($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=inventory_fg_standard_actual_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get("filter_division");
        $filter_type = $this->input->get("filter_type");

        $start = strtotime($filter_from);
        $finish = strtotime($filter_to);

        $display_title = ($filter_display == "DETAIL") ? '(DETAIL)' : '(RECAP)';

        // Config Logo & Name
        $config = $this->db->get('config')->row();

        //------------------------------------ GET DATA ----------------------------------//
        
        // mengambil 'price' (standard price) dari standard_price_fg
        $query_standard_price = "SELECT item_fg_id, currency, price 
        FROM standard_price_fg 
        WHERE '$filter_from' >= `start_date` AND '$filter_to' <= `end_date` 
        GROUP BY item_fg_id";

        // Step 1: Hitung qty_in dari checksheet
        $query_qty_in_checksheet = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_checksheet
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY e.item_fg_id";

        // Step 2: Hitung qty_in tanpa checksheet
        $query_qty_in_no_checksheet = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
        FROM scan_item_receipts_fg i
        WHERE i.type = 'NBFG'
        AND i.packing_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY i.item_fg_id";

        // Step 3: Hitung initial `i` dari transaction_fg (kind IN)
        $query_transaction_fg_in = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        // Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT'
        AND a.request_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY a.item_fg_id";

        // Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes = "SELECT item_fg_id, SUM(qty) as initial_out_g
        FROM delivery_notes
        WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY item_fg_id";

        // Step 6: Hitung initial `h` (scan_repair_of_goods)
        $query_scan_repair_of_goods = "SELECT e.item_fg_id, SUM(f.qty) as initial_out_h
        FROM scan_repair_of_goods f
        JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
        WHERE DATE_FORMAT(e.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY f.item_fg_id";

        // Step 7: Hitung qty_in WIP division MTS
        $query_qty_in_wip_receipt = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_wip_receipt
        FROM wip_receipts i
        WHERE i.division = 'MTS'
        AND i.trans_date BETWEEN '$filter_from' AND '$filter_to'
        GROUP BY i.item_fg_id";

        //-----------------------------------------------------------------

        $query_qty_in_checksheet2 = "SELECT e.item_fg_id, SUM(f.qty) as qty_in_checksheet
        FROM scan_item_receipts_fg f
        JOIN checksheets e ON e.number = f.checksheet_number
        WHERE DATE_FORMAT(e.packing_date, '%Y-%m-%d') < '$filter_from'
        GROUP BY e.item_fg_id";

        // Step 2: Hitung qty_in tanpa checksheet
        $query_qty_in_no_checksheet2 = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_no_checksheet
        FROM scan_item_receipts_fg i
        WHERE i.type = 'NBFG'
        AND i.packing_date < '$filter_from'
        GROUP BY i.item_fg_id";

        // Step 3: Hitung initial `i` dari transaction_fg (kind IN)
        $query_transaction_fg_in2 = "SELECT a.item_fg_id, SUM(a.qty) as initial_in
        FROM transaction_fg a
        WHERE a.transaction_kind = 'IN'
        AND a.request_date < '$filter_from'
        GROUP BY a.item_fg_id";

        // Step 4: Hitung qty_out dari transaction_fg
        $query_qty_out2 = "SELECT a.item_fg_id, SUM(a.qty) as qty_out
        FROM transaction_fg a
        WHERE a.transaction_kind = 'OUT'
        AND a.request_date < '$filter_from'
        GROUP BY a.item_fg_id";

        // Step 5: Hitung initial `g` (delivery_notes)
        $query_delivery_notes2 = "SELECT item_fg_id, SUM(qty) as initial_out_g
        FROM delivery_notes
        WHERE delivery_note_date < '$filter_from'
        GROUP BY item_fg_id";

        // Step 6: Hitung initial `h` (scan_repair_of_goods)
        $query_scan_repair_of_goods2 = "SELECT e.item_fg_id, SUM(f.qty) as initial_out_h
        FROM scan_repair_of_goods f
        JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
        WHERE DATE_FORMAT(e.trans_date, '%Y-%m-%d') < '$filter_from'
        GROUP BY f.item_fg_id";

        // Step 8: Hitung qty_in WIP division MTS
        $query_qty_in_wip_receipt2 = "SELECT i.item_fg_id, SUM(i.qty) as qty_in_wip_receipt
        FROM wip_receipts i
        WHERE i.division = 'MTS'
        AND i.trans_date < '$filter_from'
        GROUP BY i.item_fg_id";

        // Step 9: Gabungan query
        $query_main = "SELECT 
            a.id, 
            a.number, 
            a.name, 
            a.uom,
            a.type,
            xy.number as division,
            COALESCE(aa.price,0) as price,
            COALESCE(aa.currency,'-') as currency,
            COALESCE(x.begin_stock,0) AS begin_stock,
            COALESCE(sp.price, 0) as std_price,

            COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) AS qty_in,
            
            COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0) AS qty_out,
            
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) - 
            (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS end_stock
        FROM item_fg a
        LEFT JOIN divisions xy on a.division_id = xy.id
        LEFT JOIN (SELECT item_fg_id, currency, price from standard_price_fg where '$filter_from' >= `start_date` and '$filter_to' <= `end_date`) aa on a.id = aa.item_fg_id
        LEFT JOIN ($query_standard_price) sp ON a.id = sp.item_fg_id
        LEFT JOIN ($query_qty_in_checksheet) qc ON a.id = qc.item_fg_id
        LEFT JOIN ($query_qty_in_no_checksheet) qnc ON a.id = qnc.item_fg_id
        LEFT JOIN ($query_transaction_fg_in) qi ON a.id = qi.item_fg_id
        LEFT JOIN ($query_qty_out) qo ON a.id = qo.item_fg_id
        LEFT JOIN ($query_delivery_notes) qg ON a.id = qg.item_fg_id
        LEFT JOIN ($query_scan_repair_of_goods) qh ON a.id = qh.item_fg_id
        LEFT JOIN ($query_qty_in_wip_receipt) qw ON a.id = qw.item_fg_id

        LEFT JOIN ( SELECT a.id,
            (COALESCE(qc.qty_in_checksheet, 0) + COALESCE(qnc.qty_in_no_checksheet, 0) + COALESCE(qi.initial_in, 0) + COALESCE(qw.qty_in_wip_receipt, 0) - 
            (COALESCE(qo.qty_out, 0) + COALESCE(qg.initial_out_g, 0) + COALESCE(qh.initial_out_h, 0))) AS begin_stock
            FROM item_fg a
            LEFT JOIN ($query_qty_in_checksheet2) qc ON a.id = qc.item_fg_id
            LEFT JOIN ($query_qty_in_no_checksheet2) qnc ON a.id = qnc.item_fg_id
            LEFT JOIN ($query_transaction_fg_in2) qi ON a.id = qi.item_fg_id
            LEFT JOIN ($query_qty_out2) qo ON a.id = qo.item_fg_id
            LEFT JOIN ($query_delivery_notes2) qg ON a.id = qg.item_fg_id
            LEFT JOIN ($query_scan_repair_of_goods2) qh ON a.id = qh.item_fg_id
            LEFT JOIN ($query_qty_in_wip_receipt2) qw ON a.id = qw.item_fg_id
            GROUP BY a.id) x ON a.id = x.id
        WHERE a.id LIKE '%$filter_items%' AND a.division_id LIKE '%$filter_division%' AND a.type LIKE '%$filter_type%'
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
                <h3 style="margin:0;">INVENTORY FG STANDARD AND ACTUAL <i>' . $display_title . '</i> </h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';
        
        // Build Table Header
        $html .= '<table id="customers" border="1" style="font-size: 11px;">
            <thead>
                <tr style="background-color:#eee;">
                    <th rowspan="4" width="20">No</th>
                    <th rowspan="4" colspan="3">Product No</th>
                    <th rowspan="4" colspan="2">Product Name</th>
                    <th rowspan="4">Uom</th>
                    <th rowspan="4">Division</th>
                    <th rowspan="4">Product Family</th>
                    <th rowspan="4">Type</th>
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

        foreach ($records as $record) 
        {
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

            $totalBeginStock += @$record->begin_stock;
            $totalBeginAmount += @$record->price * $rate * @$record->begin_stock;
            $totalIn += @$record->qty_in;
            $totalAmountIn += @$record->price * $rate * @$record->qty_in;
            $totalOut += @$record->qty_out;
            $totalAmountOut += @$record->price * $rate * @$record->qty_out;
            $totalEndingStock += @(@$record->begin_stock + $record->qty_in) - $record->qty_out;
            $totalAmountEndingStock += ((@$record->price * $rate) * @$record->qty_in) + ((@$record->price * $rate) * @$record->begin_stock) - ((@$record->price * $rate) * @$record->qty_out);


            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td colspan="3" style="mso-number-format:\@;">' . $record->number . '</td>
                            <td colspan="2" style="mso-number-format:\@;">' . $record->name . '</td>
                            <td>' . $record->uom . '</td>
                            <td>' . $record->division . '</td>
                            <td>FINISH GOOD</td>
                            <td>' . $record->type . '</td>
                            <td style="text-align:center;">' . $record->currency . '</td>
                            <td style="text-align:right;">' . number_format($rate, 2) . '</td>

                            <td style="text-align:right;">' . number_format(@$record->begin_stock, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->price * $rate) * $record->begin_stock, 2) . '</td>
                            <td>0</td>
                            <td>-</td>

                            <td style="text-align:right;">' . number_format($record->qty_in, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->price * $rate) * $record->qty_in, 2) . '</td>
                            <td>0</td>
                            <td>0</td>

                            <td style="text-align:right;">' . number_format($record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format(($record->price * $rate) * $record->qty_out, 2) . '</td>
                            <td>0</td>
                            <td>0</td>

                            <td style="text-align:right;">' . number_format((@$record->begin_stock + $record->qty_in) - $record->qty_out, 2) . '</td>
                            <td style="text-align:right;">' . number_format($record->price * $rate, 2) . '</td>
                            <td style="text-align:right;">' . number_format((@($record->price * $rate) * $record->qty_in) + (($record->price * $rate) * $record->begin_stock) - (($record->price * $rate) * $record->qty_out), 2) . '</td>
                            <td>0</td>
                            <td>0</td>
                        
                        </tr>';

                $nod = 1;
                $begin = @$record->begin_stock;
                $price = @$record->std_price;
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

                $receipts = $this->crud->query("SELECT f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type
                        FROM scan_item_receipts_fg f
                        JOIN checksheets e ON e.number = f.checksheet_number
                        LEFT JOIN users c ON f.created_by = c.username
                        WHERE e.item_fg_id = '$item_fg_id' 
                        and DATE_FORMAT(e.packing_date, '%Y-%m-%d') between '$filter_from' and '$filter_to'");

                $receiptsNB = $this->crud->query("SELECT f.*, u.name as username ,f.packing_date as trans_date,'NEW BARCODE FG' AS receipt_type
                        FROM new_barcode_fg a
                        LEFT JOIN scan_item_receipts_fg f ON a.label_no = f.checksheet_label AND a.item_fg_id = f.item_fg_id
                        LEFT JOIN users u ON f.created_by = u.username
                        WHERE a.item_fg_id = '$item_fg_id' 
                        AND DATE_FORMAT(a.packing_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                $receiptsWIP = $this->crud->query("SELECT a.*, u.name as username, 'RECEIPT FG' AS receipt_type, a.document_no as checksheet_label
                        FROM wip_receipts a
                        LEFT JOIN users u ON a.created_by = u.username
                        WHERE a.item_fg_id = '$item_fg_id' AND a.division = 'MTS'
                        AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to'");

                $delivery_notes = $this->crud->query("SELECT a.*, d.name AS username, so.price as act_price
                    FROM delivery_notes a
                    JOIN users d ON a.created_by = d.username
                    LEFT JOIN sales_orders so ON so.sales_order_no = a.sales_order_no
                    WHERE a.item_fg_id = '$item_fg_id'
                    AND a.delivery_note_date BETWEEN '$filter_from' AND '$filter_to'");

                $transactions = $this->crud->query("SELECT
                    a.request_date,
                    a.transaction_type,
                    a.transaction_kind,
                    a.request_no,
                    a.qty,
                    b.name AS username
                    FROM transaction_fg a
                    JOIN users b ON a.created_by = b.username
                    WHERE a.item_fg_id = '$item_fg_id'
                    AND a.request_date BETWEEN '$filter_from' AND '$filter_to'");

                $scan_repair_of_goods = $this->crud->query("SELECT f.wo_no, 
                    f.document_no, 
                    f.qty, 
                    c.name AS username, 
                    e.trans_date AS trans_date, 
                    'REPAIR OF GOODS' AS receipt_type
                    FROM scan_repair_of_goods f
                    LEFT JOIN repair_of_goods e ON e.document_no = f.document_no and f.item_fg_id = e.item_fg_id
                    LEFT JOIN users c ON f.created_by = c.username
                    WHERE f.item_fg_id = '$item_fg_id'
                    AND DATE_FORMAT(e.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");

                // Proses data berdasarkan tanggal
                $all_data = [];

                // Gabungkan data receipts
                foreach ($receipts as $receipt) {
                    $all_data[] = [
                        'type' => $receipt->receipt_type,
                        'username' => $receipt->username,
                        'date' => $receipt->trans_date,
                        'wo_no' => $receipt->wo_no,
                        'label' => $receipt->checksheet_label,
                        'qty_in' => $receipt->qty,
                        'qty_out' => 0,
                        'price' => 0,
                    ];
                }

                foreach ($receiptsNB as $receiptNB) {
                    $all_data[] = [
                        'type' => $receiptNB->receipt_type,
                        'username' => $receiptNB->username,
                        'date' => $receiptNB->trans_date,
                        'wo_no' => $receiptNB->wo_no,
                        'label' => $receiptNB->checksheet_label,
                        'qty_in' => $receiptNB->qty,
                        'qty_out' => 0,
                        'price' => 0,
                    ];
                }

                foreach ($receiptsWIP as $receiptWIP) {
                    $all_data[] = [
                        'type' => $receiptWIP->receipt_type,
                        'username' => $receiptWIP->username,
                        'date' => $receiptWIP->trans_date,
                        'wo_no' => $receiptWIP->wo_no,
                        'label' => $receiptWIP->checksheet_label,
                        'qty_in' => $receiptWIP->qty,
                        'qty_out' => 0,
                        'price' => 0,
                    ];
                }

                // Gabungkan data delivery notes
                foreach ($delivery_notes as $delivery_note) {
                    $all_data[] = [
                        'type' => 'DELIVERY NOTE',
                        'username' => $delivery_note->username,
                        'date' => $delivery_note->delivery_note_date,
                        'wo_no' => $delivery_note->delivery_order_no,
                        'label' => $delivery_note->delivery_note_no,
                        'qty_in' => 0,
                        'qty_out' => $delivery_note->qty,
                        'price' => $delivery_note->act_price,
                    ];
                }

                // Gabungkan data transactions
                foreach ($transactions as $transaction) {
                    $all_data[] = [
                        'type' => $transaction->transaction_type,
                        'username' => $transaction->username,
                        'date' => $transaction->request_date,
                        'wo_no' => '-',
                        'label' => $transaction->request_no,
                        'qty_in' => $transaction->transaction_kind == 'IN' ? $transaction->qty : 0,
                        'qty_out' => $transaction->transaction_kind == 'OUT' ? $transaction->qty : 0,
                        'price' => 0,
                    ];
                }

                foreach ($scan_repair_of_goods as $scan_repair_of_good) {
                    $all_data[] = [
                        'type' => $scan_repair_of_good->receipt_type,
                        'username' => $scan_repair_of_good->username,
                        'date' => $scan_repair_of_good->trans_date,
                        'wo_no' => $scan_repair_of_good->wo_no,
                        'label' => $scan_repair_of_good->document_no,
                        'qty_in' => 0,
                        'qty_out' => $scan_repair_of_good->qty,
                        'price' => 0,
                    ];
                }

                // Urutkan data berdasarkan tanggal
                usort($all_data, function ($a, $b) {
                    return strtotime($a['date']) - strtotime($b['date']);
                });

            if (!empty($all_data)) {
                $html .= '  <tr>
                                <td colspan="32" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
                $html .= '<thead>
                        <tr>
                            <th rowspan="3" width="20"></th>
                            <th rowspan="3" width="20">No</th>
                            <th rowspan="3">Trans Type</th>
                            <th rowspan="3">Created By</th>
                            <th rowspan="3">Trans Date</th>
                            <th rowspan="3">WO / DO</th>
                            <th rowspan="3" colspan="3" >Doc. No</th>
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

                $nod = 1;
                $balance = $begin;

                foreach ($all_data as $data) {
                    $wo_no = (isset($data['wo_no']) && !in_array($data['wo_no'], ['undefined', 'null', ''])) ? $data['wo_no'] : '-';
                    $act_price = $data['price'] ?? 0;
                    
                    $balance += $data['qty_in'] - $data['qty_out'];

                    $html .= '  <tr>
                                    <td></td>
                                    <td style="text-align:center">' . $nod . '</td>
                                    <td>' . $data['type'] . '</td>
                                    <td>' . $data['username'] . '</td>
                                    <td>' . $data['date'] . '</td>
                                    <td>' . $wo_no . '</td>
                                    <td colspan="3">' . $data['label'] . '</td>
                                    <td style="text-align:center;">' . $currency . '</td>
                                    <td style="text-align:right;">' . number_format($price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($rate, 2) . '</td>

                                    <td style="text-align:right; background:#f9f9f9;">' . number_format($begin, 2) . '</td>
                                    <td style="text-align:right; background:#f9f9f9;">' . number_format($rate * $price, 2) . '</td>
                                    <td style="text-align:right; background:#f9f9f9;">' . number_format(($rate * $price) * $begin, 2) . '</td>
                                    <td style="text-align:right; background:#f9f9f9;">' . number_format($rate * $act_price, 2) . '</td>
                                    <td style="text-align:right; background:#f9f9f9;"></td>

                                    <td style="text-align:right; background:#efffef;">' . number_format($data['qty_in'], 2) . '</td>
                                    <td style="text-align:right; background:#efffef;">' . number_format($rate * $price, 2) . '</td>
                                    <td style="text-align:right; background:#efffef;">' . number_format(($rate * $price) * $data['qty_in'], 2) . '</td>
                                    <td style="text-align:right; background:#efffef;">' . number_format($rate * $act_price, 2) . '</td>
                                    <td style="text-align:right; background:#efffef;"></td>

                                    <td style="text-align:right; background:#fff2f2;">' . number_format($data['qty_out'], 2) . '</td>
                                    <td style="text-align:right; background:#fff2f2;">' . number_format($rate * $price, 2) . '</td>
                                    <td style="text-align:right; background:#fff2f2;">' . number_format(($rate * $price) * $data['qty_out'], 2) . '</td>
                                    <td style="text-align:right; background:#fff2f2;">' . number_format($rate * $act_price, 2) . '</td>
                                    <td style="text-align:right; background:#fff2f2;"></td>

                                    <td style="text-align:right; background:#fffbcc;">' . number_format($balance, 2) . '</td>
                                    <td style="text-align:right; background:#fffbcc;">' . number_format($rate * $price, 2) . '</td>
                                    <td style="text-align:right; background:#fffbcc;">' . number_format(($rate * $price) * $balance, 2) . '</td>
                                    <td style="text-align:right; background:#fffbcc;">' . number_format($rate * $act_price, 2) . '</td>
                                    <td style="text-align:right; background:#fffbcc;"></td>
                                </tr>';

                    $begin = $balance;
                    $nod++;
                }
            }
            $no++;
        }

        $html .= '<tr>
                    <td colspan="12" style="text-align:right;"><b>GRAND TOTAL</b></td>
                    <td style="text-align:right;"><b>' . number_format($totalBeginStock, 2) . '</b></td>
                    <td style="text-align:right;"></td>
                    <td style="text-align:right;"><b>' . number_format($totalBeginAmount, 2) . '</b></td>
                    <td style="text-align:right;"></td>
                    <td style="text-align:right;"></td>

                    <td style="text-align:right;"><b>' . number_format($totalIn, 2) . '</b></td>
                    <td style="text-align:right;"></td>
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
}
