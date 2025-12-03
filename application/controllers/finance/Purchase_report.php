<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_report extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/purchase_report');
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
            header("Content-Disposition: attachment; filename=purchase_report_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_display = $this->input->get("filter_display");
        $filter_supplier_id = $this->input->get("filter_supplier_id");
        $filter_category_id = $this->input->get("filter_category_id");

        $division = $this->crud->read('divisions',[],["number"=> $filter_division]);
        $division_num = isset($division->number) && !empty($division->number) ? $division->number : NULL;

        $supplier = $this->crud->read('suppliers',[],["id"=> $filter_supplier_id]);
        $supplier_name = isset($supplier->name) && !empty($supplier->name) ? $supplier->name : 'ALL';

        $item_categories = $this->crud->read('item_categories',[],["id"=> $filter_category_id]);
        $category_name = isset($item_categories->number) && !empty($item_categories->number) ? $item_categories->number : NULL;

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        if($filter_display == 'DETAIL'){

                $query= "SELECT 
                    a.id, 
                    f.name as supplier_name,
                    a.receipt_no,
                    e.number as category_number,
                    a.po_no,
                    a.bc_document,
                    a.bc_date,
                    a.receipt_date,
                    a.item_rm_id,
                    b.number as item_rm_number,
                    b.name as item_rm_name,
                    a.qty_receipt2 as qty_receipt,
                    f.currency,
                    g.uom_default as uom,
                    b.division,
                    d.total_sub,
                    d.total,
                    (d.total / d.total_sub) as disc_portion,
                    (CASE 
                        WHEN COALESCE(d.discount_nominal,0) > 0 
                            THEN COALESCE(d.total,0) / COALESCE(d.qty,0) 
                        ELSE 
                            (COALESCE(d.total,0) 
                            - ((COALESCE(d.total,0) / NULLIF(COALESCE(d.total_sub,0),0)) 
                                * COALESCE(d.discount_total,0)
                            )
                            ) / NULLIF(COALESCE(d.qty,0),0)
                    END) AS price 
                FROM purchase_order_receipts a
                LEFT JOIN item_rm b ON a.item_rm_id = b.id
                LEFT JOIN purchase_orders d ON a.po_no = d.po_no and a.item_rm_id = d.item_rm_id
                LEFT JOIN item_categories e ON b.item_category_id = e.id
                LEFT JOIN suppliers f ON d.supplier_id = f.id
                LEFT JOIN supplier_items g ON d.item_rm_id = g.item_rm_id and d.supplier_id = g.supplier_id
                WHERE a.supplier_id LIKE '%$filter_supplier_id%' and b.division LIKE '%$filter_division%' and 
                DATE_FORMAT(a.receipt_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and b.item_category_id LIKE '%$filter_category_id%'
                GROUP BY a.id  
                ORDER BY f.name ASC, a.receipt_no ASC, b.number DESC";
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
                <h3 style="margin:0;">PURCHASE REPORT - DETAILS</h3>
                </center>
                <div style="float:left; width:50%;">
                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                        <tr>
                            <td width="100">Periode</td>
                            <td width="5">:</td>
                            <td>' . $filter_from . ' to ' . $filter_to . '</td>
                        </tr>';
                if (!empty($division_num)) {
                $html .= '<tr>
                            <td width="100">Division</td>
                            <td width="5">:</td>
                            <td>' . $division_num . '</td>
                        </tr>';
                }
                if (!empty($category_name)) {
                $html .='<tr>
                            <td width="100">Category</td>
                            <td width="5">:</td>
                            <td>' . $category_name . '</td>
                        </tr>';
                }
                $html .= '<tr>
                            <td width="100">Supplier</td>
                            <td width="5">:</td>
                            <td>' . $supplier_name . '</td>
                        </tr>
                    </table>
                </div>
                <table id="customers" border="1" style="font-size: 11px;">
                    <tr>
                        <th width="20">No</th>
                        <th width="150">Receipt No</th>
                        <th width="60">Category</th>
                        <th>Division</th>
                        <th>PO No</th>
                        <th>Document</th>
                        <th>Document Date</th>
                        <th>Supplier Name</th>
                        <th>Part No</th>
                        <th>Part Name</th>
                        <th>Qty</th>
                        <th>Uom</th>
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
                $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($record->receipt_date)));
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

                $amount = ($record->qty_receipt * $record->price);
                $amountIDR = ($amount * $exchange_rate);

                $html .= '  <tr>
                                <td style="text-align:center;">' . $no . '</td>
                                <td>' . $record->receipt_no . '</td>
                                <td style="text-align:center;">' . $record->category_number . '</td>
                                <td style="text-align:center;">' . $record->division . '</td>
                                <td>' . $record->po_no . '</td>
                                <td>' . $record->bc_document . '</td>
                                <td>' . $record->bc_date . '</td>
                                <td>' . $record->supplier_name . '</td>
                                <td style="mso-number-format:\@;">' . $record->item_rm_number . '</td>
                                <td style="mso-number-format:\@;">' . $record->item_rm_name . '</td>
                                <td style="text-align:right">' . $this->numberFormatPrint($record->qty_receipt, $option) . '</td>
                                <td style="text-align:center;">' . $record->uom . '</td>
                                <td style="text-align:center;">' . $record->currency . '</td>
                                <td style="text-align:right;">' . $this->numberFormatPrint($record->price, $option) . '</td>
                                <td style="text-align:right;">' . $this->numberFormatPrint($amount, $option) . '</td>
                                <td style="text-align:center;">' . $exchange_rate . '</td>
                                <td style="text-align:right;">' . $this->numberFormatPrint($amountIDR, $option) . '</td>
                            </tr>';
                $no++;
                $totalAmount += $amount;
                $totalAmountIDR += $amountIDR;
            }

            $html .= '<tr>
                <td colspan="14" style="text-align:right;"><b>GRAND TOTAL</b></td>
                <td style="text-align:right">' . $this->numberFormatPrint($totalAmount, $option) . '</td>
                <td style="text-align:right;">-</td>
                <td style="text-align:right">' . $this->numberFormatPrint($totalAmountIDR, $option) . '</td>
            </tr>';

            $html .= '</table></body></html>';
            echo $html;
        }
        else
        {
            // SUMMARY REPORT 
            $query = "SELECT 
                    a.id, 
                    f.id as supplier_id,
                    f.name as supplier_name,
                    a.receipt_no,
                    e.number as category_number,
                    a.po_no,
                    a.bc_document,
                    a.bc_date,
                    a.receipt_date,
                    a.item_rm_id,
                    b.number as item_rm_number,
                    b.name as item_rm_name,
                    a.qty_receipt2,
                    a.qty_receipt2 as qty,
                    f.currency,
                    g.uom_default as uom,
                    b.division,
                    (CASE 
                        WHEN COALESCE(d.discount_nominal,0) > 0 
                            THEN COALESCE(d.total,0) / COALESCE(d.qty,0) 
                        ELSE 
                            (COALESCE(d.total,0) 
                            - ((COALESCE(d.total,0) / NULLIF(COALESCE(d.total_sub,0),0)) 
                                * COALESCE(d.discount_total,0)
                            )
                            ) / NULLIF(COALESCE(d.qty,0),0)
                    END) AS price 
                FROM purchase_order_receipts a
                LEFT JOIN item_rm b ON a.item_rm_id = b.id
                LEFT JOIN purchase_orders d ON a.po_no = d.po_no and a.item_rm_id = d.item_rm_id
                LEFT JOIN item_categories e ON b.item_category_id = e.id
                LEFT JOIN suppliers f ON d.supplier_id = f.id
                LEFT JOIN supplier_items g ON d.item_rm_id = g.item_rm_id and d.supplier_id = g.supplier_id
                WHERE a.supplier_id LIKE '%$filter_supplier_id%' and b.division LIKE '%$filter_division%' and 
                DATE_FORMAT(a.receipt_date, '%Y-%m-%d') BETWEEN '$filter_from' and '$filter_to' and b.item_category_id LIKE '%$filter_category_id%'
                GROUP BY a.id  
                ORDER BY f.name ASC, a.receipt_no ASC, b.number DESC";
            $records = $this->db->query($query)->result_array();

            // mapping data per supplier_id and division
            $summary_data = $this->mappingDataSummary($records);

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
                <h3 style="margin:0;">PURCHASE REPORT - SUMMARY</h3>
                </center>
                <div style="float:left; width:50%;">
                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                        <tr>
                            <td width="100">Periode</td>
                            <td width="5">:</td>
                            <td>' . $filter_from . ' to ' . $filter_to . '</td>
                        </tr>';
                    if (!empty($division_num)) {
                    $html .= '<tr>
                            <td width="100">Division</td>
                            <td width="5">:</td>
                            <td>' . $division_num . '</td>
                        </tr>';
                    }
                    if (!empty($category_name)) {
                    $html .='<tr>
                            <td width="100">Category</td>
                            <td width="5">:</td>
                            <td>' . $category_name . '</td>
                        </tr>';
                    }
                    $html .= '<tr>
                            <td width="100">Supplier</td>
                            <td width="5">:</td>
                            <td>' . $supplier_name . '</td>
                        </tr>
                    </table>
                </div>
                <table id="customers" border="1" style="font-size: 11px;">
                    <thead>
                        <tr>
                            <th width="20" rowspan="2">No</th>
                            <th width="200" rowspan="2">Supplier Name</th>
                            <th width="200" colspan="3">DIVISION</th>
                            <th width="100" rowspan="2">TOTAL</th>
                        </tr>
                        <tr>
                            <th width="100">INJ</th>
                            <th width="100">MTS</th>
                            <th width="100">ADM</th>
                        </tr>
                    </thead>
                    <tbody>';
                    
            $no = 1;
            $grand_total_inj = 0;
            $grand_total_mts = 0;
            $grand_total_adm = 0;
            $grand_overall_total = 0;

            foreach ($summary_data as $supplier_group) {
                $html .= '<tr>';
                $html .= '<td style="text-align: center;">'. $no++ .'</td>';
                $html .= '<td>'. htmlspecialchars($supplier_group['supplier_name']) .'</td>';
                $html .= '<td style="text-align: right;">' . $this->numberFormatPrint($supplier_group['divisions']['INJ'], $option) . '</td>';
                $html .= '<td style="text-align: right;">' . $this->numberFormatPrint($supplier_group['divisions']['MTS'], $option) . '</td>';
                $html .= '<td style="text-align: right;">' . $this->numberFormatPrint($supplier_group['divisions']['ADM'], $option) . '</td>';
                $html .= '<td style="text-align: right;">' . $this->numberFormatPrint($supplier_group['total_per_supplier_overall'], $option) . '</td>';
                $html .= '</tr>';

                // Akumulasi grand totals
                $grand_total_inj += $supplier_group['divisions']['INJ'];
                $grand_total_mts += $supplier_group['divisions']['MTS'];
                $grand_total_adm += $supplier_group['divisions']['ADM'];
                $grand_overall_total += $supplier_group['total_per_supplier_overall'];
            }

            $html .= '</tbody>';
            $html .= '<tfoot>';
            $html .= '<tr style="background-color:#EBEBEB;">';
            $html .= '<th colspan="2" style="text-align: right;">GRAND TOTAL</th>';
            $html .= '<th style="text-align: right;">' . $this->numberFormatPrint($grand_total_inj, $option) . '</th>';
            $html .= '<th style="text-align: right;">' . $this->numberFormatPrint($grand_total_mts, $option) . '</th>';
            $html .= '<th style="text-align: right;">' . $this->numberFormatPrint($grand_total_adm, $option) . '</th>';
            $html .= '<th style="text-align: right;">' . $this->numberFormatPrint($grand_overall_total, $option) . '</th>';
            $html .= '</tr>';
            $html .= '</tfoot>';
            $html .= '</table>';
            $html .= '</body></html>';

            echo $html;
        }
    }
        
    function mappingDataSummary($records) 
    {
        $grouped_data = [];
    
        foreach ($records as $record) {
            $supplier_id = $record['supplier_id'];
            $supplier_name = $record['supplier_name'];
            $division = $record['division'];
    
            if (!isset($grouped_data[$supplier_id])) {
                $grouped_data[$supplier_id] = [
                    'supplier_id' => $supplier_id,
                    'supplier_name' => $supplier_name,
                    'divisions' => [
                        'INJ' => 0,
                        'MTS' => 0,
                        'ADM' => 0,
                    ],
                    'total_per_supplier_overall' => 0
                ];
            }
    
            $currency = $record['currency'];
            $monthBf = date('Y-m-01', strtotime('-1 month', strtotime($record['receipt_date'])));
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
    
            // Masukkan ke total divisi yang sesuai
            if (isset($grouped_data[$supplier_id]['divisions'][$division])) {
                $grouped_data[$supplier_id]['divisions'][$division] += $item_total_price;
            } else {
                $grouped_data[$supplier_id]['divisions'][$division] = $item_total_price;
            }
    
            $grouped_data[$supplier_id]['total_per_supplier_overall'] += $item_total_price;
        }

        return array_values($grouped_data);
    }

    function numberFormatPrint($nominal, $option = "") 
    {
        if ($option == "excel") {
            $formatted = number_format($nominal, 2, ",", "");
        } else {
            $formatted = number_format($nominal, 2, ",", ".");        
        }
        return $formatted;
    }

}
