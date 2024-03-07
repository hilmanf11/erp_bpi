<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Delivery_orders extends CI_Controller
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
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/delivery_orders');
        } else {
            redirect('error_access');
        }
    }

    public function readSalesOrderDeliveries($sales_order)
    {
        if($sales_order == "FG"){
            $send = $this->crud->query("SELECT DISTINCT trans_date FROM sales_order_deliveries WHERE `status` = '0'");
            echo json_encode($send);
        }else{
            $send = $this->crud->query("SELECT DISTINCT trans_date FROM sales_order_delivery_rm WHERE `status` = '0'");
            echo json_encode($send);
        }
    }

    public function readsC($sales_order, $delivery_date)
    {
        $delivery_date = base64_decode($delivery_date);
        
        if($sales_order == "FG"){
            $send = $this->crud->query("SELECT c.id, c.name, c.number
                FROM sales_orders a
                JOIN sales_order_deliveries b ON a.sales_order_no = b.sales_order_no and b.status = 0
                JOIN customers c ON a.customer_id = c.id
                JOIN production_schedules d ON a.sales_order_no = d.so_number
                WHERE a.status = 0 and b.trans_date = '$delivery_date' GROUP BY c.id");
            echo json_encode($send);
        }else{
            $send = $this->crud->query("SELECT c.id, c.name, c.number
                FROM sales_order_rm a
                JOIN sales_order_delivery_rm b ON a.sales_order_no = b.sales_order_no and b.status = 0
                JOIN customers c ON a.customer_id = c.id
                WHERE a.status = 0 and b.trans_date = '$delivery_date' GROUP BY c.id");
            echo json_encode($send);
        }
    }

    public function readsCustOrderNo($sales_order, $customer_id, $delivery_date)
    {
        $delivery_date = base64_decode($delivery_date);
        $customer_id = base64_decode($customer_id);

        if($sales_order == "FG"){
            $send = $this->crud->query("SELECT a.customer_order_no 
                    FROM sales_orders a 
                    JOIN customers c ON a.customer_id = c.id
                    JOIN sales_order_deliveries b ON a.sales_order_no = b.sales_order_no and b.status = 0
                    WHERE a.customer_id= '$customer_id' and a.status = 0 and b.trans_date = '$delivery_date' GROUP BY a.customer_order_no");
                echo json_encode($send);
        }else{
            $send = $this->crud->query("SELECT a.customer_order_no 
                    FROM sales_order_rm a 
                    JOIN customers c ON a.customer_id = c.id
                    JOIN sales_order_delivery_rm b ON a.sales_order_no = b.sales_order_no and b.status = 0
                    WHERE a.customer_id= '$customer_id' and a.status = 0 and b.trans_date = '$delivery_date' GROUP BY a.customer_order_no");
                echo json_encode($send);
        }
    }

    public function number($delivery_order_date, $customer_no)
    {
        $datenow    = "DO" . $customer_no . date("ym", strtotime(base64_decode($delivery_order_date)));
        $sqlGetID   = $this->db->query("SELECT max(`delivery_order_no`) as kode FROM delivery_orders WHERE `delivery_order_no` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $datenow . $autoID;
    }

    public function datatablesTemp($sales_order, $delivery_date, $customer_id, $customer_order_no)
    {
        $delivery_date = base64_decode($delivery_date);
        $customer_id = base64_decode($customer_id);
        $customer_order_no = explode(",", base64_decode($customer_order_no));

        if($sales_order == "FG"){
            $this->db->select('b.item_fg_id, d.number as item_fg_number, d.name as item_fg_name, 
                b.customer_order_no,
                b.sales_order_no,
                d.uom, 
                b.qty as qty_so, 
                (b.qty - COALESCE(SUM(c.qty_del), 0)) as qty_remain,
                COALESCE(SUM(c.qty_del), 0) as qty_do,
                COALESCE(a.qty, 0) as qty_del,
                COALESCE(SUM(e.qty), 0) as stock,
                ((b.qty - COALESCE(SUM(c.qty_del), 0)) - a.qty) as stock_bal');
            $this->db->from('sales_orders b');
            $this->db->join('sales_order_deliveries a', 'a.sales_order_no = b.sales_order_no and a.item_fg_id = b.item_fg_id and a.customer_id = b.customer_id');
            $this->db->join('delivery_orders c', 'b.sales_order_no = c.sales_order_no and b.item_fg_id = c.item_fg_id and b.customer_id = c.customer_id', 'left');
            $this->db->join('item_fg d', 'b.item_fg_id = d.id');
            $this->db->join('scan_item_receipts_fg e', 'a.sales_order_no = e.so_number', 'left');
            $this->db->where('b.customer_id', $customer_id);
            $this->db->where_in('b.customer_order_no', $customer_order_no);
            $this->db->group_by('b.item_fg_id');
            $this->db->group_by('b.sales_order_no');
            $this->db->order_by('b.item_fg_id', 'asc');
        }else{
            $this->db->select('b.item_fg_id, d.number as item_fg_number, d.name as item_fg_name, 
                b.customer_order_no,
                b.sales_order_no,
                d.uom, 
                b.qty as qty_so, 
                (b.qty - COALESCE(SUM(c.qty_del), 0)) as qty_remain,
                COALESCE(SUM(c.qty_del), 0) as qty_do,
                COALESCE(a.qty, 0) as qty_del,
                COALESCE(SUM(e.qty), 0) as stock,
                ((b.qty - COALESCE(SUM(c.qty_del), 0)) - a.qty) as stock_bal');
            $this->db->from('sales_order_rm b');
            $this->db->join('sales_order_delivery_rm a', 'a.sales_order_no = b.sales_order_no and a.item_fg_id = b.item_fg_id and a.customer_id = b.customer_id');
            $this->db->join('delivery_orders c', 'b.sales_order_no = c.sales_order_no and b.item_fg_id = c.item_fg_id and b.customer_id = c.customer_id', 'left');
            $this->db->join('item_fg d', 'b.item_fg_id = d.id');
            $this->db->join('scan_item_receipts_fg e', 'a.sales_order_no = e.so_number', 'left');
            $this->db->where('b.customer_id', $customer_id);
            $this->db->where_in('b.customer_order_no', $customer_order_no);
            $this->db->group_by('b.item_fg_id');
            $this->db->group_by('b.sales_order_no');
            $this->db->order_by('b.item_fg_id', 'asc');
        }
        
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_delivery_order_no = @base64_decode($get['filter_delivery_order_no']);
            $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
            $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);
            $filter_status = @base64_decode($get['filter_status']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, b.name as customer_name");
            $this->db->from('delivery_orders a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.delivery_order_date >=', $filter_from);
                $this->db->where('a.delivery_order_date <=', $filter_to);
            }
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.delivery_order_no', $filter_delivery_order_no);
            $this->db->like('a.sales_order_no', $filter_sales_order_no);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->like('a.customer_order_no', $filter_customer_order_no);
            $this->db->like('a.status', $filter_status);
            $this->db->group_by('a.delivery_order_no');
            $this->db->order_by('a.status', 'ASC');
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

    //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $delivery_order_no = base64_decode($this->input->get('delivery_order_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $delivery_order_no = base64_decode($this->input->get('delivery_order_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $delivery_orders = $this->crud->read("delivery_orders", [], ["delivery_order_no" => $post['delivery_order_no'], "item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no']]);

            if (@$delivery_orders->delivery_order_no != "") {
                $send = $this->crud->update('delivery_orders', [
                    "delivery_order_no" => $post['delivery_order_no'], 
                    "item_fg_id" => $post['item_fg_id'], 
                    "sales_order_no" => $post['sales_order_no']
                ], ["remarks" => $post['remarks']]);
            } else {
                $send = $this->crud->create('delivery_orders', $post);

                //Ubah Status Sales Order Delivery
                $this->crud->update("sales_order_deliveries", ["item_fg_id" => $post['item_fg_id'], "sales_order_no" => $post['sales_order_no'], "trans_date" => $post['delivery_date']], ["status" => 1]);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $delivery_orders = $this->crud->read("delivery_orders", [], $data);
        foreach ($delivery_orders as $delivery_order) {
            $this->crud->update("sales_order_deliveries", [
                "item_fg_id" => $delivery_order->item_fg_id,
                "sales_order_no" => $delivery_order->sales_order_no,
                "trans_date" => $delivery_order->delivery_date
            ], [
                "status" => 0
            ]);
        }

        $send = $this->crud->delete('delivery_orders', $data);
        echo $send;
    }

    public function print_do($delivery_order_no)
    {
        $delivery_order_no = base64_decode($delivery_order_no);

        $delivery_orders = $this->crud->reads('delivery_orders', [], ["delivery_order_no" => $delivery_order_no]);
        $delivery_order = $this->crud->read('delivery_orders', [], ["delivery_order_no" => $delivery_order_no]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 10;
        $page = ceil(count($delivery_orders) / $rows);
        //Generate QRcode
        $this->createQrcode($delivery_order_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $delivery_order->delivery_order_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center>
                    <h1>Press CTRL + P for Print</h1>
                    <p>Display pages for 10 rows</p>
                    <p>Paper Size A4, Layout Landscape</p>
                    <p>Margin Default, Scale 98</p>
                </center></div><div class="print">';
        //Loop Page
        $no = 1;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.name as customer_name');
            $this->db->from('delivery_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers c', 'a.customer_id = c.id');
            $this->db->where('a.delivery_order_no', $delivery_order_no);
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(10, ($i * 10));
            $records = $this->db->get()->result_array();

            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="40" /></th>
                                <td width="300" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->description . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
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
                        <div style="border: 1px solid black; width:100%; height:73%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>DELIVERY ORDER</h3>
                                </center>
                                <div style="float:left; width:60%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150">Delivery Order No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$delivery_order->delivery_order_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Delivery Order Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . date("d F Y", strtotime(@$delivery_order->delivery_order_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Delivery Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . date("d F Y", strtotime(@$delivery_order->delivery_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Customer Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['customer_name'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:40%; text-align:right;">
                                    <img style="margin-right:10px;" src="' . base_url('assets/image/qrcode/' . $delivery_order->delivery_order_no . '.png') . '" width="80"/><br>
                                    <small style="font-size:10px; margin-right:16px;">' . $delivery_order->delivery_order_no . '</small><br><br>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th>Product ID</th>
                                        <th>Product No</th>
                                        <th>Product Name</th>
                                        <th>UoM</th>
                                        <th width="60">Qty</th>
                                        <th width="120">Sales Order No</th>
                                        <th width="120">Customer Order No</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_fg_id'] . '</td>
                                <td>' . $record['item_fg_number'] . '</td>
                                <td>' . $record['item_fg_name'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right">' . number_format($record['qty_del'], 2, ",", ".") . '</td>
                                <td>' . $record['sales_order_no'] . '</td>
                                <td>' . $record['customer_order_no'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>';
            if ($i + 1 != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }

            $html .= '</div></div>';

            if (($i + 1) == $page) {
                $html .= '  <div style="position:fixed; bottom:0; width:98.7%;">
                                <table id="customers" style="margin-top:10px;">
                                    <tr>
                                        <th width="400" style="text-align:left; vertical-align:top;" rowspan="4">Note.</th>
                                    </tr>
                                    <tr>
                                        <th width="200" style="text-align:center;">AUTHORISED SIGNATURE</th>
                                        <th width="200" style="text-align:center;">DELIVER CONTROL</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;"></th>
                                        <th style="height:80px;"></th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;"></th>
                                        <th style="height:20px; text-align:center;"></th>
                                    </tr>
                                </table>
                            </div>';
            }
        }
        $html .= '</div><script>window.print()</script>';
        die($html);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=delivery_orders_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_delivery_order_no = @base64_decode($get['filter_delivery_order_no']);
        $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
        $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);
        $filter_status = @base64_decode($get['filter_status']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.name as customer_name, c.number as item_fg_number, c.name as item_fg_name");
        $this->db->from('delivery_orders a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.delivery_order_date >=', $filter_from);
            $this->db->where('a.delivery_order_date <=', $filter_to);
        }
        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.delivery_order_no', $filter_delivery_order_no);
        $this->db->like('a.sales_order_no', $filter_sales_order_no);
        $this->db->like('a.item_fg_id', $filter_item_fg);
        $this->db->like('d.customer_order_no', $filter_customer_order_no);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.delivery_order_no', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customer_items {border-collapse: collapse;width: 100%;font-size: 12px;}#customer_items td, #customer_items th {border: 1px solid #ddd;padding: 2px;}#customer_items tr:nth-child(even){background-color: #f2f2f2;}#customer_items tr:hover {background-color: #ddd;}#customer_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>' . $config->description . '</small>
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
                <h3>DELIVERY ORDER</h3>
            </div>
        </center>
        
        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer Name</th>
                <th>Delivery Order No</th>
                <th>Delivery Order Date</th>
                <th>Delivery Date</th>
                <th>Trans Type</th>
                <th>Sales Order No</th>
                <th>Customer Order No</th>
                <th>Remarks</th>
                <th>Product ID</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Uom</th>
                <th>Qty SO</th>
                <th>Qty Remain</th>
                <th>Qty DO</th>
                <th>Qty Delivery</th>
                <th>Stock</th>
                <th>Stock Balance</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['delivery_order_no'] . '</td>
                        <td>' . $data['delivery_order_date'] . '</td>
                        <td>' . $data['delivery_date'] . '</td>
                        <td>' . $data['trans_type'] . '</td>
                        <td>' . $data['sales_order_no'] . '</td>
                        <td>' . $data['customer_order_no'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['item_fg_id'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty_so'] . '</td>
                        <td>' . $data['qty_remain'] . '</td>
                        <td>' . $data['qty_do'] . '</td>
                        <td>' . $data['qty_del'] . '</td>
                        <td>' . $data['stock'] . '</td>
                        <td>' . $data['stock_bal'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
