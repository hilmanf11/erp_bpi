<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Ap_payments extends CI_Controller
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
        $this->form_validation->set_rules('purchase_invoice', 'Purchase Invoice', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/ap_payments');
        } else {
            redirect('error_access');
        }
    }

    public function reads($number)
    {
        $payment_no = base64_decode($number);
        $this->db->select('*');
        $this->db->from('ap_payments');
        $this->db->where('deleted', 0);
        $this->db->where('status', 0);
        $this->db->where('payment_no', $payment_no);
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function readInvoiceType()
    {
        $supplier_id = $this->input->get('supplier_id');
        $payment_type = $this->input->get('payment_type');

        if($payment_type == "PURCHASE"){
            $where_por = "por_no != '-'";
        }else{
            $where_por = "por_no = '-'";
        }

        $records = $this->crud->query("SELECT `number` FROM purchase_invoices WHERE $where_por and supplier_id = '$supplier_id' and `status` = 0");
        echo json_encode($records);
    }

    public function readPayments($supplier_id)
    {
        $data = $this->crud->query("SELECT DISTINCT payment_no FROM ap_payments WHERE supplier_id = '$supplier_id' ORDER BY `payment_no` ASC");
        echo json_encode($data);
    }

    public function readInvoices($supplier_id)
    {
        $data = $this->crud->query("SELECT DISTINCT `purchase_invoice` FROM ap_payments WHERE supplier_id = '$supplier_id' ORDER BY `purchase_invoice` ASC");
        echo json_encode($data);
    }

    public function number($trans_date)
    {
        $datenow    = "AP-" . date("Ymd", strtotime(base64_decode($trans_date)));
        $sqlGetID   = $this->db->query("SELECT max(`payment_no`) as kode FROM ap_payments WHERE `payment_no` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $datenow . "-" . $autoID;
    }

    public function datatablesTemp()
    {
        $purchase_invoice = base64_decode($this->input->get('purchase_invoice'));
        $purchase_invoice_ex = explode(",", $purchase_invoice);

        $this->db->select("a.*, (CASE WHEN b.payment is null THEN a.total_grand ELSE (a.total_grand - b.payment) END) as payment");
        $this->db->from('purchase_invoices a');
        $this->db->join('ap_payments b', 'a.number = b.purchase_invoice', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where_in('a.number', $purchase_invoice_ex);
        $this->db->group_by('a.number');
        $this->db->order_by('a.number', 'asc');
        $records = $this->db->get()->result_array();

        $total_payment = 0;
        foreach ($records as $record) {
            $total_payment += $record['payment'];
            $obj[] = array(
                "purchase_invoice" => $record['number'],
                "supplier_invoice" => $record['invoice_no'],
                "currency" => $record['currency'],
                "amount" => $record['payment'],
                "balance" => $record['payment'],
                "payment" => $record['payment'],
                "account_number" => $record['account_number'],
                "account_type" => $record['account_type']
            );
        }

        $arr['rows'] = $obj;
        $arr['total_payment'] = round($total_payment, 2);
        die(json_encode($arr));
    }

    public function datatables($details = "")
    {
        $filter_payment_type  = base64_decode($this->input->get('filter_payment_type'));
        $filter_payment_date_from = base64_decode($this->input->get('filter_payment_date_from'));
        $filter_payment_date_to = base64_decode($this->input->get('filter_payment_date_to'));
        $filter_payment_no = base64_decode($this->input->get('filter_payment_no'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_payment_by = base64_decode($this->input->get('filter_payment_by'));
        if ($details == "") {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as supplier_name');
            $this->db->from('ap_payments a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->like('a.payment_type', $filter_payment_type);
            if($filter_payment_date_from != "" && $filter_payment_date_to == ""){
                $this->db->where("a.payment_date between '$filter_payment_date_from' and '$filter_payment_date_to'");
            }
            $this->db->like('a.payment_no', $filter_payment_no);
            $this->db->like('a.supplier_id', $filter_supplier);
            $this->db->like('a.purchase_invoice', $filter_invoice_no);
            $this->db->like('a.bank_account', $filter_bank_no);
            $this->db->like('a.payment_by', $filter_payment_by);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.payment_date', 'DESC');
            $this->db->group_by('a.payment_no');
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
        } else {
            $payment_no = base64_decode($this->input->get('payment_no'));

            $this->db->select('*');
            $this->db->from('ap_payments');
            $this->db->where('payment_no', $payment_no);
            $this->db->group_by('purchase_invoice');
            $this->db->order_by('status', 'ASC');
            $this->db->order_by('purchase_invoice', 'DESC');
        }
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $ap_payments = $this->crud->read('ap_payments', [], [
                    "purchase_invoice" => $post['purchase_invoice'], 
                    "amount" => $post['amount']
                ]);

                if (@$ap_payments->id != "") {
                    $send = $this->crud->update('ap_payments', ["purchase_invoice" => $post['purchase_invoice'], "amount" => $post['amount']], $post);
                    echo $send;
                } else {
                    $send = $this->crud->create('ap_payments', $post);
                    if ($send) {
                        if($post['amount'] == $post['payment']){
                            $this->crud->update('purchase_invoices', ["number" => $post['purchase_invoice']], ["status" => 1]);
                        }
                    }
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->crud->update('ap_payments', ["payment_no" => $post['payment_no']], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();

        $ap_payments = $this->crud->reads("ap_payments", [], ["payment_no" => $data['payment_no']]);
        foreach ($ap_payments as $ap_payment) {
            $this->crud->update("purchase_invoices", [
                "number" => $ap_payment->purchase_invoice,
            ], ["status" => 0]);
        }

        $send = $this->crud->delete('ap_payments', $data);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=ap_payment_$format.xls");
        }

        $filter_payment_type  = base64_decode($this->input->get('filter_payment_type'));
        $filter_payment_date_from = base64_decode($this->input->get('filter_payment_date_from'));
        $filter_payment_date_to = base64_decode($this->input->get('filter_payment_date_to'));
        $filter_payment_no = base64_decode($this->input->get('filter_payment_no'));
        $filter_supplier = base64_decode($this->input->get('filter_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_bank_no = base64_decode($this->input->get('filter_bank_no'));
        $filter_payment_by = base64_decode($this->input->get('filter_payment_by'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('ap_payments a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->like('a.payment_type', $filter_payment_type);
        if($filter_payment_date_from != "" && $filter_payment_date_to == ""){
            $this->db->where("a.payment_date between '$filter_payment_date_from' and '$filter_payment_date_to'");
        }
        $this->db->like('a.payment_no', $filter_payment_no);
        $this->db->like('a.supplier_id', $filter_supplier);
        $this->db->like('a.purchase_invoice', $filter_invoice_no);
        $this->db->like('a.bank_account', $filter_bank_no);
        $this->db->like('a.payment_by', $filter_payment_by);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.payment_date', 'DESC');
        $this->db->group_by('a.payment_no');
        $records = $this->db->get()->result_array();

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
                                <small>REPORT AP PAYMENT</small><br>
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
                    <th width="20">No</th>
                    <th>Payment Type</th>
                    <th colspan="2">Payment No</th>
                    <th>Payment Date</th>
                    <th>Supplier Name</th>
                    <th>Bank Account</th>
                    <th>Payment By</th>
                    <th colspan="2">Note</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $payment_no = $data['payment_no'];

            $this->db->select('*');
            $this->db->from('ap_payments');
            $this->db->where('payment_no', $payment_no);
            $this->db->group_by('purchase_invoice');
            $this->db->order_by('status', 'ASC');
            $this->db->order_by('purchase_invoice', 'DESC');
            $details = $this->db->get()->result_array();

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['payment_type'] . '</td>
                            <td colspan="2">' . $data['payment_no'] . '</td>
                            <td>' . $data['payment_date'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['bank_account'] . '</td>
                            <td>' . $data['payment_by'] . '</td>
                            <td colspan="2">' . $data['note'] . '</td>
                        </tr>';
            $html .= '  <tr>
                            <td colspan="11" style="background:#D1FFC6;"><b>DETAIL OF ' . $data['payment_no'] . '</b></td>
                        </tr>
                        <tr>
                            <th width="20"></th>
                            <th>Purchase Invoice</th>
                            <th>Supplier Invoice</th>
                            <th>Currency</th>
                            <th>Amount</th>
                            <th>Balance</th>
                            <th>Payment</th>
                            <th>Remarks</th>
                            <th>Account No</th>
                            <th>Debt/Credit</th>
                        </tr>';
            foreach ($details as $detail) {
                $html .= '  <tr>
                                <td></td>
                                <td>' . $detail['purchase_invoice'] . '</td>
                                <td>' . $detail['supplier_invoice'] . '</td>
                                <td>' . $detail['currency'] . '</td>
                                <td style="text-align:right">' . number_format($detail['amount'], 2) . '</td>
                                <td style="text-align:right">' . number_format($detail['balance'], 2) . '</td>
                                <td style="text-align:right">' . number_format($detail['payment'], 2)  . '</td>
                                <td>' . $detail['remarks'] . '</td>
                                <td>' . $detail['account_number'] . '</td>
                                <td>' . $detail['account_type'] . '</td>
                            </tr>';
            }
            $no++;
        }
        $html .= '</table>';
        $html .= '  <table id="customers" style="margin-top:20px; width:50%;">
                        <tr>
                            <th width="200" style="text-align:center;">Approval By</th>
                            <th width="200" style="text-align:center;">Dept Manager</th>
                            <th width="200" style="text-align:center;">Created By</th>
                        </tr>
                        <tr>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;">'.$this->session->name.'</th>
                        </tr>
                    </table></body></html>';
        echo $html;
    }
}
