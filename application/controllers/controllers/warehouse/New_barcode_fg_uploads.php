<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class New_barcode_fg_uploads extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Zend');
        $this->zend->load('Zend/Barcode');
        $this->load->model('crud');
        //VALIDASI FORM
        // $this->form_validation->set_rules('number', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[purgings.number]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/new_barcode_fg_uploads');
        } else {
            redirect('error_access');
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $id = $data['id'];
        $label_no = $data['label_no'];
        
        $send = $this->crud->delete('new_barcode_fg', ["id" => $id]);
        $send2 = $this->crud->delete('scan_item_receipts_fg', ["checksheet_label" => $label_no]);
        echo $send;
    }
    
    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.number as item_number');
            $this->db->from('new_barcode_fg a');
            $this->db->join('item_fg b', "a.item_fg_id = b.id");
            $this->db->where('a.label_type', 'manual');
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->group_by('id');
            $this->db->order_by('id', 'ASC');
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
   
    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'cut_off_date' => $data->val($i, 2),
                'item_number' => $data->val($i, 3),
                'prod_date' => $data->val($i, 4),
                'packing_date' => $data->val($i, 5),
                'lot_no' => $data->val($i, 6),
                'qc_1' => $data->val($i, 7),
                'qc_2' => $data->val($i, 8),
                'op_1' => $data->val($i, 9),
                'op_2' => $data->val($i, 10),
                'shift' => $data->val($i, 11),
                'qty' => $data->val($i, 12),
                'packing' => $data->val($i, 13),
                'packing_qty' => $data->val($i, 14),
                'qty_label' => $data->val($i, 15),
                'label_no' => $data->val($i, 16)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/new_barcode_fg_uploads.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/new_barcode_fg_uploads.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/new_barcode_fg_uploads.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }
    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Process Number          //table       //field        //field excel
            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_number']]);
            $new_barcode_fg = $this->crud->reads('new_barcode_fg',[],['label' => $data['label_no']]);

            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not Found", "message" => " Item Number. " . $data['item_number'] . " Not Found", "theme" => "error"));
            }else if (count($new_barcode_fg)){
                echo json_encode(array("title" => "Available", "message" => " Label No. " . $data['label_no'] ." has Been Created ", "theme" => "error"));
            } else {
                $qty_label = $data['qty_label'];
                $qty_receipt = $data['qty'];
                
                if ($qty_label > 0) {
                    for ($i=0; $i < $qty_label; $i++) { 

                        $label_no = $data['label_no'];

                        if ($qty_receipt > $data['packing_qty']) {
                            $qty = $data['packing_qty'];
                        } else {
                            $qty = $qty_receipt;
                        }

                        $dataFinal = [
                            //field
                            "item_fg_id" => $item_fg->id,
                            "label_no" => $label_no,
                            "label" => $data['label_no'],
                            "qty" => $qty,
                            "cut_off_date" => $data['cut_off_date'],
                            "prod_date" => $data['prod_date'],
                            "packing_date" => $data['packing_date'],
                            "qc_1" => $data['qc_1'],
                            "qc_2" => $data['qc_2'],
                            "op_1" => $data['op_1'],
                            "op_2" => $data['op_2'],
                            "shift" => $data['shift'],
                            "packing" => $data['packing'],
                            "packing_qty" => $data['packing_qty'],
                            "lot_no" => $data['lot_no'],
                            "label_type" => "manual",
                            "status" => "0",
                        ];

                        $send   = $this->crud->create('new_barcode_fg', $dataFinal);
                        echo $send;

                        // Generate Barcode
                        $this->createBarcode($label_no, "assets/image/barcode/");

                        // Kurangi qty_receipt dengan qty yang telah diproses
                        $qty_receipt -= $qty;
                    }
                } else {
                    echo json_encode(array("title" => "Available", "message" => "QTY label is 0 ", "theme" => "error"));
                }
            }
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

    //PRINT & EXCEL DATA
    public function print($id="")
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $id = base64_decode($id);

        $config_iso = $this->db->get('config_iso')->row();


        $this->db->select('a.*,d.number,d.number_customer as item_number_customer, d.name, e.location, e.area, d.color, d.uom, a.qty, a.cut_off_date, d.number as item_number, d.name as item_name, d.alias, d.logo, d.uom, e.location, a.packing');
        $this->db->from('new_barcode_fg a');
        $this->db->join('item_fg d', 'a.item_fg_id = d.id');
        $this->db->join('warehouse_location_items e', 'e.item_fg_id = d.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.id', $id);
        $records = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
            //$html .= '<div style="width: 120mm;">';
            $no = 1;
            foreach ($records as $record) {
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
                                            <small style="font-size:10px;">Part No</small><br><b style="font-size:12px;">' . $record->item_number . '</b>
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
                                                <small style="font-size:10px;">Qty</small><br><b style="font-size:12px;">' . number_format($record->qty, 2) . '</b>
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
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }
}
