<?php
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
class Report_bank_statements extends CI_Controller
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
            $this->load->view('finance/report_bank_statements');
        } else {
            redirect('error_access');
        }
    }


    public function printOld($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=bank_statements_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_from_1 = date("Y-m-d", strtotime("-1 day", strtotime($filter_from)));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_account = base64_decode($this->input->get("filter_account"));
        $filter_currency = base64_decode($this->input->get("filter_currency"));

        $where_currency = !empty($filter_currency) ? "AND b.currency = '" . $filter_currency . "'" : "";
        $where_currency_journal = !empty($filter_currency) ? "AND a.currency = '" . $filter_currency . "'" : "";
        
        $time_start = new DateTime($filter_from);
        $time_to = new DateTime($filter_to);

        $account_coa = $this->crud->read("account_coa", [], ["account_number" => $filter_account]);

        $ar_receipt_begin = $this->crud->query("SELECT z.account_number, 
                    (SUM(z.local_debit) - SUM(z.local_credit)) as local_begin, 
                    (SUM(z.debit) - SUM(z.credit)) as original_begin 
            FROM (SELECT a.*, b.currency
            FROM ar_receipt_journals a 
            JOIN ar_receipts b ON a.receipt_no = b.receipt_no 
            WHERE b.receipt_date < '$filter_from' and a.account_number = '$filter_account'
            " . $where_currency . "
            GROUP BY a.receipt_no, a.account_number, a.description) z GROUP BY z.account_number");

        $ap_payment_begin = $this->crud->query("SELECT a.account_number, (SUM(a.local_debit) - SUM(a.local_credit)) as local_begin, (SUM(a.debit) - SUM(a.credit)) as original_begin
            FROM (
            SELECT a.*
            FROM ap_payment_journals a 
            JOIN ap_payments b ON a.payment_no = b.payment_no 
            WHERE b.payment_date between '2023-01-01' and '$filter_from_1' and a.account_number = '$filter_account' 
            " . $where_currency . "
            GROUP BY a.account_number, b.payment_no) a
            GROUP BY a.account_number");

        $journal_posting_begin = $this->crud->query("SELECT a.account_number, (SUM(a.local_debit) - SUM(a.local_credit)) as local_begin 
            FROM journal_postings a 
            JOIN journal_revaluations b ON a.document_no = b.number and b.flag = 2
            WHERE a.invoice_no like '%CASHBANK%' and a.account_number = '$filter_account' and a.trans_date < '$filter_from' 
            " . $where_currency_journal . "
            GROUP BY a.account_number");

        $datas = array();
        $coa_original = (@$account_coa->original_debit - @$account_coa->original_kredit);
        $coa_local = (@$account_coa->local_debit - @$account_coa->local_kredit);
        $ar_begin_local = @$ar_receipt_begin[0]->local_begin;
        $ar_begin_original = @$ar_receipt_begin[0]->original_begin;
        $ap_begin_local = @$ap_payment_begin[0]->local_begin;
        $ap_begin_original = @$ap_payment_begin[0]->original_begin;
        $journal_begin_local = @$journal_posting_begin[0]->local_begin;

        while ($time_start <= $time_to) {
            $trans_date = $time_start->format('Y-m-d');
            $ar_receipts = $this->crud->query("SELECT a.*, b.description as description2, b.currency, c.number as gl_no
                FROM ar_receipt_journals a 
                JOIN ar_receipts b ON a.receipt_no = b.receipt_no 
                LEFT JOIN journal_postings c ON b.receipt_no = c.document_no
                WHERE b.receipt_date = '$trans_date' and a.account_number = '$filter_account' 
                " . $where_currency . "
                GROUP BY a.receipt_no, a.account_number, a.description");

            $ap_payments = $this->crud->query("SELECT a.*, a.description, b.currency, c.number as gl_no
                FROM ap_payment_journals a 
                JOIN ap_payments b ON a.payment_no = b.payment_no 
                LEFT JOIN journal_postings c ON b.payment_no = c.document_no
                WHERE b.payment_date = '$trans_date' and a.account_number = '$filter_account' 
                " . $where_currency . "
                GROUP BY a.payment_no, a.account_number");

            $journal_postings = $this->crud->query("SELECT a.*
                FROM journal_postings a 
                WHERE a.account_number = '$filter_account' and a.trans_date = '$trans_date'
                " . $where_currency_journal .  "
                GROUP BY a.number");
            
            foreach ($ar_receipts as $ar_receipt) {
                $ar_original_balance = ($coa_original + $ar_begin_original + $ap_begin_original + $ar_receipt->debit - $ar_receipt->credit);
                $ar_local_balance = ($coa_local + $ar_begin_local + $ap_begin_local + $journal_begin_local + $ar_receipt->local_debit - $ar_receipt->local_credit);

                $datas[] = array(
                    "trans_date" => $trans_date,
                    "document_no" => $ar_receipt->receipt_no,
                    "gl_no" => $ar_receipt->gl_no,
                    "description" => $ar_receipt->description . " | " . $ar_receipt->description2,
                    "currency" => $ar_receipt->currency,
                    "original_debit" => $ar_receipt->debit,
                    "original_credit" => $ar_receipt->credit,
                    "original_balance" => $ar_original_balance,
                    "rate" => (($ar_receipt->local_debit + $ar_receipt->local_credit) / ($ar_receipt->debit + $ar_receipt->credit)),
                    "local_debit" => $ar_receipt->local_debit,
                    "local_credit" => $ar_receipt->local_credit,
                    "local_balance" => $ar_local_balance,
                );

                $coa_original = 0;
                $coa_local = 0;
                $ap_begin_original = 0;
                $ap_begin_local = 0;
                $journal_begin_local = 0;
                $ar_begin_original = $ar_original_balance;
                $ar_begin_local = $ar_local_balance;
            }

            foreach ($ap_payments as $ap_payment) {
                $ap_original_balance = ($coa_original + $ar_begin_original + $ap_begin_original + $ap_payment->debit - $ap_payment->credit);
                $ap_local_balance = ($coa_local + $ar_begin_local + $ap_begin_local + $journal_begin_local + $ap_payment->local_debit - $ap_payment->local_credit);

                $datas[] = array(
                    "trans_date" => $trans_date,
                    "document_no" => $ap_payment->payment_no,
                    "gl_no" => $ap_payment->gl_no,
                    "description" => $ap_payment->description,
                    "currency" => $ap_payment->currency,
                    "original_debit" => $ap_payment->debit,
                    "original_credit" => $ap_payment->credit,
                    "original_balance" => $ap_original_balance,
                    "rate" => (($ap_payment->local_debit + $ap_payment->local_credit) / ($ap_payment->debit + $ap_payment->credit)),
                    "local_debit" => $ap_payment->local_debit,
                    "local_credit" => $ap_payment->local_credit,
                    "local_balance" => $ap_local_balance,
                );

                $coa_original = 0;
                $coa_local = 0;
                $journal_begin_local = 0;
                $ap_begin_original = $ap_original_balance;
                $ap_begin_local = $ap_local_balance;
                $ar_begin_original = 0;
                $ar_begin_local = 0;
            }

            foreach ($journal_postings as $journal_posting) {
                $ap_original_balance = ($coa_original + $ar_begin_original + $ap_begin_original + $journal_posting->original_debit - $journal_posting->original_credit);
                $ap_local_balance = ($coa_local + $ar_begin_local + $ap_begin_local + $journal_begin_local + $journal_posting->local_debit - $journal_posting->local_credit);

                $datas[] = array(
                    "trans_date" => $journal_posting->trans_date,
                    "document_no" => $journal_posting->document_no,
                    "gl_no" => $journal_posting->number,
                    "description" => $journal_posting->description,
                    "currency" => $journal_posting->currency,
                    "original_debit" => $journal_posting->original_debit,
                    "original_credit" => $journal_posting->original_credit,
                    "original_balance" => $ap_original_balance,
                    "rate" => $journal_posting->rates,
                    "local_debit" => $journal_posting->local_debit,
                    "local_credit" => $journal_posting->local_credit,
                    "local_balance" => $ap_local_balance,
                );

                $coa_original = 0;
                $coa_local = 0;
                $ap_begin_original = $ap_original_balance;
                $ap_begin_local = $ap_local_balance;
                $ar_begin_original = 0;
                $ar_begin_local = 0;
            }

            $ar_begin_original = $ar_begin_original;
            $ar_begin_local = $ar_begin_local;
            $ap_begin_original = $ap_begin_original;
            $ap_begin_local = $ap_begin_local;

            $time_start->modify('+1 day');
        }

        $account = $this->crud->read('account_coa', [], ["account_number" => $filter_account]);

        $opening_balance_original = ((@$account_coa->original_debit - @$account_coa->original_kredit) + (@$ar_receipt_begin[0]->original_begin + @$ap_payment_begin[0]->original_begin));
        // $opening_balance_local = ((@$account_coa->local_debit - @$account_coa->local_kredit) + (@$ar_receipt_begin[0]->local_begin + @$ap_payment_begin[0]->local_begin));
        $opening_balance_local = ((@$account_coa->local_debit - @$account_coa->local_kredit) + (@$ar_receipt_begin[0]->local_begin + @$ap_payment_begin[0]->local_begin) + @$journal_posting_begin[0]->local_begin);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b style="font-size:14px;">' . $config->name . '</b><br>
                                <span style="font-size:10px;">' . $config->address . '</span><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br><br>
            <center>
                <h3 style="margin:0;">CASH & BANK STATEMENT</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            <table style="width: 50%; font-size:12px;">
                <tr>
                    <td width="150">Account No</td>
                    <td width="5">:</td>
                    <td>'.$account->account_number.'</td>
                </tr>
                <tr>
                    <td>Account Name</td>
                    <td>:</td>
                    <td>'.$account->account_name.'</td>
                </tr>
            </table>
            <br>
            
            <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Transaction Date</th>
                <th rowspan="2">Document No</th>
                <th rowspan="2">GL No</th>
                <th rowspan="2">Description</th>
                <th rowspan="2">Currency</th>
                <th colspan="3">Original Currency</th>
                <th colspan="4">Local Currency</th>
            </tr>
            <tr>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
                <th>Rate</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
            </tr>';

            $html .= '  <tr>
                            <td style="text-align:center">#</td>
                            <td>' . $filter_from . '</td>
                            <td></td>
                            <td></td>
                            <td>OPENING BALANCE</td>
                            <td></td>
                            <td style="text-align:right;font-weight:bold;"></td>
                            <td style="text-align:right;font-weight:bold;"></td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($opening_balance_original, 2, ",", ".") . '</td>
                            <td style="text-align:right;font-weight:bold;"></td>
                            <td style="text-align:right;font-weight:bold;"></td>
                            <td style="text-align:right;font-weight:bold;"></td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($opening_balance_local, 2, ",", ".") . '</td>
                        </tr>';

        $no = 1;
        $grand_total_debit_original = 0;
        $grand_total_credit_original = 0;
        $grand_total_debit_local = 0;
        $grand_total_credit_local = 0;
        foreach ($datas as $data) {
            $html .= '  <tr>
                            <td style="text-align:center">'.$no.'</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['document_no'] . '</td>
                            <td>' . $data['gl_no'] . '</td>
                            <td>' . $data['description'] . '</td>
                            <td style="text-align:center;">' . $data['currency'] . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['original_debit'], 2, ",", ".") . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['original_credit'], 2, ",", ".") . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['original_balance'], 2, ",", ".") . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['rate'], 2, ",", ".") . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['local_debit'], 2, ",", ".") . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['local_credit'], 2, ",", ".") . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['local_balance'], 2, ",", ".") . '</td>
                        </tr>';
            $no++;
            $grand_total_debit_original += $data['original_debit'];
            $grand_total_credit_original += $data['original_credit'];
            $grand_total_debit_local += $data['local_debit'];
            $grand_total_credit_local += $data['local_credit'];
        }

        $html .= '  <tr style="background:#EBEBEB;">
                        <td colspan="6"><b>GRAND TOTAL</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_debit_original, 2, ",", ".") . '</b></td>
                        <td style="text-align:right;"><b>' . number_format(@$grand_total_credit_original, 2, ",", ".") . '</b></td>
                        <td style="text-align:right;"><b>-</b></td>
                        <td style="text-align:right;"><b>-</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_debit_local, 2, ",", ".") . '</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_credit_local, 2, ",", ".") . '</b></td>
                        <td style="text-align:right;"><b>-</b></td>
                    </tr>';

        $html .= '</table></body></html>';
        echo $html;
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
            $format = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=bank_statements_$format.xls");
        }

        $filter_from     = base64_decode($this->input->get("filter_from"));
        $filter_to       = base64_decode($this->input->get("filter_to"));
        $filter_account  = base64_decode($this->input->get("filter_account"));
        $filter_currency = base64_decode($this->input->get("filter_currency"));

        // Validasi filter mata uang
        $where_currency_journal = "";
        if (!empty($filter_currency)) {
            // Gunakan escape string atau query builder untuk keamanan
            $where_currency_journal = "AND a.currency = " . $this->db->escape($filter_currency);
        }

        // utk pengambilan begining balance langsung dari opening account bank, utk transaksi pengambil dari transaksi AP/AR tidak membaca status open/closed (Bu Nina)
        $this->db->select('a.account_number, a.account_name, a.starting_date, b.balance, b.balance_local');
        $this->db->from('account_coa a');
        $this->db->join('account_banks b', 'a.account_number = b.account_number');
        $this->db->where('a.account_number', $filter_account);
        $account_coa = $this->db->get()->row();
        
        // Get Saldo Awal Statis dari database
        $initial_balance       = $account_coa->balance ?? 0;
        $initial_balance_local = $account_coa->balance_local ?? 0;

        // Sesuaikan beginning balance per 2026-01-01 berdasarkan dari Account Bank (Bu Nina)
        $static_cutoff = "2026-01-01"; 

        $opening_mutation = 0;
        $opening_mutation_local = 0;

        /**
         * Jika user memfilter mulai dari 2026-01-01, maka mutasi = 0.
         * Jika user memfilter mulai dari 2026-02-01, maka hitung mutasi Jan 2026 saja.
         */
        if ($filter_from > $static_cutoff) {
            $query_opening = "
                SELECT 
                    SUM(original_debit - original_credit) as total_mutation,
                    SUM(local_debit - local_credit) as total_mutation_local
                FROM journal_postings a
                WHERE a.account_number = " . $this->db->escape($filter_account) . "
                AND a.trans_date >= '$static_cutoff' 
                AND a.trans_date < " . $this->db->escape($filter_from) . "
                $where_currency_journal
            ";
            $res = $this->crud->query($query_opening)[0];
            $opening_mutation = $res->total_mutation ?? 0;
            $opening_mutation_local = $res->total_mutation_local ?? 0;
        }

        // Opening Balance yang akan dicetak di laporan
        $opening_balance_original = $initial_balance + $opening_mutation;
        $opening_balance_local    = $initial_balance_local + $opening_mutation_local;

        // Inisialisasi Running Balance untuk looping transaksi di bawahnya
        $current_original_balance = $opening_balance_original;
        $current_local_balance    = $opening_balance_local;

        // Get Data Transaksi dalam Range Filter
        $time_start = new DateTime($filter_from);
        $time_to    = new DateTime($filter_to);
        $datas      = array();

        while ($time_start <= $time_to) {
            $trans_date = $time_start->format('Y-m-d');
            
            $journal_entries = $this->crud->query("
                SELECT a.*, 
                    COALESCE(b.receipt_no, c.payment_no) AS doc_no,
                    COALESCE(b.description, c.note) AS doc_desc
                FROM journal_postings a
                LEFT JOIN ar_receipts b ON a.document_no = b.receipt_no
                LEFT JOIN ap_payments c ON a.document_no = c.payment_no
                WHERE a.account_number = '$filter_account' AND a.trans_date = '$trans_date'
                $where_currency_journal
                GROUP BY a.number
                ORDER BY a.number ASC
            ");

            foreach ($journal_entries as $row) {
                // Update Running Balance
                $current_original_balance += ($row->original_debit - $row->original_credit);
                $current_local_balance    += ($row->local_debit - $row->local_credit);

                $datas[] = [
                    "trans_date"       => $trans_date,
                    "document_no"      => $row->doc_no,
                    "gl_no"            => $row->number,
                    "description"      => $row->description . " | " . $row->doc_desc,
                    "currency"         => $row->currency,
                    "original_debit"   => $row->original_debit,
                    "original_credit"  => $row->original_credit,
                    "original_balance" => $current_original_balance,
                    "rate"             => $row->rates,
                    "local_debit"      => $row->local_debit,
                    "local_credit"     => $row->local_credit,
                    "local_balance"    => $current_local_balance,
                ];
            }
            $time_start->modify('+1 day');
        }
        
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // PRINT
        $html = '<html><head><title>Print Data</title>';
        $html .= $this->customCss();
        $html .= '</head><body>';
        $html .= '<center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b style="font-size:14px;">' . $config->name . '</b><br>
                                <span style="font-size:10px;">' . $config->address . '</span><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br><br>
            <center>
                <h3 style="margin:0;">CASH & BANK STATEMENT</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>
            <table style="width: 50%; font-size:12px;">
                <tr>
                    <td width="150">Account No</td>
                    <td width="5">:</td>
                    <td>'.$account_coa->account_number.'</td>
                </tr>
                <tr>
                    <td>Account Name</td>
                    <td>:</td>
                    <td>'.$account_coa->account_name.'</td>
                </tr>
            </table>
            <br>';

        $html .= '<table id="customers" border="1" style="font-size: 11px;">
                <thead>
                    <tr style="background-color:#eee;">
                        <th rowspan="2" width="20">No</th>
                        <th rowspan="2">Transaction Date</th>
                        <th rowspan="2">Document No</th>
                        <th rowspan="2">GL No</th>
                        <th rowspan="2">Description</th>
                        <th rowspan="2">Currency</th>
                        <th colspan="3">Original Currency</th>
                        <th colspan="4">Local Currency</th>
                    </tr>
                    <tr style="background-color:#eee;">
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                        <th>Rate</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                    </tr>
                </thead>';

        $html .= '<tr>
                    <td style="text-align:center">#</td>
                    <td>' . $filter_from . '</td>
                    <td></td>
                    <td></td>
                    <td> <b>OPENING BALANCE</b> </td>
                    <td></td>
                    <td style="text-align:right;font-weight:bold;"></td>
                    <td style="text-align:right;font-weight:bold;"></td>
                    <td style="text-align:right;font-weight:bold;">' . number_format($opening_balance_original, 2, ",", ".") . '</td>
                    <td style="text-align:right;font-weight:bold;"></td>
                    <td style="text-align:right;font-weight:bold;"></td>
                    <td style="text-align:right;font-weight:bold;"></td>
                    <td style="text-align:right;font-weight:bold;">' . number_format($opening_balance_local, 2, ",", ".") . '</td>
                </tr>';

        $no = 1;
        $grand_total_debit_original = 0;
        $grand_total_credit_original = 0;
        $grand_total_debit_local = 0;
        $grand_total_credit_local = 0;
        foreach ($datas as $data) {
            $html .= '  <tr>
                            <td style="text-align:center">'.$no.'</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['document_no'] . '</td>
                            <td>' . $data['gl_no'] . '</td>
                            <td>' . $data['description'] . '</td>
                            <td style="text-align:center;">' . $data['currency'] . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['original_debit'], 2, ",", ".") . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['original_credit'], 2, ",", ".") . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['original_balance'], 2, ",", ".") . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['rate'], 2, ",", ".") . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['local_debit'], 2, ",", ".") . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['local_credit'], 2, ",", ".") . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($data['local_balance'], 2, ",", ".") . '</td>
                        </tr>';
            $no++;
            $grand_total_debit_original += $data['original_debit'];
            $grand_total_credit_original += $data['original_credit'];
            $grand_total_debit_local += $data['local_debit'];
            $grand_total_credit_local += $data['local_credit'];
        }

        $html .= '  <tr style="background:#EBEBEB;">
                        <td colspan="6"><b>GRAND TOTAL</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_debit_original, 2, ",", ".") . '</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_credit_original, 2, ",", ".") . '</b></td>
                        <td style="text-align:right;"><b>-</b></td>
                        <td style="text-align:right;"><b>-</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_debit_local, 2, ",", ".") . '</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_total_credit_local, 2, ",", ".") . '</b></td>
                        <td style="text-align:right;"><b>-</b></td>
                    </tr>';

        // Ending Balance Total = {Opening Balance Awal Laporan} + {Total Mutasi Selama Periode Laporan}
        $grand_ending_balance_original = $opening_balance_original + $grand_total_debit_original - $grand_total_credit_original;
        $grand_ending_balance_local = $opening_balance_local + $grand_total_debit_local - $grand_total_credit_local;

        $html .= '  <tr style="background:#EBEBEB;">
                        <td colspan="6"><b>ENDING BALANCE</b></td>
                        <td style="text-align:right;"><b>-</b></td>
                        <td style="text-align:right;"><b>-</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_ending_balance_original, 2, ",", ".") . '</b></td>
                        <td style="text-align:right;"><b>-</b></td>
                        <td style="text-align:right;"><b>-</b></td>
                        <td style="text-align:right;"><b>-</b></td>
                        <td style="text-align:right;"><b>' . number_format($grand_ending_balance_local, 2, ",", ".") . '</b></td>
                    </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }
}
