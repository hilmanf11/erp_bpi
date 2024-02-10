<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Barcode_divides extends CI_Controller
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
        $this->form_validation->set_rules('label_no', 'Label No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/barcode_divides');
        } else {
            redirect('error_access');
        }
    }

    public function getSerial()
    {
        if ($this->input->post()) {
            $label_no = $this->input->post('label_no');
            $this->db->select('a.*, b.receipt_date, c.number, c.name');
            $this->db->from('purchase_order_labels a');
            $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
            $this->db->join('item_rm c', 'b.item_rm_id = c.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.label_no', $label_no);
            $records = $this->db->get()->result_array();
            if (!$records) {
                $this->db->select('a.*, c.receipt_date, d.number, d.name');
                $this->db->from('barcode_divides a');
                $this->db->join('purchase_order_labels b', 'a.reff = b.receipt_id');
                $this->db->join('purchase_order_receipts c', 'b.receipt_id = c.receipt_id');
                $this->db->join('item_rm d', 'c.item_rm_id = d.id');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.status', 0);
                $this->db->where('a.label_divided', $label_no);
                $this->db->group_by('a.label_divided');
                $records = $this->db->get()->result_array();

                if (!$records) {
                    $this->db->select('a.return_date as receipt_date, b.label_no, b.qty, c.number, c.name');
                    $this->db->from('return_materials a');
                    $this->db->join('return_material_labels b', 'a.return_id = b.return_id');
                    $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                    $this->db->where('a.deleted', 0);
                    $this->db->where('a.status', 0);
                    $this->db->where('b.label_no', $label_no);
                    $this->db->group_by('b.label_no');
                    $records = $this->db->get()->result_array();
                }
            }
            //Mapping Data
            $result['total'] = count($records);
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                if ($post['bal'] >= 0) {
                    $label = $post['label_no'];
                    $label_sub = explode("-", $label);
                    $reff = $label_sub[0] . "-" . $label_sub[1] . '-' . $label_sub[2] . '-' . @$label_sub[3];
                    $barcode_divides = $this->crud->read("barcode_divides", [], ["label_no" => $label]);

                    $checkLabel = $this->crud->read("purchase_order_labels", [], ["label_no" => $reff]);
                    if (!$checkLabel) {
                        $this->db->select('return_id as receipt_id');
                        $this->db->from('return_material_labels');
                        $this->db->where('deleted', 0);
                        $this->db->where('status', 0);
                        $this->db->where('label_no', $reff);
                        $this->db->group_by('label_no');
                        $checkLabel = $this->db->get()->row();
                    }

                    $kode = @$label_sub[4];
                    $autoID = sprintf("%03s", $kode + 1);
                    for ($i = 0; $i < 2; $i++) {
                        $label_divided = $label_sub[0] . '-' . $label_sub[1] . '-' . $label_sub[2] . '-' . @$label_sub[3] . '-' . $autoID;
                        if ($i == 0) {
                            $qty = $post['qty'];
                            $type = "SUPPLY";
                        } else {
                            $qty = $post['bal'];
                            $type = "BALANCE";
                        }
                        $arrLabel = [
                            "reff" => !empty($checkLabel->receipt_id) ? $checkLabel->receipt_id : $reff,
                            "label_no" => $post['label_no'],
                            "label_divided" => $label_divided,
                            "qty" => $qty,
                            "type" => $type,
                        ];
                        if (!$barcode_divides) {
                            $send   = $this->crud->create('barcode_divides', $arrLabel);
                            $message = json_encode(array("title" => "Success", "message" => "Data Saved Successfully ", "theme" => "success"));
                            //Generate QRcode
                            $this->createQrcode($label_divided, "assets/image/qrcode/");
                        } else {
                            $message = json_encode(array("title" => "Available", "message" => "Barcode Divided has been created ", "theme" => "error"));
                        }

                        $autoID = sprintf("%03s", $autoID + 1);
                    }
                } else {
                    $message = json_encode(array("title" => "Not Balance", "message" => "Qty Balance <= 0 ", "theme" => "error"));
                }
                echo $message;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function print()
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $label_no = base64_decode($this->input->get('label_no'));

        $this->db->select('a.*, c.receipt_date, d.number, d.name, e.location, e.area');
        $this->db->from('barcode_divides a');
        $this->db->join('purchase_order_labels b', 'a.reff = b.receipt_id');
        $this->db->join('purchase_order_receipts c', 'b.receipt_id = c.receipt_id');
        $this->db->join('item_rm d', 'c.item_rm_id = d.id');
        $this->db->join('warehouse_location_items e', 'e.item_rm_id = d.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.label_no', $label_no);
        $this->db->group_by('a.label_divided');
        $records = $this->db->get()->result_object();

        if (!$records) {
            $this->db->select('a.*, c.return_date as receipt_date, d.number, d.name, e.location, e.area');
            $this->db->from('barcode_divides a');
            $this->db->join('return_material_labels b', 'a.reff = b.return_id');
            $this->db->join('return_materials c', 'b.return_id = c.return_id');
            $this->db->join('item_rm d', 'c.item_rm_id = d.id');
            $this->db->join('warehouse_location_items e', 'e.item_rm_id = d.id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->where('a.label_no', $label_no);
            $this->db->group_by('a.label_divided');
            $records = $this->db->get()->result_object();
        }

        $html = '<html>
                    <head>
                        <title>' . $label_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        $html .= '<div style="width: 120mm;">';
        $no = 1;
        foreach ($records as $record) {
            if ($no == 3) {
                $no = 1;
            }
            if ($no == 1) {
                $padding = "padding:0 3mm 1mm 0mm;";
            } else {
                $padding = "padding:0 0mm 1mm 4mm;";
            }
            $html .= '  <div style="width: 55mm; max-height:42mm; float:left; ' . $padding . '">
                            <table id="customers" border="1" style="margin-bottom:20px;">
                                <tr>
                                    <th colspan="2" style="font-size:9px; text-align:center;"><b>' . $config->name . '</b></th>
                                </tr>
                                <tr>
                                    <td colspan="2" style="height:35px;">
                                        <div style="float:left;">
                                            <small style="font-size:13px;"><b>' . $record->number . '</b></small>
                                            <br>
                                            <b style="font-size:9px;">' . $record->name . '</b>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="text-align:left">
                                        <small>Quantity</small><br><b style="font-size:24px;">' . number_format($record->qty, 0, ",", ".") . '</b>
                                    </th>
                                    <th style="text-align:left">
                                        <small>Location</small><br><b style="font-size:24px;">' . $record->location . '</b>
                                    </th>
                                </tr>
                                <tr>
                                    <td style="width:65%;">
                                        <small>Date</small><br>
                                        <b>' . $record->receipt_date . '</b><br>
                                        <small>Label No</small><br>
                                        <b style="font-size:7px;">' . $record->label_divided . '</b><br>
                                        <br>
                                        <b style="color:blue;">BARCODE DIVIDES</b>
                                    </td>
                                    <th style="text-align:center;">
                                        <img src="' . base_url('assets/image/qrcode/' . $record->label_divided . '.png') . '" width="50"/>
                                    </th>
                                </tr>
                            </table>
                    </div>';
            $no++;
        }
        $html .= '</div></body>';
        die($html);
    }
}
