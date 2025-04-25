<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Menus extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        //VALIDASI FORM
        $this->form_validation->set_rules('name', 'Name', 'required|min_length[2]|max_length[50]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('admin/menus');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function getmenu()
    {
        $post = isset($_POST['q']) ? array("a.name" => $_POST['q']) : $this->input->get();
        $this->db->select('a.id, a.name, b.name as parent_name');
        $this->db->from('menus a');
        $this->db->join('menus b', 'a.menus_id = b.id', 'left');
        $this->db->like($post);
        $this->db->order_by('a.menus_id', 'asc');
        $this->db->order_by('a.name', 'asc');
        $menus = $this->db->get()->result_array();
        echo json_encode($menus);
    }
    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $filters = json_decode($this->input->post('filterRules'));
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.name as parent_name');
            $this->db->from('menus a');
            $this->db->join('menus b', 'a.menus_id = b.id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.status', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    if ($filter->field == "parent_name") {
                        $this->db->like("b.name", $filter->value);
                    } elseif ($filter->field == "name") {
                        $this->db->like("a.name", $filter->value);
                    } elseif ($filter->field == "link") {
                        $this->db->like("a.link", $filter->value);
                    } elseif ($filter->field == "sort") {
                        $this->db->like("a.sort", $filter->value);
                    } elseif ($filter->field == "state") {
                        $this->db->like("a.state", $filter->value);
                    }
                }
            }
            $this->db->order_by('b.name', 'ASC');
            $this->db->order_by('a.sort', 'ASC');
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
    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            if ($this->form_validation->run() == TRUE) {
                $post = $this->input->post();
                $menus = $this->crud->create('menus', $post);
                echo $menus;
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
            $id = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $menus = $this->crud->update('menus', ["id" => $id], $post);
            echo $menus;
        } else {
            show_error("Cannot Process your request");
        }
    }
    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $menus = $this->crud->delete('menus', $data);
        echo $menus;
    }
    //PRINT & EXCEl DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=menus_$format.xls");
        }
        //CONFIG
        $config = $this->crud->read('config');
        $name = $this->input->get('name');
        $this->db->select('a.*, b.name as parent_name');
        $this->db->from('menus a');
        $this->db->join('menus b', 'a.menus_id = b.id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->like('a.name', $name);
        $this->db->order_by('b.name', 'ASC');
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
                            <small>MASTER MENU</small>
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
                <th>Parent Menu</th>
                <th>Menu Name</th>
                <th>Link</th>
                <th>Sort</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['parent_name'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['link'] . '</td>
                    <td>' . $data['sort'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
