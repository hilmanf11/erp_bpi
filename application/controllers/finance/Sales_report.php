<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Sales_report extends CI_Controller
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
        $this->form_validation->set_rules('po_no', 'PO No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/sales_report');
        } else {
            redirect('error_access');
        }
    }

    public function readsDivision()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('divisions', ["name" => $post]);
        echo json_encode($send);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_report_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_display = $this->input->get("filter_display");
        $filter_customer_id = $this->input->get("filter_customer_id");

        $division = $this->crud->read('divisions',[],["number"=> $filter_division]);
        $division_num = isset($division->number) && !empty($division->number) ? $division->number : '-';

        $customer = $this->crud->read('customers',[],["id"=> $filter_customer_id]);
        $customer_name = isset($customer->name) && !empty($customer->name) ? $customer->name : 'ALL';

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        if($filter_display == 'DETAIL'){

                $query= "SELECT 
                    a.id, 
                    c.name as customer_name,
                    a.delivery_note_no,
                    a.delivery_note_date,
                    a.item_fg_id,
                    b.number as item_fg_number,
                    b.name as item_fg_name,
                    COALESCE(a.sales_order_no,a.sales_order_no_rm) as sales_order_no,
                    a.customer_order_no,
                    a.uom,
                    a.qty,
                    COALESCE(d.currency,e.currency) as currency,
                    COALESCE(d.price,e.price) as price
                FROM delivery_notes a
                LEFT JOIN item_fg b ON a.item_fg_id = b.id
                LEFT JOIN customers c ON a.customer_id = c.id
                LEFT JOIN sales_orders d ON a.sales_order_no = d.sales_order_no and a.item_fg_id = d.item_fg_id
                LEFT JOIN sales_order_rm e ON a.sales_order_no_rm = e.sales_order_no and a.item_fg_id = e.item_fg_id
                WHERE a.customer_id LIKE '%$filter_customer_id%' and a.division LIKE '%$filter_division%' and 
                DATE_FORMAT(a.delivery_note_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' AND a.trans_type = 'SALES'
                GROUP BY a.id  
                ORDER BY a.delivery_note_no ASC, b.number ASC, c.name ASC";
            $records = $this->crud->query($query);

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
                                <small>'.$config->description.'</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <h3 style="margin:0;">SALES REPORT - DETAILS</h3>
                </center>
                <div style="float:left; width:50%;">
                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                        <tr>
                            <td width="100">Periode</td>
                            <td width="5">:</td>
                            <td>' . $filter_from . ' to ' . $filter_to . '</td>
                        </tr>
                        <tr>
                            <td width="100">Division</td>
                            <td width="5">:</td>
                            <td>' . $division_num . '</td>
                        </tr>
                        <tr>
                            <td width="100">Customer</td>
                            <td width="5">:</td>
                            <td>' . $customer_name . '</td>
                        </tr>
                    </table>
                </div>
                <table id="customers" border="1" style="font-size: 11px;">
                    <tr>
                        <th width="20">No</th>
                        <th width="200">Customer Name</th>
                        <th width="150">Delivery Note No</th>
                        <th width="100">Delivery Note Date</th>
                        <th>Product ID</th>
                        <th width="120">Product No</th>
                        <th>Product Name</th>
                        <th>Sales Order No</th>
                        <th>Customer Order No</th>
                        <th>Uom</th>
                        <th>Qty</th>
                        <th>Currency</th>
                        <th>Price</th>
                        <th>Amount</th>
                        <th>Exchange Rate</th>
                        <th>Amount (IDR)</th>
                    </tr>';
            $no = 1;
            $totalAmount = 0;
            $totalAmountIDR = 0;
            foreach ($records as $record) {
                $currency = $record->currency;
                $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($record->delivery_note_date)));
                $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

                if ($currency != "IDR") {
                    if ($exchange) {
                        $exchange_rate = $exchange->middle;
                    } else {
                        $exchange_rate = 0;
                    }
                } else {
                    $exchange_rate = 1;
                }

                $amount = ($record->qty * $record->price);
                $amountIDR = ($amount * $exchange_rate);

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record->customer_name . '</td>
                                <td>' . $record->delivery_note_no . '</td>
                                <td>' . $record->delivery_note_date . '</td>
                                <td>' . $record->item_fg_id . '</td>
                                <td style="mso-number-format:\@;">' . $record->item_fg_number . '</td>
                                <td style="mso-number-format:\@;">' . $record->item_fg_name . '</td>
                                <td>' . $record->sales_order_no . '</td>
                                <td>' . $record->customer_order_no . '</td>
                                <td>' . $record->uom . '</td>
                                <td style="text-align:right">' . number_format($record->qty, 2, ',', '.') . '</td>
                                <td>' . $record->currency . '</td>
                                <td style="text-align:right">' . number_format($record->price, 2, ',', '.') . '</td>
                                <td style="text-align:right">' . number_format($amount, 2, ',', '.') . '</td>
                                <td style="text-align:right">' . $exchange_rate . '</td>
                                <td style="text-align:right">' . number_format($amountIDR, 2, ',', '.') . '</td>
                            </tr>';
                $no++;
                $totalAmount += $amount;
                $totalAmountIDR += $amountIDR;
            }

            $html .= '<tr>
                <td colspan="13" style="text-align:right;"><b>GRAND TOTAL</b></td>
                <td style="text-align:right">' . number_format($totalAmount, 2, ',', '.') . '</td>
                <td style="text-align:right;">-</td>
                <td style="text-align:right">' . number_format($totalAmountIDR, 2, ',', '.') . '</td>
            </tr>';

            $html .= '</table></body></html>';
            echo $html;
        }else{
                    $query= "SELECT 
                    a.customer_id,
                    c.name AS customer_name,
                    a.delivery_note_date,
                    SUM(a.qty) AS total_qty,
                    SUM(COALESCE(d.price, e.price) * a.qty) AS amount,
                    COALESCE(d.currency, e.currency) AS currency
                FROM delivery_notes a
                LEFT JOIN item_fg b ON a.item_fg_id = b.id
                LEFT JOIN customers c ON a.customer_id = c.id
                LEFT JOIN sales_orders d ON a.sales_order_no = d.sales_order_no AND a.item_fg_id = d.item_fg_id
                LEFT JOIN sales_order_rm e ON a.sales_order_no_rm = e.sales_order_no AND a.item_fg_id = e.item_fg_id
                WHERE a.customer_id LIKE '%$filter_customer_id%' and a.division LIKE '%$filter_division%' and 
                DATE_FORMAT(a.delivery_note_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' AND a.trans_type = 'SALES'
                GROUP BY a.customer_id 
                ORDER BY b.name ASC";
            $records = $this->crud->query($query);

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
                                <small>'.$config->description.'</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br><br>
                <h3 style="margin:0;">SALES REPORT - SUMMARY</h3>
                </center>
                <div style="float:left; width:50%;">
                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                        <tr>
                            <td width="100">Periode</td>
                            <td width="5">:</td>
                            <td>' . $filter_from . ' to ' . $filter_to . '</td>
                        </tr>
                        <tr>
                            <td width="100">Division</td>
                            <td width="5">:</td>
                            <td>' . $division_num . '</td>
                        </tr>
                        <tr>
                            <td width="100">Customer</td>
                            <td width="5">:</td>
                            <td>' . $customer_name . '</td>
                        </tr>
                    </table>
                </div>
                <table id="customers" border="1" style="font-size: 11px;">
                    <tr>
                        <th width="20">No</th>
                        <th width="200">Customer Name</th>
                        <th width="100">Amount (IDR)</th>
                    </tr>';
            $no = 1;
            $totalAmount = 0;
            $totalAmountIDR = 0;
            foreach ($records as $record) {
                $currency = $record->currency;
                $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($record->delivery_note_date)));
                $exchange = $this->crud->read('exchange_rates', [], ["start_date" => $monthBf, "currency_from" => $currency, "currency_to" => "IDR"]);

                if ($currency != "IDR") {
                    if ($exchange) {
                        $exchange_rate = $exchange->middle;
                    } else {
                        $exchange_rate = 0;
                    }
                } else {
                    $exchange_rate = 1;
                }

                $amountIDR = ($record->amount * $exchange_rate);

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record->customer_name . '</td>
                                <td style="text-align:right">' . number_format($amountIDR, 2, ',', '.') . '</td>
                            </tr>';
                $no++;
                $totalAmountIDR += $amountIDR;
            }

            $html .= '<tr>
                <td colspan="2" style="text-align:right;"><b>GRAND TOTAL</b></td>
                
                <td style="text-align:right">' . number_format($totalAmountIDR, 2, ',', '.') . '</td>
            </tr>';

            $html .= '</table></body></html>';
            echo $html;
        }
    }
}
