<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Stock_wip extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[50]|is_unique[stock_wip.item_fg_id]');
        // $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[50]|is_unique[stock_wip.customer_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/stock_wip');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('stock_wip', ["item_fg_id" => $post]);
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

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_period_month = @base64_decode($get['filter_period_month']);
            $filter_period_year = @base64_decode($get['filter_period_year']);
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
            // $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_revision = @base64_decode($get['filter_revision']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            //Select Query
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
            $this->db->from('stock_wip a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            // $this->db->join('customer_items c', 'a.item_fg_id = c.item_fg_id AND a.customer_id = c.customer_id');
            // $this->db->join('customers d', 'c.customer_id = d.id');
            $this->db->like('a.p_month', $filter_period_month);
            $this->db->like('a.p_year', $filter_period_year);
            // $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            // $this->db->like('d.id', $filter_customer_id);
            $this->db->like('a.revision', $filter_revision);
            $this->db->group_by('a.p_month');
            $this->db->group_by('a.p_year');
            $this->db->group_by('a.revision');
            $this->db->group_by('a.item_fg_id');
            // $this->db->group_by('a.customer_id');
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

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('stock_wip', $post);
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
            $send = $this->crud->update('stock_wip', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('stock_wip', $data);
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

        $p_month = $data->val(2, 3);
        $p_year = $data->val(2, 4);
        $revision = $data->val(3, 3);

        for ($i = 5; $i <= $total_row; $i++) {
            $datas[] = array(
                'p_month' => $p_month,
                'p_year' => $p_year,
                'revision' => $revision,
                'document_no' => $data->val($i, 2),
                'item_fg_id' => $data->val($i, 3),
                'pp' => $data->val($i, 4),
                'p1' => $data->val($i, 5),
                'p2' => $data->val($i, 6),
                'p3' => $data->val($i, 7)
            );
        }

        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/stock_wip.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/stock_wip.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/stock_wip.txt";
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
            
            $item_fg = $this->crud->read('item_fg', [], [
                "id" => $data['item_fg_id'],
            ]);

            $stock_wip = $this->crud->read('stock_wip', [], [
                "document_no" => $data['document_no'],
                // "customer_id" => $data['customer_id'],
                "item_fg_id" => $data['item_fg_id'],
                "p_month" => $data['p_month'],
                "p_year" => $data['p_year'],
                "revision" => $data['revision'],
                'pp' => $data['pp'],
                'p1' => $data['p1'],
                'p2' => $data['p2'],
                'p3' => $data['p3'],
            ]);

            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not found", "message" => " Product No. " . $data['item_fg_id'] . " is Not Found!", "theme" => "error"));
            } elseif (!empty($stock_wip->document_no)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Document No. " . $data['document_no'] . " is Duplicate Data", "theme" => "error"));
            } elseif (!empty($stock_wip->item_fg_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['item_fg_id'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $send   = $this->crud->create('stock_wip', $data);
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
            header("Content-Disposition: attachment; filename=stock_wip_$format.xls");
        }

        $get = $this->input->get();
        $filter_period_month = @base64_decode($get['filter_period_month']);
        $filter_period_year = @base64_decode($get['filter_period_year']);
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        // $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_revision = @base64_decode($get['filter_revision']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
        $this->db->from('stock_wip a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        // $this->db->join('customer_items c', 'a.item_fg_id = c.item_fg_id AND a.customer_id = c.customer_id');
        // $this->db->join('customers d', 'c.customer_id = d.id');
        $this->db->like('a.p_month', $filter_period_month);
        $this->db->like('a.p_year', $filter_period_year);
        // $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        // $this->db->like('d.id', $filter_customer_id);
        $this->db->like('a.revision', $filter_revision);
        $this->db->group_by('a.p_month');
        $this->db->group_by('a.p_year');
        $this->db->group_by('a.revision');
        $this->db->group_by('a.item_fg_id');
        // $this->db->group_by('a.customer_id');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_array();

        if ($filter_period_month == "01") {
            $month_name = "JANUARY";
        } elseif ($filter_period_month == "02") {
            $month_name = "FEBRUARY";
        } elseif ($filter_period_month == "03") {
            $month_name = "MARCH";
        } elseif ($filter_period_month == "04") {
            $month_name = "APRIL";
        } elseif ($filter_period_month == "05") {
            $month_name = "MAY";
        } elseif ($filter_period_month == "06") {
            $month_name = "JUNE";
        } elseif ($filter_period_month == "07") {
            $month_name = "JULY";
        } elseif ($filter_period_month == "08") {
            $month_name = "AUGUST";
        } elseif ($filter_period_month == "09") {
            $month_name = "SEPTEMBER";
        } elseif ($filter_period_month == "10") {
            $month_name = "OCTOBER";
        } elseif ($filter_period_month == "11") {
            $month_name = "NOVEMBER";
        } elseif ($filter_period_month == "12") {
            $month_name = "DECEMBER";
        }

        if ($filter_revision == "" && $filter_item_fg_id == "") {
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#stock_wip {border-collapse: collapse;width: 100%;font-size: 12px;}#stock_wip td, #stock_wip th {border: 1px solid #ddd;padding: 2px;}#stock_wip tr:nth-child(even){background-color: #f2f2f2;}#stock_wip tr:hover {background-color: #ddd;}#stock_wip th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>DATA STOCK WIP</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left; width:30%;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PERIOD</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $month_name . ' ' . $filter_period_year . '</b></small>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>REVISION</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>0</b></small>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PRODUCT NO.</small>
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
            
            <table id="stock_wip" border="1">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Document No</th>
                    <th rowspan="2">Product No</th>
                    <th rowspan="2">Product Name</th>
                    <th colspan="4" style="text-align:center;">Stock WIP</th>  
                </tr>
                <tr>
                    <th>Injection</th>
                    <th>Assembly</th>
                    <th>On Hold</th>
                    <th>Subcont</th>
                </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['document_no'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['pp'] . '</td>
                        <td>' . $data['p1'] . '</td>
                        <td>' . $data['p2'] . '</td>
                        <td>' . $data['p3'] . '</td>
                    </tr>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
        } elseif ($filter_revision != "" && $filter_item_fg_id == "") {
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#stock_wip {border-collapse: collapse;width: 100%;font-size: 12px;}#stock_wip td, #stock_wip th {border: 1px solid #ddd;padding: 2px;}#stock_wip tr:nth-child(even){background-color: #f2f2f2;}#stock_wip tr:hover {background-color: #ddd;}#stock_wip th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>DATA STOCK WIP</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left; width:60%;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PERIOD</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $month_name . ' ' . $filter_period_year . '</b></small>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>REVISION</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $filter_revision . '</b></small>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PRODUCT NO.</small>
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
            
            <table id="stock_wip" border="1">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Document No</th>
                    <th rowspan="2">Product No</th>
                    <th rowspan="2">Product Name</th>
                    <th colspan="4" style="text-align:center;">Stock WIP</th>  
                </tr>
                <tr>
                    <th>Injection</th>
                    <th>Assembly</th>
                    <th>On Hold</th>
                    <th>Subcont</th>
                </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['document_no'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['pp'] . '</td>
                        <td>' . $data['p1'] . '</td>
                        <td>' . $data['p2'] . '</td>
                        <td>' . $data['p3'] . '</td>
                    </tr>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
        } elseif ($filter_revision == "" && $filter_item_fg_id != "") {
            foreach ($records as $data) {
                $filter_item_fg_id = $data['item_fg_number'];
            }
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#stock_wip {border-collapse: collapse;width: 100%;font-size: 12px;}#stock_wip td, #stock_wip th {border: 1px solid #ddd;padding: 2px;}#stock_wip tr:nth-child(even){background-color: #f2f2f2;}#stock_wip tr:hover {background-color: #ddd;}#stock_wip th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>DATA STOCK WIP</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left; width:60%;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PERIOD</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $month_name . ' ' . $filter_period_year . '</b></small>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>REVISION</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>ALL</b></small>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PRODUCT NO.</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $filter_item_fg_id . '</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            
            <table id="stock_wip" border="1">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Document No</th>
                    <th rowspan="2">Product No</th>
                    <th rowspan="2">Product Name</th>
                    <th colspan="4" style="text-align:center;">Stock WIP</th>  
                </tr>
                <tr>
                    <th>Injection</th>
                    <th>Assembly</th>
                </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['document_no'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['pp'] . '</td>
                        <td>' . $data['p1'] . '</td>
                        <td>' . $data['p2'] . '</td>
                        <td>' . $data['p3'] . '</td>
                    </tr>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
        } elseif ($filter_revision != "" && $filter_item_fg_id != "") {
            foreach ($records as $data) {
                $filter_item_fg_id = $data['item_fg_number'];
            }
            $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#stock_wip {border-collapse: collapse;width: 100%;font-size: 12px;}#stock_wip td, #stock_wip th {border: 1px solid #ddd;padding: 2px;}#stock_wip tr:nth-child(even){background-color: #f2f2f2;}#stock_wip tr:hover {background-color: #ddd;}#stock_wip th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                    <h3>DATA STOCK WIP</h3>
                </div>
                <div style="float: left; font-size: 12px; text-align: left; width:60%;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PERIOD</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $month_name . ' ' . $filter_period_year . '</b></small>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>REVISION</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $filter_revision . '</b></small>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>PRODUCT NO.</small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small>: </small>
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <small><b>' . $filter_item_fg_id . '</b></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </center>
            
            <table id="stock_wip" border="1">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Document No</th>
                    <th rowspan="2">Product No</th>
                    <th rowspan="2">Product Name</th>
                    <th colspan="4" style="text-align:center;">Stock WIP</th>  
                </tr>
                <tr>
                    <th>Injection</th>
                    <th>Assembly</th>
                    <th>On Hold</th>
                    <th>Subcont</th>
                </tr>';
            $no = 1;
            foreach ($records as $data) {
                $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['document_no'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['pp'] . '</td>
                        <td>' . $data['p1'] . '</td>
                        <td>' . $data['p2'] . '</td>
                        <td>' . $data['p3'] . '</td>
                    </tr>';
                $no++;
            }
            $html .= '</table></body></html>';
            echo $html;
        }
    }
}
