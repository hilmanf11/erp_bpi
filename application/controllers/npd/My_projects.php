<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class My_projects extends CI_Controller
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
        $this->form_validation->set_rules('id', 'ID', 'required|min_length[1]|max_length[20]|is_unique[my_projects.id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('npd/my_projects');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        // $send = $this->crud->query("SELECT * FROM my_projects WHERE name LIKE '%$post%'AND number != 'FG'");
        $send = $this->crud->reads('my_projects', ["name" => $post]);
        echo json_encode($send);
    }

    public function readProjects()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*, b.name as project_category_name 
        FROM create_projects a 
        JOIN project_categorys b ON a.project_category_id = b.id
        WHERE a.name LIKE '%$post%' AND a.number LIKE '%$post%'");
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
        $send = $this->crud->query("SELECT * FROM my_projects WHERE name LIKE '%$post%' AND `status` = '0'");
        // $send = $this->crud->reads('my_projects', ["name" => $post]);
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            $page = isset($page) ? intval($page) : 1;
            $rows = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;

            // 1. Ambil Data Utama (Project Header)
            $this->db->select('a.id as project_id, 
                            a.number as project_number, 
                            a.name as project_name,
                            a.start_date,
                            a.end_date');
            $this->db->from('create_projects a');
            $this->db->where('a.status', 0); 
            $this->db->order_by('a.id', 'asc');

            $totalRows = $this->db->count_all_results('', false);
            $this->db->limit($rows, $offset);
            $records = $this->db->get()->result_array();

            $today_dt = new DateTime();
            $today_dt->setTime(0, 0, 0);

            // 2. Perulangan untuk setiap Project
            foreach ($records as &$row) {
                $total_task = 0;
                $complete_count = 0;
                
                $max_overdue_days = -1;
                $max_overdue_interval = null;

                $this->db->select('t_det.end_date, t_det.link, p_det.item_fg_number');
                $this->db->from('create_tasks t');
                $this->db->join('create_task_details t_det', 't.id = t_det.task_id', 'inner');
                $this->db->join('create_project_details p_det', 't.project_number = (SELECT number FROM create_projects WHERE id = p_det.create_project_id)', 'inner');
                $this->db->where('t.project_number', $row['project_number']);
                
                $this->db->group_by('t_det.id'); 
                $tasks = $this->db->get()->result_array();

                if (!empty($tasks)) {
                    $total_task = count($tasks);
                    
                    foreach ($tasks as $t) {
                        $target_table = basename($t['link']);
                        $item_fg_number = $t['item_fg_number'];
                        
                        // ==========================================
                        // 1. FILTER KEAMANAN END_DATE TASK
                        // ==========================================
                        $task_end_dt = null;
                        if (!empty($t['end_date']) && $t['end_date'] !== '0000-00-00') {
                            $clean_end_date = str_replace('/', '-', $t['end_date']);
                            $ts_end = strtotime($clean_end_date);
                            if ($ts_end !== false && $ts_end > 0) { 
                                $task_end_dt = new DateTime(date('Y-m-d', $ts_end));
                                $task_end_dt->setTime(0, 0, 0);
                            }
                        }

                        if (!empty($target_table) && $this->db->table_exists($target_table)) {
                            
                            $this->db->select_max('created_date');
                            if ($this->db->field_exists('item_fg_number', $target_table)) {
                                $this->db->where('item_fg_number', $item_fg_number);
                            } else if ($this->db->field_exists('number', $target_table)) {
                                $this->db->where('number', $item_fg_number);
                            }
                            $target_data = $this->db->get($target_table)->row_array();

                            // ==========================================
                            // 2. FILTER KEAMANAN CREATED_DATE
                            // ==========================================
                            $created_dt = null;
                            if (!empty($target_data['created_date']) && $target_data['created_date'] !== '0000-00-00 00:00:00') {
                                $ts_created = strtotime($target_data['created_date']);
                                if ($ts_created !== false && $ts_created > 0) {
                                    $created_dt = new DateTime(date('Y-m-d', $ts_created));
                                    $created_dt->setTime(0, 0, 0);
                                }
                            }

                            // Task dianggap beres HANYA JIKA ada created_date yang valid
                            $is_task_done = ($created_dt !== null);

                            if ($is_task_done) {
                                $complete_count++;
                                
                                // KONDISI 1: COMPLETE TAPI TERLAMBAT
                                if ($task_end_dt && $created_dt > $task_end_dt) {
                                    $diff = $task_end_dt->diff($created_dt);
                                    if ($diff->days > $max_overdue_days) {
                                        $max_overdue_days = $diff->days;
                                        $max_overdue_interval = $diff;
                                    }
                                }
                            } else {
                                // KONDISI 2: UNCOMPLETE DAN HARI INI MELEWATI END DATE
                                if ($task_end_dt && $today_dt > $task_end_dt) {
                                    $diff = $task_end_dt->diff($today_dt);
                                    if ($diff->days > $max_overdue_days) {
                                        $max_overdue_days = $diff->days;
                                        $max_overdue_interval = $diff;
                                    }
                                }
                            }
                        }
                    }
                }

                // --- FORMAT TEKS OVERDUE PROJECT ---
                if ($max_overdue_days > 0 && $max_overdue_interval) {
                    $overdue_text = [];
                    if ($max_overdue_interval->y > 0) $overdue_text[] = $max_overdue_interval->y . ' Year' . ($max_overdue_interval->y > 1 ? 's' : '');
                    if ($max_overdue_interval->m > 0) $overdue_text[] = $max_overdue_interval->m . ' Month' . ($max_overdue_interval->m > 1 ? 's' : '');
                    if ($max_overdue_interval->d > 0) $overdue_text[] = $max_overdue_interval->d . ' Day' . ($max_overdue_interval->d > 1 ? 's' : '');
                    
                    $row['overdue'] = empty($overdue_text) ? '0 Days' : implode(', ', $overdue_text);
                } else {
                    $row['overdue'] = '0 Days'; 
                }

                // --- PROGRESS ---
                $row['total_task'] = $total_task;
                $row['progress']   = ($total_task > 0) ? round(($complete_count / $total_task) * 100) : 0;

                // --- LOGIKA DURASI PROJECT ---
                if (!empty($row['start_date']) && !empty($row['end_date'])) {
                    $start = new DateTime($row['start_date']);
                    $end = new DateTime($row['end_date']);
                    $start->setTime(12, 0, 0);
                    $end->setTime(12, 0, 0);
                    $end->modify('+1 day');
                    $interval = $start->diff($end);
                    
                    $duration_text = [];
                    if ($interval->y > 0) $duration_text[] = $interval->y . ' Year' . ($interval->y > 1 ? 's' : '');
                    if ($interval->m > 0) $duration_text[] = $interval->m . ' Month' . ($interval->m > 1 ? 's' : '');
                    if ($interval->d > 0) $duration_text[] = $interval->d . ' Day' . ($interval->d > 1 ? 's' : '');
                    $row['duration'] = empty($duration_text) ? '0 Days' : implode(', ', $duration_text);
                } else {
                    $row['duration'] = '-';
                }
            }

            $result['total'] = $totalRows;
            $result['rows'] = $records;
            echo json_encode($result);
        }
    }

    public function datatableDetails()
    {
        $number = base64_decode($this->input->get('project_number'));

        if ($number) {
            $this->db->select('
                e.*, 
                a.number, 
                a.name, 
                b.name as customer_name, 
                "-" as model_number, 
                a.start_date, 
                a.end_date, 
                d.name as division
            '); 
            
            $this->db->from('create_projects a');
            $this->db->join('create_project_details e', 'a.id = e.create_project_id', 'inner'); 
            $this->db->join('customers b', 'a.customer_id = b.id', 'left');
            $this->db->join('divisions d', 'a.division_id = d.id', 'left');
            
            $this->db->where('a.number', $number);
            
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

    public function datatableTasks() //dokumentasi : penarapan tanpa item id
    {
        $number = base64_decode($this->input->get('project_number'));

        if ($number) {
            $this->db->select('a.id as task_id, a.phase_name, a.description, a.attachment1, a.attachment2, a.attachment3, a.attachment4, a.attachment5, b.status as project_status, d.item_fg_number'); 
            $this->db->from('create_tasks a');
            $this->db->join('create_projects b', 'a.project_number = b.number');
            $this->db->join('create_project_details d', 'b.id = d.create_project_id', 'left');
            $this->db->where('a.project_number', $number);
            $this->db->group_by('a.id');
            
            $header_row = $this->db->get()->row();

            if ($header_row) {
                $uncomplete_count = 0;
                $today_ts = strtotime(date('Y-m-d'));

                $this->db->select('*');
                $this->db->from('create_task_details');
                $this->db->where('task_id', $header_row->task_id);
                $task_details = $this->db->get()->result_array();

                if (!empty($task_details)) {
                    foreach ($task_details as &$item) {
                        $item['phase_name']   = $header_row->phase_name;
                        $item['description']  = $header_row->description;
                        $item['attachment1']  = $header_row->attachment1;
                        $item['attachment2']  = $header_row->attachment2;
                        $item['attachment3']  = $header_row->attachment3;
                        $item['attachment4']  = $header_row->attachment4;
                        $item['attachment5']  = $header_row->attachment5;
                        
                        $item['model_number'] = "-"; 
                        
                        $item_fg_number_to_check = $header_row->item_fg_number; 

                        // ==========================================
                        // NILAI DEFAULT
                        // ==========================================
                        $item['status']      = 'UNCOMPLETE';  
                        $item['status_val']  = 0;            
                        $item['status_time'] = 'On Progress'; 

                        $task_end_ts = 0;
                        if (!empty($item['end_date'])) {
                            $parsed_date = strtotime($item['end_date']);
                            if ($parsed_date !== false && $parsed_date > 0) {
                                $task_end_ts = strtotime(date('Y-m-d', $parsed_date));
                            }
                        }

                        $link = isset($item['link']) ? $item['link'] : ''; 

                        if (!empty($link)) {
                            $target_table = basename($link); 

                            if ($this->db->table_exists($target_table)) {
                                $this->db->select_max('created_date');
                                
                                // Pengecekan ke target tabel
                                if ($this->db->field_exists('item_fg_number', $target_table)) {
                                    $this->db->where('item_fg_number', $item_fg_number_to_check);
                                }   else if ($this->db->field_exists('number', $target_table)) {
                                    $this->db->where('number', $item_fg_number_to_check);
                                }

                                $target_data = $this->db->get($target_table)->row_array();

                                // JIKA DATA SUDAH DIINPUT
                                if (!empty($target_data['created_date']) && $target_data['created_date'] !== '0000-00-00 00:00:00') {
                                    
                                    $item['status']     = 'COMPLETE'; 
                                    $item['status_val'] = 1;
                                    
                                    $created_ts = strtotime(date('Y-m-d', strtotime($target_data['created_date'])));
                                    
                                    if ($task_end_ts > 0 && $created_ts > $task_end_ts) {
                                        $item['status_time'] = 'Complete (Late)';
                                    } else {
                                        $item['status_time'] = 'Complete';
                                    }

                                } else {
                                    if ($task_end_ts > 0 && $today_ts > $task_end_ts) {
                                        $item['status_time'] = 'Overdue';
                                    } else {
                                        $item['status_time'] = 'On Progress';
                                    }
                                }
                            }
                        }

                        if ($item['status_val'] == 0) {
                            $uncomplete_count++;
                        }
                    } 
                    
                    $records = $task_details; 

                } else {
                    $records = []; 
                }

                $new_project_status = ($uncomplete_count == 0 && count($records) > 0) ? 1 : 0;
                if ((int)$header_row->project_status !== $new_project_status) {
                    $this->db->where('number', $number)->update('create_projects', ['status' => $new_project_status]);
                    $this->db->where('project_number', $number)->update('create_tasks', ['status' => $new_project_status]);
                }

                echo json_encode($records);
                
            } else {
                echo json_encode([]); 
            }
        } else {
            echo json_encode([]);
        }
    }

    public function getSummaryStats()
    {
        $this->db->select('t_det.end_date, t_det.link, p_det.item_fg_number');
        $this->db->from('create_tasks t');
        $this->db->join('create_projects p', 't.project_number = p.number');
        $this->db->join('create_project_details p_det', 'p.id = p_det.create_project_id', 'left');
        $this->db->join('create_task_details t_det', 't.id = t_det.task_id', 'inner'); 
        $this->db->where('p.status', 0);
        
        $all_tasks = $this->db->get()->result_array();

        $summary = ['total' => 0, 'complete' => 0, 'incomplete' => 0, 'overdue' => 0];
        $today_ts = strtotime(date('Y-m-d')); 

        foreach ($all_tasks as $row) {
            $summary['total']++;
            
            $target_table = basename($row['link']);
            $item_fg_number = $row['item_fg_number'];
            $is_task_done = false;

            $task_end_ts = 0;
            if (!empty($row['end_date'])) {
                $parsed_date = strtotime($row['end_date']);
                if ($parsed_date !== false && $parsed_date > 0) {
                    $task_end_ts = strtotime(date('Y-m-d', $parsed_date));
                }
            }

            if (!empty($target_table) && $this->db->table_exists($target_table)) {
                
                $this->db->select_max('created_date');
                
                if ($this->db->field_exists('item_fg_number', $target_table)) {
                    $this->db->where('item_fg_number', $item_fg_number);
                }else if ($this->db->field_exists('number', $target_table)) {
                    $this->db->where('number', $item_fg_number);
                } 
                
                $target_data = $this->db->get($target_table)->row_array();

                if (!empty($target_data['created_date']) && $target_data['created_date'] !== '0000-00-00 00:00:00') {
                    $is_task_done = true;
                    $summary['complete']++;

                    // KONDISI 1: SUDAH COMPLETE, TAPI TANGGAL INPUT > END DATE TASK
                    if ($task_end_ts > 0) {
                        $created_ts = strtotime(date('Y-m-d', strtotime($target_data['created_date'])));
                        if ($created_ts > $task_end_ts) {
                            $summary['overdue']++; // Tercatat sebagai Overdue
                        }
                    }
                }
            }

            // KONDISI 2: UNCOMPLETE, DAN HARI INI > END DATE TASK
            if (!$is_task_done) {
                $summary['incomplete']++;
                if ($task_end_ts > 0 && $today_ts > $task_end_ts) {
                    $summary['overdue']++; // Tercatat sebagai Overdue
                }
            }
        }
        
        echo json_encode($summary);
    }

    public function getChartProjectData()
    {
        $this->db->select('status, end_date');
        $this->db->from('create_projects');
        $this->db->where('deleted', 0);
        $projects = $this->db->get()->result_array();

        $completed = 0;
        $on_progress = 0;
        $overdue = 0;
        
        // Ambil tanggal hari ini (format Y-m-d)
        $today = strtotime(date('Y-m-d'));

        foreach ($projects as $p) {
            // Jika status sudah 1 (Complete)
            if ($p['status'] == 1) {
                $completed++;
            } else {
                // Jika status masih 0 (Open), cek tanggalnya
                if (!empty($p['end_date'])) {
                    
                    $clean_date = str_replace('/', '-', $p['end_date']);
                    $project_end_date = strtotime($clean_date);

                    if ($project_end_date < $today) {
                        $overdue++; // Tanggal sudah lewat
                    } else {
                        $on_progress++; // Masih ada waktu
                    }
                } else {
                    // Jika tidak ada end_date, kita masukkan ke on_progress
                    $on_progress++;
                }
            }
        }

        echo json_encode([
            'labels' => ['Completed', 'On Progress', 'Overdue'],
            'data' => [$completed, $on_progress, $overdue],
            'colors' => ['#28a745', '#ffc107', '#dc3545']
        ]);
    }
}
