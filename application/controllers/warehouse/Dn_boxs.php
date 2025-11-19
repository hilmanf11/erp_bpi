<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Dn_boxs extends CI_Controller
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
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('warehouse/dn_boxs');
        } else {
            redirect('error_access');
        }
    }
  
    // public function readItemBox($customer_id, $plant, $box_list)
    // {
    //     $customer_id = base64_decode($customer_id);
    //     $plant = base64_decode($plant);
    //     $box_list = base64_decode($box_list);

    //     $customer_address = $this->crud->read("customer_address", [], ["plant" => $plant]);

    //     $post = isset($_POST['q']) ? $_POST['q'] : "";

    //     if ($box_list == 'All'){
    //         $records = $this->crud->query("SELECT id, color, name, uom , size, code
    //         FROM item_boxs 
    //         WHERE  `size` like '%$post%' or `name` like '%$post%'
    //         ORDER BY `name` ASC");
    //     }else{
    //         $records = $this->crud->query("SELECT a.id, a.color, a.name, a.uom , a.size, a.code
    //         FROM item_boxs a
    //         LEFT JOIN item_fg b ON a.name = b.boxs
    //         LEFT JOIN customer_items c ON b.id = item_fg_id
    //         WHERE c.customer_id = '$customer_id' AND c.customer_address_id = '$customer_address->id' AND (a.size like '%$post%' or a.name like '%$post%')
    //         ORDER BY `name` ASC");
    //     }
        
    //     echo json_encode($records);
    // }

    public function readItemBox($customer_id, $plant, $box_list)
{
    $customer_id = base64_decode($customer_id);
    $plant = base64_decode($plant);
    $box_list = base64_decode($box_list);

    // Ambil data alamat customer berdasarkan plant
    $customer_address = $this->crud->read("customer_address", [], ["plant" => $plant]);
    $customer_address_id = isset($customer_address->id) ? $customer_address->id : '';

    $post = isset($_POST['q']) ? $_POST['q'] : "";

    if ($box_list == 'All') {
        $records = $this->crud->query("
            SELECT id, color, name, uom, size, code
            FROM item_boxs 
            WHERE size LIKE '%$post%' OR name LIKE '%$post%'
            ORDER BY name ASC
        ");
    } else {
        $records = $this->crud->query("
            SELECT DISTINCT a.id, a.color, a.name, a.uom, a.size, a.code
            FROM item_boxs a
            LEFT JOIN item_fg b ON a.name = b.boxs
            LEFT JOIN customer_items c ON c.item_fg_id = b.id
            WHERE c.customer_id = '$customer_id'
              AND c.customer_address_id = '$customer_address_id'
              AND (a.size LIKE '%$post%' OR a.name LIKE '%$post%')
            ORDER BY a.name ASC
        ");
    }

    echo json_encode($records);
}


    public function documentNo()
    {
        $data = $this->crud->query("SELECT DISTINCT document_no FROM dn_boxs WHERE `status` = '0' ORDER BY document_no ASC");
        echo json_encode($data);
    }

    public function checkNote()
    {
        $document_no = $this->input->post('document_no');

        $this->db->select('COUNT(*) as total');
        $this->db->from('dn_boxs');
        $this->db->where('document_no', $document_no);
        $this->db->where("(approved_to IS NOT NULL AND approved_to != '')", null, false);

        $record = $this->db->get()->row_array();

        if ($record['total'] > 0) {
            echo json_encode("NO");
        } else {
            echo json_encode("YES");
        }
    }

    public function readPlant($customer_id)
    {
        $customer_id = base64_decode($customer_id);
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT * FROM customer_address WHERE customer_id = '$customer_id' and (plant LIKE '%$post%' or `address` LIKE '%$post%')");
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
            $filter_box_name = $this->input->get('filter_box_name');
            $filter_status = $this->input->get('filter_status');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*, DATE(a.transaction_date) as transaction_dates, e.name as customer_name");
            $this->db->from('dn_boxs a');
            $this->db->join('item_boxs b', 'a.item_box_id = b.id');
            $this->db->join('customers e', 'a.customer_id = e.id', 'left');
            if ($filter_from != "" or $filter_to != "") {
                $this->db->where('a.transaction_date >=', $filter_from);
                $this->db->where('a.transaction_date <=', $filter_to);
            }
            // if ($filter_division != "") {
            //     $this->db->where('a.division', $filter_division);
            // }
            if ($filter_document_no != "") {
                $this->db->where('a.document_no', $filter_document_no);
            }
            if ($filter_box_name != "") {
                $this->db->where('a.item_box_id', $filter_box_name);
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
            $filter_box_name = base64_decode($this->input->get('item_box_id'));

            $this->db->select('a.*, b.name as item_box_name, b.color as item_box_color, b.code as item_box_code, b.size as item_box_size, b.uom');
            $this->db->from('dn_boxs a');
            $this->db->join('item_boxs b', 'a.item_box_id = b.id', 'left');
            $this->db->where('a.document_no', $number);
            if ($filter_box_name != "") {
                $this->db->where('b.name', $filter_box_name);
            }
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();
            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    // public function datatableUpdates()
    // {
    //     if ($this->input->get()) {
    //         $customer_id = base64_decode($this->input->get('customer_id'));
    //         $division_id = base64_decode($this->input->get('division_id'));
    //         $customer_address_id = base64_decode($this->input->get('customer_address_id'));
            
    //         $this->db->select('a.*, b.number as item_fg_number, b.number_customer as item_fg_customer, a.currency');
    //         $this->db->from('customer_items a');
    //         $this->db->join('item_fg b', 'a.item_fg_id = b.id');
    //         $this->db->join('customers c', 'a.customer_id = c.id');
    //         $this->db->where('a.customer_id', $customer_id);
    //         $this->db->where('a.division_id', $division_id);
    //         $this->db->where('a.customer_address_id', $customer_address_id);
    //         $this->db->order_by('a.id', 'ASC');
    //         $records = $this->db->get()->result_array();

    //         echo json_encode($records);
    //     }
    // }

    // GET DATATABLE HISTORY PRICE
    public function datatableHistories()
    {
        if ($this->input->get()) {
            $customer_id = base64_decode($this->input->get('customer_id'));
            $item_fg_id = base64_decode($this->input->get('item_fg_id'));
            $division_id = base64_decode($this->input->get('division_id'));
            $customer_address_id = base64_decode($this->input->get('customer_address_id'));

            $this->db->select('*');
            $this->db->from('customer_item_histories');
            $this->db->where('customer_id', $customer_id);
            $this->db->where('item_fg_id', $item_fg_id);
            $this->db->where('division_id', $division_id);
            $this->db->where('customer_address_id', $customer_address_id);
            $this->db->order_by('valid_date', 'DESC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        $header  = $this->input->post('header');
        $details = json_decode($this->input->post('details'), true);

        if (empty($header) || empty($details)) {
            echo json_encode([
                "status"  => "error",
                "theme"   => "error",
                "message" => "Data header atau detail tidak ada!"
            ]);
            return;
        }

        $trans_date = $header['transaction_date'];
        $div        = $header['division'];
        $year       = date("Y", strtotime($trans_date));
        $monthYear  = date("my", strtotime($trans_date));

        $sqlGetID = $this->db->query("SELECT MAX(LEFT(document_no,3)) as kode 
                                    FROM dn_boxs 
                                    WHERE YEAR(transaction_date) = '$year'");
        $rowID = $sqlGetID->row();
        $kode  = $rowID->kode;

        $urutan = ($kode == NULL) ? 1 : ((int) $kode + 1);
        $autoID = sprintf("%03s", $urutan);

        $autonumber = $autoID . "-" . $div . "-BX-" . $monthYear;

        $inserted = 0;
        foreach ($details as $row) {
            $dataInsert = array_merge($row, $header, [
                "document_no" => $autonumber
            ]);

            if ($this->crud->create('dn_boxs', $dataInsert)) {
                $inserted++;
            }
        }

        echo json_encode([
            "status"  => "success",
            "theme"   => "success",
            "message" => "Saved with Doc No: $autonumber ($inserted row)"
        ]);
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('dn_boxs', $data);
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
                'customer_id' => $data->val($i, 2),
                'division_id' => $data->val($i, 3),
                'customer_address_id' => $data->val($i, 4),
                'item_fg_id' => $data->val($i, 5),
                'price' => $data->val($i, 6),
                'currency' => $data->val($i, 7),
                'valid_date' => $data->val($i, 8),
                'remark' => $data->val($i, 9)
            );
        }
        $datas['total'] = count($datas);
        echo json_encode($datas);
        unlink($_FILES['file_upload']['name']);
    }

    // public function uploadclearFailed()
    // {
    //     @unlink('failed/customer_items.txt');
    // }

    // public function uploadcreateFailed()
    // {
    //     if ($this->input->post()) {
    //         $message = $this->input->post('message');
    //         $textFailed = fopen('failed/customer_items.txt', 'a');
    //         fwrite($textFailed, $message . "\n");
    //         fclose($textFailed);
    //     }
    // }

    // //UPLOAD DOWNLOAD FAILED
    // public function uploadDownloadFailed()
    // {
    //     $file = "failed/customer_items.txt";
    //     header('Content-Description: File Failed');
    //     header('Content-Disposition: attachment; filename=' . basename($file));
    //     header('Expires: 0');
    //     header('Cache-Control: must-revalidate');
    //     header('Pragma: public');
    //     header('Content-Length: ' . @filesize($file));
    //     header("Content-Type: text/plain");
    //     @readfile($file);
    // }

    // //UPLOAD CREATE DATA
    // public function uploadcreate()
    // {
    //     if ($this->input->post()) {
    //         $data = $this->input->post('data');

    //         //Cek Process Number          //table       //field        //field excel
    //         $customer_items = $this->crud->read('customer_items', [], ["customer_id" => $data['customer_id'], "item_fg_id" => $data['item_fg_id'], "division_id" => $data['division_id'],"customer_address_id" => $data['customer_address_id']]);
    //         $customers = $this->crud->read('customers', [], ["id" => $data['customer_id']]);
    //         $divisions = $this->crud->read('divisions', [], ["id" => $data['division_id']]);
    //         $customer_address = $this->crud->read('customer_address', [], ["id" => $data['customer_address_id']]);
    //         $item_fg_id = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);

    //         if (empty($customers->id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => " Customer " . $data['customer_id'] . " is Not Found", "theme" => "error"));
    //         }else if (empty($item_fg_id->id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => " Item id " . $data['item_fg_id'] . " is Not Found", "theme" => "error"));
    //         }else if (empty($divisions->id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => " Division Id " . $data['division_id'] . " is Not Found", "theme" => "error"));
    //         }else if (empty($customer_address->id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => " Customer address Id " . $data['customer_address_id'] . " is Not Found", "theme" => "error"));
    //         }else if (!empty($customer_items->customer_id)) {
    //             $send   = $this->db->update('customer_items',["price" => $data['price'],"valid_date" => $data['valid_date'],"remark" => $data['remark']], ["customer_id" => $data['customer_id'],"item_fg_id" => $data['item_fg_id'],"division_id" => $data['division_id'],"customer_address_id" => $data['customer_address_id']]);
                
    //             $dataFinal = array(
    //                 //field
    //                 "customer_id" => $data['customer_id'],
    //                 "division_id" => $data['division_id'],
    //                 "customer_address_id" => $data['customer_address_id'],
    //                 "item_fg_id" => $data['item_fg_id'],
    //                 "price" => $data['price'],
    //                 "currency" => $data['currency'],
    //                 "valid_date" => $data['valid_date'],
    //                 "remark" => $data['remark'],
    //             );

    //             $send2 = $this->crud->createNotLog('customer_item_histories', $dataFinal);
    //             echo json_encode(array("title" => "Update", "message" => " Customer " . $data['customer_id'] . " Data Updated", "theme" => "success"));
    //         } else {
    //             $dataFinal = array(
    //                 //field
    //                 "customer_id" => $data['customer_id'],
    //                 "item_fg_id" => $data['item_fg_id'],
    //                 "division_id" => $data['division_id'],
    //                 "customer_address_id" => $data['customer_address_id'],
    //                 "price" => $data['price'],
    //                 "currency" => $data['currency'],
    //                 "valid_date" => $data['valid_date'],
    //                 "remark" => $data['remark'],
    //             );
    //             $send   = $this->crud->create('customer_items', $dataFinal);
    //             $send2 = $this->crud->create('customer_item_histories', $dataFinal);
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
            header("Content-Disposition: attachment; filename=dn_crusher_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = $this->input->get('filter_from');
        $filter_to = $this->input->get('filter_to');
        $filter_division = $this->input->get('filter_division');
        $filter_document_no = $this->input->get('filter_document_no');
        $filter_transfer_from = $this->input->get('filter_transfer_from');
        $filter_transfer_to = $this->input->get('filter_transfer_to');
        $filter_box_name = $this->input->get('filter_box_name');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_status = $this->input->get('filter_status');


        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.number as item_number, b.name as item_name, b.uom, c.name as family_name, d.name as category_name");
        $this->db->from('dn_boxs a');
        $this->db->join('item_boxs b', 'a.item_box_id = b.id', 'left');
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
        if ($filter_box_name != "") {
            $this->db->where('a.item_box_id', $filter_box_name);
        }
        if ($filter_item_category != "") {
            $this->db->where('b.item_category_id', $filter_item_category);
        }
        if ($filter_item_family != "") {
            $this->db->where('b.item_family_id', $filter_item_family);
        }
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#dn_crusher {border-collapse: collapse;width: 100%;font-size: 12px;}#dn_crusher td, #dn_crusher th {border: 1px solid #ddd;padding: 2px;}#dn_crusher tr:nth-child(even){background-color: #f2f2f2;}#dn_crusher tr:hover {background-color: #ddd;}#dn_crusher th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>DN Crusher</h3>
            </div>
        </center>
        
        <table id="dn_crusher" border="1">
            <tr>
                <th width="20">No</th>
                <th>Document No</th>
                <th>Division</th>
                <th>Trans Date</th>
                <th>Part Number</th>
                <th>Part Name</th>
                <th>Qty</th>
                <th>Remarks</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['document_no'] . '</td>
                    <td>' . $data['division'] . '</td>
                    <td>' . $data['trans_date'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_number'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_name'] . '</td>
                    <td>' . $data['qty'] . '</td>
                    <td>' . $data['remarks'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

     public function print_note($document_no)
    {
        $dn_boxs_total = $this->crud->reads('dn_boxs', [], ["document_no" => base64_decode($document_no)]);
        $dn_boxss = $this->crud->read('dn_boxs', [], ["document_no" => base64_decode($document_no)]);
        $customer_address = $this->crud->read('customer_address', [], ["customer_id" => $dn_boxss->customer_id, "plant" => $dn_boxss->plant]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        $signatures = $this->db->get('signatures')->row();

        $approval = $this->crud->read('approvals', [], ["table_name" => "dn_boxs"]);
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
        
        
        if($dn_boxss->approved == 0){
            $users_1 = '';
            $users_2 = '';
            $users_3 = '';
        } elseif ($dn_boxss->approved == 1) {
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '';
            $users_2 = '';
            $users_3 = '';
        } elseif ($dn_boxss->approved == 2) {
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '';
            $users_3 = '';
        } elseif ($dn_boxss->approved == 3) {
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
        $page = ceil(count($dn_boxs_total) / $rows);
        //Generate QRcode
        $this->createQrcode($dn_boxss->document_no, "assets/image/qrcode/");
        $this->createQrcode($user_3->name, "assets/image/qrcode/");
        $this->createQrcode($user_2->name, "assets/image/qrcode/");
        $this->createQrcode($user_1->name, "assets/image/qrcode/");
        $this->createQrcode($this->session->name, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $dn_boxss->document_no . '</title>
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
        $judul = "DELIVERY NOTE BOX"; 
        // $form_iso = $config_iso->form_dn_boxs;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select("a.*, 
            b.name as item_box_name, 
            b.code as item_box_code,
            b.size as item_box_size, 
            b.color as item_box_color, 
            b.uom");
            $this->db->from('dn_boxs a');
            $this->db->join('item_boxs b', 'a.item_box_id = b.id', 'left');
            $this->db->where('a.document_no', base64_decode($document_no));
            $this->db->order_by('b.name', 'asc');
            $this->db->limit(8, ($i * 8));
            $this->db->order_by('a.id', 'ASC');

            $records = $this->db->get()->result_array();

            if ($dn_boxss->updated_date != null) {
                $revision_date = $dn_boxss->updated_date;
            } else {
                $revision_date = $dn_boxss->created_date;
            }

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
                                            <td>-</td>
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
                                    <small>NO : ' . @$dn_boxss->document_no . '</small>
                                </center>
                                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                    <tr>
                                        <td width="80">Ship to</td>
                                        <td width="10">:</td>
                                        <td width="30%"><b>' . @$customer_address->address . '</b></td>
                                        <td style="text-align:right; padding-right: 20px;" rowspan="7">
                                            Page <b>' . $hal  . '</b> of <b> ' . $page . '</b><br><br>
                                            Transaction Date:<br><b>' . date("d F Y", strtotime($dn_boxss->transaction_date)) . '</b><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50">Doc No</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$dn_boxss->document_no . '</b></td>
                                    </tr>
                                </table>
                                <table id="customers">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" width="30" style="text-align:center;">No</th>
                                            <th rowspan="2" width="150" style="text-align:center;">Box Name</th>
                                            <th rowspan="2" width="80" style="text-align:center;">Box Code</th>
                                            <th rowspan="2" width="150" style="text-align:center;">Box Size</th>
                                            <th rowspan="2" width="80" style="text-align:center;">Box Color</th>
                                            <th rowspan="2" width="80" style="text-align:center;">Uom</th>
                                            <th rowspan="2" width="80" style="text-align:center;">Qty</th>
                                            <th rowspan="2" width="100" style="text-align:center;">Notes</th>
                                        </tr>
                                    </thead>';
            $row = 0;
            foreach ($records as $record) {
                $html .= '  
                            <tr>    
                                <td style="text-align:center;">' . $no . '</td>
                                <td style="text-align:center;">' . $record['item_box_name'] . '</td>
                                <td style="text-align:center;">' . $record['item_box_code'] . '</td>
                                <td style="text-align:center;">' . $record['item_box_size'] . '</td>
                                <td style="text-align:center;">' . $record['item_box_color'] . '</td>
                                <td style="text-align:center;">' . $record['uom'] . '</td>
                                <td style="text-align:right;">' . number_format($record['qty'], 2) . '</td>
                                <td style="text-align:center;">' . $record['remarks'] . '</td>
                            </tr>';
                $row++;
                $no++;
                $subtotal += $record['qty'];
            }
            $html .= '<tr>
                <td colspan="6" style="text-align:center; font-weight:bold;">TOTAL</td>
                <td style="text-align:right; font-weight:bold;">' . number_format($subtotal, 2, ",", ".") . '</td>
                <td>-</td>
            </tr>';

            $html .= '<tr>
                <br>
                <td colspan="8" style="text-align:left; border: none; font-size: 12px;">Box harap dikembalikan ke PT.Banshu Plastic Indonesia</td>
            </tr>';

            if (($i + 1) == $page) {
                
                // $this->db->select('a.remarks, b.number as item_number, b.name as item_name');
                // $this->db->from('request_materials a');
                // $this->db->join('item_boxs b', 'a.item_box_id = b.id');
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
                            <th width="200" style="text-align:center;">Customer</th>
                            <th width="200" style="text-align:center;">Transporter</th>
                            <th width="200" style="text-align:center;">Security Check</th>
                            <th width="200" style="text-align:center;">Approved By</th>
                            <th width="200" style="text-align:center;">Checked By</th>
                            <th width="200" style="text-align:center;">Prepared By</th>
                        </tr>
                        <tr>
                            <th style="height:100px;"></th>
                            <th style="height:100px;"></th>
                            <th style="height:100px;"></th>
                            <th style="height:100px;">'. $users_2. '</th>
                            <th style="height:100px;">'. $users_1. '</th>
                            <th style="height:100px;">'. $users_0. '</th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;"></th>
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
