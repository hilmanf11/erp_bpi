<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Transaction_boxs extends CI_Controller
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
        $this->form_validation->set_rules('item_box_id', 'Item ID', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/transaction_boxs');
        } else {
            redirect('error_access');
        }
    }

    public function readPeriod()
    {
        $records = $this->crud->query("SELECT `period` FROM transaction_boxs WHERE `status` = '0' GROUP BY `period`");
        echo json_encode($records);
    }

    public function readWp($period)
    {
        $records = $this->crud->query("SELECT workorder FROM transaction_boxs WHERE `status` = '0' and `period` = '$period' GROUP BY workorder");
        echo json_encode($records);
    }

    public function readRequestNo($period, $workorder)
    {
        $workorder = base64_decode($workorder);
        $records = $this->crud->query("SELECT request_no FROM transaction_boxs WHERE status = '0' and `period` = '$period' and workorder = '$workorder' GROUP BY `request_no`");
        echo json_encode($records);
    }

    public function readRequestNos()
    {
        $records = $this->crud->query("SELECT request_no FROM transaction_boxs WHERE status = '0' GROUP BY `request_no`");
        echo json_encode($records);
    }

    public function readItemBox()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT id, color, name, uom , size, code
        FROM item_boxs 
        WHERE `size` like '%$post%' or `name` like '%$post%'
        ORDER BY `id` ASC");
        echo json_encode($records);
    }

    public function readProducts()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT number, name, id FROM item_boxs WHERE status = '0' and `number` like '%$post%' or `name` like '$post'
        GROUP BY id ORDER BY number asc");
        echo json_encode($records);
    }

    public function readType()
    {
        $records = $this->crud->query("SELECT * FROM master_transaction_boxs WHERE status = '0'");
        echo json_encode($records);
    }

    public function readCust()
    {
        $records = $this->crud->query("SELECT * FROM customers WHERE status = '0'");
        echo json_encode($records);
    }

    public function request_no($trans_date)
    {
        $trans_date = base64_decode($trans_date);
        $datenow    = date("ymd", strtotime($trans_date));
        $sqlGetID   = $this->db->query("SELECT max(request_no) as kode FROM transaction_boxs WHERE request_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "TSNB-" . $datenow . "-" . $autoID;
    }

    public function datatableUpdate($request_no){
        $request_no = base64_decode($request_no);
        //Select Query
        $this->db->select('a.*, b.name as item_box_name, b.color as item_box_color, b.code as item_box_code, b.size as item_box_size, b.uom');
        $this->db->from('transaction_boxs a');
        $this->db->join('item_boxs b', 'a.item_box_id = b.id','left');
        $this->db->where('a.request_no', $request_no);

        //Total Data
        $totalRows = $this->db->count_all_results('', false);
        //Get Data Array
        $records = $this->db->get()->result_array();
        
        //Mapping Data
        $result['total'] = $totalRows;
        $result = array_merge($result, ['rows' => $records]);
        echo json_encode($result);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_request_no = $this->input->get('filter_request_no');
            $filter_box_name = base64_decode($this->input->get('filter_box_name'));
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_transaction_type = $this->input->get('filter_transaction_type');
            $filter_customer_name = $this->input->get('filter_customer_name');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10; 
            $offset = ($page - 1) * $rows;
            $result = array();
            $id = $_POST['id'];

            if ($id === "0") {
                //Select Query
                $this->db->select('a.*, c.name as item_box_name, c.color as item_box_color, c.code as item_box_code, c.size as item_box_size, c.uom, COALESCE(a.transaction_from, "-") as customer_name');
                $this->db->from('transaction_boxs a');
                $this->db->join('item_boxs c', 'a.item_box_id = c.id', 'left');
                $this->db->where('a.deleted', 0);
                // $this->db->where('a.status', 0);
                if ($filter_request_no != "") {
                    $this->db->where('a.request_no', $filter_request_no);
                }
                if($filter_box_name != ""){
                    $this->db->where('a.item_box_id', $filter_box_name);
                }
                if (!empty($filter_from) && !empty($filter_to)) {
                    $this->db->where("a.request_date >=", $filter_from);
                    $this->db->where("a.request_date <=", $filter_to);
                }
                if($filter_transaction_type != ""){
                    $this->db->where('a.transaction_type', $filter_transaction_type);
                }
                if($filter_customer_name != ""){
                    $this->db->where('a.transaction_from', $filter_customer_name);
                }
                $this->db->group_by('a.request_no');
                $this->db->order_by('a.request_date', 'DESC');
                //Total Data
                $totalRows = $this->db->count_all_results('', false);
                //Limit 1 - 10
                $this->db->limit($rows, $offset);
                $records = $this->db->get()->result_array();
                foreach ($records as $record) {

                    $arr[] = array(
                        "id" => $record['request_no'],
                        "request_no" => $record['request_no'],
                        "request_date" => $record['request_date'],
                        "request_name" => $record['request_name'],
                        "transaction_type" => $record['transaction_type'],
                        "transaction_id" => $record['transaction_id'],
                        "remarks" => $record['remarks'],
                        "status" => $record['status'],
                        "customer_name" => $record['transaction_from'],
                        "state" => "closed"
                    );
                }
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                //Select Query
                $this->db->select('a.*, b.name as item_box_name, b.color as item_box_color, b.code as item_box_code, b.size as item_box_size, b.uom');
                $this->db->from('transaction_boxs a');
                $this->db->join('item_boxs b', 'a.item_box_id = b.id');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.request_no', $id);
                $this->db->group_by('a.id');
                if($filter_box_name != ""){
                    $this->db->where('a.item_box_id', $filter_box_name);
                }
                if (!empty($filter_from) && !empty($filter_to)) {
                    $this->db->where("a.request_date >=", $filter_from);
                    $this->db->where("a.request_date <=", $filter_to);
                }
                $this->db->order_by('a.request_no', 'DESC');
                $records = $this->db->get()->result_array();

                foreach ($records as $record) {

                    $arr[] = array(
                        "id" => $record['id'],
                        "request_no" => $record['request_no'],
                        "request_date" => $record['request_date'],
                        "request_name" => $record['request_name'],
                        "item_box_id" => $record['item_box_id'],
                        "item_box_name" => $record['item_box_name'],
                        "item_box_code" => $record['item_box_code'],
                        "item_box_color" => $record['item_box_color'],
                        "item_box_size" => $record['item_box_size'],
                        "qty" => $record['qty'],
                        "uom" => $record['uom'],
                        "remarks" => $record['remarks'],
                        "transaction_type" => $record['transaction_type'],
                        "created_by" => $record['created_by'],
                        "created_date" => $record['created_date'],
                        "updated_by" => $record['updated_by'],
                        "updated_date" => $record['updated_date']
                    );
                }
                $result = !empty($arr) ? $arr : [];
                echo json_encode($result);
            }
        }
    }

    public function create()
    {
        if ($this->input->post()) {
            // if ($this->form_validation->run() == TRUE) {
                $post   = $this->input->post();
                if ($post['qty'] == 0) {
                    echo json_encode(array("title" => "Qty 0", "message" => " Qty is 0", "theme" => "error"));
                } else {
                    $request_no = $post['request_no'];
                    $item_box_id = $post['item_box_id'];

                    $datas = $this->crud->reads('transaction_boxs', [], ["request_no" => $request_no, "item_box_id" => $item_box_id]);

                    if(count($datas) > 0){
                        $send = $this->crud->update('transaction_boxs', ["request_no" => $request_no, "item_box_id" => $item_box_id], $post);
                        echo $send;
                    }else{
                        $send = $this->crud->create('transaction_boxs', $post);
                        echo $send;
                    }
                }
            // } else {
            //     show_error(validation_errors());
            // }
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('transaction_boxs', ["id" => $data['id']]);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=transaction_boxs_$format.xls");
        }
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_box_name = base64_decode($this->input->get('filter_box_name'));
        $filter_from  = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_transaction_type = $this->input->get('filter_transaction_type');
        $filter_customer_name = $this->input->get('filter_customer_name');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.name as item_box_name, b.color as item_box_color, b.code as item_box_code, b.uom');
        $this->db->from('transaction_boxs a');
        $this->db->join('item_boxs b', 'a.item_box_id = b.id');
        $this->db->where('a.deleted', 0);
        if ($filter_request_no != "") {
            $this->db->where('a.request_no', $filter_request_no);
        }
        if($filter_box_name != ""){
            $this->db->where('a.item_box_id', $filter_box_name);
        }
        if (!empty($filter_from) && !empty($filter_to)) {
            $this->db->where("a.request_date >=", $filter_from);
            $this->db->where("a.request_date <=", $filter_to);
        }
        if($filter_transaction_type != ""){
            $this->db->where('a.transaction_type', $filter_transaction_type);
        }
        if($filter_customer_name != ""){
            $this->db->where('a.transaction_from', $filter_customer_name);
        }
        $this->db->order_by('a.request_no', 'DESC');
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
                                <small>TRANSACTION RM</small>
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
                <th>No</th>
                <th>Request No</th>
                <th>Request Date</th>
                <th>Requester</th>
                <th>Type</th>
                <th>From</th>
                <th>To</th>
                <th>Box Name</th>
                <th>Box Code</th>
                <th>Box Color</th>
                <th>Qty</th>
                <th>Uom</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['request_no'] . '</td>
                        <td>' . $data['request_date'] . '</td>
                        <td>' . $data['request_name'] . '</td>
                        <td>' . $data['transaction_type'] . '</td>
                        <td>' . $data['transaction_from'] . '</td>
                        <td>' . $data['transaction_to'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_box_name'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_box_code'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_box_color'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
