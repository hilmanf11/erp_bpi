<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Job_orders extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('engineering/job_orders');
        } else {
            redirect('error_access');
        }
    }
    public function reads($customer_id, $item_id){
        $this->db->select('circuit');
        $this->db->from('job_orders');
        $this->db->where('deleted', 0);
        $this->db->where('customer_id', $customer_id);
        $this->db->where('item_id', $item_id);
        $this->db->group_by('circuit');
        $this->db->order_by('circuit', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }
    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_product_no = $this->input->get('filter_product_no');
        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $id = $_POST['id'];
        if ($id === "0") {
            $this->db->select('a.item_id, b.number as item_number, b.name as item_name, COUNT(a.circuit) as circuit');
            $this->db->from('job_orders a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.item_id', $filter_product_no);
            $this->db->group_by('b.number');
            $this->db->order_by('b.number', 'ASC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['item_id'],
                    "item_number" => $record['item_number'],
                    "item_name" => $record['item_name'],
                    "circuit" => $record['circuit'],
                    "state" => "closed",
                );
            }
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        } else {
            $this->db->select('a.*, b.number as item_number, b.name as item_name, c.name as customer_name');
            $this->db->from('job_orders a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->where('a.item_id', $id);
            $this->db->order_by('a.circuit', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $job_orders = $this->crud->read('job_orders', [], ["item_id" => $post['item_id'], "customer_id" => $post['customer_id'], "circuit" => $post['circuit']]);
                if (@$job_orders->id != "") {
                    echo json_encode(array("title" => "Duplicated", "message" => "Product No & Circuit " . $post['circuit'] . " Data Duplicated", "theme" => "error"));
                } else {
                    $send   = $this->crud->create('job_orders', $post);
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
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('job_orders', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('job_orders', $data);
        echo $send;
    }
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
        for ($i = 4; $i <= $total_row; $i++) {
            $datas[] = array(
                'product_number' => $data->val($i, 2),
                'customer_number' => $data->val($i, 3),
                'circuit' => $data->val($i, 4),
                'wire' => $data->val($i, 5),
                'type' => $data->val($i, 6),
                'size' => $data->val($i, 7),
                'color' => $data->val($i, 8),
                'length' => $data->val($i, 9),
                'a_terminal' => $data->val($i, 10),
                'a_seal' => $data->val($i, 11),
                'a_chi' => $data->val($i, 12),
                'a_chc' => $data->val($i, 13),
                'a_stripping' => $data->val($i, 14),
                'a_process' => $data->val($i, 15),
                'a_note' => $data->val($i, 16),
                'b_terminal' => $data->val($i, 17),
                'b_seal' => $data->val($i, 18),
                'b_chi' => $data->val($i, 19),
                'b_chc' => $data->val($i, 20),
                'b_stripping' => $data->val($i, 21),
                'b_process' => $data->val($i, 22),
                'b_note' => $data->val($i, 23)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('excel/failed/job_orders.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/job_orders.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/job_orders.txt";
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
            $item = $this->crud->read('items', [], ["number" => $data['product_number']]);
            $customer = $this->crud->read('customers', [], ["number" => $data['customer_number']]);
            $job_orders = $this->crud->read('job_orders', [], ["item_id" => @$item->id, "customer_id" => @$customer->id, "circuit" => $data['circuit']]);
            $dataFinal = array(
                "item_id" => @$item->id,
                "customer_id" => @$customer->id,
                "circuit" => $data['circuit'],
                "wire" => $data['wire'],
                "type" => $data['type'],
                "size" => $data['size'],
                "color" => $data['color'],
                "length" => $data['length'],
                "a_terminal" => $data['a_terminal'],
                "a_seal" => $data['a_seal'],
                "a_chi" => $data['a_chi'],
                "a_chc" => $data['a_chc'],
                "a_stripping" => $data['a_stripping'],
                "a_process" => $data['a_process'],
                "a_note" => $data['a_note'],
                "b_terminal" => $data['b_terminal'],
                "b_seal" => $data['b_seal'],
                "b_chi" => $data['b_chi'],
                "b_chc" => $data['b_chc'],
                "b_stripping" => $data['b_stripping'],
                "b_process" => $data['b_process'],
                "b_note" => $data['b_note'],
            );
            if (empty($item->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['product_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($customer->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customer No " . $data['customer_number'] . " Not Found", "theme" => "error"));
            } elseif (!empty($job_orders->id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Circuit No " . $data['circuit'] . " Duplicate Data", "theme" => "error"));
            } else {
                $send   = $this->crud->create('job_orders', $dataFinal);
                echo $send;
            }
        }
    }
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=job_orders_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_product_no = $this->input->get('filter_product_no');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as item_number, b.name as item_name, c.name as customer_name');
        $this->db->from('job_orders a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('a.item_id', $filter_product_no);
        $this->db->order_by('b.number', 'ASC');
        $this->db->order_by('b.number', 'ASC');
        $this->db->order_by('a.circuit', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' .  $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>JOB ORDER PROCESS PARAMETER</small>
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
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Customer</th>
                <th rowspan="2">Product No</th>
                <th rowspan="2">Product Name</th>
                <th rowspan="2">Circuit</th>
                <th rowspan="2">Wire Code</th>
                <th rowspan="2">Type & Size</th>
                <th rowspan="2">Color</th>
                <th rowspan="2">Length</th>
                <th colspan="7">Terminal Side A</th>
                <th colspan="7">Terminal Side B</th>
            </tr>
            <tr>
                <th>Terminal No</th>
                <th>Seal</th>
                <th>CH</th>
                <th>IH</th>
                <th>Stripping</th>
                <th>Process</th>
                <th>Note</th>
                <th>Terminal No</th>
                <th>Seal</th>
                <th>CH</th>
                <th>IH</th>
                <th>Stripping</th>
                <th>Process</th>
                <th>Note</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['item_number'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . $data['circuit'] . '</td>
                        <td>' . $data['wire'] . '</td>
                        <td>' . $data['type'] . ' ' . $data['size'] . '</td>
                        <td>' . $data['color'] . '</td>
                        <td>' . $data['length'] . '</td>
                        <td>' . $data['a_terminal'] . '</td>
                        <td>' . $data['a_seal'] . '</td>
                        <td>' . $data['a_chi'] . '</td>
                        <td>' . $data['a_chc'] . '</td>
                        <td>' . $data['a_stripping'] . '</td>
                        <td>' . $data['a_process'] . '</td>
                        <td>' . $data['a_note'] . '</td>
                        <td>' . $data['b_terminal'] . '</td>
                        <td>' . $data['b_seal'] . '</td>
                        <td>' . $data['b_chi'] . '</td>
                        <td>' . $data['b_chc'] . '</td>
                        <td>' . $data['b_stripping'] . '</td>
                        <td>' . $data['b_process'] . '</td>
                        <td>' . $data['b_note'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
