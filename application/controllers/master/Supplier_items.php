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
        $this->form_validation->set_rules('supplier_id', 'Supplier', 'required|min_length[1]|max_length[20]|is_unique[supplier_items.supplier_id]');
        $this->form_validation->set_rules('item_rm_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[supplier_items.item_rm_id]');
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
        $send = $this->crud->reads('supplier_items', ["supplier_id" => $post]);
        echo json_encode($send);
    }

    public function readItems()
    {
       $post = isset($_POST['q']) ? $_POST['q'] : "";
       $item_family_id = explode(",", $this->input->get('item_family_id'));
       
       $this->db->select('a.*, b.currency, c.number as item_number, c.name as item_name');//c.specification
       $this->db->from('supplier_items a');
       $this->db->join('suppliers b', 'a.supplier_id = b.id');
       $this->db->join('item_rm c', 'a.item_rm_id = c.id');
       $this->db->where_in('c.item_family_id', $item_family_id);
       $this->db->like('c.number', $post);
       $this->db->group_by('a.id');
       $this->db->order_by('a.id', 'ASC');
       $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readItem()
    {
       $post = isset($_POST['q']) ? $_POST['q'] : "";
       $supplier_id = $this->input->get('supplier_id');

       $this->db->select('a.*, b.currency, c.number as item_number, c.name as item_name');//c.specification
       $this->db->from('supplier_items a');
       $this->db->join('suppliers b', 'a.supplier_id = b.id');
       $this->db->join('item_rm c', 'a.item_rm_id = c.id');
       $this->db->where('a.supplier_id', $supplier_id);
       $this->db->like('c.number', $post);
       $this->db->group_by('a.id');
       $this->db->order_by('a.id', 'ASC');
       $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readSuppliers()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $item_number = $this->input->get('item_number');
        $item_id = $this->input->get('item_rm_id');
        $item_family_id = $this->input->get('item_family_id');

        $this->db->select('b.*, c.number as item_number, a.mpq, a.moq, a.price, a.share_order');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->like("c.number", $item_number);
        $this->db->like("c.id", $item_id);
        $this->db->like("d.id", $item_family_id);
        $this->db->like("b.name", $post);
        $this->db->group_by('b.number');
        $this->db->order_by('b.name', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_supplier_id = @base64_decode($get['filter_supplier_id']);
            $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('b.id as supplier_id, b.number as supplier_number, b.name as supplier_name, b.currency, b.type, b.status, a.created_by, a.created_date, a.updated_by, a.updated_date');
            $this->db->from('supplier_items a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->like('a.supplier_id', $filter_supplier_id);
            $this->db->like('a.item_rm_id', $filter_item_rm_id);
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
            $filter_supplier_id = base64_decode($this->input->get('filter_supplier_id'));

            $this->db->select('a.*, b.number as supplier_number, b.name as supplier_name, c.number as item_rm_number, c.item_family_id as item_rm_family, d.name as item_family_name');
            $this->db->from('supplier_items a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('item_rm c', 'a.item_rm_id = c.id');
            $this->db->join('item_familys d', 'c.item_family_id = d.id');
            $this->db->where('b.number', $number);
            $this->db->like('a.supplier_id', $filter_supplier_id);
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATA TABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $supplier_id = base64_decode($this->input->get('supplier_id'));

            $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, b.item_family_id as item_rm_family, c.currency as supplier_currency, d.name as item_family_name');
            $this->db->from('supplier_items a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->join('item_familys d', 'b.item_family_id = d.id');
            $this->db->where('a.supplier_id', $supplier_id);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // UPDATE DATA
    public function datatableHistories()
    {
        if ($this->input->get()) {
            $supplier_id = base64_decode($this->input->get('supplier_id'));
            $item_rm_id = base64_decode($this->input->get('item_rm_id'));

            $this->db->select('*');
            $this->db->from('supplier_item_histories');
            $this->db->where('supplier_id', $supplier_id);
            $this->db->where('item_rm_id', $item_rm_id);
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

            $supplier_items = $this->crud->read("supplier_items", [], ["supplier_id" => $post['supplier_id'], "item_rm_id" => $post['item_rm_id']]);
            $supplier_item_histories = $this->crud->read("supplier_item_histories", [], ["supplier_id" => $post['supplier_id'], "item_rm_id" => $post['item_rm_id'], "price" => $post['price']]);
            
            if (@$supplier_items->supplier_id != "") {
                $send = $this->crud->update('supplier_items', ["supplier_id" => $post['supplier_id'], "item_rm_id" => $post['item_rm_id']], $post);
                if (@$supplier_item_histories->supplier_id == "") {
                    $send2 = $this->crud->create('supplier_item_histories', $post);
                }
            } else {
                $send = $this->crud->create('supplier_items', $post);
                $send2 = $this->crud->create('supplier_item_histories', $post);
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
                //excel
                'supplier_id' => $data->val($i, 2),
                'item_rm_id' => $data->val($i, 3),
                'maker' => $data->val($i, 4),
                'item_supplier' => $data->val($i, 5),
                'mpq' => $data->val($i, 6),
                'moq' => $data->val($i, 7),
                'share_order' => $data->val($i, 8),
                'leadtime' => $data->val($i, 9),
                'currency' => $data->val($i, 10),
                'price' => $data->val($i, 11),
                'valid_date' => $data->val($i, 12),
                'safety_stock' => $data->val($i, 13),
                'calculate' => $data->val($i, 14)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/supplier_items.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/supplier_items.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/supplier_items.txt";
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

            $dataFinal = array(
                //field
                "supplier_id" => $data['supplier_id'],
                "item_rm_id" => $data['item_rm_id'],
                "maker" => $data['maker'],
                "item_supplier" => $data['item_supplier'],
                "mpq" => $data['mpq'],
                "moq" => $data['moq'],
                "share_order" => $data['share_order'],
                "leadtime" => $data['leadtime'],
                "currency" => $data['currency'],
                "price" => $data['price'],
                "valid_date" => $data['valid_date'],
                "safety_stock" => $data['safety_stock'],
                "calculate" => $data['calculate'],
            );

            //Cek Process Number          //table       //field        //field excel
            $supplier_items = $this->crud->read('supplier_items', [], ["supplier_id" => $data['supplier_id'], "item_rm_id" => $data['item_rm_id']]);
            $supplier = $this->crud->read('suppliers', [], ["id" => $data['supplier_id']]);
            $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);
            $supplier_item_histories = $this->crud->read("supplier_item_histories", [], ["supplier_id" => $data['supplier_id'], "item_rm_id" => $data['item_rm_id'], "price" => $data['price']]);


            if (@$supplier_items->supplier_id != "") {
                $send = $this->crud->update('supplier_items', ["supplier_id" => $dataFinal['supplier_id'], "item_rm_id" => $dataFinal['item_rm_id']], $dataFinal);
                if (@$supplier_item_histories->supplier_id == "") {
                    $send2 = $this->crud->create('supplier_item_histories', $dataFinal);
                }
            } else {
                $send = $this->crud->create('supplier_items', $dataFinal);
                $send2 = $this->crud->create('supplier_item_histories', $dataFinal);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
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

        $get = $this->input->get();
        $filter_supplier_id = @base64_decode($get['filter_supplier_id']);
        $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as supplier_number, b.name as supplier_name, b.currency as supplier_currency, c.number as item_rm_number, c.name as item_rm_name, c.item_family_id as item_rm_family, d.name as item_family_name');
        $this->db->from('supplier_items a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->like('a.supplier_id', $filter_supplier_id);
        $this->db->like('a.item_rm_id', $filter_item_rm_id);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#supplier_items {border-collapse: collapse;width: 100%;font-size: 12px;}#supplier_items td, #supplier_items th {border: 1px solid #ddd;padding: 2px;}#supplier_items tr:nth-child(even){background-color: #f2f2f2;}#supplier_items tr:hover {background-color: #ddd;}#supplier_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER SUPPLIER ITEM</h3>
            </div>
        </center>
        
        <table id="supplier_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Supplier ID</th>
                <th>Supplier Code</th>
                <th>Supplier Name</th>
                <th>Part ID</th>
                <th>Part No.</th>
                <th>Part Name</th>
                <th>Maker</th>
                <th>Product Family</th>
                <th>MPQ</th>
                <th>MOQ</th>
                <th>Share Order %</th>
                <th>Lead Time (Days)</th>
                <th>Currency</th>
                <th>Price</th>
                <th>Valid Date</th>
                <th>Safety Stock</th>
                <th>Calculate</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['supplier_id'] . '</td>
                    <td>' . $data['supplier_number'] . '</td>
                    <td>' . $data['supplier_name'] . '</td>
                    <td>' . $data['item_rm_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_number'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_rm_name'] . '</td>
                    <td>' . $data['maker'] . '</td>
                    <td>' . $data['item_family_name'] . '</td>
                    <td>' . $data['mpq'] . '</td>
                    <td>' . $data['moq'] . '</td>
                    <td>' . $data['share_order'] . '</td>
                    <td>' . $data['leadtime'] . '</td>
                    <td>' . $data['supplier_currency'] . '</td>
                    <td>' . $data['price'] . '</td>
                    <td>' . $data['valid_date'] . '</td>
                    <td>' . $data['safety_stock'] . '</td>
                    <td>' . $data['calculate'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
