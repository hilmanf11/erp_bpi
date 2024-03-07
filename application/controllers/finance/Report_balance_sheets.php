<?php
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_balance_sheets extends CI_Controller
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
            $this->load->view('finance/report_balance_sheets');
        } else {
            redirect('error_access');
        }
    }

    function getData($filter_from, $filter_to, $category, $modul, $name){
        $filter_before = date("Y-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));
        $period = date("Ym", strtotime($filter_from));

        $this->db->select('b.account_number, b.account_name, b.local_debit, b.local_kredit');
        $this->db->from('account_statements a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number');
        $this->db->where('a.category', $category);
        $this->db->where('a.modul', $modul);
        $this->db->where('a.name', $name);
        $this->db->group_by('a.account_number');
        $this->db->order_by('a.flag', 'asc');
        $accounts = $this->db->get()->result_array();

        foreach ($accounts as $account) {
            $trial_balance = $this->crud->read('trial_balances', [], ["account_number" => $account['account_number'], "period" => $period]);
            $ending += ($trial_balance->ending_debit - $trial_balance->ending_credit);
        }

        return $ending;
    }

    function getDataRotate($filter_from, $filter_to, $category, $modul, $name){
        $filter_before = date("Y-01-01", strtotime($filter_from));
        $filter_before_to = date("Y-m-t", strtotime("-1 month", strtotime($filter_from)));
        $period = date("Ym", strtotime($filter_from));

        $this->db->select('b.account_number, b.account_name, b.local_debit, b.local_kredit');
        $this->db->from('account_statements a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number');
        $this->db->where('a.category', $category);
        $this->db->where('a.modul', $modul);
        $this->db->where('a.name', $name);
        $this->db->group_by('a.account_number');
        $this->db->order_by('a.flag', 'asc');
        $accounts = $this->db->get()->result_array();

        foreach ($accounts as $account) {
            $trial_balance = $this->crud->read('trial_balances', [], ["account_number" => $account['account_number'], "period" => $period]);
            //Jika Paid and Capital
            $ending += (($trial_balance->ending_debit - $trial_balance->ending_credit) * -1);
        }

        return $ending;
    }

    function balanceSheet($filter_from){
        $jan = strtotime(date("Y-01-01", strtotime($filter_from)));
        $now = strtotime($filter_from);
        
        $total = 0;
        if($filter_from != date("Y-01-01")){
            for ($i = $jan; $i <= $now; $i += strtotime("+1 month", $i)) {
                $working_date = date('Ym', $i);

                $balance_sheet = $this->crud->read("balance_sheets", [], ["name" => "P/L From Jan - Now", "period" => $working_date]);
                $total += $balance_sheet->amount;
            }
        }

        return $total;
    }

    function formatting($amount){
        if($amount >= 0){
            return number_format($amount, 2);
        }else{
            return "(".number_format(abs($amount), 2).")";
        }
    }

    public function generateData(){
        $filter_date   = $this->input->post('filter_date');
        $filter_from = date("Y-m-01", strtotime($filter_date));
        $filter_to = date("Y-m-t", strtotime($filter_date));
        $period = date("Ym", strtotime($filter_date));

        //Current Assets
        $cash_on_hand = $this->getData($filter_from, $filter_to, "Current Assets", "Balance Sheet", "Cash on Hand");
        $cash_in_bank = $this->getData($filter_from, $filter_to, "Current Assets", "Balance Sheet", "Cash in Bank");
        $time_deposit = $this->getData($filter_from, $filter_to, "Current Assets", "Balance Sheet", "Time Deposit");
        $account_receivable = $this->getData($filter_from, $filter_to, "Current Assets", "Balance Sheet", "Account Receivable");
        $allow_bad_debt = $this->getData($filter_from, $filter_to, "Current Assets", "Balance Sheet", "Allow.Bad Debt");
        $inventories = $this->getData($filter_from, $filter_to, "Current Assets", "Balance Sheet", "Inventories");
        $prepaid_expense = $this->getData($filter_from, $filter_to, "Current Assets", "Balance Sheet", "Prepaid Expense");
        $total_current_assets = ($cash_on_hand + $cash_in_bank + $time_deposit + $account_receivable + $allow_bad_debt + $inventories + $prepaid_expense);

        //Other Assets
        $long_term_investment = $this->getData($filter_from, $filter_to, "Other Assets", "Balance Sheet", "Long-term Investment");
        $fixed_asset_cost = $this->getData($filter_from, $filter_to, "Other Assets", "Balance Sheet", "Fixed assets-cost");
        $less_accumulated = $this->getData($filter_from, $filter_to, "Other Assets", "Balance Sheet", "Less: Accumulated depreciation");
        $total_fixed_asset = ($long_term_investment + $fixed_asset_cost + $less_accumulated);
        $right_to_use_sites = $this->getData($filter_from, $filter_to, "Other Assets", "Balance Sheet", "Right to use sites");
        $other_deffered_expeniture = $this->getData($filter_from, $filter_to, "Other Assets", "Balance Sheet", "Other Deffered expeniture");
        $total_other_assets = ($right_to_use_sites + $other_deffered_expeniture);

        $total_assets = ($total_current_assets + $total_fixed_asset + $total_other_assets);

        //Current Liabilities
        $short_term_loans = $this->getDataRotate($filter_from, $filter_to, "Current Liabilities", "Balance Sheet", "Short Term Loans");
        $account_payable = $this->getDataRotate($filter_from, $filter_to, "Current Liabilities", "Balance Sheet", "Account Payable");
        $accured_expense = $this->getDataRotate($filter_from, $filter_to, "Current Liabilities", "Balance Sheet", "Accrued Expense");
        $taxes_payable = $this->getDataRotate($filter_from, $filter_to, "Current Liabilities", "Balance Sheet", "Taxes Payable");
        $acc_payable_machine = $this->getDataRotate($filter_from, $filter_to, "Current Liabilities", "Balance Sheet", "Acc.Payable Machine");
        $acc_payable_other = $this->getDataRotate($filter_from, $filter_to, "Current Liabilities", "Balance Sheet", "Acc.Payable Other");
        $total_current_liabilities = ($short_term_loans + $account_payable + $accured_expense + $taxes_payable + $acc_payable_machine + $acc_payable_other);

        //Current Liabilities
        $long_term_liabilities = $this->getDataRotate($filter_from, $filter_to, "Current Investor Equity", "Balance Sheet", "Long Term Liabilities");
        $total_liabilities = ($total_current_liabilities + $long_term_liabilities);
        $paid_in_capital = $this->getDataRotate($filter_from, $filter_to, "Current Investor Equity", "Balance Sheet", "Paid in Capital");
        $capital_surplus = $this->getDataRotate($filter_from, $filter_to, "Current Investor Equity", "Balance Sheet", "Capital Surplus");
        $revaluation_surplus = $this->getDataRotate($filter_from, $filter_to, "Current Investor Equity", "Balance Sheet", "Revaluation Surplus");
        $retained_earning = $this->getDataRotate($filter_from, $filter_to, "Current Investor Equity", "Balance Sheet", "Retained Earning (Last Year)");

        //Income Statement =====================================================================================
        $total_taxes = $this->crud->read("income_statements", [], ["name" => "NET PROFIT LOSS AFTER TAXES", "period" => $period]);
        //$balance_sheet_before = $this->balanceSheet($filter_from);

        //==================================================================================================================================

        $pl_from_jan_now = (@$total_taxes->accumulated);
        $total_investor_equity = ($paid_in_capital + $capital_surplus + $revaluation_surplus + $retained_earning + $pl_from_jan_now);

        $total_liabilities_equity = ($total_liabilities + $total_investor_equity);


        $data = array(["period" => $period,"name" => "Cash on Hand","amount" => $cash_on_hand],
                ["period" => $period,"name" => "Cash in Bank","amount" => $cash_in_bank],
                ["period" => $period,"name" => "Time Deposit","amount" => $time_deposit],
                ["period" => $period,"name" => "Account Receivable","amount" => $account_receivable],
                ["period" => $period,"name" => "Allow.Bad Debt","amount" => $allow_bad_debt],
                ["period" => $period,"name" => "Inventories","amount" => $inventories],
                ["period" => $period,"name" => "Prepaid Expense","amount" => $prepaid_expense],
                ["period" => $period,"name" => "Total Current Assets","amount" => $total_current_assets],
                ["period" => $period,"name" => "Long-term Investment","amount" => $long_term_investment],
                ["period" => $period,"name" => "Fixed assets-cost","amount" => $fixed_asset_cost],
                ["period" => $period,"name" => "Less: Accumulated depreciation","amount" => $less_accumulated],
                ["period" => $period,"name" => "Total Fixed Assets","amount" => $total_fixed_asset],
                ["period" => $period,"name" => "Right to use sites","amount" => $right_to_use_sites],
                ["period" => $period,"name" => "Other Deffered expeniture","amount" => $other_deffered_expeniture],
                ["period" => $period,"name" => "Total Other Assets","amount" => $total_other_assets],
                ["period" => $period,"name" => "Total Assets","amount" => $total_assets],
                ["period" => $period,"name" => "Account Payable","amount" => $account_payable],
                ["period" => $period,"name" => "Accrued Expense","amount" => $accured_expense],
                ["period" => $period,"name" => "Taxes Payable","amount" => $taxes_payable],
                ["period" => $period,"name" => "Acc.Payable Machine","amount" => $acc_payable_machine],
                ["period" => $period,"name" => "Acc.Payable Other","amount" => $acc_payable_other],
                ["period" => $period,"name" => "Total Current Liabilities","amount" => $total_current_liabilities],
                ["period" => $period,"name" => "Long Term Liabilities","amount" => $long_term_liabilities],
                ["period" => $period,"name" => "Total Liabilities","amount" => $total_liabilities],
                ["period" => $period,"name" => "Paid in Capital","amount" => $paid_in_capital],
                ["period" => $period,"name" => "Capital Surplus","amount" => $capital_surplus],
                ["period" => $period,"name" => "Revaluation Surplus","amount" => $revaluation_surplus],
                ["period" => $period,"name" => "Retained Earning (Last Year)","amount" => $retained_earning],
                ["period" => $period,"name" => "P/L From Jan - Now","amount" => $pl_from_jan_now],
                ["period" => $period,"name" => "Total Investor Equity","amount" => $total_investor_equity],
                ["period" => $period,"name" => "Total Liabilities Equity","amount" => $total_liabilities_equity]);

        $result['total'] = count($data);
        $result = array_merge($result, ['rows' => $data]);
        echo json_encode($result);
    }

    public function datatables($period, $name){
        $account_statements = $this->crud->read('balance_sheets', [], ["period" => $period, "name" => $name]);
        return $account_statements->amount;
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');

            $trial_balances = $this->crud->reads("balance_sheets", [], [
                "period" => $post['period'], 
                "name" => $post['name']
            ]);

            if(count($trial_balances) > 0){
                $send = $this->crud->update('balance_sheets', [
                    "period" => $post['period'], 
                    "name" => $post['name']
                ], $post);

                echo $send;
            }else{
                $send = $this->crud->create('balance_sheets', $post);
                echo $send;
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=balance_sheets_$format.xls");
        }

        $filter_date = base64_decode($this->input->get("filter_date"));
        $filter_display = base64_decode($this->input->get("filter_display"));

        $period = date("Ym", strtotime($filter_date));
        $filter_from = date("Y-m-01", strtotime($filter_date));
        $filter_to = date("Y-m-t", strtotime($filter_date));

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
                <h3 style="margin:0;">BALANCE SHEET</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';

            //Current Assets
            $cash_on_hand = $this->datatables($period, "Cash on Hand");
            $cash_in_bank = $this->datatables($period, "Cash in Bank");
            $time_deposit = $this->datatables($period, "Time Deposit");
            $account_receivable = $this->datatables($period, "Account Receivable");
            $allow_bad_debt = $this->datatables($period, "Allow.Bad Debt");
            $inventories = $this->datatables($period, "Inventories");
            $prepaid_expense = $this->datatables($period, "Prepaid Expense");
            $total_current_assets = $this->datatables($period, "Total Current Assets");

            //Other Assets
            $long_term_investment = $this->datatables($period, "Long-term Investment");
            $fixed_asset_cost = $this->datatables($period, "Fixed assets-cost");
            $less_accumulated = $this->datatables($period, "Less: Accumulated depreciation");
            $total_fixed_asset = $this->datatables($period, "Total Fixed Assets");
            $right_to_use_sites = $this->datatables($period, "Right to use sites");
            $other_deffered_expeniture = $this->datatables($period, "Other Deffered expeniture");
            $total_other_assets = $this->datatables($period, "Total Other Assets");

            $total_assets = $this->datatables($period, "Total Assets");

            //Current Liabilities
            $short_term_loans = $this->datatables($period, "Short Term Loans");
            $account_payable = $this->datatables($period, "Account Payable");
            $accured_expense = $this->datatables($period, "Accrued Expense");
            $taxes_payable = $this->datatables($period, "Taxes Payable");
            $acc_payable_machine = $this->datatables($period, "Acc.Payable Machine");
            $acc_payable_other = $this->datatables($period, "Acc.Payable Other");
            $total_current_liabilities = $this->datatables($period, "Total Current Liabilities");

            //Current Liabilities
            $long_term_liabilities = $this->datatables($period, "Long Term Liabilities");
            $total_liabilities = $this->datatables($period, "Total Liabilities");
            $paid_in_capital = $this->datatables($period, "Paid in Capital");
            $capital_surplus = $this->datatables($period, "Capital Surplus");
            $revaluation_surplus = $this->datatables($period, "Revaluation Surplus");
            $retained_earning = $this->datatables($period, "Retained Earning (Last Year)");

            $pl_from_jan_now = $this->datatables($period, "P/L From Jan - Now");
            $total_investor_equity = $this->datatables($period, "Total Investor Equity");

            $total_liabilities_equity = $this->datatables($period, "Total Liabilities Equity");

            if($filter_display == "1"){
                $html .= '<table id="customers" border="1">';

                    //Current Assets & Current Liabilities
                    $html .= '  <tr>
                                    <th colspan="4" width="100" style="background: #E8E8E8;">ASSET</th>
                                    <th colspan="4" width="100" style="background: #E8E8E8;">LIABILITIES AND CAPITAL</th>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr>
                                    <td>Cash on Hand</td>
                                    <td>1</td>
                                    <td style="text-align:right;">'.$this->formatting($cash_on_hand).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($cash_on_hand / $total_assets) * 100, 2)).'%</td>
                                    <td>Short Term Loans</td>
                                    <td>17</td>
                                    <td style="text-align:right;">'.$this->formatting($short_term_loans).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($short_term_loans / $total_liabilities_equity) * 100, 2)).'%</td>
                                </tr>
                                <tr>
                                    <td>Cash in Bank</td>
                                    <td>2</td>
                                    <td style="text-align:right;">'.$this->formatting($cash_in_bank).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($cash_in_bank / $total_assets) * 100, 2)).'%</td>
                                    <td>Account Payable</td>
                                    <td>18</td>
                                    <td style="text-align:right;">'.$this->formatting($account_payable).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($account_payable / $total_liabilities_equity) * 100, 2)).'%</td>
                                </tr>
                                <tr>
                                    <td>Time Deposit</td>
                                    <td>3</td>
                                    <td style="text-align:right;">'.$this->formatting($time_deposit).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($time_deposit / $total_assets) * 100, 2)).'%</td>
                                    <td>Accrued Expense</td>
                                    <td>19</td>
                                    <td style="text-align:right;">'.$this->formatting($accured_expense).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($accured_expense / $total_liabilities_equity) * 100,2)).'%</td>
                                </tr>
                                <tr>
                                    <td>Account Receivable</td>
                                    <td>4</td>
                                    <td style="text-align:right;">'.$this->formatting($account_receivable).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($account_receivable / $total_assets) * 100, 2)).'%</td>
                                    <td colspan="4"></td>
                                </tr>
                                <tr>
                                    <td>Allow.Bad Debt</td>
                                    <td>5</td>
                                    <td style="text-align:right;">'.$this->formatting($allow_bad_debt).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($allow_bad_debt / $total_assets) * 100, 2)).'%</td>
                                    <td>Taxes Payable</td>
                                    <td>20</td>
                                    <td style="text-align:right;">'.$this->formatting($taxes_payable).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($taxes_payable / $total_liabilities_equity) * 100, 2)).'%</td>
                                </tr>
                                <tr>
                                    <td>Inventories</td>
                                    <td>6</td>
                                    <td style="text-align:right;">'.$this->formatting($inventories).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($inventories / $total_assets) * 100, 2)).'%</td>
                                    <td>Acc.Payable Machine</td>
                                    <td>21</td>
                                    <td style="text-align:right;">'.$this->formatting($acc_payable_machine).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($acc_payable_machine / $total_liabilities_equity) * 100, 2)).'%</td>
                                </tr>
                                <tr>
                                    <td>Prepaid Expense</td>
                                    <td>7</td>
                                    <td style="text-align:right;">'.$this->formatting($prepaid_expense).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($prepaid_expense / $total_assets) * 100, 2)).'%</td>
                                    <td>Acc.Payable Other</td>
                                    <td>22</td>
                                    <td style="text-align:right;">'.$this->formatting($acc_payable_other).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($acc_payable_other / $total_liabilities_equity) * 100, 2)).'%</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>Total Current Assets</b></td>
                                    <td><b>8</b></td>
                                    <td style="text-align:right;"><b>'.$this->formatting($total_current_assets).'</b></td>
                                    <td style="text-align:right;"><b>'.$this->formatting(round(($total_current_assets / $total_assets) * 100, 2)).'%</b></td>
                                    <td><b>Total Current Liabilities</b></td>
                                    <td><b>23</b></td>
                                    <td style="text-align:right;"><b>'.$this->formatting($total_current_liabilities).'</b></td>
                                    <td style="text-align:right;"><b>'.$this->formatting(round(($total_current_liabilities / $total_liabilities_equity) * 100, 2)).'%</b></td>
                                </tr>';

                    //Other Assets & Investor Equity
                    $html .= '  <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr>
                                    <td>Long-term Investment</td>
                                    <td>9</td>
                                    <td style="text-align:right;">'.$this->formatting($long_term_investment).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($long_term_investment / $total_assets) * 100, 2)).'%</td>
                                    <td>Long Term Liabilities</td>
                                    <td>24</td>
                                    <td style="text-align:right;">'.$this->formatting($long_term_liabilities).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($long_term_liabilities / $total_liabilities_equity) * 100, 2)).'%</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr>
                                    <td>Fixed assets-cost</td>
                                    <td>10</td>
                                    <td style="text-align:right;">'.$this->formatting($fixed_asset_cost).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($fixed_asset_cost / $total_assets) * 100, 2)).'%</td>
                                    <td>Total Liabilities</td>
                                    <td>25</td>
                                    <td style="text-align:right;">'.$this->formatting($total_liabilities).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($total_liabilities / $total_liabilities_equity) * 100, 2)).'%</td>
                                </tr>
                                <tr>
                                    <td>Less: Accumulated depreciation</td>
                                    <td>11</td>
                                    <td style="text-align:right;">'.$this->formatting($less_accumulated).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($less_accumulated / $total_assets) * 100, 2)).'%</td>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr>
                                    <td>Total Fixed Assets</td>
                                    <td>12</td>
                                    <td style="text-align:right;">'.$this->formatting($total_fixed_asset).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($total_fixed_asset / $total_assets) * 100, 2)).'%</td>
                                    <td>Paid in Capital</td>
                                    <td>26</td>
                                    <td style="text-align:right;">'.$this->formatting($paid_in_capital).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($paid_in_capital / $total_liabilities_equity) * 100, 2)).'%</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                    <td>Capital Surplus</td>
                                    <td>27</td>
                                    <td style="text-align:right;">'.$this->formatting($capital_surplus).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($capital_surplus / $total_liabilities_equity) * 100, 2)).'%</td>
                                </tr>
                                <tr>
                                    <td>Right to use sites</td>
                                    <td>13</td>
                                    <td style="text-align:right;">'.$this->formatting($right_to_use_sites).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($right_to_use_sites / $total_assets) * 100, 2)).'%</td>
                                    <td>Revaluation Surplus</td>
                                    <td>28</td>
                                    <td style="text-align:right;">'.$this->formatting($revaluation_surplus).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($revaluation_surplus / $total_liabilities_equity) * 100, 2)).'%</td>
                                </tr>
                                <tr>
                                    <td>Other Deffered expeniture</td>
                                    <td>14</td>
                                    <td style="text-align:right;">'.$this->formatting($other_deffered_expeniture).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($other_deffered_expeniture / $total_assets) * 100, 2)).'%</td>
                                    <td>Retained Earning (Last Year)</td>
                                    <td>29</td>
                                    <td style="text-align:right;">'.$this->formatting($retained_earning).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($retained_earning / $total_liabilities_equity) * 100, 2)).'%</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                    <td>P/L From Jan - Now</td>
                                    <td>30</td>
                                    <td style="text-align:right;">'.$this->formatting($pl_from_jan_now).'</td>
                                    <td style="text-align:right;">'.$this->formatting(round(($pl_from_jan_now / $total_liabilities_equity) * 100, 2)).'%</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>Total Other Assets</b></td>
                                    <td><b>15</b></td>
                                    <td style="text-align:right;"><b>'.$this->formatting($total_other_assets).'</b></td>
                                    <td style="text-align:right;"><b>'.$this->formatting(round(($total_other_assets / $total_assets) * 100, 2)).'%</b></td>
                                    <td><b>Total Investor`s Equity</b></td>
                                    <td><b>31</b></td>
                                    <td style="text-align:right;"><b>'.$this->formatting($total_investor_equity).'</b></td>
                                    <td style="text-align:right;"><b>'.$this->formatting(round(($total_investor_equity / $total_liabilities_equity) * 100, 2)).'%</b></td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>Total Assets</b></td>
                                    <td><b>16</b></td>
                                    <td style="text-align:right;"><b>'.$this->formatting($total_assets).'</b></td>
                                    <td style="text-align:right;"><b>100%</b></td>
                                    <td><b>Total Liabilities And Equity</b></td>
                                    <td><b>32</b></td>
                                    <td style="text-align:right;"><b>'.$this->formatting($total_liabilities_equity).'</b></td>
                                    <td style="text-align:right;"><b>100%</b></td>
                                </tr>';

                $html .= '</table>';
            }else{
                $html .= '<div style="width: 100%; font-size:12px; text-align:right;"><i>(Expressed in Rupiah)</i></div>';
                $html .= '<table id="customers" border="1">';
                //Current Assets
                $html .= '  <tr style="background: #E8E8E8;">
                                <th width="200">DESCRIPTION</th>
                                <th width="10">Notes</th>
                                <th width="50">Amount</th>
                            </tr>
                            <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr>
                                <td>Cash on Hand</td>
                                <td>1</td>
                                <td style="text-align:right;">'.$this->formatting($cash_on_hand).'</td>
                            </tr>
                            <tr>
                                <td>Cash in Bank</td>
                                <td>2</td>
                                <td style="text-align:right;">'.$this->formatting($cash_in_bank).'</td>
                            </tr>
                            <tr>
                                <td>Time Deposit</td>
                                <td>3</td>
                                <td style="text-align:right;">'.$this->formatting($time_deposit).'</td>
                            </tr>
                            <tr>
                                <td>Account Receivable</td>
                                <td>4</td>
                                <td style="text-align:right;">'.$this->formatting($account_receivable).'</td>
                            </tr>
                            <tr>
                                <td>Allow.Bad Debt</td>
                                <td>5</td>
                                <td style="text-align:right;">'.$this->formatting($allow_bad_debt).'</td>
                            </tr>
                            <tr>
                                <td>Inventories</td>
                                <td>6</td>
                                <td style="text-align:right;">'.$this->formatting($inventories).'</td>
                            </tr>
                            <tr>
                                <td>Prepaid Expense</td>
                                <td>7</td>
                                <td style="text-align:right;">'.$this->formatting($prepaid_expense).'</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr style="background: #E8E8E8;">
                                <td><b>Total Current Assets</b></td>
                                <td><b>8</b></td>
                                <td style="text-align:right;"><b>'.$this->formatting($total_current_assets).'</b></td>
                            </tr>';

                //Other Assets
                $html .= '  <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr>
                                <td>Long-term Investment</td>
                                <td>9</td>
                                <td style="text-align:right;">'.$this->formatting($long_term_investment).'</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr>
                                <td>Fixed assets-cost</td>
                                <td>10</td>
                                <td style="text-align:right;">'.$this->formatting($fixed_asset_cost).'</td>
                            </tr>
                            <tr>
                                <td>Less: Accumulated depreciation</td>
                                <td>11</td>
                                <td style="text-align:right;">'.$this->formatting($less_accumulated).'</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr>
                                <td>Total Fixed Assets</td>
                                <td>12</td>
                                <td style="text-align:right;">'.$this->formatting($total_fixed_asset).'</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr>
                                <td>Right to use sites</td>
                                <td>13</td>
                                <td style="text-align:right;">'.$this->formatting($right_to_use_sites).'</td>
                            </tr>
                            <tr>
                                <td>Other Deffered expeniture</td>
                                <td>14</td>
                                <td style="text-align:right;">'.$this->formatting($other_deffered_expeniture).'</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr style="background: #E8E8E8;">
                                <td><b>Total Other Assets</b></td>
                                <td><b>15</b></td>
                                <td style="text-align:right;"><b>'.$this->formatting($total_other_assets).'</b></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr style="background: #E8E8E8;">
                                <td><b>Total Assets</b></td>
                                <td><b>16</b></td>
                                <td style="text-align:right;"><b>'.$this->formatting($total_assets).'</b></td>
                            </tr>';

                //Current Liabilities
                $html .= '  <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr>
                                <td>Short Term Loans</td>
                                <td>17</td>
                                <td style="text-align:right;">'.$this->formatting($short_term_loans).'</td>
                            </tr>
                            <tr>
                                <td>Account Payable</td>
                                <td>18</td>
                                <td style="text-align:right;">'.$this->formatting($account_payable).'</td>
                            </tr>
                            <tr>
                                <td>Accured Expense</td>
                                <td>19</td>
                                <td style="text-align:right;">'.$this->formatting($accured_expense).'</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr>
                                <td>Taxes Payable</td>
                                <td>20</td>
                                <td style="text-align:right;">'.$this->formatting($taxes_payable).'</td>
                            </tr>
                            <tr>
                                <td>Acc.Payable Machine</td>
                                <td>21</td>
                                <td style="text-align:right;">'.$this->formatting($acc_payable_machine).'</td>
                            </tr>
                            <tr>
                                <td>Acc.Payable Other</td>
                                <td>22</td>
                                <td style="text-align:right;">'.$this->formatting($acc_payable_other).'</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr style="background: #E8E8E8;">
                                <td><b>Total Current Liabilities</b></td>
                                <td><b>23</b></td>
                                <td style="text-align:right;"><b>'.$this->formatting($total_current_liabilities).'</b></td>
                            </tr>';

                //Investor Equity
                $html .= '  <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr>
                                <td>Long Term Liabilities</td>
                                <td>24</td>
                                <td style="text-align:right;">'.$this->formatting($long_term_liabilities).'</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr>
                                <td>Total Liabilities</td>
                                <td>25</td>
                                <td style="text-align:right;">'.$this->formatting($total_liabilities).'</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr>
                                <td>Paid in Capital</td>
                                <td>26</td>
                                <td style="text-align:right;">'.$this->formatting($paid_in_capital).'</td>
                            </tr>
                            <tr>
                                <td>Capital Surplus</td>
                                <td>27</td>
                                <td style="text-align:right;">'.$this->formatting($capital_surplus).'</td>
                            </tr>
                            <tr>
                                <td>Revaluation Surplus</td>
                                <td>28</td>
                                <td style="text-align:right;">'.$this->formatting($revaluation_surplus).'</td>
                            </tr>
                            <tr>
                                <td>Retained Earning (Last Year)</td>
                                <td>29</td>
                                <td style="text-align:right;">'.$this->formatting($retained_earning).'</td>
                            </tr>
                            <tr>
                                <td>P/L From Jan - Now</td>
                                <td>30</td>
                                <td style="text-align:right;">'.$this->formatting($pl_from_jan_now).'</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr style="background: #E8E8E8;">
                                <td><b>Total Investor`s Equity</b></td>
                                <td><b>31</b></td>
                                <td style="text-align:right;"><b>'.$this->formatting($total_investor_equity).'</b></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="height:20px;"></td>
                            </tr>
                            <tr style="background: #E8E8E8;">
                                <td><b>Total Liabilities And Equity</b></td>
                                <td><b>32</b></td>
                                <td style="text-align:right;"><b>'.$this->formatting($total_liabilities_equity).'</b></td>
                            </tr>';

                $html .= '</table>';
            }
        
        $html .= '</body></html>';
        echo $html;
    }
}
