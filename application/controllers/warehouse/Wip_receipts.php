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
        $this->db->join('production_schedules c', 'a.wo_no = c.wo_no');
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
        JOIN 
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

        $query = "SELECT DISTINCT shift FROM checksheets WHERE trans_date = ? AND deleted = 0";
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
        WHERE `status` = '0' 
        ORDER BY document_no desc");
        echo json_encode($records);
    }

    public function readChecksheet($filter = "")
    {
        if ($filter == "") {
            $post = isset($_POST['q']) ? $_POST['q'] : "";
            $send = $this->crud->query("SELECT a.*, c.name as customer_name, d.number as product_no, d.name as product_name, d.qty_box, d.box_sub, coalesce(CEIL(a.receipt / d.qty_box), 0) as `label_box`, coalesce(CEIL(a.receipt / d.box_sub), 0) as `label`
            FROM checksheets a 
            JOIN production_schedules b ON a.workorder = b.workorder 
            JOIN customers c ON b.customer_id = c.id 
            JOIN item_fg d ON b.item_fg_id = d.id 
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

    public function document_no($date = "")
    {
        $dates = date_create(base64_decode($date));
        $p_month = $dates->format('m');
        $p_year = $dates->format('y');
        $datenow = $p_month . $p_year;
        $doc_no = "-RFG-INJ-PPC-";

        $sqlGetID   = $this->db->query("SELECT max(document_no) as kode FROM wip_receipts WHERE document_no LIKE '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;

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

    public function datatablesTemp()
    {
        $checksheet_number = base64_decode($this->input->get('checksheet_number'));
        $checksheet_number_ex = explode(",", $checksheet_number);

        $this->db->select('a.*,a.number as checksheet_number, a.qty as checksheet_qty, a.receipt as qty, b.id as item_fg_id,b.number as item_number, b.name as item_name , c.lot_no');
        $this->db->from('checksheets a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->join('production_schedules c', 'a.wo_no = c.wo_no');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->where_in('a.number', $checksheet_number_ex);
        $this->db->group_by('a.wo_no');
        $this->db->group_by('a.item_fg_id');
        $this->db->order_by('a.number', 'asc');
        $records = $this->db->get()->result_array();

        $id = 1;
        foreach ($records as $record) {
            $obj[] = array(
                "no_id" => $id,
                "checksheet_number" => $record['checksheet_number'],
                "wo_no" => $record['wo_no'],
                "qty" => $record['qty'],
                "checksheet_qty" => $record['checksheet_qty'],
                "item_fg_id" => $record['item_fg_id'],
                "item_number" => $record['item_number'],
                "item_name" => $record['item_name'],
                "lot_no" => $record['lot_no'],
                "packing_qty" => $record['packing_qty'],
                "packing" => $record['packing']
                // "label" => $record['label']
            );

            $id++;
        }

        $arr['rows'] = $obj;
        die(json_encode($arr));
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_checksheet = $this->input->get('filter_checksheet');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');
            $filter_document_no = $this->input->get('filter_document_no');
            $filter_shift = $this->input->get('filter_shift');
            $filter_checksheet_number = $this->input->get('filter_checksheet_number');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, d.number as product_no, d.name as product_name, d.uom');
            $this->db->from('wip_receipts a');
            $this->db->join('checksheets b', 'a.checksheet_number = b.number');
            $this->db->join('production_schedules c', 'b.wo_no = c.wo_no');
            $this->db->join('item_fg d', 'c.item_fg_id = d.id');
            // $this->db->join('uom e', 'd.uom_id = e.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            // $this->db->like('a.checksheet_number', $filter_checksheet);
            $this->db->like('a.document_no', $filter_document_no);
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->like('a.shift', $filter_shift);
            $this->db->like('a.checksheet_number', $filter_checksheet_number);
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->group_by('a.document_no');
            $this->db->order_by('a.checksheet_number', 'DESC');
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
            $doc_no = base64_decode($this->input->get('document_no'));
            $filter_item_fg_id = base64_decode($this->input->get('filter_item_fg_id'));

            $this->db->select('a.*, b.wo_no, d.number as product_no, d.name as product_name, d.uom');
            $this->db->from('wip_receipts a');
            $this->db->join('checksheets b', 'a.checksheet_number = b.number');
            $this->db->join('production_schedules c', 'b.wo_no = c.wo_no');
            $this->db->join('item_fg d', 'c.item_fg_id = d.id');
            $this->db->where('a.document_no', $doc_no);
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
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

    public function create_label()
    {
        $post = $this->input->post();
        @$checksheet_number = $post['checksheet_number'];
        @$qty = $post['qty'];

        //Read Label ID
        $sqlGetID = $this->db->query("SELECT max(checksheet_label) as kode FROM wip_receipt_labels WHERE checksheet_number = '$checksheet_number'");
        $rowID = $sqlGetID->row();
        $label = $rowID->kode;
        if ($label == NULL) {
            $autoID = $checksheet_number .  sprintf("%03s", $label + 1);
        } else {
            $urutan = (int) substr($label, -3);
            $autoID = $checksheet_number . sprintf("%03s", $urutan + 1);
        }

        //Simpan Label
        $arrLabel = [
            "checksheet_number" => $checksheet_number,
            "checksheet_label" => $autoID,
            "qty" => $qty
        ];

        $send = $this->crud->create('wip_receipt_labels', $arrLabel);
        die($send);
    }

    public function create_label_box()
    {
        $post = $this->input->post();
        $checksheet_number = $post['checksheet_number'];
        $qty = $post['qty'];

        //Read Label ID
        $sqlGetID = $this->db->query("SELECT max(checksheet_label) as kode FROM wip_receipt_boxs WHERE checksheet_number = '$checksheet_number'");
        $rowID = $sqlGetID->row();
        $label = $rowID->kode;
        if ($label == NULL) {
            $autoID = "B" . $checksheet_number .  sprintf("%03s", $label + 1);
        } else {
            $urutan = (int) substr($label, -3);
            $autoID = "B" . $checksheet_number . sprintf("%03s", $urutan + 1);
        }

        //Simpan Label
        $arrLabel = [
            "checksheet_number" => $checksheet_number,
            "checksheet_label" => $autoID,
            "qty" => $qty
        ];

        $send = $this->crud->create('wip_receipt_boxs', $arrLabel);
        die($send);
    }

    public function delete()
    {
        $data = $this->input->post();
        $document_no = $data['document_no'];

        $wip_receipts = $this->crud->reads("wip_receipts", [], ["document_no" => $document_no]);
        foreach ($wip_receipts as $wip_receipt) {
            $checksheet_number = $wip_receipt->checksheet_number;
            $send = $this->crud->delete('wip_receipt_boxs', ["checksheet_number" => $checksheet_number]);
            $send = $this->crud->delete('wip_receipt_labels', ["checksheet_number" => $checksheet_number]);
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

    public function print_label($checksheet_number)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $config_iso = $this->db->get('config_iso')->row();

        $checksheet_number = base64_decode($checksheet_number);
        //Cek Label
        $this->db->select('d.number_customer as item_number_customer, d.number as item_number, d.name as item_name, d.alias, a.qty, a.checksheet_label, f.trans_date, f.prod_date, f.shift, d.control_id, d.logo, d.uom, c.lot_no, b.qc_1, b.qc_2, b.op_1, b.op_2, b.qcnumber_1, b.qcnumber_2, b.opnumber_1, b.opnumber_2, h.location'); // d.description,
        $this->db->from('wip_receipt_labels a');
        $this->db->join('checksheets b', 'a.checksheet_number = b.number');
        $this->db->join('production_schedules c', 'b.wo_no = c.wo_no');
        $this->db->join('item_fg d', 'c.item_fg_id = d.id');
        // $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->join('wip_receipts f', 'a.checksheet_number = f.checksheet_number');
        $this->db->join('warehouse_location_items g', 'd.id = g.item_fg_id', 'left');
        $this->db->join('warehouse_locations h', 'g.location = h.location', 'left');
        // $this->db->join('warehouse_location_items g', 'd.id = g.item_rm_id', 'left');
        // $this->db->join('customer_items h', 'h.customer_id = c.customer_id and d.id = h.item_fg_id', 'left');
        // $this->db->join('customers i', 'i.id = h.customer_id', 'left');
        // $this->db->join('sales_orders j', 'c.so_number = j.sales_order_no', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.checksheet_number', $checksheet_number);
        $wip_receipt_labels = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $checksheet_number . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($wip_receipt_labels) {
            //$html .= '<div style="width: 120mm;">';
            $no = 1;
            foreach ($wip_receipt_labels as $wip_receipt_label) {
                // if ($no == 3) {
                //     $no = 1;
                // }
                // if ($no == 1) {
                //     $padding = "padding:0 3mm 1mm 0mm;";
                // } else {
                //     $padding = "padding:0 0mm 1mm 4mm;";
                // }

                if ($wip_receipt_label->logo == "0") {
                    $img_bpi = '<img style="width:50%;" src="' . base_url("assets/image/bpi_logo.png") . '" />';
                } else {
                    $img_bpi = '';
                }

                $qc1 = substr($wip_receipt_label->qc_1, 0, 3);
                $qcnumber1 = substr($wip_receipt_label->qcnumber_1, -3);
                $qc2 = substr($wip_receipt_label->qc_2, 0, 3);
                $qcnumber2 = substr($wip_receipt_label->qcnumber_2, -3);
                $op1 = substr($wip_receipt_label->op_1, 0, 3);
                $opnumber1 = substr($wip_receipt_label->opnumber_1, -3);
                $op2 = substr($wip_receipt_label->op_2, 0, 3);
                $opnumber2 = substr($wip_receipt_label->opnumber_2, -3);
                //Generate QRcode
                $qrcodes = $wip_receipt_label->trans_date . "|" . $wip_receipt_label->qty . "|" . $wip_receipt_label->checksheet_label;
                $this->createQrcode($qrcodes, "assets/image/qrcode/", $wip_receipt_label->checksheet_label);
                $html .= '  <div style="width: 70mm; max-height:90mm; border:1px solid black; margin-bottom:5px;">
                                <table id="customers" border="1" style="width: 100%; font-family: Arial, sans-serif; font-size: 10px; border-collapse: collapse;">
                                    <tr>
                                        <th colspan="4" style="font-size: 6px; text-align: right; border: none;"><b>' . $config_iso->doc_wip_receipt . '</b></th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" style="font-size: 12px; text-align: center; border: none;"><b>LABEL PACKAGE</b></th>
                                    </tr>
                                    <tr>
                                        <td style="width:5mm; height: 5mm; border: none; text-align: center;">' . $img_bpi . '</td>
                                        <td colspan="3" style="text-align:left; border: none;"><small style="font-size:10px;"><b>PT BANSHU PLASTIC INDONESIA</b></small></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Part Name</small><br><b style="font-size:12px;">' . $wip_receipt_label->item_number . '</b>
                                        </td>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Lot No.</small><br><b style="font-size:12px;">' . $wip_receipt_label->lot_no . '</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Part Name</small><br><br><b style="font-size:12px;">' . $wip_receipt_label->item_name . '</b>
                                        </td>

                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Prod Date.</small><br><b style="font-size:12px;">' . $wip_receipt_label->prod_date . '</b>
                                            <br>
                                            <small style="font-size:10px;">Pack Date.</small><br><b style="font-size:12px;">' . $wip_receipt_label->trans_date . '</b>
                                        </td>
                                    </tr>
                                     <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Cust Code</small><br><b style="font-size:12px;">' . $wip_receipt_label->item_number_customer . '</b>
                                        </td>
                                        <td style="text-align:left; border: none;">
                                            <small style="font-size:10px;">Shift.</small><br>
                                            <div style="text-align:center;">
                                                <b style="font-size:12px;">' . $wip_receipt_label->shift . '</b>
                                            </div>
                                        </td>
                                         <td style="text-align:left; border: none;">
                                            <img src="' . base_url('assets/image/qc_passed.png') . '" width="30" style="float: center; margin-right: 5px; margin-top: 5px;">
                                        </td>
                                    </tr>
                                   <tr>
                                        <td style="width:15mm; height: 5mm; style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Qty</small><br><b style="font-size:12px;">' . number_format($wip_receipt_label->qty, 2) . '</b>
                                        </td>

                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Unit</small><br><b style="font-size:12px;">' . $wip_receipt_label->uom . '</b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Operator</small>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">QC</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Equivalent</small><br><b style="font-size:12px;"></b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <b style="font-size:8px;">' . $qc1 . '</b>&nbsp<b style="font-size:8px;">' . $qcnumber1 . '</b>
                                            <br>
                                            <b style="font-size:8px;">' . $qc2 . '</b>&nbsp<b style="font-size:8px;">' . $qcnumber2 . '</b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <b style="font-size:8px;">' . $op1 . '</b>&nbsp<b style="font-size:8px;">' . $opnumber1 . '</b>
                                            <br>
                                            <b style="font-size:8px;">' . $op2 . '</b>&nbsp<b style="font-size:8px;">' . $opnumber2 . '</b>
                                        </td>
                                    </tr>
                                   <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;"><small style="font-size:14px;"><b>' . $wip_receipt_label->checksheet_label . '</b></small>
                                        <br><br><small style="font-size:10px;"><b>' . $wip_receipt_label->location . '</b></small></td>
                                        <td colspan="2" style="text-align:center; border: 1px solid black;">
                                            <img src="' . base_url('assets/image/qrcode/' . $wip_receipt_label->checksheet_label . '.png') . '" width="50"/>
                                        </td>
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

    public function print_label_box($checksheet_number)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $config_iso = $this->db->get('config_iso')->row();

        $checksheet_number = base64_decode($checksheet_number);
        //Cek Label
        $this->db->select('d.number_customer as item_number_customer, d.number as item_number, d.name as item_name, d.alias, a.qty, a.checksheet_label, f.trans_date, f.prod_date, f.shift, d.control_id, d.logo, d.uom, c.lot_no, b.qc_1, b.qc_2, b.op_1, b.op_2, b.qcnumber_1, b.qcnumber_2, b.opnumber_1, b.opnumber_2, h.location'); // d.description,
        $this->db->from('wip_receipt_boxs a');
        $this->db->join('checksheets b', 'a.checksheet_number = b.number');
        $this->db->join('production_schedules c', 'b.wo_no = c.wo_no');
        $this->db->join('item_fg d', 'c.item_fg_id = d.id');
        // $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->join('wip_receipts f', 'a.checksheet_number = f.checksheet_number');
        $this->db->join('warehouse_location_items g', 'd.id = g.item_fg_id', 'left');
        $this->db->join('warehouse_locations h', 'g.location = h.location', 'left');
        // $this->db->join('customer_items h', 'h.customer_id = c.customer_id and d.id = h.item_fg_id', 'left');
        // $this->db->join('customers i', 'i.id = h.customer_id', 'left');
        // $this->db->join('sales_orders j', 'c.so_number = j.sales_order_no', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.checksheet_number', $checksheet_number);
        $wip_receipt_labels = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $checksheet_number . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($wip_receipt_labels) {
            //$html .= '<div style="width: 120mm;">';
            $no = 1;
            foreach ($wip_receipt_labels as $wip_receipt_label) {
                // if ($no == 3) {
                //     $no = 1;
                // }
                // if ($no == 1) {
                //     $padding = "padding:0 3mm 1mm 0mm;";
                // } else {
                //     $padding = "padding:0 0mm 1mm 4mm;";
                // }
                //Generate QRcode

                if ($wip_receipt_label->logo == "0") {
                    $img_bpi = '<img style="width:50%;" src="' . base_url("assets/image/bpi_logo.png") . '" />';
                } else {
                    $img_bpi = '';
                }

                $qc1 = substr($wip_receipt_label->qc_1, 0, 3);
                $qcnumber1 = substr($wip_receipt_label->qcnumber_1, -3);
                $qc2 = substr($wip_receipt_label->qc_2, 0, 3);
                $qcnumber2 = substr($wip_receipt_label->qcnumber_2, -3);
                $op1 = substr($wip_receipt_label->op_1, 0, 3);
                $opnumber1 = substr($wip_receipt_label->opnumber_1, -3);
                $op2 = substr($wip_receipt_label->op_2, 0, 3);
                $opnumber2 = substr($wip_receipt_label->opnumber_2, -3);
                $qrcodes = $wip_receipt_label->trans_date . "|" . $wip_receipt_label->qty . "|" . $wip_receipt_label->checksheet_label;
                $this->createQrcode($qrcodes, "assets/image/qrcode/", $wip_receipt_label->checksheet_label);
                $html .= '  <div style="width: 70mm; max-height:90mm; border:1px solid black; margin-bottom:5px;">
                                <table id="customers" border="1" style="width: 100%; font-family: Arial, sans-serif; font-size: 10px; border-collapse: collapse;">
                                    <tr>
                                        <th colspan="4" style="font-size: 6px; text-align: right; border: none;"><b>' . $config_iso->doc_wip_receipt . '</b></th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" style="font-size: 12px; text-align: center; border: none;"><b>LABEL BOX</b></th>
                                    </tr>
                                    <tr>
                                        <td style="width:5mm; height: 5mm; border: none; text-align: center;">' . $img_bpi . '</td>
                                        <td colspan="3" style="text-align:left; border: none;"><small style="font-size:10px;"><b>PT BANSHU PLASTIC INDONESIA</b></small></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Part Name</small><br><b style="font-size:12px;">' . $wip_receipt_label->item_number . '</b>
                                        </td>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Lot No.</small><br><b style="font-size:12px;">' . $wip_receipt_label->lot_no . '</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Part Name</small><br><br><b style="font-size:12px;">' . $wip_receipt_label->item_name . '</b>
                                        </td>

                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Prod Date.</small><br><b style="font-size:12px;">' . $wip_receipt_label->prod_date . '</b>
                                            <br>
                                            <small style="font-size:10px;">Pack Date.</small><br><b style="font-size:12px;">' . $wip_receipt_label->trans_date . '</b>
                                        </td>
                                    </tr>
                                     <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Cust Code</small><br><b style="font-size:12px;">' . $wip_receipt_label->item_number_customer . '</b>
                                        </td>
                                        <td style="text-align:left; border: none;">
                                            <small style="font-size:10px;">Shift.</small><br>
                                            <div style="text-align:center;">
                                                <b style="font-size:12px;">' . $wip_receipt_label->shift . '</b>
                                            </div>
                                        </td>
                                         <td style="text-align:left; border: none;">
                                            <img src="' . base_url('assets/image/qc_passed.png') . '" width="30" style="float: center; margin-right: 5px; margin-top: 5px;">
                                        </td>
                                    </tr>
                                   <tr>
                                        <td style="width:15mm; height: 5mm; style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Qty</small><br><b style="font-size:12px;">' . number_format($wip_receipt_label->qty, 2) . '</b>
                                        </td>

                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Unit</small><br><b style="font-size:12px;">' . $wip_receipt_label->uom . '</b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Operator</small>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">QC</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Equivalent</small><br><b style="font-size:12px;"></b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <b style="font-size:8px;">' . $qc1 . '</b>&nbsp<b style="font-size:8px;">' . $qcnumber1 . '</b>
                                            <br>
                                            <b style="font-size:8px;">' . $qc2 . '</b>&nbsp<b style="font-size:8px;">' . $qcnumber2 . '</b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <b style="font-size:8px;">' . $op1 . '</b>&nbsp<b style="font-size:8px;">' . $opnumber1 . '</b>
                                            <br>
                                            <b style="font-size:8px;">' . $op2 . '</b>&nbsp<b style="font-size:8px;">' . $opnumber2 . '</b>
                                        </td>
                                    </tr>
                                   <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;"><small style="font-size:14px;"><b>' . $wip_receipt_label->checksheet_label . '</b></small>
                                        <br><br><small style="font-size:10px;"><b>' . $wip_receipt_label->location . '</b></small></td>
                                        <td colspan="2" style="text-align:center; border: 1px solid black;">
                                            <img src="' . base_url('assets/image/qrcode/' . $wip_receipt_label->checksheet_label . '.png') . '" width="50"/>
                                        </td>
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
        $filter_shift = $this->input->get('filter_shift');
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
        $this->db->join('production_schedules c', 'b.wo_no = c.wo_no');
        $this->db->join('item_fg d', 'c.item_fg_id = d.id');
        // $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('a.checksheet_number', $filter_checksheet);
        $this->db->like('a.document_no', $filter_document_no);
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->like('a.shift', $filter_shift);
        $this->db->like('a.checksheet_number', $filter_checksheet_number);
        $this->db->order_by('a.trans_date', 'DESC');
        $this->db->order_by('a.checksheet_number', 'DESC');
        $records = $this->db->get()->result_array();

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
                    <br>
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
                            <td>' . $data['remarks'] . '</td>
                        </tr>';
            $no++;
        }

        $html .= '</table></body></html>';

        echo $html;
    }
}
