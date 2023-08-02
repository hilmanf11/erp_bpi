<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Ap_schedules extends CI_Controller
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
            $this->load->view('finance/ap_schedules');
        } else {
            redirect('error_access');
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=ap_aging_schedules_$format.xls");
        }
        $filter_from = base64_decode($this->input->get("filter_from"));
        $filter_to = base64_decode($this->input->get("filter_to"));
        $filter_supplier = $this->input->get("filter_supplier");

        $start  = new DateTime($filter_from);
        $end = new DateTime($filter_to);
        $diff  = $start->diff($end);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('suppliers');
        $this->db->like("id", $filter_supplier);
        $suppliers = $this->db->get()->result_array();

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
                                <small>REPORT ACCOUNT PAYABLE AGING</small><br>
                                <small>PERIOD : <b>' . $filter_from . '</b> To <b>' . $filter_to . '</b></small>
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
            
            <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Supplier Name</th>
                <th rowspan="2">Currency</th>
                <th rowspan="2">Current</th>
                <th colspan="4">Amount Due</th>
            </tr>
            <tr>
                <th>1-30 Days</th>
                <th>31-60 Days</th>
                <th>61-90 Days</th>
                <th>Over 90 Days</th>
            </tr>';
        $no = 1;
        foreach ($suppliers as $supplier) {
            $supplier_id = $supplier['id'];
            $pi_current = $this->crud->query("SELECT SUM(total_grand) as total FROM purchase_invoices WHERE due_date between '$filter_from' and '$filter_to' and supplier_id = '$supplier_id' and `status` = '0'");

            $current = 0;
            $one = 0;
            $two = 0;
            $three = 0;
            $four = 0;
            if ($pi_current[0]->total != "") {
                $current = $pi_current[0]->total;
            } else {
                $pi_due = $this->crud->query("SELECT sum(total_grand) as total, datediff('$filter_to', due_date) as difference FROM purchase_invoices WHERE supplier_id = '$supplier_id' and `status` = '0' GROUP BY due_date");

                if (@$pi_due[0]->difference >= 1 && @$pi_due[0]->difference <= 30) {
                    $one = $pi_due[0]->total;
                } elseif (@$pi_due[0]->difference >= 31 && @$pi_due[0]->difference <= 60) {
                    $two = $pi_due[0]->total;
                } elseif (@$pi_due[0]->difference >= 61 && @$pi_due[0]->difference <= 90) {
                    $three = $pi_due[0]->total;
                } elseif (@$pi_due[0]->difference > 90) {
                    $four = $pi_due[0]->total;
                } else {
                    $one = 0;
                    $two = 0;
                    $three = 0;
                    $four = 0;
                }
            }

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $supplier['name'] . '</td>
                            <td style="text-align:center;">' . $supplier['currency'] . '</td>
                            <td style="text-align:right;">' . number_format($current, 4) . '</td>
                            <td style="text-align:right;">' . number_format($one, 4) . '</td>
                            <td style="text-align:right;">' . number_format($two, 4) . '</td>
                            <td style="text-align:right;">' . number_format($three, 4) . '</td>
                            <td style="text-align:right;">' . number_format($four, 4) . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
