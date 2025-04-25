<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Barcode_divides_fg extends CI_Controller
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
            $this->load->view('warehouse/barcode_divides_fg');
        } else {
            redirect('error_access');
        }
    }

    public function getSerial()
    {
        if ($this->input->post()) {
            $label_no = $this->input->post('label_no');

            // $scan_in = $this->crud->read('scan_item_receipts_fg',[],['checksheet_label' => $label_no]);
            // if(empty($scan_in->checksheet_label)){
            //     $result['total'] = 0;
            //     $result['message'] = 'Label No Not yet Scan IN';
            //     die(json_encode($result));
            // }

            $divided = $this->crud->read('barcode_divides_fg',[],['label_divided' => $label_no, 'type' => 'SUPPLY']);
            if(!empty($divided)){
                $result['total'] = 0;
                $result['message'] = 'This Label No for Supply '.$divided->label_divided;
                die(json_encode($result));
            }

            $divided2 = $this->crud->read('barcode_divides_fg',[],['label_no' => $label_no]);
            if(!empty($divided2)){
                $result['total'] = 0;
                $result['message'] = 'This Label Already Divided';
                die(json_encode($result));
            }

            $this->db->select('a.*, b.trans_date, c.number, c.name, "wip_boxs" as type');
            $this->db->from('wip_receipt_boxs a');
            $this->db->join('checksheets b', 'a.checksheet_number = b.number');
            $this->db->join('item_fg c', 'b.item_fg_id = c.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.checksheet_label', $label_no);
            $records = $this->db->get()->result_array();

            if (!$records) {
                $this->db->select('a.*, b.trans_date, c.number, c.name, "wip_label" as type');
                $this->db->from('wip_receipt_labels a');
                $this->db->join('checksheets b', 'a.checksheet_number = b.number');
                $this->db->join('item_fg c', 'b.item_fg_id = c.id');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.checksheet_label', $label_no);
                $records = $this->db->get()->result_array();

                if (!$records) {
                    $this->db->select('a.*, c.number, c.name ,"new_barcode_devided" as type');
                    $this->db->from('barcode_divides_fg a');
                    $this->db->join('new_barcode_fg b', 'a.reff = b.label_no');
                    $this->db->join('item_fg c', 'b.item_fg_id = c.id');
                    $this->db->where('a.deleted', 0);
                    $this->db->where('a.status', 0);
                    $this->db->where('a.label_divided', $label_no);
                    $records = $this->db->get()->result_array();

                    if (!$records) {
                        $this->db->select('a.*, d.number, d.name, "wip_label_divided" as type');
                        $this->db->from('barcode_divides_fg a');
                        $this->db->join('wip_receipt_labels b', 'a.reff = b.checksheet_label','left');
                        $this->db->join('checksheets c', 'b.checksheet_number = c.number','left');
                        $this->db->join('item_fg d', 'c.item_fg_id = d.id','left');
                        $this->db->where('a.deleted', 0);
                        $this->db->where('a.status', 0);
                        $this->db->where('a.label_divided', $label_no);
                        $records = $this->db->get()->result_array();

                        if (!$records) {
                            $this->db->select('a.label_no, a.qty, b.number, b.name, "new_barcode" as type');
                            $this->db->from('new_barcode_fg a');
                            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
                            $this->db->where('a.label_no', $label_no);
                            $this->db->group_by('a.label_no');
                            $records = $this->db->get()->result_array();
                        }
                    }
                }
            }
            //Mapping Data
            $result['total'] = count($records);
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         if ($this->form_validation->run() == TRUE) {
    //             $post   = $this->input->post();
    //             if ($post['bal'] >= 0) {
    //                 $label = $post['label_no'];
    //                 $label_sub = explode("-", $label);
    //                 $reff = $label_sub[0] . "-" . $label_sub[1] . '-' . $label_sub[2] . '-' . @$label_sub[3];

    //                 $barcode_divides = $this->crud->read("barcode_divides_fg", [], ["label_no" => $label]);

    //                 $checkLabel = $this->crud->read("wip_receipt_boxs", [], ["checksheet_label" => $reff]);

    //                 if (!$checkLabel) {
    //                     $checkLabel = $this->crud->read("wip_receipt_labels", [], ["checksheet_label" => $reff]);
    //                 }

    //                 if (!$checkLabel) {
    //                     $checkLabel = $this->crud->read("new_barcode_fg", [], ["label_no" => $reff]);
    //                 }

    //                 $kode = @$label_sub[4];
    //                 $autoID = sprintf("%03s", $kode + 1);
    //                 for ($i = 0; $i < 2; $i++) {
    //                     $label_divided = $label_sub[0] . '-' . $label_sub[1] . '-' . $label_sub[2] . '-' . @$label_sub[3] . '-' . $autoID;
    //                     if ($i == 0) {
    //                         $qty = $post['qty'];
    //                         $type = "SUPPLY";
    //                     } else {
    //                         $qty = $post['bal'];
    //                         $type = "BALANCE";
    //                     }
    //                     $arrLabel = [
    //                         // "reff" => !empty($checkLabel->checksheet_label) ? $checkLabel->checksheet_label : $reff,
    //                         "reff" => !empty($checkLabel->checksheet_label) 
    //                                     ? $checkLabel->checksheet_label 
    //                                     : (!empty($checkLabel->label_no) ? $checkLabel->label_no : $reff),
    //                         "label_no" => $post['label_no'],
    //                         "label_divided" => $label_divided,
    //                         "qty" => $qty,
    //                         "type" => $type,
    //                     ];
    //                     if (!$barcode_divides) {
    //                         $send   = $this->crud->create('barcode_divides_fg', $arrLabel);
    //                         $message = json_encode(array("title" => "Success", "message" => "Data Saved Successfully ", "theme" => "success"));
    //                         //Generate QRcode
    //                         $this->createQrcode($label_divided, "assets/image/qrcode/");

    //                         $this->crud->update('new_barcode_fg', ["label_no" => $post['label_no']], ["status" => 1]);
    //                     } else {
    //                         $message = json_encode(array("title" => "Available", "message" => "Barcode Divided has been created ", "theme" => "error"));
    //                     }

    //                     $autoID = sprintf("%03s", $autoID + 1);
    //                 }
    //             } else {
    //                 $message = json_encode(array("title" => "Not Balance", "message" => "Qty Balance <= 0 ", "theme" => "error"));
    //             }
    //             echo $message;
    //         } else {
    //             show_error(validation_errors());
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                if ($post['bal'] >= 0) {
                    $label = $post['label_no'];
                    $label_sub = explode("-", $label);  // Pisahkan label berdasarkan tanda -

                    // var_dump($label);
    
                    // Menangani undefined offset error dengan pengecekan panjang array
                    $reff = isset($label_sub[0]) ? $label_sub[0] : '';
                    $reff .= isset($label_sub[1]) ? '-' . $label_sub[1] : '';
                    $reff .= isset($label_sub[2]) ? '-' . $label_sub[2] : '';
                    $reff .= isset($label_sub[3]) ? '-' . $label_sub[3] : '';

                    // var_dump($reff);
                  
                    // Membaca data berdasarkan label_no
                    $barcode_divides = $this->crud->read("barcode_divides_fg", [], ["label_no" => $label]);

                    $barcode_divides2 = $this->crud->read("barcode_divides_fg", [], ["label_divided" => $label]);

                    // var_dump($barcode_divides2->reff);
                    // die;

                    // Mencari data dari berbagai tabel dengan format yang sesuai
                    $checkLabel = $this->crud->read("wip_receipt_boxs", [], ["checksheet_label" => $reff]);
    
                    if (!$checkLabel) {
                        $checkLabel = $this->crud->read("wip_receipt_labels", [], ["checksheet_label" => $reff]);
                    }
    
                    if (!$checkLabel) {
                        $checkLabel = $this->crud->read("new_barcode_fg", [], ["label_no" => $reff]);
                    }
    
                    // Menyesuaikan kode untuk label_divided
                    $kode = isset($label_sub[4]) ? $label_sub[4] : '0';// Menangani jika tidak ada elemen ke-4
                    $autoID = sprintf("%03s", $kode + 1);
    
                    for ($i = 0; $i < 2; $i++) {
                        // Membentuk label_divided
                        $label_divided = isset($label_sub[0]) ? $label_sub[0] : '';
                        $label_divided .= isset($label_sub[1]) ? '-' . $label_sub[1] : '';
                        $label_divided .= isset($label_sub[2]) ? '-' . $label_sub[2] : '';
                        $label_divided .= isset($label_sub[3]) ? '-' . $label_sub[3] : '';
                        $label_divided .= '-' . $autoID; // Menambahkan autoID
    
                        // Menentukan qty dan type
                        if ($i == 0) {
                            $qty = $post['qty'];
                            $type = "SUPPLY";
                        } else {
                            $qty = $post['bal'];
                            $type = "BALANCE";
                        }
    
                        // Membuat array untuk label
                        $arrLabel = [
                            "reff" => !empty($checkLabel->checksheet_label) 
                                ? $checkLabel->checksheet_label 
                                : (!empty($checkLabel->label_no) 
                                    ? $checkLabel->label_no 
                                    : (!empty($barcode_divides2->reff) 
                                        ? $barcode_divides2->reff 
                                        : $reff)),
                            "label_no" => $post['label_no'],
                            "label_divided" => $label_divided,
                            "qty" => $qty,
                            "type" => $type,
                        ];
    
                        // Mengecek apakah barcode_divides sudah ada
                        if (!$barcode_divides) {
                            // Jika belum ada, simpan data ke barcode_divides_fg
                            $send = $this->crud->create('barcode_divides_fg', $arrLabel);
                            $message = json_encode(array("title" => "Success", "message" => "Data Saved Successfully", "theme" => "success"));
                            // Generate QR code
                            $this->createQrcode($label_divided, "assets/image/qrcode/");
    
                            // Update status pada new_barcode_fg
                            $this->db->update('new_barcode_fg', ["status" => 1], ["label_no" => $post['label_no']]);
                            $this->db->update('barcode_divides_fg', ["status" => 1], ["label_divided" => $post['label_no']]);
                        } else {
                            // Jika sudah ada, tampilkan pesan error
                            $message = json_encode(array("title" => "Available", "message" => "Barcode Divided has been created", "theme" => "error"));
                        }
    
                        // Increment autoID untuk label berikutnya
                        $autoID = sprintf("%03s", $autoID + 1);
                    }
                } else {
                    $message = json_encode(array("title" => "Not Balance", "message" => "Qty Balance <= 0", "theme" => "error"));
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
        $config_iso = $this->db->get('config_iso')->row();

        $label_no = base64_decode($this->input->get('label_no'));

        $this->db->select('d.number_customer as item_number_customer, d.number as item_number, d.name as item_name, d.alias, a.qty, a.label_divided, 
            b.trans_date, b.prod_date, b.shift, d.control_id, d.logo, d.uom, 
            (CASE 
                WHEN b.lot_no IS NULL or b.lot_no = "" THEN c.lot_no 
                ELSE b.lot_no 
            END) as lot_no, 
            b.qc_1, b.qc_2, b.op_1, b.op_2, b.qcnumber_1, b.qcnumber_2, b.opnumber_1, b.opnumber_2, h.location, "LABEL PACKING" as label_desc, b.packing, a.type'); // d.description,    

            $this->db->from('barcode_divides_fg a');
            $this->db->join('wip_receipt_labels e', 'a.reff = e.checksheet_label','left');
            $this->db->join('checksheets b', 'e.checksheet_number = b.number','left');
            $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
            $this->db->join('item_fg d', 'b.item_fg_id = d.id');
            $this->db->join('warehouse_location_items g', 'd.id = g.item_fg_id', 'left');
            $this->db->join('warehouse_locations h', 'g.location = h.location', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->where('a.label_no', $label_no);
            $this->db->group_by('a.label_divided');
            $records = $this->db->get()->result_object();

        if (!$records) {
            $this->db->select('d.number_customer as item_number_customer, d.number as item_number, d.name as item_name, d.alias, a.qty, a.label_divided, 
            b.trans_date, b.prod_date, b.shift, d.control_id, d.logo, d.uom, 
            (CASE 
                WHEN b.lot_no IS NULL or b.lot_no = "" THEN c.lot_no 
                ELSE b.lot_no 
            END) as lot_no, 
            b.qc_1, b.qc_2, b.op_1, b.op_2, b.qcnumber_1, b.qcnumber_2, b.opnumber_1, b.opnumber_2, h.location,"LABEL BOXS" as label_desc, b.packing, a.type'); // d.description,    

            $this->db->from('barcode_divides_fg a');
            $this->db->join('wip_receipt_boxs e', 'a.reff = e.checksheet_label','left');
            $this->db->join('checksheets b', 'e.checksheet_number = b.number','left');
            $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
            $this->db->join('item_fg d', 'b.item_fg_id = d.id');
            $this->db->join('warehouse_location_items g', 'd.id = g.item_fg_id', 'left');
            $this->db->join('warehouse_locations h', 'g.location = h.location', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            $this->db->where('a.label_no', $label_no);
            $this->db->group_by('a.label_divided');
            $records = $this->db->get()->result_object();

            if (!$records) {
                $this->db->select('b.*,d.number,d.number_customer as item_number_customer, 
                e.location, e.area, d.color, d.uom,b.cut_off_date, d.number as item_number, 
                d.name as item_name, d.alias, d.logo, e.location, a.qty, a.label_divided, 
                b.packing_date as trans_date,a.type ');
                $this->db->from('barcode_divides_fg a');
                $this->db->join('new_barcode_fg b', 'a.reff = b.label_no');
                $this->db->join('item_fg d', 'b.item_fg_id = d.id');
                $this->db->join('warehouse_location_items e', 'e.item_fg_id = d.id', 'left');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.status', 0);
                $this->db->where('a.label_no', $label_no);
                $this->db->group_by('a.label_divided');
                $records = $this->db->get()->result_object();

                // if (!$records) {
                //     $this->db->select('d.number_customer as item_number_customer, d.number as item_number, d.name as item_name, d.alias, a.qty, a.label_divided, 
                //     b.trans_date, b.prod_date, b.shift, d.control_id, d.logo, d.uom, 
                //     (CASE 
                //         WHEN b.lot_no IS NULL or b.lot_no = "" THEN c.lot_no 
                //         ELSE b.lot_no 
                //     END) as lot_no, 
                //     b.qc_1, b.qc_2, b.op_1, b.op_2, b.qcnumber_1, b.qcnumber_2, b.opnumber_1, b.opnumber_2, h.location, "LABEL PACKING" as label_desc, b.packing'); // d.description,    
        
                //     $this->db->from('barcode_divides_fg a');
                //     $this->db->join('wip_receipt_labels e', 'a.reff = e.checksheet_label');
                //     $this->db->join('checksheets b', 'e.checksheet_number = b.number');
                //     $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
                //     $this->db->join('item_fg d', 'b.item_fg_id = d.id');
                //     $this->db->join('warehouse_location_items g', 'd.id = g.item_fg_id', 'left');
                //     $this->db->join('warehouse_locations h', 'g.location = h.location', 'left');
                //     $this->db->where('a.deleted', 0);
                //     $this->db->where('a.status', 0);
                //     $this->db->where('a.label_no', $label_no);
                //     $this->db->group_by('a.label_divided');
                //     $records = $this->db->get()->result_object();
                // }
            }
        }

        $html = '<html>
                <head>
                    <title>' . $label_no . '</title>
                    <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                </head>
                <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
        //$html .= '<div style="width: 120mm;">';
        $no = 1;
        foreach ($records as $record) {
            // if ($no == 3) {
            //     $no = 1;
            // }
            // if ($no == 1) {
            //     $padding = "padding:0 3mm 1mm 0mm;";
            // } else {
            //     $padding = "padding:0 0mm 1mm 4mm;";
            // }
            //Generate QRcode

            if ($record->logo == "0") {
                $img_bpi = '<img style="width:50%;" src="' . base_url("assets/image/bpi_logo.png") . '" />';
            } else {
                $img_bpi = '';
            }

            if($record->packing == "1" || $record->packing == "3"){
                $label = "LABEL PACKING";
            }else if($record->packing == "2"){
                $label = "LABEL BOXS";
            }
            // else{
            //     $label = $record->label_desc;
            // }

            $qc1 = substr($record->qc_1, 0, 3);
            $qcnumber1 = substr($record->qcnumber_1, -3);
            $qc2 = substr($record->qc_2, 0, 3);
            $qcnumber2 = substr($record->qcnumber_2, -3);
            $op1 = substr($record->op_1, 0, 3);
            $opnumber1 = substr($record->opnumber_1, -3);
            $op2 = substr($record->op_2, 0, 3);
            $opnumber2 = substr($record->opnumber_2, -3);
            $qrcodes = $record->label_divided;
            $this->createQrcode($qrcodes, "assets/image/qrcode/", $record->label_divided);
            $html .= '  <div style="width: 75mm; max-height:90mm; border:1px solid black; margin-bottom:5px;">
                            <table id="customers" border="1" style="width: 100%; font-family: Arial, sans-serif; font-size: 10px; border-collapse: collapse;">
                                <tr>
                                    <th colspan="4" style="font-size: 6px; text-align: right; border: none;"><b>' . $config_iso->doc_wip_receipt . '</b></th>
                                </tr>
                                <tr style="height: 10px;">
                                    <th colspan="4" style="font-size: 12px; text-align: center; border: none;"><b>'. $label .'</b></th>
                                </tr>
                                <tr>
                                    <td style="width:5mm; height: 5mm; border: none; text-align: center;">' . $img_bpi . '</td>
                                    <td colspan="3" style="text-align:left; border: none;"><small style="font-size:10px;"><b>PT BANSHU PLASTIC INDONESIA</b></small></td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:left; border: 1px solid black;">
                                        <small style="font-size:10px;">Part No</small><br><b style="font-size:12px;">' . $record->item_number . '</b>
                                    </td>
                                    <td colspan="2" style="text-align:left; border: 1px solid black;">
                                        <small style="font-size:10px;">Lot No.</small><br><b style="font-size:12px;">' . $record->lot_no . '</b>
                                    </td>
                                </tr>
                                <tr style="height: 10px;">
                                    <td colspan="2" style="text-align:left; border: 1px solid black;">
                                        <small style="font-size:10px;">Part Name</small><br><br><b style="font-size:11px;">' . $record->item_name . '</b>
                                    </td>

                                    <td colspan="2" style="text-align:left; border: 1px solid black;">
                                        <small style="font-size:10px;">Prod Date.</small><br><b style="font-size:10px;">' . $record->prod_date . '</b>
                                        <br>
                                        <small style="font-size:10px;">Pack Date.</small><br><b style="font-size:10px;">' . $record->trans_date . '</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="text-align:left; border: 1px solid black;">
                                        <small style="font-size:10px;">Cust Code</small><br><b style="font-size:12px;">' . $record->item_number_customer . '</b>
                                    </td>
                                    <td style="text-align:left; border: none;">
                                        <small style="font-size:10px;">Shift.</small><br>
                                        <div style="text-align:center;">
                                            <b style="font-size:12px;">' . $record->shift . '</b>
                                        </div>
                                    </td>
                                    <td style="text-align:left; border: none;">
                                        <img src="' . base_url('assets/image/qc_passed.png') . '" width="30" style="float: center; margin-right: 5px; margin-top: 5px;">
                                    </td>
                                </tr>
                            <tr style="height: 5px;">
                                    <td style="width:15mm; height: 5mm; style="text-align:left; border: 1px solid black;">
                                        <small style="font-size:10px;">Qty</small><br><b style="font-size:10px;">' . number_format($record->qty, 2) . '</b>
                                    </td>

                                    <td style="text-align:left; border: 1px solid black;">
                                        <small style="font-size:10px;">Unit</small><br><b style="font-size:10px;">' . $record->uom . '</b>
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
                                        <b style="font-size:8px;">' . $op1 . '</b>&nbsp<b style="font-size:8px;">' . $opnumber1 . '</b>
                                        <br>
                                        <b style="font-size:8px;">' . $op2 . '</b>&nbsp<b style="font-size:8px;">' . $opnumber2 . '</b>
                                    </td>
                                    <td style="text-align:left; border: 1px solid black;">
                                        <b style="font-size:8px;">' . $qc1 . '</b>&nbsp<b style="font-size:8px;">' . $qcnumber1 . '</b>
                                        <br>
                                        <b style="font-size:8px;">' . $qc2 . '</b>&nbsp<b style="font-size:8px;">' . $qcnumber2 . '</b>
                                    </td>
                                </tr>
                            <tr>
                                    <td colspan="2" style="text-align:left; border: 1px solid black;"><small style="font-size:14px;"><b>' . $record->label_divided . '</b></small>
                                    <br><b style="color:blue; font-size:5px;">BARCODE DIVIDES FOR '.$record->type.'</b>
                                    <br><br><small style="font-size:10px;"><b>' . $record->location . '</b></small></td>
                                    <td colspan="2" style="text-align:center; border: 1px solid black;">
                                        <img src="' . base_url('assets/image/qrcode/' . $record->label_divided . '.png') . '" width="50"/>
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
}
