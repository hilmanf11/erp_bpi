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
        // Notice/deprecated PHP 8 dari library tidak boleh tercetak ke response JSON (tetap tercatat di log)
        ini_set('display_errors', 0);
        
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
                    
                    } elseif (!empty($cutoff) && strtotime((string) $cutoff)) {
                        // Jika ternyata formatnya string tanggal (misal: "2024-01-01")
                        $cutoffDate = date('Y-m-d', strtotime((string) $cutoff));
                    
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
            $cutoff_date = date("Y-m-d", strtotime((string) $data['cutoff_date']));
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
        // Get parameter POST dari EasyUI
        $page   = $this->input->post('page') ?? 1;
        $rows   = $this->input->post('rows') ?? 10;
        $offset = ($page - 1) * $rows;
        
        // Get filter yang diketik user
        $filterRules = $this->input->post('filterRules');

        $this->db->from('inventory_rm_actual');
        $this->db->where('upload', 'YES');
        $this->db->where('deleted', 0);

        // --- PROSES FILTER DARI SEARCH BOX ---
        if (!empty($filterRules)) {
            $rules = json_decode($filterRules);
            foreach ($rules as $rule) {
                $field = $rule->field;
                $value = $rule->value;
                
                if ($value !== '') {
                    // Jika field adalah qty atau price, gunakan perbandingan angka jika perlu
                    // Tapi standarnya gunakan LIKE untuk pencarian fleksibel
                    $this->db->like($field, $value);
                }
            }
        }

        // Hitung total data setelah difilter (simpan state query dengan FALSE)
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

    // ID kategori RAW MATERIAL (item_categories.number = 'RM'), satu-satunya kategori di report ini
    private function rmCategoryId()
    {
        $category = $this->db->select('id')->get_where('item_categories', ['number' => 'RM', 'deleted' => 0])->row();
        return $category ? $category->id : 'C01';
    }

    private function nominalFormat($value)
    {
        $formatted = number_format((float) $value, 2);
        if ($value < 0) {
            $formatted = '<span style="color: red;">(' . number_format(abs($value ?? 0), 2) . ')</span>';
        }
        return $formatted;
    }

    // Kondisi join PO: item MTS wajib spesifikasi receipt = spesifikasi PO
    private function poSpecJoin($pr, $po, $irm)
    {
        return "((UPPER(TRIM($irm.division)) = 'MTS' AND COALESCE(TRIM($pr.specification), '') = COALESCE(TRIM($po.specification), ''))
                OR UPPER(TRIM(COALESCE($irm.division, ''))) != 'MTS')";
    }

    // Harga PO dari lot BPM (alias header bpm = bh), rata-rata tertimbang qty receipt jika 1 lot ada beberapa POR
    private function bpmLotPriceSql()
    {
        return "(SELECT SUM(lp_pr.qty_receipt * lp_po.price) / NULLIF(SUM(lp_pr.qty_receipt), 0)
                FROM purchase_order_receipts lp_pr
                JOIN item_rm lp_irm ON lp_pr.item_rm_id = lp_irm.id
                JOIN purchase_orders lp_po ON lp_pr.po_no = lp_po.po_no AND lp_pr.item_rm_id = lp_po.item_rm_id
                    AND " . $this->poSpecJoin('lp_pr', 'lp_po', 'lp_irm') . "
                WHERE lp_pr.item_rm_id = bh.item_rm_id AND lp_pr.lotno = bh.lot_no AND lp_pr.deleted = 0)";
    }

    // Nilai actual BPM scan per item: harga lot POR, fallback harga PO terakhir <= tanggal BPM
    private function bpmActualAmountSql($filter_from, $filter_to)
    {
        return "SELECT s.item_rm_id, SUM(s.qty) as bpm_scan_qty,
                    SUM(s.qty * COALESCE(" . $this->bpmLotPriceSql() . ",
                        (SELECT lt_po.price
                        FROM purchase_order_receipts lt_pr
                        JOIN item_rm lt_irm ON lt_pr.item_rm_id = lt_irm.id
                        JOIN purchase_orders lt_po ON lt_pr.po_no = lt_po.po_no AND lt_pr.item_rm_id = lt_po.item_rm_id
                            AND " . $this->poSpecJoin('lt_pr', 'lt_po', 'lt_irm') . "
                        WHERE lt_pr.item_rm_id = s.item_rm_id AND lt_pr.receipt_date <= DATE(COALESCE(s.request_date, s.created_date))
                        ORDER BY lt_pr.receipt_date DESC, lt_pr.receipt_id DESC LIMIT 1), 0)) as bpm_actual_amt
                FROM scan_item_bpm s
                LEFT JOIN bpm bh ON bh.request_id = s.request_id AND bh.item_rm_id = s.item_rm_id AND bh.deleted = 0
                WHERE s.request_date >= '$filter_from' AND s.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                GROUP BY s.item_rm_id";
    }

    /**
     * Part yang kemungkinan gagal auto posting Journal Inventory di periode ini (aturan sama dengan Autopostingjournal):
     * - POR: harga PO kosong / 0, kurs mata uang supplier tidak ada
     * - BPM, Transaction RM (jenis auto posting), Supply Sheet, Non Supply Sheet, Material Request: harga jurnal 0
     *   (tidak ada PO s/d tanggal dokumen dan Standard Price RM tidak berlaku), kurs PO terakhir tidak ada
     * - Journal Type modul / divisi part belum di-set atau tidak valid
     * @param array $divisions [item_rm_id => division]
     * @return array [item_rm_id => [alasan => [dokumen => true]]]
     */
    private function journalInventoryIssues(array $divisions, $filter_from, $filter_to)
    {
        if (empty($divisions)) {
            return [];
        }
        $this->load->model('autopostingjournal');
        $ids  = "'" . implode("','", array_map([$this->db, 'escape_str'], array_keys($divisions))) . "'";
        $from = $this->db->escape_str((string) $filter_from);
        $to   = $this->db->escape_str((string) $filter_to);
        $types = "'" . implode("','", array_map([$this->db, 'escape_str'], $this->autopostingjournal->transaction_rm_auto_types)) . "'";

        $issues = [];
        $add = function ($item_id, $reason, $document) use (&$issues) {
            $issues[$item_id][$reason][$document] = true;
        };
        // Error dari engine (kurs / Journal Type), di-cache per parameter
        $cache = [];
        $engine_error = function ($method, ...$args) use (&$cache) {
            $key = $method . '|' . implode('|', $args);
            if (!array_key_exists($key, $cache)) {
                try {
                    $this->autopostingjournal->$method(...$args);
                    $cache[$key] = null;
                } catch (Exception $e) {
                    $cache[$key] = $e->getMessage();
                }
            }
            return $cache[$key];
        };

        // POR: harga PO per baris receipt (_build_por)
        $receipts = $this->crud->query("
            SELECT pr.item_rm_id, pr.receipt_no, pr.receipt_date, pr.po_no, s.currency, MIN(COALESCE(f.price, 0)) AS price
            FROM purchase_order_receipts pr
            JOIN suppliers s ON pr.supplier_id = s.id
            LEFT JOIN purchase_orders f ON pr.po_no = f.po_no AND pr.item_rm_id = f.item_rm_id
            WHERE pr.deleted = 0 AND pr.receipt_date BETWEEN '$from' AND '$to' AND pr.item_rm_id IN ($ids)
            GROUP BY pr.receipt_id, pr.item_rm_id, pr.receipt_no, pr.receipt_date, pr.po_no, s.currency
            ORDER BY pr.receipt_date, pr.receipt_no");
        foreach ($receipts as $r) {
            $document = $r->receipt_no . ' (' . $r->receipt_date . ')';
            if ((float) $r->price <= 0) {
                $add($r->item_rm_id, 'Harga PO kosong / 0 di POR', $document . ' PO ' . $r->po_no);
            }
            if ($engine_error('get_rate', $r->receipt_date, $r->currency)) {
                $add($r->item_rm_id, 'Kurs ' . $r->currency . ' tidak ada di Standard Exchange Rate', $document);
            }
            if ($error = $engine_error('get_inventory_setup', 'PURCHASE ORDER RECEIPT', null, $divisions[$r->item_rm_id])) {
                $add($r->item_rm_id, $error, $document);
            }
        }

        // Mutasi selain POR per tanggal dokumen (_build_bpm, _build_transaction_rm, _build_supply_sheets, _build_non_supply_sheets, _build_material_requestion)
        $movements = $this->crud->query("
            SELECT modul, ttype, item_rm_id, d, doc_no FROM (
                SELECT 'BPM' AS modul, '' AS ttype, item_rm_id, request_date AS d, request_no AS doc_no FROM bpm
                    WHERE deleted = 0 AND status = 1 AND qty > 0 AND request_date BETWEEN '$from' AND '$to' AND item_rm_id IN ($ids)
                UNION ALL
                SELECT 'TRANSACTION RM', transaction_type, item_rm_id, request_date, request_no FROM transaction_rm
                    WHERE deleted = 0 AND qty > 0 AND transaction_type IN ($types) AND request_date BETWEEN '$from' AND '$to' AND item_rm_id IN ($ids)
                UNION ALL
                SELECT 'SUPPLY SHEET', '', s.item_rm_id, s.request_date, s.request_no FROM supply_sheets s
                    WHERE s.deleted = 0 AND s.status IN (1, 2) AND s.request_date BETWEEN '$from' AND '$to' AND s.item_rm_id IN ($ids)
                    AND (SELECT SUM(i.qty) FROM issued_material_details i WHERE i.request_no = s.request_no AND i.item_rm_id = s.item_rm_id) > 0
                UNION ALL
                SELECT 'NON SUPPLY SHEET', COALESCE(type, ''), item_rm_id, request_date, request_no FROM supply_materials
                    WHERE deleted = 0 AND status = 1 AND qty > 0 AND request_date BETWEEN '$from' AND '$to' AND item_rm_id IN ($ids)
                UNION ALL
                SELECT 'MATERIAL REQUESTION', '', item_rm_id, request_date, request_no FROM supply_requestions
                    WHERE deleted = 0 AND status = 1 AND qty > 0 AND request_date BETWEEN '$from' AND '$to' AND item_rm_id IN ($ids)
            ) m
            GROUP BY modul, ttype, item_rm_id, d, doc_no
            ORDER BY d, doc_no");
        if (empty($movements)) {
            return $issues;
        }

        // Harga jurnal = get_price_rm_for_journal(): PO terakhir s/d tanggal (price x (1 - discount), kurs supplier), jika tidak ada Standard Price RM yang berlaku
        $moved = "'" . implode("','", array_map([$this->db, 'escape_str'], array_unique(array_column($movements, 'item_rm_id')))) . "'";
        $pos = [];
        foreach ($this->crud->query("
            SELECT a.item_rm_id, a.po_date, a.price * (1 - COALESCE(a.discount, 0) / 100) AS price, d.currency
            FROM purchase_orders a
            LEFT JOIN suppliers d ON a.supplier_id = d.id
            WHERE a.deleted = 0 AND a.price > 0 AND a.po_date <= '$to' AND a.item_rm_id IN ($moved)
            ORDER BY a.po_date DESC, a.id DESC") as $po) {
            $pos[$po->item_rm_id][] = $po;
        }
        $stds = [];
        foreach ($this->crud->query("
            SELECT item_rm_id, start_date, end_date, price FROM standard_price_rm
            WHERE deleted = 0 AND start_date <= '$to' AND end_date >= '$from' AND item_rm_id IN ($moved)
            ORDER BY start_date DESC") as $std) {
            $stds[$std->item_rm_id][] = $std;
        }

        foreach ($movements as $m) {
            $document = $m->doc_no . ' (' . $m->d . ')';
            $last_po = null;
            foreach ($pos[$m->item_rm_id] ?? [] as $po) {
                if ($po->po_date <= $m->d) {
                    $last_po = $po;
                    break;
                }
            }
            if ($last_po) {
                if ((float) $last_po->price <= 0) {
                    $add($m->item_rm_id, 'Harga 0 (harga PO terakhir setelah diskon = 0)', $document);
                } elseif ($engine_error('get_rate', $m->d, $last_po->currency)) {
                    $add($m->item_rm_id, 'Kurs ' . $last_po->currency . ' tidak ada di Standard Exchange Rate', $document);
                }
            } else {
                $std_price = 0;
                foreach ($stds[$m->item_rm_id] ?? [] as $std) {
                    if ($std->start_date <= $m->d && $std->end_date >= $m->d) {
                        $std_price = (float) $std->price;
                        break;
                    }
                }
                if ($std_price <= 0) {
                    $add($m->item_rm_id, 'Harga 0 (tidak ada PO s/d tanggal transaksi dan Standard Price RM tidak berlaku)', $document);
                }
            }
            if ($error = $engine_error('get_inventory_setup', $m->modul, $m->ttype !== '' ? $m->ttype : null, $divisions[$m->item_rm_id])) {
                $add($m->item_rm_id, $error, $document);
            }
        }
        return $issues;
    }

    private function getBatchDetailData(array $item_ids, $filter_from, $filter_to)
    {
        $res = [
            'receipts'     => [],
            'issueds'      => [],
            'returns'      => [],
            'os_rms'       => [],
            'bpm_scans'    => [],
            'transactions' => [],
            'po_prices'    => [],
            'users'        => []
        ];

        if (empty($item_ids)) {
            return $res;
        }

        $ids_str = "'" . implode("','", array_map('addslashes', $item_ids)) . "'";

        // Preload users map
        $users_raw = $this->crud->query("SELECT username, name FROM users");
        if (!empty($users_raw)) {
            foreach ($users_raw as $u) {
                $res['users'][$u->username] = $u->name;
            }
        }

        // Preload PO prices
        $po_raw = $this->crud->query("SELECT pr.item_rm_id, pr.receipt_date, pr.specification as pr_spec, po.price, po.specification as po_spec
            FROM purchase_order_receipts pr
            JOIN purchase_orders po ON pr.po_no = po.po_no AND pr.item_rm_id = po.item_rm_id
            WHERE pr.item_rm_id IN ($ids_str) AND pr.receipt_date <= '$filter_to'
            ORDER BY pr.receipt_date DESC, pr.receipt_id DESC");
        if (!empty($po_raw)) {
            foreach ($po_raw as $p) {
                $res['po_prices'][$p->item_rm_id][] = $p;
            }
        }

        // 1. Receipts
        $rc_raw = $this->crud->query("SELECT
                a.item_rm_id,
                a.receipt_no,
                a.receipt_date, 
                a.bc_kind, 
                a.bc_aju, 
                a.bc_document, 
                a.bc_date, 
                SUM(b.qty) as qty_receipt,
                MAX(po.price) as actual_price_in,
                c.name as username
            FROM purchase_order_receipts a 
            JOIN scan_item_receipts b ON a.receipt_id = b.receipt_id
            JOIN users c ON a.created_by = c.username 
            JOIN item_rm irm ON a.item_rm_id = irm.id
            LEFT JOIN purchase_orders po ON a.po_no = po.po_no AND a.item_rm_id = po.item_rm_id
                AND (
                    (UPPER(TRIM(irm.division)) = 'MTS' AND COALESCE(TRIM(a.specification), '') = COALESCE(TRIM(po.specification), ''))
                    OR UPPER(TRIM(COALESCE(irm.division, ''))) != 'MTS'
                )
            WHERE a.item_rm_id IN ($ids_str) AND a.receipt_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY a.item_rm_id, a.receipt_no, a.bc_kind, a.bc_aju, a.bc_document, a.bc_date, a.receipt_id");
        if (!empty($rc_raw)) {
            foreach ($rc_raw as $r) {
                $res['receipts'][$r->item_rm_id][] = $r;
            }
        }

        // 2. Issueds
        $is_raw = $this->crud->query("SELECT item_rm_id, created_by, qty, created_date, label_no, request_no 
            FROM issued_material_details 
            WHERE item_rm_id IN ($ids_str) AND DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");
        if (!empty($is_raw)) {
            foreach ($is_raw as $i) {
                $res['issueds'][$i->item_rm_id][] = $i;
            }
        }

        // 3. Returns
        $rt_raw = $this->crud->query("SELECT
                a.item_rm_id,
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
            WHERE a.item_rm_id IN ($ids_str) AND a.return_date BETWEEN '$filter_from' AND '$filter_to'
            GROUP BY a.item_rm_id, b.label_no");
        if (!empty($rt_raw)) {
            foreach ($rt_raw as $rt) {
                $res['returns'][$rt->item_rm_id][] = $rt;
            }
        }

        // 4. OS RM
        $os_raw = $this->crud->query("SELECT item_rm_id, created_by, created_date, qty 
            FROM os_rm 
            WHERE item_rm_id IN ($ids_str) AND DATE_FORMAT(trans_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");
        if (!empty($os_raw)) {
            foreach ($os_raw as $o) {
                $res['os_rms'][$o->item_rm_id][] = $o;
            }
        }

        // 5. BPM Scans
        $bpm_raw = $this->crud->query("SELECT s.item_rm_id, s.created_by, s.qty, s.created_date, s.label, s.request_date, s.request_id,
                bh.lot_no, " . $this->bpmLotPriceSql() . " as lot_price
            FROM scan_item_bpm s
            LEFT JOIN bpm bh ON bh.request_id = s.request_id AND bh.item_rm_id = s.item_rm_id AND bh.deleted = 0
            WHERE s.item_rm_id IN ($ids_str) AND DATE_FORMAT(s.request_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");
        if (!empty($bpm_raw)) {
            foreach ($bpm_raw as $b) {
                $res['bpm_scans'][$b->item_rm_id][] = $b;
            }
        }

        // 6. Transactions
        $tr_raw = $this->crud->query("SELECT a.item_rm_id, a.request_date, a.transaction_type, a.transaction_kind, a.request_no, a.qty, b.name as username
            FROM transaction_rm a
            JOIN users b ON a.created_by = b.username
            WHERE a.item_rm_id IN ($ids_str) AND a.request_date BETWEEN '$filter_from' AND '$filter_to'");
        if (!empty($tr_raw)) {
            foreach ($tr_raw as $t) {
                $res['transactions'][$t->item_rm_id][] = $t;
            }
        }

        return $res;
    }

    // -------------- PRINT RECAP (HISTORY TRANSACTION INVENTORY RM) => LSB -------------
    public function print_without_actual($option = "")
    {
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
        $filter_item_category = $this->rmCategoryId(); // Inventory Valuation RM: hanya kategori RAW MATERIAL

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
                (COALESCE(t_rm.qty_bpm, 0) + COALESCE(o.qty_bpm_scan, 0)) as bpm_qty, 
                COALESCE(t_rm.qty_adj_in, 0) as adj_in_qty, 

                -- QTY OUT DETAILS
                COALESCE(imd.qty_supply_sheet, 0) as qty_supply_sheet,
                COALESCE(imd.qty_mat_request, 0) as qty_mat_request,
                (COALESCE(t_rm.qty_kanban_wo, 0) + COALESCE(imd.qty_wip, 0) + COALESCE(imd.qty_req_prod, 0)) as qty_kanban,
                COALESCE(imd.qty_req_subcont, 0) as qty_kanban_sj,
                COALESCE(imd.qty_req_cust, 0) as qty_kanban_sp,
                COALESCE(t_rm.qty_bpb, 0) as bpb_qty, 
                COALESCE(t_rm.qty_adj_out, 0) as adj_out_qty,

                -- ACTUAL IN AMOUNT (Uang Riil dari PO)
                COALESCE(act_in.total_actual_amt_in, 0) as total_actual_amt_in,                
                d.max_receipt_date
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN item_family_subs l ON a.item_sub_family_id = l.id
            
            -- get specification 
            LEFT JOIN (
                SELECT a.item_rm_id, MAX(COALESCE(f.specification, a.specification)) as specification
                FROM purchase_order_receipts a
                LEFT JOIN purchase_orders f ON a.po_no = f.po_no AND a.item_rm_id = f.item_rm_id
                GROUP BY a.item_rm_id
            ) item_spec ON item_spec.item_rm_id = a.id
            
            -- Standard Price Lookup
            LEFT JOIN (SELECT item_rm_id, currency, price FROM standard_price_rm WHERE '$filter_to' BETWEEN `start_date` AND `end_date` AND deleted = 0) std_price ON a.id = std_price.item_rm_id

            -- Actual Amount In (Sum Qty * Price PO)
            LEFT JOIN (
                SELECT pr.item_rm_id, SUM(sr.qty * po.price) as total_actual_amt_in
                FROM purchase_order_receipts pr
                JOIN scan_item_receipts sr ON pr.receipt_id = sr.receipt_id
                JOIN item_rm irm ON pr.item_rm_id = irm.id
                JOIN purchase_orders po ON pr.po_no = po.po_no AND pr.item_rm_id = po.item_rm_id
                    AND (
                        (UPPER(TRIM(irm.division)) = 'MTS' AND COALESCE(TRIM(pr.specification), '') = COALESCE(TRIM(po.specification), ''))
                        OR (UPPER(TRIM(COALESCE(irm.division, ''))) != 'MTS')
                    )
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

            -- Transaction RM Consolidated (BPM, ADJ IN, KANBAN WO, ADJ OUT, BPB)
            LEFT JOIN (
                SELECT a.item_rm_id,
                    SUM(CASE WHEN a.transaction_type = 'BPM' THEN a.qty ELSE 0 END) AS qty_bpm,
                    SUM(CASE WHEN a.transaction_type = 'ADJ IN STO' THEN a.qty ELSE 0 END) AS qty_adj_in,
                    SUM(CASE WHEN a.transaction_type = 'KANBAN WO' THEN a.qty ELSE 0 END) AS qty_kanban_wo,
                    SUM(CASE WHEN a.transaction_type = 'ADJ OUT STO' THEN a.qty ELSE 0 END) AS qty_adj_out,
                    SUM(CASE WHEN a.transaction_type = 'BPB' THEN a.qty ELSE 0 END) AS qty_bpb
                FROM transaction_rm a
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                GROUP BY a.item_rm_id
            ) t_rm ON a.id = t_rm.item_rm_id

            -- BPM SCAN
            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_bpm_scan
                FROM scan_item_bpm a
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                GROUP BY a.item_rm_id) o ON a.id = o.item_rm_id

            -- ISSUED MATERIAL Consolidated
            LEFT JOIN (
                SELECT 
                    a.item_rm_id,
                    SUM(CASE WHEN a.request_no LIKE '%SH-%' THEN a.qty ELSE 0 END) AS qty_supply_sheet,
                    SUM(CASE WHEN a.request_no LIKE '%PRQ-%' THEN a.qty ELSE 0 END) AS qty_mat_request,
                    SUM(CASE WHEN a.`type` LIKE '%WIP%' THEN a.qty ELSE 0 END) AS qty_wip,
                    SUM(CASE WHEN a.request_no LIKE '%REQ-%' AND b.type = 'Issued Production' THEN a.qty ELSE 0 END) AS qty_req_prod,
                    SUM(CASE WHEN a.request_no LIKE '%REQ-%' AND b.type = 'Issued Subcont' THEN a.qty ELSE 0 END) AS qty_req_subcont,
                    SUM(CASE WHEN a.request_no LIKE '%REQ-%' AND b.type = 'Issued Customer' THEN a.qty ELSE 0 END) AS qty_req_cust
                FROM issued_material_details a
                LEFT JOIN supply_materials b ON a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id AND a.request_no LIKE '%REQ-%'
                WHERE a.created_date >= '$filter_from' AND a.created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                GROUP BY a.item_rm_id
            ) imd ON a.id = imd.item_rm_id

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
                <h3 style="margin:0;">INVENTORY VALUATION RM <i>' . $display_title . '</i> </h3>
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
            if ($record->standard_currency == 'USD' && !empty($record->max_receipt_date)) {
                $rate = $get_rate($record->max_receipt_date);
            }

            // standard Price
            $std_p = (float)$record->standard_price * $rate;

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
                    
                    <td align="right">'.number_format((float) $b_qty, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format((float) $b_std_a, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format((float) $b_act_a, 2).'</td>
                    <td>'.number_format((float) $b_variance, 2).'</td>
                    
                    <td align="right">'.number_format((float) $i_qty, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format((float) $i_std_a, 2).'</td>
                    <td>'.number_format((float) $i_act_p, 2).'</td>
                    <td>'.number_format((float) $i_act_a, 2).'</td>
                    <td>'.number_format((float) $i_variance, 2).'</td>
                    
                    <td align="right">'.number_format((float) $o_qty, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format((float) $o_std_a, 2).'</td>
                    <td>'.number_format((float) $o_act_p, 2).'</td>
                    <td>'.number_format((float) $o_act_a, 2).'</td>
                    <td>'.number_format((float) $o_variance, 2).'</td>

                    <td align="right">'.number_format((float) $e_qty, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format((float) $e_std_a, 2).'</td>
                    <td>'.number_format((float) $e_act_p, 2).'</td>
                    <td>'.number_format((float) $e_act_a, 2).'</td>
                    <td>'.number_format((float) $e_variance, 2).'</td>

                    <td align="right">'.number_format((float) $record->receipt_qty, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format($record->receipt_qty*$std_p, 2).'</td>
                    <td align="right">'.number_format((float) $avg_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_purchase, 2).'</td>

                    <td align="right">'.number_format((float) $record->bpm_qty, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format($record->bpm_qty*$std_p, 2).'</td>
                    <td align="right">'.number_format((float) $avg_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_bpm, 2).'</td>

                    <td align="right">'.number_format((float) $record->adj_in_qty, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format($record->adj_in_qty*$std_p, 2).'</td>
                    <td align="right">'.number_format((float) $avg_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_adjin, 2).'</td>

                    <td align="right">'.number_format((float) $record->qty_supply_sheet, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format($record->qty_supply_sheet*$std_p, 2).'</td>
                    <td align="right">'.number_format((float) $avg_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_supply, 2).'</td>

                    <td align="right">'.number_format((float) $record->qty_mat_request, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format($record->qty_mat_request*$std_p, 2).'</td>
                    <td align="right">'.number_format((float) $avg_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_req, 2).'</td>

                    <td align="right">'.number_format((float) $record->qty_kanban, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format($record->qty_kanban*$std_p, 2).'</td>
                    <td align="right">'.number_format((float) $avg_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_kanban, 2).'</td>

                    <td align="right">'.number_format((float) $record->qty_kanban_sj, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format(($record->qty_kanban_sj)*$std_p, 2).'</td>
                    <td align="right">'.number_format((float) $avg_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_kanban_sj, 2).'</td>

                    <td align="right">'.number_format((float) $record->qty_kanban_sp, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format(($record->qty_kanban_sp)*$std_p, 2).'</td>
                    <td align="right">'.number_format((float) $avg_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_kanban_sp, 2).'</td>

                    <td align="right">'.number_format((float) $record->bpb_qty, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format($record->bpb_qty*$std_p, 2).'</td>
                    <td align="right">'.number_format((float) $avg_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_bpb, 2).'</td>

                    <td align="right">'.number_format((float) $record->adj_out_qty, 2).'</td>
                    <td>'.number_format((float) $std_p, 2).'</td>
                    <td>'.number_format($record->adj_out_qty*$std_p, 2).'</td>
                    <td align="right">'.number_format((float) $avg_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_adjout, 2).'</td>
                </tr>
            ';
        }

        $html .= '<tr style="font-weight:bold;">
            <td colspan="8" style="text-align:right;"><b>GRAND TOTAL</b></td>

            <td align="right">'.number_format((float) $total_b_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_b, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_b, 2).'</td>
            <td></td>

            <td align="right">'.number_format((float) $total_i_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_i, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_i, 2).'</td>
            <td></td>

            <td align="right">'.number_format((float) $total_o_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_o, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_o, 2).'</td>
            <td></td>

            <td align="right">'.number_format((float) $total_e_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_e, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_e, 2).'</td>
            <td></td>

            <td align="right">'.number_format((float) $total_receipt_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_purchase, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_purchase, 2).'</td>

            <td align="right">'.number_format((float) $total_bpm_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_bpm, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_bpm, 2).'</td>

            <td align="right">'.number_format((float) $total_adj_in_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_adjin, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_adjin, 2).'</td>

            <td align="right">'.number_format((float) $total_qty_supply_sheet, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_supply, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_supply, 2).'</td>

            <td align="right">'.number_format((float) $total_qty_mat_request, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_req, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_req, 2).'</td>

            <td align="right">'.number_format((float) $total_qty_kanban, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_kanban, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_kanban, 2).'</td>

            <td align="right">'.number_format((float) $total_qty_kanban_sj, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_kanban_sj, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_kanban_sj, 2).'</td>

            <td align="right">'.number_format((float) $total_qty_kanban_sp, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_kanban_sp, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_kanban_sp, 2).'</td>

            <td align="right">'.number_format((float) $total_bpb_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_bpb, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_bpb, 2).'</td>

            <td align="right">'.number_format((float) $total_adj_out_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_adjout, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_adjout, 2).'</td>
        </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }

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
        $filter_item_category = $this->rmCategoryId(); // Inventory Valuation RM: hanya kategori RAW MATERIAL

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
                        ->get('inventory_rm_actual')
                        ->row();
        $start_system = ($cutoff_data) ? $cutoff_data->cutoff_date : '2026-01-01';

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
                (COALESCE(t_rm.qty_bpm, 0) + COALESCE(o.qty_bpm_scan, 0)) as bpm_qty, 
                COALESCE(t_rm.qty_adj_in, 0) as adj_in_qty, 

                -- QTY OUT DETAILS
                COALESCE(imd.qty_supply_sheet, 0) as qty_supply_sheet,
                COALESCE(imd.qty_mat_request, 0) as qty_mat_request,
                (COALESCE(t_rm.qty_kanban_wo, 0) + COALESCE(imd.qty_wip, 0) + COALESCE(imd.qty_req_prod, 0)) as qty_kanban,
                COALESCE(imd.qty_req_subcont, 0) as qty_kanban_sj,
                COALESCE(imd.qty_req_cust, 0) as qty_kanban_sp,
                COALESCE(t_rm.qty_bpb, 0) as bpb_qty, 
                COALESCE(t_rm.qty_adj_out, 0) as adj_out_qty,

                -- ACTUAL FROM UPLOAD
                COALESCE(actual.price, 0) as actual_price,
                COALESCE(actual.qty, 0) as actual_qty,
                actual.currency as upload_currency,

                -- ACTUAL IN AMOUNT (Uang Riil dari PO)
                COALESCE(act_in.total_actual_amt_in, 0) as total_actual_amt_in,                
                COALESCE(bpm_act.bpm_scan_qty, 0) as bpm_scan_qty,
                COALESCE(bpm_act.bpm_actual_amt, 0) as bpm_actual_amt,
                d.max_receipt_date
            FROM item_rm a 
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN item_family_subs l ON a.item_sub_family_id = l.id

            -- get actual from upload
            LEFT JOIN inventory_rm_actual actual ON (actual.part_no = a.number OR actual.item_rm_id = a.id)
                AND actual.cutoff_date = '$start_system' -- perbaikan perhitungan begin qty per bulan
            
            -- get specification 
            LEFT JOIN (
                SELECT a.item_rm_id, MAX(COALESCE(f.specification, a.specification)) as specification
                FROM purchase_order_receipts a
                LEFT JOIN purchase_orders f ON a.po_no = f.po_no AND a.item_rm_id = f.item_rm_id
                GROUP BY a.item_rm_id
            ) item_spec ON item_spec.item_rm_id = a.id
            
            -- Standard Price Lookup
            LEFT JOIN (SELECT item_rm_id, currency, price FROM standard_price_rm WHERE '$filter_to' BETWEEN `start_date` AND `end_date` AND deleted = 0) std_price ON a.id = std_price.item_rm_id

            -- Actual Amount In (Sum Qty * Price PO)
            LEFT JOIN (
                SELECT pr.item_rm_id, SUM(sr.qty * po.price) as total_actual_amt_in
                FROM purchase_order_receipts pr
                JOIN scan_item_receipts sr ON pr.receipt_id = sr.receipt_id
                JOIN item_rm irm ON pr.item_rm_id = irm.id
                JOIN purchase_orders po ON pr.po_no = po.po_no AND pr.item_rm_id = po.item_rm_id
                    AND (
                        (UPPER(TRIM(irm.division)) = 'MTS' AND COALESCE(TRIM(pr.specification), '') = COALESCE(TRIM(po.specification), ''))
                        OR (UPPER(TRIM(COALESCE(irm.division, ''))) != 'MTS')
                    )
                WHERE pr.receipt_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY pr.item_rm_id
            ) act_in ON a.id = act_in.item_rm_id

            -- Actual Amount BPM (harga PO dari lot POR)
            LEFT JOIN (" . $this->bpmActualAmountSql($filter_from, $filter_to) . ") bpm_act ON a.id = bpm_act.item_rm_id

            -- Qty In Lookup
            LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) AS qty_scan_in, MAX(b.receipt_date) as max_receipt_date FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id

            -- Begin Stock Lookup
            -- PERBAIKAN: Carry Over Calculation (Sejak Cutoff s/d H-1 Filter From)
            LEFT JOIN (
                SELECT 
                    a.id, 
                    (COALESCE(b.q_in,0) + COALESCE(c.q_in,0) + COALESCE(d.q_in,0) + COALESCE(e.q_in,0) + COALESCE(h.q_in,0)) - 
                    (COALESCE(f.q_out,0) + COALESCE(g.q_out,0)) as begin_stock
                FROM item_rm a
                LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) as q_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date >= '$start_system' AND b.receipt_date < '$filter_from' GROUP BY 1) b ON a.id = b.item_rm_id
                LEFT JOIN (SELECT x.item_rm_id, SUM(x.qty) as q_in FROM os_rm x WHERE x.trans_date >= '$start_system' AND x.trans_date < '$filter_from' GROUP BY 1) c ON a.id = c.item_rm_id
                LEFT JOIN (SELECT x.item_rm_id, SUM(x.qty) as q_in FROM transaction_rm x WHERE x.transaction_kind = 'IN' AND x.request_date >= '$start_system' AND x.request_date < '$filter_from' GROUP BY 1) d ON a.id = d.item_rm_id
                LEFT JOIN (SELECT x.item_rm_id, SUM(c.qty) as q_in FROM return_materials x JOIN scan_item_receipts c ON x.return_id = c.receipt_id WHERE x.return_date >= '$start_system' AND x.return_date < '$filter_from' GROUP BY 1) e ON a.id = e.item_rm_id
                LEFT JOIN (SELECT x.item_rm_id, SUM(x.qty) as q_out FROM issued_material_details x WHERE x.created_date >= '$start_system' AND x.created_date < '$filter_from' GROUP BY 1) f ON a.id = f.item_rm_id
                LEFT JOIN (SELECT x.item_rm_id, SUM(x.qty) as q_out FROM transaction_rm x WHERE x.transaction_kind = 'OUT' AND x.request_date >= '$start_system' AND x.request_date < '$filter_from' GROUP BY 1) g ON a.id = g.item_rm_id
                LEFT JOIN (SELECT x.item_rm_id, SUM(x.qty) as q_in FROM scan_item_bpm x WHERE x.request_date >= '$start_system' AND x.request_date < '$filter_from' GROUP BY 1) h ON a.id = h.item_rm_id
            ) x ON a.id = x.id

            -- Transaction RM Consolidated (BPM, ADJ IN, KANBAN WO, ADJ OUT, BPB)
            LEFT JOIN (
                SELECT a.item_rm_id,
                    SUM(CASE WHEN a.transaction_type = 'BPM' THEN a.qty ELSE 0 END) AS qty_bpm,
                    SUM(CASE WHEN a.transaction_type = 'ADJ IN STO' THEN a.qty ELSE 0 END) AS qty_adj_in,
                    SUM(CASE WHEN a.transaction_type = 'KANBAN WO' THEN a.qty ELSE 0 END) AS qty_kanban_wo,
                    SUM(CASE WHEN a.transaction_type = 'ADJ OUT STO' THEN a.qty ELSE 0 END) AS qty_adj_out,
                    SUM(CASE WHEN a.transaction_type = 'BPB' THEN a.qty ELSE 0 END) AS qty_bpb
                FROM transaction_rm a
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                GROUP BY a.item_rm_id
            ) t_rm ON a.id = t_rm.item_rm_id

            -- BPM SCAN
            LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_bpm_scan
                FROM scan_item_bpm a
                WHERE a.request_date >= '$filter_from' AND a.request_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                GROUP BY a.item_rm_id) o ON a.id = o.item_rm_id

            -- ISSUED MATERIAL Consolidated
            LEFT JOIN (
                SELECT 
                    a.item_rm_id,
                    SUM(CASE WHEN a.request_no LIKE '%SH-%' THEN a.qty ELSE 0 END) AS qty_supply_sheet,
                    SUM(CASE WHEN a.request_no LIKE '%PRQ-%' THEN a.qty ELSE 0 END) AS qty_mat_request,
                    SUM(CASE WHEN a.`type` LIKE '%WIP%' THEN a.qty ELSE 0 END) AS qty_wip,
                    SUM(CASE WHEN a.request_no LIKE '%REQ-%' AND b.type = 'Issued Production' THEN a.qty ELSE 0 END) AS qty_req_prod,
                    SUM(CASE WHEN a.request_no LIKE '%REQ-%' AND b.type = 'Issued Subcont' THEN a.qty ELSE 0 END) AS qty_req_subcont,
                    SUM(CASE WHEN a.request_no LIKE '%REQ-%' AND b.type = 'Issued Customer' THEN a.qty ELSE 0 END) AS qty_req_cust
                FROM issued_material_details a
                LEFT JOIN supply_materials b ON a.request_no = b.request_no AND a.item_rm_id = b.item_rm_id AND a.request_no LIKE '%REQ-%'
                WHERE a.created_date >= '$filter_from' AND a.created_date < DATE_ADD('$filter_to', INTERVAL 1 DAY)
                GROUP BY a.item_rm_id
            ) imd ON a.id = imd.item_rm_id

            WHERE c.id LIKE '%$filter_item_category%' 
            AND b.number LIKE '%$filter_item_family%' 
            AND a.id LIKE '%$filter_items%' 
            AND a.division LIKE '%$filter_division%' 
            GROUP BY a.id
            ORDER BY c.name DESC, b.name DESC, a.number";

        $records = $this->crud->query($query);

        // Validasi: part yang kemungkinan gagal auto posting Journal Inventory (harga 0, kurs, Journal Type)
        $journal_issues = $this->journalInventoryIssues(array_column($records, 'division', 'id'), $filter_from, $filter_to);

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
                <h3 style="margin:0;">INVENTORY VALUATION RM <i>' . $display_title . '</i> </h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';

        // Validasi tidak ditampilkan di Print Preview: dikirim sebagai JSON, dibaca view untuk badge & dialog tombol Validasi Jurnal
        if ($option != "excel") {
            $parts = array_column($records, null, 'id');
            $issue_rows = [];
            foreach ($journal_issues as $item_id => $reasons) {
                foreach ($reasons as $reason => $documents) {
                    $issue_rows[] = [
                        'reason'    => $reason,
                        'number'    => $parts[$item_id]->number,
                        'name'      => $parts[$item_id]->name,
                        'division'  => $parts[$item_id]->division,
                        'total_doc' => count($documents),
                        'documents' => array_keys($documents),
                    ];
                }
            }
            usort($issue_rows, function ($a, $b) {
                return strcmp($a['reason'], $b['reason']) ?: strcmp($a['number'], $b['number']);
            });
            $html .= '<script type="application/json" id="journal_issues">'
                . json_encode(['parts' => count($journal_issues), 'rows' => $issue_rows], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE)
                . '</script>';
        }

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
            if ($record->standard_currency == 'USD' && !empty($record->max_receipt_date)) {
                $rate = $get_rate($record->max_receipt_date);
            }
            $std_price = (float)$record->standard_price * $rate;
            
            // Begin 
            $b_qty      = (float)$record->actual_qty;
            $b_std_a    = $b_qty * $std_price;
            // Actual price begin dari upload (sesuai cutoff date), fallback ke standard price
            $b_act_p    = $std_price;
            if ((float)$record->actual_price > 0) {
                $upload_rate = ($record->upload_currency == 'USD') ? $get_rate($start_system) : 1;
                $b_act_p     = (float)$record->actual_price * $upload_rate;
            }
            $b_act_a    = $b_qty * $b_act_p;
            $b_variance = $b_act_a - $b_std_a;

            // In
            $i_qty      = (float)$record->receipt_qty + (float)$record->bpm_qty + (float)$record->adj_in_qty;
            $i_std_a    = $i_qty * $std_price;
            $i_act_a    = ((float)$record->total_actual_amt_in + (float)$record->bpm_actual_amt) * $rate;
            $i_act_p    = 0;
            if ($i_qty > 0) {
                $i_act_p = $i_act_a / $i_qty;
            }
            $i_variance = $i_act_a - $i_std_a;

            // Out
            $o_qty      = (float)$record->qty_supply_sheet + (float)$record->qty_mat_request + (float)$record->qty_kanban + (float)$record->qty_kanban_sj + (float)$record->qty_kanban_sp + (float)$record->bpb_qty + (float)$record->adj_out_qty;
            $o_std_a    = $o_qty * $std_price;
            // OUT Actual Price : (amount begin + amount in) / (qty begin + qty in)
            $o_act_p    = 0;
            if (($b_qty + $i_qty) > 0) {
                $o_act_p = ($b_act_a + $i_act_a) / ($b_qty + $i_qty);
            }
            $o_act_a    = $o_qty * $o_act_p; 
            $o_variance = $o_act_a - $o_std_a;
            
            // Ending
            $e_qty      = ($b_qty + $i_qty) - $o_qty;
            $e_std_a    = $e_qty * $std_price;
            $e_act_a    = ($b_act_a + $i_act_a) - $o_act_a;
            $e_act_p    = 0;
            if ($e_qty > 0) {
                $e_act_p = $e_act_a / $e_qty;
            }
            $e_variance = $e_act_a - $e_std_a;

            // -- CALCULATE AMOUNT IN (Based on Actual Purchase & In Price) --
            $amt_act_purchase = (float)$record->total_actual_amt_in * $rate; 
            // BPM scan pakai harga lot POR, BPM transaction_rm (lama) pakai harga rata-rata In
            $amt_act_bpm      = ((float)$record->bpm_actual_amt * $rate) + (((float)$record->bpm_qty - (float)$record->bpm_scan_qty) * $i_act_p); 
            $amt_act_adjin    = $record->adj_in_qty * $i_act_p;

            // -- CALCULATE AMOUNT OUT (Based on Out Actual Price) --
            $amt_act_supply    = $record->qty_supply_sheet * $o_act_p;
            $amt_act_req       = $record->qty_mat_request * $o_act_p;
            $amt_act_kanban    = $record->qty_kanban * $o_act_p;
            $amt_act_bpb       = $record->bpb_qty * $o_act_p;
            $amt_act_adjout    = $record->adj_out_qty * $o_act_p;
            $amt_act_kanban_sj = $record->qty_kanban_sj * $o_act_p;
            $amt_act_kanban_sp = $record->qty_kanban_sp * $o_act_p;

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
            $total_std_purchase  += ($record->receipt_qty * $std_price);
            $total_std_bpm       += ($record->bpm_qty * $std_price);
            $total_std_adjin     += ($record->adj_in_qty * $std_price);
            $total_std_supply    += ($record->qty_supply_sheet * $std_price);
            $total_std_req       += ($record->qty_mat_request * $std_price);
            $total_std_kanban    += ($record->qty_kanban * $std_price);
            $total_std_bpb       += ($record->bpb_qty * $std_price);
            $total_std_adjout    += ($record->adj_out_qty * $std_price);
            $total_std_kanban_sj += ($record->qty_kanban_sj * $std_price);
            $total_std_kanban_sp += ($record->qty_kanban_sp * $std_price);

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

            // Baris part yang kemungkinan gagal auto posting Journal Inventory: merah + tooltip dokumen (maks 5 per alasan)
            $row_attr = '';
            if (isset($journal_issues[$record->id])) {
                $tooltip = [];
                foreach ($journal_issues[$record->id] as $reason => $documents) {
                    $documents = array_keys($documents);
                    $tooltip[] = $reason . ': ' . implode(', ', array_slice($documents, 0, 5)) . (count($documents) > 5 ? ', +' . (count($documents) - 5) . ' lagi' : '');
                }
                $row_attr = ' style="background-color: #FFC7CE !important;" title="' . htmlspecialchars(implode("\n", $tooltip)) . '"';
            }

            $html .= '<tr' . $row_attr . '>
                    <td align="center">'.$no++.'</td>
                    <td>'.$record->number.'</td>
                    <td>'.$record->name.'</td>
                    <td>'.$record->uom.'</td>
                    <td>'.$record->division.'</td>
                    <td>'.$record->category_name.'</td>
                    <td>'.$record->prodfam.'</td>
                    <td>'.$record->specification.'</td>
                    
                    <td align="right">'.number_format((float) $b_qty, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format((float) $b_std_a, 2).'</td>
                    <td>'.number_format((float) $b_act_p, 2).'</td>
                    <td>'.number_format((float) $b_act_a, 2).'</td>
                    <td>'.number_format((float) $b_variance, 2).'</td>
                    
                    <td align="right">'.number_format((float) $i_qty, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format((float) $i_std_a, 2).'</td>
                    <td>'.number_format((float) $i_act_p, 2).'</td>
                    <td>'.number_format((float) $i_act_a, 2).'</td>
                    <td>'.number_format((float) $i_variance, 2).'</td>
                    
                    <td align="right">'.number_format((float) $o_qty, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format((float) $o_std_a, 2).'</td>
                    <td>'.number_format((float) $o_act_p, 2).'</td>
                    <td>'.number_format((float) $o_act_a, 2).'</td>
                    <td>'.number_format((float) $o_variance, 2).'</td>

                    <td align="right">'.number_format((float) $e_qty, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format((float) $e_std_a, 2).'</td>
                    <td>'.number_format((float) $e_act_p, 2).'</td>
                    <td>'.number_format((float) $e_act_a, 2).'</td>
                    <td>'.number_format((float) $e_variance, 2).'</td>

                    <td align="right">'.number_format((float) $record->receipt_qty, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format($record->receipt_qty*$std_price, 2).'</td>
                    <td align="right">'.number_format($record->receipt_qty > 0 ? ($amt_act_purchase / $record->receipt_qty) : $i_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_purchase, 2).'</td>

                    <td align="right">'.number_format((float) $record->bpm_qty, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format($record->bpm_qty*$std_price, 2).'</td>
                    <td align="right">'.number_format($record->bpm_qty > 0 ? ($amt_act_bpm / $record->bpm_qty) : $i_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_bpm, 2).'</td>

                    <td align="right">'.number_format((float) $record->adj_in_qty, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format($record->adj_in_qty*$std_price, 2).'</td>
                    <td align="right">'.number_format((float) $i_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_adjin, 2).'</td>

                    <td align="right">'.number_format((float) $record->qty_supply_sheet, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format($record->qty_supply_sheet*$std_price, 2).'</td>
                    <td align="right">'.number_format((float) $o_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_supply, 2).'</td>

                    <td align="right">'.number_format((float) $record->qty_mat_request, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format($record->qty_mat_request*$std_price, 2).'</td>
                    <td align="right">'.number_format((float) $o_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_req, 2).'</td>

                    <td align="right">'.number_format((float) $record->qty_kanban, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format($record->qty_kanban*$std_price, 2).'</td>
                    <td align="right">'.number_format((float) $o_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_kanban, 2).'</td>

                    <td align="right">'.number_format((float) $record->qty_kanban_sj, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format(($record->qty_kanban_sj)*$std_price, 2).'</td>
                    <td align="right">'.number_format((float) $o_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_kanban_sj, 2).'</td>

                    <td align="right">'.number_format((float) $record->qty_kanban_sp, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format(($record->qty_kanban_sp)*$std_price, 2).'</td>
                    <td align="right">'.number_format((float) $o_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_kanban_sp, 2).'</td>

                    <td align="right">'.number_format((float) $record->bpb_qty, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format($record->bpb_qty*$std_price, 2).'</td>
                    <td align="right">'.number_format((float) $o_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_bpb, 2).'</td>

                    <td align="right">'.number_format((float) $record->adj_out_qty, 2).'</td>
                    <td>'.number_format((float) $std_price, 2).'</td>
                    <td>'.number_format($record->adj_out_qty*$std_price, 2).'</td>
                    <td align="right">'.number_format((float) $o_act_p, 2).'</td>
                    <td align="right">'.number_format((float) $amt_act_adjout, 2).'</td>
                </tr>
            ';
        }

        $html .= '<tr style="font-weight:bold;">
            <td colspan="8" style="text-align:right;"><b>GRAND TOTAL</b></td>

            <td align="right">'.number_format((float) $total_b_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_b, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_b, 2).'</td>
            <td></td>

            <td align="right">'.number_format((float) $total_i_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_i, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_i, 2).'</td>
            <td></td>

            <td align="right">'.number_format((float) $total_o_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_o, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_o, 2).'</td>
            <td></td>

            <td align="right">'.number_format((float) $total_e_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_e, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_e, 2).'</td>
            <td></td>

            <td align="right">'.number_format((float) $total_receipt_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_purchase, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_purchase, 2).'</td>

            <td align="right">'.number_format((float) $total_bpm_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_bpm, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_bpm, 2).'</td>

            <td align="right">'.number_format((float) $total_adj_in_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_adjin, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_adjin, 2).'</td>

            <td align="right">'.number_format((float) $total_qty_supply_sheet, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_supply, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_supply, 2).'</td>

            <td align="right">'.number_format((float) $total_qty_mat_request, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_req, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_req, 2).'</td>

            <td align="right">'.number_format((float) $total_qty_kanban, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_kanban, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_kanban, 2).'</td>

            <td align="right">'.number_format((float) $total_qty_kanban_sj, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_kanban_sj, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_kanban_sj, 2).'</td>

            <td align="right">'.number_format((float) $total_qty_kanban_sp, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_kanban_sp, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_kanban_sp, 2).'</td>

            <td align="right">'.number_format((float) $total_bpb_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_bpb, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_bpb, 2).'</td>

            <td align="right">'.number_format((float) $total_adj_out_qty, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_std_adjout, 2).'</td>
            <td></td>
            <td align="right">'.number_format((float) $total_act_adjout, 2).'</td>
        </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }


    // -------------- PRINT DETAIL (INVENTORY RM) -------------
    public function print_detail_without_actual($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=inventory_rm_standard_actual_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_item_category = $this->rmCategoryId(); // Inventory Valuation RM: hanya kategori RAW MATERIAL
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
                (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(t_rm.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) AS qty_in,
                (COALESCE(h.qty_issued, 0) + COALESCE(t_rm.qty_trans_rm_out, 0)) AS qty_out,

                -- ACTUAL AMOUNT CALCULATION (JOIN TO PO)
                COALESCE(calc_in.total_actual_in, 0) as actual_amount_in,
                COALESCE(calc_out.total_actual_out, 0) as actual_amount_out,
                d.receipt_date -- used for rate lookup
            FROM item_rm a
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN item_family_subs subfam ON a.item_sub_family_id = subfam.id

            -- get specification 
            LEFT JOIN (
                SELECT a.item_rm_id, MAX(COALESCE(f.specification, a.specification)) as specification
                FROM purchase_order_receipts a
                LEFT JOIN purchase_orders f ON a.po_no = f.po_no AND a.item_rm_id = f.item_rm_id
                GROUP BY a.item_rm_id
            ) item_spec ON item_spec.item_rm_id = a.id
            
            -- Standard Price
            LEFT JOIN (SELECT item_rm_id, currency, price from standard_price_rm where '$filter_to' BETWEEN `start_date` AND `end_date` AND deleted = 0) aa on a.id = aa.item_rm_id

            -- QTY IN Logic
            LEFT JOIN (SELECT MAX(b.receipt_date) AS receipt_date, b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
            
            -- ACTUAL AMOUNT IN (Logic: Sum of Qty Scan * PO Price)
            LEFT JOIN (
                SELECT pr.item_rm_id, SUM(sr.qty * po.price) as total_actual_in
                FROM purchase_order_receipts pr
                JOIN scan_item_receipts sr ON pr.receipt_id = sr.receipt_id
                JOIN item_rm irm ON pr.item_rm_id = irm.id
                JOIN purchase_orders po ON pr.po_no = po.po_no AND pr.item_rm_id = po.item_rm_id
                    AND (
                        (UPPER(TRIM(irm.division)) = 'MTS' AND COALESCE(TRIM(pr.specification), '') = COALESCE(TRIM(po.specification), ''))
                        OR (UPPER(TRIM(COALESCE(irm.division, ''))) != 'MTS')
                    )
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

            -- Query pendukung (e, g, h, k, t_rm)
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (
                SELECT item_rm_id, 
                    SUM(CASE WHEN transaction_kind = 'IN' THEN qty ELSE 0 END) AS qty_trans_rm_in,
                    SUM(CASE WHEN transaction_kind = 'OUT' THEN qty ELSE 0 END) AS qty_trans_rm_out
                FROM transaction_rm 
                WHERE request_date BETWEEN '$filter_from' AND '$filter_to' 
                GROUP BY item_rm_id
            ) t_rm ON a.id = t_rm.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
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
        $item_ids = !empty($records) ? array_map(function($r) { return $r->id; }, $records) : [];
        $batch_data = ($filter_display == "DETAIL") ? $this->getBatchDetailData($item_ids, $filter_from, $filter_to) : [];

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
                <h3 style="margin:0;">INVENTORY VALUATION RM <i>' . $display_title . '</i> </h3>
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
            // OUT Actual Price : (amount begin + amount in) / (qty begin + qty in)
            $out_act_price = 0;
            if (($begin_qty + $in_qty) > 0) {
                $out_act_price = ($begin_act_amt + $in_act_amt) / ($begin_qty + $in_qty);
            }
            $out_act_amt = $out_qty * $out_act_price;

            $end_qty = ($begin_qty + $in_qty) - $out_qty;
            $end_std_amt = ($begin_std_amt + $in_std_amt) - $out_std_amt;
            $end_act_amt = ($begin_act_amt + $in_act_amt) - $out_act_amt;

            $end_act_price = 0;
            if ($end_qty > 0) {
                $end_act_price = $end_act_amt / $end_qty;
            }

            // Add to Grand Total
            foreach ($grandtotals as $key => $val) {
                $grandtotals[$key] += ${$key};
            }

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
                        <td align="right">'.number_format((float) $rate, 2).'</td>
                        
                        <td align="right">'.number_format((float) $begin_qty, 2).'</td>
                        <td align="right">'.number_format((float) $std_price_rate, 2).'</td>
                        <td align="right">'.number_format((float) $begin_std_amt, 2).'</td>
                        <td align="right">'.number_format((float) $begin_act_price, 2).'</td>
                        <td align="right">'.number_format((float) $begin_act_amt, 2).'</td>

                        <td align="right">'.number_format((float) $in_qty, 2).'</td>
                        <td align="right">'.number_format((float) $std_price_rate, 2).'</td>
                        <td align="right">'.number_format((float) $in_std_amt, 2).'</td>
                        <td align="right">'.number_format((float) $in_act_price, 2).'</td>
                        <td align="right">'.number_format((float) $in_act_amt, 2).'</td>

                        <td align="right">'.number_format((float) $out_qty, 2).'</td>
                        <td align="right">'.number_format((float) $std_price_rate, 2).'</td>
                        <td align="right">'.number_format((float) $out_std_amt, 2).'</td>
                        <td align="right">'.number_format((float) $out_act_price, 2).'</td>
                        <td align="right">'.number_format((float) $out_act_amt, 2).'</td>

                        <td align="right">'.number_format((float) $end_qty, 2).'</td>
                        <td align="right">'.number_format((float) $std_price_rate, 2).'</td>
                        <td align="right">'.number_format((float) $end_std_amt, 2).'</td>
                        <td align="right">'.number_format((float) $end_act_price, 2).'</td>
                        <td align="right">'.number_format((float) $end_act_amt, 2).'</td>
                    </tr>';
            
            // (Logika Detail Transactions)
            if ($filter_display == "DETAIL") {
                // 1. Inisialisasi awal detail produk
                $nod = 1;
                $running_qty_bal = (float)$record->begin_stock;
                $running_act_amt_bal = (float)$record->begin_stock * $std_price_rate; // Begin Amount Actual

                // 2. Kumpulkan semua data transaksi ke dalam satu array
                if ($filter_trans_type == '') {
                    $receipts     = $batch_data['receipts'][$item_rm_id] ?? [];
                    $issueds      = $batch_data['issueds'][$item_rm_id] ?? [];
                    $returns      = $batch_data['returns'][$item_rm_id] ?? [];
                    $os_rms       = $batch_data['os_rms'][$item_rm_id] ?? [];
                    $bpm_scans    = $batch_data['bpm_scans'][$item_rm_id] ?? [];
                    $transactions = $batch_data['transactions'][$item_rm_id] ?? [];
                    $user_map     = $batch_data['users'] ?? [];

                    $all_data = [];

                    // Helper function: get last PO price on or before a given date
                    $get_last_po_price = function($date) use ($item_rm_id, $record, &$batch_data) {
                        if (empty($batch_data['po_prices'][$item_rm_id])) return 0;
                        foreach ($batch_data['po_prices'][$item_rm_id] as $p) {
                            if ($p->receipt_date <= $date) {
                                if ($record->division == 'MTS') {
                                    if (trim((string)$p->pr_spec) == trim((string)$p->po_spec)) {
                                        return (float)$p->price;
                                    }
                                } else {
                                    return (float)$p->price;
                                }
                            }
                        }
                        return 0;
                    };

                    // --- RECEIPT ---
                    foreach ($receipts as $r) {
                        $all_data[] = [
                            'type' => 'RECEIPT',
                            'date' => $r->receipt_date,
                            'username' => $r->username,
                            'qty_in' => $r->qty_receipt,
                            'qty_out' => 0,
                            'actual_price_in' => (float)$r->actual_price_in,
                            'doc1' => $r->bc_kind,
                            'doc2' => $r->bc_document, // Label No untuk RECEIPT berisi nomor dokumen (contoh: SK..., DN...)
                            'doc3' => $r->receipt_no,   // Document No untuk RECEIPT berisi nomor POR
                            'doc4' => $r->bc_date
                        ];
                    }

                    // --- ISSUED ---
                    foreach ($issueds as $i) {
                        $all_data[] = [
                            'type' => 'ISSUED',
                            'date' => $i->created_date,
                            'username' => $user_map[$i->created_by] ?? $i->created_by,
                            'qty_in' => 0,
                            'qty_out' => $i->qty,
                            'actual_price_in' => 0,
                            'doc1' => '-',
                            'doc2' => $i->label_no,
                            'doc3' => $i->request_no,
                            'doc4' => '-'
                        ];
                    }

                    // --- RETURN ---
                    foreach ($returns as $r) {
                        $t_date = date('Y-m-d', strtotime((string) $r->return_date));
                        $last_p = $get_last_po_price($t_date);
                        $all_data[] = [
                            'type' => 'RETURN',
                            'date' => $r->return_date,
                            'username' => $r->username,
                            'qty_in' => $r->qty,
                            'qty_out' => 0,
                            'actual_price_in' => $last_p,
                            'doc1' => '-',
                            'doc2' => $r->label_no,
                            'doc3' => $r->return_no,
                            'doc4' => '-'
                        ];
                    }

                    // --- OS RM ---
                    foreach ($os_rms as $o) {
                        $t_date = date('Y-m-d', strtotime((string) $o->created_date));
                        $last_p = $get_last_po_price($t_date);
                        $all_data[] = [
                            'type' => 'OS RM',
                            'date' => $o->created_date,
                            'username' => $user_map[$o->created_by] ?? $o->created_by,
                            'qty_in' => $o->qty,
                            'qty_out' => 0,
                            'actual_price_in' => $last_p,
                            'doc1' => '-',
                            'doc2' => '-',
                            'doc3' => '-',
                            'doc4' => '-'
                        ];
                    }

                    // --- SCAN BPM ---
                    foreach ($bpm_scans as $b) {
                        $t_date = !empty($b->request_date) ? date('Y-m-d', strtotime((string) $b->request_date)) : date('Y-m-d', strtotime((string) $b->created_date));
                        // Harga PO dari lot POR, fallback harga PO terakhir (BPM lama dengan lot tidak valid)
                        $bpm_price = (float) $b->lot_price > 0 ? (float) $b->lot_price : $get_last_po_price($t_date);
                        $all_data[] = [
                            'type' => 'BPM',
                            'date' => $b->created_date,
                            'username' => $user_map[$b->created_by] ?? $b->created_by,
                            'qty_in' => $b->qty,
                            'qty_out' => 0,
                            'actual_price_in' => $bpm_price,
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
                        $act_p_in = 0;
                        if ($qty_in > 0) {
                            $t_date = date('Y-m-d', strtotime((string) $t->request_date));
                            $act_p_in = $get_last_po_price($t_date);
                        }

                        $all_data[] = [
                            'type' => $t->transaction_type,
                            'date' => $t->request_date,
                            'username' => $t->username,
                            'qty_in' => $qty_in,
                            'qty_out' => $qty_out,
                            'actual_price_in' => $act_p_in,
                            'doc1' => '-',
                            'doc2' => '-',
                            'doc3' => $t->request_no,
                            'doc4' => '-'
                        ];
                    }

                    usort($all_data, function ($a, $b) {
                        return strtotime((string) $a['date']) - strtotime((string) $b['date']);
                    });
                }
                
                if (!empty($all_data)) {
                    // Baris judul detail
                    $html .= '<tr>
                                <td colspan="32" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
                    $html .= '<tr>
                                <th rowspan="3" width="20"></th>
                                <th rowspan="3" width="20">No</th>
                                <th rowspan="3">Trans Type</th>
                                <th rowspan="3">Created By</th>
                                <th rowspan="3">Transaction Date</th>
                                <th rowspan="3" colspan="2">Document No.</th>
                                <th rowspan="3" colspan="2">Label No.</th>
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
                            </tr>';

                    foreach ($all_data as $data) {
                        // Perhitungan Running Balance
                        $prev_qty = $running_qty_bal;
                        $prev_amt = $running_act_amt_bal;
                        
                        $running_qty_bal += ($data['qty_in'] - $data['qty_out']);
                        
                        // Logika Harga Aktual
                        $row_price = (float)$data['actual_price_in'] > 0 ? (float)$data['actual_price_in'] * $rate : $std_price_rate;
                        
                        // Hitung Amount IN dan OUT secara terpisah
                        $amt_in = $data['qty_in'] * $row_price;
                        
                        // Untuk OUT: Menggunakan Moving Average Sederhana (Total Amount / Total Qty)
                        $amt_out = 0;
                        if ($data['qty_out'] > 0) {
                            $average_price = ($prev_qty > 0) ? ($prev_amt / $prev_qty) : $std_price_rate;
                            $amt_out = $data['qty_out'] * $average_price;
                        }

                        $running_act_amt_bal += ($amt_in - $amt_out);

                        $html .= '<tr style="background:#fff;">
                                    <td></td>
                                    <td align="center">' . $nod . '</td>
                                    <td>' . $data['type'] . '</td>
                                    <td>' . $data['username'] . '</td>
                                    <td align="center">' . date("d-m-Y", strtotime((string) $data['date'])) . '</td>
                                    <td colspan="2">' . $data['doc3'] . '</td>
                                    <td colspan="2">' . $data['doc2'] . '</td>
                                    <td align="center">' . $record->currency . '</td>
                                    <td align="right">' . number_format((float) $record->std_price, 2) . '</td>
                                    <td align="right">' . number_format((float) $rate, 2) . '</td>

                                    <td align="right" style="background:#f9f9f9;">' . number_format((float) $prev_qty, 2) . '</td>
                                    <td align="right" style="background:#f9f9f9;">' . number_format((float) $std_price_rate, 2) . '</td>
                                    <td align="right" style="background:#f9f9f9;">' . number_format((float) $prev_amt, 2) . '</td>
                                    <td align="right" style="background:#f9f9f9;">' . number_format((float) $std_price_rate, 2) . '</td>
                                    <td align="right" style="background:#f9f9f9;">' . number_format((float) $prev_amt, 2) . '</td>

                                    <td align="right" style="background:#efffef;">' . ($data['qty_in'] > 0 ? number_format((float) $data['qty_in'], 2) : '-') . '</td>
                                    <td align="right" style="background:#efffef;">' . ($data['qty_in'] > 0 ? number_format((float) $std_price_rate, 2) : '-') . '</td>
                                    <td align="right" style="background:#efffef;">' . ($data['qty_in'] > 0 ? number_format($data['qty_in'] * $std_price_rate, 2) : '-') . '</td>
                                    <td align="right" style="background:#efffef; font-weight:bold;">' . ($data['qty_in'] > 0 ? number_format((float) $row_price, 2) : '-') . '</td>
                                    <td align="right" style="background:#efffef; font-weight:bold;">' . ($data['qty_in'] > 0 ? number_format((float) $amt_in, 2) : '-') . '</td>

                                    <td align="right" style="background:#fff2f2;">' . ($data['qty_out'] > 0 ? number_format((float) $data['qty_out'], 2) : '-') . '</td>
                                    <td align="right" style="background:#fff2f2;">' . ($data['qty_out'] > 0 ? number_format((float) $std_price_rate, 2) : '-') . '</td>
                                    <td align="right" style="background:#fff2f2;">' . ($data['qty_out'] > 0 ? number_format($data['qty_out'] * $std_price_rate, 2) : '-') . '</td>
                                    <td align="right" style="background:#fff2f2; font-weight:bold;">' . ($data['qty_out'] > 0 ? number_format($amt_out / $data['qty_out'], 2) : '-') . '</td>
                                    <td align="right" style="background:#fff2f2; font-weight:bold;">' . ($data['qty_out'] > 0 ? number_format((float) $amt_out, 2) : '-') . '</td>

                                    <td align="right" style="background:#fffbcc;">' . number_format((float) $running_qty_bal, 2) . '</td>
                                    <td align="right" style="background:#fffbcc;">' . number_format((float) $std_price_rate, 2) . '</td>
                                    <td align="right" style="background:#fffbcc;">' . number_format($running_qty_bal * $std_price_rate, 2) . '</td>
                                    <td align="right" style="background:#fffbcc;">-</td>
                                    <td align="right" style="background:#fffbcc;">' . number_format((float) $running_act_amt_bal, 2) . '</td>
                                </tr>';
                        $nod++;
                    }
                }
            }

            $no++;
        }

        // Grand Total Row
        $html .= '<tr style="background:#eee; font-weight:bold;">
                    <td colspan="12" align="right">GRAND TOTAL</td>
                    <td align="right">'.number_format((float) $grandtotals['begin_qty'], 2).'</td><td></td>
                    <td align="right">'.number_format((float) $grandtotals['begin_std_amt'], 2).'</td><td></td>
                    <td align="right">'.number_format((float) $grandtotals['begin_act_amt'], 2).'</td>
                    
                    <td align="right">'.number_format((float) $grandtotals['in_qty'], 2).'</td><td></td>
                    <td align="right">'.number_format((float) $grandtotals['in_std_amt'], 2).'</td><td></td>
                    <td align="right">'.number_format((float) $grandtotals['in_act_amt'], 2).'</td>
                    
                    <td align="right">'.number_format((float) $grandtotals['out_qty'], 2).'</td><td></td>
                    <td align="right">'.number_format((float) $grandtotals['out_std_amt'], 2).'</td><td></td>
                    <td align="right">'.number_format((float) $grandtotals['out_act_amt'], 2).'</td>
                    
                    <td align="right">'.number_format((float) $grandtotals['end_qty'], 2).'</td><td></td>
                    <td align="right">'.number_format((float) $grandtotals['end_std_amt'], 2).'</td><td></td>
                    <td align="right">'.number_format((float) $grandtotals['end_act_amt'], 2).'</td>
                </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }

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
        $filter_item_category = $this->rmCategoryId(); // Inventory Valuation RM: hanya kategori RAW MATERIAL
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_items = $this->input->get('filter_items');
        $filter_display = $this->input->get("filter_display");
        $filter_division = $this->input->get('filter_division');
        $filter_trans_type = $this->input->get('filter_trans_type');

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
                        ->get('inventory_rm_actual')
                        ->row();
        $start_system = ($cutoff_data) ? $cutoff_data->cutoff_date : '2026-01-01';

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
                (COALESCE(d.qty_scan_in, 0) + COALESCE(e.qty_os_rm, 0) + COALESCE(t_rm.qty_trans_rm_in, 0) + COALESCE(g.return_qty, 0) + COALESCE(k.qty_scan_bpm, 0)) AS qty_in,
                (COALESCE(h.qty_issued, 0) + COALESCE(t_rm.qty_trans_rm_out, 0)) AS qty_out,

                -- ACTUAL FROM UPLOAD
                COALESCE(actual.price, 0) as actual_price, 
                COALESCE(actual.qty, 0) as actual_qty,
                actual.currency as upload_currency,
                actual.created_by as upload_by,
                actual.upload_date,

                -- ACTUAL AMOUNT CALCULATION (JOIN TO PO)
                COALESCE(calc_in.total_actual_in, 0) as actual_amount_in,
                COALESCE(calc_out.total_actual_out, 0) as actual_amount_out,
                COALESCE(bpm_act.bpm_actual_amt, 0) as bpm_actual_amt,
                d.receipt_date -- used for rate lookup
            FROM item_rm a
            JOIN item_familys b ON a.item_family_id = b.id AND b.number != 'FG'
            JOIN item_categories c ON a.item_category_id = c.id
            LEFT JOIN item_family_subs subfam ON a.item_sub_family_id = subfam.id

            -- get actual from upload
            LEFT JOIN inventory_rm_actual actual ON (actual.part_no = a.number OR actual.item_rm_id = a.id)
                AND actual.cutoff_date = '$start_system' -- perbaikan perhitungan begin qty per bulan

            -- get specification 
            LEFT JOIN (
                SELECT a.item_rm_id, MAX(COALESCE(f.specification, a.specification)) as specification
                FROM purchase_order_receipts a
                LEFT JOIN purchase_orders f ON a.po_no = f.po_no AND a.item_rm_id = f.item_rm_id
                GROUP BY a.item_rm_id
            ) item_spec ON item_spec.item_rm_id = a.id
            
            -- Standard Price
            LEFT JOIN (SELECT item_rm_id, currency, price from standard_price_rm where '$filter_to' BETWEEN `start_date` AND `end_date` AND deleted = 0) aa on a.id = aa.item_rm_id

            -- QTY IN Logic
            LEFT JOIN (SELECT MAX(b.receipt_date) AS receipt_date, b.item_rm_id, SUM(a.qty) AS qty_scan_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY b.item_rm_id) d ON a.id = d.item_rm_id
            
            -- ACTUAL AMOUNT IN (Logic: Sum of Qty Scan * PO Price)
            LEFT JOIN (
                SELECT pr.item_rm_id, SUM(sr.qty * po.price) as total_actual_in
                FROM purchase_order_receipts pr
                JOIN scan_item_receipts sr ON pr.receipt_id = sr.receipt_id
                JOIN item_rm irm ON pr.item_rm_id = irm.id
                JOIN purchase_orders po ON pr.po_no = po.po_no AND pr.item_rm_id = po.item_rm_id
                    AND (
                        (UPPER(TRIM(irm.division)) = 'MTS' AND COALESCE(TRIM(pr.specification), '') = COALESCE(TRIM(po.specification), ''))
                        OR (UPPER(TRIM(COALESCE(irm.division, ''))) != 'MTS')
                    )
                WHERE pr.receipt_date BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY pr.item_rm_id
            ) calc_in ON a.id = calc_in.item_rm_id

            -- Actual Amount BPM (harga PO dari lot POR)
            LEFT JOIN (" . $this->bpmActualAmountSql($filter_from, $filter_to) . ") bpm_act ON a.id = bpm_act.item_rm_id

            -- ACTUAL AMOUNT OUT (Logic: Sum of Issued Qty * PO Price)
            LEFT JOIN (
                SELECT imd.item_rm_id, SUM(imd.qty * po.price) as total_actual_out
                FROM issued_material_details imd
                LEFT JOIN (SELECT item_rm_id, MAX(price) as price FROM purchase_orders GROUP BY item_rm_id) po ON imd.item_rm_id = po.item_rm_id
                WHERE DATE_FORMAT(imd.created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'
                GROUP BY imd.item_rm_id
            ) calc_out ON a.id = calc_out.item_rm_id

            -- Query pendukung (e, g, h, k, t_rm)
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_os_rm FROM os_rm WHERE trans_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
            LEFT JOIN (
                SELECT item_rm_id, 
                    SUM(CASE WHEN transaction_kind = 'IN' THEN qty ELSE 0 END) AS qty_trans_rm_in,
                    SUM(CASE WHEN transaction_kind = 'OUT' THEN qty ELSE 0 END) AS qty_trans_rm_out
                FROM transaction_rm 
                WHERE request_date BETWEEN '$filter_from' AND '$filter_to' 
                GROUP BY item_rm_id
            ) t_rm ON a.id = t_rm.item_rm_id
            LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty FROM return_materials a JOIN return_material_labels b ON a.return_id = b.return_id JOIN scan_item_receipts c ON a.return_id = c.receipt_id AND b.label_no = c.label_no WHERE a.return_date BETWEEN '$filter_from' AND '$filter_to' GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_issued FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) h ON a.id = h.item_rm_id
            LEFT JOIN (SELECT item_rm_id, SUM(qty) AS qty_scan_bpm FROM scan_item_bpm WHERE DATE_FORMAT(request_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to' GROUP BY item_rm_id) k ON a.id = k.item_rm_id
            
            -- BEGIN STOCK Logic (Query j)
            -- PERBAIKAN: Get data setelah cutoff_date dan sebelum filter_from untuk carry-over QTY
            LEFT JOIN (
                SELECT 
                    a.id, 
                    (COALESCE(b.q_in,0) + COALESCE(c.q_in,0) + COALESCE(d.q_in,0) + COALESCE(e.q_in,0) + COALESCE(h.q_in,0)) - 
                    (COALESCE(f.q_out,0) + COALESCE(g.q_out,0)) as begin_stock
                FROM item_rm a
                LEFT JOIN (SELECT b.item_rm_id, SUM(a.qty) as q_in FROM scan_item_receipts a JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id WHERE b.receipt_date >= '$start_system' AND b.receipt_date < '$filter_from' GROUP BY 1) b ON a.id = b.item_rm_id
                LEFT JOIN (SELECT x.item_rm_id, SUM(x.qty) as q_in FROM os_rm x WHERE x.trans_date >= '$start_system' AND x.trans_date < '$filter_from' GROUP BY 1) c ON a.id = c.item_rm_id
                LEFT JOIN (SELECT x.item_rm_id, SUM(x.qty) as q_in FROM transaction_rm x WHERE x.transaction_kind = 'IN' AND x.request_date >= '$start_system' AND x.request_date < '$filter_from' GROUP BY 1) d ON a.id = d.item_rm_id
                LEFT JOIN (SELECT x.item_rm_id, SUM(c.qty) as q_in FROM return_materials x JOIN scan_item_receipts c ON x.return_id = c.receipt_id WHERE x.return_date >= '$start_system' AND x.return_date < '$filter_from' GROUP BY 1) e ON a.id = e.item_rm_id
                LEFT JOIN (SELECT x.item_rm_id, SUM(x.qty) as q_out FROM issued_material_details x WHERE x.created_date >= '$start_system' AND x.created_date < '$filter_from' GROUP BY 1) f ON a.id = f.item_rm_id
                LEFT JOIN (SELECT x.item_rm_id, SUM(x.qty) as q_out FROM transaction_rm x WHERE x.transaction_kind = 'OUT' AND x.request_date >= '$start_system' AND x.request_date < '$filter_from' GROUP BY 1) g ON a.id = g.item_rm_id
                LEFT JOIN (SELECT x.item_rm_id, SUM(x.qty) as q_in FROM scan_item_bpm x WHERE x.request_date >= '$start_system' AND x.request_date < '$filter_from' GROUP BY 1) h ON a.id = h.item_rm_id
            ) j ON a.id = j.id

            WHERE c.id LIKE '%$filter_item_category%'
            AND b.number LIKE '%$filter_item_family%'
            AND a.id LIKE '%$filter_items%'
            AND a.division LIKE '%$filter_division%'
            GROUP BY a.id
            ORDER BY c.name DESC, b.name DESC, a.number";

        $records = $this->crud->query($query_main);
        $item_ids = !empty($records) ? array_map(function($r) { return $r->id; }, $records) : [];
        $batch_data = ($filter_display == "DETAIL") ? $this->getBatchDetailData($item_ids, $filter_from, $filter_to) : [];

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
                <h3 style="margin:0;">INVENTORY VALUATION RM <i>' . $display_title . '</i> </h3>
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
            'b_qty' => 0, 'b_std' => 0, 'b_act' => 0,
            'i_qty' => 0, 'i_std' => 0, 'i_act' => 0,
            'o_qty' => 0, 'o_std' => 0, 'o_act' => 0,
            'e_qty' => 0, 'e_std' => 0, 'e_act' => 0,
        ];

        foreach ($records as $record) {
            $item_rm_id = $record->id;
            $rate = 1;
            if ($record->currency == 'USD' && !empty($record->max_receipt_date)) {
                $rate = $get_rate($record->max_receipt_date);
            }
            $std_price = $record->std_price * $rate;
            $begin_qty       = (float)$record->actual_qty;  // Begin Balance = Qty dari upload saja
            $begin_std_amt   = $begin_qty * $std_price;
            // Actual price begin dari upload (sesuai cutoff date), fallback ke standard price
            $begin_act_price = $std_price;
            if ((float)$record->actual_price > 0) {
                $upload_rate     = ($record->upload_currency == 'USD') ? $get_rate($start_system) : 1;
                $begin_act_price = (float)$record->actual_price * $upload_rate;
            }
            $begin_act_amt   = $begin_qty * $begin_act_price;

            $in_qty          = $record->qty_in;
            $in_std_amt      = $in_qty * $std_price;
            $in_act_amt      = ((float)$record->actual_amount_in + (float)$record->bpm_actual_amt) * $rate;
            $in_act_price    = 0;
            if ($in_qty > 0) {
                $in_act_price = $in_act_amt / $in_qty;
            }

            $out_qty         = $record->qty_out;
            $out_std_amt     = $out_qty * $std_price;
            // OUT Actual Price : (amount begin + amount in) / (qty begin + qty in)
            $out_act_price   = 0;
            if (($begin_qty + $in_qty) > 0) {
                $out_act_price = ($begin_act_amt + $in_act_amt) / ($begin_qty + $in_qty);
            }
            $out_act_amt     = $out_qty * $out_act_price;

            $end_qty         = ($begin_qty + $in_qty) - $out_qty;
            $end_std_amt     = ($begin_std_amt + $in_std_amt) - $out_std_amt;
            $end_act_amt     = ($begin_act_amt + $in_act_amt) - $out_act_amt;
            $end_act_price   = 0;
            if ($end_qty > 0) {
                $end_act_price = $end_act_amt / $end_qty;
            }

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
                        <td align="right">'.number_format((float) $rate, 2).'</td>
                        
                        <td align="right">'.number_format((float) $begin_qty, 2).'</td>
                        <td align="right">'.number_format((float) $std_price, 2).'</td>
                        <td align="right">'.number_format((float) $begin_std_amt, 2).'</td>
                        <td align="right">'.number_format((float) $begin_act_price, 2).'</td>
                        <td align="right">'.number_format((float) $begin_act_amt, 2).'</td>

                        <td align="right">'.number_format((float) $in_qty, 2).'</td>
                        <td align="right">'.number_format((float) $std_price, 2).'</td>
                        <td align="right">'.number_format((float) $in_std_amt, 2).'</td>
                        <td align="right">'.number_format((float) $in_act_price, 2).'</td>
                        <td align="right">'.number_format((float) $in_act_amt, 2).'</td>

                        <td align="right">'.number_format((float) $out_qty, 2).'</td>
                        <td align="right">'.number_format((float) $std_price, 2).'</td>
                        <td align="right">'.number_format((float) $out_std_amt, 2).'</td>
                        <td align="right">'.number_format((float) $out_act_price, 2).'</td>
                        <td align="right">'.number_format((float) $out_act_amt, 2).'</td>

                        <td align="right">'.number_format((float) $end_qty, 2).'</td>
                        <td align="right">'.number_format((float) $std_price, 2).'</td>
                        <td align="right">'.number_format((float) $end_std_amt, 2).'</td>
                        <td align="right">'.number_format((float) $end_act_price, 2).'</td>
                        <td align="right">'.number_format((float) $end_act_amt, 2).'</td>
                    </tr>';

            $running_qty_bal = 0; 
            $running_act_amt_bal = 0;

            // (Logika Detail Transactions)
                if ($filter_trans_type == '') {
                    $receipts     = $batch_data['receipts'][$item_rm_id] ?? [];
                    $issueds      = $batch_data['issueds'][$item_rm_id] ?? [];
                    $returns      = $batch_data['returns'][$item_rm_id] ?? [];
                    $os_rms       = $batch_data['os_rms'][$item_rm_id] ?? [];
                    $bpm_scans    = $batch_data['bpm_scans'][$item_rm_id] ?? [];
                    $transactions = $batch_data['transactions'][$item_rm_id] ?? [];
                    $user_map     = $batch_data['users'] ?? [];

                    $all_data = [];

                    // Helper function: get last PO price on or before a given date
                    $get_last_po_price = function($date) use ($item_rm_id, $record, &$batch_data) {
                        if (empty($batch_data['po_prices'][$item_rm_id])) return 0;
                        foreach ($batch_data['po_prices'][$item_rm_id] as $p) {
                            if ($p->receipt_date <= $date) {
                                if ($record->division == 'MTS') {
                                    if (trim((string)$p->pr_spec) == trim((string)$p->po_spec)) {
                                        return (float)$p->price;
                                    }
                                } else {
                                    return (float)$p->price;
                                }
                            }
                        }
                        return 0;
                    };

                    // --- RECEIPT ---
                    foreach ($receipts as $r) {
                        $all_data[] = [
                            'type' => 'RECEIPT',
                            'date' => $r->receipt_date,
                            'username' => $r->username,
                            'qty_in' => $r->qty_receipt,
                            'qty_out' => 0,
                            'actual_price_in' => (float)$r->actual_price_in,
                            'doc1' => $r->bc_kind,
                            'doc2' => $r->bc_document, // Label No untuk RECEIPT berisi nomor dokumen (contoh: SK..., DN...)
                            'doc3' => $r->receipt_no,   // Document No untuk RECEIPT berisi nomor POR
                            'doc4' => $r->bc_date
                        ];
                    }

                    // --- ISSUED ---
                    foreach ($issueds as $i) {
                        $all_data[] = [
                            'type' => 'ISSUED',
                            'date' => $i->created_date,
                            'username' => $user_map[$i->created_by] ?? $i->created_by,
                            'qty_in' => 0,
                            'qty_out' => $i->qty,
                            'actual_price_in' => 0,
                            'doc1' => '-',
                            'doc2' => $i->label_no,
                            'doc3' => $i->request_no,
                            'doc4' => '-'
                        ];
                    }

                    // --- RETURN ---
                    foreach ($returns as $r) {
                        $t_date = date('Y-m-d', strtotime((string) $r->return_date));
                        $last_p = $get_last_po_price($t_date);
                        $all_data[] = [
                            'type' => 'RETURN',
                            'date' => $r->return_date,
                            'username' => $r->username,
                            'qty_in' => $r->qty,
                            'qty_out' => 0,
                            'actual_price_in' => $last_p,
                            'doc1' => '-',
                            'doc2' => $r->label_no,
                            'doc3' => $r->return_no,
                            'doc4' => '-'
                        ];
                    }

                    // --- OS RM ---
                    foreach ($os_rms as $o) {
                        $t_date = date('Y-m-d', strtotime((string) $o->created_date));
                        $last_p = $get_last_po_price($t_date);
                        $all_data[] = [
                            'type' => 'OS RM',
                            'date' => $o->created_date,
                            'username' => $user_map[$o->created_by] ?? $o->created_by,
                            'qty_in' => $o->qty,
                            'qty_out' => 0,
                            'actual_price_in' => $last_p,
                            'doc1' => '-',
                            'doc2' => '-',
                            'doc3' => '-',
                            'doc4' => '-'
                        ];
                    }

                    // --- SCAN BPM ---
                    foreach ($bpm_scans as $b) {
                        $t_date = !empty($b->request_date) ? date('Y-m-d', strtotime((string) $b->request_date)) : date('Y-m-d', strtotime((string) $b->created_date));
                        // Harga PO dari lot POR, fallback harga PO terakhir (BPM lama dengan lot tidak valid)
                        $bpm_price = (float) $b->lot_price > 0 ? (float) $b->lot_price : $get_last_po_price($t_date);
                        $all_data[] = [
                            'type' => 'BPM',
                            'date' => $b->created_date,
                            'username' => $user_map[$b->created_by] ?? $b->created_by,
                            'qty_in' => $b->qty,
                            'qty_out' => 0,
                            'actual_price_in' => $bpm_price,
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
                        $act_p_in = 0;
                        if ($qty_in > 0) {
                            $t_date = date('Y-m-d', strtotime((string) $t->request_date));
                            $act_p_in = $get_last_po_price($t_date);
                        }

                        $all_data[] = [
                            'type' => $t->transaction_type,
                            'date' => $t->request_date,
                            'username' => $t->username,
                            'qty_in' => $qty_in,
                            'qty_out' => $qty_out,
                            'actual_price_in' => $act_p_in,
                            'doc1' => '-',
                            'doc2' => '-',
                            'doc3' => $t->request_no,
                            'doc4' => '-'
                        ];
                    }

                    // Transaksi diurutkan berdasarkan tanggal
                    usort($all_data, function ($a, $b) {
                        return strtotime((string) $a['date']) - strtotime((string) $b['date']);
                    });
                }
                
                if (!empty($all_data)) {
                    // Baris judul detail
                    $html .= '<tr>
                                <td colspan="32" style="background:#D1FFC6; font-size: 11px;"><b>DETAIL OF ' . $record->number . ' - ' . $record->name . '</b></td>
                            </tr>';
                    $html .= '<tr>
                                <th rowspan="3" width="20"></th>
                                <th rowspan="3" width="20">No</th>
                                <th rowspan="3">Trans Type</th>
                                <th rowspan="3">Created By</th>
                                <th rowspan="3">Transaction Date</th>
                                <th rowspan="3" colspan="2">Document No.</th>
                                <th rowspan="3" colspan="2">Label No.</th>
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
                            </tr>';

                    $running_qty_bal = 0; 
                    $running_act_amt_bal = 0;
                    $first_upload_captured = false; 

                    // PERBAIKAN: Saldo awal baris detail adalah saldo upload + mutasi carry-over sebelum filter_from
                    $actual_upload_qty = (float)$record->actual_qty;
                    $mutation_before   = (float)$record->begin_stock;
                    $running_qty_bal = $actual_upload_qty + $mutation_before;

                    // Akumulasi ke Grand Total (Kolom BEGIN)
                    $grandtotals['b_qty'] += $running_qty_bal;
                    $grandtotals['b_std'] += ($running_qty_bal * $std_price);
                    $grandtotals['b_act'] += ($running_qty_bal * $begin_act_price);

                    $is_upload_month = (date('Y-m', strtotime((string) $start_system)) == date('Y-m', strtotime((string) $filter_from)));

                    // Tampilkan Baris Kuning HANYA jika ini adalah bulan Upload
                    if ($is_upload_month) {
                        $html .= '<tr style="background: #fffbf1">
                            <td></td>
                            <td style="text-align:center">#</td>
                            <td>UPLOAD</td>
                            <td>' . $record->upload_by . '</td>
                            <td>' . $record->upload_date . '</td>
                            <td colspan="4">BEGIN BALANCE (UPLOAD)</td>
                            <td style="text-align:center;">' . $record->upload_currency . '</td>
                            <td style="text-align:right;">' . number_format((float) $std_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format((float) $rate, 2) . '</td>

                            <td style="text-align:right;">' . number_format((float) $actual_upload_qty, 2) . '</td>
                            <td style="text-align:right;">' . number_format((float) $std_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($actual_upload_qty * $std_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format((float) $begin_act_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($actual_upload_qty * $begin_act_price, 2) . '</td>

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

                            <td style="text-align:right;">' . number_format((float) $actual_upload_qty, 2) . '</td>
                            <td style="text-align:right;">' . number_format((float) $std_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($actual_upload_qty * $std_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format((float) $begin_act_price, 2) . '</td>
                            <td style="text-align:right;">' . number_format($actual_upload_qty * $begin_act_price, 2) . '</td>
                        </tr>';
                    }

                    $nod = 1;
                    // Harga upload hanya untuk begin balance (baris pertama), baris berikutnya lanjut dari ending baris sebelumnya
                    $row_begin_act_price = $begin_act_price;
                    foreach ($all_data as $data) {
                        $trans_in  = (float)$data['qty_in'];
                        $trans_out = (float)$data['qty_out'];

                        // Simpan saldo sebelum transaksi untuk kolom "BEGIN" di baris ini
                        $row_begin_qty = $running_qty_bal;
                        
                        // Update Running Balance untuk baris ini
                        $running_qty_bal += ($trans_in - $trans_out);
                            

                        // Render ke HTML menggunakan variabel hasil logika di atas
                        $html .= '<tr style="background:#fff;">
                                    <td></td>
                                    <td align="center">' . $nod . '</td>
                                    <td>' . $data['type'] . '</td>
                                    <td>' . $data['username'] . '</td>
                                    <td align="center">' . date("d-m-Y", strtotime((string) $data['date'])) . '</td>
                                    <td colspan="2">' . $data['doc3'] . '</td>
                                    <td colspan="2">' . $data['doc2'] . '</td>
                                    <td align="center">' . $record->currency . '</td>
                                    <td align="right">' . number_format((float) $record->std_price, 2) . '</td>
                                    <td align="right">' . number_format((float) $rate, 2) . '</td>
                                    
                                    <td style="text-align:right;">' . number_format((float) $row_begin_qty, 2) . '</td>
                                    <td style="text-align:right;">' . number_format((float) $std_price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($row_begin_qty * $std_price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format((float) $row_begin_act_price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($row_begin_qty * $row_begin_act_price, 2) . '</td>

                                    <td style="text-align:right;">' . number_format((float) $trans_in, 2) . '</td>
                                    <td style="text-align:right;">' . number_format((float) $std_price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($trans_in * $std_price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format((float) $data['actual_price_in'], 2) . '</td>
                                    <td style="text-align:right;">' . number_format($trans_in * $data['actual_price_in'], 2) . '</td>

                                    <td style="text-align:right;">' . number_format((float) $trans_out, 2) . '</td>
                                    <td style="text-align:right;">' . number_format((float) $std_price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($trans_out * $std_price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format((float) $out_act_price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($trans_out * $out_act_price, 2) . '</td>

                                    <td style="text-align:right; font-weight:bold;">' . number_format((float) $running_qty_bal, 2) . '</td>
                                    <td style="text-align:right;">' . number_format((float) $std_price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($running_qty_bal * $std_price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format((float) $end_act_price, 2) . '</td>
                                    <td style="text-align:right;">' . number_format($running_qty_bal * $end_act_price, 2) . '</td>
                                </tr>';
                        $nod++;
                        $row_begin_act_price = $end_act_price;

                        
                        // Akumulasi ke Grand Total (IN & OUT)
                        $grandtotals['i_qty'] += $trans_in;
                        $grandtotals['i_std'] += ($trans_in * $std_price);
                        $grandtotals['i_act'] += ($trans_in * $data['actual_price_in']);
                        $grandtotals['o_qty'] += $trans_out;
                        $grandtotals['o_std'] += ($trans_out * $std_price);
                        $grandtotals['o_act'] += ($trans_out * $out_act_price);
                    }

                    // Akumulasi ke Grand Total (Kolom ENDING/BALANCE)
                    $grandtotals['e_qty'] += $running_qty_bal;
                    $grandtotals['e_std'] += ($running_qty_bal * $std_price);
                    $grandtotals['e_act'] += ($running_qty_bal * $end_act_price);
            }

            $no++;
        }

        // Grand Total Row
        $html .= '<tfooter>
            <tr style="background:#eee; font-weight:bold;">
                <td colspan="12" align="right">GRAND TOTAL</td>
                <td align="right">' . number_format((float) $grandtotals['b_qty'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format((float) $grandtotals['b_std'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format((float) $grandtotals['b_act'], 2) . '</td>

                <td align="right">' . number_format((float) $grandtotals['i_qty'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format((float) $grandtotals['i_std'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format((float) $grandtotals['i_act'], 2) . '</td>

                <td align="right">' . number_format((float) $grandtotals['o_qty'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format((float) $grandtotals['o_std'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format((float) $grandtotals['o_act'], 2) . '</td>

                <td align="right">' . number_format((float) $grandtotals['e_qty'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format((float) $grandtotals['e_std'], 2) . '</td>
                <td></td>
                <td align="right">' . number_format((float) $grandtotals['e_act'], 2) . '</td>
            </tr>
        </tfooter>';

        $html .= '</table></body></html>';
        echo $html;
    }
}
