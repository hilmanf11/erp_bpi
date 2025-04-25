<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Checksheets extends CI_Controller
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
        // $this->form_validation->set_rules('wo_no', 'Wo No', 'required|min_length[1]|max_length[30]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/checksheets');
        } else {
            redirect('error_access');
        }
    }

    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('checksheets', ["name" => $post]);
        echo json_encode($send);
    }

    public function readRepairNo()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT a.document_no as wo_no, '-' as `period`, b.name as product_name , b.number as product_no, c.lot_no, b.id as item_fg_id, a.qty, a.division
            FROM repair_of_goods a
            JOIN item_fg b on a.item_fg_id = b.id 
            LEFT JOIN scan_repair_of_goods c on a.document_no = c.document_no 
            WHERE a.status = 0 and b.number like '%$post%'
            GROUP BY a.document_no
            ORDER BY b.number DESC");
        echo json_encode($send);
    }

    // public function readLotno($wo_no)
    // {
    //     $post = isset($_POST['q']) ? $_POST['q'] : "";
    //     $wo_no = base64_decode($wo_no);
    //     $send = $this->crud->query("SELECT DISTINCT b.item_fg_id, a.name as product_name , a.number as product_no, b.lot_no , c.qty, c.document_no as wo_no
    //     FROM item_fg a  
    //     LEFT JOIN scan_repair_of_goods b on a.id = b.item_fg_id  
    //     LEFT JOIN repair_of_goods c on b.item_fg_id = c.item_fg_id  
    //     WHERE b.document_no like '%$post%'and b.document_no = '$wo_no'
    //     ORDER BY b.document_no DESC");
    //     echo json_encode($send);
    // }

    public function readWoNo()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        
        // Query pertama: mengambil data dari production_schedules
        $query1 = "SELECT DISTINCT 
                        a.wo_no AS wo_no, 
                        a.period AS period, 
                        a.qty AS qty, 
                        a.lot_no as lot_no, 
                        a.item_fg_id AS item_fg_id, 
                        a.item_fg_name AS product_name, 
                        b.number AS product_no,
                        a.division as division
                FROM production_schedules a
                JOIN item_fg b ON a.item_fg_id = b.id
                WHERE a.status = 0 
                AND a.wo_no != '' 
                AND b.type !='SA'
                AND b.number LIKE '%$post%' or a.lot_no LIKE '%$post%' or a.wo_no LIKE '%$post%' or a.period LIKE '%$post%' 
                ORDER BY b.number DESC";

        // Query kedua: mengambil data dari purchase_order_receipts
        $query2 = "SELECT DISTINCT 
                        a.receipt_no AS wo_no, 
                        '-' AS period, 
                        COALESCE(a.qty_receipt, 0) AS qty,
                        '-' AS lot_no,
                        c.id AS item_fg_id, 
                        c.name AS product_name, 
                        COALESCE(c.number, '') AS product_no,
                        '-' AS division
                FROM purchase_order_receipts a
                JOIN item_rm b ON a.item_rm_id = b.id
                JOIN item_fg c ON b.number = c.number
                LEFT JOIN item_familys d ON b.item_family_id = d.id
                LEFT JOIN item_categories e ON b.item_category_id = e.id  
                WHERE a.status = 0 
                AND ((e.id = 'C07' AND d.id = 'P29') OR (e.id = 'C01' AND d.id = 'P27')) 
                AND a.receipt_no LIKE '%$post%'
                ORDER BY c.number ASC";

        // Menggabungkan hasil dari kedua query
        $send = $this->crud->query("($query1) UNION ALL ($query2)");

        echo json_encode($send);
    }
    
    public function readWoNos()
    {
        $send = $this->crud->query("SELECT DISTINCT wo_no
        FROM checksheets
        WHERE `deleted` = 0
        ORDER BY wo_no DESC");
        echo json_encode($send);
    }

    public function readChecksheet()
    {
        $send = $this->crud->query("SELECT DISTINCT `number`
        FROM checksheets
        WHERE `deleted` = 0
        ORDER BY `number` DESC");
        echo json_encode($send);
    }

    public function readItems($item_fg_id)
    {
        $item_id = base64_decode($item_fg_id);
        $send = $this->crud->query("SELECT mpq, qty_box
            FROM item_fg a
            WHERE `status` = 0 and id = '$item_id' 
            ORDER BY id DESC");
        echo json_encode($send);
    }

    public function checkWo_no($wo_no)
    {
        $wono = base64_decode($wo_no);
        $send = $this->crud->query("SELECT COALESCE(SUM(receipt),0) as qty
            FROM checksheets
            WHERE wo_no = '$wono' 
            ORDER BY id DESC");
        echo json_encode($send);
    }

    // public function readEmployesOP(){
    //     $ch = curl_init(); 
    //     curl_setopt($ch, CURLOPT_URL, "http://hrbpi.hris-server.com/api/master/employees/operator");
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     $output = curl_exec($ch);
    //     curl_close($ch);
    //     echo $output;
    // }

    // public function readEmployesQC(){
    //     $ch = curl_init(); 
    //     curl_setopt($ch, CURLOPT_URL, "http://hrbpi.hris-server.com/api/master/employees/qc");
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     $output = curl_exec($ch);
    //     curl_close($ch);
    //     echo $output;
    // }

    public function readEmployesOP()
    {
        $send = $this->crud->query("SELECT nik, name
            FROM employees a
            WHERE `status` = 0 and position = 'Operator' 
            ORDER BY id DESC");
        echo json_encode($send);
    }

    public function readEmployesQC()
    {
        $send = $this->crud->query("SELECT nik, name
            FROM employees a
            WHERE `status` = 0 and position = 'Qc' 
            ORDER BY id DESC");
        echo json_encode($send);
    }

    // public function readWorkorder($filter = "")
    // {
    //     if ($filter == "") {
    //         $join = "LEFT JOIN checksheets b ON a.wo_no = b.wo_no";
    //         $having = "having (a.qty - SUM(coalesce(b.receipt, 0))) > 0";
    //     } else {
    //         $join = "JOIN checksheets b ON a.wo_no = b.wo_no";
    //         $having = "";
    //     }

    //     $post = isset($_POST['q']) ? $_POST['q'] : "";
    //     $send = $this->crud->query("SELECT a.*, d.number as product_no, d.name as product_name, coalesce(SUM(b.receipt), 0) as accumulate, (a.qty - SUM(coalesce(b.receipt, 0))) as balance FROM production_schedules a 
    //     $join
    //     JOIN item_fg d ON a.item_fg_id = d.id 
    //     WHERE a.status = '1' and a.workorder like '%$post%'
    //     GROUP BY a.workorder
    //     $having
    //     order by a.workorder desc");
    //     echo json_encode($send);
    // }

    // Dokumentasi : Fungsi Create tanpa generate Number Backend
    // public function checksheet_id($trans_date)
    // {
    //     $trans_date = base64_decode($trans_date);
    //     $datenow = date("Y-m-d", strtotime($trans_date));
    //     $datenow2 = date("Ymd", strtotime($trans_date));

    //     $sqlGetID = $this->db->query("SELECT max(`number`) as kode FROM checksheets WHERE trans_date like '%$datenow%'");
    //     $rowID = $sqlGetID->row();
    //     $kode = $rowID->kode;

    //     // var_dump($kode);
        
    //     if ($kode == NULL) {
    //         $autoID = sprintf("%05s", $kode + 1);
    //     } else {
    //         $urutan = (int) substr($kode, -4);
    //         $urutan++;
    //         $autoID = sprintf("%05s", $urutan);
    //     }
    //     echo "CS" . $datenow2 . "-" . $autoID;
    // }

    private function generate_checksheet_id($trans_date)
    {
        $datenow = date("Y-m-d", strtotime($trans_date));
        $datenow2 = date("Ymd", strtotime($trans_date));

        $sqlGetID = $this->db->query("SELECT MAX(`number`) AS kode FROM checksheets WHERE trans_date = '$datenow'");
        $rowID = $sqlGetID->row();
        $kode = $rowID->kode;

        if ($kode == NULL) {
            $autoID = sprintf("%05s", 1);
        } else {
            $urutan = (int) substr($kode, -4);
            $urutan++;
            $autoID = sprintf("%05s", $urutan);
        }

        return "CS" . $datenow2 . "-" . $autoID;
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_from = $this->input->get('filter_from');
            $filter_to = $this->input->get('filter_to');
            $filter_wo_no = $this->input->get('filter_wo_no');
            $filter_checksheet = $this->input->get('filter_checksheet');
            $filter_shift = $this->input->get('filter_shift');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');
            $filter_division = $this->input->get('filter_division');
            $filter_status = $this->input->get('filter_status');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            $sort = $this->input->post('sort') ?? 'a.created_date';
            $order = $this->input->post('order') ?? 'desc';

            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*, c.number as product_no, c.name as product_name, c.id as product_id, c.uom, d.document_no,
            COALESCE(COUNT(e.status),0) as total_scan');
            $this->db->from('checksheets a');
            $this->db->join('production_schedules b', 'a.wo_no = b.wo_no','left');
            $this->db->join('item_fg c', 'a.item_fg_id = c.id','left');
            $this->db->join('wip_receipts d', 'a.number = d.checksheet_number','left');
            $this->db->join('scan_item_receipts_fg e', 'a.number = e.checksheet_number','left');
            $this->db->where('a.deleted', 0);
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.trans_date >=', $filter_from);
                $this->db->where('a.trans_date <=', $filter_to);
            }
            if ($filter_division != "") {
                $this->db->where('a.division', $filter_division);
            }
            if ($filter_status != "") {
                $this->db->where('a.status', $filter_status);
            }
            if ($filter_wo_no != "") {
                $this->db->where('a.wo_no', $filter_wo_no);
            }
            if ($filter_checksheet != "") {
                $this->db->where('a.number', $filter_checksheet);
            }
            if ($filter_shift != "") {
                $this->db->where('a.shift', $filter_shift);
            }
            if ($filter_item_fg_id != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }
            // $this->db->like('a.status', $filter_status);
            // $this->db->like('a.wo_no', $filter_wo_no);
            // $this->db->like('a.number', $filter_checksheet);
            // $this->db->like('a.shift', $filter_shift);
            // $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->group_by('a.number');
            $this->db->order_by($sort, $order);
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

     //UPDATE DATA
    // public function update()
    // {
    //     if ($this->input->post()) {
    //         $id   = $this->input->get('id');
    //         $post = $this->input->post();
    //         $send = $this->crud->update('checksheets', ["id" => $id], $post);
    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    // Dokumentasi : Fungsi Create tanpa generate Number Backend
    // public function create()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post();

    //         // var_dump($post['accumulate']);
    //         // var_dump($post['qty']);
    //         // die;

    //         if ($post['receipt'] > 0) {
    //             // $checksheet_id = $this->checksheet_id($post['trans_date']);
    //             $checksheet = $this->crud->reads("checksheets", [], ["wo_no" => $post['wo_no'], "trans_date" => $post['trans_date'], "shift" => $post['shift'], "accumulate" => $post['accumulate']]);
    //             if (count($checksheet) == 0) {
    //                 $send = $this->crud->create('checksheets',$post);
    //                 if(($post['accumulate']) == $post ['qty']){
    //                     $update = $this->crud->update('production_schedules', ["wo_no" => $post['wo_no'], "item_fg_id" => $post['item_fg_id']], ["status" => 1]);
    //                 }
    //                 echo $send;
    //             } else {
    //                 show_error("Duplicate Data");
    //             }
    //         } else {
    //             show_error("Receipt Qty cannot <= 0");
    //         }
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            if ($post['receipt'] > 0) {
                // Cek duplikat berdasarkan data WO, tanggal, shift & accumulate
                $checksheet = $this->crud->reads("checksheets", [], [
                    "wo_no" => $post['wo_no'],
                    "trans_date" => $post['trans_date'],
                    "shift" => $post['shift'],
                    "accumulate" => $post['accumulate']
                ]);

                if (count($checksheet) == 0) {
                    // Loop sampai dapat nomor unik
                    $checksheet_number = "";
                    do {
                        $checksheet_number = $this->generate_checksheet_id($post['trans_date']);
                        $exist = $this->db->get_where("checksheets", ["number" => $checksheet_number])->row();
                    } while ($exist);

                    $post['number'] = $checksheet_number;

                    $send = $this->crud->create('checksheets', $post);

                    if ($post['accumulate'] == $post['qty']) {
                        $update = $this->crud->update('production_schedules', [
                            "wo_no" => $post['wo_no'],
                            "item_fg_id" => $post['item_fg_id']
                        ], ["status" => 1]);
                    }

                    echo json_encode([
                        'theme' => 'success',
                        'message' => 'Data berhasil disimpan',
                        'title' => 'Success',
                        'checksheet_number' => $checksheet_number
                    ]);
                } else {
                    echo json_encode([
                        'theme' => 'error',
                        'message' => 'Duplicate Data',
                        'title' => 'Duplicate'
                    ]);
                }
            } else {
                echo json_encode([
                    'theme' => 'error',
                    'message' => 'Receipt Qty cannot <= 0',
                    'title' => 'Invalid'
                ]);
            }
        } else {
            echo json_encode([
                'theme' => 'error',
                'message' => 'Cannot process your request',
                'title' => 'Error'
            ]);
        }
    }

    public function create_label()
    {
        $post = $this->input->post();
        @$checksheet_number = $post['checksheet_number'];
        @$qty = $post['qty'];

        //Read Label ID
        $sqlGetID = $this->db->query("SELECT max(checksheet_label) as kode FROM wip_receipt_labels WHERE checksheet_number = '$checksheet_number'");
        $rowID = $sqlGetID->row();
        $label = $rowID->kode;
        if ($label == NULL) {
            $autoID = $checksheet_number .  sprintf("%03s", $label + 1);
        } else {
            $urutan = (int) substr($label, -3);
            $autoID = $checksheet_number . sprintf("%03s", $urutan + 1);
        }

        //Simpan Label
        $arrLabel = [
            "checksheet_number" => $checksheet_number,
            "checksheet_label" => $autoID,
            "qty" => $qty
        ];

        $send = $this->crud->create('wip_receipt_labels', $arrLabel);
        die($send);
    }

    public function create_label_box()
    {
        $post = $this->input->post();
        $checksheet_number = $post['checksheet_number'];
        $qty = $post['qty'];

        //Read Label ID
        $sqlGetID = $this->db->query("SELECT max(checksheet_label) as kode FROM wip_receipt_boxs WHERE checksheet_number = '$checksheet_number'");
        $rowID = $sqlGetID->row();
        $label = $rowID->kode;
        if ($label == NULL) {
            $autoID = "B" . $checksheet_number .  sprintf("%03s", $label + 1);
        } else {
            $urutan = (int) substr($label, -3);
            $autoID = "B" . $checksheet_number . sprintf("%03s", $urutan + 1);
        }

        //Simpan Label
        $arrLabel = [
            "checksheet_number" => $checksheet_number,
            "checksheet_label" => $autoID,
            "qty" => $qty
        ];

        $send = $this->crud->create('wip_receipt_boxs', $arrLabel);
        die($send);
    }

    public function save_reprint_reason() {
        $number = $this->input->post('number');
        $reason = $this->input->post('reason');
    
        // Update reason di tabel checksheets di mana checksheet_number = number
        $this->db->set('reason', $reason);
        $this->db->where('number', $number);
        $this->db->update('checksheets');
    
        if ($this->db->affected_rows() > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update reason']);
        }
    }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('checksheets', ["id" => $data['id']]);
        $update = $this->crud->update('production_schedules', ["wo_no" => $data['wo_no'], "item_fg_id" => $data['item_fg_id']], ["status" => 0]);
        $delete = $this->crud->delete('wip_receipts', ["item_fg_id" => $data['item_fg_id'],"checksheet_number" => $data['number']]);
        echo $send;
    }

    public function closeFc()
    {
        $id = $this->input->post('id');
        $remark = $this->input->post('remark');
        $update = $this->db->update('checksheets', ["status" => 1, "remarks" => $remark], ["id" => $id]);// , "qty" => 0
        // echo $update;

         // Berikan respon sesuai hasil
        if ($update) {
            echo json_encode(["success" => true,"message" => "Checksheets closed successfully."]);
        } else {
            echo json_encode(["success" => false,"message" => "Failed to close Checksheets."]);
        }
    }

    public function openFc()
    {
        // $po_no = $this->input->post('po_no');
        $id = $this->input->post('id');
        $remark = $this->input->post('remark');
        $update = $this->db->update('checksheets', ["status" => 0, "remarks" => $remark], ["id" => $id]);// , "qty" => 0

        if ($update) {
            echo json_encode(["success" => true,"message" => "Checksheets open successfully."]);
        } else {
            echo json_encode(["success" => false,"message" => "Failed to open Checksheets."]);
        }
    }

    // public function print_label($id)
    // {
    //     $id = base64_decode($id);

    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();
    //     $config_iso = $this->db->get('config_iso')->row();

    //     $this->db->select('a.*, c.number as product_no, c.name as product_name, c.uom');
    //     $this->db->from('checksheets a');
    //     $this->db->join('production_schedules b', 'a.wo_no = b.wo_no');
    //     $this->db->join('item_fg c', 'b.item_fg_id = c.id');
    //     // $this->db->join('uom e', 'c.uom_id = e.id');
    //     $this->db->where('a.deleted', 0);
    //     $this->db->where('a.id', $id);
    //     $this->db->order_by('a.trans_date', 'DESC');
    //     $checksheet = $this->db->get()->row();

    //     //Generate QRcode
    //     $this->createQrcode($checksheet->number, "assets/image/qrcode/");

    //     $html = '<html>
    //     <head>
    //         <title>' . $checksheet->number . '</title>
    //         <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
    //     </head>
    //     <style>
    //         body {
    //             font-family: Arial, Helvetica, sans-serif;
    //         }
    //         #customers {
    //             border-collapse: collapse;width: 100%;
    //             font-size: 12px;
    //         }
    //         #customers td, #customers th {
    //             border: 1px solid black;padding: 2px;
    //         }
    //         #customers th {
    //             padding-top: 2px;
    //             padding-bottom: 2px;
    //             text-align: center;color: black;
    //         }
    //         @media screen {
    //             .print {
    //                 display: none !important;
    //             }
    //         }

    //         @media print {
    //             .noprint {
    //                 display: none !important;
    //             }
    //         }
    //     </style>
    //     <body>
    //         <div style="margin:20%;" class="noprint">
    //             <center>
    //                 <h1>Press CTRL + P for Print</h1>
    //                 <p>Paper Size A5, Layout Potrait</p>
    //                 <p>Margin Default, Scale 98</p>
    //             </center>
    //         </div>
    //         <div class="print">
    //             <div style="float: left; font-size: 12px; text-align: left;">
    //                 <table style="width: 100%;">
    //                     <tr>
    //                         <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
    //                             <img src="' . $config->favicon . '" width="30">
    //                         </td>
    //                         <td style="font-size: 14px; text-align: left; margin:2px;">
    //                             <b>' . $config->name . '</b><br>
    //                             <span style="font-size:10px;">' . $config->address . '</span><br>
    //                         </td>
    //                     </tr>
    //                 </table>
    //             </div>
    //             <div style="float: right; font-size: 12px; text-align: right;">
    //                 <table style="width:100%; font-size:10px;">
    //                     <tr>
    //                         <td width="80">Doc No</td>
    //                         <td width="5">:</td>
    //                         <td width="100">' . $config_iso->doc_checksheet . '</td>
    //                     </tr>
    //                     <tr>
    //                         <td>Form</td>
    //                         <td>:</td>
    //                         <td>' . $config_iso->form_checksheet . '</td>
    //                     </tr>
    //                     <tr>
    //                         <td>Print Date</td>
    //                         <td>:</td>
    //                         <td>' . date("d M Y H:m:s") . '</td>
    //                     </tr>
    //                     <tr>
    //                         <td>Print By</td>
    //                         <td>:</td>
    //                         <td>' . $this->session->username . '</td>
    //                     </tr>
    //                 </table>
    //             </div>

    //             <br><br><br><br>
    //             <center>
    //                 <h3 style="margin:0;"><u>FINAL CHECK SHEET</u></h3>
    //                 <b style="font-size:12px;">Doc. No ' . $checksheet->number . '</b>
    //             </center>
    //             <br>
    //             <div style="float:left; width:80%;"> 
    //                 <table style="width:100%; font-size:12px;">
    //                     <tr>
    //                         <td width="100" style="padding:5px;">Date</td>
    //                         <td width="20">:</td>
    //                         <td><b>' . date("d F Y", strtotime($checksheet->trans_date)) . '</b></td>
    //                     </tr>
    //                     <tr>
    //                         <td style="padding:5px;">Product No</td>
    //                         <td>:</td>
    //                         <td><b>' . $checksheet->product_no . '</b></td>
    //                     </tr>
    //                     <tr>
    //                         <td style="padding:5px;">Product Name</td>
    //                         <td>:</td>
    //                         <td><b>' . $checksheet->product_name . '</b></td>
    //                     </tr>
    //                     <tr>
    //                         <td style="padding:5px;">WO. No</td>
    //                         <td>:</td>
    //                         <td><b>' . $checksheet->wo_no . '</b></td>
    //                     </tr>
    //                     <tr>
    //                         <td style="padding:5px;">WO. Qty</td>
    //                         <td>:</td>
    //                         <td><b>' . $checksheet->qty . '</b></td>
    //                     </tr>
    //                     <tr>
    //                         <td style="padding:5px;">Receipt Qty</td>
    //                         <td>:</td>
    //                         <td><b>' . $checksheet->receipt . '</b></td>
    //                     </tr>
    //                     <tr>
    //                         <td style="padding:5px;">Accumulate</td>
    //                         <td>:</td>
    //                         <td><b>' . $checksheet->accumulate . '</b></td>
    //                     </tr>
    //                     <tr>
    //                         <td style="padding:5px; vertical-align:top;">Remarks</td>
    //                         <td style="vertical-align:top;">:</td>
    //                         <td style="vertical-align:top;"><b>' . $checksheet->remarks . '</b></td>
    //                     </tr>
    //                 </table>
    //             </div>
    //             <div style="float:left; width:20%; text-align:center;">
    //                 <img src="' . base_url('assets/image/qrcode/' . $checksheet->number . '.png') . '" width="100"/>
    //             </div>
    //             <table id="customers" style="margin-top:20px;">
    //                 <tr>
    //                     <th width="200" style="text-align:center;">Production</th>
    //                     <th width="200" style="text-align:center;">QC</th>
    //                     <th width="200" style="text-align:center;">DC</th>
    //                 </tr>
    //                 <tr>
    //                     <th style="height:80px;"></th>
    //                     <th style="height:80px;"></th>
    //                     <th style="height:80px;"></th>
    //                 </tr>
    //                 <tr>
    //                     <th style="height:20px; text-align:center;"></th>
    //                     <th style="height:20px; text-align:center;"></th>
    //                     <th style="height:20px; text-align:center;"></th>
    //                 </tr>
    //             </table>
    //         </div>
    //         <script>window.print()</script>
    //     </body>
    // </html>';
    //     echo $html;
    // }

    public function print_label($checksheet_number)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $config_iso = $this->db->get('config_iso')->row();

        $checksheet_number = base64_decode($checksheet_number);
        //Cek Label
        $this->db->select('d.number_customer as item_number_customer, d.number as item_number, d.name as item_name, d.alias, a.qty, a.checksheet_label, 
        b.trans_date, b.prod_date, b.shift, d.control_id, d.logo, d.uom, 
        (CASE 
            WHEN b.lot_no IS NULL or b.lot_no = "" THEN c.lot_no 
            ELSE b.lot_no 
        END) as lot_no, 
        b.qc_1, b.qc_2, b.op_1, b.op_2, b.qcnumber_1, b.qcnumber_2, b.opnumber_1, b.opnumber_2, h.location'); // d.description,

        $this->db->from('wip_receipt_labels a');
        $this->db->join('checksheets b', 'a.checksheet_number = b.number');
        $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
        $this->db->join('item_fg d', 'b.item_fg_id = d.id');
        // $this->db->join('uom e', 'd.uom_id = e.id');
        // $this->db->join('wip_receipts f', 'a.checksheet_number = f.checksheet_number');
        $this->db->join('warehouse_location_items g', 'd.id = g.item_fg_id', 'left');
        $this->db->join('warehouse_locations h', 'g.location = h.location', 'left');
        // $this->db->join('warehouse_location_items g', 'd.id = g.item_rm_id', 'left');
        // $this->db->join('customer_items h', 'h.customer_id = c.customer_id and d.id = h.item_fg_id', 'left');
        // $this->db->join('customers i', 'i.id = h.customer_id', 'left');
        // $this->db->join('sales_orders j', 'c.so_number = j.sales_order_no', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.checksheet_number', $checksheet_number);
        $wip_receipt_labels = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $checksheet_number . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($wip_receipt_labels) {
            //$html .= '<div style="width: 120mm;">';
            $no = 1;
            foreach ($wip_receipt_labels as $wip_receipt_label) {
                // if ($no == 3) {
                //     $no = 1;
                // }
                // if ($no == 1) {
                //     $padding = "padding:0 3mm 1mm 0mm;";
                // } else {
                //     $padding = "padding:0 0mm 1mm 4mm;";
                // }

                if ($wip_receipt_label->logo == "0") {
                    $img_bpi = '<img style="width:50%;" src="' . base_url("assets/image/bpi_logo.png") . '" />';
                } else {
                    $img_bpi = '';
                }

                $qc1 = substr($wip_receipt_label->qc_1, 0, 3);
                $qcnumber1 = substr($wip_receipt_label->qcnumber_1, -3);
                $qc2 = substr($wip_receipt_label->qc_2, 0, 3);
                $qcnumber2 = substr($wip_receipt_label->qcnumber_2, -3);
                $op1 = substr($wip_receipt_label->op_1, 0, 3);
                $opnumber1 = substr($wip_receipt_label->opnumber_1, -3);
                $op2 = substr($wip_receipt_label->op_2, 0, 3);
                $opnumber2 = substr($wip_receipt_label->opnumber_2, -3);
                //Generate QRcode
                $qrcodes = $wip_receipt_label->checksheet_label;
                $this->createQrcode($qrcodes, "assets/image/qrcode/", $wip_receipt_label->checksheet_label);
                $html .= '  <div style="width: 75mm; max-height:100mm; border:1px solid black; margin-bottom:5px;">
                                <table id="customers" border="1" style="width: 100%; font-family: Arial, sans-serif; font-size: 10px; border-collapse: collapse;">
                                    <tr>
                                        <th colspan="4" style="font-size: 8px; text-align: right; border: none;"><b>' . $config_iso->doc_barcode_fg . '</b></th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" style="font-size: 15px; text-align: center; border: none;"><b>LABEL PACKING</b></th>
                                    </tr>
                                    <tr>
                                        <td style="width:5mm; height: 5mm; border: none; text-align: center;">' . $img_bpi . '</td>
                                        <td colspan="3" style="text-align:center; border: none;"><small style="font-size:12px;"><b>PT BANSHU PLASTIC INDONESIA</b></small></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Part No</small><br><b style="font-size:16px;">' . $wip_receipt_label->item_number . '</b>
                                        </td>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Lot No.</small><br><b style="font-size:12px;">' . $wip_receipt_label->lot_no . '</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Part Name</small><br><br><b style="font-size:12px;">' . $wip_receipt_label->item_name . '</b>
                                        </td>

                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Prod Date.</small><br><b style="font-size:12px;">' . $wip_receipt_label->prod_date . '</b>
                                            <br>
                                            <small style="font-size:10px;">Pack Date.</small><br><b style="font-size:12px;">' . $wip_receipt_label->trans_date . '</b>
                                        </td>
                                    </tr>
                                     <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Cust Code</small><br><b style="font-size:12px;">' . $wip_receipt_label->item_number_customer . '</b>
                                        </td>
                                        <td style="text-align:left; border: none;">
                                            <small style="font-size:10px;">Shift.</small><br>
                                            <div style="text-align:center;">
                                                <b style="font-size:12px;">' . $wip_receipt_label->shift . '</b>
                                            </div>
                                        </td>
                                         <td style="text-align:left; border: none;">
                                            <img src="' . base_url('assets/image/qc_passed.png') . '" width="30" style="float: center; margin-right: 5px; margin-top: 5px;">
                                        </td>
                                    </tr>
                                   <tr>
                                        <td style="width:15mm; height: 5mm; style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:12px;">Qty</small><br><b style="font-size:16px;">' . number_format($wip_receipt_label->qty, 2) . '</b>
                                        </td>

                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:12px;">Unit</small><br><b style="font-size:12px;">' . $wip_receipt_label->uom . '</b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Operator</small>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">QC</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Equivalent</small><br><b style="font-size:12px;"></b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <b style="font-size:10px;">' . $op1 . '</b>&nbsp<b style="font-size:10px;">' . $opnumber1 . '</b>
                                            <br>
                                            <b style="font-size:10px;">' . $op2 . '</b>&nbsp<b style="font-size:10px;">' . $opnumber2 . '</b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <b style="font-size:10px;">' . $qc1 . '</b>&nbsp<b style="font-size:10px;">' . $qcnumber1 . '</b>
                                            <br>
                                            <b style="font-size:10px;">' . $qc2 . '</b>&nbsp<b style="font-size:10px;">' . $qcnumber2 . '</b>
                                        </td>
                                    </tr>
                                   <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;"><small style="font-size:14px;"><b>' . $wip_receipt_label->checksheet_label . '</b></small>
                                        <br><br><small style="font-size:10px;"><b>' . $wip_receipt_label->location . '</b></small></td>
                                        <td colspan="2" style="text-align:center; border: 1px solid black;">
                                            <img src="' . base_url('assets/image/qrcode/' . $wip_receipt_label->checksheet_label . '.png') . '" width="90"/>
                                        </td>
                                    </tr>
                                </table>
                            </div>';
                $no++;
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }

    public function print_label_box($checksheet_number)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $config_iso = $this->db->get('config_iso')->row();

        $checksheet_number = base64_decode($checksheet_number);
        //Cek Label
        $this->db->select('d.number_customer as item_number_customer, d.number as item_number, d.name as item_name, d.alias, a.qty, a.checksheet_label, 
        b.trans_date, b.prod_date, b.shift, d.control_id, d.logo, d.uom, 
        (CASE 
            WHEN b.lot_no IS NULL or b.lot_no = "" THEN c.lot_no 
            ELSE b.lot_no 
        END) as lot_no, 
        b.qc_1, b.qc_2, b.op_1, b.op_2, b.qcnumber_1, b.qcnumber_2, b.opnumber_1, b.opnumber_2, h.location'); // d.description,    

        $this->db->from('wip_receipt_boxs a');
        $this->db->join('checksheets b', 'a.checksheet_number = b.number');
        $this->db->join('production_schedules c', 'b.wo_no = c.wo_no','left');
        $this->db->join('item_fg d', 'b.item_fg_id = d.id');
        // $this->db->join('uom e', 'd.uom_id = e.id');
        // $this->db->join('wip_receipts f', 'a.checksheet_number = f.checksheet_number');
        $this->db->join('warehouse_location_items g', 'd.id = g.item_fg_id', 'left');
        $this->db->join('warehouse_locations h', 'g.location = h.location', 'left');
        // $this->db->join('customer_items h', 'h.customer_id = c.customer_id and d.id = h.item_fg_id', 'left');
        // $this->db->join('customers i', 'i.id = h.customer_id', 'left');
        // $this->db->join('sales_orders j', 'c.so_number = j.sales_order_no', 'left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.status', 0);
        $this->db->where('a.checksheet_number', $checksheet_number);
        $wip_receipt_labels = $this->db->get()->result_object();

        $html = '<html>
                    <head>
                        <title>' . $checksheet_number . '</title>
                        <link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16">
                    </head>
                    <style>body {font-family: Arial, Helvetica, sans-serif; margin:5px;}#customers {border-collapse: collapse; width: 100%; font-size: 9px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>';
        if ($wip_receipt_labels) {
            //$html .= '<div style="width: 120mm;">';
            $no = 1;
            foreach ($wip_receipt_labels as $wip_receipt_label) {
                // if ($no == 3) {
                //     $no = 1;
                // }
                // if ($no == 1) {
                //     $padding = "padding:0 3mm 1mm 0mm;";
                // } else {
                //     $padding = "padding:0 0mm 1mm 4mm;";
                // }
                //Generate QRcode

                if ($wip_receipt_label->logo == "0") {
                    $img_bpi = '<img style="width:50%;" src="' . base_url("assets/image/bpi_logo.png") . '" />';
                } else {
                    $img_bpi = '';
                }

                $qc1 = substr($wip_receipt_label->qc_1, 0, 3);
                $qcnumber1 = substr($wip_receipt_label->qcnumber_1, -3);
                $qc2 = substr($wip_receipt_label->qc_2, 0, 3);
                $qcnumber2 = substr($wip_receipt_label->qcnumber_2, -3);
                $op1 = substr($wip_receipt_label->op_1, 0, 3);
                $opnumber1 = substr($wip_receipt_label->opnumber_1, -3);
                $op2 = substr($wip_receipt_label->op_2, 0, 3);
                $opnumber2 = substr($wip_receipt_label->opnumber_2, -3);
                $qrcodes = $wip_receipt_label->checksheet_label;
                $this->createQrcode($qrcodes, "assets/image/qrcode/", $wip_receipt_label->checksheet_label);
                $html .= '  <div style="width: 75mm; max-height:100mm; border:1px solid black; margin-bottom:5px;">
                                <table id="customers" border="1" style="width: 100%; font-family: Arial, sans-serif; font-size: 10px; border-collapse: collapse;">
                                    <tr>
                                        <th colspan="4" style="font-size: 8px; text-align: right; border: none;"><b>' . $config_iso->doc_barcode_fg . '</b></th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" style="font-size: 15px; text-align: center; border: none;"><b>LABEL BOX</b></th>
                                    </tr>
                                    <tr>
                                        <td style="width:5mm; height: 5mm; border: none; text-align: center;">' . $img_bpi . '</td>
                                        <td colspan="3" style="text-align:center; border: none;"><small style="font-size:12px;"><b>PT BANSHU PLASTIC INDONESIA</b></small></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Part No</small><br><b style="font-size:16px;">' . $wip_receipt_label->item_number . '</b>
                                        </td>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Lot No.</small><br><b style="font-size:12px;">' . $wip_receipt_label->lot_no . '</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Part Name</small><br><br><b style="font-size:12px;">' . $wip_receipt_label->item_name . '</b>
                                        </td>

                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Prod Date.</small><br><b style="font-size:12px;">' . $wip_receipt_label->prod_date . '</b>
                                            <br>
                                            <small style="font-size:10px;">Pack Date.</small><br><b style="font-size:12px;">' . $wip_receipt_label->trans_date . '</b>
                                        </td>
                                    </tr>
                                     <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Cust Code</small><br><b style="font-size:12px;">' . $wip_receipt_label->item_number_customer . '</b>
                                        </td>
                                        <td style="text-align:left; border: none;">
                                            <small style="font-size:10px;">Shift.</small><br>
                                            <div style="text-align:center;">
                                                <b style="font-size:12px;">' . $wip_receipt_label->shift . '</b>
                                            </div>
                                        </td>
                                         <td style="text-align:left; border: none;">
                                            <img src="' . base_url('assets/image/qc_passed.png') . '" width="30" style="float: center; margin-right: 5px; margin-top: 5px;">
                                        </td>
                                    </tr>
                                   <tr>
                                        <td style="width:15mm; height: 5mm; style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:12px;">Qty</small><br><b style="font-size:16px;">' . number_format($wip_receipt_label->qty, 2) . '</b>
                                        </td>

                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:12px;">Unit</small><br><b style="font-size:12px;">' . $wip_receipt_label->uom . '</b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Operator</small>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">QC</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;">
                                            <small style="font-size:10px;">Equivalent</small><br><b style="font-size:12px;"></b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <b style="font-size:10px;">' . $op1 . '</b>&nbsp<b style="font-size:10px;">' . $opnumber1 . '</b>
                                            <br>
                                            <b style="font-size:10px;">' . $op2 . '</b>&nbsp<b style="font-size:10px;">' . $opnumber2 . '</b>
                                        </td>
                                        <td style="text-align:left; border: 1px solid black;">
                                            <b style="font-size:10px;">' . $qc1 . '</b>&nbsp<b style="font-size:10px;">' . $qcnumber1 . '</b>
                                            <br>
                                            <b style="font-size:10px;">' . $qc2 . '</b>&nbsp<b style="font-size:10px;">' . $qcnumber2 . '</b>
                                        </td>
                                    </tr>
                                   <tr>
                                        <td colspan="2" style="text-align:left; border: 1px solid black;"><small style="font-size:14px;"><b>' . $wip_receipt_label->checksheet_label . '</b></small>
                                        <br><br><small style="font-size:10px;"><b>' . $wip_receipt_label->location . '</b></small></td>
                                        <td colspan="2" style="text-align:center; border: 1px solid black;">
                                            <img src="' . base_url('assets/image/qrcode/' . $wip_receipt_label->checksheet_label . '.png') . '" width="90"/>
                                        </td>
                                    </tr>
                                </table>
                            </div>';
                $no++;
            }
            $html .= '</div><script>window.print()</script>';
        } else {
            $html .= "<br><br><br><center><h3>Data not found or data has been scanned</h3></center>";
        }
        die($html);
    }

    public function print_label_cs($checksheet_number, $packing)
    {
        if($packing == 2){
            $this->print_label_box($checksheet_number);
        } else {
            $this->print_label($checksheet_number);
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=checksheets_$format.xls");
        }
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_wo_no = $this->input->get('filter_wo_no');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');
        $filter_division = $this->input->get('filter_division');
        $filter_status = $this->input->get('filter_status');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, c.number as product_no, c.name as product_name, c.uom');
        $this->db->from('checksheets a');
        $this->db->join('production_schedules b', 'a.wo_no = b.wo_no');
        $this->db->join('item_fg c', 'b.item_fg_id = c.id');
        // $this->db->join('uom e', 'c.uom_id = e.id');
        $this->db->where('a.deleted', 0);
        if ($filter_from != "" or $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }

        if ($filter_division != "") {
            $this->db->where('a.division', $filter_division);
        }

        if ($filter_status != "") {
            $this->db->where('a.status', $filter_status);
        }
        $this->db->like('a.wo_no', $filter_wo_no);
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->order_by('a.number', 'ASC');
        $this->db->order_by('a.wo_no', 'ASC');
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
                                <small>FINAL CHECKSHEET</small>
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
                    <th>Checksheet ID</th>
                    <th>Wo_No</th>
                    <th>Trans Date</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>UoM</th>
                    <th>Qty</th>
                    <th>Receipt</th>
                    <th>Accumulate</th>
                    <th>Balance</th>
                    <th>Prod Date</th>
                    <th>Packing Date</th>
                    <th>QC 1</th>
                    <th>QC 2</th>
                    <th>Operator 1</th>
                    <th>Operator 2</th>
                    <th>Shift</th>
                    <th>Packing</th>
                    <th>Packing Qty</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $data['number'] . '</td>
                            <td>' . $data['wo_no'] . '</td>
                            <td>' . $data['trans_date'] . '</td>
                            <td>' . $data['product_no'] . '</td>
                            <td>' . $data['product_name'] . '</td>
                            <td>' . $data['uom'] . '</td>
                            <td>' . number_format($data['qty']) . '</td>
                            <td>' . number_format($data['receipt']) . '</td>
                            <td>' . number_format($data['accumulate']) . '</td>
                            <td>' . number_format($data['balance']) . '</td>
                            <td>' . $data['prod_date'] . '</td>
                            <td>' . $data['packing_date'] . '</td>
                            <td>' . $data['qc_1'] . '</td>
                            <td>' . $data['qc_2'] . '</td>
                            <td>' . $data['op_1'] . '</td>
                            <td>' . $data['op_2'] . '</td>
                            <td>' . $data['shift'] . '</td>
                            <td>' . $data['packing'] . '</td>
                            <td>' . $data['packing_qty'] . '</td>
                        </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
