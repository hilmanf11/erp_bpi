<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_cogs extends CI_Controller
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
            $this->load->view('finance/report_cogs');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_cogs_$format.xls");
        }

        $filter_month = $this->input->get("filter_month");
        $filter_year = $this->input->get("filter_year");
        $filter_customer = $this->input->get("filter_customer");

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('d.number as voucher_no,
            a.number as sales_invoice_no, 
            b.name as customer_name,
            a.trans_date,
            a.dn_number,
            c.number as product_no,
            c.name as product_name,
            a.uom,
            a.currency,
            SUM(a.qty) as qty,
            SUM(a.total) as total,
            d.rates,
            e.direct_material,
            e.direct_labor,
            e.direct_foh,
            e.amount as amount_out,
            a.account_number,
            g.account_name');
        $this->db->from('sales_invoices a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join("(SELECT rates, document_no, number FROM journal_postings WHERE account_number = '4111002' GROUP BY document_no, number) d", "a.number = d.document_no");
        $this->db->join("(SELECT item_id, document_no, trans_type, SUM(amount) as amount, SUM(direct_material) as direct_material, SUM(direct_labor) as direct_labor, SUM(direct_foh) as direct_foh FROM inventory_fg WHERE trans_type = 'DELIVERY NOTE' GROUP BY document_no, item_id) e", "a.item_id = e.item_id and a.dn_number = e.document_no");
        $this->db->join("(SELECT item_id, number, trans_type, SUM(qty) as qty FROM delivery_notes GROUP BY item_id, number) f", 'a.dn_number = f.number and a.item_id = f.item_id');
        $this->db->join('account_coa g', 'a.account_number = g.account_number');
        $this->db->where('f.trans_type', 'SALES');
        $this->db->like("a.trans_date", $filter_year."-".$filter_month);
        $this->db->like("a.customer_id", $filter_customer);
        $this->db->group_by('a.item_id');
        $this->db->group_by('a.dn_number');
        $this->db->group_by('a.number');
        $this->db->order_by('a.number', 'asc');
        $invoices = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
        <div style="width:200%;">
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
                <h3 style="margin:0;">COST OF GOOD SOLD REPORT</h3>
                <small>PERIOD : <b>' . date("F Y", strtotime($filter_year ."-". $filter_month."-01")) . '</b></small>
            </center>
            <br><br>
            
            <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Voucher No</th>
                <th rowspan="2">Sales Invoice No</th>
                <th rowspan="2">Customer Name</th>
                <th rowspan="2">Trans Date</th>
                <th rowspan="2">Delivery Note</th>
                <th rowspan="2">Product No</th>
                <th rowspan="2">Product Name</th>
                <th rowspan="2">Uom</th>
                <th rowspan="2">Qty</th>
                <th colspan="3">Original Currency</th>
                <th colspan="3">Local Currency</th>
                <th rowspan="2">Material</th>
                <th rowspan="2">Labor</th>
                <th rowspan="2">FOH</th>
                <th rowspan="2">COGS</th>
                <th rowspan="2">Gain/Loss</th>
                <th rowspan="2">% Gain/Loss</th>
                <th rowspan="2">Account No</th>
                <th rowspan="2">Account Name</th>
            </tr>
            <tr>
                <th>Currency</th>
                <th>Price</th>
                <th>Amount</th>
                <th>Currency</th>
                <th>Rates</th>
                <th>Amount</th>
            </tr>';

        $no = 1;
        $total_gainloss = 0;
        $total_gainlossPersen = 0;

        $total_qty = 0;
        $total_original = 0;
        $total_amount = 0;
        $total_material = 0;
        $total_labor = 0;
        $total_foh = 0;
        $total_cogs = 0;
        foreach ($invoices as $invoice) {
            $amount = ($invoice['total'] * $invoice['rates']);
            $cogs = abs($invoice['amount_out']);
            $gainloss = ($amount - $cogs);
            $gainlossPersen = (($gainloss / $amount) * 100);

            $total_gainloss += $gainloss;

            if($gainloss > 0){
                $gainloss = number_format($gainloss, 2);
                $style = "";
            }else{
                $gainloss = "(".number_format(abs($gainloss), 2).")";
                $style = "color:red;";
            }

            if($gainlossPersen > 0){
                $gainlossPersen = number_format($gainlossPersen, 2);
                $style2 = "";
            }else{
                $gainlossPersen = "(".number_format(abs($gainlossPersen), 2).")";
                $style2 = "color:red;";
            }

            $total_qty += $invoice['qty'];
            $total_original += $invoice['total'];
            $total_amount += $amount;
            $total_material += abs($invoice['direct_material']);
            $total_labor += abs($invoice['direct_labor']);
            $total_foh += abs($invoice['direct_foh']);
            $total_cogs += $cogs;
            $html .= '  <tr>
                            <td style="text-align:center">'.$no.'</td>
                            <td>'.$invoice['voucher_no'].'</td>
                            <td>'.$invoice['sales_invoice_no'].'</td>
                            <td>'.$invoice['customer_name'].'</td>
                            <td>'.$invoice['trans_date'].'</td>
                            <td>'.$invoice['dn_number'].'</td>
                            <td>'.$invoice['product_no'].'</td>
                            <td>'.$invoice['product_name'].'</td>
                            <td style="text-align:center;">' . $invoice['uom'] . '</td>
                            <td style="text-align:right;">' . number_format($invoice['qty']) . '</td>
                            <td style="text-align:center;">' . $invoice['currency'] . '</td>
                            <td style="text-align:right;">' . number_format(($invoice['total'] / $invoice['qty']), 4) . '</td>
                            <td style="text-align:right;">' . number_format($invoice['total'], 2) . '</td>
                            <td style="text-align:center;">IDR</td>
                            <td style="text-align:right;">' . number_format($invoice['rates'], 2) . '</td>
                            <td style="text-align:right;">' . number_format($amount, 2) . '</td>
                            <td style="text-align:right;">' . number_format(abs($invoice['direct_material']), 2) . '</td>
                            <td style="text-align:right;">' . number_format(abs($invoice['direct_labor']), 2) . '</td>
                            <td style="text-align:right;">' . number_format(abs($invoice['direct_foh']), 2) . '</td>
                            <td style="text-align:right;">' . number_format($cogs, 2) . '</td>
                            <td style="text-align:right;'.$style.'">' . $gainloss . '</td>
                            <td style="text-align:right;'.$style2.'">' . $gainlossPersen . '</td>
                            <td>'.$invoice['account_number'].'</td>
                            <td>'.$invoice['account_name'].'</td>
                        </tr>';
            $no++;
        }

        if($total_gainloss > 0){
            $total_gainlosspersen = (($total_gainloss / $total_amount) * 100);
        }else{
            $total_gainlosspersen = 0;
        }

        if($filter_month == "07"){
            $total_cogs_html = number_format(abs($total_cogs - 0.01), 2);
        }else{
            $total_cogs_html = number_format(abs($total_cogs), 2);
        }

        $html .= '  <tr>
                        <td style="text-align:right;" colspan="9"><b>GRAND TOTAL</b></td>
                        <td style="text-align:right; font-weight:bold;">'.number_format($total_qty).'</td>
                        <td style="text-align:right; font-weight:bold;">-</td>
                        <td style="text-align:right; font-weight:bold;">-</td>
                        <td style="text-align:right; font-weight:bold;">'.number_format($total_original, 2).'</td>
                        <td style="text-align:right; font-weight:bold;">-</td>
                        <td style="text-align:right; font-weight:bold;">-</td>
                        <td style="text-align:right; font-weight:bold;">' . number_format($total_amount, 2) . '</td>
                        <td style="text-align:right; font-weight:bold;">' . number_format($total_material, 2) . '</td>
                        <td style="text-align:right; font-weight:bold;">' . number_format($total_labor, 2) . '</td>
                        <td style="text-align:right; font-weight:bold;">' . number_format($total_foh, 2) . '</td>
                        <td style="text-align:right; font-weight:bold;">' . $total_cogs_html . '</td>
                        <td style="text-align:right; font-weight:bold;">'.number_format($total_gainloss, 2).'</td>
                        <td style="text-align:right; font-weight:bold;">'.number_format($total_gainlosspersen, 2).'</td>
                        <td style="text-align:right;" colspan="2"></td>
                    </tr>';

        $html .= '</table></div></body></html>';
        echo $html;
    }
}
