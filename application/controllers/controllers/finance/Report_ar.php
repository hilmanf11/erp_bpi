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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[50]');
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

    public function readPi($customer_id){
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

    public function print($option = "")
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
