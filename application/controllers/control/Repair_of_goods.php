<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Repair_of_goods extends CI_Controller
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
        $this->form_validation->set_rules('customer_id', 'Customer', 'required|min_length[1]|max_length[20]|is_unique[customer_items.customer_id]');
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[customer_items.item_fg_id]');
    }

    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('control/repair_of_goods');
        } else {
            redirect('error_access');
        }
    }

    public function readItemFg()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, b.number_customer, a.price, c.currency, b.uom
            FROM customer_items a 
            JOIN item_fg b ON a.item_fg_id = b.id
            JOIN customers c ON a.customer_id = c.id
            WHERE a.deleted = 0 and (b.number LIKE '%$post%' or b.name LIKE '%$post%')");
        echo json_encode($send);
    }

    public function readItems($customer_id, $sales_order_no)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT b.id, b.number, b.name, a.qty
            FROM sales_orders a 
            JOIN item_fg b ON a.item_fg_id = b.id
            JOIN customers c ON a.customer_id = c.id
            WHERE a.customer_id = '$customer_id' and a.sales_order_no = '$sales_order_no' and a.status = 0 and (b.number LIKE '%$post%' or b.name LIKE '%$post%') ");
        echo json_encode($send);
    }

    public function reads($division)
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $divisions = $this->crud->read('divisions', [], ["number" => $division]);
        $division_id = $divisions->id;
        $send = $this->crud->query("SELECT * FROM item_fg WHERE division_id = '$division_id' and (number like '%$post%' or number_customer like '%$post%' or name like '%$post%')");
        echo json_encode($send);
    }

    public function readSalesOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT sales_order_no, sales_order_date FROM sales_orders WHERE customer_id = '$customer_id' and status = 0");
        echo json_encode($send);
    }

    public function readCustomerOrder($customer_id)
    {
        $send = $this->crud->query("SELECT DISTINCT customer_order_no FROM sales_orders WHERE customer_id = '$customer_id'");
        echo json_encode($send);
    }

    public function number($trans_date = "")
    {
        if ($trans_date == "") {
            $datenow = "RG-" . date("ym");
        } else {
            $datenow = "RG-" . date("ym", strtotime(base64_decode($trans_date)));
        }

        // $datenow    = "RG-" . date("ym", strtotime(base64_decode($trans_date)));
        $sqlGetID   = $this->db->query("SELECT max(`document_no`) as kode FROM repair_of_goods WHERE `document_no` like '%$datenow%'");
        $rowID      = $sqlGetID->row();
        $kode       = $rowID->kode;
        if ($kode == NULL) {
            $autoID = sprintf("%03s", $kode + 1);
        } else {
            $urutan = (int) substr($kode, -3);
            $urutan++;
            $autoID = sprintf("%03s", $urutan);
        }
        echo $datenow ."-".$autoID;
    }

    public function closeFc()
    {
        $id = $this->input->post('id');
        $remark = $this->input->post('remark');
        $update = $this->db->update('repair_of_goods', ["status_fc" => 2, "remarks" => $remark], ["id" => $id]);// , "qty" => 0
        // echo $update;

         // Berikan respon sesuai hasil
        if ($update) {
            echo json_encode(["success" => true,"message" => "Repair of Good closed successfully."]);
        } else {
            echo json_encode(["success" => false,"message" => "Failed to close Repair of Good."]);
        }
    }

    public function openFc()
    {
        // $po_no = $this->input->post('po_no');
        $id = $this->input->post('id');
        $remark = $this->input->post('remark');
        $update = $this->db->update('repair_of_goods', ["status_fc" => 0, "remarks" => $remark], ["id" => $id]);// , "qty" => 0

        if ($update) {
            echo json_encode(["success" => true,"message" => "Repair of Good open successfully."]);
        } else {
            echo json_encode(["success" => false,"message" => "Failed to open Repair of Good."]);
        }
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_from = @base64_decode($get['filter_from']);
            $filter_to = @base64_decode($get['filter_to']);
            $filter_document_no = @base64_decode($get['filter_document_no']);
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
            $filter_status = @base64_decode($get['filter_status']);
            $filter_status_fc = @base64_decode($get['filter_status_fc']);
           

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*,
            d.total_status_open, 
            c.total_status_close, 
            COUNT(a.status) as total_status,
            COUNT(a.status_fc) as total_status_fc,
            (CASE 
                WHEN d.total_status_open = COUNT(a.status) THEN '0'
                WHEN c.total_status_close = COUNT(a.status) THEN '1'
                WHEN d.total_status_open >= 1 THEN '0'
                WHEN c.total_status_close >= 1 THEN '1'
                ELSE '0'
            END) as status2,
            (CASE 
                WHEN f.total_status_fc_open = COUNT(a.status_fc) THEN '0'
                WHEN e.total_status_fc_close = COUNT(a.status_fc) THEN '1'
                WHEN g.total_status_fc_complete = COUNT(a.status_fc) THEN '2'
                WHEN f.total_status_fc_open >= 1 THEN '0'
                WHEN e.total_status_fc_close >= 1 THEN '1'
                ELSE '0'
            END) as status2_fc");
            $this->db->from('repair_of_goods a');
            $this->db->join('(SELECT document_no, COUNT(status) as total_status_close FROM repair_of_goods WHERE status = 1 GROUP BY document_no) c', 'a.document_no = c.document_no', 'left');
            $this->db->join('(SELECT document_no, COUNT(status) as total_status_open FROM repair_of_goods WHERE status = 0 GROUP BY document_no) d', 'a.document_no = d.document_no', 'left');
            $this->db->join('(SELECT document_no, COUNT(status_fc) as total_status_fc_close FROM repair_of_goods WHERE status_fc = 1 GROUP BY document_no) e', 'a.document_no = e.document_no', 'left');
            $this->db->join('(SELECT document_no, COUNT(status_fc) as total_status_fc_open FROM repair_of_goods WHERE status_fc = 0 GROUP BY document_no) f', 'a.document_no = f.document_no', 'left');
            $this->db->join('(SELECT document_no, COUNT(status_fc) as total_status_fc_complete FROM repair_of_goods WHERE status_fc = 2 GROUP BY document_no) g', 'a.document_no = g.document_no', 'left');
            if ($filter_from != "" && $filter_to != "") {
                $this->db->where('trans_date >=', $filter_from);
                $this->db->where('trans_date <=', $filter_to);
            }
            $this->db->like('a.document_no', $filter_document_no);
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->like('a.status', $filter_status);
            $this->db->like('a.status_fc', $filter_status_fc);
            $this->db->group_by('a.document_no');
            $this->db->order_by('a.created_date', 'DESC');
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
            $document_no = base64_decode($this->input->get('document_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom, c.qty as qty_scan');
            $this->db->from('repair_of_goods a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->join("(SELECT item_fg_id, document_no, COALESCE(SUM(qty),0) as qty FROM scan_repair_of_goods GROUP BY item_fg_id, document_no ) c",'a.item_fg_id = c.item_fg_id and a.document_no = c.document_no','left');
            $this->db->where('a.document_no', $document_no);
            $this->db->group_by('a.document_no');
            $this->db->group_by('a.item_fg_id');
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // GET DATATABLES UPDATE
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $document_no = base64_decode($this->input->get('document_no'));

            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom');
            $this->db->from('repair_of_goods a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.document_no', $document_no);
            $this->db->order_by('b.number', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    //CREATE DATA
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $repair_of_goods = $this->crud->read("repair_of_goods", [], ["document_no" => $post['document_no'], "item_fg_id" => $post['item_fg_id']]);
            if (@$repair_of_goods->document_no != "") {
                $send = $this->crud->update('repair_of_goods', ["document_no" => $post['document_no'], "item_fg_id" => $post['item_fg_id']], $post);
            } else {
                $send = $this->crud->create('repair_of_goods', $post);
            }

            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function uploadatt()
    {
        // Pastikan file disimpan dalam direktori yang diinginkan
        $uploadDir = 'assets/image/sales_orders/';

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
                $maxFileSize = 2 * 1024 * 1024; // 5MB dalam bytes
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

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        var_dump($data);
        die;
        $send = $this->crud->delete('repair_of_goods', $data);
        echo $send;
    }

    //PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=repair_of_goods_$format.xls");
        }

        $get = $this->input->get();
        $filter_from = @base64_decode($get['filter_from']);
        $filter_to = @base64_decode($get['filter_to']);
        $filter_document_no = @base64_decode($get['filter_document_no']);
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_status = @base64_decode($get['filter_status']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom');
        $this->db->from('repair_of_goods a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        if ($filter_from != "" && $filter_to != "") {
            $this->db->where('a.trans_date >=', $filter_from);
            $this->db->where('a.trans_date <=', $filter_to);
        }
        $this->db->like('a.document_no', $filter_document_no);
        $this->db->like('a.item_fg_id', $filter_item_fg_id);
        $this->db->like('a.status', $filter_status);
        $this->db->order_by('a.document_no', 'ASC');
        $records = $this->db->get()->result_array();

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customer_items {border-collapse: collapse;width: 100%;font-size: 12px;}#customer_items td, #customer_items th {border: 1px solid #ddd;padding: 2px;}#customer_items tr:nth-child(even){background-color: #f2f2f2;}#customer_items tr:hover {background-color: #ddd;}#customer_items th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
        <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
                            <img src="' . $config->favicon . '" width="30">
                        </td>
                        <td style="font-size: 14px; text-align: left; margin:2px;">
                            <b>' . $config->name . '</b><br>
                            <small>' . $config->description . '</small>
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
                <h3>REPAIR OF GOODS</h3>
            </div>
        </center>
        
        <table id="customer_items" border="1">
            <tr>
                <th width="20">No</th>
                <th>Document No</th>
                <th>Product ID</th>
                <th>Product No</th>
                <th>Product Name</th>
                <th>Uom</th>
                <th>Qty</th>
                <th>Remarks</th>
                <th>Status</th>
                <th>Status FC</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            if($data['status'] == '1'){
                $status = 'CLOSED';
            }else{
                $status = 'OPEN';
            }

            if($data['status_fc'] == '1'){
                $status_fc = 'CLOSED';
            }else{
                $status_fc = 'OPEN';
            }

            $html .= '<tr>
                        <td>' . $no . '</td>
                        <td>' . $data['document_no'] . '</td>
                        <td>' . $data['item_fg_id'] . '</td>
                        <td>' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td>' . $data['uom'] . '</td>
                        <td>' . $data['qty'] . '</td>
                        <td>' . $data['remarks'] . '</td>
                        <td>' . $status . '</td>
                        <td>' . $status_fc . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_rod($document_no)
    {
        $document_no = base64_decode($document_no);

        $repair_of_goods = $this->crud->reads('repair_of_goods', [], ["document_no" => $document_no]);
        $repair_of_good = $this->crud->read('repair_of_goods', [], ["document_no" => $document_no]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        //Config Page
        $rows = 10;
        $page = ceil(count($repair_of_goods) / $rows);
        //Generate QRcode
        $this->createQrcode($document_no, "assets/image/qrcode/");
        //Header Print
        $html = '<html><head><title>' . $repair_of_good->document_no . '</title><link rel="icon" href="' . $config->favicon . '" type="image/png" sizes="16x16"></head>';
        $html .= '<style>body {font-family: Arial, Helvetica, sans-serif;}';
        $html .= '#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid black;padding: 2px;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}';
        $html .= '@media screen {.print {display: none !important;}}@media print {.noprint {display: none !important;}}</style>';
        $html .= '<body><div style="margin:20%;" class="noprint"><center>
                    <h1>Press CTRL + P for Print</h1>
                    <p>Display pages for 10 rows</p>
                    <p>Paper Size A4, Layout Landscape</p>
                    <p>Margin Default, Scale 98</p>
                </center></div><div class="print">';
        //Loop Page
        $no = 1;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, b.uom');
            $this->db->from('repair_of_goods a');
            $this->db->join('item_fg b', 'a.item_fg_id = b.id');
            $this->db->where('a.document_no', $document_no);
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(10, ($i * 10));
            $records = $this->db->get()->result_array();

            $html .= '  <table style="width:100%;">
                            <tr>
                                <th width="10"><img src="' . $config->favicon . '" width="40" /></th>
                                <td width="300" style="padding:10px;">
                                    <b style="font-size:14px;">' . $config->name . '</b><br>
                                    <span style="font-size:10px;">' . $config->description . '</span><br>
                                </td>
                                <th width="100" style="text-align:right;">
                                    <table style="width:100%; font-size:10px;">
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
                        <div style="border: 1px solid black; width:100%; height:73%;">
                            <div style="padding:10px;">
                                <center>
                                    <h3>REPAIR OF GOODS</h3>
                                </center>
                                <div style="float:left; width:60%;">
                                    <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                        <tr>
                                            <td width="150">Document No</td>
                                            <td width="10">:</td>
                                            <td><b>' . @$repair_of_good->document_no . '</b></td>
                                        </tr>
                                        <tr>
                                            <td width="100">Transaction Date</td>
                                            <td width="10">:</td>
                                            <td><b>' . date("d F Y", strtotime(@$repair_of_good->trans_date)) . '</b></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="float:left; width:40%; text-align:right;">
                                    <img style="margin-right:10px;" src="' . base_url('assets/image/qrcode/' . $repair_of_good->document_no . '.png') . '" width="80"/><br>
                                    <small style="font-size:10px; margin-right:16px;">' . $repair_of_good->document_no . '</small><br><br>
                                </div>
                                <table id="customers">
                                    <tr>
                                        <th width="20">No</th>
                                        <th>Product ID</th>
                                        <th>Product No</th>
                                        <th>Product Name</th>
                                        <th>UoM</th>
                                        <th width="60">Qty Repair</th>
                                        <th width="120">Remarks</th>
                                    </tr>';
            foreach ($records as $record) {
                $html .= '  <tr>
                                <td style="text-align:center">' . $no . '</td>
                                <td>' . $record['item_fg_id'] . '</td>
                                <td>' . $record['item_fg_number'] . '</td>
                                <td>' . $record['item_fg_name'] . '</td>
                                <td>' . $record['uom'] . '</td>
                                <td style="text-align:right">' . number_format($record['qty'], 2, ",", ".") . '</td>
                                <td>' . $record['remarks'] . '</td>
                            </tr>';
                $no++;
            }
            $html .= '</table>';
            if ($i + 1 != $page) {
                $html .= '<div style="page-break-after:always;"></div>';
            }

            $html .= '</div></div>';

            if (($i + 1) == $page) {
                $html .= '  <div style="position:fixed; bottom:0; width:98.7%;">
                                <table id="customers" style="margin-top:10px;">
                                    <tr>
                                        <th width="400" style="text-align:left; vertical-align:top;" rowspan="4">Note.</th>
                                    </tr>
                                    <tr>
                                        <th width="200" style="text-align:center;">Submit By</th>
                                        <th width="200" style="text-align:center;">Approve By</th>
                                    </tr>
                                    <tr>
                                        <th style="height:80px;"></th>
                                        <th style="height:80px;"></th>
                                    </tr>
                                    <tr>
                                        <th style="height:20px; text-align:center;"></th>
                                        <th style="height:20px; text-align:center;"></th>
                                    </tr>
                                </table>
                            </div>';
            }
        }
        $html .= '</div><script>window.print()</script>';
        die($html);

    }

}
