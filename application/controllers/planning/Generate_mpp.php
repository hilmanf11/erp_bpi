<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Generate_mpp extends CI_Controller
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
        $this->form_validation->set_rules('product_no', 'Product No', 'required|min_length[2]|max_length[50]|is_unique[generate_mpp.product_no]');
    }

    public function index()
    {
        if (empty($this->session->username)) {
            redirect('error_session');
        } elseif ($this->checkuserAccess($this->id_menu()) > 0) {
            $data['button'] = $this->getbutton($this->id_menu());
            $this->load->view('template/header', $data);
            $this->load->view('planning/generate_mpp');
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

    public function datatables()
    {
        // Filter Data 
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
        $filter_product_no = base64_decode($this->input->get('filter_item_fg'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));

        if (empty($filter_month) || empty($filter_year)) {
            echo json_encode(["total" => 0, "rows" => []]);
            return;
        }

        $query_main = "SELECT a.*, 
                b.name, 
                b.number,
                b.status_subcont,
                b.color,
                b.mpq,
                b.qty_box,
                b.default_packing,
                c.name as customer_name,
                d.mold_name
                FROM generate_mpp a
                JOIN item_fg b ON a.item_fg_id = b.id
                JOIN customers c ON a.customer_id = c.id
                JOIN molds d ON a.mold_id = d.id
                JOIN machines e ON a.machine_id = e.id
                WHERE a.item_fg_id LIKE '%$filter_product_no%' 
                    AND a.p_month LIKE '%$filter_month%' 
                    AND a.p_year LIKE '%$filter_year%' 
                    AND a.revision LIKE '%$filter_revision%' 
                    AND a.status = 0
                ORDER BY a.machine_number ASC
                ";

        $records = $this->db->query($query_main)->result_array();

        // ====================================================================
        // [FITUR BARU] 1. AMBIL SEMUA DATA YANG SUDAH DI-PRINT BULAN INI
        // ====================================================================
        // Pastikan format bulan 2 digit (contoh: '04')
        $padMonth = str_pad($filter_month, 2, '0', STR_PAD_LEFT); 
        // Mengantisipasi jika variabel tahun yang terkirim adalah 2 atau 4 digit
        $year_2_digit = substr($filter_year, -2); 
        $year_4_digit = (strlen($filter_year) == 2) ? '20'.$filter_year : $filter_year;

        // Ambil data dari production_schedules untuk bulan dan tahun ini
        $query_printed = "SELECT item_fg_id, machine_id, trans_date 
                          FROM production_schedules 
                          WHERE month = '$padMonth' AND (year = '$filter_year' OR year = '$year_2_digit')";
        
        $printed_data = $this->db->query($query_printed)->result_array();

        // Buat Array Map untuk pencarian super cepat
        $printed_map = [];
        foreach ($printed_data as $pd) {
            // Gabungkan item_id dan machine_id sebagai kunci unik
            $kunci = $pd['item_fg_id'] . '_' . $pd['machine_id'];
            // Simpan tanggalnya (Format YYYY-MM-DD)
            $printed_map[$kunci][$pd['trans_date']] = true; 
        }
        // ====================================================================

        $current_mc = "";
        $no = 1;
        
        foreach ($records as &$row) { 
            if ($current_mc != $row['machine_number']) {
                $current_mc = $row['machine_number'];
                $no = 1; // Reset ke 1 jika mesin beda
            }
            $row['custom_no'] = $no;
            $no++;

            // ====================================================================
            // [FITUR BARU] 2. BERIKAN BENDERA (FLAG) PRINTED PADA SETIAP HARI
            // ====================================================================
            $kunci_row = $row['item_fg_id'] . '_' . $row['machine_id'];

            for ($i = 1; $i <= 31; $i++) {
                $padDay = str_pad($i, 2, '0', STR_PAD_LEFT);
                // Bentuk string tanggal untuk dicocokkan (contoh: 2026-04-02)
                $tgl_cek = $year_4_digit . '-' . $padMonth . '-' . $padDay;

                // Cek apakah kombinasi Item + Mesin + Tanggal ini ada di map yang sudah diprint?
                if (isset($printed_map[$kunci_row][$tgl_cek])) {
                    $row['day_' . $i . '_printed'] = 1; // Ya, warnai hijau
                } else {
                    $row['day_' . $i . '_printed'] = 0; // Belum, biarkan putih
                }
            }
            // ====================================================================
        }

        $result = [
            "total" => count($records),
            "rows"  => $records
        ];

        header('Content-Type: application/json');
        echo json_encode($result);
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

            $current_period = $filter_year . '-' . $filter_month; 
            $first_day_of_month = $current_period . '-01';
            $prev_month_time    = strtotime('-1 month', strtotime($first_day_of_month));
            $filter_from   = date('Y-m-01', $prev_month_time);
            $filter_to     = date('Y-m-t', $prev_month_time);
            $days_in_month = date('t', $prev_month_time);


            $this->db->select_max('revision');
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $rev = $this->db->get('generate_mpp')->row();

            $revision = ($filter_revision == "") ? (empty($rev) ? 0 : ($rev->revision + 1)) : $filter_revision;

            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $this->db->where('revision', $revision);
            if (!empty($filter_product_no)) {
                $this->db->where('item_fg_id', $filter_product_no);
            }
            $this->db->delete('generate_mpp');

            // MAX REV LOADCAP
            $this->db->select_max('revision');
            $this->db->where('p_month', $filter_month);
            $this->db->where('p_year', $filter_year);
            $rev_loadcap = $this->db->get('generate_loadcap')->row();
            $revision_loadcap = !empty($rev_loadcap) ? $rev_loadcap->revision : 0;
            //-------------------

            // MAX REV MPS
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
            //----------------------

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

            $subquery_so = "(SELECT item_fg_id, MAX(CASE WHEN ltpp_month2 = '{$monthList[0]['year']}-{$monthList[0]['month']}-01' THEN so END) AS so
            FROM generate_mps_details
            WHERE p_year = '$filter_year'
                AND p_month = '$filter_month'
                AND revision = '$revision_mps_details'
            GROUP BY item_fg_id) d";

            $subquery_mps = "(SELECT item_fg_id, os_so
            FROM generate_mps 
            WHERE p_year = '$filter_year' 
                AND p_month = '$filter_month'
                AND revision = '$revision_mps'
            GROUP BY item_fg_id) c";

            $subquery_delivery = "(SELECT item_fg_id, MIN(trans_date) as earliest_delivery 
            FROM sales_order_deliveries 
            WHERE DATE_FORMAT(trans_date, '%Y-%m') = '$current_period'
            GROUP BY item_fg_id) del";

            $subquery_prev_out = "(SELECT item_fg_id, (SUM(qty) / COUNT(DISTINCT t_date)) as avg_dn 
            FROM (
                    SELECT item_fg_id, qty, DATE(request_date) as t_date 
                        FROM transaction_fg 
                        WHERE transaction_kind = 'OUT' AND request_date BETWEEN '$filter_from' AND '$filter_to'
                    UNION ALL
                    SELECT item_fg_id, qty, DATE(delivery_note_date) as t_date 
                        FROM delivery_notes 
                        WHERE delivery_note_date BETWEEN '$filter_from' AND '$filter_to'
                    UNION ALL
                    SELECT f.item_fg_id, f.qty, DATE(e.trans_date) as t_date 
                        FROM scan_repair_of_goods f 
                        JOIN repair_of_goods e ON e.document_no = f.document_no AND f.item_fg_id = e.item_fg_id 
                        WHERE DATE(e.trans_date) BETWEEN '$filter_from' AND '$filter_to'
            ) as gabungan_out 
            GROUP BY item_fg_id) prev_out";

            // QUERY UTAMA
            $this->db->select("lc.*, 
                c.os_so, 
                d.so,
                a.mpq,
                a.qty_box,
                a.default_packing,
                a.min,
                a.max,
                del.earliest_delivery,
                prev_out.avg_dn,
                (CASE 
                    WHEN COALESCE(prev_out.avg_dn, 0) > 0 THEN 
                        (COALESCE(lc.fg, 0) + COALESCE(lc.wip, 0)) / prev_out.avg_dn
                    ELSE 999999 
                END) as stock_in_days,
                (CASE 
                    WHEN COALESCE(prev_out.avg_dn, 0) > 0 AND 
                        ((COALESCE(lc.fg, 0) + COALESCE(lc.wip, 0)) / prev_out.avg_dn) < COALESCE(a.min, 0) 
                    THEN 0 /* KRITIS! Naikkan ke antrean atas */
                    
                    ELSE 1 /* AMAN  */
                END) as is_critical_stock
            ");

            $this->db->from('generate_loadcap lc');
            $this->db->join('item_fg a', 'lc.item_fg_id = a.id');
            $this->db->join('customer_items b', 'a.id = b.item_fg_id', 'left');
            $this->db->join('customers b2', 'b.customer_id = b2.id', 'left');
            $this->db->join($subquery_mps, 'a.id = c.item_fg_id', 'left', false);
            $this->db->join($subquery_so, 'a.id = d.item_fg_id', 'left', false);
            $this->db->join($subquery_delivery, 'a.id = del.item_fg_id', 'left', false);
            $this->db->join($subquery_prev_out, 'a.id = prev_out.item_fg_id', 'left', false);

            $this->db->where('lc.p_month', $filter_month);
            $this->db->where('lc.p_year', $filter_year);
            $this->db->where('lc.revision', $revision_loadcap);
            $this->db->where('a.status', 0);
            $this->db->where('lc.machine_number !=', 'SUBCONT');

            if (!empty($filter_customer)) {
                $this->db->where('b.customer_id', $filter_customer);
            }
            if (!empty($filter_product_no)) {
                $this->db->where('a.id', $filter_product_no);
            }

            $this->db->group_by(['lc.machine_id', 'lc.item_fg_id']);

            $this->db->order_by('lc.machine_id', 'asc');
            $this->db->order_by('is_critical_stock', 'asc');       
            $this->db->order_by('lc.ito', 'asc');                  
            $this->db->order_by('del.earliest_delivery', 'asc');   
            $this->db->order_by('stock_in_days', 'asc');           

            $records = $this->db->get()->result_array();
            $arr = [];
            $nowTime = date("H:i:s");

            $machine_cumulative_days = []; 
            $machine_day_tracker  = []; 
            $machine_daily_filled = []; 
            
            // ========================================================
            // 0. AMBIL DATA KALENDER UNTUK BULAN INI (OPTIMIZED)
            // ========================================================
            $working_days_map = array_fill(1, 31, false);
            
            $this->db->select('DAY(working_date) as hari, remarks');
            $this->db->from('calendars');
            $this->db->where('YEAR(working_date)', $filter_year);
            $this->db->where('MONTH(working_date)', $filter_month);
            $this->db->where('deleted', 0);
            $cal_data = $this->db->get()->result_array();

            $remark_map = [];
            foreach ($cal_data as $cal) {
                $remark_map[(int)$cal['hari']] = $cal['remarks'];
            }

            $days_in_month_filter = date("t", mktime(0, 0, 0, $filter_month, 1, $filter_year));

            for ($d = 1; $d <= 31; $d++) {
                if ($d > $days_in_month_filter) {
                    $working_days_map[$d] = false;
                    continue;
                }

                $is_sunday = (date("l", mktime(0, 0, 0, $filter_month, $d, $filter_year)) == "Sunday");
                $has_remark = (isset($remark_map[$d]) && $remark_map[$d] != "");

                if ($is_sunday) {
                    $working_days_map[$d] = $has_remark ? true : false;
                } else {
                    $working_days_map[$d] = $has_remark ? false : true;
                }
            }
            // ========================================================

            // reset sequence
            $current_machine = "";
            $seq = 1;

            foreach ($records as $data) {
                // ========================================================
                // LOGIKA RESET SEQUENCE PER MACHINE_ID
                // ========================================================
                $machine_id = $data['machine_id'];

                if ($machine_id != $current_machine) {
                    $seq = 1; 
                    $current_machine = $machine_id; 
                }
                
                $cap_day  = isset($data['cap_day']) ? (float)$data['cap_day'] : 0;
                $prodplan = isset($data['prodplan1']) ? (float)$data['prodplan1'] : 0;
                $qty_box  = isset($data['qty_box']) ? (float)$data['qty_box'] : 0;
                $mpq      = isset($data['mpq']) ? (float)$data['mpq'] : 0;
                $hkw      = isset($data['hkw']) ? (float)$data['hkw'] : 0;
                
                if ($hkw == 0) $hkw = 24; 

                $cap_hour  = $cap_day / 21; 
                $cap_shift = $cap_day / 3;
                
                $X = ($data['default_packing'] == 'BOX') ? $qty_box : $mpq;
                if ($X <= 0) $X = 1; // Safety fallback
                
                // ========================================================
                // PRODPLAN MPP BERDASARKAN ROUNDUP SHIFT
                // ========================================================
                $prodplan_mpp = 0;
                
                $shift_qty_standard = ceil($cap_shift / $X) * $X;
                $daily_qty_standard = $shift_qty_standard * 3;
                
                if ($shift_qty_standard > 0) {
                    $prodplan_mpp = ceil($prodplan / $shift_qty_standard) * $shift_qty_standard;
                } else {
                    $prodplan_mpp = $prodplan;
                }
                // ========================================================
                
                $hour_req  = ($cap_hour > 0) ? ($prodplan_mpp / $cap_hour) : 0;
                $day_req   = ($daily_qty_standard > 0) ? ($prodplan_mpp / $daily_qty_standard) : 0; 

                // ========================================================
                // 1. LOGIKA LOAD VS CAP & OVERLOAD
                // ========================================================
                $X = ($data['default_packing'] == 'BOX') ? $qty_box : $mpq;
                if ($X <= 0) $X = 1; 

                $day_req_mpp = ($daily_qty_standard > 0) ? ($prodplan_mpp / $daily_qty_standard) : 0;

                if (!isset($machine_cumulative_days[$machine_id])) {
                    $machine_cumulative_days[$machine_id] = 0;
                }

                $current_days_load = $machine_cumulative_days[$machine_id];
                $remaining_days_cap = $hkw - $current_days_load; 

                $qty_overload = 0;
                $qty_mpp_final = 0;
                $added_days = 0; 

                if ($remaining_days_cap <= 0) {
                    $qty_overload = $prodplan_mpp; 
                    $qty_mpp_final = 0;
                    $added_days = 0;
                } else {
                    if ($day_req_mpp <= $remaining_days_cap) {
                        $qty_mpp_final = $prodplan_mpp;
                        $qty_overload = 0;
                        $added_days = $day_req_mpp;
                    } else {
                        $potential_qty = $remaining_days_cap * $daily_qty_standard;
                        $fit_qty = floor($potential_qty / $X) * $X;
                        
                        $qty_mpp_final = $fit_qty; 
                        $qty_overload = $prodplan_mpp - $fit_qty; 
                        
                        $added_days = ($daily_qty_standard > 0) ? ($fit_qty / $daily_qty_standard) : 0;
                    }
                }

                $machine_cumulative_days[$machine_id] += $added_days;
                $final_load_vs_cap = ($hkw > 0) ? ($machine_cumulative_days[$machine_id] / $hkw) * 100 : 0;

                // ========================================================
                // 2. LOGIKA SPREADING (STRICT ROUNDDOWN & DUMP LAST DAY)
                // ========================================================
                $days_qty = array_fill(1, 31, 0); 
                
                if ($qty_mpp_final > 0) {
                    $remaining_to_spread = $qty_mpp_final;
                    
                    if ($daily_qty_standard <= 0) $daily_qty_standard = $X; 
                    
                    $target_days = floor($qty_mpp_final / $daily_qty_standard);
                    if ($target_days <= 0) $target_days = 1; // Minimal jalan 1 hari

                    if (!isset($machine_day_tracker[$machine_id])) {
                        $machine_day_tracker[$machine_id] = 1;
                    }

                    $day_count = 0; 

                    while ($remaining_to_spread > 0 && $machine_day_tracker[$machine_id] <= 31) {
                        $d = $machine_day_tracker[$machine_id];

                        if (!$working_days_map[$d]) {
                            $machine_day_tracker[$machine_id]++;
                            continue;
                        }

                        $day_count++;

                        if (!isset($machine_daily_filled[$machine_id][$d])) {
                            $machine_daily_filled[$machine_id][$d] = 0;
                        }

                        $is_last_day = ($day_count >= $target_days || $remaining_to_spread <= $daily_qty_standard);

                        if ($is_last_day) {
                            $put_qty = $remaining_to_spread;
                        } else {
                            $limit = (isset($data['max']) && $data['max'] >= $X) ? (float)$data['max'] : $daily_qty_standard;
                            
                            $space_left = $limit - $machine_daily_filled[$machine_id][$d];
                            
                            if ($space_left < $X) {
                                $machine_day_tracker[$machine_id]++;
                                $day_count--; 
                                continue;
                            }
                            
                            $put_qty = min($daily_qty_standard, $space_left);
                            $put_qty = floor($put_qty / $X) * $X; 
                        }

                        $days_qty[$d] += $put_qty;
                        $remaining_to_spread -= $put_qty;
                        $machine_daily_filled[$machine_id][$d] += $put_qty;

                        if ($machine_daily_filled[$machine_id][$d] >= $daily_qty_standard || $is_last_day) {
                            $machine_day_tracker[$machine_id]++;
                        }
                        
                        if ($remaining_to_spread <= 0) {
                            break; 
                        }
                    }
                }
                // ========================================================

                // ========================================================
                // 3. MENYUSUN DATA JSON 
                // ========================================================
                $row_data = [
                    "sequence"          => $seq++, 
                    "p_month"           => $filter_month,
                    "p_year"            => $filter_year,
                    "revision"          => $revision,
                    "cutoff"            => $filter_cutoff . " " . $nowTime,
                    "item_fg_id"        => $data['item_fg_id'],
                    "machine_id"        => $machine_id,
                    "machine_number"    => $data['machine_number'],
                    "customer_id"       => $data['customer_id'],
                    "mold_id"           => $data['mold_id'],
                    "cavity"            => $data['cavity'],
                    "manpower"          => $data['manpower'],
                    "cycle_time"        => $data['cycle_time'],
                    "forecast"          => $data['forecast'],
                    "prodplan"          => $prodplan,
                    
                    "prodplan_mpp"      => $qty_mpp_final,       
                    "overload"          => $qty_overload,        
                    "load_vs_cap"       => round($final_load_vs_cap, 2), 

                    "cap_day"           => $cap_day,
                    "cap_hour"          => $cap_hour,
                    "cap_shift"         => $cap_shift,
                    "hour_req"          => round($hour_req, 2),
                    "day_req"           => round($day_req, 2),
                    "fg"                => $data['fg'],
                    "os_so"             => $data['os_so'],
                    "so"                => $data['so'],
                    "wip"               => $data['wip'],
                    "ito"               => $data['ito'],
                    "earliest_delivery" => $data['earliest_delivery'],
                    "avg_dn"            => $data['avg_dn'],
                    "stock_in_days"     => $data['stock_in_days'],
                    "hkw"               => $data['hkw'],
                ];

                $total_mpp_spreading = 0;

                for ($i = 1; $i <= 31; $i++) {
                    $row_data["day_$i"] = $days_qty[$i];
                    $total_mpp_spreading += $days_qty[$i]; 
                }
                $row_data["total_mpp"] = $total_mpp_spreading;
                $arr[] = $row_data;
            }

            $response = [
                'total' => count($records),
                'rows'  => $arr
            ];

            echo json_encode($response);
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
        $this->db->from('generate_mpp');
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

    public function create()
    {
        if ($this->input->post()) {
            $post = $this->input->post('data');
            // var_dump ($post);
            // return;

            $generateMpp = $this->crud->reads('generate_mpp', [], [
                "p_month"     => $post['p_month'],
                "p_year"      => $post['p_year'],
                "revision"    => $post['revision'],
                "customer_id" => $post['customer_id'],
                "item_fg_id"  => $post['item_fg_id'],
                "machine_id"  => $post['machine_id']
            ]);

            $postFinal = [
                "sequence"      => $post['sequence'],
                "p_month"       => $post['p_month'],
                "p_year"        => $post['p_year'],
                "revision"      => $post['revision'],
                "cutoff"        => $post['cutoff'],
                "item_fg_id"    => $post['item_fg_id'],
                "machine_id"    => $post['machine_id'],
                "machine_number"=> $post['machine_number'],
                "customer_id"   => $post['customer_id'],
                "mold_id"       => $post['mold_id'],
                "cavity"        => $post['cavity'],
                "manpower"      => $post['manpower'],
                "cycle_time"    => $post['cycle_time'],
                "forecast"      => $post['forecast'],
                "prodplan"      => $post['prodplan'],
                "prodplan_mpp"  => $post['prodplan_mpp'],
                "overload"      => $post['overload'],
                "load_vs_cap"   => $post['load_vs_cap'],
                "cap_day"       => $post['cap_day'],
                "cap_hour"      => $post['cap_hour'],
                "cap_shift"     => $post['cap_shift'],
                "hour_req"      => $post['hour_req'],
                "day_req"       => $post['day_req'],
                "fg"            => $post['fg'],
                "os_so"         => $post['os_so'],
                "so"            => $post['so'],
                "wip"           => $post['wip'],
                "ito"           => $post['ito'],
                "earliest_delivery" => $post['earliest_delivery'],
                "avg_dn"            => $post['avg_dn'],
                "stock_in_days"     => $post['stock_in_days'],
                "total_mpp"     => $post['total_mpp'],
            ];
            for ($i = 1; $i <= 31; $i++) {
                $postFinal["day_$i"] = isset($post["day_$i"]) ? $post["day_$i"] : 0;
            }

            if (count($generateMpp) > 0) {
                $send = $this->crud->update(
                    'generate_mpp',
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
                $send = $this->crud->create('generate_mpp', $postFinal);
            }

            echo json_encode([
                'theme' => 'success',
                'title' => 'SUCCESS',
                'message' => 'Save Data Success'
            ]);
        }
    }

    public function create_from_mpp()
    {
        if ($this->input->post()) {
            $post = $this->input->post();

            $month = $post['month'];
            $year = $post['year'];
            $years = substr($post['year'], -2);
            $item_fg_id = $post['item_fg_id'];

            $item_fg = $this->crud->read('item_fg', [], ["id" => $post['item_fg_id']]);
            $divisions = $this->crud->read('divisions', [], ["id" => $item_fg->division_id]);

            $division = $divisions->number;
            $color = $item_fg->color;
            $status_subcont = $item_fg->status_subcont;
            $subcont_type = $item_fg->subcont_type;

            // =========================================================
            // 1. GENERATE WO NO
            // =========================================================
            $format_pencarian_wo = "/PPC/$month/$years";

            $query_wo = $this->db->query("SELECT MAX(CAST(SUBSTRING_INDEX(wo_no, '/', 1) AS UNSIGNED)) as max_wo 
                                          FROM production_schedules 
                                          WHERE month = '$month' 
                                          AND year = '$year' 
                                          AND wo_no LIKE '%$format_pencarian_wo'
                                          AND wo_no REGEXP '^[0-9]+/'")->row();
            
            $urutan_wo = (int)($query_wo->max_wo ?? 0) + 1;
            $wo_no = str_pad($urutan_wo, 3, '0', STR_PAD_LEFT) . '/PPC/' . $month . '/' . $years;

            // =========================================================
            // 2. GENERATE LOT NO 
            // =========================================================
            $akhiran_lot = $month . $years; 
            
            $query_lot = $this->db->query("SELECT MAX(CAST(SUBSTRING(lot_no, 1, LENGTH(lot_no) - 4) AS UNSIGNED)) as max_seq 
                                           FROM production_schedules 
                                           WHERE month = '$month' 
                                           AND year = '$year' 
                                           AND item_fg_id = '$item_fg_id'
                                           AND lot_no LIKE '%$akhiran_lot'")->row();
            
            // Ambil nomor urut tertinggi, lalu tambah 1
            $urutan_lot = (int)($query_lot->max_seq ?? 0) + 1;
            
            $lot_no = $urutan_lot . $akhiran_lot;

            // =========================================================
            // 3. AUTO-KALKULASI TOTAL PURGING
            // =========================================================
            $machine_id = $post['machine_id'];
            
            $purging_color = $color; 
            
            if ($purging_color == 'BLACK' || $purging_color == 'FR BLACK P B B') {
                $purging_color = 'BLACK';
            } elseif ($purging_color == 'WHITE' || $purging_color == 'CLEAR WHITE' || $purging_color == 'BRIGHT WHITE' || $purging_color == 'DIFFUSE WHITE') {
                $purging_color = 'CLEAR';
            } else {
                $purging_color = 'COLORFULL';
            }

            $query_purging = $this->db->query("SELECT DISTINCT total 
                                               FROM purgings 
                                               WHERE machine_id = '$machine_id' AND kind = '$purging_color' LIMIT 1")->row();
            $total_purging = $query_purging ? $query_purging->total : 0;

            // =========================================================
            // 4. SIAPKAN ARRAY UNTUK DI-INSERT
            // =========================================================
            $insert_data = [
                'wo_no'          => $wo_no,
                'period'         => $year . $month,
                'month'          => $month,
                'year'           => $year,
                'lot_no'         => $lot_no,
                'mold_id'        => $post['mold_id'],
                'trans_date'     => $post['trans_date'],
                'division'       => $division, 
                'item_fg_id'     => $item_fg_id,
                'item_fg_name'   => $post['item_fg_name'],
                'wo_no_assembly' => empty($post['wo_no_assembly']) ? null : $post['wo_no_assembly'],
                'color'          => $color, // Tetap gunakan warna asli (misal: FR BLACK P B B)
                'status_subcont' => $status_subcont,
                'subcont_type'   => $subcont_type,
                'machine_id'     => $machine_id,
                'total_purging'  => $total_purging,
                'qty'            => $post['qty'],
                'remarks'        => $post['remarks'] 
            ];

           // =========================================================
            // 5. EKSEKUSI MENGGUNAKAN FUNGSI CRUD
            // =========================================================
            $send = $this->crud->create('production_schedules', $insert_data);

            $response_data = json_decode($send, true);

            if (isset($response_data['theme']) && $response_data['theme'] == 'success') {
                $get_new_data = $this->db->query("SELECT id FROM production_schedules 
                                                  WHERE wo_no = '$wo_no' 
                                                  AND lot_no = '$lot_no' 
                                                  ORDER BY id DESC LIMIT 1")->row();
                
                $response_data['id'] = $get_new_data ? $get_new_data->id : 0;
            }

            echo json_encode($response_data);

        } else {
            echo json_encode(['title' => 'Error', 'theme' => 'error', 'message' => 'Invalid Request']);
        }
    }

    public function update_inline()
    {
        if ($this->input->post()) {
            $post = $this->input->post();
            $id = isset($post['id']) ? $post['id'] : null;
            $item_fg_id = isset($post['item_fg_id']) ? $post['item_fg_id'] : null;
            $machine_id = isset($post['machine_id']) ? $post['machine_id'] : null;
            $p_month    = isset($post['p_month']) ? $post['p_month'] : null;
            $p_year     = isset($post['p_year']) ? $post['p_year'] : null;
            $revision   = isset($post['revision']) ? $post['revision'] : null;

            $update_data = [
                'manpower'   => isset($post['manpower']) ? (float)$post['manpower'] : 0,
                'cavity'     => isset($post['cavity']) ? (float)$post['cavity'] : 0,
                'cycle_time' => isset($post['cycle_time']) ? (float)$post['cycle_time'] : 0,
                'hour_req'   => isset($post['hour_req']) ? (float)$post['hour_req'] : 0,
                'day_req'    => isset($post['day_req']) ? (float)$post['day_req'] : 0,
                'prodplan'   => isset($post['prodplan']) ? (float)$post['prodplan'] : 0,
                'forecast'   => isset($post['forecast']) ? (float)$post['forecast'] : 0,
                'total_mpp'  => isset($post['total_mpp']) ? (float)$post['total_mpp'] : 0,
            ];

            $new_total_mpp = 0; 

            for ($i = 1; $i <= 31; $i++) {
                $day_field = 'day_' . $i;
                if (isset($post[$day_field])) {
                    $val = $post[$day_field];
                    
                    $clean_val = (is_numeric($val) && $val > 0) ? (int)$val : 0;
                    
                    $update_data[$day_field] = $clean_val;
                    $new_total_mpp += $clean_val; 
                }
            }

            $update_data['total_mpp'] = $new_total_mpp;

            $where = [];
            if (!empty($id)) {
                $where = ['id' => $id];
            } else {
                $where = [
                    'item_fg_id' => $item_fg_id,
                    'machine_id' => $machine_id,
                    'p_month'    => $p_month,
                    'p_year'     => $p_year,
                    'revision'   => $revision
                ];
            }

            $update_result = $this->crud->update('generate_mpp', $where, $update_data);

            if ($update_result) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal update data atau Session Expired']);
            }
            
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
        }
    }

    public function uploadclearFailed()
    {
        @unlink('failed/generate_mpp.txt');
    }

    public function uploadcreateFailed()
    {
        if ($this->input->post()) {
            $message = $this->input->post('message');
            $textFailed = fopen('failed/generate_mpp.txt', 'a');
            fwrite($textFailed, $message . "\n");
            fclose($textFailed);
        }
    }

    public function uploadDownloadFailed()
    {
        $file = "failed/generate_mpp.txt";

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
            header("Content-Disposition: attachment; filename=generate_mpp_$format.xls");
        }

        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();

        //Filter Data
        $filter_month = base64_decode($this->input->get('filter_month'));
        $filter_year = base64_decode($this->input->get('filter_year'));
        $filter_cutoff = base64_decode($this->input->get('filter_cutoff'));
        $filter_product_no = base64_decode($this->input->get('filter_item_fg'));
        $filter_revision = base64_decode($this->input->get('filter_revision'));
        $period = $filter_year . "-" . $filter_month;

        $query_main = "SELECT a.*, 
                    b.name, 
                    b.number,
                    b.status_subcont,
                    b.color,
                    b.mpq,
                    b.qty_box,
                    b.default_packing,
                    c.name as customer_name,
                    d.mold_name
                    FROM generate_mpp a
                    JOIN item_fg b ON a.item_fg_id = b.id
                    JOIN customers c ON a.customer_id = c.id
                    JOIN molds d ON a.mold_id = d.id
                    JOIN machines e ON a.machine_id = e.id
                    WHERE a.item_fg_id LIKE '%$filter_product_no%' 
                        AND a.p_month LIKE '%$filter_month%' 
                        AND a.p_year LIKE '%$filter_year%' 
                        AND a.revision LIKE '%$filter_revision%' 
                        AND a.status = 0
                    
                    /* KUNCI PERBAIKAN TABEL KACAU ADA DI SINI */
                    ORDER BY a.machine_number ASC, a.sequence ASC 
                    ";

        $records = $this->crud->query($query_main);

        // =========================================================
        // HITUNG JUMLAH BARIS PER MESIN UNTUK ROWSPAN
        // =========================================================
        $rowspan_data = [];
        foreach ($records as $r) {
            $mc = $r->machine_number;
            if (!isset($rowspan_data[$mc])) {
                $rowspan_data[$mc] = 0;
            }
            $rowspan_data[$mc]++;
        }
        // =========================================================

        // 1. TANGKAP DAN HITUNG JUMLAH HARI
        $bulan = (int)$filter_month; 
        $tahun = (int)$filter_year;
        
        $jumlah_hari = date('t', mktime(0, 0, 0, $bulan, 1, $tahun));
        $nama_bulan  = date('M', mktime(0, 0, 0, $bulan, 1, $tahun)); // Format 'Apr', 'May', dll

        $html = '<html><head><title>Print Data</title></head>
        <style>
            body {font-family: Arial, Helvetica, sans-serif;}
            #customers {border-collapse: collapse;white-space: nowrap;font-size: 12px;}
            #customers td, #customers th {border: 1px solid #ddd;padding: 2px;}
            #customers tr:nth-child(even){background-color: #f2f2f2;}
            #customers tr:hover {background-color: #ddd;}
            #customers th {padding-top: 2px;padding-bottom: 2px;text-align: center;color: black; background-color: #f2f2f2;}
        </style><body>
            <center>
            <div style="float: left; font-size: 12px; text-align: left;">
                <table style="width: 100%;">
                    <tr>
                        <td width="50" style="font-size: 12px; vertical-align: top; text-align: center; margin-right:10px;">
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
            <h3 style="margin:0;">MPP</h3>
        </center>
        <br>
            <table id="customers" border="1" style="font-size: 11px;">
                <tr>
                    <th>MC No</th>
                    <th>Customer</th>
                    <th width="20">No</th>
                    <th>Product No</th>
                    <th>Product Name</th>
                    <th>Subcont</th>
                    <th>Mold No</th>
                    <th>Color</th>
                    <th>Manpower</th>
                    <th>Cav Std</th>
                    <th>C/T</th>
                    <th>Mpq</th>
                    <th>Box</th>
                    <th>Default Packing</th>
                    <th>Cap/Hour</th>
                    <th>Cap/Shift</th>
                    <th>Cap/Day</th>
                    <th>Hour Req</th>
                    <th>Day Req</th>
                    <th>OS SO</th>
                    <th>SO</th>
                    <th>Stock FG</th>
                    <th>Stock WIP</th>
                    <th>ITO</th>
                    <th>Prodplan MPS</th>
                    <th>Prodplan MPP</th>
                    <th>Overload</th>
                    <th>Forecast</th>
                    <th>Total MPP</th>
                    <th>Load Vs Cap</th>';

        // ========================================================
        // 2. LOOPING HEADER TANGGAL DINAMIS (Berdasarkan jumlah hari)
        // ========================================================
        for ($d = 1; $d <= $jumlah_hari; $d++) {
            // Mengambil nama hari (Mon, Tue, Wed, dll)
            $day_name = date('D', mktime(0, 0, 0, $bulan, $d, $tahun));
            $html .= '<th>' . $day_name . '<br>' . $d . '-' . $nama_bulan . '</th>';
        }

        $html .= '</tr>';

        $current_mc = ""; 
        $no = 1;

        foreach ($records as $record) {
            $html .= '<tr>';
            
            if ($current_mc != $record->machine_number) {
                $row_count = $rowspan_data[$record->machine_number];
                $html .= '<td rowspan="' . $row_count . '" style="text-align:center; vertical-align:middle; font-weight:bold;">' . $record->machine_number . '</td>';
                $current_mc = $record->machine_number; 
                $no = 1; 
            }

            $print_overload = ($record->overload > 0) ? $record->overload : '-';
            $print_mpp = ($record->prodplan_mpp > 0) ? $record->prodplan_mpp : '-';

            $html .= '      <td>' . $record->customer_name . '</td>
                            <td style="text-align:center">' . $no . '</td>
                            <td style="mso-number-format:\@;">' . $record->number . '</td>
                            <td style="mso-number-format:\@;">' . $record->name . '</td>
                            <td style="text-align:center;">' . $record->status_subcont . '</td>
                            <td>' . $record->mold_name . '</td>
                            <td>' . $record->color . '</td>
                            <td style="text-align:right;">' . $record->manpower . '</td>
                            <td style="text-align:right;">' . $record->cavity . '</td>
                            <td style="text-align:right;">' . $record->cycle_time . '</td>
                            <td style="text-align:right;">' . $record->mpq . '</td>
                            <td style="text-align:right;">' . $record->qty_box . '</td>
                            <td style="text-align:right;">' . $record->default_packing . '</td>
                            <td style="text-align:right;">' . $record->cap_hour . '</td>
                            <td style="text-align:right;">' . $record->cap_shift . '</td>
                            <td style="text-align:right;">' . $record->cap_day . '</td>
                            <td style="text-align:right;">' . $record->hour_req . '</td>
                            <td style="text-align:right;">' . $record->day_req . '</td>                            
                            <td style="text-align:right;">' . $record->os_so . '</td>
                            <td style="text-align:right;">' . $record->so . '</td>
                            <td style="text-align:right;">' . $record->fg . '</td>
                            <td style="text-align:right;">' . $record->wip . '</td>
                            <td style="text-align:right;">' . $record->ito . '</td>
                            <td style="text-align:right;">' . $record->prodplan . '</td>
                            <td style="text-align:right;">' . $print_mpp . '</td>
                            <td style="text-align:right; font-weight:bold; color:red;">' . $print_overload . '</td>
                            <td style="text-align:right;">' . $record->forecast . '</td>
                            <td style="text-align:right;">' . $record->total_mpp . '</td>
                            <td style="text-align:right; font-weight:bold;">' . $record->load_vs_cap . '%</td>';

            // ========================================================
            // 3. LOOPING ISI DATA HARI (day_1 sampai akhir bulan)
            // ========================================================
            for ($d = 1; $d <= $jumlah_hari; $d++) {
                $field_day = 'day_' . $d;
                $qty_day = (isset($record->$field_day) && $record->$field_day > 0) ? $record->$field_day : '-';
                
                $html .= '<td style="text-align:right;">' . $qty_day . '</td>';
            }

            $html .= '</tr>';
            $no++;
        }

        $html .= '</table></body></html>';
        echo $html;
    }

    public function print_wo($id)
    {
        //Config
        $this->db->select('*');
        $this->db->from('config');
        $config = $this->db->get()->row();
        $config_iso = $this->db->get('config_iso')->row();

        $this->db->select("a.*, 
            c.number as item_number, 
            c.name as item_name, 
            c.uom, 
            c.is_no, 
            e.number as machine_number, 
            f.mold_name, 
            f.cavity_standard, 
            f.cavity_actual, 
            f.id as mold_id, 
            h.cycle_time, 
            h.cycle_time_process, 
            h.manpower,
            h.shift,
            h.shift_hour,
            h.productcivity");
        $this->db->from('production_schedules a');
        $this->db->join('item_fg c', 'a.item_fg_id = c.id', 'left');
        $this->db->join('mold_items d', 'c.id = d.item_fg_id', 'left');
        $this->db->join('machines e', 'a.machine_id = e.id', 'left');
        $this->db->join('molds f', 'd.mold_id = f.id', 'left');
        $this->db->join('scan_item_receipts_fg g', 'a.so_number = g.so_number and a.wo_no = g.workorder', 'left');
        $this->db->join('menu_loadings h', 'c.id = h.item_fg_id','left');
        $this->db->join('bom i', 'a.item_fg_id = i.item_fg_id','left');
        $this->db->where('a.deleted', 0);
        $this->db->where('a.id', $id);
        $this->db->group_by('a.wo_no');
        $records = $this->db->get()->result_array();

        $html = '';
        // Cek apakah ada data
        if (!empty($records)) {
            foreach ($records as $data) {
                // Query tambahan untuk mengambil item_rm_id dari tabel bom
                $this->db->select('b.number as item_number, a.composition, b.uom');
                $this->db->from('bom a');
                $this->db->join('item_rm b', 'a.item_rm_id = b.id');
                $this->db->where('item_fg_id', $data['item_fg_id']);
                $bom_items = $this->db->get()->result_array();

                $shift_hour = !empty($data['shift_hour']) ? $data['shift_hour'] : 0;
                $cycle_time = !empty($data['cycle_time']) && $data['cycle_time'] > 0 ? $data['cycle_time'] : 1;
                $cavity_std = !empty($data['cavity_standard']) ? $data['cavity_standard'] : 0;
                $shift_qty  = !empty($data['shift']) ? $data['shift'] : 0;
                $prod_rate  = isset($data['productcivity']) ? ($data['productcivity'] / 100) : 1; 

                $cap_day = ((3600 * $shift_hour) / $cycle_time) * $cavity_std * $shift_qty * $prod_rate;
                $target_per_shift = ceil($cap_day / 3);

                // Gabungkan item_rm_id menjadi satu string
                $material_list = '';
                $qty = '';
                $uom = '';
                foreach ($bom_items as $item) {
                    $material_list .= $item['item_number'] . '<br>';
                    $qty .= number_format($item['composition'] * $data['qty']) . '<br>';
                    $uom .= $item['uom'] . '<br>';
                }

                $html = '<html><head><title>'.$data['item_number'].'</title></head>
                    <script>
                        window.onload = function() {
                            window.print();
                        };
                    </script>
                    <style>
                        body {font-family: Arial, Helvetica, sans-serif;}
                        .bordered-table1 {width: 80%; border-collapse: collapse; margin: auto;}
                        .bordered-table1 td {border: 1px solid #000; padding: 5px; text-align: left;}
                        .bordered-table2 {width: 50%; border-collapse: collapse; margin: auto;}
                        .bordered-table2 td {border: 1px solid #000; padding: 5px; text-align: left; height: 50px; font-size: 25px;}
                        .bordered-table3 {width: 100%; border-collapse: collapse; margin: auto;}
                        .bordered-table3 td {border: 1px solid #000; padding: 5px; text-align: left;}
                        .no-border-table {width: 100%; border-collapse: collapse;}
                        .no-border-table td {border: none; padding: 5px; text-align: left;}
                        .header-table {width: 100%; margin-bottom: 10px; border-collapse: collapse;}
                        .header-table td {border: none; padding: 5px; text-align: left; vertical-align: top;}

                        .bordered-table {border-collapse: collapse;width: 30%;margin: 10px;}
                        .bordered-table td, .bordered-table th {border: 1px solid #000;padding: 5px;text-align: center;}
                        .signature-section {width: 40%;height: 30px;}
                        .signature-section2 {width: 40%;height: 100px;}
                        .signature-header {font-weight: bold;}
                        .left-table {float: left;}
                        .right-table {float: right;}

                        .content-table {width: 100%; margin-top: 20px;}
                        .content-table td {padding: 5px; vertical-align: top;}
                        .right-align {text-align: right;}
                    </style>

                    <body>
                        <center>
                            <table class="header-table">
                                <tr>
                                    <td style="font-size: 40px; text-align: center;">
                                        WORK ORDER (WO) & <br>PRODUCTION REPORT (PR)
                                    </td>
                                </tr>
                            </table>
                        </center>
                        <br>
                        <table class="bordered-table1">
                            <tr>
                                <td style="width: 30%; text-align: left;">Issued Date</td>
                                <td style="width: 70%; text-align: center;">'.$data['trans_date'].'</td>
                            </tr>
                            <tr>
                                <td style="width: 30%; text-align: left;">No Doc</td>
                                <td style="width: 70%; text-align: center;">'.$data['wo_no'].'</td>
                            </tr>
                            <tr>
                                <td style="width: 30%; text-align: left;">Part No</td>
                                <td style="width: 70%; text-align: center;">'.$data['item_number'].'</td>
                            </tr>
                            <tr>
                                <td style="width: 30%; text-align: left;">Part Name</td>
                                <td style="width: 70%; text-align: center;">'.$data['item_name'].'</td>
                            </tr>
                            <tr>
                                <td style="width: 30%; text-align: left;">IS No</td>
                                <td style="width: 70%; text-align: center;">'.$data['is_no'].'</td>
                            </tr>
                            <tr>
                                <td style="width: 30%; text-align: left;">Mold No</td>
                                <td style="width: 70%; text-align: center;">'.$data['mold_id'].'</td>
                            </tr>
                            <tr>
                                <td style="width: 30%; text-align: left;">Machine No</td>
                                <td style="width: 70%; text-align: center;">'.$data['machine_number'].'</td>
                            </tr>
                        </table>
                        <br>
                        <table class="bordered-table2">
                            <tr>
                                <td style="width: 50%; text-align: left;">Lot No</td>
                                <td style="width: 50%; text-align: center;">'.$data['lot_no'].'</td>
                            </tr>
                            <tr>
                                <td style="width: 50%; text-align: left;">Qty (Pcs)</td>
                                <td style="width: 50%; text-align: center;">'.$data['qty'].'</td>
                            </tr>
                            <tr>
                                <td style="width: 50%; text-align: left;">Lead Time (Hour)</td>
                                <td style="width: 50%; text-align: center;">'.number_format(($data['qty']) / (3600 / ($data['cycle_time'] + $data['cycle_time_process']) * $data['cavity_standard'] * 0.85), 2).'</td>
                            </tr>
                        </table>
                        <br>
                        <table class="bordered-table1">
                            <tr>
                                <td style="width: 30%; text-align: left;">Polybag Label</td>
                                <td style="width: 70%; text-align: center;">Tidak Pakai Label Manual</td>
                            </tr>
                            <tr>
                                <td style="width: 30%; text-align: left;">Box Label</td>
                                <td style="width: 70%; text-align: center;">Tidak Pakai Label Manual</td>
                            </tr>
                        </table>
                        <br>
                        <table class="bordered-table1">
                            <tr>
                                <td style="width: 50%; text-align: center;"><b>Cycle Time</b></td>
                                <td style="width: 50%; text-align: center;"><b>Man Power</b></td>
                            </tr>
                            <tr>
                                <td style="vertical-align: top;">
                                    <table class="bordered-table1">
                                        <tr>
                                            <td style="text-align: center;">Cavity Std</td>
                                            <td style="text-align: center;">C/T Machine (sec)</td>
                                            <td style="text-align: center;">Target/Shift (pcs)</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;">'.$data['cavity_standard'].'</td>
                                            <td style="text-align: center;">'.$data['cycle_time'].'</td>
                                            <td style="text-align: center;" rowspan="3">'.$target_per_shift.'</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;">Cavity Actual</td>
                                            <td style="text-align: center;">C/T Finishing (sec)</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;">'.$data['cavity_actual'].'</td>
                                            <td style="text-align: center;">'.$data['cycle_time_process'].'</td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="vertical-align: top;">
                                    <table class="bordered-table1">
                                        <tr>
                                            <td style="text-align: left;" colspan="2">Person</td>
                                            <td style="text-align: center;">'.$data['manpower'].'</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center;" colspan="3">Material</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left;">'.$material_list.'</td>
                                            <td style="text-align: center;">'.$qty.'</td>
                                            <td style="text-align: center;">'.$uom.'</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <br>
                        <table class="bordered-table3">
                            <tr>
                                <td colspan="3"><b>Condition Check Mold:</b></td>
                                <td style="text-align: center;" colspan="2"><b>Diperiksa</b></td>
                                
                            </tr>
                            <tr>
                                <td style="width: 50%; text-align: left;">Cavity Mold</td>
                                <td style="width: 15%; text-align: center;">
                                    <span style="display: inline-block; width: 20px; height: 20px; border: 1px solid black;"></span> OK
                                </td>
                                <td style="width: 15%; text-align: center;">
                                    <span style="display: inline-block; width: 20px; height: 20px; border: 1px solid black;"></span> .....
                                </td>
                                <td rowspan="3" colspan="2"</td>
                            </tr>
                            <tr>
                                <td style="width: 35%; text-align: left;">Cooling Mold</td>
                                <td style="width: 15%; text-align: center;">
                                    <span style="display: inline-block; width: 20px; height: 20px; border: 1px solid black;"></span> OK
                                </td>
                                <td style="width: 15%; text-align: center;">
                                    <span style="display: inline-block; width: 20px; height: 20px; border: 1px solid black;"></span> .....
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 35%; text-align: left;">Nepple Mold</td>
                                <td style="width: 15%; text-align: center;">
                                    <span style="display: inline-block; width: 20px; height: 20px; border: 1px solid black;"></span> OK
                                </td>
                                <td style="width: 15%; text-align: center;">
                                    <span style="display: inline-block; width: 20px; height: 20px; border: 1px solid black;"></span> .....
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" style="width: 35%; text-align: left; height: 120px; vertical-align: top;">Catatan :</td>
                            </tr>
                        </table>

                        <br>
                        <table class="bordered-table left-table">
                            <tr>
                                <th class="signature-header" colspan="2">MENGETAHUI</th>
                            </tr>
                            <tr>
                                <td class="signature-section2"></td>
                                <td class="signature-section2"></td>
                            </tr>
                            <tr>
                                <td class="signature-section"></td>
                                <td class="signature-section"></td>
                            </tr>
                            <tr>
                                <td>SPV QC</td>
                                <td>SPV PRD</td>
                            </tr>
                        </table>

                        <table class="bordered-table right-table">
                            <tr>
                                <th class="signature-header">DIPERIKSA</th>
                                <th class="signature-header">DIBUAT</th>
                            </tr>
                            <tr>
                                <td class="signature-section2"></td>
                                <td class="signature-section2"></td>
                            </tr>
                            <tr>
                                <td class="signature-section"></td>
                                <td class="signature-section"></td>
                            </tr>
                            <tr>
                                <td>SPV PPC</td>
                                <td>PPC</td>
                            </tr>
                        </table>
                    </body></html>';
            } 
        } else {
            $html = "Data tidak ditemukan.";
        }

        echo $html;
    }

    // public function print($option = "")
    // {
    //     if ($option == "excel") {
    //         $format  = date("Ymd");
    //         header("Content-type: application/vnd-ms-excel");
    //         header("Content-Disposition: attachment; filename=generate_mpp_$format.xls");
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

    //     $query_main = "SELECT a.*, 
    //         b.name, 
    //         b.number,
    //         b.status_subcont,
    //         b.color,
    //         c.name as customer_name,
    //         d.mold_name
    //         FROM generate_mpp a
    //         JOIN item_fg b ON a.item_fg_id = b.id
    //         JOIN customers c ON a.customer_id = c.id
    //         JOIN molds d ON a.mold_id = d.id
    //         JOIN machines e ON a.machine_id = e.id
    //         WHERE a.item_fg_id LIKE '%$filter_product_no%' 
    //             AND a.p_month LIKE '%$filter_month%' 
    //             AND a.p_year LIKE '%$filter_year%' 
    //             AND a.revision LIKE '%$filter_revision%' 
    //             AND a.status = 0
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
    //         <h3 style="margin:0;">MPP</h3>
    //     </center>
    //     <br>
    //         <table id="customers" border="1" style="font-size: 11px;">
    //             <tr>
    //                 <th rowspan="2" width="20">No</th>
    //                 <th rowspan="2">MC No</th>
    //                 <th rowspan="2">Customer</th>
    //                 <th rowspan="2">Product No</th>
    //                 <th rowspan="2">Product Name</th>
    //                 <th rowspan="2">Subcont</th>
    //                 <th rowspan="2">Mold No</th>
    //                 <th rowspan="2">Color</th>
    //                 <th rowspan="2">Manpower</th>
    //                 <th rowspan="2">Cav Std</th>
    //                 <th rowspan="2">C/T</th>
    //                 <th rowspan="2">Cap/Hour</th>
    //                 <th rowspan="2">Cap/Shift</th>
    //                 <th rowspan="2">Cap/Day</th>
    //                 <th rowspan="2">Hour Req</th>
    //                 <th rowspan="2">Day Req</th>
    //                 <th rowspan="2">Prodplan</th>
    //                 <th rowspan="2">Forecast</th>
    //             </tr>';
    //     $no = 1;
    //     foreach ($records as $record) {
    //         $item_fg_id = $record->id;

    //         $html .= '  <tr>
    //                         <td style="text-align:center">' . $no . '</td>
    //                         <td>' . $record->machine_number . '</td>
    //                         <td>' . $record->customer_name . '</td>
    //                         <td style="mso-number-format:\@;">' . $record->number . '</td>
    //                         <td style="mso-number-format:\@;">' . $record->name . '</td>
    //                         <td>' . $record->status_subcont . '</td>
    //                         <td>' . $record->mold_name . '</td>
    //                         <td>' . $record->color . '</td>
    //                         <td>' . $record->manpower . '</td>
    //                         <td>' . $record->cavity . '</td>
    //                         <td>' . $record->cycle_time . '</td>
    //                         <td>' . $record->cap_hour . '</td>
    //                         <td>' . $record->cap_shift . '</td>
    //                         <td>' . $record->cap_day . '</td>
    //                         <td>' . $record->hour_req . '</td>
    //                         <td>' . $record->day_req . '</td>
    //                         <td>' . $record->prodplan . '</td>
    //                         <td>' . $record->forecast . '</td>
    //                     </tr>';
    //             $no++;
    //     }

    //     $html .= '</table></div></body></html>';
    //     echo $html;
    // }

    // public function recap_machine($option = "")
    // {
    //     if ($option == "excel") {
    //         $format  = date("Ymd");
    //         header("Content-type: application/vnd-ms-excel");
    //         header("Content-Disposition: attachment; filename=generate_mpp_$format.xls");
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
    //         FROM generate_mpp_machine a
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

    // public function recap_machine($option = "")
    // {
    //     if ($option == "excel") {
    //         $format  = date("Ymd");
    //         header("Content-type: application/vnd-ms-excel");
    //         header("Content-Disposition: attachment; filename=generate_mpp_$format.xls");
    //     }

    //     // === CONFIG & FILTER ===
    //     $this->db->select('*');
    //     $this->db->from('config');
    //     $config = $this->db->get()->row();

    //     $filter_month = base64_decode($this->input->get('filter_month'));
    //     $filter_year = base64_decode($this->input->get('filter_year'));
    //     $filter_revision = base64_decode($this->input->get('filter_revision'));

    //     // === REKAP PER MACHINE ===
    //     $query_main = "
    //         SELECT a.* 
    //         FROM generate_mpp_machine a
    //         JOIN machines b ON a.machine_id = b.id
    //         WHERE a.p_month = '$filter_month'
    //             AND a.p_year = '$filter_year'
    //             AND a.revision = '$filter_revision'
    //         ORDER BY a.machine_number
    //     ";
    //     $records = $this->crud->query($query_main);

    //     // === REKAP PER MANPOWER (dari table generate_mpp_manpower) ===
    //     $query_man = "
    //         SELECT a.*
    //         FROM generate_mpp_manpower a
    //         JOIN machines b ON a.machine_id = b.id
    //         WHERE a.p_month = '$filter_month'
    //             AND a.p_year = '$filter_year'
    //             AND a.revision = '$filter_revision'
    //         ORDER BY a.machine_number
    //     ";
    //     $manpower = $this->crud->query($query_man);

    //     // === REKAP PER TONNAGE ===
    //     $query_ton = "
    //         SELECT 
    //             toonage,
    //             COUNT(machine_id) AS unit,
    //             SUM(loadcap1) AS total,
    //             ROUND(SUM(loadcap1) / COUNT(machine_id), 2) AS average
    //         FROM generate_mpp_machine
    //         WHERE p_month = '$filter_month'
    //             AND p_year = '$filter_year'
    //             AND revision = '$filter_revision'
    //             AND toonage > 0
    //         GROUP BY toonage
    //         ORDER BY toonage ASC
    //     ";
    //     $tonnage = $this->crud->query($query_ton);

    //     // === NAMA BULAN ===
    //     $monthNames = [];
    //     $y = (int)$filter_year;
    //     $m = (int)$filter_month;
    //     for ($i = 0; $i < 4; $i++) {
    //         $timestamp = strtotime("$y-$m-01");
    //         $monthNames[] = strtoupper(substr(date('M', $timestamp), 0, 3)) . " " . substr(date('Y', $timestamp), 2, 2);
    //         $m++;
    //         if ($m > 12) { $m = 1; $y++; }
    //     }

    //     // === HTML ===
    //     $html = '
    //     <html><head><title>Loadcap Report</title></head>
    //     <style>
    //         body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
    //         #customers { border-collapse: collapse; width: 100%; font-size: 11px; margin-bottom: 20px; }
    //         #customers td, #customers th { border: 1px solid #ccc; padding: 3px; }
    //         #customers th { background: #f0f0f0; text-align: center; }
    //         .grid-2x2 { display: grid; grid-template-columns: 50% 50%; grid-template-rows: auto auto; gap: 10px; }
    //         .table-box { width: 100%; }
    //         h3 { margin-bottom: 4px; text-align: center; }
    //     </style>
    //     <body>
    //     <div style="float:left;text-align:left;">
    //         <table style="width:100%;">
    //             <tr>
    //                 <td width="50" style="text-align:center;">
    //                     <img src="' . $config->favicon . '" width="30">
    //                 </td>
    //                 <td>
    //                     <b>' . $config->name . '</b><br>
    //                     <small>' . $config->description . '</small>
    //                 </td>
    //             </tr>
    //         </table>
    //     </div>
    //     <div style="float:right;text-align:right;">
    //         Print Date: ' . date("d M Y H:i:s") . '<br>
    //         Print By: ' . $this->session->username . '
    //     </div>
    //     <div style="clear:both;"></div><br><br>

    //     <div class="grid-2x2">

    //         <!-- LEFT TOP: MACHINE -->
    //         <div class="table-box">
    //             <h3>REKAP PER MACHINE</h3>
    //             <table id="customers">
    //                 <tr>
    //                     <th>No</th>
    //                     <th>MC NO</th>
    //                     <th>Tonage</th>
    //                     <th>' . $monthNames[0] . '</th>
    //                     <th>' . $monthNames[1] . '</th>
    //                     <th>' . $monthNames[2] . '</th>
    //                     <th>' . $monthNames[3] . '</th>
    //                 </tr>';

    //     $no = 1;
    //     foreach ($records as $r) {
    //         $html .= '
    //             <tr>
    //                 <td align="center">' . $no++ . '</td>
    //                 <td>' . $r->machine_number . '</td>
    //                 <td align="center">' . $r->toonage . '</td>
    //                 <td align="right" style="color:' . ($r->loadcap1 > 100 ? 'red' : 'black') . ';">' . number_format($r->loadcap1, 2) . '%</td>
    //                 <td align="right" style="color:' . ($r->loadcap2 > 100 ? 'red' : 'black') . ';">' . number_format($r->loadcap2, 2) . '%</td>
    //                 <td align="right" style="color:' . ($r->loadcap3 > 100 ? 'red' : 'black') . ';">' . number_format($r->loadcap3, 2) . '%</td>
    //                 <td align="right" style="color:' . ($r->loadcap4 > 100 ? 'red' : 'black') . ';">' . number_format($r->loadcap4, 2) . '%</td>
    //             </tr>';
    //     }

    //     $html .= '
    //             </table>
    //         </div>

    //         <!-- RIGHT TOP: MANPOWER -->
    //         <div class="table-box">
    //             <h3>REKAP PER MANPOWER</h3>
    //             <table id="customers">
    //                 <tr>
    //                     <th>No</th>
    //                     <th>MC NO</th>
    //                     <th>Tonage</th>
    //                     <th>' . $monthNames[0] . '</th>
    //                     <th>' . $monthNames[1] . '</th>
    //                     <th>' . $monthNames[2] . '</th>
    //                     <th>' . $monthNames[3] . '</th>
    //                 </tr>';

    //     $no = 1;
    //     foreach ($manpower as $m) {
    //         $html .= '
    //                 <tr>
    //                     <td align="center">' . $no++ . '</td>
    //                     <td>' . $m->machine_number . '</td>
    //                     <td align="center">' . $m->toonage . '</td>
    //                     <td align="right" style="color:' . ($m->manpower1 > 100 ? 'red' : 'black') . ';">' . number_format($m->manpower1, 2) . '</td>
    //                     <td align="right" style="color:' . ($m->manpower2 > 100 ? 'red' : 'black') . ';">' . number_format($m->manpower2, 2) . '</td>
    //                     <td align="right" style="color:' . ($m->manpower3 > 100 ? 'red' : 'black') . ';">' . number_format($m->manpower3, 2) . '</td>
    //                     <td align="right" style="color:' . ($m->manpower4 > 100 ? 'red' : 'black') . ';">' . number_format($m->manpower4, 2) . '</td>
    //                 </tr>';
    //     }

    //     $html .= '
    //             </table>
    //         </div>

    //         <!-- LEFT BOTTOM: TONNAGE -->
    //         <div class="table-box">
    //             <h3>REKAP PER TONNAGE</h3>
    //             <table id="customers">
    //                 <tr>
    //                     <th>No</th>
    //                     <th>Tonage</th>
    //                     <th>Total</th>
    //                     <th>Unit</th>
    //                     <th>Average (%)</th>
    //                 </tr>';

    //     $no = 1;
    //     foreach ($tonnage as $t) {
    //         $html .= '
    //                 <tr>
    //                     <td align="center">' . $no++ . '</td>
    //                     <td align="center">' . $t->toonage . '</td>
    //                     <td align="right">' . number_format($t->total, 2) . '%</td>
    //                     <td align="center">' . $t->unit . '</td>
    //                     <td align="right">' . number_format($t->average, 2) . '%</td>
    //                 </tr>';
    //     }

    //     $html .= '
    //             </table>
    //         </div>

    //         <!-- RIGHT BOTTOM: EMPTY -->
    //         <div class="table-box"><h3>&nbsp;</h3></div>

    //     </div>
    //     </body></html>';

    //     echo $html;
    // }

}
