<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Umh extends CI_Controller
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

            //Process Main Number
            $this->db->select('COUNT(a.number) as total, a.number, a.name');
            $this->db->from('main_process a');
            $this->db->join('main_process_subs b', 'a.id = b.main_process_id');
            $this->db->where('a.deleted', 0);
            $this->db->group_by('a.number');
            $this->db->order_by('b.flag', 'asc');
            $main_process = $this->db->get()->result_array();

            //Process Sub Number
            $this->db->select('b.number, b.name');
            $this->db->from('main_process a');
            $this->db->join('main_process_subs b', 'a.id = b.main_process_id');
            $this->db->where('b.deleted', 0);
            $this->db->group_by('b.number');
            $this->db->order_by('a.number', 'asc');
            $this->db->order_by('b.flag', 'asc');
            $main_process_sub = $this->db->get()->result_array();

            $data['main_process'] = @$main_process;
            $data['main_process_sub'] = @$main_process_sub;

            $this->load->view('template/header', $data);
            $this->load->view('engineering/umh');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('umh', ["name" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->get()) {
            $id = @$_POST['id'];
            $ex = explode("_", $id);
            $filter_customer_id = $this->input->get('filter_customer_id');
            $filter_item_id = $this->input->get('filter_item_id');

            if ($id == 0) {
                //Select Query
                $this->db->select('a.*, COUNT(a.circuit) as circuit, SUM(a.cycle_time) as total_umh, b.name as customer_name');
                $this->db->from("(SELECT customer_id, COUNT(circuit) as circuit, SUM(cycle_time) as cycle_time 
                FROM umh 
                WHERE customer_id LIKE '%$filter_customer_id%' AND item_id LIKE '%$filter_item_id%'
                GROUP BY customer_id, circuit) a");
                $this->db->join('customers b', 'a.customer_id = b.id');
                $this->db->group_by('a.customer_id');
                $this->db->order_by('a.customer_id', 'asc');
                $records = $this->db->get()->result_array();

                foreach ($records as $record) {
                    $arr = array(
                        "id" => "1_" . $record['customer_id'],
                        "customer_id" => $record['customer_id'],
                        "name" => $record['customer_name'],
                        "total" => $record['total_umh'],
                        "circuit" => $record['circuit'],
                        "state" => "closed",
                    );

                    //Process Number
                    $this->db->select('b.number as process_number, SUM(c.cycle_time) as cycle_time');
                    $this->db->from('main_process a');
                    $this->db->join('main_process_subs b', 'a.id = b.main_process_id');
                    $this->db->join('umh c', 'c.main_process_sub_id = b.id');
                    $this->db->where('c.deleted', 0);
                    $this->db->where('c.customer_id', $record['customer_id']);
                    $this->db->group_by('c.main_process_sub_id');
                    $this->db->group_by('c.customer_id');
                    $this->db->order_by('b.flag', 'asc');
                    $main_process = $this->db->get()->result_array();

                    $arrProcess = array();
                    foreach ($main_process as $process) {
                        $arrProcess += array(
                            $process['process_number'] => $process['cycle_time']
                        );
                    }
                    $arrFinal[] = array_merge($arr, $arrProcess);
                }
            }elseif($ex[0] == 1){
                //Select Query
                $this->db->select('a.*, COUNT(a.circuit) as circuit, SUM(a.cycle_time) as total_umh, d.number as item_number, d.name as item_name');
                $this->db->from("(SELECT item_id, COUNT(circuit) as circuit, SUM(cycle_time) as cycle_time 
                FROM umh 
                WHERE customer_id = '$ex[1]' AND item_id LIKE '%$filter_item_id%'
                GROUP BY item_id, circuit) a");
                $this->db->join('items d', 'a.item_id = d.id');
                $this->db->group_by('a.item_id');
                $this->db->order_by('a.item_id', 'asc');
                $records = $this->db->get()->result_array();

                foreach ($records as $record) {
                    $arr = array(
                        "id" => "2_" . $record['item_id'],
                        "item_id" => $record['item_id'],
                        "name" => $record['item_number'],
                        "circuit" => $record['circuit'],
                        "total" => $record['total_umh'],
                        "state" => "closed",
                    );

                    //Process Number
                    $this->db->select('b.number as process_number, SUM(cycle_time) as cycle_time');
                    $this->db->from('umh a');
                    $this->db->join('main_process_subs b', 'a.main_process_sub_id = b.id');
                    $this->db->where('b.deleted', 0);
                    $this->db->where('a.customer_id', $ex[1]);
                    $this->db->where('a.item_id', $record['item_id']);
                    $this->db->group_by('a.main_process_sub_id');
                    $this->db->group_by('a.item_id');
                    $this->db->order_by('b.flag', 'asc');
                    $main_process = $this->db->get()->result_array();

                    $arrProcess = array();
                    foreach ($main_process as $process) {
                        $arrProcess += array(
                            $process['process_number'] => $process['cycle_time']
                        );
                    }
                    $arrFinal[] = array_merge($arr, $arrProcess);
                }
            }else{
                //Select Query
                $this->db->select('a.*, SUM(a.cycle_time) as total_umh, d.number as item_number, d.name as item_name');
                $this->db->from('umh a');
                $this->db->join('items d', 'a.item_id = d.id');
                $this->db->where('d.deleted', 0);
                $this->db->where('a.customer_id', $filter_customer_id);
                $this->db->where('a.item_id', $ex[1]);
                $this->db->group_by('a.item_id');
                $this->db->group_by('a.circuit');
                $this->db->order_by('a.circuit', 'asc');
                $records = $this->db->get()->result_array();

                foreach ($records as $record) {
                    $arr = array(
                        "id" => "3_" . $record['circuit'],
                        "item_id" => $record['item_id'],
                        "customer_id" => $record['customer_id'],
                        "name" => $record['item_number'],
                        "circuit" => $record['circuit'],
                        "cycle_time" => $record['total_umh'],
                        "total" => $record['total_umh'],
                    );

                    //Process Number
                    $this->db->select('b.number as process_number, cycle_time');
                    $this->db->from('umh a');
                    $this->db->join('main_process_subs b', 'a.main_process_sub_id = b.id');
                    $this->db->where('b.deleted', 0);
                    $this->db->where('a.customer_id', $filter_customer_id);
                    $this->db->where('a.item_id', $ex[1]);
                    $this->db->where('a.circuit', $record['circuit']);
                    $this->db->group_by('a.main_process_sub_id');
                    $this->db->group_by('a.circuit');
                    $this->db->order_by('b.flag', 'asc');
                    $main_process = $this->db->get()->result_array();

                    $arrProcess = array();
                    foreach ($main_process as $process) {
                        $arrProcess += array(
                            $process['process_number'] => $process['cycle_time']
                        );
                    }
                    $arrFinal[] = array_merge($arr, $arrProcess);
                }
            }

            $result = !empty($arrFinal) ? $arrFinal : [];
            echo json_encode($result);
        }
    }
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $umh = $this->crud->reads("umh", [], ["main_process_id" => $post['main_process_id'], "main_process_sub_id" => $post['main_process_sub_id'], "customer_id" => $post['customer_id'], "item_id" => $post['item_id'], "circuit" => $post['circuit']]);
            if($umh){
                show_error("Duplicate Data");
            }else{
                $send = $this->crud->create('umh', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //UPDATE DATA
    public function update()
    {
        if ($this->input->post()) {
            $customer_id = $this->input->get('customer_id');
            $item_id = $this->input->get('item_id');
            $circuit = $this->input->get('circuit');
            
            $post = $this->input->post();
            $send = $this->crud->update('umh', ["customer_id" => $customer_id, "item_id" => $item_id, "circuit" => $circuit], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('umh', ["customer_id" => $data['customer_id'], "item_id" => $data['item_id'], "circuit" => $data['circuit']]);
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
                'main_process_number' => $data->val($i, 2),
                'main_process_sub_number' => $data->val($i, 3),
                'customer_number' => $data->val($i, 4),
                'item_number' => $data->val($i, 5),
                'circuit' => $data->val($i, 6),
                'cycle_time' => $data->val($i, 7),
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    //UPLOAD CLEAR CACHE
    public function uploadclearFailed()
    {
        @unlink('excel/failed/umh.txt');
    }

    //UPLOAD CREATE FAILED
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('excel/failed/umh.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "excel/failed/umh.txt";
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
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            //Cek Process Number
            $main_process = $this->crud->read('main_process', [], ["number" => $data['main_process_number']]);
            $main_process_subs = $this->crud->read('main_process_subs', [], ["number" => $data['main_process_sub_number']]);
            $customers = $this->crud->read('customer_number', [], ["number" => $data['customer_number']]);
            $items = $this->crud->read('items', [], ["number" => $data['item_number']]);
            $job_orders = $this->crud->read('job_orders', [], ["customer_id" => @$customers->id, "item_id" => @$items->id, "circuit" => @$data['circuit']]);
            $umh = $this->crud->reads("umh", [], ["main_process_id" => @$main_process->id, "main_process_sub_id" => @$main_process_subs->id, "customer_id" => @$customers->id, "item_id" => @$items->id, "circuit" => $data['circuit']]);

            if(empty($main_process->id)){
                echo json_encode(array("title" => "Not Found", "message" => "Main Process " . $data['process_main_number'] . " Not Found", "theme" => "error"));
            }elseif(empty($main_process_subs)) {
                echo json_encode(array("title" => "Not Found", "message" => "Main Process Sub " . $data['main_process_sub_number'] . " Not Found", "theme" => "error"));
            }elseif(empty($job_orders)) {
                echo json_encode(array("title" => "Not Found", "message" => "Job Orders " . $data['customer_number'] . " and " . $data['item_number'] . " Not Found", "theme" => "error"));
            }elseif (!empty($umh->id)) {
                echo json_encode(array("title" => "Available", "message" => "Duplicate Data", "theme" => "error"));
            } else {
                $dataFinal = array(
                    "main_process_id" => $main_process->id,
                    "main_process_sub_id" => $main_process_subs->id,
                    "customer_id" => $customers->id,
                    "item_id" => $items->id,
                    "circuit" => $data['circuit'],
                    "cycle_time" => $data['efficiency']
                );
                $send   = $this->crud->create('umh', $dataFinal);
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
            header("Content-Disposition: attachment; filename=umh_$format.xls");
        }

        $filter_customer_id = $this->input->get('filter_customer_id');
        $filter_item_id = $this->input->get('filter_item_id');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Process Main Number
        $this->db->select('COUNT(a.number) as total, a.number, a.name');
        $this->db->from('main_process a');
        $this->db->join('main_process_subs b', 'a.id = b.main_process_id');
        $this->db->where('a.deleted', 0);
        $this->db->group_by('a.number');
        $this->db->order_by('b.flag', 'asc');
        $main_process = $this->db->get()->result_array();

        //Process Sub Number
        $this->db->select('b.number, b.name');
        $this->db->from('main_process a');
        $this->db->join('main_process_subs b', 'a.id = b.main_process_id');
        $this->db->where('b.deleted', 0);
        $this->db->group_by('b.number');
        $this->db->order_by('b.flag', 'asc');
        $main_process_sub = $this->db->get()->result_array();

        //Select Query
        $this->db->select('a.*, SUM(a.cycle_time) as total_umh, c.name as customer_name, b.name as item_name');
        $this->db->from('umh a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->join('customers c', 'a.customer_id = c.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.customer_id', $filter_customer_id);
        $this->db->like('a.item_id', $filter_item_id);
        $this->db->group_by('a.circuit');
        $this->db->order_by('c.name', 'asc');
        $this->db->order_by('b.name', 'asc');
        $this->db->order_by('a.circuit', 'asc');
        $records = $this->db->get()->result_array();
        
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
                            <small>MASTER UNIT MAN HOUR</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
        </center>
        <br><br><br>
        
        <table id="customers" border="1">
            <tr>
                <th rowspan="2" width="20">No</th>
                <th rowspan="2">Customer</th>
                <th rowspan="2">Product No</th>
                <th rowspan="2">Circuit</th>';

        foreach ($main_process as $main) {
            $html .= '<th style="text-align:center;" colspan="' . $main['total'] . '"> ' . $main['name'] . '</th>';
        }

        $html .= '<th rowspan="2">Total UMH</th>
                </tr><tr>';

        foreach ($main_process_sub as $proces) {
            $html .= '<th style="text-align:center;"> ' . $proces['name'] . '</th>';
        }

        $html .= '</tr>';

        $no = 1;
        $arrFinal = array();
        foreach ($records as $data) {
            $arr = array(
                "customer_name" => $data['customer_name'],
                "item_name" => $data['item_name'],
                "circuit" => $data['circuit'],
                "total_umh" => $data['total_umh'],
            );

            //Process Main Number
            $this->db->select('b.number as process_number, cycle_time');
            $this->db->from('umh a');
            $this->db->join('main_process_subs b', 'a.main_process_sub_id = b.id');
            $this->db->where('b.deleted', 0);
            $this->db->where('a.customer_id', $data['customer_id']);
            $this->db->where('a.item_id', $data['item_id']);
            $this->db->where('a.item_id', $data['item_id']);
            $this->db->group_by('a.main_process_sub_id');
            $this->db->group_by('a.item_id');
            $this->db->order_by('b.flag', 'asc');
            $main_process2 = $this->db->get()->result_array();

            $arrProcess = array();
            foreach ($main_process2 as $process2) {
                $arrProcess += array(
                    $process2['process_number'] => $process2['cycle_time']
                );
            }
            $arrFinal[] = array_merge($arr, $arrProcess);
        }

        foreach ($arrFinal as $final) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $final['customer_name'] . '</td>
                    <td>' . $final['item_name'] . '</td>
                    <td>' . $final['circuit'] . '</td>';
                    foreach ($main_process_sub as $proses) {
                        $number = $proses['number'];
                        $html .= '<td style="text-align:center;"> ' . @$final[$number] . '</td>';
                    }
            $html .= '<td>' . $final['total_umh'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
