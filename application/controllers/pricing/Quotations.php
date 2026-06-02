<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Quotations extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[quotations.item_fg_id]');
    }
    //HALAMAN UTAMA
    // public function index()
    // {
    //     if (empty($this->session->username)) {
    //         redirect('error_session');
    //     } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
    //         $data['button'] = $this->getbutton($this->id_menu());
    //         $this->load->view('template/header', $data);
    //         $this->load->view('pricing/quotations');
    //     } else {
    //         redirect('error_access');
    //     }
    // }

    //HALAMAN UTAMA
    //INDEX untuk kebutuhan NPD
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        }
        
        $url_menu_id = $this->input->get('menu_id');
        $active_menu = (!empty($url_menu_id)) ? $url_menu_id : $this->id_menu();

        if ($this->checkuserAccess($active_menu) > 0) {
            // Ambil button agar tidak ada error undefined variable
            $data['button'] = $this->getbutton($active_menu);

            $this->load->view('template/header', $data);
            $this->load->view('pricing/quotations', $data);
        } else {
            redirect('error_access');
        }
    }

    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('quotations', ["item_fg_id" => $post]);
        echo json_encode($send);
    }

    public function get_quotation_number()
    {
        $date = $this->input->post('date');
        $time = strtotime($date);
        
        $dateString = date('ymd', $time); 
        $prefix = "QUOT-" . $dateString;

        $this->db->select('quotation_number');
        $this->db->like('quotation_number', $prefix, 'after');
        $this->db->order_by('quotation_number', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('breakdown_prices');

        if ($query->num_rows() > 0) {
            $last_no = $query->row()->quotation_number;
            $parts = explode('-', $last_no);
            $next_val = intval(end($parts)) + 1;
        } else {
            $next_val = 1;
        }

        // Format nomor urut menjadi 4 digit (0001)
        $no_urut = sprintf('%04d', $next_val);
        $result = $prefix . "-" . $no_urut;
        
        echo json_encode(['number' => $result]);
    }

    public function readQuotation()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT quotation_number FROM quotations WHERE quotation_number like '%$post%'");
        echo json_encode($send);
    }

    public function readItems()
    {
        $post = $this->input->post('q') ?: "";
        
        // 1. Ambil data dasar dari breakdown_prices dan cost_patterns
        $sql = "SELECT 
                    a.item_fg_id, 
                    a.item_fg_number, 
                    a.item_fg_name, 
                    a.quotation_number, 
                    a.revision_quotation_number,
                    a.p_month,
                    a.p_year,
                    a.revision,
                    b.mpq, 
                    b.moq
                FROM breakdown_prices a
                JOIN item_fg b ON a.item_fg_id = b.id
                WHERE (a.item_fg_number LIKE '%$post%' OR a.item_fg_name LIKE '%$post%')
                GROUP BY a.item_fg_id, a.p_month, a.p_year, a.revision
                ORDER BY a.item_fg_name ASC LIMIT 50";
                
        $records = $this->db->query($sql)->result();
        $results = [];

        foreach ($records as $r) {
            $total_cost = $this->calculate_total_cost($r);

            $results[] = [
                'item_fg_id'        => $r->item_fg_id,
                'item_fg_number'    => $r->item_fg_number,
                'item_fg_name'      => $r->item_fg_name,
                'price'             => $total_cost,
                'mpq'               => $r->mpq,
                'moq'               => $r->moq,
                'quotation_number'  => $r->quotation_number,
                'revision_quotation_number'          => $r->revision_quotation_number
            ];
        }

        echo json_encode($results);
    }

    private function calculate_total_cost($header)
    {
        $this->db->select("cp.*, ml.runner, mld.cavity_standard, 
                        bn_vg.composition as comp_vg, 
                        bn_mb.composition as comp_mb, 
                        bn_cp.composition as comp_cp");
        $this->db->from('cost_patterns cp');
        $this->db->join('menu_loadings ml', 'ml.item_fg_id = cp.item_fg_id', 'left');
        $this->db->join('molds mld', 'ml.mold_id = mld.id');
        $this->db->join('bom bn_vg', 'bn_vg.item_fg_id = cp.item_fg_id AND bn_vg.item_rm_id = cp.item_rm_id_vg', 'left');
        $this->db->join('bom bn_mb', 'bn_mb.item_fg_id = cp.item_fg_id AND bn_mb.item_rm_id = cp.item_rm_id_mb', 'left');
        $this->db->join('bom bn_cp', 'bn_cp.item_fg_id = cp.item_fg_id AND bn_cp.item_rm_id = cp.item_rm_id_cp', 'left');
        
        $this->db->where([
            'cp.p_month'    => $header->p_month,
            'cp.p_year'     => $header->p_year,
            'cp.item_fg_id' => $header->item_fg_id,
            'cp.revision'   => $header->revision
        ]);

        $details = $this->db->get()->result();
        
        if (empty($details)) return 0;

        $sub_total_1 = 0;
        foreach ($details as $d) {
            $runner_per_pcs = (float)$d->runner / ((float)$d->cavity_standard > 0 ? (float)$d->cavity_standard : 1);

            // Material VG
            if($d->item_rm_id_vg) $sub_total_1 += ((float)$d->comp_vg * (float)$d->virgin_cost) / 1000;
            // Material MB
            if($d->item_rm_id_mb) $sub_total_1 += ((float)$d->comp_mb * (float)$d->mb_cost) / 1000;
            // Child Part
            if($d->item_rm_id_cp) $sub_total_1 += ((float)$d->comp_cp * (float)$d->child_part_cost);
        }

        $process = $details[0];
        $cav_std = ($process->cavity_standard > 0) ? $process->cavity_standard : 1;
        
        // Sub Total 2 (Process)
        $inj_cost = ($process->cycle_time / $cav_std) * $process->plain_rate_sec;
        $sec_process_cost = $process->cycle_time_process * $process->labour_cost;
        $sub_total_2 = $inj_cost + $sec_process_cost;

        // Sub Total 3 (Amortization)
        $sub_total_3 = ($process->mold_depreciation * 5);

        $total_cost = $sub_total_1 + $sub_total_2 + $sub_total_3 
                    + (float)$process->adm_foh_cost + (float)$process->ng_ratio_cost 
                    + (float)$process->mtn_cost + (float)$process->total_packing_cost 
                    + (float)$process->transportasion_cost_pcs + (float)$process->purging_cost 
                    + (float)$process->profit_nominal;

        return $total_cost;
    }

     //GET DATA
     public function readWeight()
     {
        $post = $this->input->post();
        $item_fg = $this->crud->read("item_fg", [] ,["id" => $post['item_fg_id']]);
        echo json_encode($item_fg);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
            $filter_quotation_number = @base64_decode($get['filter_quotation_number']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('*');
            $this->db->from('quotations a');
            if (!empty($filter_item_fg_id)) {
                $this->db->where('item_fg_id', $filter_item_fg_id);
            }

            if (!empty($filter_quotation_number)) {
                $this->db->where('quotation_number', $filter_quotation_number);
            }
            $this->db->group_by('quotation_number');
            $this->db->order_by('item_fg_number', 'DESC');
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
            $filter_item_fg_id = base64_decode($this->input->get('filter_item_fg_id'));

            $this->db->select('a.*');
            $this->db->from('quotations a');
            $this->db->where('a.quotation_number', $number);
            $this->db->like('a.item_fg_id', $filter_item_fg_id);
            $this->db->group_by('a.id');
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // UPDATE DATA
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $quotation_number = base64_decode($this->input->get('quotation_number'));

            $this->db->select('a.*');
            $this->db->from('quotations a');
            $this->db->where('a.quotation_number', $quotation_number);
            $this->db->order_by('a.id', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }
    
    //CREATE
    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $dataFinal = array(
                //field
                "id" => $post['id'],
                "quotation_date" => $post['quotation_date'],
                "quotation_number" => $post['quotation_number'],
                "customer_id" => $post['customer_id'],
                "quotation_to" => $post['quotation_to'],
                "quotation_attn" => $post['quotation_attn'],
                "quotation_cc" => $post['quotation_cc'],
                "item_fg_id" => $post['item_fg_id'],
                "item_fg_number" => $post['item_fg_number'],
                "item_fg_name" => $post['item_fg_name'],
                "quotation_number2" => $post['quotation_number2'],
                "revision_quotation_number" => $post['revision_quotation_number'],
                "price" => $post['price'],
                "moq" => $post['moq'],
                "mpq" => $post['mpq'],
                "remark" => $post['remark']
            );
            
            if (@$post['id'] != "") {
                $old_data = $this->crud->read('quotations', [], ["id" => $post['id']]);
                
                $current_revision = !empty($old_data->revision) ? (int)$old_data->revision : 0;
                $dataFinal['revision'] = $current_revision + 1;

                $send = $this->crud->update('quotations', ["id" => $post['id']], $dataFinal);
            } else {
                $send = $this->crud->create('quotations', $post);
            }
            echo $send;
        } else {
            show_error("Cannot Process your request");
        }
    }

    //DELETE DATA
    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('quotations', $data);
        echo $send;
    }

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
    //             'item_fg_id' => $data->val($i, 2),
    //             'item_rm_id' => $data->val($i, 3),
    //             'type' => $data->val($i, 4),
    //             'recyle' => $data->val($i, 5),
    //             'composition' => $data->val($i, 6),
    //             'remark' => $data->val($i, 7)
    //         );
    //     }
    //     $datas['total'] = count($datas);
    //     echo json_encode($datas);
    //     unlink($_FILES['file_upload']['name']);
    // }

    // public function uploadclearFailed()
    // {
    //     @unlink('failed/quotations.txt');
    // }

    // public function uploadcreateFailed()
    // {
    //     if ($this->input->post()) {
    //         $message = $this->input->post('message');
    //         $textFailed = fopen('failed/quotations.txt', 'a');
    //         fwrite($textFailed, $message . "\n");
    //         fclose($textFailed);
    //     }
    // }

    //UPLOAD DOWNLOAD FAILED
    // public function uploadDownloadFailed()
    // {
    //     $file = "failed/quotations.txt";
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
    //         $item_fg = $this->crud->read('item_fg', [], ["id" => $data['item_fg_id']]);
    //         $item_rm = $this->crud->read('item_rm', [], ["id" => $data['item_rm_id']]);

    //         $item_fg_id = $data['item_fg_id'];
    //         $menu_loading = $this->crud->query("SELECT a.item_fg_id, SUM(a.runner) as runner, b.cavity_standard
    //         FROM menu_loadings a JOIN molds b on a.mold_id = b.id
    //         WHERE a.item_fg_id = '$item_fg_id' group by a.item_fg_id");

            
    //         $quotations = $this->crud->read('quotations', [], ["item_fg_id" => $data['item_fg_id'], "item_rm_id" => $data['item_rm_id']]);

    //         if (empty($item_fg->id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Part ID" . $data['item_fg_id'] ." Not Found", "theme" => "error"));
    //         } elseif (empty($item_rm->id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Part ID" . $data['item_rm_id'] ." Not Found", "theme" => "error"));
    //         } elseif (empty($menu_loading[0]->item_fg_id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Part ID" . $data['item_fg_id'] . " in Menu Loading Not Found", "theme" => "error"));
    //         } elseif ($item_rm->item_family_id == 'P06' && $data['composition'] != "") {
    //             echo json_encode(array("title" => "Alert", "message" => "Part ID" . $data['item_rm_id'] ." Product Family is VIRGIN ", "theme" => "error"));
    //         } elseif (!empty($quotations->item_rm_id)) {
    //             echo json_encode(array("title" => "Duplicated", "message" => "Part ID" . $data['item_rm_id'] . " is Duplicate Data", "theme" => "error"));
    //         } else {
    //              // Hitung nilai untuk field composition
    //             $weight = $item_fg->weight;
    //             $runner = $menu_loading[0]->runner;
    //             $cavity_standard = $menu_loading[0]->cavity_standard;

    //             // if ($item_rm->item_family_id == 'P06') {
    //             //     $dataFinal['composition'] = (floatval($weight) + floatval($runner / $cavity_standard));
    //             // } elseif ($item_rm->item_family_id != 'P06') {
    //             //     $dataFinal['composition'] = $data['composition'];
    //             // }

    //             $dataFinal = array(
    //                 //field
    //                 "item_fg_id" => $data['item_fg_id'],
    //                 "item_rm_id" => $data['item_rm_id'],
    //                 "type" => $data['type'],
    //                 "recyle" => $data['recyle'],
    //                 "remark" => $data['remark'],
    //             );

    //             if ($item_rm->item_family_id == 'P06') {
    //                 $dataFinal['composition'] = (floatval($weight) + floatval($runner / $cavity_standard));
    //             } elseif ($item_rm->item_family_id != 'P06') {
    //                 $dataFinal['composition'] = $data['composition'];
    //             }

    //             $send   = $this->crud->create('quotations', $dataFinal);
    //             echo $send;
    //         }
    //     }
    // }

    // PRINT & EXCEL DATA
    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=quotations_$format.xls");
        }

        $get = $this->input->get();
        $filter_item_fg_id = @base64_decode($get['filter_item_fg_id']);
        $filter_quotation_number = @base64_decode($get['filter_quotation_number']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select('*');
        $this->db->from('quotations a');
        if (!empty($filter_item_fg_id)) {
            $this->db->where('item_fg_id', $filter_item_fg_id);
        }

        if (!empty($filter_quotation_number)) {
            $this->db->where('quotation_number', $filter_quotation_number);
        }
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#quotations {border-collapse: collapse;width: 100%;font-size: 12px;}#quotations td, #quotations th {border: 1px solid #ddd;padding: 2px;}#quotations tr:nth-child(even){background-color: #f2f2f2;}#quotations tr:hover {background-color: #ddd;}#quotations th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>QUOTATIONS</h3>
            </div>
        </center>
        
        <table id="quotations" border="1">
            <tr>
                <th width="20">No</th>
                <th>Quotation Number</th>
                <th>Quotation Date</th>
                <th>Quotation To</th>
                <th>Quotation Attn</th>
                <th>Quotation CC</th>
                <th>Product Number</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Moq</th>
                <th>Mpq</th>
                <th>Remark</th>
                <th>Created By</th>
                <th>Created Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['quotation_number'] . '</td>
                    <td>' . $data['quotation_date'] . '</td>
                    <td>' . $data['quotation_to'] . '</td>
                    <td>' . $data['quotation_attn'] . '</td>
                    <td>' . $data['quotation_cc'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                    <td style="mso-number-format:\@;">' . $data['item_fg_name'] . '</td>
                    <td>' . $data['price'] . '</td>
                    <td>' . $data['moq'] . '</td>
                    <td>' . $data['mpq'] . '</td>
                    <td>' . $data['remark'] . '</td>
                    <td>' . $data['created_by'] . '</td>
                    <td>' . $data['created_date'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_quotation($encoded_quotation_number)
    {
        $quotation_number = base64_decode($encoded_quotation_number);

        // 1. Ambil Data Header
        $this->db->where('quotation_number', $quotation_number);
        $this->db->where('deleted', 0);
        $header = $this->db->get('quotations')->row();

        if (!$header) { 
            die("Data tidak ditemukan."); 
        }

        // 2. Ambil Data Detail dengan Join ke Material
        $this->db->select('
            q.*, 
            cp.mold_price,
            cp.depreciation, 
            f_vg.name as material_name_vg
        ');
        $this->db->from('quotations q');
        $this->db->join('breakdown_prices bp', 'q.quotation_number2 = bp.quotation_number AND q.revision_quotation_number = bp.revision_quotation_number', 'left');
        $this->db->join('cost_patterns cp', 'bp.item_fg_id = cp.item_fg_id AND bp.p_month = cp.p_month AND bp.p_year = cp.p_year AND bp.revision = cp.revision', 'left');
        $this->db->join('item_rm ir_vg', 'ir_vg.id = cp.item_rm_id_vg', 'left');
        $this->db->join('item_family_subs f_vg', 'f_vg.id = ir_vg.item_sub_family_id', 'left');
        $this->db->where('q.quotation_number', $quotation_number);
        $this->db->where('q.deleted', 0);
        $this->db->order_by('q.quotation_number2', 'ASC'); 
        $items = $this->db->get()->result();

        // 3. LOGIKA PENGELOMPOKAN (Grouping)
        $grouped_data = [];
        $show_mold = false;

        foreach ($items as $row) {
            $q2 = $row->quotation_number2;
            if (!isset($grouped_data[$q2])) {
                $grouped_data[$q2] = [
                    'details' => $row,
                    'materials' => []
                ];
            }
            
            $mat_name = !empty($row->material_name_vg) ? $row->material_name_vg : '-';
            
            if (!in_array($mat_name, $grouped_data[$q2]['materials'])) {
                $grouped_data[$q2]['materials'][] = $mat_name;
            }

            if ($row->depreciation == 'YES') { 
                $show_mold = true; 
            }
        }

        // 4. GENERATE HTML
        $html = '<html>
                <head>
                    <title>Quotation - ' . $header->quotation_number . '</title>
                    <style>
                        body { font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; }
                        #customers { border-collapse: collapse; width: 100%; font-size: 11px; margin-top: 10px; }
                        #customers td, #customers th { border: 1px solid black; padding: 4px; vertical-align: middle; }
                        #customers th { text-align: center; background-color: #f2f2f2; }
                        .info-table { width: 100%; border: none; font-size: 11px; margin-bottom: 15px; }
                        .info-table td { border: none !important; padding: 2px; }
                        @media screen { .print { display: none !important; } .noprint { display: block !important; } }
                        @media print { 
                            .noprint { display: none !important; } 
                            .print { display: block !important; } 
                            @page { size: A4 portrait; margin: 1cm; } 
                        }
                    </style>
                </head>
                <body onload="window.print()">
                    <div style="margin-top:20%;" class="noprint">
                        <center>
                            <h1>Press CTRL + P for Print</h1>
                            <p>A4 Portrait Mode</p>
                        </center>
                    </div>

                    <div class="print">
                        <table class="info-table">
                            <tr>
                                <td width="12%">To</td><td width="40%">: <b>' . $header->quotation_to . '</b></td>
                                <td width="10%">Date</td><td>: ' . date("d F Y", strtotime($header->quotation_date)) . '</td>
                            </tr>
                            <tr>
                                <td>Attn</td><td>: ' . $header->quotation_attn . '</td>
                                <td>Doc No</td><td>: ' . $header->quotation_number . '</td>
                            </tr>
                            <tr>
                                <td>Cc</td><td>: ' . $header->quotation_cc . '</td>
                                <td></td><td></td>
                            </tr>
                        </table>
                        
                        <p style="font-size:11px;">Dear ' . $header->quotation_attn . '</p>
                        <p style="font-size:11px; font-weight:bold;">Subject: Quotation</p>
                        <p style="font-size:11px;">Thank you for your inquiry. We are pleased to provide you with the following quotation :</p>
                        
                        <table id="customers">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Part Number</th>
                                    <th>Part Name</th>
                                    <th>Material</th>
                                    <th width="15%">Price Part</th>';
                                    if ($show_mold) { $html .= '<th width="15%">Price Mold</th>'; }
        $html .= '                  <th width="8%">MOQ</th>
                                    <th width="8%">MPQ</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody>';

                            $no = 1;
                            foreach ($grouped_data as $q2_id => $data) {
                                $item = $data['details'];
                                $mats = $data['materials'];
                                $rowspan = count($mats);

                                foreach ($mats as $index => $mat_name) {
                                    $html .= '<tr>';
                                    
                                    if ($index === 0) {
                                        $html .= '<td align="center" rowspan="' . $rowspan . '">' . $no++ . '</td>';
                                        $html .= '<td rowspan="' . $rowspan . '">' . $item->item_fg_number . '</td>';
                                        $html .= '<td rowspan="' . $rowspan . '">' . $item->item_fg_name . '</td>';
                                        
                                        $html .= '<td align="center">' . $mat_name . '</td>';
                                        
                                        $html .= '<td rowspan="' . $rowspan . '">
                                                    <div style="float:left">Rp</div>
                                                    <div style="float:right">' . number_format($item->price, 2, ',', '.') . '</div>
                                                    <div style="clear:both"></div>
                                                </td>';

                                        if ($show_mold) {
                                            $mold_val = ($item->depreciation == "YES") ? number_format($item->mold_price, 2, ',', '.') : "0";
                                            $html .= '<td align="right" rowspan="' . $rowspan . '">' . $mold_val . '</td>';
                                        }

                                        $html .= '<td align="right" rowspan="' . $rowspan . '">' . number_format($item->moq, 0, ',', '.') . '</td>';
                                        $html .= '<td align="right" rowspan="' . $rowspan . '">' . number_format($item->mpq, 0, ',', '.') . '</td>';
                                        $html .= '<td rowspan="' . $rowspan . '">' . $item->remark . '</td>';
                                    } else {
                                        $html .= '<td align="center">' . $mat_name . '</td>';
                                    }
                                    
                                    $html .= '</tr>';
                                }
                            }

        $html .= '      </tbody>
                        </table>

                        <div style="margin-top: 20px; font-size: 11px; max-width: 50%; word-wrap: break-word;">
                            <b>Noted:</b><br>
                            ' . nl2br($header->notes) . '
                        </div>

                        <div style="margin-top: 40px; float: left; width: 250px; font-size: 11px;">
                            Best regards,<br><br><br><br><br>
                            <b><u>Rendhika D Harsono</u></b><br>
                            Director<br>
                            PT Banshu Plastic Indonesia
                        </div>
                    </div>
                </body>
                </html>';

        echo $html;
    }
}
