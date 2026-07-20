<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Production_schedules extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->library('Ciqrcode');
        $this->load->model('crud');
        //Validasi Form
        $this->form_validation->set_rules('item_fg_id', 'Product No', 'required|min_length[1]|max_length[50]');
    }
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/production_schedules');
        } else {
            redirect('error_access');
        }
    }
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('production_schedules', ["name" => $post]);
        echo json_encode($send);
    }
    public function readPeriodAll()
    {
        $send = $this->crud->query("SELECT DISTINCT `period` FROM production_schedules ORDER BY `period` DESC");
        echo json_encode($send);
    }
    public function readWpAll()
    {
        $period = base64_decode($this->input->get('period'));
        $send = $this->crud->query("SELECT DISTINCT a.wo_no, b.id as item_fg_id, b.number as item_fg_number 
        FROM production_schedules a 
        JOIN item_fg b on a.item_fg_id = b.id 
        WHERE a.period = '$period' ORDER BY a.wo_no DESC");
        echo json_encode($send);
    }
    public function readPeriod()
    {
        $send = $this->crud->query("SELECT DISTINCT `period` FROM production_schedules WHERE `status` = 0 ORDER BY `period` DESC");
        echo json_encode($send);
    }

    public function readWp()
    {
        $period = base64_decode($this->input->get('period'));
        $send = $this->crud->query("SELECT DISTINCT wp FROM production_schedules WHERE `status` = 0 and `period` = '$period' ORDER BY `wp` DESC");
        echo json_encode($send);
    }

    public function readWorkorder()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $send = $this->crud->query("SELECT DISTINCT workorder, so_number FROM production_schedules WHERE `status` = 0 and `period` = '$period' and wp = '$wp' ORDER BY `wp` DESC");
        echo json_encode($send);
    }

    public function readWoNo()
    {
        $send = $this->crud->query("SELECT wo_no FROM production_schedules WHERE `deleted` = '0' ORDER BY `wo_no` DESC");
        echo json_encode($send);
    }
    

    public function readMachine()
    {
        $send = $this->crud->query("SELECT DISTINCT b.id as machine_id, b.number as machine_number 
        FROM production_schedules a
        JOIN machines b on a.machine_id = b.id
        WHERE a.status = 0 
        ORDER BY b.number DESC");
        echo json_encode($send);
    }

    public function readMold()
    {
        $send = $this->crud->query("SELECT b.id as mold_id, b.mold_name 
        FROM production_schedules a
        JOIN molds b on a.mold_id = b.id
        WHERE a.status = 0 
        ORDER BY b.mold_name DESC");
        echo json_encode($send);
    }

    public function readCustomer()
    {
        $period = base64_decode($this->input->get('period'));
        $wp = base64_decode($this->input->get('wp'));
        $workorder = base64_decode($this->input->get('workorder'));

        $send = $this->crud->query("SELECT a.customer_id, b.number as customer_number, b.name as customer_name 
            FROM production_schedules a
            JOIN customers b on a.customer_id = b.id
            WHERE a.status = 0 and a.period = '$period' and a.wp = '$wp' and a.workorder = '$workorder' ORDER BY a.workorder DESC");
        echo json_encode($send);
    }

    public function readItems($division)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT *  
        FROM item_fg 
        WHERE (number LIKE '%$post%' OR name LIKE '%$post%') 
        AND division_id = '$division'
        ORDER BY number ASC");
        echo json_encode($send);
    }

    public function readMachines()
    {
        $send = $this->crud->query("SELECT *
            FROM machines
            WHERE `status` = '0'");
        echo json_encode($send);
    }

    public function readPurging($machine, $colors)
    {
        $machines = base64_decode($machine);

         // Cek nilai $colors dan ganti menjadi 'COLORFULL' jika tidak BLACK atau CLEAR
            if ($colors == 'BLACK' || $colors == 'FR BLACK P B B') {
                $colors = 'BLACK';
            }elseif($colors == 'WHITE' || $colors == 'CLEAR WHITE' || $colors == 'BRIGHT WHITE' || $colors == 'DIFFUSE WHITE'){
                $colors = 'CLEAR';
            }else{
                $colors = 'COLORFULL';
            }

        $send = $this->crud->query("SELECT DISTINCT total 
            FROM purgings 
            WHERE machine_id = '$machines' and kind = '$colors'");
        echo json_encode($send);
    }

    public function readMonth()
    {
        $months = array(
            '01' => 'January', '02' => 'February', '03' => 'March', 
            '04' => 'April', '05' => 'May', '06' => 'June', 
            '07' => 'July', '08' => 'August', '09' => 'September', 
            '10' => 'October', '11' => 'November', '12' => 'December'
        );

        $arr = []; // Inisialisasi awal
        foreach ($months as $key => $value) {
            $arr[] = array("number" => $key, "name" => $value);
        }

        echo json_encode($arr);
        exit;
    }

    public function readYear()
    {
        $tahun_before = date('Y', strtotime('-5 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));

        $arr = []; // Inisialisasi awal
        for ($i = $tahun_before; $i <= $tahun_next; $i++) {
            $arr[] = array("number" => "$i");
        }

        echo json_encode($arr);
        exit;
    }

    public function closePs()
    {
        $id = $this->input->post('id');
        $update = $this->db->update('production_schedules', ["status" => 2, "remarks" =>'Closing PS'], ["id" => $id]);// , "qty" => 0
        // echo $update;

         // Berikan respon sesuai hasil
        if ($update) {
            echo json_encode(["success" => true,"message" => "Production schedule complete successfully."]);
        } else {
            echo json_encode(["success" => false,"message" => "Failed to complete production schedule."]);
        }
    }

    public function openPs()
    {
        // $po_no = $this->input->post('po_no');
        $id = $this->input->post('id');
        $update = $this->db->update('production_schedules', ["status" => 0,"remarks" =>null], ["id" => $id]);// , "qty" => 0

        if ($update) {
            echo json_encode(["success" => true,"message" => "Production schedule open successfully."]);
        } else {
            echo json_encode(["success" => false,"message" => "Failed to open production schedule."]);
        }
    }

    public function workorder($wp, $trans_date)
    {
        // $production_schedule = $this->crud->read("production_schedules", [], ["wp" => $wp]);
        // if ($production_schedule) {
        //     return $production_schedule->workorder;
        // } else {
        $datenow = date("ymd", strtotime($trans_date));
        $sqlGetID = $this->db->query("SELECT max(workorder) as kode FROM production_schedules WHERE workorder like '%$datenow%'");
        $rowID = $sqlGetID->row();
        $kode = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%05s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%05s", $urutan);
        }
        $workOrderNo = "WO" . $datenow . "-" . $autoID;
        return $workOrderNo;
        //}
    }

    public function getById($id) {
        $decoded_id = base64_decode($id);
        $query = $this->db->query("SELECT * FROM production_schedules WHERE id = ?", array($decoded_id));
        $data = $query->row_array();

        echo json_encode($data);
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_month = $this->input->get('filter_month');
            $filter_year = $this->input->get('filter_year');
            $filter_wo_no = $this->input->get('filter_wo_no');
            $filter_machine_id = $this->input->get('filter_machine_id');
            $filter_mold_id = $this->input->get('filter_mold_id');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');
            $filter_status = $this->input->get('filter_status');
            $filter_division = $this->input->get('filter_division');
            $filter_status_subcont = $this->input->get('filter_status_subcont');
            $filter_subcont_type = $this->input->get('filter_subcont_type');
            $filter_supply_sheet = $this->input->get('filter_supply_sheet');
            $filter_status_supply = $this->input->get('filter_status_supply');
            $filter_type = $this->input->get('filter_type');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, 
            c.number as item_number, 
            c.name as item_name, 
            c.uom, 
            c.type,
            e.number as machine_number,
            (CASE 
                WHEN g.workorder IS NOT NULL THEN g.max_status
                ELSE 0
            END) as status_supply,
            (CASE
                WHEN g.max_status = 2 THEN 2
                WHEN g.max_status IS NOT NULL THEN 1
                ELSE 0
            END) AS supply_sheet_status,
            (COALESCE(h.qty_checksheet,0)) as os_qty,
            (a.qty - (COALESCE(h.qty_checksheet,0))) as os_wo");
            $this->db->from('production_schedules a');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            $this->db->join('mold_items d', 'c.id = d.item_fg_id','left');
            $this->db->join('machines e', 'a.machine_id = e.id','left');
            $this->db->join('molds f', 'd.mold_id = f.id','left');
            $this->db->join("(SELECT workorder, MAX(status) AS max_status FROM supply_sheets GROUP BY workorder) g", "a.wo_no = g.workorder","left");
            $this->db->join("(SELECT wo_no, SUM(receipt) as qty_checksheet FROM checksheets GROUP BY wo_no) h", "a.wo_no = h.wo_no", "left");
            $this->db->where('a.deleted', 0);

            if ($filter_year != "") {
                $this->db->where('a.year', $filter_year);
            }

            if ($filter_month != "") {
                $this->db->where('a.month', $filter_month);
            }

            if ($filter_item_fg_id != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }

            if ($filter_machine_id != "") {
                $this->db->where('a.machine_id', $filter_machine_id);
            }

            if ($filter_wo_no != "") {
                $this->db->where('a.wo_no', $filter_wo_no);
            }

            if ($filter_status != "") {
                $this->db->where('a.status', $filter_status);
            }

            if ($filter_division != "") {
                $this->db->where('a.division', $filter_division);
            }

            if ($filter_status_subcont != "") {
                $this->db->where('a.status_subcont', $filter_status_subcont);
            }

            if ($filter_subcont_type != "") {
                $this->db->where('a.subcont_type', $filter_subcont_type);
            }

            if ($filter_supply_sheet != "") {
                $this->db->having('supply_sheet_status', $filter_supply_sheet);
            }

            if ($filter_status_supply != "") {
                $this->db->having('status_supply', $filter_status_supply);
            }

            if ($filter_type != "") {
                $this->db->having('c.type', $filter_type);
            }

            // $this->db->like('a.month', $filter_month);
            // $this->db->like('a.year', $filter_year);
            // $this->db->like('a.item_fg_id', $filter_item_fg_id);
            // $this->db->like('a.machine_id', $filter_machine_id);
            //$this->db->like('a.wo_no', $filter_wo_no);
            $this->db->group_by('a.wo_no');
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->order_by('a.wo_no', 'DESC');
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

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         if ($this->form_validation->run() == TRUE) {
    //             $post = $this->input->post();
    //             // $workorder = $this->workorder($post['wp'], $post['trans_date']);
    //             //$production_schedules = $this->crud->read('production_schedules', [], ["customer_id" => $post['customer_id'], "item_fg_id" => $post['item_fg_id'], "wp" => $post['wp'], "trans_date" => $post['trans_date']]);
    //             // $sales_orders = $this->crud->query("SELECT 
    //             //     a.item_fg_id, b.number as item_number, 
    //             //     b.name as item_name, 
    //             //     (a.qty - coalesce(SUM(c.qty), 0)) as qty
    //             // FROM sales_orders a 
    //             // JOIN item_fg b on a.item_fg_id = b.id
    //             // LEFT JOIN production_schedules c ON a.sales_order_no = c.so_number and a.item_fg_id = c.item_fg_id
    //             // WHERE a.sales_order_no = '$post[so_number]' and a.item_fg_id = '$post[item_fg_id]'
    //             // GROUP BY a.item_fg_id");

    //             // if (@$production_schedules->id) {
    //             //     show_error("Duplicate Data");
    //             // } elseif ($post['qty'] > $sales_orders[0]->qty) {
    //             //     show_error("qty is bigger than sales order " . $post['qty'] . ">" . $sales_orders[0]->qty);
    //             // } else {
    //             //     $postFinal = array_merge($post, array("workorder" => $workorder, "period" => $post['year'] . $post['month']));
    //                 $send = $this->crud->create('production_schedules', $post);
    //                 if ($post['qty'] == $sales_orders[0]->qty) {
    //                     $update = $this->crud->update('sales_orders', ["sales_order_no" => $post['so_number'], "item_fg_id" => $post['item_fg_id']], ["status" => 1]);
    //                 }
    //             }
    //             echo $send;
    //         } else {
    //             show_error(validation_errors());
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            if (isset($post['wo_no_assembly']) && $post['wo_no_assembly'] === '') {
                $post['wo_no_assembly'] = null;
            }

            $postFinal = array_merge($post, array("period" => $post['year'] . $post['month']));

            $send = $this->crud->create('production_schedules', $postFinal);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    
    public function update()
    {
        if ($this->input->post()) {
            $id   = base64_decode($this->input->get('id'));
            $post = $this->input->post();
            if (isset($post['wo_no_assembly']) && $post['wo_no_assembly'] === '') {
                $post['wo_no_assembly'] = null;
            }
            $send = $this->crud->update('production_schedules', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    // public function delete()
    // {
    //     $data = $this->input->post();
    //     $send = $this->crud->delete('production_schedules', ["id" => $data['id']]);
    //     // $update = $this->crud->update('sales_orders', ["number" => $data['so_number'], "item_fg_id" => $data['item_fg_id']], ["status" => 0]);
    //     echo $send;
    // }

    public function delete()// dokumentasi : validasi untuk mencegah wo no terhapus user jika sudah ada proses
    {
        $id = $this->input->post('id');
        $schedule = $this->db->get_where('production_schedules', ['id' => $id])->row();
        
        if ($schedule) {
            $wo_no = $schedule->wo_no;
            $this->db->where('wo_no', $wo_no);
            $check = $this->db->get('checksheets')->num_rows();

            if ($check > 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => "WO No $wo_no has been processed cannot be deleted!"
                ]);
            } else {
                $send = $this->crud->delete('production_schedules', ["id" => $id]);
                echo json_encode(['success' => true]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => "Data not found!"]);
        }
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
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'wo_no' => $data->val($i, 2),
                'period' => $data->val($i, 3),
                'machine_no' => $data->val($i, 4),
                'lot_no' => $data->val($i, 5),
                'mold_id' => $data->val($i, 6),
                'trans_date' => $data->val($i, 7),
                'division' => $data->val($i, 8),
                'item_fg_number' => $data->val($i, 9),
                'qty' => $data->val($i, 10)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }
    public function uploadclearFailed()
    {
        @unlink('failed/production_schedules.txt');
    }
    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/production_schedules.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }
    //UPLOAD DOWNLOAD FAILED
    public function uploadDownloadFailed()
    {
        $file = "failed/production_schedules.txt";
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

            //Cek Process Number          //table       //field        //field excel
            $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_number']]);
            $machine = $this->crud->read('machines', [], ["number" => $data['machine_no']]);
            $ps = $this->crud->read('production_schedules', [], ["wo_no" => $data['wo_no']]);
            $period = $data['period'];
            $year = substr($period, 0, 4);
            $month = substr($period, 4, 2);

            if (empty($item_fg->id)) {
                echo json_encode(array("title" => "Not Found", "message" => "Item Finish Good " . $data['item_fg_number'] . " Not Found", "theme" => "error"));
            } elseif (empty($machine->number)) {
                echo json_encode(array("title" => "Not Found", "message" => "Machine " . $data['machine_no'] . " Not Found", "theme" => "error"));
            } elseif (!empty($ps->wo_no)) {
                echo json_encode(array("title" => "Duplicated", "message" => "Wo No  " . $data['wo_no'] . " Duplicated", "theme" => "error"));
            } else {

            $total_purgings = 0;

            $colors = $item_fg->color;
            if ($colors == 'BLACK' || $colors == 'FR BLACK P B B') {
                $colors = 'BLACK';
            }elseif($colors == 'WHITE' || $colors == 'CLEAR WHITE' || $colors == 'BRIGHT WHITE' || $colors == 'DIFFUSE WHITE'){
                $colors = 'CLEAR';
            }else{
                $colors = 'COLORFULL';
            }
            
            $machines = $machine->id;
            $purging = $this->crud->read('purgings', [], ["machine_id" => $machines, "kind" => $colors]);
            if ($purging) {
                $total_purgings = $purging->total !== null ? $purging->total : 0;
            }
    
                $dataFinal = array(
                    //field
                    "wo_no" => $data['wo_no'],
                    "period" => $data['period'],
                    "year" => $year,
                    "month" => $month,
                    "machine_id" => $machine->id,
                    "lot_no" => $data['lot_no'],
                    "mold_id" => $data['mold_id'],
                    "trans_date" => $data['trans_date'],
                    "item_fg_id" => $item_fg->id,
                    "item_fg_name" => $item_fg->name,
                    "color" => $colors,
                    "total_purging" => $total_purgings,
                    "division" => $data['division'],
                    "qty" => $data['qty'],
                    "status_subcont" => $item_fg->status_subcont,
                    "subcont_type" => $item_fg->subcont_type,
                );
                $send   = $this->crud->create('production_schedules', $dataFinal);
                echo $send;
            }
        }
    }

    // public function print_job_order($id)
    // {
    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();
    //     $config_iso = $this->db->get('config_iso')->row();

    //     $this->db->select('a.*, b.lot');
    //     $this->db->from('production_schedules a');
    //     $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //     $this->db->where('a.deleted', 0);
    //     $this->db->where('a.id', $id);
    //     $label = $this->db->get()->row();
    //     $amountQty = ceil($label->qty / $label->lot);
    //     for ($i = 1; $i <= $amountQty; $i++) {
    //         $lots = sprintf("%03s", $i);
    //         $this->db->select('b.circuit');
    //         $this->db->from('production_schedules a');
    //         $this->db->join('job_orders b', 'a.item_fg_id = b.item_fg_id and a.customer_id and b.customer_id', 'left');
    //         $this->db->where('a.deleted', 0);
    //         $this->db->where('a.id', $id);
    //         $this->db->order_by('b.circuit', 'asc');
    //         $totalRows = $this->db->count_all_results('', false);
    //         $job_orders = $this->db->get()->result_array();
    //         $no = 1;
    //         $qty = $label->qty;
    //         foreach ($job_orders as $job_order) {
    //             $sequence = sprintf("%03s", $no);
    //             $label_no = $label->workorder . $lots . $sequence;
    //             if ($no == $totalRows) {
    //                 $finalQty = $qty;
    //             } else {
    //                 $finalQty = $label->lot;
    //             }
    //             $dataJobOrderLabel = array(
    //                 "workorder" => $label->workorder,
    //                 "label_no" => $label_no,
    //                 "circuit" => $job_order['circuit'],
    //                 "qty" => $finalQty,
    //             );
    //             $jobOrderLabel = $this->crud->read("job_order_labels", [], ["label_no" => $label_no]);
    //             if (empty($jobOrderLabel->id)) {
    //                 $this->crud->create("job_order_labels", $dataJobOrderLabel);
    //             }
    //             $qty -= $label->lot;
    //             $no++;
    //         }
    //     }
    //     $this->db->select('b.*, a.so_number, a.workorder, a.so_date, a.trans_date, a.qty, c.label_no, c.circuit, d.number as item_number, d.lot');
    //     $this->db->from('production_schedules a');
    //     $this->db->join('job_order_labels c', 'a.workorder = c.workorder');
    //     $this->db->join('job_orders b', 'a.item_fg_id = b.item_fg_id and a.customer_id and b.customer_id and c.circuit = b.circuit', 'left');
    //     $this->db->join('item_fg d', 'a.item_fg_id = d.id');
    //     $this->db->where('a.deleted', 0);
    //     $this->db->where('a.id', $id);
    //     $this->db->group_by('c.circuit');
    //     $this->db->group_by('c.label_no');
    //     $this->db->order_by('c.label_no', 'asc');
    //     $records = $this->db->get()->result_object();
    //     if ($records) {
    //         $html = '<html>
    //                 <head>
    //                     <title>' . $label->workorder . '</title>
    //                     <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
    //                 </head>
    //                 <style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 20cm;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
    //         foreach ($records as $record) {
    //             //Generate QRcode
    //             $this->createQrcode($record->label_no, "assets/image/qrcode/");
    //             $html .= '  <table id="customers" border="1" style="margin-bottom:20px;">
    //                             <tr>
    //                                 <th colspan="4" style="font-size:16px; padding:10px; text-align:center;"><b>JOB ORDER ' . $config->name . '</b></th>
    //                                 <th width="150">
    //                                     <table style="width:100%; font-size:10px; border:0;">
    //                                         <tr style="border:0;">
    //                                             <td width="60">Doc No</td>
    //                                             <td width="100">' . $config_iso->doc_job_order . '</td>
    //                                         </tr>
    //                                         <tr style="border:0;">
    //                                             <td>Form</td>
    //                                             <td>' . $config_iso->form_job_order . '</td>
    //                                         </tr>
    //                                     </table>
    //                                 </th>
    //                             </tr>
    //                             <tr>
    //                                 <th style="text-align:center;">MODEL</th>
    //                                 <th style="text-align:center;">PLAN QTY</th>
    //                                 <th style="text-align:center;">LOT</th>
    //                                 <th style="text-align:center;">START DATE</th>
    //                                 <th style="text-align:center;">ISSUE DATE</th>
    //                             </tr>
    //                             <tr>
    //                                 <td style="text-align:center;">' . $record->item_number . '</td>
    //                                 <td style="text-align:center;">' . $record->qty . '</td>
    //                                 <td style="text-align:center;">' . $record->lot . '</td>
    //                                 <td style="text-align:center;">' . $record->trans_date . '</td>
    //                                 <td style="text-align:center;">' . $record->so_date . '</td>
    //                             </tr>
    //                             <tr>
    //                                 <th style="text-align:center;">WIRE CODE</th>
    //                                 <th style="text-align:center;">TYPE & SIZE</th>
    //                                 <th style="text-align:center;">COLOR</th>
    //                                 <th style="text-align:center;">LENGTH</th>
    //                                 <th style="text-align:center;">M/C NO</th>
    //                             </tr>
    //                             <tr>
    //                                 <td style="text-align:center;">' . $record->wire . '</td>
    //                                 <td style="text-align:center;">' . $record->type . ' ' . $record->size . '</td>
    //                                 <td style="text-align:center;">' . $record->color . '</td>
    //                                 <td style="text-align:center;">' . $record->length . '</td>
    //                                 <td style="text-align:center;"></td>
    //                             </tr>
    //                             <tr>
    //                                 <th style="text-align:center;">TERMINAL SIDE A</th>
    //                                 <th style="text-align:center;">TERMINAL SIDE B</th>
    //                                 <th colspan="3" style="text-align:center;">WO. No</th>
    //                             </tr>
    //                             <tr>
    //                                 <td style="text-align:center; height:20px;">' . $record->a_terminal . '</td>
    //                                 <td style="text-align:center; height:20px;">' . $record->b_terminal . '</td>
    //                                 <td rowspan="7" colspan="3" style="text-align:center;">' . $record->workorder . '</td>
    //                             </tr>
    //                             <tr>
    //                                 <td style="text-align:center; height:20px;">' . $record->a_seal . '</td>
    //                                 <td style="text-align:center; height:20px;">' . $record->b_seal . '</td>
    //                             </tr>
    //                             <tr>
    //                                 <td style="text-align:center; height:20px;">' . $record->a_chi . '</td>
    //                                 <td style="text-align:center; height:20px;">' . $record->b_chi . '</td>
    //                             </tr>
    //                             <tr>
    //                                 <td style="text-align:center; height:20px;">' . $record->a_chc . '</td>
    //                                 <td style="text-align:center; height:20px;">' . $record->b_chc . '</td>
    //                             </tr>
    //                             <tr>
    //                                 <td style="text-align:center; height:20px;">' . $record->a_stripping . '</td>
    //                                 <td style="text-align:center; height:20px;">' . $record->b_stripping . '</td>
    //                             </tr>
    //                             <tr>
    //                                 <td style="text-align:center; height:20px;">' . $record->a_process . '</td>
    //                                 <td style="text-align:center; height:20px;">' . $record->b_process . '</td>
    //                             </tr>
    //                             <tr>
    //                                 <td style="text-align:center; height:20px;">' . $record->a_note . '</td>
    //                                 <td style="text-align:center; height:20px;">' . $record->b_note . '</td>
    //                             </tr>
    //                             <tr>
    //                                 <th style="text-align:center;">CIRCUIT NO</th>
    //                                 <th style="text-align:center;">SERIAL NO</th>
    //                                 <th style="text-align:center;">OPERATOR</th>
    //                                 <th style="text-align:center;">CHECK BY</th>
    //                                 <th style="text-align:center;">INSPECT BY</th>
    //                             </tr>
    //                             <tr>
    //                                 <th rowspan="3" style="text-align:center; height:50px; font-size:40px;">' . $record->circuit . '</th>
    //                                 <td rowspan="3" style="text-align:center; height:50px;">
    //                                     <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="80"/>
    //                                     <br>
    //                                     <span>' . $record->label_no . '</span>
    //                                 </td>
    //                                 <th style="text-align:center; height:80px;"></th>
    //                                 <th style="text-align:center; height:80px;"></th>
    //                                 <th style="text-align:center; height:80px;"></th>
    //                             </tr>
    //                             <tr>
    //                                 <th style="text-align:left;">Name :</th>
    //                                 <th style="text-align:left;">Name :</th>
    //                                 <th style="text-align:left;">Name :</th>
    //                             </tr>
    //                             <tr>
    //                                 <th style="text-align:left;">Date :</th>
    //                                 <th style="text-align:left;">Date :</th>
    //                                 <th style="text-align:left;">Date :</th>
    //                             </tr>
    //                         </table>';
    //         }
    //         $html .= "<script>window.print()</script>";
    //         die($html);
    //     } else {
    //         echo "<h1>NOT FOUND JOB ORDER</h1>";
    //     }
    // }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=production_schedules_$format.xls");
        }
        $filter_month = $this->input->get('filter_month');
        $filter_year = $this->input->get('filter_year');
        $filter_wo_no = $this->input->get('filter_wo_no');
        $filter_machine_id = $this->input->get('filter_machine_id');
        $filter_mold_id = $this->input->get('filter_mold_id');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');
        $filter_status = $this->input->get('filter_status');
        $filter_division = $this->input->get('filter_division');
        $filter_status_subcont = $this->input->get('filter_status_subcont');
        $filter_subcont_type = $this->input->get('filter_subcont_type');
        $filter_supply_sheet = $this->input->get('filter_supply_sheet');
        $filter_status_supply = $this->input->get('filter_status_supply');
        $filter_type = $this->input->get('filter_type');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, 
        c.number as item_number, 
        c.name as item_name, 
        c.uom, 
        c.type,
        e.number as machine_number,
        (CASE 
            WHEN g.workorder IS NOT NULL THEN g.max_status
            ELSE 0
        END) as status_supply,
        (CASE
            WHEN g.max_status = 2 THEN 2
            WHEN g.max_status IS NOT NULL THEN 1
            ELSE 0
        END) AS supply_sheet_status,
        (COALESCE(h.qty_checksheet,0)) as os_qty,
        (a.qty - (COALESCE(h.qty_checksheet,0))) as os_wo");;
        $this->db->from('production_schedules a');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join('mold_items d', 'c.id = d.item_fg_id','left');
        $this->db->join('machines e', 'a.machine_id = e.id');
        $this->db->join('molds f', 'd.mold_id = f.id','left');
        $this->db->join("(SELECT workorder, MAX(status) AS max_status FROM supply_sheets GROUP BY workorder) g", "a.wo_no = g.workorder","left");
        $this->db->join("(SELECT wo_no, SUM(receipt) as qty_checksheet FROM checksheets GROUP BY wo_no) h", "a.wo_no = h.wo_no", "left");
        $this->db->where('a.deleted', 0);

        if ($filter_year != "") {
            $this->db->where('a.year', $filter_year);
        }

        if ($filter_month != "") {
            $this->db->where('a.month', $filter_month);
        }

        if ($filter_item_fg_id != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg_id);
        }

        if ($filter_machine_id != "") {
            $this->db->where('a.machine_id', $filter_machine_id);
        }

        if ($filter_wo_no != "") {
            $this->db->where('a.wo_no', $filter_wo_no);
        }

        if ($filter_status != "") {
            $this->db->where('a.status', $filter_status);
        }

        if ($filter_division != "") {
            $this->db->where('a.division', $filter_division);
        }

         if ($filter_status_subcont != "") {
                $this->db->where('a.status_subcont', $filter_status_subcont);
        }

        if ($filter_subcont_type != "") {
            $this->db->where('a.subcont_type', $filter_subcont_type);
        }

        if ($filter_supply_sheet != "") {
            $this->db->having('supply_sheet_status', $filter_supply_sheet);
        }

        if ($filter_status_supply != "") {
            $this->db->having('status_supply', $filter_status_supply);
        }

        if ($filter_type != "") {
            $this->db->having('c.type', $filter_type);
        }

        // $this->db->like('a.month', $filter_month);
        // $this->db->like('a.year', $filter_year);
        // $this->db->like('a.item_fg_id', $filter_item_fg_id);
        // $this->db->like('a.wo_no', $filter_wo_no);
        // $this->db->like('a.machine_id', $filter_machine_id);
        // $this->db->like('a.mold_id', $filter_mold_id);
        $this->db->group_by('a.wo_no');
        $this->db->order_by('a.trans_date', 'DESC');
        $this->db->order_by('c.number', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
            <center>
                <div style="float: left; font-size: 12px; text-align: left;">
                    <table style="width: 100%;">
                        <tr>
                            <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                                <img src="' . $config->favicon . '" width="30">
                            </td>
                            <td style="font-size: 14px; text-align: left; margin:2px;">
                                <b>' . $config->name . '</b><br>
                                <small>PRODUCTION SCHEDULE</small>
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
                    <th>No WO</th>
                    <th>Period</th>
                    <th>Machine ID</th>
                    <th>Lot No</th>
                    <th>WO Date</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Qty Checksheet</th>
                    <th>OS WO</th>
                    <th>Status Subcont</th>
                    <th>Subcont Type</th>
                    <th>Supply Sheet</th>
                    <th>Status Supply</th>
                    <th>Type</th>
                    <th>Meta Data</th>
                    <th>Status</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {

            if($data['status'] == 0){
                $status = "OPEN";
            }else{
                $status = "CLOSED";
            }

            if($data['supply_sheet_status'] == 0){
                $supply_sheet_status = "OPEN";
            }elseif($data['supply_sheet_status'] == 1){
                $supply_sheet_status = "CLOSED";
            }else{
                $supply_sheet_status = "COMPLETED";
            }

            if($data['status_supply'] == 0){
                $status_supply = "OPEN";
            }elseif($data['status_supply'] == 1){
                $status_supply = "CLOSED";
            }else{
                $status_supply = "COMPLETED";
            }

            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['wo_no'] . '</td>
                            <td>' . $data['period'] . '</td>
                            <td>' . $data['machine_number'] . '</td>
                            <td>' . $data['lot_no'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_number'] . '</td>
                            <td style="mso-number-format:\@;">' . $data['item_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . number_format($data['qty'], 2) . '</td>
                            <td>' . number_format($data['os_qty'], 2) . '</td>
                            <td>' . number_format($data['os_wo'], 2) . '</td>
                            <td>' . $data['status_subcont'] . '</td>
                            <td>' . $data['subcont_type'] . '</td>
                            <td>' . $supply_sheet_status . '</td>
                            <td>' . $status_supply . '</td>
                            <td>' . $data['type'] . '</td>
                            <td>' . $data['meta_data'] . '</td>
                            <td>' . $status . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    // public function print_wo($id)
    // {
    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();
    //     $config_iso = $this->db->get('config_iso')->row();

    //     $this->db->select("a.*, 
    //         c.number as item_number, 
    //         c.name as item_name, 
    //         c.uom, 
    //         c.is_no, 
    //         e.number as machine_number, 
    //         f.mold_name, 
    //         f.cavity_standard, 
    //         f.cavity_actual, 
    //         f.id as mold_id, 
    //         h.cycle_time, 
    //         h.cycle_time_process, 
    //         h.manpower");
    //     $this->db->from('production_schedules a');
    //     $this->db->join('item_fg c', 'a.item_fg_id = c.id', 'left');
    //     $this->db->join('mold_items d', 'c.id = d.item_fg_id', 'left');
    //     $this->db->join('machines e', 'a.machine_id = e.id', 'left');
    //     $this->db->join('molds f', 'd.mold_id = f.id', 'left');
    //     $this->db->join('scan_item_receipts_fg g', 'a.so_number = g.so_number and a.wo_no = g.workorder', 'left');
    //     $this->db->join('menu_loadings h', 'c.id = h.item_fg_id','left');
    //     $this->db->join('bom i', 'a.item_fg_id = i.item_fg_id','left');
    //     $this->db->where('a.deleted', 0);
    //     $this->db->where('a.id', $id);
    //     $this->db->group_by('a.wo_no');
    //     $records = $this->db->get()->result_array();

    //     $html = '';
    //     // Cek apakah ada data
    //     if (!empty($records)) {
    //         foreach ($records as $data) {
    //             // Query tambahan untuk mengambil item_rm_id dari tabel bom
    //             $this->db->select('b.number as item_number, a.composition, b.uom');
    //             $this->db->from('bom a');
    //             $this->db->join('item_rm b', 'a.item_rm_id = b.id');
    //             $this->db->where('item_fg_id', $data['item_fg_id']);
    //             $bom_items = $this->db->get()->result_array();

    //             $shift_hour = !empty($data['shift_hour']) ? $data['shift_hour'] : 0;
    //             $cycle_time = !empty($data['cycle_time']) && $data['cycle_time'] > 0 ? $data['cycle_time'] : 1;
    //             $cavity_std = !empty($data['cavity_standard']) ? $data['cavity_standard'] : 0;
    //             $shift_qty  = !empty($data['shift']) ? $data['shift'] : 0;
    //             $prod_rate  = isset($data['productcivity']) ? ($data['productcivity'] / 100) : 1; 

    //             $cap_day = ((3600 * $shift_hour) / $cycle_time) * $cavity_std * $shift_qty * $prod_rate;
    //             $target_per_shift = ceil($cap_day / 3);

    //             // Gabungkan item_rm_id menjadi satu string
    //             $material_list = '';
    //             $qty = '';
    //             $uom = '';
    //             foreach ($bom_items as $item) {
    //                 $material_list .= $item['item_number'] . '<br>';
    //                 $qty .= number_format($item['composition'] * $data['qty']) . '<br>';
    //                 $uom .= $item['uom'] . '<br>';
    //             }

    //             $html = '<html><head><title>'.$data['item_number'].'</title></head>
    //                 <script>
    //                     window.onload = function() {
    //                         window.print();
    //                     };
    //                 </script>
    //                 <style>
    //                     body {font-family: Arial, Helvetica, sans-serif;}
    //                     .bordered-table1 {width: 80%; border-collapse: collapse; margin: auto;}
    //                     .bordered-table1 td {border: 1px solid #000; padding: 5px; text-align: left;}
    //                     .bordered-table2 {width: 50%; border-collapse: collapse; margin: auto;}
    //                     .bordered-table2 td {border: 1px solid #000; padding: 5px; text-align: left; height: 50px; font-size: 25px;}
    //                     .bordered-table3 {width: 100%; border-collapse: collapse; margin: auto;}
    //                     .bordered-table3 td {border: 1px solid #000; padding: 5px; text-align: left;}
    //                     .no-border-table {width: 100%; border-collapse: collapse;}
    //                     .no-border-table td {border: none; padding: 5px; text-align: left;}
    //                     .header-table {width: 100%; margin-bottom: 10px; border-collapse: collapse;}
    //                     .header-table td {border: none; padding: 5px; text-align: left; vertical-align: top;}

    //                     .bordered-table {border-collapse: collapse;width: 30%;margin: 10px;}
    //                     .bordered-table td, .bordered-table th {border: 1px solid #000;padding: 5px;text-align: center;}
    //                     .signature-section {width: 40%;height: 30px;}
    //                     .signature-section2 {width: 40%;height: 100px;}
    //                     .signature-header {font-weight: bold;}
    //                     .left-table {float: left;}
    //                     .right-table {float: right;}

    //                     .content-table {width: 100%; margin-top: 20px;}
    //                     .content-table td {padding: 5px; vertical-align: top;}
    //                     .right-align {text-align: right;}
    //                 </style>

    //                 <body>
    //                     <center>
    //                         <table class="header-table">
    //                             <tr>
    //                                 <td style="font-size: 40px; text-align: center;">
    //                                     WORK ORDER (WO) & <br>PRODUCTION REPORT (PR)
    //                                 </td>
    //                             </tr>
    //                         </table>
    //                     </center>
    //                     <br>
    //                     <table class="bordered-table1">
    //                         <tr>
    //                             <td style="width: 30%; text-align: left;">Issued Date</td>
    //                             <td style="width: 70%; text-align: center;">'.$data['trans_date'].'</td>
    //                         </tr>
    //                         <tr>
    //                             <td style="width: 30%; text-align: left;">No Doc</td>
    //                             <td style="width: 70%; text-align: center;">'.$data['wo_no'].'</td>
    //                         </tr>
    //                         <tr>
    //                             <td style="width: 30%; text-align: left;">Part No</td>
    //                             <td style="width: 70%; text-align: center;">'.$data['item_number'].'</td>
    //                         </tr>
    //                         <tr>
    //                             <td style="width: 30%; text-align: left;">Part Name</td>
    //                             <td style="width: 70%; text-align: center;">'.$data['item_name'].'</td>
    //                         </tr>
    //                         <tr>
    //                             <td style="width: 30%; text-align: left;">IS No</td>
    //                             <td style="width: 70%; text-align: center;">'.$data['is_no'].'</td>
    //                         </tr>
    //                         <tr>
    //                             <td style="width: 30%; text-align: left;">Mold No</td>
    //                             <td style="width: 70%; text-align: center;">'.$data['mold_id'].'</td>
    //                         </tr>
    //                         <tr>
    //                             <td style="width: 30%; text-align: left;">Machine No</td>
    //                             <td style="width: 70%; text-align: center;">'.$data['machine_number'].'</td>
    //                         </tr>
    //                     </table>
    //                     <br>
    //                     <table class="bordered-table2">
    //                         <tr>
    //                             <td style="width: 50%; text-align: left;">Lot No</td>
    //                             <td style="width: 50%; text-align: center;">'.$data['lot_no'].'</td>
    //                         </tr>
    //                         <tr>
    //                             <td style="width: 50%; text-align: left;">Qty (Pcs)</td>
    //                             <td style="width: 50%; text-align: center;">'.$data['qty'].'</td>
    //                         </tr>
    //                         <tr>
    //                             <td style="width: 50%; text-align: left;">Lead Time (Hour)</td>
    //                             <td style="width: 50%; text-align: center;">'.number_format(($data['qty']) / (3600 / ($data['cycle_time'] + $data['cycle_time_process']) * $data['cavity_standard'] * 0.85), 2).'</td>
    //                         </tr>
    //                     </table>
    //                     <br>
    //                     <table class="bordered-table1">
    //                         <tr>
    //                             <td style="width: 30%; text-align: left;">Polybag Label</td>
    //                             <td style="width: 70%; text-align: center;">Tidak Pakai Label Manual</td>
    //                         </tr>
    //                         <tr>
    //                             <td style="width: 30%; text-align: left;">Box Label</td>
    //                             <td style="width: 70%; text-align: center;">Tidak Pakai Label Manual</td>
    //                         </tr>
    //                     </table>
    //                     <br>
    //                     <table class="bordered-table1">
    //                         <tr>
    //                             <td style="width: 50%; text-align: center;"><b>Cycle Time</b></td>
    //                             <td style="width: 50%; text-align: center;"><b>Man Power</b></td>
    //                         </tr>
    //                         <tr>
    //                             <td style="vertical-align: top;">
    //                                 <table class="bordered-table1">
    //                                     <tr>
    //                                         <td style="text-align: center;">Cavity Std</td>
    //                                         <td style="text-align: center;">C/T Machine (sec)</td>
    //                                         <td style="text-align: center;">Target/Shift (pcs)</td>
    //                                     </tr>
    //                                     <tr>
    //                                         <td style="text-align: center;">'.$data['cavity_standard'].'</td>
    //                                         <td style="text-align: center;">'.$data['cycle_time'].'</td>
    //                                         <td style="text-align: center;" rowspan="3">'.$target_per_shift.'</td>
    //                                     <tr>
    //                                         <td style="text-align: center;">Cavity Actual</td>
    //                                         <td style="text-align: center;">C/T Finishing (sec)</td>
    //                                     </tr>
    //                                     <tr>
    //                                         <td style="text-align: center;">'.$data['cavity_actual'].'</td>
    //                                         <td style="text-align: center;">'.$data['cycle_time_process'].'</td>
    //                                     </tr>
    //                                 </table>
    //                             </td>
    //                             <td style="vertical-align: top;">
    //                                 <table class="bordered-table1">
    //                                     <tr>
    //                                         <td style="text-align: left;" colspan="2">Person</td>
    //                                         <td style="text-align: center;">'.$data['manpower'].'</td>
    //                                     </tr>
    //                                     <tr>
    //                                         <td style="text-align: center;" colspan="3">Material</td>
    //                                     </tr>
    //                                     <tr>
    //                                         <td style="text-align: left;">'.$material_list.'</td>
    //                                         <td style="text-align: center;">'.$qty.'</td>
    //                                         <td style="text-align: center;">'.$uom.'</td>
    //                                     </tr>
    //                                 </table>
    //                             </td>
    //                         </tr>
    //                     </table>
    //                     <br>
    //                     <table class="bordered-table3">
    //                         <tr>
    //                             <td colspan="3"><b>Condition Check Mold:</b></td>
    //                             <td style="text-align: center;" colspan="2"><b>Diperiksa</b></td>
                                
    //                         </tr>
    //                         <tr>
    //                             <td style="width: 50%; text-align: left;">Cavity Mold</td>
    //                             <td style="width: 15%; text-align: center;">
    //                                 <span style="display: inline-block; width: 20px; height: 20px; border: 1px solid black;"></span> OK
    //                             </td>
    //                             <td style="width: 15%; text-align: center;">
    //                                 <span style="display: inline-block; width: 20px; height: 20px; border: 1px solid black;"></span> .....
    //                             </td>
    //                             <td rowspan="3" colspan="2"</td>
    //                         </tr>
    //                         <tr>
    //                             <td style="width: 35%; text-align: left;">Cooling Mold</td>
    //                             <td style="width: 15%; text-align: center;">
    //                                 <span style="display: inline-block; width: 20px; height: 20px; border: 1px solid black;"></span> OK
    //                             </td>
    //                             <td style="width: 15%; text-align: center;">
    //                                 <span style="display: inline-block; width: 20px; height: 20px; border: 1px solid black;"></span> .....
    //                             </td>
    //                         </tr>
    //                         <tr>
    //                             <td style="width: 35%; text-align: left;">Nepple Mold</td>
    //                             <td style="width: 15%; text-align: center;">
    //                                 <span style="display: inline-block; width: 20px; height: 20px; border: 1px solid black;"></span> OK
    //                             </td>
    //                             <td style="width: 15%; text-align: center;">
    //                                 <span style="display: inline-block; width: 20px; height: 20px; border: 1px solid black;"></span> .....
    //                             </td>
    //                         </tr>
    //                         <tr>
    //                             <td colspan="4" style="width: 35%; text-align: left; height: 120px; vertical-align: top;">Catatan :</td>
    //                         </tr>
    //                     </table>

    //                     <br>
    //                     <table class="bordered-table left-table">
    //                         <tr>
    //                             <th class="signature-header" colspan="2">MENGETAHUI</th>
    //                         </tr>
    //                         <tr>
    //                             <td class="signature-section2"></td>
    //                             <td class="signature-section2"></td>
    //                         </tr>
    //                         <tr>
    //                             <td class="signature-section"></td>
    //                             <td class="signature-section"></td>
    //                         </tr>
    //                         <tr>
    //                             <td>SPV QC</td>
    //                             <td>SPV PRD</td>
    //                         </tr>
    //                     </table>

    //                     <table class="bordered-table right-table">
    //                         <tr>
    //                             <th class="signature-header">DIPERIKSA</th>
    //                             <th class="signature-header">DIBUAT</th>
    //                         </tr>
    //                         <tr>
    //                             <td class="signature-section2"></td>
    //                             <td class="signature-section2"></td>
    //                         </tr>
    //                         <tr>
    //                             <td class="signature-section"></td>
    //                             <td class="signature-section"></td>
    //                         </tr>
    //                         <tr>
    //                             <td>SPV PPC</td>
    //                             <td>PPC</td>
    //                         </tr>
    //                     </table>
    //                 </body></html>';
    //         } 
    //     } else {
    //         $html = "Data tidak ditemukan.";
    //     }

    //     echo $html;
    // }

    public function print_wo($id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $config_iso = $this->db->get('config_iso')->row();

        $this->db->select("a.*, 
            c.number as item_number, 
            c.name as item_name, 
            c.uom, 
            c.is_no, 
            e.number as machine_number, 
            f.mold_name, 
            f.cavity_standard, 
            f.cavity_actual, 
            f.id as mold_id, 
            f.mold_no, 
            h.shift_hour,
            h.shift,
            h.cycle_time, 
            h.cycle_time_process, 
            h.manpower,
            h.productcivity");
        $this->db->from('production_schedules a');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id', 'left');
        $this->db->join('mold_items d', 'c.id = d.item_fg_id', 'left');
        $this->db->join('machines e', 'a.machine_id = e.id', 'left');
        $this->db->join('molds f', 'd.mold_id = f.id', 'left');
        $this->db->join('scan_item_receipts_fg g', 'a.so_number = g.so_number and a.wo_no = g.workorder', 'left');
        $this->db->join('menu_loadings h', 'c.id = h.item_fg_id','left');
        $this->db->join('bom i', 'a.item_fg_id = i.item_fg_id','left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.id', $id);
        $this->db->group_by('a.wo_no');
        $records = $this->db->get()->result_array();

        $html = '';
        // Cek apakah ada data
        if (!empty($records)) {
            foreach ($records as $data) {
                // Query tambahan untuk mengambil item_rm_id dari tabel bom
                $this->db->select('b.name as item_name,b.number as item_number, a.composition, b.uom');
                $this->db->from('bom a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->where('item_fg_id', $data['item_fg_id']);
                $bom_items = $this->db->get()->result_array();

                $shift_hour = !empty($data['shift_hour']) ? $data['shift_hour'] : 0;
                $cycle_time = !empty($data['cycle_time']) && $data['cycle_time'] > 0 ? $data['cycle_time'] : 1;
                $cavity_std = !empty($data['cavity_standard']) ? $data['cavity_standard'] : 0;
                $shift_qty  = !empty($data['shift']) ? $data['shift'] : 0;
                $prod_rate  = isset($data['productcivity']) ? ($data['productcivity'] / 100) : 1; 

                $cap_day = ((3600 * $shift_hour) / $cycle_time) * $cavity_std * $shift_qty * $prod_rate;
                $target_per_shift = ceil($cap_day / 3);

                $material_list = '';
                $material_list2 = '';
                $material_list3 = '';
                $qty = '';
                $uom = '';
                $wo_no = '';
                foreach ($bom_items as $item) {
                    // $material_list2 .= $item['item_number'] . '<br>';
                    $material_list3 .= $item['item_name'] . '<br>';
                    $qty .= number_format($item['composition'] * $data['qty']). '<br>';
                    $uom = $item['uom'];

                    $calc_qty = number_format($item['composition'] * $data['qty']);
                    $material_list .= $item['item_number'] . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ' . $uom . '<br>';
                    $material_list2 .= $item['item_number'] . ' &nbsp; ' . $uom . '<br>';
                }

                // Kalkulasi Lead Time
                $calc_denominator = (3600 / ($data['cycle_time'] + $data['cycle_time_process']) * $data['cavity_standard'] * 0.85);
                $lead_time = ($calc_denominator > 0) ? number_format(($data['qty']) / $calc_denominator, 2) : 0;

                $html = '<!DOCTYPE html>
                <html>
                <head>
                    <title>'.$data['item_number'].'</title>
                    <link href="https://fonts.googleapis.com/css?family=Libre+Barcode+39" rel="stylesheet">
                    <style>
                        /* Optimasi Kertas untuk Print */
                        @media print {
                            @page { size: A4 portrait; margin: 5mm; }
                            body { -webkit-print-color-adjust: exact !important; margin: 0; padding: 0; }
                        }
                        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000; }
                        
                        /* Styling Warna & Border */
                        .bg-grey { background-color: #d9d9d9 !important; }
                        .border-box { border: 1px solid #000; }
                        .border-box-thick { border: 2px solid #000; }
                        
                        /* Styling Teks */
                        .text-center { text-align: center; }
                        .text-bold { font-weight: bold; }
                        .text-right { text-align: right; }
                        
                        /* Styling Barcode */
                        .barcode { font-family: "Libre Barcode 39", sans-serif; font-size: 30px; line-height: 0.8; }
                        .barcode-vertical { 
                            font-family: "Libre Barcode 39", sans-serif; 
                            font-size: 24px; 
                            writing-mode: vertical-rl; 
                            transform: rotate(180deg); 
                            letter-spacing: 2px;
                        }
                        
                        /* Tabel Standard */
                        table { border-collapse: collapse; }
                        .table-info td { padding: 3px 5px; }
                        .table-grid td { border: 1px solid #000; padding: 4px; }
                        
                        /* Tabel Kanban Kanan */
                        .table-kanban td { border: 1px solid #000; padding: 2px; font-size: 10px; }
                        .table-kanban th { border: 1px solid #000; padding: 4px; font-size: 12px; font-weight: bold; }

                        /* Custom Checkbox Hitam Putih */
                        .square-box { 
                            display: inline-block; 
                            width: 12px; height: 12px; 
                            border: 1px solid #000; 
                            vertical-align: middle; 
                            margin-right: 3px; 
                        }

                        /* Container pembungkus utama */
                        .full-outline-wrapper {
                            width: 80%;
                            margin: 0 auto;
                            border: 2px solid #000; 
                            padding: 15px;           
                            border-collapse: collapse;
                        }
                    </style>
                </head>
                <body onload="window.print()">
                    
                    <table width="100%" style="border: none;">
                        <tr>
                            <td style="width: 75%; vertical-align: top; padding-right: 15px;">
                                
                                <table width="100%" style="border: none; margin-bottom: 15px;">
                                    <tr>
                                        <td width="1%" style="vertical-align: middle; padding-right: 8px;">
                                            <img src="' . $config->favicon . '" alt="Logo Perusahaan" style="height: 35px;">
                                        </td>
                                        
                                        <td width="49%" style="font-size: 14px; text-align: left; vertical-align: middle;">
                                            <b>' . $config->name . '</b><br>
                                            <small style="font-size: 10px; color: #555;">' . $config->description . '</small>
                                        </td>
                                        
                                        <td width="50%" class="text-right" style="vertical-align: top;">
                                            <div style="font-weight: bold; font-size: 14px; margin-bottom: 5px;">'.$data['item_number'].'</div>
                                            <div style="font-size: 9px; margin-right: 35px;">FM-PPC-021-Rev-06</div>
                                        </td>
                                    </tr>
                                </table>

                                <div class="text-center text-bold" style="font-size: 22px; margin-bottom: 15px; letter-spacing: 0.5px;">
                                    WORK ORDER (WO) &<br>PRODUCTION REPORT (PR)
                                </div>

                                <div class="full-outline-wrapper">

                                    <table width="100%" class="table-info" style="margin-bottom: 5px;">
                                        <tr>
                                            <td width="25%" class="text-bold" style="font-size: 11px;">Issued Date</td>
                                            <td width="5%">:</td>
                                            <td width="70%" class="bg-grey text-center text-bold">'.$data['trans_date'].'</td>
                                        </tr>
                                        <tr>
                                            <td class="text-bold" style="font-size: 11px;">No Doc</td>
                                            <td>:</td>
                                            <td class="bg-grey text-center text-bold">'.$data['wo_no'].'</td>
                                        </tr>
                                        <tr>
                                            <td></td><td></td>
                                            <td class="text-center barcode">*'.$data['wo_no'].'*</td>
                                        </tr>
                                    </table>

                                    <table width="100%" style="margin-bottom: 10px;">
                                        <tr>
                                            <td width="25%" class="text-bold" style="font-size: 11px; padding: 3px 5px;">Part No</td>
                                            <td width="5%" style="padding: 3px 5px;">:</td>
                                            <td width="70%" class="bg-grey text-center text-bold border-box-thick" style="padding: 5px; font-size: 14px;">'.$data['item_number'].'</td>
                                        </tr>
                                        <tr>
                                            <td class="text-bold" style="font-size: 11px; padding: 3px 5px;">Part Name</td>
                                            <td style="padding: 3px 5px;">:</td>
                                            <td class="text-center text-bold border-box" style="padding: 5px; font-size: 12px;">'.$data['item_name'].'</td>
                                        </tr>
                                        <tr>
                                            <td class="text-bold" style="font-size: 11px; padding: 3px 5px;">IS No</td>
                                            <td style="padding: 3px 5px;">:</td>
                                            <td class="text-center text-bold border-box" style="padding: 5px;">'.$data['is_no'].'</td>
                                        </tr>
                                        <tr>
                                            <td class="text-bold" style="font-size: 11px; padding: 3px 5px;">Mold No</td>
                                            <td style="padding: 3px 5px;">:</td>
                                            <td class="text-center border-box" style="padding: 5px;">'.$data['mold_no'].'</td>
                                        </tr>
                                        <tr>
                                            <td class="text-bold" style="font-size: 11px; padding: 3px 5px;">Mesin No</td>
                                            <td style="padding: 3px 5px;">:</td>
                                            <td class="text-center border-box" style="padding: 5px;">'.$data['machine_number'].'</td>
                                        </tr>
                                    </table>

                                </div> 

                                <table width="50%" align="center" class="table-grid border-box-thick" style="margin-bottom: 10px;">
                                    <tr>
                                        <br>
                                        <td width="50%" class="text-center text-bold" style="font-size: 16px;">LOT No</td>
                                        <td width="50%" class="text-center text-bold" style="font-size: 16px;">Qty (pcs)</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center text-bold" style="font-size: 18px; padding: 8px;">'.$data['lot_no'].'</td>
                                        <td class="text-center text-bold" style="font-size: 18px; padding: 8px;">'.number_format($data['qty']).'</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center text-bold" style="font-size: 13px;">lead time (hour)</td>
                                        <td class="text-right text-bold" style="font-size: 15px; padding-right: 15px;">'.$lead_time.'</td>
                                    </tr>
                                </table>

                                <table width="80%" align="center" class="table-info" style="margin-bottom: 15px;">
                                    <tr>
                                        <td width="25%" class="text-bold" style="font-size: 9px;">Polybag Label</td><td width="5%" style="font-size: 9px;">:</td>
                                        <td width="70%" class="bg-grey text-center text-bold" style="font-size: 9px;">Label Manual Logo BPI</td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold" style="font-size: 9px;">Box Label</td><td style="font-size: 9px;">:</td>
                                        <td class="bg-grey text-center text-bold" style="font-size: 9px;">Tidak Pakai Label Manual</td>
                                    </tr>
                                </table>

                                <table width="80%" align="center" class="table-grid border-box-thick" style="margin-bottom: 10px;">
                                    <tr>
                                        <td colspan="3" class="text-center text-bold" style="font-size: 14px; width: 60%;">Cycle Time</td>
                                        <td colspan="2" class="text-center text-bold" style="font-size: 14px; width: 40%;">Man Power</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="font-size: 9px; width: 20%;">Cavity Std</td>
                                        <td class="text-center" style="font-size: 9px; width: 20%;">C/T Machine (sec)</td>
                                        <td class="text-center" style="font-size: 9px; width: 20%;">Target/Shift (pcs)</td>
                                        <td class="text-center text-bold" style="width: 20%;">'.$data['manpower'].'</td>
                                        <td class="text-center" style="font-size: 9px; width: 20%;">Person</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center text-bold" style="font-size: 14px;">'.$data['cavity_standard'].'</td>
                                        <td class="text-center text-bold" style="font-size: 14px;">'.$data['cycle_time'].'</td>
                                        <td rowspan="3" class="text-center text-bold" style="font-size: 14px;">'.$target_per_shift.'</td>
                                        <td colspan="2" class="text-center" style="font-size: 10px;">Material</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="font-size: 9px;">Cavity Actual</td>
                                        <td class="text-center" style="font-size: 9px;">C/T Finishing (sec)</td>
                                        <td colspan="2" rowspan="2" class="text-left text-bold" style="vertical-align: bottom; padding-bottom: 8px; font-size: 9px;">
                                            '.$material_list.'
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center text-bold" style="font-size: 14px;">'.$data['cavity_actual'].'</td>
                                        <td class="text-center text-bold" style="font-size: 14px;">'.$data['cycle_time_process'].'</td>
                                    </tr>
                                </table>

                                <table width="80%" align="center" class="table-grid border-box-thick" style="margin-bottom: 10px;">
                                    <tr>
                                        <td colspan="2" class="text-bold" style="font-size: 12px; border-bottom: none; border-right: none;">Condition Check Mold:</td>
                                        <td width="25%" class="text-center" style="font-size: 10px;">DIPERIKSA</td>
                                    </tr>
                                    <tr>
                                        <td width="35%" style="border-top: none; border-right: none; padding-left: 10px; font-size: 10px; line-height: 1.5;">
                                            Cavity Mold &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<br>
                                            Cooling Mold &nbsp;&nbsp;&nbsp;:<br>
                                            Nepple Mold &nbsp;&nbsp;&nbsp;&nbsp;:
                                        </td>
                                        <td width="40%" style="border-top: none; border-left: none; font-size: 10px; line-height: 1.5;">
                                            <span class="square-box"></span> OK &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span class="square-box"></span> ........<br>
                                            <span class="square-box"></span> OK &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span class="square-box"></span> ........<br>
                                            <span class="square-box"></span> OK &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span class="square-box"></span> ........
                                        </td>
                                        <td rowspan="1"></td> </tr>
                                    <tr>
                                        <td colspan="3" style="height: 40px; vertical-align: top; font-size: 10px;">Catatan :</td>
                                    </tr>
                                </table>

                                <table width="80%" align="center" style="margin-bottom: 5px;">
                                    <tr>
                                        <td width="48%">
                                            <table width="100%" class="table-grid border-box-thick">
                                                <tr><td colspan="2" class="text-center" style="font-size: 10px;">MENGETAHUI</td></tr>
                                                <tr><td style="height: 40px; width: 50%;"></td><td style="width: 50%;"></td></tr>
                                                <tr><td class="text-center" style="font-size: 10px;">SPV.QC</td><td class="text-center" style="font-size: 10px;">SPV.PRD</td></tr>
                                            </table>
                                        </td>
                                        <td width="4%"></td>
                                        <td width="48%">
                                            <table width="100%" class="table-grid border-box-thick">
                                                <tr>
                                                    <td class="text-center" style="font-size: 10px; width: 50%;">DIPERIKSA</td>
                                                    <td class="text-center" style="font-size: 10px; width: 50%;">DIBUAT</td>
                                                </tr>
                                                <tr><td style="height: 40px;"></td><td></td></tr>
                                                <tr><td class="text-center" style="font-size: 10px;">SPV.PPC</td><td class="text-center" style="font-size: 10px;">PPC</td></tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>

                            <td style="width: 15%; vertical-align: top;">
                                <table class="table-kanban" style="width: 100%; border: 2px solid #000;">
                                    <tr>
                                        <td rowspan="31" style="width: 25px; border-right: 1px solid #000; padding: 0; vertical-align: top; text-align: center;">
                                            <div class="barcode-vertical" style="margin-top: 10px;">*MAT-'.$data['wo_no'].'*</div>
                                        </td>
                                        <th class="text-center" style="font-size: 14px; padding: 8px 2px;">Kanban<br>Material</th>
                                    </tr>
                                    <tr><td class="text-center" style="font-size: 9px;">Date Supply</td></tr>
                                    <tr><td class="text-center" style="padding: 4px; font-size: 11px;">'.$data['trans_date'].'</td></tr>
                                    
                                    <tr><td class="text-center" style="font-size: 9px;">No Doc PRR</td></tr>
                                    <tr><td class="text-center" style="padding: 4px; font-size: 11px;">'.$data['wo_no'].'</td></tr>
                                    
                                    <tr><td class="text-center" style="font-size: 9px;">Part No</td></tr>
                                    <tr><td class="text-center text-bold" style="padding: 4px; font-size: 11px;">'.$data['item_number'].'</td></tr>
                                    
                                    <tr><td class="text-center" style="font-size: 9px;">Part Name</td></tr>
                                    <tr><td class="text-center" style="padding: 4px; font-size: 11px;">'.$data['item_name'].'</td></tr>
                                    
                                    <tr><th class="text-center" style="padding: 6px; font-size: 12px;">SUPPLY</th></tr>
                                    
                                    <tr><td class="text-center" style="font-size: 9px;">Material No</td></tr>
                                    <tr><td class="text-center" style="padding: 4px; font-size: 8px;">'.$material_list2.'</td></tr>
                                    
                                    <tr><td class="text-center" style="font-size: 9px;">Material Name</td></tr>
                                    <tr><td class="text-center" style="padding: 4px; font-size: 11px;">'.$material_list3.'</td></tr>
                                    
                                    <tr><td class="text-center" style="font-size: 9px;">Qty Request</td></tr>
                                    <tr><td class="text-center" style="padding: 4px; font-size: 11px;">'.$qty.'</td></tr>
                                    
                                    <tr><td class="text-center" style="font-size: 9px; padding: 2px;">Actual Supply</td></tr>
                                    <tr><td style="height: 40px;"></td></tr>
                                    
                                    <tr><td class="text-center" style="font-size: 9px; padding: 2px;">Supplied by,</td></tr>
                                    <tr><td style="height: 40px;"></td></tr>

                                    <tr><td class="text-center" style="padding: 2px; font-size: 10px;">Warehouse</td></tr>
                                    <tr><td style="height: 40px;"></td></tr>

                                    <tr><td class="text-center" style="font-size: 9px; padding: 2px;">Received by,</td></tr>
                                    <tr><td style="height: 40px;"></td></tr>
                                    
                                    <tr><td class="text-center" style="padding: 2px; font-size: 10px;">Production</td></tr>
                                    <tr><td class="text-center text-bold" style="padding: 4px; font-size: 12px;">'.$data['machine_number'].'</td></tr>
                                    
                                    <tr><td class="text-center" style="font-size: 9px; padding: 2px;">Supply to M/C</td></tr>
                                    
                                    <tr><td class="text-center" style="font-size: 9px; padding: 2px;">LOT</td></tr>
                                    <tr><td class="text-center text-bold" style="padding: 2px; font-size: 11px;">'.$data['lot_no'].'</td></tr>
                                    
                                    <tr><td class="text-center" style="font-size: 9px; padding: 2px;">Issued date</td></tr>
                                    <tr><td class="text-center text-bold" style="padding: 2px; font-size: 11px;">'.$data['trans_date'].'</td></tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                </body>
                </html>';
            } 
        } else {
            $html = "Data tidak ditemukan.";
        }

        echo $html;
    }
}
