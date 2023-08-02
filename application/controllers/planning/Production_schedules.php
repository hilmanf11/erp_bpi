<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Production_schedules extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/production_schedules');
        } else {
            redirect('error_access');
        }
    }
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('production_schedules', ["name" => $post]);
        echo json_encode($send);
    }
    public function readPeriodAll()
    {
        $send = $this->crud->query("SELECT DISTINCT `period` FROM production_schedules ORDER BY `period` DESC");
        echo json_encode($send);
    }
    public function readWpAll()
    {
        $period = base64_decode($this->input->get('period'));
        $send = $this->crud->query("SELECT DISTINCT wp, workorder, so_number FROM production_schedules WHERE `period` = '$period' ORDER BY `wp` DESC");
        echo json_encode($send);
    }
    public function readPeriod()
    {
        $send = $this->crud->query("SELECT DISTINCT `period` FROM production_schedules WHERE `status` = 0 ORDER BY `period` DESC");
        echo json_encode($send);
    }
    
    public function readWp()
    {
        $period = base64_decode($this->input->get('period'));
        $send = $this->crud->query("SELECT DISTINCT wp FROM production_schedules WHERE `status` = 0 and `period` = '$period' ORDER BY `wp` DESC");
        echo json_encode($send);
    }
    
    public function readWorkorder()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $send = $this->crud->query("SELECT DISTINCT workorder, so_number FROM production_schedules WHERE `status` = 0 and `period` = '$period' and wp = '$wp' ORDER BY `wp` DESC");
        echo json_encode($send);
    }
    
    public function readCustomer()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $workorder = base64_decode($this->input->get('workorder'));
        
        $send = $this->crud->query("SELECT a.customer_id, b.number as customer_number, b.name as customer_name 
            FROM production_schedules a
            JOIN customers b on a.customer_id = b.id
            WHERE a.status = 0 and a.period = '$period' and a.wp = '$wp' and a.workorder = '$workorder' ORDER BY a.workorder DESC");
        echo json_encode($send);
    }
    public function readItems()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $customer_id = $this->input->get('customer_id');
        $workorder = base64_decode($this->input->get('workorder'));
        
        $send = $this->crud->query("SELECT a.workorder, b.id as item_id, b.number as item_number, b.name as item_name, c.name as customer_name  
            FROM production_schedules a
            JOIN items b on a.item_id = b.id
            JOIN customers c on a.customer_id = c.id
            WHERE a.status = 0 and a.period = '$period' and a.wp = '$wp' and a.customer_id = '$customer_id' and a.workorder = '$workorder' ORDER BY a.workorder DESC");
        echo json_encode($send);
    }
    public function readMonth()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array(
                "number" => $key,
                "name" => $value
            );
        }
        die(json_encode($arr));
    }
    public function readYear()
    {
        $tahun_before = date('Y', strtotime('-5 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        for ($i = $tahun_before; $i <= $tahun_next; $i++) {
            $arr[] = array(
                "number" => "$i"
            );
        }
        die(json_encode($arr));
    }
    public function workorder($wp, $trans_date)
    {
        // $production_schedule = $this->crud->read("production_schedules", [], ["wp" => $wp]);
        // if ($production_schedule) {
        //     return $production_schedule->workorder;
        // } else {
        $datenow = date("ymd", strtotime($trans_date));
        $sqlGetID = $this->db->query("SELECT max(workorder) as kode FROM production_schedules WHERE workorder like '%$datenow%'");
        $rowID = $sqlGetID->row();
        $kode = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%05s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%05s", $urutan);
        }
        $workOrderNo = "WO" . $datenow . "-" . $autoID;
        return $workOrderNo;
        //}
    }
    public function datatables()
    {
        if ($this->input->post()) {
            $filter_month = $this->input->get('filter_month');
            $filter_year = $this->input->get('filter_year');
            $filter_lines = $this->input->get('filter_lines');
            $filter_customers = $this->input->get('filter_customers');
            $filter_sales_order = $this->input->get('filter_sales_order');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.number as customer_number, b.name as customer_name, b.type as customer_type, c.number as item_number, c.name as item_name, e.name as uom, d.name as line_name');
            $this->db->from('production_schedules a');
            $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('items c', 'a.item_id = c.id');
            $this->db->join('lines d', 'a.line_id = d.id');
            $this->db->join('uom e', 'c.uom_id = e.id');
            $this->db->where('a.deleted', 0);
            $this->db->like('a.month', $filter_month);
            $this->db->like('a.year', $filter_year);
            $this->db->like('a.line_id', $filter_lines);
            $this->db->like('a.customer_id', $filter_customers);
            $this->db->like('a.so_number', $filter_sales_order);
            $this->db->order_by('a.trans_date', 'ASC');
            $this->db->order_by('b.name', 'ASC');
            $this->db->order_by('c.number', 'ASC');
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
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $workorder = $this->workorder($post['wp'], $post['trans_date']);
                $production_schedules = $this->crud->read('production_schedules', [], ["customer_id" => $post['customer_id'], "item_id" => $post['item_id'], "wp" => $post['wp'], "trans_date" => $post['trans_date']]);
                $sales_orders = $this->crud->query("SELECT 
                    a.item_id, b.number as item_number, 
                    b.name as item_name, 
                    (a.qty - coalesce(SUM(c.qty), 0)) as qty
                FROM sales_orders a 
                JOIN items b on a.item_id = b.id
                LEFT JOIN production_schedules c ON a.number = c.so_number and a.item_id = c.item_id
                WHERE a.number = '$post[so_number]' and a.item_id = '$post[item_id]'
                GROUP BY a.item_id");
                
                if (@$production_schedules->id) {
                    show_error("Duplicate Data");
                } elseif ($post['qty'] > $sales_orders[0]->qty) {
                    show_error("qty is bigger than sales order " . $post['qty'] .">". $sales_orders[0]->qty);
                } else {
                    $postFinal = array_merge($post, array("workorder" => $workorder, "period" => $post['year'] . $post['month']));
                    $send = $this->crud->create('production_schedules', $postFinal);
                    if ($post['qty'] == $sales_orders[0]->qty) {
                        $update = $this->crud->update('sales_orders', ["number" => $post['so_number'], "item_id" => $post['item_id']], ["status" => 1]);
                    }
                }
                echo $send;
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
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('production_schedules', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('production_schedules', ["id" => $data['id']]);
        $update = $this->crud->update('sales_orders', ["number" => $data['so_number'], "item_id" => $data['item_id']], ["status" => 0]);
        echo $send;
    }
    public function print_job_order($id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $config_iso = $this->db->get('config_iso')->row();

        $this->db->select('a.*, b.lot');
        $this->db->from('production_schedules a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.id', $id);
        $label = $this->db->get()->row();
        $amountQty = ceil($label->qty / $label->lot);
        for ($i = 1; $i <= $amountQty; $i++) {
            $lots = sprintf("%03s", $i);
            $this->db->select('b.circuit');
            $this->db->from('production_schedules a');
            $this->db->join('job_orders b', 'a.item_id = b.item_id and a.customer_id and b.customer_id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.id', $id);
            $this->db->order_by('b.circuit', 'asc');
            $totalRows = $this->db->count_all_results('', false);
            $job_orders = $this->db->get()->result_array();
            $no = 1;
            $qty = $label->qty;
            foreach ($job_orders as $job_order) {
                $sequence = sprintf("%03s", $no);
                $label_no = $label->workorder . $lots . $sequence;
                if ($no == $totalRows) {
                    $finalQty = $qty;
                } else {
                    $finalQty = $label->lot;
                }
                $dataJobOrderLabel = array(
                    "workorder" => $label->workorder,
                    "label_no" => $label_no,
                    "circuit" => $job_order['circuit'],
                    "qty" => $finalQty,
                );
                $jobOrderLabel = $this->crud->read("job_order_labels", [], ["label_no" => $label_no]);
                if (empty($jobOrderLabel->id)) {
                    $this->crud->create("job_order_labels", $dataJobOrderLabel);
                }
                $qty -= $label->lot;
                $no++;
            }
        }
        $this->db->select('b.*, a.so_number, a.workorder, a.so_date, a.trans_date, a.qty, c.label_no, c.circuit, d.number as item_number, d.lot');
        $this->db->from('production_schedules a');
        $this->db->join('job_order_labels c', 'a.workorder = c.workorder');
        $this->db->join('job_orders b', 'a.item_id = b.item_id and a.customer_id and b.customer_id and c.circuit = b.circuit', 'left');
        $this->db->join('items d', 'a.item_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.id', $id);
        $this->db->group_by('c.circuit');
        $this->db->group_by('c.label_no');
        $this->db->order_by('c.label_no', 'asc');
        $records = $this->db->get()->result_object();
        if ($records) {
            $html = '<html>
                    <head>
                        <title>' . $label->workorder . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 20cm;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
            foreach ($records as $record) {
                //Generate QRcode
                $this->createQrcode($record->label_no, "assets/image/qrcode/");
                $html .= '  <table id="customers" border="1" style="margin-bottom:20px;">
                                <tr>
                                    <th colspan="4" style="font-size:16px; padding:10px; text-align:center;"><b>JOB ORDER ' . $config->name . '</b></th>
                                    <th width="150">
                                        <table style="width:100%; font-size:10px; border:0;">
                                            <tr style="border:0;">
                                                <td width="60">Doc No</td>
                                                <td width="100">' . $config_iso->doc_job_order . '</td>
                                            </tr>
                                            <tr style="border:0;">
                                                <td>Form</td>
                                                <td>' . $config_iso->form_job_order . '</td>
                                            </tr>
                                        </table>
                                    </th>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">MODEL</th>
                                    <th style="text-align:center;">PLAN QTY</th>
                                    <th style="text-align:center;">LOT</th>
                                    <th style="text-align:center;">START DATE</th>
                                    <th style="text-align:center;">ISSUE DATE</th>
                                </tr>
                                <tr>
                                    <td style="text-align:center;">' . $record->item_number . '</td>
                                    <td style="text-align:center;">' . $record->qty . '</td>
                                    <td style="text-align:center;">' . $record->lot . '</td>
                                    <td style="text-align:center;">' . $record->trans_date . '</td>
                                    <td style="text-align:center;">' . $record->so_date . '</td>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">WIRE CODE</th>
                                    <th style="text-align:center;">TYPE & SIZE</th>
                                    <th style="text-align:center;">COLOR</th>
                                    <th style="text-align:center;">LENGTH</th>
                                    <th style="text-align:center;">M/C NO</th>
                                </tr>
                                <tr>
                                    <td style="text-align:center;">' . $record->wire . '</td>
                                    <td style="text-align:center;">' . $record->type . ' ' . $record->size . '</td>
                                    <td style="text-align:center;">' . $record->color . '</td>
                                    <td style="text-align:center;">' . $record->length . '</td>
                                    <td style="text-align:center;"></td>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">TERMINAL SIDE A</th>
                                    <th style="text-align:center;">TERMINAL SIDE B</th>
                                    <th colspan="3" style="text-align:center;">WO. No</th>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_terminal . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_terminal . '</td>
                                    <td rowspan="7" colspan="3" style="text-align:center;">' . $record->workorder . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_seal . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_seal . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_chi . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_chi . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_chc . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_chc . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_stripping . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_stripping . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_process . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_process . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_note . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_note . '</td>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">CIRCUIT NO</th>
                                    <th style="text-align:center;">SERIAL NO</th>
                                    <th style="text-align:center;">OPERATOR</th>
                                    <th style="text-align:center;">CHECK BY</th>
                                    <th style="text-align:center;">INSPECT BY</th>
                                </tr>
                                <tr>
                                    <th rowspan="3" style="text-align:center; height:50px; font-size:40px;">' . $record->circuit . '</th>
                                    <td rowspan="3" style="text-align:center; height:50px;">
                                        <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="80"/>
                                        <br>
                                        <span>' . $record->label_no . '</span>
                                    </td>
                                    <th style="text-align:center; height:80px;"></th>
                                    <th style="text-align:center; height:80px;"></th>
                                    <th style="text-align:center; height:80px;"></th>
                                </tr>
                                <tr>
                                    <th style="text-align:left;">Name :</th>
                                    <th style="text-align:left;">Name :</th>
                                    <th style="text-align:left;">Name :</th>
                                </tr>
                                <tr>
                                    <th style="text-align:left;">Date :</th>
                                    <th style="text-align:left;">Date :</th>
                                    <th style="text-align:left;">Date :</th>
                                </tr>
                            </table>';
            }
            $html .= "<script>window.print()</script>";
            die($html);
        } else {
            echo "<h1>NOT FOUND JOB ORDER</h1>";
        }
    }
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=production_schedules_$format.xls");
        }
        $filter_month = $this->input->get('filter_month');
        $filter_year = $this->input->get('filter_year');
        $filter_lines = $this->input->get('filter_lines');
        $filter_customers = $this->input->get('filter_customers');
        $filter_sales_order = $this->input->get('filter_sales_order');
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as customer_number, b.name as customer_name, b.type as customer_type, c.number as item_number, c.name as item_name, e.name as uom, d.name as line_name');
        $this->db->from('production_schedules a');
        $this->db->join('customers b', 'a.customer_id = b.id');
        $this->db->join('items c', 'a.item_id = c.id');
        $this->db->join('lines d', 'a.line_id = d.id');
        $this->db->join('uom e', 'c.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        $this->db->like('a.month', $filter_month);
        $this->db->like('a.year', $filter_year);
        $this->db->like('a.line_id', $filter_lines);
        $this->db->like('a.customer_id', $filter_customers);
        $this->db->like('a.so_number', $filter_sales_order);
        $this->db->order_by('a.trans_date', 'ASC');
        $this->db->order_by('b.name', 'ASC');
        $this->db->order_by('c.number', 'ASC');
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
                                <small>PRODUCTION SCHEDULE</small>
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
                    <th>Period</th>
                    <th>WP</th>
                    <th>WP Date</th>
                    <th>Work Order</th>
                    <th>Line Production</th>
                    <th>Customer No</th>
                    <th>Customer Name</th>
                    <th>Customer Type</th>
                    <th>Sales Order No</th>
                    <th>Sales Order Date</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>UoM</th>
                    <th>Qty</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['period'] . '</td>
                            <td>' . $data['wp'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['workorder'] . '</td>
                            <td>' . $data['line_name'] . '</td>
                            <td>' . $data['customer_number'] . '</td>
                            <td>' . $data['customer_name'] . '</td>
                            <td>' . $data['customer_type'] . '</td>
                            <td>' . $data['so_number'] . '</td>
                            <td>' . $data['so_date'] . '</td>
                            <td>' . $data['item_number'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . number_format($data['qty'], 2) . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
