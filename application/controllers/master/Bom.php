<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Bom extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/bom');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('bom', ["name" => $post]);
        echo json_encode($send);
    }
    
    public function readOperations()
    {
        $query = $this->db->query('SELECT DISTINCT operation FROM bom ORDER BY operation ASC');
        $data = $query->result_array();
        echo json_encode($data);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_customers  = base64_decode($this->input->get('filter_customers'));
            $filter_items = base64_decode($this->input->get('filter_items'));
            $filter_components = base64_decode($this->input->get('filter_components'));

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, c.number as item_number, c.name as item_name, d.number as component_number, d.name as component_name, e.name as uom');
            $this->db->from('bom a');
            $this->db->join('items c', 'a.item_id = c.id');
            $this->db->join('items d', 'a.component_id = d.id');
            $this->db->join('uom e', 'd.uom_id = e.id');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.item_id', $filter_items);
            $this->db->like('a.component_id', $filter_components);
            $this->db->order_by('c.number', 'ASC');
            $this->db->order_by('d.number', 'ASC');
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

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $customer = $this->crud->read("customer_items", [], ["item_id" => $post['item_id']]);
                $bom    = $this->crud->reads("bom", [], ["item_id" => $post['item_id'], "component_id" => $post['component_id']]);
                if (count($bom) > 0) {
                    show_error("Duplicate Product No and Component No");
                } else {
                    $send   = $this->crud->create('bom', array_merge($post, ["customer_id" => @$customer->customer_id]));
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
            $customer = $this->crud->read("customer_items", [], ["item_id" => $post['item_id']]);
            $send = $this->crud->update('bom', ["id" => $id], array_merge($post, ["customer_id" => @$customer->customer_id]));
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('bom', $data);
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
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                'customer_number' => $data->val($i, 2),
                'item_number' => $data->val($i, 3),
                'component_number' => $data->val($i, 4),
                'qpa' => $data->val($i, 5),
                'operation' => $data->val($i, 6),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('excel/failed/bom.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/bom.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/bom.txt";
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
            $component = $this->crud->read('items', [], ["number" => $data['component_number']]);
            $customer = $this->crud->read('customers', [], ["number" => $data['customer_number']]);
            $bom    = $this->crud->reads("bom", [], ["item_id" => $item->id, "component_id" => $component->id]);
            if (empty($item->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['item_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($component->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Component No " . $data['component_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($customer->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Customer" . $data['customer_number'] . " Not Found", "theme" => "error"));
            } elseif (count($bom) > 0) {
                echo json_encode(array("title" => "Duplicate", "message" => "Product No " . $data['item_number'] . " and Component No " . $data['component_number'] . " Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    "customer_id" => @$customer->id,
                    "item_id" => $item->id,
                    "component_id" => $component->id,
                    "qpa" => $data['qpa'],
                    "operation" => $data['operation'],
                );
                $send   = $this->crud->create('bom', $dataFinal);
                echo $send;
            }
        }
    }
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=bom_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $filter_customers  = base64_decode($this->input->get('filter_customers'));
        $filter_items = base64_decode($this->input->get('filter_items'));
        $filter_components = base64_decode($this->input->get('filter_components'));

        $this->db->select('a.*, b.number as customer_number, b.name as customer_name, c.number as item_number, c.name as item_name, d.number as component_number, d.name as component_name, e.name as uom');
        $this->db->from('bom a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join('items d', 'a.component_id = d.id');
        $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->like('a.customer_id', $filter_customers);
        $this->db->like('a.item_id', $filter_items);
        $this->db->like('a.component_id', $filter_components);
        $this->db->order_by('c.number', 'ASC');
        $this->db->order_by('d.number', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="'  . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>BILL OF MATERIAL</small>
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
                <th>Product No</th>
                <th>Product Name</th>
                <th>Component No</th>
                <th>Component Name</th>
                <th>QPA</th>
                <th>UOM</th>
                <th>Operation</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['item_number'] . '</td>
                    <td>' . $data['item_name'] . '</td>
                    <td>' . $data['component_number'] . '</td>
                    <td>' . $data['component_name'] . '</td>
                    <td>' . $data['qpa'] . '</td>
                    <td>' . $data['uom'] . '</td>
                    <td>' . $data['operation'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
