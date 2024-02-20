<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Journal_setups extends CI_Controller
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
            $this->load->view('finance/journal_setups');
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads($journal_type_id)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $journal_type_id = base64_decode($journal_type_id);
        $send = $this->crud->query("SELECT a.*, b.account_name FROM journal_setups a 
            JOIN account_coa b ON a.account_number = b.account_number 
            WHERE a.account_number LIKE '%$post%' and a.journal_type_id = '$journal_type_id' ORDER BY a.flag ASC");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        $filters = json_decode($this->input->post('filterRules'));
        $id = $_POST['id'];
        if ($id === "0") {
            $this->db->select('a.journal_type_id, b.name as journal_name');
            $this->db->from('journal_setups a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id');
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "journal_name") {
                        $this->db->like("b.name", $filter->value);
                    } else {
                        $this->db->like($filter->field, $filter->value);
                    }
                }
            }
            $this->db->group_by('a.journal_type_id');
            $records = $this->db->get()->result_array();
            foreach ($records as $record) {
                $arr[] = array(
                    "id" => $record['journal_type_id'],
                    "journal_type_id" => $record['journal_type_id'],
                    "journal_name" => $record['journal_name'],
                    "state" => "closed",
                    "datatable" => 1
                );
            }
        } else {
            $this->db->select('a.*, b.name as journal_name, c.account_name');
            $this->db->from('journal_setups a');
            $this->db->join('journal_types b', 'a.journal_type_id = b.id');
            $this->db->join('account_coa c', 'a.account_number = c.account_number');
            $this->db->where('a.journal_type_id', $id);
            $this->db->group_by('a.account_number');
            $this->db->order_by('a.flag', 'asc');
            $arr = $this->db->get()->result_array();
        }

        //Mapping Data
        $result = !empty($arr) ? $arr : [];
        echo json_encode($result);
    }

    public function datatable_updates()
    {
        $journal_type_id = base64_decode($this->input->get('journal_type_id'));
        $records = $this->crud->query("SELECT a.*, b.account_name FROM journal_setups a JOIN account_coa b ON a.account_number = b.account_number WHERE a.journal_type_id = '$journal_type_id' ORDER BY a.flag ASC");
        echo json_encode($records);
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post   = $this->input->post();
            $journal_setups = $this->crud->read('journal_setups', [], ["journal_type_id" => $post['journal_type_id'], "account_number" => $post['account_number'], "status" => $post['status']]);
            if (@$journal_setups->id != "") {
                $send = $this->crud->update('journal_setups', ["journal_type_id" => $post['journal_type_id'], "account_number" => $post['account_number'], "status" => $post['status']], $post);
            } else {
                $send = $this->crud->create('journal_setups', $post);
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
        $send = $this->crud->delete('journal_setups', $data);
        echo $send;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=journal_setups_$format.xls");
        }
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.name as journal_name, c.account_name');
        $this->db->from('journal_setups a');
        $this->db->join('journal_types b', 'a.journal_type_id = b.id');
        $this->db->join('account_coa c', 'a.account_number = c.account_number');
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
                            <small>MASTER JOURNAL SETUP</small>
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
                <th>Journame Name</th>
                <th>Account No</th>
                <th>Account Name</th>
                <th>Debit/Credit</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['journal_name'] . '</td>
                    <td>' . $data['account_number'] . '</td>
                    <td>' . $data['account_name'] . '</td>
                    <td>' . $data['status'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
