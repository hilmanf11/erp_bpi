<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Sales_orders extends CI_Controller
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
            $this->load->view('planning/sales_orders');
        } else {
            redirect('error_access');
        }
    }
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $sales_orders = $this->crud->reads("sales_orders", ["number" => $post], [], "", "created_date", "desc", ["number"]);
        echo json_encode($sales_orders);
    }
    public function readSalesOrders($status = 0)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $customer_id = $this->input->get("customer_id");
        $sales_orders = $this->crud->reads("sales_orders", ["number" => $post], ["customer_id" => $customer_id, "status" => $status], "", "created_date", "desc");
        echo json_encode($sales_orders);
    }
    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $customer_id = $this->input->get("customer_id");
        $number = $this->input->get("sales_order");
        $sales_orders = $this->crud->query("SELECT 
            a.item_id, b.number as item_number, 
            b.name as item_name, 
            (a.qty - SUM(coalesce(c.qty, 0))) as qty
        FROM sales_orders a 
        JOIN items b on a.item_id = b.id
        LEFT JOIN production_schedules c ON a.number = c.so_number and a.item_id = c.item_id
        WHERE b.number like '%$post%' and a.customer_id = '$customer_id' and a.number = '$number'
        GROUP BY a.item_id");
        echo json_encode($sales_orders);
    }
    public function number($customer, $trans_date)
    {
        $datenow    = "SO" . $customer . "-" . date("ymd", strtotime(base64_decode($trans_date)));
        $sqlGetID   = $this->db->query("SELECT max(`number`) as kode FROM sales_orders WHERE `number` like '%$datenow%'");
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

    public function datatables($details = "")
    {
        $filter_customers  = base64_decode($this->input->get('filter_customers'));
        $filter_items = base64_decode($this->input->get('filter_items'));
        $filter_type = base64_decode($this->input->get('filter_type'));
        $filter_number = base64_decode($this->input->get('filter_number'));

        if ($details == "") {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.number as customer_number, b.name as customer_name, b.type as customer_type, d.status as status_wo');
            $this->db->from('sales_orders a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('items c', 'a.item_id = c.id');
            $this->db->join('production_schedules d', 'a.number = d.so_number', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.customer_id', $filter_customers);
            $this->db->like('a.item_id', $filter_items);
            $this->db->like('a.number', $filter_number);
            $this->db->like('b.type', $filter_type);
            $this->db->order_by('a.status', 'ASC');
            $this->db->group_by('a.number');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
        } else {
            $number = base64_decode($this->input->get('number'));
            $this->db->select('a.*, c.number as item_number, c.name as item_name, b.currency, d.name as uom');
            $this->db->from('sales_orders a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('items c', 'a.item_id = c.id');
            $this->db->join('uom d', 'c.uom_id = d.id');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.number', $number);
            $this->db->like('a.customer_id', $filter_customers);
            $this->db->like('a.item_id', $filter_items);
            $this->db->like('b.type', $filter_type);
            $this->db->order_by('a.status', 'ASC');
            $totalRows = $this->db->count_all_results('', false);
        }
        //Get Data Array
        $records = $this->db->get()->result_array();
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datatable_updates()
    {
        $number = base64_decode($this->input->get('number'));
        $records = $this->crud->query("SELECT a.*, c.number as item_number, c.name as item_name, c.id as item_id, d.currency
            FROM sales_orders a
            JOIN items c on a.item_id = c.id
            JOIN customers d on a.customer_id = d.id
            WHERE a.number = '$number'
            GROUP BY c.number");
        echo json_encode($records);
    }

    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                $sales_orders = $this->crud->read('sales_orders', [], ["customer_id" => $post['customer_id'], "item_id" => $post['item_id'], "number" => $post['number']]);

                if (@$sales_orders->id != "") {
                    $send = $this->crud->update('sales_orders', ["customer_id" => $post['customer_id'], "item_id" => $post['item_id'], "number" => $post['number']], $post);
                    echo $send;
                } elseif ($post['qty'] == 0) {
                    echo json_encode(array("title" => "Qty 0", "message" => "Product No " . $post['item_id'] . " Qty is 0", "theme" => "error"));
                } else {
                    $send = $this->crud->create('sales_orders', $post);
                    echo $send;
                }
            } else {
                show_error(validation_errors());
            }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('sales_orders', $data);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_orders_$format.xls");
        }
        $filter_customers  = base64_decode($this->input->get('filter_customers'));
        $filter_items = base64_decode($this->input->get('filter_items'));
        $filter_type = base64_decode($this->input->get('filter_type'));
        $filter_number = base64_decode($this->input->get('filter_number'));
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as customer_id, b.name as customer_name, b.type as customer_type, c.number as item_id, c.name as item_name, b.currency');
        $this->db->from('sales_orders a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->like('a.customer_id', $filter_customers);
        $this->db->like('a.item_id', $filter_items);
        $this->db->like('a.number', $filter_number);
        $this->db->like('b.type', $filter_type);
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
                                <small>SALES ORDER</small>
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
                <th>Customer PO No</th>
                <th>Customer No</th>
                <th>Customer Name</th>
                <th>Sales Order No</th>
                <th>Sales Order Date</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Disc %</th>
                <th>Total Price</th>
                <th>Delivery Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['customer_po'] . '</td>
                            <td>' . $data['customer_id'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['item_id'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . number_format($data['qty'], 2) . '</td>
                            <td>' . number_format($data['price'], 4) . '</td>
                            <td>' . number_format($data['discount'], 2) . '</td>
                            <td>' . number_format($data['total'], 4) . '</td>
                            <td>' . $data['delivery'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
