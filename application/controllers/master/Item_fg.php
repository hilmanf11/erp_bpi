<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class item_fg extends CI_Controller
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
        $this->form_validation->set_rules('number', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[item_fg.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('master/item_fg');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM item_fg WHERE number like '%$post%' or number_customer like '%$post%' or name like '%$post%'");
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
            $this->db->select('a.*, b.name as division_name, count(c.item_fg_id) as total_mold, f.min, f.max');
            $this->db->from('item_fg a');
            $this->db->join('divisions b', 'a.division_id = b.id');
            $this->db->join('mold_items c', 'a.id = c.item_fg_id', 'left');
            $this->db->join('customer_items d', 'd.item_fg_id = a.id', 'left');
            $this->db->join('customers e', 'd.customer_id = e.id', 'left');
            $this->db->join('setting_stocks f', "e.type = f.kind AND f.item_category_id = 'C03'", 'left');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "division_name") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "total_mold") {
                        $this->db->like("count(c.item_fg_id)", $filter->value);
                    } elseif ($filter->field == "min") {
                        $this->db->like("f.min", $filter->value);
                    } elseif ($filter->field == "max") {
                        $this->db->like("f.max", $filter->value);
                    } else {
                        $this->db->like("a." . $filter->field, $filter->value);
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
    //AUTO ID
    public function autoid($division)
    {
        $month = date('my');
        $combine = "FG-" . $division;
        $format = "BPI" . $combine . $month;
        $sql = $this->db->query("SELECT max(id) as kode FROM item_fg WHERE id LIKE '%$format%'");
        $row = $sql->row();
        if ($row->kode == "") {
            $kode = 0;
        } else {
            $kode = substr($row->kode, -4);
        }
        $autoid = $format . sprintf("%04s", $kode + 1);
        echo $autoid;
    }
    //MIN STOCK
    public function min_stock(){
        $sql = $this->db->query("SELECT min FROM setting_stocks WHERE item_category_id = 'FINISHED GOOD' GROUP BY item_category_id ASC");
        $row = $sql->row();
        echo $row;
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $attachment = $this->crud->upload('attachment', ["pdf"], 'assets/documents/item_fg/', ["id" => $post['id']], "item_fg", "attachment");
                $postFinal = array_merge($post, ["attachment" => $attachment]);
                $send   = $this->crud->create('item_fg', $postFinal);
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
            $attachment = $this->crud->upload('attachment', ["pdf"], 'assets/documents/item_fg/', ["id" => $post['id']], "item_fg", "attachment");
            $postFinal = array_merge($post, ["attachment" => $attachment]);
            $send = $this->crud->update('item_fg', ["id" => $id], $postFinal);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        //Table //Like //Field       //Where
        $file = $this->crud->read('item_fg', [], ["id" => $data['id']]);
        $send = $this->crud->delete('item_fg', $data);
        // $attachment = @$file->attachment;
        // $file_path = @$file->attachment;
        // $absolute_path = realpath($file_path);
        // if ($absolute_path !== false) {
        //     unlink($absolute_path);
        // } else {
        //     echo "Path file tidak valid.";
        // }
        // if (@unlink("$attachment")) {
        //     echo ("Success deleting $attachment");
        // } else {
        //     echo ("Error deleting $attachment");
        // }
        // @unlink($attachment);
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
                'number_customer' => $data->val($i, 4),
                'alias' => $data->val($i, 5),
                'process' => $data->val($i, 6),
                'division_id' => $data->val($i, 7),
                'control_id' => $data->val($i, 8),
                'boxs' => $data->val($i, 9),
                'lot' => $data->val($i, 10),
                'polybag' => $data->val($i, 11),
                'box_label' => $data->val($i, 12),
                'ng_ration' => $data->val($i, 13),
                'is_no' => $data->val($i, 14),
                'weight' => $data->val($i, 15),
                'color' => $data->val($i, 16),
                'leadtime' => $data->val($i, 17),
                'mpq' => $data->val($i, 18),
                'moq' => $data->val($i, 19),
                'uom' => $data->val($i, 20),
                'qty_box' => $data->val($i, 21),
                'box_sub' => $data->val($i, 22),
                'logo' => $data->val($i, 23),
                'status' => $data->val($i, 24)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/item_fg.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/item_fg.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/item_fg.txt";
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
            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['number']]);
            $division = $this->crud->read('divisions', [], ["id" => $data['division_id']]);

            //AUTOID
            $month = date('my');
            $combine = "FG-" . @$division->number;
            $format = "BPI" . $combine . $month;
            $sql = $this->db->query("SELECT max(id) as kode FROM item_fg WHERE id LIKE '%$format%'");
            $row = $sql->row();
            if ($row->kode == "") {
                $kode = 0;
            } else {
                $kode = substr($row->kode, -4);
            }
            $autoid = $format . sprintf("%04s", $kode + 1);

            if (empty($division->number)) {
                echo json_encode(array("title" => "Not Found", "message" => " Division " . $data['division_id'] . " Not Found", "theme" => "error"));
            } elseif (!empty($item_fg->number)) {
                echo json_encode(array("title" => "Duplicated", "message" => " Product No. " . $data['number'] . " is Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "id" => $autoid,
                    "number" => $data['number'],
                    "name" => $data['name'],
                    "number_customer" => $data['number_customer'],
                    "alias"=> $data['alias'],
                    "process" => $data['process'],
                    "division_id" => $data['division_id'],
                    "control_id" => $data['control_id'],
                    "boxs" => $data['boxs'],
                    "lot" => $data['lot'],
                    "polybag" => $data['polybag'],
                    "box_label" => $data['box_label'],
                    "ng_ration" => $data['ng_ration'],
                    "is_no" => $data['is_no'],
                    "weight" => $data['weight'],
                    "color" => $data['color'],
                    "leadtime" => $data['leadtime'],
                    "mpq" => $data['mpq'],
                    "moq" => $data['moq'],
                    "uom" => $data['uom'],
                    "qty_box" => $data['qty_box'],
                    "box_sub" => $data['box_sub'],
                    "logo" => $data['logo'],
                    "status" => $data['status'],
                );
                $send   = $this->crud->create('item_fg', $dataFinal);
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
            header("Content-Disposition: attachment; filename=item_fg_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as division_name, count(c.item_fg_id) as total_mold, f.min, f.max');
        $this->db->from('item_fg a');
        $this->db->join('divisions b', 'a.division_id = b.id');
        $this->db->join('mold_items c', 'a.id = c.item_fg_id', 'left');
        $this->db->join('customer_items d', 'd.item_fg_id = a.id', 'left');
        $this->db->join('customers e', 'd.customer_id = e.id', 'left');
        $this->db->join('setting_stocks f', "e.type = f.kind AND f.item_category_id = 'C03'", 'left');
        $this->db->where('a.deleted', 0);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#item_fg {border-collapse: collapse;width: 100%;font-size: 12px;}#item_fg td, #item_fg th {border: 1px solid #ddd;padding: 2px;}#item_fg tr:nth-child(even){background-color: #f2f2f2;}#item_fg tr:hover {background-color: #ddd;}#item_fg th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>MASTER ITEM FINISH GOOD</h3>
            </div>
        </center>
        
        <table id="item_fg" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product ID</th>
                <th>Product No.</th>
                <th>Product Name</th>
                <th>Total Mold</th>
                <th>Product Customer</th>
                <th>Product Alias</th>
                <th>Process Type</th>
                <th>Division</th>
                <th>Control</th>
                <th>Box</th>
                <th>Lot</th>
                <th>Polybag Label</th>
                <th>Box Label</th>
                <th>NG Ratio (%)</th>
                <th>IS No.</th>
                <th>Weight (Gram)</th>
                <th>Color</th>
                <th>Leadtime (Day)</th>
                <th>MPQ</th>
                <th>MOQ</th>
                <th>Qty/Box</th>
                <th>Qty/Sub Box</th>
                <th>Uom</th>
                <th>Min</th>
                <th>Max</th>
                <th>Logo</th>
                <th>Status</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['id'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['name'] . '</td>
                        <td>' . $data['total_mold'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['number_customer'] . '</td>
                        <td>' . $data['alias'] . '</td>
                        <td>' . $data['process'] . '</td>
                        <td>' . $data['division_name'] . '</td>
                        <td>' . $data['control_id'] . '</td>
                        <td>' . $data['boxs'] . '</td>
                        <td>' . $data['lot'] . '</td>
                        <td>' . $data['polybag'] . '</td>
                        <td>' . $data['box_label'] . '</td>
                        <td>' . $data['ng_ration'] . '</td>
                        <td>' . $data['is_no'] . '</td>
                        <td>' . $data['weight'] . '</td>
                        <td>' . $data['color'] . '</td>
                        <td>' . $data['leadtime'] . '</td>
                        <td>' . $data['mpq'] . '</td>
                        <td>' . $data['moq'] . '</td>
                        <td>' . $data['qty_box'] . '</td>
                        <td>' . $data['box_sub'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['min'] . '</td>
                        <td>' . $data['max'] . '</td>
                        <td>' . $data['logo'] . '</td>
                        <td>' . $data['status'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
