<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Customers extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //VALIDASI FORM
        $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[20]|is_unique[customers.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/customers');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('customers', ["name" => $post]);
        echo json_encode($send);
    }
    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.account_name');
            $this->db->from('customers a');
            $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field != "account_name"){
                        $this->db->like("a.".$filter->field, $filter->value);
                    }else{
                        $this->db->like("b.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->order_by('a.name', 'ASC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('customers', $post);
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('customers', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('customers', $data);
        echo $send;
    }
    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=customers_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $config_iso = $this->db->get('config_iso')->row();

        $this->db->select('a.*, b.account_name');
        $this->db->from('customers a');
        $this->db->join('account_coa b', 'a.account_number = b.account_number', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.name', 'ASC');
        $records = $this->db->get()->result_array();
        
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>MASTER CUSTOMER</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                <table style="width:100%; font-size:10px;">
                    <tr>
                        <td width="60">Doc No</td>
                        <td width="5">:</td>
                        <td width="100">' . $config_iso->doc_customer . '</td>
                    </tr>
                    <tr>
                        <td>Form</td>
                        <td>:</td>
                        <td>' . $config_iso->form_customer . '</td>
                    </tr>
                    <tr>
                        <td>Print Date</td>
                        <td>:</td>
                        <td>' . date("Y-m-d H:i") . '</td>
                    </tr>
                    <tr>
                        <td>Print By</td>
                        <td>:</td>
                        <td>' . $this->session->name . '</td>
                    </tr>
                </table> 
            </div>
        </center>
        <br><br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Code</th>
                <th>Name</th>
                <th>Type</th>
                <th>Address</th>
                <th>Billing Address</th>
                <th>Contact Person</th>
                <th>Billing Telp</th>
                <th>Email</th>
                <th>Website</th>
                <th>Currency</th>
                <th>Payment Term</th>
                <th>Account No</th>
                <th>Account Name</th>
                <th>Bank Account</th>
                <th>Bank Name</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['name'] . '</td>
                        <td>' . $data['type'] . '</td>
                        <td>' . $data['address'] . '</td>
                        <td>' . $data['address_billing'] . '</td>
                        <td>' . $data['telp'] . '</td>
                        <td>' . $data['telp_billing'] . '</td>
                        <td>' . $data['email'] . '</td>
                        <td>' . $data['website'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . $data['payment_term'] . '</td>
                        <td>' . $data['account_number'] . '</td>
                        <td>' . $data['account_name'] . '</td>
                        <td>' . $data['bank_account'] . '</td>
                        <td>' . $data['bank_name'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
