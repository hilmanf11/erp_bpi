<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_invoices extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/purchase_invoices');
        } else {
            redirect('error_access');
        }
    }

    public function reads($number)
    {
        $number = base64_decode($number);
        $this->db->select('a.*, c.id as item_id, c.number as item_number, c.name as item_name, d.name as uom, b.currency');
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join('uom d', 'c.uom_id = d.id');
        // $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_id');
        // $this->db->join('purchase_orders f', 'a.po_no = f.po_no and b.id = f.supplier_id and c.id = f.item_id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.number', $number);
        $this->db->group_by('a.por_no');
        $this->db->group_by('a.item_id');
        $this->db->order_by('c.number', 'asc');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function readDueDate($trans_date, $payment_term)
    {
        $due_date = date('Y-m-t', strtotime('+' . $payment_term . ' days', strtotime(base64_decode($trans_date))));
        die($due_date);
    }

    public function readReceipt($type = "purchase")
    {
        $supplier_id = $this->input->get('supplier_id');
        $item_family_id = $this->input->get('item_family_id');

        if($type == "purchase"){
            $dp = "and d.total_dp = 0";
        }else{
            $dp = "and d.total_dp > 0";
        }

        $records = $this->crud->query("SELECT a.receipt_no, d.taxes, d.total_dp
            FROM purchase_order_receipts a
            JOIN items b ON a.item_id = b.id
            JOIN item_familys c ON b.item_family_id = c.id
            JOIN purchase_orders d ON a.po_no = d.po_no
            WHERE a.supplier_id = '$supplier_id' and c.id = '$item_family_id' and a.status = '0' $dp
            GROUP BY a.receipt_no 
            ORDER BY a.created_date desc");
        echo json_encode($records);
    }

    public function readPurchaseInvoice($item_family)
    {
        $data = $this->crud->query("SELECT DISTINCT `number` FROM purchase_invoices WHERE `status` = '0' and family_id = '$item_family' ORDER BY `number` ASC");
        echo json_encode($data);
    }

    public function readPurchaseReceipt($item_family)
    {
        $data = $this->crud->query("SELECT DISTINCT `por_no` FROM purchase_invoices WHERE `status` = '0' and family_id = '$item_family' ORDER BY `por_no` ASC");
        echo json_encode($data);
    }

    public function readPurchaseOrder($item_family)
    {
        $data = $this->crud->query("SELECT DISTINCT `po_no` FROM purchase_invoices WHERE `status` = '0' and family_id = '$item_family' ORDER BY `po_no` ASC");
        echo json_encode($data);
    }

    public function readInvoice($item_family)
    {
        $data = $this->crud->query("SELECT DISTINCT `invoice_no` FROM purchase_invoices WHERE `status` = '0' and family_id = '$item_family' ORDER BY `invoice_no` ASC");
        echo json_encode($data);
    }

    public function number($trans_date)
    {
        $datenow    = "PI-" . date("Ymd", strtotime(base64_decode($trans_date)));
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM purchase_invoices WHERE `number` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo $datenow . "-" . $autoID;
    }

    public function numberInvoice($type)
    {
        if($type == "dp"){
            $datenow    = "INVWDP";
        }else{
            $datenow    = "INVTMP";
        }
        
        echo $datenow."-".time();
    }

    public function datatablesTemp()
    {
        $por_no = base64_decode($this->input->get('por_no'));
        $por_no_ex = explode(",", $por_no);

        $this->db->select("a.receipt_no as por_no, a.po_no, c.id as item_id, c.number as item_number, c.name as item_name, d.name as uom, b.currency, 
            SUM(a.qty_receipt) as qty, f.price, f.discount, 'IDR' as currency_local,
            ((SUM(a.qty_receipt) * f.price) - (SUM(a.qty_receipt) * f.price) * (f.discount / 100)) as total,
            (CASE WHEN g.selling is null THEN ((SUM(a.qty_receipt) * f.price) - (SUM(a.qty_receipt) * f.price) * (f.discount / 100)) ELSE
            ((SUM(a.qty_receipt) * (f.price * g.selling)) - (SUM(a.qty_receipt) * (f.price * g.selling)) * (f.discount / 100)) END) as total_local");
        $this->db->from('purchase_order_receipts a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join('uom d', 'c.uom_id = d.id');
        $this->db->join('supplier_items e', 'b.id = e.supplier_id and c.id = e.item_id');
        $this->db->join('purchase_orders f', 'a.po_no = f.po_no and b.id = f.supplier_id and c.id = f.item_id');
        $this->db->join('exchange_rates g', "b.currency = g.currency_from and g.currency_to = 'IDR'", 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where_in('a.receipt_no', $por_no_ex);
        $this->db->group_by('a.po_no');
        $this->db->group_by('a.item_id');
        $this->db->order_by('a.receipt_no', 'asc');
        $records = $this->db->get()->result_array();

        $total_sub = 0;
        $id = 1;
        foreach ($records as $record) {
            $total_sub += $record['total_local'];
            $obj[] = array(
                "no_id" => $id,
                "por_no" => $record['por_no'],
                "po_no" => $record['po_no'],
                "item_id" => $record['item_id'],
                "item_number" => $record['item_number'],
                "item_name" => $record['item_name'],
                "uom" => $record['uom'],
                "currency" => $record['currency'],
                "currency_local" => $record['currency_local'],
                "qty" => $record['qty'],
                "discount" => $record['discount'],
                "price" => $record['price'],
                "total" => $record['total'],
                "total_local" => $record['total_local']
            );

            $id++;
        }

        $arr['rows'] = $obj;
        $arr['total_sub'] = round($total_sub, 4);
        die(json_encode($arr));
    }

    public function datatablesTemp2()
    {
        $po_no = base64_decode($this->input->get('po_no'));

        $this->db->select("a.po_no, a.po_date, a.po_name, a.item_id, b.number as item_number, b.name as item_name, a.qty, c.name as uom, 
        e.currency, 'IDR' as currency_local, a.price, 
        (a.qty * a.price) as total,
        (CASE WHEN g.selling is null THEN (a.qty * a.price) ELSE
        (a.qty * (a.price * g.selling)) END) as total_local");
        $this->db->from('purchase_order_others a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('uom c', 'b.uom_id = c.id');
        $this->db->join('supplier_items d', 'a.supplier_id = d.supplier_id and a.item_id = d.item_id');
        $this->db->join('suppliers e', 'a.supplier_id = e.id');
        $this->db->join('exchange_rates g', "e.currency = g.currency_from and g.currency_to = 'IDR'", 'left');
        $this->db->where('a.status', 0);
        $this->db->like('a.po_no', $po_no);
        $this->db->group_by('a.item_id');
        $this->db->order_by('b.number', 'ASC');
        $records = $this->db->get()->result_array();

        $total_sub = 0;
        foreach ($records as $record) {
            $total_sub += $record['total'];
            $obj[] = array(
                "por_no" => "-",
                "po_no" => $record['po_no'],
                "item_id" => $record['item_id'],
                "item_number" => $record['item_number'],
                "item_name" => $record['item_name'],
                "uom" => $record['uom'],
                "currency" => $record['currency'],
                "currency_local" => $record['currency_local'],
                "qty" => $record['qty'],
                "price" => $record['price'],
                "total" => $record['total'],
                "total_local" => $record['total_local']
            );
        }

        $arr['rows'] = $obj;
        $arr['total_sub'] = round($total_sub, 4);
        die(json_encode($arr));
    }

    public function datatables($details = "")
    {
        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_family_id = base64_decode($this->input->get('filter_family_id'));
        $filter_purchase_invoice = base64_decode($this->input->get('filter_purchase_invoice'));
        $filter_purchase_receipt = base64_decode($this->input->get('filter_purchase_receipt'));
        $filter_purchase_order = base64_decode($this->input->get('filter_purchase_order'));
        $filter_status_supplier = base64_decode($this->input->get('filter_status_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        if ($details == "") {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as supplier_name, a.invoice_no as status_invoice');
            $this->db->from('purchase_invoices a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            if ($filter_type == "PID") {
                $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
            } elseif ($filter_type == "PAY") {
                $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
            }
            $this->db->like('a.family_id', $filter_family_id);
            $this->db->like('a.number', $filter_purchase_invoice);
            $this->db->like('a.por_no', $filter_purchase_receipt);
            $this->db->like('a.po_no', $filter_purchase_order);
            $this->db->like('a.invoice_no', $filter_status_supplier);
            $this->db->like('a.invoice_no', $filter_invoice_no);
            $this->db->like('a.status', $filter_status);
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->group_by('a.number');
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
        } else {
            $number = base64_decode($this->input->get('number'));

            $this->db->select('a.*');
            $this->db->from('purchase_invoices a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->where('a.number', $number);
            $this->db->group_by('a.por_no');
            $this->db->group_by('a.item_id');
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
        }
        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $purchase_invoices = $this->crud->read('purchase_invoices', [], ["por_no" => $post['por_no'], "item_id" => $post['item_id'], "supplier_id" => $post['supplier_id']]);

                if (@$purchase_invoices->id != "") {
                    $send = $this->crud->update('purchase_invoices', ["por_no" => $post['por_no'], "item_id" => $post['item_id'], "supplier_id" => $post['supplier_id']], $post);
                    echo $send;
                } else {
                    $send = $this->crud->create('purchase_invoices', $post);
                    if ($send) {
                        if($post['por_no'] != "-"){
                            if($post['type'] != "dp"){
                                $update = $this->crud->update('purchase_order_receipts', ["receipt_no" => $post['por_no'], "po_no" => $post['po_no'], "item_id" => $post['item_id'], "supplier_id" => $post['supplier_id']], ["status" => 1]);
                            }
                        }else{
                            $update = $this->crud->update('purchase_order_others', ["po_no" => $post['po_no'], "item_id" => $post['item_id'], "supplier_id" => $post['supplier_id']], ["status" => 1]);
                        }
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

    public function update()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $send = $this->crud->update('purchase_invoices', ["number" => $post['number']], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();

        $purchase_invoices = $this->crud->reads("purchase_invoices", [], ["number" => $data['number']]);
        foreach ($purchase_invoices as $purchase_invoice) {
            if($purchase_invoice->por_no != "-"){
                $this->crud->update("purchase_order_receipts", [
                    "receipt_no" => $purchase_invoice->por_no,
                    "po_no" => $purchase_invoice->po_no,
                    "item_id" => $purchase_invoice->item_id,
                    "supplier_id" => $purchase_invoice->supplier_id
                ], ["status" => 0]);
            }else{
                $this->crud->update("purchase_order_others", [
                    "po_no" => $purchase_invoice->po_no,
                ], ["status" => 0]);
            }
        }

        $send = $this->crud->delete('purchase_invoices', $data);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_invoices_$format.xls");
        }

        $filter_type  = base64_decode($this->input->get('filter_type'));
        $filter_trans_date_from = base64_decode($this->input->get('filter_trans_date_from'));
        $filter_trans_date_to = base64_decode($this->input->get('filter_trans_date_to'));
        $filter_due_date_from = base64_decode($this->input->get('filter_due_date_from'));
        $filter_due_date_to = base64_decode($this->input->get('filter_due_date_to'));
        $filter_family_id = base64_decode($this->input->get('filter_family_id'));
        $filter_purchase_invoice = base64_decode($this->input->get('filter_purchase_invoice'));
        $filter_purchase_receipt = base64_decode($this->input->get('filter_purchase_receipt'));
        $filter_purchase_order = base64_decode($this->input->get('filter_purchase_order'));
        $filter_status_supplier = base64_decode($this->input->get('filter_status_supplier'));
        $filter_invoice_no = base64_decode($this->input->get('filter_invoice_no'));
        $filter_status = base64_decode($this->input->get('filter_status'));

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as supplier_name');
        $this->db->from('purchase_invoices a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        if ($filter_type == "PID") {
            $this->db->where("a.trans_date between '$filter_trans_date_from' and '$filter_trans_date_to'");
        } elseif ($filter_type == "PAY") {
            $this->db->where("a.due_date between '$filter_due_date_from' and '$filter_due_date_to'");
        }
        $this->db->like('a.family_id', $filter_family_id);
        $this->db->like('a.number', $filter_purchase_invoice);
        $this->db->like('a.por_no', $filter_purchase_receipt);
        $this->db->like('a.po_no', $filter_purchase_order);
        $this->db->like('a.invoice_no', $filter_status_supplier);
        $this->db->like('a.invoice_no', $filter_invoice_no);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.status', 'ASC');
        $this->db->order_by('a.trans_date', 'DESC');
        $this->db->group_by('a.number');
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
                                <small>REPORT PURCHASE INVOICING</small><br>
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="float: right; font-size: 12px; text-align: right;">
                    Print Date ' . date("d M Y H:i:s") . ' <br>
                    Print By ' . $this->session->username . '  
                </div>
            </center>
            <br><br><br><br>
            
            <table id="customers" border="1">
                <tr>
                    <th width="20">No</th>
                    <th>Purchase Invoice No</th>
                    <th>Invoice No</th>
                    <th>Supplier Name</th>
                    <th>Trans Date</th>
                    <th>Due Date</th>
                    <th>Payment Term</th>
                    <th>Sub Total</th>
                    <th>VAT</th>
                    <th>PPH 23</th>
                    <th>Grand Total</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $number = $data['number'];

            $this->db->select('a.*');
            $this->db->from('purchase_invoices a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->where('a.number', $number);
            $this->db->group_by('a.por_no');
            $this->db->group_by('a.item_id');
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.trans_date', 'DESC');
            $details = $this->db->get()->result_array();

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['invoice_no'] . '</td>
                            <td>' . $data['supplier_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['due_date'] . '</td>
                            <td>' . $data['payment_term'] . '</td>
                            <td style="text-align:right;">' . number_format($data['total_sub'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_vat'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_pph'], 4) . '</td>
                            <td style="text-align:right;">' . number_format($data['total_grand'], 4) . '</td>
                        </tr>';
            $html .= '  <tr>
                            <td colspan="11" style="background:#D1FFC6;"><b>DETAIL OF ' . $data['po_no'] . '</b></td>
                        </tr>
                        <tr>
                            <th width="20"></th>
                            <th>POR No</th>
                            <th>PO No</th>
                            <th>Created By</th>
                            <th>Product No</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>UoM</th>
                            <th>Currency</th>
                            <th>Unit Price</th>
                            <th>Amount</th>
                        </tr>';
            foreach ($details as $detail) {
                $html .= '  <tr>
                                <td></td>
                                <td>' . $detail['por_no'] . '</td>
                                <td>' . $detail['po_no'] . '</td>
                                <td >' . $detail['created_by'] . '</td>
                                <td>' . $detail['item_no'] . '</td>
                                <td>' . $detail['item_name'] . '</td>
                                <td style="text-align:right">' . number_format($detail['qty'], 2) . '</td>
                                <td>' . $detail['uom'] . '</td>
                                <td>' . $detail['currency'] . '</td>
                                <td style="text-align:right">' . number_format($detail['price'], 2) . '</td>
                                <td style="text-align:right">' . number_format($detail['total'], 4)  . '</td>
                            </tr>';
            }
            $no++;
        }
        $html .= '</table>';
        $html .= '  <table id="customers" style="margin-top:20px; width:50%;">
                        <tr>
                            <th width="200" style="text-align:center;">Approval By</th>
                            <th width="200" style="text-align:center;">Dept Manager</th>
                            <th width="200" style="text-align:center;">Created By</th>
                        </tr>
                        <tr>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                            <th style="height:80px;"></th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;">'.$this->session->name.'</th>
                        </tr>
                    </table></body></html>';
        echo $html;
    }
}
