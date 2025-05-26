<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Return_materials extends CI_Controller
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
            $this->load->view('warehouse/return_materials');
        } else {
            redirect('error_access');
        }
    }

    public function readRequestno()
    {
        $records = $this->crud->query("SELECT return_no, return_date, return_name FROM return_materials WHERE `status` = 0 GROUP BY return_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function return_no()
    {
        $datenow    = date("ymd");
        $sqlGetID   = $this->db->query("SELECT max(return_no) as kode FROM return_materials WHERE return_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "RTN-" . $datenow . "-" . $autoID;
    }

    public function return_id($return_no)
    {
        $sqlGetID   = $this->db->query("SELECT max(return_id) as kode FROM return_materials WHERE return_id like '%$return_no%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        return $return_no . "-" . $autoID;
    }

    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_return_no = $this->input->get('filter_return_no');

        $page = $this->input->post('page');
        $rows = $this->input->post('rows');
        //Pagination 1-10
        $page   = isset($page) ? intval($page) : 1;
        $rows   = isset($rows) ? intval($rows) : 10;
        $offset = ($page - 1) * $rows;
        $result = array();
        //Select Query
        $id = $_POST['id'];
        if ($id === "0") {
            $this->db->select('return_no, return_date, return_name, sum(a.qty) as qty, a.status');
            $this->db->from('return_materials a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.return_date >=', $filter_from);
                $this->db->where('a.return_date <=', $filter_to);
            }
            $this->db->like('a.return_no', $filter_return_no);
            $this->db->group_by('return_no');
            $this->db->order_by('a.return_no', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['return_no'],
                    "return_no" => $record['return_no'],
                    "return_date" => $record['return_date'],
                    "return_name" => $record['return_name'],
                    "qty" => $record['qty'],
                    "status" => $record['status'],
                    "state" => "closed",
                );
            }
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        } else {
            //b.description,
            $this->db->select('a.*, 
                b.number as item_number, 
                b.name as item_name, 
                
                b.uom,
                c.mpq');
            $this->db->from('return_materials a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('supplier_items c', 'c.item_rm_id = b.id');
            // $this->db->join('uom e', 'b.uom_id = e.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.return_date >=', $filter_from);
                $this->db->where('a.return_date <=', $filter_to);
            }
            $this->db->where('a.return_no', $id);
            $this->db->order_by('a.return_id', 'ASC');
            $records = $this->db->get()->result_array();

            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['id'],
                    "return_no" => $record['return_id'],
                    "return_date" => $record['return_date'],
                    "return_name" => $record['return_name'],
                    "item_number" => $record['item_number'],
                    "item_name" => $record['item_name'],
                    // "description" => $record['description'],
                    "workorder" => $record['workorder'],
                    "uom" => $record['uom'],
                    "qty" => $record['qty'],
                    "mpq" => $record['mpq'],
                    "status" => $record['status']
                );
            }
            echo json_encode($arr);
        }
    }
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $return_materials = $this->crud->read('return_materials', [], ["return_no" => $post['return_no'], "item_rm_id" => $post['item_rm_id']]);
                if (@$return_materials->id != "") {
                    echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $post['item_rm_id'] . " Data Duplicated", "theme" => "error"));
                } else {
                    $send   = $this->crud->create('return_materials', array_merge($post, ["return_id" => $this->return_id($post['return_no'])]));
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    public function update()
    {
        if ($this->input->post()) {
            $id   = $this->input->post('id');
            $post = $this->input->post();
            $send = $this->crud->update('return_materials', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('return_materials', $data);
        echo $send;
    }

    public function print_label($return_id)
    {
        $return_id = base64_decode($return_id);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Return Material
        // b.description,
        $this->db->select('a.*, 
                b.number as item_number, 
                b.name as item_name, 
                
                b.uom,
                c.mpq,
                CEIL(a.qty / c.mpq) as qty_label');
        $this->db->from('return_materials a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('supplier_items c', 'c.item_rm_id = b.id');
        // $this->db->join('uom e', 'b.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.return_id', $return_id);
        $this->db->order_by('a.return_id', 'ASC');
        $material = $this->db->get()->row();
        $qty_return = $material->qty;

        $return_material_labels = $this->crud->reads('return_material_labels', [], ["return_id" => $return_id]);
        if (!$return_material_labels) {
            for ($i = 0; $i < $material->qty_label; $i++) {
                //Read Label ID
                $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM return_material_labels WHERE return_id = '$return_id'");
                $rowID = $sqlGetID->row();
                $label = $rowID->kode;
                if ($label == NULL) {
                    $autoID = $return_id . sprintf("%04s", $label + 1);
                } else {
                    $urutan = (int) substr($label, -4);
                    $autoID = $return_id . sprintf("%04s", $urutan + 1);
                }
                if ($qty_return > $material->mpq) {
                    $qty = $material->mpq;
                } else {
                    $qty = $qty_return;
                }
                //Simpan Label
                $arrLabel = [
                    "return_id" => $material->return_id,
                    "label_no" => $autoID,
                    "qty" => $qty
                ];

                $this->crud->create('return_material_labels', $arrLabel);
                $qty_return = ($qty_return - $material->mpq);
            }
        }

        $this->db->select('a.*, b.return_date, c.number, c.name, d.location, d.area');
        $this->db->from('return_material_labels a');
        $this->db->join('return_materials b', 'a.return_id = b.return_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('warehouse_location_items d', 'd.item_rm_id = c.id', 'left');
        $this->db->where('a.deleted', 0);
        //$this->db->where('a.status', 0);
        $this->db->where('a.return_id', $return_id);
        $this->db->order_by('a.label_no', 'asc');
        $records = $this->db->get()->result_object();
        $html = '<html>
                    <head>
                        <title>' . $return_id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
            $html .= '<div style="width: 120mm;">';
            $no = 1;
            foreach ($records as $record) {
                if ($no == 3) {
                    $no = 1;
                }
                if ($no == 1) {
                    $padding = "padding:0 3mm 1mm 0mm;";
                } else {
                    $padding = "padding:0 0mm 1mm 4mm;";
                }
                //Generate QRcode
                $this->createQrcode($record->label_no, "assets/image/qrcode/");
                $html .= '  <div style="width: 55mm; max-height:42mm; float:left; ' . $padding . '">
                                <table id="customers" border="1" style="margin-bottom:20px;">
                                    <tr>
                                        <th colspan="2" style="font-size:9px; text-align:center;"><b>' . $config->name . '</b></th>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="height:35px;">
                                            <div style="float:left;">
                                                <small style="font-size:13px;"><b>' . $record->number . '</b></small>
                                                <br>
                                                <b style="font-size:9px;">' . $record->name . '</b>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left">
                                            <small>Quantity</small><br><b style="font-size:24px;">' . number_format($record->qty, 2, ",", ".") . '</b>
                                        </th>
                                        <th style="text-align:left">
                                            <small>Location</small><br><b style="font-size:24px;">' . $record->location . '</b>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td style="width:65%;">
                                            <small>Date</small><br>
                                            <b>' . $record->return_date . '</b><br>
                                            <small>Label No</small><br>
                                            <b style="font-size:8px;">' . $record->label_no . '</b>
                                            <b style="color:blue;">RETURN MATERIAL</b>
                                        </td>
                                        <th style="text-align:center;">
                                            <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="50"/>
                                        </th>
                                    </tr>
                                </table>
                            </div>';
                $no++;
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }

    public function print_return($return_no)
    {
        $return_no = base64_decode($return_no);
        $purchase_request_total = $this->crud->reads('return_materials', [], ["return_no" => $return_no]);
        $return_materials = $this->crud->read('return_materials', [], ["return_no" => $return_no]);
        $config = $this->db->get('config')->row();
        //Config Page
        $rows = 15;
        $page = ceil(count($purchase_request_total) / $rows);
        //Generate QRcode
        $this->createQrcode($return_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $return_materials->return_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center>
                    <h1>Press CTRL + P for Print</h1>
                    <p>Display pages for 15 rows</p>
                    <p>Paper Size A4, Layout Landscape</p>
                    <p>Margin Default, Scale 98</p>
                </center></div><div class="print">';
        //Loop Page
        $no = 1;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_rm_id, b.name as item_name, b.uom'); //b.description
            $this->db->from('return_materials a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            // $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.return_no', $return_no);
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(15, ($i * 15));
            $records = $this->db->get()->result_array();
            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10" style="padding:10px;"><img src="' . $config->favicon . '" width="50" /></th>
                                <td style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th style="text-align:right;">
                                    <img src="' . base_url('assets/image/qrcode/' . $return_materials->return_no . '.png') . '" width="50"/>
                                </th>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3><u>RETURN MATERIAL PRODUCTION</u></h3>
                                </center>
                                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                    <tr>
                                        <td width="100">Return No</td>
                                        <td width="30">:</td>
                                        <td><b>' . @$return_materials->return_no . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Return Date</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$return_materials->return_date . '</b></td>
                                    </tr>
                                </table>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th width="120">Product No</th>
                                        <th>Specification</th>
                                        <th width="60">Qty</th>
                                        <th width="50">Uom</th>
                                        <th>Remarks</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_rm_id'] . '</td>
                                <td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>
                                <td style="text-align:right">' . number_format($record['qty'], 0, ",", ".") . '</td>
                                <td>' . $record['uom'] . '</tdstyle=>
                                <td>' . $record['remarks'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>';
            if ($i + 1 != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            if (($i + 1) == $page) {
                $html .= '  <table id="customers" style="margin-top:20px;">
                                <tr>
                                    <th width="200" style="text-align:center;">Prepared By</th>
                                    <th width="200" style="text-align:center;">Knowed By</th>
                                    <th width="200" style="text-align:center;">Approved By</th>
                                </tr>
                                <tr>
                                    <th style="height:80px;"></th>
                                    <th style="height:80px;"></th>
                                    <th style="height:80px;"></th>
                                </tr>
                                <tr>
                                    <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                                    <th style="height:20px; text-align:center;"></th>
                                    <th style="height:20px; text-align:center;"></th>
                                </tr>
                            </table>';
            }
            $html .= '</div></div>';
        }
        $html .= '</div><script>window.print()</script>';
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=return_materials_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_return_no = $this->input->get('filter_return_no');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as item_rm_id, b.name as item_name, b.uom');
        $this->db->from('return_materials a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        // $this->db->join('uom f', 'b.uom_id = f.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.return_date >=', $filter_from);
            $this->db->where('a.return_date <=', $filter_to);
        }
        $this->db->like('a.return_no', $filter_return_no);
        $this->db->order_by('a.return_no', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' .  $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>RETURN MATERIAL FROM PRODUCTION TO WAREHOUSE</small>
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
                <th>Return No</th>
                <th>Return Date</th>
                <th>Return Name</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Uom</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if ($data['status'] == 0) {
                $status = "OPEN";
            } else {
                $status = "CLOSED";
            }
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['return_no'] . '</td>
                        <td>' . $data['return_date'] . '</td>
                        <td>' . $data['return_name'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['qty'], 2) . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $status . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
