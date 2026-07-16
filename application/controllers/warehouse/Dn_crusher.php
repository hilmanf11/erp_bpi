<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Dn_crusher extends CI_Controller
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
            $this->load->view('warehouse/dn_crusher');
        } else {
            redirect('error_access');
        }
    }

    public function checkNote()
    {
        $document_no = $this->input->post('document_no');

        $this->db->select('COUNT(*) as total');
        $this->db->from('scan_dn_crusher');
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
        $data = $this->crud->query("SELECT DISTINCT document_no FROM scan_dn_crusher WHERE `status` = '0' ORDER BY document_no ASC");
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
            $this->db->select("a.*, DATE(a.transaction_date) as transaction_dates, e.name as supplier_name");
            $this->db->from('scan_dn_crusher a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_familys c', 'b.item_family_id = c.id', 'left');
            $this->db->join('item_categories d', 'b.item_category_id = d.id', 'left');
            $this->db->join('suppliers e', 'a.supplier_id = e.id', 'left');
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
            $this->db->from('scan_dn_crusher a');
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
        $filter_item_rm_id = $this->input->get('filter_item_rm_id');
        $filter_item_category = $this->input->get('filter_item_category');
        $filter_item_family = $this->input->get('filter_item_family');
        $filter_status = $this->input->get('filter_status');


        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*, b.number as item_number, b.name as item_name, b.uom, c.name as family_name, d.name as category_name, SUM(a.qty) as qtys");
        $this->db->from('scan_dn_crusher a');
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
        $scan_dn_crusher_total = $this->crud->reads('scan_dn_crusher', [], ["document_no" => base64_decode($document_no)]);
        $scan_dn_crushers = $this->crud->read('scan_dn_crusher', [], ["document_no" => base64_decode($document_no)]);
        $suppliers = $this->crud->read('suppliers', [], ["supplier_id" => $scan_dn_crushers->supplier_id]);

        $config = $this->db->get('config')->row();
        $config_iso = $this->db->get('config_iso')->row();
        $signatures = $this->db->get('signatures')->row();

        $approval = $this->crud->read('approvals', [], ["table_name" => "scan_dn_crusher"]);
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
        
        
        if($scan_dn_crushers->approved == 0){
            $users_1 = '';
            $users_2 = '';
            $users_3 = '';
        } elseif ($scan_dn_crushers->approved == 1) {
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '';
            $users_2 = '';
            $users_3 = '';
        } elseif ($scan_dn_crushers->approved == 2) {
            $users_0 = '<img src="' . base_url('assets/image/qrcode/' . $this->session->name . '.png') . '" width="80"/>';
            $users_1 = '<img src="' . base_url('assets/image/qrcode/' . $user_1->name . '.png') . '" width="80"/>';
            $users_2 = '';
            $users_3 = '';
        } elseif ($scan_dn_crushers->approved == 3) {
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
        $page = ceil(count($scan_dn_crusher_total) / $rows);
        //Generate QRcode
        $this->createQrcode($scan_dn_crushers->document_no, "assets/image/qrcode/");
        $this->createQrcode($user_3->name, "assets/image/qrcode/");
        $this->createQrcode($user_2->name, "assets/image/qrcode/");
        $this->createQrcode($user_1->name, "assets/image/qrcode/");
        $this->createQrcode($this->session->name, "assets/image/qrcode/");
        $html = '<html>
                    <head>
                        <title>' . $scan_dn_crushers->document_no . '</title>
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
        $judul = "DELIVERY NOTE"; 
        // $form_iso = $config_iso->form_scan_dn_crusher;
        for ($i = 0; $i < $page; $i++) {
            $this->db->select("a.*, 
            e.number as item_number, 
            e.name as item_name, 
            b.uom, 
            c.name as family_name, 
            d.name as category_name, 
            SUM(a.qty) as qtys");
            $this->db->from('scan_dn_crusher a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id', 'left');
            $this->db->join('item_familys c', 'b.item_family_id = c.id', 'left');
            $this->db->join('item_categories d', 'b.item_category_id = d.id', 'left');
            $this->db->join('item_family_subs e', 'b.item_sub_family_id = e.id', 'left');
            $this->db->where('a.document_no', base64_decode($document_no));
            $this->db->order_by('b.number', 'asc');
            $this->db->limit(8, ($i * 8));
            $this->db->group_by('a.item_rm_id');
            $this->db->order_by('a.id', 'ASC');

            $records = $this->db->get()->result_array();

            if ($scan_dn_crushers->updated_date != null) {
                $revision_date = $scan_dn_crushers->updated_date;
            } else {
                $revision_date = $scan_dn_crushers->created_date;
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
                                    <small>NO : ' . @$scan_dn_crushers->document_no . '</small>
                                </center>
                                <table style="width:100%; font-size:12px; margin-bottom:10px;">
                                    <tr>
                                        <td width="80">Ship to</td>
                                        <td width="10">:</td>
                                        <td width="30%"><b>' . @$suppliers->name . '</b></td>
                                        <td style="text-align:right; padding-right: 20px;" rowspan="7">
                                            Page <b>' . $hal  . '</b> of <b> ' . $page . '</b><br><br>
                                            Transaction Date:<br><b>' . date("d F Y", strtotime($scan_dn_crushers->transaction_date)) . '</b><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="50">Doc No</td>
                                        <td width="10">:</td>
                                        <td><b>' . @$scan_dn_crushers->document_no . '</b></td>
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
                                            <th rowspan="2" width="100" style="text-align:center;">Notes</th>
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
                            <th width="200" style="text-align:center;">Customer</th>
                            <th width="200" style="text-align:center;">Transporter</th>
                            <th width="200" style="text-align:center;">Approved By</th>
                            <th width="200" style="text-align:center;">Checked By</th>
                            <th width="200" style="text-align:center;">Checked By</th>
                            <th width="200" style="text-align:center;">Prepared By</th>
                        </tr>
                        <tr>
                            <th style="height:100px;"></th>
                            <th style="height:100px;"></th>
                            <th style="height:100px;">'. $users_3. '</th>
                            <th style="height:100px;">'. $users_2. '</th>
                            <th style="height:100px;">'. $users_1. '</th>
                            <th style="height:100px;">'. $users_0. '</th>
                        </tr>
                        <tr>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;"></th>
                            <th style="height:20px; text-align:center;">' . $user_3->name . '</th>
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
