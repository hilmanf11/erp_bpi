<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Input $input
 * @property CI_Output $output
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 * @property CI_Form_validation $form_validation
 * @property Crud $crud
 */
class DeveloperLog extends CI_Controller
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
        if (!empty($this->session->username)) {
            $module_id = $this->input->get('module') ?? null;

            // Get data dari menus dimana link tidak null
            $data['modules'] = $this->db->from('menus')->where(['link !=' => null])->order_by('name','asc')->get()->result();

            // Get logs dengan JOIN ke tabel menus
            $this->db->select('feature_updates.*, menus.name as module_name');
            $this->db->from('feature_updates');
            $this->db->join('menus', 'menus.id = feature_updates.module_id', 'left');
            if (!empty($module_id)) {
                $this->db->where('module_id', $module_id);
            }
            $this->db->order_by('created_at', 'DESC'); // Tampil latest update di atas
            $data['feature_updates'] = $this->db->get()->result();

            $data['active_module_name'] = '';
            if ($module_id) {
                foreach ($data['modules'] as $m) {
                    if ($m->id == $module_id) {
                        $data['active_module_name'] = $m->name;
                        break;
                    }
                }
            }
    
            $this->load->view('template/header', $data);
            $this->load->view('developer/devlog_index');
        } else {
            redirect('error_access');
        }
    }

    public function get_detail($id) 
    {
        $this->db->select('feature_updates.*, menus.name as module_name');
        $this->db->from('feature_updates');
        $this->db->join('menus', 'menus.id = feature_updates.module_id', 'left');
        $this->db->where('feature_updates.id', $id);
        $data = $this->db->get()->row();

        if ($data) {
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
        }
    }

    function createTable() 
    {
        $sql = "CREATE TABLE feature_updates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                module_id VARCHAR(30) NOT NULL, -- menus.id
                status ENUM('local', 'dummy', 'live') DEFAULT 'local',
                feature_name VARCHAR(255) NOT NULL,
                feature_detail TEXT NOT NULL,
                change_controller TEXT, 
                change_view TEXT,
                change_db TEXT,
                change_menu VARCHAR(255),
                feature_developer VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                -- Gunakan INDEX biasa, bukan CONSTRAINT
                INDEX (module_id) 
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }


    public function save() {
        // 1. Set Rules Validasi
        $this->form_validation->set_rules('module_id', 'Modul', 'required|numeric');
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[local,dummy,live]');
        $this->form_validation->set_rules('feature_name', 'Feature Name', 'required|trim|max_length[255]');
        $this->form_validation->set_rules('feature_detail', 'Detail', 'required');
        $this->form_validation->set_rules('feature_developer', 'Developer Name', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            // Jika validasi gagal, kembalikan ke form dengan error
            // Anda bisa menggunakan flashdata untuk pesan error di Tailwind
            $this->session->set_flashdata('error', validation_errors());
            redirect('developerlog'); 
        } else {
            // 2. Siapkan Data untuk Insert
            $data = [
                'module_id'         => $this->input->post('module_id'),
                'status'            => $this->input->post('status'),
                'feature_name'      => $this->input->post('feature_name'),
                'feature_detail'    => $this->input->post('feature_detail'),
                'change_controller' => $this->input->post('change_controller'),
                'change_view'       => $this->input->post('change_view'),
                'change_db'         => $this->input->post('change_db'),
                'change_menu'       => $this->input->post('change_menu'), // Contoh: Finance >> Trans >> AP
                'feature_developer' => $this->input->post('feature_developer'),
                'created_at'        => date('Y-m-d H:i:s')
            ];

            // 3. Eksekusi Simpan (Menggunakan Query Builder)
            $insert = $this->db->insert('feature_updates', $data);

            if ($insert) {
                $this->session->set_flashdata('success', 'Log fitur berhasil disimpan!');
            } else {
                $this->session->set_flashdata('error', 'Gagal menyimpan ke database.');
            }

            redirect('developerlog');
        }
    }

}
