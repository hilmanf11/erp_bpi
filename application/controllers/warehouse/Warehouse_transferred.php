<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Warehouse_transferred extends CI_Controller
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
        //VALIDASI FORM
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[output_productions.item_fg_id]');
        $this->form_validation->set_rules('item_rm_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[output_productions.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/warehouse_transferred');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('output_productions', ["item_fg_id" => $post]);
        echo json_encode($send);
    }

    public function checkNote()
    {
        $document_no = $this->input->post('document_no');

        $this->db->select('COUNT(*) as total');
        $this->db->from('scan_rm_transfer');
        $this->db->where('document_no', $document_no);
        $this->db->where("(approved_to IS NOT NULL AND approved_to != '')", null, false);

        $record = $this->db->get()->row_array();

        if ($record['total'] > 0) {
            echo json_encode("NO");
        } else {
            echo json_encode("YES");
        }
    }

    public function documentNo()
    {
        $data = $this->crud->query("SELECT DISTINCT document_no FROM scan_rm_transfer WHERE `status` = '0' ORDER BY document_no ASC");
        echo json_encode($data);
    }

    public function readItemFg($period="")
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("
        select distinct a.item_fg_id, a.workorder as wo_no, a.period, b.number, b.name, a.lot_no ,'Supply Sheets' as modul
        from supply_sheets a 
        join item_fg b on a.item_fg_id=b.id 
        where a.period='$period' and (b.number like '%$post%' or b.number_customer like '%$post%' or b.name like '%$post%' or a.workorder like '%$post%' or a.lot_no like '%$post%')
        
        UNION

        select distinct a.item_fg_id, a.wo_no, a.period, b.number, b.name, a.lot_no , 'Production Schedule' as modul
        from production_schedules a 
        join item_fg b on a.item_fg_id=b.id 
        where a.period='$period' and a.status_subcont = 'YES' and a.subcont_type = 'Jasa'
        and (b.number like '%$post%' or b.number_customer like '%$post%' or b.name like '%$post%' or a.wo_no like '%$post%' or a.lot_no like '%$post%') 
        
        order by modul,item_fg_id asc 
        ");  /** production_schedules hanya tampil Subcont Type Jasa (Bu Septi) */
        
        echo json_encode($send);
    }

    public function readMachine()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM machines WHERE `status` = 0 and (number like '%$post%' or name like '%$post%')");
        echo json_encode($send);
    }

    //GET DATA
    public function autonumber()
    {
        $ymd = date("ymd");
        $sql = $this->db->query("SELECT max(`number`) as kode FROM output_productions where `number` like '%$ymd%'");
        $row = $sql->row();
        if ($row->kode == null) {
            $autonumber = "PRD-" . $ymd . "0001";
        } else {
            $kode = substr($row->kode, -4);
            $autonumber = "PRD-" . $ymd . sprintf("%04s", $kode + 1);
        }
        echo $autonumber;
    }

    public function readNumber()
    {
        $send = $this->crud->query("SELECT DISTINCT `number`
        FROM output_productions
        WHERE `deleted` = 0
        ORDER BY `number` DESC");
        echo json_encode($send);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_division = $this->input->get('filter_division');
            $filter_document_no = $this->input->get('filter_document_no');
            $filter_transfer_from = $this->input->get('filter_transfer_from');
            $filter_transfer_to = $this->input->get('filter_transfer_to');
            $filter_item_rm_id = $this->input->get('filter_item_rm_id');
            $filter_item_category = $this->input->get('filter_item_category');
            $filter_item_family = $this->input->get('filter_item_family');
            $filter_status = $this->input->get('filter_status');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, DATE(a.transaction_date) as transaction_dates");
            $this->db->from('scan_rm_transfer a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', 'b.item_family_id = c.id', 'left');
            $this->db->join('item_categories d', 'b.item_category_id = d.id', 'left');
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.transaction_date >=', $filter_from);
                $this->db->where('a.transaction_date <=', $filter_to);
            }
            if ($filter_division != "") {
                $this->db->where('a.division', $filter_division);
            }
            if ($filter_document_no != "") {
                $this->db->where('a.document_no', $filter_document_no);
            }
            if ($filter_transfer_from != "") {
                $this->db->where('a.transfer_from', $filter_transfer_from);
            }
            if ($filter_transfer_to != "") {
                $this->db->where('a.transfer_to', $filter_transfer_to);
            }
            if ($filter_item_rm_id != "") {
                $this->db->where('a.item_rm_id', $filter_item_rm_id);
            }
            if ($filter_item_category != "") {
                $this->db->where('b.item_category_id', $filter_item_category);
            }
            if ($filter_item_family != "") {
                $this->db->where('b.item_family_id', $filter_item_family);
            }
            if ($filter_status == "approve") {
                $this->db->where("(a.approved_to IS NULL OR a.approved_to = '')", null, false);
            } elseif ($filter_status == "checking") {
                $this->db->where("a.approved_to IS NOT NULL AND a.approved_to != ''", null, false);
            }
            $this->db->group_by('a.document_no');
            $this->db->order_by('a.created_date', 'DESC');
            // $this->db->order_by('b.number', 'ASC');
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

    //GET DATATABLES DETAILS
    public function datatableDetails()
    {
        if ($this->input->get()) {
            $number = base64_decode($this->input->get('number'));
            $filter_item_rm_id = base64_decode($this->input->get('item_rm_id'));

            $this->db->select("a.*, b.number as item_number, b.name as item_name, b.uom, c.name as family_name, d.name as category_name, SUM(a.qty) as qtys");
            $this->db->from('scan_rm_transfer a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id', 'left');
            $this->db->join('item_familys c', 'b.item_family_id = c.id', 'left');
            $this->db->join('item_categories d', 'b.item_category_id = d.id', 'left');
            $this->db->where('a.document_no', $number);
            if ($filter_item_rm_id != "") {
                $this->db->where('a.item_rm_id', $filter_item_rm_id);
            }
           
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    // UPDATE DATA
    // public function datatableUpdates()
    // {
    //     if ($this->input->get()) {
    //         $number = base64_decode($this->input->get('number'));
    //         $this->db->select("a.*, b.number as item_fg_number, b.name as item_fg_name");
    //         $this->db->from('output_productions a');
    //         $this->db->join('item_fg b', 'a.item_fg_id = b.id', 'left');
    //         $this->db->where('a.number', $number);
    //         $this->db->order_by('a.id', 'ASC');
    //         $records = $this->db->get()->result_array();

    //         echo json_encode($records);
    //     }
    // }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post();

    //         $dataFinal = array(
    //             //field
    //             "trans_date" => $post['trans_date'],
    //             "number" => $post['number'],
    //             "period" => $post['period'],
    //             "shift" => $post['shift'],
    //             "item_fg_id" => $post['item_fg_id'],
    //             "wo_no" => $post['wo_no'],
    //             "lot_no" => $post['lot_no'],
    //             "qty" => $post['qty'],
    //             "qty_wip" => $post['qty_wip'],
    //             "remarks" => $post['remarks'],
    //         );

    //         if (@$post['id'] != "") {
    //             $send = $this->crud->update('output_productions', ["id" => $post['id']], $dataFinal);
    //         } else {
    //             $send = $this->crud->create('output_productions', $post);
    //         }
    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    // public function delete()
    // {
    //     $data = $this->input->post();
    //     $send = $this->crud->delete('output_productions', $data);
    //     echo $send;
    // }

    //UPLOAD DATA
    // public function upload()
    // {
    //     error_reporting(0);
    //     require_once 'assets/vendors/excel_reader2.php';
    //     $target = basename($_FILES['file_upload']['name']);
    //     move_uploaded_file($_FILES['file_upload']['tmp_name'], $target);
    //     chmod($_FILES['file_upload']['name'], 0777);
    //     $file = $_FILES['file_upload']['name'];
    //     $data = new Spreadsheet_Excel_Reader($file, false);
    //     $total_row = $data->rowcount($sheet_index = 0);
    //     for ($i = 3; $i <= $total_row; $i++) {
    //         $datas[] = array(
    //             //excel
    //             'trans_date' => $data->val($i, 2),
    //             'period' => $data->val($i, 3),
    //             'shift' => $data->val($i, 4),
    //             'item_number' => $data->val($i, 5),
    //             'wo_no' => $data->val($i, 6),
    //             'qty' => $data->val($i, 7),
    //             'qty_wip' => $data->val($i, 8),
    //             'machine_number' => $data->val($i, 9),
    //             'remarks' => $data->val($i, 10),
    //         );
    //     }
    //     $datas['total'] = count($datas);
    //     echo json_encode($datas);
    //     unlink($_FILES['file_upload']['name']);
    // }

    // public function uploadclearFailed()
    // {
    //     @unlink('failed/output_productions.txt');
    // }

    // public function uploadcreateFailed()
    // {
    //     if ($this->input->post()) {
    //         $message = $this->input->post('message');
    //         $textFailed = fopen('failed/output_productions.txt', 'a');
    //         fwrite($textFailed, $message . "\n");
    //         fclose($textFailed);
    //     }
    // }

    //UPLOAD DOWNLOAD FAILED
    // public function uploadDownloadFailed()
    // {
    //     $file = "failed/output_productions.txt";
    //     header('Content-Description: File Failed');
    //     header('Content-Disposition: attachment; filename=' . basename($file));
    //     header('Expires: 0');
    //     header('Cache-Control: must-revalidate');
    //     header('Pragma: public');
    //     header('Content-Length: ' . @filesize($file));
    //     header("Content-Type: text/plain");
    //     @readfile($file);
    // }

    //UPLOAD CREATE DATA
    // public function uploadCreate()
    // {
    //     if ($this->input->post()) {
    //         $data = $this->input->post('data');

    //         $item_fg = $this->crud->read('item_fg', [], array("number" => $data['item_number']));
    //         $machines = $this->crud->read('machines', [], array("number" => $data['machine_number']));
    //         $data_cek = array(
    //                 "item_fg_id" => $item_fg->id,
    //                 "trans_date" => $data['trans_date'],
    //                 "wo_no" => $data['wo_no'],
    //                 "period" => $data['period'],
    //                 "qty" => $data['qty'],
    //                 "qty_wip" => $data['qty_wip'],
    //                 "shift" => $data['shift'],
    //                 "remarks" => $data['remarks'],
    //                 "machine_number" => $data['machine_number'],
    //             );
    //         $output_productions = $this->crud->read('output_productions', [], $data_cek);
    //         $send = $this->crud->query("
    //             SELECT DISTINCT a.item_fg_id, a.workorder AS wo_no, a.period, b.number, b.name, a.lot_no, 'Supply Sheets' AS modul
    //             FROM supply_sheets a 
    //             JOIN item_fg b ON a.item_fg_id = b.id 
    //             WHERE a.period = '{$data['period']}'

    //             UNION

    //             SELECT DISTINCT a.item_fg_id, a.wo_no, a.period, b.number, b.name, a.lot_no, 'Production Schedule' AS modul
    //             FROM production_schedules a 
    //             JOIN item_fg b ON a.item_fg_id = b.id 
    //             WHERE a.period = '{$data['period']}' AND a.status_subcont = 'YES' AND a.subcont_type = 'Jasa'

    //             ORDER BY modul, item_fg_id ASC
    //         ");

    //         $item_fg_ids = array_column($send, 'item_fg_id');

    //         if (empty($item_fg) || empty($item_fg->id)) {
    //             echo json_encode(array("title" => "Not Found","message" => "Product number " . $data['item_number'] . " NOT FOUND","theme" => "error"));
    //             // return;
    //         } elseif (empty($machines)) {
    //         echo json_encode(array("title" => "Not Found","message" => "Machine number " . $data['machine_number'] . " NOT FOUND IN MODUL MACHINE","theme" => "error"));
    //         // return;
    //         } elseif ($output_productions) {
    //         echo json_encode(array("title" => "Duplicate","message" => "Duplicate Product number " . $data['item_number'] . " FOUND","theme" => "error"));
    //         } elseif (!in_array($item_fg->id, $item_fg_ids)) {
    //             echo json_encode(array("title" => "Not Found","message" => "Product number " . $data['item_number'] . " NOT FOUND IN PERIOD " . $data['period'],"theme" => "error"));
    //             // return;
    //         }else{
    //             $lot_no = null;
    //             foreach ($send as $row) {
    //                 if ($row->item_fg_id == $item_fg->id) {
    //                     $lot_no = $row->lot_no;
    //                     break;
    //                 }
    //             }

    //             // AUTONUMBER
    //             $ymd = date("ymd");
    //             $sql = $this->db->query("SELECT MAX(`number`) AS kode FROM output_productions WHERE `number` LIKE '%$ymd%'");
    //             $row = $sql->row();
    //             if ($row->kode == null) {
    //                 $autonumber = "PRD-" . $ymd . "0001";
    //             } else {
    //                 $kode = substr($row->kode, -4);
    //                 $autonumber = "PRD-" . $ymd . sprintf("%04s", $kode + 1);
    //             }

    //             $dataFinal = array(
    //                 "number" => $autonumber,
    //                 "item_fg_id" => $item_fg->id,
    //                 "trans_date" => $data['trans_date'],
    //                 "wo_no" => $data['wo_no'],
    //                 "period" => $data['period'],
    //                 "qty" => $data['qty'],
    //                 "qty_wip" => $data['qty_wip'],
    //                 "shift" => $data['shift'],
    //                 "remarks" => $data['remarks'],
    //                 "lot_no" => $lot_no,
    //                 "machine_number" => $data['machine_number'],
    //                 "type" => "Upload",
    //             );

    //             $send   = $this->crud->create('output_productions', $dataFinal);
    //             echo $send;
    //         }
    //     }
    // }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=output_productions_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_document_no = $this->input->get('filter_document_no');
        $filter_transfer_from = $this->input->get('filter_transfer_from');
        $filter_transfer_to = $this->input->get('filter_transfer_to');
        $filter_item_rm_id = $this->input->get('filter_item_rm_id');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_status = $this->input->get('filter_status');


        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.number as item_number, b.name as item_name, b.uom, c.name as family_name, d.name as category_name, SUM(a.qty) as qtys");
        $this->db->from('scan_rm_transfer a');
        $this->db->join('item_rm b', 'a.item_rm_id = b.id', 'left');
        $this->db->join('item_familys c', 'b.item_family_id = c.id', 'left');
        $this->db->join('item_categories d', 'b.item_category_id = d.id', 'left');
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.transaction_date >=', $filter_from);
            $this->db->where('a.transaction_date <=', $filter_to);
        }
        if ($filter_division != "") {
            $this->db->where('a.division', $filter_division);
        }
        if ($filter_document_no != "") {
            $this->db->where('a.document_no', $filter_document_no);
        }
        if ($filter_transfer_from != "") {
            $this->db->where('a.transfer_from', $filter_transfer_from);
        }
        if ($filter_transfer_to != "") {
            $this->db->where('a.transfer_to', $filter_transfer_to);
        }
        if ($filter_item_rm_id != "") {
            $this->db->where('a.item_rm_id', $filter_item_rm_id);
        }
        if ($filter_item_category != "") {
            $this->db->where('b.item_category_id', $filter_item_category);
        }
        if ($filter_item_family != "") {
            $this->db->where('b.item_family_id', $filter_item_family);
        }
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#output_productions {border-collapse: collapse;width: 100%;font-size: 12px;}#output_productions td, #output_productions th {border: 1px solid #ddd;padding: 2px;}#output_productions tr:nth-child(even){background-color: #f2f2f2;}#output_productions tr:hover {background-color: #ddd;}#output_productions th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:m:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br>
            <div style="float: centet; font-size: 16px; text-align: center;">
                <h3>OUTPUT PRODUCTION</h3>
            </div>
        </center>
        
        <table id="output_productions" border="1">
            <tr>
                <th width="20">No</th>
                <th>Document No</th>
                <th>Period</th>
                <th>Trans Date</th>
                <th>Shift</th>
                <th>Product ID</th>
                <th>Product Number</th>
                <th>Product Name</th>
                <th>Lot No</th>
                <th>Work Order No</th>
                <th>Qty</th>
                <th>Qty WIP</th>
                <th>Machine No</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['number'] . '</td>
                    <td>' . $data['period'] . '</td>
                    <td>' . $data['trans_date'] . '</td>
                    <td>' . $data['shift'] . '</td>
                    <td>' . $data['item_fg_id'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                    <td>' . $data['item_fg_name'] . '</td>
                    <td>' . $data['lot_no'] . '</td>
                    <td>' . $data['wo_no'] . '</td>
                    <td>' . $data['qty'] . '</td>
                    <td>' . $data['qty_wip'] . '</td>
                    <td>' . $data['machine_number'] . '</td>
                    <td>' . $data['remarks'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_note($document_no)
    {
        $scan_rm_transfer_total = $this->crud->reads('scan_rm_transfer', [], ["document_no" => base64_decode($document_no)]);
        $scan_rm_transfers = $this->crud->read('scan_rm_transfer', [], ["document_no" => base64_decode($document_no)]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        $signatures = $this->db->get('signatures')->row();

        $approval = $this->crud->read('approvals', [], ["table_name" => "scan_rm_transfer"]);
        $user_1 = $this->crud->read('users', [], ["username" => $approval->user_approval_1]);

        if (!empty($approval->user_approval_2)) {
            $user_2 = $this->crud->read('users', [], ["username" => $approval->user_approval_2]);
        } else {
            $user_2 = (object) ["name" => ""];
        }
        
        if (!empty($approval->user_approval_3)) {
            $user_3 = $this->crud->read('users', [], ["username" => $approval->user_approval_3]);
        } else {
            $user_3 = (object) ["name" => ""];
        }
        
        
        if($scan_rm_transfers->approved == 0){
            $users_1 = '';
            $users_2 = '';
            $users_3 = '';
        } elseif ($scan_rm_transfers->approved == 1) {
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '';
            $users_2 = '';
            $users_3 = '';
        } elseif ($scan_rm_transfers->approved == 2) {
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '';
            $users_3 = '';
        } elseif ($scan_rm_transfers->approved == 3) {
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="80"/>';
            $users_3 = '';
        } else {
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '<img src="' . base_url('assets/image/qrcode/' . $user_2->name . '.png') . '" width="80"/>';
            $users_3 = '<img src="' . base_url('assets/image/qrcode/' . $user_3->name . '.png') . '" width="80"/>';
        }
        
        
        //Config Page
        $rows = 8;
        $page = ceil(count($scan_rm_transfer_total) / $rows);
        //Generate QRcode
        $this->createQrcode($scan_rm_transfers->document_no, "assets/image/qrcode/");
        $this->createQrcode($user_3->name, "assets/image/qrcode/");
        $this->createQrcode($user_2->name, "assets/image/qrcode/");
        $this->createQrcode($user_1->name, "assets/image/qrcode/");
        $this->createQrcode($this->session->name, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $scan_rm_transfers->document_no . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>
                        body {
                            font-family: Arial, Helvetica, sans-serif;
                        }
                        #customers {
                            border-collapse: collapse;width: 100%;
                            font-size: 12px;
                        }
                        #customers td, #customers th {
                            border: 1px solid black;padding: 2px;
                        }
                        #customers th {
                            padding-top: 2px;
                            padding-bottom: 2px;
                            text-align: center;color: black;
                        }
                        @media screen {
                            .print {
                                display: none !important;
                            }
                        }
                        @media print {
                            .noprint {
                                display: none !important;
                            }
                        }
                    </style>
                    <body>
                        <div style="margin:20%;" class="noprint">
                            <center>
                                <h1>Press CTRL + P for Print</h1>
                            </center>
                        </div>
                        <div class="print">';
        //Loop Page
        $no = 1;
        $hal = 1;
        $subtotal = 0;
        $judul = "WAREHOUSE TRANSFERRED NOTE"; 
        $form_iso = $config_iso->form_scan_rm_transferred;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select("a.*, b.number as item_number, b.name as item_name, b.uom, c.name as family_name, d.name as category_name, SUM(a.qty) as qtys");
            $this->db->from('scan_rm_transfer a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id', 'left');
            $this->db->join('item_familys c', 'b.item_family_id = c.id', 'left');
            $this->db->join('item_categories d', 'b.item_category_id = d.id', 'left');
            $this->db->where('a.document_no', base64_decode($document_no));
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(8, ($i * 8));
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('a.id', 'ASC');

            $records = $this->db->get()->result_array();

            if ($scan_rm_transfers->updated_date != null) {
                $revision_date = $scan_rm_transfers->updated_date;
            } else {
                $revision_date = $scan_rm_transfers->created_date;
            }

            // foreach ($records as $row) {
            //     // if ($row['maker'] !== '' && $row['objective'] == 'maker' ) {
            //     //     $to = @$makers->name;
            //     //     $attention = @$makers->contact_person;
            //     // }else if ($row['objective'] == 'supplier' ){
            //     //     $to = @$supplier->name;
            //     //     $attention = @$supplier->contact_person;
            //     // }else{
            //     //     $to = @$supplier->name;
            //     //     $attention = @$supplier->contact_person;
            //     // }

            //     // $from = $row['transfer_from'];
            //     // $to = $row['transfer_to'];
            // }

            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10">
                                    <img src="' . $config->favicon . '" width="60" />
                                </th>
                                <td width="250" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->address . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
                                        <tr>
                                            <td>Form</td>
                                            <td>:</td>
                                            <td>' . $form_iso . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print Date</td>
                                            <td>:</td>
                                            <td>' . date("Y-m-d H:i") . '</td>
                                        </tr>
                                        <tr>
                                            <td>Print By</td>
                                            <td>:</td>
                                            <td>' . $this->session->name . '</td>
                                        </tr>
                                    </table>
                                </th>
                            </tr>
                        </table>
                        <div style="border: 1px solid black; width:100%;">
                            <div style="padding:10px;">
                                <center>
                                    <br>
                                    <h3 style="margin:0;"><u>'.$judul.'</u></h3>
                                    <small>NO : ' . @$scan_rm_transfers->document_no . '</small>
                                </center>
                                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                    <tr>
                                        <td width="80">From</td>
                                        <td width="10">:</td>
                                        <td width="30%"><b>' . @$scan_rm_transfers->transfer_from . '</b></td>
                                        <td style="text-align:right; padding-right: 20px;" rowspan="7">
                                            Page <b>' . $hal  . '</b> of <b> ' . $page . '</b><br><br>
                                            Transaction Date:<br><b>' . date("d F Y", strtotime($scan_rm_transfers->transaction_date)) . '</b><br>
                                            Ship By:<br><b>' . $scan_rm_transfers->ship_by . '</b><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50">To</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$scan_rm_transfers->transfer_to . '</b></td>
                                    </tr>
                                    <tr>
                                        <td width="50">Doc No</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$scan_rm_transfers->document_no . '</b></td>
                                    </tr>
                                </table>
                                <table id="customers">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" width="30" style="text-align:center;">No</th>
                                            <th rowspan="2" width="150" style="text-align:center;">Part No</th>
                                            <th rowspan="2" width="150" style="text-align:center;">Part Name</th>
                                            <th rowspan="2" width="100" style="text-align:center;">Category</th>
                                            <th rowspan="2" width="100" style="text-align:center;">Product Family</th>
                                            <th rowspan="2" width="80" style="text-align:center;">Uom</th>
                                            <th rowspan="2" width="80" style="text-align:center;">Qty</th>
                                            <th rowspan="2" width="100" style="text-align:center;">Remarks</th>
                                        </tr>
                                    </thead>';
            $row = 0;
            foreach ($records as $record) {
                $html .= '  
                            <tr>    
                                <td style="text-align:center;">' . $no . '</td>
                                <td style="text-align:center;">' . $record['item_number'] . '</td>
                                <td style="text-align:center;">' . $record['item_name'] . '</td>
                                <td style="text-align:center;">' . $record['category_name'] . '</td>
                                <td style="text-align:center;">' . $record['family_name'] . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . number_format($record['qtys'], 2) . '</td>
                                <td style="text-align:center;">' . $record['remarks'] . '</td>
                            </tr>';
                $row++;
                $no++;
            }
            if (($i + 1) == $page) {

                // $this->db->select('a.remarks, b.number as item_number, b.name as item_name');
                // $this->db->from('request_materials a');
                // $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                // $this->db->where('a.deleted', 0);
                // $this->db->where('a.memo_no', base64_decode($memo_no));
                // $this->db->order_by('b.number', 'asc');
                // $remarks = $this->db->get()->result_array();

                // $note_content = []; // Menampung remarks yang valid

                // foreach ($remarks as $remark) {
                //     if (!empty($remark['remarks'])) {
                //         $note_content[] = $remark['item_number'] . " &nbsp; (" . $remark['remarks'] . ")";
                //     }
                // }

                // $html .= '  <tr>
                //             <td style="vertical-align: top; text-align:left; height:80px;" colspan="9" rowspan="8">
                //                 <b>Note :</b> <br>' . implode('<br>', $note_content) . '
                //             </td>
                //         </tr>';
                        
            } else {
                $html .= '</table>';
            }
            if (($i + 1) != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            } else{
                // Memindahkan informasi approval ke sini
                $html .= '<div style="width:100%; display: grid; grid-template-columns: auto auto auto;">
                <div style="width:40%; position: absolute; right: 50px;">
                    <table id="customers" style="margin-top:20px;">
                        <tr>
                            <th width="200" style="text-align:center;">Approved By</th>
                            <th width="200" style="text-align:center;">Checked By</th>
                            <th width="200" style="text-align:center;">Prepared By</th>
                        </tr>
                        <tr>
                            <th style="height:100px;">'. $users_2. '</th>
                            <th style="height:100px;">'. $users_1. '</th>
                            <th style="height:100px;">'. $users_0. '</th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;">' . $user_2->name . '</th>
                            <th style="height:20px; text-align:center;">' . $user_1->name . '</th>
                            <th style="height:20px; text-align:center;">' . $this->session->name . '</th>
                        </tr>
                    </table>
                        <div style="text-align:left; font-size: 15px; margin-top: 20px; border: none;">
                            <i>Electronic Auto Generating Approval No Need Signature</i>
                        </div>
                </div>
            </div>

            </div>';
            }
            $hal++;
        }
        $html .= '<script>window.print()</script>';
        die($html);
    }
}
