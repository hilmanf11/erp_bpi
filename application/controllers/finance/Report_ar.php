<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_ar extends CI_Controller
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
        // $this->form_validation->set_rules('receipt_no', 'Receipt No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/report_ar');
        } else {
            redirect('error_access');
        }
    }

    public function readAddress($customer_id)
    {
        $customer_id = base64_decode($customer_id);
        $send = $this->crud->query("SELECT * FROM customer_address WHERE customer_id = '$customer_id' ");
        echo json_encode($send);
    }

    public function readSi($customer_id){
        $customer_id = base64_decode($customer_id);
        $query = $this->crud->query("SELECT DISTINCT `number` FROM sales_invoices WHERE customer_id = '$customer_id' ORDER BY `number` ASC");
        die(json_encode($query));
    }

    function readExchangeRate($currency, $receipt_date)
    {
        $search_date = date("d", strtotime($receipt_date));
        if($search_date == "31"){
          $receipt_date = date("Y-m-d", strtotime('-10 days', strtotime($receipt_date)));
        }
        
        $monthBf = date('Y-m-01', strtotime($receipt_date));
        $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

        if ($exchange) {
            $amount = $exchange->middle;
        } else {
            $amount = 1;
        }

        return $amount;
    }

    // BEGIN BALANCE CUSTOMER
    public function begin_balance($customer_id, $filter_list, $initial_balance)
    {
        $filter_from          = $filter_list["filter_from"];
        $filter_to            = $filter_list["filter_to"];
        $filter_customer      = $filter_list["filter_customer"];
        $filter_plant         = $filter_list["filter_plant"];
        $filter_sales_invoice = $filter_list["filter_sales_invoice"];
        $filter_currency      = $filter_list["filter_currency"];
        $filter_status        = $filter_list["filter_status"];
        $filter_display       = $filter_list["filter_display"];

        // Get data Customer
        $customer = $this->crud->read("customers", ["id" => $customer_id]);
        $customer_name = $customer->name;
        if (empty($customer)) {
            return 0; // Kembalikan 0 jika pelanggan tidak ditemukan
        }
        
        // Get account_number yang termasuk ke REPORT AR
        $get_account_numbers = $this->db->select('account_number')->from('account_coa')->where('report_ar', 1)->get()->result_array();
        $account_numbers = array_column($get_account_numbers, 'account_number'); 
        if (empty($account_numbers)) {
            // Jika tidak ada akun AR, saldo awal adalah saldo customer
            return (float)$initial_balance;
        }
        
        // Inisialisasi variabel untuk Query Builder
        $this->db->reset_query();

        // Query 1: Sales Invoices (SI) - Mengambil Debit (Piutang)
        $this->db->select('SUM(b.local_debit) AS debit_si, SUM(b.local_credit) AS credit_si');
        $this->db->from('sales_invoices a');
        $this->db->join('journal_postings b', 'a.number = b.document_no');
        $this->db->join('customer_address ca', 'a.customer_id = ca.customer_id', 'left');
        if (!empty($account_numbers)) {
            $this->db->where_in('b.account_number', $account_numbers);
        }
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('b.journal_date <', $filter_from);
        $this->db->where('b.modul', 'SALES INVOICING'); // Hanya modul Sales Invoicing
        if (!empty($filter_currency)) {
            $this->db->where('a.currency', $filter_currency);
        }
        $query_1 = $this->db->get()->row();
        $this->db->reset_query();

        // Query 2: AR Receipts (AR) - Mengambil Kredit (Pembayaran Masuk)
        $this->db->select('SUM(b.local_debit) AS debit_ar, SUM(b.local_credit) AS credit_ar');
        $this->db->from('ar_receipts a');
        $this->db->join('journal_postings b', 'a.receipt_no = b.document_no');
        $this->db->join('customer_address ca', 'a.customer_id = ca.customer_id', 'left');
        if (!empty($account_numbers)) {
            $this->db->where_in('b.account_number', $account_numbers);
        }
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('b.journal_date <', $filter_from);
        $this->db->where('b.modul', 'AR RECEIPT'); // Hanya modul AR Receipt
        if (!empty($filter_currency)) {
            $this->db->where('a.currency', $filter_currency);
        }
        $query_2 = $this->db->get()->row();
        $this->db->reset_query();
        
        // === Perhitungan Saldo Awal (Local Currency) ===
        $local_debit_si  = (float)($query_1->debit_si ?? 0);
        $local_credit_si = (float)($query_1->credit_si ?? 0);
        $local_debit_ar  = (float)($query_2->debit_ar ?? 0);
        $local_credit_ar = (float)($query_2->credit_ar ?? 0);
        
        // Total Debit (Piutang)
        $total_debit_old = $local_debit_si;
        
        // Total Kredit (Pembayaran Masuk)
        $total_credit_old = $local_credit_si + $local_credit_ar;

        // Saldo awal adalah Saldo Awal Sistem + (Total Debit Lama - Total Kredit Lama)
        $begin_balance = $initial_balance + $total_debit_old - $total_credit_old;
        
        return $begin_balance;
    }

    public function formatNo($amount, $option = "") 
    {
        if ($amount >= 0) {
            return $this->formatNominal(@$amount, 2, $option);
        } else {
            return "<span style='color:red;'>(" . $this->formatNominal(abs($amount), 2, $option) . ")</span>";
        }
    }
    
    // format separator and decimal point by option excel or print
    private function formatNominal($value, $decimal, $option = "") 
    {
        if (!is_numeric($value)) {
            return $value;
        }
        
        if (!empty($option) && $option == "excel") {
            return number_format($value, $decimal, ".", ""); // tanpa separator
        } else {
            return number_format($value, $decimal, ",", ".");
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_ar_$format.xls");
        }
        
        $filter_from          = base64_decode($this->input->get('filter_from') ?? '');
        $filter_to            = base64_decode($this->input->get('filter_to') ?? '');
        $filter_sales_invoice = base64_decode($this->input->get('filter_sales_invoice')) ?? null;
        $filter_customer      = $this->input->get('filter_customer') ?? null;
        $filter_plant         = $this->input->get('filter_plant') ?? null;
        $filter_currency      = $this->input->get('filter_currency') ?? null;
        $filter_status        = $this->input->get('filter_status') ?? null;
        $filter_display       = $this->input->get('filter_display') ?? null;

        $currency_show = !empty($filter_currency) ? $filter_currency : 'IDR'; // default showed currency = IDR

        $filter_list = [
            'filter_from'          => $filter_from,
            'filter_to'            => $filter_to,
            'filter_customer'      => $filter_customer,
            'filter_plant'         => $filter_plant,
            'filter_sales_invoice' => $filter_sales_invoice,
            'filter_currency'      => $filter_currency,
            'filter_status'        => $filter_status,
            'filter_display'       => $filter_display,
        ];

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Prepare HTML Report
        $html = '<html><head><title>Print Data</title></head>
            <style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                        margin: 20px;
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
                        background-color: #ffffff;
                        text-align: center;
                        color: black; 
                        font-weight: bold;
                    }
                    #customers tr:nth-child(even) {
                        background-color: #ffffff;
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
                            background-color: #99b2e4 !important;
                            -webkit-print-color-adjust: exact;
                        }
                        
                        .begin_balance {
                            background-color: #DEE2FF !important;
                            -webkit-print-color-adjust: exact;
                        }
                        
                        .grand_total {
                            background-color: #C3FFB4 !important;
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
                                <b style="font-size:14px;">' . $config->name . '</b><br>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-size: 14px; text-align: left; margin:2px;"> 
                                <span style="font-size:10px;">' . $config->address . '</span><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>';
            if ($option != "excel") {
                $html .= '<br><br><br><br>'; // separator print 
            }
            $html .= '<center>
                <h3 style="margin:0;">ACCOUNT RECEIVABLE REPORT <i>('.$filter_display.')</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>';

        $summary = '<table id="customers" border="1">
                        <thead>
                            <tr style="background-color: #E5E5E5;">
                                <th width="20">No</th>
                                <th>Customer Name</th>
                                <th>Currency</th>
                                <th>LOCAL CURRENCY<br><i>Balance</i></th>
                            </tr>
                        </thead>';

        $invoice = '<table id="customers" border="1">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2" width="50">Status</th>
                            <th rowspan="2">Customer ID</th>
                            <th rowspan="2">Customer Name</th>
                            <th rowspan="2">Plant</th>
                            <th rowspan="2">Source</th>
                            <th rowspan="2">Invoice Date</th>
                            <th rowspan="2">Payment Due</th>
                            <th rowspan="2">Document No</th>
                            <th rowspan="2">Invoice No</th>
                            <th rowspan="2">Posting Date</th>
                            <th rowspan="2">Posting No</th>
                            <th rowspan="2">Account No</th>
                            <th rowspan="2">Currency</th>
                            <th colspan="4">LOCAL CURRENCY</th>
                        </tr>
                        <tr>
                            <th>Invoice</th>
                            <th>Payment</th>
                            <th>Remain</th>
                            <th>Accumulated</th>
                        </tr>';
    
        $detail = '<table id="customers" border="1">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2" width="50">Status</th>
                            <th rowspan="2">Customer ID</th>
                            <th rowspan="2">Customer Name</th>
                            <th rowspan="2">Source</th>
                            <th rowspan="2">Invoice Date</th>
                            <th rowspan="2">Payment Due</th>
                            <th rowspan="2">Document No</th>
                            <th rowspan="2">Invoice No</th>
                            <th rowspan="2">Posting Date</th>
                            <th rowspan="2">Posting No</th>
                            <th rowspan="2">Account No</th>
                            <th rowspan="2">Currency</th>
                            <th colspan="4">LOCAL CURRENCY</th>
                        </tr>
                        <tr>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Balance</th>
                            <th>Accumulated</th>
                        </tr>';

        // Get Customers
        $this->db->select('a.*, c.plant, (COALESCE(b.balance_local, 0)) as balance_local, (COALESCE(b.balance, 0)) as balance');
        $this->db->select('a.currency as currency_customer');
        $this->db->select('b.currency as currency_balance');
        $this->db->from('customers a');
        $this->db->join('customer_address c', 'a.id = c.customer_id', 'left');
        $this->db->join('account_balance_customers b', 'a.id = b.customer_id', 'left');
        if (!empty($filter_customer)) {
            $this->db->where("a.id", $filter_customer);
        }
        if (!empty($filter_plant)) {
            $this->db->where("c.plant", $filter_plant);
        }
        // $this->db->group_by('c.plant'); // tidak tampil per plant
        $this->db->group_by('c.customer_id');
        $this->db->order_by('a.name', 'asc');
        $customers = $this->db->get()->result_array();

        $no = 1;
        $noid = 1;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        $grand_local_balance = 0;
        $grand_summary_total = 0;

        function compare_trans_date($a, $b) {
            return strtotime($a['voucher_date']) - strtotime($b['voucher_date']);
        }

        // Get account_number yang termasuk ke REPORT AR
        $get_account_numbers = $this->db->select('account_number')->from('account_coa')->where('report_ar', 1)->get()->result_array();
        $account_numbers_array = array_column($get_account_numbers, 'account_number'); // konversi tampil hanya value

        if (empty($account_numbers_array)) {
            $account_numbers_array = ['NON_EXISTING_ACCOUNT_AR']; 
        }

        foreach ($customers as $customer) 
        {
            $customer_id = $customer['id'];
            $customer_name = $customer['name'];
            $plant = $customer['plant'];
            $company_name = $customer_name;
            $company_name2 = $customer_name . " | " . $plant;

            // Siapkan string untuk WHERE IN di Subquery
            $account_numbers_list = "'" . implode("','", $account_numbers_array) . "'"; 

            $this->db->select("
                'SI' AS source,
                a.number AS invoice_no,
                a.trans_date AS invoice_date,
                a.due_date AS payment_due,
                a.delivery_note_no AS document_no,
                b.description,
                b.journal_date AS voucher_date,
                b.number AS voucher_no,
                b.account_number,
                a.currency,
                (CASE WHEN '{$currency_show}' = 'IDR' THEN b.local_debit ELSE b.original_debit END) as local_debit,
                (CASE WHEN '{$currency_show}' = 'IDR' THEN b.local_credit ELSE b.original_credit END) as local_credit,
                b.original_debit,
                b.original_credit,
                b.local_debit AS si_debit_local,
                b.original_debit AS si_debit_original,
                /** -- Mengambil Total Pembayaran (AR) yang sudah diagregasi dari SubQuery AR **/
                COALESCE(ar_summary.total_ar_credit_local, 0) AS ar_credit_local,
                COALESCE(ar_summary.total_ar_credit_original, 0) AS ar_credit_original,
                /** -- LOGIKA STATUS (CLOSED=1, OPEN=0) **/
                (CASE 
                    WHEN '{$currency_show}' = 'IDR' THEN 
                        CASE 
                            WHEN ROUND(b.local_debit, 2) = ROUND(COALESCE(ar_summary.total_ar_credit_local, 0), 2) THEN 1 
                            ELSE 0 
                        END
                    ELSE 
                        CASE 
                            WHEN ROUND(b.original_debit, 2) = ROUND(COALESCE(ar_summary.total_ar_credit_original, 0), 2) THEN 1 
                            ELSE 0 
                        END
                END) AS status_closed_flag,
                (CASE WHEN a.number IS NULL THEN a.status ELSE 1 END) as status
            ", FALSE);
            $this->db->from('sales_invoices a');
            $this->db->join(
                "(SELECT journal_date, number, currency, document_no, account_number, description, 
                    SUM(original_debit) AS original_debit, SUM(original_credit) AS original_credit,
                    SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE modul IN ('SALES INVOICING')
                AND account_number IN ({$account_numbers_list})
                GROUP BY number, document_no, account_number) b", 
                'a.number = b.document_no', 
                'JOIN', 
                FALSE 
            );
            $this->db->join(
                /* Subquery yang menghitung total pembayaran AR per Invoice */
                "(SELECT ar.sales_invoice, 
                (CASE WHEN ar.account_type = 'DEBIT' THEN b.local_debit ELSE 0 END) as total_ar_debit_local,
                (CASE WHEN ar.account_type = 'CREDIT' THEN b.local_credit ELSE 0 END) as total_ar_credit_local,
                (CASE WHEN ar.account_type = 'DEBIT' THEN b.original_debit ELSE 0 END) as total_ar_debit_original,
                (CASE WHEN ar.account_type = 'CREDIT' THEN b.original_credit ELSE 0 END) as total_ar_credit_original
                FROM ar_receipts ar 
                JOIN journal_postings b ON ar.receipt_no = b.document_no
                WHERE b.modul = 'AR RECEIPT'
                GROUP BY ar.sales_invoice, ar.account_number ORDER BY receipt DESC
                ) ar_summary",
                'ar_summary.sales_invoice = a.number', // Relation ke nomor Invoice
                'LEFT', 
                FALSE 
            );
            $this->db->join('customer_address ca', 'a.customer_id = ca.customer_id', 'LEFT');
            $this->db->where('a.customer_id', $customer_id);
            $this->db->where("b.journal_date BETWEEN '{$filter_from}' AND '{$filter_to}'");
            if (!empty($filter_sales_invoice)) {
                $this->db->or_like('a.number', $filter_sales_invoice);
            }
            if (!empty($filter_currency)) {
                $this->db->where("a.currency", $filter_currency);
            }
            if (!empty($filter_status)) {
                $target_status = (int)$filter_status;
                $this->db->having("status_closed_flag", $target_status);
            }
            $this->db->group_by(['a.number', 'b.number']); 
            $this->db->order_by('b.journal_date', 'ASC');
            $data_1 = $this->db->get()->result_array();

            $this->db->select("
                'AR' AS source,
                a.receipt_no AS invoice_no,
                a.receipt_date AS invoice_date,
                '-' AS payment_due,
                a.sales_invoice AS document_no,
                b.description,
                b.journal_date AS voucher_date,
                b.number AS voucher_no,
                b.account_number,
                a.currency,
                a.status as status_closed_flag,
                (CASE WHEN ('{$currency_show}' = 'IDR' AND a.account_type = 'DEBIT') THEN b.local_debit ELSE b.original_debit END) as local_debit,
                (CASE WHEN ('{$currency_show}' = 'IDR' AND a.account_type = 'CREDIT') THEN b.local_credit ELSE b.original_credit END) as local_credit,
                (CASE WHEN a.account_type = 'DEBIT' THEN b.original_debit ELSE 0 END) as original_debit,
                (CASE WHEN a.account_type = 'CREDIT' THEN b.original_credit ELSE 0 END) as original_credit,
                (CASE WHEN c.status IS NULL THEN a.status ELSE c.status END) as status
            ", FALSE);
            $this->db->from('ar_receipts a');
            $this->db->join(
                "(SELECT journal_date, number, document_no, account_number, description, 
                    SUM(original_debit) AS original_debit, SUM(original_credit) AS original_credit,
                    SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE modul IN ('AR RECEIPT') 
                AND account_number IN ({$account_numbers_list})
                GROUP BY number, document_no, account_number) b", 
                'a.receipt_no = b.document_no', 
                'LEFT', 
                FALSE
            );
            $this->db->join('sales_invoices c', 'a.sales_invoice = c.number', 'LEFT');
            $this->db->join('customer_address ca', 'a.customer_id = ca.customer_id', 'LEFT');
            $this->db->where('a.customer_id', $customer_id);
            // Mengkonversi logika $where_inv ke Query Builder
            if ($filter_display == "Detail") {
                // Memastikan subquery di-execute secara literal dengan parameter ketiga = FALSE
                $this->db->where("a.sales_invoice NOT IN (SELECT DISTINCT number FROM sales_invoices)", NULL, FALSE);
            }
            $this->db->where("b.journal_date BETWEEN '{$filter_from}' AND '{$filter_to}'");
            if (!empty($filter_sales_invoice)) {
                $this->db->or_where('a.sales_invoice', $filter_sales_invoice);
            }
            if (!empty($filter_currency)) {
                $this->db->where("a.currency", $filter_currency);
            }
            if (!empty($filter_status)) {
                $target_status = (int)$filter_status;
                $this->db->having("status_closed_flag", $target_status);
            }
            $this->db->group_by(['a.receipt_no', 'a.account_number']);
            $this->db->order_by('b.journal_date', 'ASC');
            $data_2 = $this->db->get()->result_array();

            $sales_invoices = array_merge($data_1, $data_2);
            usort($sales_invoices, 'compare_trans_date');


            // ------- TRANSACTION START ----------
            $local_debit  = 0;
            $local_credit = 0;
            $init_balance = 0; // default balance

            // Get Balance sesuai filter_currency
            $get_balance = $this->db->select('*')->from('account_balance_customers')->where('customer_id', $customer_id)->get()->row();
            if (!empty($get_balance)) {
                if ($currency_show == 'IDR') {
                    // Hanya ambil saldo IDR jika data saldo customer memang ber-currency IDR
                    if ($get_balance->currency == 'IDR' || $get_balance->currency_local == 'IDR') {
                         $init_balance = $customer['balance_local'] ?? 0;
                    }
                } elseif ($currency_show != 'IDR') {
                    if ($get_balance->currency == $currency_show) {
                        $init_balance = $customer['balance'] ?? 0;
                    }
                }              
            }
            
            $begin_balance_local = $this->begin_balance($customer_id, $filter_list, $init_balance);

            if (count($sales_invoices) > 0 || $begin_balance_local != 0) {
                $detail .= '<tr style="background: #DEE2FF; font-weight:bold;" class="begin_balance">
                                <td colspan="12">BEGINING BALANCE (' . $customer_name . ')</td>
                                <td style="text-align:center;">' . $customer['currency_balance'] . '</td>
                                <td colspan="4" style="text-align:right;">' . $this->formatNominal(@$begin_balance_local, 2, $option) . '</td>
                            </tr>';

                $invoice .= '<tr style="background: #DEE2FF; font-weight:bold;" class="begin_balance">
                                <td colspan="12">BEGINING BALANCE ('.$customer_name.')</td>
                                <td style="text-align:center;">' . $customer['currency_balance'] . '</td>
                                <td colspan="4" style="text-align:right;">' . $this->formatNominal(@$begin_balance_local, 2, $option) . '</td>
                            </tr>';
            }

            $accumulated = $begin_balance_local;

            foreach ($sales_invoices as $sales_invoice) 
            {
                $document_no = $sales_invoice['invoice_no'];
                if ($filter_display == "Invoice") {
                    $payments = $this->db->query("SELECT a.*, b.number as voucher_no, b.trans_date as voucher_date, a.account_number,
                        (CASE WHEN a.account_type = 'DEBIT' THEN (a.receipt) ELSE 0 END) AS local_debit,
                        (CASE WHEN a.account_type = 'CREDIT' THEN (a.receipt) ELSE 0 END) AS local_credit
                        FROM ar_receipts a
                        LEFT JOIN journal_postings b ON a.receipt_no = b.document_no
                        JOIN sales_invoices c ON a.sales_invoice = c.number
                        WHERE a.sales_invoice = '$document_no'
                        GROUP BY a.receipt_no")->result_array();
                }

                // if ($sales_invoice['status'] == '0') {
                //     $status_purchase = "OPEN";
                //     $style_status_purchase = "background-color:#C8FFCC;";
                // } else if ($sales_invoice['status'] == '1') {
                //     $status_purchase = "CLOSE";
                //     $style_status_purchase = "background-color:#FFC8C8;";
                // } else {
                //     $status_purchase = "-";
                //     $style_status_purchase = "";
                // }

                if ($sales_invoice['status_closed_flag'] == 0) {
                    $status_purchase = "OPEN";
                    $style_status_purchase = "background-color:#C8FFCC;";
                } else { // Jika 1 (CLOSED)
                    $status_purchase = "CLOSED";
                    $style_status_purchase = "background-color:#FFC8C8;";
                }
                
                $balance_local = ($sales_invoice['local_debit'] - $sales_invoice['local_credit']);
                $accumulated += ($sales_invoice['local_debit'] - $sales_invoice['local_credit']);

                $detail .= '<tr>
                                <td>' . $no . '</td>
                                <td style="text-align:center;' . $style_status_purchase . '">' . $status_purchase . '</td>
                                <td>' . $customer_id . '</td>
                                <td>' . $customer_name . '</td>
                                <td>' . $sales_invoice['source'] . '</td>
                                <td>' . $sales_invoice['invoice_date'] . '</td>
                                <td>' . $sales_invoice['payment_due'] . '</td>
                                <td>' . $sales_invoice['document_no'] . '</td>
                                <td>' . $sales_invoice['invoice_no'] . '</td>
                                <td>' . $sales_invoice['voucher_date'] . '</td>
                                <td>' . $sales_invoice['voucher_no'] . '</td>
                                <td>' . $sales_invoice['account_number'] . '</td>
                                <td style="text-align:center;">' . $currency_show . '</td>
                                <td style="text-align:right;">' . $this->formatNominal($sales_invoice['local_debit'], 2, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNominal($sales_invoice['local_credit'], 2, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($balance_local, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($accumulated, $option) . '</td>
                            </tr>';

                if ($filter_display == "Invoice") {
                    $payment_total = 0;
                    foreach ($payments as $payment) {
                        $balance_local += ($payment['local_debit'] - $payment['local_credit']);
                        $accumulated += ($payment['local_debit'] - $payment['local_credit']);
                        $payment_total += $payment['local_credit'];
                        $local_debit += $payment['local_debit'];
                        $local_credit += $payment['local_credit'];
                    }

                    if($payment_total == 0){
                        $payment_total = $sales_invoice['local_credit'];
                    }

                    $balance_invoice = ($sales_invoice['local_debit'] - $payment_total);

                    $invoice .= '<tr>
                                    <td>' . $no . '</td>
                                    <td style="text-align:center;' . $style_status_purchase . '">' . $status_purchase . '</td>
                                    <td>' . $customer_id . '</td>
                                    <td>' . $customer_name . '</td>
                                    <td>' . $plant . '</td>
                                    <td>' . $sales_invoice['source'] . '</td>
                                    <td>' . $sales_invoice['invoice_date'] . '</td>
                                    <td>' . $sales_invoice['payment_due'] . '</td>
                                    <td>' . $sales_invoice['document_no'] . '</td>
                                    <td>' . $sales_invoice['invoice_no'] . '</td>
                                    <td>' . $sales_invoice['voucher_date'] . '</td>
                                    <td>' . $sales_invoice['voucher_no'] . '</td>
                                    <td>' . $sales_invoice['account_number'] . '</td>
                                    <td style="text-align:center;">' . $currency_show . '</td>
                                    <td style="text-align:right;">' . $this->formatNominal($sales_invoice['local_debit'], 2, $option) . '</td>
                                    <td style="text-align:right;">' . $this->formatNominal($payment_total, 2, $option) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_invoice, $option) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated, $option) . '</td>
                                </tr>';
                }

                $no++;
                $local_debit += $sales_invoice['local_debit'];
                $local_credit += $sales_invoice['local_credit'];
            }

            $balance_local = ($begin_balance_local + $local_debit - $local_credit);

            if (count($sales_invoices) > 0 || $balance_local > 0) 
            {
                $detail .= '<tr style="background: #E5E5E5; font-weight:bold;">
                                <td colspan="13">SUB TOTAL</td>
                                <td style="text-align:right;">' . $this->formatNominal($local_debit, 2, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNominal($local_credit, 2, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($balance_local, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($accumulated, $option) . '</td>
                            </tr>
                            <tr>
                                <td colspan="17" style="height:20px;"></td>
                            </tr>';

                $invoice .= '<tr style="background: #E5E5E5; font-weight:bold;">
                                <td colspan="13">SUB TOTAL</td>
                                <td style="text-align:right;">' . $this->formatNominal($local_debit, 2, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNominal($local_credit, 2, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($balance_local, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($accumulated, $option) . '</td>
                            </tr>
                            <tr>
                                <td colspan="17" style="height:20px;"></td>
                            </tr>';

                $currency_summary = !empty($customer['currency_balance']) ? $customer['currency_balance'] : $customer['currency'];
            
                $summary .= '<tbody>
                                <tr>
                                <td>' . $noid . '</td>
                                <td>' . $customer['name'] . '</td>
                                <td style="text-align:center;">' . $currency_summary . '</td>
                                <td style="text-align:right;">' . $this->formatNo($balance_local, $option) . '</td>
                            </tr>';
            }
            
            $noid++;

            $grand_local_debit += $local_debit;
            $grand_local_credit += $local_credit;
            $grand_local_balance += ($begin_balance_local + $grand_local_debit - $grand_local_credit);
            $grand_summary_total += $balance_local;
        }

        $detail .= '<tr style="background: #C3FFB4; font-weight:bold;">
                        <td colspan="13">GRAND TOTAL</td>
                        <td style="text-align:right;">' . $this->formatNominal($grand_local_debit, 2, $option) . '</td>
                        <td style="text-align:right;">' . $this->formatNominal($grand_local_credit, 2, $option) . '</td>
                        <td style="text-align:right;">' .$this->formatNo($grand_local_balance, $option) . '</td>
                        <td style="text-align:right;">' . $this->formatNo($grand_summary_total, $option) . '</td>
                    </tr>';

        $invoice .= '<tr style="background: #C3FFB4; font-weight:bold;">
                        <td colspan="13">GRAND TOTAL</td>
                        <td style="text-align:right;">' . $this->formatNominal($grand_local_debit, 2, $option) . '</td>
                        <td style="text-align:right;">' . $this->formatNominal($grand_local_credit, 2, $option) . '</td>
                        <td style="text-align:right;">' . $this->formatNo($grand_local_balance, $option) . '</td>
                        <td style="text-align:right;">' . $this->formatNo($grand_summary_total, $option) . '</td>
                    </tr>';

        $summary .= '<tr style="font-weight:bold;" class="grand_total">
                        <td style="text-align:right;" colspan="3">GRAND TOTAL</td>
                        <td style="text-align:right;">' . $this->formatNo($grand_summary_total, $option) . '</td>
                    </tr>
                </tbody>';

        $htmlend = '</table></body></html>';

        if ($filter_display == "Summary") {
            echo $html . $summary . $htmlend;
        } else {
            echo $html . $detail . $htmlend;
        }
    }

    public function print_new($option = "") 
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_ar_$format.xls");
        }

        $filter_from        = base64_decode($this->input->get("filter_from"));
        $filter_to          = base64_decode($this->input->get("filter_to"));
        $filter_customer    = $this->input->get("filter_customer");
        $filter_currency    = $this->input->get("filter_currency");
        $filter_payment     = $this->input->get("filter_payment");
        $filter_display     = $this->input->get("filter_display");
        $filter_sales_invoice = base64_decode($this->input->get("filter_sales_invoice"));
        $period             = date("Ym", strtotime($filter_to));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, (COALESCE(b.balance_local, 0)) as balance_local, (COALESCE(b.balance, 0)) as balance');
        $this->db->from('customers a');
        $this->db->join('account_balance_customers b', 'a.id = b.customer_id', 'left');
        if($filter_customer != ""){
            $this->db->where("a.id", $filter_customer);
        }
        $this->db->group_by('a.id');
        $this->db->order_by('a.name', 'asc');
        $customers = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
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
                <h3 style="margin:0;">ACCOUNT RECEIVABLE REPORT <i>('.$filter_display.')</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>';

            $summary = '<table id="customers" border="1">
                            <tr>
                                <th width="20">No</th>
                                <th>Customer Name</th>
                                <th>Currency</th>
                                <th>ORIGINAL CURRENCY<br><i>Balance</i></th>
                                <th>LOCAL CURRENCY<br><i>Balance</i></th>
                            </tr>';
            
            $detail = '<table id="customers" border="1">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2">Customer Name</th>
                            <th rowspan="2">Transaction Date</th>
                            <th rowspan="2">Receipt Due</th>
                            <th rowspan="2">Document No</th>
                            <th rowspan="2">Invoice No</th>
                            <th rowspan="2">Voucher No</th>
                            <th rowspan="2">Account No</th>
                            <th rowspan="2">Currency</th>
                            <th colspan="3">ORIGINAL CURRENCY</th>
                            <th colspan="4">LOCAL CURRENCY</th>
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
            $no = 1;
            $noid = 1;
            $grand_original_debit = 0;
            $grand_original_credit = 0;
            $grand_original_balance = 0;
            $grand_local_debit = 0;
            $grand_local_credit = 0;
            $grand_local_balance = 0;

            foreach ($customers as $customer) 
            {
                $customer_id = $customer['id'];
                $customer_name = $customer['name'];

                if($customer['currency'] != "IDR"){
                    $digit = 2;
                }else{
                    $digit = 2;
                }

                // Tentukan alias untuk subquery journal_postings
                $subquery_journal_postings = $this->db->select("number, document_no, account_number, rates, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit, SUM(original_debit) as original_debit, SUM(original_credit) as original_credit")
                                                    ->from('journal_postings')
                                                    ->group_by('number, document_no, account_number')
                                                    ->get_compiled_select();

                // Bagian 1: Query dari sales_invoices
                $this->db->select("a.trans_date, a.due_date, a.sales_order_no as document_no, a.number as sales_invoice, c.account_number, c.number as voucher_no, a.currency, c.original_debit, c.original_credit, c.local_debit, c.local_credit, c.rates");
                $this->db->from('sales_invoices a');
                $this->db->join("($subquery_journal_postings) c", 'a.number = c.document_no');
                $this->db->join("account_coa coa", 'a.account_number = coa.account_number');
                $this->db->where('coa.report_ar', 1); // Account Number Report AR=1 TRUE
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
                $this->db->where('a.customer_id', $customer_id);
                if (!empty($filter_currency)) {
                    $this->db->where('a.currency', $filter_currency);
                }
                $query1 = $this->db->get_compiled_select();

                // Bagian 2: Query dari ar_receipts
                $this->db->select("a.receipt_date as trans_date, '-' as due_date, a.sales_invoice, a.receipt_no as document_no, c.account_number, c.number as voucher_no, a.currency, c.original_debit, c.original_credit, c.local_debit, c.local_credit, c.rates");
                $this->db->from('ar_receipts a');
                $this->db->join("($subquery_journal_postings) c", 'a.receipt_no = c.document_no');
                $this->db->join("account_coa coa", 'a.account_number = coa.account_number');
                $this->db->where('coa.report_ar', 1); // Account Number Report AR=1 TRUE
                $this->db->where('a.receipt_date >=', $filter_from);
                $this->db->where('a.receipt_date <=', $filter_to);
                $this->db->where('a.customer_id', $customer_id);
                if (!empty($filter_currency)) {
                    $this->db->where('a.currency', $filter_currency);
                }
                $this->db->group_by('a.receipt_no');
                $query2 = $this->db->get_compiled_select();

                // Bagian 3: Query dari journal_postings
                $this->db->select("a.journal_date as trans_date, '-' as due_date, a.invoice_no as sales_invoice, a.document_no, a.account_number, a.number as voucher_no, a.currency, SUM(a.original_debit) as original_debit, SUM(a.original_credit) as original_credit, SUM(a.local_debit) as local_debit, SUM(a.local_credit) as local_credit, a.rates");
                $this->db->from('journal_postings a');
                $this->db->join("account_coa coa", 'a.account_number = coa.account_number');
                $this->db->where('coa.report_ar', 1); // Account Number Report AR=1 TRUE
                $this->db->where('a.journal_date >=', $filter_from);
                $this->db->where('a.journal_date <=', $filter_to);
                $this->db->where_in('a.modul', array('CURRENCY REVALUATION','ADJUSTMENT'));
                $this->db->like('a.description', $customer_name, 'both');
                if (!empty($filter_currency)) {
                    $this->db->where('a.currency', $filter_currency);
                }
                // $this->db->group_by('a.number, a.document_no'); // di-comment agar tampil detail begin_balance untuk penjelasan grand_total
                $query3 = $this->db->get_compiled_select();

                // Menggabungkan semua query menggunakan UNION ALL
                $full_query = "($query1) UNION ALL ($query2) UNION ALL ($query3)";

                // SELECT data dan tambahkan semua kolom ke GROUP BY
                $final_query = $this->db->query("
                    SELECT
                        a.trans_date, a.due_date, a.sales_invoice, a.document_no,
                        a.account_number, a.voucher_no, a.currency,
                        SUM(a.original_debit) AS original_debit,
                        SUM(a.original_credit) AS original_credit,
                        SUM(a.local_debit) AS local_debit,
                        SUM(a.local_credit) AS local_credit,
                        a.rates
                    FROM ($full_query) a
                    WHERE a.document_no IS NOT NULL AND a.document_no != ''
                    GROUP BY
                        a.trans_date, a.due_date, a.sales_invoice, a.document_no,
                        a.account_number, a.voucher_no, a.currency, a.rates
                    ORDER BY a.trans_date ASC
                ");
                $sales_invoices = $final_query->result_array();

                $original_debit = 0;
                $original_credit = 0;
                $original_balance = 0;
                $local_debit = 0;
                $local_credit = 0;
                $local_balance = 0;

                $this->db->select('COALESCE(SUM(b.local_debit) + SUM(b.local_credit), 0) as local_pi, COALESCE(SUM(b.original_debit) + SUM(b.original_credit), 0) as original_pi');
                $this->db->from('(SELECT DISTINCT customer_id, number, trans_date FROM sales_invoices) a');
                $this->db->join("journal_postings b", "a.number = b.document_no");
                $this->db->join("account_coa coa", 'b.account_number = coa.account_number');
                $this->db->where('coa.report_ar', 1); // Account Number Report AR=1 TRUE
                $this->db->where('a.customer_id', $customer_id);
                $this->db->where('a.trans_date <', $filter_from);
                if (!empty($filter_currency)) {
                    $this->db->where('b.currency', $filter_currency);
                }
                $pi_begin = $this->db->get()->row();

                $this->db->select('COALESCE(SUM(a.local_credit) - SUM(a.local_debit), 0) as local_re, COALESCE(SUM(a.original_credit) - SUM(a.original_debit), 0) as original_re');
                $this->db->from('journal_postings a');
                $this->db->join("account_coa coa", 'a.account_number = coa.account_number');
                $this->db->where('coa.report_ar', 1); // Account Number Report AR=1 TRUE
                $this->db->where("a.journal_date <", $filter_from);
                $this->db->where_in("a.modul", array('CURRENCY REVALUATION'));
                $this->db->like("a.description", $customer_name, 'both');
                if (!empty($filter_currency)) {
                    $this->db->where('a.currency', $filter_currency);
                }
                $revaluation_begin = $this->db->get()->row();
                
                $this->db->select('COALESCE(SUM(b.local_debit) + SUM(b.local_credit), 0) as local_ar, COALESCE(SUM(b.original_debit) + SUM(b.original_credit), 0) as original_ar');
                $this->db->from('(SELECT DISTINCT customer_id, receipt_no, receipt_date FROM ar_receipts) a');
                $this->db->join("journal_postings b", "a.receipt_no = b.document_no");
                $this->db->join("account_coa coa", 'b.account_number = coa.account_number');
                $this->db->where('coa.report_ar', 1); // Account Number Report AR=1 TRUE
                $this->db->where('a.customer_id', $customer_id);
                $this->db->where('a.receipt_date <', $filter_from);
                if (!empty($filter_currency)) {
                    $this->db->where('b.currency', $filter_currency);
                }
                $ap_begin = $this->db->get()->row();

                if(@$customer['balance'] > 0 || $pi_begin->original_pi > 0 || $ap_begin->original_ar > 0 || $revaluation_begin->original_re > 0){
                    $begin_balance = (@$customer['balance'] + ($pi_begin->original_pi - $revaluation_begin->original_re - abs($ap_begin->original_ar)));
                }else{
                    $begin_balance = 0;
                }

                if(@$customer['balance'] > 0 || $pi_begin->local_pi > 0 || $ap_begin->local_ar > 0 || $revaluation_begin->local_re > 0){
                    $begin_balance_local = (@$customer['balance_local'] + ($pi_begin->local_pi - $revaluation_begin->local_re - abs($ap_begin->local_ar)));
                    // $begin_balance_local = (@$customer['balance_local'] + ($revaluation_begin->local_re));
                    // $begin_balance_local = (@$customer['balance_local'] + ($pi_begin->local_pi - $ap_begin->local_ar));
                }else{
                    $begin_balance_local = 0;
                }

                if(count($sales_invoices) > 0){
                    $detail .= '<tr style="background: #DEE2FF; font-weight:bold;">
                                    <td colspan="11">BEGINING BALANCE</td>
                                    <td style="text-align:right;">'.number_format(@$begin_balance, $digit, ".", "").'</td>
                                    <td colspan="3"></td>
                                    <td style="text-align:right;">'.number_format(@$begin_balance_local, $digit, ".", "").'</td>
                                </tr>';
                }

                foreach ($sales_invoices as $sales_invoice) 
                {
                    if((@$begin_balance + $sales_invoice['original_debit'] - abs($sales_invoice['original_credit'])) >= 0){
                        $balance_original = number_format(@$begin_balance + $sales_invoice['original_debit'] - abs($sales_invoice['original_credit']), $digit, ".", "");
                    }else{
                        $balance_original = "<span style='color:red;'>(".number_format(abs(@$begin_balance + $sales_invoice['original_debit'] - abs($sales_invoice['original_credit'])), $digit, ".", "").")</span>";
                    }

                    if((@$begin_balance_local + $sales_invoice['local_debit'] - abs($sales_invoice['local_credit'])) >= 0){
                        $balance_local = number_format(@$begin_balance_local + $sales_invoice['local_debit'] - abs($sales_invoice['local_credit']), $digit, ".", "");
                    }else{
                        $balance_local = "<span style='color:red;'>(".number_format(abs(@$begin_balance_local + $sales_invoice['local_debit'] - abs($sales_invoice['local_credit'])), $digit, ".", "").")</span>";
                    }

                    $detail .= '  <tr>
                                    <td>'.$no.'</td>
                                    <td>'.$customer_name.'</td>
                                    <td>'.$sales_invoice['trans_date'].'</td>
                                    <td>'.$sales_invoice['due_date'].'</td>
                                    <td>'.$sales_invoice['document_no'].'</td>
                                    <td>'.$sales_invoice['sales_invoice'].'</td>
                                    <td>'.$sales_invoice['voucher_no'].'</td>
                                    <td>'.$sales_invoice['account_number'].'</td>
                                    <td style="text-align:center;">'.$sales_invoice['currency'].'</td>
                                    <td style="text-align:right;">'.number_format($sales_invoice['original_debit'], $digit, ".", "").'</td>
                                    <td style="text-align:right;">'.number_format(abs($sales_invoice['original_credit']), $digit, ".", "").'</td>
                                    <td style="text-align:right;">'.$balance_original.'</td>
                                    <td style="text-align:right;">'.number_format($sales_invoice['rates'], $digit, ".", "").'</td>
                                    <td style="text-align:right;">'.number_format($sales_invoice['local_debit'], $digit, ".", "").'</td>
                                    <td style="text-align:right;">'.number_format(abs($sales_invoice['local_credit']), $digit, ".", "").'</td>
                                    <td style="text-align:right;">'.$balance_local.'</td>
                                </tr>';

                    $no++;
                    $original_debit += $sales_invoice['original_debit'];
                    $original_credit += abs($sales_invoice['original_credit']);
                    $original_balance += (@$begin_balance + $sales_invoice['original_debit'] - abs($sales_invoice['original_credit']));
                    $local_debit += $sales_invoice['local_debit'];
                    $local_credit += abs($sales_invoice['local_credit']);
                    $local_balance += (@$begin_balance_local + $sales_invoice['local_debit'] - abs($sales_invoice['local_credit']));
                    $begin_balance = (@$begin_balance + $sales_invoice['original_debit'] - abs($sales_invoice['original_credit']));
                    $begin_balance_local = (@$begin_balance_local + $sales_invoice['local_debit'] - abs($sales_invoice['local_credit']));
                }

                if(count($sales_invoices) > 0)
                {
                    if($begin_balance >= 0){
                        $balance_original = number_format($begin_balance, $digit, ".", "");
                    }else{
                        $balance_original = "<span style='color:red;'>(".number_format(abs($begin_balance), $digit, ".", "").")</span>";
                    }

                    if($begin_balance_local >= 0){
                        $balance_local = number_format(@$begin_balance_local, $digit, ".", "");
                    }else{
                        $balance_local = "<span style='color:red;'>(".number_format(abs(@$begin_balance_local), $digit, ".", "").")</span>";
                    }

                    $detail .= '  <tr style="background: #E5E5E5; font-weight:bold;">
                                    <td colspan="9">SUB TOTAL</td>
                                    <td style="text-align:right;">'.number_format($original_debit, $digit, ".", "").'</td>
                                    <td style="text-align:right;">'.number_format($original_credit, $digit, ".", "").'</td>
                                    <td style="text-align:right;">'.$balance_original.'</td>
                                    <td></td>
                                    <td style="text-align:right;">'.number_format($local_debit, $digit, ".", "").'</td>
                                    <td style="text-align:right;">'.number_format($local_credit, $digit, ".", "").'</td>
                                    <td style="text-align:right;">'.$balance_local.'</td>
                                </tr>
                                <tr>
                                    <td colspan="16" style="height:20px;"></td>
                                </tr>';
                }

                // tetap tampil Customer dengan begin_balance=0 yang memiliki transaksi => agar sama antara Summary dengan Detail
                if(count($sales_invoices) > 0)
                {
                    if($begin_balance >= 0){
                        $balance_original = number_format($begin_balance, 2, ".", "");
                    }else{
                        $balance_original = "<span style='color:red;'>(".number_format(abs($begin_balance), 2, ".", "").")</span>";
                    }

                    if($begin_balance_local >= 0){
                        $balance_local = number_format(@$begin_balance_local, 2, ".", "");
                    }else{
                        $balance_local = "<span style='color:red;'>(".number_format(abs(@$begin_balance_local), 2, ".", "").")</span>";
                    }

                    $summary .= '   <tr>
                                        <td>'.$noid.'</td>
                                        <td>'.$customer['name'].'</td>
                                        <td>'.$customer['currency'].'</td>
                                        <td style="text-align:right;">'.$balance_original.'</td>
                                        <td style="text-align:right;">'.$balance_local.'</td>
                                    </tr>';
                    $noid++;
                }
                
                $grand_original_debit += $original_debit;
                $grand_original_credit += $original_credit;
                $grand_original_balance += $begin_balance;
                $grand_local_debit += $local_debit;
                $grand_local_credit += $local_credit;
                $grand_local_balance += $begin_balance_local;
            }

            $detail .= '  <tr style="background: #C3FFB4; font-weight:bold;">
                            <td colspan="9">GRAND TOTAL</td>
                            <td style="text-align:right;">'.number_format($grand_original_debit, 2, ".", "").'</td>
                            <td style="text-align:right;">'.number_format($grand_original_credit, 2, ".", "").'</td>
                            <td style="text-align:right;">'.number_format($grand_original_balance, 2, ".", "").'</td>
                            <td></td>
                            <td style="text-align:right;">'.number_format($grand_local_debit, 2, ".", "").'</td>
                            <td style="text-align:right;">'.number_format($grand_local_credit, 2, ".", "").'</td>
                            <td style="text-align:right;">'.number_format($grand_local_balance, 2, ".", "").'</td>
                        </tr>';

            $summary .= '   <tr style="font-weight:bold;">
                                <td colspan="3">GRAND TOTAL</td>
                                <td style="text-align:right;">'.number_format($grand_original_balance, 2, ".", "").'</td>
                                <td style="text-align:right;">'.number_format($grand_local_balance, 2, ".", "").'</td>
                            </tr>';

        $htmlend = '</table></body></html>';
        
        if($filter_display == "Summary"){
            echo $html . $summary . $htmlend;
        }else{
            echo $html . $detail . $htmlend;
        }
    }

    public function print_existing($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_ap_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_customer = $this->input->get("filter_customer");
        $filter_currency = $this->input->get("filter_currency");
        $filter_payment = $this->input->get("filter_payment");
        $filter_display = $this->input->get("filter_display");
        $filter_sales_invoice = base64_decode($this->input->get("filter_sales_invoice"));
        $period = date("Ym", strtotime($filter_to));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, (COALESCE(b.balance_local, 0)) as balance_local, (COALESCE(b.balance, 0)) as balance');
        $this->db->from('customers a');
        $this->db->join('account_balance_customers b', 'a.id = b.customer_id', 'left');
        if($filter_customer != ""){
            $this->db->where("a.id", $filter_customer);
        }
        $this->db->group_by('a.id');
        $this->db->order_by('a.name', 'asc');
        $customers = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
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
                <h3 style="margin:0;">ACCOUNT RECEIVABLE REPORT <i>('.$filter_display.')</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>';

            $summary = '<table id="customers" border="1">
                            <tr>
                                <th width="20">No</th>
                                <th>Customer Name</th>
                                <th>Currency</th>
                                <th>ORIGINAL CURRENCY<br><i>Balance</i></th>
                                <th>LOCAL CURRENCY<br><i>Balance</i></th>
                            </tr>';
            
            $detail = '<table id="customers" border="1">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2">Customer Name</th>
                            <th rowspan="2">Trans Date</th>
                            <th rowspan="2">Receipt Due</th>
                            <th rowspan="2">Document No</th>
                            <th rowspan="2">Invoice No</th>
                            <th rowspan="2">Voucher No</th>
                            <th rowspan="2">Account No</th>
                            <th rowspan="2">Currency</th>
                            <th colspan="3">ORIGINAL CURRENCY</th>
                            <th colspan="4">LOCAL CURRENCY</th>
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
            $no = 1;
            $noid = 1;
            $grand_original_debit = 0;
            $grand_original_credit = 0;
            $grand_original_balance = 0;
            $grand_local_debit = 0;
            $grand_local_credit = 0;
            $grand_local_balance = 0;
            foreach ($customers as $customer) {
                $customer_id = $customer['id'];
                $customer_name = $customer['name'];

                if($customer['currency'] != "IDR"){
                    $digit = 2;
                }else{
                    $digit = 2;
                }

                $query = $this->db->query("SELECT a.*
                    FROM (
                        (SELECT a.trans_date, a.due_date, a.number as document_no, a.so_number as sales_invoice, c.account_number, c.number as voucher_no, a.currency, c.original_debit, c.original_credit, c.local_debit, c.local_credit, c.rates
                        FROM sales_invoices a
                        JOIN (SELECT number, document_no, account_number, rates, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit, SUM(original_debit) as original_debit, SUM(original_credit) as original_credit FROM 
                            journal_postings WHERE account_number IN ('1-1311','1-1312','1-1314','1-1316') GROUP BY number, document_no, account_number) c ON a.number = c.document_no
                        WHERE a.trans_date between '$filter_from' and '$filter_to' and a.customer_id = '$customer_id'
                        ) 
                    UNION 
                        (SELECT a.receipt_date as trans_date, '-' as due_date, a.sales_invoice, a.receipt_no as document_no, c.account_number, c.number as voucher_no, a.currency, c.original_debit, c.original_credit, c.local_debit, c.local_credit, c.rates
                        FROM ar_receipts a
                        JOIN (SELECT number, document_no, account_number, rates, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit, SUM(original_debit) as original_debit, SUM(original_credit) as original_credit FROM 
                            journal_postings WHERE account_number IN ('1-1311','1-1312','1-1314','1-1316') GROUP BY number, document_no, account_number) c ON a.receipt_no = c.document_no
                        WHERE a.receipt_date between '$filter_from' and '$filter_to' and a.customer_id = '$customer_id' GROUP BY a.receipt_no
                        )
                    UNION 
                        (SELECT a.journal_date as trans_date, '-' as due_date, a.invoice_no as sales_invoice, a.document_no, a.account_number, a.number as voucher_no, a.currency, a.original_debit, a.original_credit, a.local_debit, a.local_credit, a.rates
                        FROM journal_postings a
                        WHERE a.journal_date between '$filter_from' and '$filter_to' and a.description like '%$customer_name%' and a.modul in ('CURRENCY REVALUATION','ADJUSTMENT') and a.account_number IN ('1-1311','1-1312','1-1314','1-1316')
                        )
                    ) a GROUP BY a.voucher_no, a.document_no ORDER BY a.trans_date ASC");
                $sales_invoices = $query->result_array();
                
                $original_debit = 0;
                $original_credit = 0;
                $original_balance = 0;
                $local_debit = 0;
                $local_credit = 0;
                $local_balance = 0;

                $this->db->select('COALESCE(SUM(local_debit) + SUM(local_credit), 0) as local_pi, COALESCE(SUM(original_debit) + SUM(original_credit), 0) as original_pi');
                $this->db->from('(SELECT DISTINCT customer_id, number, trans_date FROM sales_invoices) a');
                $this->db->join("journal_postings b", "a.number = b.document_no and b.account_number IN ('1-1311','1-1312','1-1314','1-1316')");
                $this->db->where('a.customer_id', $customer_id);
                $this->db->where('a.trans_date <', $filter_from);
                $pi_begin = $this->db->get()->row();

                $this->db->select('COALESCE(SUM(a.local_credit) - SUM(a.local_debit), 0) as local_re, COALESCE(SUM(a.original_credit) - SUM(a.original_debit), 0) as original_re');
                $this->db->from('journal_postings a');
                $this->db->where("a.journal_date <", $filter_from);
                $this->db->where_in("a.modul", array('CURRENCY REVALUATION'));
                $this->db->where_in("a.account_number", array('1-1311','1-1312','1-1314','1-1316'));
                $this->db->like("a.description", $customer_name);
                $revaluation_begin = $this->db->get()->row();
                
                $this->db->select('COALESCE(SUM(local_debit) + SUM(local_credit), 0) as local_ap, COALESCE(SUM(original_debit) + SUM(original_credit), 0) as original_ap');
                $this->db->from('(SELECT DISTINCT customer_id, receipt_no, receipt_date FROM ar_receipts) a');
                $this->db->join("journal_postings b", "a.receipt_no = b.document_no and b.account_number IN ('1-1311','1-1312','1-1314','1-1316')");
                $this->db->where('a.customer_id', $customer_id);
                $this->db->where('a.receipt_date <', $filter_from);
                $ap_begin = $this->db->get()->row();

                if(@$customer['balance'] > 0 || $pi_begin->original_pi > 0 || $ap_begin->original_ap > 0 || $revaluation_begin->original_re > 0){
                    $begin_balance = (@$customer['balance'] + ($pi_begin->original_pi - $revaluation_begin->original_re - abs($ap_begin->original_ap)));
                }else{
                    $begin_balance = 0;
                }

                if(@$customer['balance'] > 0 || $pi_begin->local_pi > 0 || $ap_begin->local_ap > 0 || $revaluation_begin->local_re > 0){
                    $begin_balance_local = (@$customer['balance_local'] + ($pi_begin->local_pi - $revaluation_begin->local_re - abs($ap_begin->local_ap)));
                    // $begin_balance_local = (@$customer['balance_local'] + ($revaluation_begin->local_re));
                    // $begin_balance_local = (@$customer['balance_local'] + ($pi_begin->local_pi - $ap_begin->local_ap));
                }else{
                    $begin_balance_local = 0;
                }

                if(count($sales_invoices) > 0){
                    $detail .= '  <tr style="background: #DEE2FF; font-weight:bold;">
                                    <td colspan="11">BEGINING BALANCE</td>
                                    <td style="text-align:right;">'.number_format(@$begin_balance, $digit).'</td>
                                    <td colspan="3"></td>
                                    <td style="text-align:right;">'.number_format(@$begin_balance_local, $digit).'</td>
                                </tr>';
                }

                foreach ($sales_invoices as $sales_invoice) {
                    if((@$begin_balance + $sales_invoice['original_debit'] - abs($sales_invoice['original_credit'])) >= 0){
                        $balance_original = number_format(@$begin_balance + $sales_invoice['original_debit'] - abs($sales_invoice['original_credit']), $digit);
                    }else{
                        $balance_original = "<span style='color:red;'>(".number_format(abs(@$begin_balance + $sales_invoice['original_debit'] - abs($sales_invoice['original_credit'])), $digit).")</span>";
                    }

                    if((@$begin_balance_local + $sales_invoice['local_debit'] - abs($sales_invoice['local_credit'])) >= 0){
                        $balance_local = number_format(@$begin_balance_local + $sales_invoice['local_debit'] - abs($sales_invoice['local_credit']), 2);
                    }else{
                        $balance_local = "<span style='color:red;'>(".number_format(abs(@$begin_balance_local + $sales_invoice['local_debit'] - abs($sales_invoice['local_credit'])), $digit).")</span>";
                    }

                    $detail .= '  <tr>
                                    <td>'.$no.'</td>
                                    <td>'.$customer_name.'</td>
                                    <td>'.$sales_invoice['trans_date'].'</td>
                                    <td>'.$sales_invoice['due_date'].'</td>
                                    <td>'.$sales_invoice['document_no'].'</td>
                                    <td>'.$sales_invoice['sales_invoice'].'</td>
                                    <td>'.$sales_invoice['voucher_no'].'</td>
                                    <td>'.$sales_invoice['account_number'].'</td>
                                    <td>'.$sales_invoice['currency'].'</td>
                                    <td style="text-align:right;">'.number_format($sales_invoice['original_debit'], $digit).'</td>
                                    <td style="text-align:right;">'.number_format(abs($sales_invoice['original_credit']), $digit).'</td>
                                    <td style="text-align:right;">'.$balance_original.'</td>
                                    <td style="text-align:right;">'.number_format($sales_invoice['rates'], $digit).'</td>
                                    <td style="text-align:right;">'.number_format($sales_invoice['local_debit'], $digit).'</td>
                                    <td style="text-align:right;">'.number_format(abs($sales_invoice['local_credit']), $digit).'</td>
                                    <td style="text-align:right;">'.$balance_local.'</td>
                                </tr>';

                    $no++;
                    $original_debit += $sales_invoice['original_debit'];
                    $original_credit += abs($sales_invoice['original_credit']);
                    $original_balance += (@$begin_balance + $sales_invoice['original_debit'] - abs($sales_invoice['original_credit']));
                    $local_debit += $sales_invoice['local_debit'];
                    $local_credit += abs($sales_invoice['local_credit']);
                    $local_balance += (@$begin_balance_local + $sales_invoice['local_debit'] - abs($sales_invoice['local_credit']));
                    $begin_balance = (@$begin_balance + $sales_invoice['original_debit'] - abs($sales_invoice['original_credit']));
                    $begin_balance_local = (@$begin_balance_local + $sales_invoice['local_debit'] - abs($sales_invoice['local_credit']));
                }

                if(count($sales_invoices) > 0){
                    if($begin_balance >= 0){
                        $balance_original = number_format($begin_balance, $digit);
                    }else{
                        $balance_original = "<span style='color:red;'>(".number_format(abs($begin_balance), $digit).")</span>";
                    }

                    if($begin_balance_local >= 0){
                        $balance_local = number_format(@$begin_balance_local, $digit);
                    }else{
                        $balance_local = "<span style='color:red;'>(".number_format(abs(@$begin_balance_local), $digit).")</span>";
                    }

                    $detail .= '  <tr style="background: #E5E5E5; font-weight:bold;">
                                    <td colspan="9">SUB TOTAL</td>
                                    <td style="text-align:right;">'.number_format($original_debit, $digit).'</td>
                                    <td style="text-align:right;">'.number_format($original_credit, $digit).'</td>
                                    <td style="text-align:right;">'.$balance_original.'</td>
                                    <td></td>
                                    <td style="text-align:right;">'.number_format($local_debit, $digit).'</td>
                                    <td style="text-align:right;">'.number_format($local_credit, $digit).'</td>
                                    <td style="text-align:right;">'.$balance_local.'</td>
                                </tr>
                                <tr>
                                    <td colspan="16" style="height:20px;"></td>
                                </tr>';
                }

                // if($begin_balance_local != 0){

                    if($begin_balance >= 0){
                        $balance_original = number_format($begin_balance, 2);
                    }else{
                        $balance_original = "<span style='color:red;'>(".number_format(abs($begin_balance), 2).")</span>";
                    }

                    if($begin_balance_local >= 0){
                        $balance_local = number_format(@$begin_balance_local, 2);
                    }else{
                        $balance_local = "<span style='color:red;'>(".number_format(abs(@$begin_balance_local), 2).")</span>";
                    }

                    $summary .= '   <tr>
                                        <td>'.$noid.'</td>
                                        <td>'.$customer['name'].'</td>
                                        <td>'.$customer['currency'].'</td>
                                        <td style="text-align:right;">'.$balance_original.'</td>
                                        <td style="text-align:right;">'.$balance_local.'</td>
                                    </tr>';
                    $noid++;
                // }
                
                $grand_original_debit += $original_debit;
                $grand_original_credit += $original_credit;
                $grand_original_balance += $begin_balance;
                $grand_local_debit += $local_debit;
                $grand_local_credit += $local_credit;
                $grand_local_balance += $begin_balance_local;
            }

            $detail .= '  <tr style="background: #C3FFB4; font-weight:bold;">
                            <td colspan="9">GRAND TOTAL</td>
                            <td style="text-align:right;">'.number_format($grand_original_debit, 2).'</td>
                            <td style="text-align:right;">'.number_format($grand_original_credit, 2).'</td>
                            <td style="text-align:right;">'.number_format($grand_original_balance, 2).'</td>
                            <td></td>
                            <td style="text-align:right;">'.number_format($grand_local_debit, 2).'</td>
                            <td style="text-align:right;">'.number_format($grand_local_credit, 2).'</td>
                            <td style="text-align:right;">'.number_format($grand_local_balance, 2).'</td>
                        </tr>';

            $summary .= '   <tr style="font-weight:bold;">
                                <td colspan="3">GRAND TOTAL</td>
                                <td style="text-align:right;">'.number_format($grand_original_balance, 2).'</td>
                                <td style="text-align:right;">'.number_format($grand_local_balance, 2).'</td>
                            </tr>';

        $htmlend = '</table></body></html>';
        
        if($filter_display == "Summary"){
            echo $html . $summary . $htmlend;
        }else{
            echo $html . $detail . $htmlend;
        }
    }
}
