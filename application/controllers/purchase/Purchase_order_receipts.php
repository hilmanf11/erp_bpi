<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_order_receipts extends CI_Controller
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
            $this->load->view('purchase/purchase_order_receipts');
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
        $this->db->from('purchase_order_receipts a');
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

    public function readPoNo($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT po_no FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' and status = '0' GROUP BY po_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readPoNos()
    {
        $records = $this->crud->query("SELECT po_no FROM purchase_order_receipts WHERE `status` = '0' GROUP BY po_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readLotNo()
    {
        $records = $this->crud->query("SELECT lotno FROM purchase_order_receipts WHERE `status` = '0' GROUP BY lotno ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readReceipt($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' and status = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readReceipts()
    {
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE `status` = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readDocno($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $records = $this->crud->query("SELECT bc_document FROM purchase_order_receipts WHERE supplier_id = '$supplier_id' GROUP BY bc_document ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readDocnos()
    {
        $records = $this->crud->query("SELECT bc_document FROM purchase_order_receipts WHERE `status` = '0' GROUP BY bc_document ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readReceiptNo()
    {
        $records = $this->crud->query("SELECT receipt_no FROM purchase_order_receipts WHERE deleted = '0' GROUP BY receipt_no ORDER BY created_date desc");
        echo json_encode($records);
    }

    public function readSupplier()
    {
        $records = $this->crud->query("SELECT b.id, b.number, b.name FROM purchase_order_receipts a JOIN suppliers b ON a.supplier_id = b.id WHERE a.status = '0' GROUP BY a.supplier_id ORDER BY a.created_date desc");
        echo json_encode($records);
    }

    public function readCategories()
    {
        $records = $this->crud->query("SELECT `number`, id FROM item_categories WHERE status = '0'");
        echo json_encode($records);
    }
    
    public function receipt_no($date = "")
    {
        if ($date == "") {
            $datenow = date("Ymd");
        } else {
            $datenow = date("Ymd", strtotime(base64_decode($date)));
        }
        $sqlGetID   = $this->db->query("SELECT max(receipt_no) as kode FROM purchase_order_receipts WHERE receipt_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "POR-" . $datenow . "-" . $autoID;
    }

    public function lotno($date = "")
    {
        $dates = date_create(base64_decode($date));
        $p_month = $dates->format('m'); 
        $p_year = $dates->format('y');
        $datenow = $p_month.$p_year;

        $sqlGetID   = $this->db->query("SELECT max(lotno) as kode FROM purchase_order_receipts WHERE lotno LIKE '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;

        if ($kode == NULL) {
            $autoID = sprintf("%03s", 1) . $p_month . $p_year;
        } else {
            $urutan = (int) substr($kode, 0, 3);
            $autoID = sprintf("%03s", $urutan + 1) . $p_month . $p_year;
        }
        
        echo $autoID;
    }


    public function receipt_id($receipt_no)
    {
        $sqlGetID   = $this->db->query("SELECT max(receipt_id) as kode FROM purchase_order_receipts WHERE receipt_id like '%$receipt_no%'");
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

    public function checkItems($receipt_no_encoded)
    {
        $receipt_no = base64_decode($receipt_no_encoded);

        $targetItems = ['BPIRM-CP06240001', 'BPIRM-CP06240002'];

        // cek apakah ada item_rm_id target
        $this->db->where('receipt_no', $receipt_no);
        $this->db->where_in('item_rm_id', $targetItems);
        $query = $this->db->get('purchase_order_receipts');

        $updated = false;
        if ($query->num_rows() > 0) {
            // update langsung
            $this->db->where('receipt_no', $receipt_no);
            $this->db->update('purchase_order_receipts', ['status' => 1]);
            $updated = true;
        }

        echo json_encode([
            'success' => true,
            'updated' => $updated
        ]);
    }

    public function checkLabel($receipt_no){
        $receipt_no = base64_decode($receipt_no);
        $sqlReceipt = $this->db->query("SELECT sum(qty_label) as qty_label FROM purchase_order_receipts WHERE receipt_no ='$receipt_no'");
        $rowReceipt = $sqlReceipt->row();

        $sqlLabel = $this->db->query("SELECT count(label_no) as label_no FROM scan_item_receipts WHERE receipt_no ='$receipt_no'");
        $rowLabel = $sqlLabel->row();

        $sqlCategory = $this->db->query("SELECT DISTINCT b.item_category_id as category_id
        FROM purchase_order_receipts a
        JOIN item_rm b ON a.item_rm_id = b.id 
        JOIN item_categories c ON b.item_category_id = c.id 
        WHERE receipt_no ='$receipt_no'");
        $rowCategory = $sqlCategory->row();

        if(empty(@$rowLabel->label_no)){
            $label_no = 0;
        }else{
            $label_no = $rowLabel->label_no;
        }

        echo json_encode(["qty_label" => $rowReceipt->qty_label, "label_no" => $label_no, "category" => $rowCategory->category_id]);
    }

    public function checkReceipt() {
        $receipt_id = $this->input->post('receipt_id');

        $sqlScan = $this->db->query("SELECT COUNT(*) as cnt FROM scan_item_receipts WHERE receipt_id = ?", [$receipt_id]);
        $exists_scan = $sqlScan->row()->cnt;

        $sqlIssue = $this->db->query("SELECT COUNT(*) as cnt FROM issued_material_details WHERE label_no LIKE ? ", [$receipt_id.'%']);
        $exists_issue = $sqlIssue->row()->cnt;

        if ($exists_scan > 0 || $exists_issue > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Receipt Id cannot be Delete, Because this receipt already use for Transaction.'
            ]);
        } else {
            echo json_encode([
                'status' => 'ok'
            ]);
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to   = $this->input->get('filter_to');
            $filter_supplier = $this->input->get('filter_supplier');
            $filter_po_no = $this->input->get('filter_po_no');
            $filter_part_no = $this->input->get('filter_part_no');
            $filter_receipt = $this->input->get('filter_receipt');
            $filter_doc_no = $this->input->get('filter_doc_no');
            $filter_categories = $this->input->get('filter_categories');
            $filter_lotno = $this->input->get('filter_lotno');
            $filter_division = $this->input->get('filter_division');
            $filter_status_invoice = $this->input->get('filter_status_invoice');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            $id = $_POST['id'];
            if ($id === "0") {
                $this->db->select('a.po_no, a.receipt_no, a.receipt_date, a.awb_no, a.awb_date, a.bc_kind, a.bc_document, a.bc_aju, a.bc_date, b.number as supplier_id, a.lotno, 
                b.name as supplier_name, a.total_receipt as qty_receipt, a.total_label as qty_label, a.status, e.number as category_code, f.division, a.print, g.number as invoice_no');
                $this->db->from('(SELECT *, sum(qty_label) as total_label, sum(qty_receipt) as total_receipt FROM purchase_order_receipts GROUP BY receipt_no ORDER BY status asc) a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('purchase_orders c', 'a.po_no = c.po_no and a.item_rm_id = c.item_rm_id');
                $this->db->join('item_rm d', 'a.item_rm_id = d.id','left');
                $this->db->join('item_categories e', 'd.item_category_id = e.id','left');
                $this->db->join('purchase_requests f', 'c.request_no = f.request_no','left');
                $this->db->join('purchase_invoices g', 'a.receipt_no = g.por_no','left');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" and $filter_to != "") {
                    $this->db->where('a.receipt_date >=', $filter_from);
                    $this->db->where('a.receipt_date <=', $filter_to);
                }
                if ($filter_supplier != "") {
                    $this->db->where('a.supplier_id', $filter_supplier);
                }
                if ($filter_po_no != "") {
                    $this->db->where('a.po_no', $filter_po_no);
                }
                if ($filter_part_no != "") {
                    $this->db->where('a.item_rm_id', $filter_part_no);
                }
                if ($filter_receipt != "") {
                    $this->db->where('a.receipt_no', $filter_receipt);
                }
                if ($filter_doc_no != "") {
                    $this->db->where('a.bc_document', $filter_doc_no);
                }
                if ($filter_categories != "") {
                    $this->db->where('d.item_category_id', $filter_categories);
                }
                if ($filter_division != "") {
                    $this->db->where('f.division', $filter_division);
                }
                if ($filter_status_invoice !== "") { 
                    $this->db->where('a.status', $filter_status_invoice);
                }
                if ($filter_lotno !== "") { 
                    $this->db->where('a.lotno', $filter_lotno);
                }
    
                $this->db->group_by('a.receipt_no');
                $this->db->order_by('a.created_date', 'DESC');
                $this->db->order_by('a.status', 'ASC');
                $this->db->order_by('a.receipt_date', 'DESC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                //Get Data Array
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {
                    $receipt_no = $record['receipt_no'];
                    $purchase_order_label = $this->crud->query("SELECT receipt_id, SUM(`status`) as total_scan FROM purchase_order_labels WHERE receipt_id like '%$receipt_no%'");

                    $arr[] = array(
                        "id" => $record['receipt_no'],
                        "po_no" => $record['po_no'],
                        "lotno" => $record['lotno'],
                        "bc_kind" => $record['bc_kind'],
                        "bc_document" => $record['bc_document'],
                        "bc_aju" => $record['bc_aju'],
                        "bc_date" => $record['bc_date'],
                        "receipt_no" => $record['receipt_no'],
                        "receipt_date" => $record['receipt_date'],
                        "category_code" => $record['category_code'],
                        "awb_no" => $record['awb_no'],
                        "awb_date" => $record['awb_date'],
                        "supplier_id" => $record['supplier_id'],
                        "supplier_name" => $record['supplier_name'],
                        "invoice_no" => $record['invoice_no'],
                        "division" => $record['division'],
                        "qty_label" => $record['qty_label'],
                        "total_scan" => $purchase_order_label[0]->total_scan,
                        "status" => $record['status'],
                        "print" => $record['print'],
                        "state" => "closed",
                    );
                }
                //Mapping Data
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                $this->db->select('a.*, 
                    a.id as purchase_order_receipts_id, 
                    a.receipt_id as id, 
                    b.number as supplier_id, 
                    b.name as supplier_name, 
                    c.number as item_number, 
                    c.name as item_name, 
                    d.name as item_family_name, 
                    b.currency, 
                    f.number as category_code, 
                    e.uom_default,
                    e.uom_inventory,
                    e.mpq,
                    sum(g.status) as total_scan');
                $this->db->from('purchase_order_receipts a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                $this->db->join('item_familys d', 'c.item_family_id = d.id');
                $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
                $this->db->join('purchase_order_labels g', 'g.receipt_id = a.receipt_id', 'left');
                $this->db->join('item_categories f', 'c.item_category_id = f.id','left');
                $this->db->where('a.deleted', 0);
                if ($filter_from != "" and $filter_to != "") {
                    $this->db->where('a.receipt_date >=', $filter_from);
                    $this->db->where('a.receipt_date <=', $filter_to);
                }
                $this->db->where('a.receipt_no', $id);
                $this->db->group_by('a.receipt_id');
                $this->db->order_by('a.receipt_id', 'ASC');
                $records = $this->db->get()->result_array();
                echo json_encode($records);
            }
        }
    }

    public function datatablesTemp()
    {
        $po_no = $this->input->get('po_no');
        //Select Query
        $this->db->select('a.po_no, a.price , a.currency,
            b.id as item_rm_id, 
            b.number as item_number, 
            b.name as item_name, 
            a.qty as qty_po, 
            c.mpq, 
            a.supplier_id, 
            c.uom_default,
            c.uom_inventory,
            c.weight_kg as convertion,
            (a.qty - (CASE WHEN e.qty_os2 is null THEN 0 ELSE e.qty_os2 END)) as qty_os,
            (a.qty - (CASE WHEN e.qty_os2 is null THEN 0 ELSE e.qty_os2 END)) as qty_receipt,
            c.weight_kg * (a.qty - (CASE WHEN e.qty_os2 is null THEN 0 ELSE e.qty_os2 END)) as qty_convertion,
            CEIL(((c.weight_kg * a.qty) - (CASE WHEN e.qty_os2 is null THEN 0 ELSE e.qty_os2 END)) / c.mpq) as qty_label');
        $this->db->from('purchase_orders a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        $this->db->join('supplier_items c', 'a.item_rm_id = c.item_rm_id and a.supplier_id = c.supplier_id');
        $this->db->join('(SELECT sum(qty_receipt) as qty_os, item_rm_id, supplier_id, po_no FROM purchase_order_receipts GROUP BY item_rm_id, supplier_id, po_no) d', 'a.item_rm_id = d.item_rm_id and a.supplier_id = d.supplier_id and a.po_no = d.po_no', 'left');
        $this->db->join('(SELECT sum(qty_receipt2) as qty_os2, item_rm_id, supplier_id, po_no FROM purchase_order_receipts GROUP BY item_rm_id, supplier_id, po_no) e', 'a.item_rm_id = e.item_rm_id and a.supplier_id = e.supplier_id and a.po_no = e.po_no', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.po_no', $po_no);
        $records = $this->db->get()->result_array();
        echo json_encode($records);
        // (a.qty - (CASE WHEN d.qty_os is null THEN 0 ELSE d.qty_os END)) as qty_receipt,
    }

    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $por    = $this->crud->read('purchase_order_receipts', [], ["item_rm_id" => $post['item_rm_id'],"supplier_id" => $post['supplier_id'],"bc_document" => $post['bc_document'],"bc_date" => $post['bc_date'],"qty_receipt" => $post['qty_receipt']]);

           if (!empty($por)) {
                echo json_encode([
                    "status"  => false,
                    "theme"   => "error",
                    "message" => "Duplicate data found!,Please Check your Doc No and Doc Date."
                ]);
                return;
            }

            if ($post['qty_os'] > $post['qty_receipt']) {
                $status = 0;
            } else {
                $status = 1;
            }

            $send   = $this->crud->create('purchase_order_receipts', array_merge($post, ["receipt_id" => $this->receipt_id($post['receipt_no'])]));

            $this->db->where('po_no', $post['po_no']);
            $this->db->where('item_rm_id', $post['item_rm_id']);
            $this->db->update("purchase_orders", ["status" => $status]);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    public function delete()
    {
        $data = $this->input->post();
        $deletePurchaseOrderReceipts = $this->crud->delete('purchase_order_receipts', ["id" => $data['id']]);
        $deleteScanItemReceipts = $this->crud->delete('scan_item_receipts', ["receipt_id" => $data['receipt_id']]);
        //$updatePurchaseOrders = $this->crud->update('purchase_orders', ["po_no" => $data['po_no'], "item_rm_id" => $data['item_rm_id']], ["status" => 0]); // update memakai approval
        $updatePurchaseOrders = $this->db->update('purchase_orders', ["status" => 0], ["po_no" => $data['po_no'], "item_rm_id" => $data['item_rm_id']]); //update tidak approval
        echo $deletePurchaseOrderReceipts;
    }

    public function print_label_po($receipt_no)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $receipt_no = base64_decode($receipt_no);
        $receipt_datas = $this->crud->reads('purchase_order_receipts', [], ["receipt_no" => $receipt_no]);
        $receipt_ids = array();

        foreach($receipt_datas as $receipt_data){
            $receipt_id = $receipt_data->receipt_id;
            $po_receipt = $this->crud->read('purchase_order_receipts', [], ["receipt_id" => $receipt_id]);
            $qty_receipt = $po_receipt->qty_receipt;
            //Cek Label

            $po_receipt_label = $this->crud->reads('purchase_order_labels', [], ["receipt_id" => $receipt_id]);
            if (!$po_receipt_label) {

                for ($i = 0; $i < $po_receipt->qty_label; $i++) {
                    //Read Label ID
                    $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM purchase_order_labels WHERE receipt_id = '$receipt_id'");
                    $rowID = $sqlGetID->row();
                    $label = $rowID->kode;
                    if ($label == NULL) {
                        $autoID = $receipt_id . sprintf("%04s", $label + 1);
                    } else {
                        $urutan = (int) substr($label, -4);
                        $autoID = $receipt_id . sprintf("%04s", $urutan + 1);
                    }
                    
                    if ($qty_receipt > $po_receipt->qty_mpq) {
                        $qty = $po_receipt->qty_mpq;
                    } else {
                        $qty = $qty_receipt;
                    }
                    $date = new DateTime($po_receipt->receipt_date);
                    $p_month = $date->format('m'); 
                    $p_year = $date->format('y');
                    
                    //Simpan Label
                    $arrLabel = [
                        "receipt_id" => $po_receipt->receipt_id,
                        "receipt_no" => $receipt_no,
                        "label_no" => $autoID,
                        "qty" => $qty,
                        "p_month" => $p_month,
                        "p_year" => $p_year 
                    ];

                    $send = $this->crud->create('purchase_order_labels', $arrLabel);
                    $qty_receipt = ($qty_receipt - $po_receipt->qty_mpq);

                    $receipt_ids[] = $po_receipt->receipt_id;
                }
            }else{
                $receipt_ids[] = $receipt_id;
            }
        }
        
        $this->db->select('a.*, b.receipt_date, c.number, c.name, d.location, d.area, c.color, c.uom, b.lotno');
        $this->db->from('purchase_order_labels a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
        $this->db->join('item_rm c', 'b.item_rm_id = c.id');
        $this->db->join('warehouse_location_items d', 'd.item_rm_id = c.id', 'left');
        $this->db->join('item_familys e', 'c.item_family_id = e.id');
        $this->db->where('a.deleted', 0);
        //$this->db->where('a.status', 0);
        $this->db->where_in('a.receipt_id', $receipt_ids);
        $this->db->order_by('a.label_no', 'asc');
        $records = $this->db->get()->result_object();
        $html = '<html>
                    <head>
                        <title>' . $receipt_no . '</title>
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
                                            <small style="font-size:7px">Location</small><br>
                                            <small style="font-size:7px">Lot No. </small><b style="font-size:8px;">' . $record->lotno . '</b>
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th style="text-align:left">
                                            <div style="display: inline-block;">
                                                <small style="font-size:9px">Date :</small><br> 
                                                <b style="font-size:7px;">' . $record->receipt_date . '</b>
                                             
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
                                            <small style="font-size:4px">QC Passed By : ' . $this->session->username . '</small><br>
                                            <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="55"/>

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

    public function print_label($receipt_id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $receipt_id = base64_decode($receipt_id);
        $po_receipt = $this->crud->read('purchase_order_receipts', [], ["receipt_id" => $receipt_id]);
        $qty_receipt = $po_receipt->qty_receipt;

        //Cek Label
        $po_receipt_label = $this->crud->reads('purchase_order_labels', [], ["receipt_id" => $receipt_id]);
        if (!$po_receipt_label) {
            for ($i = 0; $i < $po_receipt->qty_label; $i++) {
                //Read Label ID
                $sqlGetID = $this->db->query("SELECT max(label_no) as kode FROM purchase_order_labels WHERE receipt_id = '$receipt_id'");
                $rowID = $sqlGetID->row();
                $label = $rowID->kode;
                if ($label == NULL) {
                    $autoID = $receipt_id . sprintf("%04s", $label + 1);
                } else {
                    $urutan = (int) substr($label, -4);
                    $autoID = $receipt_id . sprintf("%04s", $urutan + 1);
                }
                if ($qty_receipt > $po_receipt->qty_mpq) {
                    $qty = $po_receipt->qty_mpq;
                } else {
                    $qty = $qty_receipt;
                }
                
                $date = new DateTime($po_receipt->receipt_date);
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
                $send = $this->crud->create('purchase_order_labels', $arrLabel);
                $qty_receipt = ($qty_receipt - $po_receipt->qty_mpq);
            }
        }
        
        $this->db->select('a.*, b.receipt_date, c.number, c.name, d.location, d.area, c.color, c.uom, b.lotno');
        $this->db->from('purchase_order_labels a');
        $this->db->join('purchase_order_receipts b', 'a.receipt_id = b.receipt_id');
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
                                            <small style="font-size:7px">Location </small><b style="font-size:8px;">' . $record->location . '</b><br>
                                            <small style="font-size:7px">Lot No. </small><b style="font-size:10px;">' . $record->lotno . '</b>
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th style="text-align:left">
                                            <div style="display: inline-block;">
                                                <small style="font-size:9px">Date :</small><br> 
                                                <b style="font-size:7px;">' . $record->receipt_date . '</b>
                                             
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
                                            <small style="font-size:4px">QC Passed By : ' . $this->session->username . '</small><br>
                                            <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="55"/>
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

    public function print_receiving($receipt_no)
    {
        $purchase_order_receipt_total = $this->crud->reads('purchase_order_receipts', [], ["receipt_no" => base64_decode($receipt_no)]);
        $po_receipt = $this->crud->read('purchase_order_receipts', [], ["receipt_no" => base64_decode($receipt_no)]);
        $update = $this->crud->update('purchase_order_receipts', ["receipt_no" => base64_decode($receipt_no)], ["print" => 1]);
        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 20;
        $page = ceil(count($purchase_order_receipt_total) / $rows);
        //Generate QRcode
        $this->createQrcode($po_receipt->receipt_no, "assets/image/qrcode/");
        $this->db->select('a.*, b.number as supplier_id, b.name as supplier_name, c.number as item_rm_id, c.name as item_name, c.uom, d.name as item_familys_name, e.mpq, b.currency, g.location');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
        $this->db->join('warehouse_location_items g', 'a.item_rm_id = g.item_rm_id', 'left');
        $this->db->where('a.deleted', 0);
        // $this->db->where('a.status', 0);
        $this->db->where('a.receipt_no', base64_decode($receipt_no));
        $this->db->group_by('a.po_no');
        $this->db->group_by('a.supplier_id');
        $this->db->group_by('a.item_rm_id');
        $records = $this->db->get()->result_array();
        if ($records) {
            $html = '<html>
                        <head>
                            <title>' . $po_receipt->receipt_no . '</title>
                            <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                        </head>
                        <style>
                            body {
                                font-family: Arial, Helvetica, sans-serif;
                            }
                            #customers {
                                border-collapse: collapse;width: 100%;
                                font-size: 12px;
                            }
                            #customers td, #customers th {
                                border: 1px solid black;padding: 2px;
                            }
                            #customers th {
                                padding-top: 2px;
                                padding-bottom: 2px;
                                text-align: center;color: black;
                            }
                            @media screen {
                                .print {
                                    display: none !important;
                                }
                            }
    
                            @media print {
                                .noprint {
                                    display: none !important;
                                }
                            }
                        </style>
                        <body>
                            <div style="margin:20%;" class="noprint">
                                <center>
                                    <h1>Press CTRL + P for Print</h1>
                                    <p>Display pages for 20 rows</p>
                                    <p>Paper Size A5, Layout Landscape</p>
                                    <p>Margin Default, Scale 98</p>
                                </center>
                            </div>
                            <div class="print">';
            $no = 1;
            $hal = 1;
            $subtotal = 0;
            for ($i = 0; $i < $page; $i++) {
                $this->db->select('a.*, b.number as supplier_id, b.name as supplier_name, c.number as item_rm_number, c.id as item_rm_id,  
                c.name as item_name, c.uom, f.name as item_familys_name, e.mpq, b.currency, g.location, f.number as category_code, d.number as family_code, 
                h.request_name, c.item_category_id as category_id, c.item_family_id as family_id');
                $this->db->from('purchase_order_receipts a');
                $this->db->join('suppliers b', 'a.supplier_id = b.id');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id');
                $this->db->join('item_familys d', 'c.item_family_id = d.id');
                $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
                $this->db->join('item_categories f', 'c.item_category_id = f.id');
                $this->db->join('warehouse_location_items g', 'a.item_rm_id = g.item_rm_id', 'left');
                $this->db->join('purchase_orders h', 'a.po_no = h.po_no');
                $this->db->where('a.deleted', 0);
                // $this->db->where('a.status', 0);
                $this->db->where('a.receipt_no', base64_decode($receipt_no));
                $this->db->group_by('a.po_no');
                $this->db->group_by('a.supplier_id');
                $this->db->group_by('a.item_rm_id');
                $this->db->limit(20, ($i * 20));
                $records = $this->db->get()->result_array();

                $html .= '  <table style="width:100%;">
                                    <tr>
                                        <th width="10"><img src="' . $config->favicon . '" width="60" /></th>
                                        <td width="250" style="padding:10px;">
                                            <b style="font-size:14px;">' . $config->name . '</b><br>
                                            <span style="font-size:10px;">' . $config->address . '</span><br>
                                        </td>
                                        <th width="100" style="text-align:right;">
                                            <table style="width:100%; font-size:10px;">
                                                <tr>
                                                    <td width="50" rowspan="4"><img src="' . base_url('assets/image/qrcode/' . $po_receipt->receipt_no . '.png') . '" width="60"/></td>
                                                    <td width="60">Doc No</td>
                                                    <td width="5">:</td>
                                                    <td width="100">' . $config_iso->doc_receiving_note . '</td>
                                                </tr>
                                                <tr>
                                                    <td>Form</td>
                                                    <td>:</td>
                                                    <td>' . $config_iso->form_receiving_note . '</td>
                                                </tr>
                                                <tr>
                                                    <td>Print Date</td>
                                                    <td>:</td>
                                                    <td>' . date("Y-m-d H:i") . '</td>
                                                </tr>
                                                <tr>
                                                    <td>Print By</td>
                                                    <td>:</td>
                                                    <td>' . $this->session->name . '</td>
                                                </tr>
                                            </table>
                                        </th>
                                    </tr>
                                </table>
                                <div style="border: 1px solid black; width:100%;">
                                    <div style="padding:10px;">
                                        <center>
                                            <h3><u>GOOD RECEIVING NOTE</u></h3>
                                        </center>
                                        <table style="width:50%; font-size:12px; margin-bottom:10px; float:left;">
                                            <tr>
                                                <td width="100">Receipt No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$po_receipt->receipt_no . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Receipt Date</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$po_receipt->receipt_date . '</b></td>
                                            </tr>
                                        </table>
                                        <table style="width:50%; font-size:12px; margin-bottom:10px; float:left;">
                                            <tr>
                                                <td width="50">Supplier</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$records[0]['supplier_name'] . '</b></td>
                                            </tr>
                                            <tr>
                                                <td width="50">Doc. No</td>
                                                <td width="10">:</td>
                                                <td><b>' . @$records[0]['bc_document'] . '</b></td>
                                            </tr>
                                        </table>
    
                                        <table id="customers">
                                            <tr>
                                                <th>No</th>
                                                <th>PO No</th>
                                                <th>Product No</th>
                                                <th>Product Name</th>
                                                <th>Category</th>
                                                <th>Location</th>
                                                <th>MPQ</th>
                                                <th>Quantity</th>
                                                <th>Uom</th>
                                            </tr>';
                $no = 1;
                foreach ($records as $record) {

                    if (($record['category_id'] == 'C01' && $record['family_id'] == 'P27') || ($record['category_id'] == 'C07' && $record['family_id'] == 'P31')) {
                        $number = $record['item_rm_id'];
                    } else {
                        $number = $record['item_rm_number'];
                    }

                    $html .= '  <tr>
                    <td style="text-align:center">' . $no . '</td>
                    <td>' . $record['po_no'] . '</td>
                    <td>' . $number . '</td>
                    <td>' . $record['item_name'] . '</td>
                    <td>' . $record['item_familys_name'] . '</td>
                    <td>' . $record['location'] . '</td>
                    <td style="text-align:right">' . number_format($record['mpq'], 2) . '</td>
                    <td style="text-align:right">' . number_format($record['qty_receipt'], 2) . '</td>
                    <td>' . $record['uom'] . '</td>
                </tr>';
                    $no++;
                }

                if($record['category_code'] == 'RM' || $record['category_code'] == 'SB' && $record['family_code'] == 'WIPS' ){
                        $html .= '  </table>
                                <table id="customers" style="margin-top:20px;">
                                    <tr>
                                        <th rowspan="3" style="vertical-align:top; padding:20px;">Note : </th>
                                        <th width="200" style="text-align:center;">Approval QC</th>
                                        <th width="200" style="text-align:center;">Receipt By</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;"></th>
                                        <th style="height:80px;"></th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;"></th>
                                        <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    <script>window.print()</script>';
                    if (($i + 1) != $page) {
                        $html .= '<div style="page-break-after:always;"></div>';
                    }
                    $hal++;
                }else{
                        $html .= '  </table>
                                <table id="customers" style="margin-top:20px;">
                                    <tr>
                                        <th rowspan="3" style="vertical-align:top; padding:20px;">Note : </th>
                                        <th width="200" style="text-align:center;">Receipt By</th>
                                        <th width="200" style="text-align:center;">Entry By</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;"></th>
                                        <th style="height:80px;"></th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;">'. $record['request_name'] .'</th>
                                        <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    <script>window.print()</script>';
                    if (($i + 1) != $page) {
                        $html .= '<div style="page-break-after:always;"></div>';
                    }
                    $hal++;
                }
                
            }
            $html .= "</div></div><script>window.print()</script>";
            die($html);
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_receipts_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to   = $this->input->get('filter_to');
        $filter_supplier = $this->input->get('filter_supplier');
        $filter_po_no = $this->input->get('filter_po_no');
        $filter_part_no = $this->input->get('filter_part_no');
        $filter_receipt = $this->input->get('filter_receipt');
        $filter_doc_no = $this->input->get('filter_doc_no');
        $filter_categories = $this->input->get('filter_categories');
        $filter_division = $this->input->get('filter_division');
        
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('
            a.*, 
            b.number as supplier_id, 
            b.name as supplier_name, 
            c.number as item_rm_id, 
            c.name as item_name, 
            d.name as item_family_name, 
            b.currency, 
            c.uom, 
            f.number as category_code,
            SUM(pol.status) as total_scan
        ');
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join('item_familys d', 'c.item_family_id = d.id');
        $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_rm_id');
        $this->db->join('item_categories f', 'c.item_category_id = f.id','left');
        $this->db->join('purchase_orders g', 'a.po_no = g.po_no and a.item_rm_id = g.item_rm_id','left');
        $this->db->join('purchase_order_labels pol', 'a.receipt_id = pol.receipt_id', 'left'); // Join tabel purchase_order_labels
        $this->db->where('a.deleted', 0);

        if ($filter_from != "" and $filter_to != "") {
            $this->db->where('a.receipt_date >=', $filter_from);
            $this->db->where('a.receipt_date <=', $filter_to);
        }
        if ($filter_supplier != "") {
            $this->db->where('a.supplier_id', $filter_supplier);
        }
        if ($filter_part_no != "") {
            $this->db->where('a.item_rm_id', $filter_part_no);
        }
        if ($filter_receipt != "") {
            $this->db->where('a.receipt_no', $filter_receipt);
        }
        if ($filter_doc_no != "") {
            $this->db->where('a.bc_document', $filter_doc_no);
        }
        if ($filter_categories != "") {
            $this->db->where('c.item_category_id', $filter_categories);
        }
        $this->db->like('a.po_no', $filter_po_no);

        $this->db->group_by('a.receipt_id'); // Kelompokkan berdasarkan receipt_id untuk menghitung SUM
        $this->db->order_by('a.created_date', 'DESC');
        $this->db->order_by('a.receipt_date', 'DESC');

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
                                <small>PURCHASE ORDER RECEIPT</small>
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
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Receipt No</th>
                <th rowspan="2">Category</th>
                <th rowspan="2">Status POR</th>
                <th rowspan="2">Status Invoice</th>
                <th rowspan="2">Status Print GRN</th>
                <th rowspan="2">PO No</th>
                <th rowspan="2">Document</th>
                <th rowspan="2">Document Date</th>
                <th colspan="2" style="text-align:center;">Supplier</th>
                <th rowspan="2">Product No</th>
                <th rowspan="2">Product Name</th>
                <th rowspan="2">Qty</th>
                <th rowspan="2">UoM</th>
                <th rowspan="2">Currency</th>
                <th rowspan="2">Label</th>
            </tr>
            <tr>
                <th>ID</th>
                <th>Name</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {

            if ($data['total_scan'] == $data['qty_label']) {
                $status_por = 'CLOSED';
            } else {
                $status_por = 'OPEN';
            }
            
            if ($data['status'] == 1) {
                $status = 'CLOSED';
            } else {
                $status = 'OPEN';
            }

            if ($data['print'] == 1) {
                $print = 'CLOSED';
            } else {
                $print = 'OPEN';
            }

            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['receipt_no'] . '</td>
                        <td>' . $data['category_code'] . '</td>
                        <td>' . $status_por . '</td>
                        <td>' . $status . '</td>
                        <td>' . $print . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['bc_document'] . '</td>
                        <td>' . $data['bc_date'] . '</td>
                        <td>' . $data['supplier_id'] . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_name'] . '</td>
                        <td>' . number_format($data['qty_receipt'], 2) . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['currency'] . '</td>
                        <td>' . number_format($data['qty_label']) . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
