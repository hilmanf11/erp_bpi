<?php
error_reporting(0);
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

    public function readWO()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('supply_sheets', ["workorder" => $post]);
        echo json_encode($send);
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
            $existing = $this->db->get_where('inventory_wip_actual', [
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
                $update = $this->db->update('inventory_wip_actual', $dataUpdate);
                
                if ($update) {
                    echo json_encode(array("title" => "Updated", "message" => "Data updated successfully!", "theme" => "success"));
                } else {
                    echo json_encode(array("title" => "Error", "message" => "Failed to update data!", "theme" => "error"));
                }
            } else {
                // CREATE jika belum ada
                $send   = $this->crud->create('inventory_wip_actual', $dataFinal);
                echo $send;
            }
        }
    }
    
    public function uploadclearFailed()
    {
        @unlink('failed/inventory_wip_standard_actual.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/inventory_wip_standard_actual.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/inventory_wip_standard_actual.txt";

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
        $page   = $this->input->post('page') ?? 1;
        $rows   = $this->input->post('rows') ?? 10;
        $offset = ($page - 1) * $rows;

        // Get filter yang diketik user
        $filterRules = $this->input->post('filterRules');

        $this->db->from('inventory_wip_actual');
        $this->db->where('upload', 'YES');
        $this->db->where('deleted', 0);

        // --- PROSES FILTER DARI SEARCH BOX ---
        if (!empty($filterRules)) {
            $rules = json_decode($filterRules);
            foreach ($rules as $rule) {
                $field = $rule->field;
                $value = $rule->value;

                if ($value != '') {
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


    public function print($option = "")
    {
        if (!$this->db->table_exists('inventory_wip_actual')) {
            echo "<pre> Database Error: Tabel Inventory WIP Actual not found! Please contact admin.</pre>";
            return false;
        }

        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_wip_$format.xls");
        }

        $filter_from      = $this->input->get('filter_from');
        $filter_to        = $this->input->get('filter_to');
        $filter_items     = $this->input->get('filter_items');
        $filter_display   = $this->input->get("filter_display");
        $filter_division  = $this->input->get("filter_division");
        $filter_shift     = $this->input->get("filter_shift");
        $filter_workorder = $this->input->get("filter_workorder");

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
                        ->get('inventory_wip_actual')
                        ->row();
        $start_system = ($cutoff_data) ? $cutoff_data->cutoff_date : '2026-01-01';

        //------------------------------------ GET DATA ----------------------------------//

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
            'BPIFG-INJ01250012',
            'BPIFG-INJ09250004',
            'BPIFG-INJ09250003',
            'BPIFG-INJ09250005'
        ];

        $exclude_str = "'" . implode("','", $exclude_ids) . "'";

        $where_extra = "";

        // Filter Division
        if (!empty($filter_division)) {
            $where_extra .= " AND a.division_id LIKE '%$filter_division%'";
        }

        // Filter Shift
        $where_shift    = ""; 
        $where_shift_b  = "";
        $where_shift_ab = "";
        if ($filter_shift !== "" && $filter_shift !== null) {
            $where_shift    = " shift = " . (int)$filter_shift;
            $where_shift_b  = " b.shift = " . (int)$filter_shift;
            $where_shift_ab = " ab.shift = " . (int)$filter_shift;
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

        // mengambil 'price' (standard price) dari standard_price_fg
        $query_standard_price = "SELECT item_fg_id, currency, price 
            FROM standard_price_fg 
            WHERE '$filter_from' >= `start_date` AND '$filter_to' <= `end_date` 
            GROUP BY item_fg_id";

        $query_main = "SELECT a.id,
                a.number,
                a.name, 
                a.uom,
                a.type,
                b.number as division,
                COALESCE(sp.price, 0) as std_price,
                sp.currency AS standard_currency,

                -- Actual Price and Qty from upload
                COALESCE(actual.price, 0) as actual_price, 
                COALESCE(actual.qty, 0) as actual_qty,

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
            LEFT JOIN divisions b ON a.division_id = b.id
            LEFT JOIN inventory_wip_actual actual ON (actual.part_no = a.number OR actual.item_fg_id = a.id)

            LEFT JOIN ($query_standard_price) sp ON a.id = sp.item_fg_id
            LEFT JOIN (
                SELECT 
                    item_fg_id, 
                    SUM(qty_wo) as qty_wo 
                FROM (
                    SELECT item_fg_id, workorder, qty_wo 
                    FROM supply_sheets 
                    WHERE request_date BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY item_fg_id, workorder, qty_wo
                ) aa 
                GROUP BY item_fg_id
            ) b on a.id = b.item_fg_id
            LEFT JOIN (
                        select item_fg_id, sum(qty) as qty_actual FROM output_productions where trans_date between '$filter_from' AND '$filter_to' $where_shift group by item_fg_id
            ) c on a.id = c.item_fg_id
            LEFT JOIN (
                        select item_fg_id, sum(qty_wip) as qty_wip FROM output_productions where trans_date between '$filter_from' AND '$filter_to' $where_shift group by item_fg_id
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
                    $where_shift
                    GROUP BY item_fg_id
                ) p ON sub.item_fg_id = p.item_fg_id   -- PARENT
                
                GROUP BY sub.item_fg_sa_id
            ) outmap ON a.id = outmap.item_fg_id

            LEFT JOIN (
                        select aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                select distinct document,item_fg_id, qty_product FROM  item_ng where trans_date between '$filter_from' AND '$filter_to' $where_shift AND kind LIKE 'Ng Process Production'
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
                        $where_shift
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
                    WHERE b.packing_date BETWEEN '$filter_from' AND '$filter_to' 
                        AND b.status_subcont='NO' 
                        $where_shift_b
                    GROUP BY b.item_fg_id
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
                        where ab.packing_date between '$filter_from' AND '$filter_to' and ab.subcont_type='Jasa' $where_shift_ab
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
                            WHERE trans_date >= '$start_system'
                            GROUP BY item_fg_id
                        ) e ON a.id = e.item_fg_id

                        -- Transaksi setelah cutoff_date sampai < filter_from
                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty) AS qty_actual
                            FROM output_productions
                            WHERE trans_date >= '$start_system' AND trans_date < '$filter_from'
                            $where_shift
                            GROUP BY item_fg_id
                        ) c ON a.id = c.item_fg_id

                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty_wip) AS qty_wip
                            FROM output_productions
                            WHERE trans_date >= '$start_system' AND trans_date < '$filter_from'
                            $where_shift
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
                                WHERE trans_date >= '$start_system'
                                AND trans_date < '$filter_from'
                                $where_shift
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
                                WHERE ax.request_date >= '$start_system' AND ax.request_date < '$filter_from'
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
                                WHERE DATE_FORMAT(b.packing_date, '%Y-%m-%d') >= '$start_system'
                                AND DATE_FORMAT(b.packing_date, '%Y-%m-%d') < '$filter_from'
                                AND b.status_subcont = 'NO'
                                $where_shift_b
                                GROUP BY b.item_fg_id
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
                                    WHERE trans_date >= '$start_system' AND trans_date < '$filter_from'
                                    $where_shift
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
                            AND packing_date >= '$start_system'
                            AND packing_date < '$filter_from'
                            GROUP BY item_fg_id
                        ) ga ON a.id = ga.item_fg_id

                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty) AS initial_in
                            FROM transaction_fg
                            WHERE transaction_kind = 'IN'
                            AND transaction_type = 'RECEIPT FG'
                            AND request_date >= '$start_system'
                            AND request_date < '$filter_from'
                            GROUP BY item_fg_id
                        ) gb ON a.id = gb.item_fg_id

                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty) AS qty_in_wip_receipt
                            FROM wip_receipts
                            WHERE division = 'MTS'
                            AND trans_date >= '$start_system' 
                            AND trans_date < '$filter_from'
                            GROUP BY item_fg_id
                        ) gc ON a.id = gc.item_fg_id

                        LEFT JOIN (
                            SELECT ab.item_fg_id, SUM(aa.qty) AS qty_rfg_jasa
                            FROM scan_item_receipts_fg aa
                            JOIN checksheets ab ON aa.checksheet_number = ab.number
                            WHERE ab.packing_date >= '$start_system' 
                            AND ab.packing_date < '$filter_from'
                            AND ab.subcont_type = 'Jasa'
                            $where_shift_ab
                            GROUP BY ab.item_fg_id
                        ) h ON a.id = h.item_fg_id

                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty) AS qty_adj_in
                            FROM wip_adjustment_fg
                            WHERE request_date >= '$start_system' 
                            AND request_date < '$filter_from'
                            AND transaction_type = 'ADJ IN'
                            GROUP BY item_fg_id
                        ) j ON a.id = j.item_fg_id

                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty) AS qty_adj_out
                            FROM wip_adjustment_fg
                            WHERE request_date >= '$start_system' 
                            AND request_date < '$filter_from'
                            AND transaction_type = 'ADJ OUT'
                            GROUP BY item_fg_id
                        ) k ON a.id = k.item_fg_id

                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty) AS qty_ng_wip
                            FROM wip_adjustment_fg
                            WHERE request_date >= '$start_system' 
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
                <h3 style="margin:0;">INVENTORY WIP STANDARD AND ACTUAL <i>' . $display_title . '</i> </h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';

        // Build Table Header
        $html .= '<table id="customers" border="1" style="font-size: 11px;">
            <thead>
                <tr style="background-color: #eee;">
                    <th rowspan="5" width="20">No</th>
                    <th rowspan="5" colspan="3">Product No</th>
                    <th rowspan="5" colspan="2">Product Name</th>
                    <th rowspan="5" colspan="2">UOM</th>
                    <th rowspan="5" colspan="2">Division</th>

                    <th colspan="24">SUMMARY</th>
                    <th colspan="50">DETAIL</th>
                </tr>
                <tr style="background-color:#d5d5d5;">
                    <th colspan="6">BEGIN</th>
                    <th colspan="6">IN</th>
                    <th colspan="6">OUT</th>
                    <th colspan="6">ENDING</th>
                    
                    <th colspan="20">IN</th>
                    <th colspan="30">OUT</th>
                </tr>';

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

                    <th colspan="5"> OUTPUT PROD. FG</th>
                    <th colspan="5"> OUTPUT PROD. WIP</th>
                    <th colspan="5"> SubCont Jasa</th>
                    <th colspan="5"> ADJ IN</th>

                    <th colspan="5"> NG ASSY</th>
                    <th colspan="5"> NG WIP</th>
                    <th colspan="5"> OUTPUT ASSY</th>
                    <th colspan="5"> RFG</th>
                    <th colspan="5"> RFG SubCont Jasa</th>
                    <th colspan="5"> ADJ OUT</th>
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
                </tr>
            </thead>';

        $no = 1;
        $total_b_qty   = 0;
        $total_i_qty   = 0;
        $total_o_qty   = 0;
        $total_e_qty   = 0;

        $total_b_std_amount   = 0;
        $total_i_std_amount   = 0;
        $total_o_std_amount   = 0;
        $total_e_std_amount   = 0;

        $total_b_act_amount   = 0;
        $total_i_act_amount   = 0;
        $total_o_act_amount   = 0;
        $total_e_act_amount   = 0;

        $total_qty_actual     = 0;
        $total_std_qty_actual = 0;
        $total_act_qty_actual = 0;
        
        $total_qty_wip        = 0;
        $total_std_qty_wip    = 0;
        $total_act_qty_wip    = 0;
        
        $total_subconts_jasa     = 0;
        $total_std_subconts_jasa = 0;
        $total_act_subconts_jasa = 0;

        $total_qty_adj_in     = 0;
        $total_std_qty_adj_in = 0;
        $total_act_qty_adj_in = 0;

        $total_qty_ng_sa      = 0;
        $total_std_qty_ng_sa  = 0;
        $total_act_qty_ng_sa  = 0;

        $total_qty_ng_wip     = 0;
        $total_std_qty_ng_wip = 0;
        $total_act_qty_ng_wip = 0;

        $total_qty_output     = 0;
        $total_std_qty_output = 0;
        $total_act_qty_output = 0;

        $total_qty_rfg        = 0;
        $total_std_qty_rfg    = 0;
        $total_act_qty_rfg    = 0;

        $total_rfg_jasa       = 0;
        $total_std_rfg_jasa   = 0;
        $total_act_rfg_jasa   = 0;

        $total_qty_adj_out     = 0;
        $total_std_qty_adj_out = 0;
        $total_act_qty_adj_out = 0;

        foreach ($records as $record) {
            $item_fg_id = $record->id;
            $currency   = $record->standard_currency;
            $rate = 1;

            // Get Price
            $std_price = (float)$record->std_price * $rate;
            $act_price = (float)$record->actual_price * 1; // IDR

            // Get QTY
            $actual_upload_qty = (float)$record->actual_qty;
            $mutation_before = (float)$record->begin_stock;
            $b_qty = $actual_upload_qty + $mutation_before;

            $i_qty = (float)$record->qty_actual + $record->qty_wip + $record->subconts_jasa + $record->qty_adj_in;
            $o_qty = (float)$record->qty_ng_sa + $record->qty_ng_wip + $record->qty_output + $record->qty_rfg + $record->rfg_jasa + $record->qty_adj_out;
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
                <td style="text-align:center">' . $no . '</td>
                <td colspan="3" style="mso-number-format:\@;">' . $record->number . '</td>
                <td colspan="2" style="mso-number-format:\@;">' . $record->name . '</td>
                <td colspan="2">' . $record->uom . '</td>
                <td colspan="2">' . $record->division . '</td>

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

                <td style="text-align:right;">' . number_format($record->ending_balance, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($e_std_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($e_act_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($e_act_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($e_variance, 2) . '</td>
                

                <td style="text-align:right;">' . number_format($record->qty_actual, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price * $record->qty_actual, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price * $record->qty_actual, 2) . '</td>
                
                <td style="text-align:right;">' . number_format($record->qty_wip, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price * $record->qty_wip, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price * $record->qty_wip, 2) . '</td>
                
                <td style="text-align:right;">' . number_format($record->subconts_jasa, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price * $record->subconts_jasa, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price * $record->subconts_jasa, 2) . '</td>

                <td style="text-align:right;">' . number_format($record->qty_adj_in, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price * $record->qty_adj_in, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price * $record->qty_adj_in, 2) . '</td>

                <td style="text-align:right;">' . number_format($record->qty_ng_sa, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price * $record->qty_ng_sa, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price * $record->qty_ng_sa, 2) . '</td>

                <td style="text-align:right;">' . number_format($record->qty_ng_wip, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price * $record->qty_ng_wip, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price * $record->qty_ng_wip, 2) . '</td>

                <td style="text-align:right;">' . number_format($record->qty_output, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price * $record->qty_output, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price * $record->qty_output, 2) . '</td>

                <td style="text-align:right;">' . number_format($record->qty_rfg, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price * $record->qty_rfg, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price * $record->qty_rfg, 2) . '</td>

                <td style="text-align:right;">' . number_format($record->rfg_jasa, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price * $record->rfg_jasa, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price * $record->rfg_jasa, 2) . '</td>

                <td style="text-align:right;">' . number_format($record->qty_adj_out, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price * $record->qty_adj_out, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price * $record->qty_adj_out, 2) . '</td>
            </tr>';


            $total_b_qty += $b_qty;
            $total_b_std_amount += $b_std_amount;
            $total_b_act_amount += $b_act_amount;

            $total_i_qty += $i_qty;
            $total_i_std_amount += $i_std_amount;
            $total_i_act_amount += $i_act_amount;

            $total_o_qty += $o_qty;
            $total_o_std_amount += $o_std_amount;
            $total_o_act_amount += $o_act_amount;

            $total_e_qty += $e_qty;
            $total_e_std_amount += $e_std_amount;
            $total_e_act_amount += $e_act_amount;

            // Details
            $total_qty_actual += ($record->qty_actual);
            $total_std_qty_actual += ($std_price * $record->qty_actual);
            $total_act_qty_actual += ($act_price * $record->qty_actual);
            
            $total_qty_wip += ($record->qty_wip);
            $total_std_qty_wip += ($std_price * $record->qty_wip);
            $total_act_qty_wip += ($act_price * $record->qty_wip);
            
            $total_subconts_jasa += ($record->subconts_jasa);
            $total_std_subconts_jasa += ($std_price * $record->subconts_jasa);
            $total_act_subconts_jasa += ($act_price * $record->subconts_jasa);

            $total_qty_adj_in += ($record->qty_adj_in);
            $total_std_qty_adj_in += ($std_price * $record->qty_adj_in);
            $total_act_qty_adj_in += ($act_price * $record->qty_adj_in);

            $total_qty_ng_sa += ($record->qty_ng_sa);
            $total_std_qty_ng_sa += ($std_price * $record->qty_ng_sa);
            $total_act_qty_ng_sa += ($act_price * $record->qty_ng_sa);

            $total_qty_ng_wip += ($record->qty_ng_wip);
            $total_std_qty_ng_wip += ($std_price * $record->qty_ng_wip);
            $total_act_qty_ng_wip += ($act_price * $record->qty_ng_wip);

            $total_qty_output += ($record->qty_output);
            $total_std_qty_output += ($std_price * $record->qty_output);
            $total_act_qty_output += ($act_price * $record->qty_output);

            $total_qty_rfg += ($record->qty_rfg);
            $total_std_qty_rfg += ($std_price * $record->qty_rfg);
            $total_act_qty_rfg += ($act_price * $record->qty_rfg);

            $total_rfg_jasa += ($record->rfg_jasa);
            $total_std_rfg_jasa += ($std_price * $record->rfg_jasa);
            $total_act_rfg_jasa += ($act_price * $record->rfg_jasa);

            $total_qty_adj_out += ($record->qty_adj_out);
            $total_std_qty_adj_out += ($std_price * $record->qty_adj_out);
            $total_act_qty_adj_out += ($act_price * $record->qty_adj_out);
            
            $no++;
        }


        $html .= '<tr style="background-color: #eee; font-weight: bold;">
                <td colspan="10" style="text-align:right;">GRAND TOTAL</td>
                <td style="text-align:right;">'.number_format($total_b_qty, 2).'</td>
                <td></td>
                <td style="text-align:right;">'.number_format($total_b_std_amount, 2).'</td>
                <td></td>
                <td style="text-align:right;">'.number_format($total_b_act_amount, 2).'</td>
                <td></td>

                <td style="text-align:right;">'.number_format($total_i_qty, 2).'</td>
                <td></td>
                <td style="text-align:right;">'.number_format($total_i_std_amount, 2).'</td>
                <td></td>
                <td style="text-align:right;">'.number_format($total_i_act_amount, 2).'</td>
                <td></td>

                <td style="text-align:right;">'.number_format($total_o_qty, 2).'</td>
                <td></td>
                <td style="text-align:right;">'.number_format($total_o_std_amount, 2).'</td>
                <td></td>
                <td style="text-align:right;">'.number_format($total_o_act_amount, 2).'</td>
                <td></td>

                <td style="text-align:right;">'.number_format($total_e_qty, 2).'</td>
                <td></td>
                <td style="text-align:right;">'.number_format($total_e_std_amount, 2).'</td>
                <td></td>
                <td style="text-align:right;">'.number_format($total_e_act_amount, 2).'</td>
                <td></td>

                <td style="text-align:right;">' . number_format($total_qty_actual, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_std_qty_actual, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_act_qty_actual, 2) . '</td>
                
                <td style="text-align:right;">' . number_format($total_qty_wip, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_std_qty_wip, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_act_qty_wip, 2) . '</td>
                
                <td style="text-align:right;">' . number_format($total_subconts_jasa, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_std_subconts_jasa, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_act_subconts_jasa, 2) . '</td>

                <td style="text-align:right;">' . number_format($total_qty_adj_in, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_std_qty_adj_in, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_act_qty_adj_in, 2) . '</td>

                <td style="text-align:right;">' . number_format($total_qty_ng_sa, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_std_qty_ng_sa, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_act_qty_ng_sa, 2) . '</td>

                <td style="text-align:right;">' . number_format($total_qty_ng_wip, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_std_qty_ng_wip, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_act_qty_ng_wip, 2) . '</td>

                <td style="text-align:right;">' . number_format($total_qty_output, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_std_qty_output, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_act_qty_output, 2) . '</td>

                <td style="text-align:right;">' . number_format($total_qty_rfg, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_std_qty_rfg, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_act_qty_rfg, 2) . '</td>

                <td style="text-align:right;">' . number_format($total_rfg_jasa, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_std_rfg_jasa, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_act_rfg_jasa, 2) . '</td>

                <td style="text-align:right;">' . number_format($total_qty_adj_out, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_std_qty_adj_out, 2) . '</td>
                <td></td>
                <td style="text-align:right;">' . number_format($total_act_qty_adj_out, 2) . '</td>
            </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_without_actual($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=inventory_wip_standard_actual_$format.xls");
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

        $display_title = ($filter_display == "DETAIL") ? '(DETAIL)' : '(RECAP)';

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
            'BPIFG-INJ01250012',
            'BPIFG-INJ09250004',
            'BPIFG-INJ09250003',
            'BPIFG-INJ09250005'
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

        // mengambil 'price' (standard price) dari standard_price_fg
        $query_standard_price = "SELECT item_fg_id, currency, price 
            FROM standard_price_fg 
            WHERE '$filter_from' >= `start_date` AND '$filter_to' <= `end_date` 
            GROUP BY item_fg_id";

        $query_main = "SELECT a.id,
                        a.number,
                        a.name, 
                        a.uom,
                        a.type,
                        b.number as division,
                        COALESCE(sp.price, 0) as std_price,
                        sp.currency AS standard_currency,
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
                        LEFT JOIN divisions b ON a.division_id = b.id
                        LEFT JOIN ($query_standard_price) sp ON a.id = sp.item_fg_id
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
                                WHERE b.packing_date BETWEEN '$filter_from' AND '$filter_to'
                                    AND b.status_subcont='NO' 
                                    AND b.shift LIKE '%$filter_shift%'
                                GROUP BY b.item_fg_id
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
                <h3 style="margin:0;">INVENTORY WIP STANDARD AND ACTUAL <i>' . $display_title . '</i> </h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';

        // Build Table Header
        $html .= '<table id="customers" border="1" style="font-size: 11px;">
            <thead>
                <tr style="background-color: #eee;">
                    <th rowspan="5" width="20">No</th>
                    <th rowspan="5" colspan="3">Product No</th>
                    <th rowspan="5" colspan="2">Product Name</th>
                    <th rowspan="5" colspan="2">UOM</th>
                    <th rowspan="5" colspan="2">Division</th>

                    <th colspan="24">SUMMARY</th>
                    <th colspan="50">DETAIL</th>
                </tr>
                <tr style="background-color:#d5d5d5;">
                    <th colspan="6">BEGIN</th>
                    <th colspan="6">IN</th>
                    <th colspan="6">OUT</th>
                    <th colspan="6">ENDING</th>
                    
                    <th colspan="20">IN</th>
                    <th colspan="30">OUT</th>
                </tr>';

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


                    <th colspan="5"> OUTPUT PROD. FG</th>
                    <th colspan="5"> OUTPUT PROD. WIP</th>
                    <th colspan="5"> SubCont Jasa</th>
                    <th colspan="5"> ADJ IN</th>

                    <th colspan="5"> NG ASSY</th>
                    <th colspan="5"> NG WIP</th>
                    <th colspan="5"> OUTPUT ASSY</th>
                    <th colspan="5"> RFG</th>
                    <th colspan="5"> RFG SubCont Jasa</th>
                    <th colspan="5"> ADJ OUT</th>
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
                </tr>
            </thead>';

        $no = 1;
        foreach ($records as $record) {
            $item_fg_id = $record->id;

            $rate = 1;
            if ($record->standard_currency == 'USD') {
                $q_rate = $this->db->get_where('standard_exchange_rates', ['currency_from' => 'USD', 'start_date <=' => $filter_from, 'end_date >=' => $filter_to])->row();
                $rate = $q_rate ? $q_rate->middle : 1;
            }

            // standard Price
            $std_p = (float)$record->std_price * $rate;
            $act_p = 0; // actual price WIP belum diketahui

            // Begin
            $b_qty = (float)$record->begin_balance;
            $b_std_amount = $b_qty * $std_p;
            $b_act_amount = $b_qty * $std_p; // begin actual = std
            $b_variance   = $b_act_amount - $b_std_amount;

            $in_qty = $record->qty_actual + $record->qty_wip + $record->subconts_jasa + $record->qty_adj_in;
            $out_qty = $record->qty_ng_sa + $record->qty_ng_wip + $record->qty_output + $record->qty_rfg + $record->rfg_jasa + $record->qty_adj_out;

            // In
            $in_std_amount = $in_qty * $std_p;
            $in_act_amount = $in_qty * 0;
            $in_variance   = $in_act_amount - $in_std_amount;

            // Out 
            $out_std_amount = $out_qty * $std_p;
            $out_act_amount = $out_qty * 0;
            $out_variance   = $out_act_amount - $out_std_amount;

            // Ending
            $e_qty = $record->ending_balance;
            $e_std_amount = $e_qty * $std_p;
            $e_act_amount = ($b_act_amount + $in_act_amount) - $out_act_amount;
            $e_act_p = 0;
            if ($e_qty > 0) {
                $e_act_p = $e_act_amount / $e_qty;
            }
            $e_variance = $e_act_amount - $e_std_amount;

            $html .= '  <tr>
                <td style="text-align:center">' . $no . '</td>
                <td colspan="3" style="mso-number-format:\@;">' . $record->number . '</td>
                <td colspan="2" style="mso-number-format:\@;">' . $record->name . '</td>
                <td colspan="2">' . $record->uom . '</td>
                <td colspan="2">' . $record->division . '</td>

                <td style="text-align:right;">' . number_format($record->begin_balance, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($b_std_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($b_act_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($b_variance, 2) . '</td>

                <td style="text-align:right;">' . number_format($in_qty, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($in_std_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($in_act_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($in_variance, 2) . '</td>

                <td style="text-align:right;">' . number_format($out_qty, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($out_std_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($out_act_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($out_variance, 2) . '</td>

                <td style="text-align:right;">' . number_format($record->ending_balance, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($e_std_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($e_act_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($e_act_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($e_variance, 2) . '</td>
                

                <td style="text-align:right;">' . number_format($record->qty_actual, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p * $record->qty_actual, 2) . '</td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"></td>
                
                <td style="text-align:right;">' . number_format($record->qty_wip, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p * $record->qty_wip, 2) . '</td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"></td>
                
                <td style="text-align:right;">' . number_format($record->subconts_jasa, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p * $record->subconts_jasa, 2) . '</td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"></td>

                <td style="text-align:right;">' . number_format($record->qty_adj_in, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p * $record->qty_adj_in, 2) . '</td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"></td>

                <td style="text-align:right;">' . number_format($record->qty_ng_sa, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p * $record->qty_ng_sa, 2) . '</td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"></td>

                <td style="text-align:right;">' . number_format($record->qty_ng_wip, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p * $record->qty_ng_wip, 2) . '</td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"></td>

                <td style="text-align:right;">' . number_format($record->qty_output, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p * $record->qty_output, 2) . '</td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"></td>

                <td style="text-align:right;">' . number_format($record->qty_rfg, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p * $record->qty_rfg, 2) . '</td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"></td>

                <td style="text-align:right;">' . number_format($record->rfg_jasa, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p * $record->rfg_jasa, 2) . '</td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"></td>

                <td style="text-align:right;">' . number_format($record->qty_adj_out, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_p * $record->qty_adj_out, 2) . '</td>
                <td style="text-align:right;"></td>
                <td style="text-align:right;"></td>
            </tr>';

            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_detail_without_actual($option = "")
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

        $display_title = ($filter_display == "DETAIL") ? '(DETAIL)' : '(RECAP)';

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
                <h3 style="margin:0;">INVENTORY WIP STANDARD AND ACTUAL <i>' . $display_title . '</i> </h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';
        
        // Build Table Header
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
                </tr>';
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

    public function print_detail($option = "") 
    {
        set_time_limit(300);

        if (!$this->db->table_exists('inventory_wip_actual')) {
            echo "<pre> Database Error: Tabel Inventory WIP Actual not found! Please contact admin.</pre>";
            return false;
        }

        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_wip_$format.xls");
        }

        $filter_from      = $this->input->get('filter_from');
        $filter_to        = $this->input->get('filter_to');
        $filter_items     = $this->input->get('filter_items');
        $filter_display   = $this->input->get("filter_display");
        $filter_division  = $this->input->get("filter_division");
        $filter_shift     = $this->input->get("filter_shift");
        $filter_workorder = $this->input->get("filter_workorder");

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
                        ->get('inventory_wip_actual')
                        ->row();
        $start_system = ($cutoff_data) ? $cutoff_data->cutoff_date : '2026-01-01';

        //------------------------------------ GET DATA ----------------------------------//

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
            'BPIFG-INJ01250012',
            'BPIFG-INJ09250004',
            'BPIFG-INJ09250003',
            'BPIFG-INJ09250005'
        ];

        $exclude_str = "'" . implode("','", $exclude_ids) . "'";

        $where_extra = "";

        // Filter Division
        if (!empty($filter_division)) {
            $where_extra .= " AND a.division_id LIKE '%$filter_division%'";
        }

        // Filter Shift
        $where_shift    = ""; 
        $where_shift_b  = "";
        $where_shift_ab = "";
        if ($filter_shift !== "" && $filter_shift !== null) {
            $where_shift    = " shift = " . (int)$filter_shift;
            $where_shift_b  = " b.shift = " . (int)$filter_shift;
            $where_shift_ab = " ab.shift = " . (int)$filter_shift;
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

        // mengambil 'price' (standard price) dari standard_price_fg
        $query_standard_price = "SELECT item_fg_id, currency, price 
            FROM standard_price_fg 
            WHERE '$filter_from' >= `start_date` AND '$filter_to' <= `end_date` 
            GROUP BY item_fg_id";

        $query_main = "SELECT a.id,
                a.number,
                a.name, 
                a.uom,
                a.type,
                b.number as division,
                COALESCE(sp.price, 0) as std_price,
                sp.currency AS standard_currency,

                -- Actual Price and Qty from upload
                COALESCE(actual.price, 0) as actual_price, 
                COALESCE(actual.qty, 0) as actual_qty,

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
            LEFT JOIN divisions b ON a.division_id = b.id
            LEFT JOIN inventory_wip_actual actual ON (actual.part_no = a.number OR actual.item_fg_id = a.id)

            LEFT JOIN ($query_standard_price) sp ON a.id = sp.item_fg_id
            LEFT JOIN (
                SELECT 
                    item_fg_id, 
                    SUM(qty_wo) as qty_wo 
                FROM (
                    SELECT item_fg_id, workorder, qty_wo 
                    FROM supply_sheets 
                    WHERE request_date BETWEEN '$filter_from' AND '$filter_to'
                    GROUP BY item_fg_id, workorder, qty_wo
                ) aa 
                GROUP BY item_fg_id
            ) b on a.id = b.item_fg_id
            LEFT JOIN (
                        select item_fg_id, sum(qty) as qty_actual FROM output_productions where trans_date between '$filter_from' AND '$filter_to' $where_shift group by item_fg_id
            ) c on a.id = c.item_fg_id
            LEFT JOIN (
                        select item_fg_id, sum(qty_wip) as qty_wip FROM output_productions where trans_date between '$filter_from' AND '$filter_to' $where_shift group by item_fg_id
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
                    $where_shift
                    GROUP BY item_fg_id
                ) p ON sub.item_fg_id = p.item_fg_id   -- PARENT
                
                GROUP BY sub.item_fg_sa_id
            ) outmap ON a.id = outmap.item_fg_id

            LEFT JOIN (
                        select aa.item_fg_id,sum(aa.qty_product) as qty_ng FROM (
                                select distinct document,item_fg_id, qty_product FROM  item_ng where trans_date between '$filter_from' AND '$filter_to' $where_shift AND kind LIKE 'Ng Process Production'
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
                        $where_shift
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
                    WHERE b.packing_date BETWEEN '$filter_from' AND '$filter_to' 
                        AND b.status_subcont='NO' 
                        $where_shift_b
                    GROUP BY b.item_fg_id
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
                        where ab.packing_date between '$filter_from' AND '$filter_to' and ab.subcont_type='Jasa' $where_shift_ab
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
                            WHERE trans_date >= '$start_system'
                            GROUP BY item_fg_id
                        ) e ON a.id = e.item_fg_id

                        -- Transaksi setelah cutoff_date sampai < filter_from
                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty) AS qty_actual
                            FROM output_productions
                            WHERE trans_date >= '$start_system' AND trans_date < '$filter_from'
                            $where_shift
                            GROUP BY item_fg_id
                        ) c ON a.id = c.item_fg_id

                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty_wip) AS qty_wip
                            FROM output_productions
                            WHERE trans_date >= '$start_system' AND trans_date < '$filter_from'
                            $where_shift
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
                                WHERE trans_date >= '$start_system'
                                AND trans_date < '$filter_from'
                                $where_shift
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
                                WHERE ax.request_date >= '$start_system' AND ax.request_date < '$filter_from'
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
                                WHERE DATE_FORMAT(b.packing_date, '%Y-%m-%d') >= '$start_system'
                                AND DATE_FORMAT(b.packing_date, '%Y-%m-%d') < '$filter_from'
                                AND b.status_subcont = 'NO'
                                $where_shift_b
                                GROUP BY b.item_fg_id
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
                                    WHERE trans_date >= '$start_system' AND trans_date < '$filter_from'
                                    $where_shift
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
                            AND packing_date >= '$start_system'
                            AND packing_date < '$filter_from'
                            GROUP BY item_fg_id
                        ) ga ON a.id = ga.item_fg_id

                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty) AS initial_in
                            FROM transaction_fg
                            WHERE transaction_kind = 'IN'
                            AND transaction_type = 'RECEIPT FG'
                            AND request_date >= '$start_system'
                            AND request_date < '$filter_from'
                            GROUP BY item_fg_id
                        ) gb ON a.id = gb.item_fg_id

                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty) AS qty_in_wip_receipt
                            FROM wip_receipts
                            WHERE division = 'MTS'
                            AND trans_date >= '$start_system' 
                            AND trans_date < '$filter_from'
                            GROUP BY item_fg_id
                        ) gc ON a.id = gc.item_fg_id

                        LEFT JOIN (
                            SELECT ab.item_fg_id, SUM(aa.qty) AS qty_rfg_jasa
                            FROM scan_item_receipts_fg aa
                            JOIN checksheets ab ON aa.checksheet_number = ab.number
                            WHERE ab.packing_date >= '$start_system' 
                            AND ab.packing_date < '$filter_from'
                            AND ab.subcont_type = 'Jasa'
                            $where_shift_ab
                            GROUP BY ab.item_fg_id
                        ) h ON a.id = h.item_fg_id

                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty) AS qty_adj_in
                            FROM wip_adjustment_fg
                            WHERE request_date >= '$start_system' 
                            AND request_date < '$filter_from'
                            AND transaction_type = 'ADJ IN'
                            GROUP BY item_fg_id
                        ) j ON a.id = j.item_fg_id

                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty) AS qty_adj_out
                            FROM wip_adjustment_fg
                            WHERE request_date >= '$start_system' 
                            AND request_date < '$filter_from'
                            AND transaction_type = 'ADJ OUT'
                            GROUP BY item_fg_id
                        ) k ON a.id = k.item_fg_id

                        LEFT JOIN (
                            SELECT item_fg_id, SUM(qty) AS qty_ng_wip
                            FROM wip_adjustment_fg
                            WHERE request_date >= '$start_system' 
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
                <h3 style="margin:0;">INVENTORY WIP STANDARD AND ACTUAL <i>' . $display_title . '</i> </h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';

        // Build Table Header
        $html .= '<table id="customers" border="1" style="font-size: 11px;">
            <thead>
                <tr style="background-color: #eee;">
                    <th rowspan="5" width="20">No</th>
                    <th rowspan="5" colspan="2">Product No</th>
                    <th rowspan="5">Product Name</th>
                    <th rowspan="5">UOM</th>
                    <th rowspan="5">Division</th>
                    <th rowspan="5">Product Family</th>
                    <th rowspan="5">Currency</th>
                    <th rowspan="5">Rate</th>

                    <th colspan="20">SUMMARY</th>
                </tr>
                <tr style="background-color:#d5d5d5;">
                    <th colspan="5">BEGIN</th>
                    <th colspan="5">IN</th>
                    <th colspan="5">OUT</th>
                    <th colspan="5">BALANCE</th>
                </tr>';

        $html .= '<tr>
                    <th rowspan="2">QTY</th>
                    <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>

                    <th rowspan="2">QTY</th>
                    <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>

                    <th rowspan="2">QTY</th>
                    <th colspan="2" style="background-color: #D1FFC6;">STANDARD</th>
                    <th colspan="2" style="background-color: #CFE6F9;">ACTUAL</th>

                    <th rowspan="2">QTY</th>
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
                </tr>
            </thead>';

        $no = 1;
        $total_b_qty   = 0;
        $total_i_qty   = 0;
        $total_o_qty   = 0;
        $total_e_qty   = 0;

        $total_b_std_amount   = 0;
        $total_i_std_amount   = 0;
        $total_o_std_amount   = 0;
        $total_e_std_amount   = 0;

        $total_b_act_amount   = 0;
        $total_i_act_amount   = 0;
        $total_o_act_amount   = 0;
        $total_e_act_amount   = 0;

        foreach ($records as $record) {
            $item_fg_id = $record->id;
            $currency   = $record->standard_currency;
            $rate = 1;

            // standard Price
            $std_price = (float)$record->std_price * $rate;
            $act_price = (float)$record->actual_price * $rate;

            // Begin
            $b_qty = (float)$record->actual_qty;
            $b_std_amount = $b_qty * $std_price;
            $b_act_amount = $b_qty * $act_price;
            $b_variance   = $b_act_amount - $b_std_amount;

            $in_qty = $record->qty_actual + $record->qty_wip + $record->subconts_jasa + $record->qty_adj_in;
            $out_qty = $record->qty_ng_sa + $record->qty_ng_wip + $record->qty_output + $record->qty_rfg + $record->rfg_jasa + $record->qty_adj_out;

            // In
            $in_std_amount = $in_qty * $std_price;
            $in_act_amount = $in_qty * $act_price;
            $in_variance   = $in_act_amount - $in_std_amount;

            // Out 
            $out_std_amount = $out_qty * $std_price;
            $out_act_amount = $out_qty * $act_price;
            $out_variance   = $out_act_amount - $out_std_amount;

            // Ending
            $e_qty = $record->ending_balance;
            $e_std_amount = $e_qty * $std_price;
            $e_act_p = $act_price;
            $e_act_amount = ($b_act_amount + $in_act_amount) - $out_act_amount;
            $e_variance = $e_act_amount - $e_std_amount;

            $html .= '<tr>
                <td style="text-align:center">' . $no . '</td>
                <td colspan="2" style="mso-number-format:\@;">' . $record->number . '</td>
                <td style="mso-number-format:\@;">' . $record->name . '</td>
                <td>' . $record->uom . '</td>
                <td>' . $record->division . '</td>
                <td> FINISH GOOD </td>
                <td>' . $record->standard_currency . '</td>
                <td>' . number_format($rate, 2) . '</td>

                <td style="text-align:right;">' . number_format($b_qty, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($b_std_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($b_act_amount, 2) . '</td>

                <td style="text-align:right;">' . number_format($in_qty, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($in_std_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($in_act_amount, 2) . '</td>

                <td style="text-align:right;">' . number_format($out_qty, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($out_std_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($act_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($out_act_amount, 2) . '</td>

                <td style="text-align:right;">' . number_format($record->ending_balance, 2) . '</td>
                <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                <td style="text-align:right;">' . number_format($e_std_amount, 2) . '</td>
                <td style="text-align:right;">' . number_format($e_act_p, 2) . '</td>
                <td style="text-align:right;">' . number_format($e_act_amount, 2) . '</td>
            </tr>';

            // Total Summary
            $total_b_qty += $b_qty;
            $total_b_std_amount += $b_std_amount;
            $total_b_act_amount += $b_act_amount;

            $total_i_qty += $in_qty;
            $total_i_std_amount += $in_std_amount;
            $total_i_act_amount += $in_act_amount;

            $total_o_qty += $out_qty;
            $total_o_std_amount += $out_std_amount;
            $total_o_act_amount += $out_act_amount;

            $total_e_qty += $e_qty;
            $total_e_std_amount += $e_std_amount;
            $total_e_act_amount += $e_act_amount;


            // DETAILS            
            $grandtotals = [
                'b_qty' => 0, 'b_std' => 0, 'b_act' => 0,
                'i_qty' => 0, 'i_std' => 0, 'i_act' => 0,
                'o_qty' => 0, 'o_std' => 0, 'o_act' => 0,
                'e_qty' => 0, 'e_std' => 0, 'e_act' => 0,
            ];

            $html .= '<tr>
                    <td colspan="30" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                </tr>';

            $html .= '<thead>
                    <tr>
                        <th rowspan="3" width="20"></th>
                        <th rowspan="3" width="20">No</th>
                        <th rowspan="3">Trans Type</th>
                        <th rowspan="3">Created By</th>
                        <th rowspan="3">Trans Date</th>
                        <th rowspan="3">WO / DO</th>
                        <th rowspan="3">Price</th>
                        <th rowspan="3">CCY</th>
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
                
            // GET TRANSACTIONS
            $dataActualProductions = $this->crud->query("SELECT *, created_by as username 
                FROM output_productions 
                where item_fg_id = '$item_fg_id' 
                and trans_date between '$filter_from' and '$filter_to' 
                AND shift like '%$filter_shift%' 
            ");

            $dataSubcontsJasas = $this->crud->query("SELECT 
                aa.workorder,aa.request_date,aa.item_fg_id,sum(aa.qty_wo) as qty_subcont_jasa, aa.created_by as username  
                FROM (
                    select distinct ax.request_date, ax.item_fg_id, ax.workorder, ax.period, ax.qty_wo, ax.created_by
                    FROM supply_sheets ax 
                    join item_fg ay on ax.item_fg_id=ay.id 
                    where ax.item_fg_id='$item_fg_id' and ax.request_date between '$filter_from' and '$filter_to' and ay.status_subcont='YES' and ay.subcont_type='Jasa'
                ) aa group by aa.workorder,aa.request_date,aa.item_fg_id 
            ");

            $dataAdjIns = $this->crud->query("SELECT *, created_by as username 
                        FROM wip_adjustment_fg a
                        where a.item_fg_id='$item_fg_id' 
                        and a.request_date between '$filter_from' AND '$filter_to' 
                        and a.transaction_type='ADJ IN' 
            ");
            
            $receipts = $this->crud->query("SELECT 
                f.*, c.name as username, e.packing_date as trans_date, 'RECEIPT FG' AS receipt_type, f.created_by as username 
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

            $receiptsWIP = $this->crud->query("SELECT 
                a.*, u.name as username, 'WIP RECEIPT FG' AS receipt_type, a.document_no as checksheet_label, a.created_by as username 
                FROM wip_receipts a
                LEFT JOIN users u ON a.created_by = u.username
                WHERE a.item_fg_id = '$item_fg_id' AND a.division = 'MTS' AND DATE_FORMAT(a.trans_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' 
            ");

            $transFgs = $this->crud->query("SELECT *, a.created_by as username 
                FROM transaction_fg a
                WHERE a.transaction_kind = 'IN'  
                AND a.transaction_type = 'RECEIPT FG' 
                AND a.item_fg_id = '$item_fg_id' 
                AND a.request_date BETWEEN '$filter_from' and '$filter_to'
            ");
            
            $dataRfgSubcontsJasas = $this->crud->query("SELECT 
                ab.packing_date as trans_date,ab.wo_no, ab.item_fg_id,sum(aa.qty) as qty_rfg, ab.created_by as username  
                FROM scan_item_receipts_fg aa 
                JOIN checksheets ab on aa.checksheet_number = ab.number
                where aa.item_fg_id='$item_fg_id' and ab.packing_date between '$filter_from' and '$filter_to' and ab.status_subcont='YES' AND ab.subcont_type='Jasa' and ab.shift like '%$filter_shift%'
                GROUP BY ab.packing_date,ab.wo_no,ab.item_fg_id
            ");

            $dataAdjOuts = $this->crud->query("SELECT *, a.created_by as username 
                FROM wip_adjustment_fg a
                where a.item_fg_id='$item_fg_id' 
                and a.request_date between '$filter_from' AND '$filter_to' 
                and a.transaction_type='ADJ OUT'
            ");

            // Proses data 
            $all_data = [];

            foreach ($dataActualProductions as $actualProduction) {
                $all_data[] = [
                    'type'     => 'ACTUAL PRODUCTION',
                    'date'     => $actualProduction->trans_date,
                    'wo_no'    => $actualProduction->wo_no,
                    'qty_in'   => $actualProduction->qty,
                    'qty_out'  => 0,
                    'username' => $actualProduction->username,
                ];
            }

            foreach ($dataSubcontsJasas as $dataSubcontsJasa) {
                $all_data[] = [
                    'type' => 'SUBCONTS JASA',
                    'date' => $dataSubcontsJasa->request_date,
                    'wo_no' => $dataSubcontsJasa->workorder,
                    'qty_in' => $dataSubcontsJasa->qty_subcont_jasa,
                    'qty_out' => 0,
                    'username' => $dataSubcontsJasa->username,
                ];
            }

            foreach ($dataActualProductions as $actualProduction) {
                $all_data[] = [
                    'type' => 'ACTUAL PRODUCTION WIP',
                    'date' => $actualProduction->trans_date,
                    'wo_no' => $actualProduction->wo_no,
                    'qty_in' => $actualProduction->qty_wip,
                    'qty_out' => 0,
                    'username' => $actualProduction->username,
                ];
            }
            
            foreach ($dataAdjIns as $dataAdjIn) {
                $all_data[] = [
                    'type' => $dataAdjIn->transaction_type,
                    'date' => $dataAdjIn->request_date,
                    'wo_no' => $dataAdjIn->request_no,
                    'qty_in' => $dataAdjIn->qty,
                    'qty_out' => 0,
                    'username' => $dataAdjIn->username,
                ];
            }

            foreach ($receipts  as $receipt) {
                $all_data[] = [
                    'type' => $receipt->receipt_type,
                    'date' => $receipt->trans_date,
                    'wo_no' => $receipt->wo_no,
                    'qty_in' => 0,
                    'qty_out' => $receipt->qty,
                    'username' => $receipt->username,
                ];
            }

            foreach ($receiptsWIP as $receiptWIP) {
                $all_data[] = [
                    'type' => $receiptWIP->receipt_type,
                    'date' => $receiptWIP->trans_date,
                    'wo_no' => $receiptWIP->wo_no,
                    'qty_in' => 0,
                    'qty_out' => $receiptWIP->qty,
                    'username' => $receiptWIP->username,
                ];
            }

            foreach ($transFgs as $transFg) {
                $all_data[] = [
                    'type' => 'TRANSACTION FG',
                    'date' => $transFg->request_date,
                    'wo_no' => $transFg->request_no,
                    'qty_in' => 0,
                    'qty_out' => $transFgs->qty,
                    'username' => $transFg->username,
                ];
            }

            foreach ($dataRfgSubcontsJasas  as $dataRfgSubcontsJasa) {
                $all_data[] = [
                    'type' => 'RFG SUBCONTS JASA',
                    'date' => $dataRfgSubcontsJasa->trans_date,
                    'wo_no' => $dataRfgSubcontsJasa->wo_no,
                    'qty_in' => 0,
                    'qty_out' => $dataRfgSubcontsJasa->qty_rfg,
                    'username' => $dataRfgSubcontsJasa->username,
                ];
            }

            foreach ($dataAdjOuts as $dataAdjOut) {
                $all_data[] = [
                    'type' => $dataAdjOut->transaction_type,
                    'date' => $dataAdjOut->request_date,
                    'wo_no' => $dataAdjOut->request_no,
                    'qty_in' => 0,
                    'qty_out' => $dataAdjOut->qty,
                    'username' => $dataAdjOut->username,
                ];
            }

            // Urutkan data berdasarkan tanggal
            usort($all_data, function ($a, $b) {
                // Jika ada tipe UPLOADS, maka jadi paling atas (-1)
                if ($a['type'] === 'UPLOADS') return -1;
                if ($b['type'] === 'UPLOADS') return 1;

                // Transaksi lainnya diurutkan berdasarkan tanggal
                return strtotime($a['date']) - strtotime($b['date']);
            });

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
                    <td>BEGIN BALANCE (UPLOAD)</td>
                    <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                    <td style="text-align:center;">' . $record->upload_currency . '</td>
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

                $html .= '  <tr>
                        <td></td>
                        <td style="text-align:center">' . $nod . '</td>
                        <td>' . $data['type'] . '</td>
                        <td>' . $data['username'] . '</td>
                        <td>' . $data['date'] . '</td>
                        <td>' . $data['wo_no'] . '</td>
                        <td style="text-align:right;">' . number_format($std_price, 2) . '</td>
                        <td style="text-align:center;">' . $currency . '</td>
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
            
            $no++;

            // Akumulasi ke Grand Total (Kolom ENDING/BALANCE)
            $grandtotals['e_qty'] += $running_qty_bal;
            $grandtotals['e_std'] += ($running_qty_bal * $std_price);
            $grandtotals['e_act'] += ($running_qty_bal * $act_price);
        }


        $html .= '<tfooter>
            <tr style="background:#eee; font-weight:bold;">
                <td colspan="9" align="right">GRAND TOTAL</td>
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
}
