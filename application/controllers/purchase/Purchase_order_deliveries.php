<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Purchase_order_deliveries extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('purchase/purchase_order_deliveries');
        } else {
            redirect('error_access');
        }
    }

    public function readRequestnumber()
    {
        $records = $this->crud->query("SELECT request_no, request_date, request_name FROM purchase_requests WHERE `deleted` = '0' GROUP BY request_no ORDER BY created_date desc");// WHERE `status` = '0'
        echo json_encode($records);
    }

    public function readProductNo($supplier_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $send = $this->crud->query("SELECT b.* FROM purchase_orders a 
        JOIN item_rm b ON a.item_rm_id = b.id 
        WHERE a.supplier_id = '$supplier_id' 
        GROUP BY a.item_rm_id");

        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_supplier_id = @base64_decode($get['filter_supplier_id']);
            $filter_request_no = @base64_decode($get['filter_request_no']);
            $filter_po_no = @base64_decode($get['filter_po_no']);
            $filter_item_rm = @base64_decode($get['filter_item_rm']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, b.name as supplier_name , b.currency");
            $this->db->from('purchase_orders a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.po_date >=', $filter_from);
                $this->db->where('a.po_date <=', $filter_to);
            }
            $this->db->like('a.supplier_id', $filter_supplier_id);
            $this->db->like('a.request_no', $filter_request_no);
            $this->db->like('a.po_no', $filter_po_no);
            $this->db->like('a.item_rm_id', $filter_item_rm);
            $this->db->group_by('a.po_no');
            $this->db->order_by('a.status', 'ASC');
            $this->db->order_by('a.po_no', 'DESC');
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

    //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $po_no = base64_decode($this->input->get('po_no'));

            $this->db->select('a.*, b.number as item_rm_number, b.name as item_rm_name, COALESCE(c.qty_del, 0) as qty_del, (a.qty - COALESCE(c.qty_del, 0)) as qty_os , b.uom , d.currency, d.name as supplier_name');
            $this->db->from('purchase_orders a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('suppliers d', 'a.supplier_id = d.id');
            $this->db->join("(SELECT po_no, item_rm_id, supplier_id, SUM(qty) as qty_del 
            FROM purchase_order_deliveries GROUP BY po_no, item_rm_id, supplier_id) c", "a.po_no = c.po_no and a.item_rm_id = c.item_rm_id and a.supplier_id = c.supplier_id", "left");
            $this->db->where('a.po_no', $po_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //purchase_order_deliveries
    public function datatables2($supplier_id, $po_no, $item_rm_id)
    {
        $supplier_id = base64_decode($supplier_id);
        $po_no = base64_decode($po_no);
        $item_rm_id = base64_decode($item_rm_id);

        //Select Query
        $this->db->select('a.*, b.qty as so_qty');
        $this->db->from('purchase_order_deliveries a');
        $this->db->join('purchase_orders b', 'a.po_no = b.po_no and a.item_rm_id = b.item_rm_id');
        $this->db->where('a.supplier_id', $supplier_id);
        $this->db->where('a.po_no', $po_no);
        $this->db->where('a.item_rm_id', $item_rm_id);
        $this->db->order_by('trans_date', 'asc');
        $records = $this->db->get()->result_array();

        $balance = 0;
        $qty = 0;
        $data = array();
        foreach ($records as $record) {
            $qty += $record['qty'];
            $balance = $record['so_qty'] - $qty;
            $data[] = array(
                "id" => $record['id'],
                "supplier_id" => $supplier_id,
                "po_no" => $po_no,
                "item_rm_id" => $item_rm_id,
                "trans_date" => $record['trans_date'],
                "so_qty" => $record['so_qty'],
                "qty" => $record['qty'],
                "remain_qty" => $balance,
                "status" => $record['status'],
                "created_by" => $record['created_by'],
                "created_date" => $record['created_date'],
            );
        }

        //Mapping Data
        $result['total'] = count(@$data);
        $result = array_merge($result, ['rows' => $data]);
        echo json_encode($result);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $po_no =  $post['po_no'];
            $item_rm_id =  $post['item_rm_id'];
            $purchase_orders = $this->crud->read("purchase_orders", [], ["po_no" => $po_no, "item_rm_id" => $item_rm_id]);
            $purchase_order_deliveries = $this->crud->read("purchase_order_deliveries", [], ["po_no" => $po_no, "item_rm_id" => $item_rm_id, "trans_date" => $post['trans_date']]);
            $purchase_order_deliveries_total = $this->crud->query("SELECT SUM(qty) as total FROM purchase_order_deliveries WHERE po_no='$po_no' and item_rm_id = '$item_rm_id' GROUP BY po_no, item_rm_id");

            $qty_po = $purchase_orders->qty;
            if ($qty_po >= (@$purchase_order_deliveries_total[0]->total + $post['qty'])) {
                if (empty($purchase_order_deliveries->trans_date)) {
                    $send = $this->crud->create('purchase_order_deliveries', $post);
                    echo $send;
                } else {
                    show_error("Delivery Date Has Been Created Please Choose Another Date");
                }
            } else {
                show_error("Qty is greater than the Sales Order");
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('purchase_order_deliveries', $data);
        echo $send;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=purchase_order_deliveries_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_supplier_id = @base64_decode($get['filter_supplier_id']);
        $filter_request_no = @base64_decode($get['filter_request_no']);
        $filter_po_no = @base64_decode($get['filter_po_no']);
        $filter_item_rm = @base64_decode($get['filter_item_rm']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.name as supplier_name, c.number as item_rm_number, c.name as item_rm_name, COALESCE(d.qty_del, 0) as qty_del, (a.qty - COALESCE(d.qty_del, 0)) as qty_os, c.uom");
        $this->db->from('purchase_orders a');
        $this->db->join('suppliers b', 'a.supplier_id = b.id');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id');
        $this->db->join("(SELECT po_no, item_rm_id, supplier_id, SUM(qty) as qty_del 
            FROM purchase_order_deliveries GROUP BY po_no, item_rm_id, supplier_id) d", "a.po_no = d.po_no and a.item_rm_id = d.item_rm_id and a.supplier_id = d.supplier_id", "left");
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.po_date >=', $filter_from);
            $this->db->where('a.po_date <=', $filter_to);
        }
        $this->db->like('a.supplier_id', $filter_supplier_id);
        $this->db->like('a.request_no', $filter_request_no);
        $this->db->like('a.po_no', $filter_po_no);
        $this->db->like('a.item_rm_id', $filter_item_rm);
        $this->db->order_by('a.po_no', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#supplier_items {border-collapse: collapse;width: 100%;font-size: 12px;}#supplier_items td, #supplier_items th {border: 1px solid #ddd;padding: 2px;}#supplier_items tr:nth-child(even){background-color: #f2f2f2;}#supplier_items tr:hover {background-color: #ddd;}#supplier_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>' . $config->description . '</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br>
            <div style="float: centet; font-size: 16px; text-align: center;">
                <h3>SALES ORDER SCHEDULE DELIVERY</h3>
            </div>
        </center>

        <table id="supplier_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Supplier Name</th>
                <th>Purchase Request No</th>
                <th>Purchase Order No</th>
                <th>Purchase Order Date</th>
                <th>Delivery Date</th>
                <th>Remarks</th>
                <th>Part ID</th>
                <th>Part No</th>
                <th>Part Name</th>
                <th>Uom</th>
                <th>Qty</th>
                <th>Delivery</th>
                <th>Outstanding</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['supplier_name'] . '</td>
                        <td>' . $data['request_no'] . '</td>
                        <td>' . $data['po_no'] . '</td>
                        <td>' . $data['po_date'] . '</td>
                        <td>' . $data['delivery_date'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['item_rm_id'] . '</td>
                        <td>' . $data['item_rm_number'] . '</td>
                        <td>' . $data['item_rm_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['qty_del'] . '</td>
                        <td>' . $data['qty_os'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
