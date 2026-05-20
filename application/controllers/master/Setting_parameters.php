<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Setting_parameters extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('Ciqrcode');
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
            $this->load->view('master/setting_parameters');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads($customer_id)
    {
        $customer_id = base64_decode($customer_id);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, b.number_customer, a.price FROM customer_items a 
            JOIN item_fg b ON a.item_fg_id = b.id 
            WHERE a.customer_id = '$customer_id' and (b.number LIKE '%$post%' or b.name LIKE '%$post%')");
        echo json_encode($send);
    }

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.*,c.id as item_rm_id, c.name as item_rm_name, c.number as item_rm_number
        FROM item_fg a 
        JOIN bom b ON a.id = b.item_fg_id
        JOIN item_rm c ON b.item_rm_id = c.id
        WHERE a.status = '0' AND c.item_family_id ='P06' AND (a.number like '%$post%' or a.name like '%$post%' or a.id like '%$post%') 
        ORDER BY number ASC"
        );
        echo json_encode($send);
    }

    public function get_runner_weight() {
        $item_id = $this->input->post('item_fg_id');
        $machine_id = $this->input->post('machine_id');

        // Sesuaikan query dengan struktur tabel Anda
        $this->db->select('runner');
        $this->db->from('menu_loadings');
        $this->db->where('item_fg_id', $item_id);
        $this->db->where('machine_id', $machine_id);
        $this->db->where('priority', 1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            echo json_encode($query->row());
        } else {
            echo json_encode(['runner' => 0]);
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
            $filter_machine_id = @base64_decode($get['filter_machine_id']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, b.number as machine_number');
            $this->db->from('setting_parameters a');
            $this->db->join('machines b','a.machine_id = b.id','left');
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->like('a.machine_id', $filter_machine_id);
            
            $this->db->order_by('a.item_fg_number', 'ASC');
            // $this->db->order_by('d.plant', 'ASC');
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
            $send   = $this->crud->create('setting_parameters', $post);
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
            $send = $this->crud->update('setting_parameters', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('setting_parameters', $data);
        echo $send;
    }

    //UPLOAD DATA
    public function upload()
    {
        error_reporting(0);
        require_once 'assets/vendors/excel_reader2.php';
        $target = basename($_FILES['file_upload']['name']);
        move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
        chmod($_FILES['file_upload']['name'], 0777);
        $file = $_FILES['file_upload']['name'];
        $data = new Spreadsheet_Excel_Reader($file, false);
        $total_row = $data->rowcount($sheet_index = 0);
        for ($i = 5; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'item_fg_number' => $data->val($i, 2),
                'machine_number' => $data->val($i, 3),
                'nozzle' => $data->val($i, 4),
                'front' => $data->val($i, 5),
                'middle_3' => $data->val($i, 6),
                'middle_2' => $data->val($i, 7),
                'middle_1' => $data->val($i, 8),
                'rear' => $data->val($i, 9),
                's1_injection' => $data->val($i, 10),
                's2_injection' => $data->val($i, 11),
                's3_injection' => $data->val($i, 12),
                's4_injection' => $data->val($i, 13),
                's5_injection' => $data->val($i, 14),
                'v1_injection' => $data->val($i, 15),
                'v2_injection' => $data->val($i, 16),
                'v3_injection' => $data->val($i, 17),
                'v4_injection' => $data->val($i, 18),
                'v5_injection' => $data->val($i, 19),
                'p1_injection' => $data->val($i, 20),
                'p2_injection' => $data->val($i, 21),
                'p3_injection' => $data->val($i, 22),
                'p4_injection' => $data->val($i, 23),
                'p5_injection' => $data->val($i, 24),
                'p1_holding' => $data->val($i, 25),
                'p2_holding' => $data->val($i, 26),
                'p3_holding' => $data->val($i, 27),
                'v1_holding' => $data->val($i, 28),
                'v2_holding' => $data->val($i, 29),
                'v3_holding' => $data->val($i, 30),
                't1_holding' => $data->val($i, 31),
                't2_holding' => $data->val($i, 32),
                't3_holding' => $data->val($i, 33),
                '1_charging_speed' => $data->val($i, 34),
                '2_charging_speed' => $data->val($i, 35),
                '3_charging_speed' => $data->val($i, 36),
                '4_charging_speed' => $data->val($i, 37),
                '1_charging_pressure' => $data->val($i, 38),
                '2_charging_pressure' => $data->val($i, 39),
                '3_charging_pressure' => $data->val($i, 40),
                '4_charging_pressure' => $data->val($i, 41),
                '1_charging_back_pressure' => $data->val($i, 42),
                '2_charging_back_pressure' => $data->val($i, 43),
                '3_charging_back_pressure' => $data->val($i, 44),
                '4_charging_back_pressure' => $data->val($i, 45),
                '1_charging_position' => $data->val($i, 46),
                '2_charging_position' => $data->val($i, 47),
                '3_charging_position' => $data->val($i, 48),
                '4_charging_position' => $data->val($i, 49),
                'end_charging_position' => $data->val($i, 50),
                'suck_back_1_pressure' => $data->val($i, 51),
                'suck_back_2_pressure' => $data->val($i, 52),
                'suck_back_1' => $data->val($i, 53),
                'suck_back_2' => $data->val($i, 54),
                'injection_time' => $data->val($i, 55),
                'delay_sb1' => $data->val($i, 56),
                'delay_sb2' => $data->val($i, 57),
                'delay_charge' => $data->val($i, 58),
                'inj_monitoring_time' => $data->val($i, 59),
                'charge_monitoring_time' => $data->val($i, 60),
                'cooling_time' => $data->val($i, 61),
                'min_cushion_check' => $data->val($i, 62),
                'min_cushion_low_limit' => $data->val($i, 63),
                'min_cushion_upper_limit' => $data->val($i, 64),
                'charge_after_cooling' => $data->val($i, 65),
                'use_manual_back_pressure' => $data->val($i, 66),
                'actual_cushion' => $data->val($i, 67),
                'switch_over_position' => $data->val($i, 68),
                'switch_over_time' => $data->val($i, 69),
                'v1_mold_closing' => $data->val($i, 70),
                'v2_mold_closing' => $data->val($i, 71),
                'v3_mold_closing' => $data->val($i, 72),
                'v4_mold_closing' => $data->val($i, 73),
                'v5_mold_closing' => $data->val($i, 74),
                'p1_mold_closing' => $data->val($i, 75),
                'p2_mold_closing' => $data->val($i, 76),
                'p3_mold_closing' => $data->val($i, 77),
                'p4_mold_closing' => $data->val($i, 78),
                'p5_mold_closing' => $data->val($i, 79),
                's1_mold_closing' => $data->val($i, 80),
                's2_mold_closing' => $data->val($i, 81),
                's3_mold_closing' => $data->val($i, 82),
                's4_mold_closing' => $data->val($i, 83),
                's5_mold_closing' => $data->val($i, 84),
                'v1_mold_opening' => $data->val($i, 85),
                'v2_mold_opening' => $data->val($i, 86),
                'v3_mold_opening' => $data->val($i, 87),
                'v4_mold_opening' => $data->val($i, 88),
                'v5_mold_opening' => $data->val($i, 89),
                'p1_mold_opening' => $data->val($i, 90),
                'p2_mold_opening' => $data->val($i, 91),
                'p3_mold_opening' => $data->val($i, 92),
                'p4_mold_opening' => $data->val($i, 93),
                'p5_mold_opening' => $data->val($i, 94),
                's1_mold_opening' => $data->val($i, 95),
                's2_mold_opening' => $data->val($i, 96),
                's3_mold_opening' => $data->val($i, 97),
                's4_mold_opening' => $data->val($i, 98),
                's5_mold_opening' => $data->val($i, 99),
                'delay_mold_closing' => $data->val($i, 100),
                'mq_delay_time' => $data->val($i, 101),
                'mold_closing_time' => $data->val($i, 102),
                'mold_opening_time' => $data->val($i, 103),
                'process_time' => $data->val($i, 104),
                'mold_protection_time_1' => $data->val($i, 105),
                'mold_protection_time_2' => $data->val($i, 106),
                'v1_ej_forward' => $data->val($i, 107),
                'v2_ej_forward' => $data->val($i, 108),
                'v3_ej_forward' => $data->val($i, 109),
                'v4_ej_forward' => $data->val($i, 110),
                'p1_ej_forward' => $data->val($i, 111),
                'p2_ej_forward' => $data->val($i, 112),
                'p3_ej_forward' => $data->val($i, 113),
                'p4_ej_forward' => $data->val($i, 114),
                's1_ej_forward' => $data->val($i, 115),
                's2_ej_forward' => $data->val($i, 116),
                's3_ej_forward' => $data->val($i, 117),
                's4_ej_forward' => $data->val($i, 118),
                'v1_ej_backward' => $data->val($i, 119),
                'v2_ej_backward' => $data->val($i, 120),
                'v3_ej_backward' => $data->val($i, 121),
                'v4_ej_backward' => $data->val($i, 122),
                'p1_ej_backward' => $data->val($i, 123),
                'p2_ej_backward' => $data->val($i, 124),
                'p3_ej_backward' => $data->val($i, 125),
                'p4_ej_backward' => $data->val($i, 126),
                's1_ej_backward' => $data->val($i, 127),
                's2_ej_backward' => $data->val($i, 128),
                's3_ej_backward' => $data->val($i, 129),
                's4_ej_backward' => $data->val($i, 130),
                'ejecting_time' => $data->val($i, 131),
                'delay_forward' => $data->val($i, 132),
                'delay_backward' => $data->val($i, 133),
                'forward_time' => $data->val($i, 134),
                'backward_time' => $data->val($i, 135),
                'every_time_delays' => $data->val($i, 136),
                'ejector_forward_maintain' => $data->val($i, 137),
                'semi_automatic_ej_switch' => $data->val($i, 138),
                'ejector_bw_com_signal' => $data->val($i, 139),
                'semi_automatic_safety_door_start' => $data->val($i, 140),
                'core_temp' => $data->val($i, 141),
                'core_use' => $data->val($i, 142),
                'slider_temp' => $data->val($i, 143),
                'slider_use' => $data->val($i, 144),
                'cavity_temp' => $data->val($i, 145),
                'cavity_use' => $data->val($i, 146),
                'hr_zone_1' => $data->val($i, 147),
                'hr_zone_2' => $data->val($i, 148),
                'hr_zone_3' => $data->val($i, 149),
                'hr_zone_4' => $data->val($i, 150),
                'hr_zone_5' => $data->val($i, 151),
                'hr_zone_6' => $data->val($i, 152),
                'hr_zone_7' => $data->val($i, 153),
                'hr_zone_8' => $data->val($i, 154),
                'hr_zone_9' => $data->val($i, 155),
                'hr_zone_10' => $data->val($i, 156),
                'hr_zone_11' => $data->val($i, 157),
                'hr_zone_12' => $data->val($i, 158),
                'hr_zone_13' => $data->val($i, 159),
                'hr_zone_14' => $data->val($i, 160),
                'dryer_temperature' => $data->val($i, 161),
                'dryer_time' => $data->val($i, 162),
                'cycle_time_actual' => $data->val($i, 163),
                'cavity_actual' => $data->val($i, 164)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/setting_parameters.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/setting_parameters.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/setting_parameters.txt";
        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    //UPLOAD CREATE DATA
    public function uploadcreate()
    {
        if ($this->input->post()) {
            $data = $this->input->post('data');
            
            // 1. Ambil Data Dasar
            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_number']]);
            $machine = $this->crud->read('machines', [], ["number" => $data['machine_number']]);

            // 2. Validasi Item & Machine
            if (empty($item_fg)) {
                echo json_encode(array("title" => "Not Found", "message" => "Item Number " . $data['item_fg_number'] . " is Not Found", "theme" => "error"));
                return;
            }
            if (empty($machine)) {
                echo json_encode(array("title" => "Not Found", "message" => "Machine " . $data['machine_number'] . " is Not Found", "theme" => "error"));
                return;
            }

            // 3. Validasi Kombinasi Menu Loading
            $menu_loading = $this->crud->read('menu_loadings', [], [
                "item_fg_id" => $item_fg->id, 
                "machine_id" => $machine->id,
                "priority"   => 1
            ]);

            if (empty($menu_loading)) {
                echo json_encode(array(
                    "title" => "Not Found", 
                    "message" => "Combination of Item ".$data['item_fg_number']." and Machine ".$data['machine_number']." is not registered in Menu Loading", 
                    "theme" => "error"
                ));
                return;
            }

            // 4. Ambil Data BOM
            $bom_query = $this->crud->query("SELECT a.*, c.id as item_rm_id, c.name as item_rm_name, c.number as item_rm_number
                FROM item_fg a 
                JOIN bom b ON a.id = b.item_fg_id
                JOIN item_rm c ON b.item_rm_id = c.id
                WHERE a.id = '$item_fg->id' AND c.item_family_id ='P06' 
                LIMIT 1"
            );
            $bom = (!empty($bom_query)) ? $bom_query[0] : null;

            // 5. Siapkan Data Final
            $dataFinal = array(
                "item_fg_id"              => $item_fg->id,
                "item_fg_number"          => $data['item_fg_number'],
                "item_fg_name"            => $item_fg->name,
                "item_rm_id"              => ($bom) ? $bom->item_rm_id : null,
                "item_rm_number"          => ($bom) ? $bom->item_rm_number : null,
                "item_rm_name"            => ($bom) ? $bom->item_rm_name : null,
                "machine_id"              => $machine->id,
                "toonage"                 => $machine->toonage,
                "maker"                   => $machine->maker,
                "weight"                  => $item_fg->weight,
                "runner"                  => $menu_loading->runner,
                'nozzle'                  => $data['nozzle'],
                'front'                   => $data['front'],
                'middle_3'                => $data['middle_3'],
                'middle_2'                => $data['middle_2'],
                'middle_1'                => $data['middle_1'],
                'rear'                    => $data['rear'],
                's1_injection'            => $data['s1_injection'],
                's2_injection'            => $data['s2_injection'],
                's3_injection'            => $data['s3_injection'],
                's4_injection'            => $data['s4_injection'],
                's5_injection'            => $data['s5_injection'],
                'v1_injection'            => $data['v1_injection'],
                'v2_injection'            => $data['v2_injection'],
                'v3_injection'            => $data['v3_injection'],
                'v4_injection'            => $data['v4_injection'],
                'v5_injection'            => $data['v5_injection'],
                'p1_injection'            => $data['p1_injection'],
                'p2_injection'            => $data['p2_injection'],
                'p3_injection'            => $data['p3_injection'],
                'p4_injection'            => $data['p4_injection'],
                'p5_injection'            => $data['p5_injection'],
                'p1_holding'              => $data['p1_holding'],
                'p2_holding'              => $data['p2_holding'],
                'p3_holding'              => $data['p3_holding'],
                'v1_holding'              => $data['v1_holding'],
                'v2_holding'              => $data['v2_holding'],
                'v3_holding'              => $data['v3_holding'],
                't1_holding'              => $data['t1_holding'],
                't2_holding'              => $data['t2_holding'],
                't3_holding'              => $data['t3_holding'],
                '1_charging_speed'         => $data['1_charging_speed'],
                '2_charging_speed'         => $data['2_charging_speed'],
                '3_charging_speed'         => $data['3_charging_speed'],
                '4_charging_speed'         => $data['4_charging_speed'],
                '1_charging_pressure'      => $data['1_charging_pressure'],
                '2_charging_pressure'      => $data['2_charging_pressure'],
                '3_charging_pressure'      => $data['3_charging_pressure'],
                '4_charging_pressure'      => $data['4_charging_pressure'],
                '1_charging_back_pressure' => $data['1_charging_back_pressure'],
                '2_charging_back_pressure' => $data['2_charging_back_pressure'],
                '3_charging_back_pressure' => $data['3_charging_back_pressure'],
                '4_charging_back_pressure' => $data['4_charging_back_pressure'],
                '1_charging_position'      => $data['1_charging_position'],
                '2_charging_position'      => $data['2_charging_position'],
                '3_charging_position'      => $data['3_charging_position'],
                '4_charging_position'      => $data['4_charging_position'],
                'end_charging_position'    => $data['end_charging_position'],
                'suck_back_1_pressure'     => $data['suck_back_1_pressure'],
                'suck_back_2_pressure'     => $data['suck_back_2_pressure'],
                'suck_back_1'              => $data['suck_back_1'],
                'suck_back_2'              => $data['suck_back_2'],
                'injection_time'           => $data['injection_time'],
                'delay_sb1'                => $data['delay_sb1'],
                'delay_sb2'                => $data['delay_sb2'],
                'delay_charge'             => $data['delay_charge'],
                'inj_monitoring_time'      => $data['inj_monitoring_time'],
                'charge_monitoring_time'   => $data['charge_monitoring_time'],
                'cooling_time'             => $data['cooling_time'],
                'min_cushion_check'        => $data['min_cushion_check'],
                'min_cushion_low_limit'    => $data['min_cushion_low_limit'],
                'min_cushion_upper_limit'  => $data['min_cushion_upper_limit'],
                'charge_after_cooling'     => $data['charge_after_cooling'],
                'use_manual_back_pressure' => $data['use_manual_back_pressure'],
                'actual_cushion'           => $data['actual_cushion'],
                'switch_over_position'     => $data['switch_over_position'],
                'switch_over_time'         => $data['switch_over_time'],
                'v1_mold_closing'          => $data['v1_mold_closing'],
                'v2_mold_closing'          => $data['v2_mold_closing'],
                'v3_mold_closing'          => $data['v3_mold_closing'],
                'v4_mold_closing'          => $data['v4_mold_closing'],
                'v5_mold_closing'          => $data['v5_mold_closing'],
                'p1_mold_closing'          => $data['p1_mold_closing'],
                'p2_mold_closing'          => $data['p2_mold_closing'],
                'p3_mold_closing'          => $data['p3_mold_closing'],
                'p4_mold_closing'          => $data['p4_mold_closing'],
                'p5_mold_closing'          => $data['p5_mold_closing'],
                's1_mold_closing'          => $data['s1_mold_closing'],
                's2_mold_closing'          => $data['s2_mold_closing'],
                's3_mold_closing'          => $data['s3_mold_closing'],
                's4_mold_closing'          => $data['s4_mold_closing'],
                's5_mold_closing'          => $data['s5_mold_closing'],
                'v1_mold_opening'          => $data['v1_mold_opening'],
                'v2_mold_opening'          => $data['v2_mold_opening'],
                'v3_mold_opening'          => $data['v3_mold_opening'],
                'v4_mold_opening'          => $data['v4_mold_opening'],
                'v5_mold_opening'          => $data['v5_mold_opening'],
                'p1_mold_opening'          => $data['p1_mold_opening'],
                'p2_mold_opening'          => $data['p2_mold_opening'],
                'p3_mold_opening'          => $data['p3_mold_opening'],
                'p4_mold_opening'          => $data['p4_mold_opening'],
                'p5_mold_opening'          => $data['p5_mold_opening'],
                's1_mold_opening'          => $data['s1_mold_opening'],
                's2_mold_opening'          => $data['s2_mold_opening'],
                's3_mold_opening'          => $data['s3_mold_opening'],
                's4_mold_opening'          => $data['s4_mold_opening'],
                's5_mold_opening'          => $data['s5_mold_opening'],
                'delay_mold_closing'       => $data['delay_mold_closing'],
                'mq_delay_time'            => $data['mq_delay_time'],
                'mold_closing_time'        => $data['mold_closing_time'],
                'mold_opening_time'        => $data['mold_opening_time'],
                'process_time'             => $data['process_time'],
                'mold_protection_time_1'   => $data['mold_protection_time_1'],
                'mold_protection_time_2'   => $data['mold_protection_time_2'],
                'v1_ej_forward'            => $data['v1_ej_forward'],
                'v2_ej_forward'            => $data['v2_ej_forward'],
                'v3_ej_forward'            => $data['v3_ej_forward'],
                'v4_ej_forward'            => $data['v4_ej_forward'],
                'p1_ej_forward'            => $data['p1_ej_forward'],
                'p2_ej_forward'            => $data['p2_ej_forward'],
                'p3_ej_forward'            => $data['p3_ej_forward'],
                'p4_ej_forward'            => $data['p4_ej_forward'],
                's1_ej_forward'            => $data['s1_ej_forward'],
                's2_ej_forward'            => $data['s2_ej_forward'],
                's3_ej_forward'            => $data['s3_ej_forward'],
                's4_ej_forward'            => $data['s4_ej_forward'],
                'v1_ej_backward'           => $data['v1_ej_backward'],
                'v2_ej_backward'           => $data['v2_ej_backward'],
                'v3_ej_backward'           => $data['v3_ej_backward'],
                'v4_ej_backward'           => $data['v4_ej_backward'],
                'p1_ej_backward'           => $data['p1_ej_backward'],
                'p2_ej_backward'           => $data['p2_ej_backward'],
                'p3_ej_backward'           => $data['p3_ej_backward'],
                'p4_ej_backward'           => $data['p4_ej_backward'],
                's1_ej_backward'           => $data['s1_ej_backward'],
                's2_ej_backward'           => $data['s2_ej_backward'],
                's3_ej_backward'           => $data['s3_ej_backward'],
                's4_ej_backward'           => $data['s4_ej_backward'],
                'ejecting_time'            => $data['ejecting_time'],
                'delay_forward'            => $data['delay_forward'],
                'delay_backward'           => $data['delay_backward'],
                'forward_time'             => $data['forward_time'],
                'backward_time'            => $data['backward_time'],
                'every_time_delays'        => $data['every_time_delays'],
                'ejector_forward_maintain' => $data['ejector_forward_maintain'],
                'semi_automatic_ej_switch' => $data['semi_automatic_ej_switch'],
                'ejector_bw_com_signal'    => $data['ejector_bw_com_signal'],
                'semi_automatic_safety_door_start' => $data['semi_automatic_safety_door_start'],
                'core_temp'                => $data['core_temp'],
                'core_use'                 => $data['core_use'],
                'slider_temp'              => $data['slider_temp'],
                'slider_use'               => $data['slider_use'],
                'cavity_temp'              => $data['cavity_temp'],
                'cavity_use'               => $data['cavity_use'],
                'hr_zone_1'                => $data['hr_zone_1'],
                'hr_zone_2'                => $data['hr_zone_2'],
                'hr_zone_3'                => $data['hr_zone_3'],
                'hr_zone_4'                => $data['hr_zone_4'],
                'hr_zone_5'                => $data['hr_zone_5'],
                'hr_zone_6'                => $data['hr_zone_6'],
                'hr_zone_7'                => $data['hr_zone_7'],
                'hr_zone_8'                => $data['hr_zone_8'],
                'hr_zone_9'                => $data['hr_zone_9'],
                'hr_zone_10'               => $data['hr_zone_10'],
                'hr_zone_11'               => $data['hr_zone_11'],
                'hr_zone_12'               => $data['hr_zone_12'],
                'hr_zone_13'               => $data['hr_zone_13'],
                'hr_zone_14'               => $data['hr_zone_14'],
                'dryer_temperature'        => $data['dryer_temperature'],
                'dryer_time'               => $data['dryer_time'],
                'cycle_time_actual'        => $data['cycle_time_actual'],
                'cavity_actual'            => $data['cavity_actual'],
            );

            // 6. Cek apakah sudah ada di setting_parameters (Update atau Create)
            $existing = $this->crud->read('setting_parameters', [], [
                "item_fg_id" => $item_fg->id, 
                "machine_id" => $machine->id
            ]);

            if ($existing) {
                // Jika ada, UPDATE
                $send = $this->crud->update('setting_parameters',["id" => $existing->id],$dataFinal);
            } else {
                // Jika tidak ada, CREATE
                $send = $this->crud->create('setting_parameters', $dataFinal);
            }

            echo $send;
        }
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=setting_parameters_$format.xls");
        }

        $get = $this->input->get();
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_machine_id = @base64_decode($get['filter_machine_id']);

        // Config & Data Fetching
        $config = $this->db->get('config')->row();
        
        $this->db->select('a.*, b.number as machine_number');
        $this->db->from('setting_parameters a');
        $this->db->join('machines b', 'a.machine_id = b.id');
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->like('a.machine_id', $filter_machine_id);
        $this->db->order_by('a.item_fg_number', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><style>
            body {font-family: Arial; font-size: 10px;}
            table {border-collapse: collapse; width: 100%;}
            th, td {border: 1px solid #000; padding: 3px; text-align: center;}
            .header-main {font-weight: bold;}
        </style></head><body>';

        // Header Info
        $html .= '<div style="margin-bottom:20px;">
                    <div style="float:left;"><img src="'.$config->favicon.'" width="30"> <b>'.$config->name.'</b></div>
                    <div style="float:right; text-align:right;">Print Date: '.date("d M Y H:i").'<br>Print By: '.$this->session->username.'</div>
                    <div style="clear:both; text-align:center;"><h3>SETTING PARAMETERS</h3></div>
                </div>';

        $html .= '<table><thead>';

        // BARIS 1
        $html .= '<tr class="header-main">
                    <th rowspan="3">No</th>
                    <th rowspan="3">Product Id</th>
                    <th rowspan="3">Product No</th>
                    <th rowspan="3">Product Name</th>
                    <th rowspan="3">Machine No</th>
                    <th rowspan="3">Tonage</th>
                    <th rowspan="3">Maker</th>
                    <th colspan="6">BARREL TEMPERATURE</th>
                    <th colspan="15">INJECTION</th>
                    <th colspan="9">HOLDING</th>
                    <th colspan="17">CHARGING</th>
                    <th colspan="4">SUCK BACK</th>
                    <th rowspan="3">Injection Time</th>
                    <th rowspan="3">Delay for SB 1</th>
                    <th rowspan="3">Delay for SB 2</th>
                    <th rowspan="3">Delay of Charge</th>
                    <th rowspan="3">Inj Monitoring Time</th>
                    <th rowspan="3">Charge Monitoring Time</th>
                    <th rowspan="3">Cooling Time</th>
                    <th rowspan="3">Min Cushion Check</th>
                    <th rowspan="3">Min Cushion Low</th>
                    <th rowspan="3">Min Cushion Upper</th>
                    <th rowspan="3">Charge After Cooling</th>
                    <th rowspan="3">Manual Back Pressure</th>
                    <th rowspan="3">Actual Cushion</th>
                    <th rowspan="3">Switch Over Pos</th>
                    <th rowspan="3">Switch Over Time</th>
                    <th colspan="15">MOLD CLOSING</th>
                    <th colspan="15">MOLD OPENING</th>
                    <th rowspan="3">Delay Mold Closing</th>
                    <th rowspan="3">MQ Delay Time</th>
                    <th rowspan="3">Mold Closing Time</th>
                    <th rowspan="3">Mold Opening Time</th>
                    <th rowspan="3">Process Time</th>
                    <th rowspan="3">Mold Protect 1</th>
                    <th rowspan="3">Mold Protect 2</th>
                    <th colspan="12">EJECTION FORWARD</th>
                    <th colspan="12">EJECTION BACKWARD</th>
                    <th colspan="10">EJECTION</th>
                    <th rowspan="3">Cycle Time</th>
                    <th colspan="6">Mold Cooling</th>
                    <th colspan="14">HOT RUNNER TEMPERATURE</th>
                    <th rowspan="3">Dryer Temp</th>
                    <th rowspan="3">Dryer Time</th>
                    <th rowspan="3">Part Weight</th>
                    <th rowspan="3">Runner Weight</th>
                    <th colspan="2">MATERIAL</th>
                    <th colspan="2">Created</th>
                    <th colspan="2">Updated</th>
                </tr>';

        // BARIS 2
        $html .= '<tr class="header-main">
                    <th rowspan="2">Nozle</th><th rowspan="2">Front</th><th rowspan="2">Mid 3</th><th rowspan="2">Mid 2</th><th rowspan="2">Mid 1</th><th rowspan="2">Rear</th>
                    <th colspan="5">POSITION</th><th colspan="5">SPEED</th><th colspan="5">PRESSURE</th>
                    <th colspan="3">PRESSURE</th><th colspan="3">SPEED</th><th colspan="3">TIME</th>
                    <th colspan="4">SPEED</th><th colspan="4">PRESSURE</th><th colspan="4">BACK PRESS</th><th colspan="5">POSITION</th>
                    <th rowspan="2">SB1 P</th><th rowspan="2">SB2 P</th><th rowspan="2">SB1</th><th rowspan="2">SB2</th>
                    <th colspan="5">SPEED</th><th colspan="5">PRESSURE</th><th colspan="5">POSITION</th>
                    <th colspan="5">SPEED</th><th colspan="5">PRESSURE</th><th colspan="5">POSITION</th>
                    <th colspan="4">SPEED</th><th colspan="4">PRESSURE</th><th colspan="4">POSITION</th>
                    <th colspan="4">SPEED</th><th colspan="4">PRESSURE</th><th colspan="4">POSITION</th>
                    <th rowspan="2">Ej Time</th><th rowspan="2">Dly Fw</th><th rowspan="2">Dly Bw</th><th rowspan="2">Fw Time</th><th rowspan="2">Bw Time</th><th rowspan="2">Every Dly</th><th rowspan="2">Fw Maint</th><th rowspan="2">Semi Auto</th><th rowspan="2">BW Sig</th><th rowspan="2">Safety</th>
                    <th colspan="2">Core</th><th colspan="2">Cavity</th><th colspan="2">Slider</th>
                    <th rowspan="2">Z1</th><th rowspan="2">Z2</th><th rowspan="2">Z3</th><th rowspan="2">Z4</th><th rowspan="2">Z5</th><th rowspan="2">Z6</th><th rowspan="2">Z7</th><th rowspan="2">Z8</th><th rowspan="2">Z9</th><th rowspan="2">Z10</th><th rowspan="2">Z11</th><th rowspan="2">Z12</th><th rowspan="2">Z13</th><th rowspan="2">Z14</th>
                    <th rowspan="2">Part No</th><th rowspan="2">Part Name</th>
                    <th rowspan="2">By</th><th rowspan="2">Date</th><th rowspan="2">By</th><th rowspan="2">Date</th>
                </tr>';

        // BARIS 3
        $html .= '<tr class="header-main">
                    <th>S1</th><th>S2</th><th>S3</th><th>S4</th><th>S5</th><th>V1</th><th>V2</th><th>V3</th><th>V4</th><th>V5</th><th>P1</th><th>P2</th><th>P3</th><th>P4</th><th>P5</th>
                    <th>P1</th><th>P2</th><th>P3</th><th>V1</th><th>V2</th><th>V3</th><th>T1</th><th>T2</th><th>T3</th>
                    <th>1</th><th>2</th><th>3</th><th>4</th><th>1</th><th>2</th><th>3</th><th>4</th><th>1</th><th>2</th><th>3</th><th>4</th><th>1</th><th>2</th><th>3</th><th>4</th><th>End</th>
                    <th>V1</th><th>V2</th><th>V3</th><th>V4</th><th>V5</th><th>P1</th><th>P2</th><th>P3</th><th>P4</th><th>P5</th><th>S1</th><th>S2</th><th>S3</th><th>S4</th><th>S5</th>
                    <th>V1</th><th>V2</th><th>V3</th><th>V4</th><th>V5</th><th>P1</th><th>P2</th><th>P3</th><th>P4</th><th>P5</th><th>S1</th><th>S2</th><th>S3</th><th>S4</th><th>S5</th>
                    <th>V1</th><th>V2</th><th>V3</th><th>V4</th><th>P1</th><th>P2</th><th>P3</th><th>P4</th><th>S1</th><th>S2</th><th>S3</th><th>S4</th>
                    <th>V1</th><th>V2</th><th>V3</th><th>V4</th><th>P1</th><th>P2</th><th>P3</th><th>P4</th><th>S1</th><th>S2</th><th>S3</th><th>S4</th>
                    <th>Tmp</th><th>Use</th><th>Tmp</th><th>Use</th><th>Tmp</th><th>Use</th>
                </tr></thead><tbody>';

        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                        <td>'.$no.'</td>
                        <td>'.$data['item_fg_id'].'</td>
                        <td style="mso-number-format:\@;">'.$data['item_fg_number'].'</td>
                        <td>'.$data['item_fg_name'].'</td>
                        <td>'.$data['machine_number'].'</td>
                        <td>'.$data['toonage'].'</td>
                        <td>'.$data['maker'].'</td>
                        <td>'.$data['nozzle'].'</td><td>'.$data['front'].'</td><td>'.$data['middle_3'].'</td><td>'.$data['middle_2'].'</td><td>'.$data['middle_1'].'</td><td>'.$data['rear'].'</td>
                        <td>'.$data['s1_injection'].'</td><td>'.$data['s2_injection'].'</td><td>'.$data['s3_injection'].'</td><td>'.$data['s4_injection'].'</td><td>'.$data['s5_injection'].'</td>
                        <td>'.$data['v1_injection'].'</td><td>'.$data['v2_injection'].'</td><td>'.$data['v3_injection'].'</td><td>'.$data['v4_injection'].'</td><td>'.$data['v5_injection'].'</td>
                        <td>'.$data['p1_injection'].'</td><td>'.$data['p2_injection'].'</td><td>'.$data['p3_injection'].'</td><td>'.$data['p4_injection'].'</td><td>'.$data['p5_injection'].'</td>
                        <td>'.$data['p1_holding'].'</td><td>'.$data['p2_holding'].'</td><td>'.$data['p3_holding'].'</td>
                        <td>'.$data['v1_holding'].'</td><td>'.$data['v2_holding'].'</td><td>'.$data['v3_holding'].'</td>
                        <td>'.$data['t1_holding'].'</td><td>'.$data['t2_holding'].'</td><td>'.$data['t3_holding'].'</td>
                        <td>'.$data['1_charging_speed'].'</td><td>'.$data['2_charging_speed'].'</td><td>'.$data['3_charging_speed'].'</td><td>'.$data['4_charging_speed'].'</td>
                        <td>'.$data['1_charging_pressure'].'</td><td>'.$data['2_charging_pressure'].'</td><td>'.$data['3_charging_pressure'].'</td><td>'.$data['4_charging_pressure'].'</td>
                        <td>'.$data['1_charging_back_pressure'].'</td><td>'.$data['2_charging_back_pressure'].'</td><td>'.$data['3_charging_back_pressure'].'</td><td>'.$data['4_charging_back_pressure'].'</td>
                        <td>'.$data['1_charging_position'].'</td><td>'.$data['2_charging_position'].'</td><td>'.$data['3_charging_position'].'</td><td>'.$data['4_charging_position'].'</td><td>'.$data['end_charging_position'].'</td>
                        <td>'.$data['suck_back_1_pressure'].'</td><td>'.$data['suck_back_2_pressure'].'</td><td>'.$data['suck_back_1'].'</td><td>'.$data['suck_back_2'].'</td>
                        <td>'.$data['injection_time'].'</td><td>'.$data['delay_sb1'].'</td><td>'.$data['delay_sb2'].'</td><td>'.$data['delay_charge'].'</td>
                        <td>'.$data['inj_monitoring_time'].'</td><td>'.$data['charge_monitoring_time'].'</td><td>'.$data['cooling_time'].'</td>
                        <td>'.$data['min_cushion_check'].'</td><td>'.$data['min_cushion_low_limit'].'</td><td>'.$data['min_cushion_upper_limit'].'</td>
                        <td>'.$data['charge_after_cooling'].'</td><td>'.$data['use_manual_back_pressure'].'</td><td>'.$data['actual_cushion'].'</td>
                        <td>'.$data['switch_over_position'].'</td><td>'.$data['switch_over_time'].'</td>
                        <td>'.$data['v1_mold_closing'].'</td><td>'.$data['v2_mold_closing'].'</td><td>'.$data['v3_mold_closing'].'</td><td>'.$data['v4_mold_closing'].'</td><td>'.$data['v5_mold_closing'].'</td>
                        <td>'.$data['p1_mold_closing'].'</td><td>'.$data['p2_mold_closing'].'</td><td>'.$data['p3_mold_closing'].'</td><td>'.$data['p4_mold_closing'].'</td><td>'.$data['p5_mold_closing'].'</td>
                        <td>'.$data['s1_mold_closing'].'</td><td>'.$data['s2_mold_closing'].'</td><td>'.$data['s3_mold_closing'].'</td><td>'.$data['s4_mold_closing'].'</td><td>'.$data['s5_mold_closing'].'</td>
                        <td>'.$data['v1_mold_opening'].'</td><td>'.$data['v2_mold_opening'].'</td><td>'.$data['v3_mold_opening'].'</td><td>'.$data['v4_mold_opening'].'</td><td>'.$data['v5_mold_opening'].'</td>
                        <td>'.$data['p1_mold_opening'].'</td><td>'.$data['p2_mold_opening'].'</td><td>'.$data['p3_mold_opening'].'</td><td>'.$data['p4_mold_opening'].'</td><td>'.$data['p5_mold_opening'].'</td>
                        <td>'.$data['s1_mold_opening'].'</td><td>'.$data['s2_mold_opening'].'</td><td>'.$data['s3_mold_opening'].'</td><td>'.$data['s4_mold_opening'].'</td><td>'.$data['s5_mold_opening'].'</td>
                        <td>'.$data['delay_mold_closing'].'</td><td>'.$data['mq_delay_time'].'</td><td>'.$data['mold_closing_time'].'</td><td>'.$data['mold_opening_time'].'</td>
                        <td>'.$data['process_time'].'</td><td>'.$data['mold_protection_time_1'].'</td><td>'.$data['mold_protection_time_2'].'</td>
                        <td>'.$data['v1_ej_forward'].'</td><td>'.$data['v2_ej_forward'].'</td><td>'.$data['v3_ej_forward'].'</td><td>'.$data['v4_ej_forward'].'</td>
                        <td>'.$data['p1_ej_forward'].'</td><td>'.$data['p2_ej_forward'].'</td><td>'.$data['p3_ej_forward'].'</td><td>'.$data['p4_ej_forward'].'</td>
                        <td>'.$data['s1_ej_forward'].'</td><td>'.$data['s2_ej_forward'].'</td><td>'.$data['s3_ej_forward'].'</td><td>'.$data['s4_ej_forward'].'</td>
                        <td>'.$data['v1_ej_backward'].'</td><td>'.$data['v2_ej_backward'].'</td><td>'.$data['v3_ej_backward'].'</td><td>'.$data['v4_ej_backward'].'</td>
                        <td>'.$data['p1_ej_backward'].'</td><td>'.$data['p2_ej_backward'].'</td><td>'.$data['p3_ej_backward'].'</td><td>'.$data['p4_ej_backward'].'</td>
                        <td>'.$data['s1_ej_backward'].'</td><td>'.$data['s2_ej_backward'].'</td><td>'.$data['s3_ej_backward'].'</td><td>'.$data['s4_ej_backward'].'</td>
                        <td>'.$data['ejecting_time'].'</td><td>'.$data['delay_forward'].'</td><td>'.$data['delay_backward'].'</td><td>'.$data['forward_time'].'</td><td>'.$data['backward_time'].'</td><td>'.$data['every_time_delays'].'</td><td>'.$data['ejector_forward_maintain'].'</td><td>'.$data['semi_automatic_ej_switch'].'</td><td>'.$data['ejector_bw_com_signal'].'</td><td>'.$data['semi_automatic_safety_door_start'].'</td>
                        <td>'.$data['cycle_time_actual'].'</td>
                        <td>'.$data['core_temp'].'</td><td>'.$data['core_use'].'</td><td>'.$data['cavity_temp'].'</td><td>'.$data['cavity_use'].'</td><td>'.$data['slider_temp'].'</td><td>'.$data['slider_use'].'</td>
                        <td>'.$data['hr_zone_1'].'</td><td>'.$data['hr_zone_2'].'</td><td>'.$data['hr_zone_3'].'</td><td>'.$data['hr_zone_4'].'</td><td>'.$data['hr_zone_5'].'</td><td>'.$data['hr_zone_6'].'</td><td>'.$data['hr_zone_7'].'</td><td>'.$data['hr_zone_8'].'</td><td>'.$data['hr_zone_9'].'</td><td>'.$data['hr_zone_10'].'</td><td>'.$data['hr_zone_11'].'</td><td>'.$data['hr_zone_12'].'</td><td>'.$data['hr_zone_13'].'</td><td>'.$data['hr_zone_14'].'</td>
                        <td>'.$data['dryer_temperature'].'</td><td>'.$data['dryer_time'].'</td><td>'.$data['weight'].'</td><td>'.$data['runner'].'</td>
                        <td style="mso-number-format:\@;">'.$data['item_rm_number'].'</td><td>'.$data['item_rm_name'].'</td>
                        <td>'.$data['created_by'].'</td><td>'.$data['created_date'].'</td>
                        <td>'.$data['updated_by'].'</td><td>'.$data['updated_date'].'</td>
                    </tr>';
            $no++;
        }

        $html .= '</tbody></table></body></html>';
        echo $html;
    }

    // public function print_mps($encoded_id) //non A4
    // {
    //     $id = base64_decode($encoded_id);
    //     // Ambil Config Perusahaan
    //     $config = $this->db->get('config')->row();

    //     $setting_parameters  = $this->crud->read('setting_parameters', [], ["id" => $id]);

    //     $approval = $this->crud->read('approvals', [], ["table_name" => "setting_parameters"]);
    //     $user_0 = $this->session->name;
    //     $user_1 = $this->crud->read('users', [], ["username" => $setting_parameters->created_by]);
    //     $user_2 = $this->crud->read('users', [], ["username" => $approval->user_approval_1]);

    //     $users_0 = $users_1 = $users_2 = '';
        
    //     if ($setting_parameters->approved >= 1) {
    //         $this->createQrcode($user_0, "assets/image/qrcode/");
    //         $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $user_0 . '.png') . '" width="70"/>';
    //     }
    //     if ($setting_parameters->approved >= 1) {
    //         $this->createQrcode($user_1->name, "assets/image/qrcode/");
    //         $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="70"/>';
    //     }
    //     if ($setting_parameters->approved >= 2) {
    //         $this->createQrcode($user_2->name, "assets/image/qrcode/");
    //         $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="70"/>';
    //     }

        
    //     // Ambil Data Parameter (Pastikan join sesuai dengan database Anda)
    //     $this->db->select('a.*, 
    //         b.number as machine_number, 
    //         b.name as machine_name,
    //         d.number as item_familys_number,
    //         e.color as item_fg_color,
    //         g.name as customer_name');
    //     $this->db->from('setting_parameters a');
    //     $this->db->join('machines b', 'a.machine_id = b.id', 'left');
    //     $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
    //     $this->db->join('item_family_subs d', 'c.item_sub_family_id = d.id', 'left');
    //     $this->db->join('item_fg e', 'a.item_fg_id = e.id', 'left');
    //     $this->db->join('customer_items f', 'e.id = f.item_fg_id', 'left');
    //     $this->db->join('customers g', 'g.id = f.customer_id', 'left');
        
    //     $this->db->where('a.id', $id);
    //     $data = $this->db->get()->row_array();

    //     if (!$data) {
    //         die("Data Setting Parameter tidak ditemukan!");
    //     }

    //     // Mulai Menyusun HTML (Gunakan CSS untuk merapikan tabel)
    //     $html = '
    //     <html>
    //     <head>
    //         <title>MPS - ' . $data['item_fg_number'] . '</title>
    //         <style>
    //             /* --- Gaya Dasar Form MPS --- */
    //             body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; }
    //             table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    //             th, td { border: 1px solid #000; padding: 4px 6px; }
    //             .no-border { border: none !important; }
    //             .bg-gray { background-color: #e0e0e0; font-weight: bold; }
    //             .text-center { text-align: center; }
    //             .text-bold { font-weight: bold; }
    //             .header-title { font-size: 16px; font-weight: bold; text-align: center; }

    //             /* --- Gaya Khusus Tampilan Layar vs Print --- */
    //             @media screen { 
    //                 .print-area { display: none; } 
    //                 .instruction { margin: 10% auto; text-align: center; font-family: Arial; } 
    //             }
    //             @media print { 
    //                 .instruction { display: none; } 
    //                 .print-area { display: block; } 
    //                 /* Atur ukuran kertas di sini (bisa A4 portrait atau A3 landscape) */
    //                 @page { size: A4 portrait; margin: 0.5cm; } 
    //             }
    //         </style>
    //     </head>
        
    //     <body onload="window.print()">
            
    //     <div class="instruction">
    //         <h1>Press CTRL + P to Print</h1>
    //         <p>Layout: Landscape | Paper: A3 | Scale: 98% atau Fit to Page</p>
    //     </div>

    //     <div class="print-area">';

    //     // Hitung Shot Weight (Part Weight + Runner Weight)
    //     $part_weight = floatval($data['weight']);
    //     $runner_weight = floatval($data['runner']);
    //     $shot_weight = $part_weight + $runner_weight;

    //     // ==========================================
    //     // 1. HEADER
    //     // ==========================================
    //     $html .= '
    //     <table class="no-border" style="margin-bottom: 15px;">
    //         <tr>
    //             <td width="30%" class="no-border text-bold" style="font-size: 12px; vertical-align: top;"><img src="'.$config->favicon.'" width="30"> <br><br>'.$config->name.'</td>
    //             <td width="40%" class="no-border header-title" style="vertical-align: top;">MASTER PARAMETER SETTING</td>
    //             <td width="30%" class="no-border" style="text-align: right; vertical-align: top; font-size: 11px;">
    //                 FM-ENG-051-Rev-02<br>
    //                 NO.DOC : MPSI-ENG-108<br>
    //                 REV. DATE : ' . date('d M Y') . '
    //             </td>
    //         </tr>
    //     </table>';

    //     // ==========================================
    //     // 2. RODUK & MATERIAL
    //     // ==========================================
    //     $html .= '
    //     <table style="font-size: 9px;">
    //         <tr>
    //             <td class="bg-gray" width="15%">Part No</td>
    //             <td width="20%">' . $data['item_fg_number'] . '</td>
    //             <td class="bg-gray" width="12%">Material</td>
    //             <td width="20%">' . $data['item_familys_number'] . '</td>
    //             <td class="bg-gray" width="15%">Part wt gms</td>
    //             <td width="18%">' . $part_weight . '</td>
    //         </tr>
    //         <tr>
    //             <td class="bg-gray">Part Name</td>
    //             <td>' . $data['item_fg_name'] . '</td>
    //             <td class="bg-gray">Grade</td>
    //             <td>' . $data['item_rm_name'] . '</td>
    //             <td class="bg-gray">Running Cavity</td>
    //             <td>' . $data['cavity_actual'] . '</td>
    //         </tr>
    //         <tr>
    //             <td class="bg-gray">Model</td>
    //             <td>-</td>
    //             <td class="bg-gray">Colour</td>
    //             <td>' . $data['item_fg_color'] . '</td>
    //             <td class="bg-gray">Runner wt gms</td>
    //             <td>' . $runner_weight . '</td>
    //         </tr>
    //         <tr>
    //             <td class="bg-gray">Customer</td>
    //             <td>' . $data['customer_name'] . '</td>
    //             <td class="bg-gray">M/C No, T</td>
    //             <td>' . $data['machine_number'] . '; ' . $data['toonage'] . 'T, ' . $data['maker'] . '</td>
    //             <td class="bg-gray">Shot wt gms</td>
    //             <td>' . $shot_weight . '</td>
    //         </tr>
    //     </table>';
        

    //     // ==========================================
    //     // 3. TABEL UTAMA (INJECTION, HOLD, CLAMP, EJECTOR)
    //     // ==========================================
    //     $html .= '
    //     <table style="font-size: 11px;">
    //         <tr class="bg-gray text-center text-bold">
    //             <td width="20%" colspan="2" class="text-left">TOLERENCE +/- 10</td>
    //             <td width="5%">Unit</td>
    //             <td width="7%">Stage 1</td>
    //             <td width="7%">Stage 2</td>
    //             <td width="7%">Stage 3</td>
    //             <td width="7%">Stage 4</td>
    //             <td width="7%">Stage 5</td>
    //             <td width="7%">Stage 6</td>
    //             <td width="16.5%" class="text-left">Injection Mode</td>
    //             <td width="16.5%" colspan="2">AUTO / SEMI</td>
    //         </tr>
            
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Injection position</td>
    //             <td>mm</td>
    //             <td>' . $data['s1_injection'] . '</td>
    //             <td>' . $data['s2_injection'] . '</td>
    //             <td>' . $data['s3_injection'] . '</td>
    //             <td>' . $data['s4_injection'] . '</td>
    //             <td>' . $data['s5_injection'] . '</td>
    //             <td></td>
    //             <td class="text-left">Nozzle Dia mm</td>
    //             <td colspan="2"></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Injection speed</td>
    //             <td>%</td>
    //             <td>' . $data['v1_injection'] . '</td>
    //             <td>' . $data['v2_injection'] . '</td>
    //             <td>' . $data['v3_injection'] . '</td>
    //             <td>' . $data['v4_injection'] . '</td>
    //             <td>' . $data['v5_injection'] . '</td>
    //             <td></td>
    //             <td class="text-left">Back Pressure</td>
    //             <td colspan="2"></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Injection pressure</td>
    //             <td>bar</td>
    //             <td>' . $data['p1_injection'] . '</td>
    //             <td>' . $data['p2_injection'] . '</td>
    //             <td>' . $data['p3_injection'] . '</td>
    //             <td>' . $data['p4_injection'] . '</td>
    //             <td>' . $data['p5_injection'] . '</td>
    //             <td></td>
    //             <td class="text-left">Clamp tonnage</td>
    //             <td colspan="2"></td>
    //         </tr>

    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Hold on pressure</td>
    //             <td>bar</td>
    //             <td>' . $data['p1_holding'] . '</td>
    //             <td>' . $data['p2_holding'] . '</td>
    //             <td>' . $data['p3_holding'] . '</td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td class="text-left">Shot volume</td>
    //             <td colspan="2"></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Hold on speed</td>
    //             <td>%</td>
    //             <td>' . $data['v1_holding'] . '</td>
    //             <td>' . $data['v2_holding'] . '</td>
    //             <td>' . $data['v3_holding'] . '</td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td class="text-left">Injection time (Sec)</td>
    //             <td colspan="2">' . $data['injection_time'] . '</td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Hold on position</td>
    //             <td>mm</td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td class="text-left">Holding time (Sec)</td>
    //             <td colspan="2"></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Holding time</td>
    //             <td>sec</td>
    //             <td>' . $data['t1_holding'] . '</td>
    //             <td>' . $data['t2_holding'] . '</td>
    //             <td>' . $data['t3_holding'] . '</td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td class="text-left">Melt cushion</td>
    //             <td colspan="2"></td>
    //         </tr>

    //         <tr class="text-center bg-gray"><td colspan="12"></td></tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Clamp open speed</td>
    //             <td>%</td>
    //             <td>' . $data['v1_mold_opening'] . '</td>
    //             <td>' . $data['v2_mold_opening'] . '</td>
    //             <td>' . $data['v3_mold_opening'] . '</td>
    //             <td>' . $data['v4_mold_opening'] . '</td>
    //             <td>' . $data['v5_mold_opening'] . '</td>
    //             <td></td>
    //             <td class="text-left">Sw ovr strk/prs/time</td>
    //             <td colspan="2"></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Clamp open pressure</td>
    //             <td>bar</td>
    //             <td>' . $data['p1_mold_opening'] . '</td>
    //             <td>' . $data['p2_mold_opening'] . '</td>
    //             <td>' . $data['p3_mold_opening'] . '</td>
    //             <td>' . $data['p4_mold_opening'] . '</td>
    //             <td>' . $data['p5_mold_opening'] . '</td>
    //             <td></td>
    //             <td class="text-left">Cooling time (Sec)</td>
    //             <td colspan="2">' . $data['cooling_time'] . '</td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Clamp open position</td>
    //             <td>mm</td>
    //             <td>' . $data['s1_mold_opening'] . '</td>
    //             <td>' . $data['s2_mold_opening'] . '</td>
    //             <td>' . $data['s3_mold_opening'] . '</td>
    //             <td>' . $data['s4_mold_opening'] . '</td>
    //             <td>' . $data['s5_mold_opening'] . '</td>
    //             <td></td>
    //             <td class="text-left">Dosing time (Sec)</td>
    //             <td colspan="2"></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Clamp close speed</td>
    //             <td>%</td>
    //             <td>' . $data['v1_mold_closing'] . '</td>
    //             <td>' . $data['v2_mold_closing'] . '</td>
    //             <td>' . $data['v3_mold_closing'] . '</td>
    //             <td>' . $data['v4_mold_closing'] . '</td>
    //             <td>' . $data['v5_mold_closing'] . '</td>
    //             <td></td>
    //             <td class="text-left">Cycle time (Sec)</td>
    //             <td colspan="2">' . $data['cycle_time_actual'] . '</td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Clamp close pressure</td>
    //             <td>bar</td>
    //             <td>' . $data['p1_mold_closing'] . '</td>
    //             <td>' . $data['p2_mold_closing'] . '</td>
    //             <td>' . $data['p3_mold_closing'] . '</td>
    //             <td>' . $data['p4_mold_closing'] . '</td>
    //             <td>' . $data['p5_mold_closing'] . '</td>
    //             <td></td>
    //             <td class="bg-gray text-bold" colspan="3">MOULD COOLING</td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Clamp close position</td>
    //             <td>mm</td>
    //             <td>' . $data['s1_mold_closing'] . '</td>
    //             <td>' . $data['s2_mold_closing'] . '</td>
    //             <td>' . $data['s3_mold_closing'] . '</td>
    //             <td>' . $data['s4_mold_closing'] . '</td>
    //             <td>' . $data['s5_mold_closing'] . '</td>
    //             <td></td>
    //             <td class="text-left">CORE (&deg;C)</td>
    //             <td>' . $data['core_temp'] . '</td>
    //             <td>' . $data['core_use'] . '</td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left"></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td class="text-left">CAVITY (&deg;C)</td>
    //             <td>' . $data['cavity_temp'] . '</td>
    //             <td>' . $data['cavity_use'] . '</td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left"></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td class="text-left">SLIDER (&deg;C)</td>
    //             <td>' . $data['slider_temp'] . '</td>
    //             <td>' . $data['slider_use'] . '</td>
    //         </tr>

    //         <tr class="text-center bg-gray"><td colspan="12"></td></tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Ejection Forward stroke</td>
    //             <td>mm</td>
    //             <td>' . $data['s1_ej_forward'] . '</td>
    //             <td>' . $data['s2_ej_forward'] . '</td>
    //             <td>' . $data['s3_ej_forward'] . '</td>
    //             <td>' . $data['s4_ej_forward'] . '</td>
    //             <td></td>
    //             <td></td>
    //             <td class="bg-gray text-bold" colspan="3">HOT RUNNER TEMPERATURE (&deg;C)</td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Ejection Forward speed</td>
    //             <td>%</td>
    //             <td>' . $data['v1_ej_forward'] . '</td>
    //             <td>' . $data['v2_ej_forward'] . '</td>
    //             <td>' . $data['v3_ej_forward'] . '</td>
    //             <td>' . $data['v4_ej_forward'] . '</td>
    //             <td></td>
    //             <td></td>
    //             <td class="text-left">ZONE 1: <b>' . $data['hr_zone_1'] . '</b></td>
    //             <td colspan="2" class="text-left">ZONE 8: <b>' . $data['hr_zone_8'] . '</b></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Ejection Forward pressure</td>
    //             <td>bar</td>
    //             <td>' . $data['p1_ej_forward'] . '</td>
    //             <td>' . $data['p2_ej_forward'] . '</td>
    //             <td>' . $data['p3_ej_forward'] . '</td>
    //             <td>' . $data['p4_ej_forward'] . '</td>
    //             <td></td>
    //             <td></td>
    //             <td class="text-left">ZONE 2: <b>' . $data['hr_zone_2'] . '</b></td>
    //             <td colspan="2" class="text-left">ZONE 9: <b>' . $data['hr_zone_9'] . '</b></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Ejection Backward speed</td>
    //             <td>%</td>
    //             <td>' . $data['v1_ej_backward'] . '</td>
    //             <td>' . $data['v2_ej_backward'] . '</td>
    //             <td>' . $data['v3_ej_backward'] . '</td>
    //             <td>' . $data['v4_ej_backward'] . '</td>
    //             <td></td>
    //             <td></td>
    //             <td class="text-left">ZONE 3: <b>' . $data['hr_zone_3'] . '</b></td>
    //             <td colspan="2" class="text-left">ZONE 10: <b>' . $data['hr_zone_10'] . '</b></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Ejection Backward pressure</td>
    //             <td>bar</td>
    //             <td>' . $data['p1_ej_backward'] . '</td>
    //             <td>' . $data['p2_ej_backward'] . '</td>
    //             <td>' . $data['p3_ej_backward'] . '</td>
    //             <td>' . $data['p4_ej_backward'] . '</td>
    //             <td></td>
    //             <td></td>
    //             <td class="text-left">ZONE 4: <b>' . $data['hr_zone_4'] . '</b></td>
    //             <td colspan="2" class="text-left">ZONE 11: <b>' . $data['hr_zone_11'] . '</b></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Ejection Backward position</td>
    //             <td>mm</td>
    //             <td>' . $data['s1_ej_backward'] . '</td>
    //             <td>' . $data['s2_ej_backward'] . '</td>
    //             <td>' . $data['s3_ej_backward'] . '</td>
    //             <td>' . $data['s4_ej_backward'] . '</td>
    //             <td></td>
    //             <td></td>
    //             <td class="text-left">ZONE 5: <b>' . $data['hr_zone_5'] . '</b></td>
    //             <td colspan="2" class="text-left">ZONE 12: <b>' . $data['hr_zone_12'] . '</b></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Ejection Counter</td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td class="text-left">ZONE 6: <b>' . $data['hr_zone_6'] . '</b></td>
    //             <td colspan="2" class="text-left">ZONE 13: <b>' . $data['hr_zone_13'] . '</b></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td colspan="2" class="text-left">Ejection Forward Hold</td>
    //             <td>ON/OFF</td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td class="text-left">ZONE 7: <b>' . $data['hr_zone_7'] . '</b></td>
    //             <td colspan="2" class="text-left">ZONE 14: <b>' . $data['hr_zone_14'] . '</b></td>
    //         </tr>
    //     </table>';

    //     // ==========================================
    //     // 4. TABEL BARREL TEMP & REFILLING
    //     // ==========================================
    //     $html .= '
    //     <table style="font-size: 11px;">
    //         <tr class="bg-gray text-center text-bold">
    //             <td width="16%" class="text-left">Barrel Temp &deg;C</td>
    //             <td width="9%">Nozzle</td>
    //             <td width="8%">Z 1</td>
    //             <td width="8%">Z 2</td>
    //             <td width="8%">Z 3</td>
    //             <td width="8%">Z 4</td>
    //             <td width="8%">Z 5</td>
    //             <td width="8%">Z 6</td>
    //             <td width="13%">Feeding</td>
    //             <td width="14%">Oil</td>
    //         </tr>
    //         <tr class="text-center">
    //             <td></td>
    //             <td>' . $data['nozzle'] . '</td>
    //             <td>' . $data['front'] . '</td>
    //             <td>' . $data['middle_3'] . '</td>
    //             <td>' . $data['middle_2'] . '</td>
    //             <td>' . $data['middle_1'] . '</td>
    //             <td>' . $data['rear'] . '</td>
    //             <td></td> 
    //             <td></td>
    //             <td></td>
    //         </tr>
    //     </table>

    //     <table style="font-size: 11px;">
    //         <tr class="bg-gray text-center text-bold">
    //             <td width="16%" class="text-left">Refilling</td>
    //             <td width="9%">F.S. Back</td>
    //             <td width="8%">Stage I</td>
    //             <td width="8%">Stage II</td>
    //             <td width="8%">Stage III</td>
    //             <td width="8%">Stage IV</td>
    //             <td width="8%">Stage V</td>
    //             <td width="8%">S. Back</td>
    //             <td width="27%">Hopper Dryer Temp &deg;C +/- 10</td>
    //         </tr>
    //         <tr class="text-center">
    //             <td class="text-left">Position</td>
    //             <td></td>
    //             <td>' . $data['1_charging_position'] . '</td>
    //             <td>' . $data['2_charging_position'] . '</td>
    //             <td>' . $data['3_charging_position'] . '</td>
    //             <td>' . $data['4_charging_position'] . '</td>
    //             <td>' . $data['end_charging_position'] . '</td>
    //             <td>' . $data['suck_back_1'] . '</td>
    //             <td rowspan="2" style="font-size: 14px;"><b>' . $data['dryer_temperature'] . '</b></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td class="text-left">Pressure</td>
    //             <td></td>
    //             <td>' . $data['1_charging_pressure'] . '</td>
    //             <td>' . $data['2_charging_pressure'] . '</td>
    //             <td>' . $data['3_charging_pressure'] . '</td>
    //             <td>' . $data['4_charging_pressure'] . '</td>
    //             <td></td>
    //             <td>' . $data['suck_back_1_pressure'] . '</td>
    //         </tr>
    //         <tr class="text-center">
    //             <td class="text-left">Speed</td>
    //             <td></td>
    //             <td>' . $data['1_charging_speed'] . '</td>
    //             <td>' . $data['2_charging_speed'] . '</td>
    //             <td>' . $data['3_charging_speed'] . '</td>
    //             <td>' . $data['4_charging_speed'] . '</td>
    //             <td></td>
    //             <td></td>
    //             <td class="bg-gray text-bold">Pre Drying Time</td>
    //         </tr>
    //         <tr class="text-center">
    //             <td class="text-left">Back press</td>
    //             <td></td>
    //             <td>' . $data['1_charging_back_pressure'] . '</td>
    //             <td>' . $data['2_charging_back_pressure'] . '</td>
    //             <td>' . $data['3_charging_back_pressure'] . '</td>
    //             <td>' . $data['4_charging_back_pressure'] . '</td>
    //             <td></td>
    //             <td></td>
    //             <td><b>' . $data['dryer_time'] . ' Hours</b></td>
    //         </tr>
    //     </table>';

    //     // ==========================================
    //     // 5. TABEL VALVE GATE & NOTE
    //     // ==========================================
    //     $html .= '
    //     <table style="font-size: 9px; margin-top: 10px;">
    //         <tr class="bg-gray text-center text-bold">
    //             <td rowspan="2" width="5%">Valve Gate</td>
    //             <td rowspan="2" width="5%">Gate No</td>
    //             <td colspan="4" width="30%">Open at Injection</td>
    //             <td colspan="3" width="30%">Close At injection</td>
    //             <td colspan="4" width="30%">Open at Hold</td>
    //         </tr>
    //         <tr class="bg-gray text-center text-bold">
    //             <td width="7%">Use</td>
    //             <td width="7%">Mode</td>
    //             <td width="7%">Posn. Mm</td>
    //             <td width="7%">Sec</td>

    //             <td width="9%">Mode</td>
    //             <td width="9%">Posn. Mm</td>
    //             <td width="9%">Sec</td>

    //             <td width="7%">Use</td>
    //             <td width="7%">Delay</td>
    //             <td width="7%">Time</td>
    //             <td width="7%">Pos</td>
    //         </tr>
    //         <tr class="text-center">
    //             <td></td>
    //             <td>1</td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td></td>
    //             <td>2</td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td></td>
    //             <td>3</td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td></td>
    //             <td>4</td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //             <td></td>
    //         </tr>
    //     </table>';

    //     // ==========================================
    //     // 6. TABEL USE / NOT USE (CORE) & REMARKS
    //     // ==========================================
    //     $html .= '
    //     <table style="font-size: 9px; margin-top: 5px;">
    //         <tr class="bg-gray text-center text-bold">
    //             <td colspan="2" width="14%">Use / Not Use</td>
    //             <td width="8%">Mode</td>
    //             <td width="9%">Operation</td>
    //             <td width="8%">Coincide</td>
    //             <td width="9%">Start pos. (mm)</td>
    //             <td width="10%">Monitor pos. (mm)</td>
    //             <td width="9%">Pressure (bar)</td>
    //             <td width="9%">Velocity (%)</td>
    //             <td width="6%">Hold</td>
    //             <td width="6%">Delay</td>
    //             <td width="6%">Set Time</td>
    //             <td width="6%">Actual Time</td>
    //         </tr>
    //         <tr class="text-center">
    //             <td rowspan="2" width="7%" class="text-bold">Core 1</td>
    //             <td width="7%">In</td>
    //             <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td>Out</td>
    //             <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td rowspan="2" class="text-bold">Core 2</td>
    //             <td>In</td>
    //             <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td>Out</td>
    //             <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td rowspan="2" class="text-bold">Core 3</td>
    //             <td>In</td>
    //             <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td>Out</td>
    //             <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td rowspan="2" class="text-bold">Core 4</td>
    //             <td>In</td>
    //             <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    //         </tr>
    //         <tr class="text-center">
    //             <td>Out</td>
    //             <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    //         </tr>
    //         <tr>
    //             <td colspan="13" style="text-align: left; padding: 5px;">
    //                 <b>REMARKS :</b> This Parameters are Maintained and to be Monitoring
    //             </td>
    //         </tr>
    //     </table>';

    //     // ==========================================
    //     // 7. TABEL RIWAYAT REVISI & KOLOM APPROVAL
    //     // ==========================================
    //     $html .= '
    //     <table class="no-border" style="width: 100%; margin-top: 15px; font-size: 9px;">
    //         <tr>
    //             <td class="no-border" width="55%" style="vertical-align: bottom;">
    //                 <table style="width: 95%; font-size: 9px; text-align: center;">
    //                    <tr>
    //                         <td style="height: 10px;">4</td>
    //                         <td></td>
    //                         <td></td>
    //                         <td></td>
    //                     </tr>
    //                     <tr>
    //                         <td style="height: 10px;">3</td>
    //                         <td></td>
    //                         <td></td>
    //                         <td></td>
    //                     </tr>
    //                     <tr>
    //                         <td style="height: 10px;">2</td>
    //                         <td></td>
    //                         <td></td>
    //                         <td></td>
    //                     </tr>
    //                     <tr>
    //                         <td style="height: 10px;">1</td>
    //                         <td></td>
    //                         <td></td>
    //                         <td></td>
    //                     </tr>
    //                     <tr class="bg-gray text-bold">
    //                         <td width="10%">NO</td>
    //                         <td width="50%">Description</td>
    //                         <td width="20%">Date</td>
    //                         <td width="20%">PIC</td>
    //                     </tr>
    //                 </table>
    //             </td>
                
    //             <td class="no-border" width="30%" style="vertical-align: bottom;">
    //                 <table style="width: 100%; font-size: 9px; text-align: center;">
    //                     <tr class="bg-gray text-bold">
    //                         <td width="33.3%">Approval</td>
    //                         <td width="33.3%">Checked</td>
    //                         <td width="33.3%">Prepared</td>
    //                     </tr>
    //                     <tr>
    //                         <td height="60">' . $users_2 . '</td>
    //                         <td height="60">' . $users_1 . '</td>
    //                         <td height="60">' . $users_0 . '</td>
    //                     </tr>
    //                     <tr style="font-weight: bold;">
    //                         <td>' . ($user_2->name ?: '-') . '</td>
    //                         <td>' . ($user_1->name ?: '-') . '</td>
    //                         <td>' . ($user_0 ?: '-') . '</td>
    //                     </tr>
    //                 </table>
    //             </td>
    //         </tr>
    //     </table>';

    //     $html .= '
    //         </div>
    //     </body>
    //     </html>';

    //     // CETAK HTML
    //     echo $html;
    // }

    public function print_mps($encoded_id) //A4
    {
        $id = base64_decode($encoded_id);
        // Ambil Config Perusahaan
        $config = $this->db->get('config')->row();

        $setting_parameters  = $this->crud->read('setting_parameters', [], ["id" => $id]);

        $approval = $this->crud->read('approvals', [], ["table_name" => "setting_parameters"]);
        $user_0 = $this->session->name;
        $user_1 = $this->crud->read('users', [], ["username" => $setting_parameters->created_by]);
        $user_2 = $this->crud->read('users', [], ["username" => $approval->user_approval_1]);

        $users_0 = $users_1 = $users_2 = '';
        
        // Perkecil ukuran QR code agar tidak memakan ruang tinggi tabel (dari 70 ke 55)
        if ($setting_parameters->approved >= 1) {
            $this->createQrcode($user_0, "assets/image/qrcode/");
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $user_0 . '.png') . '" width="55"/>';
        }
        if ($setting_parameters->approved >= 1) {
            $this->createQrcode($user_1->name, "assets/image/qrcode/");
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="55"/>';
        }
        if ($setting_parameters->approved >= 2) {
            $this->createQrcode($user_2->name, "assets/image/qrcode/");
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="55"/>';
        }

        
        // Ambil Data Parameter
        $this->db->select('a.*, 
            b.number as machine_number, 
            b.name as machine_name,
            d.number as item_familys_number,
            e.color as item_fg_color,
            g.name as customer_name');
        $this->db->from('setting_parameters a');
        $this->db->join('machines b', 'a.machine_id = b.id', 'left');
        $this->db->join('item_rm c', 'a.item_rm_id = c.id', 'left');
        $this->db->join('item_family_subs d', 'c.item_sub_family_id = d.id', 'left');
        $this->db->join('item_fg e', 'a.item_fg_id = e.id', 'left');
        $this->db->join('customer_items f', 'e.id = f.item_fg_id', 'left');
        $this->db->join('customers g', 'g.id = f.customer_id', 'left');
        
        $this->db->where('a.id', $id);
        $data = $this->db->get()->row_array();

        if (!$data) {
            die("Data Setting Parameter tidak ditemukan!");
        }

        // Mulai Menyusun HTML
        $html = '
        <html>
        <head>
            <title>MPS - ' . $data['item_fg_number'] . '</title>
            <style>
                /* --- Gaya Dasar Form MPS --- */
                body { font-family: Arial, sans-serif; font-size: 8.5px; margin: 0; padding: 0; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
                th, td { border: 1px solid #000; padding: 2px 3px; } 
                .no-border { border: none !important; }
                .bg-gray { background-color: #e0e0e0; font-weight: bold; }
                .text-center { text-align: center; }
                .text-bold { font-weight: bold; }
                .header-title { font-size: 14px; font-weight: bold; text-align: center; text-decoration: underline; }

                /* --- Gaya Khusus Tampilan Layar vs Print --- */
                @media screen { 
                    .print-area { display: none; } 
                    .instruction { margin: 10% auto; text-align: center; font-family: Arial; } 
                }
                @media print { 
                    .instruction { display: none; } 
                    .print-area { display: block; } 
                    
                    /* MENGATUR MARGIN KERTAS AGAR DITENGAH */
                    /* Format: margin: Atas Kanan Bawah Kiri; */
                    @page { 
                        size: A4 portrait; 
                        margin: 2cm 0.3cm 2cm 0.3cm; 
                    } 
                }
            </style>
        </head>
        
        <body onload="window.print()">
            
        <div class="instruction">
            <h1>Memuat Pratinjau Cetak...</h1>
            <p>Jika dialog Print tidak muncul otomatis, tekan <b>CTRL + P</b> untuk mencetak.</p>
            <p>Rekomendasi Print: Layout <b>Portrait</b> | Kertas <b>A4</b> | Scale <b>Fit to Page / Default</b></p>
        </div>

        <div class="print-area">';

        // Hitung Shot Weight
        $part_weight = floatval($data['weight']);
        $runner_weight = floatval($data['runner']);
        $shot_weight = $part_weight + $runner_weight;

        // ==========================================
        // 1. HEADER
        // ==========================================
        $html .= '
        <table class="no-border" style="margin-bottom: 5px;">
            <tr>
                <td width="30%" class="no-border text-bold" style="font-size: 11px; vertical-align: middle;">
                    <img src="'.$config->favicon.'" width="25" style="vertical-align: middle;"> '.$config->name.'
                </td>
                <td width="40%" class="no-border header-title" style="vertical-align: middle;">MASTER PARAMETER SETTING</td>
                <td width="30%" class="no-border" style="text-align: right; vertical-align: top; font-size: 9px;">
                    FM-ENG-051-Rev-02<br>
                    NO.DOC : MPSI-ENG-108<br>
                    REV. DATE : ' . date('d M Y') . '
                </td>
            </tr>
        </table>';

        // ==========================================
        // 2. PRODUK & MATERIAL
        // ==========================================
        $html .= '
        <table style="font-size: 8.5px;">
            <tr>
                <td class="bg-gray" width="15%">Part No</td>
                <td width="20%">' . $data['item_fg_number'] . '</td>
                <td class="bg-gray" width="12%">Material</td>
                <td width="20%">' . $data['item_familys_number'] . '</td>
                <td class="bg-gray" width="15%">Part wt gms</td>
                <td width="18%">' . $part_weight . '</td>
            </tr>
            <tr>
                <td class="bg-gray">Part Name</td>
                <td>' . $data['item_fg_name'] . '</td>
                <td class="bg-gray">Grade</td>
                <td>' . $data['item_rm_name'] . '</td>
                <td class="bg-gray">Running Cavity</td>
                <td>' . $data['cavity_actual'] . '</td>
            </tr>
            <tr>
                <td class="bg-gray">Model</td>
                <td>-</td>
                <td class="bg-gray">Colour</td>
                <td>' . $data['item_fg_color'] . '</td>
                <td class="bg-gray">Runner wt gms</td>
                <td>' . $runner_weight . '</td>
            </tr>
            <tr>
                <td class="bg-gray">Customer</td>
                <td>' . $data['customer_name'] . '</td>
                <td class="bg-gray">M/C No, T</td>
                <td>' . $data['machine_number'] . '; ' . $data['toonage'] . 'T, ' . $data['maker'] . '</td>
                <td class="bg-gray">Shot wt gms</td>
                <td>' . $shot_weight . '</td>
            </tr>
        </table>';
        

        // ==========================================
        // 3. TABEL UTAMA (INJECTION, HOLD, CLAMP, EJECTOR)
        // ==========================================
        $html .= '
        <table style="font-size: 8.5px;">
            <tr class="bg-gray text-center text-bold">
                <td width="20%" colspan="2" class="text-left">TOLERENCE +/- 10</td>
                <td width="5%">Unit</td>
                <td width="7%">Stage 1</td>
                <td width="7%">Stage 2</td>
                <td width="7%">Stage 3</td>
                <td width="7%">Stage 4</td>
                <td width="7%">Stage 5</td>
                <td width="7%">Stage 6</td>
                <td width="16.5%" class="text-left">Injection Mode</td>
                <td width="16.5%" colspan="2">AUTO / SEMI</td>
            </tr>
            
            <tr class="text-center">
                <td colspan="2" class="text-left">Injection position</td>
                <td>mm</td>
                <td>' . $data['s1_injection'] . '</td>
                <td>' . $data['s2_injection'] . '</td>
                <td>' . $data['s3_injection'] . '</td>
                <td>' . $data['s4_injection'] . '</td>
                <td>' . $data['s5_injection'] . '</td>
                <td></td>
                <td class="text-left">Nozzle Dia mm</td>
                <td colspan="2"></td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Injection speed</td>
                <td>%</td>
                <td>' . $data['v1_injection'] . '</td>
                <td>' . $data['v2_injection'] . '</td>
                <td>' . $data['v3_injection'] . '</td>
                <td>' . $data['v4_injection'] . '</td>
                <td>' . $data['v5_injection'] . '</td>
                <td></td>
                <td class="text-left">Back Pressure</td>
                <td colspan="2"></td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Injection pressure</td>
                <td>bar</td>
                <td>' . $data['p1_injection'] . '</td>
                <td>' . $data['p2_injection'] . '</td>
                <td>' . $data['p3_injection'] . '</td>
                <td>' . $data['p4_injection'] . '</td>
                <td>' . $data['p5_injection'] . '</td>
                <td></td>
                <td class="text-left">Clamp tonnage</td>
                <td colspan="2"></td>
            </tr>

            <tr class="text-center">
                <td colspan="2" class="text-left">Hold on pressure</td>
                <td>bar</td>
                <td>' . $data['p1_holding'] . '</td>
                <td>' . $data['p2_holding'] . '</td>
                <td>' . $data['p3_holding'] . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-left">Shot volume</td>
                <td colspan="2"></td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Hold on speed</td>
                <td>%</td>
                <td>' . $data['v1_holding'] . '</td>
                <td>' . $data['v2_holding'] . '</td>
                <td>' . $data['v3_holding'] . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-left">Injection time (Sec)</td>
                <td colspan="2">' . $data['injection_time'] . '</td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Hold on position</td>
                <td>mm</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-left">Holding time (Sec)</td>
                <td colspan="2"></td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Holding time</td>
                <td>sec</td>
                <td>' . $data['t1_holding'] . '</td>
                <td>' . $data['t2_holding'] . '</td>
                <td>' . $data['t3_holding'] . '</td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-left">Melt cushion</td>
                <td colspan="2"></td>
            </tr>

            <tr class="text-center bg-gray"><td colspan="12"></td></tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Clamp open speed</td>
                <td>%</td>
                <td>' . $data['v1_mold_opening'] . '</td>
                <td>' . $data['v2_mold_opening'] . '</td>
                <td>' . $data['v3_mold_opening'] . '</td>
                <td>' . $data['v4_mold_opening'] . '</td>
                <td>' . $data['v5_mold_opening'] . '</td>
                <td></td>
                <td class="text-left">Sw ovr strk/prs/time</td>
                <td colspan="2"></td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Clamp open pressure</td>
                <td>bar</td>
                <td>' . $data['p1_mold_opening'] . '</td>
                <td>' . $data['p2_mold_opening'] . '</td>
                <td>' . $data['p3_mold_opening'] . '</td>
                <td>' . $data['p4_mold_opening'] . '</td>
                <td>' . $data['p5_mold_opening'] . '</td>
                <td></td>
                <td class="text-left">Cooling time (Sec)</td>
                <td colspan="2">' . $data['cooling_time'] . '</td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Clamp open position</td>
                <td>mm</td>
                <td>' . $data['s1_mold_opening'] . '</td>
                <td>' . $data['s2_mold_opening'] . '</td>
                <td>' . $data['s3_mold_opening'] . '</td>
                <td>' . $data['s4_mold_opening'] . '</td>
                <td>' . $data['s5_mold_opening'] . '</td>
                <td></td>
                <td class="text-left">Dosing time (Sec)</td>
                <td colspan="2"></td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Clamp close speed</td>
                <td>%</td>
                <td>' . $data['v1_mold_closing'] . '</td>
                <td>' . $data['v2_mold_closing'] . '</td>
                <td>' . $data['v3_mold_closing'] . '</td>
                <td>' . $data['v4_mold_closing'] . '</td>
                <td>' . $data['v5_mold_closing'] . '</td>
                <td></td>
                <td class="text-left">Cycle time (Sec)</td>
                <td colspan="2">' . $data['cycle_time_actual'] . '</td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Clamp close pressure</td>
                <td>bar</td>
                <td>' . $data['p1_mold_closing'] . '</td>
                <td>' . $data['p2_mold_closing'] . '</td>
                <td>' . $data['p3_mold_closing'] . '</td>
                <td>' . $data['p4_mold_closing'] . '</td>
                <td>' . $data['p5_mold_closing'] . '</td>
                <td></td>
                <td class="bg-gray text-bold" colspan="3">MOULD COOLING</td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Clamp close position</td>
                <td>mm</td>
                <td>' . $data['s1_mold_closing'] . '</td>
                <td>' . $data['s2_mold_closing'] . '</td>
                <td>' . $data['s3_mold_closing'] . '</td>
                <td>' . $data['s4_mold_closing'] . '</td>
                <td>' . $data['s5_mold_closing'] . '</td>
                <td></td>
                <td class="text-left">CORE (&deg;C)</td>
                <td>' . $data['core_temp'] . '</td>
                <td>' . $data['core_use'] . '</td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-left">CAVITY (&deg;C)</td>
                <td>' . $data['cavity_temp'] . '</td>
                <td>' . $data['cavity_use'] . '</td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-left">SLIDER (&deg;C)</td>
                <td>' . $data['slider_temp'] . '</td>
                <td>' . $data['slider_use'] . '</td>
            </tr>

            <tr class="text-center bg-gray"><td colspan="12"></td></tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Ejection Forward stroke</td>
                <td>mm</td>
                <td>' . $data['s1_ej_forward'] . '</td>
                <td>' . $data['s2_ej_forward'] . '</td>
                <td>' . $data['s3_ej_forward'] . '</td>
                <td>' . $data['s4_ej_forward'] . '</td>
                <td></td>
                <td></td>
                <td class="bg-gray text-bold" colspan="3">HOT RUNNER TEMPERATURE (&deg;C)</td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Ejection Forward speed</td>
                <td>%</td>
                <td>' . $data['v1_ej_forward'] . '</td>
                <td>' . $data['v2_ej_forward'] . '</td>
                <td>' . $data['v3_ej_forward'] . '</td>
                <td>' . $data['v4_ej_forward'] . '</td>
                <td></td>
                <td></td>
                <td class="text-left">ZONE 1: <b>' . $data['hr_zone_1'] . '</b></td>
                <td colspan="2" class="text-left">ZONE 8: <b>' . $data['hr_zone_8'] . '</b></td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Ejection Forward pressure</td>
                <td>bar</td>
                <td>' . $data['p1_ej_forward'] . '</td>
                <td>' . $data['p2_ej_forward'] . '</td>
                <td>' . $data['p3_ej_forward'] . '</td>
                <td>' . $data['p4_ej_forward'] . '</td>
                <td></td>
                <td></td>
                <td class="text-left">ZONE 2: <b>' . $data['hr_zone_2'] . '</b></td>
                <td colspan="2" class="text-left">ZONE 9: <b>' . $data['hr_zone_9'] . '</b></td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Ejection Backward speed</td>
                <td>%</td>
                <td>' . $data['v1_ej_backward'] . '</td>
                <td>' . $data['v2_ej_backward'] . '</td>
                <td>' . $data['v3_ej_backward'] . '</td>
                <td>' . $data['v4_ej_backward'] . '</td>
                <td></td>
                <td></td>
                <td class="text-left">ZONE 3: <b>' . $data['hr_zone_3'] . '</b></td>
                <td colspan="2" class="text-left">ZONE 10: <b>' . $data['hr_zone_10'] . '</b></td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Ejection Backward pressure</td>
                <td>bar</td>
                <td>' . $data['p1_ej_backward'] . '</td>
                <td>' . $data['p2_ej_backward'] . '</td>
                <td>' . $data['p3_ej_backward'] . '</td>
                <td>' . $data['p4_ej_backward'] . '</td>
                <td></td>
                <td></td>
                <td class="text-left">ZONE 4: <b>' . $data['hr_zone_4'] . '</b></td>
                <td colspan="2" class="text-left">ZONE 11: <b>' . $data['hr_zone_11'] . '</b></td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Ejection Backward position</td>
                <td>mm</td>
                <td>' . $data['s1_ej_backward'] . '</td>
                <td>' . $data['s2_ej_backward'] . '</td>
                <td>' . $data['s3_ej_backward'] . '</td>
                <td>' . $data['s4_ej_backward'] . '</td>
                <td></td>
                <td></td>
                <td class="text-left">ZONE 5: <b>' . $data['hr_zone_5'] . '</b></td>
                <td colspan="2" class="text-left">ZONE 12: <b>' . $data['hr_zone_12'] . '</b></td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Ejection Counter</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-left">ZONE 6: <b>' . $data['hr_zone_6'] . '</b></td>
                <td colspan="2" class="text-left">ZONE 13: <b>' . $data['hr_zone_13'] . '</b></td>
            </tr>
            <tr class="text-center">
                <td colspan="2" class="text-left">Ejection Forward Hold</td>
                <td>ON/OFF</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-left">ZONE 7: <b>' . $data['hr_zone_7'] . '</b></td>
                <td colspan="2" class="text-left">ZONE 14: <b>' . $data['hr_zone_14'] . '</b></td>
            </tr>
        </table>';

        // ==========================================
        // 4. TABEL BARREL TEMP & REFILLING
        // ==========================================
        $html .= '
        <table style="font-size: 8.5px;">
            <tr class="bg-gray text-center text-bold">
                <td width="16%" class="text-left">Barrel Temp &deg;C</td>
                <td width="9%">Nozzle</td>
                <td width="8%">Z 1</td>
                <td width="8%">Z 2</td>
                <td width="8%">Z 3</td>
                <td width="8%">Z 4</td>
                <td width="8%">Z 5</td>
                <td width="8%">Z 6</td>
                <td width="13%">Feeding</td>
                <td width="14%">Oil</td>
            </tr>
            <tr class="text-center">
                <td></td>
                <td>' . $data['nozzle'] . '</td>
                <td>' . $data['front'] . '</td>
                <td>' . $data['middle_3'] . '</td>
                <td>' . $data['middle_2'] . '</td>
                <td>' . $data['middle_1'] . '</td>
                <td>' . $data['rear'] . '</td>
                <td></td> 
                <td></td>
                <td></td>
            </tr>
        </table>

        <table style="font-size: 8.5px;">
            <tr class="bg-gray text-center text-bold">
                <td width="16%" class="text-left">Refilling</td>
                <td width="9%">F.S. Back</td>
                <td width="8%">Stage I</td>
                <td width="8%">Stage II</td>
                <td width="8%">Stage III</td>
                <td width="8%">Stage IV</td>
                <td width="8%">Stage V</td>
                <td width="8%">S. Back</td>
                <td width="27%">Hopper Dryer Temp &deg;C +/- 10</td>
            </tr>
            <tr class="text-center">
                <td class="text-left">Position</td>
                <td></td>
                <td>' . $data['1_charging_position'] . '</td>
                <td>' . $data['2_charging_position'] . '</td>
                <td>' . $data['3_charging_position'] . '</td>
                <td>' . $data['4_charging_position'] . '</td>
                <td>' . $data['end_charging_position'] . '</td>
                <td>' . $data['suck_back_1'] . '</td>
                <td rowspan="2" style="font-size: 14px;"><b>' . $data['dryer_temperature'] . '</b></td>
            </tr>
            <tr class="text-center">
                <td class="text-left">Pressure</td>
                <td></td>
                <td>' . $data['1_charging_pressure'] . '</td>
                <td>' . $data['2_charging_pressure'] . '</td>
                <td>' . $data['3_charging_pressure'] . '</td>
                <td>' . $data['4_charging_pressure'] . '</td>
                <td></td>
                <td>' . $data['suck_back_1_pressure'] . '</td>
            </tr>
            <tr class="text-center">
                <td class="text-left">Speed</td>
                <td></td>
                <td>' . $data['1_charging_speed'] . '</td>
                <td>' . $data['2_charging_speed'] . '</td>
                <td>' . $data['3_charging_speed'] . '</td>
                <td>' . $data['4_charging_speed'] . '</td>
                <td></td>
                <td></td>
                <td class="bg-gray text-bold">Pre Drying Time</td>
            </tr>
            <tr class="text-center">
                <td class="text-left">Back press</td>
                <td></td>
                <td>' . $data['1_charging_back_pressure'] . '</td>
                <td>' . $data['2_charging_back_pressure'] . '</td>
                <td>' . $data['3_charging_back_pressure'] . '</td>
                <td>' . $data['4_charging_back_pressure'] . '</td>
                <td></td>
                <td></td>
                <td><b>' . $data['dryer_time'] . ' Hours</b></td>
            </tr>
        </table>';

        // ==========================================
        // 5. TABEL VALVE GATE
        // ==========================================
        $html .= '
        <table style="font-size: 8.5px;">
            <tr class="bg-gray text-center text-bold">
                <td rowspan="2" width="5%">Valve Gate</td>
                <td rowspan="2" width="5%">Gate No</td>
                <td colspan="4" width="30%">Open at Injection</td>
                <td colspan="3" width="30%">Close At injection</td>
                <td colspan="4" width="30%">Open at Hold</td>
            </tr>
            <tr class="bg-gray text-center text-bold">
                <td width="7%">Use</td>
                <td width="7%">Mode</td>
                <td width="7%">Posn. Mm</td>
                <td width="7%">Sec</td>

                <td width="9%">Mode</td>
                <td width="9%">Posn. Mm</td>
                <td width="9%">Sec</td>

                <td width="7%">Use</td>
                <td width="7%">Delay</td>
                <td width="7%">Time</td>
                <td width="7%">Pos</td>
            </tr>
            <tr class="text-center">
                <td></td><td>1</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr class="text-center">
                <td></td><td>2</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr class="text-center">
                <td></td><td>3</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr class="text-center">
                <td></td><td>4</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
        </table>';

        // ==========================================
        // 6. TABEL USE / NOT USE (CORE) & REMARKS
        // ==========================================
        $html .= '
        <table style="font-size: 8.5px;">
            <tr class="bg-gray text-center text-bold">
                <td colspan="2" width="14%">Use / Not Use</td>
                <td width="8%">Mode</td>
                <td width="9%">Operation</td>
                <td width="8%">Coincide</td>
                <td width="9%">Start pos. (mm)</td>
                <td width="10%">Monitor pos. (mm)</td>
                <td width="9%">Pressure (bar)</td>
                <td width="9%">Velocity (%)</td>
                <td width="6%">Hold</td>
                <td width="6%">Delay</td>
                <td width="6%">Set Time</td>
                <td width="6%">Actual Time</td>
            </tr>
            <tr class="text-center">
                <td rowspan="2" width="7%" class="text-bold">Core 1</td>
                <td width="7%">In</td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr class="text-center">
                <td>Out</td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr class="text-center">
                <td rowspan="2" class="text-bold">Core 2</td>
                <td>In</td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr class="text-center">
                <td>Out</td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr class="text-center">
                <td rowspan="2" class="text-bold">Core 3</td>
                <td>In</td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr class="text-center">
                <td>Out</td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr class="text-center">
                <td rowspan="2" class="text-bold">Core 4</td>
                <td>In</td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr class="text-center">
                <td>Out</td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr>
                <td colspan="13" style="text-align: left; padding: 2px 5px;">
                    <b>REMARKS :</b> This Parameters are Maintained and to be Monitoring
                </td>
            </tr>
        </table>';

        // ==========================================
        // 7. TABEL RIWAYAT REVISI & KOLOM APPROVAL
        // ==========================================
        $html .= '
        <table class="no-border" style="width: 100%; font-size: 8.5px;">
            <tr>
                <td class="no-border" width="55%" style="vertical-align: bottom;">
                    <table style="width: 95%; font-size: 8.5px; text-align: center;">
                       <tr>
                            <td style="height: 8px;">4</td><td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td style="height: 8px;">3</td><td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td style="height: 8px;">2</td><td></td><td></td><td></td>
                        </tr>
                        <tr>
                            <td style="height: 8px;">1</td><td></td><td></td><td></td>
                        </tr>
                        <tr class="bg-gray text-bold">
                            <td width="10%">NO</td>
                            <td width="50%">Description</td>
                            <td width="20%">Date</td>
                            <td width="20%">PIC</td>
                        </tr>
                    </table>
                </td>
                
                <td class="no-border" width="45%" style="vertical-align: bottom;">
                    <table style="width: 100%; font-size: 8.5px; text-align: center;">
                        <tr class="bg-gray text-bold">
                            <td width="33.3%">Approval</td>
                            <td width="33.3%">Checked</td>
                            <td width="33.3%">Prepared</td>
                        </tr>
                        <tr>
                            <td height="45" style="vertical-align: middle;">' . $users_2 . '</td>
                            <td height="45" style="vertical-align: middle;">' . $users_1 . '</td>
                            <td height="45" style="vertical-align: middle;">' . $users_0 . '</td>
                        </tr>
                        <tr style="font-weight: bold;">
                            <td>' . ($user_2->name ?: '-') . '</td>
                            <td>' . ($user_1->name ?: '-') . '</td>
                            <td>' . ($user_0 ?: '-') . '</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>';

        $html .= '
            </div>
        </body>
        </html>';

        // CETAK HTML
        echo $html;
    }
}
