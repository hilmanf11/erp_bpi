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

    public function readEmployesOP(){
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, "https://hrbpi.hris-server.com/api/master/employees/operator");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        echo $output;
    }

    public function readEmployesQC(){
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, "https://hrbpi.hris-server.com/api/master/employees/qc");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        echo $output;
    }

    public function readItemrm($category_id, $family_id, $subfamily_id)
    {
       $post = isset($_POST['q']) ? $_POST['q'] : "";
       $this->db->select('a.*');
       $this->db->from('item_fg a');
       $this->db->join('item_categories b','a.item_category_id = b.id');
       $this->db->join('item_familys c','a.item_family_id = c.id');
       $this->db->join('item_family_subs d','a.item_sub_family_id = d.id','left');
       $this->db->where('a.item_category_id', $category_id);
       $this->db->where('a.item_family_id', $family_id);
       $this->db->where('a.item_sub_family_id', $subfamily_id);
       $this->db->like('a.number', $post);
       $this->db->group_by('a.id');
       $this->db->order_by('a.id', 'ASC');
       $records = $this->db->get()->result_array();

        echo json_encode($records);
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

    // public function stock($item_fg_id, $cut_off_date ){
    //     $cut_off_date = base64_decode($cut_off_date);
    //     $item_fg_id = base64_decode($item_fg_id);

    //     $itemReceipts = $this->crud->query("SELECT
    //     a.id,
    //     (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty,0) + COALESCE(h.qty_stock_rm, 0) - COALESCE(f.qty, 0) ) as begin_stock
    //     FROM item_fg a 
    //     JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'
    //     LEFT JOIN purchase_order_receipts d ON a.id = d.item_fg_id and d.receipt_date < '$cut_off_date'
    //     LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
    //     LEFT JOIN (SELECT item_fg_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$cut_off_date' GROUP BY item_fg_id) f ON a.id = f.item_fg_id
    //     LEFT JOIN (SELECT a.item_fg_id, SUM(c.qty) as return_qty
    //         FROM return_materials a 
    //         JOIN return_material_labels b ON a.return_id = b.return_id
    //         JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
    //         WHERE a.return_date < '$cut_off_date'
    //         GROUP BY a.item_fg_id) g ON a.id = g.item_fg_id

    //     LEFT JOIN (SELECT a.item_fg_id, SUM(a.qty) as qty_stock_rm
    //     FROM os_rm a
    //     JOIN item_fg b ON a.item_fg_id = b.id
    //     WHERE a.trans_date < '$cut_off_date'
    //     GROUP BY a.item_fg_id) h ON a.id = h.item_fg_id

    //     WHERE a.id like '$item_fg_id'
    //     GROUP BY a.id
    //     ORDER BY a.number");

    //     $begin_stock = 0;
    //     if (!empty($itemReceipts)) {
    //         $begin_stock = $itemReceipts[0]->begin_stock;
    //     }

    //     echo $begin_stock;
    // }

    // public function itemMpq($item_fg_id){
    //     $item_fg_id = base64_decode($item_fg_id);
        
    //     $send = $this->crud->read("supplier_items",[],['item_fg_id' => $item_fg_id]);
    //     echo json_encode($send);
    // }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $new_barcode_fg = $this->crud->reads('new_barcode_fg',[],['item_fg_id' => $post['item_fg_id'],'cut_off_date' => $post['cut_off_date']]);
            
            if (count($new_barcode_fg)){
                echo json_encode(array("title" => "Available", "message" => "Item Id has Been Created in Period ", "theme" => "error"));
            }else{
                $qty_label = $post['qty_label'];
                $datenow = date('Ymd', strtotime($post['cut_off_date']));
                $sqlGetID   = $this->db->query("SELECT max(label_no) as kode FROM new_barcode_fg WHERE label_no like '%$datenow%'");
                $rowID      = $sqlGetID->row();
                $kode       = $rowID->kode;
                if ($kode == NULL) {
                    $autoID = sprintf("%04s", $kode + 1);
                } else {
                    $urutan = (int) substr($kode, -4);
                    $urutan++;
                    $autoID = sprintf("%04s", $urutan);
                }

                $qty_receipt = $post['qty'];
                if ($qty_label > 0) {
                    for ($i=0; $i < $qty_label; $i++) { 
                        $label_no = "NBCFG-" . $datenow . "-" . $autoID;
                        
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
                            "shift" => $post['shift'],
                            "packing" => $post['packing'],
                            "cut_off_date" => $post['cut_off_date'],
                            "cut_off_date" => $post['cut_off_date'],

                        ];

                        $send   = $this->crud->create('new_barcode_fg', $arrLabel);
                        $message = json_encode(array("title" => "Success", "message" => "Data Saved Successfully ", "theme" => "success"));
                        //Generate QRcode
                        $this->createQrcode($label_no, "assets/image/qrcode/");
                        $autoID = sprintf("%04s", $autoID + 1);

                        $qty_receipt = ($qty_receipt - $post['packing_qty']);
                    }

                    echo $message;
                }else{
                    echo json_encode(array("title" => "Available", "message" => "QTY label is 0 ", "theme" => "error"));
                }
            }
        } else {
            show_error('Cannot Process your Request');
        }
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

        $date = new DateTime($cut_off_date);
        $p_month = $date->format('m'); 
        $p_year = $date->format('y');

        $this->db->select('a.*,d.number,d.number_customer as item_number_customer, d.name, e.location, e.area, d.color, d.uom, a.qty, a.cut_off_date, d.number as item_number, d.name as item_name, d.alias, d.logo, d.uom, e.location');
        $this->db->from('new_barcode_fg a');
        $this->db->join('item_fg d', 'a.item_fg_id = d.id');
        $this->db->join('warehouse_location_items e', 'e.item_fg_id = d.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.item_fg_id', $item_fg_id);
        $this->db->where('a.cut_off_date', $cut_off_date);
        // $this->db->group_by('a.cut_off_date', $cut_off_date);
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
                // if ($no == 3) {
                //     $no = 1;
                // }
                // if ($no == 1) {
                    $padding = "padding:3mm 5mm 3mm 3mm;";
                // } else {
                //     $padding = "padding:0 0mm 1mm 4mm;";
                // }

                if ($record->logo == "0") {
                    $img_bpi = '<img style="width:50%;" src="' . base_url("assets/image/bpi_logo.png") . '" />';
                } else {
                    $img_bpi = '';
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
                $qrcodes =$record->cut_off_date . "|" . $record->qty . "|" . $record->label_no;
                $this->createQrcode($qrcodes, "assets/image/qrcode/", $record->label_no);
                $html .= '  <div style="width: 70mm; max-height:90mm; border:none; margin-bottom:5px;">
                                <table id="customers" border="1" style="width: 100%; font-family: Arial, sans-serif; font-size: 10px; border-collapse: collapse;">
                                    <tr>
                                        <td style="width:10mm; height: 5mm; border: none; text-align: center;">' . $img_bpi . '</td>
                                        <td colspan="3" style="text-align:center; border: none;"><small style="font-size:10px;"><b>PT BANSHU PLASTIC INDONESIA</b></small></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Part Name</small><br><b style="font-size:12px;">' . $record->item_number . '</b>
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
                                        <td colspan="2" style="text-align:left; border: 1px solid black;"><small style="font-size:14px;"><b>' . $record->label_no . '</b></small></td>
                                        <br><br><small style="font-size:10px;"><b>' . $record->location . '</b></small></td>
                                        <td colspan="2" style="text-align:center; border: 1px solid black;">
                                            <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="50"/>
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
