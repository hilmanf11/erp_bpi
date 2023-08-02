<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Sto_wip extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('production/sto_wip');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('sto_wip', ["name" => $post]);
        echo json_encode($send);
    }

    public function readProductionSchedules($item_id = "")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('production_schedules', ["workorder" => $post], ["item_id" => $item_id]);
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
            $this->db->select('a.*, d.name as process_name, b.number as item_number, b.name as item_name, c.name as uom');
            $this->db->from('sto_wip a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->join('process d', 'a.process_id = d.id');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "trans_date") {
                        $this->db->like("a.trans_date", $filter->value);
                    } elseif ($filter->field == "departement") {
                        $this->db->like("a.departement", $filter->value);
                    } elseif ($filter->field == "process_name") {
                        $this->db->like("d.name", $filter->value);
                    } elseif ($filter->field == "item_number") {
                        $this->db->like("b.number", $filter->value);
                    } elseif ($filter->field == "item_name") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "workorder") {
                        $this->db->like("a.workorder", $filter->value);
                    } elseif ($filter->field == "qty") {
                        $this->db->like("a.qty", $filter->value);
                    } elseif ($filter->field == "uom") {
                        $this->db->like("c.name", $filter->value);
                    } elseif ($filter->field == "pic") {
                        $this->db->like("a.pic", $filter->value);
                    }
                }
            }
            $this->db->order_by('d.name', 'ASC');
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
                $sto_wip = $this->crud->read('sto_wip', [], ["workorder" => $post['workorder'], "item_id" => $post['item_id']]);

                if (!empty($sto_wip->item_id)) {
                    echo json_encode(array("title" => "Duplicated", "message" => "Duplicate Data", "theme" => "error"));
                } else {
                    $send = $this->crud->create('sto_wip', $post);
                    echo $send;
                }
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
            $send = $this->crud->update('sto_wip', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('sto_wip', $data);
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
                'trans_date' => $data->val($i, 2),
                'departement' => $data->val($i, 3),
                'process_number' => $data->val($i, 4),
                'item_number' => $data->val($i, 5),
                'workorder' => $data->val($i, 6),
                'qty' => $data->val($i, 7),
                'pic' => $data->val($i, 8)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/sto_wip.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/sto_wip.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/sto_wip.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data       = $this->input->post('data');
            //Cek Process Number
            $process = $this->crud->read('process', [], ["number" => $data['process_number']]);
            $item = $this->crud->read('items', [], ["number" => $data['item_number']]);
            $sto_wip = $this->crud->read('sto_wip', [], ["workorder" => $data['workorder'], "item_id" => $item->id]);

            if (empty($item->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['item_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($process->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Process No " . $data['process_number'] . " Not Found", "theme" => "error"));
            } elseif (!empty($sto_wip->item_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['item_number'] . " Duplicate Data", "theme" => "error"));
            } else {
                $postFinal = array(
                    "item_id" => $item->id,
                    "process_id" => $process->id,
                    "trans_date" => $data['trans_date'],
                    "departement" => $data['departement'],
                    "workorder" => $data['workorder'],
                    "qty" => $data['qty'],
                    "pic" => $data['pic'],
                );
                $send   = $this->crud->create('sto_wip', $postFinal);
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
            header("Content-Disposition: attachment; filename=sto_wip_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, d.name as process_name, b.number as item_number, b.name as item_name, c.name as uom');
        $this->db->from('sto_wip a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('uom c', 'b.uom_id = c.id');
        $this->db->join('process d', 'a.process_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('d.name', 'ASC');
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
                            <b>' . $config->name . '</b><br>
                            <small>STOCK OPNAME WIP</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Cut Off</th>
                <th>Departement</th>
                <th>Process</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Workorder</th>
                <th>Qty</th>
                <th>PIC</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['trans_date'] . '</td>
                    <td>' . $data['departement'] . '</td>
                    <td>' . $data['process_name'] . '</td>
                    <td>' . $data['item_number'] . '</td>
                    <td>' . $data['item_name'] . '</td>
                    <td>' . $data['workorder'] . '</td>
                    <td>' . number_format($data['qty']) . '</td>
                    <td>' . $data['pic'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
