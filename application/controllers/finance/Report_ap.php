<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_ap extends CI_Controller
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
        // $this->form_validation->set_rules('payment_no', 'Payment No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/report_ap');
        } else {
            redirect('error_access');
        }
    }

    // DROPDOWN FILTER
    public function readSuppliers()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('suppliers', ["name" => $post]);
        echo json_encode($send);
    }

    public function readPostingNo($supplier_name) 
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_name = base64_decode($supplier_name);

        $send = $this->crud->query("SELECT DISTINCT number, modul, journal_date, company_name FROM journal_postings WHERE modul NOT IN ('BANK STATEMENT','CLEARING') and modul IN ('AP PAYMENT','PURCHASE INVOICING') 
        and number like '%$post%' and company_name like '%$supplier_name%' ORDER BY number DESC");
        echo json_encode($send);
    }

    public function readDocumentNo($supplier_name) 
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_name = base64_decode($supplier_name);

        $send = $this->crud->query("SELECT DISTINCT document_no, number, modul, journal_date, company_name FROM journal_postings WHERE modul NOT IN ('BANK STATEMENT','CLEARING') and modul IN ('AP PAYMENT','PURCHASE INVOICING') 
        and document_no like '%$post%' and company_name like '%$supplier_name%' ORDER BY document_no DESC");
        echo json_encode($send);
    }

    public function readInvoiceNo($supplier_name)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_name = base64_decode($supplier_name);

        $send = $this->crud->query("SELECT DISTINCT invoice_no, number, modul, journal_date, company_name FROM journal_postings WHERE modul NOT IN ('BANK STATEMENT','CLEARING') and modul IN ('AP PAYMENT','PURCHASE INVOICING') 
        and invoice_no like '%$post%' and company_name like '%$supplier_name%' ORDER BY invoice_no DESC");
        echo json_encode($send);
    }

    public function readCurrencies()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('currencies', ["name" => $post]);
        echo json_encode($send);
    }

    function exchangeRates($currency, $date)
    {
        $this->db->select('*');
        $this->db->from('exchange_rates');
        $this->db->where('currency_from', $currency);
        $this->db->where('currency_to', 'IDR');
        $this->db->where("'$date' BETWEEN start_date AND end_date", null, false); // penting: raw SQL
        $exchange = $this->db->get()->row();
        
        if ($currency !== "IDR") {
            if (!empty($exchange)) {
                $rate = $exchange->middle;
            } else {
                $rate = 0;
            }                            
        } else {
            $rate = 1;
        }
        return $rate;
    }

    // BEGIN BALANCE 
    public function begin_balance($supplier_id, $filter_list, $initial_balance)
    {
        $filter_from        = $filter_list["filter_from"];
        $filter_to          = $filter_list["filter_to"];
        $filter_supplier    = $filter_list["filter_supplier"];
        $filter_posting_no  = $filter_list["filter_posting_no"];
        $filter_document_no = $filter_list["filter_document_no"];
        $filter_invoice_no  = $filter_list["filter_invoice_no"];
        $filter_currency    = $filter_list["filter_currency"];
        $filter_status      = $filter_list["filter_status"];
        $filter_display     = $filter_list["filter_display"];
        
        // Get data Supplier
        $supplier = $this->db->select('*')->from("suppliers")->like('id', $supplier_id, 'both')->get()->row();
        if (empty($supplier)) {
            return 0; // Kembalikan 0 jika supplier tidak ditemukan
        }
        $supplier_id = $supplier->id;
        
        // Get account_number yang termasuk ke REPORT AP
        $get_account_numbers = $this->db->select('account_number')->from('account_coa')->where('report_ap', 1)->get()->result_array();
        $account_numbers = array_column($get_account_numbers, 'account_number'); 
        if (empty($account_numbers)) {
            // Jika tidak ada akun AP, saldo awal adalah saldo supplier (manual)
            return (float)$initial_balance;
        }
        
        // Siapkan array untuk WHERE IN
        if (empty($account_numbers)) {
            $account_numbers = ['NON_EXISTING_ACCOUNT']; 
        }
        
        // Inisialisasi variabel untuk Query Builder
        $this->db->reset_query();

        // Query 1: Purchase Invoices (PI) - Mengambil Kredit (Hutang Bertambah)
        $this->db->select('SUM(b.local_debit) AS debit_pi, SUM(b.local_credit) AS credit_pi');
        $this->db->select('SUM(b.original_debit) AS original_debit_pi, SUM(b.original_credit) AS original_credit_pi');
        $this->db->from('purchase_invoices a');
        $this->db->join('journal_postings b', 'a.number = b.document_no');
        if (!empty($account_numbers)) {
            $this->db->where_in('b.account_number', $account_numbers);
        }
        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->where('b.journal_date <', $filter_from);
        $this->db->where('b.modul', 'PURCHASE INVOICING'); // Hanya modul Purchase Invoicing

        if (!empty($filter_currency)) {
            $this->db->where('a.currency', $filter_currency); // Hanya filter, tidak ada konversi
        }
        $query_1 = $this->db->get()->row();
        $this->db->reset_query();
        
        // Query 2: AP Payments (AP) - Mengambil Debit (Hutang Berkurang)
        $this->db->select('SUM(b.local_debit) AS debit_payment, SUM(b.local_credit) AS credit_payment');
        $this->db->select('SUM(b.original_debit) AS original_debit_payment, SUM(b.original_credit) AS original_credit_payment');
        $this->db->from('ap_payments a');
        $this->db->join('journal_postings b', 'a.payment_no = b.document_no');
        if (!empty($account_numbers)) {
            $this->db->where_in('b.account_number', $account_numbers);
        }
        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->where('b.journal_date <', $filter_from);
        $this->db->where('b.modul', 'AP PAYMENT'); // Hanya modul AP Payment
        
        if (!empty($filter_currency)) {
            $this->db->where('a.currency', $filter_currency); // Hanya filter, tidak ada konversi
        }
        $query_2 = $this->db->get()->row();
        $this->db->reset_query();

        // === Perhitungan Saldo Awal (Local Currency) ===
        if (!empty($filter_currency) && $filter_currency != "IDR") {
            $debit_pi = (float)($query_1->original_debit_pi ?? 0);
            $credit_pi = (float)($query_1->original_credit_pi ?? 0);
            $debit_payment = (float)($query_2->original_debit_payment ?? 0);
            $credit_payment = (float)($query_2->original_credit_payment ?? 0);
        } else {
            // Default/IDR
            $debit_pi = (float)($query_1->debit_pi ?? 0);
            $credit_pi = (float)($query_1->credit_pi ?? 0);
            $debit_payment = (float)($query_2->debit_payment ?? 0);
            $credit_payment = (float)($query_2->credit_payment ?? 0);
        }

        // Total Transaksi Kumulatif Sebelum Periode (Kredit Hutang Bertambah, Debit Hutang Berkurang)
        $total_credit_old = $credit_pi + $credit_payment;
        $total_debit_old  = $debit_pi + $debit_payment;

        // Hitung Saldo Jurnal Bersih (Net Journal Activity) sebelum periode filter
        $net_journal_activity = $total_credit_old - $total_debit_old;

        // --- LOGIKA BALANCE ---
        // Cek apakah tanggal filter_from adalah tanggal 1 Januari (Awal Tahun)
        $is_start_of_year = (date('m-d', strtotime($filter_from)) === '01-01');

        if ($is_start_of_year) {
            // KETENTUAN 1: Periode Januari (Awal Tahun)
            // Saldo Awal = Initial Balance (dari account_balance_suppliers)
            // Namun, transaksi sebelum 01 Jan tetap harus diperhitungkan jika ada
            // $initial_balance adalah Saldo per 31 Des tahun lalu.
            $begin_balance = (float)$initial_balance + $net_journal_activity;

        } else {
            // KETENTUAN 2 & 3: Periode Februari, Maret, dst.
            // Saldo Awal = Saldo Akhir Bulan Sebelumnya.
            $begin_balance = (float)$initial_balance + $net_journal_activity;
        }

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
            header("Content-Disposition: attachment; filename=report_ap_$format.xls");
        }

        $filter_from        = base64_decode($this->input->get('filter_from')) ?? '';
        $filter_to          = base64_decode($this->input->get('filter_to')) ?? '';
        $filter_supplier    = base64_decode($this->input->get('filter_supplier')) ?? null;
        $filter_posting_no  = base64_decode($this->input->get("filter_posting_no")) ?? null;
        $filter_document_no = base64_decode($this->input->get("filter_document_no")) ?? null;
        $filter_invoice_no  = base64_decode($this->input->get("filter_invoice_no")) ?? null;
        $filter_currency    = $this->input->get('filter_currency') ?? null;
        $filter_status      = $this->input->get('filter_status') ?? null;
        $filter_source      = $this->input->get('filter_source') ?? null;
        $filter_display     = $this->input->get('filter_display') ?? "Detail"; // Default: Detail

        $currency_show = !empty($filter_currency) ? $filter_currency : 'IDR'; // default showed currency = IDR

        $filter_list = [
            'filter_from'        => $filter_from,
            'filter_to'          => $filter_to,
            'filter_supplier'    => $filter_supplier,
            'filter_posting_no'  => $filter_posting_no,
            'filter_document_no' => $filter_document_no,
            'filter_invoice_no'  => $filter_invoice_no,
            'filter_currency'    => $filter_currency,
            'filter_status'      => $filter_status,
            'filter_source'      => $filter_source,
            'filter_display'     => $filter_display,
        ];

        // Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row(); 

        // Prepare HTML Report
        $html = '<html><head><title>Print Data</title></head>
            <style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                        margin: 20px;
                        zoom: 80%;
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
                            <td>&nbsp;</td>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b style="font-size:14px;">' . $config->name . '</b><br>
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="font-size: 14px; text-align: left; margin:2px;"> 
                                <span style="font-size:10px;">' . $config->address . '</span><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date : ' . date("d M Y H:i:s") . ' <br>
                    Print By : ' . $this->session->username . '  
                </div>
            </center>';
            if ($option != "excel") {
                $html .= '<br><br><br><br>'; // separator print 
            }
            $html .= '<center>
                <h3 style="margin:0;">ACCOUNT PAYABLE REPORT <i>('.$filter_display.')</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>';        
                
        $currency_title = 'LOCAL';
        if ($currency_show != 'IDR') {
            $currency_title = 'ORIGINAL';
        }

        // Header Tabel
        $detail_table_header = '<table id="customers" border="1">
                                    <tr>
                                        <th rowspan="2" width="20">No</th>
                                        <th rowspan="2" width="50">Status</th>
                                        <th rowspan="2">Supplier ID</th>
                                        <th rowspan="2">Supplier Name</th>
                                        <th rowspan="2">Source</th>
                                        <th rowspan="2">Transaction Date</th>
                                        <th rowspan="2"> <span style="color:white">_</span>Payment<span style="color:white">_</span> Due</th>
                                        <th rowspan="2">Aging Due (Days)</th>
                                        <th rowspan="2">Document No</th>
                                        <th rowspan="2">Invoice No</th>
                                        <th rowspan="2">Posting Date</th>
                                        <th rowspan="2">Posting No</th>
                                        <th rowspan="2">Payment No</th>
                                        <th rowspan="2">Account No</th>
                                        <th rowspan="2">Currency</th>
                                        <th rowspan="2">Rate</th>
                                        <th colspan="5">' . $currency_title . ' CURRENCY</th>
                                    </tr>
                                    <tr>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Balance</th>
                                        <th>Accumulated</th>
                                    </tr>';

        $summary_table_header = '<table id="customers" border="1">
                                    <thead>
                                        <tr style="background-color: #E5E5E5;">
                                            <th width="20">No</th>
                                            <th>Supplier Name</th>
                                            <th>Currency</th>
                                            <th> 
                                                ' . $currency_title . ' CURRENCY<br> 
                                                <i>Balance</i>
                                            </th>
                                        </tr>
                                    </thead>';

        $detail  = $detail_table_header;
        $summary = $summary_table_header;

        // Get Suppliers
        $this->db->select('a.*, (COALESCE(b.balance_local, 0)) as balance_local, (COALESCE(b.balance, 0)) as balance');
        $this->db->select('a.currency as currency_supplier');
        $this->db->select('b.currency as currency_balance');
        $this->db->from('suppliers a');
        $this->db->join('account_balance_suppliers b', 'a.id = b.supplier_id', 'left');
        if (!empty($filter_supplier)) {
            $this->db->where("a.id", $filter_supplier);
        }
        $this->db->group_by('a.id');
        $this->db->order_by('a.name', 'asc');
        $suppliers = $this->db->get()->result_array();
        
        $no = 1;
        $noid = 1;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        $grand_total_payment = 0;
        $grand_local_balance = 0;
        $grand_summary_total = 0; // Menggantikan grand_local_balance untuk konsistensi nama grand total
        
        // Fungsi Compare (diperlukan untuk sorting)
        function compare_trans_date($a, $b) {
            return strtotime($a['voucher_date']) - strtotime($b['voucher_date']);
        }
        
        // Get account_number yang termasuk ke REPORT AP
        $get_account_numbers = $this->db->select('account_number')->from('account_coa')->where('report_ap', 1)->get()->result_array();
        $account_numbers_array = array_column($get_account_numbers, 'account_number'); 

        // Siapkan string untuk WHERE IN di Subquery
        $account_numbers_list = "'" . implode("','", $account_numbers_array) . "'";

        if (empty($account_numbers_array)) {
            $account_numbers_array = ['NON_EXISTING_ACCOUNT_AP']; 
        } 
        
        // fix bug ketika tanggal journal pada posting_no | document_no | invoice_no berbeda dengan periode
        $is_document_filter_active = false;

        // Tetapkan tanggal cut-off dari filter_to untuk Aging Due (Days)
        $cut_off_date = new DateTime($filter_to);

        foreach ($suppliers as $supplier) 
        {
            $supplier_id = $supplier['id'];
            $supplier_name = $supplier['name'];
            
            // 3.1 Query 1: Purchase Invoices (Hutang Bertambah = Credit)
            $this->db->select("
                'PI' AS source, 
                a.number AS document_no, 
                a.invoice_no, 
                a.number as purchase_invoice,
                ap_summary.payment_no as column_payment_no,
                a.trans_date AS trans_date,
                a.due_date AS payment_due, 
                b.journal_date AS voucher_date, 
                b.number AS voucher_no,
                b.account_number, 
                a.currency,
                a.rate,
                (CASE WHEN '{$currency_show}' = 'IDR' THEN b.local_debit ELSE b.original_debit END) as local_debit,
                (CASE WHEN '{$currency_show}' = 'IDR' THEN b.local_credit ELSE b.original_credit END) as local_credit, 
                b.original_debit, 
                b.original_credit, 
                ap_summary.summary_original_debit,
                ap_summary.summary_original_credit,
                b.local_debit AS summary_local_debit,
                b.local_credit AS summary_local_credit,
                /** -- LOGIKA STATUS (CLOSED=1, OPEN=0) **/
                (CASE 
                    WHEN a.currency = 'IDR' AND '{$currency_show}' = 'IDR' THEN 
                        CASE 
                            WHEN ROUND(b.local_credit, 2) = ROUND(COALESCE(ap_summary.summary_original_debit, 0), 2) THEN 1 
                            ELSE 0 
                        END
                    ELSE 
                        CASE 
                            WHEN ROUND(b.original_credit, 2) = ROUND(COALESCE(ap_summary.summary_original_debit, 0), 2) THEN 1 
                            ELSE 0 
                        END
                END) AS status_closed_flag,
                a.status
            ", FALSE);
            $this->db->from('purchase_invoices a');
            $this->db->join(
                "(SELECT journal_date, number, currency, document_no, account_number, 
                    SUM(original_debit) AS original_debit, SUM(original_credit) AS original_credit,
                    SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE modul IN ('PURCHASE INVOICING')
                AND account_number IN ({$account_numbers_list})
                GROUP BY number, document_no, account_number) b", 
                'a.number = b.document_no', 
                'JOIN', 
                FALSE 
            );
            $this->db->join(
                /* Subquery yang menghitung total pembayaran AP per Invoice */
                "(SELECT ap.purchase_invoice, ap.supplier_id,
                    ap.payment_no, ap.currency,
                    (CASE WHEN ap.account_type = 'DEBIT' THEN ap.payment ELSE 0 END) as summary_original_debit,
                    (CASE WHEN ap.account_type = 'CREDIT' THEN ap.payment ELSE 0 END) as summary_original_credit
                FROM ap_payments ap 
                JOIN (
                    SELECT document_no, modul FROM journal_postings
                    WHERE modul = 'AP PAYMENT'
                    GROUP BY document_no
                    ) journal_ap ON journal_ap.document_no = ap.payment_no
                GROUP BY ap.purchase_invoice
                ) ap_summary",
                'ap_summary.purchase_invoice = a.number', // Relation ke nomor Invoice
                'LEFT', 
                FALSE 
            );
            $this->db->where('a.supplier_id', $supplier_id);
            // $this->db->where("b.journal_date BETWEEN '{$filter_from}' AND '{$filter_to}'");
            if (!empty($filter_posting_no) || !empty($filter_document_no) || !empty($filter_invoice_no)) {
                $is_document_filter_active = true;
                $this->db->group_start();
                if (!empty($filter_posting_no)) {
                    $this->db->like('b.number', $filter_posting_no, 'both');
                }
                if (!empty($filter_document_no)) {
                    // Gunakan OR LIKE agar filter-filter ini bekerja secara independen, fix bug posting_no berbeda dengan posting_no di document_no
                    $this->db->or_like('document_no', $filter_document_no, 'both'); 
                }
                if (!empty($filter_invoice_no)) {
                    $this->db->or_like('invoice_no', $filter_invoice_no, 'both');
                }
                $this->db->group_end();
            }
            if (!empty($filter_from) && !empty($filter_to) && !$is_document_filter_active) {
                $this->db->where('journal_date >=', $filter_from);
                $this->db->where('journal_date <=', $filter_to);
            }
            if (!empty($filter_currency) && $filter_currency !== 'IDR') {
                $this->db->where("a.currency", $filter_currency);
            }
            if (!empty($filter_status)) {
                $target_status = ($filter_status == 'OPEN') ? '0' : '1';
                $this->db->having("status_closed_flag", $target_status);
            }
            $this->db->group_by(['a.number', 'b.number']);
            $this->db->order_by('b.journal_date', 'ASC');
            $this->db->order_by('b.number', 'ASC');
            $data_1 = $this->db->get()->result_array();

            // 3.2 Query 2: AP Payments (Hutang Berkurang = Debit)
            $this->db->select("
                'AP' AS source, 
                a.payment_no AS document_no, 
                '-' AS invoice_no, 
                a.payment_no as column_payment_no,
                a.payment_date AS trans_date,
                c.due_date AS payment_due, 
                a.purchase_invoice,
                b.journal_date AS voucher_date, 
                b.number AS voucher_no,
                b.account_number,
                a.currency, 
                a.rate,
                (CASE WHEN ('{$currency_show}' = 'IDR' AND a.account_type = 'DEBIT') THEN b.local_debit ELSE b.original_debit END) as local_debit,
                (CASE WHEN ('{$currency_show}' = 'IDR' AND a.account_type = 'CREDIT') THEN b.local_credit ELSE b.original_credit END) as local_credit,
                (CASE WHEN a.account_type = 'DEBIT' THEN b.original_debit ELSE 0 END) as original_debit,
                (CASE WHEN a.account_type = 'CREDIT' THEN b.original_credit ELSE 0 END) as original_credit,
                pi_summary.summary_original_debit,
                pi_summary.summary_original_credit,
                pi_summary.summary_local_debit,
                pi_summary.summary_local_credit,
                pi_summary.invoices as invoice_no,
                (CASE 
                    WHEN a.currency = 'IDR' AND '{$currency_show}' = 'IDR' THEN 
                        CASE 
                            WHEN ROUND(b.local_debit, 2) = ROUND(COALESCE(pi_summary.summary_local_credit, 0), 2) THEN 1 
                            ELSE 0 
                        END
                    ELSE 
                        CASE 
                            WHEN ROUND(b.original_debit, 2) = ROUND(COALESCE(pi_summary.summary_original_credit, 0), 2) THEN 1 
                            ELSE 0 
                        END
                END) AS status_closed_flag,
                a.status
            ", FALSE);
            $this->db->from('ap_payments a');
            $this->db->join(
                "(SELECT journal_date, number, currency, document_no, account_number, 
                    SUM(original_debit) AS original_debit, SUM(original_credit) AS original_credit,
                    SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE modul IN ('AP PAYMENT') 
                AND account_number IN ({$account_numbers_list})
                GROUP BY number, document_no, account_number) b", 
                'a.payment_no = b.document_no', 
                'LEFT', 
                FALSE
            );
            /* Subquery yang menghitung total pembayaran AP per Invoice */
            $this->db->join(
                "(SELECT
                    t.payment_no,
                    GROUP_CONCAT(t.pi_number SEPARATOR ', ') AS invoices, 
                    SUM(t.pi_local_credit) AS summary_local_credit,     
                    SUM(t.pi_original_credit) AS summary_original_credit, 
                    SUM(t.pi_local_debit) AS summary_local_debit,     
                    SUM(t.pi_original_debit) AS summary_original_debit 
                FROM
                    (
                        SELECT 
                            pay.payment_no,
                            pi.number AS pi_number,
                            journal_ap.local_debit AS pi_local_debit,
                            journal_ap.original_debit AS pi_original_debit,
                            journal_ap.local_credit AS pi_local_credit,
                            journal_ap.original_credit AS pi_original_credit
                        FROM 
                            ap_payments pay 
                        JOIN 
                            purchase_invoices pi ON pi.number = pay.purchase_invoice 
                        JOIN 
                            journal_postings journal_ap 
                            ON journal_ap.document_no = pi.number 
                        WHERE 
                            journal_ap.modul = 'PURCHASE INVOICING' 
                            AND journal_ap.account_number IN ({$account_numbers_list})
                        GROUP BY 
                            pay.payment_no, pi.number, journal_ap.document_no
                            
                    ) t
                GROUP BY t.payment_no) pi_summary",
                'pi_summary.payment_no = a.payment_no', // Relation ke nomor Invoice
                'LEFT', 
                FALSE 
            );
            $this->db->join('purchase_invoices c', 'a.purchase_invoice = c.number', 'LEFT');
            $this->db->where('a.supplier_id', $supplier_id);
            // if ($filter_display == "Detail") { // -- Bug AP tidak muncul (Bu Nina)
            //     // Memastikan subquery di-execute secara literal dengan parameter ketiga = FALSE
            //     $this->db->where("a.purchase_invoice NOT IN (SELECT DISTINCT number FROM purchase_invoices)", NULL, FALSE);
            // }
            // $this->db->where("b.journal_date BETWEEN '{$filter_from}' AND '{$filter_to}'");
            if (!empty($filter_posting_no) || !empty($filter_document_no) || !empty($filter_invoice_no)) {
                $is_document_filter_active = true;
                $this->db->group_start();
                if (!empty($filter_posting_no)) {
                    $this->db->like('b.number', $filter_posting_no, 'both');
                }
                if (!empty($filter_document_no)) {
                    // Gunakan OR LIKE agar filter-filter ini bekerja secara independen, fix bug posting_no berbeda dengan posting_no di document_no
                    $this->db->or_like('document_no', $filter_document_no, 'both'); 
                }
                if (!empty($filter_invoice_no)) {
                    $this->db->or_like('pi_summary.invoices', $filter_invoice_no, 'both');
                }
                $this->db->group_end();
            }
            if (!empty($filter_from) && !empty($filter_to) && !$is_document_filter_active) {
                $this->db->where('journal_date >=', $filter_from);
                $this->db->where('journal_date <=', $filter_to);
            }
            if (!empty($filter_currency) && $filter_currency !== 'IDR') {
                $this->db->where("a.currency", $filter_currency);
            }
            if (!empty($filter_status)) {
                $target_status = ($filter_status == 'OPEN') ? '0' : '1';
                $this->db->having("status_closed_flag", $target_status);
            }
            $this->db->group_by(['a.payment_no', 'a.account_number']);
            $this->db->order_by('b.journal_date', 'ASC');
            $data_2 = $this->db->get()->result_array();

            // Tampil data PI atau AP saja
            if (!empty($filter_source) && $filter_source == 'PI') {
                $transactions = $data_1;
                
            } elseif (!empty($filter_source) && $filter_source == 'AP') {
                $transactions = $data_2;
            
            } else {
            // Tampil semua
            $transactions = array_merge($data_1, $data_2);
            usort($transactions, 'compare_trans_date'); // Menggunakan fungsi sort yang sudah didefinisikan sebelumnya
            
            }


            // ------- TRANSACTION START ----------
            $local_debit  = 0;
            $local_credit = 0;
            $init_balance = 0; // default balance
            $total_payment = 0;

            // Get Balance sesuai filter_currency
            $get_balance = $this->db->select('*')->from('account_balance_suppliers')->where('supplier_id', $supplier_id)->get()->row();
            if (!empty($get_balance)) {
                if ($currency_show == 'IDR') {
                    // Hanya ambil saldo IDR jika data saldo supplier memang ber-currency IDR
                    if ($get_balance->currency == 'IDR' || $get_balance->currency_local == 'IDR') {
                        $init_balance = $supplier['balance_local'] ?? 0;
                    }
                } elseif ($currency_show != 'IDR') {
                    if ($get_balance->currency == $currency_show) {
                        $init_balance = $supplier['balance'] ?? 0;
                    }
                }              
            }
            
            // Ambil Saldo Awal
            $begin_balance_local = $this->begin_balance($supplier_id, $filter_list, $init_balance);
            
            if (count($transactions) > 0 || $begin_balance_local != 0) {
                $detail .= '<tr style="background: #DEE2FF; font-weight:bold;" class="begin_balance">
                    <td colspan="15">BEGINING BALANCE ('.$supplier_name.')</td>
                    <td style="text-align:center;">' . $supplier['currency_balance'] . '</td>
                    <td style="text-align:center;">-</td>
                    <td colspan="4" style="text-align:right;">' . $this->formatNominal(@$begin_balance_local, 2, $option) . '</td>
                    </tr>';
            }

            $accumulated = $begin_balance_local;

            foreach ($transactions as $transaction) 
            {
                $document_no = $transaction['document_no'];
                $payment_due = $transaction['payment_due'];
                
                // Kolom baru : Aging Due (Bu Nina)
                $aging_due_days = 0;
                $style_aging = '';
                if ($transaction['status_closed_flag'] == "0" && !empty($payment_due) && $payment_due != '-') 
                {
                    $due_date = new DateTime($payment_due);
            
                    // Hitung selisih: Cut-off - Payment Due. (%r memberikan tanda - untuk overdue)
                    $interval = $cut_off_date->diff($due_date);
                    $days = (int)$interval->format('%r%a'); 

                    // Jika hari negatif (due_date sudah lewat cut_off), itu adalah OVERDUE.
                    // tampilkan nilai hari OVERDUE sebagai angka positif.                    
                    if ($days < 0) {
                        // Hutang sudah lewat jatuh tempo (Overdue)
                        $aging_due_days = abs($days);
                        $style_aging = 'font-weight:bold; color:red;';
                    } elseif ($days > 0) {
                        // Hutang belum jatuh tempo (Not Due Yet).
                        $aging_due_days = -$days; 
                        $style_aging = 'font-weight:bold;';
                    } else {
                        // Nol: Jatuh tempo tepat hari ini (0 hari aging)
                        $aging_due_days = 0;
                    }
                }

                // --- STYLE STATUS
                // if ($transaction['status'] == '0') {
                //     $status_display = "OPEN";
                //     $style_status = "background-color:#C8FFCC;";
                // } else if ($transaction['status'] == '1') {
                //     $status_display = "CLOSE";
                //     $style_status = "background-color:#FFC8C8;";
                // } else {
                //     $status_display = "-"; $style_status = "";
                // }
                
                if ($transaction['status_closed_flag'] == 0) {
                    $status_purchase = "OPEN";
                    $style_status_purchase = "background-color:#FFC8C8;";
                } else { // Jika 1 (CLOSED)
                    $status_purchase = "CLOSED";
                    $style_status_purchase = "background-color:#C8FFCC;";
                }

                // Logika AP: Kredit menambah hutang, Debit mengurangi hutang
                $debit_value = (float)$transaction['local_debit'];
                $credit_value = (float)$transaction['local_credit'];
                $rate_show = (float)$transaction['rate'] ?? 1;
                $payment = 0;

                // Jika closed get payment
                if ($transaction['status_closed_flag'] == 1) 
                { 
                    if ($currency_show == 'IDR') {
                        $payment = (float)$transaction['summary_local_credit'];
                    } else {
                        $payment = (float)$transaction['summary_original_credit'];
                    }

                    if ($transaction['source'] == 'PI') {
                        $debit_calc = $debit_value + $payment;
                        $credit_calc = $credit_value;
                        
                    } elseif ($transaction['source'] == 'AP') {
                        $debit_calc = $debit_value;
                        $credit_calc = $credit_value + $payment;
                    } else {
                        $debit_calc = 0;
                        $credit_calc = 0;
                    }

                } else {
                    $debit_calc = $debit_value;
                    $credit_calc = $credit_value;
                }

                $balance_local = $credit_calc - $debit_calc;
                $accumulated = $credit_value - $debit_value;

                // Jika mode Detail, cetak baris transaksi
                if ($filter_display == "Detail") {
                    $detail .= '<tr>';
                    $detail .= '<td>' . $no . '</td>';
                    $detail .= '<td style="text-align:center;' . $style_status_purchase . '">' . $status_purchase . '</td>';
                    $detail .= '<td>' . $supplier_id . '</td>';
                    $detail .= '<td>' . $supplier_name . '</td>';
                    $detail .= '<td style="text-align:center;">' . $transaction['source'] . '</td>';
                    $detail .= '<td>' . $transaction['trans_date'] . '</td>';
                    $detail .= '<td>' . $transaction['payment_due'] . '</td>';
                    $detail .= '<td style="text-align:center;' . $style_aging . '">' . $aging_due_days . '</td>';
                    $detail .= '<td>' . $transaction['document_no'] . '</td>';
                    $detail .= '<td>' . $transaction['invoice_no'] . '</td>';
                    $detail .= '<td>' . $transaction['voucher_date'] . '</td>';
                    $detail .= '<td>' . $transaction['voucher_no'] . '</td>';
                    $detail .= '<td>' . $transaction['column_payment_no']. '</td>';
                    $detail .= '<td>' . $transaction['account_number'] . '</td>';
                    $detail .= '<td style="text-align:center;">' . $currency_show . '</td>';
                    $detail .= '<td style="text-align:right;">' . $this->formatNominal($rate_show, 2, $option) . '</td>';
                    $detail .= '<td style="text-align:right;">' . $this->formatNominal($transaction['local_debit'], 2, $option) . '</td>';
                    $detail .= '<td style="text-align:right;">' . $this->formatNominal($transaction['local_credit'], 2, $option) . '</td>';
                    $detail .= '<td style="text-align:right;">' . $this->formatNo($balance_local, $option) . '</td>';
                    $detail .= '<td style="text-align:right;">' . $this->formatNo($accumulated, $option) . '</td>';
                    $detail .= '</tr>';
                }

                $no++;
                $local_debit += $debit_value;
                $local_credit += $credit_value;
                $total_payment += $payment;
            }

            
            $current_balance = ($begin_balance_local + $local_credit - $local_debit);

            if (count($transactions) > 0 || $current_balance > 0) 
            {
                $sub_total_row = '<tr style="background: #E5E5E5; font-weight:bold;">
                                        <td colspan="16">SUB TOTAL</td>
                                        <td style="text-align:right;">' . $this->formatNominal($local_debit, 2, $option) . '</td>
                                        <td style="text-align:right;">' . $this->formatNominal($local_credit, 2, $option) . '</td>
                                        <td style="text-align:right;">' . $this->formatNo($current_balance, $option) . '</td>
                                        <td style="text-align:right;">' . $this->formatNo($current_balance, $option) . '</td>
                                    </tr>
                                    <tr><td colspan="18" style="height:20px;"></td></tr>';
                
                if ($filter_display == "Detail") {
                    $detail .= $sub_total_row;
                }

                // Summary row (digunakan di mode Summary)
                $summary .= '<tbody>
                                <tr>
                                    <td>' . $noid . '</td>
                                    <td>' . $supplier_name . '</td>
                                    <td style="text-align:center;">' . $currency_show . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($current_balance, $option) . '</td>
                                </tr>';
            }
            
            $noid++;

            $grand_local_debit += $local_debit;
            $grand_local_credit += $local_credit;
            $grand_total_payment += $total_payment;
            $grand_local_balance += ($begin_balance_local + $grand_local_credit - $grand_local_debit);
            $grand_summary_total += $current_balance; 
        }

        $grand_total_row = '<tr style="background: #C3FFB4; font-weight:bold;" class="grand_total">
                                <td colspan="16">GRAND TOTAL</td>
                                <td style="text-align:right;">' . $this->formatNominal($grand_local_debit, 2, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNominal($grand_local_credit, 2, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($grand_local_balance, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($grand_summary_total, $option) . '</td>
                            </tr>';

        if ($filter_display == "Detail") {
            $detail .= $grand_total_row;
            echo $html . $detail . '</table></body></html>'; // Menutup tabel detail
        
        } elseif ($filter_display == "Summary") {
            $summary .= '<tr style="font-weight:bold;" class="grand_total">
                            <td style="text-align:right;" colspan="3">GRAND TOTAL</td>
                            <td style="text-align:right;">' . $this->formatNo($grand_summary_total, $option) . '</td>
                        </tr>
                        </tbody>';
            echo $html . $summary . '</table></body></html>'; // Menutup tabel summary
        
        } else {
            // Fallback ke Summary jika display tidak dikenal
            echo $html . $summary . '</table></body></html>';
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
        $filter_supplier = $this->input->get("filter_supplier");
        $filter_currency = $this->input->get("filter_currency");
        $filter_payment = $this->input->get("filter_payment");
        $filter_display = $this->input->get("filter_display");
        $filter_purchase_invoice = base64_decode($this->input->get("filter_purchase_invoice"));
        $period = date("Ym", strtotime($filter_to));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, (COALESCE(b.balance_local, 0)) as balance_local, (COALESCE(b.balance, 0)) as balance');
        $this->db->from('suppliers a');
        $this->db->join('account_balance_suppliers b', 'a.id = b.supplier_id', 'left');
        $this->db->like("a.id", $filter_supplier);
        $this->db->group_by('a.id');
        $this->db->order_by('a.name', 'asc');
        $suppliers = $this->db->get()->result_array();

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
                <h3 style="margin:0;">ACCOUNT PAYABLE REPORT <i>('.$filter_display.')</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>';

            $summary = '<table id="customers" border="1">
                            <tr>
                                <th width="20">No</th>
                                <th>Supplier Name</th>
                                <th>Currency</th>
                                <th>ORIGINAL CURRENCY<br><i>Balance</i></th>
                                <th>LOCAL CURRENCY<br><i>Balance</i></th>
                            </tr>';
            
            $detail = '<table id="customers" border="1">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2">Supplier Name</th>
                            <th rowspan="2">Trans Date</th>
                            <th rowspan="2">Payment Due</th>
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
            foreach ($suppliers as $supplier) {
                $supplier_id = $supplier['id'];
                $supplier_name = $supplier['name'];

                // $query = $this->db->query("SELECT a.*
                //     FROM ((SELECT a.journal_date as trans_date, '-' as due_date, a.invoice_no, a.document_no, a.account_number, a.number as voucher_no, a.currency, a.original_debit, a.original_credit, a.local_debit, a.local_credit, a.rates
                //         FROM journal_postings a
                //         WHERE a.journal_date between '$filter_from' and '$filter_to' and (a.company_name like '%$supplier_name%') and a.modul = 'PURCHASE INVOICING' and a.account_number IN ('2-1110','2-1120','2-1130','2-1140','2-1190'))
                //     UNION 
                //         (SELECT a.journal_date as trans_date, '-' as due_date, a.invoice_no, a.document_no, a.account_number, a.number as voucher_no, a.currency, a.original_debit, a.original_credit, a.local_debit, a.local_credit, a.rates
                //         FROM journal_postings a
                //         WHERE a.journal_date between '$filter_from' and '$filter_to' and (a.company_name like '%$supplier_name%') and a.modul = 'AP PAYMENT' and a.account_number IN ('2-1110','2-1120','2-1130','2-1140','2-1190'))
                //     UNION 
                //         (SELECT a.journal_date as trans_date, '-' as due_date, a.invoice_no, a.document_no, a.account_number, a.number as voucher_no, a.currency, a.original_debit, a.original_credit, a.local_debit, a.local_credit, a.rates
                //         FROM journal_postings a
                //         WHERE a.journal_date between '$filter_from' and '$filter_to' and (a.company_name like '%$supplier_name%') and a.modul in ('CURRENCY REVALUATION','ADJUSTMENT') and a.account_number IN ('2-1110','2-1120','2-1130','2-1140','2-1190'))
                //     ) a ORDER BY a.trans_date ASC");
                // $purchase_invoices = $query->result_array();

                $query = $this->db->query("SELECT a.*
                    FROM (
                        (SELECT a.trans_date, a.due_date, a.invoice_no, a.number as document_no, c.account_number, c.number as voucher_no, a.currency, c.original_debit, c.original_credit, c.local_debit, c.local_credit, c.rates
                        FROM purchase_invoices a
                        JOIN (SELECT number, document_no, account_number, rates, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit, SUM(original_debit) as original_debit, SUM(original_credit) as original_credit FROM 
                            journal_postings WHERE account_number IN ('2-1110','2-1120','2-1130','2-1140','2-1190') GROUP BY number, document_no, account_number) c ON a.number = c.document_no
                        WHERE a.trans_date between '$filter_from' and '$filter_to' and a.supplier_id = '$supplier_id'
                        ) 
                    UNION 
                        (SELECT a.payment_date as trans_date, '-' as due_date, CONCAT(a.purchase_invoice, ' | ', a.supplier_invoice) as invoice_no, a.payment_no as document_no, c.account_number, c.number as voucher_no, a.currency, c.original_debit, c.original_credit, c.local_debit, c.local_credit, c.rates
                        FROM ap_payments a
                        JOIN (SELECT number, document_no, account_number, rates, SUM(local_debit) as local_debit, SUM(local_credit) as local_credit, SUM(original_debit) as original_debit, SUM(original_credit) as original_credit FROM 
                            journal_postings WHERE account_number IN ('2-1110','2-1120','2-1130','2-1140','2-1190') GROUP BY number, document_no, account_number) c ON a.payment_no = c.document_no
                        WHERE a.payment_date between '$filter_from' and '$filter_to' and a.supplier_id = '$supplier_id' GROUP BY a.payment_no
                        )
                    UNION 
                        (SELECT a.journal_date as trans_date, '-' as due_date, a.invoice_no, a.document_no, a.account_number, a.number as voucher_no, a.currency, SUM(a.original_debit) as original_debit, SUM(a.original_credit) as original_credit, SUM(a.local_debit) as local_debit, SUM(a.local_credit) as local_credit, a.rates
                        FROM journal_postings a
                        WHERE a.journal_date between '$filter_from' and '$filter_to' and (a.company_name like '%$supplier_name%') and a.modul in ('CURRENCY REVALUATION','ADJUSTMENT') and a.account_number IN ('2-1110','2-1120','2-1130','2-1140','2-1190')
                        )
                    ) a GROUP BY a.voucher_no, a.document_no ORDER BY a.trans_date ASC");
                $purchase_invoices = $query->result_array();
                
                $original_debit = 0;
                $original_credit = 0;
                $original_balance = 0;
                $local_debit = 0;
                $local_credit = 0;
                $local_balance = 0;

                $this->db->select('COALESCE(SUM(local_debit) + SUM(local_credit), 0) as local_pi, COALESCE(SUM(original_debit) + SUM(original_credit), 0) as original_pi');
                $this->db->from('(SELECT DISTINCT supplier_id, number, trans_date FROM purchase_invoices) a');
                $this->db->join("journal_postings b", "a.number = b.document_no and b.account_number IN ('2-1110','2-1120','2-1130','2-1140','2-1190')");
                $this->db->where('a.supplier_id', $supplier_id);
                $this->db->where('a.trans_date <', $filter_from);
                $pi_begin = $this->db->get()->row();

                $this->db->select('COALESCE(SUM(a.local_credit) - SUM(a.local_debit), 0) as local_re, COALESCE(SUM(a.original_credit) - SUM(a.original_debit), 0) as original_re');
                $this->db->from('journal_postings a');
                $this->db->where("a.journal_date <", $filter_from);
                $this->db->where_in("a.modul", array('CURRENCY REVALUATION','ADJUSTMENT'));
                $this->db->where_in("a.account_number", array('2-1110','2-1120','2-1130','2-1140','2-1190'));
                $this->db->like("a.company_name", $supplier_name);
                $revaluation_begin = $this->db->get()->row();
                
                $this->db->select('COALESCE(SUM(local_debit) + SUM(local_credit), 0) as local_ap, COALESCE(SUM(original_debit) + SUM(original_credit), 0) as original_ap');
                $this->db->from('(SELECT DISTINCT supplier_id, payment_no, payment_date FROM ap_payments) a');
                $this->db->join("journal_postings b", "a.payment_no = b.document_no and b.account_number IN ('2-1110','2-1120','2-1130','2-1140','2-1190')");
                $this->db->where('a.supplier_id', $supplier_id);
                $this->db->where('a.payment_date <', $filter_from);
                $ap_begin = $this->db->get()->row();

                if(@$supplier['balance'] > 0 || $pi_begin->original_pi > 0 || $ap_begin->original_ap > 0 || $revaluation_begin->original_re > 0){
                    $begin_balance = (@$supplier['balance'] + ($pi_begin->original_pi + $revaluation_begin->original_re - $ap_begin->original_ap));
                }else{
                    $begin_balance = 0;
                }

                if(@$supplier['balance'] > 0 || $pi_begin->local_pi > 0 || $ap_begin->local_ap > 0 || $revaluation_begin->local_re > 0){
                    $begin_balance_local = (@$supplier['balance_local'] + ($pi_begin->local_pi + $revaluation_begin->local_re - $ap_begin->local_ap));
                }else{
                    $begin_balance_local = 0;
                }

                if(count($purchase_invoices) > 0){
                    $detail .= '  <tr style="background: #DEE2FF; font-weight:bold;">
                                    <td colspan="11">BEGINING BALANCE</td>
                                    <td style="text-align:right;">'.number_format(@$begin_balance, 2).'</td>
                                    <td colspan="3"></td>
                                    <td style="text-align:right;">'.number_format(@$begin_balance_local, 2).'</td>
                                </tr>';
                }

                foreach ($purchase_invoices as $purchase_invoice) {
                    if(trim($purchase_invoice['currency']) != "IDR"){
                        $original_debit2 = $purchase_invoice['original_debit'];
                        $original_credit2 = $purchase_invoice['original_credit'];
                    }else{
                        $original_debit2 = $purchase_invoice['local_debit'];;
                        $original_credit2 = $purchase_invoice['local_credit'];;
                    }

                    if((@$begin_balance - $original_debit2 + $original_credit2) >= 0){
                        $balance_original = number_format(@$begin_balance - $original_debit2 + $original_credit2, 2);
                    }else{
                        $balance_original = "<span style='color:red;'>(".number_format(abs(@$begin_balance - $original_debit2 + $original_credit2), 2).")</span>";
                    }

                    if((@$begin_balance_local - $purchase_invoice['local_debit'] + $purchase_invoice['local_credit']) >= 0){
                        $balance_local = number_format(@$begin_balance_local - $purchase_invoice['local_debit'] + $purchase_invoice['local_credit'], 2);
                    }else{
                        $balance_local = "<span style='color:red;'>(".number_format(abs(@$begin_balance_local - $purchase_invoice['local_debit'] + $purchase_invoice['local_credit']), 2).")</span>";
                    }

                    $detail .= '  <tr>
                                    <td>'.$no.'</td>
                                    <td>'.$supplier_name.'</td>
                                    <td>'.$purchase_invoice['trans_date'].'</td>
                                    <td>'.$purchase_invoice['due_date'].'</td>
                                    <td>'.$purchase_invoice['document_no'].'</td>
                                    <td>'.$purchase_invoice['invoice_no'].'</td>
                                    <td>'.$purchase_invoice['voucher_no'].'</td>
                                    <td>'.$purchase_invoice['account_number'].'</td>
                                    <td>'.$purchase_invoice['currency'].'</td>
                                    <td style="text-align:right;">'.number_format($original_debit2, 2).'</td>
                                    <td style="text-align:right;">'.number_format($original_credit2, 2).'</td>
                                    <td style="text-align:right;">'.$balance_original.'</td>
                                    <td style="text-align:right;">'.number_format($purchase_invoice['rates'], 2).'</td>
                                    <td style="text-align:right;">'.number_format($purchase_invoice['local_debit'], 2).'</td>
                                    <td style="text-align:right;">'.number_format($purchase_invoice['local_credit'], 2).'</td>
                                    <td style="text-align:right;">'.$balance_local.'</td>
                                </tr>';

                    $no++;
                    $original_debit += $original_debit2;
                    $original_credit += $original_credit2;
                    $original_balance += (@$begin_balance - $original_debit2 + $original_credit2);
                    $local_debit += $purchase_invoice['local_debit'];
                    $local_credit += $purchase_invoice['local_credit'];
                    $local_balance += (@$begin_balance_local - $purchase_invoice['local_debit'] + $purchase_invoice['local_credit']);
                    $begin_balance = (@$begin_balance - $original_debit2 + $original_credit2);
                    $begin_balance_local = (@$begin_balance_local - $purchase_invoice['local_debit'] + $purchase_invoice['local_credit']);
                }
                
                if(count($purchase_invoices) > 0){
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

                    $detail .= '  <tr style="background: #E5E5E5; font-weight:bold;">
                                    <td colspan="9">SUB TOTAL</td>
                                    <td style="text-align:right;">'.number_format($original_debit, 2).'</td>
                                    <td style="text-align:right;">'.number_format($original_credit, 2).'</td>
                                    <td style="text-align:right;">'.$balance_original.'</td>
                                    <td></td>
                                    <td style="text-align:right;">'.number_format($local_debit, 2).'</td>
                                    <td style="text-align:right;">'.number_format($local_credit, 2).'</td>
                                    <td style="text-align:right;">'.$balance_local.'</td>
                                </tr>
                                <tr>
                                    <td colspan="16" style="height:20px;"></td>
                                </tr>';
                }

                if($begin_balance_local != 0){

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
                                        <td>'.$supplier['name'].'</td>
                                        <td>'.$supplier['currency'].'</td>
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
