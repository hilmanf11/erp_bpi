<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property CI_Form_validation $form_validation
 * @property Crud $crud
 * @property Bank_reconciliation $bank_reconciliation
 */
class Report_bank_reconciliation extends CI_Controller
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
            $this->load->view('finance/report_bank_reconciliation');
        } else {
            redirect('error_access');
        }
    }

    // Konversi tanggal PhpSpreadsheet
    private function _formatExcelDate($value, $withTime = false, $defaultYear = null) {
        if (empty($value)) return null;

        // Jika nilai adalah angka murni (Excel Serial Date)
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                ->format($withTime ? 'Y-m-d H:i:s' : 'Y-m-d');
        }

        // Jika nilai adalah string seperti "02/03"
        if (strlen($value) <= 5 && strpos($value, '/') !== false) {
            $year = $defaultYear ?? date('Y'); // Gunakan tahun dari parameter atau tahun sekarang
            $cleanDate = str_replace('/', '-', $value . '/' . $year);
            return date('Y-m-d', strtotime($cleanDate));
        }

        // Default strtotime untuk format string lainnya
        return date($withTime ? 'Y-m-d H:i:s' : 'Y-m-d', strtotime(str_replace('/', '-', $value)));
    }

    private function _formatMandiriDate($rawDate) 
    {
        if (empty($rawDate)) return date('Y-m-d H:i:s');

        // Jika berupa angka serial Excel (Numeric)
        if (is_numeric($rawDate)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate)->format('Y-m-d H:i:s');
        }

        // Jika berupa String (e.g., "01 April 2026 12:13:03")
        // Konversi nama bulan Indonesia ke Inggris agar bisa dibaca strtotime
        $bulan_indo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $bulan_eng  = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        
        $cleanDate = str_replace($bulan_indo, $bulan_eng, $rawDate);
        
        // Pastikan hasil akhirnya YYYY-MM-DD HH:mm:ss
        $timestamp = strtotime($cleanDate);
        
        return ($timestamp) ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d H:i:s');
    }

    // Download Template
    public function template() 
    {
        $filter_bank_account = $this->input->get('filter_bank_account');

        // Ambil data bank
        $account_banks = $this->crud->read('account_banks', [], ["bank_account" => $filter_bank_account]);
        
        if (empty($account_banks)) {
            // Return JSON jika error agar ditangkap catch/if di JS
            echo json_encode(array("title" => "Not Found", "message" => "Bank Account not found", "theme" => "error"));
            return;
        }

        // Tentukan Path (Gunakan FCPATH atau path relatif jika ingin lebih aman, 
        // tapi base_url sudah cukup untuk window.location.assign)
        $bank_code = $account_banks->bank_code;

        if (strpos($bank_code, "MDR") !== false) {
            $path = base_url('template/tmp_bank_mdr.xls');
        } elseif (strpos($bank_code, "RSN") !== false) {
            $path = base_url('template/tmp_bank_rsn.xls');
        } else {
            $path = base_url('template/tmp_bank_reconciliation.xls');
        }
        
        // Kirim plain text URL
        echo $path;
    }

    // GET PAYLOAD FOR UPLOAD DATA
    public function upload_default()
    {
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/json');
        
        // Load PHPSpreadsheet
        require_once 'assets/vendors/phpspreadsheet/vendor/autoload.php';

        try {
            if (!isset($_FILES['file_upload']) || $_FILES['file_upload']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("File not found or upload error.");
            }

            $filter_account_number = $this->input->post("filter_account_number");
            $filter_bank_account = $this->input->post("filter_bank_account");
            $filter_from = $this->input->post("filter_from");
            $filter_to = $this->input->post("filter_to");

            $tmpPath = $_FILES['file_upload']['tmp_name'];
            $spreadsheet = IOFactory::load($tmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            // Get Data Header (Metadata) dari Excel
            $dataBank = [
                'bank_account' => $sheet->getCell("C3")->getValue(),
                'start_date'   => $this->_formatExcelDate($sheet->getCell("C4")->getValue()),
                'end_date'     => $this->_formatExcelDate($sheet->getCell("C5")->getValue()),
                'currency'     => $sheet->getCell("C6")->getValue(),
            ];

            if (empty($dataBank['bank_account'])) {
                echo json_encode(array("title" => "Error", "message" => "Bank Account is required!", "theme" => "error"));
                return;
            }

            // VALIDASI: Cocokkan Excel dengan Filter
            if (strtotime($filter_from) !== strtotime($dataBank['start_date']) || 
                strtotime($filter_to) !== strtotime($dataBank['end_date'])) {
                echo json_encode(["title" => "Not Matched", "message" => "Failed! Period in Excel ({$dataBank['start_date']} to {$dataBank['end_date']}) does not match selection.", "theme" => "error"]);
                return;
            }

            if ($filter_bank_account !== $dataBank['bank_account']) {
                echo json_encode(["title" => "Not Matched", "message" => "Failed! Bank Account in file is different.", "theme" => "error"]);
                return;
            }

            // check available account_bank
            $account_banks = $this->crud->read('account_banks', [], ["bank_account" => $dataBank['bank_account']]);
            if (empty($account_banks->bank_account)) {
                echo json_encode(array("title" => "Not Found", "message" => "Bank Account Of " . $dataBank['bank_account'] ." Is Not Found", "theme" => "error"));
                return;
            }

            // PROSES DELETE (Panggil Model)
            $this->load->model('bank_reconciliation');
            $deleteStatus = $this->bank_reconciliation->replace_existing_data([
                'bank_account'   => $filter_bank_account,
                'account_number' => $filter_account_number,
                'from'           => $filter_from,
                'to'             => $filter_to
            ]);

            // PARSING DATA
            $datas = [];
            for ($i = 10; $i <= $highestRow; $i++) {
                $rawDate = $sheet->getCell("B$i")->getValue();
                if (empty($rawDate)) continue; // Lewati baris kosong

                $datas[] = [
                    'account_number' => $account_banks->account_number,
                    'bank_account'   => $account_banks->bank_account,
                    'start_date'     => $filter_from,
                    'end_date'       => $filter_to,
                    'currency'       => 'IDR',
                    'source'         => 'upload',
                    'posting_date'   => $this->_formatExcelDate($rawDate, true),
                    'remark'         => htmlspecialchars($sheet->getCell("C$i")->getValue()),
                    'debit'          => (float) str_replace(',', '', $sheet->getCell("D$i")->getValue() ?? 0),
                    'credit'         => (float) str_replace(',', '', $sheet->getCell("E$i")->getValue() ?? 0),
                    'result'         => null,   // Reconcile matched or not matched
                    'status'         => 0,      // 1=Reconciliation, 0=Not yet
                    'created_date'   => date('Y-m-d H:i:s'),
                    'created_by'     => $this->session->username,
                ];
            }

            // INSERT - Optimasi latency
            $processResults = $this->bank_reconciliation->batch_insert_with_log($datas);

            echo json_encode([
                "title"           => "Upload Processed",
                "delete_existing" => $deleteStatus,     // Status penghapusan data lama
                "results"         => $processResults,   // Status per baris (Success / Failed)
                "total"           => count($datas),
                "total_success"   => count(array_filter($processResults, function($r){ return $r['theme'] == 'success'; })),
            ]);

        } catch (Exception $e) {
            echo json_encode([
                "title"   => "Error",
                "message" => $e->getMessage(),
                "theme"   => "error"
            ]);
        }
    }


    public function upload()
    {
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/json');

        try {
            $filter_bank_account = $this->input->post("filter_bank_account");
            $fileName = $_FILES['file_upload']['name'] ?? '';
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // --- VALIDASI AWAL & ROUTING ---
            $account_banks = $this->crud->read('account_banks', [], ["bank_account" => $filter_bank_account]);
            if (empty($account_banks)) {
                throw new Exception("Bank Account is not registered in the system.");
            }

            // Khusus: RESONA (Wajib Excel)
            if (substr($account_banks->bank_code, 0, 3) === "RSN") {
                    if (!in_array($fileExt, ['xls', 'xlsx'])) {
                    throw new Exception("The selected Bank Account only accepts Excel file (.xls, .xlsx)");
                }
                return $this->_upload_resona(); // Panggil fungsi internal khusus Resona
            }

            // Khusus: MANDIRI (Wajib CSV)
            if (substr($account_banks->bank_code, 0, 3) === "MDR") {
                if ($fileExt !== 'csv') {
                    throw new Exception("The selected Bank Account only accepts CSV files (.csv).");
                }
                return $this->_upload_mandiri(); // Panggil fungsi internal khusus Mandiri
            }

            // Default: Bank lainnya
            return $this->upload_default();

        } catch (Exception $e) {
            echo json_encode(["title" => "Error", "message" => $e->getMessage(), "theme" => "error"]);
        }
    }

    // Import Bank Resona
    private function _upload_resona()
    {
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/json');
        require_once 'assets/vendors/phpspreadsheet/vendor/autoload.php';

        try {
            if (!isset($_FILES['file_upload']) || $_FILES['file_upload']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("File not found or upload error.");
            }

            // Ambil filter dari post
            $filter_bank_account   = $this->input->post("filter_bank_account");
            $filter_from           = $this->input->post("filter_from");
            $filter_to             = $this->input->post("filter_to");
            $filter_account_number = $this->input->post("filter_account_number");

            $tmpPath     = $_FILES['file_upload']['tmp_name'];
            $spreadsheet = IOFactory::load($tmpPath);
            $sheet       = $spreadsheet->getActiveSheet();

            // --- VALIDASI METADATA LANGSUNG DARI CELL ---
            $filter_bank_account = preg_replace('/[^0-9]/', '', $this->input->post("filter_bank_account"));
            $rawCellJ5 = (string)$sheet->getCell("J5")->getValue();
            $fileBankAccount = preg_replace('/[^0-9]/', '', $rawCellJ5);

            $fileStart       = $this->_formatExcelDate($sheet->getCell("J13")->getValue());
            $fileEnd         = $this->_formatExcelDate($sheet->getCell("O13")->getValue());
            
            // Metadata tambahan
            $fileOrgUnit     = $sheet->getCell("J15")->getValue();
            $fileProductName = $sheet->getCell("J17")->getValue();

            // VALIDASI: Cocokkan isi Excel dengan Filter User
            if ($fileBankAccount !== $filter_bank_account) {
                throw new Exception("Account in Excel ({$fileBankAccount}) does not match selection ({$filter_bank_account})!");
            }

            if (strtotime($filter_from) !== strtotime($fileStart) || strtotime($filter_to) !== strtotime($fileEnd)) {
                throw new Exception("Period in Excel ({$fileStart} to {$fileEnd}) does not match selection!");
            }

            // --- GET DATA REKENING DARI DATABASE ---
            $account_banks = $this->crud->read('account_banks', [], ["bank_account" => $filter_bank_account]);
            if (empty($account_banks)) {
                throw new Exception("Bank Account is not registered in the system.");
            }

            // --- PROSES TRANSAKSI (START BARIS 21) ---
            $highestRow   = $sheet->getHighestRow();
            $startDataRow = 21;
            $datas        = [];

            for ($i = $startDataRow; $i <= $highestRow; $i++) {
                // Ambil range kolom B sampai AE untuk antisipasi shifting yang jauh
                $rangeData = $sheet->rangeToArray("B$i:AE$i", NULL, TRUE, FALSE)[0];
                
                // Bersihkan baris dari element null atau string kosong di bagian akhir
                $row = array_values(array_filter($rangeData, function($val) {
                    return !is_null($val) && trim((string)$val) !== '';
                }));

                // A. Skip jika baris kosong
                if (empty($row)) continue;

                // B. Hapus baris "Total" (Cek di seluruh kolom baris tersebut)
                $cleanRow = array_map(function($val) { return trim(strtolower((string)$val)); }, $row);
                if (in_array('total', $cleanRow)) continue;

                // C. Handling Column Shifting & Mapping Data
                // Logika: 3 nilai numerik terakhir selalu Balance, Credit, Debit
                if (count($row) >= 6) {
                    $rawBalance = array_pop($row);
                    $rawCredit  = array_pop($row);
                    $rawDebit   = array_pop($row);
                    
                    // Ambil tanggal posting (biasanya kolom B atau C di Excel asli)
                    // Di array $row kita (hasil range B:AE), B adalah index 0, C adalah index 1
                    $posting_date = $this->_formatExcelDate($rangeData[1] ?? $rangeData[0], true);
                    
                    // Sisa kolom di tengah digabung menjadi Remark
                    // Kita slice mulai dari index 3 (asumsi No, PostDate, EffDate sudah terlewati)
                    $descParts = array_slice($row, 3);
                    $desc      = implode(" ", $descParts);
                    
                    // D. Push ke Datas (Ubah negatif jadi positif dengan abs)
                    $datas[] = [
                        'account_number' => $account_banks->account_number,
                        'bank_account'   => $account_banks->bank_account,
                        'start_date'     => $filter_from,
                        'end_date'       => $filter_to,
                        'currency'       => 'IDR',
                        'source'         => 'upload',
                        'posting_date'   => $posting_date,
                        'remark'         => htmlspecialchars((string)$desc),
                        'debit'          => abs((float) str_replace(',', '', $rawDebit ?? 0)),
                        'credit'         => abs((float) str_replace(',', '', $rawCredit ?? 0)),
                        'status'         => 0,
                        'created_date'   => date('Y-m-d H:i:s'),
                        'created_by'     => $this->session->username,
                    ];
                }
            }

            if (empty($datas)) {
                throw new Exception("No valid transaction data found after header data.");
            }

            // --- EXECUTE DATABASE ---
            $this->load->model('bank_reconciliation');
            
            // Ganti data lama sesuai kriteria filter
            $deleteStatus = $this->bank_reconciliation->replace_existing_data([
                'bank_account'   => $filter_bank_account,
                'account_number' => $filter_account_number,
                'from'           => $filter_from,
                'to'             => $filter_to
            ]);

            // Insert Batch
            $processResults = $this->bank_reconciliation->batch_insert_with_log($datas);

            echo json_encode([
                "title"           => "Resona Upload Success",
                "delete_existing" => $deleteStatus,
                "total_success"   => count($datas),
                "results"         => $processResults,
            ]);

        } catch (Exception $e) {
            echo json_encode([
                "title"   => "Error", 
                "message" => $e->getMessage(), 
                "theme"   => "error"
            ]);
        }
    }

    // Import Bank Mandiri
    private function _upload_mandiri()
    {
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/json');

        if (!function_exists('mime_content_type')) {
            function mime_content_type($filename) {
                return 'text/plain'; // Fallback manual untuk CSV
            }
        }

        require_once 'assets/vendors/phpspreadsheet/vendor/autoload.php';

        try {
            if (!isset($_FILES['file_upload']) || $_FILES['file_upload']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("File not found or upload error.");
            }

            $filter_bank_account   = $this->input->post("filter_bank_account");
            $filter_from           = $this->input->post("filter_from");
            $filter_to             = $this->input->post("filter_to");
            $filter_account_number = $this->input->post("filter_account_number");

            $tmpPath     = $_FILES['file_upload']['tmp_name'];
            $extension   = strtolower(pathinfo($_FILES['file_upload']['name'], PATHINFO_EXTENSION));

            // Load Spreadsheet (Handle CSV atau Excel)
            if ($extension == 'csv') {
                // Buat reader khusus CSV
                $reader = IOFactory::createReader('Csv');
                if ($reader instanceof Csv) {
                    $reader->setDelimiter(';'); 
                    $reader->setEnclosure('"');
                }

                $spreadsheet = $reader->load($tmpPath);
            } else {
                $spreadsheet = IOFactory::load($tmpPath);
            }
            
            $sheet = $spreadsheet->getActiveSheet();
            $this->load->model('bank_reconciliation');

            // Hapus data lama SEBELUM generate ID baru
            $deleteStatus = $this->bank_reconciliation->replace_existing_data([
                'bank_account'   => $filter_bank_account,
                'account_number' => $filter_account_number,
                'from'           => $filter_from,
                'to'             => $filter_to
            ]);

            $highestRow  = $sheet->getHighestRow();
            $datas       = [];

            // --- PROSES TRANSAKSI (Mulai baris 2) ---
            for ($i = 2; $i <= $highestRow; $i++) {
                // Gunakan getFormattedValue agar angka panjang tidak jadi format scientific (e.g. 1.56E+12)
                $accountNo = preg_replace('/[^0-9]/', '', (string)$sheet->getCell("A$i")->getFormattedValue());
                
                if (empty($accountNo)) continue;

                // Match bank account dari filter dengan bank account di dalam excel
                $bank_account = preg_replace('/[^0-9]/', '', $filter_bank_account);
                if ($accountNo !== $bank_account) {
                    echo json_encode(["title" => "Not Matched", "message" => "Bank Account inside the file (" . $accountNo . ") is Not Match with the selected data!", "theme" => "error"]);
                    return;
                }
                
                // Format Posting Date (Contoh: "01 April 2026 12:13:03")
                $rawDate = $sheet->getCell("C$i")->getValue();
                $postingDate = $this->_formatMandiriDate($rawDate);

                // Get Remarks (D) dan AdditionalDesc (E)
                $remarks = $sheet->getCell("D$i")->getValue();
                $addDesc = $sheet->getCell("E$i")->getValue();
                $cleanRemark = preg_replace('/\s+/', ' ', trim((string)$remarks . " " . $addDesc));

                $datas[] = [
                    'account_number' => $filter_account_number,
                    'bank_account'   => $filter_bank_account,
                    'start_date'     => $filter_from,
                    'end_date'       => $filter_to,
                    'currency'       => 'IDR',
                    'source'         => 'upload',
                    'posting_date'   => $postingDate,
                    'remark'         => htmlspecialchars($cleanRemark),
                    'credit'         => abs((float) str_replace(',', '', $sheet->getCell("F$i")->getCalculatedValue() ?? 0)),
                    'debit'          => abs((float) str_replace(',', '', $sheet->getCell("G$i")->getCalculatedValue() ?? 0)),
                    'status'         => 0,
                    'created_date'   => date('Y-m-d H:i:s'),
                    'created_by'     => $this->session->username,
                ];
            }

            if (empty($datas)) {
                throw new Exception("No valid transaction data found in this file.");
            }

            // --- EXECUTE DATABASE ---
            $processResults = $this->bank_reconciliation->batch_insert_with_log($datas);

            echo json_encode([
                "title"           => "Upload Data Success",
                "delete_existing" => $deleteStatus,
                "total_success"   => count($datas),
                "results"         => $processResults,
            ]);

        } catch (Exception $e) {
            echo json_encode(["title" => "Error", "message" => $e->getMessage(), "theme" => "error"]);
        }
    }

    public function upload_existing()
    {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(0);
        
        header('Content-Type: application/json');
        
        require_once 'assets/vendors/excel_reader2.php';
        header('Content-Type: application/json');
        
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        
        $dataBank = [
            'bank_account' => $data->val(3, 3),
            'start_date'   => $data->val(4, 3),
            'end_date'     => $data->val(5, 3),
            'currency'     => $data->val(6, 3),
        ];

        $filter_account_number = $this->input->post("filter_account_number") ?? null;
        $filter_bank_account = $this->input->post("filter_bank_account") ?? null;
        $filter_from = $this->input->post("filter_from") ?? null;
        $filter_to = $this->input->post("filter_to") ?? null;


        // CHECK Date Period same as excel
        if (strtotime($filter_from) !== strtotime($dataBank['start_date']) && strtotime($filter_to) !== strtotime($dataBank['end_date']) ) {
            echo json_encode(["title" => "Not Matched", "message" => "Failed! Period in Excel Is Not Match with the selected Date", "theme" => "error"]);
            return;
        }

        // CHECK Bank Account Number same as excel
        $account_banks = $this->crud->read('account_banks', [], ["account_number" => $filter_account_number]);
        if (!$account_banks || $account_banks->bank_account !== $dataBank['bank_account'] || $filter_bank_account !== $dataBank['bank_account']) {
            echo json_encode(["title" => "Not Matched", "message" => "Failed! Bank Account ". $dataBank['bank_account'] ." inside the file is Not Match with the selected data", "theme" => "error"]);
            return;
        }

        // CHECK data reconciliation sudah ada
        $this->db->select('*');
        $this->db->from('bank_reconciliation');
        $this->db->where('bank_account', $filter_bank_account);
        $this->db->where('account_number', $filter_account_number);
        $this->db->where("DATE_FORMAT(posting_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");
        $this->db->where("start_date BETWEEN '$filter_from' AND '$filter_to'");
        $this->db->where("end_date BETWEEN '$filter_from' AND '$filter_to'");
        $reconciliation_exists = $this->db->get();
        // jika data available : ketika diupload dgn template baru, data yang ada dihapus
        if (!empty($reconciliation_exists)) 
        {    
            $this->db->where('bank_account', $filter_bank_account);
            $this->db->where('account_number', $filter_account_number);
            $this->db->where("DATE_FORMAT(posting_date, '%Y-%m-%d') BETWEEN '$filter_from' AND '$filter_to'");
            $this->db->where("start_date BETWEEN '$filter_from' AND '$filter_to'");
            $this->db->where("end_date BETWEEN '$filter_from' AND '$filter_to'");
            $deletePeriod = $this->db->delete('bank_reconciliation');

            if ($deletePeriod) {
                $deleteStatus = ["title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"];
                $dataBefore = $this->crud->read('bank_reconciliation', [], ['bank_account' => $filter_bank_account, 'account_number' => $filter_account_number, 'start_date' => $filter_from, 'end_date' => $filter_to]);
                $this->crud->logs("Delete", json_encode($dataBefore), 'bank_reconciliation');
            } else {
                $deleteStatus = ["title" => "Info", "message" => "No changes detected, data remains the same.", "theme" => "info"];
            }
        }

        $datas = [];
        for ($i = 10; $i <= $total_row; $i++) {
            $datas[] = [
                'posting_date' => date("Y-m-d H:i:s", strtotime($data->val($i, 2))),
                'remark'       => htmlspecialchars($data->val($i, 3)),
                'debit'        => str_replace(',', '', $data->val($i, 4)),
                'credit'       => str_replace(',', '', $data->val($i, 5))
            ];
        }

        $payload = [
            "bank"  => $dataBank,
            "data"  => $datas,
            "total" => count($datas),
            "delete_existing" => $deleteStatus ?? null,
        ];
        echo json_encode($payload);
        
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/bank_reconciliation.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/bank_reconciliation.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/bank_reconciliation.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    public function uploadCreate()
    {
        if ($this->input->post()) {
            $bank = $this->input->post('bank');
            $data = $this->input->post('data');

            if (empty($bank['bank_account'])) {
                echo json_encode(array("title" => "Error", "message" => "Bank Account is required!", "theme" => "error"));
            } else {
                // check available account_bank
                $account_banks = $this->crud->read('account_banks', [], ["bank_account" => $bank['bank_account']]);
    
                if (empty($account_banks->bank_account)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Bank Account Of " . $bank['bank_account'] ." Is Not Found", "theme" => "error"));
                } else {
    
                    $dataFinal = [
                        // Data Bank
                        "account_number"  => $account_banks->account_number,
                        "bank_account"    => $bank['bank_account'],
                        "start_date"      => $bank['start_date'],
                        "end_date"        => $bank['end_date'],
                        "currency"        => $bank['currency'],
                        // Data Transactions
                        "source"          => "Bank",
                        "posting_date"    => $data["posting_date"],
                        "remark"          => $data["remark"],
                        "credit"          => $data["credit"],
                        "debit"           => $data["debit"],
                    ];
                    
                    // CREATE NEW 
                    $send = $this->crud->create('bank_reconciliation', $dataFinal);
                    echo $send;

                }
            }

        }
    }



    public function getJournal($filter_from, $filter_to, $filter_account_number)
    {
        $filter_before = date("Y-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));
        
        $start_date = date("Y-m-d", strtotime($filter_from));
        $end_date   = date("Y-m-d", strtotime($filter_to));

        $summary = [];
        
        $this->db->select('*');
        $this->db->from('account_coa');
        $this->db->where("account_number", $filter_account_number);
        $account_coa = $this->db->get()->row();
        if ($account_coa) {
            $begin_account_no = $account_coa->account_number;
            $begin_balance_local = ($account_coa->local_debit + $account_coa->local_kredit);
            $begin_balance_ori = ($account_coa->original_debit + $account_coa->original_kredit);
        } else {
            // Tangani kasus jika akun tidak ditemukan
            $begin_account_no = null;
            $begin_balance_local = 0;
            $begin_balance_ori = 0;
        }
        
        $this->db->select('*');
        $this->db->from('journal_postings');
        $this->db->where('trans_date >=', $start_date);
        $this->db->where('trans_date <=', $end_date);
        $this->db->where("account_number", $filter_account_number);
        $this->db->group_start();
        $this->db->where('modul', 'AP PAYMENT');
        $this->db->or_where('modul', 'AR RECEIPT');
        $this->db->or_where('modul', 'CURRENCY REVALUATION');
        $this->db->group_end();
        $this->db->order_by('trans_date', 'asc');
        $this->db->order_by('document_no', 'asc');
        $this->db->order_by('id', 'asc'); // Tambahkan ini
        $journals = $this->db->get()->result_array();

        $this->db->select('account_number, account_name,
            COALESCE(SUM(original_debit)) as original_debit,
            COALESCE(SUM(original_credit)) as original_credit,
            COALESCE(SUM(local_debit)) as local_debit,
            COALESCE(SUM(local_credit)) as local_credit');
        $this->db->from('journal_postings');
        $this->db->where("trans_date between '$filter_before' and '$filter_before_to'");
        $this->db->where("account_number", $filter_account_number);
        $this->db->group_start();
        $this->db->where('modul', 'AP PAYMENT');
        $this->db->or_where('modul', 'AR RECEIPT');
        $this->db->or_where('modul', 'CURRENCY REVALUATION');
        $this->db->group_end();
        $this->db->order_by('trans_date', 'asc');
        $this->db->order_by('id', 'asc'); // Tambahkan ini
        $journal_bf = $this->db->get()->row();

        $journal_ori_debit = @$journal_bf->original_debit;
        $journal_ori_credit = @$journal_bf->original_credit;
        $journal_local_debit = @$journal_bf->local_debit;
        $journal_local_credit = @$journal_bf->local_credit;

        $journal_end_ori_debit = 0;
        $journal_end_ori_credit = 0;
        $journal_end_local_debit = 0;
        $journal_end_local_credit = 0;
        
        if ( (($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit) > 0 ) {
            $journal_end_ori_debit = abs(($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit);
            $journal_end_ori_credit = 0;
            $journal_end_local_debit = abs(($begin_balance_local + @$journal_local_debit) - @$journal_local_credit);
            $journal_end_local_credit = 0;
        } else {
            $journal_end_ori_debit = 0;
            $journal_end_ori_credit = abs(($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit);
            $journal_end_local_debit = 0;
            $journal_end_local_credit = abs(($begin_balance_local + @$journal_local_debit) - @$journal_local_credit);
        }
        
        $ori_balance = ($journal_end_ori_debit + $journal_end_ori_credit);
        $ori_debit = 0;
        $ori_credit = 0;
        $local_balance = ($journal_end_local_debit + $journal_end_local_credit);
        $local_debit = 0;
        $local_credit = 0;

        $summary['currency'] = $account_coa->original_currency;
        $summary['open_ori_balance'] = $ori_balance;
        $summary['open_local_balance'] = $local_balance;
        
        $journalTransactions = [];
        $last_transaction = null;
        $status_recheck = false;
        $no = 1;

        foreach ($journals as $journal) 
        {
            // Status Recheck transaksi identik
            if ($last_transaction && 
                $last_transaction['trans_date'] == $journal['trans_date'] &&
                $last_transaction['original_debit'] == $journal['original_debit'] &&
                $last_transaction['original_credit'] == $journal['original_credit']) 
            {
                $status_recheck = true;
            } else {
                $status_recheck = false;
            }

            $journalTransactions[] = [
                "no"                => $no,
                "id"                => $journal['id'],
                "trans_date"        => $journal['trans_date'],
                "number"            => $journal['number'],
                "account_number"    => $journal['account_number'],
                "account_name"      => $journal['account_name'],
                "description"       => $journal['description'],
                "currency"          => $journal['currency'],
                "ori_balance"       => $ori_balance,
                "original_debit"    => $journal['original_debit'],
                "original_credit"   => $journal['original_credit'],
                "local_balance"     => $local_balance,
                "local_debit"       => $journal['local_debit'],
                "local_credit"      => $journal['local_credit'],
                "status_recheck"    => $status_recheck,
            ];
            
            $ori_balance += ($journal['original_debit'] - $journal['original_credit']);
            $local_balance += ($journal['local_debit'] - $journal['local_credit']);  
            

            $ori_debit += $journal['original_debit'];
            $ori_credit += $journal['original_credit'];
            $local_debit += $journal['local_debit'];
            $local_credit += $journal['local_credit'];
            $no++;

            // Simpan transaksi saat ini untuk perbandingan selanjutnya
            $last_transaction = $journal;
        }

        $summary['ending_ori_balance'] = abs($ori_balance);
        $summary['ending_local_balance'] = abs($local_balance);
        $summary['grand_ori_debit'] = abs($ori_debit);
        $summary['grand_ori_credit'] = abs($ori_credit);
        $summary['grand_local_debit'] = abs($local_debit);
        $summary['grand_local_credit'] = abs($local_credit);

        $result = [
            'journal_summary' => $summary,
            'journal_transactions' => $journalTransactions,
        ];
        return $result;
    }

    public function getBankMutation($filter_from, $filter_to, $filter_account_number)
    {
        $filter_before = date("Y-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));
        
        $start_date = date("Y-m-d", strtotime($filter_from));
        $end_date   = date("Y-m-d", strtotime($filter_to));

        $summary = [];
        
        $this->db->select('coa.*, b.bank_account, b.currency');
        $this->db->from('account_coa coa');
        $this->db->join('account_banks b', 'b.account_number = coa.account_number', 'left');
        $this->db->where("coa.account_number", $filter_account_number);
        $account_coa = $this->db->get()->row();
        if ($account_coa) {
            $begin_account_no = $account_coa->account_number;
            $begin_balance_local = ($account_coa->local_debit + $account_coa->local_kredit);
            $begin_balance_ori = ($account_coa->original_debit + $account_coa->original_kredit);
        } else {
            // Tangani kasus jika akun tidak ditemukan
            $begin_account_no = null;
            $begin_balance_local = 0;
            $begin_balance_ori = 0;
        }

        $this->db->select('*');
        $this->db->from('bank_reconciliation');
        $this->db->where("DATE_FORMAT(posting_date, '%Y-%m-%d') BETWEEN '$start_date' AND '$end_date'");
        $this->db->where("account_number", $filter_account_number);
        $this->db->order_by('posting_date', 'asc');
        $this->db->order_by('account_number', 'asc');
        $mutations = $this->db->get()->result_array();

        $this->db->select('account_number, 
            COALESCE(SUM(debit)) as debit,
            COALESCE(SUM(credit)) as credit');
        $this->db->from('bank_reconciliation');
        $this->db->where("posting_date between '$filter_before' and '$filter_before_to'");
        $this->db->where("account_number", $filter_account_number);
        $bank_sum = $this->db->get()->row();

        $bank_ori_debit = @$bank_sum->debit;
        $bank_ori_credit = @$bank_sum->credit;

        $bank_end_ori_debit = 0;
        $bank_end_ori_credit = 0;
        
        if ( (($begin_balance_ori + @$bank_ori_debit) - @$bank_ori_credit) > 0 ) {
            $bank_end_ori_debit = abs(($begin_balance_ori + @$bank_ori_debit) - @$bank_ori_credit);
            $bank_end_ori_credit = 0;
        } else {
            $bank_end_ori_debit = 0;
            $bank_end_ori_credit = abs(($begin_balance_ori + @$bank_ori_debit) - @$bank_ori_credit);
        }
        
        $ori_balance = ($bank_end_ori_debit + $bank_end_ori_credit);
        $ori_debit = 0;
        $ori_credit = 0;

        $summary['currency'] = $account_coa->original_currency;
        $summary['open_ori_balance'] = $ori_balance;
        
        $bankMutations = [];
        $last_transaction = null;
        $status_recheck = false;
        $no = 1;
        
        foreach ($mutations as $bank) 
        {
            // Status Recheck transaksi identik
            if ($last_transaction && 
                $last_transaction['posting_date'] == $bank['posting_date'] &&
                $last_transaction['debit'] == $bank['debit'] &&
                $last_transaction['credit'] == $bank['credit']) 
            {
                $status_recheck = true;
            } else {
                $status_recheck = false;
            }

            $bankMutations[] = [
                "no"             => $no,
                "id"             => $bank['id'],
                "posting_date"   => $bank['posting_date'],
                "account_number" => $bank['account_number'],
                "remark"         => $bank['remark'],
                "currency"       => $bank['currency'],
                "balance"        => $ori_balance,
                "debit"          => $bank['debit'],
                "credit"         => $bank['credit'],
                "status_recheck" => $status_recheck,
            ];
            
            $ori_balance += ($bank['debit'] - $bank['credit']);
            
            $ori_debit += $bank['debit'];
            $ori_credit += $bank['credit'];
            $no++;

            // Simpan transaksi saat ini untuk perbandingan selanjutnya
            $last_transaction = $bank;
        }

        $summary['ending_ori_balance'] = abs($ori_balance);
        $summary['grand_ori_debit'] = abs($ori_debit);
        $summary['grand_ori_credit'] = abs($ori_credit);

        $result = [
            'bank_summary'   => $summary,
            'bank_mutations' => $bankMutations,
        ];
        return $result;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=bank_reconciliation_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to   = base64_decode($this->input->get("filter_to"));
        $filter_account_number   = base64_decode($this->input->get("filter_account_number"));       

        if (empty($filter_from) || !strtotime($filter_from)) {
            show_error('Invalid "filter_from" date parameter.');
            return;
        }
        if (empty($filter_to) || !strtotime($filter_to)) {
            show_error('Invalid "filter_to" date parameter.');
            return;
        }
        if (empty($filter_account_number)) {
            show_error('Bank Account is required.');
            return;
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Bank Account
        $this->db->select('*');
        $this->db->from('account_banks');
        $this->db->where('account_number', $filter_account_number);
        $dataBank = $this->db->get()->row();

        // GET DATA JOURNAL 
        $dataJournal = $this->getJournal($filter_from, $filter_to, $filter_account_number);
        
        // GET DATA BANK MUTATION 
        $dataMutation = $this->getBankMutation($filter_from, $filter_to, $filter_account_number);

        // --- Core Reconciliation Logic ---
        $matched_transactions = [];
        $unmatched_bank = [];
        $unmatched_journal = [];

        // Temporary arrays to track matched IDs
        $matched_journal_ids = [];
        $matched_bank_ids = [];

        // Loop through bank entries and try to match them with journal entries
        foreach ($dataMutation['bank_mutations'] as $b_key => $bank_entry) {
            $is_matched = false;
            foreach ($dataJournal['journal_transactions'] as $j_key => $journal_entry) {
                // Check if this journal entry has already been matched
                if (in_array($journal_entry['id'], $matched_journal_ids)) {
                    continue; // Skip if already matched
                }
                
                // Matching criteria
                $j_date = date("Y-m-d", strtotime($journal_entry['trans_date']));
                $b_date = date("Y-m-d", strtotime($bank_entry['posting_date']));
                
                $journal_amount_debit = (float)$journal_entry['original_debit'];
                $journal_amount_credit = (float)$journal_entry['original_credit'];
                $bank_amount_debit = (float)$bank_entry['debit'];
                $bank_amount_credit = (float)$bank_entry['credit'];

                // Check for debit match
                $debit_match = abs($journal_amount_debit - $bank_amount_debit) < 0.01 && $journal_amount_credit == 0 && $bank_amount_credit == 0;
                // Check for credit match
                $credit_match = abs($journal_amount_credit - $bank_amount_credit) < 0.01 && $journal_amount_debit == 0 && $bank_amount_debit == 0;
                
                if ($j_date == $b_date && ($debit_match || $credit_match)) {
                    $bank_entry['result'] = "Matched";
                    $journal_entry['result'] = "Matched";

                    $matched_transactions[] = [
                        'journal_data' => $journal_entry,
                        'bank_data' => $bank_entry,
                        'posting_date' => $j_date // Tambahkan posting_date untuk sorting
                    ];

                    $matched_journal_ids[] = $journal_entry['id'];
                    $matched_bank_ids[] = $bank_entry['id'];

                    $is_matched = true;
                    break;
                }
            }

            if (!$is_matched) {
                $bank_entry['result'] = "Not Matched";
                $bank_entry['posting_date'] = date("Y-m-d", strtotime($bank_entry['posting_date'])); // Tambahkan posting_date
                $unmatched_bank[] = $bank_entry;
            }
        }
        
        // Find all unmatched journal entries
        foreach ($dataJournal['journal_transactions'] as $journal_entry) {
            if (!in_array($journal_entry['id'], $matched_journal_ids)) {
                $journal_entry['result'] = "Not Matched";
                $journal_entry['posting_date'] = date("Y-m-d", strtotime($journal_entry['trans_date'])); // Tambahkan posting_date
                $unmatched_journal[] = $journal_entry;
            }
        }

        // Menggabungkan semua data ke dalam satu array untuk sort date A-Z (Bu Nina)
        $all_transactions = [];
        foreach ($matched_transactions as $matched) {
            $all_transactions[] = [
                'type' => 'matched',
                'data' => $matched,
                'sort_date' => $matched['posting_date']
            ];
        }
        foreach ($unmatched_bank as $unmatched) {
            $all_transactions[] = [
                'type' => 'unmatched_bank',
                'data' => $unmatched,
                'sort_date' => $unmatched['posting_date']
            ];
        }
        foreach ($unmatched_journal as $unmatched) {
            $all_transactions[] = [
                'type' => 'unmatched_journal',
                'data' => $unmatched,
                'sort_date' => $unmatched['posting_date']
            ];
        }

        // Mengurutkan array berdasarkan 'sort_date' secara ascending (ASC)
        usort($all_transactions, function($a, $b) {
            return strtotime($a['sort_date']) - strtotime($b['sort_date']);
        });
        
        // PRINT
        $html = "";
        $html .= '<html>
                <head>
                <title>Bank Reconciliation - '. date("F Y", strtotime($filter_to)).'</title>';
        $html .= $this->customTable();
        $html .= '</head><body>';

        $html .= '<div class="header-section clearfix">
                    <div id="print-header-container">
                        <header class="header-section">
                            <table width="100%">
                                <tr>
                                    <td width="30">
                                        <img src="' . htmlspecialchars($config->favicon ?? "") . '" width="30" alt="Logo">
                                    </td>
                                    <td width="60%">
                                        <div class="logo-text">
                                            <p>
                                                <b>' . htmlspecialchars($config->name ?? 'Company Name Not Set') . '</b> <br>
                                                <small>' . htmlspecialchars($config->description ?? 'Description Not Set') . '</small>
                                            </p>
                                        </div>
                                    </td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td width="40%" align="right">
                                        <div class="print-info">
                                            Print Date ' . date("d M Y H:i:s") . ' <br>
                                            Print By ' . htmlspecialchars($this->session->username) . '
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </header>
                    </div>';

        $html .= '<div class="report-title">
                    <h3>BANK RECONCILIATION</h3>
                    </div>
                <br>
                <div class="two-panel-section">
                    <table width="100%" border="0">
                        <tr>
                            <td width="50%">
                                <table> 
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Cut off Date</td>
                                        <td><b>' . htmlspecialchars(date("d M Y", strtotime($filter_from))) . '</b> to <b> ' . htmlspecialchars(date("d M Y", strtotime($filter_to))) . ' </b></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Bank Account</td>
                                        <td><b>' . htmlspecialchars($dataBank->bank_account) . '</b></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Bank Name</td>
                                        <td>' . htmlspecialchars($dataBank->bank_name) . '</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Currency</td>
                                        <td>' . htmlspecialchars($dataBank->currency) . '</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Opening Balance</td>
                                        <td>' . $this->formatIDR($dataBank->balance) . '</td>
                                    </tr>
                                </table>
                            </td>
                            <td>&nbsp;</td>
                            <td width="50%">
                                <table class="table-custom-summary">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left;">Summary</th>
                                            <th>Bank</th>
                                            <th>ERP</th>
                                            <th>DIFF</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Opening Balance</td>
                                            <td class="text-right">' . $this->formatIDR($dataMutation['bank_summary']['open_ori_balance']) . '</td> 
                                            <td class="text-right">' . $this->formatIDR($dataJournal['journal_summary']['open_ori_balance']) . '</td>
                                            ' . $this->balanceDiff($dataMutation['bank_summary']['open_ori_balance'], $dataJournal['journal_summary']['open_ori_balance']) . ' 
                                        </tr>
                                        <tr>
                                            <td>Debit</td>
                                            <td class="text-right">' . $this->formatIDR($dataMutation['bank_summary']['grand_ori_debit']) . '</td>
                                            <td class="text-right">' . $this->formatIDR($dataJournal['journal_summary']['grand_ori_debit']) . '</td>
                                            ' . $this->balanceDiff($dataMutation['bank_summary']['grand_ori_debit'], $dataJournal['journal_summary']['grand_ori_debit']) . '  
                                        </tr>
                                        <tr>
                                            <td>Credit</td>
                                            <td class="text-right">' . $this->formatIDR($dataMutation['bank_summary']['grand_ori_credit']) . '</td> 
                                            <td class="text-right">' . $this->formatIDR($dataJournal['journal_summary']['grand_ori_credit']) . '</td>
                                            ' . $this->balanceDiff($dataMutation['bank_summary']['grand_ori_credit'], $dataJournal['journal_summary']['grand_ori_credit']) . '  
                                        </tr>
                                        <tr>
                                            <td>Ending Balance</td>
                                            <td class="text-right">' . $this->formatIDR($dataMutation['bank_summary']['ending_ori_balance']) . '</td>
                                            <td class="text-right">' . $this->formatIDR($dataJournal['journal_summary']['ending_ori_balance']) . '</td>
                                            ' . $this->balanceDiff($dataMutation['bank_summary']['ending_ori_balance'], $dataJournal['journal_summary']['ending_ori_balance']) . ' 
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                <br>';

        $html .= '<table id="customers">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Source</th>
                        <th>Posting Date</th>
                        <th>Remark</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Balance</th>
                        <th>Results</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';

        $no = 1;
        // Loop melalui array yang sudah diurutkan
        foreach ($all_transactions as $transaction) {
            if ($transaction['type'] == 'matched') {
                $matched = $transaction['data'];
                $html .= '<tr>
                            <td rowspan="2">' . $no . '</td>
                            <td> Bank </td>
                            <td>' . $matched['bank_data']['posting_date'] . '</td>
                            <td>' . $matched['bank_data']['remark'] . '</td>
                            <td class="text-right">' . $this->formatIDR($matched['bank_data']['debit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($matched['bank_data']['credit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($matched['bank_data']['balance']) . '</td>
                            <td rowspan="2" class="text-center">' . $matched['bank_data']['result'] . '</td>
                            <td rowspan="2" class="text-center">' . $this->statusRecheck($matched['journal_data']['status_recheck']) . '</td>
                        </tr>';
                $html .= '<tr>
                            <td> ERP </td>
                            <td>' . $matched['journal_data']['trans_date'] . '</td>
                            <td>' . $matched['journal_data']['description'] . '</td>
                            <td class="text-right">' . $this->formatIDR($matched['journal_data']['original_debit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($matched['journal_data']['original_credit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($matched['journal_data']['ori_balance']) . '</td>
                        </tr>';
            } elseif ($transaction['type'] == 'unmatched_bank') {
                $rowBank = $transaction['data'];
                $html .= '<tr>
                            <td rowspan="2">' . $no . '</td>
                            <td> Bank </td>
                            <td>' . $rowBank['posting_date'] . '</td>
                            <td>' . $rowBank['remark'] . '</td>
                            <td class="text-right">' . $this->formatIDR($rowBank['debit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($rowBank['credit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($rowBank['balance']) . '</td>
                            <td rowspan="2" class="text-center" style="color:red;">' . $rowBank['result'] . '</td>
                            <td rowspan="2" class="text-center">' . $this->statusRecheck($rowBank['status_recheck']) . '</td>
                        </tr>';
                $html .= '<tr>
                            <td> ERP </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>';
            } elseif ($transaction['type'] == 'unmatched_journal') {
                $rowJournal = $transaction['data'];
                $html .= '<tr>
                            <td rowspan="2">' . $no . '</td>
                            <td> Bank </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td rowspan="2" class="text-center" style="color:red;"> ' . $rowJournal['result'] . ' </td>
                            <td rowspan="2" class="text-center"> ' . $this->statusRecheck($rowJournal['status_recheck']) . ' </td>
                        </tr>';
                $html .='<tr>
                            <td> ERP </td>
                            <td>' . $rowJournal['trans_date'] . '</td>
                            <td>' . $rowJournal['description'] . '</td>
                            <td class="text-right">' . $this->formatIDR($rowJournal['original_debit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($rowJournal['original_credit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($rowJournal['ori_balance']) . '</td>
                        </tr>';
            }
            $no++;
        }

        $html .= '</tbody></table>';
        $html .= '</body></html>';

        echo $html;
    }

    public function printMappingBiayaAdmin($option = "") // Mapping Biaya Admin
    {
        if ($option == "excel") {
            $format = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=bank_reconciliation_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_account_number = base64_decode($this->input->get("filter_account_number"));

        if (empty($filter_from) || !strtotime($filter_from)) {
            show_error('Invalid "filter_from" date parameter.');
            return;
        }
        if (empty($filter_to) || !strtotime($filter_to)) {
            show_error('Invalid "filter_to" date parameter.');
            return;
        }
        if (empty($filter_account_number)) {
            show_error('Bank Account is required.');
            return;
        }

        // Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Bank Account
        $this->db->select('*');
        $this->db->from('account_banks');
        $this->db->where('account_number', $filter_account_number);
        $dataBank = $this->db->get()->row();

        // GET DATA JOURNAL 
        $dataJournal = $this->getJournal($filter_from, $filter_to, $filter_account_number);

        // GET DATA BANK MUTATION 
        $dataMutation = $this->getBankMutation($filter_from, $filter_to, $filter_account_number);

        // --- Core Reconciliation Logic ---
        $matched_transactions = [];
        $unmatched_bank = [];
        $unmatched_journal = [];

        // Temporary arrays to track matched IDs
        $matched_journal_ids = [];
        $matched_bank_ids = [];
        
        // Group "Biaya Admin" transactions by date
        $grouped_unmatched_bank = [];
        foreach ($dataMutation['bank_mutations'] as $bank_entry) {
            $date = date("Y-m-d", strtotime($bank_entry['posting_date']));
            if (!isset($grouped_unmatched_bank[$date])) {
                $grouped_unmatched_bank[$date] = [
                    'transactions' => [],
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'has_admin_fee' => false
                ];
            }
            
            $is_admin_fee = strpos(strtolower($bank_entry['remark']), 'biaya admin') !== false;
            
            // Add a new key to identify original vs. admin fee
            $bank_entry['is_admin_fee'] = $is_admin_fee;
            
            $grouped_unmatched_bank[$date]['transactions'][] = $bank_entry;
            $grouped_unmatched_bank[$date]['total_debit'] += (float)$bank_entry['debit'];
            $grouped_unmatched_bank[$date]['total_credit'] += (float)$bank_entry['credit'];
            
            if ($is_admin_fee) {
                $grouped_unmatched_bank[$date]['has_admin_fee'] = true;
            }
        }
        
        // Loop through grouped bank entries and try to match them with journal entries
        foreach ($grouped_unmatched_bank as $date => $bank_group) {
            $is_matched = false;
            
            // Find a matching journal entry for this date and total amount
            foreach ($dataJournal['journal_transactions'] as $j_key => $journal_entry) {
                if (in_array($journal_entry['id'], $matched_journal_ids)) {
                    continue; // Skip if already matched
                }

                $j_date = date("Y-m-d", strtotime($journal_entry['trans_date']));
                $journal_amount_debit = (float)$journal_entry['original_debit'];
                $journal_amount_credit = (float)$journal_entry['original_credit'];
                
                $bank_amount_debit = $bank_group['total_debit'];
                $bank_amount_credit = $bank_group['total_credit'];
                
                // Check for debit match
                $debit_match = abs($journal_amount_debit - $bank_amount_debit) < 0.01 && $journal_amount_credit == 0 && $bank_amount_credit == 0;
                // Check for credit match
                $credit_match = abs($journal_amount_credit - $bank_amount_credit) < 0.01 && $journal_amount_debit == 0 && $bank_amount_debit == 0;
                
                if ($j_date == $date && ($debit_match || $credit_match)) {
                    foreach ($bank_group['transactions'] as $bank_entry) {
                        $matched_transactions[] = [
                            'journal_data' => $journal_entry,
                            'bank_data' => $bank_entry,
                            'is_admin_fee' => $bank_entry['is_admin_fee']
                        ];
                        $matched_bank_ids[] = $bank_entry['id'];
                    }

                    $matched_journal_ids[] = $journal_entry['id'];
                    $is_matched = true;
                    break;
                }
            }
            
            // If no match found for the entire group, add individual transactions to unmatched
            if (!$is_matched) {
                foreach ($bank_group['transactions'] as $bank_entry) {
                    if (!in_array($bank_entry['id'], $matched_bank_ids)) {
                        $unmatched_bank[] = $bank_entry;
                    }
                }
            }
        }
        
        // Find all unmatched journal entries
        foreach ($dataJournal['journal_transactions'] as $journal_entry) {
            if (!in_array($journal_entry['id'], $matched_journal_ids)) {
                $unmatched_journal[] = $journal_entry;
            }
        }

        $result = [
            'success' => true,
            'message' => 'Reconciliation data fetched successfully.',
            'matched_transactions' => $matched_transactions,
            'unmatched_bank_transactions' => $unmatched_bank,
            'unmatched_journal_postings' => $unmatched_journal
        ];

        // PRINT 
        $html = "";
        $html .= '<html>
                <head>
                <title>Bank Reconciliation - <?php echo date("F Y", strtotime($filter_to)); ?></title>';
        $html .= $this->customTable();
        $html .= '</head><body>';

        $html .= '<div class="header-section clearfix">
                <div id="print-header-container">
                        <header class="header-section">
                            <table width="100%">
                                <tr>
                                    <td width="30">
                                        <img src="' . htmlspecialchars($config->favicon ?? "") . '" width="30" alt="Logo">
                                    </td>
                                    <td width="60%">
                                        <div class="logo-text">
                                            <p>
                                                <b>' . htmlspecialchars($config->name ?? 'Company Name Not Set') . '</b> <br>
                                                <small>' . htmlspecialchars($config->description ?? 'Description Not Set') . '</small>
                                            </p>
                                        </div>
                                    </td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td width="40%" align="right">
                                        <div class="print-info">
                                            Print Date ' . date("d M Y H:i:s") . ' <br>
                                            Print By ' .  htmlspecialchars($this->session->username) . '
                                        </div>
                                    </td>
                                <tr>
                            </table>                                
                        </header>

                    </div>';

        $html .= '<div class="report-title">
                    <h3>BANK RECONCILIATION</h3>
                    </div>
                <br>

                <div class="two-panel-section">
                    <table width="100%" border="0">
                        <tr>
                            <td width="50%">
                                <table> 
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Cut off Date</td>
                                        <td><b>' . htmlspecialchars(date("d M Y", strtotime($filter_from))) . '</b> to <b> ' . htmlspecialchars(date("d M Y", strtotime($filter_to))) . ' </b></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Bank Account</td>
                                        <td><b>' . htmlspecialchars($dataBank->bank_account) . '</b></td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Bank Name</td>
                                        <td>' . htmlspecialchars($dataBank->bank_name) . '</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Currency</td>
                                        <td>' . htmlspecialchars($dataBank->currency) . '</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td width="50%">Opening Balance</td>
                                        <td>' . $this->formatIDR($dataBank->balance) . '</td>
                                    </tr>

                                </table>

                            </td>
                            <td>&nbsp;</td>
                            <td width="50%">
                                <table class="table-custom-summary">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left;">Summary</th>
                                            <th>Bank</th>
                                            <th>ERP</th>
                                            <th>DIFF</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Opening Balance</td>
                                            <td class="text-right">' . $this->formatIDR($dataMutation['bank_summary']['open_ori_balance']) . '</td> 
                                            <td class="text-right">' . $this->formatIDR($dataJournal['journal_summary']['open_ori_balance']) . '</td>
                                            ' . $this->balanceDiff($dataMutation['bank_summary']['open_ori_balance'], $dataJournal['journal_summary']['open_ori_balance']) . ' 
                                        </tr>
                                        <tr>
                                            <td>Debit</td>
                                            <td class="text-right">' . $this->formatIDR($dataMutation['bank_summary']['grand_ori_debit']) . '</td>
                                            <td class="text-right">' . $this->formatIDR($dataJournal['journal_summary']['grand_ori_debit']) . '</td>
                                            ' . $this->balanceDiff($dataMutation['bank_summary']['grand_ori_debit'], $dataJournal['journal_summary']['grand_ori_debit']) . '  
                                        </tr>
                                        <tr>
                                            <td>Credit</td>
                                            <td class="text-right">' . $this->formatIDR($dataMutation['bank_summary']['grand_ori_credit']) . '</td> 
                                            <td class="text-right">' . $this->formatIDR($dataJournal['journal_summary']['grand_ori_credit']) . '</td>
                                            ' . $this->balanceDiff($dataMutation['bank_summary']['grand_ori_credit'], $dataJournal['journal_summary']['grand_ori_credit']) . '  
                                        </tr>
                                        <tr>
                                            <td>Ending Balance</td>
                                            <td class="text-right">' . $this->formatIDR($dataMutation['bank_summary']['ending_ori_balance']) . '</td>
                                            <td class="text-right">' . $this->formatIDR($dataJournal['journal_summary']['ending_ori_balance']) . '</td>
                                            ' . $this->balanceDiff($dataMutation['bank_summary']['ending_ori_balance'], $dataJournal['journal_summary']['ending_ori_balance']) . ' 
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                <br>';
        $html .= '<table id="customers">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Source</th>
                        <th>Posting Date</th>
                        <th>Remark</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Balance</th>
                        <th>Results</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';

        $no = 1;

        // MATCHED BANK X JOURNAL
        if (!empty($result['matched_transactions'])) {
            $last_journal_id = null;
            $bank_entries_for_journal = [];

            foreach ($result['matched_transactions'] as $matched) {
                $current_journal_id = $matched['journal_data']['id'];

                if ($current_journal_id != $last_journal_id) {
                    // Process and print previous group
                    if (!empty($bank_entries_for_journal)) {
                        // Print the first bank entry with rowspan
                        $first_bank = $bank_entries_for_journal[0];
                        $html .= '<tr>
                                    <td rowspan="' . (count($bank_entries_for_journal) + 1) . '">' . $no . '</td>
                                    <td> Bank </td>
                                    <td>' . date("d M Y", strtotime($first_bank['bank_data']['posting_date'])) . '</td>
                                    <td>' . htmlspecialchars($first_bank['bank_data']['remark']) . '</td>
                                    <td class="text-right">' . $this->formatIDR($first_bank['bank_data']['debit']) . '</td>
                                    <td class="text-right">' . $this->formatIDR($first_bank['bank_data']['credit']) . '</td>
                                    <td class="text-right">' . $this->formatIDR($first_bank['bank_data']['balance']) . '</td>
                                    <td rowspan="' . (count($bank_entries_for_journal) + 1) . '" class="text-center">' . htmlspecialchars($first_bank['bank_data']['result']) . '</td>
                                    <td rowspan="' . (count($bank_entries_for_journal) + 1) . '" class="text-center">' . $this->statusRecheck($first_bank['journal_data']['status_recheck']) . '</td>
                                </tr>';
                        
                        // Print remaining bank entries
                        for ($i = 1; $i < count($bank_entries_for_journal); $i++) {
                            $bank_entry = $bank_entries_for_journal[$i];
                            $html .= '<tr>
                                        <td> Bank </td>
                                        <td>' . date("d M Y", strtotime($bank_entry['bank_data']['posting_date'])) . '</td>
                                        <td>' . htmlspecialchars($bank_entry['bank_data']['remark']) . '</td>
                                        <td class="text-right">' . $this->formatIDR($bank_entry['bank_data']['debit']) . '</td>
                                        <td class="text-right">' . $this->formatIDR($bank_entry['bank_data']['credit']) . '</td>
                                        <td class="text-right">' . $this->formatIDR($bank_entry['bank_data']['balance']) . '</td>
                                    </tr>';
                        }

                        // Print journal entry
                        $html .= '<tr>
                                    <td> ERP </td>
                                    <td>' . date("d M Y", strtotime($first_bank['journal_data']['trans_date'])) . '</td>
                                    <td>' . htmlspecialchars($first_bank['journal_data']['description']) . '</td>
                                    <td class="text-right">' . $this->formatIDR($first_bank['journal_data']['original_debit']) . '</td>
                                    <td class="text-right">' . $this->formatIDR($first_bank['journal_data']['original_credit']) . '</td>
                                    <td class="text-right">' . $this->formatIDR($first_bank['journal_data']['ori_balance']) . '</td>
                                </tr>';
                        $no++;
                    }
                    
                    // Start new group
                    $bank_entries_for_journal = [$matched];
                    $last_journal_id = $current_journal_id;
                } else {
                    // Add to current group
                    $bank_entries_for_journal[] = $matched;
                }
            }
            
            // Print the last group
            if (!empty($bank_entries_for_journal)) {
                $first_bank = $bank_entries_for_journal[0];
                $html .= '<tr>
                            <td rowspan="' . (count($bank_entries_for_journal) + 1) . '">' . $no . '</td>
                            <td> Bank </td>
                            <td>' . date("d M Y", strtotime($first_bank['bank_data']['posting_date'])) . '</td>
                            <td>' . htmlspecialchars($first_bank['bank_data']['remark']) . '</td>
                            <td class="text-right">' . $this->formatIDR($first_bank['bank_data']['debit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($first_bank['bank_data']['credit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($first_bank['bank_data']['balance']) . '</td>
                            <td rowspan="' . (count($bank_entries_for_journal) + 1) . '" class="text-center"> Matched </td>
                            <td rowspan="' . (count($bank_entries_for_journal) + 1) . '" class="text-center">' . $this->statusRecheck($first_bank['journal_data']['status_recheck']) . '</td>
                        </tr>';
                
                for ($i = 1; $i < count($bank_entries_for_journal); $i++) {
                    $bank_entry = $bank_entries_for_journal[$i];
                    $html .= '<tr>
                                <td> Bank </td>
                                <td>' . date("d M Y", strtotime($bank_entry['bank_data']['posting_date'])) . '</td>
                                <td>' . htmlspecialchars($bank_entry['bank_data']['remark']) . '</td>
                                <td class="text-right">' . $this->formatIDR($bank_entry['bank_data']['debit']) . '</td>
                                <td class="text-right">' . $this->formatIDR($bank_entry['bank_data']['credit']) . '</td>
                                <td class="text-right">' . $this->formatIDR($bank_entry['bank_data']['balance']) . '</td>
                            </tr>';
                }

                $html .= '<tr>
                            <td> ERP </td>
                            <td>' . date("d M Y", strtotime($first_bank['journal_data']['trans_date'])) . '</td>
                            <td>' . htmlspecialchars($first_bank['journal_data']['description']) . '</td>
                            <td class="text-right">' . $this->formatIDR($first_bank['journal_data']['original_debit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($first_bank['journal_data']['original_credit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($first_bank['journal_data']['ori_balance']) . '</td>
                        </tr>';
                $no++;
            }
        }


        // NOT MATCHED BANK 
        if (!empty($result['unmatched_bank_transactions'])) {
            $grouped_unmatched_bank = [];
            foreach ($result['unmatched_bank_transactions'] as $rowBank) {
                $date = date("Y-m-d", strtotime($rowBank['posting_date']));
                if (!isset($grouped_unmatched_bank[$date])) {
                    $grouped_unmatched_bank[$date] = [];
                }
                $grouped_unmatched_bank[$date][] = $rowBank;
            }

            foreach ($grouped_unmatched_bank as $date => $bank_entries) {
                $rowspan_count = count($bank_entries) + 1; // +1 for the ERP row
                
                // Print bank entries for the current date group
                foreach ($bank_entries as $key => $rowBank) {
                    $html .= '<tr>';
                    if ($key === 0) { // First row of the group
                        $html .= '<td rowspan="' . $rowspan_count . '">' . $no . '</td>';
                    }
                    $html .= '<td> Bank </td>
                            <td>' . date("d M Y", strtotime($rowBank['posting_date'])) . '</td>
                            <td>' . htmlspecialchars($rowBank['remark']) . '</td>
                            <td class="text-right">' . $this->formatIDR($rowBank['debit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($rowBank['credit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($rowBank['balance']) . '</td>';

                    if ($key === 0) { // First row of the group
                        $html .= '<td rowspan="' . $rowspan_count . '" class="text-center" style="color:red;">Not Matched</td>
                                <td rowspan="' . $rowspan_count . '" class="text-center">' . $this->statusRecheck($rowBank['status_recheck']) . '</td>';
                    }
                    $html .= '</tr>';
                }
                
                // Print a single empty ERP row for the group
                $html .= '<tr>
                            <td> ERP </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>';
                
                $no++;
            }
        }

        // NOT MATCHED JOURNAL
        if (!empty($result['unmatched_journal_postings'])) {
            foreach ($result['unmatched_journal_postings'] as $rowJournal) {
                // NULL Bank
                $html .= '<tr>
                            <td rowspan="2">' . $no . '</td>
                            <td> Bank </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td rowspan="2" class="text-center" style="color:red;"> Not Matched </td>
                            <td rowspan="2" class="text-center"> ' . $this->statusRecheck($rowJournal['status_recheck']) . ' </td>
                        </tr>';
                // ERP
                $html .= '<tr>
                            <td> ERP </td>
                            <td>' . date("d M Y", strtotime($rowJournal['trans_date'])) . '</td>
                            <td>' . htmlspecialchars($rowJournal['description']) . '</td>
                            <td class="text-right">' . $this->formatIDR($rowJournal['original_debit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($rowJournal['original_credit']) . '</td>
                            <td class="text-right">' . $this->formatIDR($rowJournal['ori_balance']) . '</td>
                        </tr>';
                $no++;
            }
        }

        $html .= '</tbody></table>';
        $html .= '</body></html>';

        echo $html;
    }

    function formatIDR($number) {
        $formatted_number = number_format($number, 2, ',', '.');
        return $formatted_number;
    }

    function balanceDiff($bank, $erp) {
        $calc = abs($erp - $bank);
        if ($calc > 0) {
            return '<td style="color:red; font-weight:bold; text-align:right;"> ' . number_format($calc, 2, ",", ".") . '</td>';
        } else {
            return '<td style="color:green; font-weight:bold; text-align:right;"> ' . number_format($calc, 2, ",", ".") . '</td>';
        }
    }

    function statusRecheck($status) {
        $note = "";
        if ($status == true || $status == 1) {
            $note = "Recheck";
        }
        return $note;
    }

    function customTable() 
    {
        $css = '<style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
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
                        border: 1px solid black;
                        padding: 0.6rem; 
                    }
                    #customers th {
                        background-color: #4E73BE;
                        text-align: center;
                        color: white; 
                        font-weight: bold;
                    }
                    #customers tr:nth-child(even) {
                        background-color: #DEEBF7;;
                    }
                    #customers tr:hover {
                        background-color: #f1f1f1;
                    }

                    /* Aturan CSS khusus untuk print */
                    @media print {
                        body {
                            zoom: 90%;
                        }

                        /* Memaksa warna latar belakang untuk muncul saat dicetak */
                        #customers th {
                            background-color: #4E73BE !important;
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
                </style>';
        return $css;
    }

}
