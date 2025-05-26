<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Foreign_currencies extends CI_Controller
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
            $this->load->view('finance/foreign_currencies');
        } else {
            redirect('error_access');
        }
    }

    public function readYears()
    {
        $tahun_before = date('Y', strtotime('-10 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        for ($i = $tahun_next; $i >= $tahun_before; $i--) {
            $arr[] = array("id" => $i, "name" => $i);
        }

        echo json_encode($arr);
    }

    public function readMonths()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array("id" => $key, "name" => $value);
        }

        echo json_encode($arr);
    }

    public function number()
    {
        $datenow    = "CR" . date("y");
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM journal_revaluations WHERE `number` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }

        return $datenow . $autoID;
    }

    function readExchangeRate($currency, $payment_date, $type = "")
    {
        $search_date = date("d", strtotime($payment_date));
        if($search_date == "31"){
          $payment_date = date("Y-m-d", strtotime('-1 days', strtotime($payment_date)));
        }
        
        if(date("m", strtotime($payment_date)) == "10"){
            if($type != ""){
                $monthNow = date('Y-m-t', strtotime($payment_date));
                $monthBf = date('Y-m-01', strtotime($payment_date));
            }else{
                $monthNow = date('Y-m-t', strtotime($payment_date));
                $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($payment_date)));
            }
        }else{
            $monthNow = date('Y-m-01', strtotime($payment_date));
            $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($payment_date)));
        }

        $exchangeNow = $this->crud->read('exchange_rates', [], ["start_date" => $monthNow, "currency_from" => $currency, "currency_to" => "IDR"]);
        $exchangeBf = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

        if ($exchangeNow) {
            $amount_now = $exchangeNow->middle;
        } else {
            $amount_now = 1;
        }

        if ($exchangeBf) {
            $amount_bf = $exchangeBf->middle;
        } else {
            $amount_bf = 1;
        }

        return array("amount_now" => $amount_now, "amount_bf" => $amount_bf);
    }

    public function getData()
    {
        if ($this->input->post()) {
            $month = $this->input->post('month');
            $year = $this->input->post('year');
            $modul = $this->input->post('modul');
            $filter_account = $this->input->post('filter_account');
            $type = $this->input->post('double');

            $periode = $year . '-' . $month;
            $filter_from = date("Y-m-01", strtotime($periode));
            $filter_exchange = date("Y-m-10", strtotime($periode));
            $filter_to = date("Y-m-t", strtotime($periode));
            $date = date("Y-m-t", strtotime($periode));

            if ($modul == "PURCHASE INVOICING") {
                $this->db->select('b.*, a.currency, a.trans_date, c.payment, (abs(b.debit - b.credit) - COALESCE(c.payment, 0)) as total');
                $this->db->from('purchase_invoice_journals b');
                $this->db->join('purchase_invoices a', 'a.number = b.number');
                $this->db->join("(select purchase_invoice, SUM(payment) as payment from ap_payments WHERE DATE_FORMAT(payment_date, '%Y-%m') < '$periode' GROUP BY purchase_invoice) c", 'a.number = c.purchase_invoice', 'left');
                // $this->db->where("a.trans_date between '$filter_from' and '$filter_to'");
                $this->db->where_not_in("a.currency", ["IDR"]);
                $this->db->where_in("b.account_number", ['2-1120','2-1130','2-1140']);
                $this->db->where("DATE_FORMAT(a.trans_date, '%Y-%m') < '$periode'");
                // $this->db->where('a.status', 0);
                $this->db->where('(abs(b.debit - b.credit) - COALESCE(c.payment, 0)) > 0');
                $this->db->group_by('a.number');
                $this->db->order_by('a.number', 'desc');
                $records = $this->db->get()->result_array();

            } elseif($modul == "SALES INVOICING") {
                $this->db->select('a.*, b.currency, b.trans_date, c.receipt, (abs(a.debit - a.credit) - COALESCE(c.receipt, 0)) as total');
                $this->db->from('sales_invoice_journals a');
                $this->db->join('sales_invoices b', 'a.number = b.number');
                $this->db->join("(select sales_invoice, SUM(receipt) as receipt from ar_receipts WHERE DATE_FORMAT(receipt_date, '%Y-%m') < '$periode' GROUP BY sales_invoice) c", 'a.number = c.sales_invoice', 'left');
                $this->db->where_not_in("b.currency", ["IDR"]);
                $this->db->where_in("a.account_number", ['1-1312', '1-1314', '1-1316']);
                $this->db->where("DATE_FORMAT(b.trans_date, '%Y-%m') < '$periode'");
                // $this->db->where('b.status', 0);
                $this->db->where('(abs(a.debit - a.credit) - COALESCE(c.receipt, 0)) > 0');
                $this->db->group_by('a.number');
                $this->db->order_by('a.number', 'asc');
                $records = $this->db->get()->result_array();

            } else {
                $account_coa = $this->crud->read("account_coa", [], ["account_number" => $filter_account]);
                $coa_original = abs(@$account_coa->original_debit - @$account_coa->original_kredit);

                $ar_receipt_begin = $this->crud->query("SELECT z.account_number, 
                        (SUM(z.local_debit) - SUM(z.local_credit)) as local_begin, 
                        (SUM(z.debit) - SUM(z.credit)) as original_begin 
                FROM (SELECT a.*, b.currency, c.number as gl_no
                FROM ar_receipt_journals a 
                JOIN ar_receipts b ON a.receipt_no = b.receipt_no 
                LEFT JOIN journal_postings c ON b.receipt_no = c.document_no
                WHERE b.receipt_date < '$filter_from' and a.account_number = '$filter_account'  and b.currency != 'IDR'
                GROUP BY a.receipt_no, a.account_number) z GROUP BY z.account_number");
                $ar_begin_original = @$ar_receipt_begin[0]->original_begin;

                $ap_payment_begin = $this->crud->query("SELECT a.account_number, (SUM(abs(a.local_debit)) - SUM(abs(a.local_credit))) as local_begin, SUM(abs(a.debit)) - SUM(abs(a.credit)) as original_begin
                FROM (
                SELECT a.*
                FROM ap_payment_journals a 
                JOIN ap_payments b ON a.payment_no = b.payment_no 
                WHERE b.payment_date between '2023-01-01' and '$filter_from' and a.account_number = '$filter_account' and b.currency != 'IDR' GROUP BY a.account_number, b.payment_no) a
                GROUP BY a.account_number");
                $ap_begin_original = @$ap_payment_begin[0]->original_begin;

                // $journal_posting_begin = $this->crud->query("SELECT a.account_number, (SUM(a.local_debit) - SUM(a.local_credit)) as local_begin 
                // FROM journal_postings a 
                // JOIN journal_revaluations b ON a.document_no = b.number and b.flag = 2
                // WHERE a.invoice_no like '%CASHBANK%' and a.account_number = '$filter_account' and a.journal_date < '$filter_from' 
                // GROUP BY a.account_number");

                $ar_receipts = $this->crud->query("SELECT b.currency, (SUM(a.debit) - SUM(a.credit)) as total_ar FROM ar_receipt_journals a 
                JOIN (SELECT DISTINCT receipt_no, receipt_date, currency FROM ar_receipts WHERE currency != 'IDR') b ON a.receipt_no = b.receipt_no
                WHERE b.receipt_date BETWEEN '$filter_from' and '$filter_to' and a.account_number = '$filter_account' 
                GROUP BY a.account_number");

                $ap_payments = $this->crud->query("SELECT b.currency, (SUM(a.debit) - SUM(a.credit)) as total_ap FROM ap_payment_journals a 
                JOIN (SELECT DISTINCT payment_no, payment_date, currency FROM ap_payments WHERE currency != 'IDR') b ON a.payment_no = b.payment_no
                WHERE b.payment_date BETWEEN '$filter_from' and '$filter_to' and a.account_number = '$filter_account' 
                GROUP BY a.account_number");
                
                if(!empty($ar_receipts[0]->currency)){
                    $currency = @$ar_receipts[0]->currency;
                }elseif(!empty($ap_payments[0]->currency)){
                    $currency = @$ap_payments[0]->currency;
                }else{
                    $currency = $account_coa->original_currency;
                }

                if($currency != ""){
                    $records[] = array(
                        "number" => "CASHBANK-".$account_coa->account_number,
                        "trans_date" => $filter_to,
                        "currency" => $currency,
                        "account_number" => $account_coa->account_number,
                        "account_name" => $account_coa->account_name,
                        "original_balance" => ($coa_original + $ar_begin_original + $ap_begin_original + @$ar_receipts[0]->total_ar + @$ap_payments[0]->total_ap),
                    );
                }else{
                    $records = array();
                }
            }
            
            $total_qty = 0;
            $dataFinals = array();
            foreach ($records as $record) {
                if($record['currency'] != "IDR"){
                    if ($modul == "PURCHASE INVOICING") {
                        $this->db->select('*, SUM(payment) as total_ori_ap');
                        $this->db->from('ap_payments');
                        $this->db->where("purchase_invoice", $record['number']);
                        $this->db->where("DATE_FORMAT(payment_date, '%Y-%m') = '$periode'");
                        $this->db->where_not_in("account_number", ['6-8315','2-1303','9-0000']);
                        $this->db->group_by('purchase_invoice');
                        $invoice_ap = $this->db->get()->row();

                        $account_name = $record['account_name'];
                        $original_currency = abs(abs($record['total']) - abs(@$invoice_ap->total_ori_ap));
                    } elseif($modul == "SALES INVOICING") {
                        //Sales Return
                        $this->db->select('sales_invoice, SUM(receipt) as total');
                        $this->db->from('ar_receipts');
                        $this->db->where("sales_invoice", $record['number']);
                        $this->db->where_in("account_number", ['4-6004','4-6005','4-6009']);
                        $this->db->where("DATE_FORMAT(receipt_date, '%Y-%m') = '$periode'");
                        $this->db->group_by('sales_invoice');
                        $dataSub2 = $this->db->get()->row();

                        if(empty($dataSub2)){
                            $this->db->select('sales_invoice, SUM(receipt) as total');
                            $this->db->from('ar_receipts');
                            $this->db->where("sales_invoice", $record['number']);
                            $this->db->where_not_in("account_number", ['6-8315','2-1303','9-0000']);
                            $this->db->where("DATE_FORMAT(receipt_date, '%Y-%m') = '$periode'");
                            $this->db->group_by('sales_invoice');
                            $dataSub = $this->db->get()->row();
                            $account_name = $record['account_name'];
                            $original_currency = abs(abs($record['total']) - abs(@$dataSub->total));
                            $tsales = "";
                        }else{
                            $account_name = $record['account_name'];
                            $original_currency = abs(abs($record['total']) - abs(@$dataSub2->total));
                            $tsales = "YES";
                        }
                    } else {
                        $account_name = $record['account_name'];
                        $original_currency = abs(@$record['original_balance']);
                    }

                    if($original_currency > 0){
                        $exchange_rate = $this->readExchangeRate($record['currency'], $filter_exchange, $type);

                        if($type == ""){
                            $unique = "";
                        }else{
                            $unique = " (END)";
                        }

                        $revaluation_now = abs($original_currency * $exchange_rate['amount_now']);
                        $revaluation_bf = abs($original_currency * $exchange_rate['amount_bf']);
                        
                        if($record['currency'] != "IDR"){
                            $deviation = ($revaluation_bf - $revaluation_now);
                        }else{
                            $deviation = $original_currency;
                        }
                        
                        if($modul == "PURCHASE INVOICING"){
                            if ($deviation > 0) {
                                $debit_now = abs($deviation);
                                $debit_bf = 0;
                                $credit_now = 0;
                                $credit_bf = abs($deviation);
                                $journal_type = "20240926000002";
                                $flag_1 = 1;
                                $flag_2 = 2;
                                $trans_date_1 = $record['trans_date'];
                                $trans_date_2 = $date;
                                $rate_1 = $exchange_rate['amount_bf'];
                                $rate_2 = $exchange_rate['amount_now'];
                                $revaluation_now = abs($original_currency * $exchange_rate['amount_bf']);
                                $revaluation_bf = abs($original_currency * $exchange_rate['amount_now']);
                                $account_number_loss = "8-0006";
                                $account_name_loss = "GAIN ON FOREIGN EXCHANGE";
                            } else {
                                $debit_now = 0;
                                $debit_bf = abs($deviation);
                                $credit_now = abs($deviation);
                                $credit_bf = 0;
                                $journal_type = "20240926000001";
                                $flag_1 = 2;
                                $flag_2 = 1;
                                $trans_date_1 = $date;
                                $trans_date_2 = $record['trans_date'];
                                $rate_1 = $exchange_rate['amount_now'];
                                $rate_2 = $exchange_rate['amount_bf'];
                                $revaluation_now = abs($original_currency * $exchange_rate['amount_now']);
                                $revaluation_bf = abs($original_currency * $exchange_rate['amount_bf']);
                                $account_number_loss = "9-0010";
                                $account_name_loss = "LOSS ON FOREIGN EXCHANGE";
                            }
                        }elseif($modul == "SALES INVOICING"){
                            if($tsales == "YES"){
                                if ($deviation > 0) {
                                    $debit_now = abs($deviation);
                                    $debit_bf = 0;
                                    $credit_now = 0;
                                    $credit_bf = abs($deviation);
                                    $journal_type = "20240926000004";
                                    $flag_1 = 1;
                                    $flag_2 = 2;
                                    $trans_date_1 = $record['trans_date'];
                                    $trans_date_2 = $date;
                                    $rate_1 = $exchange_rate['amount_bf'];
                                    $rate_2 = $exchange_rate['amount_now'];
                                    $revaluation_now = abs($original_currency * $exchange_rate['amount_bf']);
                                    $revaluation_bf = abs($original_currency * $exchange_rate['amount_now']);
                                    $account_number_loss = "8-0006";
                                    $account_name_loss = "GAIN ON FOREIGN EXCHANGE";
                                } else {
                                    $debit_now = 0;
                                    $debit_bf = abs($deviation);
                                    $credit_now = abs($deviation);
                                    $credit_bf = 0;
                                    $journal_type = "20240926000003";
                                    $flag_1 = 2;
                                    $flag_2 = 1;
                                    $trans_date_1 = $date;
                                    $trans_date_2 = $record['trans_date'];
                                    $rate_1 = $exchange_rate['amount_now'];
                                    $rate_2 = $exchange_rate['amount_bf'];
                                    $revaluation_now = abs($original_currency * $exchange_rate['amount_now']);
                                    $revaluation_bf = abs($original_currency * $exchange_rate['amount_bf']);
                                    $account_number_loss = "9-0010";
                                    $account_name_loss = "LOSS ON FOREIGN EXCHANGE";
                                }
                            }else{
                                if ($deviation > 0) {
                                    $debit_now = 0;
                                    $debit_bf = abs($deviation);
                                    $credit_now = abs($deviation);
                                    $credit_bf = 0;
                                    $journal_type = "20240926000003";
                                    $flag_1 = 2;
                                    $flag_2 = 1;
                                    $trans_date_1 = $date;
                                    $trans_date_2 = $record['trans_date'];
                                    $rate_1 = $exchange_rate['amount_now'];
                                    $rate_2 = $exchange_rate['amount_bf'];
                                    $revaluation_now = abs($original_currency * $exchange_rate['amount_now']);
                                    $revaluation_bf = abs($original_currency * $exchange_rate['amount_bf']);
                                    $account_number_loss = "9-0010";
                                    $account_name_loss = "LOSS ON FOREIGN EXCHANGE";
                                } else {
                                    $debit_now = abs($deviation);
                                    $debit_bf = 0;
                                    $credit_now = 0;
                                    $credit_bf = abs($deviation);
                                    $journal_type = "20240926000004";
                                    $flag_1 = 1;
                                    $flag_2 = 2;
                                    $trans_date_1 = $record['trans_date'];
                                    $trans_date_2 = $date;
                                    $rate_1 = $exchange_rate['amount_bf'];
                                    $rate_2 = $exchange_rate['amount_now'];
                                    $revaluation_now = abs($original_currency * $exchange_rate['amount_bf']);
                                    $revaluation_bf = abs($original_currency * $exchange_rate['amount_now']);
                                    $account_number_loss = "8-0006";
                                    $account_name_loss = "GAIN ON FOREIGN EXCHANGE";
                                }
                            }
                        }else{
                            if ($deviation > 0) {
                                $debit_now = 0;
                                $debit_bf = abs($deviation);
                                $credit_now = abs($deviation);
                                $credit_bf = 0;
                                $journal_type = "20240926000006";
                                $flag_1 = 2;
                                $flag_2 = 1;
                                $trans_date_1 = $date;
                                $trans_date_2 = $record['trans_date'];
                                $rate_1 = $exchange_rate['amount_now'];
                                $rate_2 = $exchange_rate['amount_bf'];
                                $revaluation_now = abs($original_currency * $exchange_rate['amount_now']);
                                $revaluation_bf = abs($original_currency * $exchange_rate['amount_bf']);
                                $account_number_loss = "8-0006";
                                $account_name_loss = "GAIN ON FOREIGN EXCHANGE";
                            } else {
                                $debit_now = abs($deviation);
                                $debit_bf = 0;
                                $credit_now = 0;
                                $credit_bf = abs($deviation);
                                $journal_type = "20240926000005";
                                $flag_1 = 1;
                                $flag_2 = 2;
                                $trans_date_1 = $record['trans_date'];
                                $trans_date_2 = $date;
                                $rate_1 = $exchange_rate['amount_bf'];
                                $rate_2 = $exchange_rate['amount_now'];
                                $revaluation_now = abs($original_currency * $exchange_rate['amount_bf']);
                                $revaluation_bf = abs($original_currency * $exchange_rate['amount_now']);
                                $account_number_loss = "9-0010";
                                $account_name_loss = "LOSS ON FOREIGN EXCHANGE";
                            }
                        }

                        $dataFinals[] = array(
                            "journal_type_id" => $journal_type,
                            "number" => $this->number(),
                            "period" => $periode,
                            "modul" => $modul,
                            "document_no" => $record['number'] . $unique,
                            "trans_date" => $trans_date_1,
                            "qty" => abs($original_currency),
                            "rate" => $rate_1,
                            "revaluation" => $revaluation_now,
                            "account_number" => $record['account_number'],
                            "account_name" => $account_name,
                            "debit" => $debit_now,
                            "credit" => $credit_now,
                            "flag" => $flag_1,
                        );

                        $dataFinals[] = array(
                            "journal_type_id" => $journal_type,
                            "number" => $this->number(),
                            "period" => $periode,
                            "modul" => $modul,
                            "document_no" => $record['number'] . $unique,
                            "trans_date" => $trans_date_2,
                            "qty" => abs($original_currency),
                            "rate" => $rate_2,
                            "revaluation" => $revaluation_bf,
                            "account_number" => $account_number_loss,
                            "account_name" => $account_name_loss,
                            "debit" => $debit_bf,
                            "credit" => $credit_bf,
                            "flag" => $flag_2,
                        );
                    }
                }
            }

            $result['total'] = count($dataFinals);
            $result = array_merge($result, ['rows' => $dataFinals]);
            echo json_encode($result);
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->get()) {

            $filter_month = base64_decode($this->input->get('filter_month'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_journal_type = base64_decode($this->input->get('filter_journal_type'));
            $filter_modul = base64_decode($this->input->get('filter_modul'));

            $filter_period = $filter_year . "-" . $filter_month;

            //Select Query
            $this->db->select('a.*, c.number as gl_no, b.name as journal_type_name, c.company_name');
            $this->db->from('journal_revaluations a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id');
            $this->db->join('journal_postings c', 'a.number = c.document_no and a.account_number = c.account_number', 'left');
            $this->db->where('a.period', $filter_period);
            $this->db->like('a.journal_type_id', $filter_journal_type);
            $this->db->like('a.modul', $filter_modul);
            $this->db->group_by('a.document_no, a.trans_date, a.account_number');
            $this->db->order_by('a.document_no', 'asc');
            $this->db->order_by('a.flag', 'asc');

            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $journal_revaluations = $this->crud->read('journal_revaluations', [], ["period" => $post['period'], "document_no" => $post['document_no'], "account_number" => $post['account_number']]);

            if (@$journal_revaluations->id != "") {
                $send = $this->crud->update('journal_revaluations', ["period" => $post['period'], "document_no" => $post['document_no'], "account_number" => $post['account_number']], $post);
            } else {
                $send = $this->crud->create('journal_revaluations', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('journal_revaluations', $data);
        echo $send;
    }

    public function uploadclearFailed()
    {
        @unlink('failed/journal_revaluations.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/journal_revaluations.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/journal_revaluations.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=journal_revaluations_$format.xls");
        }

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_journal_type = base64_decode($this->input->get('filter_journal_type'));
        $filter_modul = base64_decode($this->input->get('filter_modul'));

        $filter_period = $filter_year . "-" . $filter_month;


        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Select Query
        $this->db->select('a.*, b.name as journal_type_name');
        $this->db->from('journal_revaluations a');
        $this->db->join('journal_types b', 'a.journal_type_id = b.id');
        $this->db->where('a.period', $filter_period);
        $this->db->like('a.journal_type_id', $filter_journal_type);
        $this->db->like('a.modul', $filter_modul);
        $this->db->order_by('a.trans_date', 'asc');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>CURRENCY REVALUATION PERIOD ' . $filter_period . '</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Period</th>
                <th rowspan="2">Document No</th>
                <th rowspan="2">Amount</th>
                <th rowspan="2">Rate</th>
                <th rowspan="2">Trans Date</th>
                <th rowspan="2">Amount</th>
                <th rowspan="2">Journal Name</th>
                <th rowspan="2">Account No</th>
                <th rowspan="2">Account Name</th>
                <th colspan="2">Revaluation</th>
            </tr>
            <tr>
                <th>Debit</th>
                <th>Credit</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['period'] . '</td>
                            <td>' . $data['document_no'] . '</td>
                            <td>' . number_format($data['qty'], 2) . '</td>
                            <td>' . number_format($data['rate'], 2) . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . number_format($data['revaluation'], 2) . '</td>
                            <td>' . $data['journal_type_name'] . '</td>
                            <td>' . $data['account_number'] . '</td>
                            <td>' . $data['account_name'] . '</td>
                            <td>' . number_format($data['debit'], 2) . '</td>
                            <td>' . number_format($data['credit'], 2) . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
