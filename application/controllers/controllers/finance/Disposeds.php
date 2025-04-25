<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Disposeds extends CI_Controller
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
        $this->form_validation->set_rules('item_id', 'Product No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('finance/disposeds');
        } else {
            redirect('error_access');
        }
    }

    public function readFixeds()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT *
            FROM asset_fixeds
            WHERE status = 0 and (number LIKE '%$post%' or name LIKE '%$post%') 
            ORDER BY name asc");

        echo json_encode($send);
    }

    public function readAssets()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.id, a.number, a.name
            FROM asset_fixeds a JOIN asset_disposals b ON a.id = b.asset_fixed_id
            WHERE (a.number LIKE '%$post%' or a.name LIKE '%$post%') 
            ORDER BY a.name asc");

        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');

            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();

            $filter_from = base64_decode($this->input->get('filter_from'));
            $filter_to = base64_decode($this->input->get('filter_to'));
            $filter_number = base64_decode($this->input->get('filter_number'));

            //Select Query
            $this->db->select('a.*, b.number, b.name, b.cost, b.residual, c.name as asset_category_name, c.type as asset_category_type');
            $this->db->from('asset_disposals a');
            $this->db->join('asset_fixeds b', 'a.asset_fixed_id = b.id');
            $this->db->join('asset_categories c', 'b.asset_category_id = c.id');
            $this->db->where('a.disposal_date >=', $filter_from);
            $this->db->where('a.disposal_date <=', $filter_to);
            $this->db->like('a.asset_fixed_id', $filter_number);
            $this->db->order_by('a.disposal_date', 'asc');

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
            $post   = $this->input->post();
            $send   = $this->crud->create('asset_disposals', $post);
            if ($send) {
                $this->crud->update('asset_fixeds', ["id" => $post['asset_fixed_id']], ["status" => 1]);
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
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            $send = $this->crud->update('asset_disposals', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('asset_disposals', $data);
        echo $send;
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=asset_fixeds_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_from = base64_decode($this->input->get('filter_from'));
        $filter_to = base64_decode($this->input->get('filter_to'));
        $filter_number = base64_decode($this->input->get('filter_number'));

        //Select Query
        $this->db->select('a.*, b.number, b.name, b.cost, b.residual, c.name as asset_category_name, c.type as asset_category_type');
        $this->db->from('asset_disposals a');
        $this->db->join('asset_fixeds b', 'a.asset_fixed_id = b.id');
        $this->db->join('asset_categories c', 'b.asset_category_id = c.id');
        $this->db->where('a.disposal_date >=', $filter_from);
        $this->db->where('a.disposal_date <=', $filter_to);
        $this->db->like('a.asset_fixed_id', $filter_number);
        $this->db->order_by('a.disposal_date', 'asc');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: black;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' .  $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>ASSET DISPOSED</small>
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
                <th>Disposal Date</th>
                <th>Asset Code</th>
                <th>Asset Name</th>
                <th>Asset Category</th>
                <th>Asset Cost</th>
                <th>Residual Value</th>
                <th>Disposal Value</th>
                <th>Net Book Value</th>
                <th>Gain/Loss</th>
                <th>Gain/Loss Value</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td style="text-align:center">' . $no . '</td>
                        <td>' . $data['disposal_date'] . '</td>
                        <td>' . $data['number'] . '</td>
                        <td>' . $data['name'] . '</td>
                        <td>' . $data['asset_category_name'] . '</td>
                        <td>' . $data['cost'] . '</td>
                        <td>' . $data['residual'] . '</td>
                        <td>' . $data['disposal'] . '</td>
                        <td>' . $data['book_value'] . '</td>
                        <td>' . $data['gainloss_type'] . '</td>
                        <td>' . $data['gainloss_value'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
