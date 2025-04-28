<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Standard_price_rm extends CI_Controller
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
        // $this->form_validation->set_rules('name', 'Name', 'required|min_length[1]|max_length[50]|is_unique[standard_price_rm.name]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/standard_price_rm');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('standard_price_rm', ["item_rm_id" => $post]);
        echo json_encode($send);
    }

    public function readItemByDivision($division)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as item_category_name, c.name as item_family_name FROM item_rm a 
        left join item_categories b on a.item_category_id = b.id
        left join item_familys c on a.item_family_id = c.id
        WHERE a.division = '$division' and (a.number like '%$post%' or a.name like '$post')");

        // $this->db->select('a.*'); //c.specification
        // $this->db->from('item_rm a');
        // $this->db->where('a.division', $division);
        // $send = $this->db->get()->result_array();

        echo json_encode($send);
    }


    public function autonumber()
    {
        $post = $this->input->post();
        $division = $post['division'];
        $start_date = $post['start_date'];
        $end_date = $post['end_date'];
        $datenow = date("y", strtotime($start_date));
        $sql = $this->db->query("SELECT max(`document_number`) as kode From standard_price_rm where division='$division' AND `start_date`='$start_date' AND `end_date`='$end_date'");
        $sql2 = $this->db->query("SELECT max(`document_number`) as kode From standard_price_rm where `start_date`='$start_date' AND `end_date`='$end_date'");
        $row = $sql->row();
        $row2 = $sql2->row();
        if (!empty(@$row->kode)) {
            $autoid = @$row->kode;
        } else {
            if (empty(@$row2->kode)) {
                $kode = substr(@$row2->kode, 1);
                $autoid = "SP-RM" . $datenow . sprintf("%02s", $kode + 1);
            } else {
                $kode = substr(@$row2->kode, -2);
                $autoid = "SP-RM" . $datenow . sprintf("%02s", $kode + 1);
            }
        }
        echo $autoid;
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_start_date = @base64_decode($get['filter_start_date']);
            $filter_end_date = @base64_decode($get['filter_end_date']);
            $filter_division = @base64_decode($get['filter_division']);
            $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, c.name as division_name');
            $this->db->from('standard_price_rm a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('divisions c', 'b.division = c.number');
            $this->db->join('item_categories d', 'b.item_category_id = d.id');
            $this->db->join('item_familys e', 'b.item_family_id = e.id');
            $this->db->where('a.deleted', 0);
            if ($filter_start_date != "" || $filter_end_date != "") {
                $this->db->where('a.start_date >=', $filter_start_date);
                $this->db->where('a.end_date <=', $filter_end_date);
            }
            if (!empty($filter_division)) {
                $this->db->where('b.division', $filter_division);
            }
            if (!empty($filter_item_rm_id)) {
                $this->db->where('a.item_rm_id', $filter_item_rm_id);
            }
            $this->db->group_by('a.document_number,a.start_date,a.end_date,a.division');
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

    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $document_number = base64_decode($this->input->get('document_number'));

            $this->db->select('a.*,b.number as item_rm_number, b.name as item_rm_name, b.uom, c.name as item_category_name, d.name as item_family_name');
            // $this->db->select('a.*');
            $this->db->from('standard_price_rm a');
            $this->db->join('item_rm b', 'a.item_rm_id=b.id', 'left');
            $this->db->join('item_categories c', 'b.item_category_id=c.id', 'left');
            $this->db->join('item_familys d', 'b.item_family_id=d.id', 'left');
            $this->db->where('a.document_number', $document_number);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $document_number = base64_decode($this->input->get('document_number'));

            $this->db->select('a.*,b.number as item_rm_number, b.name as item_rm_name, b.uom, c.name as item_category_name, d.name as item_family_name, e.name as division_name');
            // $this->db->select('a.*');
            $this->db->from('standard_price_rm a');
            $this->db->join('item_rm b', 'a.item_rm_id=b.id', 'left');
            $this->db->join('item_categories c', 'b.item_category_id=c.id', 'left');
            $this->db->join('item_familys d', 'b.item_family_id=d.id', 'left');
            $this->db->join('divisions e', 'b.division=e.number', 'left');
            $this->db->where('a.document_number', $document_number);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    public function datatableHistory()
    {

        $start_date  = base64_decode($this->input->get('start_date'));
        $end_date = base64_decode($this->input->get('end_date'));
        $item_rm_id = base64_decode($this->input->get('item_rm_id'));

        $result = array();
        //Select Query
        $this->db->select('a.*, b.name as item_rm_name, b.number as item_rm_number, b.uom, c.name as division_name, d.name as item_category_name, e.name as item_family_name');
        $this->db->from('standard_price_rm_histories a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('divisions c', 'b.division = c.number');
        $this->db->join('item_categories d', 'b.item_category_id = d.id');
        $this->db->join('item_familys e', 'b.item_family_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.start_date', $start_date);
        $this->db->where('a.end_date', $end_date);
        $this->db->where('a.item_rm_id', $item_rm_id);
        $this->db->order_by('a.id', 'ASC');
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $whereupdate = array(
                'document_number' => $post['document_number'],
                'start_date' => $post['start_date'],
                'end_date' => $post['end_date'],
                'item_rm_id' => $post['item_rm_id'],

            );

            $postFinal = array(
                'document_number' => $post['document_number'],
                'division' => $post['division'],
                'start_date' =>  $post['start_date'],
                'end_date' => $post['end_date'],
                'item_rm_id' => $post['item_rm_id'],
                'currency' => $post['currency'],
                'price' => $post['price'],
            );
            $standard_price_rm = $this->crud->read("standard_price_rm", [], $whereupdate);
            if (@$standard_price_rm->item_rm_id != "") {
                if (
                    @$standard_price_rm->document_number == $post['document_number'] &&
                    @$standard_price_rm->division == $post['division'] &&
                    @$standard_price_rm->start_date == $post['start_date'] &&
                    @$standard_price_rm->end_date == $post['end_date'] &&
                    @$standard_price_rm->item_rm_id == $post['item_rm_id'] &&
                    @$standard_price_rm->currency == $post['currency'] &&
                    @$standard_price_rm->price == $post['price']
                ) {
                    $send = json_encode(array("title" => "Good Job", "message" => "Data Updated Successfully", "theme" => "success"));
                } else {
                    $send = $this->crud->update('standard_price_rm', $whereupdate, $postFinal);
                    $send2 = $this->crud->create('standard_price_rm_histories', $postFinal);
                }
            } else {
                $send = $this->crud->create('standard_price_rm', $postFinal);
                $send2 = $this->crud->create('standard_price_rm_histories', $postFinal);
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
        $send = $this->crud->delete('standard_price_rm', $data);
        $send = $this->crud->delete('standard_price_rm_histories', $data);
        echo $send;
    }

    public function deleteItem()
    {
        if ($this->input->post()) {
            $id = $this->input->post('id');
            $send = $this->crud->delete('standard_price_rm', ["id" => $id]);
            echo $send;
        }
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
                'start_date' => $data->val($i, 2),
                'end_date' => $data->val($i, 3),
                'item_rm_id' => $data->val($i, 4),
                'currency' => $data->val($i, 5),
                'price' => $data->val($i, 6)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/standard_price_rm.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/standard_price_rm.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/standard_price_rm.txt";
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
            $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);
            $currencies = $this->crud->read('currencies', [], ["name" => $data['currency']]);

            $division = @$item_rm->division;
            $start_date = $data['start_date'];
            $end_date = $data['end_date'];

            $datenow = date("y", strtotime($start_date));
            $sql = $this->db->query("SELECT max(`document_number`) as kode From standard_price_rm where division='$division' AND `start_date`='$start_date' AND `end_date`='$end_date'");
            $sql2 = $this->db->query("SELECT max(`document_number`) as kode From standard_price_rm where `start_date`='$start_date' AND `end_date`='$end_date'");
            $row = $sql->row();
            $row2 = $sql2->row();
            if (!empty(@$row->kode)) {
                $document_number = @$row->kode;
            } else {
                if (empty(@$row2->kode)) {
                    $kode = substr(@$row2->kode, 1);
                    $document_number = "SP-RM" . $datenow . sprintf("%02s", $kode + 1);
                } else {
                    $kode = substr(@$row2->kode, -2);
                    $document_number = "SP-RM" . $datenow . sprintf("%02s", $kode + 1);
                }
            }

            if (empty($item_rm->number)) {
                echo json_encode(array("title" => "Not Found", "message" => " Part No. " . $data['item_rm_id'] . " Not Found", "theme" => "error"));
            } else if (empty($currencies->name)) {
                echo json_encode(array("title" => "Not Found", "message" => " Currency Code. " . $data['currency'] . " Not Found", "theme" => "error"));
            } else {
                $whereupdate = array(
                    "document_number" => $document_number,
                    "item_rm_id" => $data['item_rm_id'],
                    "start_date" => $data['start_date'],
                    "end_date" => $data['end_date'],

                );
                $standard_price_rm = $this->crud->read('standard_price_rm', [], $whereupdate);

                $dataFinal = array(
                    //field
                    "document_number" => $document_number,
                    "division" => $division,
                    "item_rm_id" => $data['item_rm_id'],
                    "start_date" => $data['start_date'],
                    "end_date" => $data['end_date'],
                    "currency" => $data['currency'],
                    "price" => $data['price']
                );

                if (@$standard_price_rm->item_rm_id != "") {
                    if (
                        @$standard_price_rm->document_number == $document_number &&
                        @$standard_price_rm->division == $division &&
                        @$standard_price_rm->start_date == $data['start_date'] &&
                        @$standard_price_rm->end_date == $data['end_date'] &&
                        @$standard_price_rm->item_rm_id == $data['item_rm_id'] &&
                        @$standard_price_rm->currency == $data['currency'] &&
                        @$standard_price_rm->price == $data['price']
                    ) {
                        $send = json_encode(array("title" => "No Update", "message" => "Data Is Same", "theme" => "warning"));
                    } else {
                        $send = $this->crud->update('standard_price_rm', $whereupdate, $dataFinal);
                        $send2 = $this->crud->create('standard_price_rm_histories', $dataFinal);
                    }
                } else {
                    $send = $this->crud->create('standard_price_rm', $dataFinal);
                    $send2 = $this->crud->create('standard_price_rm_histories', $dataFinal);
                }

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
            header("Content-Disposition: attachment; filename=standard_price_rm_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $get = $this->input->get();
        $filter_start_date = @base64_decode($get['filter_start_date']);
        $filter_end_date = @base64_decode($get['filter_end_date']);
        $filter_division = @base64_decode($get['filter_division']);
        $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

        $this->db->select('a.*, b.name as item_rm_name, b.number as item_rm_number, b.division, b.uom, c.name as division_name, d.name as item_category_name, e.name as item_family_name');
        $this->db->from('standard_price_rm a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('divisions c', 'b.division = c.number');
        $this->db->join('item_categories d', 'b.item_category_id = d.id');
        $this->db->join('item_familys e', 'b.item_family_id = e.id');
        if ($filter_start_date != "" || $filter_end_date != "") {
            $this->db->where('a.start_date >=', $filter_start_date);
            $this->db->where('a.end_date <=', $filter_end_date);
        }
        if (!empty($filter_division)) {
            $this->db->where('b.division', $filter_division);
        }
        if (!empty($filter_item_rm_id)) {
            $this->db->where('a.item_rm_id', $filter_item_rm_id);
        }

        $this->db->order_by('a.id', 'ASC');
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
                <h3>STANDARD PRICE MATERIAL</h3>
            </div>
        </center>
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Start Date</th>
                <th>Ending Date</th>
                <th>Part ID</th>
                <th>Part No</th>
                <th>Part Name</th>
                <th>UOM</th>
                <th>Division</th>
                <th>Category</th>
                <th>Product Family</th>
                <th>Currency</th>
                <th>Price</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['start_date'] . '</td>
                    <td>' . $data['end_date'] . '</td>
                    <td>' . $data['item_rm_id'] . '</td>
                    <td>' . $data['item_rm_number'] . '</td>
                    <td>' . $data['item_rm_name'] . '</td>
                    <td>' . $data['uom'] . '</td>
                    <td>' . $data['division_name'] . '</td>
                    <td>' . $data['item_category_name'] . '</td>
                    <td>' . $data['item_family_name'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . $data['price'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
