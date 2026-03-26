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
 * @property Convertcurrency $convertcurrency
 */
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

    public function readsDN()
    {
        $q = isset($_POST['q']) ? $_POST['q'] : '';
        $this->db->select('delivery_note_no');
        $this->db->from('delivery_notes');
        $this->db->where('created_date >=', '2025-01-01 00:00:00');
        $this->db->where('created_date <=', '2026-12-31 23:59:59');
        $this->db->group_by('delivery_note_no');
        $this->db->order_by('created_date','ASC');
        if ($q !== '') {
            $this->db->like('delivery_note_no', $q);
        }
        // Optional: batasi hasil (boleh diaktifkan)
        // $this->db->limit(20);
        $result = $this->db->get()->result_array();
        echo json_encode($result);
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
        $filter_item_fg_id = $this->input->get("filter_item_fg_id");
        $filter_delivery_notes = $this->input->get("filter_delivery_notes");

        $division = $this->crud->read('divisions',[],["number"=> $filter_division]);
        $division_num = isset($division->number) && !empty($division->number) ? $division->number : '-';

        $customer = $this->crud->read('customers',[],["id"=> $filter_customer_id]);
        $customer_name = isset($customer->name) && !empty($customer->name) ? $customer->name : 'ALL';

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        if($filter_display == 'DETAIL')
        {
            $query= "SELECT
                a.id,
                c.name AS customer_name,
                (CASE WHEN a.sales_order_no_rm IS NOT NULL THEN 'RM / SUBCONT' ELSE a.division END) AS division,
                a.delivery_note_no,
                a.delivery_note_date,
                a.item_fg_id,
                b.number AS item_fg_number,
                b.name AS item_fg_name,
                s.number AS sales_invoice_no,
                COALESCE(a.sales_order_no, a.sales_order_no_rm) AS sales_order_no,
                a.customer_order_no,
                a.uom,
                a.qty,
                (CASE
                    WHEN a.sales_order_no_rm IS NOT NULL THEN e.currency
                    WHEN a.sales_order_no IS NOT NULL THEN d.currency
                    ELSE NULL 
                END) AS currency, 
                (CASE
                    WHEN a.sales_order_no_rm IS NOT NULL THEN e.price
                    WHEN a.sales_order_no IS NOT NULL THEN d.price
                    ELSE NULL 
                END) AS price 
                FROM delivery_notes a
                LEFT JOIN item_fg b ON a.item_fg_id = b.id
                LEFT JOIN customers c ON a.customer_id = c.id
                LEFT JOIN sales_orders d ON a.sales_order_no = d.sales_order_no and a.item_fg_id = d.item_fg_id
                LEFT JOIN sales_order_rm e ON a.sales_order_no_rm = e.sales_order_no and a.item_fg_id = e.item_fg_id
                LEFT JOIN sales_invoices s ON s.delivery_note_no = a.delivery_note_no and s.item_fg_id = a.item_fg_id
                WHERE a.customer_id LIKE '%$filter_customer_id%' and a.division LIKE '%$filter_division%' and a.item_fg_id LIKE '%$filter_item_fg_id%' 
                and a.delivery_note_no LIKE '%$filter_delivery_notes%' and DATE_FORMAT(a.delivery_note_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' AND a.trans_type = 'SALES'
                GROUP BY a.id  
                ORDER BY c.name ASC, a.delivery_note_no ASC, b.number ASC";
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
                        <th width="250">Customer Name</th>
                        <th>Division</th>
                        <th>Sales Invoice No.</th>
                        <th width="150">Delivery Note No</th>
                        <th width="100">Delivery Note Date</th>
                        <th witth="150">Product ID</th>
                        <th width="150">Product No</th>
                        <th width="150">Product Name</th>
                        <th>Sales Order No</th>
                        <th width="150">Customer Order No</th>
                        <th>Uom</th>
                        <th>Qty</th>
                        <th>Currency</th>
                        <th>Price</th>
                        <th width="85">Amount</th>
                        <th>Exchange Rate</th>
                        <th width="85">Amount (IDR)</th>
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
                                <td style="text-align:center">' . $record->division . '</td>
                                <td>' . $record->sales_invoice_no . '</td>
                                <td>' . $record->delivery_note_no . '</td>
                                <td>' . $record->delivery_note_date . '</td>
                                <td>' . $record->item_fg_id . '</td>
                                <td style="mso-number-format:\@;">' . $record->item_fg_number . '</td>
                                <td style="mso-number-format:\@;">' . $record->item_fg_name . '</td>
                                <td>' . $record->sales_order_no . '</td>
                                <td>' . $record->customer_order_no . '</td>
                                <td>' . $record->uom . '</td>
                                <td style="text-align:right;">' . number_format($record->qty, 2, '.', ',') . '</td>
                                <td style="text-align:center;">' . $record->currency . '</td>
                                <td style="text-align:right;">' . number_format($record->price, 2, '.', ',') . '</td>
                                <td style="text-align:right;">' . number_format($amount, 2, '.', ',') . '</td>
                                <td style="text-align:center;">' . $exchange_rate . '</td>
                                <td style="text-align:right;">' . number_format($amountIDR, 2, '.', ',') . '</td>
                            </tr>';
                $no++;
                $totalAmount += $amount;
                $totalAmountIDR += $amountIDR;
            }

            $html .= '<tr style="background-color:#EBEBEB;">
                <td colspan="14" style="text-align:right;"><b>GRAND TOTAL</b></td>
                <td style="text-align:right">' . number_format($totalAmount, 2, '.', ',') . '</td>
                <td style="text-align:right;">-</td>
                <td style="text-align:right">' . number_format($totalAmountIDR, 2, '.', ',') . '</td>
            </tr>';

            $html .= '</table></body></html>';
            echo $html;
        }
        else
        {
            // SUMMARY REPORT
            $query = "SELECT
                a.id,
                c.name AS customer_name,
                c.id AS customer_id,
                (CASE WHEN a.sales_order_no_rm IS NOT NULL THEN 'RM / SUBCONT' ELSE a.division END) AS division,
                a.delivery_note_no,
                a.delivery_note_date,
                a.item_fg_id,
                b.number AS item_fg_number,
                b.name AS item_fg_name,
                COALESCE(a.sales_order_no, a.sales_order_no_rm) AS sales_order_no,
                a.customer_order_no,
                a.uom,
                a.qty,
                (CASE
                    WHEN a.sales_order_no_rm IS NOT NULL THEN e.currency
                    WHEN a.sales_order_no IS NOT NULL THEN d.currency
                    ELSE NULL 
                END) AS currency, 
                (CASE
                    WHEN a.sales_order_no_rm IS NOT NULL THEN e.price
                    WHEN a.sales_order_no IS NOT NULL THEN d.price
                    ELSE NULL 
                END) AS price 
                FROM delivery_notes a
                LEFT JOIN item_fg b ON a.item_fg_id = b.id
                LEFT JOIN customers c ON a.customer_id = c.id
                LEFT JOIN sales_orders d ON a.sales_order_no = d.sales_order_no and a.item_fg_id = d.item_fg_id
                LEFT JOIN sales_order_rm e ON a.sales_order_no_rm = e.sales_order_no and a.item_fg_id = e.item_fg_id
                WHERE a.customer_id LIKE '%$filter_customer_id%' and a.division LIKE '%$filter_division%' and 
                DATE_FORMAT(a.delivery_note_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' AND a.trans_type = 'SALES'
                GROUP BY a.id  
                ORDER BY c.name ASC, a.delivery_note_no ASC, b.number ASC";
            $records = $this->db->query($query)->result_array();

            // mapping data per customer_id and division
            $summary_data = $this->mappingDataSummary($records);

            $customer_display_name = $this->getCustomerName($filter_customer_id);
            $division_display_name = ($filter_division === '') ? 'ALL' : $filter_division;

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
                    Print By ' . ($this->session->username ?? 'Admin') . '
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
                            <td>' . $division_display_name . '</td>
                        </tr>
                        <tr>
                            <td width="100">Customer</td>
                            <td width="5">:</td>
                            <td>' . $customer_display_name . '</td>
                        </tr>
                    </table>
                </div>
                <table id="customers" border="1" style="font-size: 11px;">
                    <thead>
                        <tr>
                            <th width="20" rowspan="2">No</th>
                            <th width="200" rowspan="2">Customer Name</th>
                            <th width="200" colspan="4">SEGMENT</th>
                            <th width="100" rowspan="2">TOTAL</th>
                        </tr>
                        <tr>
                            <th width="100">RM / SUBCONT</th>
                            <th width="100">INJ</th>
                            <th width="100">MTS</th>
                            <th width="100">ADM</th>
                        </tr>
                    </thead>
                    <tbody>';

            $no = 1;
            $grand_total_rm = 0;
            $grand_total_inj = 0;
            $grand_total_mts = 0;
            $grand_total_adm = 0;
            $grand_overall_total = 0;

            foreach ($summary_data as $customer_group) {
                $html .= '<tr>';
                $html .= '<td style="text-align: center;">' . $no++ . '</td>';
                $html .= '<td>' . htmlspecialchars($customer_group['customer_name']) . '</td>';
                $html .= '<td style="text-align: right;">' . number_format($customer_group['divisions']['RM / SUBCONT'], 2, ".", ",") . '</td>';
                $html .= '<td style="text-align: right;">' . number_format($customer_group['divisions']['INJ'], 2, ".", ",") . '</td>';
                $html .= '<td style="text-align: right;">' . number_format($customer_group['divisions']['MTS'], 2, ".", ",") . '</td>';
                $html .= '<td style="text-align: right;">' . number_format($customer_group['divisions']['ADM'], 2, ".", ",") . '</td>';
                $html .= '<td style="text-align: right;">' . number_format($customer_group['total_per_customer_overall'], 2, ".", ",") . '</td>';
                $html .= '</tr>';

                // Akumulasi grand totals
                $grand_total_rm += $customer_group['divisions']['RM / SUBCONT'];
                $grand_total_inj += $customer_group['divisions']['INJ'];
                $grand_total_mts += $customer_group['divisions']['MTS'];
                $grand_total_adm += $customer_group['divisions']['ADM'];
                $grand_overall_total += $customer_group['total_per_customer_overall'];
            }

            $html .= '</tbody>';
            $html .= '<tfoot>';
            $html .= '<tr style="background-color:#EBEBEB;">';
            $html .= '<th colspan="2" style="text-align: right;">GRAND TOTAL</th>';
            $html .= '<th style="text-align: right;">' . number_format($grand_total_rm, 2, ".", ",") . '</th>';
            $html .= '<th style="text-align: right;">' . number_format($grand_total_inj, 2, ".", ",") . '</th>';
            $html .= '<th style="text-align: right;">' . number_format($grand_total_mts, 2, ".", ",") . '</th>';
            $html .= '<th style="text-align: right;">' . number_format($grand_total_adm, 2, ".", ",") . '</th>';
            $html .= '<th style="text-align: right;">' . number_format($grand_overall_total, 2, ".", ",") . '</th>';
            $html .= '</tr>';
            $html .= '</tfoot>';
            $html .= '</table>';
            $html .= '</body></html>';

            echo $html;
        }
    }

    private function mappingDataSummary($records)
    {
        $grouped_data = [];

        foreach ($records as $record) 
        {
            $customer_id = $record['customer_id'];
            $customer_name = $record['customer_name'];
            $division = $record['division'];
            
            if (!isset($grouped_data[$customer_id])) {
                $grouped_data[$customer_id] = [
                    'customer_id' => $customer_id,
                    'customer_name' => $customer_name,
                    'divisions' => [
                        'RM / SUBCONT' => 0,
                        'INJ'          => 0,
                        'MTS'          => 0,
                        'ADM'          => 0,
                    ],
                    'total_per_customer_overall' => 0 
                ];
            }
            $currency = $record['currency'];
            $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($record['delivery_note_date'])));
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

            $item_total_price = $record['qty'] * $record['price'] * $exchange_rate;

            // Tambahkan ke total divisi yang sesuai
            if (isset($grouped_data[$customer_id]['divisions'][$division])) {
                $grouped_data[$customer_id]['divisions'][$division] += $item_total_price;
            } else {
                $grouped_data[$customer_id]['divisions'][$division] = $item_total_price;
            }

            $grouped_data[$customer_id]['total_per_customer_overall'] += $item_total_price;
        }

        return array_values($grouped_data);
    }

    public function getCustomerName($customer_id)
    {
        if (empty($customer_id)) {
            return 'ALL';
        }
        $query = $this->db->get_where('customers', ['id' => $customer_id]);
        if ($query->num_rows() > 0) {
            return $query->row()->name;
        }
        return 'UNKNOWN CUSTOMER';
    }

}
