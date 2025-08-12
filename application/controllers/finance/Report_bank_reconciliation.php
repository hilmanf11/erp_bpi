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

        $filter_account_number = $this->input->post("filter_account_number");
        $filter_from = $this->input->post("filter_from");
        $filter_to = $this->input->post("filter_to");

        // CHECK Date Period same as excel
        if (strtotime($filter_from) !== strtotime($dataBank['start_date']) && strtotime($filter_to) !== strtotime($dataBank['end_date']) ) {
            echo json_encode(["title" => "Not Matched", "message" => "Failed! Period in Excel Is Not Match with the selected Date", "theme" => "error"]);
            return;
        }

        // CHECK Bank Account Number same as excel
        $account_banks = $this->crud->read('account_banks', [], ["account_number" => $filter_account_number]);
        if (!$account_banks || $account_banks->bank_account !== $dataBank['bank_account']) {
            echo json_encode(["title" => "Not Matched", "message" => "Failed! Bank Account No in Excel " . $bank['bank_account'] ." Is Not Match with the selected Account", "theme" => "error"]);
            return;
        }

        $datas = [];
        for ($i = 10; $i <= $total_row; $i++) {
            $datas[] = [
                'posting_date' => date("Y-m-d H:i:s", strtotime($data->val($i, 2))),
                'remark'       => htmlspecialchars($data->val($i, 3)),
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
    
                    $send   = $this->crud->create('bank_reconciliation', $dataFinal);
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
        $end_date = date("Y-m-d", strtotime($filter_to));

        $dataJournal = [];

        $this->db->select('*');
        $this->db->from('journal_postings');
        $this->db->where("trans_date between '$filter_from' and '$filter_to'");
        $this->db->where("account_number", $filter_account_number);
        $this->db->where("trans_date between '$start_date' and '$end_date'");
        $this->db->group_start();
        $this->db->where('modul', 'AP PAYMENT');
        $this->db->or_where('modul', 'AR RECEIPT');
        $this->db->or_where('modul', 'CURRENCY REVALUATION');
        $this->db->group_end();
        $this->db->order_by('trans_date', 'asc');
        $this->db->order_by('document_no', 'asc');
        $this->db->order_by('account_number', 'asc');
        $journals = $this->db->get()->result_array();

        $this->db->select('account_number, account_name, 
            COALESCE(SUM(original_debit)) as original_debit,
            COALESCE(SUM(original_credit)) as original_credit,
            COALESCE(SUM(local_debit)) as local_debit,
            COALESCE(SUM(local_credit)) as local_credit');
        $this->db->from('journal_postings');
        $this->db->where("journal_date between '$filter_before' and '$filter_before_to'");
        $this->db->where("account_number", $filter_account_number);
        $this->db->group_start();
        $this->db->where('modul', 'AP PAYMENT');
        $this->db->or_where('modul', 'AR RECEIPT');
        $this->db->or_where('modul', 'CURRENCY REVALUATION');
        $this->db->group_end();
        $journal_bf = $this->db->get()->row();

        $this->db->select('coa.*, b.bank_account, b.currency');
        $this->db->from('account_coa coa');
        $this->db->join('account_banks b', 'b.account_number = coa.account_number', 'left');
        $this->db->where("coa.account_number", $filter_account_number);
        $account_coa = $this->db->get()->row();

        $journal_ori_debit = @$journal_bf->original_debit;
        $journal_ori_credit = @$journal_bf->original_credit;
        
        $begin_balance_ori = (@$account_coa->original_debit + $account_coa->original_kredit);

        $journal_end_ori_debit = 0;
        $journal_end_ori_credit = 0;
        
        if((($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit) > 0)
        {
            $journal_end_ori_debit = abs(($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit);
            $journal_end_ori_credit = 0;
        }
        else
        {
            $journal_end_ori_debit = 0;
            $journal_end_ori_credit = abs(($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit);
        }

        $ori_balance = ($journal_end_ori_debit + $journal_end_ori_credit); // OPENING BALANCE

        foreach ($journals as $journal) 
        {
            $dataJournal = [
                "source"         => "ERP",
                "account_number" => $account_coa->account_number,
                "bank_account"   => $account_coa->bank_account,
                "start_date"     => $start_date,
                "end_date"       => $end_date,
                "currency"       => $account_coa->currency,
                // Data Journal
                "posting_date"   => $journal['trans_date'],
                "remark"         => $journal['description'],
                "currency"       => $journal['currency'],
                "debit"          => $journal['original_debit'],
                "credit"         => $journal['original_credit'],
                "balance"        => $ori_balance,
            ];

            // Get Bank Reconciliation data
            $this->db->select('id, posting_date, remark, debit, credit, result, account_number, currency, start_date, end_date'); // Select all necessary fields
            $this->db->from('bank_reconciliation');
            $this->db->where('source', $dataJournal["source"]);
            $this->db->where('account_number', $dataJournal["account_number"]);
            $this->db->where('currency', $dataJournal["currency"]);
            $this->db->where('start_date', $dataJournal["start_date"]);
            $this->db->where('end_date', $dataJournal["end_date"]);
            $this->db->where('credit', $dataJournal["credit"]);
            $this->db->where('debit', $dataJournal["debit"]);
            $check_bank_reconciliation = $this->db->get()->row();

            if (!$check_bank_reconciliation) {
                $send = $this->crud->create('bank_reconciliation', $dataJournal);            
                echo json_encode($send);
            } else {
                echo json_encode(array("title" => "Duplicated", "message" => "This data is already reconciled before!", "theme" => "error"));
            }
        }
    }

    // RECONCILE
    function reconcile()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->post("filter_from");
            $filter_to = $this->input->post("filter_to");
            $filter_account_number = $this->input->post("filter_account_number");

            // Input validation
            if (empty($filter_from) || empty($filter_to) || empty($filter_account_number)) {
                echo json_encode(['success' => false, 'message' => 'Start Date, End Date, and Bank Account No are required.', 'title' => 'Error', 'theme' => 'error']);
                return;
            }

            $start_date = date("Y-m-d", strtotime($filter_from));
            $end_date = date("Y-m-d", strtotime($filter_to));

            // Get Bank Account data
            $this->db->select('*');
            $this->db->from('account_banks');
            $this->db->where('account_number', $filter_account_number);
            $dataBank = $this->db->get()->row();

            if (!$dataBank) {
                echo json_encode(['success' => false, 'message' => 'Bank account not found.', 'title' => 'Error', 'theme' => 'error']);
                return;
            }            

            // Get Bank Reconciliation data
            $this->db->select('id, posting_date, remark, debit, credit, result, account_number, currency, start_date, end_date'); // Select all necessary fields
            $this->db->from('bank_reconciliation');
            $this->db->where('source', "Bank");
            $this->db->where('account_number', $filter_account_number);
            $this->db->where('currency', $dataBank->currency);
            $this->db->where('start_date', $start_date);
            $this->db->where('end_date', $end_date);
            $bank_reconciliation = $this->db->get()->result_array();

            if (!$bank_reconciliation) {
                echo json_encode(['success' => false, 'message' => 'Bank Mutation not found. Please upload first.', 'title' => 'Error', 'theme' => 'error']);
                return;
            }

            // Generate dulu Journal insert to Bank Reconciliation 
            $journal_postings = $this->getJournal($filter_from, $filter_to, $filter_account_number); // belum ada validasi journal sama

            return json_encode($journal_postings);

        } else {
            $this->output->set_status_header(405); // Method Not Allowed
            echo json_encode(['success' => false, 'message' => 'Method not allowed.', 'title' => 'Error', 'theme' => 'error']);
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

        $start_date = date("Y-m-d", strtotime($filter_from));
        $end_date   = date("Y-m-d", strtotime($filter_to));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Bank Account
        $this->db->select('*');
        $this->db->from('account_banks');
        $this->db->where('account_number', $filter_account_number);
        $dataBank = $this->db->get()->row();

        // Get Bank Reconciliation data
        $this->db->select('id, posting_date, remark, debit, credit, result, account_number, currency, start_date, end_date'); // Select all necessary fields
        $this->db->from('bank_reconciliation');
        $this->db->where('source', "Bank");
        $this->db->where('account_number', $filter_account_number);
        $this->db->where('currency', $dataBank->currency);
        $this->db->where('start_date', $start_date);
        $this->db->where('end_date', $end_date);
        $bank_reconciliation = $this->db->get()->result_array();

        if (!$bank_reconciliation) {
            echo "<h2>Bank Mutation not found. Please upload first. <h2>";
            // echo json_encode(['success' => false, 'message' => 'Bank Mutation not found. Please upload first.', 'title' => 'Error', 'theme' => 'error']);
            return;
        }

        // Get Journal Postings data with OR grouping
        $this->db->select('id, trans_date, description, original_debit, original_credit, modul, number');
        $this->db->from('journal_postings');
        // $this->db->join('journal_revaluations b', 'b.number = journal_postings.document_no and b.document_no = journal_postings.invoice_no', 'left');
        $this->db->where('account_number', $dataBank->account_number);
        $this->db->where('trans_date >=', $start_date);
        $this->db->where('trans_date <=', $end_date);
        $this->db->group_start();
        $this->db->where('modul', 'AP PAYMENT');
        $this->db->or_where('modul', 'AR RECEIPT');
        $this->db->or_where('modul', 'CURRENCY REVALUATION');
        $this->db->group_end();
        $journal_postings = $this->db->get()->result_array();

        // --- Core Reconciliation Logic ---
        $matched_transactions = [];
        $unmatched_bank = [];
        $unmatched_journal = [];

        // Temporary arrays to track matched IDs
        $matched_journal_ids = [];
        $matched_bank_ids = [];

        // Loop through bank entries and try to match them with journal entries
        foreach ($bank_reconciliation as $b_key => $bank_entry) {
            $is_matched = false;
            foreach ($journal_postings as $j_key => $journal_entry) {
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
                        'bank_data' => $bank_entry
                    ];

                    $matched_journal_ids[] = $journal_entry['id'];
                    $matched_bank_ids[] = $bank_entry['id'];

                    $is_matched = true;
                    break;
                }
            }

            if (!$is_matched) {
                $bank_entry['result'] = "Not Matched";
                $unmatched_bank[] = $bank_entry;
            }
        }
        
        // Find all unmatched journal entries
        foreach ($journal_postings as $journal_entry) {
            if (!in_array($journal_entry['id'], $matched_journal_ids)) {
                $journal_entry['result'] = "Not Matched";
                $unmatched_journal[] = $journal_entry;
            }
        }

        // Calculate opening and closing balances (this logic needs to be implemented separately)
        // Opening Balance Bank			: berdasarkan modul master bank per 01 Januari 2025	
        $opening_balance = 0; 
        $closing_balance = 0; 

        // Prepare the final result to be sent as JSON
        $result = [
            'success' => true,
            'message' => 'Reconciliation data fetched successfully.',
            'opening_balance' => $opening_balance,
            'closing_balance' => $closing_balance,
            'matched_transactions' => $matched_transactions,
            'unmatched_bank_transactions' => $unmatched_bank,
            'unmatched_journal_postings' => $unmatched_journal
        ];
        
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
                                        <td><b>' . htmlspecialchars($filter_account_number) . '</b></td>
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
                                            <td> - </td>
                                            <td>' . number_format($dataBank->balance, 2, ",", ".") . '</td>
                                            <td>0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Debit</td>
                                            <td> - </td>
                                            <td> - </td>
                                            <td>0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Credit</td>
                                            <td> - </td>
                                            <td> - </td>
                                            <td class="text-danger"> 0.00 </td>
                                        </tr>
                                        <tr>
                                            <td>Ending Balance</td>
                                            <td> - </td>
                                            <td> - </td>
                                            <td class="text-danger"> 0.00 </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>

                <br>                
                <table id="customers">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Source</th>
                            <th>Posting Date (Transaction Date)</th>
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
                    if (!empty($result['matched_transactions'])) 
                    {
                        foreach ($result['matched_transactions'] as $matched) {
                            $html .= '<tr>
                                    <td rowspan="2">' . $no . '</td>
                                    <td> Bank </td>
                                    <td>' . $matched['bank_data']['posting_date'] . '</td>
                                    <td>' . $matched['bank_data']['remark'] . '</td>
                                    <td>' . $matched['bank_data']['debit'] . '</td>
                                    <td>' . $matched['bank_data']['credit'] . '</td>
                                    <td> - </td>
                                    <td rowspan="2">' . $matched['bank_data']['result'] . '</td>
                                    <td rowspan="2"> - </td>
                                </tr>';                        
                            $html .= '<tr>
                                    <td> ERP </td>
                                    <td>' . $matched['journal_data']['trans_date'] . '</td>
                                    <td>' . $matched['journal_data']['description'] . '</td>
                                    <td>' . $matched['journal_data']['original_debit'] . '</td>
                                    <td>' . $matched['journal_data']['original_credit'] . '</td>
                                    <td> - </td>
                                </tr>';                        
                            $no++;
                        }
                        
                    }
                    
                    // NOT MATCHED BANK 
                    if (!empty($result['unmatched_bank_transactions'])) 
                    {
                        foreach ($result['unmatched_bank_transactions'] as $rowBank) {
                            // Bank
                            $html .= '<tr>
                                    <td rowspan="2">' . $no . '</td>
                                    <td> Bank </td>
                                    <td>' . $rowBank['posting_date'] . '</td>
                                    <td>' . $rowBank['remark'] . '</td>
                                    <td>' . $rowBank['debit'] . '</td>
                                    <td>' . $rowBank['credit'] . '</td>
                                    <td> - </td>
                                    <td rowspan="2" style="color:red;">' . $rowBank['result'] . '</td>
                                    <td rowspan="2">  </td>
                                </tr>';  
                            // NULL ERP
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
                    if (!empty($result['unmatched_journal_postings'])) 
                    {
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
                                    <td rowspan="2" style="color:red;"> ' . $rowJournal['result'] . ' </td>
                                    <td rowspan="2"> </td>
                                </tr>'; 
                            // ERP
                            $html .='<tr>
                                <td> ERP </td>
                                <td>' . $rowJournal['trans_date'] . '</td>
                                <td>' . $rowJournal['description'] . '</td>
                                <td>' . $rowJournal['original_debit'] . '</td>
                                <td>' . $rowJournal['original_credit'] . '</td>
                                <td> - </td>
                            </tr>';
                            $no++;
                        }
                    }

        $html .= '</tbody>
                </table>
            </body>
        </html>';

        echo $html;
    }

    function diff($erp, $bank) {
        if ($erp - $bank > 0) {
            return 'class="text-danger"';
        } else {
            return "";
        }
    }
}
