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
        $send = $this->crud->query("SELECT DISTINCT wo_no FROM production_schedules WHERE `status` = 0 ORDER BY `wo_no` DESC");
        echo json_encode($send);
    }

    public function readMachine()
    {
        $send = $this->crud->query("SELECT b.id as machine_id, b.number as machine_number 
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

    public function readItems()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT *  
            FROM item_fg 
            WHERE  `number` like '%$post%' or `name` like '$post'
            ORDER BY `number` ASC");
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

        $send = $this->crud->query("SELECT DISTINCT total 
            FROM purgings 
            WHERE machine_id = '$machines' and kind = '$colors'");
        echo json_encode($send);
    }

    public function readMonth()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array(
                "number" => $key,
                "name" => $value
            );
        }
        die(json_encode($arr));
    }

    public function readYear()
    {
        $tahun_before = date('Y', strtotime('-5 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        for ($i = $tahun_before; $i <= $tahun_next; $i++) {
            $arr[] = array(
                "number" => "$i"
            );
        }
        die(json_encode($arr));
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
            // $filter_line_productions = $this->input->get('filter_line_productions');
            // $filter_customers = $this->input->get('filter_customers');
            // $filter_sales_order = $this->input->get('filter_sales_order');
            $filter_wo_no = $this->input->get('filter_wo_no');
            $filter_machine_id = $this->input->get('filter_machine_id');
            $filter_mold_id = $this->input->get('filter_mold_id');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');
            $filter_status = $this->input->get('filter_status');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, c.number as item_number, c.name as item_name, c.uom, e.number as machine_number, f.mold_name,
            (CASE WHEN g.id != '' THEN 2 ELSE a.status END) as status_wo");
            $this->db->from('production_schedules a');
            // $this->db->join('customers b', 'a.customer_id = b.id');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id');
            $this->db->join('mold_items d', 'c.id = d.item_fg_id', 'left');
            $this->db->join('machines e', 'a.machine_id = e.id');
            $this->db->join('molds f', 'd.mold_id = f.id', 'left');
            // $this->db->join('line_productions d', 'a.line_id = d.id');
            $this->db->join('scan_item_receipts_fg g', 'a.so_number = g.so_number and a.workorder = g.workorder', 'left');
            $this->db->where('a.deleted', 0);
            if($filter_status == "0"){
                $this->db->where("a.status", 0);
            }elseif($filter_status == "1"){
                $this->db->where("f.id is NULL");
            }elseif($filter_status == "2"){
                $this->db->where("f.id != ''");
            }
            $this->db->like('a.month', $filter_month);
            $this->db->like('a.year', $filter_year);
            $this->db->like('a.wo_no', $filter_wo_no);
            // $this->db->like('a.line_id', $filter_line_productions);
            // $this->db->like('a.customer_id', $filter_customers);
            // $this->db->like('a.so_number', $filter_sales_order);
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->like('a.machine_id', $filter_machine_id);
            $this->db->like('a.mold_id', $filter_mold_id);
            $this->db->order_by('a.trans_date', 'DESC');
            $this->db->group_by('a.wo_no');
            // $this->db->order_by('b.name', 'ASC');
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
            $postFinal = array_merge($post, array("period" => $post['year'] . $post['month']));
            $send   = $this->crud->create('production_schedules', $postFinal);
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
            $send = $this->crud->update('production_schedules', ["id" => $id], $post);
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('production_schedules', ["id" => $data['id']]);
        // $update = $this->crud->update('sales_orders', ["number" => $data['so_number'], "item_fg_id" => $data['item_fg_id']], ["status" => 0]);
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
        for ($i = 3; $i <= $total_row; $i++) {
            $datas[] = array(
                //excel
                'wo_no' => $data->val($i, 2),
                'period' => $data->val($i, 3),
                'machine_no' => $data->val($i, 4),
                'lot_no' => $data->val($i, 5),
                'mold_id' => $data->val($i, 6),
                'trans_date' => $data->val($i, 7),
                'item_fg_number' => $data->val($i, 8),
                'qty' => $data->val($i, 9)
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

            $color = $item_fg->color;
            $machines = $machine->id;
            $purging = $this->crud->read('purgings', [], ["machine_id" => $machines, "kind" => $color]);
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
                    "color" => $color,
                    "total_purging" => $total_purgings,
                    "qty" => $data['qty'],
                );
                $send   = $this->crud->create('production_schedules', $dataFinal);
                echo $send;
            }
        }
    }

    public function print_job_order($id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $config_iso = $this->db->get('config_iso')->row();

        $this->db->select('a.*, b.lot');
        $this->db->from('production_schedules a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.id', $id);
        $label = $this->db->get()->row();
        $amountQty = ceil($label->qty / $label->lot);
        for ($i = 1; $i <= $amountQty; $i++) {
            $lots = sprintf("%03s", $i);
            $this->db->select('b.circuit');
            $this->db->from('production_schedules a');
            $this->db->join('job_orders b', 'a.item_fg_id = b.item_fg_id and a.customer_id and b.customer_id', 'left');
            $this->db->where('a.deleted', 0);
            $this->db->where('a.id', $id);
            $this->db->order_by('b.circuit', 'asc');
            $totalRows = $this->db->count_all_results('', false);
            $job_orders = $this->db->get()->result_array();
            $no = 1;
            $qty = $label->qty;
            foreach ($job_orders as $job_order) {
                $sequence = sprintf("%03s", $no);
                $label_no = $label->workorder . $lots . $sequence;
                if ($no == $totalRows) {
                    $finalQty = $qty;
                } else {
                    $finalQty = $label->lot;
                }
                $dataJobOrderLabel = array(
                    "workorder" => $label->workorder,
                    "label_no" => $label_no,
                    "circuit" => $job_order['circuit'],
                    "qty" => $finalQty,
                );
                $jobOrderLabel = $this->crud->read("job_order_labels", [], ["label_no" => $label_no]);
                if (empty($jobOrderLabel->id)) {
                    $this->crud->create("job_order_labels", $dataJobOrderLabel);
                }
                $qty -= $label->lot;
                $no++;
            }
        }
        $this->db->select('b.*, a.so_number, a.workorder, a.so_date, a.trans_date, a.qty, c.label_no, c.circuit, d.number as item_number, d.lot');
        $this->db->from('production_schedules a');
        $this->db->join('job_order_labels c', 'a.workorder = c.workorder');
        $this->db->join('job_orders b', 'a.item_fg_id = b.item_fg_id and a.customer_id and b.customer_id and c.circuit = b.circuit', 'left');
        $this->db->join('item_fg d', 'a.item_fg_id = d.id');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.id', $id);
        $this->db->group_by('c.circuit');
        $this->db->group_by('c.label_no');
        $this->db->order_by('c.label_no', 'asc');
        $records = $this->db->get()->result_object();
        if ($records) {
            $html = '<html>
                    <head>
                        <title>' . $label->workorder . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 20cm;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
            foreach ($records as $record) {
                //Generate QRcode
                $this->createQrcode($record->label_no, "assets/image/qrcode/");
                $html .= '  <table id="customers" border="1" style="margin-bottom:20px;">
                                <tr>
                                    <th colspan="4" style="font-size:16px; padding:10px; text-align:center;"><b>JOB ORDER ' . $config->name . '</b></th>
                                    <th width="150">
                                        <table style="width:100%; font-size:10px; border:0;">
                                            <tr style="border:0;">
                                                <td width="60">Doc No</td>
                                                <td width="100">' . $config_iso->doc_job_order . '</td>
                                            </tr>
                                            <tr style="border:0;">
                                                <td>Form</td>
                                                <td>' . $config_iso->form_job_order . '</td>
                                            </tr>
                                        </table>
                                    </th>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">MODEL</th>
                                    <th style="text-align:center;">PLAN QTY</th>
                                    <th style="text-align:center;">LOT</th>
                                    <th style="text-align:center;">START DATE</th>
                                    <th style="text-align:center;">ISSUE DATE</th>
                                </tr>
                                <tr>
                                    <td style="text-align:center;">' . $record->item_number . '</td>
                                    <td style="text-align:center;">' . $record->qty . '</td>
                                    <td style="text-align:center;">' . $record->lot . '</td>
                                    <td style="text-align:center;">' . $record->trans_date . '</td>
                                    <td style="text-align:center;">' . $record->so_date . '</td>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">WIRE CODE</th>
                                    <th style="text-align:center;">TYPE & SIZE</th>
                                    <th style="text-align:center;">COLOR</th>
                                    <th style="text-align:center;">LENGTH</th>
                                    <th style="text-align:center;">M/C NO</th>
                                </tr>
                                <tr>
                                    <td style="text-align:center;">' . $record->wire . '</td>
                                    <td style="text-align:center;">' . $record->type . ' ' . $record->size . '</td>
                                    <td style="text-align:center;">' . $record->color . '</td>
                                    <td style="text-align:center;">' . $record->length . '</td>
                                    <td style="text-align:center;"></td>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">TERMINAL SIDE A</th>
                                    <th style="text-align:center;">TERMINAL SIDE B</th>
                                    <th colspan="3" style="text-align:center;">WO. No</th>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_terminal . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_terminal . '</td>
                                    <td rowspan="7" colspan="3" style="text-align:center;">' . $record->workorder . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_seal . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_seal . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_chi . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_chi . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_chc . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_chc . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_stripping . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_stripping . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_process . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_process . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align:center; height:20px;">' . $record->a_note . '</td>
                                    <td style="text-align:center; height:20px;">' . $record->b_note . '</td>
                                </tr>
                                <tr>
                                    <th style="text-align:center;">CIRCUIT NO</th>
                                    <th style="text-align:center;">SERIAL NO</th>
                                    <th style="text-align:center;">OPERATOR</th>
                                    <th style="text-align:center;">CHECK BY</th>
                                    <th style="text-align:center;">INSPECT BY</th>
                                </tr>
                                <tr>
                                    <th rowspan="3" style="text-align:center; height:50px; font-size:40px;">' . $record->circuit . '</th>
                                    <td rowspan="3" style="text-align:center; height:50px;">
                                        <img src="' . base_url('assets/image/qrcode/' . $record->label_no . '.png') . '" width="80"/>
                                        <br>
                                        <span>' . $record->label_no . '</span>
                                    </td>
                                    <th style="text-align:center; height:80px;"></th>
                                    <th style="text-align:center; height:80px;"></th>
                                    <th style="text-align:center; height:80px;"></th>
                                </tr>
                                <tr>
                                    <th style="text-align:left;">Name :</th>
                                    <th style="text-align:left;">Name :</th>
                                    <th style="text-align:left;">Name :</th>
                                </tr>
                                <tr>
                                    <th style="text-align:left;">Date :</th>
                                    <th style="text-align:left;">Date :</th>
                                    <th style="text-align:left;">Date :</th>
                                </tr>
                            </table>';
            }
            $html .= "<script>window.print()</script>";
            die($html);
        } else {
            echo "<h1>NOT FOUND JOB ORDER</h1>";
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=production_schedules_$format.xls");
        }
        $filter_month = $this->input->get('filter_month');
        $filter_year = $this->input->get('filter_year');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // $this->db->select('a.*, b.number as customer_number, b.name as customer_name, b.type as customer_type, c.number as item_number, c.name as item_name, c.uom, d.name as line_name');
        // $this->db->from('production_schedules a');
        // $this->db->join('customers b', 'a.customer_id = b.id');
        // $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        // $this->db->join('line_productions d', 'a.line_id = d.id');;
        // $this->db->where('a.deleted', 0);
        // $this->db->like('a.month', $filter_month);
        // $this->db->like('a.year', $filter_year);
        // $this->db->like('a.line_id', $filter_line_productions);
        // $this->db->like('a.customer_id', $filter_customers);
        // $this->db->like('a.so_number', $filter_sales_order);
        // $this->db->like('a.item_fg_id', $filter_item_fg_id);
        // $this->db->order_by('a.trans_date', 'ASC');
        // $this->db->order_by('b.name', 'ASC');
        // $this->db->order_by('c.number', 'ASC');

        $this->db->select("a.*, c.number as item_number, c.name as item_name, c.uom, e.number as machine_number, f.mold_name");
        $this->db->from('production_schedules a');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id');
        $this->db->join('mold_items d', 'c.id = d.item_fg_id');
        $this->db->join('machines e', 'a.machine_id = e.id');
        $this->db->join('molds f', 'd.mold_id = f.id');
        $this->db->join('scan_item_receipts_fg g', 'a.so_number = g.so_number and a.workorder = g.workorder', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->like('a.month', $filter_month);
        $this->db->like('a.year', $filter_year);
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->order_by('a.trans_date', 'DESC');
        $this->db->group_by('a.wo_no');
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
                    <th>Mold No</th>
                    <th>WO Date</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>UoM</th>
                    <th>Qty</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['wo_no'] . '</td>
                            <td>' . $data['period'] . '</td>
                            <td>' . $data['machine_number'] . '</td>
                            <td>' . $data['lot_no'] . '</td>
                            <td>' . $data['mold_name'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['item_number'] . '</td>
                            <td>' . $data['item_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . number_format($data['qty'], 2) . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
