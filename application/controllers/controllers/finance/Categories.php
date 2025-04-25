<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Categories extends CI_Controller
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
            $this->load->view('finance/categories');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM asset_categories
            WHERE name LIKE '%$post%' GROUP BY number ORDER BY flag ASC");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        $filters = json_decode($this->input->post('filterRules'));
        $id = $_POST['id'];
        if ($id === "0") {
            $this->db->select('a.*, b.name as journal_type_name');
            $this->db->from('asset_categories a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id', 'left');
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if($filter->field == "journal_type_name"){
                        $this->db->like("b.name", $filter->value);
                    }else{
                        $this->db->like("a.".$filter->field, $filter->value);
                    }
                }
            }
            $this->db->group_by('number');
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['number'],
                    "journal_type_id" => $record['journal_type_id'],
                    "number" => $record['number'],
                    "name" => $record['name'],
                    "type" => $record['type'],
                    "journal_type_name" => $record['journal_type_name'],
                    "state" => "closed",
                    "datatable" => 1
                );
            }
        } else {
            $this->db->select('a.*, b.name as journal_type_name');
            $this->db->from('asset_categories a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id','left');
            $this->db->where('a.number', $id);
            $this->db->order_by('a.flag', 'asc');
            $arr = $this->db->get()->result_array();
        }

        //Mapping Data
        $result = !empty($arr) ? $arr : [];
        echo json_encode($result);
    }

    public function datatable_updates()
    {
        $number = base64_decode($this->input->get('number'));
        $records = $this->crud->query("SELECT * FROM asset_categories WHERE number = '$number' ORDER BY flag asc");
        echo json_encode($records);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $categories = $this->crud->read('asset_categories', [], ["number" => $post['number'], "account_number" => $post['account_number'], "account_type" => $post['account_type']]);

            if (@$categories->id != "") {
                $send = $this->crud->update('asset_categories', ["number" => $post['number'], "account_number" => $post['account_number'], "account_type" => $post['account_type']], $post);
            } else {
                $send = $this->crud->create('asset_categories', $post);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('asset_categories', $data);
        echo $send;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=asset_categories_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as journal_type_name');
        $this->db->from('asset_categories a');
        $this->db->join('journal_types b', 'a.journal_type_id = b.id', 'left');
        $this->db->order_by('a.number', 'asc');
        $this->db->order_by('a.flag', 'asc');
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
                            <small>MASTER ASSET CATEGORIES</small>
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
                <th width="20">No</th>
                <th>Number</th>
                <th>Name</th>
                <th>Journal Type</th>
                <th>Type</th>
                <th>Account No</th>
                <th>Account Name</th>
                <th>Debit/Credit</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['journal_type_name'] . '</td>
                    <td>' . $data['type'] . '</td>
                    <td>' . $data['account_number'] . '</td>
                    <td>' . $data['account_name'] . '</td>
                    <td>' . $data['account_type'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
