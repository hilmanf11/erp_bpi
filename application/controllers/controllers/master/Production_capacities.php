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
        $send = $this->crud->query("SELECT distinct a.item_fg_id, d.number as item_fg_number, d.name as item_fg_name, 
        a.machine_id, b.number as machine_number, a.cycle_time, a.item_fg_id, a.productcivity, c.cavity_actual, a.shift, a.shift_hour
            FROM menu_loadings a 
            JOIN machines b ON a.machine_id = b.id
            JOIN molds c ON a.mold_id = c.id
            JOIN item_fg d ON a.item_fg_id = d.id
            WHERE d.number LIKE '%$post%' or d.name LIKE '%$post%'");
        echo json_encode($send);
    }

    // public function readMachines($item_fg_id)
    // {
    //     $send = $this->crud->query("SELECT a.machine_id, b.number as machine_number, a.cycle_time, a.item_fg_id, a.productcivity, c.cavity_actual, a.shift, a.shift_hour
    //         FROM menu_loadings a 
    //         JOIN machines b ON a.machine_id = b.id
    //         JOIN molds c ON a.mold_id = c.id
    //         JOIN item_fg d ON a.item_fg_id = d.id
    //         ORDER BY b.number ASC");
    //     echo json_encode($send);
    // }

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
            $production_capacities = $this->crud->read('production_capacities', [], ["item_fg_id" => $post['item_fg_id'],"machine_id" => $post['machine_id']]);
            $machines = $this->crud->read('machines', [], ["id" => $post['machine_id']]);
            $item_fg = $this->crud->read('item_fg', [], ["id" => $post['item_fg_id']]);

           if (!empty($production_capacities->item_fg_id)) {
               echo json_encode(array("title" => "Duplicated", "message" => "Product Id " . $item_fg->number ." & Machine Id " . $machines->number . " Duplicate Data", "theme" => "error"));
           } else {
               $send   = $this->crud->create('production_capacities', $post);
               echo $send;
           }
       } else {
           show_error("Cannot Process your request");
   }
    }
    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            
            $id = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $existing_data = $this->crud->read('production_capacities', [], ["id" => $id]); // Membaca data yang ada

            // Periksa apakah item_fg_id dan machine_id tetap sama
            if (
                ($existing_data->item_fg_id == $post['item_fg_id']) &&
                ($existing_data->machine_id == $post['machine_id'])
            ) {
                // Item_fg_id dan machine_id tetap sama, lanjutkan dengan pembaruan
                $send = $this->crud->update('production_capacities', ["id" => $id], $post);
                echo $send;
            } else {
                // Item_fg_id atau machine_id telah berubah, lakukan validasi duplikasi
                $production_capacities = $this->crud->read('production_capacities', [], ["item_fg_id" => $post['item_fg_id'], "machine_id" => $post['machine_id']]);
                $machines = $this->crud->read('machines', [], ["id" => $post['machine_id']]);
                $item_fg = $this->crud->read('item_fg', [], ["id" => $post['item_fg_id']]);
                if (!empty($production_capacities->item_fg_id)) {
                    echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $item_fg->number ." & Machine No " . $machines->number . " Duplicate Data", "theme" => "error"));
                } else {
                    // Tidak ada duplikasi, lanjutkan dengan pembaruan
                    $send = $this->crud->update('production_capacities', ["id" => $id], $post);
                    echo $send;
                }
            }
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
                'item_fg_id' => $data->val($i, 2),
                'machine_id' => $data->val($i, 3),
                'remarks' => $data->val($i, 4)
                
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/production_capacities.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/production_capacities.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/production_capacities.txt";
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
                $menu_loading = $this->crud->read('menu_loadings', [], ["item_fg_id" => $data['item_fg_id'], 'machine_id' => $data['machine_id']]);
                $production_capacities = $this->crud->read('production_capacities', [], ["item_fg_id" => $data['item_fg_id'],"machine_id" => $data['machine_id']]);

                if (empty($menu_loading->id)) {
                    echo json_encode(array("title" => "Not Found", "message" => "Product Id" . $data['item_fg_id'] . " & Machine Id " . $data['machine_id'] . " Not Found in Menu Loading", "theme" => "error"));
                } elseif (!empty($production_capacities->item_fg_id)) {
                    echo json_encode(array("title" => "Duplicated", "message" => "Product Id " . $data['item_fg_id'] ." & Machine Id " . $data['machine_id'] . " Duplicate Data", "theme" => "error"));
                } else {

                    $mold = $this->crud->read('molds', [], ["id" => $menu_loading->mold_id]);


                    // Hitung nilai untuk field capacity
                    $cycle_time = $menu_loading->cycle_time;
                    $productcivity = $menu_loading->productcivity;
                    $actual_cavity = $mold->cavity_actual;
                    $shift = $menu_loading->shift;
                    $shift_hour = $menu_loading->shift_hour;
                    
                    $capacity_hour = (3600 / $cycle_time) * $actual_cavity * ($productcivity / 100);
                    $capacity_shift = ($capacity_hour * $capacity_hour);
                    $capacity_day = (($capacity_shift * $capacity_hour) * $shift_hour * $shift );

                    $dataFinal = array(
                        //field
                        "item_fg_id" => $data['item_fg_id'],
                        "machine_id" => $data['machine_id'],
                        "capacity_hour" => $capacity_hour,
                        "capacity_shift" => $capacity_shift,
                        "capacity_day" => $capacity_day,
                        "remarks" => $data['remarks'],
                    );
                    $send   = $this->crud->create('production_capacities', $dataFinal);
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
