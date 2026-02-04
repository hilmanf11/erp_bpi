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
class Report_general_ledgers extends CI_Controller
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
            $this->load->view('finance/report_general_ledgers');
        } else {
            redirect('error_access');
        }
    }

    public function readCategories() 
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->db->select('a.*, a.name as category_name');
        $this->db->where("a.name LIKE '%$post%' or a.number LIKE '%$post%'");
        $this->db->from('account_group_details a');
        
        $send = $this->db->get()->result_array();
        echo json_encode($send);
    }

    public function readCoa($category_number = "")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $category = isset($category_number) ? base64_decode($category_number) : "";

        $this->db->select('a.*, b.number as category_number');
        $this->db->from('account_coa a');
        $this->db->join('account_group_details b', 'b.id = a.account_group_detail_id');
        $this->db->where("(a.account_name LIKE '%$post%' or a.account_number LIKE '%$post%') AND b.number LIKE '%$category%' ");

        $send = $this->db->get()->result_array();
        echo json_encode($send);
    }

    public function print($option = "") 
    {
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_account = base64_decode($this->input->get("filter_account"));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $settings = ['favicon' => $config->favicon, 'name' => $config->name, 'description' => $config->description];

        $pattern = '/[^0-9.,]/';
        $filter_account = preg_replace($pattern, '', $filter_account);
        $filter_account = rtrim($filter_account, ',');
        
        $html = $this->calculate($settings, $filter_from, $filter_to, $filter_account, $option);
        echo $html;
    }

    // perhitungan dipisah agar dapat di loop jika multiple account_number
    public function calculate($settings, $filter_from, $filter_to, $filter_account, $option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_general_ledgers_$format.xls");
        }

        $filter_before = date("Y-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));

        $account_numbers = [];
        if (is_string($filter_account)) {
            $account_numbers = array_map('trim', explode(',', $filter_account));
        } elseif (is_array($filter_account)) {
            $account_numbers = $filter_account;
        }
        $account_numbers = array_filter($account_numbers);
        
        $html = '<html>
            <head>
                <title>Print Data</title>
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
                        border: 1px solid #ddd;
                        padding: 4px 8px; 
                        vertical-align: middle;
                    }
                    #customers th {
                        background-color: #f0f0f0;
                        text-align: center;
                        color: black;
                        font-weight: bold;
                    }
                    #customers tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    #customers tr:hover {
                        background-color: #f1f1f1;
                    }
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    .font-bold { font-weight: bold; }
                    .bg-light-green { background-color: #CAFFB3; } /* Untuk baris kelompok akun */
                    .bg-grey { background-color: #E0E0E0; } /* Untuk grand total */

                    .link-transaction {
                        color: inherit;
                        text-decoration: none;
                    }
                    .link-transaction:hover {
                        color: inherit;
                        font-weight: bolder;
                        text-decoration: underline;
                    }

                    .clearfix::after {
                        content: "";
                        clear: both;
                        display: table;
                    }
                </style>
            </head>
        <body>';
            
        // loop multiple filter_account
        foreach ($account_numbers as $filter_account_number) 
        {
            $html .= '<div class="company-info">
                    <table style="width: 100%; page-break-after: auto;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; margin-right:10px;">
                                <img src="' . $settings['favicon'] . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b style="font-size:14px;">' . $settings['name'] . '</b><br>
                                <span style="font-size:10px;">' . $settings['description'] . '</span><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="print-info">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br>
                <div class="report-title">
                    <h3>GENERAL LEDGER</h3>
                    <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
                </div>
                <br><br>
            
            <table id="customers" style="margin-bottom:50px;">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Transaction Date</th>
                <th rowspan="2">Voucher No</th>
                <th rowspan="2">Account No</th>
                <th rowspan="2">Account Name</th>
                <th rowspan="2">Description</th>
                <th rowspan="2">Currency</th>
                <th colspan="3">ORIGINAL CURRENCY</th>
                <th colspan="4">LOCAL CURRENCY</th>
            </tr>
            <tr>
                <th>Balance</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Rate</th>
                <th>Balance</th>
                <th>Debit</th>
                <th>Credit</th>
            </tr>';

            $this->db->select('*');
            $this->db->from('journal_postings');
            // $this->db->where("journal_date between '$filter_from' and '$filter_to'"); // ganti ke trans_date
            $this->db->where("trans_date between '$filter_from' and '$filter_to'");
            $this->db->where("account_number", $filter_account_number);
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
            // $this->db->where("journal_date between '$filter_before' and '$filter_before_to'");
            $this->db->where("trans_date between '$filter_before' and '$filter_before_to'");
            $this->db->where("account_number", $filter_account_number);
            $journal_bf = $this->db->get()->row();

            $this->db->select('*');
            $this->db->from('account_coa');
            $this->db->where("account_number", $filter_account_number);
            $this->db->where("starting_date <=", $filter_before); // Get Begin Balance before Y-01-01
            $account_coa = $this->db->get()->row();
            if (!$account_coa) {
                echo "<h3 style='font-family:monospace;'> Account Number ". $filter_account_number. " is unavailable! </h3>";
                return;
            }

            $journal_ori_debit = @$journal_bf->original_debit;
            $journal_ori_credit = @$journal_bf->original_credit;
            $journal_local_debit = @$journal_bf->local_debit;
            $journal_local_credit = @$journal_bf->local_credit;
            $begin_account_no = @$account_coa->account_number;

            $begin_balance_local = (@$account_coa->local_debit + $account_coa->local_kredit);
            $begin_balance_ori = (@$account_coa->original_debit + $account_coa->original_kredit);

            $journal_end_ori_debit = 0;
            $journal_end_ori_credit = 0;
            $journal_end_local_debit = 0;
            $journal_end_local_credit = 0;

            if(in_array($begin_account_no[0], ["1","5"])){
            if(in_array($begin_account_no, ["5311001","5311006"])){
                if((($begin_balance_local + @$journal_local_credit) - @$journal_local_debit) > 0){
                    $journal_end_ori_debit = 0;
                    $journal_end_ori_credit = abs(($begin_balance_ori + @$journal_ori_credit) - @$journal_ori_debit);
                    $journal_end_local_debit = 0;
                    $journal_end_local_credit = abs(($begin_balance_local + @$journal_local_credit) - @$journal_local_debit);
                }else{
                    $journal_end_ori_debit = abs(($begin_balance_ori + @$journal_ori_credit) - @$journal_ori_debit);
                    $journal_end_ori_credit = 0;
                    $journal_end_local_debit = abs(($begin_balance_local + @$journal_local_credit) - @$journal_local_debit);
                    $journal_end_local_credit = 0;
                }
            }else{
                if((($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit) > 0){
                    $journal_end_ori_debit = abs(($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit);
                    $journal_end_ori_credit = 0;
                    $journal_end_local_debit = abs(($begin_balance_local + @$journal_local_debit) - @$journal_local_credit);
                    $journal_end_local_credit = 0;
                }else{
                    $journal_end_ori_debit = 0;
                    $journal_end_ori_credit = abs(($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit);
                    $journal_end_local_debit = 0;
                    $journal_end_local_credit = abs(($begin_balance_local + @$journal_local_debit) - @$journal_local_credit);
                }
            }

            }elseif(in_array($begin_account_no[0], ["2","3","4"])){
            
                if(in_array($begin_account_no[0], ["4"])){
                if(in_array($begin_account_no, ["4111"])){
                    if((($begin_balance_ori + @$journal_ori_credit) - @$journal_ori_debit) > 0){
                        $journal_end_ori_debit = 0;
                        $journal_end_ori_credit = abs(($begin_balance_ori + @$journal_ori_credit) - @$journal_ori_debit);
                        $journal_end_local_debit = 0;
                        $journal_end_local_credit = abs(($begin_balance_local + @$journal_local_credit) - @$journal_local_debit);
                    }else{
                        $journal_end_ori_debit = abs(($begin_balance_ori + @$journal_ori_credit) - @$journal_ori_debit);
                        $journal_end_ori_credit = 0;
                        $journal_end_local_debit = abs(($begin_balance_local + @$journal_local_credit) - @$journal_local_debit);
                        $journal_end_local_credit = 0;
                    }
                }else{
                    if((($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit) > 0){
                        $journal_end_ori_debit = abs(($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit);
                        $journal_end_ori_credit = 0;
                        $journal_end_local_debit = abs(($begin_balance_local + @$journal_local_debit) - @$journal_local_credit);
                        $journal_end_local_credit = 0;
                    }else{
                        $journal_end_ori_debit = 0;
                        $journal_end_ori_credit = abs(($begin_balance_ori + @$journal_ori_debit) - @$journal_ori_credit);
                        $journal_end_local_debit = 0;
                        $journal_end_local_credit = abs(($begin_balance_local + @$journal_local_debit) - @$journal_local_credit);
                    }
                }
            }else{
                if((($begin_balance_ori + @$journal_ori_credit) - @$journal_ori_debit) > 0){
                    $journal_end_ori_debit = 0;
                    $journal_end_ori_credit = abs(($begin_balance_ori + @$journal_ori_credit) - @$journal_ori_debit);
                    $journal_end_local_debit = 0;
                    $journal_end_local_credit = abs(($begin_balance_local + @$journal_local_credit) - @$journal_local_debit);
                }else{
                    $journal_end_ori_debit = abs(($begin_balance_ori + @$journal_ori_credit) - @$journal_ori_debit);
                    $journal_end_ori_credit = 0;
                    $journal_end_local_debit = abs(($begin_balance_local + @$journal_local_credit) - @$journal_local_debit);
                }
            }

            }

            $no = 1;
            $ori_balance = ($journal_end_ori_debit + $journal_end_ori_credit);
            $ori_debit = 0;
            $ori_credit = 0;
            $local_balance = ($journal_end_local_debit + $journal_end_local_credit);
            $local_debit = 0;
            $local_credit = 0;

            $html .= '<tr>
                    <td style="text-align:center">-</td>
                    <td>' . $filter_from . '</td>
                    <td></td>';
                    if (count($journals) === 0) {
                        $html .= '<td style="text-align:center">' . $account_coa->account_number . '</td>
                                <td style="text-align:center">' . $account_coa->account_name . '</td>';
                    } else {
                        $html .= '<td></td>
                                <td></td>';
                    }
            $html .= '  <td>BALANCE</td>
                    <td style="text-align:center;">' . $account_coa->original_currency . '</td>
                    <td style="text-align:right;font-weight:bold;">' . $this->formatIDR($ori_balance, 2) . '</td>
                    <td style="text-align:right;font-weight:bold;">' . $this->formatIDR(0, 2) . '</td>
                    <td style="text-align:right;font-weight:bold;">' . $this->formatIDR(0, 2) . '</td>
                    <td style="text-align:right;font-weight:bold;">-</td>
                    <td style="text-align:right;font-weight:bold;">' . $this->formatIDR($local_balance, 2) . '</td>
                    <td style="text-align:right;font-weight:bold;">' . $this->formatIDR(0, 2) . '</td>
                    <td style="text-align:right;font-weight:bold;">' . $this->formatIDR(0, 2) . '</td>
                </tr>';

            foreach ($journals as $journal) 
            {
                $account_no = $journal['account_number'];

                if($ori_balance > 0){
                    $ori_style = "color:green;";
                }else{
                    $ori_style = "color:red;";
                }

                if($local_balance > 0){
                    $local_style = "color:green;";
                }else{
                    $local_style = "color:red;";
                }

                // --- Link transaksi GL Posting Journal
                $linked_ori_debit  = $this->createLink($journal['original_debit'], $journal['number']);
                $linked_ori_credit = $this->createLink($journal['original_credit'], $journal['number']);
                $linked_debit      = $this->createLink($journal['local_debit'], $journal['number']);
                $linked_credit     = $this->createLink($journal['local_credit'], $journal['number']);

                $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $journal['trans_date'] . '</td>
                            <td>' . $journal['number'] . '</td>
                            <td>' . $journal['account_number'] . '</td>
                            <td>' . $journal['account_name'] . '</td>
                            <td>' . $journal['description'] . '</td>
                            <td style="text-align:center;">' . $journal['currency'] . '</td>
                            <td style="text-align:right;font-weight:bold;' . $ori_style . '">' . $this->formatIDR($ori_balance, 2) . '</td>
                            <td style="text-align:right;font-weight:bold;color:green;">' . $linked_ori_debit . '</td>
                            <td style="text-align:right;font-weight:bold;color:red;">' . $linked_ori_credit . '</td>
                            <td style="text-align:right;font-weight:bold;">' . $this->formatIDR($journal['rates'], 2) . '</td>
                            <td style="text-align:right;font-weight:bold;' . $local_style . '">' . $this->formatIDR($local_balance, 2) . '</td>
                            <td style="text-align:right;font-weight:bold;color:green;">' . $linked_debit . '</td>
                            <td style="text-align:right;font-weight:bold;color:red;">' . $linked_credit . '</td>
                        </tr>';

                if(in_array($account_no[0], ["1","5"])){
                
                if(in_array($account_no, ["5311001","5311006"])){
                    $ori_balance += ($journal['original_credit'] - $journal['original_debit']);
                    $local_balance += ($journal['local_credit'] - $journal['local_debit']);
                }else{
                    $ori_balance += ($journal['original_debit'] - $journal['original_credit']);
                    $local_balance += ($journal['local_debit'] - $journal['local_credit']);
                }

                }elseif(in_array($account_no[0], ["2","3","4"])){

                if(in_array($account_no[0], ["4"])){
                    if(in_array($account_no, ["4111"])){
                        $ori_balance += ($journal['original_credit'] - $journal['original_debit']);
                        $local_balance += ($journal['local_credit'] - $journal['local_debit']);
                    }else{
                        $ori_balance += ($journal['original_debit'] - $journal['original_credit']);
                        $local_balance += ($journal['local_debit'] - $journal['local_credit']);
                    }
                }else{
                    $ori_balance += ($journal['original_credit'] - $journal['original_debit']);
                    $local_balance += ($journal['local_credit'] - $journal['local_debit']);
                }
                }

                $ori_debit += $journal['original_debit'];
                $ori_credit += $journal['original_credit'];
                $local_debit += $journal['local_debit'];
                $local_credit += $journal['local_credit'];
                $no++;
            }

            $html .= '  <tr style="background:#DEDEDE;">
                        <th style="text-align:right;" colspan="7"><b>ENDING BALANCE</b></th>
                        <th style="text-align:right;">' . $this->formatIDR(abs($ori_balance), 2) . '</th>
                        <th style="text-align:right;">-</th>
                        <th style="text-align:right;">-</th>
                        <th></th>
                        <th style="text-align:right;">' . $this->formatIDR(abs($local_balance), 2) . '</th>
                        <th style="text-align:right;">-</th>
                        <th style="text-align:right;">-</th>
                    </tr>';
            $html .= '  <tr style="background:#DEDEDE;">
                        <th style="text-align:right;" colspan="7"><b>GRAND TOTAL</b></th>
                        <th style="text-align:right;"></th>
                        <th style="text-align:right;">' . $this->formatIDR(abs($ori_debit), 2) . '</th>
                        <th style="text-align:right;">' . $this->formatIDR(abs($ori_credit), 2) . '</th>
                        <th></th>
                        <th style="text-align:right;"></th>
                        <th style="text-align:right;">' . $this->formatIDR(abs($local_debit), 2) . '</th>
                        <th style="text-align:right;">' . $this->formatIDR(abs($local_credit), 2) . '</th>
                    </tr>';

            $html .= '</table> <br><br>
            <div style="break-after:page"></div>';        
        } // end loop multiple filter_account
        
        $html .= '</body></html>';
        return $html;
    }

    // get link detail transaksi GL
    function createLink($value, $idLink) 
    {
        $base_url   = base_url('finance/journal_postings/print_voucher_gl/');
        $id_encoded = base64_encode($idLink);
        $url        = $base_url . $id_encoded;

        if ($value > 0) {
            return '<a href="javascript:void(0)" onclick="window.open(\'' . $url . '\', \'_blank\', \'location=yes,height=600,width=1200,scrollbars=yes,status=yes\');" class="link-transaction">' . $this->formatIDR($value, 2) . '</a>';
        }
        return $this->formatIDR($value, 2);
    }

    function formatIDR($number, $decimal_places = 2) {
        $formatted_number = number_format($number, $decimal_places, ',', '.');
        return $formatted_number;
    }
}
