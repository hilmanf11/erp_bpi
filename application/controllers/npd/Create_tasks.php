<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Create_tasks extends CI_Controller
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
        $this->form_validation->set_rules('id', 'ID', 'required|min_length[1]|max_length[20]|is_unique[create_tasks.id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('npd/create_tasks');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        // $send = $this->crud->query("SELECT * FROM create_tasks WHERE name LIKE '%$post%'AND number != 'FG'");
        $send = $this->crud->reads('create_tasks', ["name" => $post]);
        echo json_encode($send);
    }

    public function readPhases()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        // $send = $this->crud->query("SELECT * FROM project_phases WHERE name LIKE '%$post%'AND number != 'FG'");
        $send = $this->crud->reads('project_phases', ["name" => $post]);
        echo json_encode($send);
    }
    
    public function read_by_phase_ids()
    {
        $phase_ids = $this->input->post('phase_ids');

        if (empty($phase_ids)) {
            echo json_encode([]);
            return;
        }

        $ids_array = explode(',', $phase_ids);
        
        $quoted_ids = array_map(function($id) {
            return "'" . $this->db->escape_str(trim($id)) . "'";
        }, $ids_array);
        
        $phase_ids_formatted = implode(',', $quoted_ids);

        $query = "SELECT 
                a.id as phase_sub_id,
                a.phase_name_sub,
                a.module,
                a.link,
                a.menus_id,
                a.department_id,
                a.department,
                a.sub_department
                FROM project_phase_subs a
                WHERE a.phase_id IN ($phase_ids_formatted) 
                ORDER BY a.phase_id ASC";

        $result = $this->db->query($query)->result_array();
        
        echo json_encode($result);
    }

    public function readProjects()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as project_category_name 
        FROM create_projects a 
        JOIN project_categorys b ON a.project_category_id = b.id
        WHERE a.name LIKE '%$post%' AND a.number LIKE '%$post%' AND a.status = 0");
        echo json_encode($send);
    }

    public function readMenus()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $id = ['20251212000005','20260107000002','20251215000001','20251224000001','20260112000001','20260129000001','20260209000001'];
        $id = implode("','", $id);        
        $send = $this->crud->query("SELECT * FROM menus WHERE id IN ('$id') AND name LIKE '%$post%' AND `status` = '0'");
        // $send = $this->crud->reads('menus', ["name" => $post]);
        echo json_encode($send);
    }

    public function readUsers()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('users', ["name" => $post]);
        echo json_encode($send);
    }
    
    public function readsnotfg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM create_tasks WHERE name LIKE '%$post%' AND `status` = '0'");
        // $send = $this->crud->reads('create_tasks', ["name" => $post]);
        echo json_encode($send);
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
            $this->db->select('a.*, b.start_date as project_start_date, b.end_date as project_end_date');
            $this->db->from('create_tasks a');
            $this->db->join('create_projects b', 'a.project_number = b.number');
            $this->db->where('a.deleted', 0);
            if (@count($filters) > 0) {
                foreach ($filters as $filter) {
                    $this->db->like($filter->field, $filter->value);
                }
            }
            $this->db->order_by('a.id', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Limit 1 - 10
            $this->db->limit($rows, $offset);
            //Get Data Array
            $records = $this->db->get()->result_array();

            foreach ($records as &$row) {
                if (!empty($row['project_start_date']) && !empty($row['project_end_date'])) {
                            
                    $start = new DateTime($row['project_start_date']);
                    $end = new DateTime($row['project_end_date']);
                    
                    $start->setTime(12, 0, 0);
                    $end->setTime(12, 0, 0);
                    
                    $end->modify('+1 day');

                    $interval = $start->diff($end);
                    
                    $duration_text = [];
                    if ($interval->y > 0) { $duration_text[] = $interval->y . ' Year' . ($interval->y > 1 ? 's' : ''); }
                    if ($interval->m > 0) { $duration_text[] = $interval->m . ' Month' . ($interval->m > 1 ? 's' : ''); }
                    if ($interval->d > 0) { $duration_text[] = $interval->d . ' Day' . ($interval->d > 1 ? 's' : ''); }
                    
                    $row['project_duration'] = empty($duration_text) ? '0 Days' : implode(', ', $duration_text);
                    
                } else {
                    $row['project_duration'] = '-';
                }
            }

            // Return ke EasyUI
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    public function datatableDetails()
    {
        $number = base64_decode($this->input->get('project_number'));

        if ($number) {
            $this->db->select('b.*, a.project_category');
            $this->db->from('create_tasks a');
            $this->db->join('create_task_details b', 'a.id = b.task_id');
            $this->db->where('a.project_number', $number);
            $records = $this->db->get()->result_array();

            if (!empty($records)) {
                foreach ($records as &$item) {
                    
                    if (!empty($item['start_date']) && !empty($item['end_date']) && $item['start_date'] != '0000-00-00' && $item['end_date'] != '0000-00-00') {
                        
                        $start = new DateTime($item['start_date']);
                        $end = new DateTime($item['end_date']);
                        
                        $start->setTime(12, 0, 0);
                        $end->setTime(12, 0, 0);
                        
                        $end->modify('+1 day');

                        $interval = $start->diff($end);
                        
                        $duration_text = [];
                        if ($interval->y > 0) { $duration_text[] = $interval->y . ' Year' . ($interval->y > 1 ? 's' : ''); }
                        if ($interval->m > 0) { $duration_text[] = $interval->m . ' Month' . ($interval->m > 1 ? 's' : ''); }
                        if ($interval->d > 0) { $duration_text[] = $interval->d . ' Day' . ($interval->d > 1 ? 's' : ''); }
                        
                        $item['duration'] = empty($duration_text) ? '0 Days' : implode(', ', $duration_text);
                        
                    } else {
                        $item['duration'] = '-';
                    }
                }
                echo json_encode($records);
            } else {
                echo json_encode([]);
            }
        } else {
            echo json_encode([]);
        }
    }

    // UPLOAD GAMBAR SUMMERNOTE
    public function upload_image_summernote() {
        if (isset($_FILES["file"]["name"])) {
            $config['upload_path']   = './assets/uploads/descriptions/'; 
            $config['allowed_types'] = 'jpg|jpeg|png|gif|pdf';
            $config['max_size']      = 5000; 
            $config['encrypt_name']  = TRUE; 

            $this->load->library('upload', $config);
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('file')) {
                echo $this->upload->display_errors();
            } else {
                $data = $this->upload->data();
                
                $image_url = base_url('assets/uploads/descriptions/' . $data['file_name']);
                echo $image_url;
            }
        }
    }

    public function uploadatt()
    {
        // Pastikan file disimpan dalam direktori yang diinginkan
        $uploadDir = 'assets/image/create_tasks/';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Pastikan ada file yang diunggah dari permintaan
            if (isset($_FILES['file'])) {
                $file = $_FILES['file'];

                // Validasi ekstensi file yang diunggah
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($fileExtension, $allowedExtensions)) {
                    echo json_encode(['success' => false, 'message' => 'Only files with the extension .pdf, .jpg, or .png are allowed.']);
                    exit; // Menghentikan proses lebih lanjut jika ekstensi tidak valid
                }

                // Validasi ukuran file yang diunggah (maksimal 5MB)
                $maxFileSize = 5 * 1024 * 1024; // 5MB dalam bytes
                if ($file['size'] > $maxFileSize) {
                    echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB yang diperbolehkan.']);
                    exit; // Menghentikan proses lebih lanjut jika ukuran terlalu besar
                }

                // Pastikan tidak ada error dalam proses upload
                if ($file['error'] === UPLOAD_ERR_OK) {
                    // Buat nama unik untuk file yang diunggah
                    $fileName = uniqid() . '_' . $file['name'];
                    $uploadPath = $uploadDir . $fileName;

                    // Pindahkan file dari temporary directory ke lokasi yang diinginkan
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        // File berhasil diunggah
                        echo json_encode(['success' => true, 'message' => 'File Upload Success.', 'filename' => $fileName]);
                    } else {
                        // Gagal menyimpan file
                        echo json_encode(['success' => false, 'message' => 'File Upload Failed.']);
                    }
                } else {
                    // Ada error dalam proses upload
                    echo json_encode(['success' => false, 'message' => 'Error while Upload.']);
                }
            } else {
                // File tidak ditemukan dalam permintaan
                echo json_encode(['success' => false, 'message' => 'File Not Found.']);
            }
        } else {
            // Metode request yang diperlukan adalah POST
            echo json_encode(['success' => false, 'message' => 'Metode request yang diperlukan adalah POST.']);
        }
    }

    //CREATE DATA TASK
    public function create() {
        $project_number = $this->input->post('project_number');
        
        $this->db->where('project_number', $project_number);
        $cek_duplikat = $this->db->get('create_tasks')->num_rows();
        
        if ($cek_duplikat > 0) {
            echo json_encode(['theme' => 'error', 'message' => 'Project Number has already add.']);
            return; 
        }

        $details_json = $this->input->post('details');

        // 1. Tampung data master
        $data_project = array(
            'project_number'      => $project_number,
            'project_name'        => $this->input->post('project_name'),
            'project_level'       => $this->input->post('project_level'),
            'project_category_id' => $this->input->post('project_category_id'),
            'project_category'    => $this->input->post('project_category'),
            'phase_id'            => $this->input->post('phase_id'),
            'phase_name'          => $this->input->post('phase_name'),
            'event'               => $this->input->post('event'),
            'attachment1'         => $this->input->post('attachment1'),
            'attachment2'         => $this->input->post('attachment2'),
            'attachment3'         => $this->input->post('attachment3'),
            'attachment4'         => $this->input->post('attachment4'),
            'attachment5'         => $this->input->post('attachment5'),
            'description'         => $this->input->post('description'),
            'remark'              => $this->input->post('remark')
        );

        $insert = $this->crud->create('create_tasks', $data_project);
        
        if ($insert) {
            $this->db->select('id');
            $this->db->where('project_number', $data_project['project_number']);
            $this->db->where('phase_id', $data_project['phase_id']);
            $this->db->order_by('id', 'DESC');
            $task = $this->db->get('create_tasks')->row();
            
            if ($task) {
                $details = json_decode($details_json, true);
                
                if (!empty($details)) {
                    $batch_data = [];
                    foreach ($details as $row) {
                        $batch_data[] = array(
                            'task_id'        => $task->id, 
                            'phase_sub_id'   => $row['phase_sub_id'] ?? null,
                            'phase_name_sub' => $row['phase_name_sub'] ?? null,
                            'module'         => $row['module'] ?? null,
                            'link'           => $row['link'] ?? null,
                            'menus_id'       => $row['menus_id'] ?? null,
                            'department_id'  => $row['department_id'] ?? null,
                            'department'     => $row['department'] ?? null,
                            'sub_department' => $row['sub_department'] ?? null,
                            'level'          => $this->input->post('project_level') ?? null,
                            'start_date'     => (!empty($row['start_date'])) ? date('Y-m-d', strtotime($row['start_date'])) : null,
                            'end_date'       => (!empty($row['end_date'])) ? date('Y-m-d', strtotime($row['end_date'])) : null,
                            'remark'         => $row['remark'] ?? null
                        );
                    }
                    $this->db->insert_batch('create_task_details', $batch_data);
                }
            }
            echo json_encode(['theme' => 'success', 'message' => 'Task and all details successfully saved!']);
        } else {
            echo json_encode(['theme' => 'error', 'message' => 'Failed to save task data.']);
        }
    }

    // UPDATE DATA
    public function update() {
        $id = $this->input->get('id');

        if ($id) {
            $details_json = $this->input->post('details');
            $details = json_decode($details_json, true);

            $data_update = array(
                'project_name'        => $this->input->post('project_name'),
                'project_level'       => $this->input->post('project_level'),
                'project_category_id' => $this->input->post('project_category_id'),
                'project_category'    => $this->input->post('project_category'),
                'phase_id'            => $this->input->post('phase_id'),
                'phase_name'          => $this->input->post('phase_name'),
                'event'               => $this->input->post('event'),
                'attachment1'         => $this->input->post('attachment1'),
                'attachment2'         => $this->input->post('attachment2'),
                'attachment3'         => $this->input->post('attachment3'),
                'attachment4'         => $this->input->post('attachment4'),
                'attachment5'         => $this->input->post('attachment5'),
                'description'         => $this->input->post('description'),
                'remark'              => $this->input->post('remark')
            );

            $this->db->trans_start();

            // 1. Update Tabel Master
            $this->db->where('id', $id);
            $this->db->update('create_tasks', $data_update);

            // 2. Hapus Semua Detail Lama (berdasarkan ID varchar)
            $this->db->delete('create_task_details', array('task_id' => $id));

            // 3. Masukkan Detail Baru 
            if (!empty($details)) {
                $detail_data = [];
                foreach ($details as $row) {
                    $detail_data[] = array(
                        'task_id'        => $id, // ID langsung pakai dari $_GET['id']
                        'phase_sub_id'   => $row['phase_sub_id'] ?? null,
                        'phase_name_sub' => $row['phase_name_sub'] ?? null,
                        'module'         => $row['module'] ?? null,
                        'link'           => $row['link'] ?? null,
                        'menus_id'       => $row['menus_id'] ?? null,
                        'department_id'  => $row['department_id'] ?? null,
                        'department'     => $row['department'] ?? null,
                        'sub_department' => $row['sub_department'] ?? null,
                        'level'          => $row['level'] ?? null,
                        'start_date'     => (!empty($row['start_date'])) ? date('Y-m-d', strtotime($row['start_date'])) : null,
                        'end_date'       => (!empty($row['end_date'])) ? date('Y-m-d', strtotime($row['end_date'])) : null,
                        'remark'         => $row['remark'] ?? null
                    );
                }
                $this->db->insert_batch('create_task_details', $detail_data);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === TRUE) {
                echo json_encode(['theme' => 'success', 'message' => 'Task successfully updated!']);
            } else {
                echo json_encode(['theme' => 'error', 'message' => 'Failed to update task data.']);
            }
        } else {
            echo json_encode(['theme' => 'error', 'message' => 'Task ID not found.']);
        }
    }

    // DELETE DATA
    public function delete() {
        $id = $this->input->post('id');
        if ($id) {
            $this->db->trans_start();
            $this->db->delete('create_task_details', array('task_id' => $id));
            $this->db->delete('create_tasks', array('id' => $id));
            $this->db->trans_complete();
            if ($this->db->trans_status() === TRUE) {
                echo json_encode(['theme' => 'success', 'message' => 'Task successfully deleted!']);
            } else {
                echo json_encode(['theme' => 'error', 'message' => 'Failed to delete task.']);
            }
        } else {
            echo json_encode(['theme' => 'error', 'message' => 'Task ID not found.']);
        }
    }

    public function get_details() {
        $task_id = $this->input->get('task_id');
        $details = $this->db->get_where('create_task_details', array('task_id' => $task_id))->result_array();
        echo json_encode($details);
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=create_tasks_$format.xls");
        }

        // Config Data
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Select Query Project
        $this->db->select('a.*, b.start_date as project_start_date, b.end_date as project_end_date');
        $this->db->from('create_tasks a');
        $this->db->join('create_projects b', 'a.project_number = b.number');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        // --- PROSES PERHITUNGAN DURASI ---
        foreach ($records as &$row) {
            if (!empty($row['project_start_date']) && !empty($row['project_end_date'])) {
                $start = new DateTime($row['project_start_date']);
                $end = new DateTime($row['project_end_date']);
                $interval = $start->diff($end);
                
                $duration_text = [];
                if ($interval->y > 0) { $duration_text[] = $interval->y . ' Year' . ($interval->y > 1 ? 's' : ''); }
                if ($interval->m > 0) { $duration_text[] = $interval->m . ' Month' . ($interval->m > 1 ? 's' : ''); }
                if ($interval->d > 0) { $duration_text[] = $interval->d . ' Day' . ($interval->d > 1 ? 's' : ''); }
                
                $row['duration'] = empty($duration_text) ? '0 Days' : implode(', ', $duration_text);
            } else {
                $row['duration'] = '-'; 
            }
        }
        // ----------------------------------

        // Build HTML
        $html = '<html><head><title>Print Data Project</title></head>
        <style>
            body {font-family: Arial, Helvetica, sans-serif;}
            #customers {border-collapse: collapse; width: 100%; font-size: 12px;}
            #customers td, #customers th {border: 1px solid #ddd; padding: 4px;}
            #customers tr:nth-child(even){background-color: #f2f2f2;}
            #customers tr:hover {background-color: #ddd;}
            #customers th {padding-top: 6px; padding-bottom: 6px; text-align: left; color: black; background-color: #e0e0e0;}
        </style>
        <body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; margin-right:10px;">
                            <img src="' . ($config->favicon ?? '') . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . ($config->name ?? 'Company Name') . '</b>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date : ' . date("d M Y H:i:s") . ' <br>
                Print By : ' . $this->session->userdata('username') . '  
            </div>
            <br><br>
            <div style="clear: both; font-size: 16px; text-align: center; margin-top: 15px; margin-bottom: 15px;">
                <h3>CREATE TASKS</h3>
            </div>
        </center>
        
        <table id="customers" border="1">
            <tr>
                <th width="30" style="text-align: center;">No</th>
                <th>Project No</th>
                <th>Project Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Duration</th>
                <th>Level</th>
                <th>Category</th>
            </tr>';
            
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td style="text-align: center;">' . $no . '</td>
                    <td>' . $data['project_number'] . '</td>
                    <td>' . $data['project_name'] . '</td>
                    <td>' . $data['project_start_date'] . '</td>
                    <td>' . $data['project_end_date'] . '</td>
                    <td>' . $data['duration'] . '</td>
                    <td style="text-align: center;">' . $data['project_level'] . '</td>
                    <td>' . $data['project_category'] . '</td>
                </tr>';
            $no++;
        }
        
        $html .= '</table></body></html>';
        
        echo $html;
    }
}
