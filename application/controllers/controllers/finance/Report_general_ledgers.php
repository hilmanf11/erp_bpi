<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
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

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_general_ledgers_$format.xls");
        }

        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_account = base64_decode($this->input->get("filter_account"));

        $filter_before = date("Y-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('journal_postings');
        $this->db->where("journal_date between '$filter_from' and '$filter_to'");
        $this->db->where("account_number", $filter_account);
        $this->db->order_by('journal_date', 'asc');
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
        $this->db->where("account_number", $filter_account);
        $journal_bf = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('account_coa');
        $this->db->where("account_number", $filter_account);
        $account_coa = $this->db->get()->row();

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
                                <span style="font-size:10px;">' . $config->description . '</span><br>
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
                <h3 style="margin:0;">GENERAL LEDGER</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br><br>
            
            <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Voucher Date</th>
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

        $html .= '  <tr>
                        <td style="text-align:center">-</td>
                        <td>' . $filter_from . '</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>BALANCE</td>
                        <td style="text-align:center;">' . $account_coa->original_currency . '</td>
                        <td style="text-align:right;font-weight:bold;">' . number_format($ori_balance, 2) . '</td>
                        <td style="text-align:right;font-weight:bold;">' . number_format(0, 2) . '</td>
                        <td style="text-align:right;font-weight:bold;">' . number_format(0, 2) . '</td>
                        <td style="text-align:right;font-weight:bold;">-</td>
                        <td style="text-align:right;font-weight:bold;">' . number_format($local_balance, 2) . '</td>
                        <td style="text-align:right;font-weight:bold;">' . number_format(0, 2) . '</td>
                        <td style="text-align:right;font-weight:bold;">' . number_format(0, 2) . '</td>
                    </tr>';

        foreach ($journals as $journal) {
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

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $journal['journal_date'] . '</td>
                            <td>' . $journal['number'] . '</td>
                            <td>' . $journal['account_number'] . '</td>
                            <td>' . $journal['account_name'] . '</td>
                            <td>' . $journal['description'] . '</td>
                            <td style="text-align:center;">' . $journal['currency'] . '</td>
                            <td style="text-align:right;font-weight:bold;'.$ori_style.'">' . number_format($ori_balance, 2) . '</td>
                            <td style="text-align:right;font-weight:bold;color:green;">' . number_format($journal['original_debit'], 2) . '</td>
                            <td style="text-align:right;font-weight:bold;color:red;">' . number_format(($journal['original_credit']), 2) . '</td>
                            <td style="text-align:right;font-weight:bold;">' . number_format($journal['rates'], 2) . '</td>
                            <td style="text-align:right;font-weight:bold;'.$local_style.'">' . number_format($local_balance, 2) . '</td>
                            <td style="text-align:right;font-weight:bold;color:green;">' . number_format($journal['local_debit'], 2) . '</td>
                            <td style="text-align:right;font-weight:bold;color:red;">' . number_format($journal['local_credit'], 2) . '</td>
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
                        <th style="text-align:right;">' . number_format(abs($ori_balance), 2) . '</th>
                        <th style="text-align:right;">-</th>
                        <th style="text-align:right;">-</th>
                        <th></th>
                        <th style="text-align:right;">' . number_format(abs($local_balance), 2) . '</th>
                        <th style="text-align:right;">-</th>
                        <th style="text-align:right;">-</th>
                    </tr>';

        $html .= '  <tr style="background:#DEDEDE;">
                        <th style="text-align:right;" colspan="7"><b>GRAND TOTAL</b></th>
                        <th style="text-align:right;"></th>
                        <th style="text-align:right;">' . number_format(abs($ori_debit), 2) . '</th>
                        <th style="text-align:right;">' . number_format(abs($ori_credit), 2) . '</th>
                        <th></th>
                        <th style="text-align:right;"></th>
                        <th style="text-align:right;">' . number_format(abs($local_debit), 2) . '</th>
                        <th style="text-align:right;">' . number_format(abs($local_credit), 2) . '</th>
                    </tr>';

        $html .= '</table></body></html>';
        echo $html;
    }
}
