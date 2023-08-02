<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_orders extends CI_Controller
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
        $this->form_validation->set_rules('item_number', 'Product No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $data['approval'] = $this->crud->read('signatures');

            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_orders');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('purchase_orders', ["name" => $post]);
        echo json_encode($send);
    }

    public function readPono()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $supplier_id = $this->input->get('supplier_id');
        $this->db->select('a.po_no, a.po_date, a.po_name, b.number as supplier_number, b.name as supplier_name');
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->like('a.supplier_id', $supplier_id);
        $this->db->like('a.po_no', $post);
        $this->db->group_by('a.po_no');
        $this->db->order_by('a.created_date', 'desc');
        $records = $this->db->get()->result_object();
        echo json_encode($records);
    }

    public function readTotalPo()
    {
        $item_id = $this->input->post('item_id');
        $this->db->select('item_id, SUM(qty) as qty');
        $this->db->from('purchase_orders');
        $this->db->where('deleted', 0);
        $this->db->where('status', 0);
        $this->db->where('item_id', $item_id);
        $this->db->group_by('item_id');
        $records = $this->db->get()->row();

        echo json_encode($records);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to   = $this->input->get('filter_to');
            $filter_po_no = $this->input->get('filter_po_no');
            $filter_suppliers = $this->input->get('filter_suppliers');
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
                //Select Query
                $this->db->select('a.po_no, a.request_no,
                    a.po_date, 
                    d.currency, 
                    f.name as uom,
                    d.name as supplier_name, 
                    SUM(a.qty) as qty, 
                    SUM(a.price) as price, 
                    SUM(a.total) as total_price,
                    a.status');
                $this->db->from('purchase_orders a');
                $this->db->join('items b', 'a.item_id = b.id');
                $this->db->join('item_familys c', 'b.item_family_id = c.id');
                $this->db->join('suppliers d', 'a.supplier_id = d.id');
                $this->db->join('supplier_items e', 'a.item_id = e.item_id and a.supplier_id = e.supplier_id');
                $this->db->join('uom f', 'b.uom_id = f.id');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" or $filter_to != "") {
                    $this->db->where('a.po_date >=', $filter_from);
                    $this->db->where('a.po_date <=', $filter_to);
                }
                $this->db->like('a.po_no', $filter_po_no);
                $this->db->like('d.id', $filter_suppliers);
                $this->db->group_by('a.po_no');
                $this->db->order_by('a.status', 'ASC');
                $this->db->order_by('a.po_date', 'DESC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                //Get Data Array
                $records = $this->db->get()->result_array();
                //Mapping Data
                foreach ($records as $record) {
                    $arr[] = array(
                        "id" => $record['po_no'],
                        "po_no" => $record['po_no'],
                        "request_no" => $record['request_no'],
                        "po_date" => $record['po_date'],
                        "uom" => $record['uom'],
                        "currency" => $record['currency'],
                        "supplier_name" => $record['supplier_name'],
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
                    c.name as item_family_name, 
                    d.name as supplier_name, 
                    d.currency, e.mpq, 
                    e.moq,
                    a.price, 
                    f.name as uom,
                    a.status, 
                    (a.qty * a.price) as total_price');
                $this->db->from('purchase_orders a');
                $this->db->join('items b', 'a.item_id = b.id');
                $this->db->join('item_familys c', 'b.item_family_id = c.id');
                $this->db->join('suppliers d', 'a.supplier_id = d.id');
                $this->db->join('supplier_items e', 'a.item_id = e.item_id and a.supplier_id = e.supplier_id');
                $this->db->join('uom f', 'b.uom_id = f.id');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" or $filter_to != "") {
                    $this->db->where('a.po_date >=', $filter_from);
                    $this->db->where('a.po_date <=', $filter_to);
                }
                $this->db->like('a.po_no', $id);
                $this->db->like('d.id', $filter_suppliers);
                $this->db->order_by('a.status', 'ASC');
                $this->db->order_by('a.po_no', 'DESC');
                $records = $this->db->get()->result_array();
                echo json_encode($records);
            }
        }
    }

    public function datatable_updates()
    {
        $po_no = base64_decode($this->input->get('po_no'));
        $this->db->select('a.*, 
            b.number as item_number, 
            b.name as item_name,
            d.id as supplier_id, 
            d.number as supplier_number, 
            d.name as supplier_name, 
            e.mpq, 
            e.moq,
            d.vat_status,
            (a.qty * a.price) as amount,
            d.currency');
        $this->db->from('purchase_orders a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.item_id = e.item_id and a.supplier_id = e.supplier_id');
        $this->db->join('uom f', 'b.uom_id = f.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.po_no', $po_no);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();

        $total_sub = 0;
        $obj = array();
        foreach ($records as $record) {
            $total_sub += $record['amount'];
            array_push($obj, $record);
        }

        $arr['rows'] = $obj;
        $arr['total_sub'] = round($total_sub, 2);
        die(json_encode($arr));

        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $items = $this->crud->read('items', [], ['number' => $post['item_number']]);
                $item_family = $this->crud->read('item_familys', [], ['id' => $items->item_family_id]);
                $suppliers = $this->crud->read('suppliers', [], ["id" => $post['supplier_id']]);
                $supplier_items = $this->crud->read('supplier_items', [], ["item_id" => $items->id, "supplier_id" => $post['supplier_id']]);
                $purchaseOrder = $this->crud->read('purchase_orders', [], ["request_no" => $post['request_no'], "supplier_id" => $post['supplier_id']]);
                $config = $this->crud->read("config");

                $datenow    = $item_family->number . date("ymd");
                $sqlGetID   = $this->db->query("SELECT max(po_no) as kode FROM purchase_orders WHERE po_no like '%$datenow%'");
                $rowID      = $sqlGetID->row();
                $kode       = $rowID->kode;
                if ($kode == NULL) {
                    $autoID = sprintf("%04s", $kode + 1);
                    $po_no = "PO-" . $datenow . "-" . $autoID;
                } else {
                    if ($purchaseOrder) {
                        $po_no = $purchaseOrder->po_no;
                    } else {
                        $urutan = (int)substr($kode, -4);
                        $urutan++;
                        $autoID = sprintf("%04s", $urutan);
                        $po_no = "PO-" . $datenow . "-" . $autoID;
                    }
                }

                if($suppliers->vat_status == "VAT"){
                    $taxes = $config->tax;
                }else{
                    $taxes = 0;
                }

                $data = array(
                    "supplier_id" => $post['supplier_id'],
                    "item_id" => $items->id,
                    "request_no" => $post['request_no'],
                    "request_date" => $post['request_date'],
                    "request_name" => $post['request_name'],
                    "po_date" => $post['po_date'],
                    "po_no" => $po_no,
                    "po_name" => $this->session->name,
                    "delivery_date" => $post['delivery_date'],
                    "qty" => $post['qty'],
                    "price" => $supplier_items->price,
                    "total" => ($supplier_items->price * $post['qty']),
                    "taxes" => $taxes,
                    "total_vat" => (($supplier_items->price * $post['qty']) * ($taxes / 100)),
                    "remarks" => $post['remarks'],
                );
                $send = $this->crud->create('purchase_orders', $data);
                $this->db->where('request_no', $post['request_no']);
                $this->db->where('item_id', $items->id);
                $this->db->update("purchase_requests", ["status" => 1]);
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
            $id   = $this->input->post('id');
            $post = $this->input->post();

            $items = $this->crud->read('items', [], ['number' => $post['item_number']]);
            $supplier_items = $this->crud->read('supplier_items', [], ["item_id" => $items->id, "supplier_id" => $post['supplier_id']]);
            $purchaseOrder = $this->crud->read('purchase_orders', [], ["request_no" => $post['request_no'], "supplier_id" => $post['supplier_id'], "item_id" => $items->id]);
            if (@$post['price'] != "") {
                $price = $post['price'];
            } else {
                $price = @$supplier_items->price;
            }

            $purchase_orders = $this->crud->update('purchase_orders', ["request_no" => $post['request_no'], "supplier_id" => $post['supplier_id'], "item_id" => $items->id], [
                "qty" => $post['qty'],
                "price" => $price,
                "total" => ($price * $post['qty']),
                "delivery_date" => $post['delivery_date'],
                "remarks" => $post['remarks'],
                "total_dp" => $post['total_dp'],
                "revision" => (@$purchaseOrder->revision + 1)
            ]);

            $purchase_requests = $this->crud->update('purchase_requests', ["request_no" => $post['request_no'], "item_id" => $items->id], [
                "qty" => $post['qty']
            ]);

            echo $purchase_orders;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update_approval()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->crud->update('signatures', [], $post);

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_orders', $data);
        $update = $this->crud->update('purchase_requests', ["request_no" => $data['request_no'], "item_id" => $data['item_id']], ["status" => 0]);
        echo $send;
    }
    public function print_po($po_no)
    {
        $purchase_orders_total = $this->crud->reads('purchase_orders', [], ["po_no" => base64_decode($po_no)]);
        $purchase_orders = $this->crud->read('purchase_orders', [], ["po_no" => base64_decode($po_no)], "", "revision", "desc");
        $supplier = $this->crud->read('suppliers', [], ["id" => $purchase_orders->supplier_id]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        $signatures = $this->db->get('signatures')->row();

        //Config Page
        $rows = 8;
        $page = ceil(count($purchase_orders_total) / $rows);
        //Generate QRcode
        $this->createQrcode($purchase_orders->po_no, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $purchase_orders->po_no . '</title>
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
            $this->db->select('a.*, b.number as item_id, b.description as item_name, c.currency, a.price, e.name as uom');
            $this->db->from('purchase_orders a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->join('supplier_items d', 'a.supplier_id = d.supplier_id and a.item_id = d.item_id');
            $this->db->join('uom e', 'b.uom_id = e.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.po_no', base64_decode($po_no));
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(8, ($i * 8));
            $records = $this->db->get()->result_array();

            if ($purchase_orders->updated_date != null) {
                $revision_date = $purchase_orders->updated_date;
            } else {
                $revision_date = $purchase_orders->created_date;
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
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $purchase_orders->po_no . '.png') . '" width="60"/></td>
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
                                    <small>NO : ' . @$purchase_orders->po_no . '</small>
                                </center>
                                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                    <tr>
                                        <td width="80">Supplier</td>
                                        <td width="10">:</td>
                                        <td width="30%"><b>' . @$supplier->name . '</b></td>
                                        <td style="text-align:right; padding-right: 20px;" rowspan="7">
                                            Page <b>' . $hal  . '</b> of <b> ' . $page . '</b><br><br>
                                            PO Date:<br><b>' . date("d F Y", strtotime($purchase_orders->po_date)) . '</b><br>
                                            Revision:<br><b>' . $purchase_orders->revision . '</b><br>
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
                                        <td><b>' . @$purchase_orders->request_no . '</b></td>
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
                                <td>' . $record['item_id'] . '</td>
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
                $this->db->select('a.remarks, b.number as item_id, b.description as item_name');
                $this->db->from('purchase_orders a');
                $this->db->join('items b', 'a.item_id = b.id');
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
                        $html .= $remark['item_id'] . " &nbsp; (" . $remark['remarks'] . ") <br>";
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
                                <th style="text-align:right;">' . number_format(@$purchase_orders->total_dp, 2) . '</th>
                            </tr>
                            <tr>
                                <th style="text-align:left" colspan="2">Total Amount</th>
                                <th style="text-align:right;">' . number_format($subtotal + ((@$subtotal * $tax) / 100) - @$purchase_orders->total_dp, 2) . '</th>
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
            header("Content-Disposition: attachment; filename=purchase_orders_$format.xls");
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
            b.number as item_id, 
            b.name as item_name,
            c.name as item_family_name, 
            d.name as supplier_name, 
            d.currency, 
            e.mpq, 
            e.moq,
            f.name as uom');
        $this->db->from('purchase_orders a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_id = c.id');
        $this->db->join('suppliers d', 'a.supplier_id = d.id');
        $this->db->join('supplier_items e', 'a.item_id = e.item_id and a.supplier_id = e.supplier_id');
        $this->db->join('uom f', 'b.uom_id = f.id');
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
                                <small>PURCHASE ORDER</small>
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
                        <td>' . $data['item_id'] . '</td>
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
