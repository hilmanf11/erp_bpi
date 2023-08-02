<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Supplier_items extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/supplier_items');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('supplier_items', ["name" => $post]);
        echo json_encode($send);
    }

    //GET SUPPLIER
    public function readSuppliers()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $item_number = $this->input->get('item_number');
        $item_id = $this->input->get('item_id');
        $item_family_id = $this->input->get('item_family_id');

        $this->db->select('b.*, c.number as item_number, a.mpq, a.moq');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->like("c.number", $item_number);
        $this->db->like("c.id", $item_id);
        $this->db->like("d.id", $item_family_id);
        $this->db->like("b.name", $post);
        $this->db->group_by('b.number');
        $this->db->order_by('b.name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    //GET DATA ITEM
    public function readItem()
    {
        $supplier_id = $this->input->post('supplier_id');
        $item_number = $this->input->post('item_number');
        $this->db->select('a.*');
        $this->db->from('supplier_items a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where("a.supplier_id", $supplier_id);
        $this->db->where("b.number", $item_number);
        $this->db->group_by('b.number');
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->row();
        echo json_encode($records);
    }

    //GET DATA ITEMS
    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $item_family_id = $this->input->get('item_family_id');
        $item_family_number = $this->input->get('item_family_number');
        $supplier_id = $this->input->get('supplier_id');
        $this->db->select('b.id, b.number, b.name, b.description, c.id as item_family_id, c.name as item_family_name, a.mpq, a.moq, d.currency, a.price');
        $this->db->from('supplier_items a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->like("c.id", $item_family_id);
        $this->db->like("c.number", $item_family_number);
        $this->db->like("a.supplier_id", $supplier_id);
        $this->db->like("b.number", $post);
        $this->db->group_by('b.number');
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    //GET DATATABLES
    public function datatables()
    {
        $filter_suppliers  = base64_decode($this->input->get('filter_suppliers'));
        $filter_items = base64_decode($this->input->get('filter_items'));
        $id = $_POST['id'];
        if ($id === "0") {
            $this->db->select('a.*, b.name as supplier_name, b.type');
            $this->db->from('supplier_items a');
            $this->db->join('suppliers b', 'b.id = a.supplier_id');
            $this->db->join('items c', 'c.id = a.item_id');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.supplier_id', $filter_suppliers);
            $this->db->like('a.item_id', $filter_items);
            $this->db->group_by('a.supplier_id');
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['supplier_id'],
                    "supplier_name" => $record['supplier_name'],
                    "state" => "closed",
                    "type" => $record['type'],
                );
            }
        } else {
            $this->db->select('a.*, c.number as item_number, c.name as item_name, b.name as supplier_name, b.type, b.currency');
            $this->db->from('supplier_items a');
            $this->db->join('suppliers b', 'b.id = a.supplier_id');
            $this->db->join('items c', 'c.id = a.item_id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.supplier_id', $id);
            $this->db->like('a.item_id', $filter_items);
            $this->db->order_by('c.number', 'ASC');
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['id'],
                    "supplier_id" => $record['supplier_id'],
                    "supplier_name" => $record['supplier_name'],
                    "type" => $record['type'],
                    "item_id" => $record['item_id'],
                    "item_number" => $record['item_number'],
                    "item_name" => $record['item_name'],
                    "mpq" => $record['mpq'],
                    "moq" => $record['moq'],
                    "price" => $record['price'],
                    "currency" => $record['currency'],
                    "remarks" => $record['remarks'],
                    "purchase" => $record['purchase'],
                    "safety_stock" => $record['safety_stock'],
                    "calculate" => $record['calculate'],
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
                $post   = $this->input->post();
                $supplier_items = $this->crud->read('supplier_items', [], ["supplier_id" => $post['supplier_id'], "item_id" => $post['item_id']]);
                if (!$supplier_items) {
                    $send   = $this->crud->create('supplier_items', $post);
                    echo $send;
                } else {
                    show_error("Duplicate");
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
            $send = $this->crud->update('supplier_items', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('supplier_items', $data);
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
                'supplier_number' => $data->val($i, 3),
                'mpq' => $data->val($i, 4),
                'moq' => $data->val($i, 5),
                'price' => $data->val($i, 6),
                'remarks' => $data->val($i, 7)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('excel/failed/supplier_items.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/supplier_items.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/supplier_items.txt";
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
            $item = $this->crud->read('items', [], ["number" => $data['item_number']]);
            $supplier = $this->crud->read('suppliers', [], ["number" => $data['supplier_number']]);
            $supplier_items = $this->crud->read('supplier_items', [], ["item_id" => $item->id, "supplier_id" => $supplier->id]);
            if (empty($item->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['item_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($supplier->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Supplier " . $data['supplier_number'] . " Not Found", "theme" => "error"));
            } elseif (!empty($supplier_items->item_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['item_number'] . " Duplicate Data", "theme" => "error"));
            } else {
                $postFinal = array(
                    "item_id" => $item->id,
                    "supplier_id" => $supplier->id,
                    "mpq" => $data['mpq'],
                    "moq" => $data['moq'],
                    "price" => $data['price'],
                    "remarks" => $data['remarks']
                );
                $send   = $this->crud->create('supplier_items', $postFinal);
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
            header("Content-Disposition: attachment; filename=supplier_items_$format.xls");
        }
        $filter_suppliers  = base64_decode($this->input->get('filter_suppliers'));
        $filter_items = base64_decode($this->input->get('filter_items'));
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, c.number as item_id, c.name as item_name, b.name as supplier_name, b.type, b.currency');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'b.id = a.supplier_id');
        $this->db->join('items c', 'c.id = a.item_id');
        $this->db->where('a.deleted', 0);
        $this->db->like('a.supplier_id', $filter_suppliers);
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
                            <small>MASTER SUPPLIER ITEM</small>
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
                    <th>Supplier</th>
                    <th>Type</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>MPQ</th>
                    <th>MOQ</th>
                    <th>Price</th>
                    <th>Currency</th>
                    <th>Purchase (Days)</th>
                    <th>Safety Stock (%)</th>
                    <th>Calculate</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['type'] . '</td>
                        <td>' . $data['item_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . $data['mpq'] . '</td>
                        <td>' . $data['mpq'] . '</td>
                        <td>' . $data['price'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . $data['purchase'] . '</td>
                        <td>' . $data['safety_stock'] . '</td>
                        <td>' . $data['calculate'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
