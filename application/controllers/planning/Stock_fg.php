<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Stock_fg extends CI_Controller
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
        // $this->form_validation->set_rules('item_fg_id', 'Customer', 'required|min_length[1]|max_length[20]|is_unique[stock_fg.item_fg_id]');
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[stock_fg.item_fg_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/stock_fg');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('stock_fg', ["item_fg_id" => $post]);
        echo json_encode($send);
    }

    //GET PERIOD
    public function readPeriod($select)
    {
        if ($select == "month") {
            $month = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
            foreach ($month as $key => $value) {
                $months[] = array("id" => $key, "name" => $value);
            }

            echo json_encode($months);
        } else if ($select == "year") {
            $year_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
            $year_now = date('Y', strtotime('+1 year', strtotime(date('Y'))));
            for ($i = $year_now; $i >= $year_before; $i--) {
                $years[] = array("id" => $i, "name" => $i);
            }

            echo json_encode($years);
        } else {
            show_error("Cannot Process your request");
        }
    }

    //GET PERIOD LISTS
    public function readPeriodLists()
    {
        $p_month = $this->input->post('p_month');
        $p_year = $this->input->post('p_year');
        $p_date_start = date("Y-m-d", strtotime($p_year . "-" . $p_month . "-01"));
        $p_date_to = date('Y-m-d', strtotime('+11 month', strtotime($p_date_start)));

        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
        }

        echo json_encode($dates);
    }

    //GET REVISION LAST
    public function readRevisionLast()
    {
        $item_fg_id = $this->input->post('item_fg_id');
        $send = $this->crud->query("SELECT max(revision) as rev FROM stock_fg WHERE item_fg_id = '$item_fg_id'");

        if (count($send) > 0) {
            if ($send[0]->rev == "5") {
                $data = array("revision" => ($send[0]->rev));
            } else {
                $data = array("revision" => ($send[0]->rev + 1));
            }
        } else {
            $data = array("revision" => 1);
        }

        echo json_encode($data);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_period_month = @base64_decode($get['filter_period_month']);
            $filter_period_year = @base64_decode($get['filter_period_year']);
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
            $filter_revision = @base64_decode($get['filter_revision']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.item_fg_id, c.customer_id, d.name as customer_name');
            $this->db->from('stock_fg a');
            $this->db->join('item_fg b', 'c.item_fg_id = b.id');
            $this->db->join('customers d', 'c.customer_id = d.id');
            $this->db->join('customer_items c', 'a.item_fg_id = c.item_fg_id');
            $this->db->like('a.p_month', $filter_period_month);
            $this->db->like('a.p_year', $filter_period_year);
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->like('a.revision', $filter_revision);
            $this->db->group_by('a.item_fg_id');
            $this->db->group_by('a.p_month');
            $this->db->group_by('a.p_year');
            $this->db->group_by('a.revision');
            $this->db->order_by('a.created_date', 'DESC');

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
            $item_fg_id = base64_decode($this->input->get('item_fg_id'));
            $p_month = base64_decode($this->input->get('p_month'));
            $p_year = base64_decode($this->input->get('p_year'));
            $revision = base64_decode($this->input->get('revision'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.item_fg_id, c.customer_id, d.name as customer_name');
            $this->db->from('stock_fg a');
            $this->db->join('item_fg b', 'c.item_fg_id = b.id');
            $this->db->join('customers d', 'c.customer_id = d.id');
            $this->db->join('customer_items c', 'a.item_fg_id = c.item_fg_id');
            $this->db->where('a.item_fg_id', $item_fg_id);
            $this->db->where('a.p_month', $p_month);
            $this->db->where('a.p_year', $p_year);
            $this->db->where('a.revision', $revision);
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
            $item_fg_id = base64_decode($this->input->get('item_fg_id'));
            $p_month = base64_decode($this->input->get('p_month'));
            $p_year = base64_decode($this->input->get('p_year'));
            $revision = base64_decode($this->input->get('revision'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.item_fg_id, c.customer_id, d.name as customer_name');
            $this->db->from('stock_fg a');
            $this->db->join('item_fg b', 'c.item_fg_id = b.id');
            $this->db->join('customers d', 'c.customer_id = d.id');
            $this->db->join('customer_items c', 'a.item_fg_id = c.item_fg_id');
            $this->db->where('a.item_fg_id', $item_fg_id);
            $this->db->where('a.p_month', $p_month);
            $this->db->where('a.p_year', $p_year);
            $this->db->where('a.revision', $revision);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLE HISTORY PRICE
    // public function datatableHistories()
    // {
    //     if ($this->input->get()) {
    //         $item_fg_id = base64_decode($this->input->get('item_fg_id'));
    //         $item_fg_id = base64_decode($this->input->get('item_fg_id'));
    //         $p_month = base64_decode($this->input->get('p_month'));
    //         $p_year = base64_decode($this->input->get('p_year'));

    //         $this->db->select('*');
    //         $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
    //         $this->db->from('forecast_histories a');
    //         $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //         $this->db->where('a.item_fg_id', $item_fg_id);
    //         $this->db->where('a.item_fg_id', $item_fg_id);
    //         $this->db->where('a.p_month', $p_month);
    //         $this->db->where('a.p_year', $p_year);
    //         $this->db->order_by('a.created_date', 'DESC');
    //         $records = $this->db->get()->result_array();

    //         echo json_encode($records);
    //     }
    // }

    //AUTO ID
    public function autoid(){
        $post = $this->input->post();
        $month = date('ymd');
        $format = "FG".$month;
        $sql = $this->db->query("SELECT max(document_no) as kode FROM stock_fg WHERE document_no LIKE '%$format%'");
        $row = $sql->row();
        if ($row->kode == ""){
            $kode = 0;
        } else {
            $kode = substr($row->kode,-2);
        }
        $autoid =$format. sprintf("%02s", $kode + 1);
        echo $autoid;
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $stock_fg = $this->crud->read("stock_fg", [], ["item_fg_id" => $post['item_fg_id'], "p_month" => $post['p_month'], "p_year" => $post['p_year']]);
            // $forecast_histories = $this->crud->read("forecast_histories", [], ["item_fg_id" => $post['item_fg_id'], "item_fg_id" => $post['item_fg_id'], "p_month" => $post['p_month'], "p_year" => $post['p_year'], "revision" => $post['revision'], "month_1" => $post['month_1'], "month_2" => $post['month_2'], "month_3" => $post['month_3'], "month_4" => $post['month_4'], "month_5" => $post['month_5'], "month_6" => $post['month_6'], "month_7" => $post['month_7'], "month_8" => $post['month_8'], "month_9" => $post['month_9'], "month_10" => $post['month_10'], "month_11" => $post['month_11'], "month_12" => $post['month_12']]);

            if (@$stock_fg->item_fg_id != "") {
                $send = $this->crud->update('stock_fg', ["item_fg_id" => $post['item_fg_id'], "p_month" => $post['p_month'], "p_year" => $post['p_year']], $post);
                // if (@$forecast_histories->item_fg_id == "") {
                //     $send2 = $this->crud->create('forecast_histories', $post);
                // }
            } else {
                $send = $this->crud->create('stock_fg', $post);
                // $send2 = $this->crud->create('forecast_histories', $post);
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
        $send = $this->crud->delete('stock_fg', $data);
        // $send2 = $this->crud->delete('forecast_histories', $data);
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
        for ($i = 2; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'p_month' => $data->val($i, 3),
                'p_year' => $data->val($i, 4)
            );
        }
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'revision' => $data->val($i, 3)
            );
        }
        for ($i = 5; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'item_fg_id' => $data->val($i, 2),
                'qty' => $data->val($i, 3)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/stock_fg.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/stock_fg.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/stock_fg.txt";
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
            $stock_fg = $this->crud->read('stock_fg', [], ["item_fg_id" => $data['item_fg_id']]);

            $post = $this->input->post();
            $month = date('ymd');
            $format = "FG".$month;
            $sql = $this->db->query("SELECT max(document_no) as kode FROM stock_fg WHERE document_no LIKE '%$format%'");
            $row = $sql->row();
            if ($row->kode == ""){
                $kode = 0;
            } else {
                $kode = substr($row->kode,-2);
            }
            $autoid =$format. sprintf("%02s", $kode + 1);

            if (!empty($stock_fg->item_fg_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['item_fg_id'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "item_fg_id" => $data['item_fg_id'],
                    "document_no" => $autoid,
                    "p_month" => $data['p_month'],
                    "p_year" => $data['p_year'],
                    "revision" => $data['revision'],
                    "qty" => $data['qty'],
                );
                $send   = $this->crud->create('stock_fg', $dataFinal);
                // $send2  = $this->crud->create('forecast_histories', $dataFinal);
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
            header("Content-Disposition: attachment; filename=stock_fg_$format.xls");
        }

        $get = $this->input->get();
        // $filter_issued_date_from = @base64_decode($get['filter_issued_date_from']);
        // $filter_issued_date_to = @base64_decode($get['filter_issued_date_to']);
        $filter_period_month = @base64_decode($get['filter_period_month']);
        $filter_period_year = @base64_decode($get['filter_period_year']);
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_revision = @base64_decode($get['filter_revision']);

        $p_date_start = date("Y-m-d", strtotime($filter_period_year . "-" . $filter_period_month . "-01"));
        $p_date_to = date('Y-m-d', strtotime('+11 month', strtotime($p_date_start)));
        while (strtotime($p_date_start) <= strtotime($p_date_to)) {
            $dates[] = array(
                "name" => date("M-y", strtotime($p_date_start))
            );

            $p_date_start = date("Y-m-d", strtotime("+1 month", strtotime($p_date_start)));
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.item_fg_id, c.customer_id, d.name as customer_name');
        $this->db->from('stock_fg a');
        $this->db->join('item_fg b', 'c.item_fg_id = b.id');
        $this->db->join('customers d', 'c.customer_id = d.id');
        $this->db->join('customer_items c', 'a.item_fg_id = c.item_fg_id');
        $this->db->like('a.p_month', $filter_period_month);
        $this->db->like('a.p_year', $filter_period_year);
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->like('a.revision', $filter_revision);
        $this->db->group_by('a.item_fg_id');
        $this->db->group_by('a.p_month');
        $this->db->group_by('a.p_year');
        $this->db->group_by('a.revision');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        if ($filter_item_fg_id == ""){
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#stock_fg {border-collapse: collapse;width: 100%;font-size: 12px;}#stock_fg td, #stock_fg th {border: 1px solid #ddd;padding: 2px;}#stock_fg tr:nth-child(even){background-color: #f2f2f2;}#stock_fg tr:hover {background-color: #ddd;}#stock_fg th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>FORECAST CUSTOMER</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small>CUSTOMER NAME</small>
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small>: </small>
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small><b>ALL</b></small>
                                </td>
                            </tr>
                        </table>
                    </div>
            </center>
            
            <table id="stock_fg" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Document No</th>
                    <th>Customer Name</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['document_no'] . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . number_format($data['qty']) . '</td>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
        } elseif ($filter_item_fg_id != "") {
            foreach ($records as $data) {
                $filter_customer_name = $data['customer_name'];
            }
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#stock_fg {border-collapse: collapse;width: 100%;font-size: 12px;}#stock_fg td, #stock_fg th {border: 1px solid #ddd;padding: 2px;}#stock_fg tr:nth-child(even){background-color: #f2f2f2;}#stock_fg tr:hover {background-color: #ddd;}#stock_fg th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>FORECAST CUSTOMER</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left;">
                        <table style="width: 100%;">
                            <tr>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small>CUSTOMER NAME</small>
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small>: </small>
                                </td>
                                <td style="font-size: 14px; text-align: left; margin:2px;">
                                    <small><b>' . $filter_customer_name . '</b></small>
                                </td>
                            </tr>
                        </table>
                    </div>
            </center>
            
            <table id="stock_fg" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Document No</th>
                    <th>Customer Name</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['document_no'] . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . number_format($data['qty']) . '</td>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
        }
    }
}
