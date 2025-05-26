<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Item_equivalent extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[item_equivalents.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/item_equivalent');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('item_equivalents', ["item_rm_id" => $post]);
        echo json_encode($send);
    }

    public function readItem()
    {
       $post = isset($_POST['q']) ? $_POST['q'] : "";
       
       $this->db->select('*');
       $this->db->from('item_rm');
       $this->db->where('item_category_id', 'C01');
       $this->db->where('number', $post);
       $this->db->group_by('id');
       $this->db->order_by('number', 'ASC');
       $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readItems($division)
    {
       $post = isset($_POST['q']) ? $_POST['q'] : "";
       
       $this->db->select('*');
       $this->db->from('item_rm');
       $this->db->where('item_category_id', 'C01');
       $this->db->where('division', $division);
       $this->db->where('number', $post);
       $this->db->group_by('id');
       $this->db->order_by('number', 'ASC');
       $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_item_rm_id_equivalent = @base64_decode($get['filter_item_rm_id_equivalent']);
            $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*,b.number as item_number_induk, b.name as item_name_induk, c.name as division_name');
            $this->db->from('item_equivalents a');
            $this->db->join('item_rm b','a.item_rm_id = b.id');
            $this->db->join('divisions c','a.division = c.number');
            $this->db->like('a.item_rm_id', $filter_item_rm_id);
            $this->db->like('a.item_rm_id_equivalent', $filter_item_rm_id_equivalent);
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('a.item_name', 'ASC');
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

            $this->db->select('a.*,c.name as item_family_name');
            $this->db->from('item_equivalents a');
            $this->db->join('item_rm b','a.item_rm_id_equivalent = b.id');
            $this->db->join('item_familys c','b.item_family_id = c.id');
            $this->db->where('item_rm_id', $number);
            $this->db->group_by('id');
            $this->db->order_by('id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATA TABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $item_rm_id = base64_decode($this->input->get('item_rm_id'));

            $this->db->select('a.*,c.name as item_family_name');
            $this->db->from('item_equivalents a');
            $this->db->join('item_rm b','a.item_rm_id_equivalent = b.id');
            $this->db->join('item_familys c','b.item_family_id = c.id');
            $this->db->where('a.item_rm_id', $item_rm_id);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $item_equivalents = $this->crud->read("item_equivalents", [], ["item_rm_id" => $post['item_rm_id'], "item_rm_id_equivalent" => $post['item_rm_id_equivalent'], "division" => $post['division']]);
            
            if (!empty($item_equivalents)) {
                $send = $this->crud->update('item_equivalents', ["item_rm_id" => $post['item_rm_id'], "item_rm_id_equivalent" => $post['item_rm_id_equivalent'], "division" => $post['division']], $post);
            } else {
                $send = $this->crud->create('item_equivalents', $post);
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
        $send = $this->crud->delete('item_equivalents', $data);
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
                'item_rm_id' => $data->val($i, 2),
                'item_rm_id_equivalent' => $data->val($i, 3),
                'division' => $data->val($i, 4),
                'remarks' => $data->val($i, 5)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/item_equivalents.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/item_equivalents.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/item_equivalents.txt";
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
            $item_equivalents = $this->crud->read("item_equivalents", [], ["item_rm_id" => $data['item_rm_id'], "item_rm_id_equivalent" => $data['item_rm_id_equivalent'], "division" => $data['division']]);
            $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);            
            $uom = $item_rm->uom;
            $item_number = $item_rm->number;
            $item_name = $item_rm->name;

            $dataFinal = array(
                //field
                "item_rm_id" => $data['item_rm_id'],
                "item_rm_id_equivalent" => $data['item_rm_id_equivalent'],
                "uom" => $uom,
                "item_number" => $item_number,
                "item_name" => $item_name,
                "remarks" => $data['remarks'],
            );

            if (!empty($item_equivalents)) {
                $send = $this->crud->update('item_equivalents', ["item_rm_id" => $data['item_rm_id'], "item_rm_id_equivalent" => $data['item_rm_id_equivalent'], "division" => $data['division']], $dataFinal);
            } else {
                $send = $this->crud->create('item_equivalents', $dataFinal);
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
            header("Content-Disposition: attachment; filename=item_equivalents_$format.xls");
        }

        $get = $this->input->get();
        $filter_item_rm_id_equivalent = @base64_decode($get['filter_item_rm_id_equivalent']);
        $filter_item_rm_id = @base64_decode($get['filter_item_rm_id']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*,c.number as item_number_induk, c.name as item_name_induk, c.item_family_id as item_rm_family, d.name as item_family_name');
        $this->db->from('item_equivalents a');
        $this->db->join('item_rm c', 'a.item_rm_id_equivalent = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->like('a.item_rm_id_equivalent', $filter_item_rm_id_equivalent);
        $this->db->like('a.item_rm_id', $filter_item_rm_id);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#item_equivalents {border-collapse: collapse;width: 100%;font-size: 12px;}#item_equivalents td, #item_equivalents th {border: 1px solid #ddd;padding: 2px;}#item_equivalents tr:nth-child(even){background-color: #f2f2f2;}#item_equivalents tr:hover {background-color: #ddd;}#item_equivalents th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
        
        <table id="item_equivalents" border="1">
            <tr>
                <th width="20">No</th>
                <th>Part ID</th>
                <th>Part No.</th>
                <th>Part Name</th>
                <th>Part No Equivalent.</th>
                <th>Part Name Equivalent</th>
                <th>Product Family</th>
                <th>UOM</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['item_rm_id'] . '</td>
                    <td style="mso-number-format:\@;">' . str_replace("Ø", "&Oslash;", $data['item_number_induk']) . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_name_induk'] . '</td>
                    <td style="mso-number-format:\@;">' . str_replace("Ø", "&Oslash;", $data['item_number']) . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_name'] . '</td>
                    <td>' . $data['item_family_name'] . '</td>
                    <td>' . $data['uom'] . '</td>
                    <td>' . $data['remarks'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
