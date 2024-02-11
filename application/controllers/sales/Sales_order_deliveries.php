<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Sales_order_deliveries extends CI_Controller
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
            $this->load->view('sales/sales_order_deliveries');
        } else {
            redirect('error_access');
        }
    }

    public function readSalesOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT sales_order_no FROM sales_orders WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readCustomerOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM sales_orders WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readProductNo($customer_id)
    {
        $send = $this->crud->query("SELECT b.* FROM sales_orders a JOIN item_fg b ON a.item_fg_id = b.id WHERE a.customer_id = '$customer_id' GROUP BY a.item_fg_id");

        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_customer_id = @base64_decode($get['filter_customer_id']);
            $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
            $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
            $filter_item_fg = @base64_decode($get['filter_item_fg']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, b.name as customer_name");
            $this->db->from('sales_orders a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('a.sales_order_date >=', $filter_from);
                $this->db->where('a.sales_order_date <=', $filter_to);
            }
            $this->db->like('a.customer_id', $filter_customer_id);
            $this->db->like('a.sales_order_no', $filter_sales_order_no);
            $this->db->like('a.customer_order_no', $filter_customer_order_no);
            $this->db->like('a.item_fg_id', $filter_item_fg);
            $this->db->group_by('a.sales_order_no');
            $this->db->order_by('a.status', 'ASC');
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
            $sales_order_no = base64_decode($this->input->get('sales_order_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, COALESCE(c.qty_del, 0) as qty_del, (a.qty - COALESCE(c.qty_del, 0)) as qty_os');
            $this->db->from('sales_orders a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join("(SELECT sales_order_no, item_fg_id, customer_id, SUM(qty) as qty_del 
            FROM sales_order_deliveries GROUP BY sales_order_no, item_fg_id, customer_id) c", "a.sales_order_no = c.sales_order_no and a.item_fg_id = c.item_fg_id and a.customer_id = c.customer_id", "left");
            $this->db->where('a.sales_order_no', $sales_order_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //sales_order_deliveries
    public function datatables2($customer_id, $sales_order_no, $item_fg_id)
    {
        $customer_id = base64_decode($customer_id);
        $sales_order_no = base64_decode($sales_order_no);
        $item_fg_id = base64_decode($item_fg_id);

        //Select Query
        $this->db->select('a.*, b.qty as so_qty');
        $this->db->from('sales_order_deliveries a');
        $this->db->join('sales_orders b', 'a.sales_order_no = b.sales_order_no and a.item_fg_id = b.item_fg_id');
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('a.sales_order_no', $sales_order_no);
        $this->db->where('a.item_fg_id', $item_fg_id);
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
                "customer_id" => $customer_id,
                "sales_order_no" => $sales_order_no,
                "item_fg_id" => $item_fg_id,
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
            $sales_order_no =  $post['sales_order_no'];
            $item_fg_id =  $post['item_fg_id'];
            $sales_orders = $this->crud->read("sales_orders", [], ["sales_order_no" => $sales_order_no, "item_fg_id" => $item_fg_id]);
            $sales_order_deliveries = $this->crud->read("sales_order_deliveries", [], ["sales_order_no" => $sales_order_no, "item_fg_id" => $item_fg_id, "trans_date" => $post['trans_date']]);
            $sales_order_deliveries_total = $this->crud->query("SELECT SUM(qty) as total FROM sales_order_deliveries WHERE sales_order_no='$sales_order_no' and item_fg_id = '$item_fg_id' GROUP BY sales_order_no, item_fg_id");

            $qty_so = $sales_orders->qty;
            if ($qty_so >= (@$sales_order_deliveries_total[0]->total + $post['qty'])) {
                if (empty($sales_order_deliveries->trans_date)) {
                    $send = $this->crud->create('sales_order_deliveries', $post);
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
        $send = $this->crud->delete('sales_order_deliveries', $data);
        echo $send;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=sales_orders_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_customer_id = @base64_decode($get['filter_customer_id']);
        $filter_sales_order_no = @base64_decode($get['filter_sales_order_no']);
        $filter_customer_order_no = @base64_decode($get['filter_customer_order_no']);
        $filter_item_fg = @base64_decode($get['filter_item_fg']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.name as customer_name, c.number as item_fg_number, c.name as item_fg_name, COALESCE(d.qty_del, 0) as qty_del, (a.qty - COALESCE(d.qty_del, 0)) as qty_os");
        $this->db->from('sales_orders a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join("(SELECT sales_order_no, item_fg_id, customer_id, SUM(qty) as qty_del 
            FROM sales_order_deliveries GROUP BY sales_order_no, item_fg_id, customer_id) d", "a.sales_order_no = d.sales_order_no and a.item_fg_id = d.item_fg_id and a.customer_id = d.customer_id", "left");
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.sales_order_date >=', $filter_from);
            $this->db->where('a.sales_order_date <=', $filter_to);
        }
        $this->db->like('a.customer_id', $filter_customer_id);
        $this->db->like('a.sales_order_no', $filter_sales_order_no);
        $this->db->like('a.customer_order_no', $filter_customer_order_no);
        $this->db->like('a.item_fg_id', $filter_item_fg);
        $this->db->order_by('a.sales_order_no', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customer_items {border-collapse: collapse;width: 100%;font-size: 12px;}#customer_items td, #customer_items th {border: 1px solid #ddd;padding: 2px;}#customer_items tr:nth-child(even){background-color: #f2f2f2;}#customer_items tr:hover {background-color: #ddd;}#customer_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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

        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Customer Name</th>
                <th>Customer Order No</th>
                <th>Sales Order No</th>
                <th>Sales Order Date</th>
                <th>Division</th>
                <th>Delivery Date</th>
                <th>Remarks</th>
                <th>Product ID</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Uom</th>
                <th>Qty</th>
                <th>Delivery</th>
                <th>Outstanding</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['customer_name'] . '</td>
                        <td>' . $data['customer_order_no'] . '</td>
                        <td>' . $data['sales_order_no'] . '</td>
                        <td>' . $data['sales_order_date'] . '</td>
                        <td>' . $data['division'] . '</td>
                        <td>' . $data['delivery_date'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $data['item_fg_id'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
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
