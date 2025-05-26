<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_order_others extends CI_Controller
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
            $this->load->view('purchase/purchase_order_others');
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
        $this->db->from('purchase_order_others a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        // $this->db->join('uom d', 'b.uom_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.request_no', $request_no);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readPono($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT po_no, po_date, po_name FROM purchase_order_others WHERE `status` = '0' AND supplier_id = '$supplier_id' GROUP BY po_no ORDER BY `status` desc");
        echo json_encode($records);
    }

    public function po_no($supplier_number = "")
    {
        $datenow    = date("ymd");
        $sqlGetID   = $this->db->query("SELECT max(po_no) as kode FROM purchase_order_others WHERE po_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "PO-MISC-" . $datenow . "-" . $autoID;
    }

    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_supplier_id = $this->input->get('filter_supplier_id');
        $filter_po_no = $this->input->get('filter_po_no');
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
            $this->db->select('a.po_no, a.po_date, a.po_name, sum(a.qty) as qty, a.status, a.supplier_id, a.taxes, a.total_dp, c.name as supplier_name, SUM(a.qty * a.price) as total');
            $this->db->from('purchase_order_others a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.po_date >=', $filter_from);
                $this->db->where('a.po_date <=', $filter_to);
            }
            $this->db->like('a.po_no', $filter_po_no);
            $this->db->like('c.id', $filter_supplier_id);
            $this->db->group_by('a.po_no');
            $this->db->order_by('a.po_date', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['po_no'],
                    "supplier_name" => $record['supplier_name'],
                    "supplier_id" => $record['supplier_id'],
                    "po_no" => $record['po_no'],
                    "po_date" => $record['po_date'],
                    "po_name" => $record['po_name'],
                    "qty" => $record['qty'],
                    "total" => $record['total'],
                    "total_vat" => ($record['total'] * ($record['taxes'] / 100)),
                    "total_amount" => ($record['total'] - ($record['total'] * ($record['taxes'] / 100))),
                    "total_dp" => $record['total_dp'],
                    "total_grand" => (($record['total'] - ($record['total'] * ($record['taxes'] / 100))) - $record['total_dp']),
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
                c.name as supplier_name,
                c.currency,
                b.description,
                d.mpq, d.moq');
                
            $this->db->from('purchase_order_others a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->join('supplier_items d', 'a.supplier_id = d.supplier_id and a.item_rm_id = d.item_rm_id');
            // $this->db->join('uom e', 'b.uom_id = e.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.po_date >=', $filter_from);
                $this->db->where('a.po_date <=', $filter_to);
            }
            $this->db->where('a.po_no', $id);
            $this->db->like('c.id', $filter_supplier_id);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function datatable_updates(){
        $po_no = base64_decode($this->input->get('po_no'));
        $records = $this->crud->query("SELECT b.number as item_number, b.name as item_name, a.item_rm_id, d.mpq, d.moq, a.qty, c.currency, d.price, a.total as amount, a.delivery_date, a.remarks
            FROM purchase_order_others a
            JOIN item_rm b on a.item_rm_id = b.id
            JOIN suppliers c on a.supplier_id = c.id
            JOIN supplier_items d on a.supplier_id = d.supplier_id and a.item_rm_id = d.item_rm_id
            JOIN uom e on b.uom = e.name
            WHERE a.status = '0' and a.po_no = '$po_no'
            GROUP BY b.number");
        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $purchase_order_others = $this->crud->read('purchase_order_others', [], ["po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id']]);
                if (@$purchase_order_others->id != "") {
                    $send = $this->crud->update('purchase_order_others', ["po_no" => $post['po_no'], "item_rm_id" => $post['item_rm_id']], array_merge($post, array("revision" => (@$purchase_order_others->revision + 1))));
                } else {
                    $send = $this->crud->create('purchase_order_others', $post);
                }

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
        $send = $this->crud->delete('purchase_order_others', $data);
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

        for ($i = 3; $i <= $total_row; $i++) {
            $item_number = $data->val($i, 3);
            $items = $this->crud->read('item_rm', [], ["number" => $item_number]);

            $datas[] = array(
                'po_no' => $data->val($i, 2),
                'po_date' => date("Y-m-d", $data->val($i, 3)),
                'po_name' => $this->session->name,
                'supplier_number' => $data->val($i, 4),
                'product_number' => $data->val($i, 5),
                'qty' => $data->val($i, 6),
                'delivery_date' => $data->val($i, 7),
                'remarks' => $data->val($i, 8),
            );
        }
        
        $datas['total'] = count($datas);
        echo json_encode($datas);

        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/purchase_order_others.txt');
    }
    
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/purchase_order_others.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/purchase_order_others.txt";
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
            $config = $this->crud->read('config');
            $item = $this->crud->read('item_rm', [], ["number" => $data['product_number']]);
            $supplier = $this->crud->read('suppliers', [], ["number" => $data['supplier_number']]);
            $supplier_item = $this->crud->read('supplier_items', [], ["supplier_id" => @$supplier->id, "item_rm_id" => @$item->id]);

            $purchase_order_others = $this->crud->read('purchase_order_others', [], ["po_no" => $data['po_no'], "item_rm_id" => @$item->id, "supplier_id" => @$supplier->id]);
            if (empty($item->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Product No " . $data['product_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($supplier->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Supplier No " . $data['supplier_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($supplier_item->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Supplier Items " . $data['supplier_number'] . " and " . $data['product_number'] . " Not Found", "theme" => "error"));
            } elseif (!empty($purchase_order_others->id)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $data['product_number'] . " and " . $data['po_no'] . " Duplicate Data", "theme" => "error"));
            } else {
                if($supplier->vat_status == "NON VAT"){
                    $taxes = 0;
                }else{
                    $taxes = $config->tax;
                }

                $finalPost = array(
                    "supplier_id" => $supplier->id,
                    "item_rm_id" => $item->id,
                    "po_no" => $data['po_no'],
                    "po_date" => $data['po_date'],
                    "po_name" => $data['po_name'],
                    "delivery_date" => $data['delivery_date'],
                    "qty" => $data['qty'],
                    "price" => $supplier_item->price,
                    "total" => ($data['qty'] * $supplier_item->price),
                    "taxes" => ((($data['qty'] * $supplier_item->price) * $taxes) / 100),
                    "remarks" => $data['remarks'],
                );

                $send = $this->crud->create('purchase_order_others', $finalPost);
                echo $send;
            }
        }
    }

    public function print_po($po_no)
    {
        $purchase_order_others_total = $this->crud->reads('purchase_order_others', [], ["po_no" => base64_decode($po_no)]);
        $purchase_order_others = $this->crud->read('purchase_order_others', [], ["po_no" => base64_decode($po_no)], "", "revision", "desc");
        $supplier = $this->crud->read('suppliers', [], ["id" => $purchase_order_others->supplier_id]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        $signatures = $this->db->get('signatures')->row();

        //Config Page
        $rows = 8;
        $page = ceil(count($purchase_order_others_total) / $rows);
        //Generate QRcode
        $this->createQrcode($purchase_order_others->po_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $purchase_order_others->po_no . '</title>
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
                            </center>
                        </div>
                        <div class="print">';
        //Loop Page
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_rm_id, c.currency, b.description as item_name, a.price, b.uom');
            $this->db->from('purchase_order_others a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->join('supplier_items d', 'a.supplier_id = d.supplier_id and a.item_rm_id = d.item_rm_id');
            // $this->db->join('uom e', 'b.uom_id = e.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.po_no', base64_decode($po_no));
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(8, ($i * 8));
            $records = $this->db->get()->result_array();

            if ($purchase_order_others->updated_date != null) {
                $revision_date = $purchase_order_others->updated_date;
            } else {
                $revision_date = $purchase_order_others->created_date;
            }
            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10">
                                    <img src="' . $config->favicon . '" width="60" />
                                </th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $purchase_order_others->po_no . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_purchase_order . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_purchase_order . '</td>
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
                                    <br>
                                    <h3 style="margin:0;"><u>PURCHASE ORDER</u></h3>
                                    <small>NO : ' . @$purchase_order_others->po_no . '</small>
                                </center>
                                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                    <tr>
                                        <td width="80">Supplier</td>
                                        <td width="10">:</td>
                                        <td width="30%"><b>' . @$supplier->name . '</b></td>
                                        <td style="text-align:right; padding-right: 20px;" rowspan="7">
                                            Page <b>' . $hal  . '</b> of <b> ' . $page . '</b><br><br>
                                            PO Date:<br><b>' . date("d F Y", strtotime($purchase_order_others->po_date)) . '</b><br>
                                            Revision:<br><b>' . $purchase_order_others->revision . '</b><br>
                                            Revision Date:<br><b>' . date("d F Y", strtotime($revision_date)) . '</b><br>
                                            Payment Terms:<br><b>' . $supplier->payment_term . ' Days</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50">Address</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier->address . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Reff No</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$purchase_order_others->request_no . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Attention</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier->attention . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Phone</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier->telp . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Fax</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier->fax . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Email</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$supplier->email . '</b></td>
                                    </tr>
                                </table>
                                <table id="customers">
                                    <tr>
                                        <th width="30" style="text-align:center;">No</th>
                                        <th style="text-align:center;">Product No</th>
                                        <th style="text-align:center;">Specification</th>
                                        <th width="50" style="text-align:center;">Qty</th>
                                        <th width="50" style="text-align:center;">Uom</th>
                                        <th width="80" style="text-align:center;">Delivery<br>Date</th>
                                        <th width="50" style="text-align:center;">Unit<br>Price</th>
                                        <th width="50" style="text-align:center;">Currency</th>
                                        <th width="50" style="text-align:center;">Amount</th>
                                    </tr>';
            $row = 0;
            foreach ($records as $record) {
                $subtotal += ($record['qty'] * $record['price']);
                if ($record['currency'] != "IDR") {
                    $digits = 2;
                } else {
                    $digits = 2;
                }
                $html .= '  <tr>
                                <td  style="text-align:center;">' . $no . '</td>
                                <td>' . $record['item_rm_id'] . '</td>
                                <td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>
                                <td style="text-align:right;">' . number_format($record['qty'], 2) . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                <td style="text-align:center;">' . $record['delivery_date'] . '</td>
                                <td style="text-align:right;">' . number_format($record['price'], 2) . '</td>
                                <td style="text-align:center;">' . $record['currency'] . '</td>
                                <td style="text-align:right;">' . number_format(($record['qty'] * $record['price']), 2) . '</td>
                            </tr>';
                $row++;
                $no++;
            }
            if (($i + 1) == $page) {
                $this->db->select('a.remarks, b.number as item_rm_id, b.description as item_name');
                $this->db->from('purchase_order_others a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.po_no', base64_decode($po_no));
                $this->db->order_by('b.number', 'asc');
                $this->db->limit(8, ($i * 8));
                $remarks = $this->db->get()->result_array();

                $html .= '  <tr>
                                <td style="vertical-align: top; text-align:left;" colspan="6" rowspan="5">
                                    <b>Note :</b> <br>';
                foreach ($remarks as $remark) {
                    if ($remark['remarks'] != "") {
                        $html .= $remark['item_rm_id'] . " &nbsp; (" . $remark['remarks'] . ") <br>";
                    }
                }

                if($supplier->vat_status == "VAT"){
                    $tax = $config->tax;
                }else{
                    $tax = 0;
                }

                $html .= '  </td>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Sub Total</th>
                                <th style="text-align:right;">' . number_format($subtotal, 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">VAT (' . $tax . '%)</th>
                                <th style="text-align:right;">' . number_format(((@$subtotal * $tax) / 100), 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Down Payment</th>
                                <th style="text-align:right;">' . number_format(@$purchase_order_others->total_dp, 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Total Amount</th>
                                <th style="text-align:right;">' . number_format($subtotal - ((@$subtotal * $tax) / 100) - @$purchase_order_others->total_dp, 2) . '</th>
                            </tr>
                        </table>';
            } else {
                $html .= '</table>';
            }
            $html .= '  <div style="width:100%; display: grid; grid-template-columns: auto auto auto;">
                        <div style="width:50%;">
                            <table style="margin-top:20px; font-size:12px;">
                                <tr>
                                    <th width="200" style="text-align:center;">Supplier Approval</th>
                                </tr>
                                <tr>
                                    <th style="height:80px;"></th>
                                </tr>
                                <tr>
                                    <th style="height:20px; text-align:center;">(...................................)</th>
                                </tr>
                            </table>
                        </div>
                        <div style="width:50%; position: absolute; right: 50px;">
                            <table id="customers" style="margin-top:20px;">
                                <tr>
                                    <th width="200" style="text-align:center;">Prepared By</th>
                                    <th width="200" style="text-align:center;">Checked By</th>
                                    <th width="200" style="text-align:center;">Approved By</th>
                                </tr>
                                <tr>
                                    <th style="height:80px;"></th>
                                    <th style="height:80px;"></th>
                                    <th style="height:80px;"></th>
                                </tr>
                                <tr>
                                    <th style="height:20px; text-align:center;">' . $signatures->po_prepared . '</th>
                                    <th style="height:20px; text-align:center;">' . $signatures->po_checked . '</th>
                                    <th style="height:20px; text-align:center;">' . $signatures->po_approved . '</th>
                                </tr>
                            </table>
                        </div>
                    </div>
    
                    <table style="width:100%; font-size:12px; margin-top:20px;">
                        <tr>
                            <td width="20">1.</td>
                            <td>Please mention the Purchase Order Number in the shipping & billing document</td>
                        </tr>
                        <tr>
                            <td>2. </td>
                            <td>Make sure the delivery of goods must be meet to specifications otherwise penalty will be issued</td>
                        </tr>
                        <tr>
                            <td>3. </td>
                            <td>Please pay attention to letter of vendor regulations</td>
                        </tr>
                        <tr>
                            <td>4. </td>
                            <td>Late delivery must be inform one week before due date</td>
                        </tr>
                    </table>
                </div>
            </div>';
            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }
            $hal++;
        }
        $html .= '<script>window.print()</script>';
        die($html);
    }
    
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_misc_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_po_no = $this->input->get('filter_po_no');
        $filter_suppliers = $this->input->get('filter_suppliers');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, 
            b.number as item_rm_id, 
            b.name as item_name,
            c.name as item_family_name, 
            d.name as supplier_name, 
            d.currency, 
            e.mpq, 
            e.moq,
            b.uom');
        $this->db->from('purchase_order_others a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id');
        // $this->db->join('uom f', 'b.uom_id = f.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.po_date >=', $filter_from);
            $this->db->where('a.po_date <=', $filter_to);
        }
        $this->db->like('a.po_no', $filter_po_no);
        $this->db->like('d.number', $filter_suppliers);
        $this->db->order_by('a.po_date', 'DESC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>PURCHASE ORDER MISC</small>
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
                    <th>PO No</th>
                    <th>PO Period</th>
                    <th>PO Name</th>
                    <th>Supplier</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>MPQ</th>
                    <th>MOQ</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total Price</th>
                    <th>Currency</th>
                    <th>Uom</th>
                    <th>Delivery</th>
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
            if ($data['currency'] != "IDR") {
                $digits = 4;
            } else {
                $digits = 2;
            }
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['po_date'] . '</td>
                        <td>' . $data['po_name'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['mpq'], 2) . '</td>
                        <td>' . number_format($data['moq'], 2) . '</td>
                        <td>' . number_format($data['qty'], 2) . '</td>
                        <td>' . number_format($data['price'], 4, ",", ".") . '</td>
                        <td>' . number_format(($data['qty'] * $data['price']), 2, ",", ".") . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['delivery_date'] . '</td>
                        <td>' . $status . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
