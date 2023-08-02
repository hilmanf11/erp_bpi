<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Items extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Code', 'required|min_length[1]|max_length[20]|is_unique[items.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/items');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads($item_family_id = "")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->db->select('a.*, b.name as item_family_name, c.name as uom');
        $this->db->from('items a');
        $this->db->join('item_familys b', 'b.id = a.item_family_id');
        $this->db->join('uom c', 'a.uom_id = c.id');
        $this->db->where('a.deleted', 0);
        if ($item_family_id != "") {
            $this->db->where("b.number", $item_family_id);
        }
        $this->db->like('a.number', $post);
        $this->db->order_by('a.number', 'asc');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }
    //GET DATA NOT SUPPLY
    public function readSupply($supply = 1)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->db->select('a.id, a.number, a.name, b.name as item_family_name, c.name as uom');
        $this->db->from('items a');
        $this->db->join('item_familys b', 'b.id = a.item_family_id');
        $this->db->join('uom c', 'a.uom_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->where("a.supply", $supply);
        $this->db->group_start();
        $this->db->like('a.number', $post);
        $this->db->or_like('a.name', $post);
        $this->db->group_end();
        $this->db->order_by('a.name', 'asc');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }
    //GET DATA NOT FINISH GOOD
    public function readNotFg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->db->select('a.id, a.number, a.name, b.name as item_family_name, c.name as uom');
        $this->db->from('items a');
        $this->db->join('item_familys b', 'b.id = a.item_family_id');
        $this->db->join('uom c', 'a.uom_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->where("b.number !=", "001");
        $this->db->like('a.number', $post);
        $this->db->order_by('a.name', 'asc');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }
    //GET AUTOMATIC CODE FG
    public function itemNumberFg($noid)
    {
        $sqlGetID   = $this->db->query("SELECT max(`number`)  as kode FROM items WHERE `number` like '%$noid%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $noid . $autoID;
    }
    //GET YEARS
    public function years()
    {
        $yearFrom = date('Y', strtotime(date("Y-m-d") . ' -5 years'));
        $yearTo = date('Y', strtotime(date("Y-m-d") . ' +5 years'));
        for ($i = $yearFrom; $i < $yearTo; $i++) {
            $arr[] = array("number" => $i);
        }
        echo json_encode($arr);
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
            $this->db->select('a.*, b.name as item_family_name, c.name as uom, d.account_number, d.account_name');
            $this->db->from('items a');
            $this->db->join('item_familys b', 'b.id = a.item_family_id');
            $this->db->join('uom c', 'c.id = a.uom_id');
            $this->db->join('account_coa d', 'b.account_number = d.account_number', 'left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "number") {
                        $this->db->like("a.number", $filter->value);
                    } elseif ($filter->field == "name") {
                        $this->db->like("a.name", $filter->value);
                    } elseif ($filter->field == "item_family_name") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "description") {
                        $this->db->like("a.description", $filter->value);
                    } elseif ($filter->field == "leadtime") {
                        $this->db->like("a.leadtime", $filter->value);
                    } elseif ($filter->field == "uom") {
                        $this->db->like("a.uom", $filter->value);
                    } elseif ($filter->field == "created_name") {
                        $this->db->like("a.created_by", $filter->value);
                    } elseif ($filter->field == "created_date") {
                        $this->db->like("a.created_date", $filter->value);
                    } elseif ($filter->field == "updated_by") {
                        $this->db->like("a.updated_by", $filter->value);
                    } elseif ($filter->field == "updated_date") {
                        $this->db->like("a.updated_date", $filter->value);
                    } elseif ($filter->field == "status") {
                        $this->db->like("a.status", $filter->value);
                    } elseif ($filter->field == "account_name") {
                        $this->db->like("d.account_name", $filter->value);
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
                $send   = $this->crud->create('items', $post);
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
            $send = $this->crud->update('items', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('items', $data);
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
                'item_family_number' => $data->val($i, 2),
                'item_category_number' => $data->val($i, 3),
                'number' => $data->val($i, 4),
                'name' => $data->val($i, 5),
                'description' => $data->val($i, 6),
                'leadtime' => $data->val($i, 7),
                'box' => $data->val($i, 8),
                'lot' => $data->val($i, 9),
                'uom_number' => $data->val($i, 10)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('excel/failed/items.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/items.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/items.txt";
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
            $data = $this->input->post('data');
            $item_family = $this->crud->read('item_familys', [], ["number" => $data['item_family_number']]);
            $category = $this->crud->read('item_categories', [], ["number" => $data['item_category_number']]);
            $unit_of_measure = $this->crud->read('uom', [], ["number" => $data['uom_number']]);
            if (empty($item_family->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product Family " . $data['item_family_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($category->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Category " . $data['item_category_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($unit_of_measure->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Unit of Measure " . $data['uom_number'] . " Not Found", "theme" => "error"));
            } else {
                $send   = $this->crud->create('items', array_merge($data, ["item_family_id" => $item_family->id, "item_category_id" => $category->id, "uom_id" => $unit_of_measure->id]));
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
            header("Content-Disposition: attachment; filename=items_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        
        $this->db->select('a.*, b.name as item_family_name, c.name as uom, d.account_number, d.account_name');
        $this->db->from('items a');
        $this->db->join('item_familys b', 'b.id = a.item_family_id');
        $this->db->join('uom c', 'c.id = a.uom_id');
        $this->db->join('account_coa d', 'b.account_number = d.account_number', 'left');
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
                            <small>MASTER ITEM</small>
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
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Specification</th>
                <th>Account No</th>
                <th>Account Name</th>
                <th>Leadtime</th>
                <th>Box of Delivery</th>
                <th>Lots</th>
                <th>UoM</th>
                <th>Product Family</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['description'] . '</td>
                    <td>' . $data['account_number'] . '</td>
                    <td>' . $data['account_name'] . '</td>
                    <td>' . $data['leadtime'] . '</td>
                    <td>' . $data['box'] . '</td>
                    <td>' . $data['lot'] . '</td>
                    <td>' . $data['uom'] . '</td>
                    <td>' . $data['item_family_name'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
