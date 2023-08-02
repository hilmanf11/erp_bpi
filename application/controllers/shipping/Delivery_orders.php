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
        //Validasi Form
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('shipping/delivery_orders');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        $number_no = base64_decode($this->input->get('number'));
        $number_ex = explode(",", $number_no);
        
        $this->db->select('a.item_id, a.number, a.trans_date, a.so_number, f.customer_po, a.trans_type, a.note, b.number as item_number, b.name as item_name, c.name as uom, d.name as customer_name, a.delivery as qty');
        $this->db->from('delivery_orders a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('uom c', 'b.uom_id = c.id');
        $this->db->join('customers d', 'a.customer_id = d.id');
        $this->db->join('sales_orders f', 'f.number = a.so_number and f.item_id = a.item_id and a.customer_id = f.customer_id', 'left');
        $this->db->join('shipping_orders e', 'a.number = e.do_number');
        $this->db->where_in('a.number', $number_ex);
        $this->db->group_by('a.so_number');
        $this->db->group_by('a.item_id');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readDeliveryTotal(){
        $so_number = $this->input->post('so_number');
        $item_id = $this->input->post('item_id');
        $delivery_orders = $this->crud->query("SELECT SUM(delivery) as total_do FROM delivery_orders WHERE so_number = '$so_number' and item_id = '$item_id'");
        
        if($delivery_orders[0]->total_do != null){
            echo json_encode($delivery_orders);
        }else{
            echo json_encode(array(['total_do' => 0]));
        }
    }

    public function readDeliveryno($customer_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $delivery_orders = $this->crud->query("SELECT DISTINCT `number`, trans_type, note FROM delivery_orders WHERE customer_id = '$customer_id' and `status` = '0' and `number` like '%$post%' ORDER BY `number` ASC");
        echo json_encode($delivery_orders);
    }

    public function readSalesOrders()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $customer_id = $this->input->get('customer_id');
        $trans_type = $this->input->get('trans_type');

        if ($trans_type == "SALES") {
            $sales_orders = $this->crud->query("SELECT DISTINCT a.number, a.trans_date 
                FROM sales_orders a JOIN scan_item_receipts_fg b ON a.number = b.so_number WHERE a.customer_id = '$customer_id' and a.status in ('1','0') and a.number like '%$post%' 
                ORDER BY a.number ASC");
            echo json_encode($sales_orders);
        } else {
            $datenow    = $trans_type . "-" . date("ymd");
            $sqlGetID   = $this->db->query("SELECT max(`so_number`) as kode FROM delivery_orders WHERE `so_number` like '%$datenow%'");
            $rowID      = $sqlGetID->row();
            $kode       = $rowID->kode;
            if ($kode == NULL) {
                $autoID = sprintf("%02s", $kode + 1);
            } else {
                $urutan = (int) substr($kode, -2);
                $urutan++;
                $autoID = sprintf("%02s", $urutan);
            }

            $number = $datenow . "-" . $autoID;

            echo json_encode([array("number" => $number)]);
        }
    }

    public function readSalesOrderItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $number = $this->input->get('number');
        $trans_type = $this->input->get('trans_type');
        $customer_id = $this->input->get('customer_id');

        if ($trans_type == "SALES") {
            $sales_orders = $this->crud->query("SELECT a.item_id, b.number as item_number, b.name as item_name, c.name as uom, SUM(a.qty) as qty, a.price, a.status
            FROM sales_orders a 
            JOIN items b ON a.item_id = b.id
            JOIN uom c ON b.uom_id = c.id
            WHERE a.number = '$number' and a.customer_id = '$customer_id' and a.status in ('1','0') and b.number like '%$post%'
            GROUP BY a.item_id
            ORDER BY b.number ASC");

            $datas = [];
            foreach ($sales_orders as $sales_order) {
                $item_id = $sales_order->item_id;
                $endstock = $this->crud->query("SELECT
                    a.id,
                    (COALESCE(SUM(f.qty),0) - COALESCE(g.qty, 0)) as begin_stock
                    FROM items a 
                    JOIN item_familys b ON a.item_family_id = b.id
                    JOIN uom c ON a.uom_id = c.id
                    LEFT JOIN production_schedules d ON a.id = d.item_id
                    LEFT JOIN checksheets e ON d.workorder = e.workorder
                    LEFT JOIN scan_item_receipts_fg f ON e.number = f.checksheet_number
                    LEFT JOIN (SELECT item_id, trans_date, COALESCE(SUM(qty), 0) as qty FROM delivery_notes GROUP BY item_id) g ON a.id = g.item_id
                    WHERE a.id = '$item_id'
                    GROUP BY a.id
                    ORDER BY a.number");

                $datas[] = array(
                    "item_id" => $sales_order->item_id,
                    "item_number" => $sales_order->item_number,
                    "item_name" => $sales_order->item_name,
                    "uom" => $sales_order->uom,
                    "qty" => $sales_order->qty,
                    "price" => $sales_order->price,
                    "stock" => @$endstock[0]->begin_stock
                );
            }

            echo json_encode($datas);
        } elseif ($trans_type == "SAMPLE") {
            $sales_orders = $this->crud->query("SELECT
                a.id as item_id,
                a.number as item_number,
                a.name as item_name,
                c.name as uom,
                '0' as qty,
                '0' as price,
                (COALESCE(SUM(f.qty),0) - COALESCE(g.qty, 0)) as stock,
                d.workorder
            FROM items a 
            JOIN item_familys b ON a.item_family_id = b.id
            JOIN uom c ON a.uom_id = c.id
            LEFT JOIN production_schedules d ON a.id = d.item_id
            LEFT JOIN checksheets e ON d.workorder = e.workorder
            LEFT JOIN scan_item_receipts_fg f ON e.number = f.checksheet_number
            LEFT JOIN (SELECT item_id, trans_date, COALESCE(SUM(qty), 0) as qty FROM delivery_notes GROUP BY item_id) g ON a.id = g.item_id
            JOIN customer_items i ON i.item_id = a.id
            WHERE i.customer_id = '$customer_id' and a.number like '%$post%'
            GROUP BY a.id
            ORDER BY a.number");

            echo json_encode($sales_orders);
        } else {
            $sales_orders = $this->crud->query("SELECT a.item_id, b.number as item_number, b.name as item_name, c.name as uom, coalesce(a.qty, 0) as qty, a.price, coalesce(d.stock, 0) as stock, d.workorder
            FROM items b
            JOIN uom c ON b.uom_id = c.id
            JOIN customer_items f ON f.item_id = b.id
            LEFT JOIN sales_orders a ON a.item_id = b.id
            LEFT JOIN (SELECT b.item_id, b.workorder, sum(a.qty) as stock FROM scan_item_receipts_fg a JOIN production_schedules b ON a.workorder = b.workorder GROUP BY b.item_id, b.workorder) d ON d.item_id = b.id
            WHERE f.customer_id = '$customer_id' and b.number like '%$post%' GROUP BY d.workorder, b.number ORDER BY b.number ASC");

            echo json_encode($sales_orders);
        }
    }

    public function number($trans_date)
    {
        $datenow    = "DO" . date("Ymd", strtotime(base64_decode($trans_date)));
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM delivery_orders WHERE `number` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $datenow . "-" . $autoID;
    }

    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_delivery_no = $this->input->get('filter_delivery_no');
        $filter_customer = $this->input->get('filter_customer');

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
            $this->db->select('a.number, a.trans_date, a.so_number, a.workorder, d.name as customer_name, SUM(a.qty) as qty, SUM(a.delivery) as delivery, a.status, a.customer_id, a.trans_type, a.note');
            $this->db->from('delivery_orders a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->join('customers d', 'a.customer_id = d.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.number', $filter_delivery_no);
            $this->db->like('a.customer_id', $filter_customer);
            $this->db->group_by('a.number');
            $this->db->order_by('a.number', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['number'],
                    "number" => $record['number'],
                    "trans_date" => $record['trans_date'],
                    "trans_type" => $record['trans_type'],
                    "customer_id" => $record['customer_id'],
                    "customer_name" => $record['customer_name'],
                    "qty" => $record['qty'],
                    "delivery" => $record['delivery'],
                    "note" => $record['note'],
                    "status" => $record['status'],
                    "state" => "closed",
                );
            }
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        } else {
            $this->db->select('a.*, e.customer_po, b.number as item_number, b.name as item_name, b.description, d.name as customer_name, c.name as uom');
            $this->db->from('delivery_orders a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->join('customers d', 'a.customer_id = d.id');
            $this->db->join('sales_orders e', 'a.so_number = e.number', 'left');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.number', $id);
            $this->db->like('a.customer_id', $filter_customer);
            $this->db->order_by('a.delivery', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function datatable_updates(){
        $number = base64_decode($this->input->get('number'));
        $records = $this->crud->query("SELECT a.*, b.id as item_id, b.number as item_number, b.name as item_name, c.name as uom
            FROM delivery_orders a
            JOIN items b on a.item_id = b.id
            JOIN uom c on b.uom_id = c.id
            WHERE a.status = '0' and a.number = '$number'");
        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();

                $sales_orders = $this->crud->read('sales_orders', [], ["number" => $post['so_number'], "item_id" => $post['item_id']]);

                if ($post['id'] != "") {
                    $send = $this->crud->update('delivery_orders', ["id" => $post['id']], $post);
                } else {
                    if($sales_orders->status == 1){
                        $status = "2";
                    }else{
                        $status = "0";
                    }

                    $send = $this->crud->create('delivery_orders', $post);
                    $update = $this->crud->update('sales_orders', ["number" => $post['so_number'], "item_id" => $post['item_id'], "customer_id" => $post['customer_id']], ["status" => $status]);
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
        $send = $this->crud->delete('delivery_orders', $data);
        $sales_orders = $this->crud->read('sales_orders', [], ["number" => $data['so_number'], "item_id" => $data['item_id']]);

        if($sales_orders->status == 1){
            $status = "1";
        }else{
            $status = "0";
        }

        $update = $this->crud->update('sales_orders', ["number" => $data['so_number'], "item_id" => $data['item_id'], "customer_id" => $data['customer_id']], ["status" => $status]);
        echo $send;
    }

    public function print_delivery($delivery_no)
    {
        $delivery_no = base64_decode($delivery_no);
        $delivery_orders = $this->crud->reads('delivery_orders', [], ["number" => $delivery_no]);
        $delivery_order = $this->crud->read('delivery_orders', [], ["number" => $delivery_no]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 10;
        $page = ceil(count($delivery_orders) / $rows);
        //Generate QRcode
        $this->createQrcode($delivery_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $delivery_order->number . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
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
            $this->db->select('a.*, b.number as item_id, b.name as item_name, b.description, c.name as uom, d.name as customer_name, d.address, d.attention, d.telp_billing');
            $this->db->from('delivery_orders a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->join('customers d', 'a.customer_id = d.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.number', $delivery_no);
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(10, ($i * 10));
            $records = $this->db->get()->result_array();
            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                <td width="300" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $delivery_order->number . '.png') . '" width="60"/></td>
                                            <td width="80">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_delivery_order . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_delivery_order . '</td>
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
                        <div style="border: 1px solid black; width:100%; height:73%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>DELIVERY ORDER</h3>
                                </center>
                                <div style="float:left; width:60%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Delivery Order No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$delivery_order->number . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Customer Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . $records[0]['customer_name'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:40%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td>Trans Type</td>
                                            <td>:</td>
                                            <td><b>' . @$delivery_order->trans_type . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Delivery Date</td>
                                            <td>:</td>
                                            <td><b>' . date("d F Y", strtotime($delivery_order->trans_date)) . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th width="120">Sales Order No</th>
                                        <th>Product No</th>
                                        <th>Product Name</th>
                                        <th>UoM</th>
                                        <th width="60">Qty</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td style="font-size:10px;">' . $record['so_number'] . '</td>
                                <td style="font-size:10px;">' . $record['item_id'] . '</td>
                                <td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>
                                <td><span style="font-size:10px;">' . $record['uom'] . '</span></td>
                                <td style="text-align:right">' . number_format($record['delivery'], 2, ",", ".") . '</td>
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
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=delivery_orders_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_delivery_no = $this->input->get('filter_delivery_no');
        $filter_customer = $this->input->get('filter_customer');
        
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as item_id, b.name as item_name, f.name as uom, c.name as customer_name');
        $this->db->from('delivery_orders a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->join('uom f', 'b.uom_id = f.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('a.number', $filter_delivery_no);
        $this->db->like('c.id', $filter_customer);
        $this->db->order_by('b.number', 'ASC');
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
                                <small>DELIVERY ORDER</small>
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
                <th>Delivery No</th>
                <th>Delivery Date</th>
                <th>Customer</th>
                <th>Sales Order No</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Uom</th>
                <th>Status</th>
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
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['trans_date'] . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['so_number'] . '</td>
                        <td>' . $data['item_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['qty'], 2) . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $status . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
