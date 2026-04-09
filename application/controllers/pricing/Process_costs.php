<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Process_costs extends CI_Controller
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
            $this->load->view('pricing/process_costs');
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

    public function readItems()
    {
        $post = isset($_POST['q']) ? $this->db->escape_like_str($_POST['q']) : "";

        $sql = "
            SELECT 
                a.*,
                b.cycle_time,
                b.cycle_time_process,
                b.cavity_standard,
                c.toonage,
                c.volume AS machine_volume,
                IFNULL(m.price, 0) AS mold_price,
                m.lifetime,
                m.mold_id,
                m.mold_name
            FROM item_fg a
            LEFT JOIN menu_loadings_npd b ON a.id = b.item_fg_id
            LEFT JOIN machines c ON c.id = b.machine_id
            LEFT JOIN (
                SELECT 
                    mi.item_fg_id,
                    mp.price,
                    md.lifetime,
                    md.id as mold_id,
                    md.mold_name
                FROM mold_items mi
                JOIN mold_prices mp ON mp.mold_id = mi.mold_id
                JOIN molds md ON md.id = mi.mold_id
                GROUP BY mi.item_fg_id
            ) m ON m.item_fg_id = a.id
            WHERE (a.number LIKE '%{$post}%' OR a.name LIKE '%{$post}%')
            ORDER BY a.number ASC
        ";

        $send = $this->crud->query($sql);
        echo json_encode($send);
    }

    public function readPeriod($select)
    {
        if ($select == "month") {
            $month = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
            foreach ($month as $key => $value) {
                $months[] = array("id" => $key, "name" => $value);
            }

            echo json_encode($months);
        } else if ($select == "year") {
            $year_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
            $year_now = date('Y', strtotime('+1 year', strtotime(date('Y'))));
            for ($i = $year_now; $i >= $year_before; $i--) {
                $years[] = array("id" => $i, "name" => $i);
            }

            echo json_encode($years);
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function get_plain_rate()
    {
        $toonage = $this->input->post('toonage');
        $year = $this->input->post('year');

        if (!$toonage || !$year) {
            echo json_encode(['plain_rate_sec' => 0]);
            return;
        }

        $this->db->select('plain_rate_sec');
        $this->db->from('rates');
        $this->db->where('toonage', $toonage);
        $this->db->where('year', $year);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            echo json_encode([
                'status' => 'success',
                'plain_rate_sec' => $query->row()->plain_rate_sec
            ]);
        } else {
            echo json_encode([
                'status' => 'empty',
                'plain_rate_sec' => 0
            ]);
        }
    }

    public function get_total_mat()
    {
        $item_fg_id = $this->input->post('item_fg_id');
        $year       = $this->input->post('year');
        $month       = $this->input->post('month');

        if (!$item_fg_id || !$year) {
            echo json_encode([
                'status' => 'empty',
                'total_material_cost' => 0,
                'total_material_cost_virgin' => 0
            ]);
            return;
        }

        // Ambil revision tertinggi
        $sub = $this->db->select_max('revision')
            ->where('item_fg_id', $item_fg_id)
            ->where('p_year', $year)
            ->where('p_month', $month)
            ->get_compiled_select('material_costs');

        $this->db->select([
            'MAX(total_material_cost) AS total_material_cost',
            'SUM(CASE 
                WHEN UPPER(TRIM(product_family)) = "VIRGIN" 
                THEN material_cost 
                ELSE 0 
            END) AS total_material_cost_virgin'
        ]);
        $this->db->from('material_costs');
        $this->db->where('item_fg_id', $item_fg_id);
        $this->db->where('p_year', $year);
        $this->db->where('p_month', $month);
        $this->db->where("revision = ($sub)", null, false);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $row = $query->row();

            echo json_encode([
                'status' => 'success',
                'total_material_cost' => (float)$row->total_material_cost,
                'total_material_cost_virgin' => (float)$row->total_material_cost_virgin
            ]);
        } else {
            echo json_encode([
                'status' => 'empty',
                'total_material_cost' => 0,
                'total_material_cost_virgin' => 0
            ]);
        }
    }

    public function getPackagingVolume() 
    {
        $item_fg_id = $this->input->post('item_fg_id');
        $p_month    = $this->input->post('p_month');
        $p_year     = $this->input->post('p_year');

        $this->db->select('volume');
        $this->db->from('packaging_transportation_costs');
        $this->db->where('item_fg_id', $item_fg_id);
        $this->db->where('p_month', $p_month);
        $this->db->where('p_year', $p_year);
        
        $query = $this->db->get()->row();

        if ($query) {
            echo json_encode([
                'status' => 'success', 
                'volume' => $query->volume
            ]);
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Data tidak ditemukan'
            ]);
        }
    }

    public function datatables()
    {
        if ($this->input->post()) {
            $filter_period_month = $this->input->get('filter_period_month');
            $filter_period_year = $this->input->get('filter_period_year');
            $filter_item_fg_id = $this->input->get('filter_item_fg_id');

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select("a.*");
            $this->db->from('process_costs a');
            $this->db->where('a.deleted', 0);

            if ($filter_period_year != "") {
                $this->db->where('a.p_year', $filter_period_year);
            }

            if ($filter_period_month != "") {
                $this->db->where('a.p_month', $filter_period_month);
            }

            if ($filter_item_fg_id != "") {
                $this->db->where('a.item_fg_id', $filter_item_fg_id);
            }

           
            // $this->db->group_by('a.wo_no');
            $this->db->order_by('a.created_date', 'ASC');
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

    public function create()
    {
        $post = $this->input->post();

        if (empty($post['item_fg_id']) || $post['revision'] === '' || empty($post['p_year']) || empty($post['p_month'])
        ) {
            echo json_encode([
                'theme'   => 'error',
                'title'   => 'Error',
                'message' => 'Key data tidak lengkap'
            ]);
            return;
        }

        $where = [
            'item_fg_id' => $post['item_fg_id'],
            'revision'   => $post['revision'],
            'p_year'     => $post['p_year'],
            'p_month'    => $post['p_month']
        ];

        $existing = $this->db->get_where('process_costs', $where)->row();

        if ($existing) {

            unset(
                $post['id'],
                $post['item_fg_id'],
                $post['revision'],
                $post['p_year'],
                $post['p_month']
            );

            // UPDATE
            $this->crud->update('process_costs', $where, $post);

            echo json_encode([
                'theme'   => 'success',
                'title'   => 'Success',
                'message' => 'Data Update Successfully',
                'status'  => 'updated'
            ]);
            return;
        }

        $id = $this->crud->create('process_costs', $post);

        echo json_encode([
            'theme'   => 'success',
            'title'   => 'Success',
            'message' => 'Data Save Successfully',
            'status'  => 'created',
            'id'      => $id
        ]);
    }

    // public function update()
    // {
    //     if ($this->input->post()) {
    //         $id   = base64_decode($this->input->get('id'));
    //         $post = $this->input->post();
    //         if (isset($post['wo_no_assembly']) && $post['wo_no_assembly'] === '') {
    //             $post['wo_no_assembly'] = null;
    //         }
    //         $send = $this->crud->update('production_schedules', ["id" => $id], $post);
    //         echo $send;
    //     } else {
    //         show_error("Cannot Process your request");
    //     }
    // }

    public function delete()
    {
        $data = $this->input->post();
        $send = $this->crud->delete('process_costs', ["id" => $data['id']]);
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
    //             'wo_no' => $data->val($i, 2),
    //             'period' => $data->val($i, 3),
    //             'machine_no' => $data->val($i, 4),
    //             'lot_no' => $data->val($i, 5),
    //             'mold_id' => $data->val($i, 6),
    //             'trans_date' => $data->val($i, 7),
    //             'division' => $data->val($i, 8),
    //             'item_fg_number' => $data->val($i, 9),
    //             'qty' => $data->val($i, 10)
    //         );
    //     }
    //     $datas['total'] = count($datas);
    //     echo json_encode($datas);
    //     unlink($_FILES['file_upload']['name']);
    // }
    // public function uploadclearFailed()
    // {
    //     @unlink('failed/production_schedules.txt');
    // }
    // public function uploadcreateFailed()
    // {
    //     if ($this->input->post()) {
    //         $message = $this->input->post('message');
    //         $textFailed = fopen('failed/production_schedules.txt', 'a');
    //         fwrite($textFailed, $message . "\n");
    //         fclose($textFailed);
    //     }
    // }
    // //UPLOAD DOWNLOAD FAILED
    // public function uploadDownloadFailed()
    // {
    //     $file = "failed/production_schedules.txt";
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
    //         $item_fg = $this->crud->read('item_fg', [], ["number" => $data['item_fg_number']]);
    //         $machine = $this->crud->read('machines', [], ["number" => $data['machine_no']]);
    //         $ps = $this->crud->read('production_schedules', [], ["wo_no" => $data['wo_no']]);
    //         $period = $data['period'];
    //         $year = substr($period, 0, 4);
    //         $month = substr($period, 4, 2);

    //         if (empty($item_fg->id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Item Finish Good " . $data['item_fg_number'] . " Not Found", "theme" => "error"));
    //         } elseif (empty($machine->number)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Machine " . $data['machine_no'] . " Not Found", "theme" => "error"));
    //         } elseif (!empty($ps->wo_no)) {
    //             echo json_encode(array("title" => "Duplicated", "message" => "Wo No  " . $data['wo_no'] . " Duplicated", "theme" => "error"));
    //         } else {

    //         $total_purgings = 0;

    //         $colors = $item_fg->color;
    //         if ($colors == 'BLACK' || $colors == 'FR BLACK P B B') {
    //             $colors = 'BLACK';
    //         }elseif($colors == 'WHITE' || $colors == 'CLEAR WHITE' || $colors == 'BRIGHT WHITE' || $colors == 'DIFFUSE WHITE'){
    //             $colors = 'CLEAR';
    //         }else{
    //             $colors = 'COLORFULL';
    //         }
            
    //         $machines = $machine->id;
    //         $purging = $this->crud->read('purgings', [], ["machine_id" => $machines, "kind" => $colors]);
    //         if ($purging) {
    //             $total_purgings = $purging->total !== null ? $purging->total : 0;
    //         }
    
    //             $dataFinal = array(
    //                 //field
    //                 "wo_no" => $data['wo_no'],
    //                 "period" => $data['period'],
    //                 "year" => $year,
    //                 "month" => $month,
    //                 "machine_id" => $machine->id,
    //                 "lot_no" => $data['lot_no'],
    //                 "mold_id" => $data['mold_id'],
    //                 "trans_date" => $data['trans_date'],
    //                 "item_fg_id" => $item_fg->id,
    //                 "item_fg_name" => $item_fg->name,
    //                 "color" => $colors,
    //                 "total_purging" => $total_purgings,
    //                 "division" => $data['division'],
    //                 "qty" => $data['qty'],
    //                 "status_subcont" => $item_fg->status_subcont,
    //                 "subcont_type" => $item_fg->subcont_type,
    //             );
    //             $send   = $this->crud->create('production_schedules', $dataFinal);
    //             echo $send;
    //         }
    //     }
    // }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=production_schedules_$format.xls");
        }

        $filter_period_month = $this->input->get('filter_period_month');
        $filter_period_year = $this->input->get('filter_period_year');
        $filter_item_fg_id = $this->input->get('filter_item_fg_id');

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $this->db->select("a.*");
        $this->db->from('process_costs a');
        $this->db->where('a.deleted', 0);

        if ($filter_period_year != "") {
                $this->db->where('a.p_year', $filter_period_year);
        }

        if ($filter_period_month != "") {
            $this->db->where('a.p_month', $filter_period_month);
        }

        if ($filter_item_fg_id != "") {
            $this->db->where('a.item_fg_id', $filter_item_fg_id);
        }
       
        $this->db->order_by('a.created_date', 'ASC');
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
                                <small>PROCESS COSTS</small>
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
                    <th>Month</th>
                    <th>Year</th>
                    <th>Rev</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>CT</th>
                    <th>CT Process</th>
                    <th>Cavity</th>
                    <th>Tonage</th>
                    <th>Plain Rate</th>
                    <th>Process Cost</th>
                    <th>Material Cost</th>
                    <th>NG %</th>
                    <th>NG Cost</th>
                    <th>ADM %</th>
                    <th>ADM Cost</th>
                    <th>MTN %</th>
                    <th>MTN Cost</th>
                    <th>Profit %</th>
                    <th>Profit</th>
                    <th>Purging</th>
                    <th>Purging Cost</th>
                    <th>MOQ</th>
                    <th>Mold Dep.</th>
                </tr>';
        $no = 1;
        foreach ($records as $data) {

            $html .= '<tr>
                        <td style="text-align:center;">' . $no . '</td>
                        <td style="text-align:center;">' . $data['p_month'] . '</td>
                        <td style="text-align:center;">' . $data['p_year'] . '</td>
                        <td style="text-align:center;">' . $data['revision'] . '</td>
                        <td style="mso-number-format:\@;">' . $data['item_fg_number'] . '</td>
                        <td>' . $data['item_fg_name'] . '</td>
                        <td style="text-align:right;">' . number_format($data['cycle_time'], 2) . '</td>
                        <td style="text-align:right;">' . number_format($data['cycle_time_process'], 2) . '</td>
                        <td style="text-align:center;">' . $data['cavity_standard'] . '</td>
                        <td style="text-align:center;">' . $data['toonage'] . '</td>
                        <td style="text-align:right;">' . number_format($data['plain_rate_sec'], 2) . '</td>
                        <td style="text-align:right;">' . number_format($data['total_process_cost'], 2) . '</td>
                        <td style="text-align:right;">' . number_format($data['total_material_cost'], 2) . '</td>
                        <td style="text-align:right;">' . number_format($data['ng_ratio'], 2) . '</td>
                        <td style="text-align:right;">' . number_format($data['ng_ratio_cost'], 2) . '</td>
                        <td style="text-align:right;">' . number_format($data['adm_foh'], 2) . '</td>
                        <td style="text-align:right;">' . number_format($data['adm_foh_cost'], 2) . '</td>
                        <td style="text-align:right;">' . number_format($data['mtn'], 2) . '</td>
                        <td style="text-align:right;">' . number_format($data['mtn_cost'], 2) . '</td>
                        <td style="text-align:right;">' . number_format($data['profit'], 2) . '</td>
                        <td style="text-align:right;">' . number_format($data['profit_nominal'], 2) . '</td>
                        <td style="text-align:center;">' . $data['purging'] . '</td>
                        <td style="text-align:right;">' . number_format($data['purging_cost'], 2) . '</td>
                        <td style="text-align:right;">' . number_format($data['moq'], 2) . '</td>
                        <td style="text-align:right;">' . number_format($data['mold_depreciation'], 2) . '</td>
                    </tr>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
