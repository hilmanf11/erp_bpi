<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Setting_users extends CI_Controller
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
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $this->load->view('template/header');
            $this->load->view('admin/setting_users');
        } else {
            redirect('error_access');
        }
    }
    public function getUsers()
    {
        $post = isset($_POST['q']) ? array("name" => $_POST['q']) : $this->input->get();
        $send = $this->crud->reads('users', $post, ["deleted" => 0, "actived" => 0]);
        echo json_encode($send);
    }
    function hasChild($id)
    {
        $menus = $this->crud->reads("menus", [], ["menus_id" => $id]);
        return count($menus) > 0 ? true : false;
    }
    public function datatables()
    {
        if ($this->input->get('users_id')) {
            $id = $this->input->post('id');
            $nama = $this->input->get('users_id');
            $result = array();
            if ($id === "0") {
                $rs = $this->db->query("SELECT count(*) as total from menus where menus_id=''");
                $row = $rs->row();
                $result['total'] = $row->total;
                $query  = $this->db->query("SELECT a.m_view,a.m_add,a.m_edit,a.m_delete,a.m_upload,a.m_download,a.m_print,a.m_excel,b.v_view, b.v_add,b.v_edit,b.v_delete,b.v_upload,b.v_download,b.v_print,b.v_excel, c.name, c.id, b.id as id_user_set 
                                        FROM setting_menus a 
                                        JOIN setting_users b on a.menus_id = b.menus_id 
                                        JOIN menus c on a.menus_id = c.id 
                                        WHERE b.users_id = '$nama' and c.menus_id='' ORDER BY c.sort asc");
                $items = array();
                foreach ($query->result_array() as $data) {
                    $data['state'] = $this->haschild($data['id']) ? 'closed' : 'open';
                    array_push($items, $data);
                }
                $result["rows"] = $items;
            } else {
                $query  = $this->db->query("SELECT a.m_view,a.m_add,a.m_edit,a.m_delete,a.m_upload,a.m_download,a.m_print,a.m_excel,b.v_view, b.v_add,b.v_edit,b.v_delete,b.v_upload,b.v_download,b.v_print,b.v_excel, c.name, c.id, b.id as id_user_set
                                        FROM setting_menus a 
                                        JOIN setting_users b on a.menus_id = b.menus_id 
                                        JOIN menus c on a.menus_id = c.id 
                                        WHERE b.users_id = '$nama' and c.menus_id='$id' ORDER BY c.sort asc");
                $items = array();
                foreach ($query->result_array() as $data) {
                    $data['state'] = $this->haschild($data['id']) ? 'closed' : 'open';
                    array_push($result, $data);
                }
            }
            echo json_encode($result);
        }
    }
    public function create()
    {
        if (!empty($this->session->username)) {
            if ($this->input->post()) {
                $post = $this->input->post();
                $sql = $this->db->query("SELECT * from setting_menus order by menus_id asc");
                foreach ($sql->result_array() as $data) {
                    //Ambil data yang belum terdaftar di setting user
                    $menus_id = $data['menus_id'];
                    $sql_cek2 = $this->db->query("SELECT * FROM setting_users WHERE menus_id = '$menus_id' and users_id = '$post[users_id]'");
                    $row_cek2 = $sql_cek2->num_rows();
                    if ($row_cek2 > 0) {
                        //jika data sudah ada maka tidak ada proses
                    } else {
                        //Jika data belum ada maka terjadi insert data di setting user
                        $value  = array(
                            'users_id'       => $post['users_id'],
                            'menus_id'       => $menus_id
                        );
                        $send   = $this->crud->create('setting_users', $value);
                    }
                }
            } else {
                show_error("Cannot Process your request");
            }
        } else {
            show_error("Your Session has been Expired");
        }
    }
    public function update()
    {
        if ($this->input->post()) {
            $id     = base64_decode($this->input->get('id'));
            $post   = $this->input->post();
            $send   = $this->crud->update('setting_users', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=setting_users_$format.xls");
        }
        //Config
        $config = $this->crud->read('config');
        $name = $this->input->get('name');
        $this->db->select('a.*, b.name as menu_name');
        $this->db->from('setting_users a');
        $this->db->join('menus b', 'a.menus_id = b.id', 'join');
        $this->db->where('a.deleted', 0);
        $this->db->like('b.name', $name);
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
                            <small>USER SETTING</small>
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
                <th>Menu Name</th>
                <th>View</th>
                <th>Add</th>
                <th>Edit</th>
                <th>Delete</th>
                <th>Upload</th>
                <th>Download</th>
                <th>Print</th>
                <th>Excel</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['menu_name'] . '</td>
                        <td>' . $data['m_view'] . '</td>
                        <td>' . $data['m_add'] . '</td>
                        <td>' . $data['m_edit'] . '</td>
                        <td>' . $data['m_delete'] . '</td>
                        <td>' . $data['m_upload'] . '</td>
                        <td>' . $data['m_download'] . '</td>
                        <td>' . $data['m_print'] . '</td>
                        <td>' . $data['m_excel'] . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
