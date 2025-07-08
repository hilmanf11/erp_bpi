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

    public function readSalesOrders()
    {
        $send = $this->crud->query("SELECT DISTINCT sales_order_no FROM sales_orders WHERE `status` = '0'");
        echo json_encode($send);
    }

    public function readCustomerOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM sales_orders WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function readCustomerOrders()
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM sales_orders WHERE `status` = '0'");
        echo json_encode($send);
    }

    public function readProductNo($customer_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.* FROM sales_orders a JOIN item_fg b ON a.item_fg_id = b.id WHERE a.customer_id = '$customer_id' and b.number like '%$post%' GROUP BY a.item_fg_id");

        echo json_encode($send);
    }

    public function readProductNos()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.* FROM sales_orders a JOIN item_fg b ON a.item_fg_id = b.id WHERE a.status = '0' and b.number like '%$post%' GROUP BY a.item_fg_id");

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
            $filter_division = @base64_decode($get['filter_division']);

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

            if ($filter_division != "") {
                $this->db->where('a.division', $filter_division);
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
        $this->db->select('a.*, b.qty as so_qty, COALESCE(c.qty_del,0) as qty_do');
        $this->db->from('sales_order_deliveries a');
        $this->db->join('sales_orders b', 'a.sales_order_no = b.sales_order_no and a.item_fg_id = b.item_fg_id');
        $this->db->join("(SELECT sales_order_no, item_fg_id, delivery_date, COALESCE(SUM(qty_del),0) as qty_del FROM delivery_orders GROUP BY sales_order_no, item_fg_id, delivery_date) c", 'a.sales_order_no = c.sales_order_no and a.item_fg_id = c.item_fg_id and a.trans_date = c.delivery_date','left');
        $this->db->where('a.customer_id', $customer_id);
        $this->db->where('a.sales_order_no', $sales_order_no);
        $this->db->where('a.item_fg_id', $item_fg_id);
        $this->db->group_by('a.id');
        $this->db->order_by('a.trans_date', 'asc');
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
                "qty_do" => $record['qty_do'],
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

            if (round($qty_so) >= round(@$sales_order_deliveries_total[0]->total + $post['qty'])) {
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

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $sales_order_no =  $post['sales_order_no'];
            $item_fg_id =  $post['item_fg_id'];
            // var_dump($post);
            // die;

            $sales_orders = $this->crud->read("sales_orders", [], ["sales_order_no" => $sales_order_no, "item_fg_id" => $item_fg_id]);
            $sales_order_deliveries = $this->crud->read("sales_order_deliveries", [], ["sales_order_no" => $sales_order_no, "item_fg_id" => $item_fg_id, "trans_date" => $post['trans_date']]);
            $sales_order_deliveries_total = $this->crud->query("SELECT SUM(qty) as total FROM sales_order_deliveries WHERE sales_order_no='$sales_order_no' and item_fg_id = '$item_fg_id' and id != '$id' GROUP BY sales_order_no, item_fg_id");
            $qty_so = $sales_orders->qty;

            // var_dump($qty_so);
            // var_dump($sales_order_deliveries_total[0]->total);
            // var_dump($post['qty']);
            // die;

            if (round($qty_so) >= round(@$sales_order_deliveries_total[0]->total + $post['qty'])) {
                $send = $this->crud->update('sales_order_deliveries', ["id" => $id], $post);
                echo $send;
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
                'sales_order_no' => $data->val($i, 2),
                'number' => $data->val($i, 3),
                'trans_date' => $data->val($i, 4),
                'qty' => $data->val($i, 5)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/delivery_schedules.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/delivery_schedules.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/delivery_schedules.txt";
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
    //upload data tanpa sortir trans date
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');

            //Cek Process Number          //table       //field        //field excel
            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['number']]);
            $sales_orders = $this->crud->read('sales_orders', [], ["sales_order_no" => $data['sales_order_no']]);
            $sales_order_item = $this->crud->read('sales_orders', [], ["sales_order_no" => $data['sales_order_no'], "item_fg_id" => $item_fg->id]);
            $sales_order_duplicate = $this->crud->read('sales_order_deliveries', [], ["sales_order_no" => $data['sales_order_no'],"item_fg_id" => $item_fg->id,"trans_date" => $data['trans_date']]);
            $sales_order_deliveries_total = $this->crud->query("SELECT SUM(qty) as total FROM sales_order_deliveries WHERE sales_order_no='$sales_orders->sales_order_no' and item_fg_id = '$item_fg->id' GROUP BY sales_order_no, item_fg_id");

            $qty_so = $sales_order_item->qty;

            // var_dump("Acumulate: ", $sales_order_deliveries_total[0]->total);
            // var_dump("Qty Upload: ", $data['qty']);
            // var_dump("Qty SO: ", $qty_so);
            // var_dump("SO NO:", $sales_orders->sales_order_no);
            // var_dump("Item FG Id:", $item_fg->id);
            // return;
            
            if (empty($sales_orders->sales_order_no)) {
                echo json_encode(array("title" => "Not Found", "message" => " Sales Order No " . $data['sales_order_no'] . " Not Found", "theme" => "error"));
            }elseif (!$sales_order_item) {
                echo json_encode(array("title" => "Item Not Found", "message" => " Product No " .$data['number']. " Not Found in " .$data['sales_order_no'] , "theme" => "error"));
            }elseif ($sales_order_duplicate) {
                echo json_encode(array("title" => "Duplicate", "message" => " Product No " . $data['number'] . " Trans Date " . $data['trans_date'] , "theme" => "error"));
            } elseif (empty($item_fg->number)) {
                echo json_encode(array("title" => "Not Found", "message" => " Product No. " . $data['number'] . " Not Found", "theme" => "error"));
            } elseif (round($qty_so) < round(@$sales_order_deliveries_total[0]->total + $data['qty'])) {
                echo json_encode(array("title" => "Qty Over", "message" => " Product No. " . $data['number'] . " Trans Date " . $data['trans_date'] ." Qty is greater than the Sales Order", "theme" => "error"));
            } else {
                $dataFinal = array(
                    //field
                    "sales_order_no" => $data['sales_order_no'],
                    "customer_id" => $sales_orders->customer_id,
                    "item_fg_id" => $item_fg->id,
                    "trans_date" => $data['trans_date'],
                    "qty" => $data['qty'],
                    "remarks" => 'Upload Data',
                );
                $send   = $this->crud->create('sales_order_deliveries', $dataFinal);
                echo $send;
            }
        }
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
        $filter_division = @base64_decode($get['filter_division']);

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

        if ($filter_division != "") {
            $this->db->where('a.division', $filter_division);
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
