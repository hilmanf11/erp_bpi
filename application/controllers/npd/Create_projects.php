<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Create_projects extends CI_Controller
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
        $this->form_validation->set_rules('id', 'ID', 'required|min_length[1]|max_length[20]|is_unique[create_projects.id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('npd/create_projects');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM create_projects WHERE name LIKE '%$post%' AND number LIKE '%$post%'");
        echo json_encode($send);
    }

    //GET DATA
    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $division_id = isset($_POST['division_id']) ? $_POST['division_id'] : "";
        $filter_division = "";
        if ($division_id != "") {
            $filter_division = " AND division_id = '$division_id' "; 
        }

        $send = $this->crud->query("SELECT * FROM item_fg WHERE status = '0' $filter_division AND (number like '%$post%' or number_customer like '%$post%' or name like '%$post%' or id like '%$post%')");
        
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
        $send = $this->crud->query("SELECT * FROM create_projects WHERE name LIKE '%$post%' AND `status` = '0'");
        // $send = $this->crud->reads('create_projects', ["name" => $post]);
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
            $this->db->select('a.*, 
            b.name as division, 
            c.name as customer_name, 
            e.name as project_category_name, 
            a.created_by as owner, 
            a.status as status_project');
            $this->db->from('create_projects a');
            $this->db->join('divisions b','b.id = a.division_id');
            $this->db->join('customers c','c.id = a.customer_id');
            $this->db->join('project_categorys e','e.id = a.project_category_id');
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

            $today = new DateTime();
            $today->setTime(0, 0, 0);
            foreach ($records as &$row) {
                
                // ============================================
                // 1. LOGIKA STATUS TIME (OVERDUE / ON TRACK)
                // ============================================
                if ($row['status_project'] == 1) {
                    // Jika status project 1 (Selesai), maka waktunya dianggap Completed
                    $row['status_time'] = 'Completed'; 
                } else {
                    // Jika status project masih 0 (Open)
                    if (!empty($row['end_date'])) {
                        $end_date_dt = new DateTime($row['end_date']);
                        $end_date_dt->setTime(0, 0, 0);
                        
                        // Bandingkan hari ini dengan end_date
                        if ($today > $end_date_dt) {
                            $row['status_time'] = 'Overdue';
                        } else {
                            $row['status_time'] = 'On Progress';
                        }
                    } else {
                        // Jika tidak ada data end_date
                        $row['status_time'] = '-';
                    }
                }

                // ============================================
                // 2. LOGIKA DURASI
                // ============================================
                if (!empty($row['start_date']) && !empty($row['end_date'])) {
                    $start = new DateTime($row['start_date']);
                    $end = new DateTime($row['end_date']);
                    
                    $start->setTime(12, 0, 0);
                    $end->setTime(12, 0, 0);
                    $end->modify('+1 day');

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
            // Return ke EasyUI
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        }
    }

    // public function datatableDetails()
    // {
    //     $number = base64_decode($this->input->get('number'));

    //     if ($number) {
    //         $this->db->select('details');
    //         $this->db->from('create_projects');
    //         $this->db->where('number', $number);
    //         $row = $this->db->get()->row();

    //         if ($row && !empty($row->details)) {
    //             $records = json_decode($row->details, true); 
    //             echo json_encode($records);
    //         } else {
    //             echo json_encode([]);
    //         }
    //     } else {
    //         echo json_encode([]);
    //     }
    // }

    public function datatableDetails()
    {
        $number = base64_decode($this->input->get('number'));

        if ($number) {
            // 1. JOIN tabel details ke header untuk mendapatkan data berdasarkan nomor project
            $this->db->select('d.*');
            $this->db->from('create_project_details d');
            $this->db->join('create_projects p', 'p.id = d.create_project_id');
            $this->db->where('p.number', $number);
            $records = $this->db->get()->result_array();

            $result = [];
            // 2. Mapping ulang nama kolom DB agar sesuai dengan field datagrid frontend
            foreach ($records as $r) {
                $result[] = [
                    // 'item_fg_id'     => $r['item_fg_id'],
                    'item_fg_number' => $r['item_fg_number'],
                    'item_fg_name'   => $r['item_fg_name'],
                    'qty'                       => $r['qty'],
                    'volume'                    => $r['volume'],
                    'volume_unit'               => $r['volume_unit'],
                    'remark'                    => $r['remark']
                ];
            }

            echo json_encode($result);
        } else {
            echo json_encode([]);
        }
    }

    //AUTO ID
    public function autoid()
    {
        $year = date('Y');
        $month = date('m');
        
        $prefix_search = "PRO" . $year; 
        
        $sql = $this->db->query("SELECT MAX(number) as kode FROM create_projects WHERE number LIKE '$prefix_search%'");
        $row = $sql->row();

        if ($row && $row->kode) {
            $urutan = (int) substr($row->kode, -3);
            $urutan++;
        } else {
            $urutan = 1;
        }

        $autoid = "PRO" . $year . $month . sprintf("%03s", $urutan);
        
        echo $autoid;
    }
    // //CREATE DATA
    // public function create() {
    //     $details_json = $this->input->post('details');

    //     $data_project = array(
    //         'number'              => $this->input->post('number'),
    //         'name'                => $this->input->post('name'),
    //         'division_id'         => $this->input->post('division_id'),
    //         'customer_id'         => $this->input->post('customer_id'),
    //         'model'               => $this->input->post('model'),
    //         'start_date'          => $this->input->post('start_date'),
    //         'end_date'            => $this->input->post('end_date'),
    //         'level'               => $this->input->post('level'),
    //         'project_category_id' => $this->input->post('project_category_id'),
    //         'description'         => $this->input->post('description'),
    //         'details'             => $details_json
    //     );

    //     $insert = $this->crud->create('create_projects', $data_project);

    //     if ($insert) {
    //         echo json_encode(['theme' => 'success', 'message' => 'Project and all details successfully saved!']);
    //     } else {
    //         echo json_encode(['theme' => 'error', 'message' => 'Failed to save project data.']);
    //     }
    // }
    // //UPDATE DATA
    // public function update() {
    //     $id = $this->input->get('id');

    //     if ($id) {
    //         $details_json = $this->input->post('details');

    //         $data_update = array(
    //             'name'                => $this->input->post('name'),
    //             'division_id'         => $this->input->post('division_id'),
    //             'customer_id'         => $this->input->post('customer_id'),
    //             'model'               => $this->input->post('model'),
    //             'start_date'          => $this->input->post('start_date'),
    //             'end_date'            => $this->input->post('end_date'),
    //             'level'               => $this->input->post('level'),
    //             'project_category_id' => $this->input->post('project_category_id'),
    //             'description'         => $this->input->post('description'),
    //             'details'             => $details_json
    //         );

    //         $where = array('id' => $id);
    //         $update = $this->crud->update('create_projects', $where, $data_update);

    //         if ($update) {
    //             echo json_encode(['theme' => 'success', 'message' => 'Project successfully updated!']);
    //         } else {
    //             echo json_encode(['theme' => 'error', 'message' => 'Failed to update project data.']);
    //         }
    //     } else {
    //         echo json_encode(['theme' => 'error', 'message' => 'Project ID not found.']);
    //     }
    // }
    // //DELETE DATA
    // public function delete()
    // {
    //     $id = $this->input->post('id');
    //     if ($id) {
    //         $where = array('id' => $id);
    //         $delete = $this->crud->delete('create_projects', $where);
    //         if ($delete) {
    //             echo json_encode(['theme' => 'success', 'message' => 'Project successfully deleted!']);
    //         } else {
    //             echo json_encode(['theme' => 'error', 'message' => 'Failed to delete project.']);
    //         }
    //     } else {
    //         echo json_encode(['theme' => 'error', 'message' => 'Project ID not found.']);
    //     }
    // }

    //CREATE DATA
    public function create() {
        $details_json = $this->input->post('details');
        $number = $this->input->post('number');

        $data_project = array(
            'number'              => $number,
            'name'                => $this->input->post('name'),
            'division_id'         => $this->input->post('division_id'),
            'customer_id'         => $this->input->post('customer_id'),
            'model'               => $this->input->post('model'),
            'start_date'          => $this->input->post('start_date'),
            'end_date'            => $this->input->post('end_date'),
            'level'               => $this->input->post('level'),
            'project_category_id' => $this->input->post('project_category_id'),
            'description'         => $this->input->post('description')
        );
        $insert = $this->crud->create('create_projects', $data_project);
        if ($insert) {
            $project = $this->db->select('id')->where('number', $number)->get('create_projects')->row();
            
            if ($project) {
                $details = json_decode($details_json, true);
                if (!empty($details)) {
                    $batch_data = [];
                    foreach ($details as $row) {
                        $batch_data[] = array(
                            'create_project_id' => $project->id,
                            // 'item_fg_id'        => $row['item_fg_id'],
                            'item_fg_number'    => $row['item_fg_number'],
                            'item_fg_name'      => $row['item_fg_name'],
                            'qty'               => $row['qty'],
                            'volume'            => $row['volume'],
                            'volume_unit'       => $row['volume_unit'],
                            'remark'            => $row['remark']
                        );
                    }
                    $this->db->insert_batch('create_project_details', $batch_data);
                }
            }
            echo json_encode(['theme' => 'success', 'message' => 'Project and all details successfully saved!']);
        } else {
            echo json_encode(['theme' => 'error', 'message' => 'Failed to save project data.']);
        }
    }

    //UPDATE DATA
    public function update() {
        $id = $this->input->get('id');

        if ($id) {
            $details_json = $this->input->post('details');

            $data_update = array(
                'name'                => $this->input->post('name'),
                'division_id'         => $this->input->post('division_id'),
                'customer_id'         => $this->input->post('customer_id'),
                'model'               => $this->input->post('model'),
                'start_date'          => $this->input->post('start_date'),
                'end_date'            => $this->input->post('end_date'),
                'level'               => $this->input->post('level'),
                'project_category_id' => $this->input->post('project_category_id'),
                'description'         => $this->input->post('description')
            );

            $where = array('id' => $id);
            $update = $this->crud->update('create_projects', $where, $data_update);

            if ($update) {
                $this->db->where('create_project_id', $id)->delete('create_project_details');
                
                $details = json_decode($details_json, true);
                if (!empty($details)) {
                    $batch_data = [];
                    foreach ($details as $row) {
                        $batch_data[] = array(
                            'create_project_id' => $id,
                            // 'item_fg_id'        => $row['item_fg_id'],
                            'item_fg_number'    => $row['item_fg_number'],
                            'item_fg_name'      => $row['item_fg_name'],
                            'qty'               => $row['qty'],
                            'volume'            => $row['volume'],
                            'volume_unit'       => $row['volume_unit'],
                            'remark'            => $row['remark']
                        );
                    }
                    $this->db->insert_batch('create_project_details', $batch_data);
                }

                echo json_encode(['theme' => 'success', 'message' => 'Project successfully updated!']);
            } else {
                echo json_encode(['theme' => 'error', 'message' => 'Failed to update project data.']);
            }
        } else {
            echo json_encode(['theme' => 'error', 'message' => 'Project ID not found.']);
        }
    }

    //DELETE DATA
    public function delete()
    {
        $id = $this->input->post('id');
        
        if ($id) {
            $this->db->where('create_project_id', $id)->delete('create_project_details');
            
            $where = array('id' => $id);
            $delete = $this->crud->delete('create_projects', $where);
            
            if ($delete) {
                echo json_encode(['theme' => 'success', 'message' => 'Project and its details successfully deleted!']);
            } else {
                echo json_encode(['theme' => 'error', 'message' => 'Failed to delete project.']);
            }
        } else {
            echo json_encode(['theme' => 'error', 'message' => 'Project ID not found.']);
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=create_projects_$format.xls");
        }

        // Config Data
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // Select Query Project
        $this->db->select('a.*, b.name as division, c.name as customer_name, e.name as project_category_name, a.created_by as owner');
        $this->db->from('create_projects a');
        $this->db->join('divisions b', 'b.id = a.division_id', 'left');
        $this->db->join('customers c', 'c.id = a.customer_id', 'left');
        $this->db->join('project_categorys e', 'e.id = a.project_category_id', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();

        // --- PROSES PERHITUNGAN DURASI ---
        foreach ($records as &$row) {
            if (!empty($row['start_date']) && !empty($row['end_date'])) {
                            
                $start = new DateTime($row['start_date']);
                $end = new DateTime($row['end_date']);
                
                $start->setTime(12, 0, 0);
                $end->setTime(12, 0, 0);
                
                $end->modify('+1 day');

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
                <h3>CREATE PROJECT</h3>
            </div>
        </center>
        
        <table id="customers" border="1">
            <tr>
                <th width="30" style="text-align: center;">No</th>
                <th>Project No</th>
                <th>Project Name</th>
                <th>Division</th>
                <th>Customer</th>
                <th>Model</th>
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
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['name'] . '</td>
                    <td>' . $data['division'] . '</td>
                    <td>' . $data['customer_name'] . '</td>
                    <td>' . $data['model'] . '</td>
                    <td>' . $data['start_date'] . '</td>
                    <td>' . $data['end_date'] . '</td>
                    <td>' . $data['duration'] . '</td>
                    <td style="text-align: center;">' . $data['level'] . '</td>
                    <td>' . $data['project_category_name'] . '</td>
                </tr>';
            $no++;
        }
        
        $html .= '</table></body></html>';
        
        echo $html;
    }
}
