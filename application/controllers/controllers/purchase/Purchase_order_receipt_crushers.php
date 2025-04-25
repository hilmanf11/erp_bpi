<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_order_receipt_crushers extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_order_receipt_crushers');
        } else {
            redirect('error_access');
        }
    }
    public function reads()
    {
        $request_no = $this->input->get('request_no');
        $supplier_id = $this->input->get('supplier_id');
        //Select Query
        $this->db->select('a.*, b.number, b.name, b.uom, c.name as item_family_name, e.name as supplier_name, d.mpq, d.moq, d.price');
        $this->db->from('purchase_order_receipt_crushers a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('item_familys c', 'b.item_family_number = c.number');
        $this->db->join('supplier_items d', 'a.item_rm_id = d.item_rm_id');
        $this->db->join('suppliers e', 'd.supplier_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->like('a.request_no', $request_no);
        $this->db->like('d.supplier_id', $supplier_id);
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();
        echo json_encode($records);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT *
        FROM item_rm 
        WHERE `status` = '0'
        order by `number` asc");
        echo json_encode($send);
    }

    public function readRM()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, COALESCE(c.mpq, 0) AS mpq 
        FROM item_rm a 
        JOIN item_familys b ON a.item_family_id = b.id 
        LEFT JOIN supplier_items c ON a.id = c.item_rm_id 
        WHERE (a.number like '%$post%' or a.name like '$post') and a.item_family_id = 'P05'");
        echo json_encode($send);
    }

    public function readReceiptNo()
    {
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipt_crushers WHERE status = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }
    
    public function receipt_no($date = "")
    {
        if ($date == "") {
            $datenow = date("Ymd");
        } else {
            $datenow = date("Ymd", strtotime(base64_decode($date)));
        }
        $sqlGetID   = $this->db->query("SELECT max(receipt_no) as kode FROM purchase_order_receipt_crushers WHERE receipt_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "RCV-" . $datenow . "-" . $autoID;
    }

    public function receipt_id($receipt_no)
    {
        $sqlGetID   = $this->db->query("SELECT max(receipt_id) as kode FROM purchase_order_receipt_crushers WHERE receipt_id like '%$receipt_no%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        return $receipt_no . "-" . $autoID;
    }

    public function checkLabel($receipt_no){
        $receipt_no = base64_decode($receipt_no);
        $sqlReceipt = $this->db->query("SELECT sum(qty_label) as qty_label FROM purchase_order_receipt_crushers WHERE receipt_no ='$receipt_no'");
        $rowReceipt = $sqlReceipt->row();

        $sqlLabel = $this->db->query("SELECT count(label_no) as label_no FROM scan_item_receipts WHERE receipt_no ='$receipt_no'");
        $rowLabel = $sqlLabel->row();

        if(empty(@$rowLabel->label_no)){
            $label_no = 0;
        }else{
            $label_no = $rowLabel->label_no;
        }

        echo json_encode(["qty_label" => $rowReceipt->qty_label, "label_no" => $label_no]);
    }
    
    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to   = $this->input->get('filter_to');
            $filter_receipt = @base64_decode($this->input->get['filter_receipt']);
           
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('*');
            $this->db->from('purchase_order_receipt_crushers');
            $this->db->where('status','0');
            if ($filter_from != "" and $filter_to != "") {
                $this->db->where('trans_date >=', $filter_from);
                $this->db->where('trans_date <=', $filter_to);
            }

            $this->db->like('receipt_no', $filter_receipt);
            $this->db->order_by('receipt_no', 'ASC');

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

    public function datatableDetails()
    {
        if ($this->input->get()) {
            $receipt_nos = base64_decode($this->input->get('receipt_no'));
        
            $this->db->select('a.*, b.id as item_rm_id, b.number as item_rm_number, b.name as item_rm_name, b.uom, a.receipt_no as id');
            $this->db->from('purchase_order_receipt_crushers a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.receipt_no', $receipt_nos);
          
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $receipt_no = base64_decode($this->input->get('receipt_no'));

            $this->db->select('a.*, b.id as item_rm_id, b.number as item_rm_number, b.name as item_rm_name, b.uom');
            $this->db->from('purchase_order_receipt_crushers a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->where('a.receipt_no', $receipt_no);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();

                $receipt_crushers = $this->crud->read("purchase_order_receipt_crushers", [], ["receipt_no" => $post['receipt_no'], "item_rm_id" => $post['item_rm_id']]);
                if ($receipt_crushers->receipt_no != "") {
                    $post_final = [
                        "qty" => $post['qty'],
                        "qty_label" => $post['qty_label']
                    ];
                    $send = $this->crud->update('purchase_order_receipt_crushers', ["receipt_no" => $post['receipt_no']], $post_final);
                }else{
                    $send = $this->crud->create('purchase_order_receipt_crushers', array_merge($post, ["receipt_id" => $this->receipt_id($post['receipt_no'])]));
                }
                echo $send;
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_order_receipt_crushers', $data);
        $send2 = $this->crud->delete('purchase_order_label_crushers', $data);
        echo $send;
    }

    public function print_label($receipt_id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $receipt_id = base64_decode($receipt_id);
        $po_receipt = $this->crud->read('purchase_order_receipt_crushers', [], ["receipt_id" => $receipt_id]);
        $qty_receipt = $po_receipt->qty;

        //Cek Label
        $po_receipt_label = $this->crud->reads('purchase_order_label_crushers', [], ["receipt_id" => $receipt_id]);
        if (!$po_receipt_label) {
            for ($i = 0; $i < $po_receipt->qty_label; $i++) {
                //Read Label ID
                $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM purchase_order_label_crushers WHERE receipt_id = '$receipt_id'");
                $rowID = $sqlGetID->row();
                $label = $rowID->kode;
                if ($label == NULL) {
                    $autoID = $receipt_id . sprintf("%04s", $label + 1);
                } else {
                    $urutan = (int) substr($label, -4);
                    $autoID = $receipt_id . sprintf("%04s", $urutan + 1);
                }

                if ($qty_receipt > $po_receipt->mpq) {
                    $qty = $po_receipt->mpq;
                } else {
                    $qty = $qty_receipt;
                }
                
                $date = new DateTime($po_receipt->trans_date);
                $p_month = $date->format('m'); 
                $p_year = $date->format('y');               
                //Simpan Label
                $post   = $this->input->post();
                $arrLabel = [
                    "receipt_id" => $po_receipt->receipt_id,
                    "label_no" => $autoID,
                    "qty" => $qty,
                    "p_month" => $p_month,
                    "p_year" => $p_year 
                ];
                $send = $this->crud->create('purchase_order_label_crushers', $arrLabel);
                $qty_receipt = ($qty_receipt - $po_receipt->mpq);
            }
        }
        
        $this->db->select('a.*, b.trans_date, c.number, c.name, d.location, d.area, c.color, c.uom');
        $this->db->from('purchase_order_label_crushers a');
        $this->db->join('purchase_order_receipt_crushers b', 'a.receipt_id = b.receipt_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('warehouse_location_items d', 'd.item_rm_id = c.id', 'left');
        $this->db->join('item_familys e', 'c.item_family_id = e.id');
        $this->db->where('a.deleted', 0);
        //$this->db->where('a.status', 0);
        $this->db->where('a.receipt_id', $receipt_id);
        $this->db->order_by('a.label_no', 'asc');
        $records = $this->db->get()->result_object();
        $html = '<html>
                    <head>
                        <title>' . $receipt_id . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($records) {
            $html .= '<div style="width: 55mm;">';
            // $no = 1;
            foreach ($records as $record) {
                // if ($no == 3) {
                //     $no = 1;
                // }
                // if ($no == 1) {
                    $padding = "padding:3mm 5mm 3mm 3mm;";
                // } else {
                //     $padding = "padding:5mm 3mm 0mm 3mm;";
                // }
                //Generate QRcode
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
                                                <small style="font-size:14px;"><b>' . $record->p_month . '</b></small><small style="font-size:10px;"><b>' ." - ". $record->p_year . '</b></small>
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
                                            <small style="font-size:9px">Lot No. </small><b style="font-size:8px;"></b>
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th style="text-align:left">
                                            <div style="display: inline-block;">
                                                <small style="font-size:9px">Date :</small><br> 
                                                <b style="font-size:7px;">' . $record->trans_date . '</b>
                                             
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
                // $no++;
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
            header("Content-Disposition: attachment; filename=purchase_order_receipt_crushers_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_receipt = $this->input->get('filter_receipt');
       
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.id as item_rm_id, b.number as item_rm_number, b.name as item_rm_name, b.uom');
        $this->db->from('purchase_order_receipt_crushers a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" and $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        if ($filter_receipt != "") {
            $this->db->where('a.receipt_no', $filter_receipt);
        }
       
        $this->db->order_by('a.trans_date', 'DESC');
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
                                <small>RECEIVING CRUSHER</small>
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
                <th>Document No</th>
                <th>Transaction Date</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>UoM</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td style="text-align:center">' . $no . '</td>
                    <td>' . $data['receipt_no'] . '</td>
                    <td>' . $data['trans_date'] . '</td>
                    <td>' . $data['item_rm_number'] . '</td>
                    <td>' . $data['item_rm_name'] . '</td>
                    <td>' . number_format($data['qty'], 2) . '</td>
                    <td>' . $data['uom'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
