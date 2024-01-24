<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Setting_subconts extends CI_Controller
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
        $this->form_validation->set_rules('subcont_id', 'Subcont', 'required|min_length[1]|max_length[20]|is_unique[setting_subconts.subcont_id]');
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[setting_subconts.item_fg_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/setting_subconts');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('setting_subconts', ["subcont_id" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_subcont_id = @base64_decode($get['filter_subcont_id']);
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('b.id as subcont_id, b.number as subcont_number, b.name as subcont_name, b.status, a.created_by, a.created_date, a.updated_by, a.updated_date');
            $this->db->from('setting_subconts a');
            $this->db->join('subconts b', 'a.subcont_id = b.id');
            $this->db->like('a.subcont_id', $filter_subcont_id);
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
            $filter_subcont_id = base64_decode($this->input->get('filter_subcont_id'));

            $this->db->select('a.*, b.number as subcont_number, b.name as subcont_name, b.status, c.number as item_fg_number, c.name as item_fg_name');
            $this->db->from('setting_subconts a');
            $this->db->join('subconts b', 'a.subcont_id = b.id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
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

            $this->db->select('a.*, b.number as subcont_number, b.name as subcont_name, b.status, c.number as item_fg_number, c.name as item_fg_name');
            $this->db->from('setting_subconts a');
            $this->db->join('subconts b', 'a.subcont_id = b.id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            $this->db->where('a.subcont_id', $subcont_id);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // UPDATE DATA HISTORIES
    // public function datatableHistories()
    // {
    //     if ($this->input->get()) {
    //         $subcont_id = base64_decode($this->input->get('subcont_id'));
    //         $item_fg_id = base64_decode($this->input->get('item_fg_id'));

    //         $this->db->select('*');
    //         $this->db->from('subcont_item_histories');
    //         $this->db->where('subcont_id', $subcont_id);
    //         $this->db->where('item_fg_id', $item_fg_id);
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

            $setting_subconts = $this->crud->read("setting_subconts", [], ["subcont_id" => $post['subcont_id'], "item_fg_id" => $post['item_fg_id']]);
            // $subcont_item_histories = $this->crud->read("subcont_item_histories", [], ["subcont_id" => $post['subcont_id'], "item_fg_id" => $post['item_fg_id'], "price" => $post['price']]);
            if (@$setting_subconts->subcont_id != "") {
                $send = $this->crud->update('setting_subconts', ["subcont_id" => $post['subcont_id'], "item_fg_id" => $post['item_fg_id']], $post);
                // if (@$subcont_item_histories->subcont_id == "") {
                //     $send2 = $this->crud->create('subcont_item_histories', $post);
                // }
            } else {
                $send = $this->crud->create('setting_subconts', $post);
                // $send2 = $this->crud->create('subcont_item_histories', $post);
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
        $send = $this->crud->delete('setting_subconts', $data);
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
                'item_fg_id' => $data->val($i, 3),
                'share_order' => $data->val($i, 4),
                'type' => $data->val($i, 5),
                'currency' => $data->val($i, 6),
                'price' => $data->val($i, 7),
                'valid_date' => $data->val($i, 8),
                'capacity' => $data->val($i, 9),
                'leadtime' => $data->val($i, 10),
                'status' => $data->val($i, 11)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/setting_subconts.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/setting_subconts.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/setting_subconts.txt";
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
            $setting_subconts = $this->crud->read('setting_subconts', [], ["subcont_id" => $data['subcont_id'], "item_fg_id" => $data['item_fg_id']]);

            if (!empty($setting_subconts->subcont_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['subcont_id'] . " is Duplicate Data", "theme" => "error"));
            } elseif (!empty($setting_subconts->item_fg_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Part No. " . $data['item_fg_id'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "subcont_id" => $data['subcont_id'],
                    "item_fg_id" => $data['item_fg_id'],
                    "share_order" => $data['share_order'],
                    "type" => $data['type'],
                    "currency" => $data['currency'],
                    "price" => $data['price'],
                    "valid_date" => $data['valid_date'],
                    "capacity" => $data['capacity'],
                    "leadtime" => $data['leadtime'],
                    "status" => $data['status'],
                );
                $send   = $this->crud->create('setting_subconts', $dataFinal);
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
            header("Content-Disposition: attachment; filename=setting_subconts_$format.xls");
        }

        $get = $this->input->get();
        $filter_subcont_id = @base64_decode($get['filter_subcont_id']);
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as subcont_number, b.name as subcont_name, b.status, c.number as item_fg_number, c.name as item_fg_name');
        $this->db->from('setting_subconts a');
        $this->db->join('subconts b', 'a.subcont_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->like('a.subcont_id', $filter_subcont_id);
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#setting_subconts {border-collapse: collapse;width: 100%;font-size: 12px;}#setting_subconts td, #setting_subconts th {border: 1px solid #ddd;padding: 2px;}#setting_subconts tr:nth-child(even){background-color: #f2f2f2;}#setting_subconts tr:hover {background-color: #ddd;}#setting_subconts th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER SETTING SUBCONT</h3>
            </div>
        </center>
        
        <table id="setting_subconts" border="1">
            <tr>
                <th width="20">No</th>
                <th>subcont ID</th>
                <th>subcont Code</th>
                <th>subcont Name</th>
                <th>Product ID</th>
                <th>Product No.</th>
                <th>Product Name</th>
                <th>Share Job Order %</th>
                <th>Type</th>
                <th>Currency</th>
                <th>Price</th>
                <th>Valid Date Until</th>
                <th>Capacity/Day (Pcs)</th>
                <th>Lead Time (Days)</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['subcont_id'] . '</td>
                    <td>' . $data['subcont_number'] . '</td>
                    <td>' . $data['subcont_name'] . '</td>
                    <td>' . $data['item_fg_id'] . '</td>
                    <td>' . $data['item_fg_number'] . '</td>
                    <td>' . $data['item_fg_name'] . '</td>
                    <td>' . $data['share_order'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . $data['price'] . '</td>
                    <td>' . $data['valid_date'] . '</td>
                    <td>' . $data['capacity'] . '</td>
                    <td>' . $data['leadtime'] . '</td>
                    <td>' . $data['status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
