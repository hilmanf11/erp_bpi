<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Scraps extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/scraps');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('scraps', ["name" => $post]);
        echo json_encode($send);
    }

    public function readDocument()
    {
        $send = $this->crud->query("SELECT DISTINCT document FROM scraps order by document desc");
        echo json_encode($send);
    }

    public function readItem()
    {
        $send = $this->crud->query("SELECT b.id, b.number, b.name, b.uom, SUM(a.qty) as qty 
        FROM scraps a 
        JOIN item_rm b ON a.item_rm_id = b.id
        -- JOIN uom c ON b.uom_id = c.id
        GROUP BY b.number 
        order by b.number asc");
        echo json_encode($send);
    }

    public function scraps_no($trans_date)
    {
        $trans_date = base64_decode($trans_date);
        $year       = date("Y", strtotime($trans_date));
        $datenow    = date("ymd", strtotime($trans_date));
        $sqlGetID   = $this->db->query("SELECT max(document) as kode FROM scraps WHERE trans_date like '%$year%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }

        echo "SCP-" . $datenow . "-" . $autoID;
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_document = $this->input->get('filter_document');
            $filter_family_id = $this->input->get('filter_family_id');
            $filter_item_rm_id = $this->input->get('filter_item_rm_id');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.number as item_number, b.name as item_name');
            $this->db->from('scraps a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.document', $filter_document);
            $this->db->like('b.item_family_id', $filter_family_id);
            $this->db->like('b.id', $filter_item_rm_id);
            $this->db->order_by('a.trans_date', 'DESC');
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

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();

                $scraps = $this->crud->reads("scraps", [], ["item_rm_id" => $post['item_rm_id'], "document" => $post['document']]);
                if (count($scraps) > 0) {
                    show_error("Duplicate");
                } else {
                    $send = $this->crud->create('scraps', $post);
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
            $send = $this->crud->update('scraps', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('scraps', ["id" => $data['id']]);
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
                'document' => $data->val($i, 3),
                'item_number' => $data->val($i, 4),
                'type' => $data->val($i, 5),
                'qty' => $data->val($i, 6),
                'uom' => $data->val($i, 7),
                'remarks' => $data->val($i, 8)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('excel/failed/scraps.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/scraps.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "excel/failed/scraps.txt";
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
            $item = $this->crud->read('item_rm', [], ["number" => $data['item_number']]);
            $scraps = $this->crud->read('scraps', [], ["trans_date" => $data['trans_date'], "item_rm_id" => $item->id]);

            if (empty($item->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['item_number'] . " Not Found", "theme" => "error"));
            } elseif (!empty($scraps->item_rm_id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['item_number'] . " Duplicate Data", "theme" => "error"));
            } else {
                $postFinal = array(
                    "item_rm_id" => $item->id,
                    "trans_date" => $data['trans_date'],
                    "document" => $data['document'],
                    "type" => $data['type'],
                    "qty" => $data['qty'],
                    "uom" => $data['uom'],
                    "remarks" => $data['remarks'],
                );
                $send   = $this->crud->create('scraps', $postFinal);
                echo $send;
            }
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=scraps_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_document = $this->input->get('filter_document');
        $filter_family_id = $this->input->get('filter_family_id');
        $filter_item_rm_id = $this->input->get('filter_item_rm_id');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $records = $this->crud->query("SELECT
            a.id,
        	a.number, 
            a.name, 
            b.name as prodfam, 
            a.uom, 
        	f.begin_in,
            g.begin_out,
            COALESCE(d.qty_in, 0) as qty_in,
            COALESCE(e.qty_out, 0) as qty_out
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty_in FROM scraps WHERE `type` = 'IN' AND document like '%$filter_document%' AND DATE_FORMAT(trans_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) d ON a.id = d.item_rm_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty_out FROM scraps WHERE `type` = 'OUT' AND document like '%$filter_document%' AND DATE_FORMAT(trans_date, '%Y-%m-%d') between '$filter_from' and '$filter_to' GROUP BY item_rm_id) e ON a.id = e.item_rm_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as begin_in FROM scraps WHERE `type` = 'IN' AND document like '%$filter_document%' AND DATE_FORMAT(trans_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as begin_out FROM scraps WHERE `type` = 'OUT' AND document like '%$filter_document%' AND DATE_FORMAT(trans_date, '%Y-%m-%d') < '$filter_from' GROUP BY item_rm_id) g ON a.id = g.item_rm_id
        WHERE b.number != 'FG' and a.item_family_id like '%$filter_family_id%' and a.id like '%$filter_item_rm_id%'
        GROUP BY a.id
        ORDER BY a.number");

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>SCRAP TRANSACTION</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Product Family</th>
                    <th>Uom</th>
                    <th>Begin</th>
                    <th>In</th>
                    <th>Out</th>
                    <th>Ending Stock</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $item_rm_id = $data->id;

            $this->db->select('a.*, b.number as item_number, b.name as item_name');
            $this->db->from('scraps a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.document', $filter_document);
            $this->db->where('b.id', $item_rm_id);
            $this->db->order_by('b.number', 'ASC');
            $this->db->order_by('a.trans_date', 'ASC');
            $details = $this->db->get()->result_object();

            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data->number . '</td>
                            <td>' . $data->name . '</td>
                            <td>' . $data->prodfam . '</td>
                            <td style="text-align:center">' . $data->uom . '</td>
                            <td style="text-align:right">' . number_format($data->begin_in - $data->begin_out, 2) . '</td>
                            <td style="text-align:right">' . number_format($data->qty_in, 2) . '</td>
                            <td style="text-align:right">' . number_format($data->qty_out, 2) . '</td>
                            <td style="text-align:right">' . number_format(($data->begin_in - $data->begin_out) + ($data->qty_in - $data->qty_out), 2) . '</td>
                        </tr>';
            $html .= '  <tr>
                            <td colspan="13" style="background:#D1FFC6;"><b>DETAIL OF ' . $data->number . ' - ' . $data->name . '</b></td>
                        </tr>
                        <tr>
                            <th width="20">No</th>
                            <th>Trans Date</th>
                            <th>Document No</th>
                            <th>Remarks</th>
                            <th>Uom</th>
                            <th>Begin</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Balance</th>
                        </tr>';
            $begin = 0;
            $balance = 0;
            $noDetail = 1;
            foreach ($details as $detail) {
                if ($detail->type == "IN") {
                    $in = $detail->qty;
                    $out = 0;
                    $balance = ($begin + $in - $out);
                } else {
                    $in = 0;
                    $out = $detail->qty;
                    $balance = ($begin + $in - $out);
                }

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . "." . $noDetail . '</td>
                                <td>' . $detail->trans_date . '</td>
                                <td>' . $detail->document . '</td>
                                <td>' . $detail->remarks . '</td>
                                <td style="text-align:center">' . $detail->uom . '</td>
                                <td style="text-align:right;">' . number_format($begin, 2) . '</td>
                                <td style="text-align:right;">' . number_format($in, 2) . '</td>
                                <td style="text-align:right;">' . number_format($out, 2)  . '</td>
                                <td style="text-align:right;">' . number_format($balance, 2)  . '</td>
                            </tr>';
                $begin = ($begin + $in - $out);
                $noDetail++;
            }

            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
