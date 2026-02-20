<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property CI_Output $output
 * @property Crud $crud
 * @property Convertcurrency $convertcurrency
 */
class Ap_payments extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->library('Convertcurrency');
        $this->load->model('crud');
        // //Validasi Form
        // $this->form_validation->set_rules('purchase_invoice', 'Purchase Invoice', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['menus_id'] = $this->id_menu();

            $this->load->view('template/header', $data);
            $this->load->view('finance/ap_payments');
        } else {
            redirect('error_access');
        }
    }

    public function reads($number)
    {
        $payment_no = base64_decode($number);
        $this->db->select('a.*, b.account_name, c.status as pi_status');
        $this->db->from('ap_payments a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');        
        $this->db->join('purchase_invoices c', 'a.purchase_invoice = c.number', 'left');
        $this->db->where('a.payment_no', $payment_no);
        $records = $this->db->get()->result_array();

        header('Content-Type: application/json');
        die(json_encode($records));
    }

    public function reads_existing($number)
    {
        $payment_no = base64_decode($number);
        $this->db->select('a.*, b.account_name');
        $this->db->from('ap_payments a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
        $this->db->where('a.payment_no', $payment_no);
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function readJournals($number, $journal_type_id = "", $bank_account = "")
    {
        $number = base64_decode($number);
        $reads = $this->crud->reads("ap_payment_journals", [], ["payment_no" => $number], "", "flag", "asc");

        if (count($reads) > 0) {
            echo json_encode($reads);
        }
    }

    // function readExchangeRate()
    // {
    //     $payment_date = $this->input->post('payment_date');
    //     $currency = $this->input->post('currency');
        
    //     $search_date = date("d", strtotime($payment_date));
    //     if($search_date == "31"){
    //       $payment_date = date("Y-m-d", strtotime('-1 days', strtotime($payment_date)));
    //     }
        
    //     $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($payment_date)));
    //     $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

    //     if ($exchange) {
    //         $amount = $exchange->middle;
    //     } else {
    //         $amount = 0;
    //     }

    //     echo "Rp. " . number_format($amount, 2);
    // }

    function readExchangeRate()
    {
        $payment_date = $this->input->post('payment_date');
        $currency = $this->input->post('currency');

        $this->db->select('middle, currency_from, currency_to');
        $this->db->from('exchange_rates');
        $this->db->where('currency_from', $currency);
        $this->db->where('currency_to', 'IDR');
        $this->db->where("'$payment_date' BETWEEN start_date AND end_date", null, false); // penting: raw SQL

        $query = $this->db->get()->row();

        if ($query) {
            $amount = $query->middle;
            $currency_from = $query->currency_from;
            $currency_to = $query->currency_to;
        } else {
            $amount = 0;
            $currency_from = '-';
            $currency_to = '-';
        }

        echo json_encode([
            'amount' => $amount,
            'label' => "Rate $currency_from to $currency_to: Rp. " . number_format($amount, 2)
        ]);
    }

    function getExchange($currency, $date) 
    {
        $this->db->select('*');
        $this->db->from('exchange_rates');
        $this->db->where('currency_from', $currency);
        $this->db->where('currency_to', 'IDR');
        $this->db->where("'$date' BETWEEN start_date AND end_date", null, false); // penting: raw SQL
        $exchange = $this->db->get()->row();

        if (!empty($exchange)) {
            $rate = $exchange->middle;
        } else {
            $rate = 0;
        }
        return $rate;
    }

    public function calculateJournal($journal_type_id = "", $bank_account = "")
    {
        $journal_type_id = base64_decode($journal_type_id);
        $bank_account = base64_decode($bank_account);

        $banks = $this->crud->query("SELECT a.*, b.account_name FROM account_banks a 
            JOIN account_coa b ON a.account_number = b.account_number 
            WHERE a.bank_account = '{$bank_account}'");

        $jsonDatas = json_decode(file_get_contents("json/ap_payments.json"), true);
        
        $grand_total = 0;
        $grand_total_local = 0;
        $total_payment_local_now = 0;
        $currency = "IDR"; // Default

        $arr = [];
        $flag = 1;

        // --- Ambil data per baris transaksi (Bu Nina 2026-01-22) ---
        foreach ($jsonDatas as $journal) {
            $account_number   = $journal['account_number'];
            $account_name     = $journal['account_name'];
            $currency         = $journal['currency'];
            $payment_original = $journal['payment'] ?? 0;
            $payment_date     = $journal['payment_date'];
            $trans_date       = $journal['trans_date'] ?? $journal['payment_date'];
            $description      = $journal['description'];
            $account_type     = $journal['account_type'];
            
            $debit_original  = ($account_type == "DEBIT") ? $payment_original : 0;
            $credit_original = ($account_type == "CREDIT") ? $payment_original : 0;

            // Ambil Kurs
            $exchange_trans_date = ($currency !== 'IDR') ? ($this->getExchange($currency, $trans_date) ?? 0) : 1;
            $local_debit  = round($debit_original * $exchange_trans_date, 2); 
            $local_credit = round($credit_original * $exchange_trans_date, 2); 

            // LANGSUNG MASUKKAN KE ARRAY (Tanpa pengecekan isset/merging)
            $arr[] = [
                "account_number" => $account_number,
                "account_name"   => $account_name,
                "description"    => $description,
                "currency"       => $currency,
                "exchange_rate"  => $exchange_trans_date,
                "debit"          => number_format($debit_original, 2, '.', ''),
                "credit"         => number_format($credit_original, 2, '.', ''),
                "local_debit"    => $local_debit,
                "local_credit"   => $local_credit,
                "flag"           => $flag
            ];

            // Akumulasi untuk perhitungan Grand Total & Selisih Kurs
            $grand_total += ($debit_original - $credit_original);
            $grand_total_local += ($local_debit - $local_credit);
            
            $exchange_payment_date = ($currency !== 'IDR') ? ($this->getExchange($currency, $payment_date) ?? 0) : 1;
            $total_payment_local_now += round($payment_original * $exchange_payment_date, 2);
            
            $flag++;
        }

        if($grand_total < 0){
            $debit = abs($grand_total);
            $credit = 0;
            $local_debit = abs($grand_total_local);
            $local_credit = 0;
        }else{
            $debit = 0;
            $credit = abs($grand_total);
            $local_debit = 0;
            $local_credit = abs($grand_total_local);
        }
        
        // --- Akun Bank ---
        foreach ($banks as $bank) {
            $arr[] = array(
                "account_number" => $bank->account_number,
                "account_name"   => $bank->account_name,
                "description"    => "Payment Total",
                "currency"       => "IDR",
                "exchange_rate"  => $exchange_payment_date ?? 1,
                "debit"          => $debit,
                "credit"         => $credit,
                "local_debit"    => $local_debit,
                "local_credit"   => $local_credit,
                "flag"           => $flag,
            );
            $flag++;
        }

        // --- Perhitungan Selisih Kurs (Gain/Loss) ---
        $final_local_debit  = array_sum(array_column($arr, 'local_debit'));
        $final_local_credit = array_sum(array_column($arr, 'local_credit'));
        $difference = round($final_local_debit - $final_local_credit, 2);

        if ($currency !== "IDR" && abs($difference) > 0.01) {
            $isLoss = ($difference < 0);
            $gainLossDebit  = $isLoss ? abs($difference) : 0;
            $gainLossCredit = !$isLoss ? abs($difference) : 0;

            $arr[] = [
                "account_number" => "810.150.00",
                "account_name"   => "Gain (Loss) Sales Asset / Foreign Exchange",
                "description"    => "Selisih Kurs Otomatis",
                "currency"       => "IDR",
                "exchange_rate"  => "-",
                "debit"          => 0,
                "credit"         => 0,
                "local_debit"    => $gainLossDebit,
                "local_credit"   => $gainLossCredit,
                "flag"           => $flag,
            ];
        }

        echo json_encode($arr);
    }

    // --- Journal total per Account Number dari PI, jika account_number berbeda maka di split
    public function calculateJournalPerAccount($journal_type_id = "", $bank_account = "")
    {
        $journal_type_id = base64_decode($journal_type_id);
        $bank_account = base64_decode($bank_account);

        $banks = $this->crud->query("SELECT a.*, b.account_name FROM account_banks a 
            JOIN account_coa b ON a.account_number = b.account_number 
            WHERE a.bank_account = '{$bank_account}'");

        $jsonDatas = json_decode(file_get_contents("json/ap_payments.json"), true);
        
        $grand_total = 0;
        $grand_local_credit = 0;
        $grand_local_debit = 0;
        $total_payment_local_now = 0;

        foreach ($jsonDatas as $jsonData) {
            $currency         = $jsonData['currency'];
            $payment_original = $jsonData["payment"] ?? 0;
            $payment_date     = $jsonData['payment_date'];
            $trans_date       = $jsonData['trans_date'] ?? $jsonData['payment_date'];
            $account_type     = $jsonData["account_type"] ?? "";
            
            // Hitung total pada kurs transaksi
            $exchange = ($currency !== 'IDR') ? ($this->getExchange($currency, $trans_date) ?? 0) : 1; 
            if ($account_type == "DEBIT") {
                $grand_local_debit +=  round($payment_original * $exchange, 2);
                
            } elseif ($account_type == "CREDIT") {
                $grand_local_credit += round($payment_original * $exchange, 2);
            }

            $grand_total += $payment_original;
            
            // Hitung total penerimaan di bank pada kurs saat ini 
            $exchange_payment_date = ($currency !== 'IDR') ? ($this->getExchange($currency, $payment_date) ?? 0) : 1;
            $total_payment_local_now += round($payment_original * $exchange_payment_date, 2);
        }
        
        $arr = [];
        $flag = 1;
        
        $mergedData = array();
        foreach ($jsonDatas as $journal) {
            $account_number   = $journal['account_number'];
            $account_name     = $journal['account_name'];
            $currency         = $journal['currency'];
            $payment_original = $journal['payment'];
            $trans_date       = $journal['trans_date'] ?? $journal['payment_date'];
            $description      = $journal['description'];
            $account_type     = $journal['account_type'];
            
            $debit_original  = ($account_type == "DEBIT") ? $payment_original : 0;
            $credit_original = ($account_type == "CREDIT") ? $payment_original : 0;

            $exchange_trans_date = ($currency !== 'IDR') ? ($this->getExchange($currency, $trans_date) ?? 0) : 1;
            $local_debit  = round($debit_original * $exchange_trans_date, 2); 
            $local_credit = round($credit_original * $exchange_trans_date, 2); 
            
            // Menggabungkan account_number yang sama
            if (isset($mergedData[$account_number])) {
                $mergedData[$account_number]["exchange_rate"] = $exchange_trans_date;
                $mergedData[$account_number]["debit"] += $debit_original;
                $mergedData[$account_number]["credit"] += $credit_original;
                $mergedData[$account_number]["local_debit"] += $local_debit;
                $mergedData[$account_number]["local_credit"] += $local_credit;
                
            } else {
                $mergedData[$account_number] = [
                    "account_number" => $account_number,
                    "account_name"   => $account_name,
                    "description"    => $description,
                    "currency"       => $currency,
                    "exchange_rate"  => $exchange_trans_date,
                    "debit"          => $debit_original,
                    "credit"         => $credit_original,
                    "local_debit"    => $local_debit,
                    "local_credit"   => $local_credit,
                ];
            }
        }
        
        foreach (array_values($mergedData) as $item) {
            $item['flag'] = $flag;
            $arr[] = $item;
            $flag++;
        }

        foreach ($banks as $bank) {
            $arr[] = array(
                "account_number" => $bank->account_number,
                "account_name"   => $bank->account_name,
                "currency"       => "IDR",
                "exchange_rate"  => $exchange_payment_date,
                "debit"          => "0.00",
                "credit"         => $grand_total,
                "local_debit"    => 0,
                "local_credit"   => round($total_payment_local_now, 2),
                "flag"           => $flag,
            );
        }
        $flag++;

        // Gain (Loss) Sales Asset. 810.150.00 . Foreign Exchange A/P
        $final_local_debit  = array_sum(array_column($arr, 'local_debit'));
        $final_local_credit = array_sum(array_column($arr, 'local_credit'));
        $difference = round($final_local_debit - $final_local_credit, 2);

        $gainLossDebit  = 0;
        $gainLossCredit = 0;

        if ($currency !== "IDR") {
            if (abs($difference) > 0.01) {
                if ($difference > 0) {
                    $gainLossCredit = abs($difference);
                } else {
                    $gainLossDebit = abs($difference);
                }

                $account_gain_loss = $this->db->select('*')->from('account_coa')->where('account_number', '810.150.00')->get()->row();
                    $arr[] = [
                    "account_number" => "810.150.00",
                    "account_name"   => $account_gain_loss->account_name ?? "Gain (Loss) Sales Asset",
                    "description"    => "",
                    "currency"       => "IDR",
                    "exchange_rate"  => "-",
                    "debit"          => 0,
                    "credit"         => 0,
                    "local_debit"    => $gainLossDebit,
                    "local_credit"   => $gainLossCredit,
                    "flag"           => $flag,
                ];
            }
        }

        echo json_encode($arr);
    }

    // -- belum menggunakan rate
    public function calculateJournal_existing($journal_type_id = "", $bank_account = "")
    {
        $journal_type_id = base64_decode($journal_type_id);
        $bank_account = base64_decode($bank_account);

        $banks = $this->crud->query("SELECT a.*, b.account_name FROM account_banks a 
            JOIN account_coa b ON a.account_number = b.account_number 
            WHERE a.bank_account = '$bank_account'");

        $jsonDatas = json_decode(file_get_contents("json/ap_payments.json"), true);
        $total = 0;
        $grand_total = 0;
        $flag = 1;
        $mergedData = array();
        foreach ($jsonDatas as $jsonData) {
            $account_number = $jsonData["account_number"];
            $account_name = $jsonData["account_name"];
            $account_type = $jsonData["account_type"];
            $description = $jsonData["description"];
            $total = $jsonData["payment"];
            $currency = $jsonData['currency'];
            $payment_date = $jsonData['payment_date'];

            // $search_date = date("d", strtotime($payment_date));
            // if($search_date == "31"){
            //   $payment_date = date("Y-m-d", strtotime('-1 days', strtotime($payment_date)));
            // }

            // $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($payment_date)));
            // $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

            $this->db->select('middle');
            $this->db->from('exchange_rates');
            $this->db->where('currency_from', $currency);
            $this->db->where('currency_to', 'IDR');
            $this->db->where("'$payment_date' BETWEEN start_date AND end_date", null, false); // penting: raw SQL

            $exchange = $this->db->get()->row();
            
            if ($currency != "IDR") {
                if ($exchange) {
                    $amount = ($total * $exchange->middle);
                } else {
                    $amount = 0;
                }
            } else {
                $amount = $total;
            }

            if (isset($mergedData[$account_number])) {
                // Jika nomor akun sudah ada dalam hasil penggabungan, tambahkan nilai total ke nomor akun tersebut
                if ($jsonData['account_type'] == "DEBIT") {
                    $mergedData[$account_number]["debit"] += $total;
                    $mergedData[$account_number]["local_debit"] += $amount;
                    
                    $grand_total += $total;
                } elseif ($jsonData['account_type'] == "CREDIT") {
                    $mergedData[$account_number]["credit"] += $total;
                    $mergedData[$account_number]["local_credit"] += $amount;

                    $grand_total -= $total;
                }
            } else {
                // Jika nomor akun belum ada dalam hasil penggabungan, tambahkan data baru
                if ($jsonData['account_type'] == "DEBIT") {
                    $mergedData[$account_number] = array(
                        "account_number" => $account_number,
                        "account_name" => $account_name,
                        "account_type" => $account_type,
                        "description" => $description,
                        "debit" => $total,
                        "credit" => 0,
                        "local_debit" => round($amount, 2),
                        "local_credit" => 0,
                        "flag" => $flag,
                    );

                    $grand_total += $total;
                } elseif ($jsonData['account_type'] == "CREDIT") {
                    $mergedData[$account_number] = array(
                        "account_number" => $account_number,
                        "account_name" => $account_name,
                        "account_type" => $account_type,
                        "description" => $description,
                        "debit" => 0,
                        "credit" => $total,
                        "local_debit" => 0,
                        "local_credit" => round($amount, 2),
                        "flag" => $flag,
                    );
                    $grand_total -= $total;
                }
            }

            $flag++;
        }

        $arr = array_values($mergedData);

        foreach ($banks as $bank) {
            if ($currency != "IDR") {
                if ($exchange) {
                    $amount = ($grand_total * $exchange->middle);
                } else {
                    $amount = 0;
                }
            } else {
                $amount = $grand_total;
            }

            $arr[] = array(
                "account_number" => $bank->account_number,
                "account_name" => $bank->account_name,
                "debit" => "0.00",
                "credit" => $grand_total,
                "local_debit" => 0,
                "local_credit" => round($amount, 2),
                "flag" => $flag,
            );
        }

        echo json_encode($arr);
    }

    public function readInvoiceDropdown()
    {
        $supplier_id = $this->input->get('supplier_id');
        $formMode    = $this->input->get('formMode');
        $payment_no  = $this->input->get('payment_no') ?? "";

        $this->db->select('a.number, a.journal_type_id, a.trans_date, a.invoice_no, a.due_date');
        $this->db->select("(SUM(CASE WHEN a.account_type = 'DEBIT' THEN a.total ELSE -a.total END) + a.total_vat - a.total_pph) as total_invoice", FALSE);
        $this->db->select("IFNULL(pay.total_paid, 0) as total_paid", FALSE);
        
        if ($formMode == "update" && !empty($payment_no)) {
            $this->db->select("IFNULL(SUM(this_pay.payment), 0) as current_payment_amount", FALSE);
        }

        $this->db->from('purchase_invoices a');
        $this->db->join('(SELECT purchase_invoice, SUM(payment) as total_paid FROM ap_payments GROUP BY purchase_invoice) pay', 'a.number = pay.purchase_invoice', 'LEFT');
        
        if ($formMode == "update" && !empty($payment_no)) {
            $this->db->join('ap_payments this_pay', "a.number = this_pay.purchase_invoice AND this_pay.payment_no = '$payment_no'", 'LEFT');
        }

        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->where('a.deleted', 0);

        // Form Update: Tampil yang masih Open (status 0) ATAU yang sudah ada di payment ini
        if ($formMode == "update" && !empty($payment_no)) {
            $this->db->group_start();
                $this->db->where('a.status', 0);
                $this->db->or_where('this_pay.payment_no', $payment_no);
            $this->db->group_end();
            
            $this->db->group_by('a.number');
            $this->db->having("(total_invoice > total_paid) OR (current_payment_amount > 0)");
        } else {
            // Form Add: Murni hanya yang status 0 dan belum lunas
            $this->db->where('a.status', 0);
            $this->db->group_by('a.number');
            $this->db->having('total_invoice > total_paid');
        }

        $records = $this->db->get()->result();

        foreach ($records as $key => $record) {
            $record->no = $key + 1;
            // Hitung balance sisa
            $record->balance = $record->total_invoice - $record->total_paid;
            
            // Fix balance untuk tampilan Update
            if ($formMode == "update" && isset($record->current_payment_amount)) {
                $record->balance += $record->current_payment_amount;
            }
        }

        header('Content-Type: application/json');
        echo json_encode($records);
    }

    // public function readInvoiceType()
    // {
    //     $supplier_id = $this->input->get('supplier_id');
    //     $payment_type = $this->input->get('payment_type');

    //     if ($payment_type == "PURCHASE") {
    //         $where_por = "por_no != '-'";
    //     } else {
    //         $where_por = "por_no = '-'";
    //     }

    //     $records = $this->crud->query("SELECT DISTINCT `number`, journal_type_id, trans_date, invoice_no, due_date FROM purchase_invoices WHERE supplier_id = '$supplier_id' and `status` = 0");
    //     echo json_encode($records);
    // }

    public function readInvoiceType()
    {
        $supplier_id  = $this->input->get('supplier_id');
        $formMode     = $this->input->get('formMode');

        $this->db->select('a.number, a.journal_type_id, a.trans_date, a.invoice_no, a.due_date');
        $this->db->select("(SUM(CASE WHEN a.account_type = 'DEBIT' THEN a.total ELSE -a.total END) + a.total_vat - a.total_pph) as total_invoice", FALSE);
        $this->db->select("IFNULL(pay.total_paid, 0) as total_paid", FALSE);
        $this->db->from('purchase_invoices a');
        $this->db->join('(SELECT purchase_invoice, SUM(payment) as total_paid FROM ap_payments GROUP BY purchase_invoice) pay', 'a.number = pay.purchase_invoice', 'LEFT');
        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->where('a.status', 0); // Hanya status 0 (Open)
        $this->db->where('a.deleted', 0);
        $this->db->group_by('a.number');
        // Sisa Pembayaran (Partial Paid) Balance: Total Invoice > Total yang sudah dibayar
        if ($formMode == "add") {
            $this->db->having('total_invoice > total_paid');
        }
        $records = $this->db->get()->result();

        // Tambahkan nomor urut
        foreach ($records as $key => $record) {
            $record->no = $key + 1;
            $record->balance = $record->total_invoice - $record->total_paid;
        }

        header('Content-Type: application/json');
        echo json_encode($records);
    }

    public function readInvoiceType_existing()
    {
        $supplier_id = $this->input->get('supplier_id');
        $payment_type = $this->input->get('payment_type');

        if ($payment_type == "PURCHASE") {
            $where_por = "por_no != '-'";
        } else {
            $where_por = "por_no = '-'";
        }

        $records = $this->crud->query("SELECT DISTINCT `number`, journal_type_id, trans_date, invoice_no, due_date FROM purchase_invoices WHERE supplier_id = '$supplier_id' and `status` = 0");
        
        // Tambahkan nomor urut
        $data_with_no = [];
        $no = 1;
        foreach ($records as $record) {
            $record->no = $no++; // Tambahkan nomor urut
            $data_with_no[] = $record;
        }

        echo json_encode($data_with_no);
    }

    public function readPayments($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $data = $this->crud->query("SELECT DISTINCT payment_no FROM ap_payments WHERE supplier_id = '$supplier_id' ORDER BY `payment_no` ASC");
        echo json_encode($data);
    }

    public function readInvoices($supplier_id)
    {
        $date_now = date("Y-m-t");
        $supplier_id = base64_decode($supplier_id);
        $data = $this->crud->query("SELECT DISTINCT `purchase_invoice` FROM ap_payments WHERE supplier_id = '$supplier_id' and `status` = 0 ORDER BY `purchase_invoice` ASC");
        echo json_encode($data);
    }

    public function readInvoicesUpdate($supplier_id)
    {
        $date_now = date("Y-m-t");
        $supplier_id = base64_decode($supplier_id);
        $data = $this->crud->query("SELECT DISTINCT ap.purchase_invoice, jt.name as journal_type 
            FROM ap_payments ap
            LEFT JOIN journal_types jt ON ap.journal_type_id = jt.id
            WHERE supplier_id = '$supplier_id' and ap.status = 0 
            ORDER BY ap.purchase_invoice ASC
        ");
        
        // Tambahkan nomor urut
        $data_with_no = [];
        $no = 1;
        foreach ($data as $record) {
            $record->no = $no++; // Tambahkan nomor urut
            $data_with_no[] = $record;
        }

        echo json_encode($data_with_no);
    }

    public function readDp()
    {
        $supplier_id = $this->input->post('supplier_id');
        $purchase_invoice = $this->input->post('purchase_invoice');

        $this->db->select('a.*, b.account_name');
        $this->db->from('ap_payments a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number');
        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->where_in('a.account_number', ['130.110.00','130.120.00']);
        $this->db->where("a.balance != a.payment");
        $this->db->where('a.status_dp', '0');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    // public function number($trans_date)
    // {
    //     $datenow    = "AP-" . date("Ym", strtotime(base64_decode($trans_date)));
    //     $sqlGetID   = $this->db->query("SELECT max(`payment_no`) as kode FROM ap_payments WHERE `payment_no` like '%$datenow%'");
    //     $rowID      = $sqlGetID->row();
    //     $kode       = $rowID->kode;
    //     if ($kode == NULL) {
    //         $autoID = sprintf("%04s", $kode + 1);
    //     } else {
    //         $urutan = (int) substr($kode, -4);
    //         $urutan++;
    //         $autoID = sprintf("%04s", $urutan);
    //     }
    //     echo $datenow . "-" . $autoID;
    // }

    public function number($trans_date, $bank_code = null)
    {
        if (!empty($trans_date) && !empty($bank_code)) {
            $decoded_date = base64_decode($trans_date);
            $year = date("y", strtotime($decoded_date));
            $month = date("m", strtotime($decoded_date));
            // $bank_code = base64_decode($bank_code);
            $datenow    = $bank_code."/".$month."-".$year."/"."K";
            $sqlGetID   = $this->db->query("SELECT max(`payment_no`) as kode FROM ap_payments WHERE `payment_no` like '%$datenow%'");
            $rowID      = $sqlGetID->row();
            $kode       = $rowID->kode;
            if ($kode == NULL) {
                $autoID = sprintf("%03s", $kode + 1);
            } else {
                $urutan = (int) substr($kode, 0, 3);
                $urutan++;
                $autoID = sprintf("%03s", $urutan);
            }
            echo $autoID."/".$datenow;
        }
        echo ""; // if trans_date or bank_code is not choosed
    }

    // Bug PI tidak tampil dan tanpa PI status
    public function datatablesTemp1()
    {
        $purchase_invoice = base64_decode($this->input->get('purchase_invoice'));
        $purchase_invoice_ex = explode(",", $purchase_invoice);

        $this->db->select("number, journal_type_id, invoice_no, currency, (SUM(CASE WHEN account_type = 'DEBIT' THEN total ELSE -total END) + total_vat - total_pph) as total");
        $this->db->select("trans_date, account_number");
        $this->db->from('purchase_invoices');
        $this->db->where('deleted', 0);
        // $this->db->where('status', 0); // tidak tampil saat update
        $this->db->where_in('number', $purchase_invoice_ex);
        $this->db->group_by('number');
        $this->db->order_by('number', 'asc');
        $records = $this->db->get()->result_array();

        $obj = [];
        $total_payment = 0;
        foreach ($records as $record) {
            $total_payment += $record['total'];
            $journal_type_id = $record['journal_type_id'];
            $number = $record['number'];

            $account_number = null;
            $account_name   = null;

            // -- Journal Setups
            $this->db->select('a.*, b.account_name, b.account_number');
            $this->db->from('journal_setups a');
            $this->db->join('account_coa b', 'a.account_number = b.account_number', 'LEFT');
            $this->db->where('a.journal_type_id', $journal_type_id);
            $this->db->where('a.ap_payment', 'YES');
            $journal_setup = $this->db->get()->row();
            if (!empty($journal_setup)) {
                $account_number = $journal_setup->account_number;
                $account_name   = $journal_setup->account_name;
            } else {
                // -- Jika Tidak ada setting account di journal_setups, maka ambil dari COA category=Account Payable
                $this->db->select('*');
                $this->db->from('account_coa');
                $this->db->where('account_number', $record['account_number']);
                $get_account = $this->db->get()->row();

                $pi_number = $record['number'];
                $get_account_payable = $this->crud->query("SELECT a.* 
                    FROM purchase_invoice_journals a 
                    JOIN account_coa b ON a.account_number = b.account_number 
                    JOIN account_group_details c ON c.id = b.account_group_detail_id 
                    WHERE c.name LIKE 'Account Payable%' AND b.status = 0 
                    AND a.number = '$pi_number' 
                    ORDER BY a.id");

                if (!empty($get_account_payable)) {
                    $account_number = $get_account_payable[0]->account_number;
                    $account_name   = $get_account_payable[0]->account_name;

                } else {
                    $account_number = $get_account->account_number ?? $record['account_number'];;
                    $account_name   = $get_account->account_name ?? "";
                }
            }

            // -- Exchange Rate by trans_date PI
            $exchange_rate = 0;
            if ($record['currency'] == "IDR") {
                $exchange_rate = number_format(1, 2);
            } else {
                $this->db->select('middle, currency_from, currency_to');
                $this->db->from('exchange_rates');
                $this->db->where('currency_from', $record['currency']);
                $this->db->where('currency_to', 'IDR');
                $this->db->where("'".$record['trans_date']."' BETWEEN start_date AND end_date", null, false);
                $get_rate = $this->db->get()->row();

                if ($get_rate) {
                    $exchange_rate = $get_rate->middle;
                } else {
                    $exchange_rate = number_format(0, 2);
                }
            }

            $ap_payment = $this->crud->query("SELECT purchase_invoice, SUM(payment) as payment FROM ap_payments WHERE purchase_invoice = '$number' GROUP BY purchase_invoice, account_number ORDER BY payment DESC");

            $obj[] = array(
                "trans_date"       => $record['trans_date'],
                "purchase_invoice" => $record['number'],
                "supplier_invoice" => $record['invoice_no'],
                "currency"         => $record['currency'],
                "rate"             => $exchange_rate,
                "amount"           => $record['total'],
                "balance"          => ($record['total'] - @$ap_payment[0]->payment), // jadi 0 saat update = lunas
                "payment"          => ($record['total'] - @$ap_payment[0]->payment), // jadi 0 saat update = lunas
                "account_number"   => $account_number,
                "account_name"     => $account_name,
                "account_type"     => "DEBIT",
            );
        }

        $arr['rows'] = $obj;
        $arr['total_payment'] = round($total_payment, 2);
        die(json_encode($arr));
    }

    public function datatablesTemp()
    {
        $purchase_invoice = base64_decode($this->input->get('purchase_invoice'));
        $purchase_invoice_ex = explode(",", $purchase_invoice);
        $formMode = $this->input->get('formMode') ?? 'add';

        $this->db->select("number, journal_type_id, invoice_no, currency, trans_date, account_number");
        $this->db->select("(SUM(CASE WHEN account_type = 'DEBIT' THEN total ELSE -total END) + total_vat - total_pph) as total", FALSE);
        $this->db->select("status");
        $this->db->from('purchase_invoices');
        $this->db->where('deleted', 0);
        if ($formMode == "add") {
            $this->db->where('status', 0);
        }
        $this->db->where_in('number', $purchase_invoice_ex);
        $this->db->group_by('number');
        $this->db->order_by('number', 'asc');
        $records = $this->db->get()->result_array();

        $obj = [];
        $total_payment = 0;
        foreach ($records as $record) {
            $total_payment += $record['total'];
            $journal_type_id = $record['journal_type_id'];
            $number = $record['number'];
            $pi_status = $record['status'];

            $account_number = $record['account_number'];
            $account_name   = "";

            // Get Account: Journal Setup -> Invoice Journal (AP) -> COA Default
            $this->db->select('a.account_number, b.account_name');
            $this->db->from('journal_setups a');
            $this->db->join('account_coa b', 'a.account_number = b.account_number', 'LEFT');
            $this->db->where(['a.journal_type_id' => $journal_type_id, 'a.ap_payment' => 'YES']);
            $journal_setup = $this->db->get()->row();

            if ($journal_setup) {
                $account_number = $journal_setup->account_number;
                $account_name   = $journal_setup->account_name;
            } else {
                // Cari dari purchase_invoice_journals (Account Payable)
                $get_ap = $this->db->select('a.account_number, b.account_name')
                    ->from('purchase_invoice_journals a')
                    ->join('account_coa b', 'a.account_number = b.account_number')
                    ->join('account_group_details c', 'c.id = b.account_group_detail_id')
                    ->where('a.number', $number)
                    ->like('c.name', 'Account Payable', 'after')
                    ->where('b.status', 0)
                    ->order_by('a.id', 'ASC')
                    ->get()->row();

                if ($get_ap) {
                    $account_number = $get_ap->account_number;
                    $account_name   = $get_ap->account_name;
                } else {
                    // Fallback ke COA Default
                    $coa = $this->db->get_where('account_coa', ['account_number' => $record['account_number']])->row();
                    $account_name = $coa->account_name ?? "";
                }
            }

            // Get Exchange Rate
            $exchange_rate = 1.00;
            if ($record['currency'] !== "IDR") {
                $rate_row = $this->db->get_where('exchange_rates', [
                    'currency_from' => $record['currency'],
                    'currency_to'   => 'IDR',
                    'start_date <=' => $record['trans_date'],
                    'end_date >='   => $record['trans_date']
                ])->row();
                $exchange_rate = $rate_row->middle ?? 0;
            }

            // Hitung Sisa Pembayaran (Balance)
            $balance = 0;
            $showRow = true;

            if ($record['status'] == '0') {
                // Kondisi PI Belum Lunas show di ADD & UPDATE
                $this->db->select_sum('payment');
                $this->db->where('purchase_invoice', $number);
                $ap_payment_row = $this->db->get('ap_payments')->row();
                $paid = $ap_payment_row->payment ?? 0;
                
                $balance = (float)$record['total'] - (float)$paid;

            } else if ($record['status'] == '1') {
                // Kondisi PI Sudah Lunas:
                if ($formMode == 'update') {
                    // Tampil total real di form UPDATE
                    $balance = (float)$record['total'];
                } else {
                    // Jangan tampil di form ADD
                    $showRow = false;
                }
            }

            // Filter tampilan berdasarkan formMode
            if (!$showRow || ($formMode == 'add' && $balance <= 0)) {
                continue; 
            }

            $row_data = [
                "trans_date"       => $record['trans_date'],
                "purchase_invoice" => $record['number'],
                "supplier_invoice" => $record['invoice_no'],
                "currency"         => $record['currency'],
                "rate"             => $exchange_rate,
                "amount"           => (float)$record['total'],
                "balance"          => (float)$balance,
                "payment"          => (float)$balance,
                "account_number"   => $account_number,
                "account_name"     => $account_name,
                "account_type"     => "DEBIT",
                "pi_status"        => $pi_status,
            ];

            $obj[] = $row_data;
        }

        $arr['rows'] = $obj;
        $arr['total_payment'] = round($total_payment, 2);

        header('Content-Type: application/json');
        die(json_encode($arr));
    }

    public function datatablesTemp_existing()
    {
        $purchase_invoice = base64_decode($this->input->get('purchase_invoice'));
        $purchase_invoice_ex = explode(",", $purchase_invoice);

        // var_dump($purchase_invoice_ex);
        // die;

        $this->db->select("number, journal_type_id, invoice_no, currency, (SUM(CASE WHEN account_type = 'DEBIT' THEN total ELSE -total END) + total_vat - total_pph) as total");
        $this->db->from('purchase_invoices');
        $this->db->where('deleted', 0);
        $this->db->where('status', 0);
        $this->db->where_in('number', $purchase_invoice_ex);
        $this->db->group_by('number');
        $this->db->order_by('number', 'asc');
        $records = $this->db->get()->result_array();

        $obj = [];
        $total_payment = 0;
        foreach ($records as $record) {
            $total_payment += $record['total'];
            $journal_type_id = $record['journal_type_id'];

            // var_dump($journal_type_id);
            // return;
            $number = $record['number'];

            $journal = $this->crud->query("SELECT a.*, a.flag, b.account_name FROM journal_setups a 
            JOIN account_coa b ON a.account_number = b.account_number 
            WHERE a.journal_type_id = '$journal_type_id' AND a.ap_payment = 'YES'");

            $ap_payment = $this->crud->query("SELECT purchase_invoice, SUM(payment) as payment FROM ap_payments WHERE purchase_invoice = '$number' GROUP BY purchase_invoice, account_number ORDER BY payment DESC");

            $obj[] = array(
                "purchase_invoice" => $record['number'],
                "supplier_invoice" => $record['invoice_no'],
                "currency" => $record['currency'],
                "amount" => $record['total'],
                "balance" => ($record['total'] - @$ap_payment[0]->payment),
                "payment" => ($record['total'] - @$ap_payment[0]->payment),
                "account_number" => @$journal[0]->account_number,
                "account_name" => @$journal[0]->account_name,
                "account_type" => "DEBIT",
            );
        }

        $arr['rows'] = @$obj;
        $arr['total_payment'] = round($total_payment, 2);
        die(json_encode($arr));
    }

    public function datatables($details = "")
    {
        $filter_payment_type  = base64_decode($this->input->get('filter_payment_type'));
        $filter_payment_date_from = base64_decode($this->input->get('filter_payment_date_from'));
        $filter_payment_date_to = base64_decode($this->input->get('filter_payment_date_to'));
        $filter_payment_no = base64_decode($this->input->get('filter_payment_no'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_payment_by = base64_decode($this->input->get('filter_payment_by'));

        $date_from = date("Y-m-01");
        $date_to = date("Y-m-t");
        
        if ($details == "") {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, d.number as gl_no, b.name as supplier_name, SUM(CASE WHEN a.account_type = 'DEBIT' THEN payment ELSE -payment END) as total_ap, 
            (CASE WHEN a.journal_type_id is null THEN c.journal_type_id ELSE a.journal_type_id END) as journal_type , GROUP_CONCAT(DISTINCT REPLACE(a.purchase_invoice, ' ', '') SEPARATOR ',') as purchase_invoices");
            $this->db->select("'view' as details");
            $this->db->from('ap_payments a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id', 'left');
            $this->db->join('purchase_invoices c', 'a.purchase_invoice = c.number', 'left');
            $this->db->join('journal_postings d', 'a.payment_no = d.document_no', 'left');
            $this->db->like('a.payment_type', $filter_payment_type);
            if ($filter_payment_date_from != "" && $filter_payment_date_to != "") {
                $this->db->where("a.payment_date between '$filter_payment_date_from' and '$filter_payment_date_to'");
            }else{
                $this->db->where("a.payment_date between '$date_from' and '$date_to'");
            }
            $this->db->like('a.payment_no', $filter_payment_no);
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.purchase_invoice', $filter_invoice_no);
            $this->db->like('a.bank_account', $filter_bank_no);
            $this->db->like('a.payment_by', $filter_payment_by);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.payment_no', 'DESC');
            $this->db->group_by('a.payment_no');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
        } else {
            $payment_no = base64_decode($this->input->get('payment_no'));

            $this->db->select('*');
            $this->db->select("'view' as details");
            $this->db->from('ap_payments');
            $this->db->where('payment_no', $payment_no);
            //$this->db->group_by('purchase_invoice');
            $this->db->order_by('status', 'ASC');
            $this->db->order_by('purchase_invoice', 'DESC');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
        }

        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    // CREATE DATA
    // (Just Insert Batch without Approvals)
    public function create()
    {
        set_time_limit(300);

        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body, true);

        if (empty($data)) {
            echo json_encode(["theme" => "error", "message" => "No data received"]);
            return;
        }

        $header   = $data['header'];
        $payments = $data['combinedAp'];
        $journals = $data['combinedJournals'];
        $today    = date('Ymd');
        $formMode = $header['formMode'] ?? 'add';

        // Get list account DP
        $account_coa = $this->db->select('account_number')
                        ->get_where('account_coa', ["account_group_detail_id" => "20240524000009"])
                        ->result_array();
        $dp_accounts = array_column($account_coa, 'account_number');

        $this->db->trans_start();

        // AP Payments
        $current_payment_ids = [];
        $newApData = [];
        $last_ap = $this->db->select_max('id')->like('id', $today, 'after')->get('ap_payments')->row();
        $inc_ap = ($last_ap && $last_ap->id) ? (int) substr($last_ap->id, -6) : 0;

        // Get list PI number untuk update status PI nanti
        $pi_before = $this->db->select('purchase_invoice')
                            ->where('payment_no', $header['payment_no'])
                            ->get('ap_payments')->result_array();
        $affected_pi_list = array_column($pi_before, 'purchase_invoice');

        foreach ($payments as $ap) {
            $pi_status_check = $ap['pi_status'] ?? null;
            $ap_id = $ap['id'] ?? null;

            // Get PI Number
            $affected_pi_list[] = $ap['purchase_invoice'];

            // Unset field yg tidak ada di database
            unset($ap['pi_status'], $ap['editing'], $ap['account_name'], $ap['trans_date']);

            // Mapping field dari header
            $ap['supplier_id']     = $header['supplier_id'];
            $ap['journal_type_id'] = $header['journal_type_id'];
            $ap['payment_type']    = $header['payment_type'];
            $ap['payment_date']    = $header['payment_date'];
            $ap['payment_by']      = $header['payment_by'];
            $ap['bank_account']    = $header['bank_account'];
            $ap['total_payment']   = $header['total_payment'];
            $ap['cheque_no']       = $header['cheque_no'] ?? null;
            $ap['rate']            = $header['rate'];
            $ap['note']            = $header['note'] ?? null;
            $ap['payment_no']      = $header['payment_no'];

            if (!empty($ap_id)) {
                // UPDATE Data existing
                $ap['updated_by'] = $this->session->username;
                $ap['updated_date'] = date('Y-m-d H:i:s');
                
                $this->db->update('ap_payments', $ap, ["id" => $ap_id]);
                $current_payment_ids[] = $ap_id;
            } else {
                // INSERT Data baru (Hanya jika PI masih Open/0)
                if ($pi_status_check == 0) {
                    $inc_ap++;
                    $ap['id'] = $today . sprintf("%06d", $inc_ap);
                    $ap['created_by'] = $this->session->username;
                    $ap['created_date'] = date('Y-m-d H:i:s');
                    
                    $newApData[] = $ap;
                    $current_payment_ids[] = $ap['id'];
                }
            }
        }

        // Hapus baris yang dibuang user di UI
        if ($formMode == 'update') {
            $this->db->where('payment_no', $header['payment_no']);
            if (!empty($current_payment_ids)) {
                $this->db->where_not_in('id', $current_payment_ids);
            }
            $this->db->delete('ap_payments');
        }

        // Insert data baru (Batch)
        if (!empty($newApData)) {
            $this->db->insert_batch('ap_payments', $newApData);
        }

        // --- PROSES JURNAL (REPLACE) ---
        $this->db->delete('ap_payment_journals', ['payment_no' => $header['payment_no']]);
        
        $batch_journals = [];
        $latest_jr = $this->db->select_max('id')->like('id', $today, 'after')->get('ap_payment_journals')->row();
        $id_journal = ($latest_jr && $latest_jr->id) ? (int) substr($latest_jr->id, -6) : 0;

        foreach ($journals as $j) {
            $id_journal++;
            $batch_journals[] = [
                'id'             => $today . sprintf("%06d", $id_journal),
                'payment_no'     => $header['payment_no'],
                'account_number' => $j['account_number'] ?? null,
                'account_name'   => $j['account_name'] ?? null,
                'description'    => $j['description'] ?? null,
                'exchange_rate'  => (float)($j['exchange_rate'] ?? 1),                
                'debit'          => (float)($j['debit'] ?? 0),
                'credit'         => (float)($j['credit'] ?? 0),
                'local_debit'    => (float)($j['local_debit'] ?? 0),
                'local_credit'   => (float)($j['local_credit'] ?? 0),
                'flag'           => $j['flag'] ?? '0',
                'created_by'     => $this->session->username,
                'created_date'   => date('Y-m-d H:i:s'),
            ];
        }
        if (!empty($batch_journals)) $this->db->insert_batch('ap_payment_journals', $batch_journals);

        // --- UPDATE AFFECTED PI NUMBER ---
        $unique_pi = array_unique($affected_pi_list);
        foreach ($unique_pi as $pi_number) {
            $this->update_pi_status($pi_number, $header['payment_no'], $dp_accounts);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(["theme" => "error", "message" => "Database Transaction Failed"]);
        } else {
            echo json_encode(["theme" => "success", "message" => "Successfully processed Payment: " . $header['payment_no']]);
        }
    }

    // Set Status PI
    private function update_pi_status($pi_number, $payment_no, $dp_accounts = []) 
    {
        $sql = "SELECT 
                    ROUND((SUM(CASE WHEN account_type = 'DEBIT' THEN total ELSE -total END) + total_vat - total_pph), 2) as total_bill,
                    (SELECT ROUND(IFNULL(SUM(payment), 0), 2) FROM ap_payments WHERE purchase_invoice = ?) as total_paid
                FROM purchase_invoices 
                WHERE number = ? 
                GROUP BY number";
        
        $query = $this->db->query($sql, [$pi_number, $pi_number])->row();

        if ($query) {
            $total_bill = (float)$query->total_bill;
            $total_paid = (float)$query->total_paid;

            // Status PI: 1 jika Lunas, 0 jika belum atau partial
            $new_status = ($total_paid >= $total_bill) ? 1 : 0;
            $this->db->update('purchase_invoices', ['status' => $new_status], ['number' => $pi_number]);

            // Status DP
            if (!empty($dp_accounts)) {
                $this->db->where('payment_no', $payment_no);
                $this->db->where_in('account_number', $dp_accounts);
                $count = $this->db->count_all_results('ap_payments');

                $status_dp = ($count > 0) ? 1 : 0;
                $this->db->update('ap_payments', ['status_dp' => $status_dp], ['payment_no' => $payment_no]);
            }
        }
    }

    public function create_recursive()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            // check journal_type_id
            $input_value = trim($post['journal_type_id']) ?? '';
            if (!is_numeric($input_value) || empty($input_value)) {
                $this->db->select('id');
                $this->db->from('journal_types');
                $this->db->like('name', $input_value, 'both'); 
                $getJournalType = $this->db->get()->row();

                if (!empty($getJournalType)) {
                    $journal_type_id = $getJournalType->id;
                } else {
                    $journal_type_id = NULL;                     
                }
                
                $post['journal_type_id'] = $journal_type_id;
            }
            
            // if (@$post['id'] != "") {
            //     $send = $this->crud->update('ap_payments', ["id" => $post['id']], $post);
            //     echo $send;
            // } else {
                $send = $this->crud->create('ap_payments', $post);
                if ($send) {
                    if ($post['amount'] == $post['payment']) {
                        $this->crud->update('purchase_invoices', ["number" => $post['purchase_invoice']], ["status" => 1]);
                    }

                    if ($post['balance'] == $post['payment']) {
                        $this->crud->update('ap_payments', ["payment_no" => $post['purchase_invoice']], ["status_dp" => 1]);
                    }
                }
                echo $send;
            // }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createJson()
    {
        $jsonData = $this->input->post('jsonData');
        $jsonData2 = $this->input->post('jsonData2');

        // Simpan data JSON ke dalam file
        file_put_contents('json/ap_payments.json', $jsonData);
        file_put_contents('json/ap_payment_journals.json', $jsonData2);
    }

    // Create Journal Posting (Just Insert Batch without Approvals)
    public function createPosting() 
    {
        $request_body = file_get_contents('php://input');
        $post = json_decode($request_body, true);
        $numberGL = $post['number'] ?? null;
        $details = $post['details'] ?? [];
        if (empty($details) && empty($numberGL)) {
            echo json_encode(["status" => "error", "message" => "No AP transactions found", "theme" => "error"]);
            return;
        }

        // --- AUTO-INCREMENT ID ---
        $today = date('Ymd');
        $this->db->select_max('id');
        $this->db->like('id', $today, 'after');
        $last_query = $this->db->get('journal_postings')->row();
        // Casting ke int agar increment berjalan benar
        $last_increment = ($last_query && $last_query->id) ? (int) substr($last_query->id, -6) : 0;

        $batch_data = [];
        $total_debit = 0;
        $total_credit = 0;

        foreach ($details as $row) {
            // Get autoID
            $last_increment++;
            $id = $today . sprintf("%06d", $last_increment);

            $debit = (float)($row['original_debit'] ?? 0);
            $credit = (float)($row['original_credit'] ?? 0);

            $batch_data[] = [
                "id"              => $id, 
                "number"          => $numberGL,
                "journal_date"    => $post['journal_date'],
                "journal_type_id" => $post['journal_type_id'],
                "trans_date"      => $row['trans_date'],
                "document_no"     => $row['document_no'],
                "invoice_no"      => $row['invoice_no'],
                "company_name"    => $row['company_name'],
                "account_number"  => $row['account_number'],
                "account_name"    => $row['account_name'],
                "description"     => $row['description'],
                "currency"        => $row['currency'],
                "original_debit"  => $debit,
                "original_credit" => $credit,
                "rates"           => (float)($row['rates'] ?? 1),
                "local_debit"     => (float)($row['local_debit'] ?? 0),
                "local_credit"    => (float)($row['local_credit'] ?? 0),
                "modul"           => $post['modul'],
                "created_date"    => date('Y-m-d H:i:s'),
                "created_by"      => $this->session->username
            ];

            $total_debit += $debit;
            $total_credit += $credit;
        }

        // Validasi Balance dengan toleransi selisih kecil (floating point issue)
        if (abs(round($total_debit, 2) - round($total_credit, 2)) > 0.01) {
            echo json_encode(["status" => "error", "message" => "Unbalanced Journal! Debit: $total_debit | Credit: $total_credit"]);
            return;
        }

        $this->db->trans_start();
        $this->db->insert_batch('journal_postings', $batch_data);
        $this->crud->logs("Create Batch Posting", $numberGL, 'journal_postings');
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(["status" => "error", "message" => "Database Transaction Failed"]);
        } else {
            echo json_encode(["status" => "success", "message" => "Data successfully posted with code: " . $numberGL]);
        }
    }

    // Create Journal Posting (pak Hilman)
    public function createPostingOld()
    {
        $document_no = $this->input->post('payment_no');

        $datenow = date("Y-m-d");
        $sql_menu = $this->db->query("SELECT * FROM menus_no WHERE menus_id = '20240227000005' and `status` = '0' and `start_date` <= '$datenow' and end_date >= '$datenow'");
        $row_menu = $sql_menu->row();

        if(@$row_menu->number == ""){
            $voucher_no = "";
        }else{
            $number = @$row_menu->number;
            $yearnow = date("y", strtotime($row_menu->start_date));
            $yearend = date("y", strtotime($row_menu->end_date));

            $whereDate = $number.$yearnow.$yearend."1";

            $sqlGetID = $this->db->query("SELECT max(`number`) as kode FROM journal_postings WHERE `number` like '%$whereDate%'");
            $rowID = $sqlGetID->row();
            $kode = $rowID->kode;
            
            if ($kode == NULL) {
                $autoID = sprintf("%0".$row_menu->sequence."s", $kode + 1);
                $autoNo = $whereDate.$autoID;
            } else {
                $urutan = (int) substr($kode, -6);
                $urutan++;
                $autoID = sprintf("%0".$row_menu->sequence."s", $urutan);
                $autoNo = $whereDate.$autoID;
            }

            $voucher_no = $autoNo;
        }

        $this->db->select("a.payment_no, b.journal_type_id, b.payment_date, b.purchase_invoice, b.supplier_invoice, c.id as supplier_id, c.name as supplier_name, 
                b.currency, a.description, a.account_number, a.account_name, a.debit, a.credit, a.flag, a.local_debit, a.local_credit, b.rate,
                (CASE WHEN e.name is null THEN d.account_name ELSE e.name END) as other_account");
        $this->db->from('ap_payment_journals a');
        $this->db->join("(SELECT * FROM ap_payments GROUP BY payment_no) b", "b.payment_no = a.payment_no");
        $this->db->join("suppliers c", "b.supplier_id = c.id");
        $this->db->join('account_coa d', 'b.other_account = d.account_number', 'left');
        $this->db->join('account_imprests e', 'b.imprest_account = e.number', 'left');
        $this->db->where_in('a.payment_no', $document_no);
        $this->db->order_by('a.payment_no', 'asc');
        $this->db->order_by('a.flag', 'asc');
        $journals = $this->db->get()->result_array();

        $trans_date = "";
        $customer_name = "";
        $currency = "";

        $original_debit = 0;
        $original_credit = 0;
        $local_debit = 0;
        $local_credit = 0;

        $grand_original_debit = 0;
        $grand_original_credit = 0;
        $grand_local_debit = 0;
        $grand_local_credit = 0;

        $data = array();
        foreach ($journals as $journal) {
            $number = $journal['payment_no'];
            $account_number = $journal['account_number'];
            $account_name = $journal['account_name'];
            $debit = $journal['debit'];
            $credit = $journal['credit'];
            $description = $journal['description'];
            $explode = explode(" | ", $description);
            $purchase_invoice = @$explode[0];
            $supplier_invoice = @$explode[1];

            if ($journal['currency'] != "INR") {
                $original_debit = $debit;
                $original_credit = $credit;
                $local_debit = $journal['local_debit'];
                $local_credit = $journal['local_credit'];

                if(($original_debit + $original_credit) == 0){
                    $rates = 1;
                }else{
                    $rates = (($journal['local_debit'] + $journal['local_credit']) / ($original_debit + $original_credit));
                }
            } else {
                $original_debit = $debit;
                $original_credit = $credit;
                $local_debit = $journal['local_debit'];
                $local_credit = $journal['local_credit'];

                $rates = 1;
            }

            $data[] = array(
                "journal_date" => $journal['payment_date'],
                "journal_type_id" => $journal['journal_type_id'],
                "number" => $voucher_no,
                "trans_date" => $journal['payment_date'],
                "document_no" => $number,
                "invoice_no" => $purchase_invoice,
                "company_id" => $journal['supplier_id'],
                "company_name" => $journal['supplier_name'] . " (" . $journal['other_account'] . ")",
                "modul" => "AP PAYMENT",
                "account_number" => $account_number,
                "account_name" => $account_name,
                "description" => "(" . $journal['other_account'] . ") | " . $number . " | " . $purchase_invoice . " | " . $supplier_invoice,
                "currency" => $journal['currency'],
                "original_debit" => $original_debit,
                "original_credit" => $original_credit,
                "rates" => $rates,
                "local_debit" => $local_debit,
                "local_credit" => $local_credit,
            );

            $grand_original_debit += $original_debit;
            $grand_original_credit += $original_credit;
            $grand_local_debit += $local_debit;
            $grand_local_credit += $local_credit;
        }

        if($voucher_no == ""){
            die(json_encode(array("title" => "Configuration", "message" => "Please Check Configuration in Serial No (Journal Posting) First", "theme" => "error")));
        }

        if(round($grand_local_debit, 2) != round($grand_local_credit, 2)){
            die(json_encode(array("title" => "Failed", "message" => "Balance Debit (".$grand_local_debit.") Cannot match on Balance Credit (" . $grand_local_credit . ")", "theme" => "error")));
        }

        foreach ($data as $dt) {
            $this->crud->create('journal_postings', $dt);
        }

        die(json_encode(array("title" => "Good Job", "message" => "Data Successfully Created to Journal Posting with Code GL No " . $voucher_no, "theme" => "success")));
    }

    public function createJournals()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $purchase_invoice_journals = $this->crud->read('ap_payment_journals', [], ["payment_no" => $post['payment_no'], "description" => $post['description'], "account_number" => $post['account_number']]);

            if (@$purchase_invoice_journals->id != "") {
                $send = $this->crud->update('ap_payment_journals', ["payment_no" => $post['payment_no'], "description" => $post['description'], "account_number" => $post['account_number']], $post);
                echo $send;
            } else {
                $send = $this->crud->create('ap_payment_journals', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function createJournals_existing()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $purchase_invoice_journals = $this->crud->read('ap_payment_journals', [], ["payment_no" => $post['payment_no'], "account_number" => $post['account_number']]);

            if (@$purchase_invoice_journals->id != "") {
                $send = $this->crud->update('ap_payment_journals', ["payment_no" => $post['payment_no'], "account_number" => $post['account_number']], $post);
                echo $send;
            } else {
                $send = $this->crud->create('ap_payment_journals', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function update()
    {
        header('Content-Type: application/json');
        if ($this->input->post()) 
        {
            $post = $this->input->post();
            unset($post['id']);

            // Validasi Exist
            $ap_exists = $this->db->get_where('ap_payments', ['payment_no' => $post['payment_no'], 'purchase_invoice' => $post['purchase_invoice']])->row();

            if ($ap_exists) {
                $update = $this->crud->update('ap_payments', ['payment_no' => $post['payment_no'], 'purchase_invoice' => $post['purchase_invoice']], $post);
                echo $update;
            } else {
                // Jika tidak ada PI yang sesuai, update by payment_no
                $send = $this->crud->update('ap_payments', ["payment_no" => $post['payment_no']], $post);
                echo $send;

                // echo json_encode(['success' => false, 'message' => 'AP Payment Not Found.', 'title' => 'Error', 'theme' => 'error']);
            }

        } else {
            echo json_encode(['success' => false, 'message' => 'Method not allowed.', 'title' => 'Error', 'theme' => 'error']);
        }
    }

    public function update_backup()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->crud->update('ap_payments', ["payment_no" => $post['payment_no']], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function deleteOnUncheck() 
    {
        $data = $this->input->post();
        // $send = $this->crud->delete('ap_payments', $data); // fatal jika sisa PI tinggal 1 terhapus AP Payment saat update
        // echo $send;
        // exit;

        if ($this->input->method() === 'post') 
        {
            $purchase_invoice = $this->input->post('purchase_invoice');

            if ($data !== null) 
            {
                // check availability first 
                $check_availability = $this->crud->read("ap_payments", [], ["purchase_invoice" => $purchase_invoice]);
                if (!empty($check_availability)) {

                    // $this->db->where_in('purchase_invoice', $purchase_invoice);
                    // $result = $this->db->delete('ap_payments'); // Mengembalikan TRUE/FALSE

                    $result = $this->crud->update('ap_payments', ["id" => $check_availability->id, "purchase_invoice" => $purchase_invoice], ["deleted" => 1]);
                    echo json_encode($result);

                    // if ($result) {
                    //     $rows_affected = $this->db->affected_rows(); // lihat data yang telah dihapus
                    //     echo json_encode(['success' => true, 'message' => "Data $rows_affected berhasil dihapus."]);
                    // } else {
                    //     echo json_encode(['success' => false, 'message' => 'Gagal menghapus data.']);
                    // }
                }
            } else {
                $this->output->set_status_header(400); // Bad Request
                echo json_encode(['success' => false, 'message' => 'Parameter ID item tidak lengkap.']);
            }
        } else {
            $this->output->set_status_header(405); // Method Not Allowed
            echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan.']);
        }
    }

    public function deleteSingle()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('ap_payments', array("id" => $data['id']));

        $this->crud->update('ap_payments', ["payment_no" => $data['purchase_invoice']], ["status_dp" => 0]);
        echo $send;
    }

    public function deleteJournal()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('ap_payments', $data);
        $send = $this->crud->delete('ap_payment_journals', $data);
        echo $send;
    }

    public function delete()
    {
        $data = $this->input->post();

        $ap_payments = $this->crud->reads("ap_payments", [], ["payment_no" => $data['payment_no']]);
        foreach ($ap_payments as $ap_payment) {
            $this->crud->update("purchase_invoices", [
                "number" => $ap_payment->purchase_invoice,
            ], ["status" => 0]);

            $this->crud->update('ap_payments', ["payment_no" => $ap_payment->purchase_invoice], ["status_dp" => 0]);
        }

        $send = $this->crud->delete('ap_payments', $data);
        $send = $this->crud->delete('ap_payment_journals', ["payment_no" => $data['payment_no']]);
        echo $send;
    }


    // ---------- UPLOAD FUNCTIONS ------------
    //UPLOAD DATA
    public function upload()
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

            chmod($target, 0777);
            $file = $target;
            $data = new Spreadsheet_Excel_Reader($file, false);
            $total_row = $data->rowcount($sheet_index = 0);
            $datas = [];

            for ($i = 4; $i <= $total_row; $i++) {
                $datas[] = array(
                    'payment_no'       => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 2)),
                    'supplier_code'    => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 3)),
                    'payment_type'     => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 4)),
                    'payment_date'     => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 5)),
                    'journal_number'   => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 6)),
                    'bank_account'     => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 7)),
                    'note'             => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 8)),
                    'purchase_invoice' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 9)),
                    'supplier_invoice' => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 10)),
                    'currency'         => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 11)),
                    'amount'           => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 12)),
                    'balance'          => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 13)),
                    'payment'          => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 14)),
                    'remark'           => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 15)),
                    'account_number'   => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 16)),
                    'account_type'     => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 17)),
                    'action'           => preg_replace('/[^\x20-\x7E]/', '', $data->val($i, 18)),
                );
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

    public function uploadclearFailed()
    {
        @unlink('failed/ap_payments.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/ap_payments.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/ap_payments.txt";
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
    public function uploadcreate()
    {
        if (!$this->input->post()) {
            echo json_encode(["title" => "Error", "message" => "Invalid request method", "theme" => "error"]);
            return;
        }
        
        $data = $this->input->post('data');

        $ap_payment    = $this->crud->read('ap_payments', [], ["purchase_invoice" => $data['purchase_invoice'], "payment_no" => $data['payment_no']]);
        $supplier      = $this->crud->read('suppliers', ["id" => $data['supplier_code']]);
        $account_coa   = $this->crud->read('account_coa', ["account_number" => $data['account_number']]);
        $journal_types = $this->crud->read('journal_types', ["number" => $data['journal_number']]);
        $purchase_data = $this->crud->read('purchase_invoices', [], ["number" => $data['purchase_invoice'], "account_number" => $data['account_number']]);
        $account_banks = $this->db->select('*')->from('account_banks')->like('bank_account', trim($data['bank_account']), 'both')->get()->row();
        
        // Tambahkan validasi jika Payment No. sudah ada dari insert via Form bukan via upload (Bu Nina)
        $this->db->select('*');
        $this->db->from('ap_payments');
        $this->db->where('payment_no', $data['payment_no']);
        $this->db->where('upload', null);
        $this->db->where('upload_date', null);
        $ap_payment_manual = $this->db->get()->result();
        
        $trans_date = $data['payment_date'];
        $valid_date = ($d = DateTime::createFromFormat('Y-m-d', $trans_date)) && $d->format('Y-m-d') === $trans_date;

        // Validate required data
        if (!empty($ap_payment_manual)) {
            echo json_encode(["title" => "Duplicated", "message" => "Payment No " . $data['payment_no'] . " is already exists. Please provide a unique number.", "theme" => "error"]);
        
        } elseif (empty($data['action']) || (strtolower($data['action']) !== "new" && strtolower($data['action']) !== "update")) {
            echo json_encode(["title" => "Error", "message" => "ACTION must be NEW or UPDATE", "theme" => "error"]);
        
        } elseif (strtolower($data['action']) !== 'update' && !empty($ap_payment) && strtoupper($ap_payment->upload) === "YES") {
            echo json_encode(["title" => "Duplicated", "message" => "Action=NEW and Payment No. " . $data['payment_no'] . " is Duplicate Data", "theme" => "error"]);
        
        } elseif (empty($purchase_data) && ($data['purchase_invoice'] !== "-" && !empty($data['purchase_invoice']))) {
            echo json_encode(["title" => "Not Found", "message" => "Purchase Invoice No. " . $data['purchase_invoice'] . " & Account Number " . $data['account_number'] . " Not Found", "theme" => "error"]);
        
        } elseif (!empty($purchase_data) && $purchase_data->status == "1" && strtolower($data['action']) !== 'update') {
            echo json_encode(["title" => "Duplicated", "message" => "Purchase Invoice No. " . $data['purchase_invoice'] . " has been processed previously (Closed)", "theme" => "error"]);
        
        } elseif (empty($data['payment_type']) || (strtoupper($data['payment_type']) !== "PURCHASE" && strtoupper($data['payment_type']) !== "OTHER")) {
            echo json_encode(["title" => "Error", "message" => "Type must be PURCHASE or OTHER", "theme" => "error"]);
        
        } elseif (empty($data['account_type']) || (strtoupper($data['account_type']) !== "DEBIT" && strtoupper($data['account_type']) !== "CREDIT")) {
            echo json_encode(["title" => "Error", "message" => "Account Type must be DEBIT or CREDIT", "theme" => "error"]);
        
        } elseif (empty($trans_date) || !$valid_date) {
            echo json_encode(["title" => "Error", "message" => "Date format must be 'YYYY-MM-DD'", "theme" => "error"]);
        
        } elseif (empty($supplier)) {
            echo json_encode(["title" => "Not Found", "message" => "Supplier No " . $data['supplier_code'] . " Not Found", "theme" => "error"]);
        
        } elseif (empty($journal_types)) {
            echo json_encode(["title" => "Not Found", "message" => "Journal Type Code " . $data['journal_number'] . " Not Found", "theme" => "error"]);
            
        } elseif (empty($account_coa)) {
            echo json_encode(["title" => "Not Found", "message" => "Account COA " . $data['account_number'] . " Not Found", "theme" => "error"]);
        
        } elseif (empty($account_banks)) {
            echo json_encode(["title" => "Not Found", "message" => "Bank Account No. " . $data['bank_account'] . " Not Found", "theme" => "error"]);
        
        } else {
                
            // Get AP Payment number
            $payment_no_from_excel = $data['payment_no'] ?? '-';
            $existing_payment = null;
            if ($payment_no_from_excel !== '-') {
                $existing_payment = $this->crud->read('ap_payments', [], ["payment_no" => $payment_no_from_excel]);
            }
            
            if (empty($payment_no_from_excel) || $payment_no_from_excel === '-') {
                // KONDISI 1: Jika data dari excel kosong atau '-', buat nomor baru.
                $trans_date = $data['payment_date'];
                $year = date("y", strtotime($trans_date));
                $month = date("m", strtotime($trans_date));
                $bank_code  = $account_banks->bank_code ?? $data['bank_account'];

                $datenow    = $bank_code."/".$month."-".$year."/"."K";
                $sqlGetID   = $this->db->query("SELECT max(`payment_no`) as kode FROM ap_payments WHERE `payment_no` like '%$datenow%'");
                $rowID      = $sqlGetID->row();
                $kode       = $rowID->kode;
                if ($kode == NULL) {
                    $autoID = sprintf("%03s", $kode + 1);
                } else {
                    $urutan = (int) substr($kode, 0, 3);
                    $urutan++;
                    $autoID = sprintf("%03s", $urutan);
                }
                $payment_no = $autoID."/".$datenow;

            } elseif (!empty($existing_payment)) {
                // KONDISI 2: Jika number dari excel ada di database, gunakan yang sudah ada.
                $payment_no = $existing_payment->payment_no;

            } else {
                // KONDISI 3: Jika number dari excel tidak kosong dan tidak ada di database, gunakan nilai dari excel.
                $payment_no = $payment_no_from_excel;
            }

            // Get Rate
            $currency  = $data['currency'];
            $date_rate = $data['payment_date'];
            $monthBf   = date('Y-m-01', strtotime('-1 month', strtotime($date_rate)));
            $exchange  = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);
            if ($currency != "IDR") {
                if ($exchange) {
                    $rate = $exchange->middle;
                } else {
                    $rate = (float)($data['rate'] ?? 1);
                }
            } else {
                $rate = 1;
            }

            // Payment Method tidak ada di excel
            $payment_by = !empty($data['bank_account']) ? 'TRANSFER' : 'CASH';

            // Prepare Data
            $post = [
                "supplier_id"       => $supplier->id,
                "journal_type_id"   => $journal_types->id,
                "payment_type"      => $data['payment_type'] ?? null,
                "payment_date"      => $data['payment_date'] ?? null,
                "payment_no"        => $payment_no,
                "payment_by"        => $payment_by,
                "bank_account"      => $data['bank_account'] ?? null,
                "cheque_no"         => $data['cheque_no'] ?? null,
                "purchase_invoice"  => $data['purchase_invoice'] ?? null,
                "supplier_invoice"  => $data['supplier_invoice'] ?? null,
                "currency"          => $data['currency'] ?? null,
                "amount"            => (float)($data['amount'] ?? 0),
                "balance"           => (float)($data['balance'] ?? 0),
                "payment"           => (float)($data['payment'] ?? 0),
                "total_payment"     => (float)($data['payment'] ?? 0),
                "remarks"           => $data['remark'] ?? null,
                "note"              => $data['note'] ?? null,
                "rate"              => $rate ?? null,
                "account_number"    => $data['account_number'] ?? null,
                "account_type"      => $data['account_type'] ?? null,
                "status_dp"         => $data['status_dp'] ?? 0,
                "status"            => $data['status'] ?? 0,
                "upload"            => "YES",
                "upload_date"       => date('Y-m-d'),
            ];

            // CREATE (INSERT)
            if (strtoupper($data['action'] === "NEW")) {
                $send = $this->crud->create('ap_payments', $post);
            } else {
                if (!empty($ap_payment)) {
                    $whereParams = ["payment_no" => $post['payment_no'], "purchase_invoice" => $post["purchase_invoice"], "account_number" => $post['account_number']];
                    $send = $this->crud->update('ap_payments', $whereParams, $post);
                } else {
                    $send = $this->crud->create('ap_payments', $post);
                }
            }
            
            if ($send) {
                if (!empty($post['purchase_invoice'])) {
                    // update status purchase to closed
                    if ($post['amount'] == $post['payment']) {
                        $this->crud->update('purchase_invoices', ["number" => $post['purchase_invoice'], "account_number" => $post['account_number']], ["status" => "1"]);
                    }

                    if ($post['balance'] == $post['payment']) {
                        $this->crud->update('ap_payments', ["payment_no" => $post['purchase_invoice']], ["status_dp" => 1]);
                    }
                }

                // send response to frontend
                echo $send;
            } else {
                echo json_encode(["title" => "Error", "message" => "Failed to create AP payment", "theme" => "error"]);
            }
        }
    }

    // UPLOAD GET JOURNAL (AP)
    public function uploadGetJournal()
    {
        $this->db->select('a.*, b.account_name');
        $this->db->from('ap_payments a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number');
        $this->db->where('a.upload', "YES");
        $this->db->where('a.upload_date', date("Y-m-d"));
        $records = $this->db->get()->result_array();

        // Kelompokkan data berdasarkan payment_no
        $groupedPayments = [];
        foreach ($records as $record) {
            $payment_no = $record['payment_no'];
            if (!isset($groupedPayments[$payment_no])) {
                $groupedPayments[$payment_no] = [
                    'main_record' => $record,
                    'items' => []
                ];
            }
            $groupedPayments[$payment_no]['items'][] = $record;
        }

        $allJournals = [];

        // Proses setiap grup pembayaran 
        foreach ($groupedPayments as $payment_no => $group) {
            $main_record = $group['main_record'];
            $items = $group['items'];

            $grand_total = 0;
            $grand_total_local = 0;
            $merged_accounts = [];

            // Hitung total untuk setiap payment_no dan gabungkan entri yang sama
            foreach ($items as $item) {
                $payment_amount = (float)($item['payment'] ?? 0);
                $account_number = $item['account_number'];
                $rate = (float)($item['rate'] ?? 1);
                
                // Logika Debit/Kredit (Pembayaran AP)
                $debit = $payment_amount; // AP didebit
                $credit = 0;
                
                // Akumulasi total
                $grand_total += $payment_amount;
                $grand_total_local += $payment_amount * $rate;
                
                // Gabungkan entri jurnal yang memiliki nomor akun sama
                if (!isset($merged_accounts[$account_number])) {
                    $merged_accounts[$account_number] = [
                        'payment_no' => $payment_no,
                        'account_number' => $account_number,
                        'account_name' => $item['account_name'],
                        'description' => $item['supplier_invoice'],
                        'exchange_rate' => $rate,
                        'debit' => 0,
                        'credit' => 0,
                        'local_debit' => 0,
                        'local_credit' => 0,
                    ];
                }
                $merged_accounts[$account_number]['debit'] += $debit;
                $merged_accounts[$account_number]['local_debit'] += $debit * $rate;
            }

            $flag_counter = 1;
            
            // Tambahkan entri akun lainnya dengan flag berurutan setelah bank entry
            foreach (array_values($merged_accounts) as $journal) {
                $journal['flag'] = $flag_counter++;
                $allJournals[] = $journal;
            }

            // Buat entri jurnal untuk Akun Bank (KREDIT)
            $getBank = $this->db->select('a.*, b.account_name')
                ->from('account_banks a')
                ->join('account_coa b', 'a.account_number = b.account_number')
                ->where('bank_account', $main_record['bank_account'])->get()->row();

            $bank_entry = [
                'payment_no'     => $payment_no,
                'account_number' => $getBank->account_number ?? null,
                'account_name'   => $getBank->account_name ?? $main_record['bank_account'],
                'description'    => 'Payment to Bank',
                'exchange_rate'  => (float)($main_record['rate'] ?? 1),
                'debit'          => 0,
                'credit'         => $grand_total,
                'local_debit'    => 0,
                'local_credit'   => $grand_total_local,
                'flag'           => $flag_counter++,
            ];

            // Gabungkan entri bank dengan entri akun lainnya
            $allJournals[] = $bank_entry;

            // Update total payment di database
            $this->crud->update('ap_payments', ["payment_no" => $payment_no], ["total_payment" => $grand_total]);
        }

        $result = [
            'total' => count($allJournals),
            'data'  => $allJournals
        ];

        echo json_encode($result);
    }

    public function uploadCreateJournal()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            $ap_payment_journals = $this->crud->read('ap_payment_journals', [], ["payment_no" => $post['payment_no'], "account_number" => $post['account_number']]);

            if (!empty($ap_payment_journals)) {
                // update error foreign key constraint debit credit
                // $send = $this->crud->update('ap_payment_journals', ["number" => $post['number'], "account_number" => $post['account_number']], $post);
                
                // delete existing then re-create 
                $this->crud->delete('ap_payment_journals', $post);
                $send = $this->crud->create('ap_payment_journals', $post);
                echo $send;
            } else {
                $send = $this->crud->create('ap_payment_journals', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function uploadcreate_existing()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Process Number
            $supplier = $this->crud->read('suppliers', [], ["number" => $data['supplier_code']]);
            $account_coa = $this->crud->read('account_coa', [], ["account_number" => $data['account_number']]);
            $journal_types = $this->crud->read('journal_types', [], ["number" => $data['journal_number']]);
            //$ap_payments = $this->crud->read('ap_payments', [], ["purchase_invoice" => $data['purchase_invoice']]);

            if (empty($supplier->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Supplier No " . $data['supplier_code'] . " Not Found", "theme" => "error"));
            } elseif (empty($journal_types->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Journal Type Code " . $data['journal_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($account_coa->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Account COA " . $data['account_number'] . " Not Found", "theme" => "error"));
                // } elseif (!empty($ap_payments->id)) {
                //     echo json_encode(array("title" => "Duplicated", "message" => "Purchase Invoice " . $data['purchase_invoice'] . " Duplicate Data", "theme" => "error"));
            } else {
                $ap_payment_no = $this->crud->reads('ap_payments', [], ["supplier_id" => $supplier->id, "payment_date" => $data['payment_date'], "purchase_invoice" => $data['purchase_invoice']]);

                if (count($ap_payment_no) > 0) {
                    $payment_no = $ap_payment_no[0]->payment_no;
                } else {
                    $datenow    = "AP-" . date("Ymd", strtotime($data['payment_date']));
                    $sqlGetID   = $this->db->query("SELECT max(`payment_no`) as kode FROM ap_payments WHERE `payment_no` like '%$datenow%'");
                    $rowID      = $sqlGetID->row();
                    $kode       = $rowID->kode;
                    if ($kode == NULL) {
                        $autoID = sprintf("%04s", $kode + 1);
                    } else {
                        $urutan = (int) substr($kode, -4);
                        $urutan++;
                        $autoID = sprintf("%04s", $urutan);
                    }
                    $payment_no = $datenow . "-" . $autoID;
                }

                $data_final = array(
                    "payment_no" => $payment_no,
                    "supplier_id" => $supplier->id,
                    "journal_type_id" => $journal_types->id,
                    "payment_type" => $data['payment_type'],
                    "payment_date" => $data['payment_date'],
                    "payment_by" => $data['payment_by'],
                    "note" => $data['note'],
                    "bank_account" => $data['bank_account'],
                    "purchase_invoice" => $data['purchase_invoice'],
                    "supplier_invoice" => $data['supplier_invoice'],
                    "currency" => $data['currency'],
                    "amount" => $data['amount'],
                    "balance" => $data['balance'],
                    "payment" => $data['payment'],
                    "remarks" => $data['remark'],
                    "account_number" => $data['account_number'],
                    "account_type" => $data['account_type'],
                );

                //Simpan Data
                $send   = $this->crud->create('ap_payments', $data_final);

                $account_number = $data['account_number'];
                $ap_payment_journals = $this->crud->reads('ap_payment_journals', [], ["payment_no" => $payment_no, "account_number" => $account_number]);

                $sqlJournalMax = $this->db->query("SELECT max(flag) as kode FROM ap_payment_journals WHERE payment_no = '$payment_no'");
                $rowJournalMax = $sqlJournalMax->row();

                $sqlJournal = $this->db->query("SELECT account_number, SUM(debit) as debit, SUM(credit) as credit FROM ap_payment_journals WHERE payment_no = '$payment_no' AND account_number = '$account_number' GROUP BY account_number");
                $rowJournal = $sqlJournal->row();

                if (count($ap_payment_journals) == 0) {
                    if ($data['account_type'] == "DEBIT") {
                        $debit = $data['payment'];
                        $credit = 0;
                    } else {
                        $debit = 0;
                        $credit = $data['payment'];
                    }

                    $arr = array(
                        "payment_no" => $payment_no,
                        "account_number" => $account_coa->account_number,
                        "account_name" => $account_coa->account_name,
                        "debit" => $debit,
                        "credit" => $credit,
                        "flag" => ($rowJournalMax->kode + 1),
                    );

                    $this->crud->create('ap_payment_journals', $arr);
                } else {
                    if ($data['account_type'] == "DEBIT") {
                        $debit = ($rowJournal->debit + $data['payment']);
                        $credit = 0;
                    } else {
                        $debit = 0;
                        $credit = ($rowJournal->credit + $data['payment']);
                    }

                    $arr = array(
                        "debit" => $debit,
                        "credit" => $credit
                    );

                    $send = $this->crud->update('ap_payment_journals', ["payment_no" => $payment_no, "account_number" => $account_number], $arr);
                }

                echo $send;
            }
        }
    }


    // ------- PRINT FUNCTIONS ---------
    public function print_voucher_existing($payment)
    {
        $payment_no = base64_decode($payment);
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('ap_payments a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->like('a.payment_no', $payment_no);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.payment_date', 'DESC');
        $payment_total = $this->db->get()->result_array();

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 40;
        $page = ceil(count($payment_total) / $rows);
        //Generate QRcode
        // $this->createQrcode(@$payment_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $payment_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        @media screen {
                            .print {
                                display: none !important;
                            }
                        }
            
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }
                    </style>
                    <body>
                    <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <p>Display pages for 40 rows</p>
                            <p>Paper Size A4, Layout Landscape</p>
                            <p>Margin Default, Scale 95</p>
                        </center>
                    </div>
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.name as supplier_name, c.bank_name');
            $this->db->from('ap_payments a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('account_banks c', 'a.bank_account = c.bank_account');
            $this->db->like('a.payment_no', $payment_no);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.payment_date', 'DESC');
            //$this->db->group_by('a.payment_no');
            $this->db->limit(40, ($i * 40));
            $records = $this->db->get()->result_array();

            //Exchange Rate
            $payment_date = $records[0]['payment_date'];
            $currency = $records[0]['currency'];

            $search_date = date("d", strtotime($payment_date));
            if($search_date == "31"){
              $payment_date = date("Y-m-d", strtotime('-1 days', strtotime($payment_date)));
            }

            $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($payment_date)));
            $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

            if ($exchange) {
                $amount = $exchange->middle;
                $hide = "";
            } else {
                $amount = 0;
                $hide = "hidden";
            }
            // <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $payment_no . '.png') . '" width="60"/></td>
            $exchangeName = "Rp. " . number_format($amount, 2);

            $html .= '<table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <td width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_ap_payment . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_ap_payment . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print Date</td>
                                            <td>:</td>
                                            <td>' . date("Y-m-d H:i") . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print By</td>
                                            <td>:</td>
                                            <td>' . $this->session->name . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div style="border: none; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>PAYMENT VOUCHER</h3>
                                </center>
                                <div style="float:left; width:40%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Pay To</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['supplier_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Payment By</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['payment_by'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:30%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Payment No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['payment_no'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="80">Payment Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @date("d F Y", strtotime($records[0]['payment_date'])) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Bank Account</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['bank_account'] . '</b></td>
                                        </tr>
                                         <tr>
                                            <td width="50">Bank Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['bank_name'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div ' . $hide . ' style="float:left; width:25%; border:2px solid black; padding:10px; font-size:12px; margin-left:20px;"> 
                                    <p style="margin:0;">Rate USD to IDR : <b>' . $exchangeName . '</b></p>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th>No</th>
                                        <th>Purchase Invoice No</th>
                                        <th>Supplier Invoice No</th>
                                        <th>Currency</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                        <th>Payment</th>
                                    </tr>';
            $grand_total = 0;
            foreach ($records as $record) {
                if ($record['account_type'] == "DEBIT") {
                    $grand_total += $record['payment'];
                    $subtotal += $record['payment'];
                } else {
                    $grand_total -= $record['payment'];
                    $subtotal -= $record['payment'];
                }

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['purchase_invoice'] . '</td>
                                <td>' . $record['supplier_invoice'] . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . @number_format(($record['amount']), 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['balance'], 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['payment'], 2) . '</td>
                            </tr>';
                $no++;
            }

            // if (($i + 1) == $page) {
            //     $html_grand_total = '<tr>
            //                 <th style="text-align:right" colspan="6">GRAND TOTAL</th>
            //                 <th style="text-align:right;">' . @number_format($subtotal, 2) . '</th>
            //             </tr>';
            // }else{
            //     $html_grand_total = "";
            // }

            // $html .= '  <tr>
            //                 <th style="text-align:right" colspan="6">SUB TOTAL</th>
            //                 <th style="text-align:right;">' . @number_format($grand_total, 2) . '</th>
            //             </tr>
            //             '.$html_grand_total.'
            //         </table>';

            // $html .= '  <tr>
            //                 <th style="text-align:right" colspan="6">GRAND TOTAL</th>
            //                 <th style="text-align:right;">' . @number_format($grand_total, 2) . '</th>
            //             </tr>
            //         </table>';

            if (($i + 1) == $page) { 
                $html .= '  <tr>
                                <th style="text-align:right" colspan="6">GRAND TOTAL</th>
                                <th style="text-align:right;">' . @number_format($grand_total, 2) . '</th>
                            </tr>';
            }
            
            $html .= '</table>';

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }

        //<td>' . $this->convertcurrency->convertCurrencyToWords($subtotal, $records[0]['currency']) . '</td>
        $html .= '<div style="width:100%; float:left;">
                        <table id="customers" style="margin-top:10px;">
                            <tr>
                                <th style="text-align:center;">Amount in Words</th>
                                <td>' . $this->convertcurrency->convertCurrencyToWords($grand_total, $records[0]['currency']) . '</td>
                               
                            </tr>
                        </table>
                        <p style="font-size:12px;"><i>Note: ' . @$records[0]['note'] . '</i>
                        <i>*This Payment Voucher was prepared by ' . $config->name . '</i></p>
                    </div>
                    <div style="width:100%; float:left; margin-bottom:20px;">
                        <table id="customers" style="width:100%; font-size:12px;">
                            <tr>
                                <td rowspan="2" style="font-weight:bold;">Account No</td>
                                <td rowspan="2" style="font-weight:bold;">Account Name</td>
                                <td rowspan="2" style="font-weight:bold;">Description</td>
                                <td colspan="2" style="font-weight:bold;">Original Currency</td>
                                <td colspan="2" style="font-weight:bold;">Local Currency</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Debit</td>
                                <td style="font-weight:bold;">Credit</td>
                                <td style="font-weight:bold;">Debit</td>
                                <td style="font-weight:bold;">Credit</td>
                            </tr>';
            $journals = $this->crud->query("SELECT a.*, b.account_name 
            FROM ap_payment_journals a 
            JOIN account_coa b ON a.account_number = b.account_number
            WHERE a.payment_no = '$payment_no' ORDER BY a.flag ASC");

            $total_debit = 0;
            $total_credit = 0;
            $local_total_debit = 0;
            $local_total_credit = 0;
            foreach ($journals as $journal) {

                $total_debit += $journal->debit;
                $total_credit += $journal->credit;
                $local_total_debit += $journal->local_debit;
                $local_total_credit += $journal->local_credit;

                $html .= '  <tr>
                                <td>' . $journal->account_number . '</td>
                                <td>' . $journal->account_name . '</td>
                                <td>' . $journal->description . '</td>
                                <td style="text-align:right;">' . number_format($journal->debit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->credit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->local_debit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->local_credit, 2) . '</td>
                            </tr>';
            }

            $html .= '      <tr>
                                <td colspan="3"><b>BALANCE TOTAL</b></td>
                                <td style="text-align:right;">' . @number_format($total_debit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($total_credit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($local_total_debit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($local_total_credit, 2) . '</td>
                            </tr>
                        </table>
                    </div>';

            $html .= '</table>
                <br>
                <table style="width:100%; font-size:12px;">
                    <tr>
                        <td style="text-align:center;">Prepared By</td>
                        <td style="text-align:center;">Checked By</td>
                        <td style="text-align:center;">Checked By</td>
                        <td style="text-align:center;">Approved By</td>
                    </tr>
                    <tr>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                    </tr>
                    <tr>
                        <th style="height:20px; text-align:center;">' . $this->session->name . '<hr style="width:60%;margin-left:20%;">User Entry</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Assistant Manager</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Finance Accounting Manager</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Director</th>
                    </tr>
                </table>
                </div>
            </div>';

        $html .= "</div></div><script>window.print()</script></body>";
        die($html);
    }

    public function print_voucher($payment) // perbaikan undefined supplier_id
    {
        $payment_no = base64_decode($payment);

        /** -- existing query --
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('ap_payments a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->like('a.payment_no', $payment_no);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.payment_date', 'DESC');
        $payment_total = $this->db->get()->result_array();
        */

        // Ambil data lengkap di awal (Master Data)
        $this->db->select('a.*, b.name as supplier_name, c.bank_name');
        $this->db->from('ap_payments a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id', 'left'); // Gunakan LEFT JOIN
        $this->db->join('account_banks c', 'a.bank_account = c.bank_account', 'left'); // Gunakan LEFT JOIN
        $this->db->where('a.payment_no', $payment_no);
        $payment_total = $this->db->get()->result_array();

        // Proteksi jika data benar-benar tidak ada di database
        if (empty($payment_total)) {
            die("Error: Data payment tidak ditemukan untuk nomor: " . $payment_no);
        }

        // Ambil data default jika records kosong
        $header_supplier = !empty($payment_total) ? $payment_total[0]['supplier_name'] : '-';
        $header_pay_by   = !empty($payment_total) ? $payment_total[0]['payment_by'] : '-';
        $header_pay_no   = !empty($payment_total) ? $payment_total[0]['payment_no'] : $payment_no;
        $header_date     = !empty($payment_total) ? date("d F Y", strtotime($payment_total[0]['payment_date'])) : date("d F Y");
        $header_bank_acc = !empty($payment_total) ? $payment_total[0]['bank_account'] : '-';
        $header_bank_nm  = !empty($payment_total) ? $payment_total[0]['bank_name'] : '-';
        $header_curr     = !empty($payment_total) ? $payment_total[0]['currency'] : 'IDR';

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 40;
        $page = ceil(count($payment_total) / $rows);
        //Generate QRcode
        // $this->createQrcode(@$payment_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $payment_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        @media screen {
                            .print {
                                display: none !important;
                            }
                        }
            
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }
                    </style>
                    <body>
                    <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <p>Display pages for 40 rows</p>
                            <p>Paper Size A4, Layout Landscape</p>
                            <p>Margin Default, Scale 95</p>
                        </center>
                    </div>
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        $grand_total = 0;

        for ($i = 0; $i < $page; $i++) {

            $this->db->select('a.*, b.name as supplier_name, c.bank_name');
            $this->db->from('ap_payments a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id', 'left');
            $this->db->join('account_banks c', 'a.bank_account = c.bank_account');
            $this->db->like('a.payment_no', $payment_no);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.payment_date', 'DESC');
            //$this->db->group_by('a.payment_no');
            $this->db->limit(40, ($i * 40));
            $records = $this->db->get()->result_array();

            //Exchange Rate
            $payment_date = $records[0]['payment_date'];
            $currency = $records[0]['currency'];

            $search_date = date("d", strtotime($payment_date));
            if($search_date == "31"){
              $payment_date = date("Y-m-d", strtotime('-1 days', strtotime($payment_date)));
            }

            $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($payment_date)));
            $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

            if ($exchange) {
                $amount = $exchange->middle;
                $hide = "";
            } else {
                $amount = 0;
                $hide = "hidden";
            }
            // <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $payment_no . '.png') . '" width="60"/></td>
            $exchangeName = "Rp. " . number_format($amount, 2);

            $html .= '<table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <td width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_ap_payment . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_ap_payment . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print Date</td>
                                            <td>:</td>
                                            <td>' . date("Y-m-d H:i") . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print By</td>
                                            <td>:</td>
                                            <td>' . $this->session->name . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div style="border: none; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>PAYMENT VOUCHER</h3>
                                </center>
                                <div style="float:left; width:40%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Pay To</td>
                                            <td width="10">:</td>
                                            <td><b>' . $header_supplier . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Payment By</td>
                                            <td width="10">:</td>
                                            <td><b>' . $header_pay_by . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:30%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Payment No</td>
                                            <td width="10">:</td>
                                            <td><b>' . $header_pay_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="80">Payment Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . $header_date . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Bank Account</td>
                                            <td width="10">:</td>
                                            <td><b>' . $header_bank_acc . '</b></td>
                                        </tr>
                                         <tr>
                                            <td width="50">Bank Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . $header_bank_nm . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div ' . $hide . ' style="float:left; width:25%; border:2px solid black; padding:10px; font-size:12px; margin-left:20px;"> 
                                    <p style="margin:0;">Rate USD to IDR : <b>' . $exchangeName . '</b></p>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th>No</th>
                                        <th>Purchase Invoice No</th>
                                        <th>Supplier Invoice No</th>
                                        <th>Currency</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                        <th>Payment</th>
                                    </tr>';
            // $grand_total = 0;
            foreach ($records as $record) {
                if ($record['account_type'] == "DEBIT") {
                    $grand_total += $record['payment'];
                    $subtotal += $record['payment'];
                } else {
                    $grand_total -= $record['payment'];
                    $subtotal -= $record['payment'];
                }

                if($record['account_type'] == "CREDIT"){
                    $payment = "(".@number_format($record['payment'], 2).")";
                }else{
                    $payment = @number_format($record['payment'], 2);
                }

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['purchase_invoice'] . '</td>
                                <td>' . $record['supplier_invoice'] . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . @number_format(($record['amount']), 2) . '</td>
                                <td style="text-align:right;">' . @number_format($record['balance'], 2) . '</td>
                                <td style="text-align:right;">' . $payment . '</td>
                            </tr>';
                $no++;
            }

            // if (($i + 1) == $page) {
            //     $html_grand_total = '<tr>
            //                 <th style="text-align:right" colspan="6">GRAND TOTAL</th>
            //                 <th style="text-align:right;">' . @number_format($subtotal, 2) . '</th>
            //             </tr>';
            // }else{
            //     $html_grand_total = "";
            // }

            // $html .= '  <tr>
            //                 <th style="text-align:right" colspan="6">SUB TOTAL</th>
            //                 <th style="text-align:right;">' . @number_format($grand_total, 2) . '</th>
            //             </tr>
            //             '.$html_grand_total.'
            //         </table>';

            // $html .= '  <tr>
            //                 <th style="text-align:right" colspan="6">GRAND TOTAL</th>
            //                 <th style="text-align:right;">' . @number_format($grand_total, 2) . '</th>
            //             </tr>
            //         </table>';

            if (($i + 1) == $page) { 
                $html .= '  <tr>
                                <th style="text-align:right" colspan="6">GRAND TOTAL</th>
                                <th style="text-align:right;">' . @number_format($grand_total, 2) . '</th>
                            </tr>';
            }
            
            $html .= '</table>';

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }

        $display_currency = !empty($payment_total) ? $payment_total[0]['currency'] : '';
        $display_note = !empty($payment_total) ? $payment_total[0]['note'] : '';

        //<td>' . $this->convertcurrency->convertCurrencyToWords($subtotal, $records[0]['currency']) . '</td>
        $html .= '<div style="width:100%; float:left;">
                        <table id="customers" style="margin-top:10px;">
                            <tr>
                                <th style="text-align:center;">Amount in Words</th>
                                <td>' . $this->convertcurrency->convertCurrencyToWords($grand_total, $display_currency) . '</td>
                               
                            </tr>
                        </table>
                        <p style="font-size:12px;"><i>Note: ' . $display_note . '</i>
                        <i>*This Payment Voucher was prepared by ' . $config->name . '</i></p>
                    </div>
                    <div style="width:100%; float:left; margin-bottom:20px;">
                        <table id="customers" style="width:100%; font-size:12px;">
                            <tr>
                                <td rowspan="2" style="font-weight:bold;">Account No</td>
                                <td rowspan="2" style="font-weight:bold;">Account Name</td>
                                <td rowspan="2" style="font-weight:bold;">Description</td>
                                <td colspan="2" style="font-weight:bold;">Original Currency</td>
                                <td colspan="2" style="font-weight:bold;">Local Currency</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Debit</td>
                                <td style="font-weight:bold;">Credit</td>
                                <td style="font-weight:bold;">Debit</td>
                                <td style="font-weight:bold;">Credit</td>
                            </tr>';
            $journals = $this->crud->query("SELECT a.*, b.account_name 
            FROM ap_payment_journals a 
            JOIN account_coa b ON a.account_number = b.account_number
            WHERE a.payment_no = '$payment_no' ORDER BY a.flag ASC");

            $total_debit = 0;
            $total_credit = 0;
            $local_total_debit = 0;
            $local_total_credit = 0;
            foreach ($journals as $journal) {

                $total_debit += $journal->debit;
                $total_credit += $journal->credit;
                $local_total_debit += $journal->local_debit;
                $local_total_credit += $journal->local_credit;

                $html .= '  <tr>
                                <td>' . $journal->account_number . '</td>
                                <td>' . $journal->account_name . '</td>
                                <td>' . $journal->description . '</td>
                                <td style="text-align:right;">' . number_format($journal->debit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->credit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->local_debit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->local_credit, 2) . '</td>
                            </tr>';
            }

            $html .= '      <tr>
                                <td colspan="3"><b>BALANCE TOTAL</b></td>
                                <td style="text-align:right;">' . @number_format($total_debit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($total_credit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($local_total_debit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($local_total_credit, 2) . '</td>
                            </tr>
                        </table>
                    </div>';

            $html .= '</table>
                <br>
                <table style="width:100%; font-size:12px;">
                    <tr>
                        <td style="text-align:center;">Prepared By</td>
                        <td style="text-align:center;">Checked By</td>
                        <td style="text-align:center;">Checked By</td>
                        <td style="text-align:center;">Approved By</td>
                    </tr>
                    <tr>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                    </tr>
                    <tr>
                        <th style="height:20px; text-align:center;">' . $this->session->name . '<hr style="width:60%;margin-left:20%;">User Entry</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Assistant Manager</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Finance Accounting Manager</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Director</th>
                    </tr>
                </table>
                </div>
            </div>';

        $html .= "</div></div><script>window.print()</script></body>";
        die($html);
    }


    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=ap_payment_$format.xls");

            // Tambahkan Byte Order Mark (BOM) untuk membantu Excel mengenali encoding
            echo "\xEF\xBB\xBF";
            echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        }

        $filter_payment_type  = base64_decode($this->input->get('filter_payment_type'));
        $filter_payment_date_from = base64_decode($this->input->get('filter_payment_date_from'));
        $filter_payment_date_to = base64_decode($this->input->get('filter_payment_date_to'));
        $filter_payment_no = base64_decode($this->input->get('filter_payment_no'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_payment_by = base64_decode($this->input->get('filter_payment_by'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('ap_payments a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->like('a.payment_type', $filter_payment_type);
        if ($filter_payment_date_from != "" && $filter_payment_date_to != "") {
            $this->db->where("a.payment_date between '$filter_payment_date_from' and '$filter_payment_date_to'");
        }
        $this->db->like('a.payment_no', $filter_payment_no);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.purchase_invoice', $filter_invoice_no);
        $this->db->like('a.bank_account', $filter_bank_no);
        $this->db->like('a.payment_by', $filter_payment_by);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.payment_date', 'DESC');
        $this->db->group_by('a.payment_no');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>REPORT AP PAYMENT</small><br>
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
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Payment Type</th>
                    <th colspan="2">Payment No</th>
                    <th>Payment Date</th>
                    <th>Supplier Name</th>
                    <th>Bank Account</th>
                    <th>Payment By</th>
                    <th colspan="2">Note</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $payment_no = $data['payment_no'];

            $this->db->select('*');
            $this->db->from('ap_payments');
            $this->db->where('payment_no', $payment_no);
            $this->db->group_by('purchase_invoice');
            $this->db->order_by('status', 'ASC');
            $this->db->order_by('purchase_invoice', 'DESC');
            $details = $this->db->get()->result_array();

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['payment_type'] . '</td>
                            <td colspan="2">' . $data['payment_no'] . '</td>
                            <td>' . $data['payment_date'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['bank_account'] . '</td>
                            <td>' . $data['payment_by'] . '</td>
                            <td colspan="2">' . $data['note'] . '</td>
                        </tr>';
            $html .= '  <tr>
                            <td colspan="11" style="background:#D1FFC6;"><b>DETAIL OF ' . $data['payment_no'] . '</b></td>
                        </tr>
                        <tr>
                            <th width="20"></th>
                            <th>Purchase Invoice</th>
                            <th>Supplier Invoice</th>
                            <th>Currency</th>
                            <th>Amount</th>
                            <th>Balance</th>
                            <th>Payment</th>
                            <th>Remarks</th>
                            <th>Account No</th>
                            <th>Debt/Credit</th>
                        </tr>';
            foreach ($details as $detail) {
                $html .= '  <tr>
                                <td></td>
                                <td>' . $detail['purchase_invoice'] . '</td>
                                <td>' . $detail['supplier_invoice'] . '</td>
                                <td>' . $detail['currency'] . '</td>
                                <td style="text-align:right">' . number_format($detail['amount'], 2, ",", ".") . '</td>
                                <td style="text-align:right">' . number_format($detail['balance'], 2, ",", ".") . '</td>
                                <td style="text-align:right">' . number_format($detail['payment'], 2, ",", ".")  . '</td>
                                <td>' . $detail['remarks'] . '</td>
                                <td>' . $detail['account_number'] . '</td>
                                <td>' . $detail['account_type'] . '</td>
                            </tr>';
            }
            $no++;
        }
        $html .= '</table>';
        $html .= '  <table id="customers" style="margin-top:20px; width:50%;">
                        <tr>
                            <th width="200" style="text-align:center;">Approval By</th>
                            <th width="200" style="text-align:center;">Dept Manager</th>
                            <th width="200" style="text-align:center;">Created By</th>
                        </tr>
                        <tr>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                        </tr>
                    </table></body></html>';
        echo $html;
    }

    public function printDetail($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=ap_payment_detail_$format.xls");
        }

        $filter_payment_type  = base64_decode($this->input->get('filter_payment_type'));
        $filter_payment_date_from = base64_decode($this->input->get('filter_payment_date_from'));
        $filter_payment_date_to = base64_decode($this->input->get('filter_payment_date_to'));
        $filter_payment_no = base64_decode($this->input->get('filter_payment_no'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_payment_by = base64_decode($this->input->get('filter_payment_by'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as supplier_name, c.name as journal_type_name, d.account_name, e.bank_name');
        $this->db->from('ap_payments a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('journal_types c', 'a.journal_type_id = c.id', 'left');
        $this->db->join('account_coa d', 'a.account_number = d.account_number', 'left');
        $this->db->join('account_banks e', 'a.bank_account = e.bank_account', 'left');
        $this->db->like('a.payment_type', $filter_payment_type);
        if ($filter_payment_date_from != "" && $filter_payment_date_to != "") {
            $this->db->where("a.payment_date between '$filter_payment_date_from' and '$filter_payment_date_to'");
        }
        $this->db->like('a.payment_no', $filter_payment_no);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.purchase_invoice', $filter_invoice_no);
        $this->db->like('a.bank_account', $filter_bank_no);
        $this->db->like('a.payment_by', $filter_payment_by);
        $this->db->order_by('b.name', 'ASC');
        $this->db->order_by('a.payment_no', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>REPORT AP PAYMENT DETAIL</small><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br>
            <center>
                <h2>REPORT AP PAYMENT DETAIL</h2>
            </center>
            <br><br>
            <table style="width:50%;">
                <tr>
                    <td width="100">Trans Date</td>
                    <td width="20">:</td>
                    <td>' . $filter_payment_date_from . ' - ' . $filter_payment_date_to . '</td>
                </tr>
            </table>
            <br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Journal Type</th>
                    <th>Payment No</th>
                    <th>Payment Date</th>
                    <th>Supplier Name</th>
                    <th>Bank Account</th>
                    <th>Payment By</th>
                    <th>Note</th>
                    <th>Purchase Invoice</th>
                    <th>Supplier Invoice</th>
                    <th>Currency</th>
                    <th>Amount</th>
                    <th>Balance</th>
                    <th>Payment</th>
                    <th>Remark</th>
                    <th>Account No</th>
                    <th>Account Name</th>
                    <th>Debit/Credit</th>
                    <th>Created By</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['journal_type_name'] . '</td>
                            <td>' . $data['payment_no'] . '</td>
                            <td>' . $data['payment_date'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['bank_account'] . ' - ' . $data['bank_name'] . '</td>
                            <td>' . $data['payment_by'] . '</td>
                            <td>' . $data['note'] . '</td>
                            <td>' . $data['purchase_invoice'] . '</td>
                            <td>' . $data['supplier_invoice'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['amount'] . '</td>
                            <td>' . $data['balance'] . '</td>
                            <td>' . $data['payment'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                            <td>' . $data['account_number'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                            <td>' . $data['account_type'] . '</td>
                            <td>' . $data['created_by'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function printJournal($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=ap_payment_journal_$format.xls");
        }

        $filter_payment_type  = base64_decode($this->input->get('filter_payment_type'));
        $filter_payment_date_from = base64_decode($this->input->get('filter_payment_date_from'));
        $filter_payment_date_to = base64_decode($this->input->get('filter_payment_date_to'));
        $filter_payment_no = base64_decode($this->input->get('filter_payment_no'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_payment_by = base64_decode($this->input->get('filter_payment_by'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.purchase_invoice, b.payment_date, b.payment_by, b.note, b.bank_account, c.name as supplier_name, d.name as journal_type_name, e.account_name, f.bank_name');
        $this->db->from('ap_payment_journals a');
        $this->db->join('ap_payments b', 'a.payment_no = b.payment_no');
        $this->db->join('suppliers c', 'b.supplier_id = c.id');
        $this->db->join('journal_types d', 'b.journal_type_id = d.id', 'left');
        $this->db->join('account_coa e', 'a.account_number = e.account_number', 'left');
        $this->db->join('account_banks f', 'b.bank_account = f.bank_account', 'left');
        $this->db->like('b.payment_type', $filter_payment_type);
        if ($filter_payment_date_from != "" && $filter_payment_date_to != "") {
            $this->db->where("b.payment_date between '$filter_payment_date_from' and '$filter_payment_date_to'");
        }
        $this->db->like('b.payment_no', $filter_payment_no);
        $this->db->like('b.supplier_id', $filter_supplier);
        $this->db->like('b.purchase_invoice', $filter_invoice_no);
        $this->db->like('b.bank_account', $filter_bank_no);
        $this->db->like('b.payment_by', $filter_payment_by);
        $this->db->group_by('a.payment_no');
        $this->db->group_by('a.account_number');
        $this->db->order_by('c.name', 'ASC');
        $this->db->order_by('a.payment_no', 'ASC');
        $this->db->order_by('a.flag', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br>
            <center>
                <h2>REPORT AP PAYMENT JOURNAL</h2>
            </center>
            <br><br>
            <table style="width:50%;">
                <tr>
                    <td width="100">Trans Date</td>
                    <td width="20">:</td>
                    <td>' . $filter_payment_date_from . ' - ' . $filter_payment_date_to . '</td>
                </tr>
            </table>
            <br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Journal Type</th>
                    <th rowspan="2">Payment No</th>
                    <th rowspan="2">Payment Date</th>
                    <th rowspan="2">Payment By</th>
                    <th rowspan="2">Purchase Invoice</th>
                    <th rowspan="2">Supplier Name</th>
                    <th rowspan="2">Bank Account</th>
                    <th rowspan="2">Note</th>
                    <th rowspan="2">Account No</th>
                    <th rowspan="2">Account Name</th>
                    <th rowspan="2">Description</th>
                    <th colspan="2">Orginal Currency</th>
                    <th colspan="2">Local Currency</th>
                </tr>
                <tr>
                    <th>Debit</th>
                    <th>Credit</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['journal_type_name'] . '</td>
                            <td>' . $data['payment_no'] . '</td>
                            <td>' . $data['payment_date'] . '</td>
                            <td>' . $data['payment_by'] . '</td>
                            <td>' . $data['purchase_invoice'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['bank_account'] . ' - ' . $data['bank_name'] . '</td>
                            <td>' . $data['note'] . '</td>
                            <td>' . $data['account_number'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                            <td>' . $data['description'] . '</td>
                            <td>' . $data['debit'] . '</td>
                            <td>' . $data['credit'] . '</td>
                            <td>' . $data['local_debit'] . '</td>
                            <td>' . $data['local_credit'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    // Show GL and Hyperlink each transaction (Report_general_ledgers)
    public function print_voucher_gl($payment)
    {
        $payment_no = base64_decode($payment);
        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('ap_payments a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->like('a.payment_no', $payment_no);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.payment_date', 'DESC');
        $payment_total = $this->db->get()->result_array();

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        //Config Page
        $rows = 40;
        $page = ceil(count($payment_total) / $rows);
        //Generate QRcode
        // $this->createQrcode(@$payment_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $payment_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        .link-transaction {
                            color: inherit;
                            text-decoration: none;
                        }
                        .link-transaction:hover {
                            color: inherit;
                            font-weight: bolder;
                            text-decoration: underline;
                        }
                        @media screen {
                            .print {
                                display: none !important;
                            }
                        }
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }
                    </style>
                <body>';
        
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.name as supplier_name, c.bank_name');
            $this->db->from('ap_payments a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('account_banks c', 'a.bank_account = c.bank_account');
            $this->db->like('a.payment_no', $payment_no);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.payment_date', 'DESC');
            //$this->db->group_by('a.payment_no');
            $this->db->limit(40, ($i * 40));
            $records = $this->db->get()->result_array();

            //Exchange Rate
            $payment_date = $records[0]['payment_date'];
            $currency = $records[0]['currency'];

            $search_date = date("d", strtotime($payment_date));
            if($search_date == "31"){
              $payment_date = date("Y-m-d", strtotime('-1 days', strtotime($payment_date)));
            }

            $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($payment_date)));
            $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

            if ($exchange) {
                $amount = $exchange->middle;
                $hide = "";
            } else {
                $amount = 0;
                $hide = "hidden";
            }
            
            $exchangeName = "Rp. " . number_format($amount, 2);

            $html .= '<table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <td width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_ap_payment . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_ap_payment . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print Date</td>
                                            <td>:</td>
                                            <td>' . date("Y-m-d H:i") . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print By</td>
                                            <td>:</td>
                                            <td>' . $this->session->name . '</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <div style="border: none; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>PAYMENT VOUCHER</h3>
                                </center>
                                <div style="float:left; width:40%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Pay To</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['supplier_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Payment By</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['payment_by'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:30%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="80">Payment No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['payment_no'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="80">Payment Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @date("d F Y", strtotime($records[0]['payment_date'])) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Bank Account</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['bank_account'] . '</b></td>
                                        </tr>
                                         <tr>
                                            <td width="50">Bank Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['bank_name'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div ' . $hide . ' style="float:left; width:25%; border:2px solid black; padding:10px; font-size:12px; margin-left:20px;"> 
                                    <p style="margin:0;">Rate USD to IDR : <b>' . $exchangeName . '</b></p>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th>No</th>
                                        <th>Purchase Invoice No</th>
                                        <th>Supplier Invoice No</th>
                                        <th>Currency</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                        <th>Payment</th>
                                    </tr>';
            $grand_total = 0;
            foreach ($records as $record) 
            {
                if ($record['account_type'] == "DEBIT") {
                    $grand_total += $record['payment'];
                    $subtotal += $record['payment'];
                } else {
                    $grand_total -= $record['payment'];
                    $subtotal -= $record['payment'];
                }

                // --- Link transaksi GL Posting Journal
                $linked_balance = $this->createLink($record['balance'], $record['purchase_invoice']);
                $linked_payment = $this->createLink($record['payment'], $record['purchase_invoice']);

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['purchase_invoice'] . '</td>
                                <td>' . $record['supplier_invoice'] . '</td>
                                <td>' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . @number_format(($record['amount']), 2) . '</td>
                                <td style="text-align:right;">' . $linked_balance . '</td>
                                <td style="text-align:right;">' . $linked_payment . '</td>
                            </tr>';
                $no++;
            }

            if (($i + 1) == $page) { 
                $html .= '  <tr>
                                <th style="text-align:right" colspan="6">GRAND TOTAL</th>
                                <th style="text-align:right;">' . @number_format($grand_total, 2) . '</th>
                            </tr>';
            }
            
            $html .= '</table>';

            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }

        //<td>' . $this->convertcurrency->convertCurrencyToWords($subtotal, $records[0]['currency']) . '</td>
        $html .= '<div style="width:100%; float:left;">
                        <table id="customers" style="margin-top:10px;">
                            <tr>
                                <th style="text-align:center;">Amount in Words</th>
                                <td>' . $this->convertcurrency->convertCurrencyToWords($grand_total, $records[0]['currency']) . '</td>
                               
                            </tr>
                        </table>
                        <p style="font-size:12px;"><i>Note: ' . @$records[0]['note'] . '</i>
                        <i>*This Payment Voucher was prepared by ' . $config->name . '</i></p>
                    </div>
                    <div style="width:100%; float:left; margin-bottom:20px;">
                        <table id="customers" style="width:100%; font-size:12px;">
                            <tr>
                                <td rowspan="2" style="font-weight:bold;">Account No</td>
                                <td rowspan="2" style="font-weight:bold;">Account Name</td>
                                <td rowspan="2" style="font-weight:bold;">Description</td>
                                <td colspan="2" style="font-weight:bold;">Original Currency</td>
                                <td colspan="2" style="font-weight:bold;">Local Currency</td>
                            </tr>
                            <tr>
                                <td style="font-weight:bold;">Debit</td>
                                <td style="font-weight:bold;">Credit</td>
                                <td style="font-weight:bold;">Debit</td>
                                <td style="font-weight:bold;">Credit</td>
                            </tr>';
            $journals = $this->crud->query("SELECT a.*, b.account_name 
            FROM ap_payment_journals a 
            JOIN account_coa b ON a.account_number = b.account_number
            WHERE a.payment_no = '$payment_no' ORDER BY a.flag ASC");

            $total_debit = 0;
            $total_credit = 0;
            $local_total_debit = 0;
            $local_total_credit = 0;
            foreach ($journals as $journal) {

                $total_debit += $journal->debit;
                $total_credit += $journal->credit;
                $local_total_debit += $journal->local_debit;
                $local_total_credit += $journal->local_credit;

                $html .= '  <tr>
                                <td>' . $journal->account_number . '</td>
                                <td>' . $journal->account_name . '</td>
                                <td>' . $journal->description . '</td>
                                <td style="text-align:right;">' . number_format($journal->debit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->credit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->local_debit, 2) . '</td>
                                <td style="text-align:right;">' . number_format($journal->local_credit, 2) . '</td>
                            </tr>';
            }

            $html .= '      <tr>
                                <td colspan="3"><b>BALANCE TOTAL</b></td>
                                <td style="text-align:right;">' . @number_format($total_debit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($total_credit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($local_total_debit, 2) . '</td>
                                <td style="text-align:right;">' . @number_format($local_total_credit, 2) . '</td>
                            </tr>
                        </table>
                    </div>';

            $html .= '</table>
                <br>
                <table style="width:100%; font-size:12px;">
                    <tr>
                        <td style="text-align:center;">Prepared By</td>
                        <td style="text-align:center;">Checked By</td>
                        <td style="text-align:center;">Checked By</td>
                        <td style="text-align:center;">Approved By</td>
                    </tr>
                    <tr>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                    </tr>
                    <tr>
                        <th style="height:20px; text-align:center;">' . $this->session->name . '<hr style="width:60%;margin-left:20%;">User Entry</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Assistant Manager</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Finance Accounting Manager</th>
                        <th style="height:20px; text-align:center;"><br><hr style="width:60%;margin-left:20%;">Director</th>
                    </tr>
                </table>
                </div>
            </div>';

        $html .= "</div> </body>";
        die($html);
    }

    // get link detail transaksi GL
    function createLink($value, $idLink) 
    {
        // validasi tidak input manual number PI
        if (isset($idLink) && stripos($idLink, 'PI-') !== false)
        {
            $base_url   = base_url('finance/purchase_invoices/print_invoicing/');
            $id_encoded = base64_encode($idLink);
            $url        = $base_url . $id_encoded . "/GL";
            
            if ($value > 0) {
                return '<a href="javascript:void(0)" onclick="window.open(\'' . $url . '\', \'_blank\', \'location=yes,height=600,width=1200,scrollbars=yes,status=yes\');" class="link-transaction">' . $this->formatIDR($value, 2) . '</a>';
            }
        }
        return $this->formatIDR($value, 2);
    }

    function formatIDR($number, $decimal_places = 2) {
        $formatted_number = number_format($number, $decimal_places, ',', '.');
        return $formatted_number;
    }
}
