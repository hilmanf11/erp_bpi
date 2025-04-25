<?php
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_cash_flow extends CI_Controller
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
            $this->load->view('finance/report_cash_flow');
        } else {
            redirect('error_access');
        }
    }

    // function getData($filter_from, $filter_to, $number){
    //     //Current Assets
    //     $this->db->select('a.number,
    //         ((COALESCE(SUM(b.local_debit), 0) + COALESCE(SUM(c.local_debit),0)) - (COALESCE(SUM(b.local_kredit), 0) + COALESCE(SUM(c.local_credit), 0))) as total
    //     ');
    //     $this->db->from('account_cash_flow a');
    //     $this->db->join('account_coa b', 'a.account_number = b.account_number');
    //     $this->db->join('journal_postings c', "a.account_number = c.account_number and c.journal_date BETWEEN '$filter_from' and '$filter_to'", 'left');
    //     $this->db->where('a.number', $number);
    //     $this->db->group_by('a.number');
    //     $data = $this->db->get()->row();

    //     return @$data->total;
    // }

    function getData($filter_from, $filter_to, $number){
        $filter_before = date("Y-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));

        $this->db->select('b.account_number, b.account_name, b.local_debit, b.local_kredit');
        $this->db->from('account_cash_flow a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number');
        $this->db->where('a.number', $number);
        $this->db->group_by('a.account_number');
        $this->db->order_by('a.flag', 'asc');
        $accounts = $this->db->get()->result_array();

        foreach ($accounts as $account) {
            $this->db->select('account_number, account_name, 
                COALESCE(SUM(local_debit)) as local_debit,
                COALESCE(SUM(local_credit)) as local_credit');
            $this->db->from('journal_postings');
            $this->db->where('account_number', $account['account_number']);
            $this->db->where("journal_date BETWEEN '$filter_from' and '$filter_to'");
            $this->db->group_by('account_number');
            $journal = $this->db->get()->row();

            $this->db->select('account_number, account_name, 
                COALESCE(SUM(local_debit)) as local_debit,
                COALESCE(SUM(local_credit)) as local_credit');
            $this->db->from('journal_postings');
            $this->db->where('account_number', $account['account_number']);
            $this->db->where("journal_date BETWEEN '$filter_before' and '$filter_before_to'");
            $this->db->group_by('account_number');
            $journal_bf = $this->db->get()->row();

            $journal_debit = @$journal_bf->local_debit;
            $journal_credit = @$journal_bf->local_credit;
            $account_no = $account['account_number'];

            $begin_balance = abs(($account['local_kredit'] + @$journal_bf->local_credit) - ($account['local_debit'] + @$journal_bf->local_debit));
            if((($account['local_kredit'] + @$journal_bf->local_credit) - ($account['local_debit'] + @$journal_bf->local_debit)) > 0){
                $begin_balance_credit = abs(($account['local_kredit'] + @$journal_bf->local_credit) - ($account['local_debit'] + @$journal_bf->local_debit));
                $begin_balance_debit = 0;
            }else{
                $begin_balance_credit = 0;
                $begin_balance_debit = abs(($account['local_kredit'] + @$journal_bf->local_credit) - ($account['local_debit'] + @$journal_bf->local_debit));
            }

            if(($begin_balance_debit - $begin_balance_credit) > 0){
                $begin_debit += ($begin_balance_debit - $begin_balance_credit);
                $begin_credit += 0;
            }else{
                $begin_credit += ($begin_balance_debit - $begin_balance_credit);
                $begin_debit += 0;
            }

            $local_debit += @$journal->local_debit;
            $local_credit += @$journal->local_credit;
        }

        if(($begin_debit - abs($begin_credit)) > 0){
            $final_begin_debit = abs($begin_debit - abs($begin_credit));
            $final_begin_credit = 0;
        }else{
            $final_begin_debit = 0;
            $final_begin_credit = abs($begin_debit - abs($begin_credit));
        }

        $begin_balance = ($final_begin_debit - $final_begin_credit);
        $account_group_no = $accounts[0]['account_number'];

        if(in_array($account_group_no[0], ["1","5"])){
            if(((@$begin_balance + @$local_debit) - @$local_credit) > 0){
                $ending_debit = ((@$begin_balance + @$local_debit) - @$local_credit);
                $ending_credit = 0;
            }else{
                $ending_debit = 0;
                $ending_credit = abs((@$begin_balance + @$local_debit) - @$local_credit);
            }
        }elseif(in_array($account_group_no[0], ["2","3","4"])){
            if(((@$begin_balance + @$local_credit) - @$local_debit) > 0){
                $ending_debit = 0;
                $ending_credit = abs((@$begin_balance + @$local_credit) - @$local_debit);
            }else{
                $ending_debit = abs((@$begin_balance + @$local_credit) - @$local_debit);
                $ending_credit = 0;
            }
        }

        if($ending_credit > 0){
            return "(".$ending_credit.")";
        }else{
            return $ending_debit;
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_ap_$format.xls");
        }

        $filter_date = base64_decode($this->input->get("filter_date"));

        $year_now = date("Y", strtotime($filter_date));
        $filter_from_now = date("Y-01-01", strtotime($filter_date));
        $filter_to_now = $filter_date;

        $year_bf = date("Y", strtotime('-1 years', strtotime($filter_date)));
        $filter_from_bf = date("Y-01-01", strtotime('-1 years', strtotime($filter_date)));
        $filter_to_bf = date("Y-12-t", strtotime('-1 years', strtotime($filter_date)));

        $date_header_ind = date("d F Y", strtotime($filter_date));
        $date_header_eng = date("F d, Y", strtotime($filter_date));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 3px; padding-left: 10px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
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
                <div style="width:80%; border: 2px solid black; padding:20px;">
                    <table style="width: 100%; font-size:12px;">
                        <tr>
                            <th colspan="2" style="text-align:left;">
                                <b>LAPORAN ARUS KAS<br>UNTUK TAHUN YANG BERAKHIR PADA<br>TANGGAL '.$date_header_ind.' DAN '.$year_bf.'</b><br>
                                <small>(Disajikan dalam Rupiah, kecuali dinyatakan lain)</small>
                            </th>
                            <th colspan="2" style="text-align:right;">
                                <b>CASH FLOW STATEMENTS<br>FOR THE YEAR ENDED<br>'.$date_header_eng.' DAN '.$year_bf.'</b><br>
                                <small>(Figures are Presented in Rupiah, uniess Otherwise Started)</small>
                            </th>
                        </tr>
                        <tr>
                            <th width="100" height="50"></th>
                            <th style="border-bottom:1px solid black;" width="50">'.$year_bf.'</th>
                            <th style="border-bottom:1px solid black;" width="50">'.$year_now.'</th>
                            <th width="100"></th>
                        </tr>
                        <tr>
                            <th colspan="2" style="text-align:left; padding:5px;">ARUS KAS DARI AKTIVITAS OPRASI</th>
                            <th colspan="2" style="text-align:right; padding:5px;">CASH FLOWS FROM OPERATING ACTIVITIES</th>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Laba setelah Pajak Penghasilan</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_bf, $filter_to_bf, "101")).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_now, $filter_to_now, "101")).'</td>
                            <td style="text-align:right; padding:5px;">Net Income after tax</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Penyesuaian untuk merekonsilasi laba bersih menjadi kas bersih yang diperoleh dari aktivitas operasi :</td>
                            <td style="text-align:right; padding:5px;"></td>
                            <td style="text-align:right; padding:5px;"></td>
                            <td style="text-align:right; padding:5px;">Adjustment to reconcile net income to net cash used in operating activities :</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Penyusutan</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_bf, $filter_to_bf, "102")).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_now, $filter_to_now, "102")).'</td>
                            <td style="text-align:right; padding:5px;">Depreciation</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Laba operasi sebelum modal kerja</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">Operating cash flow before change in working capital</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Piutang Usaha</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_bf, $filter_to_bf, "104")).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_now, $filter_to_now, "104")).'</td>
                            <td style="text-align:right; padding:5px;">Account Receivable</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Persediaan</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_bf, $filter_to_bf, "105")).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_now, $filter_to_now, "105")).'</td>
                            <td style="text-align:right; padding:5px;">Inventory</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Uang muka dibayar</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_bf, $filter_to_bf, "106")).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_now, $filter_to_now, "106")).'</td>
                            <td style="text-align:right; padding:5px;">Prepayment</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Pajak dibayar dimuka</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_bf, $filter_to_bf, "107")).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_now, $filter_to_now, "107")).'</td>
                            <td style="text-align:right; padding:5px;">Prepaid expenses</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Biaya yang harus dibayar</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">Accured Payable</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Utang Usaha</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_bf, $filter_to_bf, "109")).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_now, $filter_to_now, "109")).'</td>
                            <td style="text-align:right; padding:5px;">Account Payable</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Utang Pajak</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_bf, $filter_to_bf, "110")).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_now, $filter_to_now, "110")).'</td>
                            <td style="text-align:right; padding:5px;">Taxes Payables</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Utang Lain-lain</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_bf, $filter_to_bf, "111")).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format($this->getData($filter_from_now, $filter_to_now, "111")).'</td>
                            <td style="text-align:right; padding:5px;">Others Payables</td>
                        </tr>
                        <tr>
                            <th style="text-align:left; padding:5px;">Jumlah kas bersih diperoleh/(digunakan) untuk aktifitas operasi</th>
                            <th style="text-align:right; padding:5px;">'.number_format(0).'</th>
                            <th style="text-align:right; padding:5px;">'.number_format(0).'</th>
                            <th style="text-align:right; padding:5px;">Net Cash provided by (used in) Operating Activities</th>
                        </tr>
                        <tr>
                            <th colspan="2" style="text-align:left; padding:5px;">ARUS KAS DARI AKTIVITAS INVESTASI</th>
                            <th colspan="2" style="text-align:right; padding:5px;">CASH FLOW FROM INVESTING ACTIVITIES</th>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Pembelian aset tetap</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">Acqusition fixed assets</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Jumlah kas bersih diperoleh/(digunakan) untuk aktivitas investasi</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">Net cash flows (Used in) Provide from Investing Activities</td>
                        </tr>
                        <tr>
                            <th colspan="2" style="text-align:left; padding:5px;">ARUS KAS DARI AKTIVITAS PENDANAAN</th>
                            <th colspan="2" style="text-align:right; padding:5px;">CASH FLOW FROM FINANCING ACTIVITIES</th>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Modal Saham</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">Capital Stock</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Jumlah kas bersih diperoleh/(digunakan) untuk aktivitas pendanaan</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">Net cash flows (Used in) Provide from Financing Activities</td>
                        </tr>
                        <tr>
                            <td style="text-align:left; padding:5px;">Jumlah kenaikan/(penurunan) Kas</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">'.number_format(0).'</td>
                            <td style="text-align:right; padding:5px;">Increase / (Decrease) Cash & Equivalents</td>
                        </tr>
                        <tr>
                            <th style="text-align:left; padding:5px;">KAS DAN SETARA KAS, AWAL</th>
                            <th style="text-align:right; padding:5px;">'.number_format(0).'</th>
                            <th style="text-align:right; padding:5px;">'.number_format(0).'</th>
                            <th style="text-align:right; padding:5px;">Cash & Equivalents - Begining Balance</th>
                        </tr>
                        <tr>
                            <th style="text-align:left; padding:5px;">KAS DAN SETARA KAS, AKHIR</th>
                            <th style="text-align:right; padding:5px;">'.number_format(0).'</th>
                            <th style="text-align:right; padding:5px;">'.number_format(0).'</th>
                            <th style="text-align:right; padding:5px;">Cash & Equivalents - Ending Balance</th>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align:center; padding:10px;"><br><br><i>Lihat Catatan Atas Laporan Keuangan yang merupakan bagian tidak terpisahkan dan laporan keuangan secara keseluruhan</i></td>
                            <td colspan="2" style="text-align:center; padding:10px;"><br><br><i>See accompanying Notes to Financial Statements which are an integral part of the Financial Statements</i></td>
                        </tr>
                    </table>
                </div>
            </center>';
        $html .= '</body></html>';
        echo $html;
    }
}
