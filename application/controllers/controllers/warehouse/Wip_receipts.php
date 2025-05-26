<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Wip_receipts extends CI_Controller
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
        $this->form_validation->set_rules('checksheet_number', 'Checksheet No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/wip_receipts');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('wip_receipts', ["name" => $post]);
        echo json_encode($send);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $checksheet_number = explode(",", $this->input->get('checksheet_number'));

        $this->db->select('a.*,b.id as item_id,b.number as item_number, b.name as item_name , c.lot_no , b.qty_box, b.box_sub, COALESCE (CEIL(a.receipt / b.qty_box), 0) as label_box, coalesce(CEIL(a.receipt / b.box_sub), 0) as label');
        $this->db->from('checksheets a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('production_schedules c', 'a.wo_no = c.wo_no','left');
        $this->db->where_in('a.number', $checksheet_number);
        $this->db->like('a.number', $post);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function finalChecksheet()
    {
        $trans_date = $this->input->get('trans_date');
        $shift = $this->input->get('shift');

        if (!$trans_date || !strtotime($trans_date)) {
            echo json_encode([]);
            return;
        }

        $query = "
        SELECT 
            a.`number`, 
            a.`trans_date`, 
            a.`wo_no`, 
            b.`number` AS product_no
        FROM 
            checksheets a
        LEFT JOIN 
            production_schedules c ON a.`wo_no` = c.`wo_no`
        JOIN 
            item_fg b ON c.`item_fg_id` = b.`id`
        WHERE 
            a.`status` = '0'
            AND a.`trans_date` = ?
            AND a.`shift` = ?
        ORDER BY 
            a.`number` DESC
    ";

        $checksheet_numbers = $this->db->query($query, array($trans_date, $shift))->result();

        echo json_encode($checksheet_numbers);
    }

    public function getshift()
    {
        $trans_date = $this->input->get('trans_date');

        if (!$trans_date || !strtotime($trans_date)) {
            echo json_encode([]);
            return;
        }

        $query = "SELECT DISTINCT shift FROM checksheets WHERE packing_date = ? AND deleted = 0";
        $shift = $this->db->query($query, array($trans_date))->result();

        echo json_encode($shift);
    }

    public function readfinalchecksheet()
    {
        $records = $this->crud->query("SELECT checksheet_number 
        FROM wip_receipts 
        WHERE `deleted` = '0' 
        ORDER BY `checksheet_number` desc"); // WHERE `status` = '0'
        echo json_encode($records);
    }

    public function documentNo()
    {
        $records = $this->crud->query("SELECT document_no 
        FROM wip_receipts 
        WHERE `deleted` = '0' 
        ORDER BY document_no desc");
        echo json_encode($records);
    }

    public function readChecksheet($filter = "")
    {
        if ($filter == "") {
            $post = isset($_POST['q']) ? $_POST['q'] : "";
            $send = $this->crud->query("SELECT a.*, c.name as customer_name, d.number as product_no, d.name as product_name, d.qty_box, d.box_sub, coalesce(CEIL(a.receipt / d.qty_box), 0) as `label_box`, coalesce(CEIL(a.receipt / d.box_sub), 0) as `label`
            FROM checksheets a 
            LEFT JOIN production_schedules b ON a.workorder = b.workorder 
            JOIN customers c ON b.customer_id = c.id 
            JOIN item_fg d ON a.item_fg_id = d.id 
            WHERE a.status = '0' and a.number like '%$post%'
            GROUP BY a.number
            order by a.number desc");
            echo json_encode($send);
        } else {
            $post = isset($_POST['q']) ? $_POST['q'] : "";
            $send = $this->crud->reads("wip_receipts", ["checksheet_number" => $post]);
            echo json_encode($send);
        }
    }

    // public function document_no($date = "")
    // {
    //     $dates = date_create(base64_decode($date));
    //     $p_month = $dates->format('m');
    //     $p_year = $dates->format('y');
    //     $datenow = $p_month . $p_year;
    //     $doc_no = "-RFG-INJ-PPC-";

    //     $sqlGetID   = $this->db->query("SELECT max(document_no) as kode FROM wip_receipts WHERE document_no LIKE '%$datenow%'");
    //     $rowID      = $sqlGetID->row();
    //     $kode       = $rowID->kode;

    //     if ($kode == NULL) {
    //         $autoID = sprintf("%04s", 1) . $doc_no . $p_month . $p_year;
    //     } else {
    //         $urutan = (int) substr($kode, 0, 4);
    //         $autoID = sprintf("%04s", $urutan + 1) . $doc_no . $p_month . $p_year;
    //     }

    //     echo $autoID;
    // }

    public function document_no($date = "", $division = "")
    {
        $dates = date_create(base64_decode($date));
        $p_month = $dates->format('m');
        $p_year = $dates->format('y');
        $datenow = $p_month . $p_year;
        
        $division = base64_decode($division);
        $doc_no = "-RFG-" . $division . "-PPC-";

        $sqlGetID = $this->db->query("SELECT max(document_no) as kode FROM wip_receipts WHERE document_no LIKE '%$division%' AND document_no LIKE '%$datenow%'");
        $rowID = $sqlGetID->row();
        $kode = $rowID->kode;

        if ($kode == NULL) {
            $autoID = sprintf("%04s", 1) . $doc_no . $p_month . $p_year;
        } else {
            $urutan = (int) substr($kode, 0, 4);
            $autoID = sprintf("%04s", $urutan + 1) . $doc_no . $p_month . $p_year;
        }

        echo $autoID;
    }

    public function label_no($trans_date)
    {
        $datenow = date("Y-m", strtotime($trans_date));
        $sqlGetID = $this->db->query("SELECT max(`number`) as kode FROM wip_receipts WHERE trans_date like '%$datenow%'");
        $rowID = $sqlGetID->row();
        $kode = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%05s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%05s", $urutan);
        }
        $workOrderNo = "CS" . $datenow . "-" . $autoID;
        return $workOrderNo;
    }

    // public function datatablesTemp()
    // {
    //     $checksheet_number = base64_decode($this->input->get('checksheet_number'));
    //     $checksheet_number_ex = explode(",", $checksheet_number);

    //     $this->db->select('a.*,a.number as checksheet_number, a.qty as checksheet_qty, a.receipt as qty, b.id as item_fg_id,b.number as item_number, b.name as item_name , c.lot_no');
    //     $this->db->from('checksheets a');
    //     $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //     $this->db->join('production_schedules c', 'a.wo_no = c.wo_no');
    //     $this->db->where('a.deleted', 0);
    //     // $this->db->where('a.status', 0);
    //     $this->db->where_in('a.number', $checksheet_number_ex);
    //     $this->db->group_by('a.wo_no');
    //     $this->db->group_by('a.item_fg_id');
    //     $this->db->order_by('a.number', 'asc');
    //     $records = $this->db->get()->result_array();

    //     $id = 1;
    //     foreach ($records as $record) {
    //         $obj[] = array(
    //             "no_id" => $id,
    //             "checksheet_number" => $record['checksheet_number'],
    //             "wo_no" => $record['wo_no'],
    //             "qty" => $record['qty'],
    //             "checksheet_qty" => $record['checksheet_qty'],
    //             "item_fg_id" => $record['item_fg_id'],
    //             "item_number" => $record['item_number'],
    //             "item_name" => $record['item_name'],
    //             "lot_no" => $record['lot_no'],
    //             "packing_qty" => $record['packing_qty'],
    //             "packing" => $record['packing']
    //             // "label" => $record['label']
    //         );

    //         $id++;
    //     }

    //     $arr['rows'] = $obj;
    //     die(json_encode($arr));
    // }

    public function datatablesTemp()
    {
        $prod_date = base64_decode($this->input->get('prod_date'));
        $shift = $this->input->get('shift');
        $division = $this->input->get('division');

        $this->db->select('a.*,a.number as checksheet_number, a.qty as checksheet_qty, a.receipt as qty, b.id as item_fg_id, b.number as item_number, b.name as item_name, c.lot_no');
        $this->db->from('checksheets a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('production_schedules c', 'a.wo_no = c.wo_no','left');
        $this->db->where('a.status', 0);
        $this->db->where('a.packing_date', $prod_date);
        $this->db->where('a.shift', $shift);
        $this->db->where('a.division', $division);
        $this->db->group_by('a.number');
        $this->db->group_by('a.wo_no');
        $this->db->group_by('a.item_fg_id');
        $this->db->order_by('a.number', 'asc');
        $records = $this->db->get()->result_array();

        // Inisialisasi variabel $obj sebagai array kosong
        $obj = array();
        
        $id = 1;
        foreach ($records as $record) {
            $obj[] = array(
                "no_id" => $id,
                "checksheet_number" => $record['checksheet_number'],
                "wo_no" => $record['wo_no'],
                "division" => $record['division'],
                "qty" => $record['qty'],
                "checksheet_qty" => $record['checksheet_qty'],
                "item_fg_id" => $record['item_fg_id'],
                "item_number" => $record['item_number'],
                "item_name" => $record['item_name'],
                "lot_no" => $record['lot_no'],
                "status_subcont" => $record['status_subcont'],
                "subcont_type" => $record['subcont_type'],
            );

            $id++;
            
        }

        // Jika tidak ada data, $obj tetap kosong
        $arr['rows'] = $obj;
        echo json_encode($arr);
    }


    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_checksheet = $this->input->get('filter_checksheet');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');
            $filter_document_no = $this->input->get('filter_document_no');
            $filter_division = $this->input->get('filter_division');
            $filter_shift = $this->input->get('filter_shift');
            $filter_status = $this->input->get('filter_status');
            $filter_status_subcont = $this->input->get('filter_status_subcont');
            $filter_subcont_type = $this->input->get('filter_subcont_type');
            $filter_checksheet_number = $this->input->get('filter_checksheet_number');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            $arr = [];
            //Select Query
            $this->db->select('a.*, d.number as product_no, 
            d.name as product_name, 
            d.uom, 
            g.total_status, 
            f.total_status_open,
            e.total_status_close');
            $this->db->from('wip_receipts a');
            $this->db->join('checksheets b', 'a.checksheet_number = b.number');
            $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id');
            $this->db->join('(SELECT document_no, COUNT(status) as total_status_close FROM wip_receipts WHERE status = 1 GROUP BY document_no) e', 'a.document_no = e.document_no', 'left');
            $this->db->join('(SELECT document_no, COUNT(status) as total_status_open FROM wip_receipts WHERE status = 0 GROUP BY document_no) f', 'a.document_no = f.document_no', 'left');
            $this->db->join('(SELECT document_no, COUNT(status) as total_status FROM wip_receipts GROUP BY document_no) g', 'a.document_no = g.document_no', 'left');
            // $this->db->join('uom e', 'd.uom_id = e.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }

            if ($filter_division != "") {
                $this->db->where('a.division', $filter_division);
            }

            if ($filter_status_subcont != "") {
                $this->db->where('a.status_subcont', $filter_status_subcont);
            }

            if ($filter_subcont_type != "") {
                $this->db->where('a.subcont_type', $filter_subcont_type);
            }

            // $this->db->like('a.checksheet_number', $filter_checksheet);
            $this->db->like('a.document_no', $filter_document_no);
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->like('a.shift', $filter_shift);
            $this->db->like('a.status', $filter_status);
            $this->db->like('a.checksheet_number', $filter_checksheet_number);
            $this->db->group_by('a.document_no');
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->order_by('a.checksheet_number', 'DESC');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
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
                    "document_no" => $record['document_no'],
                    "division" => $record['division'],
                    "trans_date" => $record['trans_date'],
                    "prod_date" => $record['prod_date'],
                    "shift" => $record['shift'],
                    "total_status" => $record['total_status'],
                    "total_status_close" => $record['total_status_close'],
                    "total_status_open" => $record['total_status_open'],
                    "status" => $status,
                    "created_by" => $record['created_by'],
                    "created_date" => $record['created_date'],
                    "updated_by" => $record['updated_by'],
                    "updated_date" => $record['updated_date']
        
                );
            }
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $arr]);
            echo json_encode($result);
        }
    }

    //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $doc_no = base64_decode($this->input->get('document_no'));
            // $filter_item_fg_id = base64_decode($this->input->get('filter_item_fg_id'));

            $this->db->select('a.*, b.wo_no, d.number as product_no, d.name as product_name, d.uom');
            $this->db->from('wip_receipts a');
            $this->db->join('checksheets b', 'a.checksheet_number = b.number','left');
            $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
            $this->db->join('item_fg d', 'a.item_fg_id = d.id','left');
            $this->db->where('a.document_no', $doc_no);
            // $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $read = $this->crud->reads("wip_receipts", [], ["checksheet_number" => $post['checksheet_number']]);

                if (count($read) > 0) {
                    show_error("Duplicate Checksheet ID");
                } else {
                    if ($post['division'] == 'MTS') {
                        $post['status'] = 1;
                    }
    
                    $send_wip_receipt = $this->crud->create('wip_receipts', $post);
                    $this->crud->update('checksheets', ["number" => $post['checksheet_number']], ["status" => 1]);
                    die($send_wip_receipt);
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
        $document_no = $data['document_no'];

        $wip_receipts = $this->crud->reads("wip_receipts", [], ["document_no" => $document_no]);
        foreach ($wip_receipts as $wip_receipt) {
            $checksheet_number = $wip_receipt->checksheet_number;
            $update = $this->crud->update('checksheets', ["number" => $checksheet_number], ["status" => 0]);
        }

        $send = $this->crud->delete('wip_receipts', $data);
        echo $send;
    }

    public function deleteSingle()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('wip_receipts', $data);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=wip_receipts_$format.xls");
        }

        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_checksheet = $this->input->get('filter_checksheet');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');
        $filter_document_no = $this->input->get('filter_document_no');
        $filter_division = $this->input->get('filter_division');
        $filter_shift = $this->input->get('filter_shift');
        $filter_status = $this->input->get('filter_status');
        $filter_checksheet_number = $this->input->get('filter_checksheet_number');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $config_iso = $this->db->get('config_iso')->row();

        $wip_receipts = $this->crud->read('wip_receipts', ["checksheet_number" => $filter_checksheet_number, "document_no" => $filter_document_no,], []);

        $this->db->select('a.*, b.wo_no, d.number as product_no, d.name as product_name, d.uom, c.lot_no');
        $this->db->from('wip_receipts a');
        $this->db->join('checksheets b', 'a.checksheet_number = b.number');
        $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
        $this->db->join('item_fg d', 'a.item_fg_id = d.id');
        // $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        if ($filter_division != "") {
            $this->db->where('a.division', $filter_division);
        }
        $this->db->like('a.checksheet_number', $filter_checksheet);
        $this->db->like('a.document_no', $filter_document_no);
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->like('a.shift', $filter_shift);
        $this->db->like('a.status', $filter_status);
        $this->db->like('a.checksheet_number', $filter_checksheet_number);
        $this->db->order_by('a.trans_date', 'DESC');
        $this->db->order_by('a.checksheet_number', 'DESC');
        $records = $this->db->get()->result_array();

        $qrcodes = $wip_receipts->document_no;
        $this->createQrcode($qrcodes, "assets/image/qrcode/", $wip_receipts->document_no);
        $html = '<html>
            <head>
                <title>Print Data</title>
                <style>
                    body {
                        font-family: Arial, Helvetica, sans-serif;
                    }
                    #customers {
                        border-collapse: collapse;
                        width: 100%;
                        font-size: 12px;
                    }
                    #customers td, #customers th {
                        border: 1px solid #ddd;
                        padding: 2px;
                    }
                    #customers tr:nth-child(even) {
                        background-color: #f2f2f2;
                    }
                    #customers tr:hover {
                        background-color: #ddd;
                    }
                    #customers th {
                        padding-top: 2px;
                        padding-bottom: 2px;
                        text-align: center;
                        color: black;
                    }
                    .header-table {
                        width: 100%;
                        font-size: 12px;
                        margin-bottom: 20px;
                    }
                    .header-table td {
                        vertical-align: top;
                    }
                </style>
            </head>
            <body>
                <center>
                    <table class="header-table">
                        <tr>
                            <td width="10%">
                                <img src="' . $config->favicon . '" width="60">
                            </td>
                            <td width="70%">
                                <b style="font-size: 14px;">' . $config->name . '</b><br>
                                <span style="font-size: 10px;">' . $config->description . '</span><br>
                                <span style="font-size: 10px;">Jl. Jababeka XIV, Blok U No.12B</span><br>
                                <span style="font-size: 10px;">Cikarang Industrial Estate, Bekasi 17530, Indonesia</span>
                            </td>
                            <td width="20%" style="text-align: right;">
                                <table style="width: 100%; font-size: 10px;">
                                    <tr>
                                        <td>Form No</td>
                                        <td>:</td>
                                        <td>' . $config_iso->doc_wip_receipt . '</td>
                                    </tr>
                                    <tr>
                                        <td>Print Date</td>
                                        <td>:</td>
                                        <td>' . date("d M Y H:i:s") . '</td>
                                    </tr>
                                    <tr>
                                        <td>Print By</td>
                                        <td>:</td>
                                        <td>' . $this->session->username . '</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <h2 style="text-align: center; margin-bottom: 0;">RECEIVING FINISHED GOOD</h2>
                    <div style="text-align: left; font-size: 12px; margin-bottom: 10px;">
                        <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                            <tr>
                                <td>
                                    <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                                        <tr>
                                            <td style="width: 150px;">Document No</td>
                                            <td style="width: 10px;">:</td>
                                            <td>' . $wip_receipts->document_no . '</td>
                                        </tr>
                                        <tr>
                                            <td>Production Date</td>
                                            <td>:</td>
                                            <td>' . $wip_receipts->prod_date . '</td>
                                        </tr>
                                        <tr>
                                            <td>Process Name</td>
                                            <td>:</td>
                                            <td>INJECTION</td>
                                        </tr>
                                        <tr>
                                            <td>Shift</td>
                                            <td>:</td>
                                            <td>' . $wip_receipts->shift . '</td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 100px; text-align: center;">
                                    <img src="' . base_url('assets/image/qrcode/' . $wip_receipts->document_no . '.png') . '" width="80"/>
                                </td>
                            </tr>
                        </table>

                    </div>
                </center>
                
                <table id="customers">
                    <tr>
                        <th width="20">No</th>
                        <th>Product No</th>
                        <th>Product Name</th>
                        <th>Lot No</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Remarks</th>
                    </tr>';

        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['product_no'] . '</td>
                            <td>' . $data['product_name'] . '</td>
                            <td>' . $data['lot_no'] . '</td>
                            <td style="text-align:right">' . number_format($data['qty']) . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['status_subcont'] . '</td>
                            <td>' . $data['subcont_type'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table>
            <br><br>
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: center; border: 1px solid #ddd;">
                <tr>
                    <td style="width: 25%; border: 1px solid #ddd; padding: 20px;">
                        <b>Approved By</b>
                    </td>
                    <td style="width: 25%; border: 1px solid #ddd; padding: 20px;">
                        <b>Accepted By</b>
                    </td>
                    <td style="width: 25%; border: 1px solid #ddd; padding: 20px;">
                        <b>Approved By</b>
                    </td>
                    <td style="width: 25%; border: 1px solid #ddd; padding: 20px;">
                        <b>Submitted By</b>
                    </td>
                </tr>
                <tr>
                    <td style="height: 60px; border: 1px solid #ddd;"></td>
                    <td style="height: 60px; border: 1px solid #ddd;"></td>
                    <td style="height: 60px; border: 1px solid #ddd;"></td>
                    <td style="height: 60px; border: 1px solid #ddd;"></td>
                </tr>
            </table>
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left; margin-top: 10px;">
                <tr>
                    <td style="width: 100%; border: 1px solid #ddd; padding: 5px;">
                        <b>Note:</b>
                    </td>
                </tr>
            </table>
        </body></html>';

        $html .= '</table></body></html>';

        echo $html;
    }
}
