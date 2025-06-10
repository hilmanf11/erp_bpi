<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Locks extends CI_Controller
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
            $this->load->view('closing/locks');
        } else {
            redirect('error_access');
        }
    }

    public function getPeriod()
    {
        $send = $this->crud->reads('account_period');
        echo json_encode($send);
    }

    public function checkLock()
    {
        if($this->input->post()){
            $post = $this->input->post();
            $period = $post['period'];
            $menus_id = $post['menus_id'];

            $change = date("F Y", strtotime($period));

            $reads = $this->crud->reads('account_lock', [], ["period" => $change, "menus_id" => $menus_id, "lock" => 1]);
            die(json_encode(array("total" => count($reads))));
        }
    }

    public function datatables()
    {
        if ($this->input->get('period')) {
            $period = $this->input->get('period');

            $result = array();
            $query  = $this->db->query("SELECT a.period, b.name, a.lock, b.id, a.id as period_id, a.updated_by, a.updated_date
                FROM account_lock a 
                JOIN menus b on a.menus_id = b.id 
                WHERE a.period = '$period' ORDER BY b.name asc");
            $datas = $query->result_array();

            $result["rows"] = $datas;
            echo json_encode($result);
        }
    }

    public function create()
    {
        if (!empty($this->session->username)) {
            if ($this->input->post()) {
                $period = $this->input->post('period');

                $sql = $this->db->query("SELECT * from menus where accounting = 'YES' order by menus_id asc");
                foreach ($sql->result_array() as $data) {
                    //Ambil data yang belum terdaftar di setting user
                    $menus_id = $data['id'];

                    $sql_cek2 = $this->db->query("SELECT * FROM account_lock WHERE menus_id = '$menus_id' and `period` = '$period'");
                    $row_cek2 = $sql_cek2->num_rows();

                    if ($row_cek2 > 0) {
                        //jika data sudah ada maka tidak ada proses
                    } else {
                        //Jika data belum ada maka terjadi insert data di setting user
                        $value  = array(
                            'period' => $period,
                            'menus_id' => $menus_id
                        );

                        $this->crud->create('account_lock', $value);
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
            $send   = $this->crud->update('account_lock', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function updateAll(){
        if ($this->input->post()) {
            $post   = $this->input->post();

            if($post['check'] == "true"){
                $lock = 1;
            }else{
                $lock = 0;
            }

            $send = $this->crud->update('account_lock', ["period" => $post['period']], ["lock" => $lock]);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }
}
