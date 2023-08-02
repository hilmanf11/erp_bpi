<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_order_receipts extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_order_receipts');
        } else {
            redirect('error_access');
        }
    }
    public function reads()
    {
        $request_no = $this->input->get('request_no');
        $supplier_id = $this->input->get('supplier_id');
        //Select Query
        $this->db->select('a.*, b.number, b.name, b.uom, c.name as item_family_name, e.name as supplier_name, d.mpq, d.moq, d.price');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('supplier_items d', 'a.item_id = d.item_id');
        $this->db->join('suppliers e', 'd.supplier_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->like('a.request_no', $request_no);
        $this->db->like('d.supplier_id', $supplier_id);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }
    public function readPoNo($supplier_id)
    {
        $records = $this->crud->query("SELECT po_no FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' and status = '0' GROUP BY po_no ORDER BY created_date desc");
        echo json_encode($records);
    }
    public function readReceipt($supplier_id)
    {
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' and status = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }
    public function readDocno($supplier_id)
    {
        $records = $this->crud->query("SELECT bc_document FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' GROUP BY bc_document ORDER BY created_date desc");
        echo json_encode($records);
    }
    public function readReceiptNo()
    {
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE status = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }
    public function readSupplier()
    {
        $records = $this->crud->query("SELECT b.id, b.number, b.name FROM purchase_order_receipts a JOIN suppliers b ON a.supplier_id = b.id WHERE a.status = '0' GROUP BY a.supplier_id ORDER BY a.created_date desc");
        echo json_encode($records);
    }
    public function receipt_no($date = "")
    {
        if ($date == "") {
            $datenow = date("Ymd");
        } else {
            $datenow = date("Ymd", strtotime(base64_decode($date)));
        }
        $sqlGetID   = $this->db->query("SELECT max(receipt_no) as kode FROM purchase_order_receipts WHERE receipt_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "POR-" . $datenow . "-" . $autoID;
    }
    public function receipt_id($receipt_no)
    {
        $sqlGetID   = $this->db->query("SELECT max(receipt_id) as kode FROM purchase_order_receipts WHERE receipt_id like '%$receipt_no%'");
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
    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to   = $this->input->get('filter_to');
            $filter_supplier = $this->input->get('filter_supplier');
            $filter_po_no = $this->input->get('filter_po_no');
            $filter_receipt = $this->input->get('filter_receipt');
            $filter_doc_no = $this->input->get('filter_doc_no');
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            $id = $_POST['id'];
            if ($id === "0") {
                $this->db->select('a.po_no, a.receipt_no, a.receipt_date, a.awb_no, a.awb_date, a.bc_kind, a.bc_document, a.bc_aju, a.bc_date, b.number as supplier_id, b.name as supplier_name, a.total_receipt as qty_receipt, a.total_label as qty_label, a.status');
                $this->db->from('(SELECT *, sum(qty_label) as total_label, sum(qty_receipt) as total_receipt FROM purchase_order_receipts GROUP BY receipt_no ORDER BY status asc) a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('purchase_orders c', 'a.po_no = c.po_no and a.item_id = c.item_id');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" and $filter_to != "") {
                    $this->db->where('a.receipt_date >=', $filter_from);
                    $this->db->where('a.receipt_date <=', $filter_to);
                }
                if ($filter_supplier != "") {
                    $this->db->where('a.supplier_id', $filter_supplier);
                }
                if ($filter_po_no != "") {
                    $this->db->where('a.po_no', $filter_po_no);
                }
                if ($filter_receipt != "") {
                    $this->db->where('a.receipt_no', $filter_receipt);
                }
                if ($filter_doc_no != "") {
                    $this->db->where('a.bc_document', $filter_doc_no);
                }
                $this->db->group_by('a.receipt_no');
                $this->db->order_by('a.status', 'ASC');
                $this->db->order_by('a.receipt_date', 'DESC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                //Get Data Array
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {
                    $receipt_no = $record['receipt_no'];
                    $purchase_order_label = $this->crud->query("SELECT receipt_id, SUM(`status`) as total_scan FROM purchase_order_labels WHERE receipt_id like '%$receipt_no%'");

                    $arr[] = array(
                        "id" => $record['receipt_no'],
                        "po_no" => $record['po_no'],
                        "bc_kind" => $record['bc_kind'],
                        "bc_document" => $record['bc_document'],
                        "bc_aju" => $record['bc_aju'],
                        "bc_date" => $record['bc_date'],
                        "receipt_no" => $record['receipt_no'],
                        "receipt_date" => $record['receipt_date'],
                        "awb_no" => $record['awb_no'],
                        "awb_date" => $record['awb_date'],
                        "supplier_id" => $record['supplier_id'],
                        "supplier_name" => $record['supplier_name'],
                        "qty_label" => $record['qty_label'],
                        "total_scan" => $purchase_order_label[0]->total_scan,
                        "status" => $record['status'],
                        "state" => "closed",
                    );
                }
                //Mapping Data
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                $this->db->select('a.*, 
                    a.id as purchase_order_receipts_id, 
                    a.receipt_id as id, 
                    b.number as supplier_id, 
                    b.name as supplier_name, 
                    c.number as item_number, 
                    c.name as item_name, 
                    d.name as item_family_name, 
                    b.currency, 
                    f.name as uom,
                    e.mpq,
                    sum(g.status) as total_scan');
                $this->db->from('purchase_order_receipts a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('items c', 'a.item_id = c.id');
                $this->db->join('item_familys d', 'c.item_family_id = d.id');
                $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_id');
                $this->db->join('uom f', 'c.uom_id = f.id');
                $this->db->join('purchase_order_labels g', 'g.receipt_id = a.receipt_id', 'left');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" and $filter_to != "") {
                    $this->db->where('a.receipt_date >=', $filter_from);
                    $this->db->where('a.receipt_date <=', $filter_to);
                }
                $this->db->where('a.receipt_no', $id);
                $this->db->group_by('a.receipt_id');
                $this->db->order_by('a.receipt_id', 'ASC');
                $records = $this->db->get()->result_array();
                echo json_encode($records);
            }
        }
    }
    public function datatablesTemp()
    {
        $po_no = $this->input->get('po_no');
        //Select Query
        $this->db->select('a.po_no, 
            b.id as item_id, 
            b.number as item_number, 
            b.name as item_name, 
            a.qty as qty_po, 
            c.mpq, 
            a.supplier_id, 
            e.name as uom,
            (a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) as qty_os,
            (a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) as qty_receipt,
            CEIL((a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) / c.mpq) as qty_label');
        $this->db->from('purchase_orders a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('supplier_items c', 'a.item_id = c.item_id and a.supplier_id = c.supplier_id');
        $this->db->join('(SELECT sum(qty_receipt) as qty_os, item_id, supplier_id, po_no FROM purchase_order_receipts GROUP BY item_id, supplier_id, po_no) d', 'a.item_id = d.item_id and a.supplier_id = d.supplier_id and a.po_no = d.po_no', 'left');
        $this->db->join('uom e', 'b.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.po_no', $po_no);
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $send   = $this->crud->create('purchase_order_receipts', array_merge($post, ["receipt_id" => $this->receipt_id($post['receipt_no'])]));
                if ($post['qty_os'] > $post['qty_receipt']) {
                    $status = 0;
                } else {
                    $status = 1;
                }
                $this->db->where('po_no', $post['po_no']);
                $this->db->where('item_id', $post['item_id']);
                $this->db->update("purchase_orders", ["status" => $status]);
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }
    public function delete()
    {
        $data = $this->input->post();
        $deletePurchaseOrderReceipts = $this->crud->delete('purchase_order_receipts', ["id" => $data['id']]);
        $deleteScanItemReceipts = $this->crud->delete('scan_item_receipts', ["receipt_id" => $data['receipt_id']]);
        $updatePurchaseOrders = $this->crud->update('purchase_orders', ["po_no" => $data['po_no'], "item_id" => $data['item_id']], ["status" => 0]);
        echo $deletePurchaseOrderReceipts;
    }

    public function print_label($receipt_id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $receipt_id = base64_decode($receipt_id);
        $po_receipt = $this->crud->read('purchase_order_receipts', [], ["receipt_id" => $receipt_id]);
        $qty_receipt = $po_receipt->qty_receipt;
        //Cek Label
        $po_receipt_label = $this->crud->reads('purchase_order_labels', [], ["receipt_id" => $receipt_id]);
        if (!$po_receipt_label) {
            for ($i = 0; $i < $po_receipt->qty_label; $i++) {
                //Read Label ID
                $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM purchase_order_labels WHERE receipt_id = '$receipt_id'");
                $rowID = $sqlGetID->row();
                $label = $rowID->kode;
                if ($label == NULL) {
                    $autoID = $receipt_id . sprintf("%04s", $label + 1);
                } else {
                    $urutan = (int) substr($label, -4);
                    $autoID = $receipt_id . sprintf("%04s", $urutan + 1);
                }
                if ($qty_receipt > $po_receipt->qty_mpq) {
                    $qty = $po_receipt->qty_mpq;
                } else {
                    $qty = $qty_receipt;
                }
                //Simpan Label
                $arrLabel = [
                    "receipt_id" => $po_receipt->receipt_id,
                    "label_no" => $autoID,
                    "qty" => $qty
                ];
                $send = $this->crud->create('purchase_order_labels', $arrLabel);
                $qty_receipt = ($qty_receipt - $po_receipt->qty_mpq);
            }
        }
        $this->db->select('a.*, b.receipt_date, c.number, c.name, c.description, d.location, d.area');
        $this->db->from('purchase_order_labels a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
        $this->db->join('items c', 'b.item_id = c.id');
        $this->db->join('warehouse_location_items d', 'd.item_id = c.id', 'left');
        $this->db->where('a.deleted', 0);
        //$this->db->where('a.status', 0);
        $this->db->where('a.receipt_id', $receipt_id);
        $this->db->order_by('a.label_no', 'asc');
        $records = $this->db->get()->result_object();
        $html = '<html>
                    <head>
                        <title>' . $receipt_id . '</title>
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
                                            <small>Quantity</small><br><b style="font-size:24px;">' . number_format($record->qty, 2) . '</b>
                                        </th>
                                        <th style="text-align:left">
                                            <small>Location</small><br><b style="font-size:24px;">' . $record->location . '</b>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td style="width:65%;">
                                            <small>Date</small><br>
                                            <b>' . $record->receipt_date . '</b><br>
                                            <small>Label No</small><br>
                                            <b style="font-size:8px;">' . $record->label_no . '</b>
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

    public function print_receiving($receipt_no)
    {
        $purchase_order_receipt_total = $this->crud->reads('purchase_order_receipts', [], ["receipt_no" => base64_decode($receipt_no)]);
        $po_receipt = $this->crud->read('purchase_order_receipts', [], ["receipt_no" => base64_decode($receipt_no)]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 8;
        $page = ceil(count($purchase_order_receipt_total) / $rows);
        //Generate QRcode
        $this->createQrcode($po_receipt->receipt_no, "assets/image/qrcode/");
        $this->db->select('a.*, b.number as supplier_id, b.name as supplier_name, c.number as item_id, c.name as item_name, c.lot, f.name as uom, d.name as item_familys_name, e.mpq, b.currency, g.location');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_id');
        $this->db->join('uom f', 'f.id = c.uom_id');
        $this->db->join('warehouse_location_items g', 'a.item_id = g.item_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.receipt_no', base64_decode($receipt_no));
        $this->db->group_by('a.po_no');
        $this->db->group_by('a.supplier_id');
        $this->db->group_by('a.item_id');
        $records = $this->db->get()->result_array();
        if ($records) {
            $html = '<html>
                        <head>
                            <title>' . $po_receipt->receipt_no . '</title>
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
                                text-align: center;color: black;
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
                                    <p>Margin Default, Scale 98</p>
                                </center>
                            </div>
                            <div class="print">';
            $no = 1;
            $hal = 1;
            $subtotal = 0;
            for ($i = 0; $i < $page; $i++) {
                $this->db->select('a.*, b.number as supplier_id, b.name as supplier_name, c.number as item_id, c.name as item_name, c.lot, f.name as uom, d.name as item_familys_name, e.mpq, b.currency, g.location');
                $this->db->from('purchase_order_receipts a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('items c', 'a.item_id = c.id');
                $this->db->join('item_familys d', 'c.item_family_id = d.id');
                $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_id');
                $this->db->join('uom f', 'f.id = c.uom_id');
                $this->db->join('warehouse_location_items g', 'a.item_id = g.item_id', 'left');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.status', 0);
                $this->db->where('a.receipt_no', base64_decode($receipt_no));
                $this->db->group_by('a.po_no');
                $this->db->group_by('a.supplier_id');
                $this->db->group_by('a.item_id');
                $this->db->limit(8, ($i * 8));
                $records = $this->db->get()->result_array();
                $html .= '  <table style="width:100%;">
                                    <tr>
                                        <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                        <td width="250" style="padding:10px;">
                                            <b style="font-size:14px;">' . $config->name . '</b><br>
                                            <span style="font-size:10px;">' . $config->address . '</span><br>
                                        </td>
                                        <th width="100" style="text-align:right;">
                                            <table style="width:100%; font-size:10px;">
                                                <tr>
                                                    <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $po_receipt->receipt_no . '.png') . '" width="60"/></td>
                                                    <td width="60">Doc No</td>
                                                    <td width="5">:</td>
                                                    <td width="100">' . $config_iso->doc_receiving_note . '</td>
                                                </tr>
                                                <tr>
                                                    <td>Form</td>
                                                    <td>:</td>
                                                    <td>' . $config_iso->form_receiving_note . '</td>
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
                                            <h3><u>GOOD RECEIVING NOTE</u></h3>
                                        </center>
                                        <table style="width:50%; font-size:12px; margin-bottom:10px; float:left;">
                                            <tr>
                                                <td width="100">Receipt No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$po_receipt->receipt_no . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Receipt Date</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$po_receipt->receipt_date . '</b></td>
                                            </tr>
                                        </table>
                                        <table style="width:50%; font-size:12px; margin-bottom:10px; float:left;">
                                            <tr>
                                                <td width="50">Supplier</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$records[0]['supplier_name'] . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Doc. No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$records[0]['bc_document'] . '</b></td>
                                            </tr>
                                        </table>
    
                                        <table id="customers">
                                            <tr>
                                                <th>No</th>
                                                <th>PO No</th>
                                                <th>Product No</th>
                                                <th>Product Name</th>
                                                <th>Category</th>
                                                <th>Location</th>
                                                <th>MPQ</th>
                                                <th>Quantity</th>
                                                <th>Uom</th>
                                            </tr>';
                $no = 1;
                foreach ($records as $record) {
                    $html .= '  <tr>
                    <td style="text-align:center">' . $no . '</td>
                    <td>' . $record['po_no'] . '</td>
                    <td>' . $record['item_id'] . '</td>
                    <td>' . $record['item_name'] . '</td>
                    <td>' . $record['item_familys_name'] . '</td>
                    <td>' . $record['location'] . '</td>
                    <td style="text-align:right">' . number_format($record['mpq'], 2) . '</td>
                    <td style="text-align:right">' . number_format($record['qty_receipt'], 2) . '</td>
                    <td>' . $record['uom'] . '</td>
                </tr>';
                    $no++;
                }
                $html .= '  </table>
                            <table id="customers" style="margin-top:20px;">
                                <tr>
                                    <th rowspan="3" style="vertical-align:top; padding:20px;">Note : </th>
                                    <th width="200" style="text-align:center;">Approval QC</th>
                                    <th width="200" style="text-align:center;">Receipt By</th>
                                </tr>
                                <tr>
                                    <th style="height:80px;"></th>
                                    <th style="height:80px;"></th>
                                </tr>
                                <tr>
                                    <th style="height:20px; text-align:center;"></th>
                                    <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                                </tr>
                            </table>
                        </div>
                    </div>
                <script>window.print()</script>';
                if (($i + 1) != $page) {
                    $html .= '<div style="page-break-after:always;"></div>';
                }
                $hal++;
            }
            $html .= "</div></div><script>window.print()</script>";
            die($html);
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_receipts_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_supplier = $this->input->get('filter_supplier');
        $filter_po_no = $this->input->get('filter_po_no');
        $filter_receipt = $this->input->get('filter_receipt');
        $filter_doc_no = $this->input->get('filter_doc_no');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as supplier_id, b.name as supplier_name, c.number as item_id, c.name as item_name, d.name as item_family_name, b.currency, f.name as uom');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_id');
        $this->db->join('uom f', 'c.uom_id = f.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        if ($filter_from != "" and $filter_to != "") {
            $this->db->where('a.receipt_date <=', $filter_from);
            $this->db->where('a.receipt_date >=', $filter_to);
        }
        if ($filter_supplier != "") {
            $this->db->where('a.supplier_id', $filter_supplier);
        }
        if ($filter_receipt != "") {
            $this->db->where('a.receipt_no', $filter_receipt);
        }
        if ($filter_doc_no != "") {
            $this->db->where('a.bc_document', $filter_doc_no);
        }
        $this->db->like('a.po_no', $filter_po_no);
        $this->db->order_by('a.receipt_date', 'DESC');
        $records = $this->db->get()->result_array();
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
                                <small>PURCHASE ORDER RECEIPT</small>
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
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">PO No</th>
                <th colspan="4" style="text-align:center;">Beacukai</th>
                <th rowspan="2">Receipt No</th>
                <th rowspan="2">AWB No</th>
                <th colspan="2" style="text-align:center;">Supplier</th>
                <th rowspan="2">Product No</th>
                <th rowspan="2">Product Name</th>
                <th rowspan="2">Qty</th>
                <th rowspan="2">UoM</th>
                <th rowspan="2">Currency</th>
                <th rowspan="2">Label</th>
            </tr>
            <tr>
                <th>Kind</th>
                <th>Document</th>
                <th>AJU</th>
                <th>Date</th>
                <th>ID</th>
                <th>Name</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['bc_kind'] . '</td>
                        <td>' . $data['bc_document'] . '</td>
                        <td>' . $data['bc_aju'] . '</td>
                        <td>' . $data['bc_date'] . '</td>
                        <td>' . $data['receipt_no'] . '</td>
                        <td>' . $data['awb_no'] . '</td>
                        <td>' . $data['supplier_id'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['item_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['qty_receipt'], 2) . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . number_format($data['qty_label']) . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
