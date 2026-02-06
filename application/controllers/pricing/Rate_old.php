<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');
class Rate extends CI_Controller
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
        $this->form_validation->set_rules('item_fg_id', 'Product No.', 'required|min_length[1]|max_length[20]|is_unique[rate.item_fg_id]');
        $this->form_validation->set_rules('item_rm_id', 'Part No.', 'required|min_length[1]|max_length[20]|is_unique[rate.item_rm_id]');
    }
    //HALAMAN UTAMA
    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('pricing/rate');
        } else {
            redirect('error_access');
        }
    }
    //GET DATA
    public function reads()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->reads('rates', ["item_fg_id" => $post]);
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
            $year_now = date('Y', strtotime('+4 year', strtotime(date('Y'))));
            for ($i = $year_now; $i >= $year_before; $i--) {
                $years[] = array("id" => $i, "name" => $i);
            }

            echo json_encode($years);
        } else {
            show_error("Cannot Process your request");
        }
    }

    public function readEficiency()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT efisiensi
        FROM component_rates  
        WHERE efisiensi like '%$post%'");
        echo json_encode($send);
    }

    
    public function readsEficiency()
    {
        $post = isset($_POST['q']) ? $_POST['q'] : "";
        $send = $this->crud->query("SELECT DISTINCT efisiensi
        FROM rates  
        WHERE efisiensi like '%$post%'");
        echo json_encode($send);
    }

    public function readTonage()
    {
        $post = $this->input->post('q') ?? '';

        $sql = "
            SELECT 
                a.toonage,
                a.price,
                a.depretiation,
                a.interest,
                a.manpower,
                (
                    SELECT COUNT(*) 
                    FROM machines m 
                    WHERE m.toonage = a.toonage
                ) AS jumlah_machine
            FROM machine_prices a
            WHERE a.toonage LIKE ?
            GROUP BY a.toonage, a.price
        ";

        $send = $this->db->query($sql, ["%{$post}%"])->result();
        echo json_encode($send);
    }

     //GET DATA
     public function readYear()
     {
        $post = $this->input->post();
        $year = $this->crud->read("component_rates", [] ,["year" => $post['year']]);
        echo json_encode($year);
    }

    //GET DATATABLES
    public function datatables()
    {
        if ($this->input->post()) {
            $get = $this->input->get();
            $filter_year = @base64_decode($get['filter_year']);
            $filter_eficiency = @base64_decode($get['filter_eficiency']);

            $page = $this->input->post('page');
            $rows = $this->input->post('rows');
            //Pagination 1-10
            $page   = isset($page) ? intval($page) : 1;
            $rows   = isset($rows) ? intval($rows) : 10;
            $offset = ($page - 1) * $rows;
            $result = array();
            //Select Query
            $this->db->select('a.*');
            $this->db->from('rates a');
            if (!empty($filter_year)) {
                $this->db->where('a.year', $filter_year);
            }

            if (!empty($filter_eficiency)) {
                $this->db->where('a.efisiensi', $filter_eficiency);
            }
            $this->db->group_by('a.year');
            $this->db->group_by('a.efisiensi');
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
            $year = base64_decode($this->input->get('year'));
            $eficiency = base64_decode($this->input->get('eficiency'));
            $filter_year = base64_decode($this->input->get('filter_year'));
            $filter_eficiency = base64_decode($this->input->get('filter_eficiency'));

            $this->db->select('a.*');
            $this->db->from('rates a');
            $this->db->where('a.year', $year);
            $this->db->where('a.efisiensi', $eficiency);
            $this->db->group_by('a.year');
            $this->db->group_by('a.efisiensi');
            $this->db->group_by('a.toonage');
            $this->db->order_by('a.toonage', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // UPDATE DATA
    public function datatableUpdates()
    {
        if ($this->input->get()) {
            $year = base64_decode($this->input->get('year'));
            $eficiency = base64_decode($this->input->get('eficiency'));

            $this->db->select('a.*');
            $this->db->from('rates a');
            $this->db->where('a.year', $year);
            $this->db->where('a.efisiensi', $eficiency);
            $this->db->group_by('a.year');
            $this->db->group_by('a.efisiensi');
            $this->db->group_by('a.toonage');
            $this->db->order_by('a.toonage', 'ASC');
            $records = $this->db->get()->result_array();

            echo json_encode($records);
        }
    }

    // public function create()
    // {
    //     if ($this->input->post()) {
    //         $post = $this->input->post();
    //         $dataFinal = array(
    //             "year"                      => $post['year'],
    //             "efisiensi"                 => $post['efisiensi'],
    //             "plain_rate_sec"            => $post['plain_rate_sec'],
    //             "plain_rate_hour"           => $post['plain_rate_hour'],
    //             "foh"                       => $post['foh'],
    //             "energy"                    => $post['energy'],
    //             "labour"                    => $post['labour'],
    //             "labour_cost"               => $post['labour_cost'],
    //             "machine_depretiation_cost" => $post['machine_depretiation_cost'],
    //             "price"                     => $post['price'],
    //             "depretiation"              => $post['depretiation'],
    //             "interest"                  => $post['interest'],
    //             "shift"                     => $post['shift'],
    //         );

    //         if (!empty($post['id'])) {

    //             $send = $this->crud->update('rates', ["id" => $post['id']], $dataFinal);
    //             echo $send;
    //             return;
    //         }

    //         $duplicate = $this->db
    //             ->where('year', $post['year'])
    //             ->where('efisiensi', $post['efisiensi'])
    //             ->get('rates')
    //             ->row();

    //         if ($duplicate) {
    //             $send = $this->crud->update('rates', ["id" => $duplicate->id], $dataFinal);

    //         } else {
    //             $send = $this->crud->create('rates', $dataFinal);
    //         }

    //         echo $send;

    //     } else {
    //         show_error("Cannot process your request");
    //     }
    // }

    public function create()
    {
        // ambil raw JSON body
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);

        // jika form-data biasa, pakai input->post
        $post = $this->input->post() ?: ($json ?: []);

        if (empty($post)) {
            echo json_encode([
                'status' => false,
                'message' => 'No input provided',
                'theme' => 'error'
            ]);
            return;
        }
        $rows = [];

        // rows = JSON array
        if (isset($post['rows']) && is_array($post['rows'])) {
            $rows = $post['rows'];
        }
        // rows = JSON string (perlu decode)
        else if (isset($post['rows']) && is_string($post['rows'])) {
            $decoded = json_decode($post['rows'], true);
            if (is_array($decoded)) {
                $rows = $decoded;
            }
        }

        if (empty($rows)) {
            echo json_encode([
                'status' => false,
                'message' => 'Rows data missing',
                'theme' => 'error'
            ]);
            return;
        }
        $year      = $post['year']      ?? null;
        $efisiensi = $post['efisiensi'] ?? null;

        if (!$year || !$efisiensi) {
            echo json_encode([
                'status' => false,
                'message' => 'Missing year or efisiensi',
                'theme' => 'error'
            ]);
            return;
        }

        $total_machine_price = 0;
        foreach ($rows as $r) {

            $price_unit = floatval($r['price'] ?? 0);
            $qty        = floatval($r['jumlah_machine'] ?? 0);

            $total_machine_price += ($price_unit * $qty);
        }

        if ($total_machine_price <= 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Total machine price is zero',
                'theme' => 'error'
            ]);
            return;
        }

        $summary = ['insert' => 0, 'update' => 0, 'errors' => []];

        foreach ($rows as $r) {

            $price          = floatval($r['price'] ?? 0);
            $labour         = floatval($r['labour'] ?? 0);
            $labour_cost    = floatval($r['labour_cost'] ?? 0);
            $machine_depr   = floatval($r['machine_depretiation_cost'] ?? 0);
            $depretiation   = floatval($r['depretiation'] ?? 0);
            $interest       = floatval($r['interest'] ?? 0);
            $shift          = floatval($r['shift'] ?? 0);
            $jumlah_machine = floatval($r['jumlah_machine'] ?? 0);

            // row-level prioritas
            $electricity = floatval($r['electricity'] ?? ($post['electricity'] ?? 0));
            $overhead    = floatval($r['overhead'] ?? ($post['overhead'] ?? 0));

            // efisiensi -> dibagi persen
            $ef = floatval($efisiensi);
            if ($ef <= 0) $ef = 1;
            $ef = $ef / 100;

            // --- ENERGY ---
            $energy = ($price > 0 && $electricity > 0) ? ($price / $total_machine_price) * $electricity / 21 / 24 / 3600 / $ef : 0;

            // --- FOH ---
            $foh = ($price > 0 && $overhead > 0) ? ($price / $total_machine_price) * $overhead / 21 / 24 / 3600 / $ef : 0;

            // plain rates
            $plain_rate_sec  = $foh + $energy + ($labour * $labour_cost) + $machine_depr;
            $plain_rate_sec  = round($plain_rate_sec, 2);

            $plain_rate_hour = $plain_rate_sec * 3600;
            $plain_rate_hour = round($plain_rate_hour, 2);

            // final data
            $dataFinal = [
                'year' => $year,
                'efisiensi' => $efisiensi,
                'toonage' => $r['toonage'] ?? null,
                'plain_rate_sec' => $plain_rate_sec,
                'plain_rate_hour' => $plain_rate_hour,
                'foh' => $foh,
                'energy' => $energy,
                'labour' => $labour,
                'labour_cost' => $labour_cost,
                'machine_depretiation_cost' => $machine_depr,
                'price' => $price,
                'depretiation' => $depretiation,
                'interest' => $interest,
                'electricity' => $electricity,
                'overhead' => $overhead,
                'shift' => $shift,
                'jumlah_machine' => $jumlah_machine,
                'total_price' => $total_machine_price,
            ];

            // cek existing
            $exist = $this->db->get_where('rates', [
                'year' => $year,
                'efisiensi' => $efisiensi,
                'toonage' => $r['toonage'] ?? null
            ])->row();

            try {
                if ($exist) {
                    $this->crud->update('rates', ['id' => $exist->id], $dataFinal);
                    $summary['update']++;
                } else {
                    $this->crud->create('rates', $dataFinal);
                    $summary['insert']++;
                }
            } catch (Exception $e) {
                $summary['errors'][] = $e->getMessage();
            }
        }

        echo json_encode([
            'status' => true,
            'summary' => $summary,
            'message' => 'Saved successfully',
            'theme' => 'success'
        ]);
    }


    public function delete()
    {
        $data = $this->input->post();
        $tonage = $this->crud->read("rates", [], ["efisiensi" => $data['efisiensi'], "year" => $data['year']]);
        
        if (!empty($tonage)) {
            $dataFinal = [
                "efisiensi" => $data['efisiensi'],
                "year" => $data['year']
            ];
        } else {
            $dataFinal = [
                "efisiensi" => $data['efisiensi'],
                "year" => $data['year']
            ];
        }

        $send = $this->crud->delete('rates', $dataFinal);
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
    //     @unlink('failed/rate.txt');
    // }

    // public function uploadcreateFailed()
    // {
    //     if ($this->input->post()) {
    //         $message = $this->input->post('message');
    //         $textFailed = fopen('failed/rate.txt', 'a');
    //         fwrite($textFailed, $message . "\n");
    //         fclose($textFailed);
    //     }
    // }

    //UPLOAD DOWNLOAD FAILED
    // public function uploadDownloadFailed()
    // {
    //     $file = "failed/rate.txt";
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

            
    //         $rate = $this->crud->read('rate', [], ["item_fg_id" => $data['item_fg_id'], "item_rm_id" => $data['item_rm_id']]);

    //         if (empty($item_fg->id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Part ID" . $data['item_fg_id'] ." Not Found", "theme" => "error"));
    //         } elseif (empty($item_rm->id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Part ID" . $data['item_rm_id'] ." Not Found", "theme" => "error"));
    //         } elseif (empty($menu_loading[0]->item_fg_id)) {
    //             echo json_encode(array("title" => "Not Found", "message" => "Part ID" . $data['item_fg_id'] . " in Menu Loading Not Found", "theme" => "error"));
    //         } elseif ($item_rm->item_family_id == 'P06' && $data['composition'] != "") {
    //             echo json_encode(array("title" => "Alert", "message" => "Part ID" . $data['item_rm_id'] ." Product Family is VIRGIN ", "theme" => "error"));
    //         } elseif (!empty($rate->item_rm_id)) {
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

    //             $send   = $this->crud->create('rate', $dataFinal);
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
            header("Content-Disposition: attachment; filename=rate_$format.xls");
        }

        $get = $this->input->get();
        $filter_year = @base64_decode($get['filter_year']);
        $filter_eficiency = @base64_decode($get['filter_eficiency']);

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        // $this->db->select('a.*, b.number as item_fg_number, b.name as item_fg_name, c.number as item_rm_number, c.name as item_rm_name, c.item_family_id as product_family, c.uom as uom, , d.name as product_family_name');
        $this->db->select('a.*');
        $this->db->from('rates a');
        if (!empty($filter_year)) {
            $this->db->where('a.year', $filter_year);
        }

        if (!empty($filter_eficiency)) {
            $this->db->where('a.efisiensi', $filter_eficiency);
        }
        $this->db->order_by('a.id', 'ASC');
        $records = $this->db->get()->result_array();
        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#rate {border-collapse: collapse;width: 100%;font-size: 12px;}#rate td, #rate th {border: 1px solid #ddd;padding: 2px;}#rate tr:nth-child(even){background-color: #f2f2f2;}#rate tr:hover {background-color: #ddd;}#rate th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
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
                <h3>RATES</h3>
            </div>
        </center>
        
        <table id="rate" border="1">
            <tr>
                <th width="20">No</th>
                <th>Year</th>
                <th>Eficiency (%)</th>
                <th>Tonage</th>
                <th>Plain Rate / <br> Sec</th>
                <th>Plain Rate / <br> Hour</th>
                <th>FOH</th>
                <th>Energy</th>
                <th>Labour/ <br>Machine</th>
                <th>Labour <br>Cost</th>
                <th>Machine <br>Deprc Cost</th>
                <th>Machine <br>Price</th>
                <th>Total <br>Machine</th>
                <th>Depretiation</th>
                <th>Interest (%)</th>
                <th>Shift</th>
                <th>Created By</th>
                <th>Created Date</th>
            </tr>';
        $no = 1;
        foreach ($records as $data) {
            $html .= '<tr>
                    <td>' . $no . '</td>
                    <td>' . $data['year'] . '</td>
                    <td>' . $data['efisiensi'] . '</td>
                    <td>' . $data['toonage'] . '</td>
                    <td>' . $data['plain_rate_sec'] . '</td>
                    <td>' . $data['plain_rate_hour'] . '</td>
                    <td>' . $data['foh'] . '</td>
                    <td>' . $data['energy'] . '</td>
                    <td>' . $data['labour'] . '</td>
                    <td>' . $data['labour_cost'] . '</td>
                    <td>' . $data['machine_depretiation_cost'] . '</td>
                    <td>' . number_format($data['price'], 2) . '</td>
                    <td>' . $data['jumlah_machine'] . '</td>
                    <td>' . $data['depretiation'] . '</td>
                    <td>' . $data['interest'] . '</td>
                    <td>' . $data['shift'] . '</td>
                    <td>' . $data['created_by'] . '</td>
                    <td>' . $data['created_date'] . '</td>';
            $no++;
        }
        $html .= '</table></body></html>';
        echo $html;
    }
}
