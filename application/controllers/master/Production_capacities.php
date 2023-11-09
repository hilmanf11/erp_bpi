<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Production_capacities extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/production_capacities');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('production_capacities', ["name" => $post]);
        echo json_encode($send);
    }

    //GET DATA
    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT distinct a.item_fg_id, b.number as item_fg_number, b.name as item_fg_name
            FROM menu_loadings a 
            JOIN item_fg b ON a.item_fg_id = b.id
            WHERE b.number LIKE '%$post%' or b.name LIKE '%$post%'");
        echo json_encode($send);
    }

    public function readMachines($item_fg_id)
    {
        $send = $this->crud->query("SELECT a.machine_id, b.number as machine_number, a.cycle_time, a.productcivity, c.cavity_actual, a.shift
            FROM menu_loadings a 
            JOIN machines b ON a.machine_id = b.id
            JOIN molds c ON a.mold_id = c.id
            ORDER BY b.number ASC");
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
            $this->db->select('a.*,b.number as item_fg_number, b.name as item_fg_name, c.number as machine_number, d.cycle_time, d.productcivity, e.cavity_actual');
            $this->db->from('production_capacities a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('machines c', 'a.machine_id = c.id');
            $this->db->join('menu_loadings d', 'd.machine_id = c.id');
            $this->db->join('molds e', 'd.mold_id = e.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "item_fg_id"){
                        $this->db->like("b.id", $filter->value);
                    }elseif($filter->field == "item_fg_name"){
                        $this->db->like("b.name", $filter->value);
                    }elseif($filter->field == "machine_number"){
                        $this->db->like("c.number", $filter->value);
                    }elseif($filter->field == "cycle_time"){
                        $this->db->like("d.cycle_time", $filter->value);
                    }elseif($filter->field == "productcivity"){
                        $this->db->like("d.productcivity", $filter->value);
                    }elseif($filter->field == "cavity_actual"){
                        $this->db->like("e.cavity_actual", $filter->value);
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
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
                $post   = $this->input->post();
                $send   = $this->crud->create('production_capacities', $post);
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
            $send = $this->crud->update('production_capacities', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('production_capacities', $data);
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
                'number' => $data->val($i, 2),
                'name' => $data->val($i, 3),
                'uom' => $data->val($i, 4),
                'item_category_id' => $data->val($i, 5),
                'item_family_id' => $data->val($i, 6),
                'item_sub_family_id' => $data->val($i, 7),
                'account_number' => $data->val($i, 8),
                'account_name' => $data->val($i, 9),
                'status' => $data->val($i, 10)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/item_rm.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/item_rm.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/item_rm.txt";
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
                $item_rm = $this->crud->read('item_rm', [], ["number" => $data['number']]);
                $category = $this->crud->read('item_categories', [], ["id" => $data['item_category_id']]);
                $product_family = $this->crud->read('item_familys', [], ["id" => $data['item_family_id']]);
                $product_family_sub = $this->crud->read('item_family_subs', [], ["id" => $data['item_sub_family_id']]);


                if (empty($category->number)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Category " . $data['item_category_id'] . " Not Found", "theme" => "error"));
                } elseif (empty($product_family->number)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Product Family " . $data['item_family_id'] . " Not Found", "theme" => "error"));
                } elseif (empty($product_family_sub->name)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Product Family Sub " . $data['item_sub_family_id'] . " Not Found", "theme" => "error"));
                } elseif (!empty($item_rm->number)) {
                    echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['number'] . " is Duplicate Data", "theme" => "error"));
                } else {
                    $dataFinal = array(
                        //field
                        "id" => $autoid,
                        "number" => $data['number'],
                        "name" => $data['name'],
                        "uom" => $data['uom'],
                        "item_category_id" => $data['item_category_id'],
                        "item_family_id" => $data['item_family_id'],
                        "item_sub_family_id" => $data['item_sub_family_id'],
                        "account_number" => $data['account_number'],
                        "account_name" => $data['account_name'],
                        "status" => $data['status'],
                    );
                    $send   = $this->crud->create('item_rm', $dataFinal);
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
            header("Content-Disposition: attachment; filename=production_capacities_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*,b.number as item_fg_number, b.name as item_fg_name, c.number as machine_number, d.cycle_time, d.productcivity, e.cavity_actual');
        $this->db->from('production_capacities a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('machines c', 'a.machine_id = c.id');
        $this->db->join('menu_loadings d', 'd.machine_id = c.id');
        $this->db->join('molds e', 'd.mold_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('id', 'ASC');
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
                <h3>PRODUCTION CAPACITIES</h3>
            </div>
        </center>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product No</th>
                <th>Machine No</th>
                <th>Product Name</th>
                <th>Cycle Time</th>
                <th>Productivity</th>
                <th>Cavity Actual</th>
                <th>Capacity/Hour</th>
                <th>Capacity/Shift</th>
                <th>Capacity/Day</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['item_fg_id'] . '</td>
                    <td>' . $data['machine_number'] . '</td>
                    <td>' . $data['item_fg_name'] . '</td>
                    <td>' . $data['cycle_time'] . '</td>
                    <td>' . $data['productcivity'] . '</td>
                    <td>' . $data['cavity_actual'] . '</td>
                    <td>' . $data['capacity_hour'] . '</td>
                    <td>' . $data['capacity_shift'] . '</td>
                    <td>' . $data['capacity_day'] . '</td>
                    <td>' . $data['remarks'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
