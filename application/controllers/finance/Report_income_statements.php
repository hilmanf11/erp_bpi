<?php
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_income_statements extends CI_Controller
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
            $this->load->view('finance/report_income_statements');
        } else {
            redirect('error_access');
        }
    }

    function getData($filter_from, $filter_to, $category, $modul, $name){
        $period = date("Ym", strtotime($filter_from));

        $this->db->select('b.account_number, b.account_name, b.local_debit, b.local_kredit');
        $this->db->from('account_statements a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number');
        //$this->db->where('a.category', $category);
        $this->db->where('a.modul', $modul);
        $this->db->where('a.name', $name);
        $this->db->group_by('a.account_number');
        //$this->db->order_by('a.flag', 'asc');
        $accounts = $this->db->get()->result_array();

        $ending = 0;
        foreach ($accounts as $account) {
            $account_number = $account['account_number'];
            // $trial_balance = $this->crud->read('trial_balances', [], ["account_number" => $account['account_number'], "period" => $period]);
            $trial_balances = $this->crud->query("SELECT * FROM trial_balances WHERE account_number = '$account_number' and `period` = '$period'");
            
            foreach ($trial_balances as $trial_balance) {
                $ending += ($trial_balance->ending_debit - abs($trial_balance->ending_credit));
            }
        }

        return $ending;
    }

    function getDataAcc($filter_from, $filter_to, $name){
        $period = date("Ym", strtotime($filter_from));
        $period_to = date("Ym", strtotime($filter_to));

        $income = $this->crud->query("SELECT name, SUM(amount) as amount FROM income_statements WHERE name = '$name' and `period` < '$period_to'");

        return @$income[0]->amount;
    }

    function getDataCustom($filter_from, $filter_to, $account, $category){
        $period = date("Ym", strtotime($filter_from));
        $period_to = date("Ym", strtotime($filter_to));

        $trial_balances = $this->crud->query("SELECT * FROM trial_balances WHERE account_number = '$account' and `period` BETWEEN '$period' and '$period_to'");
        $ending = 0;
        foreach ($trial_balances as $trial_balance) {
            if($category == "BEGIN"){
                $ending += ($trial_balance->begin_debit - $trial_balance->begin_credit);
            }elseif($category == "TRANS_DEBIT"){
                $ending += $trial_balance->local_debit;
            }elseif($category == "TRANS_CREDIT"){
                $ending += $trial_balance->local_credit;
            }else{
                $ending += ($trial_balance->ending_debit - $trial_balance->ending_credit);
            }
        }

        return $ending;
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

        $filter_from_bf = date("Y-01-01", strtotime($filter_date));

        //Manufacturing
        // $raw_material_begin = $this->getData($filter_from, $filter_to, "Manufacturing", "Income Statement", "Raw Material, Begining");
        $raw_material_begin = $this->getDataCustom($filter_from, $filter_to, "1131", "BEGIN");
        $raw_material_begin_acc = ($raw_material_begin + $this->getDataAcc($filter_from_bf, $filter_to, "Raw Material"));
        // $purchase = $this->getData($filter_from, $filter_to, "Manufacturing", "Income Statement", "Purchase");
        $purchase = $this->getDataCustom($filter_from, $filter_to, "1131", "TRANS_DEBIT");
        $purchase_acc = ($purchase + $this->getDataAcc($filter_from_bf, $filter_to, "Purchase"));

        $misc_transaction = $this->getData($filter_from, $filter_to, "Manufacturing", "Income Statement", "MISC Transaction");
        $misc_transaction_acc = ($misc_transaction + $this->getDataAcc($filter_from_bf, $filter_to, "MISC Transaction"));
        $return_material = $this->getData($filter_from, $filter_to, "Manufacturing", "Income Statement", "Return Borrowed Material");
        $return_material_acc = ($return_material + $this->getDataAcc($filter_from_bf, $filter_to, "Return Borrowed Material"));
        $adj_costing = $this->getData($filter_from, $filter_to, "Manufacturing", "Income Statement", "Adjustment Costing");
        $adj_costing_acc = ($adj_costing + $this->getDataAcc($filter_from_bf, $filter_to, "Adjustment Costing"));

        $available_invproses = ($raw_material_begin + $purchase + $misc_transaction + $return_material + $adj_costing);
        $available_invproses_acc = ($raw_material_begin_acc + $purchase_acc + $misc_transaction_acc + $return_material_acc + $adj_costing_acc);
        
        $raw_material_end = $this->getData($filter_from, $filter_to, "Manufacturing", "Income Statement", "Raw Material, Ending");
        $raw_material_end_acc = ($raw_material_end + $this->getDataAcc($filter_from_bf, $filter_to, "Raw Material, Ending"));
        $raw_material_used = ($available_invproses - $raw_material_end);
        $raw_material_used_acc = ($available_invproses_acc - $raw_material_end_acc);

        $direct_labour = $this->getData($filter_from, $filter_to, "Manufacturing", "Income Statement", "DIRECT LABOUR");
        $direct_labour_acc = ($direct_labour + $this->getDataAcc($filter_from_bf, $filter_to, "DIRECT LABOUR"));
        $factory_overhead = $this->getData($filter_from, $filter_to, "Manufacturing", "Income Statement", "FACTORY OVERHEAD");
        $factory_overhead_acc = ($factory_overhead + $this->getDataAcc($filter_from_bf, $filter_to, "FACTORY OVERHEAD"));
        $work_in_proses_begin = $this->getDataCustom($filter_from, $filter_to, "1133", "BEGIN");
        $work_in_proses_begin_acc = ($work_in_proses_begin + $this->getDataAcc($filter_from_bf, $filter_to, "Work In Process, Begining"));
        $work_in_proses_end = $this->getData($filter_from, $filter_to, "Manufacturing", "Income Statement", "Work in Proses, Ending");
        $work_in_proses_end_acc = ($work_in_proses_end + $this->getDataAcc($filter_from_bf, $filter_to, "Work in Proses, Ending"));

        //Finish Good
        $finishgood_begin = $this->getDataCustom($filter_from, $filter_to, "1134", "BEGIN");
        $finishgood_begin_acc = ($finishgood_begin + $this->getDataAcc($filter_from_bf, $filter_to, "FINISH GOODS, BEGINING"));
        $finishgood_ending = $this->getData($filter_from, $filter_to, "Finishgood", "Income Statement", "FINISH GOODS, ENDING");
        $finishgood_ending_acc = ($finishgood_ending + $this->getDataAcc($filter_from_bf, $filter_to, "FINISH GOODS, ENDING"));
        $cost_of_good_finishgood = ($finishgood_begin + $finishgood_ending);
        $cost_of_good_finishgood_acc = ($finishgood_begin_acc + $finishgood_ending_acc);

        //Sales
        $sales = abs($this->getData($filter_from, $filter_to, "Sales", "Income Statement", "Sales"));
        $sales_acc = ($sales + $this->getDataAcc($filter_from_bf, $filter_to, "Sales"));
        $sales_rerturn = abs($this->getData($filter_from, $filter_to, "Sales", "Income Statement", "Sales Return"));
        $sales_rerturn_acc = ($sales_rerturn + $this->getDataAcc($filter_from_bf, $filter_to, "Sales Return"));
        $sales_discount = $this->getData($filter_from, $filter_to, "Sales", "Income Statement", "Sales Discount");
        $sales_discount_acc = ($sales_discount + $this->getDataAcc($filter_from_bf, $filter_to, "Sales Discount"));
        $total_sales = ($sales + $sales_rerturn + $sales_discount);
        $total_sales_acc = ($sales_acc + $sales_rerturn_acc + $sales_discount_acc);

        //Gross Profit Loss
        $cogs = $this->getData($filter_from, $filter_to, "Cost of Good Sold", "Income Statement", "Cost of Good Sold");
        $cogs_acc = ($cogs + $this->getDataAcc($filter_from_bf, $filter_to, "Cost of Good Sold"));
        $total_cogs = ($total_sales - $cogs);
        $total_cogs_acc = ($total_sales_acc - $cogs_acc);
        $net_profit_before = ($total_cogs - $total_operating_expenses);

        //Operating Expenses
        $selling = $this->getData($filter_from, $filter_to, "Operating Expenses", "Income Statement", "Selling");
        $selling_acc = ($selling + $this->getDataAcc($filter_from_bf, $filter_to, "Selling"));
        $general_administrative = $this->getData($filter_from, $filter_to, "Operating Expenses", "Income Statement", "General and Administrative");
        $general_administrative_acc = ($general_administrative + $this->getDataAcc($filter_from_bf, $filter_to, "General and Administrative"));

        //Non Operating Expenses
        $interest_income = $this->getData($filter_from, $filter_to, "Non Operating Expenses", "Income Statement", "Interest Income");
        $interest_income_acc = ($interest_income + $this->getDataAcc($filter_from_bf, $filter_to, "Interest Income"));
        $bank_charge = $this->getData($filter_from, $filter_to, "Non Operating Expenses", "Income Statement", "Bank Charge");
        $bank_charge_acc = ($bank_charge + $this->getDataAcc($filter_from_bf, $filter_to, "Bank Charge"));
        $other = $this->getData($filter_from, $filter_to, "Non Operating Expenses", "Income Statement", "Other");
        $other_acc = ($other + $this->getDataAcc($filter_from_bf, $filter_to, "Other"));
        $foreign_exchange_loss = $this->getData($filter_from, $filter_to, "Non Operating Expenses", "Income Statement", "Foreign Exchange Loss/Profit");
        $foreign_exchange_loss_acc = ($foreign_exchange_loss + $this->getDataAcc($filter_from_bf, $filter_to, "Foreign Exchange Loss/Profit"));
        $total_non_operating_expenses = ($interest_income + $bank_charge + $foreign_exchange_loss + $other);
        $total_non_operating_expenses_acc = ($interest_income_acc + $bank_charge_acc + $foreign_exchange_loss_acc + $other_acc);

        $total_operating_expenses = ($selling + $general_administrative + $total_non_operating_expenses);
        $total_operating_expenses_acc = ($selling_acc + $general_administrative_acc + $total_non_operating_expenses_acc);

        //Taxes
        $corporate_income_tax = $this->getData($filter_from, $filter_to, "Taxes", "Income Statement", "Corporate Income Tax");
        $corporate_income_tax_acc = ($corporate_income_tax + $this->getDataAcc($filter_from_bf, $filter_to, "Corporate Income Tax"));
        $deffered_income_tax = $this->getData($filter_from, $filter_to, "Taxes", "Income Statement", "Deffered Income Tax");
        $deffered_income_tax_acc = ($deffered_income_tax + $this->getDataAcc($filter_from_bf, $filter_to, "Deffered Income Tax"));
        $total_taxes = (($total_cogs - $total_operating_expenses) - $corporate_income_tax - $deffered_income_tax);
        $total_taxes_acc = (($total_cogs_acc - $total_operating_expenses_acc) - $corporate_income_tax_acc - $deffered_income_tax_acc);

        $data = array(
                ["period" => $period,"name" => "Raw Material","amount" => $raw_material_begin, "accumulated" => $raw_material_begin_acc],
                ["period" => $period,"name" => "Purchase","amount" => $purchase, "accumulated" => $purchase_acc],
                ["period" => $period,"name" => "MISC Transaction","amount" => $misc_transaction, "accumulated" => $misc_transaction_acc],
                ["period" => $period,"name" => "Return Borrowed Material","amount" => $return_material, "accumulated" => $return_material_acc],
                ["period" => $period,"name" => "Adjustment Costing","amount" => $adj_costing, "accumulated" => $adj_costing_acc],
                ["period" => $period,"name" => "Available Inv To Proses","amount" => $available_invproses, "accumulated" => $available_invproses_acc],
                ["period" => $period,"name" => "Raw Material, Ending","amount" => $raw_material_end, "accumulated" => $raw_material_end_acc],
                ["period" => $period,"name" => "Raw Material Used","amount" => $raw_material_used, "accumulated" => $raw_material_used_acc],
                ["period" => $period,"name" => "DIRECT LABOUR","amount" => $direct_labour, "accumulated" => $direct_labour_acc],
                ["period" => $period,"name" => "FACTORY OVERHEAD","amount" => $factory_overhead, "accumulated" => $factory_overhead_acc],
                ["period" => $period,"name" => "Work In Process, Begining","amount" => $work_in_proses_begin, "accumulated" => $work_in_proses_begin_acc],
                ["period" => $period,"name" => "Work In Process, Ending","amount" => $work_in_proses_end, "accumulated" => $work_in_proses_end_acc],
                ["period" => $period,"name" => "FINISH GOODS, BEGINING","amount" => $finishgood_begin, "accumulated" => $finishgood_begin_acc],
                ["period" => $period,"name" => "FINISH GOODS, ENDING","amount" => $finishgood_ending, "accumulated" => $finishgood_ending_acc],
                ["period" => $period,"name" => "COGS","amount" => $cost_of_good_finishgood, "accumulated" => $cost_of_good_finishgood_acc],
                ["period" => $period,"name" => "Sales","amount" => $sales, "accumulated" => $sales_acc],
                ["period" => $period,"name" => "Sales Return","amount" => $sales_rerturn, "accumulated" => $sales_rerturn_acc],
                ["period" => $period,"name" => "Sales Discount","amount" => $sales_discount, "accumulated" => $sales_discount_acc],
                ["period" => $period,"name" => "Total Sales","amount" => $total_sales, "accumulated" => $total_sales_acc],
                ["period" => $period,"name" => "Cost of Good Sold","amount" => $cogs, "accumulated" => $cogs_acc],
                ["period" => $period,"name" => "Gross Profit Loss","amount" => $total_cogs, "accumulated" => $total_cogs_acc],
                ["period" => $period,"name" => "Selling","amount" => $selling, "accumulated" => $selling_acc],
                ["period" => $period,"name" => "General and Administrative","amount" => $general_administrative, "accumulated" => $general_administrative_acc],
                ["period" => $period,"name" => "Interest Income","amount" => $interest_income, "accumulated" => $interest_income_acc],
                ["period" => $period,"name" => "Bank Charge","amount" => $bank_charge, "accumulated" => $bank_charge_acc],
                ["period" => $period,"name" => "Other","amount" => $other, "accumulated" => $other_acc],
                ["period" => $period,"name" => "Foreign Exchange Loss/Profit","amount" => $foreign_exchange_loss, "accumulated" => $foreign_exchange_loss_acc],
                ["period" => $period,"name" => "Total Non Operating Expenses (Income)","amount" => $total_non_operating_expenses, "accumulated" => $total_non_operating_expenses_acc],
                ["period" => $period,"name" => "Total Operating Expenses","amount" => $total_operating_expenses, "accumulated" => $total_operating_expenses_acc],
                ["period" => $period,"name" => "Corporate Income Tax","amount" => $corporate_income_tax, "accumulated" => $corporate_income_tax_acc],
                ["period" => $period,"name" => "Deffered Income Tax","amount" => $deffered_income_tax, "accumulated" => $deffered_income_tax_acc],
                ["period" => $period,"name" => "NET PROFIT LOSS AFTER TAXES","amount" => $total_taxes, "accumulated" => $total_taxes_acc]
            );
        $result['total'] = count($data);
        $result = array_merge($result, ['rows' => $data]);
        echo json_encode($result);
    }

    public function datatables($period, $name){
        $account_statements = $this->crud->read('income_statements', [], ["period" => $period, "name" => $name]);
        return $account_statements;
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');

            $trial_balances = $this->crud->reads("income_statements", [], [
                "period" => $post['period'], 
                "name" => $post['name']
            ]);

            if(count($trial_balances) > 0){
                $send = $this->crud->update('income_statements', [
                    "period" => $post['period'], 
                    "name" => $post['name']
                ], $post);

                echo $send;
            }else{
                $send = $this->crud->create('income_statements', $post);
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
            header("Content-Disposition: attachment; filename=income_statements_$format.xls");
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
                <h3 style="margin:0;">INCOME STATEMENT</h3>
                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
            </center>
            <br>';

            if($filter_display == "1"){
                $html .= '<table id="customers" border="1">';

                    //Sales
                    $html .= '<div style="width: 100%; font-size:12px; text-align:right;"><i>(Expressed in Rupiah)</i></div>';
                    $html .= '<table id="customers" border="1">';

                    //Sales
                    $html .= '  <tr style="background: #E8E8E8;">
                                    <th width="200">DESCRIPTION</th>
                                    <th width="10">Notes</th>
                                    <th width="50">Amount</th>
                                    <th width="50">Accumulated Jan - Now</th>
                                </tr>
                                <tr>
                                    <td>Sales</td>
                                    <td>1</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Sales")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Sales")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td>Sales Return</td>
                                    <td>2</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Sales Return")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Sales Return")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td>Sales Discount</td>
                                    <td>3</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Sales Discoun")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Sales Discoun")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>Total Sales</b></td>
                                    <td><b>4</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Sales")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Sales")->accumulated).'</td>
                                </tr>';

                    //Gross Profit Loss        
                    $html .= '  <tr>
                                    <td>Cost of Good Sold</td>
                                    <td>5</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Cost Of Good Sold")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Cost Of Good Sold")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>Gross Profit Loss</b></td>
                                    <td><b>6</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Gross Profit Loss")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Gross Profit Loss")->accumulated).'</td>
                                </tr>';

                    //Operating Expenses & Non Operating Expenses 
                    $html .= '  <tr>
                                    <td colspan="4" style="height:20px;">Operating Expenses</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">Selling</td>
                                    <td>7</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Selling")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Selling")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">General and Administrative</td>
                                    <td>8</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "General and Administrative")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "General and Administrative")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;">Non Operating Expenses</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">Interest Income</td>
                                    <td>9</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Interest Income")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Interest Income")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">Bank Charge</td>
                                    <td>10</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Bank Charge")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Bank Charge")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">Foreign Exchange Loss/Profit</td>
                                    <td>11</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Foreign Exchange Loss/Profit")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Foreign Exchange Loss/Profit")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">Other</td>
                                    <td>12</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Other")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Other")->accumulated).'</td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>Total Non Operating Expenses (Income)</b></td>
                                    <td><b>13</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Non Operating Expenses (Income)")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Non Operating Expenses (Income)")->accumulated).'</td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>Total Operating Expenses</b></td>
                                    <td><b>14</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Operating Expenses")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Operating Expenses")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>NET PROFIT LOSS BEFORE TAXES</b></td>
                                    <td><b>15</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Gross Profit Loss")->amount - $this->datatables($period, "Total Operating Expenses")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Gross Profit Loss")->accumulated - $this->datatables($period, "Total Operating Expenses")->accumulated).'</td>
                                </tr>';

                    //Taxes      
                    $html .= '  <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr>
                                    <td>Corporate Income Tax</td>
                                    <td>16</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Corporate Income Tax")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Corporate Income Tax")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td>Deffered Income Tax</td>
                                    <td>17</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Deffered Income Tax")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Deffered Income Tax")->accumulated).'</td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>NET PROFIT LOSS AFTER TAXES</b></td>
                                    <td><b>18</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "NET PROFIT LOSS AFTER TAXES")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "NET PROFIT LOSS AFTER TAXES")->accumulated).'</td>
                                </tr>';

                $html .= '</table>';
            }else{
                $html .= '<table id="customers" border="1">';
                    $html .= '<div style="width: 100%; font-size:12px; text-align:right;"><i>(Expressed in Rupiah)</i></div>';
                    $html .= '<table id="customers" border="1">';

                    //Manufacturing
                    $html .= '  <tr style="background: #E8E8E8;">
                                    <th width="200">DESCRIPTION</th>
                                    <th width="10">Notes</th>
                                    <th width="50">Amount</th>
                                    <th width="50">Accumulated Jan - Now</th>
                                </tr>
                                <tr>
                                    <td>Raw Material</td>
                                    <td>1</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Raw Material")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Raw Material")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">Purchase</td>
                                    <td>2</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Purchase")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Purchase")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">MISC Transaction</td>
                                    <td>3</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "MISC Transaction")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "MISC Transaction")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">Return & Borrowed Material</td>
                                    <td>4</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Return Borriwed Material")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Return Borriwed Material")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">Adjustment Costing</td>
                                    <td>5</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Adjustment Costing")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Adjustment Costing")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td>Available Inv To Proses</td>
                                    <td>6</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Available Inv To Proses")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Available Inv To Proses")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td>Raw Material, Ending</td>
                                    <td>7</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Raw Material, Ending")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Raw Material, Ending")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td>Raw Material Used</td>
                                    <td>8</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Raw Material Used")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Raw Material Used")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td>DIRECT LABOUR</td>
                                    <td>9</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "DIRECT LABOUR")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "DIRECT LABOUR")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td>FACTORY OVERHEAD</td>
                                    <td>10</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "FACTORY OVERHEAD")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "FACTORY OVERHEAD")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;">WORK IN PROSES INVENTORY</td>
                                </tr>
                                <tr>
                                    <td>Work In Process, Begining</td>
                                    <td>11</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Work In Process, Begining")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Work In Process, Begining")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td>Work In Process, Ending</td>
                                    <td>12</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Work In Process, Ending")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Work In Process, Ending")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>Cost of Good Manufacturing</b></td>
                                    <td><b>13</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Sales")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Sales")->accumulated).'</td>
                                </tr>';

                    //Finishgood
                    $html .= '  <tr>
                                    <td>FINISH GOODS, BEGINING</td>
                                    <td>14</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "FINISH GOODS, BEGINING")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "FINISH GOODS, BEGINING")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td>FINISH GOODS, ENDING</td>
                                    <td>15</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "FINISH GOODS, ENDING")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "FINISH GOODS, ENDING")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>Cost of Good Sold</b></td>
                                    <td><b>16</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "COGS")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "COGS")->accumulated).'</td>
                                </tr>';
                                
                    //Sales
                    $html .= '  <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr>
                                    <td>Sales</td>
                                    <td>17</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Sales")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Sales")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td>Sales Return</td>
                                    <td>18</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Sales Return")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Sales Return")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td>Sales Discount</td>
                                    <td>19</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Sales Discount")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Sales Discount")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>Total Sales</b></td>
                                    <td><b>20</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Sales")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Sales")->accumulated).'</td>
                                </tr>';

                    //Gross Profit Loss        
                    $html .= '  <tr>
                                    <td>Cost of Good Sold</td>
                                    <td>21</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Cost Of Good Sold")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Cost Of Good Sold")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>Gross Profit Loss</b></td>
                                    <td><b>22</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Gross Profit Loss")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Gross Profit Loss")->accumulated).'</td>
                                </tr>';

                    //Operating Expenses & Non Operating Expenses 
                    $html .= '  <tr>
                                    <td colspan="4" style="height:20px;">Operating Expenses</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">Selling</td>
                                    <td>23</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Selling")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Selling")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">General and Administrative</td>
                                    <td>24</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "General and Administrative")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "General and Administrative")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;">Non Operating Expenses</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">Interest Income</td>
                                    <td>25</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Interest Income")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Interest Income")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">Bank Charge</td>
                                    <td>26</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Bank Charge")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Bank Charge")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">Foreign Exchange Loss/Profit</td>
                                    <td>27</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Foreign Exchange Loss/Profit")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Foreign Exchange Loss/Profit")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td style="padding-left: 50px;">Other</td>
                                    <td>28</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Other")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "other")->accumulated).'</td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>Total Non Operating Expenses (Income)</b></td>
                                    <td><b>29</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Non Operating Expenses (Income)")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Non Operating Expenses (Income)")->accumulated).'</td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>Total Operating Expenses</b></td>
                                    <td><b>30</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Operating Expenses")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Total Operating Expenses")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>NET PROFIT LOSS BEFORE TAXES</b></td>
                                    <td><b>31</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Gross Profit Loss")->amount - $this->datatables($period, "Total Operating Expenses")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Gross Profit Loss")->accumulated - $this->datatables($period, "Total Operating Expenses")->accumulated).'</td>
                                </tr>';

                    //Taxes      
                    $html .= '  <tr>
                                    <td colspan="4" style="height:20px;"></td>
                                </tr>
                                <tr>
                                    <td>Corporate Income Tax</td>
                                    <td>32</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Corporate Income Tax")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Corporate Income Tax")->accumulated).'</td>
                                </tr>
                                <tr>
                                    <td>Deffered Income Tax</td>
                                    <td>33</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Deffered Income Tax")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "Deffered Income Tax")->accumulated).'</td>
                                </tr>
                                <tr style="background: #E8E8E8;">
                                    <td><b>NET PROFIT LOSS AFTER TAXES</b></td>
                                    <td><b>34</b></td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "NET PROFIT LOSS AFTER TAXES")->amount).'</td>
                                    <td style="text-align:right;">'.$this->formatting($this->datatables($period, "NET PROFIT LOSS AFTER TAXES")->accumulated).'</td>
                                </tr>';

                $html .= '</table>';
            }
        
        $html .= '</body></html>';
        echo $html;
    }
}
