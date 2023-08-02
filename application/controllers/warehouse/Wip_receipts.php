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

    public function readChecksheet($filter = "")
    {
        if ($filter == "") {
            $post = isset($_POST['q']) ? $_POST['q'] : "";
            $send = $this->crud->query("SELECT a.*, c.name as customer_name, d.number as product_no, d.name as product_name, d.box, d.box_sub, coalesce(CEIL(a.receipt / d.box), 0) as `label_box`, coalesce(CEIL(a.receipt / d.box_sub), 0) as `label`
            FROM checksheets a 
            JOIN production_schedules b ON a.workorder = b.workorder 
            JOIN customers c ON b.customer_id = c.id 
            JOIN items d ON b.item_id = d.id 
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

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_checksheet = $this->input->get('filter_checksheet');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.workorder, d.number as product_no, d.name as product_name, e.name as uom');
            $this->db->from('wip_receipts a');
            $this->db->join('checksheets b', 'a.checksheet_number = b.number');
            $this->db->join('production_schedules c', 'b.workorder = c.workorder and b.wp = c.wp');
            $this->db->join('items d', 'c.item_id = d.id');
            $this->db->join('uom e', 'd.uom_id = e.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.checksheet_number', $filter_checksheet);
            $this->db->order_by('a.trans_date', 'DESC');
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

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $read = $this->crud->reads("wip_receipts", [], ["checksheet_number" => $post['checksheet_number']]);

                if (count($read) > 0) {
                    show_error("Duplicate Checksheet ID");
                } else {
                    $checksheet_number = $post['checksheet_number'];
                    $send = $this->crud->create('wip_receipts', $post);

                    if ($send) {
                        $qty_receipt_box = $post['qty'];
                        for ($i = 0; $i < $post['label_box']; $i++) {
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

                            if ($qty_receipt_box > $post['lot_box']) {
                                $qty = $post['lot_box'];
                            } else {
                                $qty = $qty_receipt_box;
                            }

                            //Simpan Label
                            $arrLabel = [
                                "checksheet_number" => $checksheet_number,
                                "checksheet_label" => $autoID,
                                "qty" => $qty
                            ];

                            $send = $this->crud->create('wip_receipt_boxs', $arrLabel);
                            $qty_receipt_box = ($qty_receipt_box - $post['lot_box']);
                        }

                        $qty_receipt = $post['qty'];
                        for ($i = 0; $i < $post['label']; $i++) {
                            //Read Label ID
                            $sqlGetID = $this->db->query("SELECT max(checksheet_label) as kode FROM wip_receipt_labels WHERE checksheet_number = '$checksheet_number'");
                            $rowID = $sqlGetID->row();
                            $label = $rowID->kode;
                            if ($label == NULL) {
                                $autoID = $checksheet_number .  sprintf("%04s", $label + 1);
                            } else {
                                $urutan = (int) substr($label, -4);
                                $autoID = $checksheet_number . sprintf("%04s", $urutan + 1);
                            }

                            if ($qty_receipt > $post['lot_label']) {
                                $qty = $post['lot_label'];
                            } else {
                                $qty = $qty_receipt;
                            }

                            //Simpan Label
                            $arrLabel = [
                                "checksheet_number" => $checksheet_number,
                                "checksheet_label" => $autoID,
                                "qty" => $qty
                            ];

                            $send = $this->crud->create('wip_receipt_labels', $arrLabel);
                            $qty_receipt = ($qty_receipt - $post['lot_label']);
                        }

                        $this->crud->update("checksheets", ["number" => $checksheet_number], ["status" => "1"]);
                    }

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
        $send = $this->crud->delete('wip_receipts', ["id" => $data['id']]);
        $update = $this->crud->update('checksheets', ["number" => $data['checksheet_number']], ["status" => 0]);
        echo $send;
    }

    public function print_label($checksheet_number)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $checksheet_number = base64_decode($checksheet_number);
        //Cek Label
        $this->db->select('d.number, d.name, d.description, a.qty, a.checksheet_label, f.trans_date, g.location, c.so_number, h.item_cust, i.number as customer_number');
        $this->db->from('wip_receipt_labels a');
        $this->db->join('checksheets b', 'a.checksheet_number = b.number');
        $this->db->join('production_schedules c', 'b.workorder = c.workorder');
        $this->db->join('items d', 'c.item_id = d.id');
        $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->join('wip_receipts f', 'a.checksheet_number = f.checksheet_number');
        $this->db->join('warehouse_location_items g', 'd.id = g.item_id', 'left');
        $this->db->join('customer_items h', 'h.customer_id = c.customer_id and d.id = h.item_id', 'left');
        $this->db->join('customers i', 'i.id = h.customer_id', 'left');
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
                //$qrcodes = "LEOCO".$wip_receipt_label->checksheet_label."-".$wip_receipt_label->trans_date."-".$wip_receipt_label->qty;
                $this->createQrcode($wip_receipt_label->checksheet_label, "assets/image/qrcode/", $wip_receipt_label->checksheet_label);
                $html .= '  <div style="width: 70mm; max-height:90mm; border:1px solid black; margin-bottom:5px;">
                                <table id="customers" border="1">
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust. Code</td>
                                        <th colspan="2" style="font-size:15px; text-align:left;"><b>' . $wip_receipt_label->customer_number . '</b></th>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">P/O No.</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->so_number . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust P/N</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->item_cust . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:14px; text-align:center;">P/N</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->number . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Lot. No</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->checksheet_label . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Quantity</td>
                                        <td style="width:25mm;"><small style="font-size:20px;"><b>' . number_format($wip_receipt_label->qty, 2) . '</b></small></td>
                                        <td style="text-align:left;" style="width:25mm" rowspan="3">
                                            <img src="' . base_url('assets/image/qrcode/' . $wip_receipt_label->checksheet_label . '.png') . '" width="80"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Control ID</td>
                                        <td><b style="font-size:20px;">8100</b></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Date</td>
                                        <td><small style="font-size:10px;"><b>' . $wip_receipt_label->trans_date . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm;"><img style="width:100%;" src="'.base_url("assets/image/Leoco.png").'" /></td>
                                        <td colspan="2" style="text-align:center;"><small style="font-size:14px;"><b>PT CHAINWIN INDONESIA</b></small></td>
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

        $checksheet_number = base64_decode($checksheet_number);
        //Cek Label
        $this->db->select('d.number, d.name, d.description, a.qty, a.checksheet_label, f.trans_date, g.location, c.so_number, h.item_cust, i.number as customer_number');
        $this->db->from('wip_receipt_boxs a');
        $this->db->join('checksheets b', 'a.checksheet_number = b.number');
        $this->db->join('production_schedules c', 'b.workorder = c.workorder');
        $this->db->join('items d', 'c.item_id = d.id');
        $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->join('wip_receipts f', 'a.checksheet_number = f.checksheet_number');
        $this->db->join('warehouse_location_items g', 'd.id = g.item_id', 'left');
        $this->db->join('customer_items h', 'h.customer_id = c.customer_id and d.id = h.item_id', 'left');
        $this->db->join('customers i', 'i.id = h.customer_id', 'left');
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
                
                //$qrcodes = "LEOCO".$wip_receipt_label->checksheet_label."-".$wip_receipt_label->trans_date."-".$wip_receipt_label->qty;
                $this->createQrcode($wip_receipt_label->checksheet_label, "assets/image/qrcode/", $wip_receipt_label->checksheet_label);
                $html .= '  <div style="width: 70mm; max-height:90mm; border:1px solid black; margin-bottom:5px;">
                                <table id="customers" border="1">
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust. Code</td>
                                        <th colspan="2" style="font-size:15px; text-align:left;"><b>' . $wip_receipt_label->customer_number . '</b></th>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">P/O No.</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->so_number . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Cust P/N</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->item_cust . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:14px; text-align:center;">P/N</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->number . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Lot. No</td>
                                        <td colspan="2"><small style="font-size:14px;"><b>' . $wip_receipt_label->checksheet_label . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Quantity</td>
                                        <td style="width:25mm;"><small style="font-size:20px;"><b>' . number_format($wip_receipt_label->qty, 2) . '</b></small></td>
                                        <td style="text-align:center;" style="width:25mm" rowspan="3">
                                            <img src="' . base_url('assets/image/qrcode/' . $wip_receipt_label->checksheet_label . '.png') . '" width="80"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Control ID</td>
                                        <td><small style="font-size:20px;">8100</small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm; font-size:12px; text-align:center;">Date</td>
                                        <td><small style="font-size:10px;"><b>' . $wip_receipt_label->trans_date . '</b></small></td>
                                    </tr>
                                    <tr>
                                        <td style="width:15mm; height: 9mm;"><img style="width:100%;" src="'.base_url("assets/image/Leoco.png").'" /></td>
                                        <td colspan="2" style="text-align:center;"><small style="font-size:14px;"><b>PT CHAINWIN INDONESIA</b></small></td>
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

    public function print_label_strip($checksheet_number)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $checksheet_number = base64_decode($checksheet_number);
        //Cek Label
        $this->db->select('d.number, d.name, d.description, a.qty, a.checksheet_label, f.trans_date, g.location, c.so_number, h.item_cust, i.number as customer_number');
        $this->db->from('wip_receipt_labels a');
        $this->db->join('checksheets b', 'a.checksheet_number = b.number');
        $this->db->join('production_schedules c', 'b.workorder = c.workorder');
        $this->db->join('items d', 'c.item_id = d.id');
        $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->join('wip_receipts f', 'a.checksheet_number = f.checksheet_number');
        $this->db->join('warehouse_location_items g', 'd.id = g.item_id', 'left');
        $this->db->join('customer_items h', 'h.customer_id = c.customer_id and d.id = h.item_id', 'left');
        $this->db->join('customers i', 'i.id = h.customer_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.checksheet_number', $checksheet_number);
        $wip_receipt_labels = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $checksheet_number . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 0px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($wip_receipt_labels) {
            $html .= '  <div style="width: 90mm; max-height:17mm;">';
            $no = 1;
            foreach ($wip_receipt_labels as $wip_receipt_label) {

                if ($no == 3) {
                    $no = 1;
                }
                if ($no == 1) {
                    $margin = "margin:2mm 1mm 2mm 0mm;";
                } else {
                    $margin = "margin:2mm 0mm 2mm 1mm;";
                }
            
                $qrcodes = "LEOCO".$wip_receipt_label->checksheet_label."-".$wip_receipt_label->trans_date."-".$wip_receipt_label->qty;
                $this->createQrcode($qrcodes, "assets/image/qrcode/", $wip_receipt_label->checksheet_label);
                $html .= '  <div style="float:left; width:48%; '.$margin.'">
                                <table id="customers" style="border: 1px solid black;">
                                    <tr>
                                        <td style="width:10mm; text-align:center;" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $wip_receipt_label->checksheet_label . '.png') . '" width="50"/></td>
                                        <th style="font-size:8px; text-align:left;">LEOCO</th>
                                    </tr>
                                    <tr>
                                        <th style="font-size:8px; text-align:left;">' . $wip_receipt_label->name . '</th>
                                    </tr>
                                    <tr>
                                        <th style="font-size:8px; text-align:left;">' . date("d-m-Y") . '</th>
                                    </tr>
                                    <tr>
                                        <th style="font-size:8px; text-align:left;">' . $wip_receipt_label->checksheet_label . '</th>
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

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.workorder, d.number as product_no, d.name as product_name, e.name as uom');
        $this->db->from('wip_receipts a');
        $this->db->join('checksheets b', 'a.checksheet_number = b.number');
        $this->db->join('production_schedules c', 'b.workorder = c.workorder and b.wp = c.wp');
        $this->db->join('items d', 'c.item_id = d.id');
        $this->db->join('uom e', 'd.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('a.checksheet_number', $filter_checksheet);
        $this->db->order_by('a.trans_date', 'DESC');
        $this->db->order_by('a.checksheet_number', 'DESC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>FINAL CHECKSHEET</small>
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
                    <th>Checksheet ID</th>
                    <th>Workorder</th>
                    <th>Trans Date</th>
                    <th>WP</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Lot Qty</th>
                    <th>Lot Box</th>
                    <th>Label Qty</th>
                    <th>Label Box</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['checksheet_number'] . '</td>
                            <td>' . $data['workorder'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['wp'] . '</td>
                            <td>' . $data['product_no'] . '</td>
                            <td>' . $data['product_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . number_format($data['qty']) . '</td>
                            <td>' . number_format($data['lot_label']) . '</td>
                            <td>' . number_format($data['lot_box']) . '</td>
                            <td>' . number_format($data['label']) . '</td>
                            <td>' . number_format($data['label_box']) . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}