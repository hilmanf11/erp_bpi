<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Delivery_notes extends CI_Controller
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
            $this->load->view('shipping/delivery_notes');
        } else {
            redirect('error_access');
        }
    }

    public function number($trans_date)
    {
        $datenow    = "DN" . date("Ymd", strtotime(base64_decode($trans_date)));
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM delivery_notes WHERE `number` like '%$datenow%'");
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

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $number = $this->input->get('number');
        $this->db->select('a.*, b.number as item_number, b.name as item_name, c.name as uom, SUM(a.qty) as qty');
        $this->db->from('delivery_notes a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('uom c', 'b.uom_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.number', $number);
        $this->db->group_by('a.item_id');
        $this->db->order_by('a.number', 'DESC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readDeliveryNote($customer_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $delivery_notes = $this->crud->query("SELECT DISTINCT `number` FROM delivery_notes WHERE customer_id = '$customer_id' and `number` like '%$post%' ORDER BY `number` ASC");
        echo json_encode($delivery_notes);
    }

    public function readDeliveryOrder($customer_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $delivery_orders = $this->crud->query("SELECT a.number, a.trans_type, a.note, SUM(b.qty) as qty
            FROM delivery_orders a
            JOIN shipping_orders b ON a.number = b.do_number
            WHERE a.customer_id = '$customer_id' and a.status = '0' and a.number like '%$post%' 
            GROUP BY a.number
            ORDER BY a.number ASC");
        echo json_encode($delivery_orders);
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
            $this->db->select('a.number, a.trans_date, a.do_number, d.name as customer_name, SUM(a.qty) as qty, a.status');
            $this->db->from('delivery_notes a');
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
            $this->db->group_by('number');
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
                    "customer_name" => $record['customer_name'],
                    "qty" => $record['qty'],
                    "status" => $record['status'],
                    "state" => "closed",
                );
            }
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        } else {
            $this->db->select('a.*, b.number as item_number, b.name as item_name, b.description, d.name as customer_name, c.name as uom');
            $this->db->from('delivery_notes a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->join('customers d', 'a.customer_id = d.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.number', $id);
            $this->db->like('a.customer_id', $filter_customer);
            $this->db->order_by('a.qty', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $delivery_notes = $this->crud->read('delivery_notes', [], ["do_number" => $post['do_number'], "item_id" => $post['item_id'], "customer_po" => $post['customer_po']]);
                if (@$delivery_notes->id != "") {
                    // echo json_encode(array("title" => "Duplicated", "message" => "Data Duplicated", "theme" => "error"));
                    $send = $this->crud->update('delivery_notes', ["do_number" => $post['do_number'], "item_id" => $post['item_id'], "customer_po" => $post['customer_po']], ["qty" => (@$delivery_notes->qty + $post['qty'])]);
                    echo $send;
                } else {
                    $send = $this->crud->create('delivery_notes', $post);
                    $update = $this->crud->update('delivery_orders', ["number" => $post['do_number'], "item_id" => $post['item_id'], "customer_id" => $post['customer_id']], ["status" => "1"]);
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
        $send = $this->crud->delete('delivery_notes', $data);
        $update = $this->crud->update('delivery_orders', ["number" => $data['do_number'], "item_id" => $data['item_id'], "customer_id" => $data['customer_id']], ["status" => "0"]);
        echo $send;
    }

    public function print_dn($delivery_no)
    {
        $delivery_no = base64_decode($delivery_no);
        $delivery_notes = $this->crud->reads('delivery_notes', [], ["number" => $delivery_no]);
        $delivery_note = $this->crud->read('delivery_notes', [], ["number" => $delivery_no]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 10;
        $page = ceil(count($delivery_notes) / $rows);
        //Generate QRcode
        $this->createQrcode($delivery_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $delivery_note->number . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
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
            $this->db->select('a.*,
                e.so_number, 
                b.number as item_id, 
                b.name as item_name, 
                b.description, 
                c.name as uom, 
                f.price, 
                d.number as customer_number, 
                d.name as customer_name,
                d.type, 
                d.address, 
                d.address_billing, 
                d.attention, 
                d.telp_billing,
                d.telp');
            $this->db->from('delivery_notes a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->join('customers d', 'a.customer_id = d.id');
            $this->db->join('delivery_orders e', 'a.do_number = e.number');
            $this->db->join('customer_items f', 'd.id = f.customer_id and b.id = f.item_id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.number', $delivery_no);
            $this->db->group_by('a.customer_po');
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(10, ($i * 10));
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
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $delivery_note->number . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_delivery_note . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_delivery_note . '</td>
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
                                    <h3>DELIVERY NOTE</h3>
                                </center>
                                <div style="float:left; width:60%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150">Customer Code</td>
                                            <td width="10">:</td>
                                            <td><b>' . $records[0]['customer_number'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Customer Name</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['customer_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:top;">Ship To</td>
                                            <td style="vertical-align:top;">:</td>
                                            <td><b>' . $records[0]['address'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:top;">Bill To</td>
                                            <td style="vertical-align:top;">:</td>
                                            <td><b>' . $records[0]['address_billing'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Shipper From</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['origin'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Sailing On Or About to</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['sailing'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Attention</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['attention'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Telp</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['telp'] . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:40%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Delivery Note No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$delivery_note->number . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Trans Type</td>
                                            <td>:</td>
                                            <td><b>' . @$delivery_note->trans_type . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Ship By</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['ship'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Incoterm</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['incoterm'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Delivery Date</td>
                                            <td>:</td>
                                            <td><b>' . date("d F Y", strtotime($delivery_note->trans_date)) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Create Date</td>
                                            <td>:</td>
                                            <td><b>' . date("d F Y", strtotime($delivery_note->created_date)) . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th>Sales Order No</th>
                                        <th>Product No</th>
                                        <th>Product Name</th>
                                        <th width="60">UoM</th>
                                        <th width="60">Qty</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td><span style="font-size:10px;">' . $record['customer_po'] . '</span></td>
                                <td style="font-size:10px;">' . $record['item_id'] . '</td>
                                <td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>
                                <td style="text-align:center;"><span style="font-size:10px;">' . $record['uom'] . '</span></td>
                                <td style="text-align:right">' . number_format($record['qty'], 2, ",", ".") . '</td>
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
                                <table id="customers" style="margin-top:10px; font-size:10px;">
                                    <tr>
                                        <th width="400" style="text-align:left; vertical-align:top;" rowspan="4">Note.</th>
                                    </tr>
                                    <tr>
                                        <th width="200" style="text-align:center;">CUSTOMER STAMP & SIGNATURE</th>
                                        <th width="200" style="text-align:center;">AUTHORISED SIGNATURE</th>
                                        <th width="200" style="text-align:center;">DELIVER CONTROL</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;"></th>
                                        <th style="height:80px;"></th>
                                        <th style="height:80px;"></th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;"></th>
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
            header("Content-Disposition: attachment; filename=delivery_notes_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_dn_no = $this->input->get('filter_dn_no');
        $filter_customer = $this->input->get('filter_customer');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_number, b.name as item_name, b.description, d.name as customer_name, c.name as uom');
        $this->db->from('delivery_notes a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('uom c', 'b.uom_id = c.id');
        $this->db->join('customers d', 'a.customer_id = d.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('a.number', $filter_dn_no);
        $this->db->like('a.customer_id', $filter_customer);
        $this->db->order_by('a.qty', 'ASC');
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
                                <small>DELIVERY NOTES</small>
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
                    <th>DN No</th>
                    <th>DN Date</th>
                    <th>DN Type</th>
                    <th>Customer</th>
                    <th>DO No</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Qty</th>
                    <th>Uom</th>
                    <th>Origin</th>
                    <th>Sailing</th>
                    <th>Ship</th>
                    <th>Incoterm</th>
                    <th>Customs Type</th>
                    <th>Customs No</th>
                    <th>Customs Date</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['trans_date'] . '</td>
                        <td>' . $data['trans_type'] . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['do_number'] . '</td>
                        <td>' . $data['item_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['qty'], 2) . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['origin'] . '</td>
                        <td>' . $data['sailing'] . '</td>
                        <td>' . $data['ship'] . '</td>
                        <td>' . $data['incoterm'] . '</td>
                        <td>' . $data['bc_kind'] . '</td>
                        <td>' . $data['bc_no'] . '</td>
                        <td>' . $data['bc_date'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
