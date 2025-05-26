<?php
defined('BASEPATH') or exit('No direct script access allowed');
class new_barcode_fg extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->library('Zend');
        $this->zend->load('Zend/Barcode');
        $this->load->model('crud');
        //Validasi Form
        // $this->form_validation->set_rules('label_no', 'Label No', 'required|min_length[1]|max_length[50]');

    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/new_barcode_fg');
        } else {
            redirect('error_access');
        }
    }

    public function readItem($date)
    {
        $dates = base64_decode($date);

        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->db->select('a.*, b.qty');
        $this->db->from('item_fg a');
        $this->db->join('os_fg b', 'a.id = b.item_fg_id');
        $this->db->where('b.trans_date', $dates);
        $this->db->like('a.number', $post);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readDocument($item_fg)
    {
        $item_fg = base64_decode($item_fg);

        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $this->db->select('a.*, a.number as os_fg_number, b.name as item_name , b.id as item_id , b.number_customer as item_number_customer');
        $this->db->from('os_fg a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.item_fg_id', $item_fg);
        $this->db->like('a.number', $post);
        $this->db->group_by('a.id');
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    // public function readEmployesOP(){
    //     $ch = curl_init(); 
    //     curl_setopt($ch, CURLOPT_URL, "https://hrbpi.hris-server.com/api/master/employees/operator");
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     $output = curl_exec($ch);
    //     curl_close($ch);
    //     echo $output;
    // }

    // public function readEmployesQC(){
    //     $ch = curl_init(); 
    //     curl_setopt($ch, CURLOPT_URL, "https://hrbpi.hris-server.com/api/master/employees/qc");
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     $output = curl_exec($ch);
    //     curl_close($ch);
    //     echo $output;
    // }

    public function readEmployesOP()
    {
        $send = $this->crud->query("SELECT nik, name
            FROM employees a
            WHERE `status` = 0 and position = 'Operator' 
            ORDER BY id DESC");
        echo json_encode($send);
    }

    public function readEmployesQC()
    {
        $send = $this->crud->query("SELECT nik, name
            FROM employees a
            WHERE `status` = 0 and position = 'Qc' 
            ORDER BY id DESC");
        echo json_encode($send);
    }

    public function checkAcc($date, $item_fg_id)
    {
        $date = base64_decode($date);
        $item_fg_id = base64_decode($item_fg_id);
        $send = $this->crud->query("SELECT COALESCE(SUM(qty),0) as qty
            FROM new_barcode_fg
            WHERE cut_off_date = '$date' and item_fg_id = '$item_fg_id'
            ORDER BY id DESC");
        echo json_encode($send);
    }


    public function readItems($item_fg_id)
    {
        $item_id = base64_decode($item_fg_id);
        $send = $this->crud->query("SELECT mpq, qty_box
            FROM item_fg a
            WHERE `status` = 0 and id = '$item_id' 
            ORDER BY id DESC");
        echo json_encode($send);
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            // var_dump($post);
            // die;

            if (!empty($post['label_no'])) {
                $new_barcode_fg = $this->crud->reads('new_barcode_fg',[],['item_fg_id' => $post['item_fg_id'],'cut_off_date' => $post['cut_off_date'],'label' => $post['label_no']]);
            } else {
                $new_barcode_fg = $this->crud->reads('new_barcode_fg',[],['item_fg_id' => $post['item_fg_id'],'cut_off_date' => $post['cut_off_date'],'number' => $post['number']]);
            }

            if (count($new_barcode_fg)){
                echo json_encode(array("title" => "Available", "message" => "Item Id has Been Created in Period ", "theme" => "error"));
            } else {
                $qty_label = $post['qty_label'];
                $datenow = date('Ymd', strtotime($post['cut_off_date']));
                $sqlGetID   = $this->db->query("SELECT max(label_no) as kode FROM new_barcode_fg WHERE label_no like '%$datenow%'");
                $rowID      = $sqlGetID->row();
                $kode       = $rowID->kode;
                
                if ($kode == NULL) {
                    $autoID = sprintf("%05s", $kode + 1);
                } else {
                    $urutan = (int) substr($kode, -5);
                    $urutan++;
                    $autoID = sprintf("%05s", $urutan);
                }

                $qty_receipt = $post['qty'];
                if ($qty_label > 0) {
                    for ($i=0; $i < $qty_label; $i++) { 
                        if (!empty($post['label_no'])) {
                            $label_no = $post['label_no'];
                        } else {
                            $label_no = "NBCFG-" . $datenow . "-" . $autoID;
                        }
                       
                        if ($qty_receipt > $post['packing_qty']) {
                            $qty = $post['packing_qty'];
                        } else {
                            $qty = $qty_receipt;
                        }

                        $arrLabel = [
                            "label_no" => $label_no,
                            "qty" => $qty,
                            "item_fg_id" => $post['item_fg_id'],
                            "stock" => $post['qty'],
                            "prod_date" => $post['prod_date'],
                            "packing_date" => $post['packing_date'],
                            "lot_no" => $post['lot_no'],
                            "qc_1" => $post['qc_1'],
                            "qc_2" => $post['qc_2'],
                            "op_1" => $post['op_1'],
                            "op_2" => $post['op_2'],
                            "qcnumber_1" => $post['qcnumber_1'],
                            "qcnumber_2" => $post['qcnumber_2'],
                            "opnumber_1" => $post['opnumber_1'],
                            "opnumber_2" => $post['opnumber_2'],
                            "number" => $post['number'],
                            "shift" => $post['shift'],
                            "packing" => $post['packing'],
                            "cut_off_date" => $post['cut_off_date'],
                            "label_type" => $post['label_type'],
                            "label" => $post['label_no'],
                        ];

                        $send   = $this->crud->createNotLog('new_barcode_fg', $arrLabel);
                        $update = $this->crud->update('os_fg', ["item_fg_id" => $post['item_fg_id'], "number" => $post['number']], ["status" => 1]);
                        $message = json_encode(array("title" => "Success", "message" => "Data Saved Successfully ", "theme" => "success"));
                        // Generate QRcode
                        $this->createQrcode($label_no, "assets/image/qrcode/");

                        // Generate Barcode
                        $this->createBarcode($label_no, "assets/image/barcode/");

                        $autoID = sprintf("%05s", $autoID + 1);

                        // Kurangi qty_receipt dengan qty yang telah diproses
                        $qty_receipt -= $qty;
                    }

                    echo $message;
                } else {
                    echo json_encode(array("title" => "Available", "message" => "QTY label is 0 ", "theme" => "error"));
                }
            }
        } else {
            show_error('Cannot Process your Request');
        }
    }

    private function createBarcode($label_no, $path)
    {
        $barcodeOptions = array(
            'text' => $label_no,  // Nilai barcode
            'barHeight' => 30,    // Tinggi barcode
            'factor' => 2,        // Skala barcode
        );
    
        // Path lengkap file barcode
        $barcodeFile = $path . $label_no . ".png";
    
        // Gunakan output buffer untuk menangkap hasil render
        ob_start();
        Zend_Barcode::render('code128', 'image', $barcodeOptions, []);
        $barcodeImage = ob_get_clean();
    
        // Simpan hasil ke file
        file_put_contents($barcodeFile, $barcodeImage);
    
        // Return path file
        return $barcodeFile;
    }

    public function print()
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('new_barcode_fg');
        $nbfg = $this->db->get()->row();

        $item_fg_id = base64_decode($this->input->get('item_fg_id'));
        $cut_off_date = base64_decode($this->input->get('cut_off_date'));
        $number = base64_decode($this->input->get('number'));
        $label_no = $this->input->get('label_no') ? base64_decode($this->input->get('label_no')) : null;

        $config_iso = $this->db->get('config_iso')->row();

        $date = new DateTime($cut_off_date);
        $p_month = $date->format('m'); 
        $p_year = $date->format('y');

        $this->db->select('a.*,d.number,d.number_customer as item_number_customer, d.name, e.location, e.area, d.color, d.uom, a.qty, a.cut_off_date, d.number as item_number, d.name as item_name, d.alias, d.logo, d.uom, e.location, a.packing');
        $this->db->from('new_barcode_fg a');
        $this->db->join('item_fg d', 'a.item_fg_id = d.id');
        $this->db->join('warehouse_location_items e', 'e.item_fg_id = d.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.item_fg_id', $item_fg_id);
        $this->db->where('a.cut_off_date', $cut_off_date);
        // $this->db->where('a.number', $number);
        // $this->db->group_by('a.cut_off_date', $cut_off_date);

        if ($label_no) {
            $this->db->where('a.label', $label_no);
        }

        if ($number) {
            $this->db->where('a.number', $number);
        }
        
        $records = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $nbfg->label_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
            //$html .= '<div style="width: 120mm;">';
            $no = 1;
            foreach ($records as $record) {
                if($record->label_type == "auto"){
                    $padding = "padding:3mm 5mm 5mm 3mm;";
                    
                    if ($record->logo == "0") {
                        $img_bpi = '<img style="width:50%;" src="' . base_url("assets/image/bpi_logo.png") . '" />';
                    } else {
                        $img_bpi = '';
                    }

                    if($record->packing == "1" || $record->packing == "3"){
                        $label = 'LABEL PACKING';
                    } else{
                        $label = 'LABEL BOXS';
                    }

                    $qc1 = substr($record->qc_1, 0, 3);
                    $qcnumber1 = substr($record->qcnumber_1, -3);
                    $qc2 = substr($record->qc_2, 0, 3);
                    $qcnumber2 = substr($record->qcnumber_2, -3);
                    $op1 = substr($record->op_1, 0, 3);
                    $opnumber1 = substr($record->opnumber_1, -3);
                    $op2 = substr($record->op_2, 0, 3);
                    $opnumber2 = substr($record->opnumber_2, -3);

                    //Generate QRcode
                    $qrcodes =$record->label_no;
                    $this->createQrcode($qrcodes, "assets/image/qrcode/", $record->label_no);

                    $html .= '  <div style="width: 70mm; max-height:100mm; border:none; margin-bottom:5px;">
                                    <table id="customers" border="1" style="width: 100%; font-family: Arial, sans-serif; font-size: 10px; border-collapse: collapse;">
                                        <tr>
                                            <th colspan="4" style="font-size: 6px; text-align: right; border: none;"><b>' . $config_iso->doc_barcode_fg . '</b></th>
                                        </tr>
                                        <tr>
                                        <th colspan="4" style="font-size: 12px; text-align: center; border: none;"><b>' . $label . '</b></th>
                                        </tr>
                                        <tr>
                                            <td style="width:5mm; height: 5mm; border: none; text-align: center;">' . $img_bpi . '</td>
                                            <td colspan="3" style="text-align:center; border: none;"><small style="font-size:10px;"><b>PT BANSHU PLASTIC INDONESIA</b></small></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="text-align:left; border: 1px solid black;">
                                                <small style="font-size:10px;">Part No</small><br><b style="font-size:16px;">' . $record->item_number . '</b>
                                            </td>
                                            <td colspan="2" style="text-align:left; border: 1px solid black;">
                                                <small style="font-size:10px;">Lot No.</small><br><b style="font-size:12px;">' . $record->lot_no . '</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="text-align:left; border: 1px solid black;">
                                                <small style="font-size:10px;">Part Name</small><br><b style="font-size:12px;">' . $record->item_name . '</b>
                                            </td>

                                            <td colspan="2" style="text-align:left; border: 1px solid black;">
                                                <small style="font-size:10px;">Prod Date.</small><br><b style="font-size:12px;">' . $record->prod_date . '</b>
                                                <br>
                                                <small style="font-size:10px;">Pack Date.</small><br><b style="font-size:12px;">' . $record->packing_date . '</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="text-align:left; border: 1px solid black;">
                                                <small style="font-size:10px;">Cust Code</small><br><b style="font-size:12px;">'. $record->item_number_customer . '</b>
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
                                        <tr>
                                            <td style="text-align:left; border: 1px solid black;">
                                                    <small style="font-size:10px;">Qty</small><br><b style="font-size:16px;">' . number_format($record->qty, 2) . '</b>
                                            </td>

                                            <td style="text-align:left; border: 1px solid black;">
                                                <small style="font-size:10px;">Unit</small><br><b style="font-size:12px;">'. $record->uom .'</b>
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
                                            <td colspan="2" style="text-align:left; border: 1px solid black;"><small style="font-size:14px;"><b>' . $record->label_no . '</b></small>
                                            <br><small style="font-size:10px;"><b>' . $record->location . '</b></small></td>
                                            <td colspan="2" style="text-align:center; border: 1px solid black;">
                                                <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="90"/>
                                            </td>
                                        </tr>
                                    </table>
                                </div>';
                    $no++;
                } else {
                    $padding = "padding:3mm 5mm 3mm 3mm;";
                    
                    if ($record->logo == "0") {
                        $img_bpi = '<img style="width:50%;" src="' . base_url("assets/image/bpi_logo.png") . '" />';
                    } else {
                        $img_bpi = '';
                    }

                    if($record->packing == "1" || $record->packing == "3"){
                        $label = 'LABEL PACKING';
                    } else{
                        $label = 'LABEL BOXS';
                    }

                    $qc1 = substr($record->qc_1, 0, 3);
                    $qcnumber1 = substr($record->qcnumber_1, -3);
                    $qc2 = substr($record->qc_2, 0, 3);
                    $qcnumber2 = substr($record->qcnumber_2, -3);
                    $op1 = substr($record->op_1, 0, 3);
                    $opnumber1 = substr($record->opnumber_1, -3);
                    $op2 = substr($record->op_2, 0, 3);
                    $opnumber2 = substr($record->opnumber_2, -3);

                    //Generate QRcode
                    $qrcodes =$record->label_no;
                    $this->createQrcode($qrcodes, "assets/image/qrcode/", $record->label_no);

                    $html .= '  <div style="width: 70mm; max-height:100mm; border:none; margin-bottom:5px;">
                                    <table id="customers" border="1" style="width: 100%; font-family: Arial, sans-serif; font-size: 10px; border-collapse: collapse;">
                                        <tr>
                                            <th colspan="4" style="font-size: 6px; text-align: right; border: none;"><b>' . $config_iso->doc_barcode_fg . '</b></th>
                                        </tr>
                                        <tr>
                                        <th colspan="4" style="font-size: 12px; text-align: center; border: none;"><b>' . $label . '</b></th>
                                        </tr>
                                        <tr>
                                            <td style="width:10mm; height: 10mm; border: none; text-align: center;">' . $img_bpi . '</td>
                                            <td colspan="3" style="text-align:center; border: none;"><small style="font-size:10px;"><b>PT BANSHU PLASTIC INDONESIA</b></small></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="text-align:left; border: 1px solid black;">
                                                <small style="font-size:10px;">Part No</small><br><b style="font-size:16px;">' . $record->item_number . '</b>
                                            </td>
                                            <td colspan="2" style="text-align:left; border: 1px solid black;">
                                                <small style="font-size:10px;">Lot No.</small><br><b style="font-size:12px;">' . $record->lot_no . '</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="text-align:left; border: 1px solid black;">
                                                <small style="font-size:10px;">Part Name</small><br><b style="font-size:12px;">' . $record->item_name . '</b>
                                            </td>

                                            <td colspan="2" style="text-align:left; border: 1px solid black;">
                                                <small style="font-size:10px;">Prod Date.</small><br><b style="font-size:12px;">' . $record->prod_date . '</b>
                                                <br>
                                                <small style="font-size:10px;">Pack Date.</small><br><b style="font-size:12px;">' . $record->packing_date . '</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="text-align:left; border: 1px solid black;">
                                                <small style="font-size:10px;">Cust Code</small><br><b style="font-size:12px;">'. $record->item_number_customer . '</b>
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
                                        <tr>
                                            <td style="text-align:left; border: 1px solid black;">
                                                    <small style="font-size:10px;">Qty</small><br><b style="font-size:16px;">' . number_format($record->qty, 2) . '</b>
                                            </td>

                                            <td style="text-align:left; border: 1px solid black;">
                                                <small style="font-size:10px;">Unit</small><br><b style="font-size:12px;">'. $record->uom .'</b>
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
                                            <td colspan="4" style="text-align:center; border: 1px solid black;"><small style="font-size:14px;"><b>' . $record->label_no . '</b></small>
                                            <br><small style="font-size:10px;"><b>' . $record->location . '</b></small></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" style="text-align:center; border: 1px solid black; height:40;">
                                                <img src="' . base_url('assets/image/barcode/' . $record->label_no . '.png') . '" width="280"/>
                                            </td>
                                        </tr>
                                    </table>
                                </div>';
                    $no++;
                }
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }
}
