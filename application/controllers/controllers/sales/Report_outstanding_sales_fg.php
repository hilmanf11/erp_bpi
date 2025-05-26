<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Report_outstanding_sales_fg extends CI_Controller
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
        // $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[50]');
        // $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('sales/report_outstanding_sales_fg');
        } else {
            redirect('error_access');
        }
    }

    public function readCustomerOrder()
    {
        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $customer_id = $this->input->get("customer_id");

        $customer_orders = $this->crud->query("SELECT customer_order_no, sales_order_no
            FROM sales_orders
            WHERE sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'
            AND customer_id = '$customer_id'
            GROUP BY sales_order_no
            ORDER BY sales_order_no ASC");
        echo json_encode($customer_orders);
    }

    public function readItems()
    {
        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $filter_sales_order_no = base64_decode($this->input->get("filter_sales_order_no"));
        $customer_id = $this->input->get("customer_id");

        $customer_orders = $this->crud->query("SELECT b.id, b.number, b.name
            FROM sales_orders a
            JOIN item_fg b ON a.item_fg_id = b.id
            WHERE a.sales_order_date between '$filter_so_date_from' and '$filter_so_date_to'
            AND a.customer_id = '$customer_id' AND a.sales_order_no = '$filter_sales_order_no'
            GROUP BY a.item_fg_id");
        echo json_encode($customer_orders);
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=report_outstanding_so_$format.xls");
        }

        $filter_so_date_from = base64_decode($this->input->get("filter_so_date_from"));
        $filter_so_date_to = base64_decode($this->input->get("filter_so_date_to"));
        $filter_customer_name = base64_decode($this->input->get("filter_customer_name"));
        $filter_customer_order_no = base64_decode($this->input->get("filter_customer_order_no"));
        $filter_sales_order_no = base64_decode($this->input->get("filter_sales_order_no"));
        $filter_item_fg = base64_decode($this->input->get("filter_item_fg"));
        $filter_division = base64_decode($this->input->get("filter_division"));
        $filter_display = base64_decode($this->input->get("filter_display"));
        $filter_type = base64_decode($this->input->get("filter_type"));

        $customer = $this->crud->read("customers", [], ["id" => $filter_customer_name]);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                        <h3>' . $filter_display . ' REPORT OUTSTANDING SALES FG</h3>
                    </div>
                </center>
                <table style="width: 40%; font-size:12px;">
                    <tr>
                        <th style="width:100px; text-align:left;">Period</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $filter_so_date_from . ' To ' . $filter_so_date_to . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Customer Name</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . @$customer->name . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Customer Order No</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $filter_customer_order_no . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Sales Order No</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $filter_sales_order_no . '</td>
                    </tr>
                    <tr>
                        <th style="width:100px; text-align:left;">Product No</th>
                        <td style="width:10px;">:</td>
                        <td style="width:200px;">' . $filter_item_fg . '</td>
                    </tr>
                </table>
                <br>';

        if ($filter_display == "RECAP") {
            if($filter_type == "DS"){
                $this->db->select('a.trans_date,
                a.sales_order_no, 
                SUM(a.qty) as qty, 
                c.qty AS qty_so,
                COALESCE(e.qty, 0) AS qty_dn,
                (CASE 
                    WHEN SUM(a.qty) <= COALESCE(e.qty, 0) THEN 1
                    ELSE 0
                END) AS status,
                SUM(a.qty) - COALESCE(e.qty, 0) as outstanding,
                b.number as customer_number, 
                b.name as customer_name, 
                c.customer_order_no, 
                c.currency,
                c.price,
                d.id as item_fg_id, 
                d.name as item_fg_name, 
                d.number as item_fg_number, 
                d.uom as item_fg_uom');
                $this->db->from('sales_order_deliveries a');
                $this->db->join('customers b', 'a.customer_id = b.id');
                $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id');
                $this->db->join('item_fg d', 'a.item_fg_id = d.id');
                $this->db->join("(SELECT item_fg_id, sales_order_no, COALESCE(SUM(qty),0) as qty FROM delivery_notes GROUP BY item_fg_id, sales_order_no ) e",'a.sales_order_no = e.sales_order_no and a.item_fg_id = e.item_fg_id','left');
                $this->db->where("a.trans_date between '$filter_so_date_from' and '$filter_so_date_to'");
                $this->db->having('SUM(a.qty) > qty_dn');
                $this->db->like('a.customer_id', $filter_customer_name);
                $this->db->like('a.item_fg_id', $filter_item_fg);
                $this->db->like('a.sales_order_no', $filter_sales_order_no);
                $this->db->like('c.division', $filter_division);
                $this->db->order_by('a.sales_order_no', 'ASC');
                $this->db->group_by('a.customer_id');
                $this->db->group_by('a.item_fg_id');
                $this->db->group_by('a.sales_order_no');
                $records = $this->db->get()->result_array();

                $rekap = [];
                foreach ($records as $data) {
                    // Hitung amount IDR
                    if ($data['currency'] == 'IDR') {
                        $rate = 1;
                    } else {
                        $this->db->where('currency_from', 'USD');
                        $this->db->where('start_date <=', $data['trans_date']);
                        $this->db->where('end_date >=', $data['trans_date']);
                        $query = $this->db->get('exchange_rates');
                        if ($query->num_rows() > 0) {
                            $rate = $query->row()->middle;
                        } else {
                            $rate = 1; // default rate jika tidak ketemu
                        }
                    }
                
                    $amount_idr = ($data['outstanding'] * $data['price']) * $rate;
                
                    $customer_name = $data['customer_name'];
                
                    if (!isset($rekap[$customer_name])) {
                        $rekap[$customer_name] = 0;
                    }
                    $rekap[$customer_name] += $amount_idr;
                }
                
                $total_all_idr = array_sum($rekap);

                // Setelah rekap dibuat, tampilkan tabel rekap:
                $html .= '<table id="customers" border="1">
                <tr>
                    <th>No</th>
                    <th>Customer Name</th>
                    <th>Total Amount (IDR)</th>
                </tr>';
                
                $no = 1;
                foreach ($rekap as $customer => $total_idr) {
                    $html .= '<tr>
                        <td>'.$no.'</td>
                        <td>'.$customer.'</td>
                        <td>'.number_format($total_idr, 2).'</td>
                    </tr>';
                    $no++;
                }
        

                $html .= '<tr>
                    <th colspan="2" style="text-align:right;">TOTAL</th>
                    <th style="mso-number-format:\@;">'. number_format($total_all_idr). '</th>
                </tr>';

            }else{
                $this->db->select('a.sales_order_no, 
                a.sales_order_date,
                a.customer_order_no, 
                a.qty as qty, 
                d.qty as delivery,  
                a.qty - COALESCE(d.qty,0) as outstanding, 
                b.number as customer_number, 
                b.name as customer_name, 
                a.status, 
                a.currency,
                a.price,
                a.total,
                c.number as item_fg_number, 
                c.name as item_fg_name');
                $this->db->from('sales_orders a');
                $this->db->join('customers b', 'a.customer_id = b.id');
                $this->db->join('item_fg c', 'a.item_fg_id = c.id','left');
                // $this->db->join('delivery_notes d', 'a.sales_order_no = d.sales_order_no AND a.item_fg_id = d.item_fg_id');
                $this->db->join("(SELECT item_fg_id, sales_order_no, COALESCE(SUM(qty),0) as qty FROM delivery_notes GROUP BY item_fg_id, sales_order_no ) d",'a.sales_order_no = d.sales_order_no and a.item_fg_id = d.item_fg_id','left');
                $this->db->where("a.delivery_date between '$filter_so_date_from' and '$filter_so_date_to'");
                $this->db->where("a.status != 1 AND (a.qty - COALESCE(d.qty, 0) != 0)");
                $this->db->like('a.customer_id', $filter_customer_name);
                $this->db->like('a.item_fg_id', $filter_item_fg);
                $this->db->like('a.customer_order_no', $filter_customer_order_no);
                $this->db->like('a.sales_order_no', $filter_sales_order_no);
                $this->db->like('a.division', $filter_division);
                $this->db->order_by('b.name', 'ASC');
                $this->db->order_by('a.sales_order_no', 'ASC');
                // $this->db->group_by('a.sales_order_no');
                // $this->db->group_by('a.item_fg_id');
                $records = $this->db->get()->result_array();
                // var_dump($records) ;
                // die;

                $rekap = [];
                foreach ($records as $data) {
                    // Hitung amount IDR
                    if ($data['currency'] == 'IDR') {
                        $rate = 1;
                    } else {
                        $this->db->where('currency_from', 'USD');
                        $this->db->where('start_date <=', $data['sales_order_date']);
                        $this->db->where('end_date >=', $data['sales_order_date']);
                        $query = $this->db->get('exchange_rates');
                        if ($query->num_rows() > 0) {
                            $rate = $query->row()->middle;
                        } else {
                            $rate = 1; // default rate jika tidak ketemu
                        }
                    }
                
                    $amount_idr = (($data['outstanding'] * $data['price']) * $rate);
                
                    $customer_name = $data['customer_name'];
                
                    if (!isset($rekap[$customer_name])) {
                        $rekap[$customer_name] = 0;
                    }
                    $rekap[$customer_name] += $amount_idr;
                }
                
                $total_all_idr = array_sum($rekap);

                // Setelah rekap dibuat, tampilkan tabel rekap:
                $html .= '<table id="customers" border="1">
                <tr>
                    <th>No</th>
                    <th>Customer Name</th>
                    <th>Total Amount (IDR)</th>
                </tr>';
                
                $no = 1;
                foreach ($rekap as $customer => $total_idr) {
                    $html .= '<tr>
                        <td>'.$no.'</td>
                        <td>'.$customer.'</td>
                        <td>'.number_format($total_idr, 2).'</td>
                    </tr>';
                    $no++;
                }
        

                $html .= '<tr>
                    <th colspan="2" style="text-align:right;">TOTAL</th>
                    <th style="mso-number-format:\@;">'. number_format($total_all_idr). '</th>
                </tr>';
            }

        } else {
            //DETAIL
            if($filter_type == "DS"){
                $this->db->select('a.trans_date,
                a.sales_order_no, 
                SUM(a.qty) as qty, 
                c.qty AS qty_so,
                COALESCE(e.qty, 0) AS qty_dn,
                (CASE 
                    WHEN SUM(a.qty) <= COALESCE(e.qty, 0) THEN 1
                    ELSE 0
                END) AS status,
                SUM(a.qty) - COALESCE(e.qty, 0) as outstanding,
                b.number as customer_number, 
                b.name as customer_name, 
                c.customer_order_no, 
                c.currency,
                c.price,
                d.id as item_fg_id, 
                d.name as item_fg_name, 
                d.number as item_fg_number, 
                d.uom as item_fg_uom');
                $this->db->from('sales_order_deliveries a');
                $this->db->join('customers b', 'a.customer_id = b.id');
                $this->db->join('sales_orders c', 'a.sales_order_no = c.sales_order_no AND a.item_fg_id = c.item_fg_id');
                $this->db->join('item_fg d', 'a.item_fg_id = d.id');
                $this->db->join("(SELECT item_fg_id, sales_order_no, COALESCE(SUM(qty),0) as qty FROM delivery_notes GROUP BY item_fg_id, sales_order_no) e",'a.sales_order_no = e.sales_order_no and a.item_fg_id = e.item_fg_id','left');
                $this->db->where("a.trans_date between '$filter_so_date_from' and '$filter_so_date_to'");
                $this->db->having('SUM(a.qty) > qty_dn');
                $this->db->like('a.customer_id', $filter_customer_name);
                $this->db->like('a.item_fg_id', $filter_item_fg);
                $this->db->like('a.sales_order_no', $filter_sales_order_no);
                $this->db->like('c.division', $filter_division);
                $this->db->order_by('a.sales_order_no', 'ASC');
                $this->db->group_by('a.customer_id');
                $this->db->group_by('a.item_fg_id');
                $this->db->group_by('a.sales_order_no');
                $records = $this->db->get()->result_array();

                $html .= '<table id="customers" border="1">
                            <tr>
                                <th width="20">No</th>
                                <th>Customer Name</th>
                                <th>Sales Order No.</th>
                                <th>Customer Order No.</th>
                                <th>Product ID</th>
                                <th>Product No</th>
                                <th>Product Name</th>
                                <th>UoM</th>
                                <th>Delivery Qty</th>
                                <th>Qty DN</th>
                                <th>Outstanding</th>
                                <th>Currency</th>
                                <th>Price</th>
                                <th>Amount</th>
                                <th>Exchange Rate</th>
                                <th>Amount (IDR)</th>
                            </tr>';

                $no = 1;
                $qty_amount = 0;
                $qty_amount_idr = 0;
                
                foreach ($records as $data) {

                    // if($data['status'] == 0){
                    //     $status = "<b style='color:green;'>OPEN</b>";
                    // }else{
                    //     $status = "<b style='color:red;'>CLOSE</b>";
                    // }

                    if($data['currency'] == 'IDR'){
                        $precision = 2;
                        $rate = 1;
                    }else{
                        $precision = 4;
                        $this->db->where('currency_from', 'USD');
                        $this->db->where('start_date <=', $data['trans_date']);
                        $this->db->where('end_date >=', $data['trans_date']);
                        $query = $this->db->get('exchange_rates');
                        if ($query->num_rows() > 0) {
                            $rate = $query->row()->middle;
                        }
                    }

                    $qty_amount += $data['price'] * $data['outstanding'];
                    $qty_amount_idr += (($data['price'] * $data['outstanding']) * $rate);

                    $html .= '<tr>
                                <td>' . $no . '</td>
                                <td>' . $data['customer_name'] . '</td>
                                <td>' . $data['sales_order_no'] . '</td>
                                <td align="center">' . $data['customer_order_no'] . '</td>
                                <td>' . $data['item_fg_id'] . '</td>
                                <td>' . $data['item_fg_number'] . '</td>
                                <td>' . $data['item_fg_name'] . '</td>
                                <td>' . $data['item_fg_uom'] . '</td>
                                <td align="center">' . $data['qty'] . '</td>
                                <td align="center">' . $data['qty_dn'] . '</td>
                                <td align="center">' . $data['outstanding'] . '</td>
                                <td>' . $data['currency'] . '</td>
                                <td>' . number_format($data['price'],$precision) . '</td>
                                <td>' . number_format($data['price'] * $data['outstanding'],$precision) . '</td>
                                <td align="right">' . $rate . '</td>
                                <td>' . number_format((($data['price'] * $data['outstanding']) * $rate) ,$precision) . '</td>
                            </tr>';
                    $no++;
                }

                    $html .= '<tr>
                        <th colspan="13" style="text-align:right;">TOTAL</th>
                        <th style="mso-number-format:\@;">'. number_format($qty_amount). '</th>
                        <th style="mso-number-format:\@;"></th>
                        <th style="mso-number-format:\@;">'. number_format($qty_amount_idr). '</th>
                    </tr>';
            }else{
                $this->db->select('a.sales_order_no, 
                a.sales_order_date,
                a.customer_order_no, 
                a.qty as qty, 
                d.qty as delivery,  
                a.qty - COALESCE(d.qty,0) as outstanding, 
                b.number as customer_number, 
                b.name as customer_name, 
                a.status, 
                a.currency,
                a.price,
                a.total,
                a.type,
                c.number as item_fg_number, 
                c.name as item_fg_name');
                $this->db->from('sales_orders a');
                $this->db->join('customers b', 'a.customer_id = b.id');
                $this->db->join('item_fg c', 'a.item_fg_id = c.id','left');
                // $this->db->join('delivery_notes d', 'a.sales_order_no = d.sales_order_no AND a.item_fg_id = d.item_fg_id');
                $this->db->join("(SELECT item_fg_id, sales_order_no, COALESCE(SUM(qty),0) as qty FROM delivery_notes GROUP BY item_fg_id, sales_order_no ) d",'a.sales_order_no = d.sales_order_no and a.item_fg_id = d.item_fg_id','left');
                $this->db->where("a.delivery_date between '$filter_so_date_from' and '$filter_so_date_to'");
                $this->db->where("a.status != 1 AND (a.qty - COALESCE(d.qty, 0) != 0)");
                $this->db->like('a.customer_id', $filter_customer_name);
                $this->db->like('a.item_fg_id', $filter_item_fg);
                $this->db->like('a.customer_order_no', $filter_customer_order_no);
                $this->db->like('a.sales_order_no', $filter_sales_order_no);
                $this->db->like('a.division', $filter_division);
                $this->db->order_by('b.name', 'ASC');
                $this->db->order_by('a.sales_order_no', 'ASC');
                // $this->db->group_by('a.sales_order_no');
                // $this->db->group_by('a.item_fg_id');
                $records = $this->db->get()->result_array();
                // var_dump($records) ;
                // die;
                $html .= '<table id="customers" border="1">
                            <tr>
                                <th width="20">No</th>
                                <th>Customer Name</th>
                                <th>Sales Order No.</th>
                                <th>Customer Order No.</th>
                                <th>SO Date</th>
                                <th>Product No</th>
                                <th>Product Name</th>
                                <th>Qty SO</th>
                                <th>Qty DN</th>
                                <th>Undelivery Qty</th>
                                <th>Outstanding SO</th>
                                <th>Currency</th>
                                <th>Price</th>
                                <th>Amount</th>
                                <th>Exchange Rate</th>
                                <th>Amount (IDR)</th>
                            </tr>';

                $no = 1;
                $qty_amount = 0;
                $qty_amount_idr = 0;
                $qty_undelivery = 0;
                $qty_outstanding = 0;
                foreach ($records as $data) {
                    if($data['type'] == "Closing SO") {
                        $undelivery = $data['outstanding'];
                        $outstanding = 0;
                        $qty_undelivery += $undelivery;
                        $qty_outstanding += $outstanding;
                    } else{
                        $undelivery = 0;
                        $outstanding = $data['outstanding'];
                        $qty_undelivery += $undelivery;
                        $qty_outstanding += $outstanding;
                    }

                    if($data['currency'] == 'IDR'){
                        $precision = 2;
                        $rate = 1;
                    }else{
                        $precision = 4;
                        $this->db->where('currency_from', 'USD');
                        $this->db->where('start_date <=', $data['sales_order_date']);
                        $this->db->where('end_date >=', $data['sales_order_date']);
                        $query = $this->db->get('exchange_rates');
    
                        if ($query->num_rows() > 0) {
                            $rate = $query->row()->middle;
                        }
                    }

                    $qty_amount += $outstanding * $data['price'];
                    $qty_amount_idr += (($outstanding * $data['price']) * $rate);

                    $html .= '<tr>
                                <td>' . $no . '</td>
                                <td>' . $data['customer_name'] . '</td>
                                <td>' . $data['sales_order_no'] . '</td>
                                <td>' . $data['customer_order_no'] . '</td>
                                <td>' . $data['sales_order_date'] . '</td>
                                <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                                <td>' . $data['item_fg_name'] . '</td>
                                <td style="mso-number-format:\@;">' . number_format($data['qty']) . '</td>
                                <td style="mso-number-format:\@;">' . number_format($data['delivery']) . '</td>
                                <td style="mso-number-format:\@;">' . number_format($undelivery) . '</td>
                                <td style="mso-number-format:\@;">' . number_format($outstanding) . '</td>
                                <td>' . $data['currency'] . '</td>
                                <td>' . number_format($data['price'],$precision) . '</td>
                                <td>' . number_format($outstanding * $data['price'],$precision) . '</td>
                                <td align="right">' . $rate . '</td>
                                <td>' . number_format((($outstanding * $data['price']) * $rate) ,$precision) . '</td>
                            </tr>';
                    $no++;
                }

                $html .= '<tr>
                            <th colspan="13" style="text-align:right;">TOTAL</th>
                            <th style="mso-number-format:\@;">'. number_format($qty_amount). '</th>
                            <th style="mso-number-format:\@;"></th>
                            <th style="mso-number-format:\@;">'. number_format($qty_amount_idr). '</th>
                        </tr>';
            }
        }

        $html .= '</table></body></html>';
        echo $html;
    }
}
