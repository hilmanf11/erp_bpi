<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Bpm extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Item ID', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/bpm');
        } else {
            redirect('error_access');
        }
    }

    public function readPeriod()
    {
        $records = $this->crud->query("SELECT `period` FROM bpm WHERE `status` = '0' GROUP BY `period`");
        echo json_encode($records);
    }

    public function readWp($period)
    {
        $records = $this->crud->query("SELECT workorder FROM bpm WHERE `status` = '0' and `period` = '$period' GROUP BY workorder");
        echo json_encode($records);
    }

    public function readRequestNo($period, $workorder)
    {
        $workorder = base64_decode($workorder);
        $records = $this->crud->query("SELECT request_no FROM bpm WHERE status = '0' and `period` = '$period' and workorder = '$workorder' GROUP BY `request_no`");
        echo json_encode($records);
    }

    public function readRequestNos()
    {
        $records = $this->crud->query("SELECT request_no FROM bpm WHERE status = '0' GROUP BY `request_no`");
        echo json_encode($records);
    }

    // public function readItemRm()
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";
    //     $records = $this->crud->query("SELECT id, number, name, uom 
    //     FROM item_rm 
    //     WHERE (item_category_id = 'C01' or (item_category_id = 'C09' AND item_family_id = 'P23')) and `number` like '%$post%' or `name` like '$post'
    //     ORDER BY `number` ASC");
    //     echo json_encode($records);
    // }

    public function readItemRm()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT a.id, a.number, a.name, a.uom, b.mpq 
        FROM item_rm a
        LEFT JOIN supplier_items b ON a.id = b.item_rm_id
        WHERE b.share_order = '100' and a.item_category_id != 'C03' and a.number like '%$post%' or a.name like '$post'
        ORDER BY `number` ASC");
        echo json_encode($records);
    }

    public function readProduct($product_family_id)
    {
        $records = $this->crud->query("SELECT b.number, b.name, b.id FROM bpm a JOIN item_rm b ON a.item_rm_id = b.id WHERE b.item_family_id = '$product_family_id' GROUP BY b.id ORDER BY b.number asc");
        echo json_encode($records);
    }

    public function readProducts()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT number, name, id FROM item_rm WHERE status = '0' and `number` like '%$post%' or `name` like '$post'
        GROUP BY id ORDER BY number asc");
        echo json_encode($records);
    }

    public function request_no($trans_date)
    {
        $trans_date = base64_decode($trans_date);
        $datenow    = date("ymd", strtotime($trans_date));
        $sqlGetID   = $this->db->query("SELECT max(request_no) as kode FROM bpm WHERE request_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "BPM-" . $datenow . "-" . $autoID;
    }

    public function datatableUpdate($request_no){
        $request_no = base64_decode($request_no);

        //Select Query
        $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
        $this->db->from('bpm a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id','left');
        // $this->db->join('uom c', 'b.uom_id = c.id');
        $this->db->where('a.request_no', $request_no);

        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            // $filter_period = $this->input->get('filter_period');
            // $filter_workorder   = $this->input->get('filter_workorder');
            $filter_request_no = $this->input->get('filter_request_no');
            $filter_product_family = $this->input->get('filter_product_family');
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));
            $filter_kanban_date = $this->input->get('filter_kanban_date');
            $filter_status = $this->input->get('filter_status');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10; 
            $offset = ($page - 1) * $rows;
            $result = array();
            $id = $_POST['id'];

            if ($id === "0") {
                //Select Query
                $this->db->select('a.*,b.uom, COUNT(a.status) as total_status, i.total_status_open,
                    g.total_status_close');
                $this->db->from('bpm a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('(SELECT request_no, COUNT(status) as total_status_close FROM bpm WHERE status = 1 GROUP BY request_no) g', 'a.request_no = g.request_no', 'left');
                $this->db->join('(SELECT request_no, COUNT(status) as total_status_open FROM bpm WHERE status = 0 GROUP BY request_no) i', 'a.request_no = i.request_no', 'left');
                $this->db->where('a.deleted', 0);
                // $this->db->where('a.status', 0);
                if ($filter_request_no != "") {
                    $this->db->where('a.request_no', $filter_request_no);
                }
                if($filter_product_family != ""){
                    $this->db->where('b.item_family_id', $filter_product_family);
                }
                if($filter_product_no != ""){
                    $this->db->where('a.item_rm_id', $filter_product_no);
                }
                if($filter_kanban_date != ""){
                    $this->db->where('a.request_date', $filter_kanban_date);
                }
                if($filter_status != ""){
                    $this->db->where('a.status', $filter_status);
                }
                $this->db->group_by('a.request_no');
                $this->db->order_by('a.request_no', 'DESC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {

                    if ($record['total_status'] == $record['total_status_open']) {
                        $status = "0";
                    } elseif ($record['total_status'] == $record['total_status_close']) {
                        $status = "1";
                    } elseif ($record['total_status_open'] >= 1) {
                        $status = "0";
                    } elseif ($record['total_status_close'] >= 1) {
                        $status = "1";
                    } else {
                        $status = "0";
                    }

                    $arr[] = array(
                        "id" => $record['request_no'],
                        "request_no" => $record['request_no'],
                        "request_date" => $record['request_date'],
                        "request_name" => $record['request_name'],
                        "item_rm_id" => $record['item_rm_id'],
                        "item_fg_id" => $record['item_fg_id'],
                        "status" => $status,
                        "state" => "closed"
                    );
                }
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                //Select Query
                $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom, COALESCE(SUM(c.qty),0) as qty_actual');
                $this->db->from('bpm a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('scan_item_bpm c', 'a.request_id = c.request_id and a.item_rm_id = c.item_rm_id','left');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.request_no', $id);
                $this->db->group_by('a.id');
                if($filter_product_family != ""){
                    $this->db->where('b.item_family_id', $filter_product_family);
                }
                if($filter_product_no != ""){
                    $this->db->where('a.item_rm_id', $filter_product_no);
                }
                if($filter_kanban_date != ""){
                    $this->db->where('a.request_date', $filter_kanban_date);
                }
                if($filter_status != ""){
                    $this->db->where('a.status', $filter_status);
                }
                $this->db->order_by('a.request_no', 'DESC');
                $records = $this->db->get()->result_array();

                foreach ($records as $record) {

                    if($record['qty'] == $record['qty_actual']){
                        $status = '1';
                    }else{
                        $status = '0';
                    }

                    $arr[] = array(
                        "id" => $record['request_id'],
                        "request_no" => $record['request_no'],
                        "request_date" => $record['request_date'],
                        "request_name" => $record['request_name'],
                        "item_rm_id" => $record['item_rm_id'],
                        "item_number" => $record['item_number'],
                        "item_name" => $record['item_name'],
                        "qty" => $record['qty'],
                        "qty_actual" => $record['qty_actual'],
                        "label" => $record['label'],
                        "uom" => $record['uom'],
                        "remarks" => $record['remarks'],
                        "status" => $status,
                        "created_by" => $record['created_by'],
                        "created_date" => $record['created_date'],
                        "updated_by" => $record['updated_by'],
                        "updated_date" => $record['updated_date']
                    );
                }
                $result = !empty($arr) ? $arr : [];
                echo json_encode($result);
            }
        }
    }

    public function request_id($receipt_no)
    {
        $sqlGetID   = $this->db->query("SELECT max(request_id) as kode FROM bpm WHERE request_id like '%$receipt_no%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        return $receipt_no . "-" . $autoID;
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                if ($post['qty'] == 0) {
                    echo json_encode(array("title" => "Qty 0", "message" => " Qty is 0", "theme" => "error"));
                } else {
                    $request_no = $post['request_no'];
                    $item_rm_id = $post['item_rm_id'];

                    $datas = $this->crud->reads('bpm', [], ["request_no" => $request_no, "item_rm_id" => $item_rm_id]);

                    if(count($datas) > 0){
                        $send = $this->crud->update('bpm', ["request_no" => $request_no, "item_rm_id" => $item_rm_id], $post);
                        echo $send;
                    }else{
                        $send   = $this->crud->create('bpm', array_merge($post, ["request_id" => $this->request_id($post['request_no'])]));
                        echo $send;
                    }
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function print_label($request_id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $request_id = base64_decode($request_id);
        $po_receipt = $this->crud->read('bpm', [], ["request_id" => $request_id]);
        $qty_receipt = $po_receipt->qty;
        $item_rm_id = $po_receipt->item_rm_id;
        $lot_no = $po_receipt->lot_no;

        $lot_no_month = substr($lot_no, 3, 2);
        $lot_no_year = substr($lot_no, 5, 2);

        // var_dump($po_receipt);
        // die;

        //Cek Label
        $po_receipt_label = $this->crud->reads('bpm_labels', [], ["request_id" => $request_id]);
        if (!$po_receipt_label) {
            for ($i = 0; $i < $po_receipt->label; $i++) {
                //Read Label ID
                $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM bpm_labels WHERE request_id = '$request_id'");
                $rowID = $sqlGetID->row();
                $label = $rowID->kode;
                if ($label == NULL) {
                    $autoID = $request_id . sprintf("%04s", $label + 1);
                } else {
                    $urutan = (int) substr($label, -4);
                    $autoID = $request_id . sprintf("%04s", $urutan + 1);
                }
                if ($qty_receipt > $po_receipt->packing_qty) {
                    $qty = $po_receipt->packing_qty;
                } else {
                    $qty = $qty_receipt;
                }
                
                $date = new DateTime($po_receipt->request_date);
                $p_month = $date->format('m'); 
                $p_year = $date->format('y');               
                //Simpan Label
                $post   = $this->input->post();
                $arrLabel = [
                    "request_id" => $po_receipt->request_id,
                    "label_no" => $autoID,
                    "qty" => $qty,
                    "item_rm_id" => $item_rm_id,
                    "lot_no" => $lot_no,
                    "p_month" => $p_month,
                    "p_year" => $p_year 
                ];
                $send = $this->crud->create('bpm_labels', $arrLabel);
                $qty_receipt = ($qty_receipt - $po_receipt->packing_qty);
            }
        }
        
        $this->db->select('a.*, c.number, c.name, d.location, d.area, c.color, c.uom, a.lot_no as lotno');
        $this->db->from('bpm_labels a');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('warehouse_location_items d', 'd.item_rm_id = c.id', 'left');
        $this->db->join('item_familys e', 'c.item_family_id = e.id');
        $this->db->where('a.deleted', 0);
        //$this->db->where('a.status', 0);
        $this->db->where('a.request_id', $request_id);
        $this->db->order_by('a.label_no', 'asc');
        $records = $this->db->get()->result_object();
        $html = '<html>
                    <head>
                        <title>' . $request_id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
            $html .= '<div style="width: 55mm;">';
            // $no = 1;
            foreach ($records as $record) {
                // if ($no == 3) {
                //     $no = 1;
                // }
                // if ($no == 1) {
                    $padding = "padding:3mm 5mm 3mm 3mm;";
                // } else {
                //     $padding = "padding:5mm 3mm 0mm 3mm;";
                // }
                //Generate QRcode
                $this->createQrcode($record->label_no, "assets/image/qrcode/");
                $html .= '  <div style="max-width: 50mm; max-height:40mm; float:left; ' . $padding . '">
                                <table id="customers" border="1" style="margin-bottom:20px;">
                                    <tr>   
                                        <th colspan="3" style="font-size:8px; text-align:center;">
                                            <img src="' . base_url('assets/image/bpi_logo.png') . '" width="10" style="float: left; margin-right: 5px;">
                                            <b>' . $config->name . '</b>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="height:35px;">
                                            <div style="float:left;">
                                                <small style="font-size:10px;"><b>' . $record->number . '</b></small>
                                                <br>
                                                <b style="font-size:9px;">' . $record->name . " - " .$record->color.'</b>
                                            </div>
                                            
                                            <div style="float:right;">
                                                <small style="font-size:14px;"><b>' . $lot_no_month . '</b></small><small style="font-size:10px;"><b>' ." - ". $lot_no_year . '</b></small>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="text-align:left">
                                            <small style="font-size:10px">Quantity<br><b style="font-size:12px;">' . number_format($record->qty, 2) . '</b></small>
                                            <small style="font-size:13px; float: right;"><b>'. $record->uom . '</b></small>
                                            </th>
                                        <th style="text-align:left">
                                            <small style="font-size:7px">Location </small><b style="font-size:8px;">' . $record->location . '</b><br>
                                            <small style="font-size:7px">Lot No. </small><b style="font-size:8px;">' . $lot_no . '</b>
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th style="text-align:left">
                                            <div style="display: inline-block;">
                                                <small style="font-size:9px">Date :</small><br> 
                                                <b style="font-size:7px;">' . $po_receipt->request_date . '</b>
                                             
                                            </div>
                                            <div style="display: inline-block; float:right;">
                                                <img src="' . base_url('assets/image/qc_passed.png') . '" width="30" style="float: right; margin-right: 5px; margin-top: 5px;">
                                            </div>
                                            <div style="display: inline-block;">
                                                <small style="font-size:9px">Label No :</small><br>
                                                <b style="font-size:7px;">' . $record->label_no . '</b>
                                            </div>
                                        </th>
                                        <th style="text-align:center;">
                                            <small style="font-size:4px">QC Passed By : ' . $this->session->username . '</small><br>
                                            <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="55"/>
                                        </th>
                                    </tr>
                                </table>
                            </div>';
                // $no++;
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('bpm', ["request_id" => $data['request_id']]);
        $delete = $this->crud->delete('bpm_labels', ["request_id" => $data['request_id'], "item_rm_id" => $data['item_rm_id']]);
        echo $send;
    }

     public function print_doc($request_no)
    {
        $kanbans = $this->crud->reads('bpm', [], ["request_no" => base64_decode($request_no)]);
        $kanban = $this->crud->read('bpm', [], ["request_no" => base64_decode($request_no)]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        $rows = 20;
        $page = ceil(count($kanbans) / $rows);
        //Generate QRcode
        $this->createQrcode($kanban->request_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $kanban->request_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: left;color: black;
                        }
                        @media screen {
                            .print {
                                display: none !important;
                            }
                        }
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }
                    </style>
                    <body>
                    <div style="margin:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <p>Display pages for 8 rows</p>
                            <p>Paper Size A5, Layout Landscape</p>
                            <p>Margin Default, Scale 80</p>
                        </center>
                    </div>
                    <div class="print">';
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
            $this->db->from('bpm a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.request_no', base64_decode($request_no));
            $this->db->limit(20, ($i * 20));
            $records = $this->db->get()->result_array();
            $html .= '<table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="450" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <td width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $kanban->request_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_material_requestion . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_material_requestion . '</td>
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
                                </td>
                            </tr>
                        </table>
                        <div style="border: 1px solid #ddd; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3><u>BPM</u></h3>
                                </center>
                                <div style="float:left; width:30%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Doc No</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$kanban->request_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Doc Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @date("d F Y", strtotime($kanban->request_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Created By</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$kanban->request_name . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th>No</th>
                                        <th>Part No</th>
                                        <th>Part Name</th>
                                        <th>Qty</th>
                                        <th>Remarks</th>
                                    </tr>';
            $no = 1;
            foreach ($records as $record) {
                $item_rm_id = $record['item_rm_id'];

                $html .= '  <tr>
                                <td>' . $no . '</td>
                                <td>' . $record['item_number'] . '</td>
                                <td>' . $record['item_name'] . '</td>
                                <td style="text-align:right;">' . $record['qty'] . '</td>
                                <td>' . $record['remarks'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>
                <br>
                <table id="customers">
                    <tr>
                        <th colspan = "2" style="text-align:center;">Warehouse</th>
                        <th colspan = "2" style="text-align:center;">Production</th>
                    </tr>
                    <tr>
                        <td style="height:20px;text-align:center;">Checked</td>
                        <td style="height:20px;text-align:center;">Receive</td>
                        <td style="height:20px;text-align:center;">Checked</td>
                        <td style="height:20px;text-align:center;">Prepared</td>
                    </tr>
                    <tr>
                        <td style="height:80px;"></td>
                        <td style="height:80px;"></td>
                        <td style="height:80px;"></td>
                        <td style="height:80px;"></td>
                    </tr>
                   
                </table>
                </div>
            </div>';
            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }
        $html .= "</div></div><script>window.print()</script></body>";
        die($html);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=bpm_$format.xls");
        }
        // $filter_period = $this->input->get('filter_period');
        // $filter_workorder   = $this->input->get('filter_workorder');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_product_family = $this->input->get('filter_product_family');
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));
        $filter_kanban_date = $this->input->get('filter_kanban_date');
        $filter_status = $this->input->get('filter_status');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
        $this->db->from('bpm a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        // $this->db->join('uom d', 'b.uom_id = d.id');
        $this->db->where('a.deleted', 0);
        // $this->db->like("a.period", $filter_period);
        // $this->db->like("a.workorder", $filter_workorder);
        if ($filter_request_no != "") {
            $this->db->where('a.request_no', $filter_request_no);
        }
        if($filter_product_family != ""){
            $this->db->where('b.item_family_id', $filter_product_family);
        }
        if($filter_product_no != ""){
            $this->db->where('a.item_rm_id', $filter_product_no);
        }
        if($filter_kanban_date != ""){
            $this->db->where('a.request_date', $filter_kanban_date);
        }
        if($filter_status != ""){
            $this->db->where('a.status', $filter_status);
        }
        $this->db->order_by('a.request_no', 'DESC');
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
                                <small>BPM</small>
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
                <th>No</th>
                <th>Request No</th>
                <th>Request Id</th>
                <th>Request Date</th>
                <th>Requester</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Uom</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['request_no'] . '</td>
                        <td>' . $data['request_id'] . '</td>
                        <td>' . $data['request_date'] . '</td>
                        <td>' . $data['request_name'] . '</td>
                        <td>' . $data['item_number'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['uom'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
