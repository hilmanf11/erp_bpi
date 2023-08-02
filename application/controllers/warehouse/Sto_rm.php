<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Sto_rm extends CI_Controller
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
            $this->load->view('warehouse/sto_rm');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('sto_rm', ["name" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filter_date = $this->input->get('filter_date');
            $filter_item = $this->input->get('filter_item');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.number as item_number, b.name as item_name, c.name as uom');
            $this->db->from('sto_rm a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.trans_date', $filter_date);
            $this->db->like('a.item_id', $filter_item);
            $this->db->order_by('b.number', 'ASC');

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
                $sto_rm = $this->crud->read('sto_rm', [], ["item_id" => $post['item_id'], "trans_date" => $post['trans_date']]);

                if (!empty($sto_rm->item_id)) {
                    echo json_encode(array("title" => "Duplicated", "message" => "Duplicate Data", "theme" => "error"));
                } else {
                    $send = $this->crud->create('sto_rm', $post);
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
            $send = $this->crud->update('sto_rm', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('sto_rm', $data);
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
        for ($i = 5; $i <= $total_row; $i++) {
            $datas[] = array(
                'trans_date' => $data->val(2, 3),
                'departement' => $data->val(3, 3),
                'item_number' => $data->val($i, 2),
                'qty' => $data->val($i, 3),
                'remark' => $data->val($i, 4),
                'pic' => $data->val($i, 5)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/sto_rm.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/sto_rm.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/sto_rm.txt";
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
            $data = $this->input->post('data');
            //Cek Process Number
            $item = $this->crud->read('items', [], ["number" => $data['item_number']]);
            $sto_rm = $this->crud->read('sto_rm', [], ["item_id" => $item->id, "trans_date" => $data['trans_date']]);

            if (empty($item->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['item_number'] . " Not Found", "theme" => "error"));
            } elseif (!empty($sto_rm->item_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['item_number'] . " Duplicate Data", "theme" => "error"));
            } else {
                $date = $data['trans_date'];

                $records = $this->crud->query("SELECT
                    a.id,
                    a.number, 
                    a.name, 
                    b.name as prodfam, 
                    c.name as uom, 
                    COALESCE(0,0) as begin_stock,
                    (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty, 0)) as qty_in,
                    f.qty as qty_out,
                    (COALESCE(SUM(e.qty),0) - COALESCE(f.qty, 0) + COALESCE(g.return_qty, 0)) as end_stock
                FROM items a 
                JOIN item_familys b ON a.item_family_id = b.id
                JOIN uom c ON a.uom_id = c.id
                LEFT JOIN purchase_order_receipts d ON a.id = d.item_id and d.receipt_date <= '$date'
                LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
                LEFT JOIN (SELECT item_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$date' GROUP BY item_id) f ON a.id = f.item_id
                LEFT JOIN (SELECT a.item_id, SUM(c.qty) as return_qty
                    FROM return_materials a 
                    JOIN return_material_labels b ON a.return_id = b.return_id
                    JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                    WHERE a.return_date <=  '$date'
                    GROUP BY a.item_id) g ON a.id = g.item_id
                WHERE a.id = '$item->id'
                GROUP BY a.id
                ORDER BY a.number");

                $postFinal = array(
                    "item_id" => $item->id,
                    "trans_date" => $data['trans_date'],
                    "departement" => $data['departement'],
                    "stock" => @$records[0]->end_stock,
                    "qty" => $data['qty'],
                    "balance" => (@$records[0]->end_stock - $data['qty']),
                    "remark" => $data['remark'],
                    "pic" => $data['pic'],
                );
                $send   = $this->crud->create('sto_rm', $postFinal);
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
            header("Content-Disposition: attachment; filename=sto_rm_$format.xls");
        }

        $filter_date = $this->input->get('filter_date');
        $filter_item = $this->input->get('filter_item');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_number, b.name as item_name, c.name as uom');
        $this->db->from('sto_rm a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('uom c', 'b.uom_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.trans_date', $filter_date);
        $this->db->like('a.item_id', $filter_item);
        $this->db->order_by('b.number', 'ASC');
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
                            <small>STOCK OPNAME RAW MATERIAL</small>
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
        <table style="font-size:12px;">
            <tr>
                <td width="100">Cut Off</td>
                <td width="10">:</td>
                <td>' . date("d F Y", strtotime($filter_date)) . '</td>
            </tr>
            <tr>
                <td>Departement</td>
                <td>:</td>
                <td>' . @$records[0]['departement'] . '</td>
            </tr>
        </table>
        <br>
        <table id="customers" border="1">
            <tr>
                <th width="20">No</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Ending Stock</th>
                <th>Qty</th>
                <th>Deviasi</th>
                <th>Remark</th>
                <th>PIC</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['item_number'] . '</td>
                    <td>' . $data['item_name'] . '</td>
                    <td>' . number_format($data['stock']) . '</td>
                    <td>' . number_format($data['qty']) . '</td>
                    <td>' . number_format($data['balance']) . '</td>
                    <td>' . $data['remark'] . '</td>
                    <td>' . $data['pic'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
