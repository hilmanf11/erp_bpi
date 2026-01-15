<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Polybag_prices extends CI_Controller
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
        // $this->form_validation->set_rules('size', 'Size.', 'required|min_length[1]|max_length[20]|is_unique[polybag_prices.size]');
        $this->form_validation->set_rules('size','Size','required|callback__valid_size_unique');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/polybag_prices');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('polybag_prices', ["size" => $post]);
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
            $this->db->select('a.*, b.name as division');
            $this->db->from('polybag_prices a');
            $this->db->join('divisions b', 'a.division_id = b.id','left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {

                    if ($filter->field == 'division') {
                        $filter->field = 'b.name';
                    }

                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('a.id', 'asc');
            $this->db->order_by('a.size', 'asc');
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
        $format = "PB-".$month;
        $sql = $this->db->query("SELECT max(id) as kode FROM polybag_prices WHERE id LIKE '%$format%'");
        $row = $sql->row();
        if ($row->kode == ""){
            $kode = 0;
        } else {
            $kode = substr($row->kode,-3);
        }
        $autoid =$format. sprintf("%03s", $kode + 1);
        echo $autoid;
    }

    public function _valid_size_unique($size)
    {
        // Normalisasi input:
        // - lowercase
        // - hapus semua spasi
        // - hilangkan simbol non-angka/huruf
        $normalized = strtolower(preg_replace('/\s+/', '', $size));

        // Ambil semua size dari database
        $existing = $this->db->select('size')->from('polybag_prices')->get()->result();

        foreach ($existing as $row) {

            // Normalisasi data database
            $db_norm = strtolower(preg_replace('/\s+/', '', $row->size));

            // Cek jika sama setelah dinormalisasi
            if ($normalized == $db_norm) {
                $this->form_validation->set_message(
                    '_valid_size_unique',
                    'The {field} already exists.'
                );
                return false;
            }
        }

        return true; // valid
    }


    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('polybag_prices', $post);
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
            $send = $this->crud->update('polybag_prices', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('polybag_prices', $data);
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
                'size' => $data->val($i, 2),
                'weigth' => $data->val($i, 3),
                'uom' => $data->val($i, 4),
                'qty' => $data->val($i, 5),
                'price_kg' => $data->val($i, 6),
                'status' => $data->val($i, 7)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/polybag_prices.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/polybag_prices.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/polybag_prices.txt";
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

            $inputSizeNorm = strtolower(preg_replace('/\s+/', '', $data['size']));

            $existingSizes = $this->db->select('size')->from('polybag_prices')->get()->result();

            $duplicate = false;
            foreach ($existingSizes as $row) {
                $dbNorm = strtolower(preg_replace('/\s+/', '', $row->size));

                if ($inputSizeNorm == $dbNorm) {
                    $duplicate = true;
                    break;
                }
            }

            //AUTOID
            $month = date('my');
            $format = "PB-" . $month;
            $sql = $this->db->query("SELECT max(id) as kode FROM polybag_prices WHERE id LIKE '%$format%'");
            $row = $sql->row();
            $kode = ($row->kode == "" ? 0 : substr($row->kode, -3));
            $autoid = $format . sprintf("%03s", $kode + 1);

            // Jika duplikat
            if ($duplicate) {
                echo json_encode(array("title" => "Duplicated","message" => " Size '" . $data['size'] . "' already exists","theme" => "error"));
                return;
            }

            $dataFinal = array(
                "id" => $autoid,
                "size" => $data['size'],
                "weigth" => $data['weigth'],
                "uom" => $data['uom'],
                "qty" => $data['qty'],
                "price_kg" => $data['price_kg'],
                "price_pcs" => $data['price_kg'] / $data['qty'],
                "status" => $data['status'],
            );

            $send = $this->crud->create('polybag_prices', $dataFinal);
            echo $send;
        }
    }
    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=polybag_prices_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as division');
        $this->db->from('polybag_prices a');
        $this->db->join('divisions b', 'a.division_id = b.id','left');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $this->db->order_by('a.toonage', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#polybag_prices {border-collapse: collapse;width: 100%;font-size: 12px;}#polybag_prices td, #polybag_prices th {border: 1px solid #ddd;padding: 2px;}#polybag_prices tr:nth-child(even){background-color: #f2f2f2;}#polybag_prices tr:hover {background-color: #ddd;}#polybag_prices th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER POLYBAG PRICES</h3>
            </div>
        </center>
        
        <table id="polybag_prices" border="1">
            <tr>
                <th width="20">No</th>
                <th>Polybag ID</th>
                <th>Size</th>
                <th>Weigth</th>
                <th>Unit</th>
                <th>Pcs Kg</th>
                <th>Price Kg</th>
                <th>Price Pcs</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['id'] . '</td>
                    <td>' . $data['size'] . '</td>
                    <td>' . $data['weigth'] . '</td>
                    <td>' . $data['uom'] . '</td>
                    <td>' . $data['qty'] . '</td>
                    <td>' . number_format($data['price_kg'],2) . '</td>
                    <td>' . number_format($data['price_pcs'],2) . '</td>
                    <td>' . $data['status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
