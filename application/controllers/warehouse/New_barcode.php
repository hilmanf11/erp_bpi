<?php
defined('BASEPATH') or exit('No direct script access allowed');
class new_barcode extends CI_Controller
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
            $this->load->view('warehouse/new_barcode');
        } else {
            redirect('error_access');
        }
    }

    public function readItemrmnosub($category_id, $family_id)
    {
       $post = isset($_POST['q']) ? $_POST['q'] : "";
       $this->db->select('a.*');
       $this->db->from('item_rm a');
       $this->db->join('item_categories b','a.item_category_id = b.id');
       $this->db->join('item_familys c','a.item_family_id = c.id');
       $this->db->where('a.item_category_id', $category_id);
       $this->db->where('a.item_family_id', $family_id);
       $this->db->like('a.number', $post);
       $this->db->group_by('a.id');
       $this->db->order_by('a.id', 'ASC');
       $records = $this->db->get()->result_array();

        echo json_encode($records);
    }

    public function readItemrm($category_id, $family_id, $subfamily_id)
    {
       $post = isset($_POST['q']) ? $_POST['q'] : "";
       $this->db->select('a.*');
       $this->db->from('item_rm a');
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

    public function stock($item_rm_id, $cut_off_date ){
        $cut_off_date = base64_decode($cut_off_date);
        $item_rm_id = base64_decode($item_rm_id);

        $itemReceipts = $this->crud->query("SELECT
        a.id,
        (COALESCE(SUM(e.qty),0) + COALESCE(g.return_qty,0) + COALESCE(h.qty_stock_rm, 0) - COALESCE(f.qty, 0) ) as begin_stock
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id and b.number != '006'
        LEFT JOIN purchase_order_receipts d ON a.id = d.item_rm_id and d.receipt_date < '$cut_off_date'
        LEFT JOIN scan_item_receipts e ON d.receipt_id = e.receipt_id
        LEFT JOIN (SELECT item_rm_id, COALESCE(SUM(qty), 0) as qty FROM issued_material_details WHERE DATE_FORMAT(created_date, '%Y-%m-%d') < '$cut_off_date' GROUP BY item_rm_id) f ON a.id = f.item_rm_id
        LEFT JOIN (SELECT a.item_rm_id, SUM(c.qty) as return_qty
            FROM return_materials a 
            JOIN return_material_labels b ON a.return_id = b.return_id
            JOIN scan_item_receipts c ON a.return_id = c.receipt_id and b.label_no = c.label_no
            WHERE a.return_date < '$cut_off_date'
            GROUP BY a.item_rm_id) g ON a.id = g.item_rm_id

        LEFT JOIN (SELECT a.item_rm_id, SUM(a.qty) as qty_stock_rm
        FROM os_rm a
        JOIN item_rm b ON a.item_rm_id = b.id
        WHERE a.trans_date < '$cut_off_date'
        GROUP BY a.item_rm_id) h ON a.id = h.item_rm_id

        WHERE a.id like '$item_rm_id'
        GROUP BY a.id
        ORDER BY a.number");

        $begin_stock = 0;
        if (!empty($itemReceipts)) {
            $begin_stock = $itemReceipts[0]->begin_stock;
        }

        echo $begin_stock;
    }

    public function itemMpq($item_rm_id){
        $item_rm_id = base64_decode($item_rm_id);
        
        $send = $this->crud->read("supplier_items",[],['item_rm_id' => $item_rm_id]);
        echo json_encode($send);
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $new_barcode = $this->crud->reads('new_barcode',[],['item_rm_id' => $post['item_rm_id'],'cut_off_date' => $post['cut_off_date']]);
            
            if (count($new_barcode)){
                echo json_encode(array("title" => "Available", "message" => "Item Id has Been Created in Period ", "theme" => "error"));
            }else{
                $qty_label = $post['qty_label'];
                $datenow = date('Ymd', strtotime($post['cut_off_date']));
                $sqlGetID   = $this->db->query("SELECT max(label_no) as kode FROM new_barcode WHERE label_no like '%$datenow%'");
                $rowID      = $sqlGetID->row();
                $kode       = $rowID->kode;
                if ($kode == NULL) {
                    $autoID = sprintf("%04s", $kode + 1);
                } else {
                    $urutan = (int) substr($kode, -4);
                    $urutan++;
                    $autoID = sprintf("%04s", $urutan);
                }

                $qty_receipt = $post['stock'];
                if ($qty_label > 0) {
                    for ($i=0; $i < $qty_label; $i++) { 
                        $label_no = "NBC-" . $datenow . "-" . $autoID;
                        
                        if ($qty_receipt > $post['mpq']) {
                            $qty = $post['mpq'];
                        } else {
                            $qty = $qty_receipt;
                        }
                        
                        $arrLabel = [
                            "label_no" => $label_no,
                            "item_rm_id" => $post['item_rm_id'],
                            "stock" => $post['stock'],
                            "uom" => $post['uom'],
                            "qty" => $qty,
                            "cut_off_date" => $post['cut_off_date'],
                        ];

                        $send   = $this->crud->create('new_barcode', $arrLabel);
                        $message = json_encode(array("title" => "Success", "message" => "Data Saved Successfully ", "theme" => "success"));
                        //Generate QRcode
                        $this->createQrcode($label_no, "assets/image/qrcode/");
                        $autoID = sprintf("%04s", $autoID + 1);

                        $qty_receipt = ($qty_receipt - $post['mpq']);
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

        $item_rm_id = base64_decode($this->input->get('item_rm_id'));
        $cut_off_date = base64_decode($this->input->get('cut_off_date'));

        $date = new DateTime($cut_off_date);
        $p_month = $date->format('m'); 
        $p_year = $date->format('y');

        $this->db->select('a.*,d.number, d.name, e.location, e.area, d.color, d.uom, a.qty, a.cut_off_date');
        $this->db->from('new_barcode a');
        $this->db->join('item_rm d', 'a.item_rm_id = d.id');
        $this->db->join('warehouse_location_items e', 'e.item_rm_id = d.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.item_rm_id', $item_rm_id);
        $this->db->where('a.cut_off_date', $cut_off_date);
        // $this->db->group_by('a.cut_off_date', $cut_off_date);
        $records = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $item_rm_id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        $html .= '<div style="width: 55mm;">';
        $no = 1;
        foreach ($records as $record) {
            // if ($no == 3) {
            //     $no = 1;
            // }
            // if ($no == 1) {
                $padding = "padding:3mm 5mm 3mm 3mm;";
            // } else {
            //     $padding = "padding:5mm 5mm 0mm 3mm;";
            // }
            $this->createQrcode($record->label_no, "assets/image/qrcode/");
            $html .= '  <div style="max-width: 50mm; max-height:40mm; float:left; ' . $padding . '">
                            <table id="customers" border="1" style="margin-bottom:20px;">
                                <tr>   
                                    <th colspan="3" style="font-size:8px; text-align:center;">
                                        <img src="' . base_url('assets/image/bpi_logo.png') . '" width="10" style="float: left; margin-right: 5px;">
                                        <b>' . $config->name . '</b>
                                    </th>
                                </tr>
                                <tr>
                                    <td colspan="3" style="height:35px;">
                                        <div style="float:left;">
                                            <small style="font-size:10px;"><b>' . $record->number . '</b></small>
                                            <br>
                                            <b style="font-size:9px;">' . $record->name . " - " .$record->color.'</b>
                                        </div>
                                        
                                        <div style="float:right;">
                                            <small style="font-size:14px;"><b>' . $p_month . '</b></small><small style="font-size:10px;"><b>' ." - ". $p_year . '</b></small>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="text-align:left">
                                        <small style="font-size:10px">Quantity<br><b style="font-size:12px;">' . number_format($record->qty, 2) . '</b></small>
                                        <small style="font-size:13px; float: right;"><b>'. $record->uom . '</b></small>
                                        </th>
                                    <th style="text-align:left">
                                        <small style="font-size:9px">Location</small><br>
                                        
                                    </th>
                                </tr>
                                <tr>
                                    <th style="text-align:left">
                                        <div style="display: inline-block;">
                                            <small style="font-size:9px">Date :</small><br> 
                                            <b style="font-size:7px;">' . $record->cut_off_date . '</b>
                                        
                                        </div>
                                        <div style="display: inline-block; float:right;">
                                            <img src="' . base_url('assets/image/qc_passed.png') . '" width="30" style="float: right; margin-right: 5px; margin-top: 5px;">
                                        </div>
                                        <div style="display: inline-block;">
                                            <small style="font-size:9px">Label No :</small><br>
                                            <b style="font-size:7px;">' . $record->label_no . '</b>
                                        </div>
                                    </th>
                                    <th style="text-align:center;">
                                        <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="40"/><br>
                                        <small style="font-size:6px">QC Passed By :</small>
                                        <b style="font-size:6px;">' . $this->session->username . '</b>
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
