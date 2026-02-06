<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_loadcap extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');

        //Validasi Form
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_loadcap.product_no]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/generate_loadcap');
        } else {
            redirect('error_access');
        }
    }

    public function readYears()
    {
        $tahun_before = date('Y', strtotime('-7 year', strtotime(date('Y'))));
        $tahun_next = date('Y', strtotime('+1 year', strtotime(date('Y'))));
        for ($i = $tahun_next; $i >= $tahun_before; $i--) {
            $arr[] = array("id" => $i, "name" => $i);
        }

        echo json_encode($arr);
    }

    public function readMonths()
    {
        $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
        foreach ($months as $key => $value) {
            $arr[] = array("id" => $key, "name" => $value);
        }

        echo json_encode($arr);
    }

    public function readRevisions()
    {
        $arr = array(
            ["id" => "0", "name" => "Revision 0"],
            ["id" => "1", "name" => "Revision 1"],
            ["id" => "2", "name" => "Revision 2"],
            ["id" => "3", "name" => "Revision 3"],
            ["id" => "4", "name" => "Revision 4"],
            ["id" => "5", "name" => "Revision 5"],
        );

        echo json_encode($arr);
    }

    public function check_menu_loading() {
        $this->load->database();
        
        $query = $this->db->query("
            SELECT a.id, a.number as item_number 
            FROM item_fg a 
            WHERE NOT EXISTS (
                SELECT 1 FROM menu_loadings b WHERE b.item_fg_id = a.id
            ) and a.status = 0
        ");
    
        echo json_encode($query->result());
    }

    public function getData()
    {
        if ($this->input->get()) {

            $filter_month       = base64_decode($this->input->get('filter_month'));
            $filter_year        = base64_decode($this->input->get('filter_year'));
            $filter_cutoff      = base64_decode($this->input->get('filter_cutoff'));
            $filter_customer    = base64_decode($this->input->get('filter_customer'));
            $filter_product_no  = base64_decode($this->input->get('filter_item_fg'));
            $filter_revision    = base64_decode($this->input->get('filter_revision'));

            $prev_month         = (int)$filter_month - 1;
            $prev_year          = $filter_year;

            // Kalau bulan = 1, maka mundur ke tahun sebelumnya
            if ($prev_month == 0) {
                $prev_month = 12;
                $prev_year  = $filter_year - 1;
            }

            $this->db->select_max('revision');
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $rev = $this->db->get('generate_loadcap')->row();

            if ($filter_revision == "") {
                $revision = empty($rev) ? 0 : ($rev->revision + 1);
            } else {
                $revision = $filter_revision;
            }

            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $this->db->where('revision', $revision);
            if (!empty($filter_product_no)) {
                $this->db->where('item_fg_id', $filter_product_no);
            }
            $this->db->delete('generate_loadcap');

            //delete laodcap machine
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $this->db->where('revision', $revision);
            $this->db->delete('generate_loadcap_machine');

            //delete laodcap machine
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $this->db->where('revision', $revision);
            $this->db->delete('generate_loadcap_manpower');

            //get revision generate mps
            $this->db->select_max('revision');
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $rev_mps = $this->db->get('generate_mps')->row();
            $revision_mps = !empty($rev_mps) ? $rev_mps->revision : 0;

            $this->db->select_max('revision');
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $rev_mps_details = $this->db->get('generate_mps_details')->row();
            $revision_mps_details = !empty($rev_mps_details) ? $rev_mps_details->revision : 0;

            // Forecast (bulan aktif)
            $this->db->select('item_fg_id, forecast');
            $this->db->from('generate_mps_details');
            $this->db->where('p_year', $filter_year);
            $this->db->where('p_month', $filter_month);
            $this->db->where('revision', $revision_mps_details);
            $this->db->where('ltpp_month2', "$filter_year-$filter_month-01");
            $this->db->group_by('item_fg_id');
            $query_current = $this->db->get_compiled_select();
            $subquery_forecast = "($query_current) d";

            // untuk fallback ke bulan sebelumnya
            // $this->db->select('item_fg_id, forecast');
            // $this->db->from('generate_mps_details');
            // $this->db->where('p_year', $prev_year);
            // $this->db->where('p_month', $prev_month);
            // $this->db->where('revision', $revision_mps_details);
            // $this->db->where('ltpp_month2', "$filter_year-$filter_month-01");
            // $this->db->group_by('item_fg_id');
            // $query_prev = $this->db->get_compiled_select();

            // $subquery_forecast = "($query_current UNION $query_prev) d";

            // Prodplan Hitung 4 bulan ke depan
            $monthList = [];
            $y = (int)$filter_year;
            $m = (int)$filter_month;
            for ($i = 0; $i < 4; $i++) {
                $monthList[] = [
                    'year' => $y,
                    'month' => str_pad($m, 2, '0', STR_PAD_LEFT)
                ];
                $m++;
                if ($m > 12) {
                    $m = 1;
                    $y++;
                }
            }

            // prodplan dinamis
            $subquery_prodplan = "(SELECT 
                item_fg_id,
                MAX(CASE WHEN ltpp_month2 = '{$monthList[0]['year']}-{$monthList[0]['month']}-01' THEN prod_plan END) AS prodplan1,
                MAX(CASE WHEN ltpp_month2 = '{$monthList[1]['year']}-{$monthList[1]['month']}-01' THEN prod_plan END) AS prodplan2,
                MAX(CASE WHEN ltpp_month2 = '{$monthList[2]['year']}-{$monthList[2]['month']}-01' THEN prod_plan END) AS prodplan3,
                MAX(CASE WHEN ltpp_month2 = '{$monthList[3]['year']}-{$monthList[3]['month']}-01' THEN prod_plan END) AS prodplan4
            FROM generate_mps_details
            WHERE p_year = '$filter_year'
            AND p_month = '$filter_month'
            AND revision = '$revision_mps_details'
            GROUP BY item_fg_id
            ) e";

            // Subquery: Kapasitas dari menu_loadings + molds
            $subquery_loadcap = "(SELECT 
                    ml.item_fg_id,
                    (CASE 
                        WHEN i.status_subcont = 'YES' THEN 'MC-1125001'
                        ELSE ml.machine_id
                    END) AS machine_id,
                    g.mold_name,
                    g.id AS mold_id,
                    (CASE 
                        WHEN i.status_subcont = 'YES' THEN 'SUBCONT'
                        ELSE h.number
                    END) AS machine_number,
                    (CASE 
                        WHEN i.status_subcont = 'YES' THEN 0
                        ELSE h.toonage
                    END) AS toonage,
                    (3600 * COALESCE(ml.shift_hour, 0) / NULLIF(ml.cycle_time, 0)) 
                        * COALESCE(g.cavity_standard, 0) 
                        * COALESCE(ml.shift, 0) 
                        * (COALESCE(ml.productcivity, 100) / 100) AS cap_day,
                    ml.cycle_time,
                    g.cavity_standard AS cavity,
                    COALESCE (ml.manpower, 0) as manpower
                FROM menu_loadings ml
                LEFT JOIN molds g ON ml.mold_id = g.id
                LEFT JOIN machines h ON ml.machine_id = h.id
                LEFT JOIN item_fg i ON ml.item_fg_id = i.id
                WHERE i.division_id != 'DIV02'
            ) f";

            $hkw_per_bulan = [];
            $startMonth = (int)$filter_month;
            $startYear = (int)$filter_year;

            for ($i = 0; $i < 4; $i++) {
                // Tentukan bulan & tahun (handle jika lewat Desember)
                $month = $startMonth + $i;
                $year = $startYear;
                if ($month > 12) {
                    $month -= 12;
                    $year++;
                }

                // Dapatkan tanggal awal & akhir bulan
                $monthStart = strtotime("$year-$month-01");
                $monthEnd = strtotime(date('Y-m-t', $monthStart));

                // Ambil tanggal libur di bulan ini (remarks != '')
                $this->db->select('working_date');
                $this->db->from('calendars');
                $this->db->where('working_date >=', date('Y-m-01', $monthStart));
                $this->db->where('working_date <=', date('Y-m-t', $monthStart));
                $this->db->where("remarks != ''");
                $holidays = $this->db->get()->result_array();
                $holidayDates = array_column($holidays, 'working_date');

                // Hitung jumlah hari kerja (tidak Minggu & tidak libur)
                $hkw = 0;
                for ($z = $monthStart; $z <= $monthEnd; $z += 86400) { // 86400 = 1 hari
                    $currentDate = date('Y-m-d', $z);
                    $isSunday = (date('w', $z) == 0);
                    $isHoliday = in_array($currentDate, $holidayDates);
                    if (!$isSunday && !$isHoliday) {
                        $hkw++;
                    }
                }

                // Simpan ke array dengan format label bulan
                $label = strtoupper(date('M y', $monthStart));
                $hkw_per_bulan[$label] = $hkw;
            }

            $hkw1 = $hkw_per_bulan[array_keys($hkw_per_bulan)[0]];
            $hkw2 = $hkw_per_bulan[array_keys($hkw_per_bulan)[1]];
            $hkw3 = $hkw_per_bulan[array_keys($hkw_per_bulan)[2]];
            $hkw4 = $hkw_per_bulan[array_keys($hkw_per_bulan)[3]];

            //query utama
            $this->db->select('a.id, 
                a.division_id,
                a.number, 
                a.name, 
                a.status_subcont,
                b2.name as customer_name,
                b2.id as customer_id,
                c.fg,
                c.pp,
                c.p1,
                c.p2,
                c.p3,
                d.forecast,
                e.prodplan1,
                e.prodplan2,
                e.prodplan3,
                e.prodplan4,
                f.cap_day,
                f.cavity,
                f.cycle_time,
                f.manpower,
                f.machine_id,
                f.machine_number,
                f.toonage,
                f.mold_name,
                f.mold_id');
            $this->db->from('item_fg a');
            $this->db->join('customer_items b', 'a.id = b.item_fg_id');
            $this->db->join('customers b2', 'b.customer_id = b2.id');
            $this->db->join("(SELECT item_fg_id, fg, pp, p1, p2, p3
                        FROM generate_mps 
                        WHERE p_year = '$filter_year' 
                            AND p_month = '$filter_month'
                            AND revision = '$revision_mps'
                        GROUP BY item_fg_id) c", 'a.id = c.item_fg_id', 'left', false);
            $this->db->join($subquery_forecast, 'a.id = d.item_fg_id', 'left', false);
            $this->db->join($subquery_prodplan, 'a.id = e.item_fg_id', 'left', false);
            $this->db->join($subquery_loadcap, 'a.id = f.item_fg_id', 'left', false);
            $this->db->where('a.status', 0);
            $this->db->where('a.division_id !=', 'DIV02');

            if (!empty($filter_customer)) {
                $this->db->where('b.customer_id', $filter_customer);
            }
            if (!empty($filter_product_no)) {
                $this->db->where('a.id', $filter_product_no);
            }

            $this->db->group_by('a.id');
            $this->db->group_by('f.machine_id');
            $this->db->order_by('a.number', 'asc');
            $records = $this->db->get()->result_array();

            $arr = [];
            $nowTime = date("H:i:s");
            foreach ($records as $data) {
                // casting ke tipe numerik
                $fg = isset($data['fg']) ? (int)$data['fg'] : 0;
                $pp = isset($data['pp']) ? (int)$data['pp'] : 0;
                $p1 = isset($data['p1']) ? (int)$data['p1'] : 0;
                $p2 = isset($data['p2']) ? (int)$data['p2'] : 0;
                $p3 = isset($data['p3']) ? (int)$data['p3'] : 0;

                $forecast = isset($data['forecast']) ? (float)$data['forecast'] : 0;
                $wip = $pp + $p1 + $p2 + $p3;

                $ito = ($forecast > 0) ? round(($fg + $wip) / $forecast, 4) : 0;

                $cap_day = isset($data['cap_day']) ? (float)$data['cap_day'] : 0;
                $cap_month = $cap_day * $hkw1;

                // hindari pembagian nol
                $need_day1 = ($cap_day > 0) ? $data['prodplan1'] / $cap_day : 0;
                $need_day2 = ($cap_day > 0) ? $data['prodplan2'] / $cap_day : 0;
                $need_day3 = ($cap_day > 0) ? $data['prodplan3'] / $cap_day : 0;
                $need_day4 = ($cap_day > 0) ? $data['prodplan4'] / $cap_day : 0;
                $sum_need_day = $need_day1; // atau bisa logika lain kalau mau dijumlahkan nanti

                $loadcap1 = ($need_day1 > 0) ? ($need_day1 / $hkw1) * 100 : 0;
                $loadcap2 = ($need_day2 > 0) ? ($need_day2 / $hkw2) * 100 : 0;
                $loadcap3 = ($need_day3 > 0) ? ($need_day3 / $hkw3) * 100 : 0;
                $loadcap4 = ($need_day4 > 0) ? ($need_day4 / $hkw4) * 100 : 0;

                $manpower1 = ($need_day1 > 0) ? ($need_day1 / $hkw1) * $data['manpower'] : 0;
                $manpower2 = ($need_day2 > 0) ? ($need_day2 / $hkw2) * $data['manpower'] : 0;
                $manpower3 = ($need_day3 > 0) ? ($need_day3 / $hkw3) * $data['manpower'] : 0;
                $manpower4 = ($need_day4 > 0) ? ($need_day4 / $hkw4) * $data['manpower'] : 0;

                $arr[] = [
                    "p_month"           => $filter_month,
                    "p_year"            => $filter_year,
                    "revision"          => $revision,
                    "cutoff"            => $filter_cutoff . " " . $nowTime,
                    "item_fg_id"        => $data['id'],
                    "division_id"       => $data['division_id'],
                    "customer_id"       => $data['customer_id'],
                    "customer_name"     => $data['customer_name'],
                    "number"            => $data['number'],
                    "name"              => $data['name'],
                    "fg"                => $fg,
                    "wip"               => $wip,
                    "forecast"          => $forecast,
                    "ito"               => $ito,
                    "prodplan1"         => $data['prodplan1'],
                    "prodplan2"         => $data['prodplan2'],
                    "prodplan3"         => $data['prodplan3'],
                    "prodplan4"         => $data['prodplan4'],
                    "cavity"            => $data['cavity'],
                    "cycle_time"        => $data['cycle_time'],
                    "manpower"          => $data['manpower'],
                    "hkw"               => $hkw1,
                    "hkw2"              => $hkw2,
                    "hkw3"              => $hkw3,
                    "hkw4"              => $hkw4,
                    "cap_day"           => $cap_day,
                    "cap_month"         => $cap_month,
                    "need_day"          => $need_day1,
                    "need_day2"         => $need_day2,
                    "need_day3"         => $need_day3,
                    "need_day4"         => $need_day4,
                    "sum_need_day"      => $sum_need_day,
                    "machine_id"        => $data['machine_id'],
                    "machine_number"    => $data['machine_number'],
                    "toonage"           => $data['toonage'],
                    "mold_name"         => $data['mold_name'],
                    "mold_id"           => $data['mold_id'],
                    "loadcap1"          => $loadcap1,
                    "loadcap2"          => $loadcap2,
                    "loadcap3"          => $loadcap3,
                    "loadcap4"          => $loadcap4,
                    "manpower1"         => $manpower1,
                    "manpower2"         => $manpower2,
                    "manpower3"         => $manpower3,
                    "manpower4"         => $manpower4,
                ];
            }

            $arr['total'] = count($records);

            // var_dump($arr);
            // return;

            echo json_encode($arr);
            exit;

        } else {
            show_error("Cannot process your request");
        }
    }

    public function revision()
    {
        $filter_month = $this->input->post('filter_month');
        $filter_year = $this->input->post('filter_year');

        $this->db->select('revision');
        $this->db->from('generate_loadcap');
        if ($filter_month != "" or $filter_year != "") {
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
        }
        $this->db->where('deleted', 0);
        $this->db->group_by('revision');
        $this->db->order_by('revision', 'desc');
        $this->db->limit(1);
        $record = $this->db->get()->row();
        echo @$record->revision ? $record->revision : 0;
    }

    public function checkCalendar()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));

        // Hitung periode 6 bulan ke depan
        $monthStart = strtotime("$filter_year-$filter_month-01");
        $monthEnd   = strtotime(date('Y-m-d', strtotime('+6 month', $monthStart)));

        $html = "";
        $no = 1;

        while ($monthStart < $monthEnd) {
            $monthName = date('M Y', $monthStart);
            $start = strtotime(date('Y-m-01', $monthStart));
            $finish = strtotime(date('Y-m-t', $monthStart));

            // Ambil semua tanggal libur dalam bulan ini (1x query)
            $this->db->select('working_date');
            $this->db->from('calendars');
            $this->db->where('working_date >=', date('Y-m-01', $monthStart));
            $this->db->where('working_date <=', date('Y-m-t', $monthStart));
            $this->db->where("remarks != ''");
            $holidays = $this->db->get()->result_array();
            $holidayDates = array_column($holidays, 'working_date');

            // Hitung HKW
            $hkw = 0;
            for ($z = $start; $z <= $finish; $z += 86400) { // 86400 = 1 hari
                $working_date = date('Y-m-d', $z);
                $isSunday = (date('w', $z) == 0);
                // $isSaturday = (date('w', $z) == 6); // aktifkan jika sabtu libur
                $isHoliday = in_array($working_date, $holidayDates);

                if (!$isSunday && !$isHoliday) {
                    $hkw++;
                }
            }

            $html .= '<div style="margin:15px;">
                        HKW ' . $no . ' : ' . strtoupper($monthName) . ' → <b>' . $hkw . '</b>
                    </div>';

            $no++;
            $monthStart = strtotime("+1 month", $monthStart);
        }

        echo $html;
    }

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            // var_dump ($post['machine_id']);
            // return;
            
            if ($post['division_id'] == 'DIV02' || empty($post['division_id'])) {
                echo json_encode([
                    'theme' => 'success',
                    'title' => 'SKIPPED',
                    'message' => 'Item ' . $post['number'] . ' belongs to DIV02 and was skipped.'
                ]);
                return;
            }

            $machine = $this->crud->reads('machines', ['id' => $post['machine_id']]);
            if (count($machine) == 0 || empty($post['machine_id'])) {
                echo json_encode([
                    'theme' => 'error',
                    'title' => 'FAILED',
                    'message' => 'Item ' . $post['number'] . ' and Machine ID ' . $post['machine_id'] . ' Not Found in Menu loading.'
                ]);
                return;
            }

            $generateLoadcap = $this->crud->reads('generate_loadcap', [], [
                "p_month"     => $post['p_month'],
                "p_year"      => $post['p_year'],
                "revision"    => $post['revision'],
                "customer_id" => $post['customer_id'],
                "item_fg_id"  => $post['item_fg_id'],
                "machine_id"  => $post['machine_id']
            ]);

            $postFinal = [
                "p_month"       => $post['p_month'],
                "p_year"        => $post['p_year'],
                "revision"      => $post['revision'],
                "cutoff"        => $post['cutoff'],
                "customer_id"   => $post['customer_id'],
                "item_fg_id"    => $post['item_fg_id'],
                "fg"            => $post['fg'],
                "wip"           => $post['wip'],
                "forecast"      => $post['forecast'],
                "ito"           => $post['ito'],
                "prodplan1"     => $post['prodplan1'],
                "prodplan2"     => $post['prodplan2'],
                "prodplan3"     => $post['prodplan3'],
                "prodplan4"     => $post['prodplan4'],
                "cycle_time"    => $post['cycle_time'],
                "cavity"        => $post['cavity'],
                "manpower"      => $post['manpower'],
                "hkw"           => $post['hkw'],
                "cap_day"       => $post['cap_day'],
                "cap_month"     => $post['cap_month'],
                "machine_id"    => $post['machine_id'],
                "machine_number"=> $post['machine_number'],
                "need_day"      => $post['need_day'],
                "sum_need_day"  => $post['sum_need_day'],
                "toonage"       => $post['toonage'],
                "mold_id"       => $post['mold_id'],
                "loadcap1"      => $post['loadcap1'],
                "loadcap2"      => $post['loadcap2'],
                "loadcap3"      => $post['loadcap3'],
                "loadcap4"      => $post['loadcap4'],
                "manpower1"     => $post['manpower1'],
                "manpower2"     => $post['manpower2'],
                "manpower3"     => $post['manpower3'],
                "manpower4"     => $post['manpower4'],
            ];

            if (count($generateLoadcap) > 0) {
                $send = $this->crud->update(
                    'generate_loadcap',
                    [
                        "p_month"     => $post['p_month'],
                        "p_year"      => $post['p_year'],
                        "revision"    => $post['revision'],
                        "customer_id" => $post['customer_id'],
                        "item_fg_id"  => $post['item_fg_id'],
                        "machine_id"  => $post['machine_id']
                    ],
                    $postFinal
                );
            } else {
                $send = $this->crud->create('generate_loadcap', $postFinal);
            }

            echo json_encode([
                'theme' => 'success',
                'title' => 'SUCCESS',
                'message' => 'Save Data Success'
            ]);
        }
    }

    // DOKUENTASI : Create tanpa validasi 
    // public function create()
    // {   
    //     if ($this->input->post()) {
    //         $post = $this->input->post('data');


    //         $generateLoadcap = $this->crud->reads('generate_loadcap', [], [
    //             "p_month" => $post['p_month'],
    //             "p_year" => $post['p_year'],
    //             "revision" => $post['revision'],
    //             "customer_id" => $post['customer_id'],
    //             "item_fg_id" => $post['item_fg_id'],
    //             "machine_id" => $post['machine_id']
    //         ]);

    //         $postFinal = array(
    //             "p_month"       => $post['p_month'],
    //             "p_year"        => $post['p_year'],
    //             "revision"      => $post['revision'],
    //             "cutoff"        => $post['cutoff'],
    //             "customer_id"   => $post['customer_id'],
    //             "item_fg_id"    => $post['item_fg_id'],
    //             "fg"            => $post['fg'],
    //             "wip"           => $post['wip'],
    //             "forecast"      => $post['forecast'],
    //             "ito"           => $post['ito'],
    //             "prodplan1"     => $post['prodplan1'],
    //             "prodplan2"     => $post['prodplan2'],
    //             "prodplan3"     => $post['prodplan3'],
    //             "prodplan4"     => $post['prodplan4'],
    //             "cycle_time"    => $post['cycle_time'],
    //             "manpower"      => $post['manpower'],
    //             "hkw"           => $post['hkw'],
    //             "cap_day"       => $post['cap_day'],
    //             "cap_month"     => $post['cap_month'],
    //             "machine_id"    => $post['machine_id'],
    //             "machine_number"=> $post['machine_number'],
    //             "need_day"      => $post['need_day'],
    //             "sum_need_day"  => $post['sum_need_day'],
    //             "toonage"       => $post['toonage'],
    //             "mold_id"       => $post['mold_id'],
    //             "loadcap1"      => $post['loadcap1'],
    //             "loadcap2"      => $post['loadcap2'],
    //             "loadcap3"      => $post['loadcap3'],
    //             "loadcap4"      => $post['loadcap4'],
    //             "manpower1"     => $post['manpower1'],
    //             "manpower2"     => $post['manpower2'],
    //             "manpower3"     => $post['manpower3'],
    //             "manpower4"     => $post['manpower4'],
    //         );

    //         if (count($generateLoadcap) > 0) {
    //             $send   = $this->crud->update('generate_loadcap', [
    //                 "p_month" => $post['p_month'],
    //                 "p_year" => $post['p_year'],
    //                 "revision" => $post['revision'],
    //                 "customer_id" => $post['customer_id'],
    //                 "item_fg_id" => $post['item_fg_id'],
    //                 "machine_id" => $post['machine_id']
    //             ], $postFinal);
    //             echo $send;
    //         } else {
    //             $send = $this->crud->create('generate_loadcap', $postFinal);
    //             echo $send;
    //         }
    //     }
    // }

    // public function overList()
    // {
    //     $filter_month    = base64_decode($this->input->get('filter_month'));
    //     $filter_year     = base64_decode($this->input->get('filter_year'));
    //     $filter_revision = base64_decode($this->input->get('filter_revision'));

    //     $this->db->select('machine_number, fg, ito, loadcap1, loadcap2, loadcap3, loadcap4');
    //     $this->db->from('generate_loadcap_machine_over');
    //     $this->db->where('p_month', $filter_month);
    //     $this->db->where('p_year', $filter_year);
    //     $this->db->where('revision', $filter_revision);
    //     $this->db->order_by('machine_number ASC, ito ASC');
    //     $data = $this->db->get()->result_array();

    //     if (empty($data)) {
    //         echo "<div style='padding:8px;text-align:center;color:red;font-weight:bold;'>No Over Data Found</div>";
    //         return;
    //     }

    //     $monthNames = [];
    //     $y = (int)$filter_year;
    //     $m = (int)$filter_month;
    //     for ($i = 0; $i < 4; $i++) {
    //         $timestamp = strtotime("$y-$m-01");
    //         $monthNames[] = strtoupper(substr(date('M', $timestamp), 0, 3)) . " " . substr(date('Y', $timestamp), 2, 2);
    //         $m++;
    //         if ($m > 12) {
    //             $m = 1;
    //             $y++;
    //         }
    //     }


    //     // Build EasyUI datagrid table
    //     $html = '
    //     <table id="dgOverList" class="easyui-datagrid" style="width:100%;height:230px;"
    //         data-options="fitColumns:true,singleSelect:true">
    //         <thead>
    //             <tr>
    //                 <th data-options=\'field:"machine_number",width:100,align:"center"\'>Machine</th>
    //                 <th data-options=\'field:"fg",width:80\'>FG</th>
    //                 <th data-options=\'field:"loadcap1",width:100,align:"right"\'>'. $monthNames[0].'</th>
    //                 <th data-options=\'field:"loadcap2",width:100,align:"right"\'>'. $monthNames[1].'</th>
    //                 <th data-options=\'field:"loadcap3",width:100,align:"right"\'>'. $monthNames[2].'</th>
    //                 <th data-options=\'field:"loadcap4",width:100,align:"right"\'>'. $monthNames[3].'</th>
    //             </tr>
    //         </thead>
    //         <tbody>';
        
    //     foreach ($data as $row) {
    //         $html .= "
    //         <tr>
    //             <td>{$row['machine_number']}</td>
    //             <td>{$row['fg']}</td>
    //             <td align='right'>" . number_format($row['loadcap1'], 2) . "</td>
    //             <td align='right'>" . number_format($row['loadcap2'], 2) . "</td>
    //             <td align='right'>" . number_format($row['loadcap3'], 2) . "</td>
    //             <td align='right'>" . number_format($row['loadcap4'], 2) . "</td>
    //         </tr>";
    //     }

    //     $html .= '
    //         </tbody>
    //     </table>';

    //     echo $html;
    // }

    // Generate dengan Over list
    // public function rekapMachine()
    // {
    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year  = base64_decode($this->input->get('filter_year'));
    //     $revision     = base64_decode($this->input->get('filter_revision'));

    //     // Ambil semua data generate_loadcap berdasarkan filter
    //     $this->db->select('*');
    //     $this->db->from('generate_loadcap');
    //     $this->db->where('p_month', $filter_month);
    //     $this->db->where('p_year', $filter_year);
    //     $this->db->where('revision', $revision);
    //     $records = $this->db->get()->result_array();

    //     if (empty($records)) {
    //         echo json_encode(['status' => 'failed', 'message' => 'No data found.']);
    //         return;
    //     }

    //     // Kelompokkan data per machine_id
    //     $grouped = [];
    //     foreach ($records as $r) {
    //         $grouped[$r['machine_id']][] = $r;
    //     }

    //     foreach ($grouped as $machine_id => $items) {
    //         // Urutkan berdasarkan ITO ASC (yang kecil dimasukkan dulu)
    //         usort($items, fn($a, $b) => $a['ito'] <=> $b['ito']);

    //         // Ambil informasi dasar mesin (dari item pertama)
    //         $cap_month      = $items[0]['cap_month'] ?? 1;
    //         $machine_number = $items[0]['machine_number'] ?? '';
    //         $toonage        = $items[0]['toonage'] ?? '';
    //         $ito            = $items[0]['ito'] ?? 0;

    //         $sumLoadcap1 = 0;
    //         $sumLoadcap2 = 0;
    //         $sumLoadcap3 = 0;
    //         $sumLoadcap4 = 0;

    //         $overItems = [];
    //         $isOver = false;

    //         foreach ($items as $item) {
    //             $newTotal = $sumLoadcap1 + $item['loadcap1'];

    //             if ($newTotal <= 100) {
    //                 // Masih dalam batas 100
    //                 $sumLoadcap1 += $item['loadcap1'];
    //                 $sumLoadcap2 += $item['loadcap2'];
    //                 $sumLoadcap3 += $item['loadcap3'];
    //                 $sumLoadcap4 += $item['loadcap4'];
    //             } else {
    //                 // Item ini membuat total loadcap1 > 100 → masuk over
    //                 $overItems[] = $item;
    //             }
    //         }

    //         $machineData = [
    //             'p_month'       => $filter_month,
    //             'p_year'        => $filter_year,
    //             'revision'      => $revision,
    //             'machine_id'    => $machine_id,
    //             'machine_number'=> $machine_number,
    //             'toonage'       => $toonage,
    //             'ito'           => $ito,
    //             'loadcap1'      => $sumLoadcap1,
    //             'loadcap2'      => $sumLoadcap2,
    //             'loadcap3'      => $sumLoadcap3,
    //             'loadcap4'      => $sumLoadcap4,
    //         ];

    //         $this->crud->create('generate_loadcap_machine', $machineData);

    //         foreach ($overItems as $over) {
    //             $overData = [
    //                 "p_month"        => $over['p_month'],
    //                 "p_year"         => $over['p_year'],
    //                 "revision"       => $over['revision'],
    //                 "cutoff"         => $over['cutoff'],
    //                 "customer_id"    => $over['customer_id'],
    //                 "item_fg_id"     => $over['item_fg_id'],
    //                 "fg"             => $over['fg'],
    //                 "wip"            => $over['wip'],
    //                 "forecast"       => $over['forecast'],
    //                 "ito"            => $over['ito'],
    //                 "prodplan1"      => $over['prodplan1'],
    //                 "prodplan2"      => $over['prodplan2'],
    //                 "prodplan3"      => $over['prodplan3'],
    //                 "prodplan4"      => $over['prodplan4'],
    //                 "cycle_time"     => $over['cycle_time'],
    //                 "manpower"       => $over['manpower'],
    //                 "hkw"            => $over['hkw'],
    //                 "cap_day"        => $over['cap_day'],
    //                 "cap_month"      => $over['cap_month'],
    //                 "machine_id"     => $over['machine_id'],
    //                 "machine_number" => $over['machine_number'],
    //                 "need_day"       => $over['need_day'],
    //                 "sum_need_day"   => $over['sum_need_day'],
    //                 "toonage"        => $over['toonage'],
    //                 "mold_id"        => $over['mold_id'],
    //                 "loadcap1"       => $over['loadcap1'],
    //                 "loadcap2"       => $over['loadcap2'],
    //                 "loadcap3"       => $over['loadcap3'],
    //                 "loadcap4"       => $over['loadcap4'],
    //                 "manpower1"      => $over['manpower1'],
    //                 "manpower2"      => $over['manpower2'],
    //                 "manpower3"      => $over['manpower3'],
    //                 "manpower4"      => $over['manpower4'],
    //             ];

    //             $this->crud->create('generate_loadcap_machine_over', $overData);
    //         }
    //     }

    //     echo json_encode(['status' => 'success', 'message' => 'Machine recap and over data processed successfully.']);
    // }

    public function rekapMachine()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year  = base64_decode($this->input->get('filter_year'));
        $revision     = base64_decode($this->input->get('filter_revision'));

        $this->db->select('*');
        $this->db->from('generate_loadcap');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->where('revision', $revision);
        $records = $this->db->get()->result_array();

        if (empty($records)) {
            echo json_encode(['status' => 'failed', 'message' => 'No data found.']);
            return;
        }

        // Kelompokkan data per machine_id
        $grouped = [];
        foreach ($records as $r) {
            $grouped[$r['machine_id']][] = $r;
        }

        foreach ($grouped as $machine_id => $items) {
            // Urutkan berdasarkan ITO ASC
            usort($items, fn($a, $b) => $a['ito'] <=> $b['ito']);

            $cap_month      = $items[0]['cap_month'] ?? 1;
            $machine_number = $items[0]['machine_number'] ?? '';
            $toonage        = $items[0]['toonage'] ?? '';
            $ito            = $items[0]['ito'] ?? 0;

            $sumLoadcap1 = 0;
            $sumLoadcap2 = 0;
            $sumLoadcap3 = 0;
            $sumLoadcap4 = 0;

            foreach ($items as $item) {
                $sumLoadcap1 += $item['loadcap1'];
                $sumLoadcap2 += $item['loadcap2'];
                $sumLoadcap3 += $item['loadcap3'];
                $sumLoadcap4 += $item['loadcap4'];
            }

            $machineData = [
                'p_month'        => $filter_month,
                'p_year'         => $filter_year,
                'revision'       => $revision,
                'machine_id'     => $machine_id,
                'machine_number' => $machine_number,
                'toonage'        => $toonage,
                'ito'            => $ito,
                'loadcap1'       => $sumLoadcap1,
                'loadcap2'       => $sumLoadcap2,
                'loadcap3'       => $sumLoadcap3,
                'loadcap4'       => $sumLoadcap4,
            ];

            $this->crud->create('generate_loadcap_machine', $machineData);
        }

        echo json_encode(['status' => 'success', 'message' => 'Machine recap data processed successfully.']);
    }

    public function rekapManPower()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year  = base64_decode($this->input->get('filter_year'));
        $revision     = base64_decode($this->input->get('filter_revision'));

        $this->db->select('*');
        $this->db->from('generate_loadcap');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->where('revision', $revision);
        $records = $this->db->get()->result_array();

        if (empty($records)) {
            echo json_encode(['status' => 'failed', 'message' => 'No data found.']);
            return;
        }

        // Kelompokkan data per machine_id
        $grouped = [];
        foreach ($records as $r) {
            $grouped[$r['machine_id']][] = $r;
        }

        foreach ($grouped as $machine_id => $items) {
            // Urutkan berdasarkan ITO ASC
            usort($items, fn($a, $b) => $a['ito'] <=> $b['ito']);

            $cap_month      = $items[0]['cap_month'] ?? 1;
            $machine_number = $items[0]['machine_number'] ?? '';
            $toonage        = $items[0]['toonage'] ?? '';
            $ito            = $items[0]['ito'] ?? 0;

            $sumManpower1 = 0;
            $sumManpower2 = 0;
            $sumManpower3 = 0;
            $sumManpower4 = 0;

            foreach ($items as $item) {
                $sumManpower1 += $item['manpower1'];
                $sumManpower2 += $item['manpower2'];
                $sumManpower3 += $item['manpower3'];
                $sumManpower4 += $item['manpower4'];
            }

            $machineData = [
                'p_month'        => $filter_month,
                'p_year'         => $filter_year,
                'revision'       => $revision,
                'machine_id'     => $machine_id,
                'machine_number' => $machine_number,
                'toonage'        => $toonage,
                'ito'            => $ito,
                'manpower1'      => $sumManpower1,
                'manpower2'      => $sumManpower2,
                'manpower3'      => $sumManpower3,
                'manpower4'      => $sumManpower4,
            ];

            $this->crud->create('generate_loadcap_manpower', $machineData);
        }

        echo json_encode(['status' => 'success', 'message' => 'Machine recap data processed successfully.']);
    }

    public function rekapTonnage()
    {
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year  = base64_decode($this->input->get('filter_year'));
        $revision     = base64_decode($this->input->get('filter_revision'));

        // Ambil data dari rekap per machine
        $this->db->select('toonage, SUM(loadcap1) AS total, COUNT(machine_id) AS unit');
        $this->db->from('generate_loadcap_machine');
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->where('revision', $revision);
        $this->db->group_by('toonage');
        $data = $this->db->get()->result_array();

        // Hapus dulu rekap lama (jika ada)
        $this->db->where('p_month', $filter_month);
        $this->db->where('p_year', $filter_year);
        $this->db->where('revision', $revision);
        $this->db->delete('generate_loadcap_machine_tonage');

        foreach ($data as $row) {
            $average = $row['unit'] > 0 ? $row['total'] / $row['unit'] : 0;

            $insert = [
                'p_month'   => $filter_month,
                'p_year'    => $filter_year,
                'revision'  => $revision,
                'toonage'   => $row['toonage'],
                'total'     => $row['total'],
                'unit'      => $row['unit'],
                'average'   => $average,
            ];

            $this->crud->create('generate_loadcap_machine_tonage', $insert);
        }

        echo json_encode(['status' => 'success', 'message' => 'Tonnage recap completed.']);
    }

    public function uploadclearFailed()
    {
        @unlink('failed/generate_loadcap.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/generate_loadcap.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/generate_loadcap.txt";

        header('Content-Description: File Failed');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . @filesize($file));
        header("Content-Type: text/plain");
        @readfile($file);
    }

    public function monthName($id)
    {
        if ($id == "01") {
            return "JANUARY";
        } elseif ($id == "02") {
            return "FEBRUARY";
        } elseif ($id == "03") {
            return "MARCH";
        } elseif ($id == "04") {
            return "APRIL";
        } elseif ($id == "05") {
            return "MAY";
        } elseif ($id == "06") {
            return "JUNE";
        } elseif ($id == "07") {
            return "JULY";
        } elseif ($id == "08") {
            return "AUGUST";
        } elseif ($id == "09") {
            return "SEPTEMBER";
        } elseif ($id == "10") {
            return "OCTOBER";
        } elseif ($id == "11") {
            return "NOVEMBER";
        } elseif ($id == "12") {
            return "DECEMBER";
        }
    }

    public function print($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=generate_loadcap_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        // $filter_customer = base64_decode($this->input->get('filter_customer'));
        $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
        $filter_product_no = base64_decode($this->input->get('filter_item_fg'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $period = $filter_year . "-" . $filter_month;

        $query_main = "SELECT a.*, 
            b.name, 
            b.number,
            b.status_subcont,
            b.color,
            c.name as customer_name,
            d.mold_name
            FROM generate_loadcap a
            JOIN item_fg b ON a.item_fg_id = b.id
            JOIN customers c ON a.customer_id = c.id
            JOIN molds d ON a.mold_id = d.id
            JOIN machines e ON a.machine_id = e.id
            WHERE a.item_fg_id LIKE '%$filter_product_no%' 
                AND a.p_month LIKE '%$filter_month%' 
                AND a.p_year LIKE '%$filter_year%' 
                AND a.revision LIKE '%$filter_revision%' 
                AND a.status = 0
            ORDER BY b.number
            ";

        $records = $this->crud->query($query_main);

        $monthNames = [];
        $y = (int)$filter_year;
        $m = (int)$filter_month;
        for ($i = 0; $i < 4; $i++) {
            $timestamp = strtotime("$y-$m-01");
            $monthNames[] = strtoupper(substr(date('M', $timestamp), 0, 3)) . " " . substr(date('Y', $timestamp), 2, 2);
            $m++;
            if ($m > 12) {
                $m = 1;
                $y++;
            }
        }

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
                            <small>'.$config->description.'</small>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: right; font-size: 12px; text-align: right;">
                Print Date ' . date("d M Y H:i:s") . ' <br>
                Print By ' . $this->session->username . '  
            </div>
            <br><br><br>
            <h3 style="margin:0;">LOADCAP</h3>
        </center>
        <br>
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th rowspan="2" width="20">No</th>
                    <th rowspan="2">Customer</th>
                    <th rowspan="2">Product No</th>
                    <th rowspan="2">Product Name</th>
                    <th rowspan="2">Subcont</th>
                    <th rowspan="2">MC No</th>
                    <th rowspan="2">Tonage</th>
                    <th rowspan="2">Mold</th>
                    <th rowspan="2">Color</th>
                    <th rowspan="2">Man Power</th>
                    <th rowspan="2">Cavity</th>
                    <th rowspan="2">Cycle Time</th>
                    <th rowspan="2">Cap/Day</th>
                    <th rowspan="2">Cap/Month</th>
                    <th rowspan="2">Stock FG</th>
                    <th rowspan="2">WIP</th>
                    <th rowspan="2">FC</th>
                    <th rowspan="2">ITO</th>
                    <th rowspan="2">Prodplan <br>'. $monthNames[0].'</th>
                    <th rowspan="2">Prodplan <br>'. $monthNames[1].'</th>
                    <th rowspan="2">Prodplan <br>'. $monthNames[2].'</th>
                    <th rowspan="2">Prodplan <br>'. $monthNames[3].'</th>
                    <th rowspan="2">Need Day</th>
                    <th rowspan="2">Sum Need Day</th>
                    <th colspan="4">Loadcap</th>
                    <th colspan="4">Man Power</th>
                    <th rowspan="2">Rev</th>
                </tr>
                <tr>
                    <th>'. $monthNames[0].'</th>
                    <th>'. $monthNames[1].'</th>
                    <th>'. $monthNames[2].'</th>
                    <th>'. $monthNames[3].'</th>
                    <th>'. $monthNames[0].'</th>
                    <th>'. $monthNames[1].'</th>
                    <th>'. $monthNames[2].'</th>
                    <th>'. $monthNames[3].'</th>
                    
                </tr>';
        $no = 1;
        foreach ($records as $record) {
            $item_fg_id = $record->id;

            $html .= '  <tr>
                            <td style="text-align:center">' . $no . '</td>
                            <td>' . $record->customer_name . '</td>
                            <td style="mso-number-format:\@;">' . $record->number . '</td>
                            <td style="mso-number-format:\@;">' . $record->name . '</td>
                            <td>' . $record->status_subcont . '</td>
                            <td>' . $record->machine_number . '</td>
                            <td>' . $record->toonage . '</td>
                            <td>' . $record->mold_name . '</td>
                            <td>' . $record->color . '</td>
                            <td>' . $record->manpower . '</td>
                            <td>' . $record->cavity . '</td>
                            <td>' . $record->cycle_time . '</td>
                            <td>' . $record->cap_day . '</td>
                            <td>' . $record->cap_month . '</td>
                            <td>' . $record->fg . '</td>
                            <td>' . $record->wip . '</td>
                            <td>' . $record->forecast . '</td>
                            <td>' . $record->ito . '</td>
                            <td>' . $record->prodplan1 . '</td>
                            <td>' . $record->prodplan2 . '</td>
                            <td>' . $record->prodplan3 . '</td>
                            <td>' . $record->prodplan4 . '</td>
                            <td>' . $record->need_day . '</td>
                            <td>' . $record->sum_need_day . '</td>
                            <td>' . $record->loadcap1 . '%</td>
                            <td>' . $record->loadcap2 . '%</td>
                            <td>' . $record->loadcap3 . '%</td>
                            <td>' . $record->loadcap4 . '%</td>
                            <td>' . $record->manpower1 . '</td>
                            <td>' . $record->manpower2 . '</td>
                            <td>' . $record->manpower3 . '</td>
                            <td>' . $record->manpower4 . '</td>
                            <td>' . $record->revision . '</td>
                        </tr>';
                $no++;
        }

        $html .= '</table></div></body></html>';
        echo $html;
    }

    // public function recap_machine($option = "")
    // {
    //     if ($option == "excel") {
    //         $format  = date("Ymd");
    //         header("Content-type: application/vnd-ms-excel");
    //         header("Content-Disposition: attachment; filename=generate_loadcap_$format.xls");
    //     }

    //     //Config
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();

    //     //Filter Data
    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year = base64_decode($this->input->get('filter_year'));
    //     // $filter_customer = base64_decode($this->input->get('filter_customer'));
    //     $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
    //     $filter_product_no = base64_decode($this->input->get('filter_item_fg'));
    //     $filter_revision = base64_decode($this->input->get('filter_revision'));
    //     $period = $filter_year . "-" . $filter_month;

    //     $query_main = "SELECT a.*
    //         FROM generate_loadcap_machine a
    //         WHERE a.p_month LIKE '%$filter_month%' 
    //             AND a.p_year LIKE '%$filter_year%' 
    //             AND a.revision LIKE '%$filter_revision%' 
    //         ORDER BY a.machine_number
    //         ";

    //     $records = $this->crud->query($query_main);

    //     $monthNames = [];
    //     $y = (int)$filter_year;
    //     $m = (int)$filter_month;
    //     for ($i = 0; $i < 4; $i++) {
    //         $timestamp = strtotime("$y-$m-01");
    //         $monthNames[] = strtoupper(substr(date('M', $timestamp), 0, 3)) . " " . substr(date('Y', $timestamp), 2, 2);
    //         $m++;
    //         if ($m > 12) {
    //             $m = 1;
    //             $y++;
    //         }
    //     }

    //     $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black;}</style><body>
    //         <center>
    //         <div style="float: left; font-size: 12px; text-align: left;">
    //             <table style="width: 100%;">
    //                 <tr>
    //                     <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; vertical-align:jus margin-right:10px;">
    //                         <img src="' . $config->favicon . '" width="30">
    //                     </td>
    //                     <td style="font-size: 14px; text-align: left; margin:2px;">
    //                         <b>' . $config->name . '</b><br>
    //                         <small>'.$config->description.'</small>
    //                     </td>
    //                 </tr>
    //             </table>
    //         </div>
    //         <div style="float: right; font-size: 12px; text-align: right;">
    //             Print Date ' . date("d M Y H:i:s") . ' <br>
    //             Print By ' . $this->session->username . '  
    //         </div>
    //         <br><br><br>
    //         <h3 style="margin:0;">LOADCAP Per MACHINE</h3>
    //     </center>
    //     <br>
    //         <table id="customers" border="1" style="font-size: 11px;">
    //             <tr>
    //                 <th width="20">No</th>
    //                 <th>MC NO</th>
    //                 <th>Tonage</th>
    //                 <th>'. $monthNames[0].'</th>
    //                 <th>'. $monthNames[1].'</th>
    //                 <th>'. $monthNames[2].'</th>
    //                 <th>'. $monthNames[3].'</th> 
    //                 <th>Revision</th>
    //             </tr>';
    //     $no = 1;
    //     foreach ($records as $record) {
    //         $item_fg_id = $record->id;

    //         $html .= '  <tr>
    //                         <td style="text-align:center">' . $no . '</td>
    //                         <td>' . $record->machine_number . '</td>
    //                         <td>' . $record->toonage . '</td>
    //                         <td>' . $record->loadcap1 . '%</td>
    //                         <td>' . $record->loadcap2 . '%</td>
    //                         <td>' . $record->loadcap3 . '%</td>
    //                         <td>' . $record->loadcap4 . '%</td>
    //                         <td>' . $record->revision . '</td>
    //                     </tr>';
    //             $no++;
    //     }

    //     $html .= '</table></div></body></html>';
    //     echo $html;
    // }

    public function recap_machine($option = "")
    {
        if ($option == "excel") {
            $format  = date("Ymd");
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=generate_loadcap_$format.xls");
        }

        // === CONFIG & FILTER ===
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        // === REKAP PER MACHINE ===
        $query_main = "
            SELECT a.* 
            FROM generate_loadcap_machine a
            JOIN machines b ON a.machine_id = b.id
            WHERE a.p_month = '$filter_month'
                AND a.p_year = '$filter_year'
                AND a.revision = '$filter_revision'
            ORDER BY a.machine_number
        ";
        $records = $this->crud->query($query_main);

        // === REKAP PER MANPOWER (dari table generate_loadcap_manpower) ===
        $query_man = "
            SELECT a.*
            FROM generate_loadcap_manpower a
            JOIN machines b ON a.machine_id = b.id
            WHERE a.p_month = '$filter_month'
                AND a.p_year = '$filter_year'
                AND a.revision = '$filter_revision'
            ORDER BY a.machine_number
        ";
        $manpower = $this->crud->query($query_man);

        // === REKAP PER TONNAGE ===
        $query_ton = "
            SELECT 
                toonage,
                COUNT(machine_id) AS unit,
                SUM(loadcap1) AS total,
                ROUND(SUM(loadcap1) / COUNT(machine_id), 2) AS average
            FROM generate_loadcap_machine
            WHERE p_month = '$filter_month'
                AND p_year = '$filter_year'
                AND revision = '$filter_revision'
                AND toonage > 0
            GROUP BY toonage
            ORDER BY toonage ASC
        ";
        $tonnage = $this->crud->query($query_ton);

        // === NAMA BULAN ===
        $monthNames = [];
        $y = (int)$filter_year;
        $m = (int)$filter_month;
        for ($i = 0; $i < 4; $i++) {
            $timestamp = strtotime("$y-$m-01");
            $monthNames[] = strtoupper(substr(date('M', $timestamp), 0, 3)) . " " . substr(date('Y', $timestamp), 2, 2);
            $m++;
            if ($m > 12) { $m = 1; $y++; }
        }

        // === HTML ===
        $html = '
        <html><head><title>Loadcap Report</title></head>
        <style>
            body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
            #customers { border-collapse: collapse; width: 100%; font-size: 11px; margin-bottom: 20px; }
            #customers td, #customers th { border: 1px solid #ccc; padding: 3px; }
            #customers th { background: #f0f0f0; text-align: center; }
            .grid-2x2 { display: grid; grid-template-columns: 50% 50%; grid-template-rows: auto auto; gap: 10px; }
            .table-box { width: 100%; }
            h3 { margin-bottom: 4px; text-align: center; }
        </style>
        <body>
        <div style="float:left;text-align:left;">
            <table style="width:100%;">
                <tr>
                    <td width="50" style="text-align:center;">
                        <img src="' . $config->favicon . '" width="30">
                    </td>
                    <td>
                        <b>' . $config->name . '</b><br>
                        <small>' . $config->description . '</small>
                    </td>
                </tr>
            </table>
        </div>
        <div style="float:right;text-align:right;">
            Print Date: ' . date("d M Y H:i:s") . '<br>
            Print By: ' . $this->session->username . '
        </div>
        <div style="clear:both;"></div><br><br>

        <div class="grid-2x2">

            <!-- LEFT TOP: MACHINE -->
            <div class="table-box">
                <h3>REKAP PER MACHINE</h3>
                <table id="customers">
                    <tr>
                        <th>No</th>
                        <th>MC NO</th>
                        <th>Tonage</th>
                        <th>' . $monthNames[0] . '</th>
                        <th>' . $monthNames[1] . '</th>
                        <th>' . $monthNames[2] . '</th>
                        <th>' . $monthNames[3] . '</th>
                    </tr>';

        $no = 1;
        foreach ($records as $r) {
            $html .= '
                <tr>
                    <td align="center">' . $no++ . '</td>
                    <td>' . $r->machine_number . '</td>
                    <td align="center">' . $r->toonage . '</td>
                    <td align="right" style="color:' . ($r->loadcap1 > 100 ? 'red' : 'black') . ';">' . number_format($r->loadcap1, 2) . '%</td>
                    <td align="right" style="color:' . ($r->loadcap2 > 100 ? 'red' : 'black') . ';">' . number_format($r->loadcap2, 2) . '%</td>
                    <td align="right" style="color:' . ($r->loadcap3 > 100 ? 'red' : 'black') . ';">' . number_format($r->loadcap3, 2) . '%</td>
                    <td align="right" style="color:' . ($r->loadcap4 > 100 ? 'red' : 'black') . ';">' . number_format($r->loadcap4, 2) . '%</td>
                </tr>';
        }

        $html .= '
                </table>
            </div>

            <!-- RIGHT TOP: MANPOWER -->
            <div class="table-box">
                <h3>REKAP PER MANPOWER</h3>
                <table id="customers">
                    <tr>
                        <th>No</th>
                        <th>MC NO</th>
                        <th>Tonage</th>
                        <th>' . $monthNames[0] . '</th>
                        <th>' . $monthNames[1] . '</th>
                        <th>' . $monthNames[2] . '</th>
                        <th>' . $monthNames[3] . '</th>
                    </tr>';

        $no = 1;
        foreach ($manpower as $m) {
            $html .= '
                    <tr>
                        <td align="center">' . $no++ . '</td>
                        <td>' . $m->machine_number . '</td>
                        <td align="center">' . $m->toonage . '</td>
                        <td align="right" style="color:' . ($m->manpower1 > 100 ? 'red' : 'black') . ';">' . number_format($m->manpower1, 2) . '</td>
                        <td align="right" style="color:' . ($m->manpower2 > 100 ? 'red' : 'black') . ';">' . number_format($m->manpower2, 2) . '</td>
                        <td align="right" style="color:' . ($m->manpower3 > 100 ? 'red' : 'black') . ';">' . number_format($m->manpower3, 2) . '</td>
                        <td align="right" style="color:' . ($m->manpower4 > 100 ? 'red' : 'black') . ';">' . number_format($m->manpower4, 2) . '</td>
                    </tr>';
        }

        $html .= '
                </table>
            </div>

            <!-- LEFT BOTTOM: TONNAGE -->
            <div class="table-box">
                <h3>REKAP PER TONNAGE</h3>
                <table id="customers">
                    <tr>
                        <th>No</th>
                        <th>Tonage</th>
                        <th>Total</th>
                        <th>Unit</th>
                        <th>Average (%)</th>
                    </tr>';

        $no = 1;
        foreach ($tonnage as $t) {
            $html .= '
                    <tr>
                        <td align="center">' . $no++ . '</td>
                        <td align="center">' . $t->toonage . '</td>
                        <td align="right">' . number_format($t->total, 2) . '%</td>
                        <td align="center">' . $t->unit . '</td>
                        <td align="right">' . number_format($t->average, 2) . '%</td>
                    </tr>';
        }

        $html .= '
                </table>
            </div>

            <!-- RIGHT BOTTOM: EMPTY -->
            <div class="table-box"><h3>&nbsp;</h3></div>

        </div>
        </body></html>';

        echo $html;
    }

}
