<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Transaction_other_component extends CI_Controller
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
        $this->form_validation->set_rules('item_rm_id', 'Item ID', 'required|min_length[1]|max_length[50]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/transaction_other_component');
        } else {
            redirect('error_access');
        }
    }

    public function readPeriod()
    {
        $records = $this->crud->query("SELECT `period` FROM transaction_other_component WHERE `status` = '0' GROUP BY `period`");
        echo json_encode($records);
    }

    public function readWp($period)
    {
        $records = $this->crud->query("SELECT workorder FROM transaction_other_component WHERE `status` = '0' and `period` = '$period' GROUP BY workorder");
        echo json_encode($records);
    }

    public function readRequestNo($period, $workorder)
    {
        $workorder = base64_decode($workorder);
        $records = $this->crud->query("SELECT request_no FROM transaction_other_component WHERE status = '0' and `period` = '$period' and workorder = '$workorder' GROUP BY `request_no`");
        echo json_encode($records);
    }

    public function readRequestNos()
    {
        $records = $this->crud->query("SELECT request_no FROM transaction_other_component WHERE status = '0' GROUP BY `request_no`");
        echo json_encode($records);
    }

    public function readItemRm()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT id, number, name, uom 
        FROM item_rm 
        WHERE status = '0' 
            AND division = 'INJ' 
            AND item_category_id = 'C11' 
            AND item_family_id = 'P05' 
            AND (number LIKE '%$post%' OR name LIKE '%$post%')
        ORDER BY `number` ASC");
        echo json_encode($records);
    }

    public function readProduct($product_family_id)
    {
        $records = $this->crud->query("SELECT b.number, b.name, b.id FROM transaction_other_component a JOIN item_rm b ON a.item_rm_id = b.id WHERE b.item_family_id = '$product_family_id' GROUP BY b.id ORDER BY b.number asc");
        echo json_encode($records);
    }

    public function readProducts()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $records = $this->crud->query("SELECT number, name, id FROM item_rm WHERE status = '0' and `number` like '%$post%' or `name` like '$post'
        GROUP BY id ORDER BY number asc");
        echo json_encode($records);
    }

    public function readType()
    {
        $records = $this->crud->query("SELECT * FROM transactions WHERE status = '0'");
        echo json_encode($records);
    }

    public function request_no($trans_date)
    {
        $trans_date = base64_decode($trans_date);
        $datenow    = date("ymd", strtotime($trans_date));
        $sqlGetID   = $this->db->query("SELECT max(request_no) as kode FROM transaction_other_component WHERE request_no like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }
        echo "TSNOTH-" . $datenow . "-" . $autoID;
    }

    public function datatableUpdate($request_no){
        $request_no = base64_decode($request_no);

        //Select Query
        $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
        $this->db->from('transaction_other_component a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id','left');
        // $this->db->join('uom c', 'b.uom_id = c.id');
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
            // $filter_period = $this->input->get('filter_period');
            // $filter_workorder   = $this->input->get('filter_workorder');
            $filter_request_no = $this->input->get('filter_request_no');
            $filter_product_no = base64_decode($this->input->get('filter_product_no'));
            $filter_product_family = $this->input->get('filter_product_family');
            // $filter_kanban_date = $this->input->get('filter_kanban_date');
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_transaction_type = $this->input->get('filter_transaction_type');

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
                $this->db->select('a.*, c.number as item_number, c.name as item_name, c.uom');
                $this->db->from('transaction_other_component a');
                $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
                $this->db->where('a.deleted', 0);
                // $this->db->where('a.status', 0);
                if ($filter_request_no != "") {
                    $this->db->where('a.request_no', $filter_request_no);
                }
                if($filter_product_family != ""){
                    $this->db->where('c.item_family_id', $filter_product_family);
                }
                if($filter_product_no != ""){
                    $this->db->where('a.item_rm_id', $filter_product_no);
                }
                // if($filter_kanban_date != ""){
                //     $this->db->where('a.request_date', $filter_kanban_date);
                // }
                if (!empty($filter_from) && !empty($filter_to)) {
                    $this->db->where("a.request_date >=", $filter_from);
                    $this->db->where("a.request_date <=", $filter_to);
                }
                if($filter_transaction_type != ""){
                    $this->db->where('a.transaction_type', $filter_transaction_type);
                }
                $this->db->group_by('a.request_no');
                $this->db->order_by('a.request_no', 'DESC');
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
                        "period" => $record['period'],
                        "item_rm_id" => $record['item_rm_id'],
                        "item_number" => $record['item_number'],
                        "item_fg_id" => $record['item_fg_id'],
                        "item_name" => $record['item_name'],
                        "workorder" => $record['workorder'],
                        "transaction_type" => $record['transaction_type'],
                        "transaction_kind" => $record['transaction_kind'],
                        "transaction_id" => $record['transaction_id'],
                        "remarks" => $record['remarks'],
                        "status" => $record['status'],
                        "state" => "closed"
                    );
                }
                $result['total'] = $totalRows;
                $result = array_merge($result, ['rows' => @$arr]);
                echo json_encode($result);
            } else {
                //Select Query
                $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
                $this->db->from('transaction_other_component a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->where('a.deleted', 0);
                $this->db->where('a.request_no', $id);
                $this->db->group_by('a.id');
                if($filter_product_family != ""){
                    $this->db->where('b.item_family_id', $filter_product_family);
                }
                if($filter_product_no != ""){
                    $this->db->where('a.item_rm_id', $filter_product_no);
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
                        "period" => $record['period'],
                        "wp" => $record['wp'],
                        "workorder" => $record['workorder'],
                        "item_rm_id" => $record['item_rm_id'],
                        "item_number" => $record['item_number'],
                        "item_name" => $record['item_name'],
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
                    $item_rm_id = $post['item_rm_id'];

                    $datas = $this->crud->reads('transaction_other_component', [], ["request_no" => $request_no, "item_rm_id" => $item_rm_id]);

                    if(count($datas) > 0){
                        $send = $this->crud->update('transaction_other_component', ["request_no" => $request_no, "item_rm_id" => $item_rm_id], $post);
                        echo $send;
                    }else{
                        $send = $this->crud->create('transaction_other_component', $post);
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
        $send = $this->crud->delete('transaction_other_component', ["id" => $data['id']]);
        $delete = $this->crud->delete('wip_balances', ["request_no" => $data['request_no'], "item_rm_id" => $data['item_rm_id']]);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=transaction_other_component_$format.xls");
        }
        // $filter_period = $this->input->get('filter_period');
        // $filter_workorder   = $this->input->get('filter_workorder');
        $filter_request_no = $this->input->get('filter_request_no');
        $filter_product_no = base64_decode($this->input->get('filter_product_no'));
        $filter_product_family = $this->input->get('filter_product_family');
        // $filter_kanban_date = $this->input->get('filter_kanban_date');
        $filter_from  = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_transaction_type = $this->input->get('filter_transaction_type');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $this->db->select('a.*, b.number as item_number, b.name as item_name, b.uom');
        $this->db->from('transaction_other_component a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id');
        // $this->db->join('uom d', 'b.uom_id = d.id');
        $this->db->where('a.deleted', 0);
        // $this->db->like("a.period", $filter_period);
        // $this->db->like("a.workorder", $filter_workorder);
        if ($filter_request_no != "") {
            $this->db->where('a.request_no', $filter_request_no);
        }
        if($filter_product_family != ""){
            $this->db->where('b.item_family_id', $filter_product_family);
        }
        if($filter_product_no != ""){
            $this->db->where('a.item_rm_id', $filter_product_no);
        }
        // if($filter_kanban_date != ""){
        //     $this->db->where('a.request_date', $filter_kanban_date);
        // }
        if (!empty($filter_from) && !empty($filter_to)) {
            $this->db->where("a.request_date >=", $filter_from);
            $this->db->where("a.request_date <=", $filter_to);
        }
        if($filter_transaction_type != ""){
            $this->db->where('a.transaction_type', $filter_transaction_type);
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
                <th>Product No</th>
                <th>Product Name</th>
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
                        <td style="mso-number-format:\@;">' . $data['item_number'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_name'] . '</td>
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
