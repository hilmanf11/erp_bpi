<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Summary_forecast_vs_sales extends CI_Controller
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
            $this->load->view('planning/summary_forecast_vs_sales');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=history_transactions_fg_$format.xls");
        }
        $filter_month = $this->input->get('filter_month');
        $filter_year   = $this->input->get('filter_year');
        $filter_customer_id = $this->input->get('filter_customer_id');

        $customer = $this->crud->read('customers',[],["id"=> $filter_customer_id]);
        $customer_name = isset($customer->name) && !empty($customer->name) ? $customer->name : '-';

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $query_main = "SELECT 
            a.id, 
            a.number, 
            a.number_customer, 
            a.name, 
            a.uom,
            b.name as customer_name,
            COALESCE(f.month_1, 0) AS month1,
            COALESCE(f.month_2, 0) AS month2,
            COALESCE(f.month_3, 0) AS month3,
            COALESCE(f.month_4, 0) AS month4,
            COALESCE(f.month_5, 0) AS month5,
            COALESCE(f.month_6, 0) AS month6,
            COALESCE(f.month_7, 0) AS month7,
            COALESCE(f.month_8, 0) AS month8,
            COALESCE(f.month_9, 0) AS month9,
            COALESCE(f.month_10, 0) AS month10,
            COALESCE(f.month_11, 0) AS month11,
            COALESCE(f.month_12, 0) AS month12,
            
            CASE 
                WHEN f.month_12 > 0 THEN f.month_12
                WHEN f.month_11 > 0 THEN f.month_11
                WHEN f.month_10 > 0 THEN f.month_10
                WHEN f.month_9 > 0 THEN f.month_9
                WHEN f.month_8 > 0 THEN f.month_8
                WHEN f.month_7 > 0 THEN f.month_7
                WHEN f.month_6 > 0 THEN f.month_6
                WHEN f.month_5 > 0 THEN f.month_5
                WHEN f.month_4 > 0 THEN f.month_4
                WHEN f.month_3 > 0 THEN f.month_3
                WHEN f.month_2 > 0 THEN f.month_2
                WHEN f.month_1 > 0 THEN f.month_1
                ELSE 0 
            END AS last_qty_forecast,
            
            COALESCE(dn.total_qty, 0) AS total_qty_delivery_notes

        FROM item_fg a
        LEFT JOIN forecasts f ON a.id = f.item_fg_id 
        LEFT JOIN customers b ON f.customer_id = b.id

        LEFT JOIN (
            SELECT item_fg_id, customer_id, SUM(qty) AS total_qty
            FROM delivery_notes
            WHERE delivery_note_date LIKE CONCAT('$filter_year', '-', '$filter_month', '%')
            GROUP BY item_fg_id, customer_id
        ) dn ON a.id = dn.item_fg_id and f.customer_id = dn.customer_id

        WHERE f.customer_id LIKE '%$filter_customer_id' 
        AND f.p_month LIKE '%$filter_month' 
        AND f.p_year LIKE '%$filter_year'

        ORDER BY a.id";

        $records = $this->crud->query($query_main);

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
            <h3 style="margin:0;">SUMMARY FORECAST VS SALES - IN QTY</h3>
        </center>
        <br>
        <div style="float:left; width:50%;">
            <table style="width:100%; font-size:12px; margin-bottom:10px;">
                <tr>
                    <td width="100">Period</td>
                    <td width="5">:</td>
                    <td>' . $filter_month . '-' . $filter_year . '</td>
                </tr>
                <tr>
                    <td width="100">Customer Name</td>
                    <td width="5">:</td>
                    <td>' . $customer_name . '</td>
                </tr>
                <tr>
                    <td width="100">Product No</td>
                    <td width="5">:</td>
                    <td>ALL</td>
                </tr>
            </table>
        </div>
        <table id="customers" border="1" style="font-size: 11px;">
            <tr>
                <th width="20">No</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Product Customer</th>
                <th>Uom</th>
                <th width="100">FC</th>
                <th width="100">SALES</th>
                <th width="100">GAP</th>
                <th width="100">Percentage</th>
            </tr>';
        $no = 1;
        foreach ($records as $record) {
            $item_fg_id = $record->id;

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $record->number . '</td>
                            <td style="mso-number-format:\@;">' . $record->name . '</td>
                            <td style="mso-number-format:\@;">' . $record->number_customer . '</td>
                            <td>' . $record->uom . '</td>
                            <td style="text-align:right;">' . number_format(@$record->month1, 2) . '</td>
                            <td style="text-align:right;">' . number_format(@$record->total_qty_delivery_notes, 2) . '</td>
                            <td style="text-align:right;">' . number_format(@$record->total_qty_delivery_notes - $record->month1 , 2) . '</td>
                            <td style="text-align:right;">' . 
                                ( @$record->month1 != 0 
                                    ? round((@$record->total_qty_delivery_notes / @$record->month1) * 100) . '%'
                                    : '0%' 
                                ) . 
                            '</td> 
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
