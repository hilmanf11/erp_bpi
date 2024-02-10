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
        $this->form_validation->set_rules('number', 'Customer Code', 'required|min_length[1]|max_length[20]|is_unique[customers.number]');
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
        $send = $this->crud->query("SELECT * FROM customers WHERE id like '%$post%' or `number` like '%$post%' or `name` like '%$post%'");
        echo json_encode($send);
    }

    public function readAddress($customer_id)
    {
        $send = $this->crud->query("SELECT * FROM customer_address WHERE customer_id = '$customer_id'");
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
            $this->db->select('*');
            $this->db->from('customers');
            $this->db->where('deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('id', 'asc');
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

    public function datatables2($customer_id)
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
            $this->db->select('*');
            $this->db->from('customer_address');
            $this->db->where('customer_id', $customer_id);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('plant', 'asc');
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

    //AUTO ID
    public function autoid()
    {
        $sql = $this->db->query("SELECT max(id) as kode FROM customers");
        $row = $sql->row();
        $kode = substr($row->kode, 2);
        $autoid = "C" . sprintf("%03s", $kode + 1);
        echo $autoid;
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

    public function create2()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $send   = $this->crud->create('customer_address', $post);
            echo $send;
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

    public function update2()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('customer_address', ["id" => $id], $post);
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

    public function delete2()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('customer_address', $data);
        echo $send;
    }
    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'name' => $data->val($i, 2),
                'number' => $data->val($i, 3),
                'type' => $data->val($i, 4),
                'currency' => $data->val($i, 5),
                'taxes' => $data->val($i, 6),
                'payment_term' => $data->val($i, 7),
                'bank_account' => $data->val($i, 8),
                'bank_name' => $data->val($i, 9),
                'status' => $data->val($i, 10)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/customers.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/customers.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/customers.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }
    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Process Number          //table       //field        //field excel
            $customers = $this->crud->read('customers', [], ["number" => $data['number']]);

            //AUTOID
            $sql = $this->db->query("SELECT max(id) as kode FROM customers");
            $row = $sql->row();
            $kode = substr($row->kode, 2);
            $autoid = "C" . sprintf("%03s", $kode + 1);

            if (!empty($customers->number)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Customer Code " . $data['number'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "id" => $autoid,
                    "name" => $data['name'],
                    "number" => $data['number'],
                    "type" => $data['type'],
                    "taxes" => $data['taxes'],
                    "currency" => $data['currency'],
                    "payment_term" => $data['payment_term'],
                    "bank_account" => $data['bank_account'],
                    "bank_name" => $data['bank_name'],
                    "status" => $data['status'],
                );
                $send   = $this->crud->create('customers', $dataFinal);
                echo $send;
            }
        }
    }

    //UPLOAD DATA
    public function upload2()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload2']['name']);
        move_uploaded_file($_FILES['file_upload2']['tmp_name'], $target);
        chmod($_FILES['file_upload2']['name'], 0777);
        $file = $_FILES['file_upload2']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'customer_id' => $data->val($i, 2),
                'plant' => $data->val($i, 3),
                'department' => $data->val($i, 4),
                'address' => $data->val($i, 5),
                'address_billing' => $data->val($i, 6),
                'contact_person' => $data->val($i, 7),
                'telp' => $data->val($i, 8),
                'telp_billing' => $data->val($i, 9),
                'email' => $data->val($i, 10),
                'website' => $data->val($i, 11),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload2']['name']);
    }
    public function uploadclearFailed2()
    {
        @unlink('failed/customer_address.txt');
    }
    public function uploadcreateFailed2()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/customer_address.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed2()
    {
        $file = "failed/customer_address.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate2()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Process Number          //table       //field        //field excel
            $customers = $this->crud->read('customers', [], ["id" => $data['customer_id']]);

            if (empty($customers->number)) {
                echo json_encode(array("title" => "Not Found", "message" => " Customer Id " . $data['customer_id'] . " is Not Found", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "customer_id" => $data['customer_id'],
                    "plant" => $data['plant'],
                    "department" => $data['department'],
                    "address" => $data['address'],
                    "address_billing" => $data['address_billing'],
                    "contact_person" => $data['contact_person'],
                    "telp" => $data['telp'],
                    "telp_billing" => $data['telp_billing'],
                    "email" => $data['email'],
                    "website" => $data['website'],
                );

                $send   = $this->crud->create('customer_address', $dataFinal);
                echo $send;
            }
        }
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

        $this->db->select('a.*, b.*');
        $this->db->from('customers a');
        $this->db->join('customer_address b', 'a.id = b.customer_id', 'left');
        $this->db->order_by('a.id', 'ASC');
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
                            <b>' . $config->name . '</b>
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
                <h3>MASTER CUSTOMER</h3>
            </div>
        </center>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer Name</th>
                <th>Customer Code</th>
                <th>Type</th>
                <th>Plant</th>
                <th>Department</th>
                <th>Address</th>
                <th>Billing Address</th>
                <th>Contact Person</th>
                <th>Telepon</th>
                <th>Billing Contact</th>
                <th>Email</th>
                <th>Website</th>
                <th>Currency</th>
                <th>Payment Term (Day)</th>
                <th>Bank Account</th>
                <th>Bank Name</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            
            if ($data['status'] == 0) {
                $status = 'Active';
            } else {
                $status = 'Not Active';
            }

            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td>' . $data['plant'] . '</td>
                    <td>' . $data['department'] . '</td>
                    <td>' . $data['address'] . '</td>
                    <td>' . $data['address_billing'] . '</td>
                    <td>' . $data['contact_person'] . '</td>
                    <td>' . $data['telp'] . '</td>
                    <td>' . $data['telp_billing'] . '</td>
                    <td>' . $data['email'] . '</td>
                    <td>' . $data['website'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . $data['payment_term'] . '</td>
                    <td>' . $data['bank_account'] . '</td>
                    <td>' . $data['bank_name'] . '</td>
                    <td>' . $status . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
