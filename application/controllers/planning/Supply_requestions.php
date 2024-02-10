<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Supply_requestions extends CI_Controller
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
            $this->load->view('planning/supply_requestions');
        } else {
            redirect('error_access');
        }
    }
    public function readPeriod()
    {
        $records = $this->crud->query("SELECT `period` FROM supply_requestions WHERE `status` = '0' GROUP BY `period`");
        echo json_encode($records);
    }
    public function readWp($period)
    {
        $records = $this->crud->query("SELECT wp FROM supply_requestions WHERE `status` = '0' and `period` = '$period' GROUP BY wp");
        echo json_encode($records);
    }
    public function readRequestNo($period, $wp)
    {
        $wp = base64_decode($wp);
        $records = $this->crud->query("SELECT request_no FROM supply_requestions WHERE status = '0' and `period` = '$period' and wp = '$wp' GROUP BY `request_no`");
        echo json_encode($records);
    }
    public function request_no($trans_date)
    {
        $trans_date = base64_decode($trans_date);
        $datenow    = date("ymd", strtotime($trans_date));
        $sqlGetID   = $this->db->query("SELECT max(request_no) as kode FROM supply_requestions WHERE request_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "PRQ-" . $datenow . "-" . $autoID;
    }
    public function datatables()
    {
        if ($this->input->post()) {
            $filter_period = $this->input->get('filter_period');
            $filter_wp   = $this->input->get('filter_wp');
            $filter_request_no = $this->input->get('filter_request_no');
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
                $this->db->select('a.*, e.number as item_number, e.name as item_name, b.uom');
                $this->db->from('supply_requestions a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->join('supply_sheets c', 'a.workorder = c.workorder','left');
                // $this->db->join('uom d', 'b.uom_id = d.id');
                $this->db->join('item_fg e', 'c.item_fg_id = e.id','left');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.status', 0);
                $this->db->like("a.period", $filter_period);
                $this->db->like("a.wp", $filter_wp);
                if ($filter_request_no != "") {
                    $this->db->where('a.request_no', $filter_request_no);
                }
                $this->db->group_by('a.request_no');
                $this->db->order_by('a.request_no', 'DESC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {
                    $arr[] = array(
                        "id" => $record['request_no'],
                        "request_no" => $record['request_no'],
                        "request_date" => $record['request_date'],
                        "request_name" => $record['request_name'],
                        "period" => $record['period'],
                        "wp" => $record['wp'],
                        "workorder" => $record['workorder'],
                        "item_number" => $record['item_number'],
                        "item_name" => $record['item_name'],
                        "state" => "closed"
                    );
                }
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                //Select Query
                $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
                $this->db->from('supply_requestions a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                // $this->db->join('uom d', 'b.uom_id = d.id');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.status', 0);
                $this->db->where('a.request_no', $id);
                $this->db->order_by('a.request_no', 'DESC');
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {
                    $arr[] = array(
                        "id" => $record['id'],
                        "request_no" => $record['request_no'],
                        "request_date" => $record['request_date'],
                        "request_name" => $record['request_name'],
                        "period" => $record['period'],
                        "wp" => $record['wp'],
                        "workorder" => $record['workorder'],
                        "item_rm_id" => $record['item_rm_id'],
                        "item_number" => $record['item_number'],
                        "item_name" => $record['item_name'],
                        "qty" => $record['qty'],
                        "uom" => $record['uom'],
                        "status" => $record['status'],
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
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                if ($post['qty'] == 0) {
                    echo json_encode(array("title" => "Qty 0", "message" => " Qty is 0", "theme" => "error"));
                } else {
                    $item_rm_id = $post['item_rm_id'];
                    $wip_balances = $this->crud->read("wip_balances", [], ["item_rm_id" => $item_rm_id], "", "id", "desc");
                    $supplier_items = $this->crud->read("supplier_items", [], ["item_rm_id" => $item_rm_id]);
                    $items = $this->crud->read("item_rm", [], ["id" => $item_rm_id]);

                    $dateNow = date("Y-m-d");

                    //Stock kWarehouse RM
                    $stockWarehouse = $this->crud->query("SELECT
                        a.id,
                        (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty,0) - COALESCE(f.qty, 0)) as end_stock
                    FROM item_rm a 
                    JOIN item_familys b ON a.item_family_id = b.id
                    JOIN uom c ON a.uom = c.name
                    LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date <= '$dateNow'
                    LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
                    LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$dateNow' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                    LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
                        FROM return_materials a 
                        JOIN return_material_labels b ON a.return_id = b.return_id
                        JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                        WHERE a.return_date <= '$dateNow'
                        GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
                    WHERE a.id like '$item_rm_id'
                    GROUP BY a.id
                    ORDER BY a.number");

                    $qIssued = $this->db->query("SELECT COALESCE(SUM(a.qty), 0) as balance 
                    FROM scan_item_receipts a 
                    JOIN purchase_order_receipts b ON a.receipt_id = b.receipt_id
                    WHERE b.item_rm_id = '$item_rm_id' 
                    GROUP BY b.item_rm_id");
                    $dIssued = $qIssued->row();

                    if (!empty($wip_balances->balance)) {
                        if ($post['qty'] >= $wip_balances->balance) {
                            //kalo items di hitung mpq
                            if (@$supplier_items->calculate == 1) {
                                $begin = $wip_balances->balance;
                                $need = $post['qty'];
                                $issued = (ceil(($post['qty'] - $wip_balances->balance) / @$supplier_items->mpq) * @$supplier_items->mpq);
                                $balance = (($wip_balances->balance + $issued) - $post['qty']);
                                $warehouse = (@$stockWarehouse[0]->end_stock - $issued);
                            } elseif (@$supplier_items->calculate == 0) {
                                $begin = $wip_balances->balance;
                                $need = $post['qty'];
                                $issued = ($wip_balances->balance + $post['qty']);
                                $balance = ($issued - $post['qty']);
                                $warehouse = (@$stockWarehouse[0]->end_stock - $issued);
                            } else {
                                $begin = $wip_balances->balance;
                                $need = $post['qty'];
                                $issued = 0;
                                $balance = 0;
                                $warehouse = (@$stockWarehouse[0]->end_stock - $issued);
                            }
                        } else {
                            $begin = $wip_balances->balance;
                            $need = $post['qty'];
                            $issued = 0;
                            $balance = (($wip_balances->balance + $issued) - $post['qty']);
                            $warehouse = (@$stockWarehouse[0]->end_stock);
                        }
                    } else {
                        if ($post['qty'] >= @$supplier_items->mpq) {
                            $begin = 0;
                            $need = $post['qty'];
                            $issued = $post['qty'];
                            $balance = 0;
                            $warehouse = (@$dIssued->balance - $issued);
                        } else {
                            $begin = 0;
                            $need = $post['qty'];
                            $issued = @$supplier_items->mpq;
                            $balance = (@$supplier_items->mpq - $post['qty']);
                            $warehouse = (@$dIssued->balance - $issued);
                        }
                    }

                    if ($items->supply == 0) {
                        $balance = $this->crud->create("wip_balances", [
                            "item_rm_id" => $item_rm_id,
                            "request_no" => $post['request_no'],
                            "begin" => $begin,
                            "need" => $need,
                            "issued" => $issued,
                            "balance" => $balance,
                            "warehouse" => $warehouse
                        ]);
                    }
                    $send   = $this->crud->create('supply_requestions', $post);
                    echo $send;
                }
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
        $send = $this->crud->delete('supply_requestions', ["id" => $data['id']]);
        $delete = $this->crud->delete('wip_balances', ["request_no" => $data['request_no'], "item_rm_id" => $data['item_rm_id']]);
        echo $send;
    }

    public function print_kanban($request_no)
    {
        $kanbans = $this->crud->reads('supply_requestions', [], ["request_no" => base64_decode($request_no)]);
        $kanban = $this->crud->read('supply_requestions', [], ["request_no" => base64_decode($request_no)]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        $rows = 8;
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
            $this->db->select('a.*, b.number as item_number, b.name as item_name, f.location, b.uom');
            $this->db->from('supply_requestions a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            // $this->db->join('uom d', 'b.uom_id = d.id');
            $this->db->join('warehouse_location_items f', "a.item_rm_id = f.item_rm_id and type = 'RM'", 'left');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.request_no', base64_decode($request_no));
            $this->db->limit(8, ($i * 8));
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
                                    <h3><u>MATERIAL REQUESTION</u></h3>
                                </center>
                                <div style="float:left; width:30%;"> 
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Request No</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$kanban->request_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Request Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . @date("d F Y", strtotime($kanban->request_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Request Name</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$kanban->request_name . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:30%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Period / WP</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$kanban->period . ' / ' . @$kanban->wp . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Work Order ID</td>
                                            <td width="30">:</td>
                                            <td><b>' . @$kanban->workorder . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:30%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Print Date</td>
                                            <td width="30">:</td>
                                            <td><b>' . @date("d F Y") . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="50">Print By</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$this->session->name . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th>No</th>
                                        <th>Product No</th>
                                        <th>Product Name</th>
                                        <th>Product Specification</th>
                                        <th>Uom</th>
                                        <th>Qty</th>
                                        <th width="80">WHS Stock</th>
                                        <th width="80">WIP Balance</th>
                                        <th width="80">WHS Location</th>
                                        <th width="80">Remarks</th>
                                    </tr>';
            $no = 1;
            foreach ($records as $record) {
                $item_rm_id = $record['item_rm_id'];
                $wip_balances = $this->crud->read("wip_balances", [], ["item_rm_id" => $record['item_rm_id'], "request_no" => $record['request_no']], "", "id", "desc");
                $dateNow = date("Y-m-d");

                //Stock kWarehouse RM
                $stockWarehouse = $this->crud->query("SELECT
                    a.id,
                    (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty,0) - COALESCE(f.qty, 0)) as end_stock
                FROM item_rm a 
                JOIN item_familys b ON a.item_family_id = b.id
                JOIN uom c ON a.uom = c.uom
                LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date <= '$dateNow'
                LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
                LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') <= '$dateNow' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
                LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
                    FROM return_materials a 
                    JOIN return_material_labels b ON a.return_id = b.return_id
                    JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
                    WHERE a.return_date <= '$dateNow'
                    GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id
                WHERE a.id like '$item_rm_id'
                GROUP BY a.id
                ORDER BY a.number");

                if (@$wip_balances->balance > $record['qty']) {
                    $remarksSupply = "<b style='color:red;'>NO SUPPLY</b>";
                } else {
                    $remarksSupply = "<b style='color:green;'>-</b>";
                }

                $html .= '  <tr>
                                <td>' . $no . '</td>
                                <td>' . $record['item_number'] . '</td>
                                <td>' . $record['item_name'] . '</td>
                                <td>' . $record['description'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . $record['qty'] . '</td>
                                <td style="text-align:right;">' . number_format((@$stockWarehouse[0]->end_stock), 2) . '</td>
                                <td style="text-align:right;">' . number_format((@$wip_balances->balance), 2) . '</td>
                                <td style="text-align:center;">' . $record['location'] . '</td>
                                <td style="text-align:center;">' . $remarksSupply . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>
                <br>
                <table id="customers">
                    <tr>
                        <th style="text-align:center;">Production</th>
                        <th style="text-align:center;">Warehouse</th>
                    </tr>
                    <tr>
                        <td style="height:60px;"></td>
                        <td style="height:60px;"></td>
                    </tr>
                    <tr>
                        <td style="height:20px;"></td>
                        <td style="height:20px;"></td>
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
            header("Content-Disposition: attachment; filename=supply_requestions_$format.xls");
        }
        $filter_period = $this->input->get('filter_period');
        $filter_wp   = $this->input->get('filter_wp');
        $filter_request_no = $this->input->get('filter_request_no');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
        $this->db->from('supply_requestions a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        // $this->db->join('uom d', 'b.uom_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->like("a.period", $filter_period);
        $this->db->like("a.wp", $filter_wp);
        if ($filter_request_no != "") {
            $this->db->where('a.request_no', $filter_request_no);
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
                                <small>MATERIAL REQUESTION</small>
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
