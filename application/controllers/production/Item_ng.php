<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Item_ng extends CI_Controller
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
        $this->form_validation->set_rules('workorder', 'Workorder No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('production/item_ng');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('item_ng', ["name" => $post]);
        echo json_encode($send);
    }

    public function readDocument()
    {
        $send = $this->crud->query("SELECT DISTINCT document FROM item_ng order by document desc");
        echo json_encode($send);
    }

    public function item_ng_no($trans_date)
    {
        $trans_date = base64_decode($trans_date);
        $year       = date("Y", strtotime($trans_date));
        $datenow    = date("ymd", strtotime($trans_date));
        $sqlGetID   = $this->db->query("SELECT MAX(SUBSTR(document, -4, 4)) as kode FROM item_ng WHERE trans_date like '%$year%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%04s", $kode + 1);
        } else {
            $urutan = (int) $kode;
            $urutan++;
            $autoID = sprintf("%04s", $urutan);
        }

        echo "NG-" . $datenow . "-" . $autoID;
    }

    public function readWorkorders()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT workorder, wp FROM production_schedules 
        WHERE `status` = '1' and workorder like '%$post%'
        order by workorder desc");
        echo json_encode($send);
    }

    public function readItems($workorder)
    {
        $workorder = base64_decode($workorder);

        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, a.qty_act as qty, c.name as uom
        FROM supply_sheets a
        JOIN items b ON a.component_id = b.id
        JOIN uom c ON b.uom_id = c.id
        WHERE a.workorder = '$workorder' and b.status = '0' and b.number like '%$post%'
        order by b.number asc");
        echo json_encode($send);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_document = $this->input->get('filter_document');
            $filter_family_id = $this->input->get('filter_family_id');
            $filter_item_id = $this->input->get('filter_item_id');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.number as item_number, b.name as item_name');
            $this->db->from('item_ng a');
            $this->db->join('items b', 'a.item_id = b.id');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            $this->db->like('a.document', $filter_document);
            $this->db->like('b.item_family_id', $filter_family_id);
            $this->db->like('b.id', $filter_item_id);
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->order_by('a.document', 'DESC');
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
                $itemNg = $this->crud->reads("item_ng", [], ["item_id" => $post['item_id'], "document" => $post['document']]);

                if (count($itemNg) > 0) {
                    echo json_encode(array("title" => "Duplicate", "message" => "Data has been created", "theme" => "error"));
                } else {
                    $send = $this->crud->create('item_ng', $post);
                    echo $send;
                }
            } else {
                show_error(validation_errors());
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
            $send = $this->crud->update('item_ng', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('item_ng', ["id" => $data['id']]);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=item_ng_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_document = $this->input->get('filter_document');
        $filter_family_id = $this->input->get('filter_family_id');
        $filter_item_id = $this->input->get('filter_item_id');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_number, b.name as item_name');
        $this->db->from('item_ng a');
        $this->db->join('items b', 'a.item_id = b.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('a.document', $filter_document);
        $this->db->like('b.item_family_id', $filter_family_id);
        $this->db->like('b.id', $filter_item_id);
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
                                <small>ITEMS NG TRANSACTION</small>
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
                    <th>Trans Date</th>
                    <th>Document No</th>
                    <th>Departement</th>
                    <th>Process</th>
                    <th>NG Type</th>
                    <th>Work Order</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Qty</th>
                    <th>Uom</th>
                    <th>Remarks</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['document'] . '</td>
                            <td>' . $data['departement'] . '</td>
                            <td>' . $data['process'] . '</td>
                            <td>' . $data['type'] . '</td>
                            <td>' . $data['workorder'] . '</td>
                            <td>' . $data['item_number'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . number_format($data['qty']) . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . $data['remarks'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
