<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Menu_loading_subconts extends CI_Controller
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
        $this->form_validation->set_rules('subcont_id', 'Subcont', 'required|min_length[1]|max_length[20]|is_unique[menu_loading_subconts.subcont_id]');
        $this->form_validation->set_rules('machine_id', 'Machine No.', 'required|min_length[1]|max_length[20]|is_unique[menu_loading_subconts.machine_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/menu_loading_subconts');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('menu_loading_subconts', ["subcont_id" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_subcont_id = @base64_decode($get['filter_subcont_id']);
            $filter_machine_id = @base64_decode($get['filter_machine_id']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('b.id as subcont_id, b.number as subcont_number, b.name as subcont_name, b.status, a.created_by, a.created_date, a.updated_by, a.updated_date');
            $this->db->from('menu_loading_subconts a');
            $this->db->join('subconts b', 'a.subcont_id = b.id');
            $this->db->like('a.subcont_id', $filter_subcont_id);
            $this->db->like('a.machine_id', $filter_machine_id);
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
            $filter_subcont_id = base64_decode($this->input->get('filter_subcont_id'));

            $this->db->select('a.*, b.number as subcont_number, b.name as subcont_name, c.number as machine_number, c.name as machine_name, c.specification as machine_spec, c.maker as machine_maker, c.toonage as machine_toonage, c.tiebar as machine_tiebar, c.uom_tiebar as machine_uom_tiebar, c.min_closing as machine_min_close, c.uom_min as machine_uom_min_close, c.max_open as machine_max_open, c.uom_max as machine_uom_max_open, c.volume as machine_volume, c.uom_volume as machine_uom_volume, c.diameter as machine_diameter, c.uom_diameter as machine_uom_diameter, c.brand as machine_brand, c.status as machine_status');
            $this->db->from('menu_loading_subconts a');
            $this->db->join('subconts b', 'a.subcont_id = b.id');
            $this->db->join('machines c', 'a.machine_id = c.id');
            $this->db->where('b.number', $number);
            $this->db->like('a.subcont_id', $filter_subcont_id);
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
            $subcont_id = base64_decode($this->input->get('subcont_id'));

            $this->db->select('a.*, b.number as subcont_number, b.name as subcont_name, c.number as machine_number, c.name as machine_name, c.specification as machine_spec, c.maker as machine_maker, c.toonage as machine_toonage, c.tiebar as machine_tiebar, c.uom_tiebar as machine_uom_tiebar, c.min_closing as machine_min_close, c.uom_min as machine_uom_min_close, c.max_open as machine_max_open, c.uom_max as machine_uom_max_open, c.volume as machine_volume, c.uom_volume as machine_uom_volume, c.diameter as machine_diameter, c.uom_diameter as machine_uom_diameter, c.brand as machine_brand, c.status as machine_status');
            $this->db->from('menu_loading_subconts a');
            $this->db->join('subconts b', 'a.subcont_id = b.id');
            $this->db->join('machines c', 'a.machine_id = c.id');
            $this->db->where('a.subcont_id', $subcont_id);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // UPDATE DATA
    // public function datatableHistories()
    // {
    //     if ($this->input->get()) {
    //         $subcont_id = base64_decode($this->input->get('subcont_id'));
    //         $machine_id = base64_decode($this->input->get('machine_id'));

    //         $this->db->select('*');
    //         $this->db->from('supplier_item_histories');
    //         $this->db->where('subcont_id', $subcont_id);
    //         $this->db->where('machine_id', $machine_id);
    //         $this->db->order_by('valid_date', 'DESC');
    //         $records = $this->db->get()->result_array();

    //         echo json_encode($records);
    //     }
    // }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $menu_loading_subconts = $this->crud->read("menu_loading_subconts", [], ["subcont_id" => $post['subcont_id'], "machine_id" => $post['machine_id']]);
            // $supplier_item_histories = $this->crud->read("supplier_item_histories", [], ["subcont_id" => $post['subcont_id'], "machine_id" => $post['machine_id'], "price" => $post['price']]);
            if (@$menu_loading_subconts->subcont_id != "") {
                $send = $this->crud->update('menu_loading_subconts', ["subcont_id" => $post['subcont_id'], "machine_id" => $post['machine_id']], $post);
                // if (@$supplier_item_histories->subcont_id == "") {
                //     $send2 = $this->crud->create('supplier_item_histories', $post);
                // }
            } else {
                $send = $this->crud->create('menu_loading_subconts', $post);
                // $send2 = $this->crud->create('supplier_item_histories', $post);
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
        $send = $this->crud->delete('menu_loading_subconts', $data);
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
                'subcont_id' => $data->val($i, 2),
                'machine_id' => $data->val($i, 3),
                'capacity' => $data->val($i, 4)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/menu_loading_subconts.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/menu_loading_subconts.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/menu_loading_subconts.txt";
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
            $menu_loading_subconts = $this->crud->read('menu_loading_subconts', [], ["subcont_id" => $data['subcont_id'], "machine_id" => $data['machine_id']]);

            if (!empty($menu_loading_subconts->subcont_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Subcont " . $data['subcont_id'] . " is Duplicate Data", "theme" => "error"));
            } elseif (!empty($menu_loading_subconts->machine_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Machine No. " . $data['machine_id'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "subcont_id" => $data['subcont_id'],
                    "machine_id" => $data['machine_id'],
                    "capacity" => $data['capacity'],
                );
                $send   = $this->crud->create('menu_loading_subconts', $dataFinal);
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
            header("Content-Disposition: attachment; filename=menu_loading_subconts_$format.xls");
        }

        $get = $this->input->get();
        $filter_subcont_id = @base64_decode($get['filter_subcont_id']);
        $filter_machine_id = @base64_decode($get['filter_machine_id']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as subcont_number, b.name as subcont_name, c.number as machine_number, c.name as machine_name, c.specification as machine_spec, c.maker as machine_maker, c.toonage as machine_toonage, c.tiebar as machine_tiebar, c.uom_tiebar as machine_uom_tiebar, c.min_closing as machine_min_close, c.uom_min as machine_uom_min_close, c.max_open as machine_max_open, c.uom_max as machine_uom_max_open, c.volume as machine_volume, c.uom_volume as machine_uom_volume, c.diameter as machine_diameter, c.uom_diameter as machine_uom_diameter, c.brand as machine_brand, c.status as machine_status');
        $this->db->from('menu_loading_subconts a');
        $this->db->join('subconts b', 'a.subcont_id = b.id');
        $this->db->join('machines c', 'a.machine_id = c.id');
        $this->db->like('a.subcont_id', $filter_subcont_id);
        $this->db->like('a.machine_id', $filter_machine_id);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#menu_loading_subconts {border-collapse: collapse;width: 100%;font-size: 12px;}#menu_loading_subconts td, #menu_loading_subconts th {border: 1px solid #ddd;padding: 2px;}#menu_loading_subconts tr:nth-child(even){background-color: #f2f2f2;}#menu_loading_subconts tr:hover {background-color: #ddd;}#menu_loading_subconts th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER MENU LOADING SUBCONT</h3>
            </div>
        </center>
        
        <table id="menu_loading_subconts" border="1">
            <tr>
                <th width="20">No</th>
                <th>Subcont ID</th>
                <th>Subcont Code</th>
                <th>Subcont Name</th>
                <th>Capacity/Day</th>
                <th>Machine ID</th>
                <th>Machine No.</th>
                <th>Machine Name</th>
                <th>Specification</th>
                <th>Maker</th>
                <th>Toonage of Machine</th>
                <th>Tiebar</th>
                <th>UOM</th>
                <th>Minimum Closing</th>
                <th>UOM</th>
                <th>Maximum Open</th>
                <th>UOM</th>
                <th>Barrel Volume</th>
                <th>UOM</th>
                <th>Screw Diameter</th>
                <th>UOM</th>
                <th>Brand</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['subcont_id'] . '</td>
                    <td>' . $data['subcont_number'] . '</td>
                    <td>' . $data['subcont_name'] . '</td>
                    <td>' . $data['capacity'] . '</td>
                    <td>' . $data['machine_id'] . '</td>
                    <td>' . $data['machine_number'] . '</td>
                    <td>' . $data['machine_name'] . '</td>
                    <td>' . $data['machine_spec'] . '</td>
                    <td>' . $data['machine_maker'] . '</td>
                    <td>' . $data['machine_toonage'] . '</td>
                    <td>' . $data['machine_tiebar'] . '</td>
                    <td>' . $data['machine_uom_tiebar'] . '</td>
                    <td>' . $data['machine_min_close'] . '</td>
                    <td>' . $data['machine_uom_min_close'] . '</td>
                    <td>' . $data['machine_max_open'] . '</td>
                    <td>' . $data['machine_uom_max_open'] . '</td>
                    <td>' . $data['machine_volume'] . '</td>
                    <td>' . $data['machine_uom_volume'] . '</td>
                    <td>' . $data['machine_diameter'] . '</td>
                    <td>' . $data['machine_uom_diameter'] . '</td>
                    <td>' . $data['machine_brand'] . '</td>
                    <td>' . $data['machine_status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
