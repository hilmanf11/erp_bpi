<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Packing_lists extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/packing_lists');
        } else {
            redirect('error_access');
        }
    }

    public function number($trans_date, $nickname)
    {
        $datenow    = "PL-" . $nickname . date("Ym", strtotime(base64_decode($trans_date)));
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM packing_lists WHERE `number` like '%$datenow%'");
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
        $send = $this->crud->reads('packing_lists', ["name" => $post]);
        echo json_encode($send);
    }

    public function readPackingLists($customer_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $packing_lists = $this->crud->query("SELECT DISTINCT `number` FROM packing_lists WHERE customer_id = '$customer_id' and `status` = '0' and `number` like '%$post%' ORDER BY `number` ASC");
        echo json_encode($packing_lists);
    }

    public function readDeliveryNote()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $delivery_notes = $this->crud->query("SELECT dn_number as `number`, b.nickname
            FROM sales_invoices a
            JOIN customers b ON a.customer_id = b.id
            WHERE a.status = '0' and a.dn_number like '%$post%' 
            GROUP BY a.dn_number
            ORDER BY a.dn_number ASC");
        echo json_encode($delivery_notes);
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
            $this->db->select('a.number, a.trans_date, a.dn_number, e.number as si_number, d.name as customer_name, SUM(a.qty) as qty');
            $this->db->from('packing_lists a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('customers d', 'a.customer_id = d.id');
            $this->db->join('sales_invoices e', 'a.dn_number = e.dn_number', 'left');
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
                    "dn_number" => $record['dn_number'],
                    "si_number" => $record['si_number'],
                    "trans_date" => $record['trans_date'],
                    "customer_name" => $record['customer_name'],
                    // "qty" => $record['qty'],
                    "state" => "closed",
                );
            }
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        } else {
            $this->db->select('a.*, e.number as si_number, b.number as item_number, b.name as item_name, b.description, d.name as customer_name, c.name as uom');
            $this->db->from('packing_lists a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->join('customers d', 'a.customer_id = d.id');
            $this->db->join('sales_invoices e', 'a.dn_number = e.dn_number');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.number', $id);
            $this->db->like('a.customer_id', $filter_customer);
            $this->db->group_by('a.customer_po');
            $this->db->group_by('a.item_fg_id');
            $this->db->order_by('a.qty', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function datatableUpdate()
    {
        $number = base64_decode($this->input->get('number'));
        $this->db->select('a.*, b.number as item_number, b.name as item_name, c.name as uom');
        $this->db->from('packing_lists a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('uom c', 'b.uom_id = c.id');
        $this->db->where('a.number', $number);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $packing_lists = $this->crud->read('packing_lists', [], ["number" => $post['number'], "item_fg_id" => $post['item_fg_id']]);

                if (@$packing_lists->id != "") {
                    $send = $this->crud->update('packing_lists', ["number" => $post['number'], "item_fg_id" => $post['item_fg_id']], $post);
                    echo $send;
                } else {
                    $send = $this->crud->create('packing_lists', $post);
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
        $send = $this->crud->delete('packing_lists', $data);
        echo $send;
    }

    public function print_dn($packing_list)
    {
        $packing_list = base64_decode($packing_list);
        $packing_lists = $this->crud->reads('packing_lists', [], ["number" => $packing_list]);
        $packing_list_no = $this->crud->read('packing_lists', [], ["number" => $packing_list]);
        $delivery_note = $this->crud->read('delivery_notes', [], ["delivery_note_no" => $packing_list_no->dn_number]);

        if ($delivery_note->address == "2") {
            $address_no = "_2";
        } else {
            $address_no = "";
        }

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 15;
        $page = ceil(count($packing_lists) / $rows);
        //Generate QRcode
        $this->createQrcode($packing_list, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $packing_list_no->number . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
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
                f.number as si_number, 
                e.origin, 
                e.sailing, 
                e.ship, 
                e.incoterm, 
                e.trans_type, 
                b.number as item_number, 
                b.name as item_name, 
                b.description, 
                c.name as uom, 
                d.number as customer_number, 
                d.name as customer_name, 
                d.address, 
                d.address_2, 
                d.address_billing, 
                d.address_billing_2, 
                d.attention, 
                d.attention_2, 
                d.telp_billing,
                d.telp_billing_2,
                d.telp,
                d.telp_2');
            $this->db->from('packing_lists a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->join('customers d', 'a.customer_id = d.id');
            $this->db->join('delivery_notes e', 'a.dn_number = e.delivery_note_no');
            $this->db->join('sales_invoices f', 'a.dn_number = f.dn_number', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.number', $packing_list);
            $this->db->group_by('a.customer_po');
            $this->db->group_by('a.item_fg_id');
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(15, ($i * 15));
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
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $packing_list_no->number . '.png') . '" width="60"/></td>
                                            <td width="60">Doc No</td>
                                            <td width="5">:</td>
                                            <td width="100">' . $config_iso->doc_packing_list . '</td>
                                        </tr>
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $config_iso->form_packing_list . '</td>
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
                                    <h3>PACKING LIST</h3>
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
                                            <td><b>' . $records[0]['address' . $address_no] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:top;">Bill To</td>
                                            <td style="vertical-align:top;">:</td>
                                            <td><b>' . $records[0]['address_billing' . $address_no] . '</b></td>
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
                                    </table>
                                </div>
                                <div style="float:left; width:40%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="100">Packing List No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$packing_list_no->number . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Delivery Note No</td>
                                            <td>:</td>
                                            <td><b>' . @$packing_list_no->dn_number . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Sales Invoice No</td>
                                            <td>:</td>
                                            <td><b>' . @$records[0]['si_number'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Trans Type</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['trans_type'] . '</b></td>
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
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th width="200">Product No</th>
                                        <th width="200">Product Name</th>
                                        <th width="60">UoM</th>
                                        <th width="80">Qty</th>
                                        <th width="80">Pallet<br>Qty</th>
                                        <th width="80">Qty<br>Carton</th>
                                        <th width="80">Net Weight<br>(KGS)</th>
                                        <th width="80">Gross Weight<br>(KGS)</th>
                                        <th width="80">Measure<br>(CBM)</th>
                                    </tr>';
            $qty = 0;
            $pallet_no = 0;
            $carton = 0;
            $net_weight = 0;
            $gross_weight = 0;
            $measure = 0;
            foreach ($records as $record) {
                $qty += $record['qty'];
                $pallet_no += $record['pallet_no'];
                $carton += $record['carton'];
                $net_weight += $record['net_weight'];
                $gross_weight += $record['gross_weight'];
                $measure += $record['measure'];

                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td style="font-size:10px;">' . $record['item_number'] . '</td>
                                <td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>
                                <td><span style="font-size:10px;">' . $record['uom'] . '</span></td>
                                <td style="text-align:right">' . number_format($record['qty'], 2, ",", ".") . '</td>
                                <td style="text-align:right">' . number_format($record['pallet_no'], 2, ",", ".") . '</td>
                                <td style="text-align:right">' . number_format($record['carton'], 2, ",", ".") . '</td>
                                <td style="text-align:right">' . number_format($record['net_weight'], 2, ",", ".") . '</td>
                                <td style="text-align:right">' . number_format($record['gross_weight'], 2, ",", ".") . '</td>
                                <td style="text-align:right">' . number_format($record['measure'], 2, ",", ".") . '</td>
                            </tr>';
                $no++;
            }

            $html .= '  <tr>
                            <td colspan="4">TOTAL</td>
                            <td style="text-align:right"><b>' . number_format($qty, 2, ",", ".") . '</b></td>
                            <td style="text-align:right"><b>' . number_format($pallet_no, 2, ",", ".") . '</b></td>
                            <td style="text-align:right"><b>' . number_format($carton, 2, ",", ".") . '</b></td>
                            <td style="text-align:right"><b>' . number_format($net_weight, 2, ",", ".") . '</b></td>
                            <td style="text-align:right"><b>' . number_format($gross_weight, 2, ",", ".") . '</b></td>
                            <td style="text-align:right"><b>' . number_format($measure, 2, ",", ".") . '</b></td>
                        </tr>';

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
            header("Content-Disposition: attachment; filename=packing_lists_$format.xls");
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
        $this->db->from('packing_lists a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
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
                                <small>PACKING LIST</small>
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
                    <th>PL No</th>
                    <th>PL Date</th>
                    <th>Customer</th>
                    <th>DN No</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Qty</th>
                    <th>Net Weight</th>
                    <th>Gross Weight</th>
                    <th>Measure</th>
                    <th>Uom</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['trans_date'] . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['dn_number'] . '</td>
                        <td>' . $data['item_number'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['qty'], 2) . '</td>
                        <td>' . number_format($data['net_weight'], 2) . '</td>
                        <td>' . number_format($data['gross_weight'], 2) . '</td>
                        <td>' . number_format($data['measure'], 2) . '</td>
                        <td>' . $data['uom'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
