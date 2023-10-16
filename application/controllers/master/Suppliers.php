<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Suppliers extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Supplier Code', 'required|min_length[1]|max_length[20]|is_unique[suppliers.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/suppliers');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('suppliers', ["name" => $post]);
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
            $this->db->select('*');
            $this->db->from('suppliers');
            $this->db->where('deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('id', 'asc');
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
    //AUTO ID
    public function autoid(){
        $month = date('my');
        $format = "SP-".$month;
        $sql = $this->db->query("SELECT max(id) as kode FROM suppliers WHERE id LIKE '%$format%'");
        $row = $sql->row();
        if ($row->kode == ""){
            $kode = 0;
        } else {
            $kode = substr($row->kode,-3);
        }
        $autoid =$format. sprintf("%03s", $kode + 1);
        echo $autoid;
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('suppliers', $post);
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
            $send = $this->crud->update('suppliers', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('suppliers', $data);
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
                'name' => $data->val($i, 2),
                'number' => $data->val($i, 3),
                'type' => $data->val($i, 4),
                'address' => $data->val($i, 5),
                'contact_person' => $data->val($i, 6),
                'telp' => $data->val($i, 7),
                'fax' => $data->val($i, 8),
                'email' => $data->val($i, 9),
                'website' => $data->val($i, 10),
                'currency' => $data->val($i, 11),
                'payment_term' => $data->val($i, 12),
                'incoterm' => $data->val($i, 13),
                'bank_account' => $data->val($i, 14),
                'bank_name' => $data->val($i, 15),
                'status' => $data->val($i, 16)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/suppliers.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/suppliers.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/suppliers.txt";
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
            $suppliers = $this->crud->read('suppliers', [], ["number" => $data['number']]);

            //AUTOID
            $month = date('my');
            $format = "SP-".$month;
            $sql = $this->db->query("SELECT max(id) as kode FROM suppliers WHERE id LIKE '%$format%'");
            $row = $sql->row();
            if ($row->kode == ""){
                $kode = 0;
            } else {
                $kode = substr($row->kode,-3);
            }
            $autoid =$format. sprintf("%03s", $kode + 1);

            if (!empty($suppliers->number)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Supplier Code " . $data['number'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "id" => $autoid,
                    "name" => $data['name'],
                    "number" => $data['number'],
                    "type" => $data['type'],
                    "address" => $data['address'],
                    "contact_person" => $data['contact_person'],
                    "telp" => $data['telp'],
                    "fax" => $data['fax'],
                    "email" => $data['email'],
                    "website" => $data['website'],
                    "currency" => $data['currency'],
                    "payment_term" => $data['payment_term'],
                    "incoterm" => $data['incoterm'],
                    "bank_account" => $data['bank_account'],
                    "bank_name" => $data['bank_name'],
                    "status" => $data['status'],
                );
                $send   = $this->crud->create('suppliers', $dataFinal);
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
            header("Content-Disposition: attachment; filename=suppliers_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('suppliers');
        $this->db->where('deleted', 0);
        $this->db->order_by('id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#suppliers {border-collapse: collapse;width: 100%;font-size: 12px;}#suppliers td, #suppliers th {border: 1px solid #ddd;padding: 2px;}#suppliers tr:nth-child(even){background-color: #f2f2f2;}#suppliers tr:hover {background-color: #ddd;}#suppliers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER SUPPLIER</h3>
            </div>
        </center>
        
        <table id="suppliers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Supplier ID</th>
                <th>Supplier Name</th>
                <th>Supplier Code</th>
                <th>type</th>
                <th>Address</th>
                <th>Contact Person</th>
                <th>Telepon</th>
                <th>Fax</th>
                <th>Email</th>
                <th>Website</th>
                <th>Currency</th>
                <th>Payment Term (Day)</th>
                <th>Incoterm</th>
                <th>Bank Account</th>
                <th>Bank Name</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['id'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td>' . $data['address'] . '</td>
                    <td>' . $data['contact_person'] . '</td>
                    <td>' . $data['telp'] . '</td>
                    <td>' . $data['fax'] . '</td>
                    <td>' . $data['email'] . '</td>
                    <td>' . $data['website'] . '</td>
                    <td>' . $data['currency'] . '</td>
                    <td>' . $data['payment_term'] . '</td>
                    <td>' . $data['incoterm'] . '</td>
                    <td>' . $data['bank_account'] . '</td>
                    <td>' . $data['bank_name'] . '</td>
                    <td>' . $data['status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
