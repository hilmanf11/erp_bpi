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
        $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[20]|is_unique[customer_items.customer_id]');
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[customer_items.item_fg_id]');
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
        $send = $this->crud->reads('customer_items', ["customer_id" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('b.id as customer_id, b.number as customer_number, b.name as customer_name, b.type, b.status, a.created_by, a.created_date, a.updated_by, a.updated_date');
            $this->db->from('customer_items a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->group_by('b.name');
            $this->db->order_by('b.id', 'ASC');
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

    //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $number = base64_decode($this->input->get('number'));
            $filter_customer_id = base64_decode($this->input->get('filter_customer_id'));

            $this->db->select('a.*, b.number as customer_number, b.name as customer_name, b.currency as customer_currency, c.number as item_fg_number, c.number_customer as item_fg_customer');
            $this->db->from('customer_items a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            $this->db->where('b.number', $number);
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $customer_id = base64_decode($this->input->get('customer_id'));

            $this->db->select('a.*, b.number as item_fg_number, b.number_customer as item_fg_customer, c.currency as customer_currency');
            $this->db->from('customer_items a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->where('a.customer_id', $customer_id);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLE HISTORY PRICE
    public function datatableHistories()
    {
        if ($this->input->get()) {
            $customer_id = base64_decode($this->input->get('customer_id'));
            $item_fg_id = base64_decode($this->input->get('item_fg_id'));

            $this->db->select('*');
            $this->db->from('customer_item_histories');
            $this->db->where('customer_id', $customer_id);
            $this->db->where('item_fg_id', $item_fg_id);
            $this->db->order_by('valid_date', 'DESC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $customer_items = $this->crud->read("customer_items", [], ["customer_id" => $post['customer_id'], "item_fg_id" => $post['item_fg_id']]);
            $customer_item_histories = $this->crud->read("customer_item_histories", [], ["customer_id" => $post['customer_id'], "item_fg_id" => $post['item_fg_id'], "price" => $post['price']]);
            if (@$customer_items->customer_id != "") {
                $send = $this->crud->update('customer_items', ["customer_id" => $post['customer_id'], "item_fg_id" => $post['item_fg_id']], $post);
                if (@$customer_item_histories->customer_id == "") {
                    $send2 = $this->crud->create('customer_item_histories', $post);
                }
            } else {
                $send = $this->crud->create('customer_items', $post);
                $send2 = $this->crud->create('customer_item_histories', $post);
            }
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
                //excel
                'customer_id' => $data->val($i, 2),
                'item_fg_id' => $data->val($i, 3),
                'price' => $data->val($i, 4),
                'valid_date' => $data->val($i, 5),
                'remark' => $data->val($i, 6)
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

    //UPLOAD DOWNLOAD FAILED
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

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Process Number          //table       //field        //field excel
            $customer_items = $this->crud->read('customer_items', [], ["customer_id" => $data['customer_id'], "item_fg_id" => $data['item_fg_id']]);

            if (!empty($customer_items->customer_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Customer " . $data['customer_id'] . " is Duplicate Data", "theme" => "error"));
            } elseif (!empty($customer_items->item_fg_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['item_fg_id'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "customer_id" => $data['customer_id'],
                    "item_fg_id" => $data['item_fg_id'],
                    "price" => $data['price'],
                    "valid_date" => $data['valid_date'],
                    "remark" => $data['remark'],
                );
                $send   = $this->crud->create('customer_items', $dataFinal);
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

        $get = $this->input->get();
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as customer_number, b.name as customer_name, b.currency as customer_currency, c.number as item_fg_number, c.name as item_fg_name, c.number_customer as item_fg_customer');
        $this->db->from('customer_items a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customer_items {border-collapse: collapse;width: 100%;font-size: 12px;}#customer_items td, #customer_items th {border: 1px solid #ddd;padding: 2px;}#customer_items tr:nth-child(even){background-color: #f2f2f2;}#customer_items tr:hover {background-color: #ddd;}#customer_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER CUSTOMER ITEM</h3>
            </div>
        </center>
        
        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer ID</th>
                <th>Customer Code</th>
                <th>Customer Name</th>
                <th>Product ID</th>
                <th>Product No.</th>
                <th>Product Name</th>
                <th>Product Customer</th>
                <th>Currency</th>
                <th>Price</th>
                <th>Valid Date</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['customer_id'] . '</td>
                    <td>' . $data['customer_number'] . '</td>
                    <td>' . $data['customer_name'] . '</td>
                    <td>' . $data['item_fg_id'] . '</td>
                    <td>' . $data['item_fg_number'] . '</td>
                    <td>' . $data['item_fg_name'] . '</td>
                    <td>' . $data['item_fg_customer'] . '</td>
                    <td>' . $data['customer_currency'] . '</td>
                    <td>' . $data['price'] . '</td>
                    <td>' . $data['valid_date'] . '</td>
                    <td>' . $data['remark'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
