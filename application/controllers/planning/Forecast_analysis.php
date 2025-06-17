<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Forecast_analysis extends CI_Controller
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
        $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[50]');
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/forecast_analysis');
        } else {
            redirect('error_access');
        }
    }

    private function format_number($input)
    {
        // Remove commas from the input
        $numeric_value = str_replace(',', '', $input);

        // Format the number to '15.000'
        return number_format($numeric_value, 0, '.', '.');
    }

    //GET PERIOD
    public function readPeriod($select)
    {
        if ($select == "month") {
            $month = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
            foreach ($month as $key => $value) {
                $months[] = array("id" => $key, "name" => $value);
            }

            echo json_encode($months);
        } else if ($select == "year") {
            $year_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
            $year_now = date('Y', strtotime('+1 year', strtotime(date('Y'))));
            for ($i = $year_now; $i >= $year_before; $i--) {
                $years[] = array("id" => $i, "name" => $i);
            }

            echo json_encode($years);
        } else {
            show_error("Cannot Process your request");
        }
    }

    //GET PERIOD LISTS
    public function readPeriodLists()
    {
        $p_month = $this->input->post('p_month');
        $p_year = $this->input->post('p_year');
        $p_date_start = date("Y-m-d", strtotime($p_year . "-" . $p_month . "-01"));
        $p_date_to = date('Y-m-d', strtotime('+11 month', strtotime($p_date_start)));

        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
        }

        echo json_encode($dates);
    }

    public function readsProductFamily() {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT * FROM item_familys WHERE id like '%$post%' or `number` like '%$post%' or `name` like '%$post%'");
        echo json_encode($send);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=forecast_analysis_$format.xls");
        }

        $filter_period_year = base64_decode($this->input->get("filter_period_year"));
        $filter_period_month = base64_decode($this->input->get("filter_period_month"));
        $filter_customer_name = $this->input->get("filter_customer_name");
        $filter_item_fg = $this->input->get("filter_item_fg");
        $filter_product_family = base64_decode($this->input->get("filter_product_family"));

        $p_date_start = date("Y-m-d", strtotime($filter_period_year . "-" . $filter_period_month . "-01"));
        $p_date_to = date('Y-m-d', strtotime('+11 month', strtotime($p_date_start)));
        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
        }

        $selected_month_int = (int)$filter_period_month;

        $columns = [];
        for ($i = 0; $i < 3; $i++) {
            $month_index = ($selected_month_int + $i - 1) % 12 + 1;
            $columns[] = $month_index;
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('MAX(a.revision) as version, a.p_month, a.p_year, a.customer_id, a.item_fg_id, 
            a.month_1 as qty_month_1, a.month_2 as qty_month_2, a.month_3 as qty_month_3, 
            b.id as item_fg_id, b.number as item_fg_number, b.name as item_fg_name,
            c.name as customer_name, d.currency, d.price');
        $this->db->select("(SELECT middle FROM exchange_rates 
            WHERE currency_to='IDR' 
            AND DATE_FORMAT(start_date, '%m') = " . $filter_period_month . " 
            AND DATE_FORMAT(start_date, '%Y') = " . $filter_period_year . ") AS rate", FALSE);
        
        $this->db->from('forecasts a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
        $this->db->join('customers c', 'a.customer_id = c.id', 'left');
        $this->db->join('customer_items d', 'a.customer_id = d.customer_id and a.item_fg_id = d.item_fg_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.p_month', $filter_period_month);
        $this->db->where('a.p_year', $filter_period_year);

        if ($filter_customer_name != '') {
            $this->db->where('a.customer_id', $filter_customer_name);
        }
        if ($filter_item_fg != '') {
            $this->db->where('a.item_fg_id', $filter_item_fg);
        }
        if ($filter_product_family != "") {
            $this->db->where('b.type', $filter_product_family);
        }
        $this->db->group_by('a.customer_id');
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.p_month');
        $this->db->group_by('a.p_year');
        $this->db->order_by('c.name', 'ASC');
        $records = $this->db->get()->result_array();
        
        $customer_name = 'ALL';
        $part_name = 'ALL';
        if ($filter_customer_name != '') {
            $customer = $this->crud->read('customers', [], ["id" => $filter_customer_name]);
            if (!empty($customer->name)) {
                $customer_name = $customer->name;
            }
        }
        if ($filter_item_fg != '') {
            $part_name = $filter_item_fg;
        }
        $long_months = array('01' => 'JANUARY', '02' => 'FEBRUARY', '03' => 'MARCH', '04' => 'APRIL', '05' => 'MAY', '06' => 'JUNE', '07' => 'JULY', '08' => 'AUGUST', '09' => 'SEPTEMBER', '10' => 'OCTOBER', '11' => 'NOVEMBER', '12' => 'DECEMBER');
        $months = array('1' => 'Jan', '2' => 'Feb', '3' => 'Mar', '4' => 'Apr', '5' => 'May', '6' => 'Jun', '7' => 'Jul', '8' => 'Aug', '9' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#forecast_analysis {border-collapse: collapse;width: 100%;font-size: 12px;}#forecast_analysis td, #forecast_analysis th {border: 1px solid #ddd;padding: 2px;}#forecast_analysis tr:nth-child(even){background-color: #f2f2f2;}#forecast_analysis tr:hover {background-color: #ddd;}#forecast_analysis th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:m:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
                <br><br>
                <div style="float: centet; font-size: 16px; text-align: center;">
                    <h3>FORECAST ANALYSIS</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PERIOD</small><br>
                                <small>CUSTOMER NAME</small><br>
                                <small>PRODUCT NO.</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small><br>
                                <small>: </small><br>
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $long_months[$filter_period_month] . '</b>  <b>' . $filter_period_year . '</b></small><br>
                                <small><b>' . $customer_name . '</b></small><br>
                                <small><b>' . $part_name . '</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            <br><br>
            <table id="forecast_analysis" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2" style="text-align:center;">Customer</th>
                <th rowspan="2" style="text-align:center;">Part No.</th>
                <th rowspan="2" style="text-align:center;">Part Name</th>
                <th colspan="3" style="text-align:center;">QTY</th>
                <th colspan="3" style="text-align:center;">AMOUNT</th>
            </tr>
            <tr>
                <th style="text-align:center;">' . $months[$columns[0]] . '</th>
                <th style="text-align:center;">' . $months[$columns[1]] . '</th>
                <th style="text-align:center;">' . $months[$columns[2]] . '</th>
                <th style="text-align:center;">' . $months[$columns[0]] . '</th>
                <th style="text-align:center;">' . $months[$columns[1]] . '</th>
                <th style="text-align:center;">' . $months[$columns[2]] . '</th>
            </tr>';

        $no = 1;
        $current_customer = '';
        $total_qty_1 = 0;
        $total_qty_2 = 0;
        $total_qty_3 = 0;
        $total_amount_1 = 0;
        $total_amount_2 = 0;
        $total_amount_3 = 0;

        $grand_total_qty_1 = 0;
        $grand_total_qty_2 = 0;
        $grand_total_qty_3 = 0;
        $grand_total_amount_1 = 0;
        $grand_total_amount_2 = 0;
        $grand_total_amount_3 = 0;

        foreach ($records as $data) 
        {
            if ($current_customer !== $data['customer_name']) {
                if ($current_customer !== '') {
                    $html .= "<tr style='background-color:#FFFF00;'>
                                <td style='text-align:center; font-weight:bold;' colspan='4'>Total</td>
                                <td style='text-align:right; font-weight:bold;'>{$this->format_number($total_qty_1)}</td>
                                <td style='text-align:right; font-weight:bold;'>{$this->format_number($total_qty_2)}</td>
                                <td style='text-align:right; font-weight:bold;'>{$this->format_number($total_qty_3)}</td>
                                <td style='text-align:right; font-weight:bold;'>{$this->format_number($total_amount_1)}</td>
                                <td style='text-align:right; font-weight:bold;'>{$this->format_number($total_amount_2)}</td>
                                <td style='text-align:right; font-weight:bold;'>{$this->format_number($total_amount_3)}</td>
                              </tr>";
                }
                // Reset for the new group
                $current_customer = $data['customer_name'];
                $total_qty_1 = 0;
                $total_qty_2 = 0;
                $total_qty_3 = 0;
                $total_amount_1 = 0;
                $total_amount_2 = 0;
                $total_amount_3 = 0;
            }

            $backgroundColor1 = $data['qty_month_1'] == 0 ? '#FFC2C2' : 'transparent';
            $backgroundColor2 = $data['qty_month_2'] == 0 ? '#FFC2C2' : 'transparent';
            $backgroundColor3 = $data['qty_month_3'] == 0 ? '#FFC2C2' : 'transparent';

            // validate rate
            if ($data['currency'] == "IDR" || empty($data['currency'])) {
                $rate = 1;
            } else {
                if (empty($data['rate'])) {
                    $rate = 0;
                } else {
                    $rate = $data['rate'];
                }
            }

            $subtotal_month_1 = ($data['price']  * $rate) * $data['qty_month_1'];
            $subtotal_month_2 = ($data['price']  * $rate) * $data['qty_month_2'];
            $subtotal_month_3 = ($data['price']  * $rate) * $data['qty_month_3'];

            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td style="text-align:right;background-color:' . $backgroundColor1 . ';">' . $this->format_number($data['qty_month_1']) . '</td>
                        <td style="text-align:right;background-color:' . $backgroundColor2 . ';">' . $this->format_number($data['qty_month_2']) . '</td>
                        <td style="text-align:right;background-color:' . $backgroundColor3 . ';">' . $this->format_number($data['qty_month_3']) . '</td>
                        <td style="text-align:right;">' . $this->format_number($subtotal_month_1) . '</td>
                        <td style="text-align:right;">' . $this->format_number($subtotal_month_2) . '</td>
                        <td style="text-align:right;">' . $this->format_number($subtotal_month_3) . '</td>
                        </tr>';
            $no++;
            $total_qty_1 += $data['qty_month_1'];
            $total_qty_2 += $data['qty_month_2'];
            $total_qty_3 += $data['qty_month_3'];
            $total_amount_1 += $subtotal_month_1;
            $total_amount_2 += $subtotal_month_2;
            $total_amount_3 += $subtotal_month_3;

            $grand_total_qty_1 += $data['qty_month_1'];
            $grand_total_qty_2 += $data['qty_month_2'];
            $grand_total_qty_3 += $data['qty_month_3'];
            $grand_total_amount_1 += $subtotal_month_1;
            $grand_total_amount_2 += $subtotal_month_2;
            $grand_total_amount_3 += $subtotal_month_3;
        }

        if ($current_customer !== '') {
            $html .= "<tr style='background-color:#FFFF00;'>
                        <td style='text-align:center; font-weight:bold;' colspan='4'>Total</td>
                        <td style='text-align:right; font-weight:bold;'>{$this->format_number($total_qty_1)}</td>
                        <td style='text-align:right; font-weight:bold;'>{$this->format_number($total_qty_2)}</td>
                        <td style='text-align:right; font-weight:bold;'>{$this->format_number($total_qty_3)}</td>
                        <td style='text-align:right; font-weight:bold;'>{$this->format_number($total_amount_1)}</td>
                        <td style='text-align:right; font-weight:bold;'>{$this->format_number($total_amount_2)}</td>
                        <td style='text-align:right; font-weight:bold;'>{$this->format_number($total_amount_3)}</td>
                        </tr>";
        }
           
        $html .= "<tr style='background-color:#C6EFCE;'>
                    <td style='text-align:center; font-weight:bold;' colspan='4'>Grand Total</td>
                    <td style='text-align:right; font-weight:bold;'>{$this->format_number($grand_total_qty_1)}</td>
                    <td style='text-align:right; font-weight:bold;'>{$this->format_number($grand_total_qty_2)}</td>
                    <td style='text-align:right; font-weight:bold;'>{$this->format_number($grand_total_qty_3)}</td>
                    <td style='text-align:right; font-weight:bold;'>{$this->format_number($grand_total_amount_1)}</td>
                    <td style='text-align:right; font-weight:bold;'>{$this->format_number($grand_total_amount_2)}</td>
                    <td style='text-align:right; font-weight:bold;'>{$this->format_number($grand_total_amount_3)}</td>
                </tr>";

        $html .= '</table></body></html>';
        echo $html;
    }
}
