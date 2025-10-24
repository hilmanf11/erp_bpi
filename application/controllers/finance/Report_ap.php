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
    public function begin_balance($supplier_name, $filter_list, $initial_balance)
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
        $supplier = $this->db->select('*')->from("suppliers")->like('name', $supplier_name, 'both')->get()->row();
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
        $this->db->from('purchase_invoices a');
        $this->db->join('journal_postings b', 'a.number = b.document_no');
        if (!empty($account_numbers)) {
            $this->db->where_in('b.account_number', $account_numbers);
        }
        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->where('b.journal_date <', $filter_from);
        $this->db->where('b.modul', 'PURCHASE INVOICING'); // Hanya modul Purchase Invoicing
        $this->db->like('a.status', $filter_status, 'both');
        if (!empty($filter_currency)) {
            $this->db->where('a.currency', $filter_currency); // Hanya filter, tidak ada konversi
        }
        $query_1 = $this->db->get()->row();
        $this->db->reset_query();
        
        // Query 2: AP Payments (AP) - Mengambil Debit (Hutang Berkurang)
        $this->db->select('SUM(b.local_debit) AS debit_payment, SUM(b.local_credit) AS credit_payment');
        $this->db->from('ap_payments a');
        $this->db->join('journal_postings b', 'a.payment_no = b.document_no');
        if (!empty($account_numbers)) {
            $this->db->where_in('b.account_number', $account_numbers);
        }
        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->where('b.journal_date <', $filter_from);
        $this->db->where('b.modul', 'AP PAYMENT'); // Hanya modul AP Payment
        $this->db->like('a.status', $filter_status, 'both');
        if (!empty($filter_currency)) {
            $this->db->where('a.currency', $filter_currency); // Hanya filter, tidak ada konversi
        }
        $query_2 = $this->db->get()->row();
        $this->db->reset_query();
        
        // === Perhitungan Saldo Awal (Local Currency) ===
        $local_debit_pi    = (float)($query_1->debit_pi ?? 0);
        $local_credit_pi   = (float)($query_1->credit_pi ?? 0);
        $local_debit_payment = (float)($query_2->debit_payment ?? 0);
        $local_credit_payment = (float)($query_2->credit_payment ?? 0);

        // Total Kredit Lama (Hutang Bertambah)
        // Hutang bertambah melalui Credit PI dan Credit Payment (jika ada jurnal reversal/adjustment)
        $total_credit_old = $local_credit_pi; 

        // Total Debit Lama (Hutang Berkurang)
        // Hutang berkurang melalui Debit PI (jika ada jurnal reversal/adjustment) dan Debit Payment
        $total_debit_old = $local_debit_pi + $local_debit_payment;

        // Logika AP (Hutang): Saldo Manual + Kredit - Debit
        $begin_balance = $initial_balance + $total_credit_old - $total_debit_old;
        
        return $begin_balance;
    }

    public function formatNo($amount, $option = ""){
        if ($amount >= 0) {
            return $this->formatnominal(@$amount, 2, $option);
        } else {
            return "<span style='color:red;'>(" . $this->formatnominal(abs($amount), 2, $option) . ")</span>";
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
        // --- Bagian 1: Inisialisasi & Pengambilan Filter ---
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
        $filter_display     = $this->input->get('filter_display') ?? "Detail"; // Default: Detail

        $filter_list = [
            'filter_from'        => $filter_from,
            'filter_to'          => $filter_to,
            'filter_supplier'    => $filter_supplier,
            'filter_posting_no'  => $filter_posting_no,
            'filter_document_no' => $filter_document_no,
            'filter_invoice_no'  => $filter_invoice_no,
            'filter_currency'    => $filter_currency,
            'filter_status'      => $filter_status,
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
                        #customers tr:nth-child(even) {
                            background-color: #ededed !important;
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
                <h3 style="margin:0;">ACCOUNT PAYABLE REPORT <i>('.$filter_display.')</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>';

    
        // --- Bagian 2: Persiapan Data & Format Laporan ---
        
        // 2.1 Get Suppliers
        $this->db->select('a.*, (COALESCE(b.balance_local, 0)) as balance_local, (COALESCE(b.balance, 0)) as balance');
        $this->db->from('suppliers a');
        $this->db->join('account_balance_suppliers b', 'a.id = b.supplier_id', 'left');
        if (!empty($filter_supplier)) {
            $this->db->where("a.id", $filter_supplier);
        }
        if (!empty($filter_currency)) {
            $this->db->where("a.currency", $filter_currency);
            $this->db->or_where("b.currency", $filter_currency);
        }
        $this->db->group_by('a.id');
        $this->db->order_by('a.name', 'asc');
        $suppliers = $this->db->get()->result_array();

        // 2.2 Get account_number yang termasuk ke REPORT AP
        $get_account_numbers = $this->db->select('account_number')->from('account_coa')->where('report_ap', 1)->get()->result_array();
        $account_numbers_array = array_column($get_account_numbers, 'account_number'); 
        if (empty($account_numbers_array)) {
            $account_numbers_array = ['NON_EXISTING_ACCOUNT_AP']; 
        } 
        $account_numbers_list = "'" . implode("','", $account_numbers_array) . "'";

        // Fungsi Compare (diperlukan untuk sorting)
        function compare_trans_date($a, $b) {
            return strtotime($a['voucher_date']) - strtotime($b['voucher_date']);
        }
                
        // 2.3 Persiapan Kerangka Tabel
        $detail_table_header = '<table id="customers" border="1">
                                    <tr>
                                        <th rowspan="2" width="20">No</th>
                                        <th rowspan="2" width="50">Status</th>
                                        <th rowspan="2">Supplier ID</th>
                                        <th rowspan="2">Supplier Name</th>
                                        <th rowspan="2">Source</th>
                                        <th rowspan="2">Transaction Date</th>
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

        $summary_table_header = '<table id="customers" border="1">
                                    <thead>
                                        <tr style="background-color: #E5E5E5;">
                                            <th width="20">No</th>
                                            <th>Supplier Name</th>
                                            <th>Currency</th>
                                            <th>LOCAL CURRENCY<br><i>Balance</i></th>
                                        </tr>
                                    </thead>';

        $detail  = $detail_table_header;
        $summary = $summary_table_header;
        
        $no = 1;
        $noid = 1;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        $grand_summary_total = 0; // Menggantikan grand_local_balance untuk konsistensi nama grand total
        
        // --- Bagian 3: Loop Per Supplier & Pengambilan Transaksi ---
        
        // fix bug ketika tanggal journal pada posting_no | document_no | invoice_no berbeda dengan periode
        $is_document_filter_active = false;

        foreach ($suppliers as $supplier) 
        {
            $supplier_id = $supplier['id'];
            $supplier_name = $supplier['name'];
            
            // 3.1 Query 1: Purchase Invoices (Hutang Bertambah = Credit)
            $this->db->select("
                'PI' AS source, 
                a.number AS document_no, 
                a.invoice_no, 
                a.trans_date AS trans_date,
                a.due_date AS payment_due, 
                b.journal_date AS voucher_date, 
                b.number AS voucher_no,
                b.account_number, 
                a.currency, 
                b.local_debit, 
                b.local_credit, 
                a.status
            ", FALSE);
            $this->db->from('purchase_invoices a');
            $this->db->join(
                "(SELECT journal_date, number, currency, document_no, account_number, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE modul IN ('PURCHASE INVOICING')
                AND account_number IN ({$account_numbers_list})
                GROUP BY number, document_no, account_number) b", 
                'a.number = b.document_no', 
                'JOIN', 
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
            if (!empty($filter_currency)) {
                $this->db->group_start();
                $this->db->where("a.currency", $filter_currency);
                $this->db->or_where("b.currency", $filter_currency);
                $this->db->group_end();
            }
            if ($filter_status != null) {
                $this->db->where("a.status", $filter_status);
            }
            $this->db->group_by(['a.number', 'b.number']);
            $this->db->order_by('b.journal_date', 'ASC');
            $data_1 = $this->db->get()->result_array();

            // 3.2 Query 2: AP Payments (Hutang Berkurang = Debit)
            $this->db->select("
                'AP' AS source, 
                a.payment_no AS document_no, 
                '-' AS invoice_no, 
                a.payment_date AS trans_date,
                '-' AS payment_due, 
                b.journal_date AS voucher_date, 
                b.number AS voucher_no,
                b.account_number, a.currency, 
                (CASE WHEN a.account_type = 'DEBIT' THEN b.local_debit ELSE 0 END) as local_debit,
                (CASE WHEN a.account_type = 'CREDIT' THEN b.local_credit ELSE 0 END) as local_credit, 
                a.status
            ", FALSE);
            $this->db->from('ap_payments a');
            $this->db->join(
                "(SELECT journal_date, number, currency, document_no, account_number, SUM(local_debit) AS local_debit, SUM(local_credit) AS local_credit 
                FROM journal_postings 
                WHERE modul IN ('AP PAYMENT') 
                AND account_number IN ({$account_numbers_list})
                GROUP BY number, document_no, account_number) b", 
                'a.payment_no = b.document_no', 
                'LEFT', 
                FALSE
            );
            $this->db->join('purchase_invoices c', 'a.purchase_invoice = c.number', 'LEFT');
            $this->db->where('a.supplier_id', $supplier_id);
            if ($filter_display == "Detail") {
                // Memastikan subquery di-execute secara literal dengan parameter ketiga = FALSE
                $this->db->where("a.purchase_invoice NOT IN (SELECT DISTINCT number FROM purchase_invoices)", NULL, FALSE);
            }
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
            if (!empty($filter_currency)) {
                $this->db->group_start();
                $this->db->where("a.currency", $filter_currency);
                $this->db->or_where("b.currency", $filter_currency);
                $this->db->group_end();
            }
            if ($filter_status != null) {
                $this->db->group_start();
                $this->db->where("a.status", $filter_status); // Status Payment (tabel a)
                $this->db->or_where("c.status", $filter_status); // Status Invoice terkait (tabel c)
                $this->db->group_end();
            }
            $this->db->group_by(['a.payment_no', 'a.account_number']);
            $this->db->order_by('b.journal_date', 'ASC');
            $data_2 = $this->db->get()->result_array();
            
            // Gabungkan dan urutkan seperti skrip AR
            $transactions = array_merge($data_1, $data_2);
            usort($transactions, 'compare_trans_date'); // Menggunakan fungsi sort yang sudah didefinisikan sebelumnya

            $local_debit  = 0;
            $local_credit = 0;
            
            // Ambil Saldo Awal
            $begin_balance_local = $this->begin_balance($supplier_id, $filter_list, @$supplier['balance_local']);

            // --- Bagian 4: Loop Transaksi & Format Detail ---
            
            $accumulated = $begin_balance_local;

            // Tampilkan Begin Balance
            // if (count($transactions) > 0 || $begin_balance_local != 0) {
            if (count($transactions) > 0) {
                $begin_row = '<tr style="background: #DEE2FF; font-weight:bold;" class="begin_balance">
                                    <td colspan="13">BEGINING BALANCE ('.$supplier_name.')</td>
                                    <td colspan="4" style="text-align:right;">' . $this->formatNominal(@$begin_balance_local, 2, $option) . '</td>
                                </tr>';
                
                if ($filter_display == "Detail") {
                    $detail .= $begin_row;
                }
            }

            foreach ($transactions as $transaction) 
            {
                // Logika AP: Kredit menambah hutang, Debit mengurangi hutang
                $debit_value = (float)$transaction['local_debit'];
                $credit_value = (float)$transaction['local_credit'];

                $balance_local = ($credit_value - $debit_value); // Saldo per baris transaksi
                $accumulated += $balance_local;
                
                // Penentuan Status (Disimpilifikasi mengikuti logika AR/AP: Open/Close)
                if ($transaction['status'] == '0') {
                    $status_display = "OPEN";
                    $style_status = "background-color:#C8FFCC;";
                } else if ($transaction['status'] == '1') {
                    $status_display = "CLOSE";
                    $style_status = "background-color:#FFC8C8;";
                } else {
                    $status_display = "-"; $style_status = "";
                }
                
                // Jika mode Detail, cetak baris transaksi
                if ($filter_display == "Detail") {
                    $detail .= '<tr>
                                    <td>' . $no . '</td>
                                    <td style="text-align:center;' . $style_status . '">' . $status_display . '</td>
                                    <td>' . $supplier_id . '</td>
                                    <td>' . $supplier_name . '</td>
                                    <td>' . $transaction['source'] . '</td>
                                    <td>' . $transaction['trans_date'] . '</td>
                                    <td>' . $transaction['payment_due'] . '</td>
                                    <td>' . $transaction['document_no'] . '</td>
                                    <td>' . $transaction['invoice_no'] . '</td>
                                    <td>' . $transaction['voucher_date'] . '</td>
                                    <td>' . $transaction['voucher_no'] . '</td>
                                    <td>' . $transaction['account_number'] . '</td>
                                    <td style="text-align:center;">' . $transaction['currency'] . '</td>
                                    <td style="text-align:right;">' . $this->formatNominal($debit_value, 2, $option) . '</td>
                                    <td style="text-align:right;">' . $this->formatNominal($credit_value, 2, $option) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_local, $option) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated, $option) . '</td>
                                </tr>';
                }

                $no++;
                $local_debit += $debit_value;
                $local_credit += $credit_value;
            }

            // --- Bagian 5: Sub Total & Summary ---
            
            $current_balance = ($begin_balance_local + $local_credit - $local_debit);

            if (count($transactions) > 0 || $current_balance != 0) 
            {
                $sub_total_row = '<tr style="background: #E5E5E5; font-weight:bold;">
                                        <td colspan="13">SUB TOTAL</td>
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
                                    <td style="text-align:center;">' . $supplier['currency'] . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($current_balance, $option) . '</td>
                                </tr>';
            }
            
            $noid++;
            $grand_local_debit += $local_debit;
            $grand_local_credit += $local_credit;
            $grand_summary_total += $current_balance; 
        }

        // --- Bagian 6: Grand Total dan Output ---
        $grand_total_row = '<tr style="background: #C3FFB4; font-weight:bold;" class="grand_total">
                                <td colspan="13">GRAND TOTAL</td>
                                <td style="text-align:right;">' . $this->formatNominal($grand_local_debit, 2, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNominal($grand_local_credit, 2, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($grand_summary_total, $option) . '</td>
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

    public function print_detail($option = "") 
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_ap_$format.xls");
        }

        $filter_from        = base64_decode($this->input->get('filter_from') ?? '');
        $filter_to          = base64_decode($this->input->get('filter_to') ?? '');
        $filter_supplier    = base64_decode($this->input->get('filter_supplier')) ?? null;
        $filter_posting_no  = base64_decode($this->input->get('filter_posting_no')) ?? null;
        $filter_document_no = base64_decode($this->input->get('filter_document_no')) ?? null;
        $filter_invoice_no  = base64_decode($this->input->get('filter_invoice_no')) ?? null;
        $filter_currency    = $this->input->get('filter_currency') ?? null;
        $filter_status      = $this->input->get('filter_status') ?? null;
        $filter_display     = $this->input->get('filter_display') ?? null;

        $filter_list = [
            'filter_from'        => $filter_from,
            'filter_to'          => $filter_to,
            'filter_supplier'    => $filter_supplier,
            'filter_posting_no'  => $filter_posting_no,
            'filter_document_no' => $filter_document_no,
            'filter_invoice_no'  => $filter_invoice_no,
            'filter_currency'    => $filter_currency,
            'filter_status'      => $filter_status,
            'filter_display'     => $filter_display,
        ];

        $currency    = (empty($filter_currency) || $filter_currency == "IDR") ? "IDR" : $filter_currency;
        $supplier_id = $filter_supplier;
        $ap_status   = (!empty($filter_status) && $filter_status == "0") ? "1" : "0";
        
        // Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Get account_number yang termasuk ke REPORT AP
        $get_account_numbers = $this->db->select('account_number')->from('account_coa')->where('report_ap', 1)->get()->result_array();
        $account_numbers = array_column($get_account_numbers, 'account_number'); // konversi tampil hanya value
        
        // Get Suppliers
        $this->db->select("a.*, 
            b.currency as supplier_currency, 
            b.account_number, 
            c.account_name, 
            (CASE WHEN '$currency' = 'IDR' THEN b.balance_local ELSE b.balance END) as balance_local");
        $this->db->from('suppliers a');
        $this->db->join('account_balance_suppliers b', 'a.id = b.supplier_id', 'left');
        $this->db->join('account_coa c', 'b.account_number = c.account_number', 'left');
        if (!empty($supplier_id)) {
            $this->db->like("a.id", $supplier_id);
        }
        if (!empty($filter_currency)) {
            $this->db->group_start();
            $this->db->where("a.currency", $filter_currency);
            $this->db->or_where("b.currency", $filter_currency);
            $this->db->group_end();
        }
        $this->db->group_by('a.id');
        $this->db->order_by('a.name', 'asc');
        $suppliers = $this->db->get()->result_array();

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
                        #customers tr:nth-child(even) {
                            background-color: #ededed !important;
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
                <h3 style="margin:0;">ACCOUNT PAYABLE REPORT <i>('.$filter_display.')</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>';

        $detail = '<table id="customers" border="1">
                        <tr>
                            <th rowspan="2" width="20">No</th>
                            <th rowspan="2" width="50">Status</th>
                            <th rowspan="2">Supplier ID</th>
                            <th rowspan="2">Supplier Name</th>
                            <th rowspan="2">Source</th>
                            <th rowspan="2">Transaction Date</th>
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
        $no = 1;
        $noid = 1;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        $grand_local_balance = 0;

        foreach ($suppliers as $supplier) 
        {
            $supplier_id = $supplier['id'];
            $supplier_name = $supplier['name'];
            $supplier_currency = $supplier['currency'] ?? $supplier['supplier_currency'];

            // fix bug ketika tanggal journal pada posting_no | document_no | invoice_no berbeda dengan periode
            $is_document_filter_active = false;
            
            //Purchase invoicing
            $this->db->select("
                number, modul, document_no, invoice_no, journal_date, account_number, currency, company_id, description, status, trans_date,
                SUM(original_debit) AS original_debit,
                SUM(original_credit) AS original_credit,
                SUM(local_debit) AS local_debit,
                SUM(local_credit) AS local_credit
            ");
            $this->db->from('journal_postings');
            $this->db->like('company_name', $supplier_name, 'both');
            if (!empty($account_numbers)) {
                $this->db->where_in('account_number', $account_numbers);
            }
            if (!empty($filter_posting_no) || !empty($filter_document_no) || !empty($filter_invoice_no)) {
                $is_document_filter_active = true;
                $this->db->group_start();
                if (!empty($filter_posting_no)) {
                    $this->db->like('number', $filter_posting_no, 'both');
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
            if (!empty($filter_currency)) {
                $this->db->where('currency', $filter_currency);
            }
            $this->db->group_by('number, document_no, account_number, modul, invoice_no, journal_date, currency, company_id, description, status, trans_date');
            $subquery_a = $this->db->get_compiled_select(); // Dapatkan string SQL dari subquery
            $this->db->reset_query(); // Reset Query Builder sebelum query utama

            $this->db->select("
                (CASE 
                    WHEN a.modul = 'PURCHASE INVOICING' THEN 'PI'
                    WHEN a.modul = 'AP PAYMENT' THEN 'AP'
                    WHEN a.modul = 'ADJUSTMENT' THEN 'AD'
                    ELSE 'NA'
                END) AS source,
                a.document_no,
                a.invoice_no,
                a.trans_date,
                a.journal_date AS posting_date,
                a.number AS voucher_no,
                a.account_number,
                a.currency,
                a.original_debit,
                a.original_credit,
                a.local_debit,
                a.local_credit,
                a.status
            ");
            $this->db->from("($subquery_a) a");
            $this->db->group_by('a.number, a.document_no, a.account_number, a.modul, a.invoice_no, a.trans_date, a.journal_date, a.currency, a.original_debit, a.original_credit, a.local_debit, a.local_credit, a.status');
            $this->db->order_by('a.journal_date', 'ASC');
            $purchase_invoices = $this->db->get()->result_array();

            $local_debit = 0;
            $local_credit = 0;

            if ($supplier_currency == $filter_currency || $filter_currency == "") {
                $begin_balance_local = $this->begin_balance($supplier_id, $filter_list, @$supplier['balance_local']);
            } else {
                $begin_balance_local = $this->begin_balance($supplier_id, $filter_list, 0);
            }

            if (count($purchase_invoices) > 0 || $begin_balance_local > 0) {
                $detail .= '  <tr style="background: #DEE2FF; font-weight:bold;">
                                    <td colspan="13">BEGINING BALANCE - '.$supplier['name'].'</td>
                                    <td colspan="4" style="text-align:right;">' . $this->formatnominal(@$begin_balance_local, 2, $option) . '</td>
                                </tr>';
            }

            $accumulated = $begin_balance_local;
            foreach ($purchase_invoices as $purchase_invoice) 
            {
                $document_no = $purchase_invoice['document_no'];

                $invoice_date = $purchase_invoice['trans_date'];
                $payment_due = "-";
                $total_payment = 0;
                
                if ($purchase_invoice['source'] == "PI") {
                    $payments = $this->db->query("SELECT payment_no, payment_date, payment, account_type FROM ap_payments WHERE purchase_invoice = '$document_no'")->result_array();
                    $purchase = $this->db->query("SELECT * FROM purchase_invoices WHERE number = '$document_no'")->row_array();

                    $invoice_date = $purchase['trans_date'] ?? '-';
                    $payment_due = $purchase['due_date'] ?? '-';
                    
                    foreach ($payments as $payment) {
                        if ($payment['account_type'] == "DEBIT") {
                            $total_payment += $payment['payment'];
                        } else {
                            $total_payment -= $payment['payment'];
                        }
                    }

                } elseif ($purchase_invoice['source'] == "AP") {
                    $payments = $this->db->query("SELECT * FROM ap_payments WHERE payment_no = '$document_no' and purchase_invoice IN (SELECT DISTINCT number FROM purchase_invoices)")->result_array();

                    foreach ($payments as $payment) {
                        if ($payment['account_type'] == "DEBIT") {
                            $total_payment -= $payment['payment'];
                        } else {
                            $total_payment += $payment['payment'];
                        }
                    }
                }

                if ($currency != "IDR") {
                    $debit_local  = $purchase_invoice['local_debit'] * $this->exchangeRates( $purchase_invoice['currency'], $purchase_invoice['trans_date']);
                    $credit_local = $purchase_invoice['local_credit'] * $this->exchangeRates( $purchase_invoice['currency'], $purchase_invoice['trans_date']);
                } else {
                    $debit_local  = $purchase_invoice['local_debit'];
                    $credit_local = $purchase_invoice['local_credit'];
                }

                if ($total_payment != 0) {
                    $balance_local = ($purchase_invoice['original_credit'] - $purchase_invoice['original_debit'] - $total_payment);
                } else {
                    $balance_local = ($purchase_invoice['original_credit'] - $purchase_invoice['original_debit']);
                }

                if (round($balance_local, 2) != 0) {
                    $status_purchase = "OPEN";
                    $style_status_purchase = "background-color:#C8FFCC;";
                } else {
                    $status_purchase = "CLOSE";
                    $style_status_purchase = "background-color:#FFC8C8;";
                }
                
                $accumulated += ($credit_local - $debit_local);

                $detail .= '  <tr>
                                <td>' . $no . '</td>
                                <td style="text-align:center;' . $style_status_purchase . '">' . $status_purchase . '</td>
                                <td>' . $supplier['id'] . '</td>
                                <td>' . $supplier['name'] . '</td>
                                <td>' . $purchase_invoice['source'] . '</td>
                                <td>' . $invoice_date . '</td>
                                <td>' . $payment_due . '</td>
                                <td>' . $purchase_invoice['document_no'] . '</td>
                                <td>' . $purchase_invoice['invoice_no'] . '</td>
                                <td>' . $purchase_invoice['posting_date'] . '</td>
                                <td>' . $purchase_invoice['voucher_no'] . '</td>
                                <td>' . $purchase_invoice['account_number'] . '</td>
                                <td>' . $currency . '</td>
                                <td style="text-align:right;">' . $this->formatnominal($debit_local, 2, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatnominal($credit_local, 2, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($balance_local, $option) . '</td>
                                <td style="text-align:right;">' . $this->formatNo($accumulated, $option) . '</td>
                            </tr>';
                $no++;
                $local_debit += $debit_local;
                $local_credit += $credit_local;
            }

            $balance_local = ($begin_balance_local + $local_credit - $local_debit);

            // jika transaksi tidak ada, maka tampil hanya supplier yang balance awal > 0
            if (empty($purchase_invoices) && $balance_local > 0) 
            {
                $detail .= '  <tr>
                    <td>' . $no . '</td>
                    <td style="text-align:center;">-</td>
                    <td>' . $supplier['id'] . '</td>
                    <td>' . $supplier['name'] . '</td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td>' . $currency . '</td>
                    <td style="text-align:right;">' . $this->formatnominal($local_debit, 2, $option) . '</td>
                    <td style="text-align:right;">' . $this->formatnominal($local_credit, 2, $option) . '</td>
                    <td style="text-align:right;">' . $this->formatNo($balance_local, $option) . '</td>
                    <td style="text-align:right;">' . $this->formatNo($accumulated, $option) . '</td>
                </tr>';
            }

            if (count($purchase_invoices) > 0 || $balance_local > 0) {
                $detail .= '    <tr style="background: #E5E5E5; font-weight:bold;">
                                    <td colspan="13">SUB TOTAL</td>
                                    <td style="text-align:right;">' . $this->formatnominal($local_debit, 2, $option) . '</td>
                                    <td style="text-align:right;">' . $this->formatnominal($local_credit, 2, $option) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($balance_local, $option) . '</td>
                                    <td style="text-align:right;">' . $this->formatNo($accumulated, $option) . '</td>
                                </tr>
                                <tr>
                                    <td colspan="17" style="height:20px;"></td>
                                </tr>';
            }

            $noid++;

            $grand_local_debit += $local_debit;
            $grand_local_credit += $local_credit;
            $grand_local_balance += ($begin_balance_local + $local_credit - $local_debit);
        }

        $detail .= '    <tr style="background: #C3FFB4; font-weight:bold;">
                            <td colspan="13">GRAND TOTAL</td>
                            <td style="text-align:right;">' . $this->formatnominal($grand_local_debit, 2, $option) . '</td>
                            <td style="text-align:right;">' . $this->formatnominal($grand_local_credit, 2, $option) . '</td>
                            <td style="text-align:right;">' .$this->formatNo($grand_local_balance, $option) . '</td>
                            <td style="text-align:right;">-</td>
                        </tr>';

        $htmlend = '</table></body></html>';

        echo $html . $detail . $htmlend;
    }

    public function print_summary($option = "") 
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_ap_$format.xls");
        }

        $filter_from        = base64_decode($this->input->get('filter_from') ?? '');
        $filter_to          = base64_decode($this->input->get('filter_to') ?? '');
        $filter_supplier    = base64_decode($this->input->get('filter_supplier')) ?? null;
        $filter_posting_no  = base64_decode($this->input->get('filter_posting_no')) ?? null;
        $filter_document_no = base64_decode($this->input->get('filter_document_no')) ?? null;
        $filter_invoice_no  = base64_decode($this->input->get('filter_invoice_no')) ?? null;
        $filter_currency    = $this->input->get('filter_currency') ?? null;
        $filter_status      = $this->input->get('filter_status') ?? null;
        $filter_display     = $this->input->get('filter_display') ?? null;

        $filter_list = [
            'filter_from'        => $filter_from,
            'filter_to'          => $filter_to,
            'filter_supplier'    => $filter_supplier,
            'filter_posting_no'  => $filter_posting_no,
            'filter_document_no' => $filter_document_no,
            'filter_invoice_no'  => $filter_invoice_no,
            'filter_currency'    => $filter_currency,
            'filter_status'      => $filter_status,
            'filter_display'     => $filter_display,
        ];

        $currency    = (empty($filter_currency) || $filter_currency == "IDR") ? "IDR" : $filter_currency;
        $supplier_id = $filter_supplier;
        $ap_status   = (!empty($filter_status) && $filter_status == "0") ? "1" : "0";
        
        // Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Get account_number yang termasuk ke REPORT AP
        $get_account_numbers = $this->db->select('account_number')->from('account_coa')->where('report_ap', 1)->get()->result_array();
        $account_numbers = array_column($get_account_numbers, 'account_number'); // konversi tampil hanya value
        
        // Get Suppliers
        $this->db->select("a.*, 
            b.currency as supplier_currency, 
            b.account_number, 
            c.account_name, 
            (CASE WHEN '$currency' = 'IDR' THEN b.balance_local ELSE b.balance END) as balance_local");
        $this->db->from('suppliers a');
        $this->db->join('account_balance_suppliers b', 'a.id = b.supplier_id', 'left');
        $this->db->join('account_coa c', 'b.account_number = c.account_number', 'left');
        if (!empty($supplier_id)) {
            $this->db->like("a.id", $supplier_id);
        }
        if (!empty($filter_currency)) {
            $this->db->group_start();
            $this->db->where("a.currency", $filter_currency);
            $this->db->or_where("b.currency", $filter_currency);
            $this->db->group_end();
        }
        $this->db->group_by('a.id');
        $this->db->order_by('a.name', 'asc');
        $suppliers = $this->db->get()->result_array();

        // Prepare HTML Report
        $html = '<html><head><title>Print Data</title></head>
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
                        background-color: #E5E5E5;
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
                <h3 style="margin:0;">ACCOUNT PAYABLE REPORT <i>('.$filter_display.')</i></h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>';

        $detail = '<table id="customers" border="1">
                    <tr>
                        <th width="20">No</th>
                        <th>Supplier Name</th>
                        <th>Currency</th>
                        <th>ORIGINAL CURRENCY<br><i>Balance</i></th>
                        <th>LOCAL CURRENCY<br><i>Balance</i></th>
                    </tr>';
        $noid = 1;
        $grand_local_debit = 0;
        $grand_local_credit = 0;
        $grand_original_balance = 0;
        $grand_local_balance = 0;

        foreach ($suppliers as $supplier) 
        {
            $supplier_id = $supplier['id'];
            $supplier_name = $supplier['name'];
            $supplier_currency = $supplier['currency'] ?? $supplier['supplier_currency'];

            //Purchase invoicing
            $this->db->select("
                number, modul, document_no, invoice_no, journal_date, account_number, currency, company_id, description, status, trans_date,
                SUM(original_debit) AS original_debit,
                SUM(original_credit) AS original_credit,
                SUM(local_debit) AS local_debit,
                SUM(local_credit) AS local_credit
            ");
            $this->db->from('journal_postings');
            if (!empty($account_numbers)) {
                $this->db->where_in('account_number', $account_numbers);
            }
            $this->db->like('company_name', $supplier_name, 'both');
            $this->db->where('journal_date >=', $filter_from);
            $this->db->where('journal_date <=', $filter_to);
            if (!empty($filter_posting_no)) {
                $this->db->like('number', $filter_posting_no, 'both');
            }
            if (!empty($filter_document_no)) {
                $this->db->like('document_no', $filter_document_no, 'both');
            }
            if (!empty($filter_invoice_no)) {
                $this->db->like('invoice_no', $filter_invoice_no, 'both');
            }
            $this->db->group_by('number, document_no, account_number, modul, invoice_no, journal_date, currency, company_id, description, status, trans_date');
            $subquery_a = $this->db->get_compiled_select(); // Dapatkan string SQL dari subquery
            $this->db->reset_query(); // Reset Query Builder sebelum query utama

            $this->db->select("
                (CASE 
                    WHEN a.modul = 'PURCHASE INVOICING' THEN 'PI'
                    WHEN a.modul = 'AP PAYMENT' THEN 'AP'
                    WHEN a.modul = 'ADJUSTMENT' THEN 'AD'
                    ELSE 'NA'
                END) AS source,
                a.document_no,
                a.invoice_no,
                a.trans_date,
                a.journal_date AS posting_date,
                a.number AS voucher_no,
                a.account_number,
                a.currency,
                a.original_debit,
                a.original_credit,
                a.local_debit,
                a.local_credit,
                a.status
            ");
            $this->db->from("($subquery_a) a");
            $this->db->group_by('a.number, a.document_no, a.account_number, a.modul, a.invoice_no, a.trans_date, a.journal_date, a.currency, a.original_debit, a.original_credit, a.local_debit, a.local_credit, a.status');
            $this->db->order_by('a.journal_date', 'ASC');
            $purchase_invoices = $this->db->get()->result_array();

            $local_debit = 0;
            $local_credit = 0;
            $original_debit = 0;
            $original_credit = 0;

            $begin_balance_local = $this->begin_balance($supplier_id, $filter_list, @$supplier['balance_local']);
            
            foreach ($purchase_invoices as $purchase_invoice) 
            {
                $document_no = $purchase_invoice['document_no'];
                $total_payment = 0;
                
                if ($purchase_invoice['source'] == "PI") {
                    $payments = $this->db->query("SELECT payment_no, payment_date, payment, account_type FROM ap_payments WHERE purchase_invoice = '$document_no'")->result_array();
                    
                    foreach ($payments as $payment) {
                        if ($payment['account_type'] == "DEBIT") {
                            $total_payment += $payment['payment'];
                        } else {
                            $total_payment -= $payment['payment'];
                        }
                    }

                } elseif ($purchase_invoice['source'] == "AP") {
                    $payments = $this->db->query("SELECT * FROM ap_payments WHERE payment_no = '$document_no' and purchase_invoice IN (SELECT DISTINCT number FROM purchase_invoices)")->result_array();

                    foreach ($payments as $payment) {
                        if ($payment['account_type'] == "DEBIT") {
                            $total_payment -= $payment['payment'];
                        } else {
                            $total_payment += $payment['payment'];
                        }
                    }
                }

                $debit_original  = $purchase_invoice['original_debit'] * $this->exchangeRates( $purchase_invoice['currency'], $purchase_invoice['trans_date']);
                $credit_original = $purchase_invoice['original_credit'] * $this->exchangeRates( $purchase_invoice['currency'], $purchase_invoice['trans_date']);
                $debit_local  = $purchase_invoice['local_debit'];
                $credit_local = $purchase_invoice['local_credit'];
                
                if ($total_payment != 0) {
                    $balance_local = ($purchase_invoice['original_credit'] - $purchase_invoice['original_debit'] - $total_payment);
                } else {
                    $balance_local = ($purchase_invoice['original_credit'] - $purchase_invoice['original_debit']);
                }

                $original_debit += $debit_original;
                $original_credit += $credit_original;
                $local_debit += $debit_local;
                $local_credit += $credit_local;
            }

            $balance_local = ($begin_balance_local + $local_credit - $local_debit);
            $balance_original = (0 + $original_credit - $original_debit); // balance original masih 0

            $detail .= '<tr>
                            <td>' . $noid++ . '</td>
                            <td>' . $supplier_name . '</td>
                            <td>' . $supplier_currency . '</td>
                            <td style="text-align:right;">' . $this->formatNo($balance_original, $option) . '</td>
                            <td style="text-align:right;">' . $this->formatNo($balance_local, $option) . '</td>
                        </tr>';
            
            $grand_local_debit += $local_debit;
            $grand_local_credit += $local_credit;
            $grand_local_balance += ($begin_balance_local + $local_credit - $local_debit);
            $grand_original_balance += (0 + $original_credit - $original_debit);
        }

        $detail .= '<tr style="background: #C3FFB4; font-weight:bold;">
                        <td colspan="3">GRAND TOTAL</td>
                        <td style="text-align:right;">' . $this->formatNo($grand_original_balance, $option) . '</td>
                        <td style="text-align:right;">' . $this->formatNo($grand_local_balance, $option) . '</td>
                    </tr>';

        $htmlend = '</table></body></html>';

        echo $html . $detail . $htmlend;
    }

}
