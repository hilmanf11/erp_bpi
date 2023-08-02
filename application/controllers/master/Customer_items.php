<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Customer_items extends CI_Controller
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
        $this->form_validation->set_rules('customer_id', 'Customer ID', 'required|min_length[1]|max_length[30]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/customer_items');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('customer_items', ["name" => $post]);
        echo json_encode($send);
    }

    //GET DATA ITEM CUSTOMER
    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $get = $this->input->get('customer_id');
        $this->db->select('c.id, c.number, c.name, c.description, a.price, b.currency');
        $this->db->from('customer_items a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->like("a.customer_id", $get);
        $this->db->like("c.number", $post);
        $this->db->group_by('c.id');
        $this->db->order_by('c.id', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    //GET DATA ITEM CUSTOMER BY ID and PRODUCT
    public function readPrice()
    {
        $customer_id = $this->input->post('customer_id');
        $item_number = $this->input->post('item_number');

        $this->db->select('c.id, c.number, c.name, c.description, a.price, b.currency');
        $this->db->from('customer_items a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where("a.customer_id", $customer_id);
        $this->db->where("c.number", $item_number);
        $this->db->group_by('c.id');
        $this->db->order_by('c.id', 'ASC');
        $records = $this->db->get()->row();
        echo json_encode($records);
    }

    //GET DATATABLES
    public function datatables()
    {
        $filter_customers  = base64_decode($this->input->get('filter_customers'));
        $filter_items = base64_decode($this->input->get('filter_items'));
        $id = $_POST['id'];
        if ($id === "0") {
            $this->db->select('a.*, c.name as item_name, b.name as customer_name, b.type, b.currency');
            $this->db->from('customer_items a');
            $this->db->join('customers b', 'b.id = a.customer_id');
            $this->db->join('items c', 'c.id = a.item_id');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.customer_id', $filter_customers);
            $this->db->like('a.item_id', $filter_items);
            $this->db->group_by('a.customer_id');
            $this->db->order_by('b.name', 'ASC');
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['customer_id'],
                    "customer_name" => $record['customer_name'],
                    "state" => "closed",
                    "type" => $record['type'],
                );
            }
        } else {
            $this->db->select('a.*, c.number as item_number, c.name as item_name, b.name as customer_name, b.type, b.currency');
            $this->db->from('customer_items a');
            $this->db->join('customers b', 'b.id = a.customer_id');
            $this->db->join('items c', 'c.id = a.item_id');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.customer_id', $id);
            $this->db->like('a.item_id', $filter_items);
            $this->db->order_by('c.name', 'ASC');
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['id'],
                    "customer_id" => $record['customer_id'],
                    "customer_name" => $record['customer_name'],
                    "type" => $record['type'],
                    "item_id" => $record['item_id'],
                    "item_number" => $record['item_number'],
                    "item_name" => $record['item_name'],
                    "item_cust" => $record['item_cust'],
                    "max_order" => $record['max_order'],
                    "ar_balance" => $record['ar_balance'],
                    "expired" => $record['expired'],
                    "price" => $record['price'],
                    "currency" => $record['currency'],
                    "remarks" => $record['remarks'],
                    "created_by" => $record['created_by'],
                    "created_date" => $record['created_date'],
                    "updated_by" => $record['updated_by'],
                    "updated_date" => $record['updated_date'],
                );
            }
        }
        $result = !empty($arr) ? $arr : [];
        echo json_encode($result);
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $customer_items = $this->crud->read('customer_items', [], ["customer_id" => $post['customer_id'], "item_id" => $post['item_id']]);
                if (!$customer_items) {
                    $send = $this->crud->create('customer_items', $post);
                    echo $send;
                } else {
                    show_error("Duplicated");
                }
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
            $send = $this->crud->update('customer_items', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('customer_items', $data);
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
                'item_number' => $data->val($i, 2),
                'customer_number' => $data->val($i, 3),
                'price' => $data->val($i, 4),
                'max_order' => $data->val($i, 5),
                'ar_balance' => $data->val($i, 6),
                'expired' => $data->val($i, 7),
                'remarks' => $data->val($i, 8)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('excel/failed/customer_items.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/customer_items.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/customer_items.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data       = $this->input->post('data');
            //Cek Process Number
            $item = $this->api->read('items', [], ["number" => $data['item_number']]);
            $customer = $this->api->read('customers', [], ["number" => $data['customer_number']]);
            $customer_items = $this->api->read('customer_items', [], ["customer_number" => $customer->id, "item_id" => $item->id]);
            if (empty($item->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['item_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($customer->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customer " . $data['customer_number'] . " Not Found", "theme" => "error"));
            } elseif (!empty($customer_items->item_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['item_number'] . " Duplicate Data", "theme" => "error"));
            } else {
                $postFinal = array(
                    "customer_id" => $customer->id,
                    "item_id" => $item->id,
                    "max_order" => $data['max_order'],
                    "ar_balance" => $data['ar_balance'],
                    "expired" => $data['expired'],
                    "price" => $data['price'],
                    "remarks" => $data['remarks'],
                );
                $send   = $this->api->create('customer_items', $postFinal);
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
            header("Content-Disposition: attachment; filename=customer_items_$format.xls");
        }
        $filter_customers  = base64_decode($this->input->get('filter_customers'));
        $filter_items = base64_decode($this->input->get('filter_items'));
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, c.number as item_number, c.name as item_name, b.name as customer_name, b.type, b.currency');
        $this->db->from('customer_items a');
        $this->db->join('customers b', 'b.id = a.customer_id');
        $this->db->join('items c', 'c.id = a.item_id');
        $this->db->where('a.deleted', 0);
        $this->db->like('a.customer_id', $filter_customers);
        $this->db->like('a.item_id', $filter_items);
        $this->db->order_by('b.name', 'ASC');
        $this->db->order_by('c.name', 'ASC');
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
                            <small>MASTER CUSTOMER ITEM</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Customer</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Product Customer</th>
                    <th>Price</th>
                    <th>Currency</th>
                    <th>Order Max</th>
                    <th>AR Balance</th>
                    <th>Expired</th>
                    <th>Remarks</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td>' . $no . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['item_number'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['item_cust'] . '</td>
                            <td>' . $data['price'] . '</td>
                            <td>' . $data['currency'] . '</td>
                            <td>' . $data['max_order'] . '</td>
                            <td>' . $data['ar_balance'] . '</td>
                            <td>' . $data['expired'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
