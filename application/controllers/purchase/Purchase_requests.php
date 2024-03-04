<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_requests extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Part No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_requests');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $request_no = $this->input->get('request_no');
        //Select Query
        $this->db->select('a.*, 
            b.number as item_number, 
            b.name as item_name, 
            b.uom, 
            c.name as category_name');
        $this->db->from('purchase_requests a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.request_no', $request_no);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readRequestno()
    {
        $records = $this->crud->query("SELECT request_no, request_date, request_name FROM purchase_requests WHERE `status` = '0' GROUP BY request_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readCategoryno()
    {
        $request_no = $this->input->get('request_no');
        $supplier_id = $this->input->get('supplier_id');
        $records = $this->crud->query("SELECT d.id, d.number, d.name
            FROM purchase_requests a
            JOIN supplier_items b on a.item_rm_id = b.item_rm_id
            JOIN item_rm c on b.item_rm_id = c.id
            JOIN item_familys d on c.item_family_id = d.id
            WHERE a.status = '0' and a.request_no = '$request_no' and b.supplier_id = '$supplier_id'
            GROUP BY d.number");
        echo json_encode($records);
    }

    public function request_no($category = "")
    {
        $datenow    = $category . date("ymd");
        $sqlGetID   = $this->db->query("SELECT max(request_no) as kode FROM purchase_requests WHERE request_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "PR-" . $datenow . "-" . $autoID;
    }

    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_familys = $this->input->get('filter_item_familys');

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
            $this->db->select('request_no, request_date, expected_date, request_name, division, sum(a.qty) as qty, a.status, c.id as item_family_id, c.number as item_family_number');
            $this->db->from('purchase_requests a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', 'b.item_family_id = c.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.request_date >=', $filter_from);
                $this->db->where('a.request_date <=', $filter_to);
            }
            $this->db->like('a.request_no', $filter_request_no);
            $this->db->like('c.id', $filter_item_familys);
            $this->db->like('c.item_category_id', $filter_item_category);
            $this->db->group_by('request_no');
            $this->db->order_by('a.updated_date', 'DESC');
            $this->db->order_by('a.request_date', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['request_no'],
                    "item_family_id" => $record['item_family_id'],
                    "item_family_number" => $record['item_family_number'],
                    "request_no" => $record['request_no'],
                    "request_date" => $record['request_date'],
                    "expected_date" => $record['expected_date'],
                    "request_name" => $record['request_name'],
                    "division" => $record['division'],
                    "qty" => $record['qty'],
                    "status" => $record['status'],
                    "state" => "closed",
                    "datatable" => 1
                );
            }
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        } else {
            $this->db->select('a.*, 
                b.number as item_number, 
                b.name as item_name, 
                b.uom, 
                d.po_no, 
                c.name as category_name');
            $this->db->from('purchase_requests a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', 'b.item_family_id = c.id');
            $this->db->join('purchase_orders d', 'a.request_no = d.request_no and a.item_rm_id = d.item_rm_id', 'left');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.request_date >=', $filter_from);
                $this->db->where('a.request_date <=', $filter_to);
            }
            $this->db->where('a.request_no', $id);
            $this->db->like('c.id', $filter_item_familys);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function datatable_updates(){
        $request_no = base64_decode($this->input->get('request_no'));
        $records = $this->crud->query("SELECT a.id, c.number as item_number, c.name as item_name, c.id as item_rm_id, a.qty, a.remarks
            FROM purchase_requests a
            JOIN item_rm c on a.item_rm_id = c.id
            WHERE a.status = '0' and a.request_no = '$request_no'
            GROUP BY c.number");
        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
                $post   = $this->input->post();
                $purchase_request_item = $this->crud->read('purchase_requests', [], ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']]);
                if (@$purchase_request_item->id != "") {
                    $send = $this->crud->update('purchase_requests', ["request_no" => $post['request_no'], "item_rm_id" => $post['item_rm_id']], $post);
                } else {
                    $send = $this->crud->create('purchase_requests', $post);
                }

                echo $send;
        
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id   = $this->input->post('id');
            $post = $this->input->post();
            $send = $this->crud->update('purchase_requests', ["id" => $id], [
                "qty" => $post['qty'], 
                "remarks" => $post['remarks'],
                "division" => $post['division']
            ]);

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_requests', $data);
        echo $send;
    }

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
        $category = $data->val(2, 3);
        $item_categories = $this->crud->read('item_categories', [], ["number" => $category]);
        if (!empty($item_categories)) {
            $datenow    = $item_categories->number . date("ymd");
            $sqlGetID   = $this->db->query("SELECT max(request_no) as kode FROM purchase_requests WHERE request_no like '%$datenow%'");
            $rowID      = $sqlGetID->row();
            $kode       = $rowID->kode;
            if ($kode == NULL) {
                $autoID = sprintf("%04s", $kode + 1);
            } else {
                $urutan = (int) substr($kode, -4);
                $urutan++;
                $autoID = sprintf("%04s", $urutan);
            }
            $request_no = "PR-" . $datenow . "-" . $autoID;
            for ($i = 4; $i <= $total_row; $i++) {
                $datas[] = array(
                    'request_no' => $request_no,
                    'request_date' => $data->val($i, 2),
                    'expected_date' => $data->val($i, 3),
                    'request_name' => $data->val($i, 4),
                    'division' => $data->val($i, 5),
                    'product_number' => $data->val($i, 6),
                    'qty' => $data->val($i, 7),
                    'remarks' => $data->val($i, 8)
                );
            }
            $datas['total'] = count($datas);
            echo json_encode($datas);
        } else {
            echo json_encode(array("title" => "Not Found", "message" => "Product Family " . $category . " Not Found Data", "theme" => "error"));
        }
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/purchase_requests.txt');
    }
    
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/purchase_requests.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/purchase_requests.txt";
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
            $item = $this->crud->read('item_rm', [], ["number" => $data['product_number']]);
            $purchase_requests = $this->crud->read('purchase_requests', [], ["request_no" => $data['request_no'], "item_rm_id" => $item->id]);
            
            if (empty($item->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['product_number'] . " Not Found", "theme" => "error"));
            } elseif (!empty($purchase_requests->id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['product_number'] . " Duplicate Data", "theme" => "error"));
            } else {
                $send   = $this->crud->create('purchase_requests',(["item_rm_id" => $item->id, "request_no" => $data['request_no'], "request_date" => $data['request_date'],"request_name" => $data['request_name'] ,"qty" => $data['qty'], "remarks" => $data['remarks']]));
                echo $send;
            }
        }
    }

    public function print_request($request_no)
    {
        $request_no = base64_decode($request_no);
        $purchase_request_total = $this->crud->reads('purchase_requests', [], ["request_no" => $request_no]);
        $purchase_requests = $this->crud->read('purchase_requests', [], ["request_no" => $request_no]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 15;
        $page = ceil(count($purchase_request_total) / $rows);
        //Generate QRcode
        $this->createQrcode($request_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $purchase_requests->request_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
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
            $this->db->select('a.*, b.number as item_rm_id, b.name as item_name, b.uom');
            $this->db->from('purchase_requests a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.request_no', $request_no);
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(15, ($i * 15));
            $records = $this->db->get()->result_array();
            $html .= '  <table style="width:100%;" border="1">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $purchase_requests->request_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_purchase_request . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_purchase_request . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print Date</td>
                                            <td>:</td>
                                            <td>' . date("Y-m-d H:i") . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print By</td>
                                            <td>:</td>
                                            <td>' . $this->session->name . '</td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3><u>PURCHASE ORDER REQUESTION</u></h3>
                                </center>
                                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                    <tr>
                                        <td width="100">Request No</td>
                                        <td width="30">:</td>
                                        <td><b>' . @$purchase_requests->request_no . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Request Date</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$purchase_requests->request_date . '</b></td>
                                    </tr>
                                </table>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th width="120">Product No</th>
                                        <th>Product Name</th>
                                        <th width="60">Qty</th>
                                        <th width="50">Uom</th>
                                        <th>Remarks</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_rm_id'] . '</td>
                                <td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>
                                <td style="text-align:right">' . number_format($record['qty'], 2, ",", ".") . '</td>
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
            header("Content-Disposition: attachment; filename=purchase_requests_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_item_familys = $this->input->get('filter_item_familys');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as item_rm_id, b.name as item_name, c.name as item_family_name, e.po_no, b.uom');
        $this->db->from('purchase_requests a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('purchase_orders e', 'a.request_no = e.request_no and a.item_rm_id = e.item_rm_id', 'left');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.request_date >=', $filter_from);
            $this->db->where('a.request_date <=', $filter_to);
        }
        $this->db->like('a.request_no', $filter_request_no);
        $this->db->like('c.id', $filter_item_familys);
        $this->db->order_by('a.request_date', 'DESC');
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
                                <small>PURCHASE REQUEST</small>
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
                <th>Request No</th>
                <th>Request Date</th>
                <th>Expected Date</th>
                <th>Request Name</th>
                <th>Division</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Uom</th>
                <th>PO No</th>
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
                        <td>' . $data['request_no'] . '</td>
                        <td>' . $data['request_date'] . '</td>
                        <td>' . $data['expected_date'] . '</td>
                        <td>' . $data['request_name'] . '</td>
                        <td>' . $data['division'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['qty'], 2) . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $status . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
    //
}
