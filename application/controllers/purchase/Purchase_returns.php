<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class purchase_returns extends CI_Controller
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
            $this->load->view('purchase/purchase_returns');
        } else {
            redirect('error_access');
        }
    }

    public function readItems()
    {
        if ($this->input->get()) {
            $post = isset($_POST['q']) ? $_POST['q'] : "";
            $po_no = $this->input->get('po_no');

            $this->db->select('a.supplier_id, a.item_id, b.number, b.name, b.description, c.mpq, a.qty');
            $this->db->from('purchase_orders a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('supplier_items c', 'a.supplier_id = c.supplier_id and a.item_id = c.item_id');
            $this->db->where('a.po_no', $po_no);
            $this->db->like('b.number', $post);
            $this->db->order_by('b.number', 'asc');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    public function readReturnNo()
    {
        $records = $this->crud->query("SELECT a.return_no, a.return_date, a.return_name, a.po_no, a.supplier_id, b.name as supplier_name 
            FROM purchase_returns a
            JOIN suppliers b ON a.supplier_id = b.id
            WHERE a.status = 0 GROUP BY a.return_no ORDER BY a.created_date desc");
        echo json_encode($records);
    }

    public function return_no()
    {
        $datenow    = date("ymd");
        $sqlGetID   = $this->db->query("SELECT max(return_no) as kode FROM purchase_returns WHERE return_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "PRTN-" . $datenow . "-" . $autoID;
    }

    public function datatables()
    {
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_return_no = $this->input->get('filter_return_no');

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
            $this->db->select('return_no, return_date, return_name, sum(a.qty) as qty, a.status');
            $this->db->from('purchase_returns a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('suppliers c', 'a.supplier_id = c.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.return_date >=', $filter_from);
                $this->db->where('a.return_date <=', $filter_to);
            }
            $this->db->like('a.return_no', $filter_return_no);
            $this->db->group_by('return_no');
            $this->db->order_by('a.return_no', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['return_no'],
                    "return_no" => $record['return_no'],
                    "return_date" => $record['return_date'],
                    "return_name" => $record['return_name'],
                    "qty" => $record['qty'],
                    "status" => $record['status'],
                    "state" => "closed",
                );
            }
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => @$arr]);
            echo json_encode($result);
        } else {
            $this->db->select('a.*, 
                b.number as item_number, 
                b.name as item_name, 
                b.description,
                d.name as supplier_name,
                e.name as uom,
                c.mpq');
            $this->db->from('purchase_returns a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('supplier_items c', 'c.item_id = b.id and a.supplier_id = c.supplier_id');
            $this->db->join('suppliers d', 'a.supplier_id = d.id');
            $this->db->join('uom e', 'b.uom_id = e.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.return_date >=', $filter_from);
                $this->db->where('a.return_date <=', $filter_to);
            }
            $this->db->where('a.return_no', $id);
            $this->db->order_by('d.name', 'ASC');
            $records = $this->db->get()->result_array();

            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['id'],
                    "return_no" => $record['return_no'],
                    "return_date" => $record['return_date'],
                    "return_name" => $record['return_name'],
                    "item_number" => $record['item_number'],
                    "item_name" => $record['item_name'],
                    "supplier_name" => $record['supplier_name'],
                    "description" => $record['description'],
                    "remarks" => $record['remarks'],
                    "uom" => $record['uom'],
                    "qty" => $record['qty'],
                    "mpq" => $record['mpq'],
                    "status" => $record['status'],
                    "created_by" => $record['created_by'],
                    "created_date" => $record['created_date'],
                    "updated_by" => $record['updated_by'],
                    "updated_date" => $record['updated_date'],
                );
            }
            echo json_encode($arr);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $purchase_returns = $this->crud->read('purchase_returns', [], ["return_no" => $post['return_no'], "item_id" => $post['item_id']]);

                if (@$purchase_returns->id != "") {
                    echo json_encode(array("title" => "Duplicated", "message" => "Product No " . $post['item_id'] . " Data Duplicated", "theme" => "error"));
                } else {
                    $send   = $this->crud->create('purchase_returns', $post);
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function update()
    {
        if ($this->input->post()) {
            $id   = $this->input->post('id');
            $post = $this->input->post();
            $send = $this->crud->update('purchase_returns', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_returns', $data);
        echo $send;
    }

    public function print_return($return_no)
    {
        $return_no = base64_decode($return_no);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();

        $this->db->select('supplier_id');
        $this->db->from('purchase_returns');
        $this->db->where('return_no', $return_no);
        $this->db->group_by('supplier_id');
        $suppliers = $this->db->get()->result_array();

        //Config Page
        // $rows = 10;
        // $page = ceil(count($purchase_returns) / $rows);
        //Generate QRcode
        $this->createQrcode($return_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $return_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
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
        $page = 1;
        foreach ($suppliers as $supplier) {
            $this->db->select('a.*, 
                b.number as item_id, 
                b.name as item_name, 
                b.description, 
                c.name as uom, 
                d.number as supplier_number, 
                d.name as supplier_name,
                d.type, 
                d.address,
                d.attention, 
                d.telp');
            $this->db->from('purchase_returns a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->join('uom c', 'b.uom_id = c.id');
            $this->db->join('suppliers d', 'a.supplier_id = d.id');
            $this->db->join('supplier_items e', 'a.item_id = e.item_id and a.supplier_id = e.supplier_id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.return_no', $return_no);
            $this->db->where('a.supplier_id', $supplier['supplier_id']);
            $this->db->order_by('b.number', 'asc');
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
                                            <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $return_no . '.png') . '" width="60"/></td>
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
                                    <h3>PURCHASE RETURN</h3>
                                </center>
                                <div style="float:left; width:60%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150">Supplier Code</td>
                                            <td width="10">:</td>
                                            <td><b>' . $records[0]['supplier_number'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Supplier Name</td>
                                            <td>:</td>
                                            <td><b>' . $records[0]['supplier_name'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align:top;">Ship To</td>
                                            <td style="vertical-align:top;">:</td>
                                            <td><b>' . $records[0]['address'] . '</b></td>
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
                                            <td width="100">PO No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$records[0]['po_no'] . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Return No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$return_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Trans Type</td>
                                            <td>:</td>
                                            <td><b>RETURN MATERIAL</b></td>
                                        </tr>
                                        <tr>
                                            <td>Return Date</td>
                                            <td>:</td>
                                            <td><b>' . date("d F Y", strtotime($records[0]['return_date'])) . '</b></td>
                                        </tr>
                                        <tr>
                                            <td>Create Date</td>
                                            <td>:</td>
                                            <td><b>' . date("d F Y", strtotime($records[0]['created_date'])) . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th>Product No</th>
                                        <th>Product Name</th>
                                        <th width="60">UoM</th>
                                        <th width="60">Qty</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td style="font-size:10px;">' . $record['item_id'] . '</td>
                                <td><span style="font-size:10px;">' . $record['item_name'] . '</span></td>
                                <td style="text-align:center;"><span style="font-size:10px;">' . $record['uom'] . '</span></td>
                                <td style="text-align:right">' . number_format($record['qty'], 2, ",", ".") . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>';
            $html .= '</div></div>';
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

            if ($page != count($suppliers)) {
                $html .= '<div style="page-break-after:always;"></div>';
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
            header("Content-Disposition: attachment; filename=purchase_returns_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_return_no = $this->input->get('filter_return_no');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_id, b.name as item_name, f.name as uom, c.name as supplier_name');
        $this->db->from('purchase_returns a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('suppliers c', 'a.supplier_id = c.id');
        $this->db->join('uom f', 'b.uom_id = f.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.return_date >=', $filter_from);
            $this->db->where('a.return_date <=', $filter_to);
        }
        $this->db->like('a.return_no', $filter_return_no);
        $this->db->order_by('a.return_no', 'ASC');
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
                                <small>RETURN MATERIAL FROM PRODUCTION TO WAREHOUSE</small>
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
                <th>Return No</th>
                <th>Return Date</th>
                <th>Return Name</th>
                <th>Supplier Name</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Uom</th>
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
                        <td>' . $data['return_no'] . '</td>
                        <td>' . $data['return_date'] . '</td>
                        <td>' . $data['return_name'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['item_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['qty'], 2) . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $status . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
