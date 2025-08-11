<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
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
        //Validasi Form
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[50]');
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

    //UPLOAD DATA
    public function upload()
    {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(0);
        
        header('Content-Type: application/json');
        
        require_once 'assets/vendors/excel_reader_2025.php';
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

        // CHECK Bank Account No
        $filter_bank_account = $this->input->post("filter_bank_account");
        $account_banks = $this->crud->read('account_banks', [], ["account_number" => $filter_bank_account]);
        if (!$account_banks || $account_banks->bank_account !== $dataBank['bank_account']) {
            echo json_encode(["title" => "Not Matched", "message" => "Bank Account in Excel " . $bank['bank_account'] ." Is Not Match with the selected Account", "theme" => "error"]);
            return;
        }

        $datas = [];
        for ($i = 10; $i <= $total_row; $i++) {
            $datas[] = [
                'posting_date' => $data->val($i, 2),
                'remark'       => $data->val($i, 3),
                'credit'       => str_replace(',', '', $data->val($i, 4)),
                'debit'        => str_replace(',', '', $data->val($i, 5))
            ];
        }

        $payload = [
            "bank"  => $dataBank,
            "data"  => $datas,
            "total" => count($datas),
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

                $account_banks = $this->crud->read('account_banks', [], ["bank_account" => $bank['bank_account']]);
                $bank_reconciliation = $this->crud->read('bank_reconciliation', [], ["bank_account" => $bank['bank_account'], "start_date" => $bank['start_date'], "end_date" => $bank['end_date'], "currency" => $bank['currency'], 
                    "posting_date" => $data['posting_date'], "remark" => $data['remark']]);
    
                if (empty($account_banks->bank_account)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Bank Account Of " . $bank['bank_account'] ." Is Not Found", "theme" => "error"));
                } 
                elseif (!empty($bank_reconciliation)) {
                    echo json_encode(array("title" => "Error", "message" => "Data in this Period is already uploaded before!", "theme" => "error"));
                } 
                else {
    
                    $dataFinal = [
                        // Data Bank
                        "account_bank_id" => $account_banks->id,
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
    
                    $send   = $this->crud->create('bank_reconciliation', $dataFinal);
                    echo $send;
                }
            }

        }
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
        $filter_bank_account   = base64_decode($this->input->get("filter_bank_account"));        
        
        if (empty($filter_from) || !strtotime($filter_from)) {
            show_error('Invalid "filter_from" date parameter.');
            return;
        }
        if (empty($filter_to) || !strtotime($filter_to)) {
            show_error('Invalid "filter_to" date parameter.');
            return;
        }
        if (empty($filter_bank_account)) {
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
        $this->db->where('account_number', $filter_bank_account);
        $dataBank = $this->db->get()->row();
        
        $html = '';

        // PRINT 
        $html .= '<html>
            <head>
                <title>Bank Reconciliation - <?php echo date("F Y", strtotime($filter_to)); ?></title>
                <style>
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
                        text-align: left;
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
                </style>
            </head>
            <body>';

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
                                        <td><b>' . htmlspecialchars($filter_bank_account) . '</b></td>
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
                                        <td>' . number_format($dataBank->balance, 2, ",", ".") . '</td>
                                    </tr>

                                </table>

                            </td>
                            <td>&nbsp;</td>
                            <td width="50%">
                                <table class="table-custom-summary">
                                    <thead>
                                        <tr>
                                            <th>Summary</th>
                                            <th>Bank</th>
                                            <th>ERP</th>
                                            <th>DIFF</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Opening Balance</td>
                                            <td>1,187,058,303.01</td>
                                            <td>' . number_format($dataBank->balance, 2, ",", ".") . '</td>
                                            <td>0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Debit</td>
                                            <td>104,390,176.50</td>
                                            <td>104,390,176.50</td>
                                            <td>0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Credit</td>
                                            <td>712,447,347.00</td>
                                            <td>88,363,770.00</td>
                                            <td class="text-danger">624,083,577.00</td>
                                        </tr>
                                        <tr>
                                            <td>Ending Balance</td>
                                            <td>1,795,115,473.51</td>
                                            <td>1,171,031,896.51</td>
                                            <td class="text-danger">624,083,577.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>

                <br>                
                <table id="customers">
                    <!-- DATA DUMMY -->
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
                    <tbody>
                        <tr>
                            <td rowspan="2">1</td>
                            <td>Bank</td>
                            <td>07/02/2025 12:00</td>
                            <td>PT TRIKELA OTOELEKTRINDO PERKASA - 033 Trf Inv On BANK OCBC NISP -16707</td>
                            <td>0</td>
                            <td>87.042.070,00</td>
                            <td class="value-error">#VALUE!</td>
                            <td class="result-match">Match</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>ERP</td>
                            <td>07/02/2025 16:05</td>
                            <td>PEMBAYARAN INVOICE 001</td>
                            <td>0,00</td>
                            <td>87.042.070,00</td>
                            <td class="value-error">#VALUE!</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td rowspan="2">2</td>
                            <td>Bank</td>
                            <td>07/04/2025 16:15</td>
                            <td>PT BANSHU PLASTIC INV 00731724 17802 Transfer DARI MINDA ASEAN AUTOMOTIVE</td>
                            <td>0,00</td>
                            <td>260.850,00</td>
                            <td class="value-error">#VALUE!</td>
                            <td class="result-match">Match</td>
                            <td class="status-recheck">Recheck</td>
                        </tr>
                        <tr>
                            <td>ERP</td>
                            <td>07/04/2025 16:19</td>
                            <td>PEMBAYARAN INVOICE 001</td>
                            <td>0,00</td>
                            <td>260.850,00</td>
                            <td class="value-error">#VALUE!</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td rowspan="2">3</td>
                            <td>Bank</td>
                            <td>07/04/2025 18:15</td>
                            <td>PT BANSHU PLASTIC INV 00731724 Transfer DARI MINDA ASEAN AUTOMOTIVE</td>
                            <td>0,00</td>
                            <td>260.850,00</td>
                            <td class="value-error">#VALUE!</td>
                            <td class="result-match">Match</td>
                            <td class="status-recheck">Recheck</td>
                        </tr>
                        <tr>
                            <td>ERP</td>
                            <td>07/04/2025 18:15</td>
                            <td>PEMBAYARAN INVOICE 002</td>
                            <td>0,00</td>
                            <td>260.850,00</td>
                            <td class="value-error">#VALUE!</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td rowspan="2">4</td>
                            <td class="fw-semibold">Bank</td>
                            <td>2/7/2025 12:08</td>
                            <td>PT. TRINELA OTOOS EXTRINDO PERMATA - 018 ...</td>
                            <td class="text-end">0</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">ERP</td>
                            <td>2/7/2025 16:05</td>
                            <td>PEMBAYARAN INVOICE 001</td>
                            <td class="text-end">0.00</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                        </tr>
                        <tr>
                            <td rowspan="2">5</td>
                            <td class="fw-semibold">Bank</td>
                            <td>2/7/2025 12:08</td>
                            <td>PT. TRINELA OTOOS EXTRINDO PERMATA - 018 ...</td>
                            <td class="text-end">0</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">ERP</td>
                            <td>2/7/2025 16:05</td>
                            <td>PEMBAYARAN INVOICE 001</td>
                            <td class="text-end">0.00</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                        
                        <tr>
                            <td rowspan="2">6</td>
                            <td class="fw-semibold">Bank</td>
                            <td>2/7/2025 12:08</td>
                            <td>PT. TRINELA OTOOS EXTRINDO PERMATA - 018 ...</td>
                            <td class="text-end">0</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">ERP</td>
                            <td>2/7/2025 16:05</td>
                            <td>PEMBAYARAN INVOICE 001</td>
                            <td class="text-end">0.00</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td rowspan="2">7</td>
                            <td class="fw-semibold">Bank</td>
                            <td>2/7/2025 12:08</td>
                            <td>PT. TRINELA OTOOS EXTRINDO PERMATA - 018 ...</td>
                            <td class="text-end">0</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">ERP</td>
                            <td>2/7/2025 16:05</td>
                            <td>PEMBAYARAN INVOICE 001</td>
                            <td class="text-end">0.00</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td rowspan="2">8</td>
                            <td class="fw-semibold">Bank</td>
                            <td>2/7/2025 12:08</td>
                            <td>PT. TRINELA OTOOS EXTRINDO PERMATA - 018 ...</td>
                            <td class="text-end">0</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">ERP</td>
                            <td>2/7/2025 16:05</td>
                            <td>PEMBAYARAN INVOICE 001</td>
                            <td class="text-end">0.00</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td rowspan="2">9</td>
                            <td class="fw-semibold">Bank</td>
                            <td>2/7/2025 12:08</td>
                            <td>PT. TRINELA OTOOS EXTRINDO PERMATA - 018 ...</td>
                            <td class="text-end">0</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">ERP</td>
                            <td>2/7/2025 16:05</td>
                            <td>PEMBAYARAN INVOICE 001</td>
                            <td class="text-end">0.00</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                        </tr>
                        <tr>
                            <td rowspan="2">10</td>
                            <td class="fw-semibold">Bank</td>
                            <td>2/7/2025 12:08</td>
                            <td>PT. TRINELA OTOOS EXTRINDO PERMATA - 018 ...</td>
                            <td class="text-end">0</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">ERP</td>
                            <td>2/7/2025 16:05</td>
                            <td>PEMBAYARAN INVOICE 001</td>
                            <td class="text-end">0.00</td>
                            <td class="text-end">87,842,070.00</td>
                            <td class="text-end">1,274,900,373.01</td>
                            <td>Match</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </body>
        </html>';

        echo $html;
    }
}
